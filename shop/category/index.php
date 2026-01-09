<?php
session_start();
require_once __DIR__ . '/../config/config.php';

// Database connection
$db = new Database();
$conn = $db->getConnection();

// Fetch all categories
$categories = $conn->query('SELECT * FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Categories - GoldenDream Shop</title>
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
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 16px;
        }
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
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 32px;
            margin-bottom: 40px;
        }
        .category-card {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 32px 18px 24px 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: box-shadow 0.2s, transform 0.2s;
            border: 1.5px solid rgba(255,255,255,0.08);
        }
        .category-card:hover {
            box-shadow: 0 12px 40px 0 rgba(255,214,0,0.13), 0 4px 24px rgba(0,0,0,0.08);
            transform: translateY(-6px) scale(1.03);
        }
        .category-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 16px;
            margin-bottom: 16px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(255,214,0,0.10);
        }
        .category-name {
            font-size: 1.18rem;
            font-weight: 700;
            color: #232526;
            margin-bottom: 12px;
            text-align: center;
        }
        .category-btn {
            background: linear-gradient(90deg, #ffd600 0%, #ffb300 100%);
            color: #232526;
            font-weight: 700;
            border: none;
            border-radius: 999px;
            padding: 10px 28px;
            font-size: 1rem;
            box-shadow: 0 2px 8px rgba(255,214,0,0.10);
            cursor: pointer;
            transition: background 0.18s, color 0.18s;
            text-decoration: none;
        }
        .category-btn:hover {
            background: #ffb300;
            color: #fff;
        }
        @media (max-width: 600px) {
            .page-title { font-size: 1.2rem; }
            .categories-grid { gap: 18px; }
            .category-card { padding: 12px 4px 8px 4px; }
            .category-img { width: 80px; height: 80px; }
        }
        /* Navbar and Footer Styles (copied from products page) */
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
    </style>
</head>
<body>
    <?php 
    $_GET['from_products'] = true;
    include '../components/navbar.php'; 
    ?>
    <div class="page-header">
        <div class="container">
            <h1 class="page-title">Categories</h1>
            <div class="breadcrumb">
                <a href="../index.php">Home</a>
                <span class="separator">></span>
                <span class="current">Categories</span>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="categories-grid">
            <?php foreach ($categories as $cat):
                $img = $cat['image'] ? '../uploads/categories/' . htmlspecialchars($cat['image']) : 'https://via.placeholder.com/120x120?text=No+Image';
            ?>
                <div class="category-card">
                    <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>" class="category-img" />
                    <div class="category-name"><?php echo htmlspecialchars($cat['name']); ?></div>
                    <a href="../products/index.php?category=<?php echo $cat['category_id']; ?>" class="category-btn">View Products</a>
                </div>
            <?php endforeach; ?>
            <?php if (empty($categories)): ?>
                <div style="text-align:center; color:#aaa; padding:24px; grid-column: 1 / -1;">No categories found</div>
            <?php endif; ?>
        </div>
    </div>
    <?php 
    $_GET['from_products'] = true;
    include '../components/footer.php'; 
    ?>
</body>
</html> 