<?php
session_start();
if (!isset($_SESSION['shop_admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/config.php';

$name = $description = '';
$error = '';
// Image upload directory
$imageDir = '../../uploads/categories/';
if (!is_dir($imageDir)) { mkdir($imageDir, 0777, true); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $imageName = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp','svg'];
        if (in_array($ext, $allowed)) {
            $imageName = 'cat_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $imageDir . $imageName);
        } else {
            $error = 'Invalid image type.';
        }
    }
    if ($name && !$error) {
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare('INSERT INTO categories (name, description, image) VALUES (?, ?, ?)');
        if ($stmt->execute([$name, $description, $imageName])) {
            header('Location: index.php?success=1');
            exit();
        } else {
            $error = 'Failed to add category.';
        }
    } else if (!$name) {
        $error = 'Name is required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Category</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body, html { background: var(--primary-bg); }
        .add-category-container {
            max-width: 900px;
            margin: 48px auto 0 auto;
            padding: 0 16px;
        }
        .add-category-card-premium {
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
        .add-category-header-row {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }
        .add-category-title {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--accent-dark);
            letter-spacing: 1px;
        }
        .add-category-avatar {
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
        .add-category-back {
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
        .add-category-back:hover {
            background: linear-gradient(90deg, var(--accent-dark) 0%, var(--accent) 100%);
            color: #fff;
            text-decoration: none;
        }
        .add-category-form-premium {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px 24px;
            margin-top: 18px;
        }
        .add-category-form-premium input,
        .add-category-form-premium textarea {
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
        .add-category-form-premium input:focus,
        .add-category-form-premium textarea:focus {
            border: 1.5px solid var(--accent);
            outline: none;
            box-shadow: 0 2px 8px rgba(123,97,255,0.10);
        }
        .add-category-form-premium textarea {
            grid-column: 1 / -1;
            resize: vertical;
            min-height: 80px;
        }
        .add-category-form-premium button {
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
        .add-category-form-premium button:hover {
            background: linear-gradient(90deg, var(--accent-light) 0%, var(--accent) 100%);
            color: var(--accent-dark);
            box-shadow: 0 4px 16px rgba(94,234,212,0.10);
        }
        .add-category-message-premium {
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
            .add-category-card-premium { padding: 14px 2vw 8px 2vw; }
            .add-category-header-row { flex-direction: column; align-items: flex-start; gap: 10px; }
            .add-category-form-premium { grid-template-columns: 1fr; gap: 16px; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/sidebar.php'; ?>
<div class="main-content">
    <div class="add-category-container">
        <div class="add-category-card-premium">
            <div class="add-category-header-row">
                <div class="add-category-title">
                    <span class="add-category-avatar"><i class="bi bi-tag"></i></span>
                    Add Category
                </div>
                <a href="index.php" class="add-category-back"><i class="bi bi-arrow-left"></i> Back to Categories</a>
            </div>
            <?php if ($error): ?>
                <div class="add-category-message-premium"><i class="bi bi-exclamation-circle-fill" style="font-size:1.2em;"></i> <?php echo $error; ?></div>
            <?php endif; ?>
            <form class="add-category-form-premium" method="post" enctype="multipart/form-data" autocomplete="off">
                <input type="text" name="name" placeholder="Category Name" value="<?php echo htmlspecialchars($name); ?>" required>
                <textarea name="description" placeholder="Description" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                <input type="file" name="image" accept="image/*" style="grid-column:1/-1;">
                <button type="submit"><i class="bi bi-plus-circle" style="margin-right:7px;"></i>Add Category</button>
            </form>
        </div>
    </div>
</div>
</body>
</html> 