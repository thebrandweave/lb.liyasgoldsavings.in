<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Database connection
$db = new Database();
$conn = $db->getConnection();

// Fetch categories for filter
$categories = $conn->query('SELECT * FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

// Get filter parameters
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Get total product count for all categories (for sidebar counts)
$totalProductsQuery = "SELECT p.category_id, COUNT(*) as count FROM products p GROUP BY p.category_id";
$totalProductsStmt = $conn->prepare($totalProductsQuery);
$totalProductsStmt->execute();
$categoryCounts = [];
while ($row = $totalProductsStmt->fetch(PDO::FETCH_ASSOC)) {
    $categoryCounts[$row['category_id']] = $row['count'];
}

// Get total count of all products
$totalProductsCount = array_sum($categoryCounts);



// Build product query for filtered results
$where_conditions = [];
$params = [];
if ($category_id > 0) {
    $where_conditions[] = 'p.category_id = ?';
    $params[] = $category_id;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Add sorting
$order_by = 'ORDER BY p.created_at DESC';
switch ($sort_by) {
    case 'price_low':
        $order_by = 'ORDER BY p.price ASC';
        break;
    case 'price_high':
        $order_by = 'ORDER BY p.price DESC';
        break;
    case 'name':
        $order_by = 'ORDER BY p.name ASC';
        break;
    case 'newest':
    default:
        $order_by = 'ORDER BY p.created_at DESC';
        break;
}

$query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id $where_clause $order_by";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch images for all products
$productImages = [];
if ($products) {
    $ids = array_column($products, 'product_id');
    $in = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $conn->prepare('SELECT product_id, image_url FROM product_images WHERE product_id IN (' . $in . ') ORDER BY uploaded_at ASC');
    $stmt->execute($ids);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $img) {
        $productImages[$img['product_id']][] = $img['image_url'];
    }
}



// Get product statistics
$statsQuery = "SELECT 
    COUNT(*) as total_products,
    COUNT(DISTINCT category_id) as total_categories,
    AVG(price) as avg_price,
    SUM(stock) as total_stock
FROM products";
$statsStmt = $conn->prepare($statsQuery);
$statsStmt->execute();
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products - GoldenDream Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
        .hero-banner {
            width: 100%;
            min-height: 220px;
            background: url('https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=1200&q=80') center/cover no-repeat;
            display: flex;
            align-items: flex-end;
            position: relative;
        }
        .hero-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.45);
        }
        .hero-content {
            position: relative;
            z-index: 2;
            padding: 48px 0 32px 0;
            width: 100%;
            text-align: center;
        }
        .hero-title {
            color: #fff;
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        .breadcrumb {
            color: #fff;
            font-size: 1.05rem;
            opacity: 0.85;
        }
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .content-layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 40px;
        }
        .filters-sidebar {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            padding: 32px;
            height: fit-content;
            position: sticky;
            top: 20px;
            padding: 5px 20px;
        }
        .filter-section {
            margin-bottom: 32px;
        }
        .filter-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .filter-title i {
            color: var(--accent);
        }
        .category-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .category-item {
            margin-bottom: 8px;
        }
        .category-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            color: #666;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .category-link:hover {
            background: #f8f9fa;
            color: var(--accent-dark);
            border-color: var(--accent);
        }
        .category-link.active {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: #232526;
            font-weight: 700;
        }
        .category-count {
            background: rgba(255,255,255,0.2);
            color: inherit;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .sort-select {
            width: 100%;
            padding: 12px;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 1rem;
            background: #fff;
            cursor: pointer;
        }
        .sort-select:focus {
            outline: none;
            border-color: var(--accent);
        }
        .apply-filters {
            width: 100%;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: #232526;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        .apply-filters:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 214, 0, 0.3);
        }
        .products-section {
            min-height: 600px;
        }
        .products-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
            padding: 24px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }


        .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }
        .product-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            position: relative;
        }
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.15);
        }
        .product-image-container {
            position: relative;
            height: 240px;
            overflow: hidden;
            background: #f8f9fa;
        }
        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .product-card:hover .product-image {
            transform: scale(1.1);
        }
        .product-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: var(--accent);
            color: #232526;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            z-index: 2;
        }
        .product-stock-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 2;
        }
        .stock-high {
            background: var(--success);
            color: white;
        }
        .stock-medium {
            background: var(--warning);
            color: #232526;
        }
        .stock-low {
            background: var(--danger);
            color: white;
        }
        .product-actions {
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 12px;
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 3;
        }
        .product-card:hover .product-actions {
            opacity: 1;
        }
        .action-btn {
            width: 36px;
            height: 36px;
            background: rgba(255,255,255,0.95);
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #232526;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .action-btn:hover {
            background: var(--accent);
            color: #232526;
            transform: scale(1.1);
        }
        .product-content {
            padding: 20px;
        }
        .product-category {
            color: var(--accent-dark);
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .product-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 12px;
            line-height: 1.4;
        }
        .product-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .product-price {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--secondary);
        }
        .product-stock {
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
        }
        .product-description {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.4;
            margin-bottom: 16px;
        }
        .product-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .view-details-btn {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: #232526;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .view-details-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 214, 0, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        .empty-icon {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 16px;
        }
        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .empty-text {
            font-size: 1rem;
            opacity: 0.7;
        }
        @media (max-width: 1200px) {
            .content-layout {
                grid-template-columns: 280px 1fr;
                gap: 32px;
            }
            .products-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (max-width: 900px) {
            .content-layout {
                grid-template-columns: 1fr;
                gap: 24px;
            }
            .filters-sidebar {
                position: static;
                order: 2;
            }
            .products-section {
                order: 1;
            }
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 600px) {
            .main-container {
                padding: 20px 12px;
            }
            .products-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            .hero-title {
                font-size: 2.5rem;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
        /* Navbar and Footer Styles */
        .topbar {
            background: #fafafa;
            color: #232526;
            font-size: 0.98rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 4vw 7px 4vw;
            border-bottom: 1px solid #ececec;
        }
        .topbar-left span {
            margin-right: 24px;
        }
        .topbar-right a, .topbar-right span {
            margin-left: 18px;
            color: #232526;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.98rem;
        }
        .main-navbar {
            background: #fff;
            color: #232526;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 4vw;
            min-height: 64px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        }
        .navbar-logo {
            font-size: 2.1rem;
            font-weight: 800;
            letter-spacing: 2px;
            color: var(--accent-dark);
            font-family: 'Montserrat', Arial, sans-serif;
        }
        .navbar-links {
            display: flex;
            gap: 36px;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .navbar-links a {
            color: #232526;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.08rem;
            transition: color 0.18s;
        }
        .navbar-links a:hover {
            color: var(--accent);
        }
        .navbar-search-icons {
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .navbar-search input {
            border: none;
            border-radius: 999px;
            padding: 8px 18px;
            font-size: 1rem;
            background: #f7f7fa;
            color: #232526;
            width: 220px;
            outline: none;
        }
        .navbar-icons {
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .navbar-icons a {
            color: #232526;
            font-size: 1.35rem;
            position: relative;
            text-decoration: none;
        }
        .icon-badge .badge {
            position: absolute;
            top: -7px;
            right: -10px;
            background: var(--accent);
            color: #232526;
            font-size: 0.78rem;
            border-radius: 50%;
            padding: 2px 6px;
            font-weight: 700;
        }
        .navbar-btn {
            text-decoration: none;
            font-weight: 700;
            transition: all 0.18s;
        }
        .navbar-btn:hover {
            transform: translateY(-1px);
        }
        /* Footer Styles */
        .newsletter-section {
            width: 100vw;
            background: #111;
            padding: 64px 0 72px 0;
            margin: 0;
            text-align: center;
            position: relative;
            left: 50%;
            right: 50%;
            margin-left: -50vw;
            margin-right: -50vw;
            font-family: 'Montserrat', sans-serif;
            border-top: 1.5px solid #23211a;
        }
        .newsletter-title {
            color: #fff;
            font-size: 2.6rem;
            font-weight: 800;
            margin-bottom: 18px;
            line-height: 1.15;
            letter-spacing: 0.01em;
        }
        .newsletter-title .accent {
            color: #ffd600;
            background: none;
            padding: 0 4px;
            border-radius: 4px;
        }
        .newsletter-subtitle {
            color: #fff;
            font-size: 1.15rem;
            margin-bottom: 38px;
            font-weight: 400;
        }
        .newsletter-form {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0;
            width: 100%;
            max-width: 540px;
            margin: 0 auto;
            background: #fff;
            border-radius: 999px;
            box-shadow: 0 2px 16px 0 rgba(0,0,0,0.06);
            border: 1.5px solid #23211a;
            padding: 0;
        }
        .newsletter-input {
            flex: 1;
            border: none;
            outline: none;
            background: transparent;
            padding: 18px 24px;
            font-size: 1.08rem;
            border-radius: 999px 0 0 999px;
            color: #232526;
            font-family: 'Montserrat', sans-serif;
        }
        .newsletter-input::placeholder {
            color: #888;
            font-size: 1.08rem;
            font-family: 'Montserrat', sans-serif;
        }
        .newsletter-btn {
            background: #ffd600;
            color: #23211a;
            font-weight: 700;
            border: none;
            border-radius: 0 999px 999px 0;
            padding: 18px 38px;
            font-size: 1.08rem;
            letter-spacing: 0.12em;
            cursor: pointer;
            transition: background 0.18s, color 0.18s, transform 0.18s;
            font-family: 'Montserrat', sans-serif;
            box-shadow: 0 2px 8px 0 rgba(255,214,0,0.08);
        }
        .newsletter-btn:hover {
            background: #ffe066;
            color: #23211a;
            transform: scale(0.97);
        }
        .shop-footer {
            background: #fff;
            border-top: 1px solid #f0f0f0;
            padding: 60px 0 20px 0;
            font-family: 'Montserrat', sans-serif;
            color: #333;
        }
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1.5fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        .footer-section {
            display: flex;
            flex-direction: column;
        }
        .footer-brand {
            grid-column: 1;
        }
        .footer-logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: #23211a;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }
        .footer-heading {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: #23211a;
        }
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-links li {
            margin-bottom: 8px;
        }
        .footer-links a {
            color: #666;
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-links a:hover {
            color: #ffd600;
        }
        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
            color: #666;
        }
        .contact-item i {
            color: #ffd600;
            margin-top: 2px;
        }
        .social-links {
            display: flex;
            gap: 16px;
        }
        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: #f8f9fa;
            border-radius: 50%;
            color: #666;
            text-decoration: none;
            transition: all 0.2s;
        }
        .social-link:hover {
            background: #ffd600;
            color: #23211a;
        }
        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }
        .copyright {
            color: #666;
            font-size: 0.9rem;
        }
        .developed-by {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 0.9rem;
        }
        .developer-logo {
            height: 20px;
            width: auto;
        }
        #backToTopBtn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #ffd600;
            color: #23211a;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 1000;
            display: none;
        }
        #backToTopBtn:hover {
            background: #ffb300;
            transform: translateY(-2px);
        }
        @media (max-width: 700px) {
            .newsletter-title { font-size: 1.5rem; }
            .newsletter-form { flex-direction: column; border-radius: 32px; max-width: 98vw; }
            .newsletter-input, .newsletter-btn { border-radius: 32px; width: 100%; padding: 14px 16px; font-size: 1rem; }
            .newsletter-btn { margin-top: 10px; }
            .footer-content { grid-template-columns: 1fr; gap: 24px; }
            .footer-bottom { flex-direction: column; gap: 12px; text-align: center; }
        }
        /* Page Header */
        .page-header {
            background: #fff;
            padding: 40px 0;
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 1px solid #f0f0f0;
        }
        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: 16px;
        }
        
        /* Breadcrumb Navigation */
        .breadcrumb {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #666;
        }
        .breadcrumb a {
            color: var(--accent-dark);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }
        .breadcrumb a:hover {
            color: var(--accent);
        }
        .breadcrumb .separator {
            color: #ccc;
            font-weight: 400;
        }
        .breadcrumb .current {
            color: #666;
            font-weight: 500;
        }
        
        /* Page Subtitle */
        .page-subtitle {
            text-align: center;
            margin-bottom: 40px;
            padding: 0 20px;
        }
        .page-subtitle p {
            font-size: 1.1rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.5;
        }
        .cart-toast {
            position: fixed;
            left: 50%;
            bottom: 40px;
            transform: translateX(-50%);
            background: #232526;
            color: #ffd600;
            padding: 16px 32px;
            border-radius: 32px;
            font-size: 1.1rem;
            font-weight: 600;
            box-shadow: 0 4px 24px rgba(0,0,0,0.18);
            z-index: 9999;
            opacity: 0.97;
            animation: fadeInOut 3s forwards;
        }
        @keyframes fadeInOut {
            0% { opacity: 0; bottom: 0; }
            10% { opacity: 0.97; bottom: 40px; }
            90% { opacity: 0.97; bottom: 40px; }
            100% { opacity: 0; bottom: 0; }
        }
    </style>
</head>
<body>
    <?php 
    $_GET['from_products'] = true;
    include '../components/navbar.php'; 
    ?>
    
    <div class="page-header">
        <div class="container">
            <h1 class="page-title">Products</h1>
            <div class="breadcrumb">
                <a href="../index.php">Home</a>
                <span class="separator">></span>
                <span class="current">Products</span>
            </div>
        </div>
    </div>

    <div class="main-container">

        <div class="content-layout">
            <aside class="filters-sidebar">
                <form method="get" action="">
                    <div class="filter-section">
                        <h3 class="filter-title">
                            <i class="bi bi-tag"></i>
                            Categories
                        </h3>
                        <ul class="category-list">
                            <li class="category-item">
                                <a href="?" class="category-link <?php if ($category_id == 0) echo 'active'; ?>">
                                    <span>All Categories</span>
                                    <span class="category-count"><?php echo $totalProductsCount; ?></span>
                                </a>
                            </li>
                            <?php foreach ($categories as $cat): ?>
                                <li class="category-item">
                                    <a href="?category=<?php echo $cat['category_id']; ?>" class="category-link <?php if ($category_id == $cat['category_id']) echo 'active'; ?>">
                                        <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                        <span class="category-count"><?php echo $categoryCounts[$cat['category_id']] ?? 0; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>



                    <div class="filter-section">
                        <h3 class="filter-title">
                            <i class="bi bi-sort-down"></i>
                            Sort By
                        </h3>
                        <select name="sort" class="sort-select">
                            <option value="newest" <?php if ($sort_by == 'newest') echo 'selected'; ?>>Newest First</option>
                            <option value="price_low" <?php if ($sort_by == 'price_low') echo 'selected'; ?>>Price: Low to High</option>
                            <option value="price_high" <?php if ($sort_by == 'price_high') echo 'selected'; ?>>Price: High to Low</option>
                            <option value="name" <?php if ($sort_by == 'name') echo 'selected'; ?>>Name: A to Z</option>
                        </select>
                    </div>

                    <!-- <button type="submit" class="apply-filters">
                        <i class="bi bi-funnel"></i> Apply Filters
                    </button> -->
                </form>
            </aside>

            <main class="products-section">
                <!-- <div class="products-header">
                </div> -->

                <div class="products-grid">
                    <?php if (isset($_GET['added'])): ?>
                        <div id="cart-toast" class="cart-toast">Product added to cart!</div>
                    <?php endif; ?>
                    <?php foreach ($products as $product): ?>
                        <?php
                        $imgs = $productImages[$product['product_id']] ?? [];
                        if (count($imgs) > 0) {
                            $img = '../uploads/products/' . htmlspecialchars(basename($imgs[0]));
                        } else {
                            $img = 'https://via.placeholder.com/300x250?text=No+Image';
                        }
                        
                        // Determine stock status
                        $stockClass = 'stock-high';
                        $stockText = 'In Stock';
                        if ($product['stock'] <= 5) {
                            $stockClass = 'stock-low';
                            $stockText = 'Low Stock';
                        } elseif ($product['stock'] <= 15) {
                            $stockClass = 'stock-medium';
                            $stockText = 'Limited';
                        }
                        ?>
                        <div class="product-card">
                            <div class="product-image-container">
                                <img src="<?php echo $img; ?>" class="product-image" alt="<?php echo htmlspecialchars($product['name']); ?>" />
                                <div class="product-badge"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></div>
                                <div class="product-stock-badge <?php echo $stockClass; ?>"><?php echo $stockText; ?></div>
                                <div class="product-actions">
                                    <form method="post" action="add_to_cart.php" style="display:inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="action-btn" title="Add to Cart">
                                            <i class="bi bi-cart-plus"></i>
                                        </button>
                                    </form>
                                    <button class="action-btn" title="Quick View">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="action-btn" title="Add to Wishlist">
                                        <i class="bi bi-heart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-content">
                                <div class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></div>
                                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <?php if (!empty($product['description'])): ?>
                                    <div class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 80)) . (strlen($product['description']) > 80 ? '...' : ''); ?></div>
                                <?php endif; ?>
                                <div class="product-info">
                                    <div class="product-price">₹<?php echo number_format($product['price'], 2); ?></div>
                                </div>
                                <div class="product-footer">
                                    <a href="details.php?id=<?= $product['product_id'] ?>" class="view-details-btn">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($products)): ?>
                        <div class="empty-state" style="grid-column: 1 / -1;">
                            <div class="empty-icon">
                                <i class="bi bi-box"></i>
                            </div>
                            <div class="empty-title">No Products Found</div>
                            <div class="empty-text">Try adjusting your filters or check back later for new products.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>


    </div>

    <?php 
    $_GET['from_products'] = true;
    include '../components/footer.php'; 
    ?>
    <script>
window.addEventListener('DOMContentLoaded', function() {
    var toast = document.getElementById('cart-toast');
    if (toast) {
        setTimeout(function() {
            toast.style.display = 'none';
        }, 3000);
    }
});
</script>
</body>
</html> 