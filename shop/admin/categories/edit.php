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
$catId = (int)$_GET['id'];
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->prepare('SELECT * FROM categories WHERE category_id = ?');
$stmt->execute([$catId]);
$cat = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$cat) {
    header('Location: index.php');
    exit();
}
$name = $cat['name'];
$description = $cat['description'];
$success = $error = '';
$imageDir = '../../uploads/categories/';
if (!is_dir($imageDir)) { mkdir($imageDir, 0777, true); }
$currentImage = $cat['image'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $imageName = $currentImage;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp','svg'];
        if (in_array($ext, $allowed)) {
            $imageName = 'cat_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $imageDir . $imageName);
            // Remove old image if exists and is different
            if ($currentImage && $currentImage !== $imageName && file_exists($imageDir . $currentImage)) {
                @unlink($imageDir . $currentImage);
            }
        } else {
            $error = 'Invalid image type.';
        }
    }
    if ($name && !$error) {
        $stmt = $conn->prepare('UPDATE categories SET name=?, description=?, image=? WHERE category_id=?');
        if ($stmt->execute([$name, $description, $imageName, $catId])) {
            header('Location: index.php?updated=1');
            exit();
        } else {
            $error = 'Failed to update category.';
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
    <title>Edit Category</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body, html { background: var(--primary-bg); }
        .edit-category-container {
            max-width: 900px;
            margin: 48px auto 0 auto;
            padding: 0 16px;
        }
        .edit-category-header-row {
            width: 100%;
            max-width: 700px;
            margin: 40px auto 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 48px;
            gap: 14px;
        }
        .edit-category-title {
            display: flex;
            align-items: baseline;
            gap: 10px;
            color: var(--accent-dark);
            letter-spacing: 1px;
            font-size: var(--font-size-base);
            font-weight: 600;
        }
        .edit-category-avatar {
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.1rem;
            height: 32px;
            width: 32px;
            margin-bottom: -2px;
        }
        .section-title {
            font-size: var(--font-size-base);
            font-weight: 600;
            color: var(--accent-dark);
            letter-spacing: 1px;
            margin-left: 2px;
            display: flex;
            align-items: center;
        }
        .section-divider {
            width: 100%;
            height: 1.5px;
            background: rgba(123,97,255,0.10);
            margin: 18px 0 18px 0;
            border-radius: 2px;
        }
        .edit-category-form-wrapper {
            max-width: 700px;
            margin: 0 auto;
        }
        .edit-category-form-premium {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 18px;
            background: none;
            border-radius: 0;
            box-shadow: none;
            padding: 0;
        }
        .edit-category-form-premium input, .edit-category-form-premium textarea {
            width: 100%;
            padding: 10px 14px 9px 14px;
            border-radius: 10px;
            border: 1.2px solid #ececec;
            background: #f8f7fa;
            color: var(--accent-dark);
            font-size: var(--font-size-base);
            font-family: var(--font-main);
            transition: border var(--transition), box-shadow var(--transition);
            box-shadow: 0 1px 4px rgba(123,97,255,0.04);
        }
        .edit-category-form-premium input:focus, .edit-category-form-premium textarea:focus {
            border: 1.2px solid var(--accent);
            outline: none;
            box-shadow: 0 1px 4px rgba(123,97,255,0.10);
        }
        .edit-category-form-premium textarea {
            grid-column: 1 / -1;
            resize: vertical;
            min-height: 60px;
        }
        .edit-category-form-premium button {
            grid-column: 1 / -1;
            padding: 10px 0;
            border-radius: 10px;
            background: linear-gradient(90deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: #fff;
            font-size: var(--font-size-base);
            font-weight: 600;
            border: none;
            box-shadow: 0 1px 4px rgba(123,97,255,0.10);
            transition: background var(--transition), color var(--transition), box-shadow var(--transition);
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .edit-category-form-premium button:hover {
            background: linear-gradient(90deg, var(--accent-light) 0%, var(--accent) 100%);
            color: var(--accent-dark);
            box-shadow: 0 2px 8px rgba(94,234,212,0.10);
        }
        .edit-category-form-premium .label {
            font-weight: 600;
            color: var(--accent-dark);
            margin-bottom: 3px;
            font-size: var(--font-size-sm);
        }
        .edit-category-back {
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
        .edit-category-back:hover {
            background: linear-gradient(90deg, var(--accent-dark) 0%, var(--accent) 100%);
            color: #fff;
            text-decoration: none;
        }
        .edit-category-message-premium {
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
        .edit-category-message-premium.success {
            background:#e6f9ed;
            color:#388e3c;
            box-shadow:0 2px 12px rgba(56,142,60,0.08);
        }
        @media (max-width: 700px) {
            .edit-category-card-premium { padding: 14px 2vw 8px 2vw; }
            .edit-category-header-row { flex-direction: column; align-items: flex-start; gap: 10px; }
            .edit-category-form-premium { grid-template-columns: 1fr; gap: 16px; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/sidebar.php'; ?>
<div class="main-content">
    <div class="edit-category-container">
        <div class="edit-category-header-row">
            <div class="edit-category-title">
                <span class="edit-category-avatar"><i class="bi bi-tag"></i></span>
                <span class="section-title">Edit Category</span>
            </div>
            <a href="index.php" class="edit-category-back"><i class="bi bi-arrow-left"></i> Back to Categories</a>
        </div>
        <div class="section-divider"></div>
        <div class="edit-category-form-wrapper">
            <form class="edit-category-form-premium" method="post" enctype="multipart/form-data" autocomplete="off">
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label class="label" for="edit-category-name">Category Name</label>
                    <input id="edit-category-name" type="text" name="name" placeholder="Category Name" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;grid-column:1/-1;">
                    <label class="label" for="edit-category-description">Description</label>
                    <textarea id="edit-category-description" name="description" placeholder="Description" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;grid-column:1/-1;">
                    <label class="label">Current Image</label>
                    <?php if ($currentImage): ?>
                        <img src="<?php echo '../../uploads/categories/' . htmlspecialchars($currentImage); ?>" alt="Category Image" style="max-width:120px;max-height:120px;border-radius:10px;margin-bottom:8px;background:#fff;box-shadow:0 2px 8px rgba(123,97,255,0.10);">
                    <?php else: ?>
                        <span style="color:#aaa;font-size:var(--font-size-sm);">No image uploaded</span>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*">
                </div>
                <button type="submit"><i class="bi bi-save" style="margin-right:7px;"></i>Save Changes</button>
            </form>
        </div>
    </div>
</div>
</body>
</html> 