 <!DOCTYPE html>
 <html lang="en">
 <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
 </head>
 <body>
    <style>
        


/* --- Premium Theme Variables --- */
:root {
    --gold-primary: #D4AF37;
    --gold-gradient: linear-gradient(135deg, #BF953F 0%, #FCF6BA 25%, #B38728 50%, #FBF5B7 75%, #AA771C 100%);
    --dark-luxury: #111111;
    --bg-white-solid: #FFFFFF; /* Replaced glassmorphism with solid premium white */
    --border-subtle: #EAEAEA;    /* Clean, crisp baseline border */
    --text-dark: #222222;
    --transition-smooth: all 0.4s cubic-bezier(0.25, 1, 0.5, 1);
}

/* --- Header & Navbar Wrapper --- */
.premium-header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
    background: var(--bg-white-solid);
    border-bottom: 1px solid var(--border-subtle);
    /* Soft, high-end ambient shadow instead of harsh outlines */
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02); 
    transition: var(--transition-smooth);
}

.premium-navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px ;
    transition: var(--transition-smooth);
    background:linear-gradient(271deg, #ffffff 0%, #cb9f5e 80%, #000000 100%);  
    border-bottom-left-radius:20px;
    border-bottom-right-radius:20px;
    border-right:1px solid #ffffff;
    /* border-left:1px solid #ffffff; */
    border-bottom:1px solid #ffffff;
}

.premium-navbar.scrolled {
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(15px);
        }
/* --- Logo Styling --- */
.premium-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
}

.logo-img {
    height: 45px;
    width: auto;
    filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.05));
    transition: var(--transition-smooth);
}

.logo-text {
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: 1px;
    color: var(--dark-luxury);
    text-transform: uppercase;
    font-family: 'Cinzel', 'Playfair Display', serif;
}

.accent-gold {
    background: var(--gold-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* --- Navigation Links --- */
.nav-links-wrapper {
    display: flex;
    gap: 32px;
    align-items: center;
}

.nav-link-item {
    font-weight: 500;
    text-decoration: none;
    color: var(--text-dark);
    letter-spacing: 0.5px;
    position: relative;
    padding: 6px 0;
    text-transform: uppercase;
    font-size: 0.85rem;
    transition: var(--transition-smooth);
    opacity: 0.80; /* Slightly raised default contrast for white backgrounds */
}

.nav-link-item:hover, 
.nav-link-item.active {
    opacity: 1;
    color: #AA771C;
}

/* Luxury Animated Underline */
.nav-link-item::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--gold-gradient);
    transition: var(--transition-smooth);
}

.nav-link-item:hover::after,
.nav-link-item.active::after {
    width: 100%;
}

/* --- Right Side Actions --- */
.nav-actions-wrapper {
    display: flex;
    align-items: center;
    gap: 20px;
}

/* Minimalist Country Pill */
.country-pill {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #F8F9FA; /* Off-white tint to distinguish structural elements */
    padding: 6px 14px;
    border-radius: 50px;
    border:1px solid #adadad;
}

.flag-icon {
    width: 18px;
    height: 18px;
    object-fit: cover;
    border-radius: 50%;
}

.country-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-dark);
}

/* Premium Metallic Action Button */
.premium-login-btn {
    background: var(--dark-luxury);
    border: 1px solid transparent;
    border-radius: 4px;
        border-top-left-radius:20px;
    border-bottom-right-radius:20px;
    padding: 10px 24px;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    transition: var(--transition-smooth);
}

.btn-content {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #FFF;
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.premium-login-btn::before {
    content: '';
    position: absolute;
    top: 0; left: -100%;
    width: 100%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: 0.6s;
}

/* Hover Effects */
.premium-login-btn:hover {
    background: #000;
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.25);
    border-color: var(--gold-primary);
}

.premium-login-btn:hover::before {
    left: 100%;
}

/* --- Responsive Mobile Styles --- */
.premium-mobile-toggle {
    display: none;
    flex-direction: column;
    gap: 6px;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 4px;
}

.premium-mobile-toggle .bar {
    width: 24px;
    height: 2px;
    background-color: var(--dark-luxury);
    transition: var(--transition-smooth);
}

@media (max-width: 991px) {
    .nav-links-wrapper {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background: var(--bg-white-solid);
        flex-direction: column;
        padding: 24px 0;
        gap: 20px;
        border-bottom: 2px solid var(--gold-primary);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: var(--transition-smooth);
    }

    .nav-links-wrapper.open {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .premium-mobile-toggle {
        display: flex;
    }
    
    .country-pill {
        display: none;
    }
}
    </style>


     <header class="premium-header">
    <div class="">
        <nav class="premium-navbar">
            
            <!-- Left: Brand Logo -->
            <div class="nav-brand-wrapper">
                <a href="./" class="premium-logo">
                    <img src="./landing_assets/images/gdLogo.png" alt="Golden Dream Logo" class="logo-img">
                    <!-- <span class="logo-text">Golden <span class="text-gold">Dream</span></span> -->
                </a>
            </div>

            <!-- Center: Navigation Links -->
            <div class="nav-links-wrapper" id="navLinksMenu">
                <a href="./" class="nav-link-item active">Home</a>
                <a href="./about.php" class="nav-link-item">About Us</a>
                <!-- <a href="./gallery.php" class="nav-link-item">Gallery</a> -->
                <a href="./career.php" class="nav-link-item">Career</a>
                <!-- <a href="./savings-plan.php" class="nav-link-item">Savings Plan</a> -->
                <!-- <a href="./contact.php" class="nav-link-item">Contact Us</a> -->
            </div>

            <!-- Right: Country & Premium CTA Actions -->
            <div class="nav-actions-wrapper">
                <!-- Premium Country Selector Display -->
                <div class="country-pill">
                    <img src="./landing_assets/images/india.png" alt="India Flag" class="flag-icon">
                    <span class="country-name">Indian</span>
                </div>
                
                <!-- Premium Login Button -->
                <button class="premium-login-btn" onclick="openLoginModal()">
                    <div class="btn-content">
                        <i class="far fa-user-circle"></i>
                        <span>Login</span>
                    </div>
                </button>

                <!-- Premium Hamburger for Mobile -->
                <button class="premium-mobile-toggle" aria-label="Toggle Menu" onclick="toggleMobileMenu()">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
            </div>

        </nav>
    </div>
</header>
 </body>
 </html>