<?php
// admin/extras/fix_missing_commissions.php
session_start();
require_once("../../config/config.php");

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die("Database connection failed.");
}

function convertCommissionToInt($commission)
{
    return intval(preg_replace('/[^0-9]/', '', (string)$commission));
}

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    echo "<!DOCTYPE html><html><head><title>Fix Missing Commissions</title><style>body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; font-size: 14px; line-height: 1.6; } pre { white-space: pre-wrap; word-wrap: break-word; }</style></head><body><pre>";
}

echo "===================================================\n";
echo " PROMOTER MISSING COMMISSION REPAIR / BACKFILL SCRIPT\n";
echo "===================================================\n\n";

try {
    // Fetch all active customers who have at least one verified payment
    $query = "
        SELECT DISTINCT 
            c.CustomerID, 
            c.CustomerUniqueID, 
            c.Name AS CustomerName, 
            TRIM(c.PromoterID) AS PromoterUniqueID,
            p.SchemeID,
            s.SchemeName
        FROM Customers c
        JOIN Payments p ON c.CustomerID = p.CustomerID
        LEFT JOIN Schemes s ON p.SchemeID = s.SchemeID
        WHERE p.Status = 'Verified'
          AND c.PromoterID IS NOT NULL
          AND TRIM(c.PromoterID) != ''
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $verifiedCustomers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($verifiedCustomers) . " customer(s) with verified payments and assigned promoters.\n\n";

    $creditedCount = 0;
    $skippedCount = 0;

    foreach ($verifiedCustomers as $cust) {
        $custID = $cust['CustomerID'];
        $custUniqueID = $cust['CustomerUniqueID'];
        $custName = $cust['CustomerName'];
        $promoterUniqueID = $cust['PromoterUniqueID'];
        $schemeName = !empty($cust['SchemeName']) ? $cust['SchemeName'] : 'Gold Savings Plan';

        // Fetch Direct Promoter details
        $pStmt = $conn->prepare("SELECT PromoterID, PromoterUniqueID, ParentPromoterID, Commission, ParentCommission, Name FROM Promoters WHERE TRIM(PromoterUniqueID) = ?");
        $pStmt->execute([$promoterUniqueID]);
        $promoter = $pStmt->fetch(PDO::FETCH_ASSOC);

        if (!$promoter) {
            echo "⚠️ Promoter [$promoterUniqueID] not found for customer $custName ($custUniqueID). Skipping.\n";
            $skippedCount++;
            continue;
        }

        $commissionAmount = convertCommissionToInt($promoter['Commission']);

        // Check if direct commission for this customer was ALREADY logged in WalletLogs
        $checkStmt = $conn->prepare("
            SELECT COUNT(*) as already_credited 
            FROM WalletLogs 
            WHERE TRIM(PromoterUniqueID) = ? 
              AND (Message LIKE ? OR Message LIKE ?)
        ");
        $checkStmt->execute([
            $promoterUniqueID,
            "%" . $custUniqueID . "%",
            "%" . $custName . "%"
        ]);
        $directAlreadyCredited = $checkStmt->fetch(PDO::FETCH_ASSOC)['already_credited'];

        $actionTaken = false;

        try {
            $conn->beginTransaction();

            // 1. Process Direct Promoter Commission if missing
            if ($directAlreadyCredited == 0 && $commissionAmount > 0) {
                $wStmt = $conn->prepare("SELECT BalanceID FROM PromoterWallet WHERE TRIM(PromoterUniqueID) = ?");
                $wStmt->execute([$promoterUniqueID]);
                $walletRecord = $wStmt->fetch(PDO::FETCH_ASSOC);

                if ($walletRecord) {
                    $upStmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = BalanceAmount + ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ?");
                    $upStmt->execute([$commissionAmount, $promoterUniqueID]);
                } else {
                    $inStmt = $conn->prepare("INSERT INTO PromoterWallet (UserID, PromoterUniqueID, BalanceAmount, Message) VALUES (?, ?, ?, ?)");
                    $inStmt->execute([$promoter['PromoterID'], $promoterUniqueID, $commissionAmount, "Commission from payment"]);
                }

                $logMsg = "Commission earned from customer $custName ($custUniqueID) for $schemeName scheme";
                $logStmt = $conn->prepare("INSERT INTO WalletLogs (PromoterUniqueID, Amount, Message, TransactionType) VALUES (?, ?, ?, 'Credit')");
                $logStmt->execute([$promoterUniqueID, $commissionAmount, $logMsg]);

                echo "✅ Credited ₹$commissionAmount to Direct Promoter {$promoter['Name']} ($promoterUniqueID) for customer $custName ($custUniqueID).\n";
                $actionTaken = true;
            } else {
                echo "ℹ️ Direct Commission (₹$commissionAmount) already credited for customer $custName ($custUniqueID) to promoter {$promoter['Name']} ($promoterUniqueID).\n";
            }

            // 2. Process Parent Promoter Commission if applicable (Independently checked)
            if (!empty($promoter['ParentPromoterID']) && !empty($promoter['ParentCommission'])) {
                $parentPromoterID = trim($promoter['ParentPromoterID']);
                $parentCommissionAmount = convertCommissionToInt($promoter['ParentCommission']);

                if ($parentCommissionAmount > 0) {
                    $parentStmt = $conn->prepare("SELECT PromoterID, PromoterUniqueID, Name FROM Promoters WHERE TRIM(PromoterUniqueID) = ?");
                    $parentStmt->execute([$parentPromoterID]);
                    $parentPromoter = $parentStmt->fetch(PDO::FETCH_ASSOC);

                    if ($parentPromoter) {
                        // Check if parent commission already logged
                        $pCheckStmt = $conn->prepare("
                            SELECT COUNT(*) as parent_already_credited 
                            FROM WalletLogs 
                            WHERE TRIM(PromoterUniqueID) = ? 
                              AND (Message LIKE ? OR Message LIKE ?)
                        ");
                        $pCheckStmt->execute([
                            $parentPromoterID,
                            "%" . $custUniqueID . "%",
                            "%" . $custName . "%"
                        ]);
                        $parentAlreadyCredited = $pCheckStmt->fetch(PDO::FETCH_ASSOC)['parent_already_credited'];

                        if ($parentAlreadyCredited == 0) {
                            $pwStmt = $conn->prepare("SELECT BalanceID FROM PromoterWallet WHERE TRIM(PromoterUniqueID) = ?");
                            $pwStmt->execute([$parentPromoterID]);
                            $parentWalletRecord = $pwStmt->fetch(PDO::FETCH_ASSOC);

                            if ($parentWalletRecord) {
                                $pupStmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = BalanceAmount + ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ?");
                                $pupStmt->execute([$parentCommissionAmount, $parentPromoterID]);
                            } else {
                                $pinStmt = $conn->prepare("INSERT INTO PromoterWallet (UserID, PromoterUniqueID, BalanceAmount, Message) VALUES (?, ?, ?, ?)");
                                $pinStmt->execute([$parentPromoter['PromoterID'], $parentPromoterID, $parentCommissionAmount, "Parent commission from payment"]);
                            }

                            $pLogMsg = "Parent commission earned from customer $custName ($custUniqueID) for $schemeName scheme";
                            $pLogStmt = $conn->prepare("INSERT INTO WalletLogs (PromoterUniqueID, Amount, Message, TransactionType) VALUES (?, ?, ?, 'Credit')");
                            $pLogStmt->execute([$parentPromoterID, $parentCommissionAmount, $pLogMsg]);

                            echo "   └─ ✅ Credited Parent Commission ₹$parentCommissionAmount to Parent Promoter {$parentPromoter['Name']} ($parentPromoterID) for customer $custName ($custUniqueID).\n";
                            $actionTaken = true;
                        } else {
                            echo "   └─ ℹ️ Parent Commission (₹$parentCommissionAmount) already credited for customer $custName ($custUniqueID) to parent promoter {$parentPromoter['Name']} ($parentPromoterID).\n";
                        }
                    }
                }
            }

            $conn->commit();

            if ($actionTaken) {
                $creditedCount++;
            } else {
                $skippedCount++;
            }

        } catch (Exception $e) {
            $conn->rollBack();
            echo "❌ Error processing customer $custName ($custUniqueID): " . $e->getMessage() . "\n";
        }
    }

    echo "\n===================================================\n";
    echo " SUMMARY: Updates Applied: $creditedCount | Already Up To Date: $skippedCount\n";
    echo "===================================================\n";

} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}

if (!$isCli) {
    echo "</pre></body></html>";
}
