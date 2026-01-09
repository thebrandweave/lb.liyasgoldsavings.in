<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

require_once '../config/config.php';
require_once '../config/UserManager.php';

$userManager = new UserManager();
$db = new Database();
$conn = $db->getConnection();

// Get user notifications
$user_id = $_SESSION['user_id'];
$user_source = $_SESSION['user_source'] ?? '';
$notifications = [];

try {
    // Get CustomerUniqueID for the current user
    if ($user_source === Database::$shop_db) {
        // For shop users, get CustomerUniqueID from shop_users table
        $stmt = $conn->prepare('SELECT CustomerUniqueID FROM shop_users WHERE CustomerID = ?');
        $stmt->execute([$user_id]);
        $customer_unique_id = $stmt->fetchColumn();
        

    } else {
        // For main users, use the user_id directly as CustomerUniqueID
        $customer_unique_id = $user_id;
    }
    
    if ($customer_unique_id) {
        $stmt = $conn->prepare('SELECT * FROM shopnotifications WHERE CustomerUniqueID = ? ORDER BY created_at DESC');
        $stmt->execute([$customer_unique_id]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } else {
        // No CustomerUniqueID found
    }
} catch (Exception $e) {
    // Handle error silently
}

// Mark notifications as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    $notification_id = $_POST['notification_id'];
    try {
        // Get CustomerUniqueID for the current user
        if ($user_source === Database::$shop_db) {
            // For shop users, get CustomerUniqueID from shop_users table
            $stmt = $conn->prepare('SELECT CustomerUniqueID FROM shop_users WHERE CustomerID = ?');
            $stmt->execute([$user_id]);
            $customer_unique_id = $stmt->fetchColumn();
        } else {
            // For main users, use the user_id directly as CustomerUniqueID
            $customer_unique_id = $user_id;
        }
        
        if ($customer_unique_id) {
            $stmt = $conn->prepare('UPDATE shopnotifications SET is_read = 1 WHERE notification_id = ? AND CustomerUniqueID = ?');
            $stmt->execute([$notification_id, $customer_unique_id]);
        }
    } catch (Exception $e) {
        // Handle error silently
    }
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications - GoldenDream Shop</title>
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
            width: 100vw;
        }
        .notifications-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .notifications-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--secondary);
            margin: 0;
        }
        .notifications-subtitle {
            font-size: 1.1rem;
            color: #666;
            margin: 8px 0 0 0;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: black;
            text-decoration: none;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 8px;
            transition: all 0.3s ease;
            background: var(--accent);
            /* border: 2px solid #e9ecef; */
        }
        .back-btn:hover {
            background:rgb(0, 0, 0);
            /* border-color: ; */
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .notification-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
            background: #fff;
        }
        .notification-item:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }
        .notification-item.unread {
            background: #fff3e0;
            border-color: var(--accent);
            box-shadow: 0 2px 8px rgba(255, 214, 0, 0.1);
        }
        .notification-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #fff;
            flex-shrink: 0;
        }
        .notification-icon.info {
            background: linear-gradient(135deg, #2196f3, #1976d2);
        }
        .notification-icon.success {
            background: linear-gradient(135deg, #4caf50, #388e3c);
        }
        .notification-icon.warning {
            background: linear-gradient(135deg, #ff9800, #f57c00);
        }
        .notification-icon.error {
            background: linear-gradient(135deg, #f44336, #d32f2f);
        }
        .notification-content {
            flex: 1;
        }
        .notification-title {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--secondary);
            margin-bottom: 8px;
        }
        .notification-message {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 12px;
        }
        .notification-time {
            font-size: 0.85rem;
            color: #999;
            margin-bottom: 12px;
        }
        .notification-actions {
            display: flex;
            gap: 8px;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-primary {
            background: var(--success);
            color: white;
        }
        .btn-primary:hover {
            background: #218838;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #f8f9fa;
            color: #666;
            border: 1px solid #ddd;
        }
        .btn-secondary:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }
        .notifications-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #eee;
        }
        .action-btn {
            background: var(--success);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        .action-btn:hover {
            transform: translateY(-2px);
        }
        .action-btn.secondary {
            background: #6c757d;
        }
        .action-btn.secondary:hover {
            background: #5a6268;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 12px;
            color: var(--secondary);
        }
        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 24px;
        }
        .shop-now-btn {
            background: var(--accent);
            color: var(--secondary);
            border: none;
            padding: 0px 24px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        .shop-now-btn i {
            font-size: 2rem;
            color: var(--secondary);
            margin-top: 18px;
        }
        .shop-now-btn:hover {
            background: var(--accent-dark);
            transform: translateY(-2px);
        }
        @media (max-width: 1024px) {
            .notifications-layout {
                grid-template-columns: 1fr;
                gap: 24px;
            }
            .notifications-summary {
                position: static;
            }
        }
        @media (max-width: 768px) {
            .notifications-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            .notifications-title {
                font-size: 2rem;
            }
            .notifications-list, .notifications-summary {
                padding: 20px;
            }
            .notification-item {
                padding: 16px;
            }
            .notification-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
            .notifications-actions {
                flex-direction: column;
                gap: 12px;
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
        <div class="notifications-container">
            <div class="notifications-header">
                <div>
                    <h1 class="notifications-title">Notifications</h1>
                    <p class="notifications-subtitle">Stay updated with the latest news and offers.</p>
                </div>
                <a href="../index.php" class="back-btn">
                    <i class="bi bi-arrow-left"></i>Back to Shop
                </a>
            </div>
            
            <div class="notifications-layout">
                <div class="notifications-list">
                    <?php if (empty($notifications)): ?>
                        <div class="empty-state">
                            <i class="bi bi-bell-slash"></i>
                            <h3>No notifications yet</h3>
                            <p>You're all caught up! We'll notify you when there's something new.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>">
                                <div class="notification-icon <?php echo $notification['type'] ?? 'info'; ?>">
                                    <?php
                                    $icon = 'bi-info-circle';
                                    switch ($notification['type']) {
                                        case 'success':
                                            $icon = 'bi-check-circle';
                                            break;
                                        case 'warning':
                                            $icon = 'bi-exclamation-triangle';
                                            break;
                                        case 'error':
                                            $icon = 'bi-x-circle';
                                            break;
                                        default:
                                            $icon = 'bi-bell';
                                    }
                                    ?>
                                    <i class="bi <?php echo $icon; ?>"></i>
                                </div>
                                <div class="notification-content">
                                    <div class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></div>
                                    <div class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></div>
                                    <div class="notification-time">
                                        <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                                    </div>
                                    <?php if (!$notification['is_read']): ?>
                                        <div class="notification-actions">
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                                <button type="submit" name="mark_read" class="btn btn-primary">
                                                    <i class="bi bi-check"></i>Mark as Read
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- Include Footer -->
    <?php include '../components/footer.php'; ?>
</body>
</html>
