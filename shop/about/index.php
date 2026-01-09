<?php
session_start();
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About - GoldenDream Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
            --card-bg: #fff;
            --radius: 20px;
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
        
        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 16px;
        }
        .main-content {
            padding: 40px 0;
        }
        .about-section {
            padding: 40px;
            margin-bottom: 40px;
        }
        .about-info h3 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: 25px;
            position: relative;
        }
        .about-info h3:after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--accent);
            border-radius: 2px;
        }
        .about-info p {
            font-size: 1.1rem;
            color: #666;
            line-height: 1.8;
            margin-bottom: 20px;
        }
        .mission-section {
            padding: 40px;
            margin-bottom: 40px;
        }
        .mission-section h3 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: 20px;
            position: relative;
        }
        .mission-section h3:after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--accent);
            border-radius: 2px;
        }
        .mission-section > p {
            font-size: 1.1rem;
            color: #666;
            font-style: italic;
            margin-bottom: 25px;
        }
        .mission-content {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 16px;
            border-left: 4px solid var(--accent);
        }
        .mission-content p {
            font-size: 1.1rem;
            color: #666;
            line-height: 1.8;
            margin-bottom: 20px;
        }
        .mission-points {
            list-style: none;
            padding: 0;
            margin: 25px 0;
        }
        .mission-points li {
            font-size: 1.1rem;
            color: #666;
            line-height: 1.8;
            margin-bottom: 15px;
            padding-left: 30px;
            position: relative;
        }
        .mission-points li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: var(--accent);
            font-weight: bold;
            font-size: 1.2rem;
        }

        .cta-section {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            border-radius: 20px;
            padding: 50px 40px;
            text-align: center;
            color: var(--secondary);
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            margin-bottom: 20px;
        }
        .cta-section h3 {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 15px;
        }
        .cta-section p {
            font-size: 1.1rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        .cta-btn {
            display: inline-block;
            background: var(--secondary);
            color: var(--accent);
            padding: 18px 45px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .cta-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        @media (max-width: 768px) {
            .about-section, .mission-section, .stats-section {
                padding: 30px 20px;
                margin-bottom: 30px;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
            .stat-number {
                font-size: 2rem;
            }
            .cta-section {
                padding: 40px 20px;
            }
            .page-title {
                font-size: 2rem;
            }
        }
        
        /* Mission Vision Section */
        .mission-vision {
            padding: 80px 0;
        }
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: 60px;
        }
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
            margin-bottom: 80px;
        }
        .mission-card, .vision-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 40px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .mission-card:hover, .vision-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 48px rgba(0,0,0,0.15);
        }
        .mission-card::before, .vision-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent) 0%, var(--accent-dark) 100%);
        }
        .card-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            font-size: 2rem;
            color: var(--secondary);
        }
        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 15px;
        }
        .card-text {
            font-size: 1.1rem;
            color: #666;
            line-height: 1.7;
        }
        
        /* Values Section */
        .values-section {
            padding: 80px 0;
            background: #fff;
            border-radius: var(--radius);
            margin: 40px 0;
        }
        .values-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }
        .value-item {
            text-align: center;
            padding: 30px 20px;
        }
        .value-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.8rem;
            color: var(--secondary);
        }
        .value-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 12px;
        }
        .value-text {
            font-size: 1rem;
            color: #666;
            line-height: 1.6;
        }
        
        /* Team Section */
        .team-section {
            padding: 80px 0;
        }
        .team-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
        }
        .team-member {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 30px 25px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .team-member:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 48px rgba(0,0,0,0.15);
        }
        .member-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            color: var(--secondary);
        }
        .member-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 8px;
        }
        .member-role {
            font-size: 0.9rem;
            color: var(--accent-dark);
            font-weight: 600;
            margin-bottom: 15px;
        }
        .member-bio {
            font-size: 0.95rem;
            color: #666;
            line-height: 1.6;
        }
        .social-links {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 20px;
        }
        .social-link {
            width: 36px;
            height: 36px;
            background: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .social-link:hover {
            background: var(--accent);
            color: var(--secondary);
            transform: scale(1.1);
        }
        
        /* Story Section */
        .story-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: var(--radius);
            margin: 40px 0;
        }
        .story-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        .story-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--secondary);
            margin-bottom: 30px;
        }
        .story-text {
            font-size: 1.1rem;
            color: #666;
            line-height: 1.8;
            margin-bottom: 30px;
        }
        .story-highlight {
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: var(--secondary);
            padding: 20px 30px;
            border-radius: 16px;
            font-size: 1.2rem;
            font-weight: 700;
            display: inline-block;
            margin: 20px 0;
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .hero-stats { grid-template-columns: repeat(2, 1fr); }
            .team-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 900px) {
            .hero-title { font-size: 2.8rem; }
            .cards-grid { grid-template-columns: 1fr; }
            .values-grid { grid-template-columns: repeat(2, 1fr); }
            .team-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 600px) {
            .hero-title { font-size: 2.2rem; }
            .hero-stats { grid-template-columns: 1fr; }
            .values-grid { grid-template-columns: 1fr; }
            .team-grid { grid-template-columns: 1fr; }
            .container { padding: 0 15px; }
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
    </style>
</head>
<body>
    <?php 
    $_GET['from_products'] = true;
    include '../components/navbar.php'; 
    ?>
    <div class="page-header">
        <div class="container">
            <h1 class="page-title">About Us</h1>
            <div class="breadcrumb">
                <a href="../index.php">Home</a>
                <span class="separator">></span>
                <span class="current">About</span>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="main-content">
            <div class="about-section">
            <div class="about-info">
                <h3>Who We Are</h3>
                <p>The GD Store is the official online store of Progee Dee Ventures, created specifically for our qualified clients to exchange Progee Dee company vouchers for premium items of their choice. In addition to the GoldenDream Shop experience, we have a diverse range of items, from fashion and lifestyle products to essentials, that provide enduring value and joy.</p>
                
                <p>In our commitment to excellence, we deliver remarkable value to the customer as our core value is remarkable service and outstanding dependable quality.</p>
            </div>

        </div>

        <!-- Mission Section -->
        <div class="mission-section">
            <h3>Our Mission</h3>
            <div class="mission-content">
                <p>GD Store's mission centers on trust, transparency, and service. It focuses on a meaningful and purposeful shopping experience using a customer-first philosophy.</p>
            </div>
        </div>





        <!-- CTA Section -->
        <div class="cta-section">
            <h3>Ready to Shop?</h3>
            <p>Explore our amazing collection of products and discover quality items that will enhance your lifestyle.</p>
            <a href="../products/" class="cta-btn">Shop Now</a>
            </div>
        </div>
    </div>
    <?php 
    $_GET['from_products'] = true;
    include '../components/footer.php'; 
    ?>
</body>
</html> 