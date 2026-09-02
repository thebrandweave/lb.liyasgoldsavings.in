<?php
// admin/extras/undo_fix_missing_commissions.php
session_start();
require_once(__DIR__ . "/../../config/config.php");

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die("Database connection failed.");
}

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    echo "<!DOCTYPE html><html><head><title>Undo Fix Missing Commissions</title><style>body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; font-size: 14px; line-height: 1.6; } pre { white-space: pre-wrap; word-wrap: break-word; } .success { color: #4EC9B0; } .warning { color: #CE9178; } .danger { color: #F44747; }</style></head><body><pre>";
}

echo "===================================================\n";
echo " UNDO / REVERT FIX MISSING COMMISSIONS SCRIPT\n";
echo "===================================================\n\n";

try {
    // 1. Find all WalletLogs created today (Sep 02, 2026) by the backfill script
    // These logs were added today for customer commission credits
    $stmt = $conn->prepare("
        SELECT LogID, TRIM(PromoterUniqueID) AS PromoterUniqueID, Amount, Message, CreatedAt 
        FROM WalletLogs 
        WHERE CreatedAt >= '2026-09-02 00:00:00' 
          AND TransactionType = 'Credit' 
          AND (Message LIKE '%Commission earned from customer%' OR Message LIKE '%Parent commission earned from customer%')
        ORDER BY LogID DESC
    ");
    $stmt->execute();
    $todayLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($todayLogs) . " commission log entry(ies) created today (Sep 02, 2026) to revert.\n\n";

    if (empty($todayLogs)) {
        echo "ℹ️ No commission logs found from today to revert.\n";
    } else {
        $logIdsToDelete = [];
        $deductionsByPromoter = [];

        foreach ($todayLogs as $log) {
            $logIdsToDelete[] = $log['LogID'];
            $pID = $log['PromoterUniqueID'];
            $amt = floatval($log['Amount']);

            if (!isset($deductionsByPromoter[$pID])) {
                $deductionsByPromoter[$pID] = 0;
            }
            $deductionsByPromoter[$pID] += $amt;

            echo " ❌ Mark for Revert: LogID #{$log['LogID']} | Promoter: $pID | Amount: ₹" . number_format($amt, 2) . " | Message: {$log['Message']}\n";
        }

        echo "\n---------------------------------------------------\n";
        echo " REVERTING DATABASE CHANGES...\n";
        echo "---------------------------------------------------\n\n";

        $conn->beginTransaction();

        // 2. Deduct amounts from PromoterWallet
        foreach ($deductionsByPromoter as $pID => $totalDeduct) {
            $wStmt = $conn->prepare("SELECT BalanceID, BalanceAmount FROM PromoterWallet WHERE TRIM(PromoterUniqueID) = ?");
            $wStmt->execute([$pID]);
            $wallet = $wStmt->fetch(PDO::FETCH_ASSOC);

            if ($wallet) {
                $oldBal = floatval($wallet['BalanceAmount']);
                $newBal = max(0, $oldBal - $totalDeduct);

                $uStmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ?");
                $uStmt->execute([$newBal, $pID]);

                echo "  • Promoter [$pID]: Old Balance: ₹" . number_format($oldBal, 2) . " | Reverted: -₹" . number_format($totalDeduct, 2) . " | Restored Balance: ₹" . number_format($newBal, 2) . "\n";
            }
        }

        // 3. Delete the backfill logs
        $inClause = implode(',', array_fill(0, count($logIdsToDelete), '?'));
        $dStmt = $conn->prepare("DELETE FROM WalletLogs WHERE LogID IN ($inClause)");
        $dStmt->execute($logIdsToDelete);
        $deletedCount = $dStmt->rowCount();

        echo "\n✅ Successfully deleted $deletedCount backfilled wallet log entry(ies).\n";

        $conn->commit();

        echo "\n===================================================\n";
        echo " REVERT COMPLETE: All commissions added by fix_missing_commissions.php have been undone.\n";
        echo "===================================================\n";
    }

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "❌ Error during revert: " . $e->getMessage() . "\n";
}

if (!$isCli) {
    echo "</pre></body></html>";
}
