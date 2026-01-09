<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require_once 'config/config.php';
require_once 'config/UserManager.php';
require_once __DIR__ . '/../vendor/firebase/php-jwt/src/JWT.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
if (!defined('JWT_SECRET')) define('JWT_SECRET', 'goldendream_super_secret_key'); // You can move this to config.php

$error = '';
$success = '';
$userManager = new UserManager();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';
    
    if ($action === 'login') {
        $identifier = trim($_POST['identifier'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if ($identifier && $password) {
            // Only authenticate shop user by email or phone
            $shopResult = $userManager->authenticateShopUser($identifier, $password);
            
            if ($shopResult['success']) {
                // Shop user found and authenticated
                $user = $shopResult['user'];
                $_SESSION['user_id'] = $user['CustomerID'];
                $_SESSION['user_name'] = $user['Name'];
                $_SESSION['user_source'] = Database::$shop_db;
                $_SESSION['user_unique_id'] = $user['CustomerUniqueID'];
                $_SESSION['user_contact'] = $user['Contact'];
                $_SESSION['user_email'] = $user['Email'];
                $_SESSION['user_address'] = $user['Address'];
                // JWT payload
                $payload = [
                    'user_id' => $user['CustomerID'],
                    'email' => $user['Email'],
                    'exp' => time() + 3600
                ];
                $jwt = JWT::encode($payload, JWT_SECRET, 'HS256');
                $encrypted_jwt = encrypt_jwt($jwt);
                // Set JWT as secure, HttpOnly cookie
                setcookie('goldendream_jwt', $encrypted_jwt, [
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
                $error = 'Invalid credentials. Please check your email/phone and password.';
            }
        } else {
            $error = 'Please enter both email/phone and password.';
        }
    } elseif ($action === 'register') {
        // Registration functionality for shop users only
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $address = trim($_POST['address'] ?? '');
        
        if ($name && $email && $contact && $password && $confirmPassword) {
            if ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters long.';
            } else {
                // Check if email exists in shop database
                $shopDbExists = $userManager->emailExistsInShopDb($email);
                
                if ($shopDbExists) {
                    $error = 'Email already exists. Please use a different email or login.';
                } else {
                    // Check if contact exists in shop_db
                    if ($userManager->contactExistsInShopDb($contact)) {
                        $error = 'Phone number already exists. Please use a different phone number or login.';
                    } else {
                        $result = $userManager->createShopUser($name, $email, $contact, $password, $address);
                        if ($result['success']) {
                            $success = 'Registration successful! Please login with your email/phone and password.';
                        } else {
                            $error = 'Registration failed. Please try again.';
                        }
                    }
                }
            }
        } else {
            $error = 'Please fill in all required fields.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login & Register - GoldenDream Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .auth-container {
            max-width: 520px;
            margin: 60px auto 0 auto;
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 38px 32px 32px 32px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            backdrop-filter: var(--card-blur);
            -webkit-backdrop-filter: var(--card-blur);
            border: 1.5px solid rgba(255,255,255,0.08);
        }
        .auth-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--accent-dark);
            margin-bottom: 18px;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .auth-tabs {
            display: flex;
            width: 100%;
            margin-bottom: 24px;
            border-radius: 12px;
            background: #f7f7fa;
            padding: 4px;
        }
        .auth-tab {
            flex: 1;
            padding: 10px 16px;
            border: none;
            background: transparent;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.18s;
            color: #666;
        }
        .auth-tab.active {
            background: var(--accent-dark);
            color: #fff;
        }
        .auth-form {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .input-icon { width: 100%; }
        .input-icon input { 
            width: 100% !important; 
            box-sizing: border-box;
        }


        .auth-form input, .auth-form select, .auth-form textarea {
            width: 100%;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1.5px solid #ececec;
            font-size: 1rem;
            font-family: var(--font-main);
            background: #f7f7fa;
            color: #232526;
            outline: none;
            transition: border 0.18s;
        }
        .auth-form input:focus, .auth-form select:focus, .auth-form textarea:focus {
            border: 1.5px solid var(--accent-dark);
        }
        .auth-btn {
            background: var(--accent-dark);
            color: #fff;
            font-weight: 700;
            border: none;
            border-radius: 999px;
            padding: 12px 0;
            font-size: 1.08rem;
            cursor: pointer;
            transition: background 0.18s, color 0.18s;
            margin-top: 6px;
            width: 100%;
        }
        .auth-btn:hover {
            background: var(--accent);
            color: #232526;
        }
        .auth-bottom {
            margin-top: 18px;
            font-size: 0.98rem;
            color: #555;
            text-align: center;
        }
        .auth-bottom a {
            color: var(--accent-dark);
            text-decoration: none;
            font-weight: 700;
            margin-left: 4px;
            transition: color 0.18s;
        }
        .auth-bottom a:hover {
            color: var(--accent);
        }
        .auth-error {
            background: #fff0f0;
            color: #d32f2f;
            border: 1.5px solid #ffd6d6;
            border-radius: 8px;
            padding: 10px 16px;
            margin-bottom: 10px;
            font-size: 0.98rem;
            width: 100%;
            text-align: center;
        }
        .auth-success {
            background: #e6f9ed;
            color: #388e3c;
            border: 1.5px solid #c8e6c9;
            border-radius: 8px;
            padding: 10px 16px;
            margin-bottom: 10px;
            font-size: 0.98rem;
            width: 100%;
            text-align: center;
        }
        .form-section {
            display: none;
        }
        .form-section.active {
            display: block;
        }
        .login-info {
            background: #f0f4ff;
            color: #7b61ff;
            border: 1.5px solid #e0e7ff;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 16px;
            font-size: 0.9rem;
            text-align: center;
            line-height: 1.4;
        }
        .field-group {
            display: flex;
            gap: 12px;
        }
        .field-group input {
            flex: 1;
        }

        /* Keep the logo inside a fixed circular container */
        .auth-logo {
            width: 220px;
            height: 220px;
            margin: 0 auto 10px auto;
            border-radius: 50%;
            overflow: hidden;
            display: block;
        }
        .auth-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        
        /* Desktop-only: widen container and form so login inputs are wider */
        @media (min-width: 992px) {
            .input-icon input { width: 100% !important; }
        }
 
         @media (max-width: 600px) {
             body { background: #fff; }
             .auth-bg { display: none; }
             .auth-container {
                 margin: 0;
                 padding: 20px 16px;
                 width: 100%;
                 max-width: none;
                 border-radius: 0;
                 background: #fff;
                 border: none;
                 box-shadow: none;
                 min-height: 300vh;
                 align-items: stretch;
                 justify-content: flex-start;
             }
             .auth-logo { position: relative; z-index: 1; width: 180px; height: 180px; }
             .auth-form {
                 width: 100%;
                 width: 300px;
             }
             .field-group {
                 flex-direction: column;
                 gap: 12px;
             }
             .auth-title {
                 font-size: 1.4rem;
             }
             .auth-tab {
                 padding: 10px;
                 font-size: 0.95rem;
             }
             .auth-btn {
                 font-size: 1rem;
                 padding: 12px 0;
             }
         }
    </style>
</head>
<body>
    <div class="auth-bg"></div>
    <div class="auth-container">
        <div class="auth-logo" style="margin-bottom: 10px;">
            <img src="assets/image/gd-store-logo2.png" alt="GD Logo">
        </div>
        
        <?php if ($error): ?>
            <div class="auth-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="auth-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="auth-tabs">
            <button class="auth-tab active" onclick="showSection('login')">Login</button>
            <button class="auth-tab" onclick="showSection('register')">Register</button>
        </div>
           <!-- This is optional but required for Main users-->
            <!-- <div class="login-info">
                <strong>Login Options:</strong><br>
                • Main Users: Enter your Customer Unique ID<br>
                • Shop Users: Enter your Email or Phone Number
            </div> -->
        
        <!-- Login Section -->
        <div id="login-section" class="form-section active">
            <form class="auth-form" method="post" action="">
                <input type="hidden" name="action" value="login">
                
                <div class="input-icon">
                    <i class="bi bi-person"></i>
                    <input type="text" name="identifier" placeholder="Customer Unique ID, Email, or Phone" required autofocus>
                </div>
                <div class="input-icon">
                    <i class="bi bi-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <!-- <div style="text-align:right; margin-bottom:8px;">
                    <a href="#" style="font-size:0.95em; color:var(--accent-dark); text-decoration:none;">Forgot password?</a>
                </div> -->
                <button type="submit" class="auth-btn">Login</button>
            </form>
        </div>
        
        <!-- Register Section -->
        <div id="register-section" class="form-section">
            <form class="auth-form" method="post" action="">
                <input type="hidden" name="action" value="register">
                
                <input type="text" name="name" placeholder="Full Name" required>
                
                <div class="field-group">
                    <input type="email" name="email" placeholder="Email Address" required>
                    <input type="text" name="contact" placeholder="Phone Number" required>
                </div>
                
                <div class="field-group">
                    <input type="password" name="password" placeholder="Password (min 6)" required>
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                
                <textarea name="address" placeholder="Address (optional)" rows="3" style="resize: vertical; font-family: inherit;"></textarea>
                <button type="submit" class="auth-btn">Register</button>
            </form>
            
            <div class="auth-bottom">
                Already have an account? <a href="#" onclick="showSection('login')">Login here</a>
            </div>
        </div>
    </div>
    
    <script>
        function showSection(section) {
            // Hide all sections
            document.querySelectorAll('.form-section').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.auth-tab').forEach(el => el.classList.remove('active'));
            
            // Show selected section
            document.getElementById(section + '-section').classList.add('active');
            event.target.classList.add('active');
        }
    </script>
</body>
</html> 