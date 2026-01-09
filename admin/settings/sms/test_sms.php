<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if the logged-in admin has SuperAdmin privileges
if ($_SESSION['admin_role'] !== 'SuperAdmin') {
    echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$phone = $input['phone'] ?? '';
$message = $input['message'] ?? '';

if (empty($phone) || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Phone number and message are required']);
    exit();
}

require_once("../../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

try {
    // Use the SMSAPI class for testing
    require_once("../../../config/SMSAPI.php");
    $smsAPI = new SMSAPI($database);
    
    if (!$smsAPI->isConfigured()) {
        echo json_encode(['success' => false, 'message' => 'SMS API is not configured or inactive']);
        exit();
    }

    // Format phone number (add country code if not present)
    if (substr($phone, 0, 2) !== '91') {
        $phone = '91' . $phone;
    }

    // Send test SMS
    $result = $smsAPI->sendSMS($phone, $message);
    
    if ($result) {
        // Log the test activity
        $action = "Test SMS sent to {$phone}";
        $stmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Admin', ?, ?)");
        $stmt->execute([$_SESSION['admin_id'], $action, $_SERVER['REMOTE_ADDR']]);
        
        echo json_encode(['success' => true, 'message' => 'Test SMS sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send test SMS. Check logs for details.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
