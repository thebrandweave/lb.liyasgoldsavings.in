<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/UserManager.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_source'])) {
    header('Location: ../login.php');
    exit();
}

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

// Get cart items
$stmt = $conn->prepare("SELECT ci.product_id, ci.quantity, p.price FROM cart_items ci JOIN products p ON ci.product_id = p.product_id WHERE ci.CustomerUniqueID = ?");
$stmt->execute([$customerUniqueID]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cartItems)) {
    header('Location: index.php?empty_cart=1');
    exit();
}

// Calculate total
$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Insert order
$stmt = $conn->prepare("INSERT INTO orders (CustomerUniqueID, total_amount, order_status) VALUES (?, ?, 'pending')");
$stmt->execute([$customerUniqueID, $total]);
$order_id = $conn->lastInsertId();

// Insert order items
$stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
foreach ($cartItems as $item) {
    $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
}

// Clear cart
$stmt = $conn->prepare("DELETE FROM cart_items WHERE CustomerUniqueID = ?");
$stmt->execute([$customerUniqueID]);

// Redirect to order confirmation
header("Location: order_success.php?order_id=" . $order_id);
exit();