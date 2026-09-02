<?php
// admin/extras/fix_missing_commissions.php
session_start();
set_time_limit(600); // Allow up to 10 minutes
ini_set('memory_limit', '512M');

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
    echo "<!DOCTYPE html><html><head><title>Fix Missing Commissions - Full System</title><style>body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; font-size: 14px; line-height: 1.6; } pre { white-space: pre-wrap; word-wrap: break-word; }</style></head><body><pre>";
}

echo "===================================================\n";
echo " ENTIRE SYSTEM PROMOTER MISSING COMMISSION REPAIR\n";
echo "===================================================\n\n";

try {
    // 1. Pre-load all promoters into memory hash maps for O(1) instant lookup
    $pStmt = $conn->prepare("SELECT PromoterID, PromoterUniqueID, ParentPromoterID, Commission, ParentCommission, Name FROM Promoters");
    $pStmt->execute();
    $allPromoters = $pStmt->fetchAll(PDO::FETCH_ASSOC);

    $promoterByRef = [];
    foreach ($allPromoters as $p) {
        $pID = (string)$p['PromoterID'];
        $uID = trim($p['PromoterUniqueID']);

        if (!empty($uID)) {
            $promoterByRef[$uID] = $p;
        }
        if (!empty($pID)) {
            $promoterByRef[$pID] = $p;
        }
    }

    // 2. Pre-load all existing WalletLogs keys into memory hash set
    $lStmt = $conn->prepare("
        SELECT LogID, TRIM(PromoterUniqueID) AS PromoterUniqueID, Message 
        FROM WalletLogs 
        WHERE TransactionType = 'Credit' OR TransactionType IS NULL OR TransactionType = ''
    ");
    $lStmt->execute();
    $allLogs = $lStmt->fetchAll(PDO::FETCH_ASSOC);

    $existingLogKeys = [];
    foreach ($allLogs as $l) {
        $pKey = trim($l['PromoterUniqueID']);
        if (preg_match('/(LB[0-9]+)/i', $l['Message'], $m)) {
            $lbKey = strtoupper($m[1]);
            $existingLogKeys[$pKey . '_' . $lbKey] = true;
        }
    }

    // 3. Fetch ALL verified payments across the ENTIRE system
    $query = "
        SELECT 
            p.PaymentID,
            p.CustomerID,
            c.CustomerUniqueID, 
            c.Name AS CustomerName, 
            TRIM(c.PromoterID) AS DirectPromoterRef,
            p.SchemeID,
            s.SchemeName,
            p.SubmittedAt
        FROM Payments p
        JOIN Customers c ON p.CustomerID = c.CustomerID
        LEFT JOIN Schemes s ON p.SchemeID = s.SchemeID
        WHERE p.Status = 'Verified'
          AND c.PromoterID IS NOT NULL
          AND TRIM(c.PromoterID) != ''
        ORDER BY p.PaymentID ASC
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $allPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Loaded " . count($promoterByRef) . " promoter records and " . count($existingLogKeys) . " log records.\n";
    echo "Processing ALL " . count($allPayments) . " verified payment records across the ENTIRE database...\n\n";

    $creditedCount = 0;
    $skippedCount = 0;

    $conn->beginTransaction();

    $walletUpdates = []; // promoterUniqueID => float amount to add
    $logInserts = [];    // arrays of log data to insert

    foreach ($allPayments as $pay) {
        $custUniqueID = strtoupper(trim($pay['CustomerUniqueID']));
        $custName = $pay['CustomerName'];
        $directRef = $pay['DirectPromoterRef'];
        $schemeName = !empty($pay['SchemeName']) ? $pay['SchemeName'] : 'Gold Savings Plan';

        // Build full multi-level promoter hierarchy in memory
        $hierarchy = [];
        $currRef = $directRef;
        $visited = [];

        while (!empty($currRef) && !isset($visited[$currRef])) {
            $visited[$currRef] = true;

            if (!isset($promoterByRef[$currRef])) {
                break;
            }

            $pData = $promoterByRef[$currRef];
            $hierarchy[] = $pData;
            $currRef = !empty($pData['ParentPromoterID']) ? trim($pData['ParentPromoterID']) : null;
        }

        if (empty($hierarchy)) {
            $skippedCount++;
            continue;
        }

        $actionTaken = false;

        // 1. Process Direct Promoter
        $directPromoter = $hierarchy[0];
        $directID = trim($directPromoter['PromoterUniqueID']);
        $directCommission = convertCommissionToInt($directPromoter['Commission']);

        if ($directCommission > 0) {
            $key1 = $directID . '_' . $custUniqueID;
            $key2 = (string)$directPromoter['PromoterID'] . '_' . $custUniqueID;

            if (!isset($existingLogKeys[$key1]) && !isset($existingLogKeys[$key2])) {
                if (!isset($walletUpdates[$directID])) {
                    $walletUpdates[$directID] = 0;
                }
                $walletUpdates[$directID] += $directCommission;

                $logMsg = "Commission earned from customer $custName ($custUniqueID) for $schemeName scheme";
                $logInserts[] = [
                    'promoter_id' => $directID,
                    'amount' => $directCommission,
                    'message' => $logMsg
                ];

                $existingLogKeys[$key1] = true;
                echo "✅ Direct Commission ₹$directCommission to {$directPromoter['Name']} ($directID) for customer $custName ($custUniqueID).\n";
                $actionTaken = true;
            }
        }

        // 2. Process All Multi-level Parent Promoters in Hierarchy
        for ($i = 0; $i < count($hierarchy) - 1; $i++) {
            $childPromoter = $hierarchy[$i];
            $parentPromoter = $hierarchy[$i + 1];

            $parentID = trim($parentPromoter['PromoterUniqueID']);
            $childCommission = convertCommissionToInt($childPromoter['Commission']);
            $parentCommission = convertCommissionToInt($parentPromoter['Commission']);

            $gapAmount = 0;
            if (!empty($childPromoter['ParentCommission']) && convertCommissionToInt($childPromoter['ParentCommission']) > 0) {
                $gapAmount = convertCommissionToInt($childPromoter['ParentCommission']);
            } else if ($parentCommission > $childCommission) {
                $gapAmount = $parentCommission - $childCommission;
            }

            if ($gapAmount > 0) {
                $pKey1 = $parentID . '_' . $custUniqueID;
                $pKey2 = (string)$parentPromoter['PromoterID'] . '_' . $custUniqueID;

                if (!isset($existingLogKeys[$pKey1]) && !isset($existingLogKeys[$pKey2])) {
                    if (!isset($walletUpdates[$parentID])) {
                        $walletUpdates[$parentID] = 0;
                    }
                    $walletUpdates[$parentID] += $gapAmount;

                    $pLogMsg = "Parent commission earned from customer $custName ($custUniqueID) for $schemeName scheme";
                    $logInserts[] = [
                        'promoter_id' => $parentID,
                        'amount' => $gapAmount,
                        'message' => $pLogMsg
                    ];

                    $existingLogKeys[$pKey1] = true;
                    echo "   └─ ✅ Parent Commission ₹$gapAmount to {$parentPromoter['Name']} ($parentID) for customer $custName ($custUniqueID).\n";
                    $actionTaken = true;
                }
            }
        }

        if ($actionTaken) {
            $creditedCount++;
        } else {
            $skippedCount++;
        }
    }

    // 4. Batch Commit Updates to Database
    echo "\n---------------------------------------------------\n";
    echo " WRITING BATCH UPDATES TO ENTIRE DATABASE...\n";
    echo "---------------------------------------------------\n";

    if (!empty($walletUpdates)) {
        foreach ($walletUpdates as $pID => $addAmount) {
            $wStmt = $conn->prepare("SELECT BalanceID FROM PromoterWallet WHERE TRIM(PromoterUniqueID) = ?");
            $wStmt->execute([$pID]);
            $wRecord = $wStmt->fetch(PDO::FETCH_ASSOC);

            if ($wRecord) {
                $uStmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = BalanceAmount + ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ?");
                $uStmt->execute([$addAmount, $pID]);
            } else {
                $pData = $promoterByRef[$pID] ?? null;
                $numID = $pData ? $pData['PromoterID'] : 0;
                $inStmt = $conn->prepare("INSERT INTO PromoterWallet (UserID, PromoterUniqueID, BalanceAmount, Message) VALUES (?, ?, ?, 'Backfilled commission')");
                $inStmt->execute([$numID, $pID, $addAmount]);
            }
        }
    }

    if (!empty($logInserts)) {
        $lStmt = $conn->prepare("INSERT INTO WalletLogs (PromoterUniqueID, Amount, Message, TransactionType) VALUES (?, ?, ?, 'Credit')");
        foreach ($logInserts as $lData) {
            $lStmt->execute([$lData['promoter_id'], $lData['amount'], $lData['message']]);
        }
    }

    $conn->commit();

    echo "\n===================================================\n";
    echo " FULL SYSTEM BACKFILL COMPLETE: Credited: $creditedCount | Up To Date: $skippedCount | Total Log Inserts: " . count($logInserts) . "\n";
    echo "===================================================\n";

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Fatal Error: " . $e->getMessage() . "\n";
}

if (!$isCli) {
    echo "</pre></body></html>";
}
