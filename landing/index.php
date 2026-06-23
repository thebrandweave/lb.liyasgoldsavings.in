<?php
// Simple PHP for dynamic date
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoldenDream - Your Financial Future</title>
    <link rel="icon" type="image/png" href="./landing_assets/images/1gdlogo.png">
    <link rel="shortcut icon" type="image/png" href="./landing_assets/images/1gdlogo.png">
    <link rel="apple-touch-icon" href="./landing_assets/images/1gdlogo.png">
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
    <meta property="og:url" content="https://lb.liyasgoldsavings.in">
    <meta property="og:image" content="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=1200&auto=format&fit=crop">
    <meta property="og:site_name" content="GoldenDream">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="GoldenDream - Building Tomorrow's Market Leaders">
    <meta name="twitter:description" content="Strategic investments across high-growth sectors that are shaping tomorrow's economy.">
    <meta name="twitter:image" content="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=1200&auto=format&fit=crop">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <!-- Structured Data -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "GoldenDream",
            "description": "Strategic investments across high-growth sectors that are shaping tomorrow's economy.",
            "url": "https://lb.liyasgoldsavings.in",
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

        * {
            box-sizing: border-box;
        }

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
            background: #fffff;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1000;
            /* backdrop-filter: blur(10px); */
            max-width: 100vw !important;
            /* overflow-x: hidden; */
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

        /* .cta-card::before{
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(135deg, #e6ca9f 0%, #a16b14 60%, #a16b14 100%);
        } */

        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(135deg, #e6ca9f 0%, #fff5e5 60%, #ffeed2 100%);
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
            background:linear-gradient(180deg, #e6ca9f 0%, #a16b14 60%, #a16b14 100%);
            font-size: 1.2rem;
            margin-bottom: 24px;
            position: relative;
            font-weight:600;
            padding-bottom: 12px;
              -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
        }

        .footer-section h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background:linear-gradient(135deg, #e6ca9f 0%, #a16b14 60%, #a16b14 100%);
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
            background: linear-gradient(180deg, #e6ca9f 0%, #a16b14 60%, #a16b14 100%);
              -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
            transform: translateX(5px);
        }

        .footer-section ul li a i {
            font-size: 0.9rem;
            background:linear-gradient(135deg, #e6ca9f 0%, #a16b14 60%, #a16b14 100%);
              -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
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
            background:linear-gradient(180deg, #e6ca9f 0%, #a16b14 60%, #a16b14 100%);
              -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
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
            text-decoration:none;
        }

        .social-links a:hover {
            background: linear-gradient(0deg, #dfb270 0%, #f9bf68 10%, #000000 100%);
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
            box-shadow: 0 8px 32px rgba(99, 91, 255, 0.10);
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

        .carousel-control-prev,
        .carousel-control-next {
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

        .carousel-control-prev:hover,
        .carousel-control-next:hover {
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

        .section-1 {
            padding: 3rem 0 6rem;
            z-index:-10;
            background:linear-gradient(340deg, #000000 30%, #a16a13 100%, #a16a13 100%);
        }
          .section-2 {
            padding: 3rem 0 5rem;
            /* background-color: black; */
        }

        .section-title {
            font-weight: 800;
            color: white;
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
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            aspect-ratio: 16/9;
        }

        .play-button-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #a16b14;
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
        /* ==========================
   HERO SECTION
========================== */

.hero {
    position: relative;
    width: 100%;
    height: 100vh;
    overflow: hidden;
}

.hero .carousel,
.hero .carousel-inner,
.hero .carousel-item,
.hero-slide {
    width: 100%;
    height: 100vh;
}

.hero-slide {
    position: relative;
}

.hero-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Dark Overlay */
.hero-slide::before {
    content: "";
    position: absolute;
    inset: 0;
   /* background: rgba(0, 0, 0, 0.55);*/
    z-index: 1;
}

/* Content */
.hero-content {
    position: absolute;
    bottom: 100px;
    left: 70px;
    z-index: 2;
    color: #fff;
    max-width: 700px;
}

.hero-line {
     width: 4px;
    height: 75px;
    background: #d4af37;
    margin-bottom: -88px;
}

.hero-content h1 {
    font-size: 4rem;
    font-weight: 700;
    line-height: 1.15;
    margin-bottom: 25px;
    color: #fff;
    /* margin-left: 16px; */
}

.hero-btn {
    display: inline-block;
    padding: 14px 32px;
    color: #fff;
    border: 2px solid #fff;
    text-decoration: none;
    font-weight: 600;
    transition: 0.3s ease;
    margin-left: 16px;
}

.hero-content {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;

    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    align-items: flex-start;

    padding: 0 60px 80px;
    z-index: 2;
/* 
    background: linear-gradient(
        to top,
        rgba(0, 0, 0, 0.95) 0%,
        rgba(0, 0, 0, 0.75) 25%,
        rgba(0, 0, 0, 0.35) 50%,
        rgba(0, 0, 0, 0.05) 75%,
        transparent 100%
    ); */
}

.hero,
.hero-slide,
.carousel-item {
    width: 100%;
    height: 100vh;
    position: relative;
}
.hero-btn:hover {
    background: #fff;
    color: #000;
}

/* Arrows */
.carousel-control-prev,
.carousel-control-next {
    width: 55px;
    height: 55px;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255,255,255,0.15);
    border-radius: 50%;
    opacity: 1;
}

@media (max-width: 768px) {
    .hero-content {
        padding: 0 30px 70px;
    }
}

.carousel-control-prev {
    left: 30px;
}

.carousel-control-next {
    right: 30px;
}

.carousel-control-prev:hover,
.carousel-control-next:hover {
    background: rgba(255,255,255,0.3);
}

/* Navbar on top */
header {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 999;
    background: transparent !important;
    border: none !important;
}

.nav-links a,
.logo {
    color: #fff !important;
}

/* Responsive */
@media(max-width:768px){

    .hero-content{
        left:30px;
        right:30px;
        bottom:70px;
    }

    .hero-content h1{
        font-size:2.3rem;
    }

    .hero-line{
        height:40px;
    }

    .carousel-control-prev{
        left:15px;
    }

    .carousel-control-next{
        right:15px;
    }
}

/* Hero Section Layout */
.hero-section {
    background-color: #0b0b0b;
    position: relative;
    overflow: hidden;
}

.hero-slide {
    height: 100vh;
    min-height: 700px;
    background-size: cover;
    background-position: center right;
    display: flex;
    align-items: center;
    position: relative;
}

.hero-container {
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    padding-top: 80px; /* Accounts for fixed navbar space */
}

/* Typography & Content styling */
.hero-content {
    max-width: 580px;
    z-index: 2;
    margin-bottom: auto;
    margin-top: auto;
}

.hero-subtitle {
    font-size: 0.75rem;
    letter-spacing: 3px;
    color: #c5a880;
    text-transform: uppercase;
    font-weight: 600;
    display: block;
    margin-bottom: 1.5rem;
    position: relative;
}

.hero-subtitle::before {
    content: '';
    display: inline-block;
    width: 25px;
    height: 1px;
    background-color: #c5a880;
    vertical-align: middle;
    margin-right: 10px;
}

.hero-subtitle-1::before {
    content: '';
    display: inline-block;
    width: 23px;
    height: 1px;
    background-color: #c5a880;
    vertical-align: middle;
    margin-right: 10px;
}
.hero-subtitle-1::after {
    content: '';
    display: inline-block;
    width: 23px;
    height: 1px;
    background-color: #c5a880;
    vertical-align: middle;
    margin-left: 10px;
}

.hero-subtitle-1 {
    font-size: 0.75rem;
    letter-spacing: 3px;
    color: #c5a880;
    text-transform: uppercase;
    font-weight: 600;
    display: block;
    margin-bottom: 1.5rem;
    position: relative;
}

.hero-title {
    font-size: 4rem;
    font-weight: 700;
    color: #ffffff;
    line-height: 1.1;
    margin-bottom: 1.5rem;
}

.text-gold {
    color: #a16b14;
    background: linear-gradient(135deg, #e6ca9f 0%, #a16b14 60%, #a16b14 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.hero-description {
    color: #b0b0b0;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 2.5rem;
}

/* Action Buttons */
.hero-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.btn-gold {
    background: linear-gradient(135deg, #a16b14 30%,#e6ca9f  100%);
    color: #000000 !important;
    font-weight: 600;
    padding: 0.75rem 2rem;
    border-radius: 6px;
    border: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 0.9rem;
}

.btn-gold:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
}

.video-btn {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.22);
    color: #ffffff !important;
    padding: 0.3rem 1rem;
    border-radius: 6px;
    font-size: 0.9rem;
    backdrop-filter: blur(5px);
    display: inline-flex;
    align-items: center;
    gap: 10px;
 transition: transform 0.2s ease, box-shadow 0.2s ease;
    duration:300;
}

.video-btn:hover {
 background: linear-gradient(135deg, #a16b14 30%,#e6ca9f  100%);
 color:black;
   transform: translateY(-2px);
   border:none;
}

/* Metrics Cards at Bottom */
.hero-metrics {
    display: flex;
    gap: 2rem;
    z-index: 2;
    margin-bottom: 2rem;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.05);
    padding: 1.5rem 2rem;
    border-radius: 12px;
    backdrop-filter: blur(10px);
    max-width: max-content;
}

.metric-item {
    display: flex;
    align-items: center;
    gap: 15px;
}

.metric-icon {
    font-size: 1.5rem;
    color: #d4af37;
    background: rgba(212, 175, 55, 0.1);
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    border: 1px solid rgba(212, 175, 55, 0.2);
}

.metric-text h3 {
    color: #ffffff;
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0;
}

.metric-text p {
    color: #8c8c8c;
    font-size: 0.75rem;
    margin: 0;
    line-height: 1.3;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Scroll Down Component */
.scroll-down {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: #8c8c8c;
    font-size: 0.7rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    z-index: 2;
}

.mouse-icon {
    width: 20px;
    height: 32px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 10px;
    position: relative;
}

.mouse-icon::before {
    content: '';
    width: 4px;
    height: 8px;
    background-color: #d4af37;
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    top: 6px;
    border-radius: 2px;
    animation: scrollMouse 1.5s infinite;
}

@keyframes scrollMouse {
    0% { opacity: 1; top: 6px; }
    100% { opacity: 0; top: 16px; }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hero-title { font-size: 2.5rem; }
    .hero-metrics { 
        flex-direction: column; 
        gap: 1.5rem; 
        width: 100%;
        max-width: 100%;
    }
    .hero-actions { flex-direction: column; align-items: stretch; }
    .scroll-down { display: none; }
}

/* Container to keep static elements like 'Scroll Down' from sliding away */
.global-hero-overlays {
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    height: 100%;
    width: 100%;
    pointer-events: none; /* Allows interacting with buttons beneath it */
    z-index: 3;
}

.global-hero-overlays .scroll-down {
    pointer-events: auto;
}

/* Customized Carousel Arrow Buttons */
.hero-section .carousel-control-prev,
.hero-section .carousel-control-next {
    width: 4%;
    height:4%;
    opacity: 0.3;
    transition: opacity 0.2s;
    z-index: 4;
}

.hero-section .carousel-control-prev:hover,
.hero-section .carousel-control-next:hover {
    opacity: 0.9;
}

.hero-section .carousel-control-prev-icon,
.hero-section .carousel-control-next-icon {
    width: 2rem;
    height: 2rem;
    background-size: 50%;
    /* background-color: rgba(212, 175, 55, 0.1);
    border: 1px solid rgba(212, 175, 55, 0.3); */
    border-radius: 50%;
    padding: 1.5rem;
}

/* Custom Bottom Dash Indicators */
.hero-section .carousel-indicators {
    bottom: 3rem;
    justify-content: flex-start;
    left: calc((100vw - 1140px) / 2); /* Matches standard Bootstrap container alignment */
    margin-left: 15px;
    z-index: 4;
}

@media (max-width: 1200px) {
    .hero-section .carousel-indicators {
        left: 10%;
    }
}

.hero-section .carousel-indicators [data-bs-target] {
    width: 30px;
    height: 3px;
    background-color: #ffffff;
    opacity: 0.2;
    border: none;
    transition: all 0.3s ease;
}

.hero-section .carousel-indicators .active {
    opacity: 1;
    background: linear-gradient(135deg, #d4af37 0%, #aa7c11 100%);
    width: 50px;
}

/* Container & Section Styles */
.ventures-section {
    background: linear-gradient(300deg, #000000 40%, #a16a13 100%, #a16a13 100%);
    /* background-color:black; */
    padding: 50px 0 80px 0;
    font-family: 'Poppins', sans-serif;
    overflow: hidden;
      padding:130px 5%;
}


.ventures-header .subtitle {
    font-size: 0.75rem;
    letter-spacing: 3px;
    color: #ffffff;
    text-transform: uppercase;
    font-weight: 600;
    display: block;
    margin-bottom: 5px;
}

.ventures-header .title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #ffffff;
}

/* Full-Height Accordion Core Layout */
.funky-accordion-container {
    display: flex;
    width: 100%;
    height: 75vh;
    min-height: 550px;
    background-color: #000;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.accordion-panel {
    position: relative;
    flex: 1;
    height: 100%;
    background-image: var(--bg-image);
    background-size: cover;
    background-position: center;
    cursor: pointer;
    overflow: hidden;
    transition: flex 0.6s cubic-bezier(0.25, 1, 0.5, 1);
}

/* Linear dark mask on image background */
.panel-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.95) 10%, rgba(0, 0, 0, 0.2) 50%);
    transition: background 0.4s ease;
    z-index: 1;
}

.accordion-panel:hover .panel-overlay {
    background: linear-gradient(to top, rgba(0, 0, 0, 0.9) 10%, rgba(0, 0, 0, 0.2) 40%);
}

/* Expanded State Behavior */
.accordion-panel.active {
    flex: 4;
    cursor: default;
}

.panel-content {
    position: relative;
    width: 100%;
    height: 100%;
    z-index: 2;
}

/* Collapsed Title Rotated Vertical Text */
.panel-collapsed-title {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-90deg);
    white-space: nowrap;
    opacity: 1;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.panel-collapsed-title h3 {
    color: rgba(255, 255, 255, 0.5);
    font-size: 1.25rem;
    font-weight: 600;
    letter-spacing: 1px;
    margin: 0;
    text-transform: uppercase;
    transition: color 0.3s ease;
}

.accordion-panel:hover .panel-collapsed-title h3 {
    color: #ffffff;
}

.accordion-panel.active .panel-collapsed-title {
    opacity: 0;
}

/* Expanded Content Area Layout */
.panel-expanded-content {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    padding: 40px;
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.4s ease, transform 0.4s ease;
    max-width: 650px;
}

.accordion-panel.active .panel-expanded-content {
    opacity: 1;
    transform: translateY(0);
    transition-delay: 0.2s;
}

/* Inner Elements Text Styling */
.venture-tag {
    font-size: 0.7rem;
    letter-spacing: 2px;
    color: #d4af37;
    text-transform: uppercase;
    font-weight: 600;
    display: inline-block;
    margin-bottom: 10px;
}

.panel-expanded-content h2 {
    color: #ffffff;
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 15px;
}

.venture-desc {
    color: #b0b0b0;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 25px;
}

/* Glassmorphism Offer Badge Card */
.offer-badge {
    background: rgba(212, 175, 55, 0.06);
    border: 1px solid rgba(212, 175, 55, 0.2);
    border-radius: 8px;
    padding: 15px 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    backdrop-filter: blur(10px);
}

.offer-badge i {
    font-size: 1.5rem;
    color: #d4af37;
    background: rgba(212, 175, 55, 0.1);
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    flex-shrink: 0;
}

.offer-badge span {
    color: #e0e0e0;
    font-size: 0.9rem;
    line-height: 1.4;
}

.offer-badge strong {
    color: #ffffff;
    font-weight: 600;
    background: linear-gradient(135deg, #ffe0a3 0%, #d4af37 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Responsiveness: Switch to vertical stack accordion layout on smaller screens */
@media (max-width: 991px) {
    .funky-accordion-container {
        flex-direction: column;
        height: auto;
    }
    
    .accordion-panel {
        width: 100%;
        height: 120px;
        transition: height 0.5s cubic-bezier(0.25, 1, 0.5, 1);
    }
    
    .accordion-panel.active {
        height: 420px;
        flex: none;
    }
    
    .panel-collapsed-title {
        transform: translate(-50%, -50%) rotate(0deg);
        top: 50%;
        left: 50%;
    }
    
    .panel-expanded-content {
        padding: 20px;
    }
    
    .panel-expanded-content h2 {
        font-size: 1.6rem;
    }
}

/* Container hides the horizontal scrollbar */
.smooth-slider-container {
    overflow: hidden;
    width: 100%;
    position: relative;
    padding: 10px 0;
}

/* The track where images line up horizontally */
.smooth-slider-track {
    display: flex;
    width: max-content;
    gap:15px;
    /* Adjust '40s' to make it faster (lower number) or slower (higher number) */
    animation: scrollContinuous 110s linear infinite; 
}

/* Individual slide item spacing and sizing */
.smooth-slider-item {
    width: 350px; /* Forces a consistent width for gallery items */
    flex-shrink: 0;
}

/* Pause the slider animation on hover so users can look closer */
.smooth-slider-container:hover .smooth-slider-track {
    animation-play-state: paused;
}

/* Keyframe for seamless infinite scrolling */
@keyframes scrollContinuous {
    0% {
        transform: translateX(0);
    }
    100% {
        /* Translates exactly halfway because we duplicated the image array */
        transform: translateX(-50%); 
    }
}
.bento-grid{
    display:grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    grid-template-rows: 220px 220px;
    gap:15px;
}

.card-1{
    grid-column:1;
    grid-row:1 / span 2;
}

.card-2{
    grid-column:2 / span 3;
    grid-row:1;
}

.card-3{
    grid-column:2;
    grid-row:2;
}

.card-4{
    grid-column:3;
    grid-row:2;
}

.card-5{
    grid-column:4;
    grid-row:2;
}

.bento-card{
    position:relative;
    overflow:hidden;
    border-radius:20px;
    cursor:pointer;
}

.bento-card img{
    width:100%;
    height:100%;
    object-fit:cover;
    transition:.5s;
}

.bento-card:hover img{
    transform:scale(1.1);
}

.overlay{
    position:absolute;
    inset:0;
    display:flex;
    align-items:flex-end;
    padding:20px;
    background:linear-gradient(
        to top,
        rgba(0,0,0,.8),
        rgba(0,0,0,.2),
        transparent
    );
}

.overlay h3{
    color:#fff;
    margin:0;
    font-size:1.3rem;
}

@media(max-width:768px){

    .bento-grid{
        grid-template-columns:1fr;
        grid-template-rows:auto;
    }

    .card-1,
    .card-2,
    .card-3,
    .card-4,
    .card-5{
        grid-column:auto;
        grid-row:auto;
        min-height:250px;
    }

}



.ventures-layout{
    display:grid;
    grid-template-columns: 70% 30%;
    gap:60px;
    align-items:center;
}

/* Left Side */

.ventures-content{
    color:#fff;
}

.subtitle{
    color:#c8a24d;
    font-size:14px;
    letter-spacing:3px;
}

.title{
    font-size:4rem;
    margin:15px 0;
    line-height:1.1;
}

.text-gold{
    color:#c8a24d;
    font-weight:600;
}

.explore{
       font-size:4rem;
    font-weight:600;
}

.about-title{
    font-size:4rem;
    font-weight:500;
}

.venture-text{
    color:rgba(255,255,255,.75);
    line-height:1.8;
    margin:25px 0;
}

.venture-btn{
    display:inline-block;
    padding:14px 32px;
    border:1px solid #c8a24d;
    color:#c8a24d;
    text-decoration:none;
    border-radius:50px;
    transition:.3s;
}

.venture-btn:hover{
    background:#c8a24d;
    color:#000;
}

/* Right Side Bento Grid */

.bento-grid{
    display:grid;
    grid-template-columns:2fr 1fr 1fr 1fr;
    grid-template-rows:220px 220px;
    gap:15px;
}

.card-1{
    grid-column:1;
    grid-row:1 / span 2;
}

.card-2{
    grid-column:2 / span 3;
    grid-row:1;
}

.card-3{
    grid-column:2;
    grid-row:2;
}

.card-4{
    grid-column:3;
    grid-row:2;
}

.card-5{
    grid-column:4;
    grid-row:2;
}

.bento-card{
    position:relative;
    overflow:hidden;
    border-radius:24px;
}

.bento-card img{
    width:100%;
    height:100%;
    object-fit:cover;
    transition:.5s;
}

.bento-card:hover img{
    transform:scale(1.1);
}

.overlay{
    position:absolute;
    inset:0;
    display:flex;
    align-items:flex-end;
    padding:20px;
    background:linear-gradient(
        to top,
        rgba(0,0,0,.85),
        rgba(0,0,0,.2),
        transparent
    );
}

.overlay h3{
    color:#fff;
    margin:0;
    font-size:1.2rem;
}

/* Mobile */

@media(max-width:992px){

    .ventures-layout{
        grid-template-columns:1fr;
    }

    .title{
        font-size:3rem;
    }

    .bento-grid{
        grid-template-columns:1fr;
        grid-template-rows:auto;
    }

    .card-1,
    .card-2,
    .card-3,
    .card-4,
    .card-5{
        grid-column:auto;
        grid-row:auto;
        min-height:250px;
    }
}


.border{
    margin-top:-50px;
    /* margin-bottom:33px; */
    z-index:99999;
    border-top-right-radius:50px;
    border-top-left-radius:50px;
     /* border-bottom-right-radius:50px;
    border-bottom-left-radius:50px; */
}


.text-gold-1{
    color: #a16b14;
    /* font-weight:800; */
    background:linear-gradient(135deg, #e6ca9f 0%, #a16b14 60%, #a16b14 100%);
      -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight:800;
}
.gold-text {
  background: linear-gradient(135deg, #e6ca9f 0%, #a16b14 60%, #a16b14 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  display: inline-block; /* Ensures the gradient bounds apply correctly to the symbol */
}
/* 
.cta{
    background:linear-gradient(135deg, #f59c15 0%, #675333 50%, #ff9d00 100%);
}

  .cta-card{
            position:relative;
            overflow:hidden;
            background:linear-gradient(90deg, #5c3b0b 0%, #c98211 30%, #ffffff 100%);
     
            min-height:340px;
            padding:80px 70px;
 
            box-shadow:0 20px 50px rgba(0,0,0,.12);
        }

        .content{
            position:relative;
            z-index:2;
            max-width:550px;
        }

        .content h2{
            color:#000;
            font-size:56px;
            font-weight:700;
            margin-bottom:15px;
        }

        .content p{
            color:rgba(255,255,255,.9);
            line-height:1.7;
            max-width:400px;
            margin-bottom:35px;
        }

        .buttons{
            display:flex;
            gap:15px;
            flex-wrap:wrap;
        }

        .btn{
            display:flex;
            align-items:center;
            gap:20px;
            background:#000;
            color:#fff;
            text-decoration:none;
            padding:14px 25px;
            border-radius:50px;
            transition:.3s;
        }

        .btn:hover{
            transform:translateY(-3px);
        }

        .btn span{
            width:16px;
            height:16px;
            background:#fff;
            border-radius:50%;
        }


        @media(max-width:768px){

            .cta-card{
                padding:50px 30px;
            }

            .content h2{
                font-size:40px;
            }

            .circles{
                right:-300px;
            }
        } */

        .cta-card{
    position:relative;
    overflow:hidden;
    background:linear-gradient(0deg, #dfb270 0%, #f9bf68 10%, #000000 100%);
    /* border-radius:30px; */
    padding:80px;
}

.cta-wrapper{
    position:relative;
    z-index:2;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:80px;
}

.content{
    flex:1;
    max-width:550px;
}

.footer-logo {
    width: 104px;
    height: 100px;
    margin: 0 0 0 -20px;

    filter: brightness(0) invert(1);
}

.logo-grid{
    flex:1;
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:13px;
    max-width:500px;
}
.logo-grid img{
    width:76%;
    height:100%;
    object-fit:contain;
    background:#fff;
    padding:10px;
    border-radius:96px;
    border:1px solid rgba(161,106,19,.15);
    transition:.4s;
}

.logo-grid img:hover{
    transform:translateY(-5px);
    border-color:#a16a13;
    box-shadow:0 15px 35px rgba(161,106,19,.15);
}

@keyframes float{
    0%,100%{
        transform:translateY(0);
    }
    50%{
        transform:translateY(-10px);
    }
}
.content h2{
    font-size:50px;
    font-weight: 800;
    line-height: 1.1;
    color: #ffffff;
    margin-bottom: 25px;
    letter-spacing: -1px;
    position: relative;
}

.content h2::after{
    content: "";
    width: 90px;
    height: 4px;
    background: linear-gradient(
        90deg,
        #f59c15,
        #a16a13
    );
    display: block;
    margin-top: 18px;
    border-radius: 20px;
}

.content p{
    font-size: 1.1rem;
    line-height: 1.9;
    color: #ffffff;
    max-width: 540px;
    margin-bottom: 40px;
}

.buttons{
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.text-gold-2{
        background: linear-gradient(0deg, #e6ca9f 0%, #a16b14 60%, #a16b14 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;

}

.btn{
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 16px 34px;
    border-radius: 60px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    overflow: hidden;
    transition: all .4s ease;
}
.btn:first-child{
    background: linear-gradient(
        135deg,
        #f59c15 0%,
        #a16a13 100%
    );
    color: #fff;
    box-shadow: 0 15px 35px rgba(161,106,19,.25);
}

.btn:first-child:hover{
    transform: translateY(-4px);
    box-shadow: 0 20px 45px rgba(161,106,19,.35);
}

.btn:last-child{
    background: white;
    color: #a16a13;
    border: 2px solid #a16a13;
}

.btn:last-child:hover{
    background: #a16a13;
    color: #fff;
    transform: translateY(-4px);
}

.btn{
    display:inline-flex;
    align-items:center;
    gap:12px;
}

.btn i{
    font-size:16px;
    transition:transform .3s ease;
}

.btn:hover i{
    transform:translateX(3px);
}

.logo-img-1{
        height: 85px;
    width: auto;
    filter: drop-shadow(0px 2px 4px rgba(0, 0, 0, 0.05));
    transition: var(--transition-smooth);
}

</style>
</head>

<body>
    <!-- Header -->


<?php include '../components/navbar.php'; ?>

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
                    <!-- <p style="color: var(--subtext-color); margin-bottom: 15px;">Don't have an account? <a href="https://lb.liyasgoldsavings.in//refer?id=GDP0001&ref=NTAw" style="color: var(--accent-color); text-decoration: none; font-weight: 600;">Register Now</a></p> -->
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

<section class="hero-section">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="3000">
        
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="4" aria-label="Slide 5"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="5" aria-label="Slide 6"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="6" aria-label="Slide 7"></button>
        </div>

        <div class="carousel-inner">
            
            <div class="carousel-item active">
                <div class="hero-slide" style="background-image: linear-gradient(to right, rgba(0, 0, 0, 0.15) 0%, rgba(0, 0, 0, 0.3) 100%), url('./assets/new1.jpeg');">
                    <div class="container hero-container">
                        <div class="hero-content">
                            <span class="hero-subtitle">INVESTING TODAY, IMPACTING TOMORROW</span>
                            <h1 class="hero-title">
                                Pro Gee Dee <br>
                                <span class="text-gold">Ventures</span>
                            </h1>
                            <p class="hero-description">
                                Strategic investments across technology, gold, diamonds, and digital innovation. 
                                Partnering with ambitious companies to drive sustainable growth and shape tomorrow's market leaders.
                            </p>
                            <div class="hero-actions">
                                <a href="/opportunities.php" class="btn btn-gold">
                                    Explore Opportunities 
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l4 4a.5.5 0 0 1-.708.708L1.5 8.5A.5.5 0 0 1 1 8"/>
                                    </svg>
                                </a>
                                <!-- <a href="/about.php" class="btn btn-outline-light video-btn">
                                    <span style="color:black;">Learn More</span> <i class="bi bi-play-circle"></i>
                                </a> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="carousel-item">
                <div class="hero-slide" style="background-image: linear-gradient(to right, rgba(0, 0, 0, 0.15) 0%, rgba(0, 0, 0, 0.3) 70%), url('./assets/gd.jpeg');">
                    <div class="container hero-container">
                        <div class="hero-content">
                           <span class="hero-subtitle">INVESTING TODAY, IMPACTING TOMORROW</span>
                            <h1 class="hero-title">
                                Golden 
                                <span class="text-gold">Dream </span>
                            </h1>
                            <p class="hero-description">
                                   Premium gold, diamond, and gemstone jewelry with investment plans and customization options. Trusted by 10,000+ clients for quality and ethical sourcing.
                            </p>
                            <div class="hero-actions">
                                <a href="h" class="btn btn-gold">
                                    View Asset Portfolio
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l4 4a.5.5 0 0 1-.708.708L1.5 8.5A.5.5 0 0 1 1 8"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="carousel-item">
                <div class="hero-slide" style="background-image: linear-gradient(to right, rgba(0, 0, 0, 0.15) 0%, rgba(0, 0, 0, 0.3) 100%), url('./assets/brand.jpeg');">
                    <div class="container hero-container">
                        <div class="hero-content">
                          <span class="hero-subtitle">INVESTING TODAY, IMPACTING TOMORROW</span>
                            <h1 class="hero-title">
                                The Brand  
                                <span class="text-gold">Weave</span>
                            </h1>
                            <p class="hero-description">
                              Digital agency specializing in creative solutions, digital marketing, branding, and design. Empowering businesses to grow online with data-driven strategies
                            </p>
                            <div class="hero-actions">
                                <a href="/contact.php" class="btn btn-gold">
                                    Partner With Us
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l4 4a.5.5 0 0 1-.708.708L1.5 8.5A.5.5 0 0 1 1 8"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


               <div class="carousel-item">
                <div class="hero-slide" style="background-image: linear-gradient(to right, rgba(0, 0, 0, 0.15) 0%, rgba(0, 0, 0, 0.3) 100%), url('./assets/gold.jpeg');">
                    <div class="container hero-container">
                        <div class="hero-content">
                          <span class="hero-subtitle">INVESTING TODAY, IMPACTING TOMORROW</span>
                            <h1 class="hero-title">
                                Liyas Gold &  
                                <span class="text-gold">Diamonds</span>
                            </h1>
                            <p class="hero-description">
                       Trusted, high-quality gold and diamond jewelry with ethical sourcing and customization. Helping secure your financial future with confidence and award-winning service.
                            </p>
                            <div class="hero-actions">
                                <a href="https://liyasgoldanddiamonds.com/" class="btn btn-gold">
                                    Partner With Us
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l4 4a.5.5 0 0 1-.708.708L1.5 8.5A.5.5 0 0 1 1 8"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            

               <div class="carousel-item">
                <div class="hero-slide" style="background-image: linear-gradient(to right, rgba(0, 0, 0, 0.15) 40%, rgba(0, 0, 0, 0.3) 100%), url('./assets/construction.jpeg');">
                    <div class="container hero-container">
                        <div class="hero-content">
                          <span class="hero-subtitle">INVESTING TODAY, IMPACTING TOMORROW</span>
                            <h1 class="hero-title">
                                Liyas 
                                <span class="text-gold">Builders</span>
                            </h1>
                            <p class="hero-description">
                        Comprehensive construction services including residential, commercial, and industrial projects. Specializing in modern architecture, sustainable building practices, and turnkey solutions with quality craftsmanship
                            </p>
                            <div class="hero-actions">
                                <a href="/contact.php" class="btn btn-gold">
                                    Partner With Us
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l4 4a.5.5 0 0 1-.708.708L1.5 8.5A.5.5 0 0 1 1 8"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            

               <div class="carousel-item">
                <div class="hero-slide" style="background-image: linear-gradient(to right, rgba(0, 0, 0, 0.15) 0%, rgba(0, 0, 0, 0.3) 100%), url('./assets/bakes.jpeg');">
                    <div class="container hero-container">
                        <div class="hero-content">
                          <span class="hero-subtitle">INVESTING TODAY, IMPACTING TOMORROW</span>
                            <h1 class="hero-title">
                                Liyas Bakes & 
                                <span class="text-gold">Cafe</span>
                            </h1>
                            <p class="hero-description">
                       Premium bakery and cafe serving fresh pastries, cakes, breads, and specialty coffee. Offering custom cakes for celebrations, corporate events, and daily fresh baked goods with exceptional taste and presentation.
                            </p>
                            <div class="hero-actions">
                                <a href="/contact.php" class="btn btn-gold">
                                    Partner With Us
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l4 4a.5.5 0 0 1-.708.708L1.5 8.5A.5.5 0 0 1 1 8"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

               <div class="carousel-item">
                <div class="hero-slide" style="background-image: linear-gradient(to right, rgba(0, 0, 0, 0.15) 0%, rgba(0, 0, 0, 0.3) 100%), url('./assets/gdedutech.jpeg');">
                    <div class="container hero-container">
                        <div class="hero-content">
                          <span class="hero-subtitle">INVESTING TODAY, IMPACTING TOMORROW</span>
                            <h1 class="hero-title">
                                Gd Edu
                                <span class="text-gold">Tech</span>
                            </h1>
                            <p class="hero-description">
                   Empowering minds through quality education and innovative learning solutions. Join us in shaping the future of education.
                            </p>
                            <div class="hero-actions">
                                <a href="/contact.php" class="btn btn-gold">
                                    Book Your Slot Now
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l4 4a.5.5 0 0 1-.708.708L1.5 8.5A.5.5 0 0 1 1 8"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container global-hero-overlays">
            <div class="scroll-down">
                <div class="mouse-icon"></div>
                <span>Scroll Down</span>
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</section>



    <!-- <main>
        <section id="ventures" class="section bg-light">
            <div class="container">
                <h2 style="text-align: center;">Our Ventures</h2>
                <div class="text-center">
                    <img src="./assets/BROCHUR_page-00012.jpg" alt="Investment Portfolio Brochure" class="img-fluid" style="max-width: 100%; height: auto;">
                </div>
            </div>
        </section>  -->
   <section class="ventures-section">

    <div class="ventures-layout">

    

        <!-- left Side Bento Grid -->
        <div class="bento-grid">

            <div class="bento-card card-1">
                <img src="./assets/colleagues-working-desk.jpg" alt="">
                <div class="overlay">
                    <h3>Liyas Gold & Diamonds</h3>
                </div>
            </div>

            <div class="bento-card card-2">
                <img src="./assets/delicious-products-arrangement-bakery.jpg" alt="">
                <div class="overlay">
                    <h3>Liyas Bakes & Cafe</h3>
                </div>
            </div>

            <div class="bento-card card-3">
                <img src="./assets/new2.webp" alt="">
                <div class="overlay">
                    <h3>GD Edu Tech</h3>
                </div>
            </div>

            <div class="bento-card card-4">
                <img src="./assets/colleagues-working-desk.jpg" alt="">
                <div class="overlay">
                    <h3>Liyas Protein</h3>
                </div>
            </div>

            <div class="bento-card card-5">
                <img src="./assets/construction-silhouette.jpg" alt="">
                <div class="overlay">
                    <h3>Liyas Construction</h3>
                </div>
            </div>

        </div>
            <!-- Right Side -->
        <div class="ventures-content">
            <!-- <span class="subtitle">OUR PORTFOLIO</span> -->

            <h2 class="about-title" >
                <span class="explore">Explore</span> <br> <span class="text-gold">Our Ventures</span>
            </h2>
              <span class="hero-subtitle-1">WHERE GREAT VENTURES BEGIN</span>

            <!-- <p class="venture-text">
                Explore our diverse portfolio of businesses spanning luxury,
                hospitality, education, health, and infrastructure. Each
                venture is built with a commitment to excellence, innovation,
                and long-term value creation.
            </p> -->

            <!-- <a href="#" class="venture-btn">
                Explore Ventures
            </a> -->
        </div>

    </div>

</section>

<div class="scene">
    <div class="a3d" id="gallery"></div>
</div>

   <!-- Gallery Section -->
<section id="gallery" class="section-2 bg-white overflow-hidden border">
    <div class="container-fluid px-0">
        <h2 class="section-title text-center mb-5" style="color:black;">Our <span class="text-gold" style="font-weight:bold;">Gallery</span></h2>
        
        <div class="smooth-slider-container">
            <div class="smooth-slider-track">
                <?php
                $galleryDir = __DIR__ . '/assets/gallery/';
                $galleryUrl = './assets/gallery/';
                
                // Fetch images cleanly
                $images = array_values(array_filter(scandir($galleryDir), function ($file) use ($galleryDir) {
                    return !is_dir($galleryDir . $file) && preg_match('/\\.(jpg|jpeg|png|gif)$/i', $file);
                }));

                // If we have images, print them twice to ensure a seamless infinite loop
                if (!empty($images)):
                    // Merge array with itself to handle the loop overlap
                    $loopImages = array_merge($images, $images); 
                    foreach ($loopImages as $img): 
                ?>
                        <div class="smooth-slider-item">
                            <div class="card h-100  border-0 mx-2">
                                <img src="<?php echo $galleryUrl . $img; ?>" class="card-img-top" alt="Gallery Image" style="object-fit:cover; width:100%; height:420px; border-radius:30px;" loading="lazy">
                            </div>
                        </div>
                <?php 
                    endforeach;
                endif; 
                ?>
            </div>
        </div>
    </div>
</section>

        <!-- YouTube Video Section -->
        <section id="youtube-video" class="section-1">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10 text-center fade-in-up">
                        <h2 class="section-title mb-4"><span class="text-gold-1">Watch Our</span> Latest Videos</h2>
                        <!-- <p class="section-subtitle mb-5">Discover our vision and mission through our latest presentations</p> -->
                                  <span class="hero-subtitle-1">DISCOVER OUR VISION AND MISSION THROUGH OUR LATEST PRESENTATIONS</span> <BR></BR>

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
                                            style="background: #a16b14; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
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
                                            style="background: #a16b14; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
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
                                            style="background: #a16b14; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
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
                                            style="background: #a16b14; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
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
                                            style="background: #a16b14; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
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
                                            style="background: #a16b14; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
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
                                            style="background: #a16b14; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
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
                                            style="background: #a16b14; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;">
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

       <div class="cta-card">

    <div class="circles"></div>

    <div class="cta-wrapper">

        <!-- Left Content -->
        <div class="content">
            <h2>Let's Get  <br><span class="text-gold-2" style="font-weight:800;">In Touch.</span></h2>

            <p>
                Your laboratory instruments should serve you,
                not the other way around. We're happy to help you.
            </p>

            <div class="buttons">
                <a href="contact.php" class="btn">
    <i class="fa-solid fa-handshake"></i>
    Partner With Us
    <span></span>
</a>

<a href="https://yourwebsite.com" class="btn">
    <i class="fa-solid fa-arrow-up-right-from-square"></i>
    Visit Website
    <span></span>
</a>
            </div>
        </div>

        <!-- Right Logos -->
        <div class="logo-grid">
         <a href="https://thebrandweave.com/" target="__blank">   <img class="logo-img-1" src="landing_assets/images/gdlogo5.png" alt="Logo"></a>
           <a href="https://liyasgoldanddiamonds.com/" target="__blank"> <img class="logo-img-1" src="landing_assets/images/gdlogo6.png" alt="Logo"></a>
         <a href="https://gdedutech.com/" target="__blank">   <img class="logo-img-1" src="landing_assets/images/gdlogo2.png" alt="Logo"></a>
       <a href="https://liyasinternational.com/" target="__blank">     <img class="logo-img-1" src="landing_assets/images/gdlogo3.webp" alt="Logo"></a>
          <a href="https://shop.goldendream.in/" target="__blank">  <img class="logo-img-1" src="landing_assets/images/gdlogo1.png" alt="Logo"></a>
            <a href="" target="__blank"><img class="logo-img-1" src="landing_assets/images/gdlogo4.png" alt="Logo"></a>
        </div>

    </div>

</div>
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
            anchor.addEventListener('click', function(e) {
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
                heroCarousel.addEventListener('slide.bs.carousel', function(event) {
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
                heroCarousel.addEventListener('slid.bs.carousel', function(event) {
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
            document.getElementById('videoModal').addEventListener('hidden.bs.modal', function() {
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
                        playButton.style.background = '#a16b14';
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

<?php include '../components/footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollToPlugin.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
     document.addEventListener('DOMContentLoaded', function() {
    // 1. Safe Carousel Setup (Removing the problematic double sync loop)
    const heroCarouselEl = document.getElementById('heroCarousel');
    if (heroCarouselEl) {
        new bootstrap.Carousel(heroCarouselEl, {
            interval: 3000,
            wrap: true,
            ride: 'carousel'
        });
    }

    // 2. Safe Smooth Scrolling for Anchor Links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 80,
                    behavior: 'smooth'
                });
            }
        });
    });

    // 3. Performance Optimized Sticky Navbar (Uses passive scroll listener)
    const header = document.querySelector('header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        }, { passive: true });
    }

    // 4. Safe Ripple Effect without destructive innerHTML conflicts
    document.querySelectorAll('.btn').forEach(button => {
        button.addEventListener('click', function(e) {
            // Stop ripple if button contains a loader to prevent crash
            if (this.querySelector('.fa-spin')) return;

            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.4);
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

            setTimeout(() => ripple.remove(), 600);
        });
    });
});

// YouTube Video Modal Function
function openVideoModal(videoId, videoTitle) {
    const iframe = document.getElementById('youtubeIframe');
    const modalTitle = document.getElementById('videoModalLabel');
    if(modalTitle) modalTitle.textContent = videoTitle;
    if(iframe) iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0&modestbranding=1`;
    
    const modal = new bootstrap.Modal(document.getElementById('videoModal'));
    modal.show();

    document.getElementById('videoModal').addEventListener('hidden.bs.modal', function() {
        if(iframe) iframe.src = '';
    }, { once: true });
}

    </script>
 
</body>

</html>