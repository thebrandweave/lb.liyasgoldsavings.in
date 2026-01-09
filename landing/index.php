<?php
// Simple PHP for dynamic date
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoldenDream - Your Financial Future</title>
    <link rel="icon" type="image/png" href="./landing_assets/images/gdLogo.png">
    <link rel="shortcut icon" type="image/png" href="./landing_assets/images/gdLogo.png">
    <link rel="apple-touch-icon" href="./landing_assets/images/gdLogo.png">
    <meta name="description" content="GoldenDream - Strategic investments across high-growth sectors. We partner with visionary companies to create sustainable value and drive innovation.">
    <meta name="keywords" content="venture capital, private equity, investments, portfolio companies, technology, healthcare, renewable energy, GoldenDream, India">
    <meta name="author" content="GoldenDream">
    <meta name="robots" content="index, follow">
    <meta name="language" content="English">
    <meta name="revisit-after" content="7 days">
    

    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="GoldenDream - Building Tomorrow's Market Leaders">
    <meta property="og:description" content="Strategic investments across high-growth sectors that are shaping tomorrow's economy.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://goldendream.in">
    <meta property="og:image" content="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=1200&auto=format&fit=crop">
    <meta property="og:site_name" content="GoldenDream">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="GoldenDream - Building Tomorrow's Market Leaders">
    <meta name="twitter:description" content="Strategic investments across high-growth sectors that are shaping tomorrow's economy.">
    <meta name="twitter:image" content="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=1200&auto=format&fit=crop">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "GoldenDream",
        "description": "Strategic investments across high-growth sectors that are shaping tomorrow's economy.",
        "url": "https://goldendream.in",
        "logo": "https://placehold.co/200x200/0a2540/ffffff?text=GD&font=raleway",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "2-108/C-7, Ground Floor, Sri Mantame Complex, Near Soorya Infotech Park, Kurnadu Post, Mudipu Road, Bantwal- 574153",
            "addressLocality": "Bantwal",
            "addressRegion": "Karnataka",
            "postalCode": "574153",
            "addressCountry": "IN"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "telephone": "+91-99951-94472",
            "contactType": "customer service",
            "email": "goldendream175@gmail.com"
        },
        "sameAs": [
            "https://linkedin.com/company/goldendream",
            "https://twitter.com/goldendream"
        ]
    }
    </script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --c-primary: #0a0a0a;
            --c-secondary: #dc3545;
            --c-accent: #00d4aa;
            --c-text: #1a1a1a;
            --c-text-muted: #6b7280;
            --c-bg-light: #fafafa;
            --c-border: #e5e7eb;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --primary-color: #0a0a0a;
            --secondary-color: #ffffff;
            --accent-color: #dc3545;
            --text-color: #ffffff;
            --subtext-color: #e5e7eb;
            --border-color: rgba(255, 255, 255, 0.1);
            --btn-bg: #dc3545;
            --btn-text: #ffffff;
            --btn-hover-bg: #c82333;
            --success: #00d4aa;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #fff;
            color: var(--c-text);
            line-height: 1.6;
            font-weight: 400;
        }

        /* Navbar Styles */
        .container {
            max-width: 100vw !important;
            margin: 0 auto;
            padding: 0 24px;
            overflow-x: hidden;
        }

        header {
            background: rgba(255, 255, 255, 0);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
            max-width: 100vw !important;
            overflow-x: hidden;
            transition: all 0.3s ease;
        }

        header.scrolled {
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(15px);
        }

        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 0;
            transition: all 0.3s ease;
        }

        .nav-left {
            display: flex;
            align-items: center;
            gap: 32px;
        }

        .logo {
            font-size: 1.7rem;
            font-weight: 700;
            color: #ffffff;
            text-decoration: none;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .logo img {
            transition: all 0.3s ease;
        }

        header.scrolled .logo img {
            opacity: 0;
            transform: scale(0.8);
        }

        .logo:hover {
            color: var(--accent-color);
            transition: color 0.3s ease;
        }

        .nav-links {
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .nav-links a {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            padding: 8px 0;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--accent-color);
            transition: width 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--accent-color);
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        @media (max-width: 1024px) {
            .nav-links {
                display: none;
            }
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .country-flag {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-color);
            font-weight: 500;
        }

        .country-flag img {
            width: 24px;
            height: 16px;
            border-radius: 2px;
        }

        .login-dropdown {
            position: relative;
        }

        .login-btn {
            background: var(--btn-bg);
            color: var(--btn-text);
            border: none;
            border-radius: 6px;
            padding: 10px 24px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .login-btn:hover {
            background: var(--btn-hover-bg);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 91, 255, 0.3);
        }

        .login-btn i {
            font-size: 1.1rem;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
        }

        .login-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            background: #1f2937;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            z-index: 1001;
            width: 90%;
            max-width: 400px;
            opacity: 0;
            transition: all 0.3s ease;
            max-height: 90vh;
            overflow-x: hidden;
        }

        .login-modal.active {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .modal-header h2 {
            font-size: 1.5rem;
            color: #ffffff;
            margin: 0;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #ffffff;
            cursor: pointer;
            padding: 8px;
            transition: color 0.3s ease;
        }

        .close-modal:hover {
            color: var(--accent-color);
        }

        .login-options {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .login-option {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            border-radius: 12px;
            background: #374151;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: #ffffff;
        }

        .login-option:hover {
            background: #4b5563;
            transform: translateX(5px);
        }

        .login-option i {
            font-size: 1.5rem;
            color: var(--accent-color);
            transition: transform 0.3s ease;
        }

        .login-option:hover i {
            transform: scale(1.1);
        }

        .option-content {
            flex: 1;
        }

        .option-title {
            font-weight: 600;
            margin-bottom: 4px;
            color: #ffffff;
        }

        .option-description {
            font-size: 0.85rem;
            color: #d1d5db;
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--text-color);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 8px;
        }

        .mobile-menu {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            padding: 80px 24px 24px;
            max-width: 100vw !important;
            overflow-x: hidden;
        }

        .mobile-menu.active {
            display: block;
        }

        .mobile-menu-close {
            position: absolute;
            top: 24px;
            right: 24px;
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .mobile-nav-links {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .mobile-nav-links a {
            color: #fff;
            text-decoration: none;
            font-size: 1.2rem;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .mobile-nav-links a i {
            color: var(--accent-color);
        }

        @media (max-width: 992px) {
            .mobile-menu-btn {
                display: block;
            }
        }

        /* Footer Styles */
        footer {
            background: #0a0a0a;
            color: #fff;
            padding: 80px 0 0;
            position: relative;
            overflow: hidden;
            max-width: 100vw !important;
            overflow-x: hidden;
        }

        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-color), #00d4aa);
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .footer-section {
            margin-bottom: 40px;
        }

        .footer-section h3 {
            color: var(--accent-color);
            font-size: 1.3rem;
            margin-bottom: 24px;
            position: relative;
            padding-bottom: 12px;
        }

        .footer-section h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background: var(--accent-color);
        }

        .footer-section p {
            color: #e5e7eb;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-section ul li {
            margin-bottom: 12px;
        }

        .footer-section ul li a {
            color: #e5e7eb;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-section ul li a:hover {
            color: var(--accent-color);
            transform: translateX(5px);
        }

        .footer-section ul li a i {
            font-size: 0.9rem;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #e5e7eb;
        }

        .contact-item i {
            color: var(--accent-color);
            font-size: 1.2rem;
        }

        .social-links {
            display: flex;
            gap: 16px;
            margin-top: 24px;
        }

        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: #fff;
            transition: all 0.3s ease;
        }

        .social-links a:hover {
            background: var(--accent-color);
            transform: translateY(-3px);
        }

        .copyright {
            text-align: center;
            padding: 24px;
            border-top: 1px solid #1f2937;
            margin-top: 40px;
        }

        .developer-credit {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .last-updated {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #9ca3af;
            font-size: 0.9rem;
        }

        /* Responsive Footer */
        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .contact-item {
                flex-direction: column !important;
                text-align: center !important;
                gap: 8px !important;
            }

            .social-links {
                justify-content: center !important;
            }

            .developer-credit {
                flex-direction: column !important;
                align-items: center !important;
                gap: 8px !important;
            }

            .copyright {
                flex-direction: column !important;
                gap: 12px !important;
                text-align: center !important;
            }

            .last-updated {
                justify-content: center !important;
            }
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            background: none;
            position: relative;
            overflow: hidden;
            margin-top: -100px;
            padding-top: 80px;
        }
        
        .hero .carousel {
            position: relative;
            z-index: 1;
        }
        
        .hero .carousel-inner {
            position: relative;
            z-index: 1;
        }
        
        .hero .carousel-item {
            position: relative;
            z-index: 1;
            transition: all 0.5s ease;
        }
        
        .carousel-item {
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .carousel-item.active,
        .carousel-item-next,
        .carousel-item-prev {
            display: block;
        }
        
        .carousel-item-next:not(.carousel-item-start),
        .active.carousel-item-end {
            transform: translateX(100%);
        }
        
        .carousel-item-prev:not(.carousel-item-end),
        .active.carousel-item-start {
            transform: translateX(-100%);
        }
        
        .carousel {
            overflow: hidden;
        }
        
        .carousel-inner {
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .carousel-item {
            width: 100%;
            height: 100%;
        }
        
        .hero-title,
        .hero-subtitle,
        .hero-card {
            will-change: opacity, transform;
        }
        
        .carousel-item .row {
            min-height: 400px;
            align-items: center;
            padding: 2rem 0;
        }
        
        .carousel-item .col-lg-6 {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 1rem;
        }

        .hero-title {
            font-weight: 800;
            font-size: clamp(2.5rem, 5vw, 4rem);
            color: #fff;
            line-height: 1.1;
            letter-spacing: -0.02em;
            margin-bottom: 1.5rem;
            transition: all 0.5s ease;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.5;
            margin-bottom: 1.5rem;
            max-width: 450px;
            overflow-wrap: break-word;
            word-wrap: break-word;
            transition: all 0.5s ease;
        }

        .hero-img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px rgba(99,91,255,0.10);
            transition: all 0.5s ease;
        }

        .btn {
            font-weight: 600;
            padding: 16px 32px;
            border-radius: 12px;
            border: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: var(--c-secondary);
            color: white;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.6);
            color: white;
        }

        .carousel-control-prev, .carousel-control-next {
            top: 50%;
            transform: translateY(-50%);
            width: 48px;
            height: 48px;
            z-index: 10;
            opacity: 0.85;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 50%;
            border: none;
            transition: all 0.3s ease;
        }

        .carousel-control-prev:hover, .carousel-control-next:hover {
            background: rgba(0, 0, 0, 0.6);
            opacity: 1;
        }

        .carousel-control-prev {
            left: 20px;
            right: auto;
            display: none !important;
        }

        .carousel-control-next {
            right: 20px;
            left: auto;
        }

        .section {
            padding: 6rem 0;
        }

        .section-title {
            font-weight: 800;
            color: var(--c-primary);
            font-size: clamp(2rem, 4vw, 3rem);
            letter-spacing: -0.02em;
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: var(--c-text-muted);
            max-width: 700px;
            margin: 1.5rem auto 0 auto;
        }

        .video-card {
            border-radius: 20px;
            overflow: hidden;
            background: white;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid var(--c-border);
            text-decoration: none;
            color: inherit;
            height: 100%;
        }

        .video-card:hover {
            text-decoration: none;
            color: inherit;
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: transparent;
        }

        .video-thumbnail {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            aspect-ratio: 16/9;
        }

        .play-button-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255,0,0,0.9);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        @media (max-width: 991.98px) {
            .hero {
                top: 0 !important;
                margin-top: -150px !important;
                padding-top: 0 !important;
            }
            .hero-img {
                width: 100%;
                height: 300px;
                margin-bottom: 1rem;
            }
            
            .carousel-item .row {
                min-height: 300px;
            }
            
            .carousel-item .col-lg-6 {
                min-height: 200px;
            }
        }

        @media (max-width: 575.98px) {
            .hero {
                top: 0 !important;
                margin-top: -150px !important;
                padding-top: 0 !important;
            }
            .hero-title {
                font-size: clamp(1.5rem, 3vw, 2rem);
                line-height: 1.4;
                margin-bottom: 0.75rem;
            }
            
            .hero-subtitle {
                font-size: 0.9rem;
                line-height: 1.3;
                margin-bottom: 1rem;
                max-width: 350px;
            }
            
            .carousel-item .row {
                min-height: 250px;
                padding: 1rem 0;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="nav-left">
                    <a href="#" class="logo">
                        <img src="./landing_assets/images/gdLogo.png" alt="Golden Dream Logo" style="height:32px;"> Golden Dream
                    </a>
                    <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="nav-links">
                        <a href="./">Home</a>
                        <a href="./about.php">About Us</a>
                        <!-- <a href="./Gallery">Gallery</a>
                        <a href="./Certificates">Certificates</a>
                        <a href="./SavingsPlan">Savings Plan</a>
                        <a href="./Blog">Blog</a>
                        <a href="./ContactUs">Contact Us</a> -->
                    </div>
                </div>
                <div class="nav-right">
                    <div class="country-flag">
                        <img src="./landing_assets/images/india.png" alt="India Flag">
                        <span>India</span>
                    </div>
                    <div class="login-dropdown">
                        <button class="login-btn" onclick="openLoginModal()">
                            <i class="fas fa-user"></i>
                            Login
                        </button>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- Login Modal -->
    <div class="modal-overlay" id="loginModal">
        <div class="login-modal">
            <div class="modal-header">
                <h2>Choose Login Type</h2>
                <button class="close-modal" onclick="closeLoginModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="login-options">
                <a href="../../customer" class="login-option">
                    <i class="fas fa-user-circle"></i>
                    <div class="option-content">
                        <div class="option-title">Login as Customer</div>
                        <div class="option-description">Access your investment dashboard</div>
                    </div>
                </a>
                <a href="../../promoter/" class="login-option">
                    <i class="fas fa-user-tie"></i>
                    <div class="option-content">
                        <div class="option-title">Login as Promoter</div>
                        <div class="option-description">Manage your promoter account</div>
                    </div>
                </a>
                <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                    <!-- <p style="color: var(--subtext-color); margin-bottom: 15px;">Don't have an account? <a href="https://goldendream.in//refer?id=GDP0001&ref=NTAw" style="color: var(--accent-color); text-decoration: none; font-weight: 600;">Register Now</a></p> -->
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <button class="mobile-menu-close" onclick="toggleMobileMenu()">
            <i class="fas fa-times"></i>
        </button>
        <div class="mobile-nav-links">
            <a href="./"><i class="fas fa-home"></i> Home</a>
            <a href="./about.php"><i class="fas fa-info-circle"></i> About Us</a>
            <!-- <a href="./Gallery"><i class="fas fa-images"></i> Gallery</a>
            <a href="./Certificates"><i class="fas fa-certificate"></i> Certificates</a>
            <a href="./SavingsPlan"><i class="fas fa-piggy-bank"></i> Savings Plan</a>
            <a href="./Blog"><i class="fas fa-blog"></i> Blog</a>
            <a href="./ContactUs"><i class="fas fa-envelope"></i> Contact Us</a> -->
        </div>
    </div>

    <section id="hero" class="hero">
        <!-- Background Carousel -->
        <div id="heroBgCarousel" class="carousel slide hero-bg-carousel" data-bs-ride="carousel" data-bs-interval="5000" data-bs-wrap="true" style="position:absolute;top:0;left:0;width:100%;height:100%;z-index:0;overflow:hidden;">
            <div class="carousel-inner" style="width:100%;height:100%;">
                <div class="carousel-item active" style="width:100%;height:100%;">
                    <img src="https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=1200&q=80" class="d-block w-100 h-100 hero-bg-img" alt="Pro Gee Dee Ventures background" style="object-fit:cover;filter:blur(16px) brightness(0.7);height:100%;">
                </div>
                <div class="carousel-item" style="width:100%;height:100%;">
                    <img src="./assets/black-gold-glitter-background.jpg" class="d-block w-100 h-100 hero-bg-img" alt="Goldendream background" style="object-fit:cover;filter:blur(16px) brightness(0.7);height:100%;">
                </div>
                <div class="carousel-item" style="width:100%;height:100%;">
                    <img src="./assets/colleagues-working-desk.jpg" class="d-block w-100 h-100 hero-bg-img" alt="The Brand Weave background" style="object-fit:cover;filter:blur(16px) brightness(0.7);height:100%;">
                </div>
                <div class="carousel-item" style="width:100%;height:100%;">
                    <img src="./assets/view-brilliant-cartoon-diamond.jpg" class="d-block w-100 h-100 hero-bg-img" alt="Liyas Gold and Diamonds background" style="object-fit:cover;filter:blur(16px) brightness(0.7);height:100%;">
                </div>
                <div class="carousel-item" style="width:100%;height:100%;">
                    <img src="./assets/construction-silhouette.jpg" class="d-block w-100 h-100 hero-bg-img" alt="Liyas Construction background" style="object-fit:cover;filter:blur(16px) brightness(0.7);height:100%;">
                </div>
                <div class="carousel-item" style="width:100%;height:100%;">
                    <img src="./assets/delicious-products-arrangement-bakery.jpg" class="d-block w-100 h-100 hero-bg-img" alt="Liyas Bakes and Cafe background" style="object-fit:cover;filter:blur(16px) brightness(0.7);height:100%;">
                </div>
            </div>
        </div>
        <!-- End Background Carousel -->
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-12">
                    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000" data-bs-wrap="true">
                        <div class="carousel-inner">
                            <!-- Slide 1: Pro Gee Dee Ventures -->
                            <div class="carousel-item active">
                                <div class="row align-items-center">
                                    <div class="col-lg-6 order-lg-1 order-2">
                                        <div class="hero-content">
                                            <h1 class="hero-title">Pro Gee Dee Ventures</h1>
                                            <p class="hero-subtitle">
                                                Strategic investments across technology, gold, diamonds, and digital innovation. Partnering with ambitious companies to drive sustainable growth and shape tomorrow's market leaders.
                                            </p>

                                            <div class="hero-cta mt-4">
                                                <a href="#" class="btn btn-primary btn-lg me-3">
                                                    <i class="fas fa-arrow-right me-2"></i>
                                                    Learn More
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 order-lg-2 order-1 text-center mb-4 mb-lg-0">
                                        <img src="https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=600&q=80" alt="Pro Gee Dee Ventures" class="img-fluid rounded-4 shadow-lg hero-img">
                                    </div>
                                </div>
                            </div>
                            <!-- Slide 2: Goldendream -->
                            <div class="carousel-item">
                                <div class="row align-items-center">
                                    <div class="col-lg-6 order-lg-1 order-2">
                                        <div class="hero-content">
                                            <h1 class="hero-title">Goldendream</h1>
                                            <p class="hero-subtitle">
                                                Premium gold, diamond, and gemstone jewelry with investment plans and customization options. Trusted by 10,000+ clients for quality and ethical sourcing.
                                            </p>

                                            <div class="hero-cta mt-4">
                                                <a href="https://goldendream.in/" target="_blank" class="btn btn-primary btn-lg me-3">
                                                    <i class="fas fa-external-link-alt me-2"></i>
                                                    Visit Website
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 order-lg-2 order-1 text-center mb-4 mb-lg-0">
                                        <img src="./assets/black-gold-glitter-background.jpg" alt="Goldendream" class="img-fluid rounded-4 shadow-lg hero-img">
                                    </div>
                                </div>
                            </div>
                            <!-- Slide 3: The Brand Weave -->
                            <div class="carousel-item">
                                <div class="row align-items-center">
                                    <div class="col-lg-6 order-lg-1 order-2">
                                        <div class="hero-content">
                                            <h1 class="hero-title">The Brand Weave</h1>
                                            <p class="hero-subtitle">
                                                Digital agency specializing in creative solutions, digital marketing, branding, and design. Empowering businesses to grow online with data-driven strategies.
                                            </p>

                                            <div class="hero-cta mt-4">
                                                <a href="https://thebrandweave.com/" target="_blank" class="btn btn-primary btn-lg me-3">
                                                    <i class="fas fa-external-link-alt me-2"></i>
                                                    Visit Website
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 order-lg-2 order-1 text-center mb-4 mb-lg-0">
                                        <img src="./assets/colleagues-working-desk.jpg" alt="The Brand Weave" class="img-fluid rounded-4 shadow-lg hero-img">
                                    </div>
                                </div>
                            </div>
                            <!-- Slide 4: Liyas Gold and Diamonds -->
                            <div class="carousel-item">
                                <div class="row align-items-center">
                                    <div class="col-lg-6 order-lg-1 order-2">
                                        <div class="hero-content">
                                            <h1 class="hero-title">Liyas Gold and Diamonds</h1>
                                            <p class="hero-subtitle">
                                                Trusted, high-quality gold and diamond jewelry with ethical sourcing and customization. Helping secure your financial future with confidence and award-winning service.
                                            </p>

                                            <div class="hero-cta mt-4">
                                                <a href="https://liyasgoldanddiamonds.com/" target="_blank" class="btn btn-primary btn-lg me-3">
                                                    <i class="fas fa-external-link-alt me-2"></i>
                                                    Visit Website
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 order-lg-2 order-1 text-center mb-4 mb-lg-0">
                                        <img src="./assets/view-brilliant-cartoon-diamond.jpg" alt="Liyas Gold and Diamonds" class="img-fluid rounded-4 shadow-lg hero-img">
                                    </div>
                                </div>
                            </div>
                            <!-- Slide 5: Liyas Construction -->
                            <div class="carousel-item">
                                <div class="row align-items-center">
                                    <div class="col-lg-6 order-lg-1 order-2">
                                        <div class="hero-content">
                                            <h1 class="hero-title">Liyas Construction</h1>
                                            <p class="hero-subtitle">
                                                Comprehensive construction services including residential, commercial, and industrial projects. Specializing in modern architecture, sustainable building practices, and turnkey solutions with quality craftsmanship.
                                            </p>

                                            <div class="hero-cta mt-4">
                                                <button class="btn btn-secondary btn-lg me-3" disabled>
                                                    <i class="fas fa-clock me-2"></i>
                                                    Coming Soon
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 order-lg-2 order-1 text-center mb-4 mb-lg-0">
                                        <img src="./assets/construction-silhouette.jpg" alt="Liyas Construction" class="img-fluid rounded-4 shadow-lg hero-img">
                                    </div>
                                </div>
                            </div>
                            <!-- Slide 6: Liyas Bakes and Cafe -->
                            <div class="carousel-item">
                                <div class="row align-items-center">
                                    <div class="col-lg-6 order-lg-1 order-2">
                                        <div class="hero-content">
                                            <h1 class="hero-title">Liyas Bakes and Cafe</h1>
                                            <p class="hero-subtitle">
                                                Premium bakery and cafe serving fresh pastries, cakes, breads, and specialty coffee. Offering custom cakes for celebrations, corporate events, and daily fresh baked goods with exceptional taste and presentation.
                                            </p>

                                            <div class="hero-cta mt-4">
                                                <button class="btn btn-secondary btn-lg me-3" disabled>
                                                    <i class="fas fa-clock me-2"></i>
                                                    Coming Soon
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 order-lg-2 order-1 text-center mb-4 mb-lg-0">
                                        <img src="./assets/delicious-products-arrangement-bakery.jpg" alt="Liyas Bakes and Cafe" class="img-fluid rounded-4 shadow-lg hero-img">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Carousel controls - Only Next button visible -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" style="display: none;">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <main>
        <section id="ventures" class="section bg-light">
            <div class="container">
                <h2 style="text-align: center;">Our Ventures</h2>
                <div class="text-center">
                    <img src="./assets/BROCHUR_page-00012.jpg" alt="Investment Portfolio Brochure" class="img-fluid" style="max-width: 100%; height: auto;">
                </div>
            </div>
        </section>

        <!-- Gallery Section -->
<section id="gallery" class="section bg-white">
    <div class="container">
        <h2 class="section-title text-center mb-4">Our Gallery</h2>
        <div id="galleryCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000" data-bs-wrap="true" data-bs-pause="false" data-bs-touch="true" data-bs-keyboard="false">
            <div class="carousel-inner">
                <?php
                $galleryDir = __DIR__ . '/assets/gallery/';
                $galleryUrl = './assets/gallery/';
                $images = array_values(array_filter(scandir($galleryDir), function($file) use ($galleryDir) {
                    return !is_dir($galleryDir . $file) && preg_match('/\\.(jpg|jpeg|png|gif)$/i', $file);
                }));
                $chunked = array_chunk($images, 3);
                foreach ($chunked as $i => $row) {
                ?>
                <div class="carousel-item<?php if($i==0) echo ' active'; ?>">
                    <div class="row g-4 justify-content-center">
                        <?php foreach ($row as $img): ?>
                        <div class="col-md-4">
                            <div class="card h-100 shadow-sm">
                                <img src="<?php echo $galleryUrl . $img; ?>" class="card-img-top img-fluid" alt="Gallery Image" style="object-fit:cover; width:100%; height:320px;" loading="lazy">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
</section>

        <!-- YouTube Video Section -->
        <section id="youtube-video" class="section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10 text-center fade-in-up">
                        <h2 class="section-title mb-4">Watch Our Latest Videos</h2>
                        <p class="section-subtitle mb-5">Discover our vision and mission through our latest presentations</p>
                        
                        <div class="row g-4">
                            <!-- Video 1 -->
                            <div class="col-lg-4 col-md-6">
                                <div class="video-card h-100">
                                    <div class="video-thumbnail position-relative" style="cursor: pointer; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); aspect-ratio: 16/9;" onclick="openVideoModal('NYHDu0_5afQ', 'Gee Dee Ventures Presentation')">
                                        <img src="https://img.youtube.com/vi/NYHDu0_5afQ/maxresdefault.jpg" 
                                             alt="Gee Dee Ventures Video" 
                                             class="img-fluid w-100 h-100" 
                                             style="transition: transform 0.3s ease; object-fit: cover;">
                                        <div class="play-button-overlay position-absolute top-50 start-50 translate-middle" 
                                             style="background: rgba(255,0,0,0.9); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                                            <i class="fas fa-play text-white" style="font-size: 18px; margin-left: 3px;"></i>
                                        </div>
                                    </div>
                                    <div class="video-info mt-3">
                                        <h6 class="fw-bold mb-2">Gee Dee Ventures Presentation</h6>
                                        <p class="text-muted small mb-0">Our latest company presentation</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Video 2 -->
                            <div class="col-lg-4 col-md-6">
                                <div class="video-card h-100">
                                    <div class="video-thumbnail position-relative" style="cursor: pointer; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); aspect-ratio: 16/9;" onclick="openVideoModal('LfKhSRvn-Es', 'Gee Dee Ventures Video 2')">
                                        <img src="https://img.youtube.com/vi/LfKhSRvn-Es/hqdefault.jpg" 
                                             alt="Gee Dee Ventures Video 2" 
                                             class="img-fluid w-100 h-100" 
                                             style="transition: transform 0.3s ease; object-fit: cover;"
                                             onerror="this.src='https://img.youtube.com/vi/LfKhSRvn-Es/mqdefault.jpg'">
                                        <div class="play-button-overlay position-absolute top-50 start-50 translate-middle" 
                                             style="background: rgba(255,0,0,0.9); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                                            <i class="fas fa-play text-white" style="font-size: 18px; margin-left: 3px;"></i>
                                        </div>
                                    </div>
                                    <div class="video-info mt-3">
                                        <h6 class="fw-bold mb-2">Gee Dee Ventures Video 2</h6>
                                        <p class="text-muted small mb-0">Additional insights and updates</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Video 3 -->
                            <div class="col-lg-4 col-md-6">
                                <div class="video-card h-100">
                                    <div class="video-thumbnail position-relative" style="cursor: pointer; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); aspect-ratio: 16/9;" onclick="openVideoModal('gnPM77ECZOM', 'Gee Dee Ventures Video 3')">
                                        <img src="https://img.youtube.com/vi/gnPM77ECZOM/maxresdefault.jpg" 
                                             alt="Gee Dee Ventures Video 3" 
                                             class="img-fluid w-100 h-100" 
                                             style="transition: transform 0.3s ease; object-fit: cover;">
                                        <div class="play-button-overlay position-absolute top-50 start-50 translate-middle" 
                                             style="background: rgba(255,0,0,0.9); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                                            <i class="fas fa-play text-white" style="font-size: 18px; margin-left: 3px;"></i>
                                        </div>
                                    </div>
                                    <div class="video-info mt-3">
                                        <h6 class="fw-bold mb-2">Gee Dee Ventures Video 3</h6>
                                        <p class="text-muted small mb-0">More about our ventures</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Video 4 -->
                            <div class="col-lg-4 col-md-6">
                                <div class="video-card h-100">
                                    <div class="video-thumbnail position-relative" style="cursor: pointer; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); aspect-ratio: 16/9;" onclick="openVideoModal('IJBZaAmkShM', 'Gee Dee Ventures Special Event')">
                                        <img src="https://img.youtube.com/vi/IJBZaAmkShM/maxresdefault.jpg" 
                                             alt="Gee Dee Ventures Special Event" 
                                             class="img-fluid w-100 h-100" 
                                             style="transition: transform 0.3s ease; object-fit: cover;">
                                        <div class="play-button-overlay position-absolute top-50 start-50 translate-middle" 
                                             style="background: rgba(255,0,0,0.9); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                                            <i class="fas fa-play text-white" style="font-size: 18px; margin-left: 3px;"></i>
                                        </div>
                                    </div>
                                    <div class="video-info mt-3">
                                        <h6 class="fw-bold mb-2">Gee Dee Ventures Special Event</h6>
                                        <p class="text-muted small mb-0">Highlights from our recent event</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Video 5 -->
                            <div class="col-lg-4 col-md-6">
                                <div class="video-card h-100">
                                    <div class="video-thumbnail position-relative" style="cursor: pointer; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); aspect-ratio: 16/9;" onclick="openVideoModal('_U5HWs1slyo', 'Gee Dee Ventures Community')">
                                        <img src="https://img.youtube.com/vi/_U5HWs1slyo/maxresdefault.jpg" 
                                             alt="Gee Dee Ventures Community" 
                                             class="img-fluid w-100 h-100" 
                                             style="transition: transform 0.3s ease; object-fit: cover;">
                                        <div class="play-button-overlay position-absolute top-50 start-50 translate-middle" 
                                             style="background: rgba(255,0,0,0.9); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                                            <i class="fas fa-play text-white" style="font-size: 18px; margin-left: 3px;"></i>
                                        </div>
                                    </div>
                                    <div class="video-info mt-3">
                                        <h6 class="fw-bold mb-2">Gee Dee Ventures Community</h6>
                                        <p class="text-muted small mb-0">Our community initiatives</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Video 6 -->
                            <div class="col-lg-4 col-md-6">
                                <div class="video-card h-100">
                                    <div class="video-thumbnail position-relative" style="cursor: pointer; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); aspect-ratio: 16/9;" onclick="openVideoModal('qW1mQ6h0bTk', 'Gee Dee Ventures Success Stories')">
                                        <img src="https://img.youtube.com/vi/qW1mQ6h0bTk/maxresdefault.jpg" 
                                             alt="Gee Dee Ventures Success Stories" 
                                             class="img-fluid w-100 h-100" 
                                             style="transition: transform 0.3s ease; object-fit: cover;">
                                        <div class="play-button-overlay position-absolute top-50 start-50 translate-middle" 
                                             style="background: rgba(255,0,0,0.9); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                                            <i class="fas fa-play text-white" style="font-size: 18px; margin-left: 3px;"></i>
                                        </div>
                                    </div>
                                    <div class="video-info mt-3">
                                        <h6 class="fw-bold mb-2">Gee Dee Ventures Success Stories</h6>
                                        <p class="text-muted small mb-0">Stories of our impact</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Video 7 -->
                            <div class="col-lg-4 col-md-6">
                                <div class="video-card h-100">
                                    <div class="video-thumbnail position-relative" style="cursor: pointer; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); aspect-ratio: 16/9;" onclick="openVideoModal('t-bOshnRc6s', 'Gee Dee Ventures Insights')">
                                        <img src="https://img.youtube.com/vi/t-bOshnRc6s/maxresdefault.jpg" 
                                             alt="Gee Dee Ventures Insights" 
                                             class="img-fluid w-100 h-100" 
                                             style="transition: transform 0.3s ease; object-fit: cover;">
                                        <div class="play-button-overlay position-absolute top-50 start-50 translate-middle" 
                                             style="background: rgba(255,0,0,0.9); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                                            <i class="fas fa-play text-white" style="font-size: 18px; margin-left: 3px;"></i>
                                        </div>
                                    </div>
                                    <div class="video-info mt-3">
                                        <h6 class="fw-bold mb-2">Gee Dee Ventures Insights</h6>
                                        <p class="text-muted small mb-0">Expert talks and analysis</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Video 8 -->
                            <div class="col-lg-4 col-md-6">
                                <div class="video-card h-100">
                                    <div class="video-thumbnail position-relative" style="cursor: pointer; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); aspect-ratio: 16/9;" onclick="openVideoModal('37_U6dup0ik', 'Gee Dee Ventures Future Plans')">
                                        <img src="https://img.youtube.com/vi/37_U6dup0ik/maxresdefault.jpg" 
                                             alt="Gee Dee Ventures Future Plans" 
                                             class="img-fluid w-100 h-100" 
                                             style="transition: transform 0.3s ease; object-fit: cover;">
                                        <div class="play-button-overlay position-absolute top-50 start-50 translate-middle" 
                                             style="background: rgba(255,0,0,0.9); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
                                            <i class="fas fa-play text-white" style="font-size: 18px; margin-left: 3px;"></i>
                                        </div>
                                    </div>
                                    <div class="video-info mt-3">
                                        <h6 class="fw-bold mb-2">Gee Dee Ventures Future Plans</h6>
                                        <p class="text-muted small mb-0">A look at what's next</p>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Video Modal -->
        <div class="modal fade" id="videoModal" tabindex="-1" aria-labelledby="videoModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content bg-dark">
                    <div class="modal-header border-0">
                        <h5 class="modal-title text-white" id="videoModalLabel">Gee Dee Ventures Presentation</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="ratio ratio-16x9">
                            <iframe id="youtubeIframe" 
                                    src="" 
                                    title="Gee Dee Ventures Video" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to Action Section - The Brand Weave -->
        <section class="section" style="background: linear-gradient(135deg, var(--c-primary) 0%, #1e293b 100%);">
            <div class="container text-center">
                <div class="row justify-content-center">
                    <div class="col-lg-8 fade-in-up">
                        <h2 class="section-title text-white mb-4">Ready to Partner With The Brand Weave?</h2>
                        <p class="section-subtitle text-white-50 mb-5">
                            The Brand Weave is a premier digital agency specializing in creative solutions, digital marketing, branding, and design. 
                            We empower businesses to grow online with data-driven strategies and innovative design solutions. 
                            From brand identity to comprehensive digital marketing campaigns, we deliver measurable results that drive your business forward.
                        </p>
                        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                            <a href="https://thebrandweave.com/" target="_blank" class="btn btn-danger btn-lg px-5" style="background: linear-gradient(135deg, #dc3545 0%, #8b0000 100%); border: none; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);">
                                <i class="fas fa-external-link-alt me-2"></i>
                                Partner With Us
                            </a>
                            <a href="https://thebrandweave.com/" target="_blank" class="btn btn-outline-light btn-lg px-5">
                                <i class="fas fa-globe me-2"></i>
                                Visit Website
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Back to Top Button -->
    <!-- <a href="#hero" class="btn btn-primary position-fixed bottom-0 end-0 m-4 rounded-circle back-to-top" 
       style="width: 50px; height: 50px; display: none; z-index: 1000; text-decoration: none;">
        <i class="fas fa-arrow-up"></i>
    </a> -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offsetTop = target.offsetTop - 80;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Show/hide back to top button
        window.addEventListener('scroll', function() {
            const backToTopBtn = document.querySelector('.back-to-top');
            if (backToTopBtn) {
                if (window.scrollY > 300) {
                    backToTopBtn.style.display = 'flex';
                    backToTopBtn.style.alignItems = 'center';
                    backToTopBtn.style.justifyContent = 'center';
                } else {
                    backToTopBtn.style.display = 'none';
                }
            }
        });

        // Newsletter form handling
        function handleNewsletter(event) {
            event.preventDefault();
            const form = event.target;
            const button = form.querySelector('button');
            const input = form.querySelector('input[type="email"]');
            
            // Simple email validation
            const email = input.value.trim();
            if (!email || !email.includes('@') || !email.includes('.')) {
                input.style.borderColor = '#ff6b6b';
                input.focus();
                setTimeout(() => {
                    input.style.borderColor = '';
                }, 2000);
                return false;
            }
            
            // Show success state
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i>';
            button.disabled = true;
            button.classList.add('btn-success');
            button.classList.remove('btn-outline-light');
            
            // Reset form after 3 seconds
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-light');
                form.reset();
            }, 3000);
            
            return false;
        }

        // Add animation classes when elements come into view
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0) translateX(0)';
                }
            });
        }, observerOptions);

        // Observe all animated elements
        document.querySelectorAll('.fade-in, .fade-in-up, .fade-in-left, .fade-in-right').forEach(el => {
            observer.observe(el);
        });

        // Enhanced hover effects for cards
        document.querySelectorAll('.venture-card, .blog-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = this.classList.contains('venture-card') ? 
                    'translateY(-12px) scale(1.02)' : 'translateY(-8px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Add loading state to buttons
        document.querySelectorAll('a[href="#"]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 2000);
            });
        });

        // Performance optimization for scroll events
        let ticking = false;
        function handleScroll() {
            if (!ticking) {
                requestAnimationFrame(() => {
                    ticking = false;
                });
                ticking = true;
            }
        }

        window.addEventListener('scroll', handleScroll);

        // Add ripple effect to buttons
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.6);
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    pointer-events: none;
                `;
                
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Add ripple animation
        const rippleStyle = document.createElement('style');
        rippleStyle.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(rippleStyle);

        console.log('Gee Dee Ventures website loaded successfully - PHP Version');
        
        // Performance monitoring
        window.addEventListener('load', function() {
            const loadTime = performance.now();
            console.log(`Page loaded in ${loadTime.toFixed(2)}ms`);
        });
        
        // Enhanced error handling
        window.addEventListener('error', function(e) {
            console.error('JavaScript error:', e.error);
        });
        
        // Add loading states for better UX
        document.addEventListener('DOMContentLoaded', function() {
            document.body.classList.add('loaded');
        });

        // Initialize and sync hero carousels with smooth slide transitions
        document.addEventListener('DOMContentLoaded', function() {
            const heroCarousel = document.getElementById('heroCarousel');
            const heroBgCarousel = document.getElementById('heroBgCarousel');
            
            if (heroCarousel && heroBgCarousel) {
                // Initialize carousels with smooth slide transitions
                const mainCarousel = new bootstrap.Carousel(heroCarousel, {
                    interval: 5000,
                    wrap: true,
                    ride: 'carousel',
                    touch: true
                });
                
                const bgCarousel = new bootstrap.Carousel(heroBgCarousel, {
                    interval: 5000,
                    wrap: true,
                    ride: 'carousel',
                    touch: true
                });
                
                // Sync carousels with smooth slide transitions
                heroCarousel.addEventListener('slide.bs.carousel', function (event) {
                    // Smooth transition for background carousel
                    bgCarousel.to(event.to);
                });
                
                // Add manual navigation sync with smooth transitions
                document.querySelectorAll('[data-bs-target="#heroCarousel"]').forEach(button => {
                    button.addEventListener('click', function() {
                        const direction = this.getAttribute('data-bs-slide');
                        if (direction === 'prev') {
                            bgCarousel.prev();
                        } else if (direction === 'next') {
                            bgCarousel.next();
                        }
                    });
                });
                
                // Pause carousels on hover for better UX
                heroCarousel.addEventListener('mouseenter', function() {
                    mainCarousel.pause();
                    bgCarousel.pause();
                });
                
                heroCarousel.addEventListener('mouseleave', function() {
                    mainCarousel.cycle();
                    bgCarousel.cycle();
                });
                
                // Handle carousel errors gracefully
                heroCarousel.addEventListener('error', function(e) {
                    console.warn('Carousel error:', e);
                });
                
                // Ensure carousels are properly initialized with smooth slide transitions
                setTimeout(() => {
                    if (mainCarousel && bgCarousel) {
                        mainCarousel.cycle();
                        bgCarousel.cycle();
                        
                        // Verify carousel synchronization
                        console.log('Carousels initialized with wrap enabled and smooth slide transitions');
                        console.log('Main carousel wrap:', mainCarousel._config.wrap);
                        console.log('Background carousel wrap:', bgCarousel._config.wrap);
                    }
                }, 100);
                
                // Additional sync check for perfect synchronization
                heroCarousel.addEventListener('slid.bs.carousel', function (event) {
                    // Ensure background carousel is on the same slide
                    const activeBgSlide = bgCarousel._element.querySelector('.carousel-item.active');
                    const activeMainSlide = event.relatedTarget;
                    
                    if (activeBgSlide && activeMainSlide) {
                        const bgIndex = Array.from(bgCarousel._element.querySelectorAll('.carousel-item')).indexOf(activeBgSlide);
                        const mainIndex = event.to;
                        
                        if (bgIndex !== mainIndex) {
                            bgCarousel.to(mainIndex);
                        }
                    }
                    
                    // Ensure content is fully visible after transition
                    const currentSlide = event.relatedTarget;
                    if (currentSlide) {
                        currentSlide.querySelector('.hero-content').style.opacity = '1';
                        currentSlide.querySelector('.hero-img').style.transform = 'scale(1)';
                    }
                });
            }
        });

        // YouTube Video Modal Function
        function openVideoModal(videoId, videoTitle) {
            const modal = new bootstrap.Modal(document.getElementById('videoModal'));
            const iframe = document.getElementById('youtubeIframe');
            const modalTitle = document.getElementById('videoModalLabel');
            
            // Set the modal title
            modalTitle.textContent = videoTitle;
            
            // Set the YouTube embed URL with autoplay
            iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0&modestbranding=1`;
            
            // Show the modal
            modal.show();
            
            // Clear iframe src when modal is hidden to stop video
            document.getElementById('videoModal').addEventListener('hidden.bs.modal', function () {
                iframe.src = '';
            });
        }

        // Add hover effects for video thumbnails
        document.addEventListener('DOMContentLoaded', function() {
            const videoThumbnails = document.querySelectorAll('.video-thumbnail');
            
            videoThumbnails.forEach(function(thumbnail) {
                const playButton = thumbnail.querySelector('.play-button-overlay');
                
                if (playButton) {
                    thumbnail.addEventListener('mouseenter', function() {
                        this.querySelector('img').style.transform = 'scale(1.05)';
                        playButton.style.transform = 'translate(-50%, -50%) scale(1.1)';
                        playButton.style.background = 'rgba(255,0,0,1)';
                    });
                    
                    thumbnail.addEventListener('mouseleave', function() {
                        this.querySelector('img').style.transform = 'scale(1)';
                        playButton.style.transform = 'translate(-50%, -50%) scale(1)';
                        playButton.style.background = 'rgba(255,0,0,0.9)';
                    });
                }
            });
        });
    </script>

    <script>
        // Navbar Functions
        function openLoginModal() {
            const modal = document.getElementById('loginModal');
            modal.style.display = 'block';
            setTimeout(() => {
                modal.classList.add('active');
                document.querySelector('.login-modal').classList.add('active');
            }, 10);
        }

        function closeLoginModal() {
            const modal = document.getElementById('loginModal');
            modal.classList.remove('active');
            document.querySelector('.login-modal').classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        // Close modal when clicking outside
        document.getElementById('loginModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLoginModal();
            }
        });

        // Mobile Menu Toggle
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('active');
            document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
        }

        // Close mobile menu when clicking outside
        document.getElementById('mobileMenu').addEventListener('click', function(e) {
            if (e.target === this) {
                toggleMobileMenu();
            }
        });

        // Sticky Navbar Scroll Effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Initialize scroll state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            }
        });
    </script>

    <!-- Footer -->
    <footer id="contact">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Golden Dream</h3>
                <p>Empowering your financial future with secure investment opportunities and expert guidance. Join us in building a prosperous tomorrow.</p>
                <div class="social-links">
                    <a href="https://www.instagram.com/pro_gd_ventures_pvt/"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.youtube.com/@goldendream23"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#features"><i class="fas fa-chevron-right"></i> Features</a></li>
                    <li><a href="#how-it-works"><i class="fas fa-chevron-right"></i> How It Works</a></li>
                    <li><a onclick="openLoginModal()"><i class="fas fa-chevron-right"></i> Login</a></li>
                    <!-- <li><a href="https://goldendream.in//refer?id=GDP0001&ref=NTAw"><i class="fas fa-chevron-right"></i> Register</a></li> -->
                    <li><a href="#contact"><i class="fas fa-chevron-right"></i> Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Investment Plans</h3>
                <ul>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Short Term Plans</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Long Term Plans</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> High Return Plans</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Fixed Deposit Plans</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Custom Plans</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Info</h3>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>2-108/C-7, Ground Floor, Sri Mantame Complex, Near Soorya Infotech Park, Kurnadu Post, Mudipu Road, Bantwal- 574153</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+91 99951 94472</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>goldendream175@gmail.com</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <span>Mon - Sun: 9:30 AM - 6:00 PM</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyright">
            <!-- <div class="developer-credit">
                <span>Developed by <a style="text-decoration: none;color:teal;" href="https://intelexsolutions.in/"> <img src="../landing_assets/images/intelex.png" style="height: 20px;margin-bottom:-5px;" alt=""> Intelex Solutions</a></span>
            </div> -->
            <p>&copy; 2025 Golden Dream. All rights reserved.</p>
            <!-- <div class="last-updated">
                <i class="fas fa-clock"></i>
                <span>Last updated: <?php echo date('F d, Y'); ?></span>
            </div> -->
        </div>
    </footer>
</body>
</html>
