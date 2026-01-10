<?php
session_start();
require_once("../../config/config.php");
require_once("../../config/SMSAPI.php");
$database = new Database();
$conn = $database->getConnection();
$smsAPI = new SMSAPI($database);

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$installmentId = $data['installment_id'] ?? null;
$schemeId = $data['scheme_id'] ?? null;

if (!$installmentId || !$schemeId) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // Get installment details
    $stmt = $conn->prepare("
        SELECT i.*, s.SchemeName 
        FROM Installments i 
        JOIN Schemes s ON i.SchemeID = s.SchemeID 
        WHERE i.InstallmentID = ?
    ");
    $stmt->execute([$installmentId]);
    $installment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$installment) {
        throw new Exception('Installment not found');
    }

    // Get customers with pending payments - All active customers, not just subscribed ones
    $stmt = $conn->prepare("
        SELECT DISTINCT c.CustomerID, c.CustomerUniqueID, c.Name, c.Contact
        FROM Customers c
        LEFT JOIN Payments p ON p.CustomerID = c.CustomerID AND p.InstallmentID = ?
        WHERE (p.PaymentID IS NULL OR p.Status != 'Verified')
        AND c.Status = 'Active'
    ");
    $stmt->execute([$installmentId]);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $successCount = 0;
    $failedCount = 0;
    $errorLogs = [];

    foreach ($customers as $customer) {
        try {
            // Format phone number (remove any non-numeric characters)
            $phone = preg_replace('/[^0-9]/', '', $customer['Contact']);

            // Add country code if not present
            if (strlen($phone) === 10) {
                $phone = '91' . $phone;
            }

            // Prepare message
            $message = "Dear " . $customer['Name'] . ",\n\n";
            $message .= "This is a reminder that your payment for " . $installment['SchemeName'] . " - " . $installment['InstallmentName'] . " is pending.\n\n";
            $message .= "Amount: ₹" . number_format($installment['Amount'], 2) . "\n";
            $message .= "Due Date: " . date('d M Y', strtotime($installment['DrawDate'])) . "\n\n";
            $message .= "Please submit your payment at the earliest to avoid any inconvenience.\n\n";
            $message .= "If already paid, please ignore this message.\n\n";
            $message .= "Thank you,\nGolden Dreams Team";

            // Send SMS via Airtel (plain reminder message)
            $result = $smsAPI->sendSMS($phone, $message);
            if ($result) {
                $successCount++;
            } else {
                $failedCount++;
                $errorLogs[] = "Failed to send to " . $phone;
            }
        } catch (Exception $e) {
            $failedCount++;
            $errorLogs[] = "Error sending to " . $phone . ": " . $e->getMessage();
            error_log("Exception while sending to " . $phone . ": " . $e->getMessage());
        }
    }

    echo json_encode([
        'success' => true,
        'message' => "Reminders sent: $successCount successful, $failedCount failed",
        'errors' => $errorLogs
    ]);
} catch (Exception $e) {
    error_log("Main exception: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
