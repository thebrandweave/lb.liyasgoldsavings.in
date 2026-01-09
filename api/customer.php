<?php
/**
 * Customer Login Verification API
 * Secure API endpoint for external websites to verify customer credentials
 * 
 * Usage:
 * POST to this endpoint with:
 * - api_key: Your API key for authentication
 * - customer_unique_id: Customer Unique ID
 * - password: Customer password
 * 
 * Response:
 * - success: true/false
 * - customer_id: Customer ID if successful
 * - message: Response message
 */

// Set headers for API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Only POST requests are accepted.'
    ]);
    exit();
}

// Include required files
require_once '../config/config.php';
require_once '../config/maintenance.php';

// Check if maintenance mode is enabled
if (is_maintenance_enabled()) {
    http_response_code(503);
    echo json_encode([
        'success' => false,
        'message' => 'Service temporarily unavailable due to maintenance.'
    ]);
    exit();
}

// API Configuration
$api_keys = [
    'goldendream_api_key_2024_secure_123', // Replace with your actual API key
    // Add more API keys for different clients if needed
];

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

// If JSON parsing failed, try POST data
if ($input === null) {
    $input = $_POST;
}

// Validate required parameters
if (!isset($input['api_key']) || !isset($input['customer_unique_id']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters: api_key, customer_unique_id, password'
    ]);
    exit();
}

$api_key = trim($input['api_key']);
$customer_unique_id = trim($input['customer_unique_id']);
$password = $input['password'];

// Validate API key
if (!in_array($api_key, $api_keys)) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid API key'
    ]);
    exit();
}

// Validate customer unique ID
if (empty($customer_unique_id)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Customer Unique ID is required'
    ]);
    exit();
}

// Validate password
if (empty($password)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Password is required'
    ]);
    exit();
}

try {
    // Create database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Prepare query to get customer by CustomerUniqueID
    $stmt = $conn->prepare("
        SELECT CustomerID, CustomerUniqueID, Email, PasswordHash, Status, CreatedAt 
        FROM Customers 
        WHERE CustomerUniqueID = ? 
        LIMIT 1
    ");
    
    $stmt->execute([$customer_unique_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if customer exists
    if (!$customer) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Customer not found'
        ]);
        exit();
    }
    
    // Check if customer account is active
    if ($customer['Status'] !== 'Active') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Account is not active. Please contact support.'
        ]);
        exit();
    }
    
    // Verify password
    if (password_verify($password, $customer['PasswordHash'])) {
        // Password is correct - return customer ID
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'customer_id' => $customer['CustomerID'],
            'customer_unique_id' => $customer['CustomerUniqueID'],
            'email' => $customer['Email'],
            'message' => 'Login successful'
        ]);
        
        // Log successful API login (optional - won't break API if table doesn't exist)
        try {
            logApiLogin($conn, $customer['CustomerID'], $customer['Email'], $_SERVER['REMOTE_ADDR'], true);
        } catch (Exception $e) {
            // Silently ignore logging errors
            error_log("API Log Error: " . $e->getMessage());
        }
        
    } else {
        // Password is incorrect
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
        
        // Log failed API login (optional - won't break API if table doesn't exist)
        try {
            logApiLogin($conn, $customer['CustomerID'], $customer['Email'], $_SERVER['REMOTE_ADDR'], false);
        } catch (Exception $e) {
            // Silently ignore logging errors
            error_log("API Log Error: " . $e->getMessage());
        }
    }
    
} catch (PDOException $e) {
    // Log error internally (don't expose database details)
    error_log("Customer API Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error. Please try again later.'
    ]);
} catch (Exception $e) {
    // Log error internally
    error_log("Customer API Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error. Please try again later.'
    ]);
}

/**
 * Log API login attempts for security monitoring
 * This function is optional and won't break the API if the table doesn't exist
 */
function logApiLogin($conn, $customer_id, $email, $ip_address, $success) {
    try {
        // Check if ApiLogs table exists first
        $checkTable = $conn->query("SHOW TABLES LIKE 'ApiLogs'");
        if ($checkTable->rowCount() == 0) {
            // Table doesn't exist, skip logging
            return;
        }
        
        $stmt = $conn->prepare("
            INSERT INTO ApiLogs (CustomerID, Email, IPAddress, Success, UserAgent, CreatedAt) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $success_value = $success ? 1 : 0;
        
        $stmt->execute([$customer_id, $email, $ip_address, $success_value, $user_agent]);
        
    } catch (Exception $e) {
        // Silently fail logging - don't break the main API
        error_log("API Log Error: " . $e->getMessage());
    }
}
?>