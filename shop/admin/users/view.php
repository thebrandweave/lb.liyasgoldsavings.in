<?php
session_start();
if (!isset($_SESSION['shop_admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/config.php';
require_once '../../config/UserManager.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: registered.php');
    exit();
}
$user_id = (int)$_GET['id'];
$userManager = new UserManager();
$user = $userManager->getUserById($user_id, Database::$shop_db);
if (!$user) {
    header('Location: registered.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body, html { background: var(--primary-bg); }
        .view-user-container {
            max-width: 900px;
            margin: 48px auto 0 auto;
            padding: 0 16px;
        }
        .view-user-card-premium {
            width: 100%;
            max-width: 700px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 28px;
            box-shadow: var(--card-shadow);
            padding: 44px 38px 36px 38px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
            position: relative;
        }
        .view-user-header-row {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }
        .view-user-title {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--accent-dark);
            letter-spacing: 1px;
        }
        .view-user-avatar {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.1rem;
            font-weight: 700;
        }
        .view-user-back {
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
        .view-user-back:hover {
            background: linear-gradient(90deg, var(--accent-dark) 0%, var(--accent) 100%);
            color: #fff;
            text-decoration: none;
        }
        .view-user-details-premium {
            width: 100%;
            margin-top: 18px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px 24px;
        }
        .view-user-details-premium .label {
            font-weight: 700;
            color: var(--accent-dark);
            margin-bottom: 4px;
            font-size: 1.08rem;
        }
        .view-user-details-premium .value {
            color: #232526;
            font-size: 1.08rem;
            margin-bottom: 10px;
        }
        @media (max-width: 700px) {
            .view-user-card-premium { padding: 14px 2vw 8px 2vw; }
            .view-user-header-row { flex-direction: column; align-items: flex-start; gap: 10px; }
            .view-user-details-premium { grid-template-columns: 1fr; gap: 16px; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/sidebar.php'; ?>
<div class="main-content">
    <div class="view-user-container" style="max-width:1500px;margin:40px auto 0 auto;padding:0 16px;">
        <div class="view-user-header-row" style="width:100%;max-width:700px;margin:0 auto 0 auto;display:flex;align-items:center;justify-content:center;min-height:60px;gap:14px;">
            <div class="view-user-title" style="display:flex;align-items:baseline;gap:8px;">
                <span class="view-user-avatar" style="display:flex;justify-content:center;align-items:center;font-size:1.1rem;height:1.1rem;width:1.1rem;margin-bottom:-2px;"><i class="bi bi-person"></i></span>
                <span class="section-title" style="display:flex;align-items:center;font-size:var(--font-size-base);font-weight:600;color:var(--accent-dark);letter-spacing:1px;margin-left:2px;">User Details</span>
            </div>
            <a href="registered.php" class="view-user-back" style="margin-left:auto;display:inline-flex;align-items:center;gap:7px;color:#fff;text-decoration:none;font-weight:600;background:linear-gradient(90deg,var(--accent) 0%,var(--accent-dark) 100%);border-radius:999px;padding:8px 22px;box-shadow:0 2px 8px rgba(123,97,255,0.10);font-size:var(--font-size-base);border:none;transition:background 0.18s,color 0.18s,box-shadow 0.18s;"><i class="bi bi-arrow-left"></i> Back to Users</a>
        </div>
        <div class="section-divider" style="width:100%;height:1.5px;background:rgba(123,97,255,0.10);margin:14px 0 14px 0;border-radius:2px;"></div>
        <div class="view-user-details-premium" style="width:100%;margin:0 auto;max-width:700px;margin-top:4px;display:grid;grid-template-columns:1fr 1fr;gap:14px 16px;background:none;border-radius:0;box-shadow:none;padding:0;">
            <div>
                <div class="label" style="font-weight:600;color:var(--accent-dark);margin-bottom:3px;font-size:var(--font-size-sm);">Name</div>
                <div class="value" style="color:#232526;font-size:var(--font-size-base);margin-bottom:7px;"><?php echo htmlspecialchars($user['Name']); ?></div>
            </div>
            <div>
                <div class="label" style="font-weight:600;color:var(--accent-dark);margin-bottom:3px;font-size:var(--font-size-sm);">Email</div>
                <div class="value" style="color:#232526;font-size:var(--font-size-base);margin-bottom:7px;"><?php echo htmlspecialchars($user['Email']); ?></div>
            </div>
            <div>
                <div class="label" style="font-weight:600;color:var(--accent-dark);margin-bottom:3px;font-size:var(--font-size-sm);">Contact</div>
                <div class="value" style="color:#232526;font-size:var(--font-size-base);margin-bottom:7px;"><?php echo htmlspecialchars($user['Contact']); ?></div>
            </div>
            <div>
                <div class="label" style="font-weight:600;color:var(--accent-dark);margin-bottom:3px;font-size:var(--font-size-sm);">Unique ID</div>
                <div class="value" style="color:#232526;font-size:var(--font-size-base);margin-bottom:7px;"><?php echo htmlspecialchars($user['CustomerUniqueID']); ?></div>
            </div>

        </div>
    </div>
</div>
</body>
</html> 