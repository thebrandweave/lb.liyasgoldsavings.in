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

require_once("../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

try {
    // Get current messaging preference
    $stmt = $conn->query("SELECT PreferredMethod FROM MessagingPreference ORDER BY PreferenceID DESC LIMIT 1");
    $preference = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$preference) {
        // Create default preference if none exists
        $stmt = $conn->prepare("INSERT INTO MessagingPreference (PreferredMethod) VALUES ('WhatsApp')");
        $stmt->execute();
        $preference = ['PreferredMethod' => 'WhatsApp'];
    }
    
    echo json_encode([
        'success' => true,
        'preference' => $preference['PreferredMethod']
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
