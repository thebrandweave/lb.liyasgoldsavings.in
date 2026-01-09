<?php
session_start();
if (!isset($_SESSION['shop_admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/config.php';

$db = new Database();
$conn = $db->getConnection();
$products = $conn->query('SELECT p.product_id, p.name, c.name AS category, p.price, p.stock, p.created_at FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.product_id DESC')->fetchAll(PDO::FETCH_ASSOC);

// Fetch images for all products
$productImages = [];
if (!empty($products)) {
    $ids = array_column($products, 'product_id');
    $in = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $conn->prepare('SELECT product_id, image_url FROM product_images WHERE product_id IN (' . $in . ') ORDER BY uploaded_at ASC');
    $stmt->execute($ids);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $img) {
        $productImages[$img['product_id']][] = $img['image_url'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .product-header-row {
            max-width: 1100px;
            margin: 40px auto 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .product-search-bar-wrapper {
            max-width: 1100px;
            margin: 0 auto 18px auto;
        }
        .product-grid-wrapper {
            max-width: 1100px;
            margin: 0 auto;
        }
        .product-title {
            font-size: var(--font-size-lg);
            font-weight: 700;
            color: var(--accent-dark);
            letter-spacing: 1px;
        }
        .product-add-btn {
            background: linear-gradient(90deg,var(--accent) 0%,var(--accent-dark) 100%);
            color: #fff;
            padding: 8px 18px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: var(--font-size-base);
            transition: background var(--transition);
            box-shadow: 0 2px 8px rgba(123,97,255,0.10);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .product-add-btn:hover {
            background: linear-gradient(90deg,var(--accent-light) 0%,var(--accent) 100%);
            color: var(--accent-dark);
        }
        .product-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }
        .product-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            font-size: 1.1rem;
            background: #f7f8fa;
            transition: background var(--transition), color var(--transition);
            box-shadow: 0 1px 4px rgba(123,97,255,0.04);
        }
        .product-actions a[title="Edit"] { color: #ff9800; }
        .product-actions a[title="Delete"] { color: #d32f2f; }
        .product-actions a:hover { background: #eceef3; }
        @media (max-width: 700px) {
            .product-card { padding: 10px 2vw; }
            .product-title { font-size: var(--font-size-base); }
            .product-add-btn { padding: 6px 10px; font-size: var(--font-size-sm); }
            .product-table th, .product-table td { padding: 7px 4px; font-size: var(--font-size-sm); }
        }
        .product-grid-wrapper {
            width: 100%;
            margin-top: 18px;
        }
        .product-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px 22px;
        }
        .product-card-item {
            background: var(--card-bg);
            border-radius: 18px;
            box-shadow: 0 2px 12px rgba(31,38,135,0.07);
            padding: 0 0 18px 0;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            position: relative;
            transition: box-shadow 0.18s, background 0.18s;
            overflow: hidden;
        }
        .product-card-item:hover {
            background: #f7f8fa;
            box-shadow: 0 4px 24px rgba(123,97,255,0.10);
        }
        .product-card-main-image {
            width: 100%;
            aspect-ratio: 4/3;
            background: #f8f7fa;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-top-left-radius: 18px;
            border-top-right-radius: 18px;
            border-bottom: 1.5px solid #ececec;
        }
        .product-main-thumb {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 0;
            box-shadow: none;
            background: #fff;
            display: block;
        }
        .product-card-info {
            width: 100%;
            padding: 18px 18px 0 18px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .product-card-title {
            font-size: 1.13rem;
            font-weight: 700;
            color: var(--accent-dark);
            margin-bottom: 6px;
        }
        .product-card-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 6px;
        }
        .product-card-category {
            color: #7b61ff;
            font-size: 0.98em;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .product-card-price {
            color: #232526;
            font-size: 1.01em;
            font-weight: 600;
        }
        .product-card-stock {
            font-size: 0.98em;
            color: #888;
            margin-bottom: 2px;
        }
        .product-card-date {
            font-size: 0.93em;
            color: #bbb;
            margin-bottom: 10px;
        }
        .product-card-actions {
            display: flex;
            gap: 10px;
            margin-top: 8px;
        }
        .product-card-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            font-size: 1.1rem;
            background: #f7f8fa;
            transition: background var(--transition), color var(--transition);
            box-shadow: 0 1px 4px rgba(123,97,255,0.04);
        }
        .product-card-actions a[title="Edit"] { color: #ff9800; }
        .product-card-actions a[title="Delete"] { color: #d32f2f; }
        .product-card-actions a:hover { background: #eceef3; }
        @media (max-width: 900px) {
            .product-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 700px) {
            .product-grid { grid-template-columns: 1fr; gap: 18px; }
            .product-card-item { padding: 0 0 10px 0; }
            .product-card-info { padding: 12px 6vw 0 6vw; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/sidebar.php'; ?>
<div class="main-content">
    <div class="product-header-row" style="margin-bottom:18px;">
            <div class="product-title"><i class="bi bi-bag" style="margin-right:8px;"></i>Products</div>
        <a href="add.php" class="product-add-btn"><i class="bi bi-plus-lg" style="margin-right:6px;"></i>Add Product</a>
    </div>
    <div class="product-search-bar-wrapper" style="width:100%;display:flex;justify-content:flex-start;align-items:center;gap:14px;margin-bottom:18px;">
        <select id="product-category-filter" class="product-category-filter" onchange="filterProducts()" style="height:40px;border-radius:999px;border:none;background:#fff;box-shadow:0 2px 12px rgba(123,97,255,0.06);font-size:1rem;color:var(--accent);padding:0 24px 0 18px;font-family:'Montserrat',Arial,sans-serif;transition:box-shadow 0.18s;">
            <option value="All">All Categories</option>
            <?php
            $catRes = $conn->query('SELECT category_id, name FROM categories ORDER BY name ASC');
            foreach ($catRes as $catOpt) {
                echo '<option value="'.htmlspecialchars($catOpt['name']).'">'.htmlspecialchars($catOpt['name']).'</option>';
            }
            ?>
        </select>
        <div class="product-search-bar" style="width:100%;max-width:800px;position:relative;display:flex;align-items:center;background:#fff;border-radius:999px;box-shadow:0 2px 12px rgba(123,97,255,0.06);padding:0 0 0 34px;height:40px;">
            <i class="bi bi-search" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#7b61ff;font-size:1.2em;pointer-events:none;"></i>
            <input id="product-search-input" type="text" placeholder="Search products..." onkeyup="filterProducts()" style="width:100%;border:none;outline:none;background:transparent;font-size:1rem;color:#232526;padding:0 14px 0 0;height:20px;border-radius:999px;font-family:'Montserrat',Arial,sans-serif;margin-left:10px;" />
        </div>
        <div style="flex:1;"></div>
        <button class="product-clear-btn" onclick="clearProductFilters()" title="Clear filters" style="background:transparent;color:var(--accent);border-radius:12px;font-size:var(--font-size-base);font-weight:600;padding:8px 18px;border:2px solid var(--accent);box-shadow:none;display:flex;align-items:center;gap:7px;cursor:pointer;transition:background var(--transition),color var(--transition),box-shadow var(--transition),border var(--transition);"><i class="bi bi-x-circle"></i>Clear</button>
    </div>
    <div class="product-grid-wrapper">
        <div class="product-grid">
                <?php foreach ($products as $prod):
                    $imgs = $productImages[$prod['product_id']] ?? [];
                    $maxThumbs = 3;
                    $cardName = strtolower(htmlspecialchars($prod['name']));
                    $cardCat = strtolower(htmlspecialchars($prod['category']));
                ?>
                <div class="product-card-item" data-name="<?php echo $cardName; ?>" data-category="<?php echo $cardCat; ?>">
                    <div class="product-card-main-image">
                        <?php if (count($imgs) > 0): ?>
                            <img src="<?php echo '../../uploads/products/' . htmlspecialchars(basename($imgs[0])); ?>" alt="" class="product-main-thumb">
                        <?php else: ?>
                            <img src="../assets/images/no-image.png" alt="No image" class="product-main-thumb">
                        <?php endif; ?>
                    </div>
                    <div class="product-card-info">
                        <div class="product-card-title"><?php echo htmlspecialchars($prod['name']); ?></div>
                        <div class="product-card-meta">
                            <span class="product-card-category"><i class="bi bi-tag"></i> <?php echo htmlspecialchars($prod['category']); ?></span>
                            <span class="product-card-price">₹<?php echo number_format($prod['price'],2); ?></span>
                        </div>
                        <div class="product-card-stock">Stock: <b><?php echo $prod['stock']; ?></b></div>
                        <div class="product-card-date">Added: <?php echo date('d M Y', strtotime($prod['created_at'])); ?></div>
                        <div class="product-card-actions">
                            <a href="view.php?id=<?php echo $prod['product_id']; ?>" title="View"><i class="bi bi-eye"></i></a>
                            <a href="edit.php?id=<?php echo $prod['product_id']; ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                            <a href="delete.php?id=<?php echo $prod['product_id']; ?>" title="Delete" onclick="return confirm('Are you sure you want to delete this product? This will remove all images as well.');"><i class="bi bi-trash"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                    <div style="text-align:center; color:#aaa; padding:24px;">No products found</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
function filterProducts() {
    var input = document.getElementById('product-search-input');
    var filter = input.value.toLowerCase();
    var category = document.getElementById('product-category-filter').value.toLowerCase();
    var cards = document.querySelectorAll('.product-card-item');
    cards.forEach(function(card) {
        var name = card.getAttribute('data-name');
        var cat = card.getAttribute('data-category');
        var matchCat = (category === 'all') || (cat === category);
        if ((name.includes(filter) || cat.includes(filter)) && matchCat) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}
function clearProductFilters() {
    document.getElementById('product-search-input').value = '';
    document.getElementById('product-category-filter').value = 'All';
    filterProducts();
}
</script>
</body>
</html> 