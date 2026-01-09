<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_source'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/UserManager.php';

try {
    // Get user details
    $userManager = new UserManager();
    $user = $userManager->getUserById($_SESSION['user_id'], $_SESSION['user_source']);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    
    $customerUniqueID = $user['CustomerUniqueID'];
    
    // Connect to database
    $conn = (new Database())->getConnection();
    
    // Clear all cart items for this user
    $stmt = $conn->prepare('DELETE FROM cart_items WHERE CustomerUniqueID = ?');
    $result = $stmt->execute([$customerUniqueID]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Cart cleared successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to clear cart']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 