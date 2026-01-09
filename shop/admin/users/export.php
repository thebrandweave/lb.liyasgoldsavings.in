<?php
session_start();
if (!isset($_SESSION['shop_admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/config.php';
require_once '../../config/UserManager.php';
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Get filter parameters
$userType = $_GET['type'] ?? 'all';

// Set headers based on user type
if ($userType === 'main') {
    $headers = ['ID', 'Name', 'Email', 'Contact', 'Unique ID', 'Source'];
    $sheet->fromArray($headers, null, 'A1');
} elseif ($userType === 'shop') {
    $headers = ['ID', 'Name', 'Email', 'Contact', 'Unique ID', 'Source'];
    $sheet->fromArray($headers, null, 'A1');
} else {
    $headers = ['ID', 'Name', 'Email', 'Contact', 'Unique ID', 'Source'];
    $sheet->fromArray($headers, null, 'A1');
}

// Style header row
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
        'size' => 12,
        'name' => 'Montserrat',
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '166534'], // dark green accent
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        'wrapText' => true,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['rgb' => 'E5E7EB'],
        ],
    ],
];
$sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
$sheet->getRowDimension(1)->setRowHeight(28);

// Fetch users based on filter
$userManager = new UserManager();

if ($userType === 'main') {
    $users = $userManager->getMainUsers();
} elseif ($userType === 'shop') {
    $users = $userManager->getShopUsers();
} else {
    $users = $userManager->getAllUsers();
}

// Write user data
$row = 2;
foreach ($users as $user) {
    $source = isset($user['Source']) ? $user['Source'] : ($userType === 'main' ? Database::$main_db : Database::$shop_db);
    $sourceText = $source === Database::$main_db ? 'Main System' : 'Shop';
    
    $sheet->fromArray([
        $user['CustomerID'],
        $user['Name'],
        $user['Email'],
        $user['Contact'],
        $user['CustomerUniqueID'],
        $sourceText,
    ], null, 'A' . $row);
    $sheet->getRowDimension($row)->setRowHeight(22);
    $row++;
}

// Auto-size columns and add extra width for padding
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
    // Add extra width for padding
    $currentWidth = $sheet->getColumnDimension($col)->getWidth();
    $sheet->getColumnDimension($col)->setWidth($currentWidth + 2);
}

// Center-align all columns
$sheet->getStyle('A2:F' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A2:F' . ($row - 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

// Set borders for all data
$dataStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['rgb' => 'E5E7EB'],
        ],
    ],
    'font' => [
        'name' => 'Montserrat',
        'size' => 10,
    ],
];
$sheet->getStyle('A2:F' . ($row - 1))->applyFromArray($dataStyle);

// Set filename based on user type
$typeText = $userType === 'main' ? 'main_users' : ($userType === 'shop' ? 'shop_users' : 'all_users');
$filename = $typeText . '_export_' . date('Y-m-d_H-i-s') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit(); 