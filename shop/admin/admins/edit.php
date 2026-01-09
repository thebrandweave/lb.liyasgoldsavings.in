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

$name = $admin['Name'];
$email = $admin['Email'];
$status = $admin['Status'];
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = $_POST['status'] ?? 'Active';
    $password = $_POST['password'] ?? '';
    if ($name && $email) {
        // Check for email conflict (ignore current admin)
        $stmt = $conn->prepare('SELECT COUNT(*) FROM shopadmin WHERE Email = ? AND ShopAdminID != ?');
        $stmt->execute([$email, $adminId]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Email already exists.';
        } else {
            if ($password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE shopadmin SET Name=?, Email=?, PasswordHash=?, Status=? WHERE ShopAdminID=?');
                $ok = $stmt->execute([$name, $email, $hash, $status, $adminId]);
            } else {
                $stmt = $conn->prepare('UPDATE shopadmin SET Name=?, Email=?, Status=? WHERE ShopAdminID=?');
                $ok = $stmt->execute([$name, $email, $status, $adminId]);
            }
            if ($ok) {
                header('Location: index.php?updated=1');
                exit();
            } else {
                $error = 'Failed to update admin.';
            }
        }
    } else {
        $error = 'Name and Email are required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body, html { background: var(--primary-bg); }
        .edit-admin-container {
            max-width: 900px;
            margin: 48px auto 0 auto;
            padding: 0 16px;
        }
        .edit-admin-card-premium {
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
        .edit-admin-header-row {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }
        .edit-admin-title {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--accent-dark);
            letter-spacing: 1px;
        }
        .edit-admin-avatar {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.1rem;
            font-weight: 700;
        }
        .edit-admin-back {
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
        .edit-admin-back:hover {
            background: linear-gradient(90deg, var(--accent-dark) 0%, var(--accent) 100%);
            color: #fff;
            text-decoration: none;
        }
        .edit-admin-form-premium {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px 24px;
            margin-top: 18px;
        }
        .edit-admin-form-premium input,
        .edit-admin-form-premium select {
            width: 100%;
            padding: 13px 16px 11px 16px;
            border-radius: 12px;
            border: 1.5px solid #ececec;
            background: #f8f7fa;
            color: var(--accent-dark);
            font-size: var(--font-size-base);
            font-family: var(--font-main);
            transition: border var(--transition), box-shadow var(--transition);
            box-shadow: 0 2px 8px rgba(123,97,255,0.04);
        }
        .edit-admin-form-premium input:focus,
        .edit-admin-form-premium select:focus {
            border: 1.5px solid var(--accent);
            outline: none;
            box-shadow: 0 2px 8px rgba(123,97,255,0.10);
        }
        .edit-admin-form-premium select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: #f8f7fa url('data:image/svg+xml;utf8,<svg fill="%237b61ff" height="20" viewBox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M7.293 7.293a1 1 0 011.414 0L10 8.586l1.293-1.293a1 1 0 111.414 1.414l-2 2a1 1 0 01-1.414 0l-2-2a1 1 0 010-1.414z"/></svg>') no-repeat right 1.2em center/1.1em auto;
            padding-right: 2.5em;
            font-weight: 600;
        }
        .edit-admin-form-premium button {
            grid-column: 1 / -1;
            padding: 13px 0;
            border-radius: 12px;
            background: linear-gradient(90deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: #fff;
            font-size: var(--font-size-base);
            font-weight: 700;
            border: none;
            box-shadow: 0 2px 8px rgba(123,97,255,0.10);
            transition: background var(--transition), color var(--transition), box-shadow var(--transition);
            margin-top: 8px;
        }
        .edit-admin-form-premium button:hover {
            background: linear-gradient(90deg, var(--accent-light) 0%, var(--accent) 100%);
            color: var(--accent-dark);
            box-shadow: 0 4px 16px rgba(94,234,212,0.10);
        }
        .edit-admin-message-premium {
            max-width:420px;
            margin:0 auto 18px auto;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            background:#fff0f0;
            color:#d32f2f;
            padding:14px 0 14px 0;
            border-radius:10px;
            text-align:center;
            font-size:1.08rem;
            box-shadow:0 2px 12px rgba(211,47,47,0.08);
            font-weight:600;
            transition:opacity 0.5s;
        }
        .edit-admin-message-premium.success {
            background:#e6f9ed;
            color:#388e3c;
            box-shadow:0 2px 12px rgba(56,142,60,0.08);
        }
        @media (max-width: 700px) {
            .edit-admin-card-premium { padding: 14px 2vw 8px 2vw; }
            .edit-admin-header-row { flex-direction: column; align-items: flex-start; gap: 10px; }
            .edit-admin-form-premium { grid-template-columns: 1fr; gap: 16px; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/sidebar.php'; ?>
<div class="main-content">
    <div class="edit-admin-header-row" style="width:100%;max-width:700px;margin:40px auto 40px auto;display:flex;align-items:center;justify-content:space-between;min-height:48px;gap:14px;">
        <div class="edit-admin-title" style="display:flex;align-items:baseline;gap:10px;color:var(--accent-dark);letter-spacing:1px;font-size:var(--font-size-base);font-weight:600;">
            <span class="edit-admin-avatar" style="display:flex;justify-content:center;align-items:center;font-size:1.1rem;height:1.1rem;width:1.1rem;margin-bottom:-2px;"><i class="bi bi-pencil-square"></i></span>
            <span class="section-title" style="font-size:var(--font-size-base);font-weight:600;color:var(--accent-dark);letter-spacing:1px;margin-left:2px;display:flex;align-items:center;">Edit Admin</span>
        </div>
        <a href="index.php" class="edit-admin-back" style="display:inline-flex;align-items:center;gap:7px;color:#fff;text-decoration:none;font-weight:600;background:linear-gradient(90deg,var(--accent) 0%,var(--accent-dark) 100%);border-radius:12px;padding:10px 28px;box-shadow:0 2px 8px rgba(123,97,255,0.10);font-size:var(--font-size-base);border:none;transition:background 0.18s,color 0.18s,box-shadow 0.18s;"><i class="bi bi-arrow-left"></i> Back to Admins</a>
    </div>
    <?php if ($success): ?>
        <div class="edit-admin-message-premium success" style="max-width:420px;margin:0 auto 18px auto;display:flex;align-items:center;justify-content:center;gap:10px;background:#e6f9ed;color:#388e3c;padding:14px 0 14px 0;border-radius:10px;text-align:center;font-size:1.08rem;box-shadow:0 2px 12px rgba(56,142,60,0.08);font-weight:600;transition:opacity 0.5s;"><i class="bi bi-check-circle-fill" style="font-size:1.2em;"></i> <?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="edit-admin-message-premium" style="max-width:420px;margin:0 auto 18px auto;display:flex;align-items:center;justify-content:center;gap:10px;background:#fff0f0;color:#d32f2f;padding:14px 0 14px 0;border-radius:10px;text-align:center;font-size:1.08rem;box-shadow:0 2px 12px rgba(211,47,47,0.08);font-weight:600;transition:opacity 0.5s;"><i class="bi bi-exclamation-circle-fill" style="font-size:1.2em;"></i> <?php echo $error; ?></div>
    <?php endif; ?>
    <form class="edit-admin-form-premium" method="post" autocomplete="off" style="width:100%;max-width:700px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:22px 24px;background:none;border-radius:0;box-shadow:none;padding:0;">
        <input type="text" name="name" placeholder="Name" value="<?php echo htmlspecialchars($name); ?>" required>
        <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
        <input type="password" name="password" placeholder="New Password (leave blank to keep current)">
        <select name="status" required>
            <option value="Active" <?php if($status==='Active') echo 'selected'; ?>>Active</option>
            <option value="Inactive" <?php if($status==='Inactive') echo 'selected'; ?>>Inactive</option>
        </select>
        <button type="submit" style="grid-column:1/-1;padding:13px 0;border-radius:12px;background:linear-gradient(90deg,var(--accent) 0%,var(--accent-dark) 100%);color:#fff;font-size:var(--font-size-base);font-weight:700;border:none;box-shadow:0 2px 8px rgba(123,97,255,0.10);transition:background var(--transition),color var(--transition),box-shadow var(--transition);margin-top:8px;display:flex;align-items:center;justify-content:center;gap:8px;"><i class="bi bi-pencil-square" style="margin-right:7px;"></i>Update Admin</button>
    </form>
</div>
</body>
</html> 