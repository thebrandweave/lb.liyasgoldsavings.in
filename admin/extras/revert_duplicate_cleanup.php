<?php
// admin/extras/revert_duplicate_cleanup.php
session_start();
require_once(__DIR__ . "/../../config/config.php");

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die("Database connection failed.");
}

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    echo "<!DOCTYPE html><html><head><title>Revert Duplicate Cleanup</title><style>body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; font-size: 14px; line-height: 1.6; } pre { white-space: pre-wrap; word-wrap: break-word; }</style></head><body><pre>";
}

echo "===================================================\n";
echo " REVERT DUPLICATE CLEANUP SCRIPT\n";
echo "===================================================\n\n";

$targetPromoterUniqueID = 'GDP01519';

try {
    $conn->beginTransaction();

    // 1. Check if LogIDs 61499 and 61500 exist or need re-insertion
    $stmt = $conn->prepare("SELECT COUNT(*) FROM WalletLogs WHERE LogID IN (61499, 61500)");
    $stmt->execute();
    $existingCount = $stmt->fetchColumn();

    if ($existingCount == 0) {
        // Re-insert Log 61499
        $in1 = $conn->prepare("INSERT INTO WalletLogs (LogID, PromoterUniqueID, Amount, Message, CreatedAt, TransactionType) VALUES (61499, 'GDP01519', 400.00, 'Commission earned from customer C BHILVANTH (LB4202) for Gold Savings Plan (LB) scheme', '2026-09-02 06:43:02', 'Credit')");
        $in1->execute();

        // Re-insert Log 61500
        $in2 = $conn->prepare("INSERT INTO WalletLogs (LogID, PromoterUniqueID, Amount, Message, CreatedAt, TransactionType) VALUES (61500, 'GDP01519', 400.00, 'Commission earned from customer S BUNNY ROHITH (LB4206) for Gold Savings Plan (LB) scheme', '2026-09-02 06:43:02', 'Credit')");
        $in2->execute();

        echo "✅ Restored LogIDs 61499 and 61500 into WalletLogs.\n";
    } else {
        echo "ℹ️ LogIDs 61499 and 61500 are already present in WalletLogs.\n";
    }

    // 2. Add ₹800.00 back to GDP01519 wallet balance
    $wStmt = $conn->prepare("SELECT BalanceID, BalanceAmount FROM PromoterWallet WHERE TRIM(PromoterUniqueID) = ?");
    $wStmt->execute([$targetPromoterUniqueID]);
    $wallet = $wStmt->fetch(PDO::FETCH_ASSOC);

    if ($wallet) {
        $oldBal = floatval($wallet['BalanceAmount']);
        $newBal = $oldBal + 800.00;

        $uStmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ?");
        $uStmt->execute([$newBal, $targetPromoterUniqueID]);

        echo "✅ Restored Wallet Balance for Nandyala Raju yadav ($targetPromoterUniqueID):\n";
        echo "   • Previous Balance: ₹" . number_format($oldBal, 2) . "\n";
        echo "   • Restored Amount : ₹800.00\n";
        echo "   • New Balance      : ₹" . number_format($newBal, 2) . "\n";
    }

    $conn->commit();

    echo "\n===================================================\n";
    echo " REVERT COMPLETED SUCCESSFULLY!\n";
    echo "===================================================\n";

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "❌ Error during revert: " . $e->getMessage() . "\n";
}

if (!$isCli) {
    echo "</pre></body></html>";
}
