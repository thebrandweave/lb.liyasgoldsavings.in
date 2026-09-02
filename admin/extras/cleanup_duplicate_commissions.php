<?php
// admin/extras/cleanup_duplicate_commissions.php
session_start();
require_once("../../config/config.php");

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die("Database connection failed.");
}

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    echo "<!DOCTYPE html><html><head><title>Cleanup Duplicate Commissions</title><style>body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; font-size: 14px; line-height: 1.6; } pre { white-space: pre-wrap; word-wrap: break-word; }</style></head><body><pre>";
}

echo "===================================================\n";
echo " CLEANUP DUPLICATE COMMISSIONS SCRIPT\n";
echo "===================================================\n\n";

$targetPromoterUniqueID = 'GDP01519';
$logIdsToDelete = [61499, 61500];
$deductAmount = 800.00;

try {
    $conn->beginTransaction();

    // 1. Fetch current wallet balance of GDP01519
    $wStmt = $conn->prepare("SELECT BalanceID, BalanceAmount FROM PromoterWallet WHERE TRIM(PromoterUniqueID) = ?");
    $wStmt->execute([$targetPromoterUniqueID]);
    $wallet = $wStmt->fetch(PDO::FETCH_ASSOC);

    if (!$wallet) {
        throw new Exception("PromoterWallet record for $targetPromoterUniqueID not found.");
    }

    $oldBalance = floatval($wallet['BalanceAmount']);
    $newBalance = max(0, $oldBalance - $deductAmount);

    echo "Promoter: Nandyala Raju yadav ($targetPromoterUniqueID)\n";
    echo "Current Wallet Balance : ₹" . number_format($oldBalance, 2) . "\n";
    echo "Deduction Amount       : ₹" . number_format($deductAmount, 2) . "\n";
    echo "New Wallet Balance     : ₹" . number_format($newBalance, 2) . "\n\n";

    // 2. Update PromoterWallet
    $uStmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = BalanceAmount - ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ?");
    $uStmt->execute([$deductAmount, $targetPromoterUniqueID]);
    echo "✅ Updated PromoterWallet: Deducted ₹$deductAmount from $targetPromoterUniqueID.\n";

    // 3. Delete Log IDs 61499 and 61500 from WalletLogs
    $dStmt = $conn->prepare("DELETE FROM WalletLogs WHERE LogID IN (?, ?) AND TRIM(PromoterUniqueID) = ?");
    $dStmt->execute([$logIdsToDelete[0], $logIdsToDelete[1], $targetPromoterUniqueID]);
    $deletedRows = $dStmt->rowCount();

    echo "✅ Deleted $deletedRows record(s) from WalletLogs (LogIDs: " . implode(', ', $logIdsToDelete) . ").\n";

    $conn->commit();

    echo "\n===================================================\n";
    echo " CLEANUP COMPLETED SUCCESSFULLY!\n";
    echo "===================================================\n";

} catch (Exception $e) {
    $conn->rollBack();
    echo "❌ Error during cleanup: " . $e->getMessage() . "\n";
}

if (!$isCli) {
    echo "</pre></body></html>";
}
