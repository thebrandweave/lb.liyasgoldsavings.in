<?php
session_start();
if (!isset($_SESSION['shop_admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}
$adminId = (int)$_GET['id'];
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->prepare('SELECT * FROM shopadmin WHERE ShopAdminID = ?');
$stmt->execute([$adminId]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$admin) {
    header('Location: index.php');
    exit();
}
// Placeholder value for role
$role = 'Super Admin';
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --accent: #7b61ff;
            --accent-dark: #5f2c82;
            --accent-light: #a78bfa;
            --card-bg: rgba(255,255,255,0.92);
            --card-shadow: 0 8px 40px 0 rgba(123,97,255,0.13), 0 1.5px 8px 0 rgba(123,97,255,0.08);
            --divider: #ece9f9;
            --details-bg: #f7f8fa;
            --actions-bg: #f6f2fd;
            --status-green: #22c55e;
            --status-bg: #eafcf3;
            --status-bg-inactive: #f8e6f9;
            --status-inactive: #a855f7;
            --danger: #d32f2f;
            --danger-bg: #fbeaea;
            --gray: #9ca3af;
            --font-size-base: 0.95rem;
            --font-size-lg: 1.15rem;
            --font-size-title: 1.35rem;
            --font-size-sm: 0.85rem;
        }
        body, html {
            background: linear-gradient(120deg, #f6f7fb 0%, #e6e9f0 100%);
            min-height: 100vh;
        }
        .view-admin-outer {
            max-width: 100vw;
            margin: 64px auto 0 auto;
            padding: 0 0 48px 0;
        }
        .view-admin-card-structure {
            width: 100%;
            max-width: 860px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 28px;
            box-shadow: var(--card-shadow);
            padding: 48px 44px 40px 44px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
            position: relative;
        }
        .view-admin-header-row {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0;
        }
        .view-admin-top-row {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 32px;
            margin-bottom: 18px;
        }
        .view-admin-avatar-square {
            width: 96px;
            height: 96px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.1rem;
            color: #fff;
            font-weight: 700;
            position: relative;
            box-shadow: 0 4px 24px 0 rgba(123,97,255,0.13);
        }
        .view-admin-status-check {
            position: absolute;
            right: -10px;
            bottom: -10px;
            background: var(--status-green);
            color: #fff;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid #fff;
            font-size: 1.1rem;
            box-shadow: 0 2px 8px rgba(34,197,94,0.10);
        }
        .view-admin-main-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        .view-admin-name {
            font-size: var(--font-size-title);
            font-weight: 800;
            color: var(--accent-dark);
            margin-bottom: 0;
            line-height: 1.1;
        }
        .view-admin-email {
            font-size: var(--font-size-base);
            color: var(--gray);
            margin-bottom: 0;
        }
        .view-admin-status-badge {
            display: inline-block;
            padding: 6px 18px;
            border-radius: 999px;
            font-size: var(--font-size-sm);
            font-weight: 600;
            background: var(--status-bg);
            color: var(--status-green);
            margin: 10px 0 0 0;
            box-shadow: 0 1px 4px 0 rgba(56,142,60,0.04);
        }
        .view-admin-status-badge.inactive {
            background: var(--status-bg-inactive);
            color: var(--status-inactive);
        }
        .view-admin-divider {
            width: 100%;
            height: 1.5px;
            background: var(--divider);
            margin: 32px 0 28px 0;
            border-radius: 2px;
        }
        .view-admin-bottom-row {
            width: 100%;
            display: flex;
            gap: 28px;
            align-items: stretch;
        }
        .view-admin-details-card, .view-admin-actions-card {
            flex: 1 1 0;
            min-width: 0;
            display: flex;
            flex-direction: column;
            border-radius: 18px;
            box-shadow: 0 2px 8px rgba(123,97,255,0.04);
        }
        .view-admin-details-card {
            padding: 24px 18px 18px 18px;
            border-left: 3px solid var(--accent);
            background: #f8f7fa;
            justify-content: space-between;
        }
        .view-admin-details-title {
            color: var(--accent);
            font-weight: 700;
            font-size: var(--font-size-lg);
            margin-bottom: 14px;
            letter-spacing: 1px;
        }
        .view-admin-details-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: var(--font-size-base);
            margin-bottom: 6px;
        }
        .view-admin-details-label {
            color: var(--accent);
            font-weight: 500;
            font-size: var(--font-size-sm);
        }
        .view-admin-details-value {
            color: var(--accent-dark);
            font-weight: 700;
            font-size: var(--font-size-base);
        }
        .view-admin-actions-card {
            padding: 24px 18px 18px 18px;
            border-right: 3px solid var(--accent);
            background: #f6f2fd;
            justify-content: space-between;
        }
        .view-admin-actions-title {
            color: var(--accent);
            font-weight: 700;
            font-size: var(--font-size-lg);
            margin-bottom: 14px;
            letter-spacing: 1px;
        }
        .view-admin-action-btn {
            width: 100%;
            padding: 10px 0;
            border-radius: 10px;
            font-size: var(--font-size-base);
            font-weight: 600;
            border: none;
            outline: none;
            margin-bottom: 0;
            transition: background 0.18s, color 0.18s, box-shadow 0.18s, border 0.18s;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: block;
        }
        .view-admin-action-btn.primary {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 2px 8px rgba(123,97,255,0.10);
        }
        .view-admin-action-btn.primary:hover {
            background: var(--accent-dark);
        }
        .view-admin-action-btn.success {
            background: #fff;
            color: var(--accent);
            border: 1.5px solid var(--accent);
            box-shadow: 0 2px 8px rgba(123,97,255,0.10);
        }
        .view-admin-action-btn.success:hover {
            background: #f3f0ff;
            color: var(--accent-dark);
            border: 1.5px solid var(--accent-dark);
        }
        .view-admin-action-btn.danger {
            background: #fff;
            color: #d32f2f;
            border: 1.5px solid var(--divider);
        }
        .view-admin-action-btn.danger:hover {
            background: var(--danger-bg);
            color: var(--danger);
            border: 1.5px solid var(--danger);
        }
        .view-admin-header-row {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0;
        }
        .view-admin-back {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            background: linear-gradient(90deg, var(--accent) 0%, var(--accent-dark) 100%);
            border-radius: 12px;
            padding: 10px 28px;
            box-shadow: 0 2px 8px rgba(123,97,255,0.10);
            font-size: var(--font-size-base);
            border: none;
            transition: background 0.18s, color 0.18s, box-shadow 0.18s;
        }
        .view-admin-back:hover {
            background: linear-gradient(90deg, var(--accent-dark) 0%, var(--accent) 100%);
            color: #fff;
            text-decoration: none;
        }
        @media (max-width: 900px) {
            .view-admin-card-structure {
                padding: 12px 2vw 8px 2vw;
            }
            .view-admin-header-row {
                flex-direction: column;
                align-items: flex-end;
                gap: 10px;
            }
            .view-admin-top-row {
                flex-direction: column;
                align-items: center;
                gap: 12px;
            }
            .view-admin-bottom-row {
                flex-direction: column;
                gap: 12px;
            }
            .view-admin-details-card, .view-admin-actions-card {
                min-width: 0;
                width: 100%;
            }
        }
    </style>
    <script>
    function confirmDeactivate(adminId) {
        if (confirm('Are you sure you want to deactivate this admin account?')) {
            window.location.href = 'deactivate.php?id=' + adminId;
        }
    }
    function confirmActivate(adminId) {
        if (confirm('Are you sure you want to activate this admin account?')) {
            window.location.href = 'activate.php?id=' + adminId;
        }
    }
    window.onload = function() {
        var msg = document.getElementById('view-admin-msg');
        if (msg) {
            setTimeout(function() {
                msg.style.opacity = '0';
                setTimeout(function(){ msg.style.display = 'none'; }, 500);
            }, 3000);
        }
    };
    </script>
</head>
<body>
<?php include __DIR__ . '/../components/sidebar.php'; ?>
<div class="main-content">
    <?php if ($msg): ?>
        <div id="view-admin-msg"
            style="max-width:420px;margin:24px auto 0 auto;display:flex;align-items:center;justify-content:center;gap:10px;background:<?php echo (stripos($msg, 'fail') !== false || stripos($msg, 'error') !== false) ? '#fff0f0' : '#e6f9ed'; ?>;color:<?php echo (stripos($msg, 'fail') !== false || stripos($msg, 'error') !== false) ? '#d32f2f' : '#388e3c'; ?>;padding:14px 0 14px 0;border-radius:10px;text-align:center;font-size:1.08rem;box-shadow:0 2px 12px <?php echo (stripos($msg, 'fail') !== false || stripos($msg, 'error') !== false) ? 'rgba(211,47,47,0.08)' : 'rgba(56,142,60,0.08)'; ?>;font-weight:600;transition:opacity 0.5s;">
            <i class="bi <?php echo (stripos($msg, 'fail') !== false || stripos($msg, 'error') !== false) ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill'; ?>" style="font-size:1.3em;"></i>
            <?php echo htmlspecialchars($msg); ?>
        </div>
    <?php endif; ?>
    <div class="view-admin-header-row" style="width:100%;max-width:700px;margin:40px auto 0 auto;display:flex;align-items:center;justify-content:center;min-height:60px;gap:14px;">
        <div class="view-admin-title" style="display:flex;align-items:baseline;gap:8px;">
            <span class="view-admin-avatar" style="display:flex;justify-content:center;align-items:center;font-size:1.1rem;height:1.1rem;width:1.1rem;margin-bottom:-2px;"><i class="bi bi-person-badge"></i></span>
            <span class="section-title" style="display:flex;align-items:center;font-size:var(--font-size-base);font-weight:600;color:var(--accent-dark);letter-spacing:1px;margin-left:2px;">Admin Details</span>
        </div>
        <a href="index.php" class="view-admin-back" style="margin-left:auto;display:inline-flex;align-items:center;gap:7px;color:#fff;text-decoration:none;font-weight:600;background:linear-gradient(90deg,var(--accent) 0%,var(--accent-dark) 100%);border-radius:999px;padding:8px 22px;box-shadow:0 2px 8px rgba(123,97,255,0.10);font-size:var(--font-size-base);border:none;transition:background 0.18s,color 0.18s,box-shadow 0.18s;"><i class="bi bi-arrow-left"></i> Back to Admins</a>
    </div>
    <div class="section-divider" style="width:100%;height:1.5px;background:rgba(123,97,255,0.10);margin:14px 0 14px 0;border-radius:2px;"></div>
    <div class="view-admin-details-premium" style="width:100%;margin:0 auto;max-width:700px;margin-top:4px;display:grid;grid-template-columns:1fr 1fr;gap:14px 16px;background:none;border-radius:0;box-shadow:none;padding:0;">
        <div>
            <div class="label" style="font-weight:600;color:var(--accent-dark);margin-bottom:3px;font-size:var(--font-size-sm);">Name</div>
            <div class="value" style="color:#232526;font-size:var(--font-size-base);margin-bottom:7px;"><?php echo htmlspecialchars($admin['Name']); ?></div>
        </div>
        <div>
            <div class="label" style="font-weight:600;color:var(--accent-dark);margin-bottom:3px;font-size:var(--font-size-sm);">Email</div>
            <div class="value" style="color:#232526;font-size:var(--font-size-base);margin-bottom:7px;"><?php echo htmlspecialchars($admin['Email']); ?></div>
                </div>
        <div>
            <div class="label" style="font-weight:600;color:var(--accent-dark);margin-bottom:3px;font-size:var(--font-size-sm);">Role</div>
            <div class="value" style="color:#232526;font-size:var(--font-size-base);margin-bottom:7px;"><?php echo htmlspecialchars($role); ?></div>
            </div>
        <div>
            <div class="label" style="font-weight:600;color:var(--accent-dark);margin-bottom:3px;font-size:var(--font-size-sm);">Status</div>
            <div class="value" style="color:#232526;font-size:var(--font-size-base);margin-bottom:7px;"><?php echo htmlspecialchars($admin['Status']); ?></div>
            </div>
        <div style="grid-column:1/-1;">
            <div class="label" style="font-weight:600;color:var(--accent-dark);margin-bottom:3px;font-size:var(--font-size-sm);">Created</div>
            <div class="value" style="color:#232526;font-size:var(--font-size-base);margin-bottom:7px;"><?php echo date('d M Y', strtotime($admin['CreatedAt'])); ?></div>
        </div>
    </div>
    <div class="view-admin-actions" style="max-width:700px;margin:24px auto 0 auto;display:flex;gap:18px;">
        <a href="edit.php?id=<?php echo $adminId; ?>" class="view-admin-action-btn primary" style="flex:1;padding:10px 0;border-radius:10px;font-size:var(--font-size-base);font-weight:600;border:none;outline:none;transition:background 0.18s,color 0.18s,box-shadow 0.18s,border 0.18s;cursor:pointer;text-align:center;text-decoration:none;display:block;background:var(--accent);color:#fff;box-shadow:0 2px 8px rgba(123,97,255,0.10);">Edit Profile</a>
        <?php if ($adminId != $_SESSION['shop_admin_id']): ?>
            <?php if ($admin['Status'] === 'Active'): ?>
                <button class="view-admin-action-btn danger" style="flex:1;padding:10px 0;border-radius:10px;font-size:var(--font-size-base);font-weight:600;border:none;outline:none;transition:background 0.18s,color 0.18s,box-shadow 0.18s,border 0.18s;cursor:pointer;text-align:center;text-decoration:none;display:block;background:#fff;color:#d32f2f;border:1.5px solid #ece9f9;" onclick="confirmDeactivate(<?php echo $adminId; ?>)">Deactivate Account</button>
            <?php else: ?>
                <button class="view-admin-action-btn success" style="flex:1;padding:10px 0;border-radius:10px;font-size:var(--font-size-base);font-weight:600;border:none;outline:none;transition:background 0.18s,color 0.18s,box-shadow 0.18s,border 0.18s;cursor:pointer;text-align:center;text-decoration:none;display:block;background:#fff;color:var(--accent);border:1.5px solid var(--accent);" onclick="confirmActivate(<?php echo $adminId; ?>)">Activate Account</button>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html> 