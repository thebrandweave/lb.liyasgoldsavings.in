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
    
    if (!isset($input['quantity']) || !is_numeric($input['quantity']) || $input['quantity'] < 1) {
        echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
        exit();
    }
    
    $cart_item_id = (int)$input['cart_item_id'];
    $quantity = (int)$input['quantity'];
    
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
    
    // First, verify that this cart item belongs to the current user and check stock
    $stmt = $conn->prepare('
        SELECT ci.cart_item_id, p.stock 
        FROM cart_items ci 
        JOIN products p ON ci.product_id = p.product_id 
        WHERE ci.cart_item_id = ? AND ci.CustomerUniqueID = ?
    ');
    $stmt->execute([$cart_item_id, $customerUniqueID]);
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cartItem) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found or access denied']);
        exit();
    }
    
    // Check if requested quantity exceeds stock
    if ($quantity > $cartItem['stock']) {
        echo json_encode(['success' => false, 'message' => 'Requested quantity exceeds available stock']);
        exit();
    }
    
    // Update the cart item quantity
    $stmt = $conn->prepare('UPDATE cart_items SET quantity = ? WHERE cart_item_id = ? AND CustomerUniqueID = ?');
    $result = $stmt->execute([$quantity, $cart_item_id, $customerUniqueID]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Quantity updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 