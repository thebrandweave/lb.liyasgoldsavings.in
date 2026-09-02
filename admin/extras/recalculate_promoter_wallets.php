<?php
// admin/extras/recalculate_promoter_wallets.php
session_start();
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
echo " RECALCULATE PROMOTER WALLETS FROM WALLETLOGS\n";
echo "===================================================\n\n";

try {
    $conn->beginTransaction();

    // Fetch all promoters
    $stmt = $conn->prepare("SELECT PromoterID, PromoterUniqueID, Name FROM Promoters");
    $stmt->execute();
    $promoters = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Synchronizing wallet balances for " . count($promoters) . " promoter(s)...\n\n";

    $updatedCount = 0;

    foreach ($promoters as $p) {
        $pID = $p['PromoterID'];
        $uniqueID = trim($p['PromoterUniqueID']);

        // Calculate Total Credits matching both string UniqueID and numeric PromoterID
        $cStmt = $conn->prepare("
            SELECT COALESCE(SUM(Amount), 0) AS total_credit 
            FROM WalletLogs 
            WHERE (TRIM(PromoterUniqueID) = ? OR TRIM(PromoterUniqueID) = ?)
              AND (TransactionType = 'Credit' OR TransactionType IS NULL OR TransactionType = '')
        ");
        $cStmt->execute([$uniqueID, (string)$pID]);
        $totalCredit = floatval($cStmt->fetch(PDO::FETCH_ASSOC)['total_credit']);

        // Calculate Total Debits
        $dStmt = $conn->prepare("
            SELECT COALESCE(SUM(Amount), 0) AS total_debit 
            FROM WalletLogs 
            WHERE (TRIM(PromoterUniqueID) = ? OR TRIM(PromoterUniqueID) = ?)
              AND TransactionType = 'Debit'
        ");
        $dStmt->execute([$uniqueID, (string)$pID]);
        $totalDebit = floatval($dStmt->fetch(PDO::FETCH_ASSOC)['total_debit']);

        $netBalance = max(0, $totalCredit - $totalDebit);

        // Fetch current wallet record
        $wStmt = $conn->prepare("SELECT BalanceID, BalanceAmount FROM PromoterWallet WHERE TRIM(PromoterUniqueID) = ? OR UserID = ?");
        $wStmt->execute([$uniqueID, $pID]);
        $wallet = $wStmt->fetch(PDO::FETCH_ASSOC);

        if ($wallet) {
            $oldBal = floatval($wallet['BalanceAmount']);
            if (abs($oldBal - $netBalance) > 0.01) {
                $uStmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ? OR UserID = ?");
                $uStmt->execute([$netBalance, $uniqueID, $pID]);
                echo "✅ Synchronized {$p['Name']} [$uniqueID]: Old Balance = ₹" . number_format($oldBal, 2) . " ➔ New Balance = ₹" . number_format($netBalance, 2) . "\n";
                $updatedCount++;
            }
        } else {
            if ($netBalance > 0) {
                $inStmt = $conn->prepare("INSERT INTO PromoterWallet (UserID, PromoterUniqueID, BalanceAmount, Message) VALUES (?, ?, ?, 'Wallet synced from logs')");
                $inStmt->execute([$pID, $uniqueID, $netBalance]);
                echo "✅ Created Wallet {$p['Name']} [$uniqueID]: Balance = ₹" . number_format($netBalance, 2) . "\n";
                $updatedCount++;
            }
        }
    }

    $conn->commit();

    echo "\n===================================================\n";
    echo " SYNCHRONIZATION COMPLETE: Updated $updatedCount promoter wallet(s).\n";
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
