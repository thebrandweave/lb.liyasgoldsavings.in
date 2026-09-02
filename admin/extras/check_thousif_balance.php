<?php
// admin/extras/check_thousif_balance.php
session_start();
require_once(__DIR__ . "/../../config/config.php");

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die("Database connection failed.");
}

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    echo "<!DOCTYPE html><html><head><title>Thousif (GD012157) Balance Report</title><style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #121212; color: #e0e0e0; padding: 25px; line-height: 1.6; }
        .card { background: #1e1e1e; border-radius: 8px; padding: 20px; margin-bottom: 20px; border: 1px solid #333; }
        h1, h2, h3 { color: #fff; margin-top: 0; }
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-box { background: #252526; padding: 15px; border-radius: 6px; border-left: 4px solid #007acc; }
        .stat-box.before { border-left-color: #ff9800; }
        .stat-box.today { border-left-color: #2196f3; }
        .stat-box.after { border-left-color: #4caf50; }
        .stat-title { font-size: 12px; text-transform: uppercase; color: #aaa; margin-bottom: 5px; }
        .stat-value { font-size: 24px; font-weight: bold; color: #fff; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: #1e1e1e; font-size: 13px; }
        th, td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #333; }
        th { background: #2d2d2d; color: #9cdcfe; font-weight: 600; }
        tr:hover { background: #2a2d2e; }
        .badge-credit { background: #1b5e20; color: #a5d6a7; padding: 3px 8px; border-radius: 4px; font-size: 11px; }
        .badge-debit { background: #b71c1c; color: #ef9a9a; padding: 3px 8px; border-radius: 4px; font-size: 11px; }
        .badge-today { background: #0d47a1; color: #90caf9; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: bold; margin-left: 5px; }
    </style></head><body>";
}

$promoterUniqueID = 'GD012157';

echo "<div class='card'>";
echo "<h1>📊 Thousif (GD012157) Wallet Balance Analysis</h1>";

// 1. Fetch Promoter Details
$pStmt = $conn->prepare("SELECT PromoterID, PromoterUniqueID, Name, Commission, Phone FROM Promoters WHERE TRIM(PromoterUniqueID) = ? OR Name LIKE '%THOUSIF%'");
$pStmt->execute([$promoterUniqueID]);
$promoter = $pStmt->fetch(PDO::FETCH_ASSOC);

if (!$promoter) {
    echo "<p style='color: #f44336;'>❌ Promoter GD012157 / THOUSIF not found in Promoters table.</p></div>";
    exit;
}

$actualPromoterID = $promoter['PromoterUniqueID'];

// 2. Fetch Wallet Balance
$wStmt = $conn->prepare("SELECT BalanceID, BalanceAmount, LastUpdated FROM PromoterWallet WHERE TRIM(PromoterUniqueID) = ?");
$wStmt->execute([$actualPromoterID]);
$wallet = $wStmt->fetch(PDO::FETCH_ASSOC);
$currentBalance = $wallet ? floatval($wallet['BalanceAmount']) : 0.00;

// 3. Fetch All Wallet Logs
$lStmt = $conn->prepare("SELECT LogID, PromoterUniqueID, Amount, Message, TransactionType, CreatedAt FROM WalletLogs WHERE TRIM(PromoterUniqueID) = ? ORDER BY CreatedAt DESC, LogID DESC");
$lStmt->execute([$actualPromoterID]);
$logs = $lStmt->fetchAll(PDO::FETCH_ASSOC);

$todayCredits = 0.00;
$todayDebits = 0.00;
$todayCount = 0;

$priorCredits = 0.00;
$priorDebits = 0.00;

$todayDate = date('Y-m-d');

foreach ($logs as $log) {
    $logDate = date('Y-m-d', strtotime($log['CreatedAt']));
    $amount = floatval($log['Amount']);
    $isCredit = (strtolower($log['TransactionType']) === 'credit');

    if ($logDate === $todayDate) {
        $todayCount++;
        if ($isCredit) {
            $todayCredits += $amount;
        } else {
            $todayDebits += $amount;
        }
    } else {
        if ($isCredit) {
            $priorCredits += $amount;
        } else {
            $priorDebits += $amount;
        }
    }
}

$todayNet = $todayCredits - $todayDebits;
$earlyBalance = $currentBalance - $todayNet;

echo "<p><strong>Promoter Name:</strong> {$promoter['Name']} | <strong>Promoter ID:</strong> {$actualPromoterID} | <strong>Phone:</strong> {$promoter['Phone']}</p>";

echo "<div class='stat-grid'>";

echo "<div class='stat-box before'>
        <div class='stat-title'>1. Early Balance (Before Today's Script)</div>
        <div class='stat-value'>₹" . number_format($earlyBalance, 2) . "</div>
        <small style='color: #bbb;'>Balance prior to Sep 02, 2026 changes</small>
      </div>";

echo "<div class='stat-box today'>
        <div class='stat-title'>2. Today's Changes (Added Sep 02)</div>
        <div class='stat-value'>" . ($todayNet >= 0 ? "+₹" : "-₹") . number_format(abs($todayNet), 2) . "</div>
        <small style='color: #bbb;'>$todayCount log entry(s) generated today</small>
      </div>";

echo "<div class='stat-box after'>
        <div class='stat-title'>3. Current Total Balance (After Today)</div>
        <div class='stat-value'>₹" . number_format($currentBalance, 2) . "</div>
        <small style='color: #bbb;'>Current live balance in PromoterWallet</small>
      </div>";

echo "</div>"; // end stat-grid
echo "</div>"; // end card

// Transaction Table
echo "<div class='card'>";
echo "<h2>📜 Complete Wallet Transaction History (" . count($logs) . " records)</h2>";

if (count($logs) === 0) {
    echo "<p>No wallet transaction logs found for this promoter.</p>";
} else {
    echo "<table>";
    echo "<thead><tr>
            <th>Log ID</th>
            <th>Date & Time</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Description / Message</th>
            <th>Tag</th>
          </tr></thead><tbody>";

    foreach ($logs as $log) {
        $logDate = date('Y-m-d', strtotime($log['CreatedAt']));
        $isToday = ($logDate === $todayDate);
        $isCredit = (strtolower($log['TransactionType']) === 'credit');

        echo "<tr>";
        echo "<td>#" . htmlspecialchars($log['LogID']) . "</td>";
        echo "<td>" . htmlspecialchars($log['CreatedAt']) . "</td>";
        echo "<td><span class='" . ($isCredit ? "badge-credit" : "badge-debit") . "'>" . strtoupper(htmlspecialchars($log['TransactionType'])) . "</span></td>";
        echo "<td style='font-weight: bold; color: " . ($isCredit ? "#81c784" : "#e57373") . ";'>" . ($isCredit ? "+" : "-") . "₹" . number_format(floatval($log['Amount']), 2) . "</td>";
        echo "<td>" . htmlspecialchars($log['Message']) . "</td>";
        echo "<td>" . ($isToday ? "<span class='badge-today'>ADDED TODAY</span>" : "<span style='color:#777; font-size:11px;'>PREVIOUS</span>") . "</td>";
        echo "</tr>";
    }

    echo "tbody></table>";
}

echo "</div>";

if (!$isCli) {
    echo "</body></html>";
}
