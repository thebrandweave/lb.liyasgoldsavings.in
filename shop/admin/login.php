<?php
session_start();
require_once '../config/config.php';
require_once __DIR__ . '/../../vendor/firebase/php-jwt/src/JWT.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
if (!defined('JWT_SECRET')) define('JWT_SECRET', 'goldendream_super_secret_key'); // You can move this to config.php

if (isset($_SESSION['shop_admin_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare('SELECT * FROM shopadmin WHERE Email = ? AND Status = "Active" LIMIT 1');
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($admin && password_verify($password, $admin['PasswordHash'])) {
            $_SESSION['shop_admin_id'] = $admin['ShopAdminID'];
            $_SESSION['shop_admin_name'] = $admin['Name'];
            // JWT payload
            $payload = [
                'admin_id' => $admin['ShopAdminID'],
                'email' => $admin['Email'],
                'exp' => time() + 3600
            ];
            $jwt = JWT::encode($payload, JWT_SECRET, 'HS256');
            $encrypted_jwt = encrypt_jwt($jwt);
            // Set JWT as secure, HttpOnly cookie
            setcookie('goldendream_admin_jwt', $encrypted_jwt, [
                'expires' => time() + 3600,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            // If AJAX/JSON request, return JSON
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'token' => $encrypted_jwt]);
                exit();
            }
            header('Location: index.php');
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    } else {
        $error = 'Please enter both email and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shop Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-card login-card-light">
    <div class="logo">GD</div>
    <h2>Shop Admin Login</h2>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off">
        <input type="email" name="email" placeholder="Email" required autofocus>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html> 