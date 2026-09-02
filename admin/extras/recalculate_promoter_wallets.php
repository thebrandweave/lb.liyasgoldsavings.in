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
    echo "<!DOCTYPE html><html><head><title>Update PromoterWallet Balances</title><style>body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; font-size: 14px; line-height: 1.6; } pre { white-space: pre-wrap; word-wrap: break-word; }</style></head><body><pre>";
}

echo "===================================================\n";
echo " UPDATE PROMOTERWALLET.BALANCEAMOUNT FROM WALLETLOGS\n";
echo "===================================================\n\n";

try {
    // 1. Calculate ActualNetBalance using exact user SQL formula
    $stmt = $conn->prepare("
        SELECT 
            TRIM(PromoterUniqueID) AS PromoterUniqueID,
            SUM(CASE 
                WHEN TransactionType = 'Credit' THEN Amount
                WHEN TransactionType = 'Debit' AND Amount < 0 THEN Amount
                ELSE 0
            END) AS ActualNetBalance
        FROM WalletLogs
        WHERE PromoterUniqueID IS NOT NULL AND TRIM(PromoterUniqueID) != ''
        GROUP BY TRIM(PromoterUniqueID)
    ");
    $stmt->execute();
    $logTotals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($logTotals) . " promoter log record(s) to update in PromoterWallet...\n\n";

    // 2. Pre-load all current PromoterWallet rows
    $wStmt = $conn->prepare("SELECT BalanceID, UserID, TRIM(PromoterUniqueID) AS PromoterUniqueID, BalanceAmount FROM PromoterWallet");
    $wStmt->execute();
    $wallets = $wStmt->fetchAll(PDO::FETCH_ASSOC);

    $currentWallets = [];
    foreach ($wallets as $w) {
        $uID = trim($w['PromoterUniqueID']);
        if (!empty($uID)) {
            $currentWallets[$uID] = $w;
        }
        $uNum = (string)$w['UserID'];
        if (!empty($uNum)) {
            $currentWallets[$uNum] = $w;
        }
    }

    $conn->beginTransaction();
    $updatedCount = 0;

    $upStmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ? OR UserID = ?");

    foreach ($logTotals as $row) {
        $pID = trim($row['PromoterUniqueID']);
        $netBalance = max(0, floatval($row['ActualNetBalance']));

        $walletRecord = $currentWallets[$pID] ?? null;

        if ($walletRecord) {
            $oldBal = floatval($walletRecord['BalanceAmount']);
            $uniqueID = trim($walletRecord['PromoterUniqueID']);
            $userID = $walletRecord['UserID'];

            $upStmt->execute([$netBalance, $uniqueID, $userID]);
            echo "✅ Updated PromoterWallet [$uniqueID]: BalanceAmount changed from ₹" . number_format($oldBal, 2) . " ➔ ₹" . number_format($netBalance, 2) . "\n";
            $updatedCount++;
        }
    }

    $conn->commit();

    echo "\n===================================================\n";
    echo " UPDATED $updatedCount PROMOTERWALLET ROW(S) IN DATABASE SUCCESSFULLY!\n";
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
