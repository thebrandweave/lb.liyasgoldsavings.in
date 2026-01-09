<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/UserManager.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_source'])) {
    header('Location: ../login.php');
    exit();
}

if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
    header('Location: index.php?error=invalid_product');
    exit();
}

$product_id = (int)$_POST['product_id'];
$quantity = isset($_POST['quantity']) && is_numeric($_POST['quantity']) && $_POST['quantity'] > 0 ? (int)$_POST['quantity'] : 1;

$userManager = new UserManager();
$user = $userManager->getUserById($_SESSION['user_id'], $_SESSION['user_source']);
if (!$user) {
    session_destroy();
    header('Location: ../login.php');
    exit();
}
$customerUniqueID = $user['CustomerUniqueID'];

$db = new Database();
$conn = $db->getConnection();

// Check if item already in cart
$stmt = $conn->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE CustomerUniqueID = ? AND product_id = ?");
$stmt->execute([$customerUniqueID, $product_id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    // Update quantity
    $newQty = $existing['quantity'] + $quantity;
    $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE cart_item_id = ?");
    $stmt->execute([$newQty, $existing['cart_item_id']]);
} else {
    // Insert new cart item
    $stmt = $conn->prepare("INSERT INTO cart_items (CustomerUniqueID, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$customerUniqueID, $product_id, $quantity]);
}

// Redirect back to products page (optionally add ?added=1)
header('Location: index.php?added=1');
exit();