<?php
if (isset($_POST['export_excel'])) {
    if (ob_get_level()) ob_end_clean(); // Clean (and end) the output buffer if any
}
$menuPath = '../'; // This will make $menuPath.'../config/JWT.php' resolve to '../../config/JWT.php'
// Admin authentication
require_once __DIR__ . '/../middleware/auth.php';
session_start();
verifyAuth();

// Include DB config (adjust path as needed)
require_once '../../config/database.php';

// PhpSpreadsheet imports
require_once '../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Get PDO connection
$db = new Database();
$conn = $db->getConnection();

// Fetch all promoters for the dropdown
$promoters = [];
$sql = "SELECT PromoterID, PromoterUniqueID, Name FROM Promoters ORDER BY Name ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$promoters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle promoter selection
$selectedPromoterID = isset($_POST['promoter_id']) ? $_POST['promoter_id'] : '';
$walletLogs = [];
$promoterBalance = null;
$selectedPromoterName = '';
$selectedPromoterUniqueID = '';
if ($selectedPromoterID) {
    // Get promoter details
    $stmt = $conn->prepare("SELECT Name, PromoterUniqueID FROM Promoters WHERE PromoterID = ?");
    $stmt->execute([$selectedPromoterID]);
    $promoter = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($promoter) {
        $selectedPromoterName = $promoter['Name'];
        $selectedPromoterUniqueID = $promoter['PromoterUniqueID'];

        // Get wallet logs
        $stmt = $conn->prepare("SELECT Amount, Message, TransactionType, CreatedAt FROM WalletLogs WHERE PromoterUniqueID = ? ORDER BY CreatedAt DESC");
        $stmt->execute([$selectedPromoterUniqueID]);
        $walletLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get current balance
        $stmt = $conn->prepare("SELECT BalanceAmount FROM PromoterWallet WHERE PromoterUniqueID = ?");
        $stmt->execute([$selectedPromoterUniqueID]);
        $promoterBalance = $stmt->fetchColumn();
    }
}

// Handle export to Excel (CSV for simplicity)
if (isset($_POST['export_excel']) && $selectedPromoterID && $selectedPromoterUniqueID) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    // Set headers
    $sheet->setCellValue('A1', 'S.No');
    $sheet->setCellValue('B1', 'Date/Time');
    $sheet->setCellValue('C1', 'Amount');
    $sheet->setCellValue('D1', 'Type');
    $sheet->setCellValue('E1', 'Message');

    // Style header row
    $headerStyle = [
        'font' => [
            'bold' => true,
            'size' => 12,
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [ 'rgb' => 'D9E1F2' ]
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ];
    $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);
    $sheet->getRowDimension(1)->setRowHeight(22);

    $rowNum = 2;
    $serial = 1;
    foreach ($walletLogs as $log) {
        $date = date('Y-m-d H:i:s', strtotime($log['CreatedAt']));
        $sheet->setCellValue('A' . $rowNum, $serial++);
        $sheet->setCellValue('B' . $rowNum, $date);
        $sheet->setCellValue('C' . $rowNum, number_format($log['Amount'], 2, '.', ''));
        $sheet->setCellValue('D' . $rowNum, $log['TransactionType']);
        $sheet->setCellValue('E' . $rowNum, $log['Message']);
        $rowNum++;
    }

    // Style all data cells with borders
    $lastRow = $rowNum - 1;
    $sheet->getStyle('A1:E' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Auto-size columns
    foreach (range('A', 'E') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Freeze header row
    $sheet->freezePane('A2');

    // Output to browser
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="wallet_history_' . $selectedPromoterUniqueID . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Promoter Wallet History</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        .container { max-width: 900px; margin: auto; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
        .balance { margin-top: 10px; font-weight: bold; }
        .selector { margin-bottom: 20px; }
        .export-btn { margin-top: 10px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Promoter Wallet History</h2>
    <form method="POST" class="selector">
        <label for="promoter_id">Select Promoter:</label>
        <select name="promoter_id" id="promoter_id" required>
            <option value="">-- Select --</option>
            <?php foreach ($promoters as $promoter): ?>
                <option value="<?= htmlspecialchars($promoter['PromoterID']) ?>" <?= $selectedPromoterID == $promoter['PromoterID'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($promoter['Name']) ?> (<?= htmlspecialchars($promoter['PromoterUniqueID']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">View History</button>
    </form>

    <?php if ($selectedPromoterID && $selectedPromoterUniqueID): ?>
        <div class="balance">
            Promoter: <strong><?= htmlspecialchars($selectedPromoterName) ?> (<?= htmlspecialchars($selectedPromoterUniqueID) ?>)</strong><br>
            Current Balance: <span style="color:green;">₹<?= number_format($promoterBalance, 2) ?></span>
        </div>
        <form method="POST" class="export-btn">
            <input type="hidden" name="promoter_id" value="<?= htmlspecialchars($selectedPromoterID) ?>">
            <button type="submit" name="export_excel">Export to Excel</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Date/Time</th>
                    <th>Amount (₹)</th>
                    <th>Type</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($walletLogs) > 0): ?>
                <?php foreach ($walletLogs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars($log['CreatedAt']) ?></td>
                        <td><?= number_format($log['Amount'], 2) ?></td>
                        <td><?= htmlspecialchars($log['TransactionType']) ?></td>
                        <td><?= htmlspecialchars($log['Message']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No wallet history found for this promoter.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html> 