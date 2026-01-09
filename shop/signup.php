<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require_once 'config/config.php';
require_once 'config/UserManager.php';

$error = '';
$success = '';
$userManager = new UserManager();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            // Check if email exists in either database
            $mainDbExists = $userManager->emailExistsInMainDb($email);
            $shopDbExists = $userManager->emailExistsInShopDb($email);
            
            if ($mainDbExists || $shopDbExists) {
                $error = 'Email already exists. Please use a different email or login.';
            } else {
                // Check if contact exists in shop database
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - GoldenDream Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .signup-container {
            max-width: 450px;
            margin: 60px auto 0 auto;
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 38px 32px 32px 32px;
            display: flex;
            flex-direction: column;
            align-items: center;
            backdrop-filter: var(--card-blur);
            -webkit-backdrop-filter: var(--card-blur);
            border: 1.5px solid rgba(255,255,255,0.08);
        }
        .signup-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--accent-dark);
            margin-bottom: 18px;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .signup-form {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .signup-form input, .signup-form textarea {
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
        .signup-form input:focus, .signup-form textarea:focus {
            border: 1.5px solid var(--accent-dark);
        }
        .signup-btn {
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
        }
        .signup-btn:hover {
            background: var(--accent);
            color: #232526;
        }
        .signup-bottom {
            margin-top: 18px;
            font-size: 0.98rem;
            color: #555;
            text-align: center;
        }
        .signup-bottom a {
            color: var(--accent-dark);
            text-decoration: none;
            font-weight: 700;
            margin-left: 4px;
            transition: color 0.18s;
        }
        .signup-bottom a:hover {
            color: var(--accent);
        }
        .signup-error {
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
        .signup-success {
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
        .field-group {
            display: flex;
            gap: 12px;
        }
        .field-group input {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-title"><i class="bi bi-person-plus"></i>Create Account</div>
        
        <?php if ($error): ?>
            <div class="signup-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="signup-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form class="signup-form" method="post" action="">
            <div class="field-group">
                <input type="text" name="name" placeholder="Full Name" required autofocus>
            </div>
            
            <div class="field-group">
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="text" name="contact" placeholder="Phone Number" required>
            </div>
            
            <div class="field-group">
                <input type="password" name="password" placeholder="Password (min 6)" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            
            <textarea name="address" placeholder="Address (optional)" rows="3" style="resize: vertical; font-family: inherit;"></textarea>
            
            <button type="submit" class="signup-btn">Create Account</button>
        </form>
        
        <div class="signup-bottom">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html> 