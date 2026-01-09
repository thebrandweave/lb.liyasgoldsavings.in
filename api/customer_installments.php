<?php
/**
 * Customer Installments API
 * Secure API endpoint to fetch customer scheme installments using customer unique ID and password
 * 
 * Usage:
 * POST to this endpoint with:
 * - api_key: Your API key for authentication
 * - customer_unique_id: Customer Unique ID
 * - password: Customer password
 * 
 * Response:
 * - success: true/false
 * - data: Customer installments grouped by schemes
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
    
    // First verify customer exists and is active
    $customerCheck = $conn->prepare("
        SELECT CustomerID, CustomerUniqueID, Name, Email, PasswordHash, Status 
        FROM Customers 
        WHERE CustomerUniqueID = ? 
        LIMIT 1
    ");
    
    $customerCheck->execute([$customer_unique_id]);
    $customer = $customerCheck->fetch(PDO::FETCH_ASSOC);
    
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
    if (!password_verify($password, $customer['PasswordHash'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
        
        // Log failed API access (optional - won't break API if table doesn't exist)
        try {
            logApiAccess($conn, $customer['CustomerID'], $customer['Email'], $_SERVER['REMOTE_ADDR'], false);
        } catch (Exception $e) {
            // Silently ignore logging errors
            error_log("API Log Error: " . $e->getMessage());
        }
        exit();
    }
    
    // Password is correct - proceed to fetch installments
    // Query using customer unique ID to get installments
    $query = "
        SELECT 
            c.CustomerID,
            c.CustomerUniqueID,
            c.Name AS CustomerName,
            s.SchemeID,
            s.SchemeName,
            i.InstallmentID,
            i.InstallmentName,
            i.InstallmentNumber,
            i.Amount AS InstallmentAmount,
            COALESCE(p.PaymentID, NULL) AS PaymentID,
            COALESCE(p.Amount, 0.00) AS PaidAmount,
            COALESCE(p.Status, 'Unpaid') AS PaymentStatus,
            p.SubmittedAt AS PaymentDate
        FROM Customers c
        INNER JOIN Subscriptions sub ON c.CustomerID = sub.CustomerID
        INNER JOIN Schemes s ON sub.SchemeID = s.SchemeID
        INNER JOIN Installments i ON s.SchemeID = i.SchemeID
        LEFT JOIN Payments p 
               ON p.CustomerID = c.CustomerID 
              AND p.SchemeID = s.SchemeID 
              AND p.InstallmentID = i.InstallmentID
        WHERE c.CustomerUniqueID = ?
        ORDER BY s.SchemeID, i.InstallmentNumber
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([$customer_unique_id]);
    
    $installments = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $installments[] = [
            'customer_id' => $row['CustomerID'],
            'customer_unique_id' => $row['CustomerUniqueID'],
            'customer_name' => $row['CustomerName'],
            'scheme_id' => $row['SchemeID'],
            'scheme_name' => $row['SchemeName'],
            'installment_id' => $row['InstallmentID'],
            'installment_name' => $row['InstallmentName'],
            'installment_number' => $row['InstallmentNumber'],
            'installment_amount' => floatval($row['InstallmentAmount']),
            'payment_id' => $row['PaymentID'],
            'paid_amount' => floatval($row['PaidAmount']),
            'payment_status' => $row['PaymentStatus'],
            'payment_date' => $row['PaymentDate'],
            'is_paid' => $row['PaymentStatus'] === 'Verified',
            'is_pending' => $row['PaymentStatus'] === 'Pending',
            'is_rejected' => $row['PaymentStatus'] === 'Rejected',
            'is_unpaid' => $row['PaymentStatus'] === 'Unpaid'
        ];
    }
    
    // Group by scheme for better organization
    $schemes = [];
    foreach ($installments as $installment) {
        $schemeId = $installment['scheme_id'];
        if (!isset($schemes[$schemeId])) {
            $schemes[$schemeId] = [
                'scheme_id' => $schemeId,
                'scheme_name' => $installment['scheme_name'],
                'customer_id' => $installment['customer_id'],
                'customer_unique_id' => $installment['customer_unique_id'],
                'customer_name' => $installment['customer_name'],
                'installments' => []
            ];
        }
        $schemes[$schemeId]['installments'][] = $installment;
    }
    
    // Convert to indexed array
    $schemes = array_values($schemes);
    
    // Calculate summary statistics
    $summary = [
        'total_schemes' => count($schemes),
        'total_installments' => count($installments),
        'paid_installments' => count(array_filter($installments, fn($i) => $i['is_paid'])),
        'pending_installments' => count(array_filter($installments, fn($i) => $i['is_pending'])),
        'rejected_installments' => count(array_filter($installments, fn($i) => $i['is_rejected'])),
        'unpaid_installments' => count(array_filter($installments, fn($i) => $i['is_unpaid'])),
        'total_amount' => array_sum(array_column($installments, 'installment_amount')),
        'total_paid' => array_sum(array_column($installments, 'paid_amount'))
    ];
    
    $response = [
        'success' => true,
        'data' => [
            'summary' => $summary,
            'schemes' => $schemes
        ],
        'message' => 'Customer scheme installments retrieved successfully'
    ];
    
    echo json_encode($response);
    
    // Log successful API access (optional - won't break API if table doesn't exist)
    try {
        logApiAccess($conn, $customer['CustomerID'], $customer['Email'], $_SERVER['REMOTE_ADDR'], true);
    } catch (Exception $e) {
        // Silently ignore logging errors
        error_log("API Log Error: " . $e->getMessage());
    }
    
} catch (PDOException $e) {
    // Log error internally (don't expose database details)
    error_log("Customer Installments API Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error. Please try again later.'
    ]);
} catch (Exception $e) {
    // Log error internally
    error_log("Customer Installments API Error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error. Please try again later.'
    ]);
}

/**
 * Log API access for security monitoring
 * This function is optional and won't break the API if the table doesn't exist
 */
function logApiAccess($conn, $customer_id, $email, $ip_address, $success) {
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

