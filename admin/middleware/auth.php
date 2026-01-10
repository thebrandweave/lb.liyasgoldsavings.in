<?php
require_once($menuPath ."../config/JWT.php");

function verifyAuth()
{
    // Check for JWT token in cookie
    if (!isset($_COOKIE['admin_token'])) {
header("Location: https://la.goldendream.in/admin/login.php");
// header("Location:http://localhost/goldendream/admin/login.php");
        exit();
    }

    $token = $_COOKIE['admin_token'];
    $decoded = JWTManager::verifyToken($token);

    if (!$decoded) {
        // Token is invalid or expired
        setcookie('admin_token', '', time() - 3600, '/');
        header("Location: https://la.goldendream.in/admin/login.php");
        // header("Location:http://localhost/goldendream/admin/login.php");
        exit();
    }

    // Token is valid, refresh it a
    $newToken = JWTManager::refreshToken($token);
    if ($newToken) {
        setcookie(
            'admin_token',
            $newToken,
            time() + 3600,
            '/',
            '',
            true,
            true
        );
    }

    // Set session variables from token
    $_SESSION['admin_id'] = $decoded->admin_id;
    $_SESSION['admin_email'] = $decoded->email;
    $_SESSION['admin_role'] = $decoded->role;

    return true;
}