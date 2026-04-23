<?php
session_start();
header('Content-Type: application/json');

function isLocalRequest()
{
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    return in_array($remote, ['127.0.0.1', '::1'], true);
}

// Allow either:
// 1) SuperAdmin session, or
// 2) localhost request (for direct PowerShell/Postman testing on local machine)
$hasAdminSession = isset($_SESSION['admin_id']) && (($_SESSION['admin_role'] ?? '') === 'SuperAdmin');

if (!$hasAdminSession && !isLocalRequest()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once("../../../config/config.php");
require_once("../../../config/WhatsAppMetaAPI.php");

$input = json_decode(file_get_contents('php://input'), true);
$phone = isset($input['phone']) ? trim((string)$input['phone']) : '';

if ($phone === '') {
    echo json_encode(['success' => false, 'message' => 'Phone number is required']);
    exit();
}

try {
    $database = new Database();
    $wa = new WhatsAppMetaAPI($database);

    if (!$wa->isConfigured()) {
        echo json_encode([
            'success' => false,
            'message' => 'WhatsApp Meta API is not configured or inactive'
        ]);
        exit();
    }

    $result = $wa->sendTemplate($phone, 'hello_world', 'en_US');

    if (!empty($result['success'])) {
        echo json_encode([
            'success' => true,
            'message' => 'hello_world template sent successfully',
            'response' => $result['response'] ?? null
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send hello_world template',
            'error' => $result['error'] ?? null,
            'httpCode' => $result['httpCode'] ?? null,
            'response' => $result['response'] ?? null
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Exception while sending hello_world template',
        'error' => $e->getMessage()
    ]);
}

