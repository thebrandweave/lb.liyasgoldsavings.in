<?php
session_start();
require_once __DIR__ . '/../config/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}
$product_id = (int)$_GET['id'];

$db = new Database();
$conn = $db->getConnection();

// Fetch product details
$stmt = $conn->prepare('SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = ?');
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: index.php?notfound=1');
    exit();
}

// Fetch product images
$stmt = $conn->prepare('SELECT image_url FROM product_images WHERE product_id = ? ORDER BY uploaded_at ASC');
$stmt->execute([$product_id]);
$images = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['name']) ?> - Product Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .product-details-container {
            max-width: 1200px;
            margin: 40px auto 60px auto;
            padding: 0 20px;
        }
        .product-details-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
        }
        .product-gallery {
            position: relative;
        }
        .zoom-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255,255,255,0.9);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            transition: all 0.3s ease;
        }
        .zoom-icon:hover {
            background: rgba(255,255,255,1);
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 16px;
            margin-bottom: 20px;
            background: #f8f9fa;
            cursor: zoom-in;
            transition: transform 0.3s ease;
        }
        .main-image.zoomed {
            transform: scale(1.5);
            cursor: zoom-out;
        }
        /* Zoom Modal */
        .zoom-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            cursor: zoom-out;
        }
        .zoom-modal img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            border-radius: 8px;
        }
        .zoom-close {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        .zoom-close:hover {
            background: rgba(0,0,0,0.8);
            transform: scale(1.1);
        }
        .thumbnail-gallery {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.3s;
        }
        .thumbnail.active {
            border-color: var(--accent);
        }
        .product-info {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .product-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--secondary);
            line-height: 1.2;
        }
        .price-section {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 8px;
        }
        .current-price {
            font-size: 2rem;
            font-weight: 800;
            color: #28a745;
        }
        .product-description {
            color: #666;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 8px;
        }
        .quantity-section {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }
        .quantity-input {
            width: 80px;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            text-align: center;
            font-size: 1rem;
        }
        .action-buttons {
            display: flex;
            gap: 16px;
            margin-bottom: 32px;
        }
        .add-to-cart-btn {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: var(--secondary);
            border: none;
            padding: 16px 32px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
            box-shadow: 0 4px 12px rgba(255, 214, 0, 0.3);
        }
        .add-to-cart-btn:hover {
            background: linear-gradient(135deg, var(--accent-dark) 0%, #e6a800 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 214, 0, 0.4);
        }
        .wishlist-btn, .share-btn {
            background: white;
            border: 2px solid #ddd;
            color: #666;
            padding: 16px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.2rem;
        }
        .wishlist-btn:hover, .share-btn:hover {
            border-color: var(--accent);
            color: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 214, 0, 0.2);
        }
        .product-meta {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .meta-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .meta-label {
            color: #666;
            font-weight: 500;
        }
        .meta-value {
            color: var(--secondary);
            font-weight: 600;
        }
        .tabs-section {
            margin-top: 40px;
        }
        .tabs {
            display: flex;
            border-bottom: 2px solid #eee;
            margin-bottom: 24px;
        }
        .tab {
            padding: 16px 24px;
            background: none;
            border: none;
            color: #666;
            font-weight: 600;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        .tab.active {
            color: var(--accent);
            border-bottom-color: var(--accent);
        }
        .tab-content {
            color: #666;
            line-height: 1.6;
        }
        @media (max-width: 900px) {
            .product-details-layout {
                grid-template-columns: 1fr;
                gap: 40px;
            }
            .meta-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php include '../components/navbar.php'; ?>

<div class="product-details-container">
    <div class="product-details-layout">
        <div class="product-gallery">
            <div class="zoom-icon">
                <i class="bi bi-zoom-in"></i>
            </div>
            <img src="<?= $images ? '../uploads/products/' . htmlspecialchars(basename($images[0])) : 'https://via.placeholder.com/500x400?text=No+Image' ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?>" 
                 class="main-image" id="mainImage">
            
            <div class="thumbnail-gallery">
                <?php if ($images): ?>
                    <?php foreach (array_slice($images, 0, 4) as $index => $img): ?>
                        <img src="../uploads/products/<?= htmlspecialchars(basename($img)) ?>" 
                             alt="Thumbnail <?= $index + 1 ?>" 
                             class="thumbnail <?= $index === 0 ? 'active' : '' ?>"
                             onclick="changeMainImage(this.src)">
                    <?php endforeach; ?>
                <?php else: ?>
                    <img src="https://via.placeholder.com/80x80?text=1" class="thumbnail active">
                    <img src="https://via.placeholder.com/80x80?text=2" class="thumbnail">
                    <img src="https://via.placeholder.com/80x80?text=3" class="thumbnail">
                    <img src="https://via.placeholder.com/80x80?text=4" class="thumbnail">
                <?php endif; ?>
            </div>
        </div>

        <div class="product-info">
            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>

            <div class="price-section">
                <span class="current-price">₹<?= number_format($product['price'], 2) ?></span>
            </div>

            <p class="product-description">
                <?= htmlspecialchars($product['description'] ?: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.') ?>
            </p>

            <form method="post" action="add_to_cart.php">
                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                
                <div class="quantity-section">
                    <label for="quantity">Quantity:</label>
                    <input type="number" name="quantity" id="quantity" value="1" min="1" class="quantity-input">
                </div>

                <div class="action-buttons">
                    <button type="submit" class="add-to-cart-btn">
                        <i class="bi bi-cart-plus"></i> Add to Cart
                    </button>
                    <button type="button" class="share-btn" title="Share" onclick="shareProduct()">
                        <i class="bi bi-share"></i>
                    </button>
                </div>
            </form>

            <div class="product-meta">
                <div class="meta-grid">
                    <div class="meta-item">
                        <span class="meta-label">Category:</span>
                        <span class="meta-value"><?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Stock:</span>
                        <span class="meta-value"><?= $product['stock'] ?> Items In Stock</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Created:</span>
                        <span class="meta-value"><?= date('M j, Y', strtotime($product['created_at'])) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Product ID:</span>
                        <span class="meta-value"><?= $product['product_id'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tabs-section">
        <div class="tabs">
            <button class="tab active" onclick="showTab('description')">Description</button>
        </div>
        
        <div id="description" class="tab-content">
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
            <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
        </div>
    </div>
</div>

<!-- Zoom Modal -->
<div id="zoom-modal" class="zoom-modal" onclick="closeZoomModal()">
    <div class="zoom-close" onclick="closeZoomModal()">
        <i class="bi bi-x"></i>
    </div>
    <img id="zoom-image" src="" alt="Zoomed Product Image">
</div>

<script>
function changeMainImage(src) {
    document.getElementById('mainImage').src = src;
    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
    event.target.classList.add('active');
}

function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.style.display = 'none';
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected tab content
    document.getElementById(tabName).style.display = 'block';
    
    // Add active class to clicked tab
    event.target.classList.add('active');
}

// Zoom functionality
function openZoomModal() {
    const mainImage = document.getElementById('mainImage');
    const zoomImage = document.getElementById('zoom-image');
    const zoomModal = document.getElementById('zoom-modal');
    
    zoomImage.src = mainImage.src;
    zoomModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeZoomModal() {
    const zoomModal = document.getElementById('zoom-modal');
    zoomModal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Add click event to main image for zoom
document.addEventListener('DOMContentLoaded', function() {
    const mainImage = document.getElementById('mainImage');
    const zoomIcon = document.querySelector('.zoom-icon');
    
    mainImage.addEventListener('click', openZoomModal);
    zoomIcon.addEventListener('click', openZoomModal);
    
    // Close modal with ESC key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeZoomModal();
        }
    });
});

// Share functionality
function shareProduct() {
    const productName = '<?= htmlspecialchars($product['name']) ?>';
    const productUrl = window.location.href;
    const productPrice = '₹<?= number_format($product['price'], 2) ?>';
    
    const shareText = `Check out this amazing product: ${productName} - ${productPrice}\n\n${productUrl}`;
    
    if (navigator.share) {
        // Use native sharing if available
        navigator.share({
            title: productName,
            text: shareText,
            url: productUrl
        }).catch(console.error);
    } else {
        // Fallback to copying to clipboard
        if (navigator.clipboard) {
            navigator.clipboard.writeText(shareText).then(function() {
                showShareNotification('Product link copied to clipboard!');
            }).catch(function() {
                showShareNotification('Failed to copy link');
            });
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = shareText;
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                showShareNotification('Product link copied to clipboard!');
            } catch (err) {
                showShareNotification('Failed to copy link');
            }
            document.body.removeChild(textArea);
        }
    }
}

function showShareNotification(message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--accent);
        color: var(--secondary);
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 600;
        z-index: 1001;
        box-shadow: 0 4px 12px rgba(255, 214, 0, 0.3);
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}
</script>

<?php include '../components/footer.php'; ?>
</body>
</html>