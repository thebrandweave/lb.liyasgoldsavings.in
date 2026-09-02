<?php
// admin/extras/recalculate_promoter_wallets.php
session_start();
set_time_limit(300);
ini_set('memory_limit', '256M');

require_once(__DIR__ . "/../../config/config.php");

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die("Database connection failed.");
}

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    echo "<!DOCTYPE html><html><head><title>Recalculate Promoter Wallets</title><style>body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; font-size: 14px; line-height: 1.6; } pre { white-space: pre-wrap; word-wrap: break-word; }</style></head><body><pre>";
}

echo "===================================================\n";
echo " RECALCULATE PROMOTER WALLETS (MATCHING EXACT DB DEBITS)\n";
echo "===================================================\n\n";

try {
    // 1. Calculate net balances using exact phpMyAdmin formula: TotalCredits MINUS TotalDebits
    $stmt = $conn->prepare("
        SELECT 
            TRIM(PromoterUniqueID) AS PromoterUniqueID,
            SUM(CASE WHEN TransactionType = 'Credit' OR TransactionType IS NULL OR TransactionType = '' THEN Amount ELSE 0 END) AS TotalCredit,
            SUM(CASE WHEN TransactionType = 'Debit' THEN ABS(Amount) ELSE 0 END) AS TotalDebit
        FROM WalletLogs
        WHERE PromoterUniqueID IS NOT NULL AND TRIM(PromoterUniqueID) != ''
        GROUP BY TRIM(PromoterUniqueID)
    ");
    $stmt->execute();
    $logTotals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Processing " . count($logTotals) . " promoter log summary record(s)...\n\n";

    // Pre-load all current wallet balances
    $wStmt = $conn->prepare("SELECT BalanceID, UserID, TRIM(PromoterUniqueID) AS PromoterUniqueID, BalanceAmount FROM PromoterWallet");
    $wStmt->execute();
    $wallets = $wStmt->fetchAll(PDO::FETCH_ASSOC);

    $currentWallets = [];
    foreach ($wallets as $w) {
        $uID = trim($w['PromoterUniqueID']);
        if (!empty($uID)) {
            $currentWallets[$uID] = $w;
        }
    }

    $conn->beginTransaction();
    $updatedCount = 0;

    $upStmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ?");

    foreach ($logTotals as $row) {
        $promoterID = trim($row['PromoterUniqueID']);
        $totalCredit = floatval($row['TotalCredit']);
        $totalDebit = floatval($row['TotalDebit']);
        $netBalance = max(0, $totalCredit - $totalDebit);

        if (isset($currentWallets[$promoterID])) {
            $oldBal = floatval($currentWallets[$promoterID]['BalanceAmount']);
            if (abs($oldBal - $netBalance) > 0.01) {
                $upStmt->execute([$netBalance, $promoterID]);
                echo "✅ Synchronized [$promoterID]: Stored = ₹" . number_format($oldBal, 2) . " ➔ Correct Net Balance = ₹" . number_format($netBalance, 2) . " (Credits: ₹" . number_format($totalCredit, 2) . " - Debits: ₹" . number_format($totalDebit, 2) . ")\n";
                $updatedCount++;
            } else {
                echo "ℹ️ Promoter [$promoterID]: Balance matches net total (₹" . number_format($netBalance, 2) . ").\n";
            }
        }
    }

    $conn->commit();

    echo "\n===================================================\n";
    echo " SYNCHRONIZATION COMPLETE: Updated $updatedCount promoter wallet(s) in DB.\n";
    echo "===================================================\n";

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "❌ Error: " . $e->getMessage() . "\n";
}

if (!$isCli) {
    echo "</pre></body></html>";
}
