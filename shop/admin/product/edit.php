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
$product_id = (int)$_GET['id'];
$db = new Database();
$conn = $db->getConnection();
$categories = $conn->query('SELECT category_id, name FROM categories ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
$stmt = $conn->prepare('SELECT * FROM products WHERE product_id = ?');
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    header('Location: index.php');
    exit();
}
$images = $conn->prepare('SELECT image_url, image_id FROM product_images WHERE product_id = ? ORDER BY uploaded_at ASC');
$images->execute([$product_id]);
$images = $images->fetchAll(PDO::FETCH_ASSOC);
$name = $product['name'];
$category_id = $product['category_id'];
$price = $product['price'];
$stock = $product['stock'];
$description = $product['description'];
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($name && $category_id && is_numeric($price) && is_numeric($stock)) {
        $stmt = $conn->prepare('UPDATE products SET name=?, category_id=?, price=?, stock=?, description=? WHERE product_id=?');
        if ($stmt->execute([$name, $category_id, $price, $stock, $description, $product_id])) {
            // Handle image deletions
            if (!empty($_POST['delete_image'])) {
                foreach ($_POST['delete_image'] as $imgId) {
                    $imgRow = $conn->prepare('SELECT image_url FROM product_images WHERE image_id=? AND product_id=?');
                    $imgRow->execute([$imgId, $product_id]);
                    $imgPath = $imgRow->fetchColumn();
                    if ($imgPath && file_exists(__DIR__ . '/../../uploads/products/' . basename($imgPath))) {
                        unlink(__DIR__ . '/../../uploads/products/' . basename($imgPath));
                    }
                    $conn->prepare('DELETE FROM product_images WHERE image_id=? AND product_id=?')->execute([$imgId, $product_id]);
                }
            }
            // Handle new image uploads
            $imagesUp = $_FILES['images'] ?? null;
            if ($imagesUp && isset($imagesUp['tmp_name']) && is_array($imagesUp['tmp_name'])) {
                $uploadDir = __DIR__ . '/../../uploads/products/';
                if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
                for ($i = 0; $i < count($imagesUp['tmp_name']); $i++) {
                    if ($imagesUp['error'][$i] === UPLOAD_ERR_OK && is_uploaded_file($imagesUp['tmp_name'][$i])) {
                        $ext = pathinfo($imagesUp['name'][$i], PATHINFO_EXTENSION);
                        $fname = 'product_' . $product_id . '_' . uniqid() . '.' . $ext;
                        $dest = $uploadDir . $fname;
                        if (move_uploaded_file($imagesUp['tmp_name'][$i], $dest)) {
                            $stmtImg = $conn->prepare('INSERT INTO product_images (product_id, image_url) VALUES (?, ?)');
                            $stmtImg->execute([$product_id, $fname]);
                        }
                    }
                }
            }
            header('Location: index.php?updated=1');
            exit();
        } else {
            $error = 'Failed to update product.';
        }
    } else {
        $error = 'All fields are required and must be valid.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body, html { background: var(--primary-bg); font-family: var(--font-main); font-size: var(--font-size-base); color: var(--text-main); }
        .edit-product-container { max-width: 1100px; margin: 40px auto 0 auto; padding: 0 16px; }
        .edit-product-header-row { width: 100%; max-width: 700px; margin: 0 auto 0 auto; display: flex; align-items: center; justify-content: space-between; min-height: 60px; gap: 18px; }
        .edit-product-title { display: flex; align-items: center; gap: 14px; color: var(--accent-dark); letter-spacing: 1px; }
        .section-title { font-size: var(--font-size-lg); font-weight: 700; color: var(--accent-dark); letter-spacing: 1px; }
        .edit-product-avatar {
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.2rem;
            height: 40px;
            width: 40px;
        }
        .edit-product-back { display: inline-flex; align-items: center; gap: 7px; color: #fff; text-decoration: none; font-weight: 600; background: linear-gradient(90deg, var(--accent) 0%, var(--accent-dark) 100%); border-radius: 12px; padding: 10px 28px; box-shadow: 0 2px 8px rgba(123,97,255,0.10); font-size: var(--font-size-base); border: none; transition: background 0.18s, color 0.18s, box-shadow 0.18s; }
        .edit-product-back:hover { background: linear-gradient(90deg, var(--accent-dark) 0%, var(--accent) 100%); color: #fff; text-decoration: none; }
        .edit-product-carousel { width: 100%; max-width: 520px; margin: 0 auto 24px auto; position: relative; aspect-ratio: 4/3; background: var(--card-bg); border-radius: 18px; box-shadow: var(--card-shadow); display: flex; align-items: center; justify-content: center; overflow: hidden; backdrop-filter: var(--glass-blur); -webkit-backdrop-filter: var(--glass-blur); }
        .carousel-image { width: 100%; height: 100%; object-fit: cover; border-radius: 0; background: #fff; display: block; transition: opacity 0.3s; }
        .carousel-arrow { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.85); border: none; border-radius: 50%; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; color: #7b61ff; font-size: 1.5em; cursor: pointer; box-shadow: 0 2px 8px rgba(123,97,255,0.08); z-index: 2; transition: background 0.15s; }
        .carousel-arrow:hover { background: #ececec; }
        .carousel-arrow.left { left: 12px; }
        .carousel-arrow.right { right: 12px; }
        .carousel-indicators { position: absolute; bottom: 12px; left: 50%; transform: translateX(-50%); display: flex; gap: 7px; }
        .carousel-indicator { width: 11px; height: 11px; border-radius: 50%; background: #d1c4e9; cursor: pointer; transition: background 0.18s; }
        .carousel-indicator.active { background: #7b61ff; }
        .section-divider { width: 100%; height: 1.5px; background: rgba(123,97,255,0.10); margin: 18px 0 18px 0; border-radius: 2px; }
        .edit-product-form-premium { width: 100%; max-width: 700px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 22px 24px; margin-top: 0; background: none; border-radius: 0; box-shadow: none; padding: 0; }
        .edit-product-form-premium input, .edit-product-form-premium select, .edit-product-form-premium textarea { width: 100%; padding: 13px 16px 11px 16px; border-radius: 12px; border: 1.5px solid #ececec; background: #f8f7fa; color: var(--accent-dark); font-size: var(--font-size-base); font-family: var(--font-main); transition: border var(--transition), box-shadow var(--transition); box-shadow: 0 2px 8px rgba(123,97,255,0.04); }
        .edit-product-form-premium input:focus, .edit-product-form-premium select:focus, .edit-product-form-premium textarea:focus { border: 1.5px solid var(--accent); outline: none; box-shadow: 0 2px 8px rgba(123,97,255,0.10); }
        .edit-product-form-premium select { appearance: none; -webkit-appearance: none; -moz-appearance: none; background: #f8f7fa url('data:image/svg+xml;utf8,<svg fill=\"%237b61ff\" height=\"20\" viewBox=\"0 0 20 20\" width=\"20\" xmlns=\"http://www.w3.org/2000/svg\"><path d=\"M7.293 7.293a1 1 0 011.414 0L10 8.586l1.293-1.293a1 1 0 111.414 1.414l-2 2a1 1 0 01-1.414 0l-2-2a1 1 0 010-1.414z\"/></svg>') no-repeat right 1.2em center/1.1em auto; padding-right: 2.5em; font-weight: 600; }
        .edit-product-form-premium textarea { grid-column: 1 / -1; resize: vertical; min-height: 80px; }
        .edit-product-form-premium .image-preview-list { grid-column: 1 / -1; display: flex; flex-wrap: wrap; gap: 12px; margin-top: 10px; margin-bottom: 8px; }
        .edit-product-form-premium .image-preview-item { position: relative; width: 70px; height: 70px; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(123,97,255,0.08); background: #fff; border: 1.5px solid #ececec; }
        .edit-product-form-premium .image-preview-item img { width: 100%; height: 100%; object-fit: cover; border-radius: 10px; }
        .edit-product-form-premium .image-remove-btn { position: absolute; top: 2px; right: 2px; background: rgba(255,255,255,0.85); border: none; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; color: #d32f2f; font-size: 1.1em; cursor: pointer; box-shadow: 0 1px 4px rgba(0,0,0,0.08); transition: background 0.15s; }
        .edit-product-form-premium .image-remove-btn:hover { background: #ffeaea; }
        .edit-product-form-premium button { grid-column: 1 / -1; padding: 13px 0; border-radius: 12px; background: linear-gradient(90deg, var(--accent) 0%, var(--accent-dark) 100%); color: #fff; font-size: var(--font-size-base); font-weight: 700; border: none; box-shadow: 0 2px 8px rgba(123,97,255,0.10); transition: background var(--transition), color var(--transition), box-shadow var(--transition); margin-top: 8px; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .edit-product-form-premium button:hover { background: linear-gradient(90deg, var(--accent-light) 0%, var(--accent) 100%); color: var(--accent-dark); box-shadow: 0 4px 16px rgba(94,234,212,0.10); }
        .edit-product-message-premium { max-width:420px; margin:0 auto 18px auto; display:flex; align-items:center; justify-content:center; gap:10px; background:#fff0f0; color:#d32f2f; padding:14px 0 14px 0; border-radius:10px; text-align:center; font-size:1.08rem; box-shadow:0 2px 12px rgba(211,47,47,0.08); font-weight:600; transition:opacity 0.5s; }
        @media (max-width: 700px) { .edit-product-header-row { flex-direction: column; align-items: flex-start; gap: 10px; } .edit-product-form-premium { grid-template-columns: 1fr; gap: 16px; } }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/sidebar.php'; ?>
<div class="main-content">
    <div class="edit-product-container">
        <div class="edit-product-header-row">
            <div class="edit-product-title">
                <span class="edit-product-avatar"><i class="bi bi-bag"></i></span>
                <span class="section-title">Edit Product</span>
            </div>
            <a href="index.php" class="edit-product-back"><i class="bi bi-arrow-left"></i> Back to Products</a>
        </div>
        <div class="edit-product-carousel" id="carousel">
            <?php if ($images && count($images) > 0): ?>
                <?php foreach ($images as $i => $img): ?>
                    <img src="<?php echo '../../uploads/products/' . htmlspecialchars(basename($img['image_url'])); ?>" alt="Product image" class="carousel-image" style="display:<?php echo $i === 0 ? 'block' : 'none'; ?>;">
                <?php endforeach; ?>
            <?php else: ?>
                <img src="../assets/images/no-image.png" alt="No image" class="carousel-image" style="display:block;">
            <?php endif; ?>
            <button class="carousel-arrow left" id="carouselPrev" style="display:none;"><i class="bi bi-chevron-left"></i></button>
            <button class="carousel-arrow right" id="carouselNext" style="display:none;"><i class="bi bi-chevron-right"></i></button>
            <div class="carousel-indicators" id="carouselIndicators"></div>
        </div>
        <div class="section-divider"></div>
        <?php if ($error): ?>
            <div class="edit-product-message-premium"><i class="bi bi-exclamation-circle-fill" style="font-size:1.2em;"></i> <?php echo $error; ?></div>
        <?php endif; ?>
        <form class="edit-product-form-premium" method="post" autocomplete="off" enctype="multipart/form-data">
                <div style="display:flex;flex-direction:column;gap:4px;">
                <label class="label" for="edit-name">Product Name</label>
                <input id="edit-name" type="text" name="name" placeholder="Product Name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label class="label" for="edit-category">Category</label>
                <select id="edit-category" name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>" <?php if($category_id==$cat['category_id']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label class="label" for="edit-price">Price</label>
                <input id="edit-price" type="number" name="price" placeholder="Price" min="0" step="0.01" value="<?php echo htmlspecialchars($price); ?>" required>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label class="label" for="edit-stock">Stock</label>
                <input id="edit-stock" type="number" name="stock" placeholder="Stock" min="0" step="1" value="<?php echo htmlspecialchars($stock); ?>" required>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;grid-column:1/-1;">
                <label class="label" for="edit-description">Description</label>
                <textarea id="edit-description" name="description" placeholder="Description" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
            </div>
            <div class="image-preview-list">
                <?php foreach ($images as $img): ?>
                    <div class="image-preview-item">
                        <img src="<?php echo '../../uploads/products/' . htmlspecialchars(basename($img['image_url'])); ?>" alt="Product image">
                        <button class="image-remove-btn" name="delete_image[]" value="<?php echo $img['image_id']; ?>" type="submit" onclick="return confirm('Remove this image?');"><i class="bi bi-x"></i></button>
                    </div>
                <?php endforeach; ?>
            </div>
            <input type="file" name="images[]" accept="image/*" multiple style="grid-column:1/-1;">
            <button type="submit"><i class="bi bi-save" style="margin-right:7px;"></i>Save Changes</button>
        </form>
    </div>
</div>
</body>
</html> 