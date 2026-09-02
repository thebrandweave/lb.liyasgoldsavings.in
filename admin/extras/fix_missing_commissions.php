<?php
// admin/extras/fix_missing_commissions.php
session_start();
require_once(__DIR__ . "/../../config/config.php");

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
            TRIM(c.PromoterID) AS DirectPromoterRef,
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
        $directRef = $cust['DirectPromoterRef'];
        $schemeName = !empty($cust['SchemeName']) ? $cust['SchemeName'] : 'Gold Savings Plan';

        // Fetch full promoter hierarchy chain (matching both string UniqueID and numeric PromoterID)
        $hierarchy = [];
        $currRef = $directRef;
        $visited = [];

        while ($currRef && !in_array($currRef, $visited)) {
            $visited[] = $currRef;

            $pStmt = $conn->prepare("
                SELECT PromoterID, PromoterUniqueID, ParentPromoterID, Commission, ParentCommission, Name 
                FROM Promoters 
                WHERE TRIM(PromoterUniqueID) = ? OR CAST(PromoterID AS CHAR) = ?
            ");
            $pStmt->execute([$currRef, $currRef]);
            $pData = $pStmt->fetch(PDO::FETCH_ASSOC);

            if (!$pData) {
                break;
            }

            $hierarchy[] = $pData;
            $currRef = !empty($pData['ParentPromoterID']) ? trim($pData['ParentPromoterID']) : null;
        }

        if (empty($hierarchy)) {
            $skippedCount++;
            continue;
        }

        $actionTaken = false;

        try {
            $conn->beginTransaction();

            // 1. Process Direct Promoter (hierarchy[0])
            $directPromoter = $hierarchy[0];
            $directID = $directPromoter['PromoterUniqueID'];
            $directCommission = convertCommissionToInt($directPromoter['Commission']);

            if ($directCommission > 0) {
                $checkStmt = $conn->prepare("
                    SELECT COUNT(*) as already_credited 
                    FROM WalletLogs 
                    WHERE (TRIM(PromoterUniqueID) = ? OR TRIM(PromoterUniqueID) = ?)
                      AND (Message LIKE ? OR Message LIKE ?)
                ");
                $checkStmt->execute([
                    $directID,
                    (string)$directPromoter['PromoterID'],
                    "%" . $custUniqueID . "%",
                    "%" . $custName . "%"
                ]);
                $directAlreadyCredited = ($checkStmt->fetch(PDO::FETCH_ASSOC)['already_credited'] > 0);

                if (!$directAlreadyCredited) {
                    $wStmt = $conn->prepare("SELECT BalanceID FROM PromoterWallet WHERE TRIM(PromoterUniqueID) = ? OR UserID = ?");
                    $wStmt->execute([$directID, $directPromoter['PromoterID']]);
                    $walletRecord = $wStmt->fetch(PDO::FETCH_ASSOC);

                    if ($walletRecord) {
                        $upStmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = BalanceAmount + ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ? OR UserID = ?");
                        $upStmt->execute([$directCommission, $directID, $directPromoter['PromoterID']]);
                    } else {
                        $inStmt = $conn->prepare("INSERT INTO PromoterWallet (UserID, PromoterUniqueID, BalanceAmount, Message) VALUES (?, ?, ?, ?)");
                        $inStmt->execute([$directPromoter['PromoterID'], $directID, $directCommission, "Commission from payment"]);
                    }

                    $logMsg = "Commission earned from customer $custName ($custUniqueID) for $schemeName scheme";
                    $logStmt = $conn->prepare("INSERT INTO WalletLogs (PromoterUniqueID, Amount, Message, TransactionType) VALUES (?, ?, ?, 'Credit')");
                    $logStmt->execute([$directID, $directCommission, $logMsg]);

                    echo "✅ Direct Commission ₹$directCommission to {$directPromoter['Name']} ($directID) for customer $custName ($custUniqueID).\n";
                    $actionTaken = true;
                }
            }

            // 2. Process All Parent / Multi-level Promoters in Hierarchy
            for ($i = 0; $i < count($hierarchy) - 1; $i++) {
                $childPromoter = $hierarchy[$i];
                $parentPromoter = $hierarchy[$i + 1];

                $parentID = $parentPromoter['PromoterUniqueID'];
                $childCommission = convertCommissionToInt($childPromoter['Commission']);
                $parentCommission = convertCommissionToInt($parentPromoter['Commission']);

                $gapAmount = 0;
                if (!empty($childPromoter['ParentCommission']) && convertCommissionToInt($childPromoter['ParentCommission']) > 0) {
                    $gapAmount = convertCommissionToInt($childPromoter['ParentCommission']);
                } else if ($parentCommission > $childCommission) {
                    $gapAmount = $parentCommission - $childCommission;
                }

                if ($gapAmount > 0) {
                    $pCheckStmt = $conn->prepare("
                        SELECT COUNT(*) as parent_already_credited 
                        FROM WalletLogs 
                        WHERE (TRIM(PromoterUniqueID) = ? OR TRIM(PromoterUniqueID) = ?)
                          AND (Message LIKE ? OR Message LIKE ?)
                    ");
                    $pCheckStmt->execute([
                        $parentID,
                        (string)$parentPromoter['PromoterID'],
                        "%" . $custUniqueID . "%",
                        "%" . $custName . "%"
                    ]);
                    $parentAlreadyCredited = ($pCheckStmt->fetch(PDO::FETCH_ASSOC)['parent_already_credited'] > 0);

                    if (!$parentAlreadyCredited) {
                        $pwStmt = $conn->prepare("SELECT BalanceID FROM PromoterWallet WHERE TRIM(PromoterUniqueID) = ? OR UserID = ?");
                        $pwStmt->execute([$parentID, $parentPromoter['PromoterID']]);
                        $parentWalletRecord = $pwStmt->fetch(PDO::FETCH_ASSOC);

                        if ($parentWalletRecord) {
                            $pupStmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = BalanceAmount + ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ? OR UserID = ?");
                            $pupStmt->execute([$gapAmount, $parentID, $parentPromoter['PromoterID']]);
                        } else {
                            $pinStmt = $conn->prepare("INSERT INTO PromoterWallet (UserID, PromoterUniqueID, BalanceAmount, Message) VALUES (?, ?, ?, ?)");
                            $pinStmt->execute([$parentPromoter['PromoterID'], $parentID, $gapAmount, "Parent commission from payment"]);
                        }

                        $pLogMsg = "Parent commission earned from customer $custName ($custUniqueID) for $schemeName scheme";
                        $pLogStmt = $conn->prepare("INSERT INTO WalletLogs (PromoterUniqueID, Amount, Message, TransactionType) VALUES (?, ?, ?, 'Credit')");
                        $pLogStmt->execute([$parentID, $gapAmount, $pLogMsg]);

                        echo "   └─ ✅ Parent Commission ₹$gapAmount to {$parentPromoter['Name']} ($parentID) for customer $custName ($custUniqueID).\n";
                        $actionTaken = true;
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
