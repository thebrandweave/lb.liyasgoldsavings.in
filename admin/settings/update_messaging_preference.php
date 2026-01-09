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
$method = $input['method'] ?? '';

if (!in_array($method, ['SMS', 'WhatsApp'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit();
}

require_once("../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

try {
    $conn->beginTransaction();
    
    // Update or insert messaging preference
    $stmt = $conn->prepare("
        INSERT INTO MessagingPreference (PreferredMethod) 
        VALUES (?) 
        ON DUPLICATE KEY UPDATE 
        PreferredMethod = VALUES(PreferredMethod),
        UpdatedAt = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$method]);
    
    // Log the activity
    $action = "Updated messaging preference to {$method}";
    $stmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Admin', ?, ?)");
    $stmt->execute([$_SESSION['admin_id'], $action, $_SERVER['REMOTE_ADDR']]);
    
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Messaging preference updated successfully']);
    
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
