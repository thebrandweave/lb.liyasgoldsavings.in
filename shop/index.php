<?php
session_start();
require_once __DIR__ . '/config/config.php';

// Initialize variables
$categories = [];
$productCounts = [];
$products = [];
$productImages = [];

try {
    // Fetch categories and product counts
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        $categories = $conn->query('SELECT * FROM categories ORDER BY category_id DESC')->fetchAll(PDO::FETCH_ASSOC);
        
        $stmt = $conn->query('SELECT category_id, COUNT(*) as cnt FROM products GROUP BY category_id');
        foreach ($stmt as $row) {
            $productCounts[$row['category_id']] = $row['cnt'];
        }

        // Fetch products for homepage
        $products = $conn->query('SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.created_at DESC LIMIT 8')->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch images for all products
        if ($products) {
            $ids = array_column($products, 'product_id');
            $in = str_repeat('?,', count($ids) - 1) . '?';
            $stmt = $conn->prepare('SELECT product_id, image_url FROM product_images WHERE product_id IN (' . $in . ') ORDER BY uploaded_at ASC');
            $stmt->execute($ids);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $img) {
                $productImages[$img['product_id']][] = $img['image_url'];
            }
        }
    }
} catch (Exception $e) {
    // Log the error
    error_log("Shop index error: " . $e->getMessage());
    
    // Redirect to local error page
    header("Location: error.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GoldenDream Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #f7f7fa;
            --secondary: #232526;
            --accent: #ffd600;
            --accent-dark: #ffb300;
            --card-bg: #fff;
            --card-blur: blur(12px);
            --radius: 22px;
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
        .navbar {
            width: 100%;
            padding: 24px 0 18px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
        }
        .navbar-logo {
            font-size: 2rem;
            font-weight: 800;
            color: var(--accent-dark);
            letter-spacing: 2px;
        }
        .navbar-links {
            display: flex;
            gap: 32px;
        }
        .navbar-links a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 600;
            font-size: 1.08rem;
            transition: color 0.18s;
        }
        .navbar-links a:hover {
            color: var(--accent);
        }
        .navbar-cta {
            background: linear-gradient(90deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: #111;
            font-weight: 700;
            border: none;
            border-radius: 999px;
            padding: 12px 32px;
            font-size: 1.08rem;
            box-shadow: 0 2px 12px rgba(255,214,0,0.10);
            cursor: pointer;
            transition: background 0.18s, color 0.18s;
        }
        .navbar-cta:hover {
            background: var(--accent-dark);
            color: #fff;
        }
        .hero {
            max-width: 1200px;
            margin: 48px auto 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 48px;
            flex-wrap: wrap;
            padding: 0 20px;
        }
        .hero-content {
            flex: 1 1 350px;
        }
        .hero-title {
            font-size: 2.8rem;
            font-weight: 900;
            color: var(--accent-dark);
            margin-bottom: 18px;
            letter-spacing: 1.5px;
        }
        .hero-desc {
            font-size: 1.18rem;
            color: #555;
            margin-bottom: 32px;
            max-width: 480px;
        }
        .hero-btn {
            background: var(--accent);
            color: #232526;
            font-weight: 700;
            border: none;
            border-radius: 999px;
            padding: 14px 38px;
            font-size: 1.15rem;
            box-shadow: 0 2px 12px rgba(255,214,0,0.10);
            cursor: pointer;
            transition: background 0.18s, color 0.18s;
        }
        .hero-btn:hover {
            background: var(--accent-dark);
            color: #fff;
        }
        .hero-image {
            flex: 1 1 350px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .hero-image img {
            width: 340px;
            max-width: 90vw;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            background: #fff;
        }
        .features-section {
            max-width: 1200px;
            margin: 64px auto 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 32px;
            padding: 0 20px;
        }
        .feature-card {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 32px 24px 28px 24px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            backdrop-filter: var(--card-blur);
            -webkit-backdrop-filter: var(--card-blur);
            border: 1.5px solid rgba(255,255,255,0.08);
        }
        .feature-icon {
            font-size: 2.2rem;
            color: var(--accent);
            margin-bottom: 16px;
        }
        .feature-title {
            font-size: 1.18rem;
            font-weight: 700;
            color: var(--accent-dark);
            margin-bottom: 8px;
        }
        .feature-desc {
            color: #555;
            font-size: 1rem;
        }
        .products-section {
            max-width: 1200px;
            margin: 100px auto 0 auto;
            padding: 0 20px;
            margin-bottom: 80px;
        }
        .products-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--accent-dark);
            margin-bottom: 8px;
            letter-spacing: 1px;
            text-align: center;
        }
        .products-subtitle {
            color: #555;
            font-size: 1.08rem;
            margin-bottom: 32px;
            text-align: center;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 32px;
            margin-top: 24px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }
        .product-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            position: relative;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            border: 1px solid #f0f0f0;
            cursor: pointer;
        }
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }
        .product-image-container {
            position: relative;
            width: 100%;
            height: 280px;
            overflow: hidden;
            background: #f8f9fa;
            border-radius: 12px 12px 0 0;
        }
        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .product-card:hover .product-image {
            transform: scale(1.08);
        }
        .product-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: #ff4757;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 4px;
            z-index: 2;
            transform: translateY(-2px);
            opacity: 0.9;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .product-card:hover .product-badge {
            transform: translateY(0);
            opacity: 1;
        }
        .product-hot-sale {
            position: absolute;
            bottom: 12px;
            left: 12px;
            background: #000;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
            transform: translateY(2px);
            opacity: 0.9;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .product-card:hover .product-hot-sale {
            transform: translateY(0);
            opacity: 1;
        }
        .product-content {
            padding: 16px;
            transform: translateY(0);
            transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .product-card:hover .product-content {
            transform: translateY(-4px);
        }
        .product-name {
            font-size: 1rem;
            font-weight: 600;
            color: #2d3436;
            line-height: 1.4;
            margin: 0 0 8px 0;
            transition: color 0.3s ease;
        }
        .product-card:hover .product-name {
            color: #ffd600;
        }
        .product-price-container {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }
        .product-price {
            color: #2d3436;
            font-size: 1.1rem;
            font-weight: 700;
        }
        .product-original-price {
            color: #636e72;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: line-through;
        }

        .product-btn {
            flex: 1;
            background: #2d3436;
            color: #fff;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            padding: 10px 16px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            overflow: hidden;
        }
        .product-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .product-btn:hover::before {
            left: 100%;
        }
        .product-btn:hover {
            background: #ff4757;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255,71,87,0.3);
        }
        .product-wishlist {
            background: #fff;
            border: 1px solid #ddd;
            color: #636e72;
            border-radius: 6px;
            padding: 10px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .product-wishlist::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: #ff4757;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.3s ease;
        }
        .product-wishlist:hover::before {
            width: 100%;
            height: 100%;
        }
        .product-wishlist:hover {
            color: #fff;
            border-color: #ff4757;
        }
        .product-wishlist i {
            position: relative;
            z-index: 1;
        }
        .product-color-swatches {
            display: flex;
            gap: 6px;
            margin-top: 8px;
            transform: translateY(0);
            transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .product-card:hover .product-color-swatches {
            transform: translateY(-2px);
        }
        .color-swatch {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
        }
        .color-swatch::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.3s ease;
        }
        .color-swatch:hover {
            transform: scale(1.3);
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .color-swatch:hover::before {
            width: 100%;
            height: 100%;
        }
        @media (max-width: 1200px) {
            .products-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 24px;
            }
            .benefits-container { 
                grid-template-columns: repeat(2, 1fr); 
                gap: 20px;
            }
        }
        @media (max-width: 900px) {
            .hero { 
                flex-direction: column; 
                gap: 32px; 
                padding: 0 20px;
            }
            .hero-title {
                font-size: 2.2rem;
            }
            .hero-desc {
                font-size: 1.1rem;
            }
            .features-section { 
                grid-template-columns: 1fr; 
                gap: 24px;
                padding: 0 20px;
            }
            .products-title { 
                font-size: 1.8rem; 
            }
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
                padding: 0 20px;
            }
            .products-section {
                padding: 0 20px;
            }
            .shop-category-section {
                padding: 0 20px;
            }
            .shop-category-title {
                font-size: 1.8rem;
            }
            .fullimg-hero-category { 
                font-size: 1.5rem; 
            }
            .fullimg-hero-content { 
                max-width: 98vw; 
                padding: 0 20px;
            }
            .fullimg-hero-btns { 
                flex-direction: column; 
                gap: 12px; 
            }
            .fullimg-hero-btn {
                padding: 12px 28px;
                font-size: 1rem;
            }
            .benefits-section {
                position: relative;
                top: auto;
                transform: none;
                margin-top: 40px;
                padding: 0 20px;
            }
            .benefits-outer-box {
                padding: 24px 20px;
            }
            .benefits-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }
            .benefit-icon {
                font-size: 2.2rem;
            }
            .benefit-title {
                font-size: 0.9rem;
            }
        }
        @media (max-width: 768px) {
            .navbar {
                padding: 16px 20px;
            }
            .navbar-logo {
                font-size: 1.6rem;
            }
            .navbar-links {
                gap: 20px;
            }
            .navbar-links a {
                font-size: 1rem;
            }
            .hero {
                margin: 32px auto 0 auto;
                gap: 24px;
            }
            .hero-title {
                font-size: 1.8rem;
                margin-bottom: 12px;
            }
            .hero-desc {
                font-size: 1rem;
                margin-bottom: 24px;
            }
            .hero-btn {
                padding: 12px 28px;
                font-size: 1rem;
            }
            .hero-image img {
                width: 280px;
            }
            .features-section {
                margin: 48px auto 0 auto;
                gap: 20px;
            }
            .feature-card {
                padding: 24px 20px;
            }
            .feature-icon {
                font-size: 1.8rem;
            }
            .feature-title {
                font-size: 1.1rem;
            }
            .products-section {
                margin: 80px auto 0 auto;
            }
            .products-title {
                font-size: 1.6rem;
            }
            .products-subtitle {
                font-size: 1rem;
            }
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }
            .product-card {
                border-radius: 12px;
            }
            .product-image-container {
                height: 200px;
            }
            .product-content {
                padding: 12px;
            }
            .product-name {
                font-size: 0.9rem;
            }
            .product-price {
                font-size: 1rem;
            }
            .view-details-btn {
                padding: 8px 12px;
                font-size: 0.8rem;
            }
            .shop-category-title {
                font-size: 1.6rem;
            }
            .shop-category-subtitle {
                font-size: 1rem;
            }
            .shop-category-card {
                padding: 20px 16px;
            }
            .shop-category-img {
                width: 100px;
                height: 100px;
            }
            .shop-category-name {
                font-size: 1rem;
            }
            .shop-category-btn {
                padding: 8px 18px;
                font-size: 0.9rem;
            }
            .fullimg-hero-carousel {
                min-height: 60vh;
            }
            .fullimg-hero-category {
                font-size: 1.3rem;
                margin-bottom: 8px;
            }
            .fullimg-hero-tagline {
                font-size: 0.9rem;
                margin-bottom: 20px;
            }
            .fullimg-hero-btns {
                gap: 10px;
                margin-bottom: 24px;
            }
            .fullimg-hero-btn {
                padding: 10px 24px;
                font-size: 0.9rem;
            }
            .fullimg-hero-dots {
                right: 20px;
                gap: 12px;
            }
            .fullimg-hero-dot {
                width: 8px;
                height: 8px;
            }
            .benefits-section {
                margin-top: 30px;
            }
            .benefits-outer-box {
                padding: 20px 16px;
            }
            .benefits-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 12px;
            }
            .benefit-item {
                padding: 0 12px;
            }
            .benefit-icon {
                font-size: 1.8rem;
                margin-bottom: 12px;
            }
            .benefit-title {
                font-size: 0.8rem;
                margin-bottom: 4px;
            }
            .newsletter-section {
                padding: 48px 20px 56px 20px;
            }
            .newsletter-title {
                font-size: 1.8rem;
            }
            .newsletter-subtitle {
                font-size: 1rem;
            }
            .newsletter-form {
                max-width: 100%;
                flex-direction: column;
                border-radius: 16px;
            }
            .newsletter-input,
            .newsletter-btn {
                border-radius: 16px;
                padding: 14px 20px;
                font-size: 1rem;
            }
            .newsletter-btn {
                margin-top: 8px;
            }
            .view-all-products-btn {
                padding: 14px 32px;
                font-size: 1rem;
            }
        }
        @media (max-width: 600px) {
            .navbar {
                padding: 12px 16px;
            }
            .navbar-logo {
                font-size: 1.4rem;
            }
            .hero {
                margin: 24px auto 0 auto;
                gap: 20px;
            }
            .hero-title {
                font-size: 1.5rem;
            }
            .hero-desc {
                font-size: 0.9rem;
            }
            .hero-btn {
                padding: 10px 24px;
                font-size: 0.9rem;
            }
            .hero-image img {
                width: 240px;
            }
            .features-section {
                margin: 40px auto 0 auto;
                gap: 16px;
            }
            .feature-card {
                padding: 20px 16px;
            }
            .feature-icon {
                font-size: 1.6rem;
            }
            .feature-title {
                font-size: 1rem;
            }
            .feature-desc {
                font-size: 0.9rem;
            }
            .products-section {
                margin: 60px auto 0 auto;
            }
            .products-title {
                font-size: 1.4rem;
            }
            .products-subtitle {
                font-size: 0.9rem;
            }
            .products-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            .product-card {
                border-radius: 10px;
            }
            .product-image-container {
                height: 180px;
            }
            .product-content {
                padding: 10px;
            }
            .product-name {
                font-size: 0.85rem;
            }
            .product-price {
                font-size: 0.9rem;
            }
            .view-details-btn {
                padding: 6px 10px;
                font-size: 0.75rem;
            }
            .shop-category-title {
                font-size: 1.2rem;
            }
            .shop-category-subtitle {
                font-size: 0.8rem;
            }
            .shop-category-card {
                padding: 16px 12px;
            }
            .shop-category-img {
                width: 80px;
                height: 80px;
            }
            .shop-category-name {
                font-size: 0.8rem;
            }
            .shop-category-btn {
                padding: 6px 14px;
                font-size: 0.8rem;
            }
            .fullimg-hero-carousel {
                min-height: 50vh;
            }
            .fullimg-hero-category {
                font-size: 1.1rem;
            }
            .fullimg-hero-tagline {
                font-size: 0.8rem;
            }
            .fullimg-hero-btns {
                gap: 8px;
                margin-bottom: 20px;
            }
            .fullimg-hero-btn {
                padding: 8px 20px;
                font-size: 0.75rem;
            }
            .fullimg-hero-dots {
                right: 16px;
                gap: 10px;
            }
            .fullimg-hero-dot {
                width: 6px;
                height: 6px;
            }
            .benefits-section {
                margin-top: 24px;
            }
            .benefits-outer-box {
                padding: 16px 12px;
            }
            .benefits-container {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            .benefit-item {
                padding: 0 8px;
            }
            .benefit-icon {
                font-size: 1.6rem;
                margin-bottom: 10px;
            }
            .benefit-title {
                font-size: 0.75rem;
            }
            .newsletter-section {
                padding: 40px 16px 48px 16px;
            }
            .newsletter-title {
                font-size: 1.5rem;
            }
            .newsletter-subtitle {
                font-size: 0.8rem;
            }
            .newsletter-form {
                border-radius: 12px;
            }
            .newsletter-input,
            .newsletter-btn {
                border-radius: 12px;
                padding: 12px 16px;
                font-size: 0.9rem;
            }
            .view-all-products-btn {
                padding: 12px 28px;
                font-size: 0.9rem;
            }
        }
        @media (max-width: 480px) {
            .navbar {
                padding: 10px 12px;
            }
            .navbar-logo {
                font-size: 1.2rem;
            }
            .hero {
                margin: 20px auto 0 auto;
                gap: 16px;
            }
            .hero-title {
                font-size: 1.3rem;
            }
            .hero-desc {
                font-size: 0.85rem;
            }
            .hero-btn {
                padding: 8px 20px;
                font-size: 0.85rem;
            }
            .hero-image img {
                width: 200px;
            }
            .features-section {
                margin: 32px auto 0 auto;
                gap: 12px;
            }
            .feature-card {
                padding: 16px 12px;
            }
            .feature-icon {
                font-size: 1.4rem;
            }
            .feature-title {
                font-size: 0.9rem;
            }
            .feature-desc {
                font-size: 0.8rem;
            }
            .products-section {
                margin: 48px auto 0 auto;
            }
            .products-title {
                font-size: 1.2rem;
            }
            .products-subtitle {
                font-size: 0.8rem;
            }
            .products-grid {
                gap: 12px;
            }
            .product-card {
                border-radius: 8px;
            }
            .product-image-container {
                height: 160px;
            }
            .product-content {
                padding: 8px;
            }
            .product-name {
                font-size: 0.8rem;
            }
            .product-price {
                font-size: 0.85rem;
            }
            .view-details-btn {
                padding: 5px 8px;
                font-size: 0.7rem;
            }
            .shop-category-title {
                font-size: 1.2rem;
            }
            .shop-category-subtitle {
                font-size: 0.8rem;
            }
            .shop-category-card {
                padding: 12px 8px;
            }
            .shop-category-img {
                width: 60px;
                height: 60px;
            }
            .shop-category-name {
                font-size: 0.8rem;
            }
            .shop-category-btn {
                padding: 5px 10px;
                font-size: 0.7rem;
            }
            .fullimg-hero-carousel {
                min-height: 40vh;
            }
            .fullimg-hero-category {
                font-size: 1rem;
            }
            .fullimg-hero-tagline {
                font-size: 0.75rem;
            }
            .fullimg-hero-btns {
                gap: 6px;
                margin-bottom: 16px;
            }
            .fullimg-hero-btn {
                padding: 6px 16px;
                font-size: 0.75rem;
            }
            .fullimg-hero-dots {
                right: 12px;
                gap: 8px;
            }
            .fullimg-hero-dot {
                width: 5px;
                height: 5px;
            }
            .benefits-section {
                margin-top: 20px;
            }
            .benefits-outer-box {
                padding: 12px 8px;
            }
            .benefits-container {
                gap: 12px;
            }
            .benefit-item {
                padding: 0 6px;
            }
            .benefit-icon {
                font-size: 1.4rem;
                margin-bottom: 8px;
            }
            .benefit-title {
                font-size: 0.7rem;
            }
            .newsletter-section {
                padding: 32px 12px 40px 12px;
            }
            .newsletter-title {
                font-size: 1.3rem;
            }
            .newsletter-subtitle {
                font-size: 0.8rem;
            }
            .newsletter-form {
                border-radius: 8px;
            }
            .newsletter-input,
            .newsletter-btn {
                border-radius: 8px;
                padding: 10px 12px;
                font-size: 0.8rem;
            }
            .view-all-products-btn {
                padding: 10px 24px;
                font-size: 0.8rem;
            }
        }
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
        @media (max-width: 768px) {
            .topbar {
                display: none;
            }
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
            position: relative;
            z-index: 100;
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
        @media (max-width: 768px) {
            .navbar-search {
                display: none;
            }
            .navbar-search-icons {
                gap: 12px;
            }
            .navbar-icons {
                gap: 12px;
            }
            .navbar-icons a {
                font-size: 1.2rem;
            }
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
        .section-divider {
            background: #ececec;
        }
        #contact {
            color: #232526;
        }
        .fullimg-hero-carousel {
            position: relative;
            width: 100vw;
            left: 50%;
            right: 50%;
            margin-left: -50vw;
            margin-right: -50vw;
            min-height: 80vh;
            max-height: 700px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .fullimg-hero-slide {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background-size: cover;
            background-position: center;
            opacity: 0;
            transition: opacity 0.7s cubic-bezier(.4,0,.2,1);
            z-index: 1;
        }
        .fullimg-hero-slide.active {
            opacity: 1;
            z-index: 2;
        }
        .fullimg-hero-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(120deg, rgba(34,34,34,0.45) 0%, rgba(255,214,0,0.18) 100%);
            z-index: 3;
        }
        .fullimg-hero-content {
            position: relative;
            z-index: 4;
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .fullimg-hero-category {
            font-size: 1.3rem;
            font-weight: 900;
            letter-spacing: 1.5px;
            margin-bottom: 12px;
            color: #fff;
            text-shadow: 0 2px 16px rgba(0,0,0,0.18);
        }
        .fullimg-hero-category .gold {
            color: #ffd600;
            background: linear-gradient(90deg, #ffd600 0%, #ffb300 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .fullimg-hero-tagline {
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 24px;
            color: #fffbe6;
            text-shadow: 0 2px 12px rgba(0,0,0,0.18);
        }
        .fullimg-hero-btns {
            display: flex;
            gap: 22px;
            justify-content: center;
            margin-bottom: 32px;
        }
        .fullimg-hero-btn {
            padding: 15px 38px;
            font-size: 1.08rem;
            font-weight: 800;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            transition: background 0.18s, color 0.18s, box-shadow 0.18s;
            box-shadow: 0 2px 18px rgba(255,214,0,0.10);
            text-decoration: none;
            display: inline-block;
        }
        .fullimg-hero-btn.gold {
            background: linear-gradient(90deg, #ffd600 0%, #ffb300 100%);
            color: #232526;
            border: 2.5px solid #ffd600;
        }
        .fullimg-hero-btn.gold:hover {
            background: #ffb300;
            color: #fff;
            box-shadow: 0 4px 32px rgba(255,214,0,0.22);
        }
        .fullimg-hero-btn.outline {
            background: #fff;
            color: #232526;
            border: 2.5px solid #ffd600;
        }
        .fullimg-hero-btn.outline:hover {
            background: #fffbe6;
            color: #bfa800;
        }
        .fullimg-hero-dots {
            display: flex;
            flex-direction: column;
            gap: 16px;
            position: absolute;
            top: 50%;
            right: 32px;
            left: auto;
            bottom: auto;
            transform: translateY(-50%);
            margin: 0;
            z-index: 5;
            justify-content: flex-start;
            align-items: center;
        }
        .fullimg-hero-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #ffd600;
            opacity: 0.35;
            border: 2px solid #ffd600;
            cursor: pointer;
            transition: opacity 0.2s, background 0.2s;
            display: flex;
            flex-direction: column;
            
        }
        .fullimg-hero-dot.active {
            opacity: 1;
            background: #ffb300;
        }
        @media (max-width: 900px) {
            .fullimg-hero-category { font-size: 1.5rem; }
            .fullimg-hero-content { max-width: 98vw; padding: 0 20px; }
            .fullimg-hero-dots {
                right: 20px;
            }
        }
        @media (max-width: 600px) {
            .fullimg-hero-category { font-size: 1.1rem; }
            .fullimg-hero-btns { flex-direction: column; gap: 12px; }
            .fullimg-hero-content { padding: 0 16px; }
            .fullimg-hero-dots {
                right: 16px;
            }
        }
        @media (max-width: 480px) {
            .fullimg-hero-content { padding: 0 12px; }
            .fullimg-hero-dots {
                right: 12px;
            }
        }
        /* Shop by Category Section */
        .shop-category-section {
            margin: 100px auto 0 auto;
            padding: 40px 20px 0 20px;
            text-align: center;
        }
        .shop-category-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        .shop-category-title .gold {
            color: #ffd600;
            background: linear-gradient(90deg, #ffd600 0%, #ffb300 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .shop-category-subtitle {
            color: #555;
            font-size: 1.08rem;
            margin-bottom: 32px;
        }
        .shop-category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 32px;
        }
        .shop-category-card {
            background: rgba(255,255,255,0.92);
            border-radius: 22px;
            box-shadow: 0 8px 32px 0 rgba(255,214,0,0.08), 0 2px 12px rgba(0,0,0,0.04);
            padding: 24px 18px 18px 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border: 1.5px solid rgba(255,255,255,0.08);
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .shop-category-card:hover {
            box-shadow: 0 12px 40px 0 rgba(255,214,0,0.13), 0 4px 24px rgba(0,0,0,0.08);
            transform: translateY(-6px) scale(1.03);
        }
        .shop-category-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 16px;
            margin-bottom: 16px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(255,214,0,0.10);
        }
        .shop-category-name {
            font-size: 1.08rem;
            font-weight: 700;
            color: #232526;
            margin-bottom: 12px;
            text-align: center;
        }
        .shop-category-btn {
            background: linear-gradient(90deg, #ffd600 0%, #ffb300 100%);
            color: #232526;
            font-weight: 700;
            border: none;
            border-radius: 999px;
            padding: 8px 22px;
            font-size: 1rem;
            box-shadow: 0 2px 8px rgba(255,214,0,0.10);
            cursor: pointer;
            transition: background 0.18s, color 0.18s;
            text-decoration: none;
            display: inline-block;
        }
        .shop-category-btn:hover {
            background: #ffb300;
            color: #fff;
            text-decoration: none;
        }
        @media (max-width: 600px) {
            .shop-category-title { font-size: 1.2rem; }
            .shop-category-grid { gap: 18px; }
            .shop-category-card { padding: 12px 4px 8px 4px; }
            .shop-category-img { width: 80px; height: 80px; }
        }
    .shop-category-carousel-wrapper {
        position: relative;
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        padding: 0 16px;
    }
    .shop-category-carousel {
        display: flex;
        gap: 32px;
        overflow-x: auto;
        scroll-behavior: smooth;
        padding: 24px 32px 18px 32px;
        width: 100%;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE 10+ */
    }
    .shop-category-carousel::-webkit-scrollbar {
        display: none;
    }
    .shop-category-card {
        min-width: 220px;
        max-width: 240px;
        flex: 0 0 23%;
        background: rgba(255,255,255,0.92);
        border-radius: 22px;
        box-shadow: 0 8px 32px 0 rgba(255,214,0,0.08), 0 2px 12px rgba(0,0,0,0.04);
        padding: 24px 18px 18px 18px;
        display: flex;
        flex-direction: column;
        align-items: center;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        border: 1.5px solid rgba(255,255,255,0.08);
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .shop-category-card:hover {
        box-shadow: 0 12px 40px 0 rgba(255,214,0,0.13), 0 4px 24px rgba(0,0,0,0.08);
        transform: translateY(-6px) scale(1.03);
    }
    .shop-category-arrow {
        background: #fff;
        border: none;
        border-radius: 50%;
        width: 44px;
        height: 44px;
        font-size: 1.6rem;
        color: #ffd600;
        box-shadow: 0 2px 8px rgba(255,214,0,0.10);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 2;
        transition: background 0.18s, color 0.18s;
    }
    .shop-category-arrow.left { left: -18px; }
    .shop-category-arrow.right { right: -18px; }
    .shop-category-arrow:hover { background: #ffd600; color: #fff; }
    @media (max-width: 1100px) {
        .shop-category-card { flex: 0 0 32%; }
    }
    @media (max-width: 900px) {
        .shop-category-card { flex: 0 0 48%; }
        .shop-category-carousel-wrapper {
            padding: 0 20px;
        }
    }
    @media (max-width: 768px) {
        .shop-category-card { flex: 0 0 60%; min-width: 200px; }
        .shop-category-carousel { gap: 16px; }
        .shop-category-arrow { width: 40px; height: 40px; font-size: 1.4rem; }
        .shop-category-carousel-wrapper {
            padding: 0 16px;
        }
    }
    @media (max-width: 600px) {
        .shop-category-card { flex: 0 0 75%; min-width: 180px; }
        .shop-category-carousel { gap: 14px; }
        .shop-category-arrow { width: 36px; height: 36px; font-size: 1.2rem; }
        .shop-category-carousel-wrapper {
            padding: 0 12px;
        }
    }
    @media (max-width: 480px) {
        .shop-category-card { flex: 0 0 85%; min-width: 160px; }
        .shop-category-carousel { gap: 12px; }
        .shop-category-arrow { width: 32px; height: 32px; font-size: 1rem; }
        .shop-category-carousel-wrapper {
            padding: 0 8px;
        }
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
    .product-image-box {
        background: #fafbfc;
        border-radius: 18px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        padding: 24px 18px 18px 18px;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 220px;
        height: 300px;
    }
    .product-image {
        width: 100%;
        object-fit: contain;
        background: #fff;
        box-shadow: none;
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
    .product-description {
        font-size: 0.9rem;
        color: #666;
        line-height: 1.4;
        margin-bottom: 16px;
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
    .product-original-price {
        color: #999;
        font-size: 1rem;
        font-weight: 600;
        text-decoration: line-through;
        text-align: center;
    }
    .product-btn {
        background: linear-gradient(90deg, #ffd600 0%, #ffb300 100%);
        color: #232526;
        font-weight: 700;
        border: none;
        border-radius: 999px;
        padding: 10px 24px;
        font-size: 1rem;
        box-shadow: 0 2px 8px rgba(255,214,0,0.10);
        cursor: pointer;
        transition: background 0.18s, color 0.18s;
        margin-top: 4px;
    }
    .product-btn:hover {
        background: #ffb300;
        color: #fff;
    }
    .product-action-icons {
        position: absolute;
        top: 18px;
        right: 18px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        opacity: 0;
        pointer-events: none;
        transform: translateY(10px);
        transition: opacity 0.35s cubic-bezier(.39,.575,.56,1), transform 0.35s cubic-bezier(.39,.575,.56,1);
        z-index: 2;
    }
    .product-card:hover .product-action-icons {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0);
    }
    .product-action-btn {
        background: rgba(255,255,255,0.95);
        border: none;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        color: #2d3436;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        cursor: pointer;
        transition: background 0.2s, color 0.2s, box-shadow 0.2s;
    }
    .product-action-btn:hover {
        background: #ffb300;
        color: #fff;
        box-shadow: 0 4px 16px rgba(255,214,0,0.18);
    }
    .product-hover-cart-btn {
        position: absolute;
        left: 50%;
        bottom: 18px;
        transform: translate(-50%, 20px);
        opacity: 0;
        background: #fff;
        color: #2d3436;
        border: none;
        border-radius: 20px;
        padding: 8px 0;
        width: 70%;
        font-size: 0.9rem;
        font-weight: 700;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        cursor: pointer;
        transition: opacity 0.35s cubic-bezier(.39,.575,.56,1), transform 0.35s cubic-bezier(.39,.575,.56,1);
        z-index: 2;
    }
    .product-card:hover .product-hover-cart-btn {
        opacity: 1;
        transform: translate(-50%, 0);
    }
    .product-hover-cart-btn:hover {
        background: #ffb300;
        color: #fff;
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
        background: #28a745;
        color: white;
    }
    .stock-medium {
        background: #ffc107;
        color: #232526;
    }
    .stock-low {
        background: #dc3545;
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
    /* Benefits Section */
    .benefits-section {
        position: absolute;
        top: 80%;
        left: 50%;
        transform: translateX(-50%);
        max-width: 1200px;
        width: 100%;
        padding: 0 16px;
        text-align: center;
        z-index: 10;
    }
    .benefits-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0;
        background: none;
    }
    .benefit-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 0 18px;
    }
    .benefit-icon {
        font-size: 2.8rem;
        color: var(--accent);
        margin-bottom: 16px;
    }
    .benefit-title {
        font-size: 1rem;
        font-weight: 700;
        color: #fff;
        margin-bottom: 6px;
    }

    @media (max-width: 1200px) {
        .benefits-container { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
        .benefits-container { grid-template-columns: 1fr; }
    }
    /* Benefits Section Outer Box */
    .benefits-outer-box {
        background:rgb(0, 0, 0);
        border-radius: 0;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.10);
        padding: 36px 32px 32px 32px;
        margin-top: 0;
        margin-bottom: 0;
        max-width: 1200px;
        width: 100%;
    }
    /* THEMED NEWSLETTER SECTION - BLACK BG */
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
    @media (max-width: 700px) {
        .newsletter-title { font-size: 1.5rem; }
        .newsletter-form { flex-direction: column; border-radius: 32px; max-width: 98vw; }
        .newsletter-input, .newsletter-btn { border-radius: 32px; width: 100%; padding: 14px 16px; font-size: 1rem; }
        .newsletter-btn { margin-top: 10px; }
    }
    @media (max-width: 480px) {
        .newsletter-title { font-size: 1.3rem; }
        .newsletter-subtitle { font-size: 0.9rem; }
        .newsletter-form { border-radius: 16px; }
        .newsletter-input, .newsletter-btn { border-radius: 16px; padding: 12px 14px; font-size: 0.9rem; }
    }
    .view-all-products-btn {
        display: inline-block;
        background: #ffd600;
        color: #23211a;
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        font-size: 1.08rem;
        padding: 16px 44px;
        border-radius: 999px;
        text-decoration: none;
        letter-spacing: 0.08em;
        box-shadow: 0 2px 8px 0 rgba(255,214,0,0.08);
        transition: background 0.18s, color 0.18s, transform 0.18s;
        border: none;
    }
    .view-all-products-btn:hover {
        background: #ffe066;
        color: #23211a;
        transform: scale(0.97);
    }
    @media (max-width: 768px) {
        .view-all-products-btn {
            padding: 14px 32px;
            font-size: 1rem;
        }
    }
    @media (max-width: 480px) {
        .view-all-products-btn {
            padding: 12px 28px;
            font-size: 0.9rem;
        }
    }
    </style>
</head>
<body>
    <?php include 'components/navbar.php'; ?>
    <div class="fullimg-hero-carousel" id="fullimgHeroCarousel">
        <div class="fullimg-hero-slide active" style="background-image:url('https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=1200&q=80');"></div>
        <div class="fullimg-hero-slide" style="background-image:url('https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80');"></div>
        <div class="fullimg-hero-slide" style="background-image:url('https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=1200&q=80');"></div>
        <div class="fullimg-hero-slide" style="background-image:url('https://images.unsplash.com/photo-1503736334956-4c8f8e92946d?auto=format&fit=crop&w=1200&q=80');"></div>
        <div class="fullimg-hero-overlay"></div>
        <div class="fullimg-hero-content">
            <div class="fullimg-hero-category" id="fullimgHeroCategory"><span class="gold">Jewellery</span></div>
            <div class="fullimg-hero-tagline" id="fullimgHeroTagline">Timeless elegance, crafted for you.</div>
            <div class="fullimg-hero-btns">
                <a href="products/" class="fullimg-hero-btn gold" id="fullimgHeroBtn">Shop Jewellery <i class="bi bi-arrow-right" style="margin-left:8px;"></i></a>
                <a href="category/" class="fullimg-hero-btn outline">View Collections</a>
            </div>
        </div>
        <div class="fullimg-hero-dots">
            <div class="fullimg-hero-dot active" data-index="0"></div>
            <div class="fullimg-hero-dot" data-index="1"></div>
            <div class="fullimg-hero-dot" data-index="2"></div>
            <div class="fullimg-hero-dot" data-index="3"></div>
        </div>
    </div>
    <!-- Benefits Section -->
    <section class="benefits-section benefits-outer-box">
        <div class="benefits-container">
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="bi bi-headset"></i>
                </div>
                <h3 class="benefit-title">Customer Support</h3>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="bi bi-star-fill"></i>
                </div>
                <h3 class="benefit-title">Best Seller</h3>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="bi bi-award"></i>
                </div>
                <h3 class="benefit-title">Premium Quality</h3>
            </div>
            <div class="benefit-item">
                <div class="benefit-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h3 class="benefit-title">Safe & Secure Checkout</h3>
            </div>
        </div>
    </section>
    <!-- Shop by Category Section -->
    <section class="shop-category-section">
        <div class="shop-category-title"><span class="gold">Shop by Category</span></div>
        <div class="shop-category-subtitle">Find your favorites by category.</div>
        <div class="shop-category-carousel-wrapper">
            <div class="shop-category-carousel" id="catCarousel">
            <?php foreach ($categories as $cat):
                $img = $cat['image'] ? 'uploads/categories/' . htmlspecialchars($cat['image']) : 'https://via.placeholder.com/120x120?text=No+Image';
            ?>
                <div class="shop-category-card">
                    <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>" class="shop-category-img" />
                    <div class="shop-category-name"><?php echo htmlspecialchars($cat['name']); ?></div>
                    <a href="products/?category=<?php echo $cat['category_id']; ?>" class="shop-category-btn">Shop <?php echo htmlspecialchars($cat['name']); ?></a>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
    </section>
    <!-- Featured Products Section -->
    <section class="products-section" id="products">
        <div class="products-title"><span class="gold">Featured Products</span></div>
        <div class="products-subtitle">Discover our handpicked collection</div>
        <div class="products-grid">
        <?php foreach ($products as $prod):
            $imgs = $productImages[$prod['product_id']] ?? [];
            if (count($imgs) > 0) {
                $img = 'uploads/products/' . htmlspecialchars(basename($imgs[0]));
            } else {
                $img = 'https://via.placeholder.com/120x120?text=No+Image';
            }
            
            // Determine stock status
            $stockClass = 'stock-high';
            $stockText = 'In Stock';
            if ($prod['stock'] <= 5) {
                $stockClass = 'stock-low';
                $stockText = 'Low Stock';
            } elseif ($prod['stock'] <= 15) {
                $stockClass = 'stock-medium';
                $stockText = 'Limited';
            }
        ?>
            <div class="product-card">
                <div class="product-image-container">
                    <img src="<?php echo $img; ?>" class="product-image" alt="<?php echo htmlspecialchars($prod['name']); ?>" />
                    <div class="product-badge"><?php echo htmlspecialchars($prod['category_name'] ?? 'Uncategorized'); ?></div>
                    <div class="product-stock-badge <?php echo $stockClass; ?>"><?php echo $stockText; ?></div>
                    <div class="product-actions">
                        <button class="action-btn" title="Add to Cart">
                            <i class="bi bi-cart-plus"></i>
                        </button>
                        <button class="action-btn" title="Quick View">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="product-content">
                    <div class="product-category"><?php echo htmlspecialchars($prod['category_name'] ?? 'Uncategorized'); ?></div>
                    <h3 class="product-name"><?php echo htmlspecialchars($prod['name']); ?></h3>
                    <?php if (!empty($prod['description'])): ?>
                        <div class="product-description"><?php echo htmlspecialchars(substr($prod['description'], 0, 80)) . (strlen($prod['description']) > 80 ? '...' : ''); ?></div>
                    <?php endif; ?>
                    <div class="product-info">
                        <div class="product-price">₹<?php echo number_format($prod['price'], 2); ?></div>
                    </div>
                    <div class="product-footer">
                        <button class="view-details-btn">View Details</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (empty($products)): ?>
            <div style="text-align:center; color:#aaa; padding:24px; grid-column: 1 / -1;">No products found</div>
        <?php endif; ?>
        </div>
        <div style="width:100%;display:flex;justify-content:center;margin-top:32px;">
            <a href="products/" class="view-all-products-btn">View All Products</a>
        </div>
    </section>

    <!-- Newsletter Signup Section -->
    <!-- Removed newsletter section from here -->

    <script>
        //Carousel Sections
        // Full-image carousel data
        const fullimgCarouselData = [
        {
            category: 'Jewellery',
            tagline: 'Timeless elegance, crafted for you.',
            btn: 'Shop Jewellery',
            link: 'products/'
        },
        {
            category: 'Furniture',
            tagline: 'Style and comfort for your home.',
            btn: 'Shop Furniture',
            link: 'products/'
        },
        {
            category: 'Electronics',
            tagline: 'Innovative tech for modern living.',
            btn: 'Shop Electronics',
            link: 'products/'
        },
        {
            category: 'Car',
            tagline: 'Drive your dreams in style.',
            btn: 'Shop Cars',
            link: 'products/'
        }
    ];
    let fullimgCarouselIndex = 0;
    const fullimgSlides = document.querySelectorAll('.fullimg-hero-slide');
    const fullimgCatEl = document.getElementById('fullimgHeroCategory');
    const fullimgTagEl = document.getElementById('fullimgHeroTagline');
    const fullimgBtnEl = document.getElementById('fullimgHeroBtn');
    const fullimgDots = document.querySelectorAll('.fullimg-hero-dot');
    function updateFullimgCarousel(idx) {
        fullimgSlides.forEach((slide, i) => slide.classList.toggle('active', i === idx));
        const d = fullimgCarouselData[idx];
        fullimgCatEl.innerHTML = `<span class='gold'>${d.category}</span>`;
        fullimgTagEl.textContent = d.tagline;
        fullimgBtnEl.innerHTML = d.btn + ' <i class="bi bi-arrow-right" style="margin-left:8px;"></i>';
        fullimgBtnEl.href = d.link;
        fullimgDots.forEach((dot, i) => dot.classList.toggle('active', i === idx));
    }
    fullimgDots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
            fullimgCarouselIndex = i;
            updateFullimgCarousel(fullimgCarouselIndex);
        });
    });
    setInterval(() => {
        fullimgCarouselIndex = (fullimgCarouselIndex + 1) % fullimgCarouselData.length;
        updateFullimgCarousel(fullimgCarouselIndex);
    }, 5000);

    // Shop by Category Carousel
    const catCarousel = document.getElementById('catCarousel');
    if (catCarousel) {
        // Clone all cards for infinite effect
        const originalCards = Array.from(catCarousel.children);
        originalCards.forEach(card => catCarousel.appendChild(card.cloneNode(true)));
        
        // Calculate original width with proper gap
        const getGap = () => {
            const computedStyle = window.getComputedStyle(catCarousel);
            return parseInt(computedStyle.gap) || 32;
        };
        
        const originalWidth = originalCards.reduce((acc, card) => acc + card.offsetWidth + getGap(), 0);
        let autoScrollInterval;
        
        function startAutoScroll() {
            // Only auto-scroll on larger screens
            if (window.innerWidth <= 768) return;
            
            autoScrollInterval = setInterval(() => {
                if (!catCarousel) return;
                // If at end of original set, jump to equivalent position in clones
                if (catCarousel.scrollLeft >= originalWidth) {
                    catCarousel.scrollLeft = catCarousel.scrollLeft - originalWidth;
                }
                catCarousel.scrollBy({ left: 2, behavior: 'smooth' });
            }, 20); // Adjust speed here
        }
        
        function stopAutoScroll() { 
            if (autoScrollInterval) {
                clearInterval(autoScrollInterval);
                autoScrollInterval = null;
            }
        }
        
        catCarousel.addEventListener('mouseenter', stopAutoScroll);
        catCarousel.addEventListener('mouseleave', startAutoScroll);
        
        // Handle window resize
        window.addEventListener('resize', () => {
            stopAutoScroll();
            if (window.innerWidth > 768) {
                startAutoScroll();
            }
        });
        
        // Start auto-scroll initially
        startAutoScroll();
    }
    </script>
    <?php include __DIR__ . '/components/footer.php'; ?>
</body>
</html> 