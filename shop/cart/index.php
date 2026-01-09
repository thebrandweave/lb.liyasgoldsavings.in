<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_source'])) {
    header('Location: ../login.php');
    exit();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/UserManager.php';

$userManager = new UserManager();
$user = $userManager->getUserById($_SESSION['user_id'], $_SESSION['user_source']);

if (!$user) {
    session_destroy();
    header('Location: ../login.php');
    exit();
}

$customerUniqueID = $user['CustomerUniqueID'];

// Fetch cart items for this user with product details
$conn = (new Database())->getConnection();
$sql = "SELECT ci.cart_item_id, ci.product_id, ci.quantity, p.name, p.price, p.stock,
               (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.product_id ORDER BY pi.uploaded_at ASC LIMIT 1) as image_url
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        WHERE ci.CustomerUniqueID = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$customerUniqueID]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cartItems = [];
$total = 0;
$itemCount = 0;
foreach ($result as $row) {
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $total += $row['subtotal'];
    $itemCount += $row['quantity'];
    $cartItems[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart - GoldenDream Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #f7f7fa;
            --secondary: #232526;
            --accent: #ffd600;
            --accent-dark: #ffb300;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
            --card-bg: #fff;
            --radius: 16px;
            --shadow: 0 8px 32px 0 rgba(0,0,0,0.08);
            --font-main: 'Montserrat', Arial, sans-serif;
        }
        
        body, html {
            background: var(--primary);
            color: var(--secondary);
            font-family: var(--font-main);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        .cart-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .cart-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--secondary);
            margin: 0;
        }
        
        .cart-subtitle {
            font-size: 1.1rem;
            color: #666;
            margin: 8px 0 0 0;
        }
        
        .clear-cart-btn {
            background: none;
            border: none;
            color: var(--danger);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .clear-cart-btn:hover {
            background: rgba(220, 53, 69, 0.1);
        }
        
        .cart-layout {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 40px;
        }
        
        .cart-items {
            background: #fff;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
        }
        
        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .cart-table th {
            text-align: left;
            padding: 16px 12px;
            border-bottom: 2px solid #eee;
            font-weight: 700;
            color: var(--secondary);
            font-size: 0.95rem;
        }
        
        .cart-table td {
            padding: 20px 12px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        
        .product-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .product-checkbox {
            width: 18px;
            height: 18px;
            accent-color: var(--success);
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .product-details h4 {
            margin: 0 0 4px 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--secondary);
        }
        
        .price {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--secondary);
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .quantity-btn {
            width: 32px;
            height: 32px;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        
        .quantity-btn:hover {
            background: var(--light);
            border-color: var(--accent);
        }
        
        .quantity-input {
            width: 50px;
            height: 32px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .subtotal {
            font-size: 1.1rem;
            font-weight: 700;
        }
        
        .remove-btn {
            background: none;
            border: none;
            color: var(--danger);
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        
        .remove-btn:hover {
            background: rgba(220, 53, 69, 0.1);
        }
        
        .order-summary {
            background: #fff;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .summary-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 24px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .summary-item:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--success);
        }
        
        .summary-label {
            color: #666;
            font-weight: 500;
        }
        
        .summary-value {
            font-weight: 600;
            color: var(--secondary);
        }
        
        .checkout-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: var(--secondary);
            border: none;
            padding: 16px 24px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(255, 214, 0, 0.3);
        }
        
        .checkout-btn:hover {
            background: linear-gradient(135deg, var(--accent-dark) 0%, #e6a800 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 214, 0, 0.4);
        }
        
        .cart-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #eee;
        }
        
        .action-btn {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: var(--secondary);
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(255, 214, 0, 0.2);
        }
        
        .action-btn:hover {
            background: linear-gradient(135deg, var(--accent-dark) 0%, #e6a800 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(255, 214, 0, 0.3);
        }
        
        
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-cart i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-cart h3 {
            font-size: 1.5rem;
            margin-bottom: 12px;
            color: var(--secondary);
        }
        
        .empty-cart p {
            font-size: 1.1rem;
            margin-bottom: 24px;
        }
        
        .shop-now-btn {
            background: var(--accent);
            color: var(--secondary);
            border: none;
            padding: 0px 24px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        .shop-now-btn i {
            font-size: 2rem;
            color: var(--secondary);
            margin-top: 18px;
        }
        
        .shop-now-btn:hover {
            background: var(--accent-dark);
            transform: translateY(-2px);
        }
        
        /* Confirmation Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(35, 37, 38, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            backdrop-filter: blur(8px);
        }
        
        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 40px 32px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            transform: scale(0.9);
            opacity: 0;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 214, 0, 0.1);
        }
        
        .modal-overlay.show .modal-content {
            transform: scale(1);
            opacity: 1;
        }
        
        .modal-icon {
            font-size: 3.5rem;
            color: var(--accent);
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .modal-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: 16px;
            letter-spacing: 0.5px;
        }
        
        .modal-message {
            font-size: 1.05rem;
            color: #666;
            margin-bottom: 32px;
            line-height: 1.6;
            font-weight: 500;
        }
        
        .modal-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
        }
        
        .modal-btn {
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 1rem;
            letter-spacing: 0.3px;
            min-width: 120px;
        }
        
        .modal-btn.cancel {
            background: #f8f9fa;
            color: #666;
            border: 2px solid #e9ecef;
        }
        
        .modal-btn.cancel:hover {
            background: #e9ecef;
            border-color: #dee2e6;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .modal-btn.confirm {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: var(--secondary);
            box-shadow: 0 4px 12px rgba(255, 214, 0, 0.3);
        }
        
        .modal-btn.confirm:hover {
            background: linear-gradient(135deg, var(--accent-dark) 0%, #e6a800 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 214, 0, 0.4);
        }
        
        @media (max-width: 1024px) {
            .cart-layout {
                grid-template-columns: 1fr;
                gap: 24px;
            }
            
            .order-summary {
                position: static;
            }
        }
        
        @media (max-width: 768px) {
            .cart-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            
            .cart-title {
                font-size: 2rem;
            }
            
            .cart-items, .order-summary {
                padding: 20px;
            }
            
            .product-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .cart-table th, .cart-table td {
                padding: 12px 8px;
            }
            
            .cart-actions {
                flex-direction: column;
                gap: 12px;
            }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../components/navbar.php'; ?>

<div class="cart-container">
    <div class="cart-header">
        <div>
            <h1 class="cart-title">Your Cart</h1>
            <p class="cart-subtitle">There are <?= $itemCount ?> products in your cart</p>
        </div>
        <?php if (!empty($cartItems)): ?>
            <button class="clear-cart-btn" onclick="clearCart()">
                <i class="bi bi-trash"></i>
                Clear Cart
            </button>
        <?php endif; ?>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <i class="bi bi-cart-x"></i>
            <h3>Your cart is empty</h3>
            <p>Looks like you haven't added any products to your cart yet.</p>
            <a href="../products/" class="shop-now-btn">
                <i class="bi bi-arrow-left"></i>
                Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="cart-items">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td>
                                    <div class="product-info">
                                        <input type="checkbox" class="product-checkbox" checked>
                                        <img src="<?= $item['image_url'] ? '../uploads/products/' . htmlspecialchars(basename($item['image_url'])) : 'https://via.placeholder.com/60x60?text=No+Image' ?>" 
                                             alt="<?= htmlspecialchars($item['name']) ?>" 
                                             class="product-image">
                                        <div class="product-details">
                                            <h4><?= htmlspecialchars($item['name']) ?></h4>
                                        </div>
                                    </div>
                                </td>
                                <td class="price">₹<?= number_format($item['price'], 2) ?></td>
                                <td>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn" onclick="updateQuantity(<?= $item['cart_item_id'] ?>, -1)">-</button>
                                        <input type="number" class="quantity-input" value="<?= $item['quantity'] ?>" 
                                               min="1" max="<?= $item['stock'] ?>" 
                                               onchange="updateQuantity(<?= $item['cart_item_id'] ?>, this.value, true)">
                                        <button class="quantity-btn" onclick="updateQuantity(<?= $item['cart_item_id'] ?>, 1)">+</button>
                                    </div>
                                </td>
                                <td class="subtotal">₹<?= number_format($item['subtotal'], 2) ?></td>
                                <td>
                                    <button class="remove-btn" onclick="removeItem(<?= $item['cart_item_id'] ?>)" title="Remove item">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="order-summary">
                <h3 class="summary-title">Order Summary</h3>
                <div class="summary-item">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value">₹<?= number_format($total, 2) ?></span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total</span>
                    <span class="summary-value">₹<?= number_format($total, 2) ?></span>
                </div>
                
                <form method="post" action="place_order.php">
                    <button type="submit" class="checkout-btn">
                        Proceed To Checkout
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="cart-actions">
            <a href="../products/" class="action-btn">
                <i class="bi bi-arrow-left"></i>
                Continue Shopping
            </a>
            <button class="action-btn" onclick="updateCart()">
                <i class="bi bi-arrow-clockwise"></i>
                Update Cart
            </button>
        </div>
    <?php endif; ?>
</div>

<div id="confirmation-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-icon"><i class="bi bi-question-circle-fill"></i></div>
        <h3 class="modal-title">Confirm Action</h3>
        <p class="modal-message"></p>
        <div class="modal-buttons">
            <button class="modal-btn cancel" onclick="closeModal()">Cancel</button>
            <button class="modal-btn confirm" onclick="confirmAction()">Confirm</button>
        </div>
    </div>
</div>

<script>
function updateQuantity(cartItemId, change, isDirectInput = false) {
    let newQuantity;
    if (isDirectInput) {
        newQuantity = parseInt(change);
    } else {
        const input = event.target.parentNode.querySelector('.quantity-input');
        const currentQty = parseInt(input.value);
        newQuantity = currentQty + parseInt(change);
    }
    
    if (newQuantity < 1) return;
    
    // Send AJAX request to update quantity
    fetch('update_quantity.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            cart_item_id: cartItemId,
            quantity: newQuantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating quantity: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating quantity');
    });
}

function removeItem(cartItemId) {
    const modalMessage = 'Are you sure you want to remove this item from your cart?';
    showConfirmationModal(modalMessage, () => {
        fetch('remove_item.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cart_item_id: cartItemId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error removing item: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error removing item');
        });
    });
}

function clearCart() {
    const modalMessage = 'Are you sure you want to clear your entire cart?';
    showConfirmationModal(modalMessage, () => {
        fetch('clear_cart.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error clearing cart: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error clearing cart');
        });
    });
}

function updateCart() {
    location.reload();
}

let currentConfirmCallback = null;

function showConfirmationModal(message, onConfirm) {
    const modalOverlay = document.getElementById('confirmation-modal');
    const modalMessageElement = modalOverlay.querySelector('.modal-message');
    
    modalMessageElement.textContent = message;
    currentConfirmCallback = onConfirm;
    
    modalOverlay.style.display = 'flex';
    setTimeout(() => {
        modalOverlay.classList.add('show');
    }, 10);
}

function closeModal() {
    const modalOverlay = document.getElementById('confirmation-modal');
    modalOverlay.classList.remove('show');
    setTimeout(() => {
        modalOverlay.style.display = 'none';
    }, 300);
    currentConfirmCallback = null;
}

function confirmAction() {
    if (currentConfirmCallback) {
        currentConfirmCallback();
    }
    closeModal();
}

// Close modal when clicking outside
document.getElementById('confirmation-modal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeModal();
    }
});
</script>

<?php include __DIR__ . '/../components/footer.php'; ?>
</body>
</html>