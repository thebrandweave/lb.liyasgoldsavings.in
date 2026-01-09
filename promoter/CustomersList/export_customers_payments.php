<?php
session_start();
require_once("../../config/config.php");
require_once("../../vendor/autoload.php");
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$database = new Database();
$conn = $database->getConnection();

// Only allow logged-in promoters
if (!isset($_SESSION['promoter_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get promoter unique ID
$stmt = $conn->prepare("SELECT PromoterUniqueID FROM Promoters WHERE PromoterID = ?");
$stmt->execute([$_SESSION['promoter_id']]);
$promoter = $stmt->fetch(PDO::FETCH_ASSOC);
$promoterUniqueID = $promoter['PromoterUniqueID'];

// Get filters from GET
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
$filterScheme = isset($_GET['scheme']) ? $_GET['scheme'] : '';
$filterInstallment = isset($_GET['installment']) ? $_GET['installment'] : '';
$filterPaymentStatus = isset($_GET['payment_status']) ? $_GET['payment_status'] : '';

// Fetch all customers under this promoter
$stmt = $conn->prepare("
    SELECT c.*
    FROM Customers c
    WHERE c.PromoterID = ?
    ORDER BY c.CreatedAt DESC
");
$stmt->execute([$promoterUniqueID]);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For each customer, fetch their installments and payments
foreach ($customers as &$customer) {
    // Check if customer has any subscriptions
    $stmt = $conn->prepare("SELECT COUNT(*) as sub_count FROM Subscriptions WHERE CustomerID = ?");
    $stmt->execute([$customer['CustomerID']]);
    $subCount = $stmt->fetch(PDO::FETCH_ASSOC)['sub_count'];
    
    if ($subCount > 0) {
        // Customer has subscriptions, fetch their installments
        $stmt = $conn->prepare("
            SELECT 
                i.InstallmentID,
                i.InstallmentName,
                i.InstallmentNumber,
                s.SchemeID,
                s.SchemeName,
                p.PaymentID,
                p.Status as PaymentStatus,
                p.Amount,
                p.SubmittedAt,
                p.VerifiedAt
            FROM Installments i
            JOIN Schemes s ON i.SchemeID = s.SchemeID
            LEFT JOIN Payments p ON i.InstallmentID = p.InstallmentID AND p.CustomerID = ?
            WHERE s.Status = 'Active'
            AND EXISTS (
                SELECT 1 FROM Subscriptions sub
                WHERE sub.CustomerID = ? 
                AND sub.SchemeID = s.SchemeID
            )
            ORDER BY s.SchemeName, i.InstallmentNumber
        ");
        $stmt->execute([$customer['CustomerID'], $customer['CustomerID']]);
        $customer['installments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Customer has no subscriptions, set empty installments array
        $customer['installments'] = [];
    }
}
unset($customer);

// Apply filters (aligned with listing page)
$filteredCustomers = [];
foreach ($customers as $customer) {
    $matchesSearch = empty($searchQuery) ||
        stripos($customer['Name'], $searchQuery) !== false ||
        stripos($customer['CustomerUniqueID'], $searchQuery) !== false ||
        stripos($customer['Contact'], $searchQuery) !== false ||
        stripos($customer['Email'], $searchQuery) !== false;
    $matchesStatus = empty($filterStatus) || $customer['Status'] === $filterStatus;
    // Scheme filter: support 'unsubscribed' (no subscriptions), else require scheme match
    $matchesScheme = empty($filterScheme);
    if (!empty($filterScheme)) {
        if ($filterScheme === 'unsubscribed') {
            // Count subscriptions and payments for this customer
            $stmt = $conn->prepare("SELECT 
                (SELECT COUNT(*) FROM Subscriptions WHERE CustomerID = :cid) AS sub_count,
                (SELECT COUNT(*) FROM Payments WHERE CustomerID = :cid) AS pay_count
            ");
            $stmt->execute([':cid' => $customer['CustomerID']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['sub_count' => 0, 'pay_count' => 0];
            $matchesScheme = ((int)$row['sub_count'] === 0) && ((int)$row['pay_count'] === 0);
        } else {
            foreach ($customer['installments'] as $installment) {
                if (isset($installment['SchemeID']) && $installment['SchemeID'] == $filterScheme) {
                    $matchesScheme = true;
                    break;
                }
            }
        }
    }
    $matchesInstallment = empty($filterInstallment);
    if (!empty($filterInstallment)) {
        foreach ($customer['installments'] as $installment) {
            if (isset($installment['InstallmentID']) && $installment['InstallmentID'] == $filterInstallment) {
                $matchesInstallment = true;
                break;
            }
        }
    }
    $matchesInstallmentStatus = true;
    if (!empty($filterInstallment) && !empty($filterPaymentStatus)) {
        $matchesInstallmentStatus = false;
        foreach ($customer['installments'] as $installment) {
            if (
                isset($installment['InstallmentID']) && $installment['InstallmentID'] == $filterInstallment &&
                (
                    ($filterPaymentStatus === 'unpaid' && empty($installment['PaymentID'])) ||
                    (!empty($installment['PaymentID']) && strtolower($installment['PaymentStatus']) === $filterPaymentStatus)
                )
            ) {
                $matchesInstallmentStatus = true;
                break;
            }
        }
    }
    $matchesPaymentStatus = empty($filterPaymentStatus) || !empty($filterInstallment);
    if (!empty($filterPaymentStatus) && empty($filterInstallment)) {
        $matchesPaymentStatus = false;
        foreach ($customer['installments'] as $installment) {
            if (
                (!empty($installment['PaymentID']) && strtolower($installment['PaymentStatus']) === $filterPaymentStatus) ||
                ($filterPaymentStatus === 'unpaid' && empty($installment['PaymentID']))
            ) {
                $matchesPaymentStatus = true;
                break;
            }
        }
    }
    if (
        $matchesSearch &&
        $matchesStatus &&
        $matchesScheme &&
        $matchesInstallment &&
        $matchesPaymentStatus &&
        $matchesInstallmentStatus
    ) {
        $filteredCustomers[] = $customer;
    }
}

// Prepare spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Title
$sheet->setCellValue('A1', 'Golden Dream - Customer Payments Export');
$sheet->mergeCells('A1:L1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Export date
$sheet->setCellValue('A2', 'Exported on: ' . date('Y-m-d H:i:s'));
$sheet->mergeCells('A2:L2');
$sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(11);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Blank row
$sheet->setCellValue('A3', '');

// Headers
$headers = [
    'Customer Name', 'Customer ID', 'Contact', 'Email', 'Status',
    'Scheme', 'Installment', 'Installment Number', 'Payment Status', 'Amount', 'Submitted At', 'Verified At'
];
$sheet->fromArray($headers, null, 'A4');
$sheet->getStyle('A4:L4')->getFont()->setBold(true);
$sheet->getStyle('A4:L4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('3498db');
$sheet->getStyle('A4:L4')->getFont()->getColor()->setRGB('FFFFFF');
$sheet->getStyle('A4:L4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A4:L4')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Data rows
$row = 5;
foreach ($filteredCustomers as $customer) {
    if (empty($customer['installments'])) {
        // Handle unsubscribed customers - export them with empty installment data
        if ($filterScheme === 'unsubscribed') {
            $sheet->fromArray([
                $customer['Name'],
                $customer['CustomerUniqueID'],
                $customer['Contact'],
                $customer['Email'],
                $customer['Status'],
                'Unsubscribed',
                'N/A',
                'N/A',
                'N/A',
                '',
                '',
                '',
            ], null, 'A' . $row);
            $row++;
        }
    } else {
        // Handle subscribed customers with installments
        foreach ($customer['installments'] as $installment) {
            $exportThis = true;
            if (!empty($filterInstallment) && $installment['InstallmentID'] != $filterInstallment) $exportThis = false;
            if (!empty($filterScheme) && $filterScheme !== 'unsubscribed' && $installment['SchemeID'] != $filterScheme) $exportThis = false;
            if (!empty($filterPaymentStatus)) {
                if ($filterPaymentStatus === 'unpaid' && !empty($installment['PaymentID'])) $exportThis = false;
                if ($filterPaymentStatus !== 'unpaid' && (empty($installment['PaymentID']) || strtolower($installment['PaymentStatus']) !== $filterPaymentStatus)) $exportThis = false;
            }
            if (!$exportThis) continue;
            $sheet->fromArray([
                $customer['Name'],
                $customer['CustomerUniqueID'],
                $customer['Contact'],
                $customer['Email'],
                $customer['Status'],
                $installment['SchemeName'],
                $installment['InstallmentName'],
                $installment['InstallmentNumber'],
                $installment['PaymentID'] ? $installment['PaymentStatus'] : 'Unpaid',
                $installment['PaymentID'] ? $installment['Amount'] : '',
                $installment['PaymentID'] ? $installment['SubmittedAt'] : '',
                $installment['PaymentID'] ? $installment['VerifiedAt'] : '',
            ], null, 'A' . $row);
            $row++;
        }
    }
}

// Auto-size columns
foreach (range('A', 'L') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Add borders to data rows
if ($row > 5) {
    $sheet->getStyle('A5:L' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
}

// Clean output buffer
if (ob_get_length()) ob_clean();

// Output as XLSX
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="customers_payments_export.xlsx"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit; 