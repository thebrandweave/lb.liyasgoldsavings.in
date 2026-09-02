<?php
// admin/extras/fix_missing_commissions.php
session_start();
set_time_limit(300); // Allow up to 5 minutes
ini_set('memory_limit', '256M');

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
echo " PROMOTER MISSING COMMISSION REPAIR / BACKFILL SCRIPT (HIGH SPEED)\n";
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

    // 2. Pre-load all existing WalletLogs keys into memory hash set for O(1) instant lookup
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
        // Match LB numbers in log message
        if (preg_match('/(LB[0-9]+)/i', $l['Message'], $m)) {
            $lbKey = strtoupper($m[1]);
            $existingLogKeys[$pKey . '_' . $lbKey] = true;
        }
    }

    // 3. Fetch all active customers with verified payments
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

    echo "Loaded " . count($promoterByRef) . " promoter references and " . count($existingLogKeys) . " existing log records into memory.\n";
    echo "Processing " . count($verifiedCustomers) . " verified customer payments...\n\n";

    $creditedCount = 0;
    $skippedCount = 0;

    $conn->beginTransaction();

    $walletUpdates = []; // promoterUniqueID => float amount to add
    $logInserts = [];    // arrays of log data to insert

    foreach ($verifiedCustomers as $cust) {
        $custUniqueID = strtoupper(trim($cust['CustomerUniqueID']));
        $custName = $cust['CustomerName'];
        $directRef = $cust['DirectPromoterRef'];
        $schemeName = !empty($cust['SchemeName']) ? $cust['SchemeName'] : 'Gold Savings Plan';

        // Build hierarchy in memory
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

        // 2. Process All Multi-level Parent Promoters
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

    // 4. Batch Commit Wallet Updates & WalletLogs Inserts
    echo "\n---------------------------------------------------\n";
    echo " WRITING BATCH UPDATES TO DATABASE...\n";
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
    echo " SUMMARY: Credited: $creditedCount | Up To Date: $skippedCount | Total Log Inserts: " . count($logInserts) . "\n";
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
