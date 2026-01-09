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
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['cart_item_id']) || !is_numeric($input['cart_item_id'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid cart item ID']);
        exit();
    }
    
    $cart_item_id = (int)$input['cart_item_id'];
    
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
    
    // First, verify that this cart item belongs to the current user
    $stmt = $conn->prepare('SELECT cart_item_id FROM cart_items WHERE cart_item_id = ? AND CustomerUniqueID = ?');
    $stmt->execute([$cart_item_id, $customerUniqueID]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found or access denied']);
        exit();
    }
    
    // Remove the cart item
    $stmt = $conn->prepare('DELETE FROM cart_items WHERE cart_item_id = ? AND CustomerUniqueID = ?');
    $result = $stmt->execute([$cart_item_id, $customerUniqueID]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Item removed from cart successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove item from cart']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 