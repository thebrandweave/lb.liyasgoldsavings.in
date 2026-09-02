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
echo " RECALCULATE PROMOTER WALLETS (DUAL-ID & CASE TRIMMED)\n";
echo "===================================================\n\n";

try {
    // 1. Pre-load all promoters to map numeric PromoterID <-> string PromoterUniqueID
    $pStmt = $conn->prepare("SELECT PromoterID, PromoterUniqueID, Name FROM Promoters");
    $pStmt->execute();
    $promoters = $pStmt->fetchAll(PDO::FETCH_ASSOC);

    $idToUnique = [];
    foreach ($promoters as $p) {
        $numID = (string)$p['PromoterID'];
        $uID = trim($p['PromoterUniqueID']);
        if (!empty($uID)) {
            $idToUnique[$uID] = $uID;
            if (!empty($numID)) {
                $idToUnique[$numID] = $uID;
            }
        }
    }

    // 2. Fetch all WalletLogs and aggregate in memory with TRIM(UPPER(TransactionType))
    $lStmt = $conn->prepare("SELECT TRIM(PromoterUniqueID) AS PromoterUniqueID, Amount, TRIM(UPPER(TransactionType)) AS TxType FROM WalletLogs");
    $lStmt->execute();
    $allLogs = $lStmt->fetchAll(PDO::FETCH_ASSOC);

    $creditsByUnique = [];
    $debitsByUnique = [];

    foreach ($allLogs as $log) {
        $rawID = trim($log['PromoterUniqueID']);
        if (empty($rawID)) continue;

        // Map to standard string PromoterUniqueID
        $canonicalID = $idToUnique[$rawID] ?? $rawID;
        $amt = floatval($log['Amount']);
        $txType = $log['TxType'];

        if ($txType === 'DEBIT' || $amt < 0) {
            if (!isset($debitsByUnique[$canonicalID])) {
                $debitsByUnique[$canonicalID] = 0;
            }
            $debitsByUnique[$canonicalID] += abs($amt);
        } else {
            if (!isset($creditsByUnique[$canonicalID])) {
                $creditsByUnique[$canonicalID] = 0;
            }
            $creditsByUnique[$canonicalID] += abs($amt);
        }
    }

    // Pre-load current wallet balances
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

    $upStmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ? OR UserID = ?");

    foreach ($promoters as $p) {
        $canonicalID = trim($p['PromoterUniqueID']);
        $numID = $p['PromoterID'];

        $totalCredit = $creditsByUnique[$canonicalID] ?? 0;
        $totalDebit = $debitsByUnique[$canonicalID] ?? 0;
        $netBalance = max(0, $totalCredit - $totalDebit);

        $oldBal = isset($currentWallets[$canonicalID]) ? floatval($currentWallets[$canonicalID]['BalanceAmount']) : -1;

        if (abs($oldBal - $netBalance) > 0.01) {
            $upStmt->execute([$netBalance, $canonicalID, $numID]);
            echo "✅ Synchronized {$p['Name']} [$canonicalID]: Stored = ₹" . number_format($oldBal, 2) . " ➔ Correct Net Balance = ₹" . number_format($netBalance, 2) . " (Credits: ₹" . number_format($totalCredit, 2) . " - Debits: ₹" . number_format($totalDebit, 2) . ")\n";
            $updatedCount++;
        } else {
            echo "ℹ️ Promoter {$p['Name']} [$canonicalID]: Balance matches net total (₹" . number_format($netBalance, 2) . ").\n";
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
