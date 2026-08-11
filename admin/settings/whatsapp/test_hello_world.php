<?php
session_start();
header('Content-Type: application/json');

function isLocalRequest()
{
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    return in_array($remote, ['127.0.0.1', '::1', 'localhost'], true);
}

// Allow logged-in admin session or localhost request
$hasAdminSession = isset($_SESSION['admin_id']);

if (!$hasAdminSession && !isLocalRequest()) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized: Please log in to your Admin Dashboard first at /admin/login.php'
    ]);
    exit();
}

require_once("../../../config/config.php");
require_once("../../../config/WhatsAppMetaAPI.php");

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
if (!$input || !is_array($input)) {
    $input = array_merge($_GET, $_POST);
}

$phone = isset($input['phone']) ? trim((string)$input['phone']) : '';

if ($phone === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Phone number is required. Usage: test_hello_world.php?phone=917411074379'
    ]);
    exit();
}


try {
    $database = new Database();
    $wa = new WhatsAppMetaAPI($database);

    if (!$wa->isConfigured()) {
        echo json_encode([
            'success' => false,
            'message' => 'WhatsApp Meta API is not configured or status is inactive. Please save your Access Token and Phone Number ID first.'
        ]);
        exit();
    }

    $result = $wa->sendTemplate($phone, 'hello_world', 'en_US');

    if (!empty($result['success'])) {
        echo json_encode([
            'success' => true,
            'message' => 'Test message (hello_world template) sent successfully to ' . $phone,
            'response' => $result['response'] ?? null
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send test message: ' . ($result['error'] ?? 'Unknown error'),
            'error' => $result['error'] ?? null,
            'httpCode' => $result['httpCode'] ?? null,
            'response' => $result['response'] ?? null
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Exception while sending test message: ' . $e->getMessage(),
        'error' => $e->getMessage()
    ]);
}


