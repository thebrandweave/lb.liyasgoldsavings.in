<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../config/config.php';
require_once '../config/UserManager.php';

$userManager = new UserManager();
$error = '';
$success = '';

// Get current user data
$user = $userManager->getUserById($_SESSION['user_id']);

if (!$user) {
    session_destroy();
    header('Location: ../login.php');
    exit();
}

// Handle profile updates for shop users only
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $address = trim($_POST['address'] ?? '');
        
        if ($name && $email && $contact) {
            // Check if email is already taken by another user
            $emailExists = $userManager->emailExistsInShopDb($email, $_SESSION['user_id']);
            if ($emailExists) {
                $error = 'Email already exists. Please use a different email.';
            } else {
                // Check if contact is already taken by another user
                $contactExists = $userManager->contactExistsInShopDb($contact, $_SESSION['user_id']);
                if ($contactExists) {
                    $error = 'Phone number already exists. Please use a different phone number.';
                } else {
                    $result = $userManager->updateShopUser($_SESSION['user_id'], $name, $email, $contact, $address);
                    if ($result) {
                        $success = 'Profile updated successfully!';
                        // Refresh user data
                        $user = $userManager->getUserById($_SESSION['user_id']);
                        // Update session variables
                        $_SESSION['user_name'] = $user['Name'];
                        $_SESSION['user_email'] = $user['Email'];
                        $_SESSION['user_contact'] = $user['Contact'];
                        $_SESSION['user_address'] = $user['Address'];
                    } else {
                        $error = 'Failed to update profile. Please try again.';
                    }
                }
            }
        } else {
            $error = 'Please fill in all required fields.';
        }
    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if ($currentPassword && $newPassword && $confirmPassword) {
            if ($newPassword !== $confirmPassword) {
                $error = 'New passwords do not match.';
            } elseif (strlen($newPassword) < 6) {
                $error = 'Password must be at least 6 characters long.';
            } else {
                // Verify current password
                if (password_verify($currentPassword, $user['PasswordHash'])) {
                    $result = $userManager->changeShopUserPassword($_SESSION['user_id'], $newPassword);
                    if ($result['success']) {
                        $success = 'Password changed successfully!';
                    } else {
                        $error = 'Failed to change password. Please try again.';
                    }
                } else {
                    $error = 'Current password is incorrect.';
                }
            }
        } else {
            $error = 'Please fill in all password fields.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - GoldenDream Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #f7f7fa;
            --secondary: #232526;
            --accent: #ffd600;
            --accent-dark: #ffb300;
            --card-bg: #fff;
            --card-blur: blur(12px);
            --radius: 22px;
            --shadow: 0 8px 32px 0 rgba(0,0,0,0.08);
            --font-main: 'Montserrat', Arial, sans-serif;
        }
        body, html {
            background: var(--primary);
            color: var(--secondary);
            font-family: var(--font-main);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .main-content {
            min-height: calc(100vh - 200px);
            padding: 40px 0;
        }
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 38px 32px 32px 32px;
            backdrop-filter: var(--card-blur);
            -webkit-backdrop-filter: var(--card-blur);
            border: 1.5px solid rgba(255,255,255,0.08);
        }
        .profile-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent-dark), var(--accent));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 2rem;
            font-weight: 700;
        }
        .profile-info h1 {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--accent-dark);
            margin: 0 0 8px 0;
        }
        .profile-info p {
            color: #666;
            margin: 0;
            font-size: 0.95rem;
        }
        .user-type-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 8px;
        }
        .badge-main {
            background: #e3f2fd;
            color: #1976d2;
        }
        .badge-shop {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        .profile-section {
            margin-bottom: 32px;
        }
        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--accent-dark);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .profile-form {
            display: grid;
            gap: 16px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .form-group label {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }
        .form-group input, .form-group textarea {
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
        .form-group input:focus, .form-group textarea:focus {
            border: 1.5px solid var(--accent-dark);
        }
        .form-group input:disabled {
            background: #f5f5f5;
            color: #666;
            cursor: not-allowed;
        }
        .btn {
            padding: 12px 24px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.18s;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary {
            background: var(--accent-dark);
            color: #fff;
        }
        .btn-primary:hover {
            background: var(--accent);
            color: #232526;
        }
        .btn-secondary {
            background: #f0f0f0;
            color: #666;
        }
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.95rem;
        }
        .alert-success {
            background: #e6f9ed;
            color: #388e3c;
            border: 1.5px solid #c8e6c9;
        }
        .alert-error {
            background: #fff0f0;
            color: #d32f2f;
            border: 1.5px solid #ffd6d6;
        }
        .readonly-notice {
            background: #fff3e0;
            color: #f57c00;
            border: 1.5px solid #ffe0b2;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 16px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .navigation {
            margin-bottom: 24px;
        }
        /* .nav-link {
            color: var(--accent-dark);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.18s;
        } */
        /* .nav-link:hover {
            color: var(--accent);
        } */
        .page-title {
            text-align: center;
            margin-bottom: 32px;
        }
        .page-title h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--accent-dark);
            margin: 0 0 8px 0;
        }
        .page-title p {
            color: #666;
            font-size: 1.1rem;
            margin: 0;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            .profile-container {
                margin: 0 16px;
                padding: 24px 20px;
            }
            .page-title h1 {
                font-size: 2rem;
            }
        }
        .navbar {
            width: 100%;
            padding: 24px 0 18px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }
        .navbar-logo {
            font-size: 2rem;
            font-weight: 800;
            color: var(--accent-dark);
            letter-spacing: 2px;
        }
        .navbar-links {
            display: flex;
            gap: 32px;
        }
        .navbar-links a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
            font-size: 1.08rem;
            transition: color 0.18s;
        }
        .navbar-links a:hover {
            color: var(--accent);
        }
        .navbar-cta {
            background: linear-gradient(90deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: #111;
            font-weight: 700;
            border: none;
            border-radius: 999px;
            padding: 12px 32px;
            font-size: 1.08rem;
            box-shadow: 0 2px 12px rgba(255,214,0,0.10);
            cursor: pointer;
            transition: background 0.18s, color 0.18s;
        }
        .navbar-cta:hover {
            background: var(--accent-dark);
            color: #fff;
        }
    </style>
</head>
<body>
    <!-- Include Navbar -->
    <?php include '../components/navbar.php'; ?>
    
    <div class="main-content">
        <div class="page-title">
            <h1><i class="bi bi-person-circle"></i> My Profile</h1>
            <p>Manage your account settings and preferences</p>
        </div>
        
        <div class="profile-container">
            <div class="navigation">
                <a href="../index.php" class="nav-link">
                    <i class="bi bi-arrow-left"></i>Back to Shop
                </a>
            </div>
            
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user['Name'], 0, 1)); ?>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user['Name']); ?></h1>
                    <p><?php echo htmlspecialchars($user['Email']); ?></p>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <!-- Profile Information Section -->
            <div class="profile-section">
                <h2 class="section-title">
                    <i class="bi bi-person"></i>Profile Information
                </h2>
                
                <form class="profile-form" method="post" action="">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['Name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact">Phone Number</label>
                            <input type="text" id="contact" name="contact" value="<?php echo htmlspecialchars($user['Contact']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="unique_id">Unique ID</label>
                            <input type="text" id="unique_id" value="<?php echo htmlspecialchars($user['CustomerUniqueID']); ?>" disabled>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['Address'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i>Update Profile
                    </button>
                </form>
            </div>
            
            <!-- Change Password Section -->
            <div class="profile-section">
                <h2 class="section-title">
                    <i class="bi bi-lock"></i>Change Password
                </h2>
                
                <form class="profile-form" method="post" action="">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-shield-check"></i>Change Password
                    </button>
                </form>
            </div>
            
            <!-- Account Actions -->
            <div class="profile-section">
                <h2 class="section-title">
                    <i class="bi bi-gear"></i>Account Actions
                </h2>
                
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="../logout.php" class="btn btn-secondary">
                        <i class="bi bi-box-arrow-right"></i>Logout
                    </a>
                    <button class="btn btn-secondary" onclick="if(confirm('Are you sure you want to delete your account? This action cannot be undone.')) { window.location.href='../delete_account.php'; }">
                        <i class="bi bi-trash"></i>Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Include Footer -->
    <?php include '../components/footer.php'; ?>
</body>
</html> 