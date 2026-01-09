<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Disable output buffering and ensure clean output
if (ob_get_level()) {
    ob_end_clean();
}

// Prevent any output before headers
error_reporting(0);
ini_set('display_errors', 0);

require_once '../../config/config.php';
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Get search parameter
    $searchTerm = $_GET['search'] ?? '';
    
    // Build search condition
    $searchCondition = '';
    $params = [];
    if (!empty($searchTerm)) {
        $searchCondition = 'WHERE p.Name LIKE ? OR p.Email LIKE ? OR p.Contact LIKE ? OR p.PromoterUniqueID LIKE ?';
        $searchParam = '%' . $searchTerm . '%';
        $params = [$searchParam, $searchParam, $searchParam, $searchParam];
    }
    
    // Get all promoters with customer count and child promoter count
    $query = 'SELECT 
                p.PromoterID,
                p.PromoterUniqueID,
                p.Name,
                p.Contact,
                p.Email,
                p.Address,
                p.TeamName,
                p.Status,
                p.CreatedAt,
                COUNT(CASE WHEN c.Status = "Active" THEN c.CustomerID END) as CustomerCount,
                COUNT(CASE WHEN cp.Status = "Active" THEN cp.PromoterID END) as ChildPromoterCount
              FROM Promoters p
              LEFT JOIN Customers c ON p.PromoterUniqueID = c.PromoterID
              LEFT JOIN Promoters cp ON p.PromoterUniqueID = cp.ParentPromoterID
              ' . $searchCondition . '
              GROUP BY p.PromoterID, p.PromoterUniqueID, p.Name, p.Contact, p.Email, p.Address, p.TeamName, p.Status, p.CreatedAt
              ORDER BY p.CreatedAt DESC';
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $promoters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check if we have data
    if (empty($promoters)) {
        throw new Exception('No promoters found to export');
    }
    
    // Create new Spreadsheet object
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set document properties
    $spreadsheet->getProperties()
        ->setCreator('GoldenDream Admin')
        ->setLastModifiedBy('GoldenDream Admin')
        ->setTitle('Promoter List Export')
        ->setSubject('Promoter List with Customer Counts')
        ->setDescription('Exported promoter list with customer counts')
        ->setKeywords('promoter list export')
        ->setCategory('Report');
    
    // Set headers
    $headers = [
        'A1' => 'S.No',
        'B1' => 'Promoter ID',
        'C1' => 'Unique ID',
        'D1' => 'Name',
        'E1' => 'Email',
        'F1' => 'Contact',
        'G1' => 'Team Name',
        'H1' => 'Address',
        'I1' => 'Status',
        'J1' => 'Customer Count',
        'K1' => 'Child Promoter Count',
        'L1' => 'Joined Date'
    ];
    
    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }
    
    // Style header row
    $headerStyle = [
        'font' => [
            'bold' => true,
            'size' => 12,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '7B61FF']
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
    
    $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);
    $sheet->getRowDimension(1)->setRowHeight(25);
    
    // Set column widths
    $sheet->getColumnDimension('A')->setWidth(8);   // S.No
    $sheet->getColumnDimension('B')->setWidth(12);  // Promoter ID
    $sheet->getColumnDimension('C')->setWidth(20);  // Unique ID
    $sheet->getColumnDimension('D')->setWidth(25);  // Name
    $sheet->getColumnDimension('E')->setWidth(30);  // Email
    $sheet->getColumnDimension('F')->setWidth(15);  // Contact
    $sheet->getColumnDimension('G')->setWidth(20);  // Team Name
    $sheet->getColumnDimension('H')->setWidth(30);  // Address
    $sheet->getColumnDimension('I')->setWidth(12);  // Status
    $sheet->getColumnDimension('J')->setWidth(15);  // Customer Count
    $sheet->getColumnDimension('K')->setWidth(18);  // Child Promoter Count
    $sheet->getColumnDimension('L')->setWidth(15);  // Joined Date
    
    // Add data rows
    $rowNum = 2;
    $serial = 1;
    
    foreach ($promoters as $promoter) {
        $sheet->setCellValue('A' . $rowNum, $serial++);
        $sheet->setCellValue('B' . $rowNum, $promoter['PromoterID']);
        $sheet->setCellValue('C' . $rowNum, $promoter['PromoterUniqueID']);
        $sheet->setCellValue('D' . $rowNum, $promoter['Name']);
        $sheet->setCellValue('E' . $rowNum, $promoter['Email']);
        $sheet->setCellValue('F' . $rowNum, $promoter['Contact']);
        $sheet->setCellValue('G' . $rowNum, $promoter['TeamName'] ?: 'N/A');
        $sheet->setCellValue('H' . $rowNum, $promoter['Address'] ?: 'N/A');
        $sheet->setCellValue('I' . $rowNum, $promoter['Status']);
        $sheet->setCellValue('J' . $rowNum, $promoter['CustomerCount']);
        $sheet->setCellValue('K' . $rowNum, $promoter['ChildPromoterCount']);
        $sheet->setCellValue('L' . $rowNum, date('d M Y', strtotime($promoter['CreatedAt'])));
        
        // Style data rows
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFCCCCCC'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        
        $sheet->getStyle('A' . $rowNum . ':L' . $rowNum)->applyFromArray($dataStyle);
        $sheet->getRowDimension($rowNum)->setRowHeight(20);
        
        // Center align specific columns
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('K' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('L' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Color code status
        if (strtolower($promoter['Status']) === 'active') {
            $sheet->getStyle('I' . $rowNum)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E6F9ED');
            $sheet->getStyle('I' . $rowNum)->getFont()->getColor()->setRGB('388E3C');
        } else {
            $sheet->getStyle('I' . $rowNum)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF0F0');
            $sheet->getStyle('I' . $rowNum)->getFont()->getColor()->setRGB('D32F2F');
        }
        
        // Color code customer count
        $sheet->getStyle('J' . $rowNum)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F4FF');
        $sheet->getStyle('J' . $rowNum)->getFont()->getColor()->setRGB('7B61FF');
        
        // Color code child promoter count
        $sheet->getStyle('K' . $rowNum)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF3E0');
        $sheet->getStyle('K' . $rowNum)->getFont()->getColor()->setRGB('F57C00');
        
        $rowNum++;
    }
    
    // Set the filename
    $filename = 'promoter_list_export_' . date('Y-m-d_H-i-s');
    if (!empty($searchTerm)) {
        $filename .= '_filtered';
    }
    $filename .= '.xlsx';
    
    // Ensure no output has been sent
    if (headers_sent($file, $line)) {
        throw new Exception("Headers already sent in $file on line $line. Cannot download file.");
    }
    
    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');
    
    // Create Excel file
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    
    // Ensure script ends here
    exit();
    
} catch (Exception $e) {
    // If there's an error, redirect back with error message
    if (!headers_sent()) {
        header('Location: index.php?error=' . urlencode('Export failed: ' . $e->getMessage()));
    } else {
        echo '<script>alert("Export failed: ' . addslashes($e->getMessage()) . '"); window.location.href = "index.php";</script>';
    }
    exit();
}
?> 