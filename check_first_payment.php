<?php
require_once("config/config.php");

$database = new Database();
$conn = $database->getConnection();

header('Content-Type: application/json');

try {
    // Get the input data
    $input = json_decode(file_get_contents('php://input'), true);
    $paymentId = $input['payment_id'] ?? null;

    if (!$paymentId) {
        echo json_encode(['status' => 'error', 'message' => 'Payment ID is required.']);
        exit;
    }

    // Get payment details
    $stmt = $conn->prepare("
        SELECT CustomerID, SchemeID, Status 
        FROM Payments 
        WHERE PaymentID = ?
    ");
    $stmt->execute([$paymentId]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        echo json_encode(['status' => 'error', 'message' => 'Payment not found.']);
        exit;
    }

    // Check if OTHER verified payments exist for this customer & scheme
    $stmt = $conn->prepare("
        SELECT COUNT(*) as prior_count 
        FROM Payments 
        WHERE CustomerID = ? AND SchemeID = ? AND Status = 'Verified' AND PaymentID != ?
    ");
    $stmt->execute([$payment['CustomerID'], $payment['SchemeID'], $paymentId]);
    $priorCount = (int)$stmt->fetch(PDO::FETCH_ASSOC)['prior_count'];

    if ($priorCount === 0) {
        echo json_encode([
            'status' => 'success',
            'is_first_payment' => true,
            'message' => 'This is the first verified payment for the scheme.'
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'is_first_payment' => false,
            'prior_verified_payments' => $priorCount,
            'message' => "This is not the first verified payment. Found $priorCount prior verified payment(s)."
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error checking payment: ' . $e->getMessage()]);
}
?>