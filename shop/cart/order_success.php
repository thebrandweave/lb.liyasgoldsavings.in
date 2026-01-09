<?php
session_start();
if (!isset($_GET['order_id'])) {
    header('Location: index.php');
    exit();
}
$order_id = (int)$_GET['order_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Order Placed</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <div class="container" style="max-width: 600px; margin: 60px auto; background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.06); padding: 32px;">
        <h2 style="text-align:center; color: #23211a;">Thank you for your order!</h2>
        <p style="text-align:center;">Your order ID is <b>#<?= htmlspecialchars($order_id) ?></b>.</p>
        <div style="text-align:center; margin-top: 32px;">
            <a href="../products/" class="product-btn">Continue Shopping</a>
        </div>
    </div>
</body>
</html>