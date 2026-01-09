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
$stmt = $conn->prepare('SELECT p.*, c.name AS category FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = ?');
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    header('Location: index.php');
    exit();
}
$images = $conn->prepare('SELECT image_url FROM product_images WHERE product_id = ? ORDER BY uploaded_at ASC');
$images->execute([$product_id]);
$images = $images->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Product</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body, html {
            background: var(--primary-bg);
            font-family: var(--font-main);
            font-size: var(--font-size-base);
            color: var(--text-main);
        }
        .view-product-container {
            max-width: 1500px;
            margin: 40px auto 0 auto;
            padding: 0 16px;
        }
        .section-title {
            font-size: var(--font-size-lg);
            font-weight: 700;
            color: var(--accent-dark);
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        .view-product-avatar {
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: var(--font-size-lg);
            height: var(--font-size-lg);
            width: var(--font-size-lg);
            margin-bottom: -2px;
        }
        .section-divider {
            width: 100%;
            height: 1.5px;
            background: rgba(123,97,255,0.10);
            margin: 18px 0 18px 0;
            border-radius: 2px;
        }
        .view-product-carousel {
            width: 100%;
            max-width: 520px;
            margin: 0 auto 24px auto;
            position: relative;
            aspect-ratio: 4/3;
            background: var(--card-bg);
            border-radius: 18px;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
        }
        .carousel-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 0;
            background: #fff;
            display: block;
            transition: opacity 0.3s;
        }
        .carousel-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.85);
            border: none;
            border-radius: 50%;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #7b61ff;
            font-size: 1.5em;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(123,97,255,0.08);
            z-index: 2;
            transition: background 0.15s;
        }
        .carousel-arrow:hover {
            background: #ececec;
        }
        .carousel-arrow.left { left: 12px; }
        .carousel-arrow.right { right: 12px; }
        .carousel-indicators {
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 7px;
        }
        .carousel-indicator {
            width: 11px;
            height: 11px;
            border-radius: 50%;
            background: #d1c4e9;
            cursor: pointer;
            transition: background 0.18s;
        }
        .carousel-indicator.active {
            background: #7b61ff;
        }
        .view-product-details-premium {
            width: 100%;
            margin: 0 auto;
            max-width: 700px;
            margin-top: 8px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px 24px;
            background: none;
            border-radius: 0;
            box-shadow: none;
            padding: 0;
        }
        .view-product-details-premium .label {
            font-weight: 700;
            color: var(--accent-dark);
            margin-bottom: 4px;
            font-size: 1.08rem;
        }
        .view-product-details-premium .value {
            color: #232526;
            font-size: 1.08rem;
            margin-bottom: 10px;
        }
        .view-product-container, .view-product-header-row, .view-product-title, .view-product-details-premium, .view-product-details-premium .label, .view-product-details-premium .value {
            font-family: var(--font-main);
            font-size: var(--font-size-base);
        }
        .view-product-header-row {
            width: 100%;
            max-width: 700px;
            margin: 0 auto 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 60px;
            gap: 18px;
        }
        .view-product-title {
            display: flex;
            align-items: baseline;
            gap: 12px;
        }
        .section-title {
            display: flex;
            align-items: center;
            font-size: var(--font-size-lg);
            font-weight: 700;
            color: var(--accent-dark);
            letter-spacing: 1px;
            margin-left: 2px;
        }
        .view-product-back {
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
        .view-product-back:hover {
            background: linear-gradient(90deg, var(--accent-dark) 0%, var(--accent) 100%);
            color: #fff;
            text-decoration: none;
        }
        @media (max-width: 700px) {
            .view-product-header-row { flex-direction: column; align-items: flex-start; gap: 10px; }
            .view-product-details-premium { grid-template-columns: 1fr; gap: 16px; }
            .view-product-carousel { max-width: 98vw; aspect-ratio: 1/1; }
            .carousel-arrow { width: 32px; height: 32px; font-size: 1.1em; }
            .carousel-indicator { width: 8px; height: 8px; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/sidebar.php'; ?>
<div class="main-content">
    <div class="view-product-container">
        <div class="view-product-header-row">
            <div class="view-product-title">
                <span class="view-product-avatar"><i class="bi bi-bag"></i></span>
                <span class="section-title">Product Details</span>
            </div>
            <a href="index.php" class="view-product-back"><i class="bi bi-arrow-left"></i> Back to Products</a>
        </div>
        <div class="view-product-carousel" id="carousel">
            <?php if ($images && count($images) > 0): ?>
                <?php foreach ($images as $i => $img): ?>
                    <img src="<?php echo '../../uploads/products/' . htmlspecialchars(basename($img)); ?>" alt="Product image" class="carousel-image" style="display:<?php echo $i === 0 ? 'block' : 'none'; ?>;">
                <?php endforeach; ?>
            <?php else: ?>
                <img src="../assets/images/no-image.png" alt="No image" class="carousel-image" style="display:block;">
            <?php endif; ?>
            <button class="carousel-arrow left" id="carouselPrev" style="display:none;"><i class="bi bi-chevron-left"></i></button>
            <button class="carousel-arrow right" id="carouselNext" style="display:none;"><i class="bi bi-chevron-right"></i></button>
            <div class="carousel-indicators" id="carouselIndicators"></div>
        </div>
        <div class="section-divider"></div>
        <div class="view-product-details-premium">
                <div>
                    <div class="label">Name</div>
                    <div class="value"><?php echo htmlspecialchars($product['name']); ?></div>
                </div>
                <div>
                    <div class="label">Category</div>
                    <div class="value"><?php echo htmlspecialchars($product['category']); ?></div>
                </div>
                <div>
                    <div class="label">Price</div>
                    <div class="value">₹<?php echo number_format($product['price'],2); ?></div>
                </div>
                <div>
                    <div class="label">Stock</div>
                    <div class="value"><?php echo $product['stock']; ?></div>
                </div>
                <div style="grid-column:1/-1;">
                    <div class="label">Description</div>
                    <div class="value"><?php echo nl2br(htmlspecialchars($product['description'])); ?></div>
                </div>
                <div style="grid-column:1/-1;">
                    <div class="label">Created</div>
                    <div class="value"><?php echo date('d M Y', strtotime($product['created_at'])); ?></div>
                </div>
            </div>
    </div>
</div>
<script>
    // Simple JS carousel
    const images = Array.from(document.querySelectorAll('.carousel-image'));
    const prevBtn = document.getElementById('carouselPrev');
    const nextBtn = document.getElementById('carouselNext');
    const indicators = document.getElementById('carouselIndicators');
    let current = 0;
    if (images.length > 1) {
        prevBtn.style.display = nextBtn.style.display = 'flex';
        images.forEach((img, i) => {
            img.style.display = i === 0 ? 'block' : 'none';
        });
        // Indicators
        for (let i = 0; i < images.length; i++) {
            const dot = document.createElement('div');
            dot.className = 'carousel-indicator' + (i === 0 ? ' active' : '');
            dot.onclick = () => showImg(i);
            indicators.appendChild(dot);
        }
    }
    function showImg(idx) {
        images[current].style.display = 'none';
        indicators.children[current].classList.remove('active');
        current = idx;
        images[current].style.display = 'block';
        indicators.children[current].classList.add('active');
    }
    if (prevBtn && nextBtn) {
        prevBtn.onclick = () => showImg((current - 1 + images.length) % images.length);
        nextBtn.onclick = () => showImg((current + 1) % images.length);
    }
</script>
</body>
</html> 