<?php
session_start();

// Check if promoter is logged in
if (!isset($_SESSION['promoter_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Database connection
require_once("../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

// Get promoter's unique ID
try {
    $stmt = $conn->prepare("SELECT PromoterUniqueID FROM Promoters WHERE PromoterID = ?");
    $stmt->execute([$_SESSION['promoter_id']]);
    $promoter = $stmt->fetch(PDO::FETCH_ASSOC);
    $promoterUniqueID = $promoter['PromoterUniqueID'];
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error fetching promoter data']);
    exit();
}

// Check if customer ID is provided
if (!isset($_GET['customer_id']) || !is_numeric($_GET['customer_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid customer ID']);
    exit();
}

$customerId = $_GET['customer_id'];

// Verify that the customer belongs to this promoter
try {
    $stmt = $conn->prepare("
        SELECT CustomerID FROM Customers 
        WHERE CustomerID = ? AND PromoterID = ?
    ");
    $stmt->execute([$customerId, $promoterUniqueID]);
    
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit();
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error verifying customer access']);
    exit();
}

// Get customer payments
try {
    $stmt = $conn->prepare("
        SELECT 
            p.*,
            s.SchemeName,
            i.InstallmentName,
            i.InstallmentNumber,
            i.DrawDate
        FROM Payments p
        LEFT JOIN Schemes s ON p.SchemeID = s.SchemeID
        LEFT JOIN Installments i ON p.InstallmentID = i.InstallmentID
        WHERE p.CustomerID = ?
        ORDER BY p.SubmittedAt DESC
        LIMIT 10
    ");
    $stmt->execute([$customerId]);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error fetching payments']);
    exit();
}

// Generate HTML for payments table
$html = '';
if (empty($payments)) {
    $html = '<div style="text-align: center; padding: 20px; color: var(--text-secondary);">No payments found for this customer</div>';
} else {
    $html = '<table class="payments-table">
        <thead>
            <tr>
                <th>Scheme</th>
                <th>Installment</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Verified</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach ($payments as $payment) {
        $html .= '<tr>
            <td>
                <div>' . htmlspecialchars($payment['SchemeName'] ?? 'N/A') . '</div>
                <div style="font-size: 12px; color: var(--text-secondary);">Draw: ' . date('d M Y', strtotime($payment['DrawDate'])) . '</div>
            </td>
            <td>
                <div>' . htmlspecialchars($payment['InstallmentName'] ?? 'N/A') . '</div>
                <div style="font-size: 12px; color: var(--text-secondary);">#' . $payment['InstallmentNumber'] . '</div>
            </td>
            <td>₹' . number_format($payment['Amount'], 2) . '</td>
            <td>
                <span class="payment-status ' . strtolower($payment['Status']) . '">
                    ' . htmlspecialchars($payment['Status']) . '
                </span>
            </td>
            <td>' . date('d M Y, h:i A', strtotime($payment['SubmittedAt'])) . '</td>
            <td>' . ($payment['VerifiedAt'] ? date('d M Y, h:i A', strtotime($payment['VerifiedAt'])) : '-') . '</td>
        </tr>';
    }
    
    $html .= '</tbody></table>';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'html' => $html
]);
?> 