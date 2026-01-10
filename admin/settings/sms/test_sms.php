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

    // Send test SMS with detailed response
    $result = $smsAPI->sendSMS($phone, $message, null, null, true);

    // Debug: Check what we're getting back
    if (!is_array($result)) {
        // If result is not an array, something went wrong
        error_log("SMS API returned non-array result: " . var_export($result, true));
        $result = ['success' => false, 'error' => 'Invalid response from SMS API: ' . (is_bool($result) ? ($result ? 'true' : 'false') : gettype($result))];
    }

    if (isset($result['success']) && $result['success'] === true) {
        // Log the test activity
        $action = "Test SMS sent to {$phone}";
        $stmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Admin', ?, ?)");
        $stmt->execute([$_SESSION['admin_id'], $action, $_SERVER['REMOTE_ADDR']]);

        echo json_encode([
            'success' => true,
            'message' => 'Test SMS sent successfully',
            'details' => $result['message'] ?? 'SMS sent'
        ]);
    } else {
        // Get detailed error information
        // Check multiple possible error fields - prioritize actual error details
        $errorMessage = 'Unknown error occurred';

        // First check if error field exists and has meaningful content
        if (isset($result['error']) && !empty($result['error']) && $result['error'] !== 'Failed to send test SMS') {
            $errorMessage = $result['error'];
        }
        // Then check response for error details
        elseif (isset($result['response'])) {
            if (is_array($result['response'])) {
                if (isset($result['response']['message']) && !empty($result['response']['message'])) {
                    $errorMessage = $result['response']['message'];
                } elseif (isset($result['response']['error']) && !empty($result['response']['error'])) {
                    $errorMessage = $result['response']['error'];
                } elseif (isset($result['response']['errorMessage']) && !empty($result['response']['errorMessage'])) {
                    $errorMessage = $result['response']['errorMessage'];
                } else {
                    // Try to get any meaningful info from response
                    $responseStr = json_encode($result['response'], JSON_UNESCAPED_UNICODE);
                    if (strlen($responseStr) > 0 && $responseStr !== '[]' && $responseStr !== '{}') {
                        $errorMessage = 'API Response: ' . substr($responseStr, 0, 200);
                    }
                }
            } elseif (is_string($result['response']) && !empty($result['response'])) {
                $errorMessage = 'API Response: ' . substr($result['response'], 0, 200);
            }
        }
        // Check HTTP code for clues
        if (isset($result['httpCode']) && $result['httpCode'] !== 'N/A' && $errorMessage === 'Unknown error occurred') {
            $errorMessage = 'HTTP Error ' . $result['httpCode'] . ' - Check API response for details';
        }

        $httpCode = isset($result['httpCode']) ? $result['httpCode'] : 'N/A';
        $apiResponse = isset($result['response']) ? $result['response'] : null;

        // Get PHP error log path
        $phpLogPath = ini_get('error_log');
        if (empty($phpLogPath)) {
            // Default XAMPP paths
            $xamppPath = getenv('XAMPP_PATH') ?: 'C:\\xampp';
            $phpLogPath = $xamppPath . '\\apache\\logs\\error.log';
            if (!file_exists($phpLogPath)) {
                $phpLogPath = $xamppPath . '\\php\\logs\\php_error_log';
            }
        }

        // Add debug info
        $debugInfo = [
            'result_type' => gettype($result),
            'result_keys' => is_array($result) ? array_keys($result) : null,
            'result_value' => is_array($result) ? $result : var_export($result, true)
        ];

        echo json_encode([
            'success' => false,
            'message' => 'Failed to send test SMS',
            'error' => $errorMessage,
            'httpCode' => $httpCode,
            'apiResponse' => $apiResponse,
            'logPath' => $phpLogPath,
            'logPathExists' => file_exists($phpLogPath),
            'debug' => $debugInfo
        ]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
