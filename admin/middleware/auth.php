<?php
require_once($menuPath . "../config/JWT.php");

function verifyAuth()
{
    global $menuPath;
    $loginRedirect = ($menuPath ?? "./") . "login.php";

    // Check for JWT token in cookie
    if (!isset($_COOKIE['admin_token'])) {
        header("Location: " . $loginRedirect);
        exit();
    }

    $token = $_COOKIE['admin_token'];
    $decoded = JWTManager::verifyToken($token);

    if (!$decoded) {
        // Token is invalid or expired
        setcookie('admin_token', '', time() - 3600, '/');
        header("Location: " . $loginRedirect);
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
