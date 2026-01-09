<?php
session_start();
if (!isset($_SESSION['shop_admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/config.php';

$db = new Database();
$conn = $db->getConnection();
$adminId = $_SESSION['shop_admin_id'];
$notifications = $conn->prepare('SELECT * FROM shopnotifications WHERE (admin_id = ? OR admin_id IS NULL) AND CustomerUniqueID IS NULL ORDER BY created_at DESC');
$notifications->execute([$adminId]);
$notifications = $notifications->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .notifications-header-row {
            max-width: 700px;
            margin: 40px auto 18px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .notifications-title {
            font-size: var(--font-size-lg);
            font-weight: 700;
            color: var(--accent-dark);
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-divider {
            width: 100%;
            height: 1.5px;
            background: rgba(123,97,255,0.10);
            margin: 24px 0 24px 0;
            border-radius: 2px;
        }
        .notifications-list {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .notification-card {
            background: var(--card-bg);
            border-radius: 18px;
            box-shadow: var(--card-shadow);
            padding: 22px 20px 18px 20px;
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            gap: 18px;
            position: relative;
            transition: box-shadow 0.18s, background 0.18s;
        }
        .notification-card.unread {
            background: #f7f8fa;
            box-shadow: 0 4px 16px rgba(123,97,255,0.10);
        }
        .notification-icon {
            font-size: 1.5rem;
            color: var(--accent);
            margin-top: 2px;
        }
        .notification-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .notification-title-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .notification-title {
            font-size: var(--font-size-base);
            font-weight: 700;
            color: var(--accent-dark);
            margin-right: 8px;
        }
        .notification-type {
            font-size: var(--font-size-sm);
            font-weight: 600;
            color: var(--accent-dark);
            background: #f3f0ff;
            border-radius: 8px;
            padding: 3px 10px;
            display: inline-block;
        }
        .notification-message {
            color: #232526;
            font-size: var(--font-size-base);
            margin-bottom: 2px;
        }
        .notification-meta-row {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-top: 4px;
        }
        .notification-date {
            font-size: var(--font-size-sm);
            color: #888;
        }
        .notification-status {
            font-size: var(--font-size-sm);
            font-weight: 600;
            border-radius: 8px;
            padding: 3px 10px;
            display: inline-block;
        }
        .notification-status.read {
            background: #e6f9ed;
            color: #388e3c;
        }
        .notification-status.unread {
            background: #fffbe6;
            color: #b59f00;
        }
        .notification-related {
            font-size: var(--font-size-sm);
            color: var(--accent);
            background: #f7f8fa;
            border-radius: 8px;
            padding: 3px 10px;
            margin-left: 0;
        }
        @media (max-width: 700px) {
            .notifications-header-row { padding: 10px 2vw; }
            .notifications-title { font-size: var(--font-size-base); }
            .notifications-list { gap: 10px; }
            .notification-card { flex-direction: column; gap: 10px; padding: 14px 8px 10px 8px; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/sidebar.php'; ?>
<div class="main-content">
    <div class="notifications-header-row">
        <div class="notifications-title"><i class="bi bi-bell" style="margin-right:8px;"></i>Notifications</div>
    </div>
    <div class="section-divider"></div>
    <div class="notifications-list">
        <?php foreach ($notifications as $n): ?>
        <a href="view.php?id=<?php echo $n['notification_id']; ?>" style="text-decoration:none;">
        <div class="notification-card<?php echo $n['is_read'] ? '' : ' unread'; ?>" style="cursor:pointer;" title="<?php echo $n['is_read'] ? '' : 'Click to mark as read'; ?>">
            <div class="notification-icon"><i class="bi bi-bell"></i></div>
            <div class="notification-content">
                <div class="notification-title-row" style="display:flex;align-items:center;justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <span class="notification-title" style="<?php echo $n['is_read'] ? '' : 'color:var(--accent);font-weight:700;'; ?>"><?php echo htmlspecialchars($n['title']); ?></span>
                        <span class="notification-type"><?php echo htmlspecialchars(ucfirst($n['type'])); ?></span>
                    </div>
                    <span class="notification-status <?php echo $n['is_read'] ? 'read' : 'unread'; ?>">
                        <?php echo $n['is_read'] ? 'Read' : 'Unread'; ?>
                    </span>
                </div>
                <div class="notification-message" style="margin:6px 0 2px 0;">
                    <?php echo htmlspecialchars($n['message']); ?>
                </div>
                <div class="notification-meta-row" style="display:flex;align-items:center;gap:16px;margin-top:4px;">
                    <span class="notification-date"><?php echo date('d M Y, H:i', strtotime($n['created_at'])); ?></span>
                    <?php if ($n['related_id']): ?>
                        <span class="notification-related">Related: <?php echo htmlspecialchars($n['related_id']); ?></span>
                    <?php endif; ?>
                    <?php if (!$n['is_read']): ?>
                        <span style="color:#b59f00;font-size:var(--font-size-sm);margin-left:auto;">Click to mark as read</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        </a>
        <?php endforeach; ?>
        <?php if (empty($notifications)): ?>
        <div style="text-align:center; color:#aaa; padding:18px;">No notifications found</div>
        <?php endif; ?>
    </div>
</div>
</body>
</html> 