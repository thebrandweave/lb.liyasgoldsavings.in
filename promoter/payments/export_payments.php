<?php
// Start output buffering at the very beginning
ob_start();

session_start();

// Check if promoter is logged in
if (!isset($_SESSION['promoter_id'])) {
    header("Location: ../login.php");
    exit();
}

// Database connection
require_once("../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

// Get promoter ID from session
$promoterId = $_SESSION['promoter_id'];

// First get the promoter's unique ID
$promoterQuery = "SELECT PromoterUniqueID FROM Promoters WHERE PromoterID = :promoterId";
$promoterStmt = $conn->prepare($promoterQuery);
$promoterStmt->bindParam(':promoterId', $promoterId);
$promoterStmt->execute();
$promoterUniqueId = $promoterStmt->fetchColumn();

// Get filter parameters
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$schemeFilter = isset($_GET['scheme']) ? $_GET['scheme'] : '';
$installmentFilter = isset($_GET['installment']) ? $_GET['installment'] : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query conditions
$conditions = ["c.PromoterID = :promoterUniqueId"];
$params = [':promoterUniqueId' => $promoterUniqueId];

if (!empty($statusFilter)) {
    $conditions[] = "p.Status = :status";
    $params[':status'] = $statusFilter;
}

if (!empty($schemeFilter)) {
    $conditions[] = "s.SchemeID = :schemeId";
    $params[':schemeId'] = $schemeFilter;
}

if (!empty($installmentFilter)) {
    $conditions[] = "i.InstallmentID = :installmentId";
    $params[':installmentId'] = $installmentFilter;
}

if (!empty($dateFrom)) {
    $conditions[] = "DATE(p.SubmittedAt) >= :dateFrom";
    $params[':dateFrom'] = $dateFrom;
}

if (!empty($dateTo)) {
    $conditions[] = "DATE(p.SubmittedAt) <= :dateTo";
    $params[':dateTo'] = $dateTo;
}

if (!empty($search)) {
    $conditions[] = "(c.Name LIKE :search OR c.CustomerUniqueID LIKE :search OR c.Contact LIKE :search)";
    $params[':search'] = "%$search%";
}

$whereClause = " WHERE " . implode(" AND ", $conditions);

// Build query for payments (without pagination)
$query = "
    SELECT 
        p.PaymentID,
        p.Amount,
        p.PaymentCodeValue,
        p.ScreenshotURL,
        p.Status,
        p.SubmittedAt,
        p.VerifiedAt,
        c.CustomerID,
        c.CustomerUniqueID,
        c.Name AS CustomerName,
        c.Contact AS CustomerContact,
        s.SchemeID,
        s.SchemeName,
        i.InstallmentID,
        i.InstallmentName,
        i.InstallmentNumber,
        i.DrawDate
    FROM 
        Payments p
    JOIN 
        Customers c ON p.CustomerID = c.CustomerID
    JOIN 
        Schemes s ON p.SchemeID = s.SchemeID
    JOIN 
        Installments i ON p.InstallmentID = i.InstallmentID" .
    $whereClause .
    " ORDER BY p.SubmittedAt DESC";

// Prepare and execute the query
$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Include PhpSpreadsheet library
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set document properties
$spreadsheet->getProperties()
    ->setCreator('Golden Dreams')
    ->setLastModifiedBy('Golden Dreams')
    ->setTitle('Payments Export')
    ->setSubject('Payments Data Export')
    ->setDescription('Payments data exported from Golden Dreams system');

// Set headers
$headers = [
    'Customer Name',
    'Customer ID',
    'Customer Contact',
    'Scheme Name',
    'Draw Date',
    'Installment Name',
    'Installment Number',
    'Amount',
    'Payment Code',
    'Status',
    'Submitted Date',
    'Submitted Time',
    'Verified Date',
    'Verified Time'
];

// Add headers to sheet
foreach ($headers as $colIndex => $header) {
    $column = chr(65 + $colIndex); // A, B, C, etc.
    $sheet->setCellValue($column . '1', $header);
}

// Add data to sheet
$row = 2;
foreach ($payments as $payment) {
    $sheet->setCellValue('A' . $row, $payment['CustomerName']);
    $sheet->setCellValue('B' . $row, $payment['CustomerUniqueID']);
    $sheet->setCellValue('C' . $row, $payment['CustomerContact']);
    $sheet->setCellValue('D' . $row, $payment['SchemeName']);
    $sheet->setCellValue('E' . $row, date('d M Y', strtotime($payment['DrawDate'])));
    $sheet->setCellValue('F' . $row, $payment['InstallmentName']);
    $sheet->setCellValue('G' . $row, $payment['InstallmentNumber']);
    $sheet->setCellValue('H' . $row, $payment['Amount']);
    $sheet->setCellValue('I' . $row, $payment['PaymentCodeValue'] > 0 ? $payment['PaymentCodeValue'] : '-');
    $sheet->setCellValue('J' . $row, $payment['Status']);
    $sheet->setCellValue('K' . $row, date('d M Y', strtotime($payment['SubmittedAt'])));
    $sheet->setCellValue('L' . $row, date('h:i A', strtotime($payment['SubmittedAt'])));
    $sheet->setCellValue('M' . $row, $payment['VerifiedAt'] ? date('d M Y', strtotime($payment['VerifiedAt'])) : '-');
    $sheet->setCellValue('N' . $row, $payment['VerifiedAt'] ? date('h:i A', strtotime($payment['VerifiedAt'])) : '-');
    $row++;
}

// Auto-size columns
foreach (range('A', 'N') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// Style the header row
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '0D6A50'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ],
];
$sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

// Style the data rows
$dataStyle = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];
$sheet->getStyle('A2:N' . ($row - 1))->applyFromArray($dataStyle);

// Clear any output buffer content
ob_end_clean();

// Set response headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="payments_export_' . date('Y-m-d_H-i-s') . '.xlsx"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: cache, must-revalidate');
header('Pragma: public');

// Create Excel file and output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?> 