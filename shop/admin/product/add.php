<?php
session_start();
if (!isset($_SESSION['shop_admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/config.php';

$db = new Database();
$conn = $db->getConnection();
$categories = $conn->query('SELECT category_id, name FROM categories ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);

$name = $price = $stock = $description = '';
$category_id = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $images = $_FILES['images'] ?? null;
    if ($name && $category_id && is_numeric($price) && is_numeric($stock)) {
        $stmt = $conn->prepare('INSERT INTO products (name, category_id, price, stock, description, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        if ($stmt->execute([$name, $category_id, $price, $stock, $description])) {
            $product_id = $conn->lastInsertId();
            // Handle image uploads
            if ($images && isset($images['tmp_name']) && is_array($images['tmp_name'])) {
                $uploadDir = __DIR__ . '/../../uploads/products/';
                if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
                for ($i = 0; $i < count($images['tmp_name']); $i++) {
                    if ($images['error'][$i] === UPLOAD_ERR_OK && is_uploaded_file($images['tmp_name'][$i])) {
                        $ext = pathinfo($images['name'][$i], PATHINFO_EXTENSION);
                        $fname = 'product_' . $product_id . '_' . uniqid() . '.' . $ext;
                        $dest = $uploadDir . $fname;
                        if (move_uploaded_file($images['tmp_name'][$i], $dest)) {
                            $stmtImg = $conn->prepare('INSERT INTO product_images (product_id, image_url) VALUES (?, ?)');
                            $stmtImg->execute([$product_id, $fname]);
                        }
                    }
                }
            }
            header('Location: index.php?success=1');
            exit();
        } else {
            $error = 'Failed to add product.';
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
    <title>Add Product</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body, html { background: var(--primary-bg); }
        .add-product-container {
            max-width: 900px;
            margin: 48px auto 0 auto;
            padding: 0 16px;
        }
        .add-product-card-premium {
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
        .add-product-header-row {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }
        .add-product-title {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--accent-dark);
            letter-spacing: 1px;
        }
        .add-product-avatar {
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
        .add-product-back {
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
        .add-product-back:hover {
            background: linear-gradient(90deg, var(--accent-dark) 0%, var(--accent) 100%);
            color: #fff;
            text-decoration: none;
        }
        .add-product-form-premium {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px 24px;
            margin-top: 18px;
        }
        .add-product-form-premium input,
        .add-product-form-premium select,
        .add-product-form-premium textarea {
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
        .add-product-form-premium input:focus,
        .add-product-form-premium select:focus,
        .add-product-form-premium textarea:focus {
            border: 1.5px solid var(--accent);
            outline: none;
            box-shadow: 0 2px 8px rgba(123,97,255,0.10);
        }
        .add-product-form-premium select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: #f8f7fa url('data:image/svg+xml;utf8,<svg fill="%237b61ff" height="20" viewBox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M7.293 7.293a1 1 0 011.414 0L10 8.586l1.293-1.293a1 1 0 111.414 1.414l-2 2a1 1 0 01-1.414 0l-2-2a1 1 0 010-1.414z"/></svg>') no-repeat right 1.2em center/1.1em auto;
            padding-right: 2.5em;
            font-weight: 600;
        }
        .add-product-form-premium textarea {
            grid-column: 1 / -1;
            resize: vertical;
            min-height: 80px;
        }
        .add-product-form-premium button {
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
        .add-product-form-premium button:hover {
            background: linear-gradient(90deg, var(--accent-light) 0%, var(--accent) 100%);
            color: var(--accent-dark);
            box-shadow: 0 4px 16px rgba(94,234,212,0.10);
        }
        .add-product-message-premium {
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
        .image-upload-area {
            grid-column: 1 / -1;
            background: #f8f7fa;
            border: 2px dashed #d1c4e9;
            border-radius: 14px;
            padding: 28px 18px;
            text-align: center;
            cursor: pointer;
            margin-bottom: 8px;
            transition: border-color 0.2s;
            position: relative;
        }
        .image-upload-area.dragover {
            border-color: #7b61ff;
            background: #f3f0fa;
        }
        .image-upload-area input[type="file"] {
            display: none;
        }
        .image-upload-label {
            color: #7b61ff;
            font-weight: 700;
            font-size: 1.08rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .image-preview-list {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 10px;
            margin-bottom: 8px;
            user-select: none;
        }
        .image-preview-item {
            position: relative;
            width: 70px;
            height: 70px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(123,97,255,0.08);
            background: #fff;
            border: 1.5px solid #ececec;
            cursor: grab;
        }
        .image-preview-item.dragging {
            opacity: 0.5;
        }
        .image-preview-item.dragover {
            border: 2px dashed #7b61ff;
        }
        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
        }
        .image-remove-btn {
            position: absolute;
            top: 2px;
            right: 2px;
            background: rgba(255,255,255,0.85);
            border: none;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #d32f2f;
            font-size: 1.1em;
            cursor: pointer;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            transition: background 0.15s;
        }
        .image-remove-btn:hover {
            background: #ffeaea;
        }
        @media (max-width: 700px) {
            .add-product-card-premium { padding: 14px 2vw 8px 2vw; }
            .add-product-header-row { flex-direction: column; align-items: flex-start; gap: 10px; }
            .add-product-form-premium { grid-template-columns: 1fr; gap: 16px; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/sidebar.php'; ?>
<div class="main-content">
    <div class="add-product-container">
        <div class="add-product-card-premium">
            <div class="add-product-header-row">
                <div class="add-product-title">
                    <span class="add-product-avatar"><i class="bi bi-bag"></i></span>
                    Add Product
                </div>
                <a href="index.php" class="add-product-back"><i class="bi bi-arrow-left"></i> Back to Products</a>
            </div>
            <?php if ($error): ?>
                <div class="add-product-message-premium"><i class="bi bi-exclamation-circle-fill" style="font-size:1.2em;"></i> <?php echo $error; ?></div>
            <?php endif; ?>
            <form class="add-product-form-premium" method="post" autocomplete="off" enctype="multipart/form-data" id="add-product-form">
                <input type="text" name="name" placeholder="Product Name" value="<?php echo htmlspecialchars($name); ?>" required>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>" <?php if($category_id==$cat['category_id']) echo 'selected'; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="price" placeholder="Price" min="0" step="0.01" value="<?php echo htmlspecialchars($price); ?>" required>
                <input type="number" name="stock" placeholder="Stock" min="0" step="1" value="<?php echo htmlspecialchars($stock); ?>" required>
                <textarea name="description" placeholder="Description" rows="3"><?php echo htmlspecialchars($description); ?></textarea>
                <div class="image-upload-area" id="image-upload-area">
                    <span class="image-upload-label"><i class="bi bi-cloud-arrow-up" style="font-size:1.3em;"></i> Drag & drop images here or click to select</span>
                    <input type="file" id="product-images-input" name="images[]" accept="image/*" multiple>
                </div>
                <div class="image-preview-list" id="image-preview-list"></div>
                <button type="submit"><i class="bi bi-plus-circle" style="margin-right:7px;"></i>Add Product</button>
            </form>
        </div>
    </div>
</div>
<script>
    // Image upload UI logic
    const uploadArea = document.getElementById('image-upload-area');
    const fileInput = document.getElementById('product-images-input');
    const previewList = document.getElementById('image-preview-list');
    let filesArr = [];
    let dragIdx = null;

    uploadArea.addEventListener('click', () => fileInput.click());
    uploadArea.addEventListener('dragover', e => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    uploadArea.addEventListener('dragleave', e => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
    });
    uploadArea.addEventListener('drop', e => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    });
    fileInput.addEventListener('change', e => {
        handleFiles(e.target.files);
    });

    function handleFiles(files) {
        for (let i = 0; i < files.length; i++) {
            if (!filesArr.some(f => f.name === files[i].name && f.size === files[i].size)) {
                filesArr.push(files[i]);
            }
        }
        renderPreviews();
    }

    function renderPreviews() {
        previewList.innerHTML = '';
        filesArr.forEach((file, idx) => {
            const item = document.createElement('div');
            item.className = 'image-preview-item';
            item.draggable = true;
            item.setAttribute('data-index', idx);
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            item.appendChild(img);
            const btn = document.createElement('button');
            btn.className = 'image-remove-btn';
            btn.innerHTML = '<i class="bi bi-x"></i>';
            btn.onclick = e => {
                e.preventDefault();
                filesArr.splice(idx, 1);
                renderPreviews();
            };
            item.appendChild(btn);
            // Robust Drag and Drop events
            item.addEventListener('dragstart', e => {
                dragIdx = idx;
                item.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
            });
            item.addEventListener('dragend', e => {
                dragIdx = null;
                item.classList.remove('dragging');
                document.querySelectorAll('.image-preview-item.dragover').forEach(el => el.classList.remove('dragover'));
            });
            item.addEventListener('dragover', e => {
                e.preventDefault();
                if (dragIdx !== null && dragIdx !== idx) {
                    item.classList.add('dragover');
                }
            });
            item.addEventListener('dragleave', e => {
                item.classList.remove('dragover');
            });
            item.addEventListener('drop', e => {
                e.preventDefault();
                item.classList.remove('dragover');
                if (dragIdx !== null && dragIdx !== idx) {
                    const moved = filesArr.splice(dragIdx, 1)[0];
                    filesArr.splice(idx, 0, moved);
                    renderPreviews();
                }
                dragIdx = null;
                document.querySelectorAll('.image-preview-item.dragover').forEach(el => el.classList.remove('dragover'));
            });
            previewList.appendChild(item);
        });
        // Update the file input files
        const dataTransfer = new DataTransfer();
        filesArr.forEach(f => dataTransfer.items.add(f));
        fileInput.files = dataTransfer.files;
    }
</script>
</body>
</html> 