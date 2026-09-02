<?php
// admin/extras/cleanup_all_duplicate_commissions.php
session_start();
require_once("../../config/config.php");

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die("Database connection failed.");
}

function extractCustomerUniqueID($message)
{
    // Match patterns like (LB1234) or LB1234
    if (preg_match('/\((LB[0-9]+)\)/i', $message, $matches)) {
        return strtoupper($matches[1]);
    }
    if (preg_match('/\b(LB[0-9]+)\b/i', $message, $matches)) {
        return strtoupper($matches[1]);
    }
    return null;
}

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    echo "<!DOCTYPE html><html><head><title>Cleanup All Duplicate Commissions</title><style>body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; font-size: 14px; line-height: 1.6; } pre { white-space: pre-wrap; word-wrap: break-word; } .success { color: #4EC9B0; } .warning { color: #CE9178; } .danger { color: #F44747; }</style></head><body><pre>";
}

echo "===================================================\n";
echo " AUTOMATED DUPLICATE COMMISSION CLEANUP SCRIPT\n";
echo "===================================================\n\n";

try {
    // 1. Fetch all Credit logs from WalletLogs
    $stmt = $conn->prepare("
        SELECT LogID, TRIM(PromoterUniqueID) AS PromoterUniqueID, Amount, Message, CreatedAt 
        FROM WalletLogs 
        WHERE TransactionType = 'Credit' 
          AND (Message LIKE '%customer%' OR Message LIKE '%LB%')
        ORDER BY LogID ASC
    ");
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Scanning " . count($logs) . " credit wallet log(s)...\n\n";

    $directCommissions = [];
    $parentCommissions = [];

    // Group logs by CustomerUniqueID
    foreach ($logs as $log) {
        $custID = extractCustomerUniqueID($log['Message']);
        if (!$custID) {
            continue;
        }

        $isParent = (strpos($log['Message'], 'Parent commission') !== false || strpos($log['Message'], 'parent commission') !== false);

        if ($isParent) {
            $parentCommissions[$custID][] = $log;
        } else {
            $directCommissions[$custID][] = $log;
        }
    }

    $duplicateLogIDs = [];
    $deductionsByPromoter = [];
    $duplicateReport = [];

    // 2. Identify Direct Commission Duplicates
    foreach ($directCommissions as $custID => $custLogs) {
        if (count($custLogs) > 1) {
            // Keep the first (earliest) log, mark others as duplicates
            $originalLog = $custLogs[0];
            for ($i = 1; $i < count($custLogs); $i++) {
                $dupLog = $custLogs[$i];
                $duplicateLogIDs[] = $dupLog['LogID'];

                $pID = $dupLog['PromoterUniqueID'];
                $amt = floatval($dupLog['Amount']);

                if (!isset($deductionsByPromoter[$pID])) {
                    $deductionsByPromoter[$pID] = 0;
                }
                $deductionsByPromoter[$pID] += $amt;

                $duplicateReport[] = [
                    'type' => 'Direct',
                    'customer' => $custID,
                    'log_id' => $dupLog['LogID'],
                    'promoter' => $pID,
                    'amount' => $amt,
                    'date' => $dupLog['CreatedAt'],
                    'original_log_id' => $originalLog['LogID']
                ];
            }
        }
    }

    // 3. Identify Parent Commission Duplicates
    foreach ($parentCommissions as $custID => $custLogs) {
        if (count($custLogs) > 1) {
            $originalLog = $custLogs[0];
            for ($i = 1; $i < count($custLogs); $i++) {
                $dupLog = $custLogs[$i];
                $duplicateLogIDs[] = $dupLog['LogID'];

                $pID = $dupLog['PromoterUniqueID'];
                $amt = floatval($dupLog['Amount']);

                if (!isset($deductionsByPromoter[$pID])) {
                    $deductionsByPromoter[$pID] = 0;
                }
                $deductionsByPromoter[$pID] += $amt;

                $duplicateReport[] = [
                    'type' => 'Parent',
                    'customer' => $custID,
                    'log_id' => $dupLog['LogID'],
                    'promoter' => $pID,
                    'amount' => $amt,
                    'date' => $dupLog['CreatedAt'],
                    'original_log_id' => $originalLog['LogID']
                ];
            }
        }
    }

    if (empty($duplicateLogIDs)) {
        echo "🎉 No duplicate commission entries found! All wallet logs are clean.\n";
    } else {
        echo "Found " . count($duplicateLogIDs) . " duplicate log entry(ies) across " . count($deductionsByPromoter) . " promoter(s):\n\n";

        foreach ($duplicateReport as $rep) {
            echo "⚠️ [{$rep['type']} Duplicate] Customer {$rep['customer']} | Duplicate LogID #{$rep['log_id']} (Created {$rep['date']}) | Promoter: {$rep['promoter']} | Amount: ₹{$rep['amount']} (Original LogID #{$rep['original_log_id']})\n";
        }

        echo "\n---------------------------------------------------\n";
        echo " PROCESSING DATABASE CLEANUP & WALLET ADJUSTMENTS...\n";
        echo "---------------------------------------------------\n\n";

        $conn->beginTransaction();

        // 4. Delete Duplicate Logs
        $inClause = implode(',', array_fill(0, count($duplicateLogIDs), '?'));
        $delStmt = $conn->prepare("DELETE FROM WalletLogs WHERE LogID IN ($inClause)");
        $delStmt->execute($duplicateLogIDs);
        $deletedCount = $delStmt->rowCount();

        echo "✅ Deleted $deletedCount duplicate log entry(ies) from WalletLogs.\n\n";

        // 5. Update PromoterWallet Balances
        echo "Updating Promoter Wallet Balances:\n";
        foreach ($deductionsByPromoter as $promoterID => $totalDeduct) {
            $wStmt = $conn->prepare("SELECT BalanceID, BalanceAmount FROM PromoterWallet WHERE TRIM(PromoterUniqueID) = ?");
            $wStmt->execute([$promoterID]);
            $wallet = $wStmt->fetch(PDO::FETCH_ASSOC);

            if ($wallet) {
                $oldBal = floatval($wallet['BalanceAmount']);
                $newBal = max(0, $oldBal - $totalDeduct);

                $uStmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ?");
                $uStmt->execute([$newBal, $promoterID]);

                echo "  • Promoter [$promoterID]: Old Balance: ₹" . number_format($oldBal, 2) . " | Deducted: ₹" . number_format($totalDeduct, 2) . " | New Balance: ₹" . number_format($newBal, 2) . "\n";
            } else {
                echo "  • ⚠️ Promoter [$promoterID] wallet record not found.\n";
            }
        }

        $conn->commit();

        echo "\n===================================================\n";
        echo " CLEANUP COMPLETED: Deleted " . count($duplicateLogIDs) . " duplicate log(s) and updated " . count($deductionsByPromoter) . " promoter wallet(s).\n";
        echo "===================================================\n";
    }

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "❌ Error: " . $e->getMessage() . "\n";
}

if (!$isCli) {
    echo "</pre></body></html>";
}
