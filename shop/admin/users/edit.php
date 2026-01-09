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
$name = $user['Name'];
$email = $user['Email'];
$contact = $user['Contact'];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    if ($name && $email && $contact) {
        // Check if email exists in other users
        if ($userManager->emailExistsInShopDb($email, $user_id)) {
            $error = 'Email already exists.';
        } else {
            if ($userManager->updateShopUser($user_id, $name, $email, $contact)) {
                header('Location: registered.php?updated=1');
                exit();
            } else {
                $error = 'Failed to update user.';
            }
        }
    } else {
        $error = 'All fields are required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body, html { background: var(--primary-bg); }
        .edit-user-container {
            max-width: 900px;
            margin: 48px auto 0 auto;
            padding: 0 16px;
        }
        .edit-user-card-premium {
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
        .edit-user-header-row {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }
        .edit-user-title {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--accent-dark);
            letter-spacing: 1px;
        }
        .edit-user-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.1rem;
            color: #fff;
            font-weight: 700;
            box-shadow: 0 4px 24px 0 rgba(123,97,255,0.13);
        }
        .edit-user-back {
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
        .edit-user-back:hover {
            background: linear-gradient(90deg, var(--accent-dark) 0%, var(--accent) 100%);
            color: #fff;
            text-decoration: none;
        }
        .edit-user-form-premium {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px 24px;
            margin-top: 18px;
        }
        .edit-user-form-premium input,
        .edit-user-form-premium select {
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
        .edit-user-form-premium input:focus,
        .edit-user-form-premium select:focus {
            border: 1.5px solid var(--accent);
            outline: none;
            box-shadow: 0 2px 8px rgba(123,97,255,0.10);
        }
        .edit-user-form-premium select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: #f8f7fa url('data:image/svg+xml;utf8,<svg fill=\"%237b61ff\" height=\"20\" viewBox=\"0 0 20 20\" width=\"20\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M7.293 7.293a1 1 0 011.414 0L10 8.586l1.293-1.293a1 1 0 111.414 1.414l-2 2a1 1 0 01-1.414 0l-2-2a1 1 0 010-1.414z\"/></svg>') no-repeat right 1.2em center/1.1em auto;
            padding-right: 2.5em;
            font-weight: 600;
        }
        .edit-user-form-premium button {
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .edit-user-form-premium button:hover {
            background: linear-gradient(90deg, var(--accent-light) 0%, var(--accent) 100%);
            color: var(--accent-dark);
            box-shadow: 0 4px 16px rgba(94,234,212,0.10);
        }
        .edit-user-message-premium {
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
        @media (max-width: 700px) {
            .edit-user-card-premium { padding: 14px 2vw 8px 2vw; }
            .edit-user-header-row { flex-direction: column; align-items: flex-start; gap: 10px; }
            .edit-user-form-premium { grid-template-columns: 1fr; gap: 16px; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/sidebar.php'; ?>
<div class="main-content">
    <div class="edit-user-header-row" style="max-width:700px;margin:40px auto 0 auto;display:flex;align-items:center;justify-content:space-between;min-height:48px;gap:14px;">
        <div class="edit-user-title" style="display:flex;align-items:baseline;gap:10px;color:var(--accent-dark);letter-spacing:1px;font-size:var(--font-size-base);font-weight:600;">
            <span class="edit-user-avatar" style="display:flex;justify-content:center;align-items:center;font-size:1.1rem;height:32px;width:32px;margin-bottom:-2px;"><i class="bi bi-pencil-square"></i></span>
            <span class="section-title" style="font-size:var(--font-size-base);font-weight:600;color:var(--accent-dark);letter-spacing:1px;margin-left:2px;display:flex;align-items:center;">Edit User</span>
        </div>
        <a href="registered.php" class="edit-user-back" style="display:inline-flex;align-items:center;gap:7px;color:#fff;text-decoration:none;font-weight:600;background:linear-gradient(90deg,var(--accent) 0%,var(--accent-dark) 100%);border-radius:12px;padding:10px 28px;box-shadow:0 2px 8px rgba(123,97,255,0.10);font-size:var(--font-size-base);border:none;transition:background 0.18s,color 0.18s,box-shadow 0.18s;"><i class="bi bi-arrow-left"></i> Back to Users</a>
    </div>
    <div class="section-divider" style="width:100%;height:1.5px;background:rgba(123,97,255,0.10);margin:18px 0 18px 0;border-radius:2px;"></div>
    <div class="edit-user-form-wrapper" style="max-width:700px;margin:0 auto;">
        <?php if ($error): ?>
            <div class="edit-user-message-premium" style="max-width:420px;margin:0 auto 18px auto;display:flex;align-items:center;justify-content:center;gap:10px;background:#fff0f0;color:#d32f2f;padding:14px 0 14px 0;border-radius:10px;text-align:center;font-size:1.08rem;box-shadow:0 2px 12px rgba(211,47,47,0.08);font-weight:600;transition:opacity 0.5s;"><i class="bi bi-exclamation-circle-fill" style="font-size:1.2em;"></i> <?php echo $error; ?></div>
        <?php endif; ?>
        <form class="edit-user-form-premium" method="post" autocomplete="off" style="width:100%;display:grid;grid-template-columns:1fr 1fr;gap:16px 18px;background:none;border-radius:0;box-shadow:none;padding:0;">
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label class="label" for="edit-user-name" style="font-weight:600;color:var(--accent-dark);margin-bottom:3px;font-size:var(--font-size-sm);">Name</label>
                <input id="edit-user-name" type="text" name="name" placeholder="Name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label class="label" for="edit-user-email" style="font-weight:600;color:var(--accent-dark);margin-bottom:3px;font-size:var(--font-size-sm);">Email</label>
                <input id="edit-user-email" type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label class="label" for="edit-user-contact" style="font-weight:600;color:var(--accent-dark);margin-bottom:3px;font-size:var(--font-size-sm);">Contact</label>
                <input id="edit-user-contact" type="text" name="contact" placeholder="Contact" value="<?php echo htmlspecialchars($contact); ?>" required>
            </div>

            <button type="submit" style="grid-column:1/-1;padding:10px 0;border-radius:10px;background:linear-gradient(90deg,var(--accent) 0%,var(--accent-dark) 100%);color:#fff;font-size:var(--font-size-base);font-weight:600;border:none;box-shadow:0 1px 4px rgba(123,97,255,0.10);transition:background var(--transition),color var(--transition),box-shadow var(--transition);margin-top:8px;display:flex;align-items:center;justify-content:center;gap:8px;"><i class="bi bi-save" style="margin-right:7px;"></i>Save Changes</button>
        </form>
    </div>
</div>
</body>
</html> 