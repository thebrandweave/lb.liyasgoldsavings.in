<?php
// Dynamically determine the base path for shop components
$script_name = $_SERVER['SCRIPT_NAME'];
$shop_pos = strpos($script_name, '/shop/');

if ($shop_pos !== false) {
    $shop_base = substr($script_name, 0, $shop_pos + strlen('/shop/'));
} else {
    // Fallback for live server where /shop/ might not be in the path
    $shop_base = '/';
}
$current_path = $_SERVER['SCRIPT_NAME'];
?>
<!-- Top Info Bar -->
<div class="topbar">
  <div class="topbar-left">
    <span>+91 8105753472</span>
    <span>goldendream175@gmail.com</span>
  </div>
  <div class="topbar-right">
    <a href="<?php echo $shop_base; ?>contact/index.php">Contact Us</a>
    <a href="<?php echo $shop_base; ?>about/index.php">About Us</a>
  </div>
</div>
<!-- Main Navbar -->
<nav class="main-navbar">
  <div class="navbar-logo flex">
    <a href="<?php echo $shop_base; ?>index.php">
      <img src="<?php echo $shop_base; ?>assets/image/gd-store-logo2.png" alt="GD Store" class="logo-image">
    </a>
  </div>
  
  <!-- Desktop Navigation -->
  <ul class="navbar-links desktop-nav">
    <li><a href="<?php echo $shop_base; ?>index.php" class="<?php echo (strpos($current_path, '/shop/index.php') !== false) ? 'active' : ''; ?>">Home</a></li>
    <li><a href="<?php echo $shop_base; ?>category/index.php" class="<?php echo (strpos($current_path, '/shop/category') !== false) ? 'active' : ''; ?>">Categories</a></li>
    <li><a href="<?php echo $shop_base; ?>products/index.php" class="<?php echo (strpos($current_path, '/shop/products') !== false) ? 'active' : ''; ?>">Products</a></li>
    <li><a href="<?php echo $shop_base; ?>about/index.php" class="<?php echo (strpos($current_path, '/shop/about') !== false) ? 'active' : ''; ?>">About</a></li>
    <li><a href="<?php echo $shop_base; ?>contact/index.php" class="<?php echo (strpos($current_path, '/shop/contact') !== false) ? 'active' : ''; ?>">Contact</a></li>
  </ul>
  
  <div class="navbar-search-icons">
    <form class="navbar-search">
      <input type="text" placeholder="I'm looking for...">
    </form>
    <?php if (isset($_SESSION['user_id'])): ?>
      <div class="navbar-icons" style="display:flex;align-items:center;gap:18px;">
        <?php
        // Get unread notification count
        $unread_count = 0;
        $cart_count = 0;
        if (isset($_SESSION['user_id'])) {
            try {
                $db = new Database();
                $conn = $db->getConnection();
                
                // Get CustomerUniqueID for the current user
                $user_id = $_SESSION['user_id'];
                $user_source = $_SESSION['user_source'] ?? '';
                
                if ($user_source === Database::$shop_db) {
                    // For shop users, get CustomerUniqueID from shop_users table
                    $stmt = $conn->prepare('SELECT CustomerUniqueID FROM shop_users WHERE CustomerID = ?');
                    $stmt->execute([$user_id]);
                    $customer_unique_id = $stmt->fetchColumn();
                } else {
                    // For main users, use the user_id directly as CustomerUniqueID
                    $customer_unique_id = $user_id;
                }
                
                if ($customer_unique_id) {
                    // Get unread notification count
                    $stmt = $conn->prepare('SELECT COUNT(*) FROM shopnotifications WHERE CustomerUniqueID = ? AND is_read = 0');
                    $stmt->execute([$customer_unique_id]);
                    $unread_count = $stmt->fetchColumn();
                    
                    // Get cart count
                    $stmt = $conn->prepare('SELECT COUNT(*) FROM cart_items WHERE CustomerUniqueID = ?');
                    $stmt->execute([$customer_unique_id]);
                    $cart_count = $stmt->fetchColumn();
                }
            } catch (Exception $e) {
                // Handle error silently
            }
        }
        ?>
        <a href="<?php echo $shop_base; ?>notifications/index.php" class="icon-badge">
            <i class="bi bi-bell"></i>
            <?php if ($unread_count > 0): ?>
                <span class="badge"><?php echo $unread_count; ?></span>
            <?php endif; ?>
        </a>
        <a href="<?php echo $shop_base; ?>cart/index.php" class="icon-badge">
            <i class="bi bi-cart"></i>
            <?php if ($cart_count > 0): ?>
                <span class="badge"><?php echo $cart_count; ?></span>
            <?php endif; ?>
        </a>
        <a href="<?php echo $shop_base; ?>profile/index.php" class="profile-icon"><i class="bi bi-person-circle"></i></a>
        <a href="<?php echo $shop_base; ?>logout.php" class="navbar-btn" style="background:var(--accent-dark);color:#fff;padding:8px 22px;border-radius:999px;font-weight:700;text-decoration:none;transition:background 0.18s,color 0.18s;font-size: var(--font-size-sm);">Logout</a>
      </div>
    <?php else: ?>
      <div style="display:flex;gap:12px;align-items:center;">
        <a href="<?php echo $shop_base; ?>login.php" class="navbar-btn" style="background:transparent;color:var(--accent-dark);border:2px solid var(--accent-dark);padding:8px 22px;border-radius:999px;font-weight:700;text-decoration:none;transition:background 0.18s,color 0.18s;">Login</a>
        <!-- <a href="<?php echo $shop_base; ?>signup.php" class="navbar-btn" style="background:var(--accent-dark);color:#fff;padding:8px 22px;border-radius:999px;font-weight:700;text-decoration:none;transition:background 0.18s,color 0.18s;">Sign Up</a> -->
      </div>
    <?php endif; ?>
  </div>
  
  <!-- Mobile Hamburger Button -->
  <button class="mobile-menu-toggle" id="mobileMenuToggle">
    <span></span>
    <span></span>
    <span></span>
  </button>
</nav>

<!-- Mobile Menu Overlay -->
<div class="mobile-menu-overlay" id="mobileMenuOverlay">
  <div class="mobile-menu-content">
    <div class="mobile-menu-header">
      <div class="mobile-logo">
        <a href="<?php echo $shop_base; ?>index.php">
          <img src="<?php echo $shop_base; ?>assets/image/gd-store-logo2.png" alt="GD Store" class="logo-image">
        </a>
      </div>
      <button class="mobile-menu-close" id="mobileMenuClose" type="button">
        <span class="close-icon">×</span>
      </button>
    </div>
    
    <div class="mobile-menu-body">
      <!-- Mobile Search -->
      <div class="mobile-search">
        <form class="navbar-search">
          <input type="text" placeholder="I'm looking for...">
          <button type="submit"><i class="bi bi-search"></i></button>
        </form>
      </div>
      
      <!-- Mobile Navigation Links -->
      <ul class="mobile-nav-links">
        <li><a href="<?php echo $shop_base; ?>index.php" class="<?php echo (strpos($current_path, '/shop/index.php') !== false) ? 'active' : ''; ?>">Home</a></li>
        <li><a href="<?php echo $shop_base; ?>category/index.php" class="<?php echo (strpos($current_path, '/shop/category') !== false) ? 'active' : ''; ?>">Categories</a></li>
        <li><a href="<?php echo $shop_base; ?>products/index.php" class="<?php echo (strpos($current_path, '/shop/products') !== false) ? 'active' : ''; ?>">Products</a></li>
        <li><a href="<?php echo $shop_base; ?>about/index.php" class="<?php echo (strpos($current_path, '/shop/about') !== false) ? 'active' : ''; ?>">About</a></li>
        <li><a href="<?php echo $shop_base; ?>contact/index.php" class="<?php echo (strpos($current_path, '/shop/contact') !== false) ? 'active' : ''; ?>">Contact</a></li>
      </ul>
      
      <!-- Mobile User Actions -->
      <div class="mobile-user-actions">
        <?php if (isset($_SESSION['user_id'])): ?>
          <div class="mobile-icons">
            <a href="<?php echo $shop_base; ?>notifications/index.php" class="mobile-icon-item">
              <i class="bi bi-bell"></i>
              <span>Notifications</span>
              <?php if ($unread_count > 0): ?>
                <span class="mobile-badge"><?php echo $unread_count; ?></span>
              <?php endif; ?>
            </a>
            <a href="<?php echo $shop_base; ?>cart/index.php" class="mobile-icon-item">
              <i class="bi bi-cart"></i>
              <span>Cart</span>
              <?php if ($cart_count > 0): ?>
                <span class="mobile-badge"><?php echo $cart_count; ?></span>
              <?php endif; ?>
            </a>
            <a href="<?php echo $shop_base; ?>profile/index.php" class="mobile-icon-item">
              <i class="bi bi-person-circle"></i>
              <span>Profile</span>
            </a>
          </div>
          <a href="<?php echo $shop_base; ?>logout.php" class="mobile-logout-btn">Logout</a>
        <?php else: ?>
          <div class="mobile-auth-buttons">
            <a href="<?php echo $shop_base; ?>login.php" class="mobile-login-btn">Login</a>
            <!-- <a href="<?php echo $shop_base; ?>signup.php" class="mobile-signup-btn">Sign Up</a> -->
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<style>
.navbar-links a.active {
    color: var(--accent-dark) !important;
    font-weight: bold;
}

.navbar-logo {
    display: flex;
    align-items: center;
}

.logo-image {
    height: 60px;
    width: auto;
    object-fit: contain;
    transition: transform 0.2s ease;
    margin: 2px 0;
}

.logo-image:hover {
    transform: scale(1.05);
}

.profile-icon {
    color: #232526;
    font-size: 1.5rem;
    position: relative;
    text-decoration: none;
    transition: color 0.2s ease;
}

.profile-icon:hover {
    color: var(--accent-dark);
}

/* Ensure badge is always visible */
.icon-badge .badge {
    display: inline-block !important;
    min-width: 18px;
    min-height: 18px;
    text-align: center;
    line-height: 14px;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Mobile Menu Toggle Button */
.mobile-menu-toggle {
    display: none;
    flex-direction: column;
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    gap: 4px;
    z-index: 1001;
}

.mobile-menu-toggle span {
    width: 25px;
    height: 3px;
    background-color: var(--accent-dark);
    border-radius: 2px;
    transition: all 0.3s ease;
}

/* Hamburger button stays as three lines - no transformation */
.mobile-menu-toggle.active span {
    /* Keep original hamburger appearance */
}

/* Mobile Menu Overlay */
.mobile-menu-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.95);
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    overflow: hidden;
    margin: 0;
    padding: 0;
}

.mobile-menu-overlay.active {
    opacity: 1;
    visibility: visible;
}

.mobile-menu-content {
    width: 100vw;
    height: 100vh;
    display: flex;
    flex-direction: column;
    padding: 0;
    margin: 0;
    overflow: hidden;
    box-sizing: border-box;
    position: relative;
}

.mobile-menu-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(0, 0, 0, 0.3);
    position: relative;
    z-index: 1001;
    flex-shrink: 0;
    box-sizing: border-box;
    width: 100%;
}

.mobile-logo .logo-image {
    height: 40px;
    width: auto;
    max-width: 150px;
}

.mobile-menu-close {
    background: var(--accent-dark);
    border: 2px solid var(--accent-dark);
    color: white;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    position: relative;
    z-index: 1002;
    flex-shrink: 0;
    box-sizing: border-box;
    font-size: 0;
    line-height: 0;
}

.mobile-menu-close:hover {
    background-color: var(--accent);
    transform: scale(1.05);
}

.close-icon {
    font-size: 24px;
    font-weight: bold;
    line-height: 1;
    color: inherit;
    pointer-events: none;
    display: block;
}

.mobile-menu-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 20px;
    gap: 30px;
    overflow-y: auto;
    box-sizing: border-box;
    width: 100%;
    min-height: 0;
}

/* Mobile Search */
.mobile-search {
    margin-bottom: 20px;
    width: 100%;
    box-sizing: border-box;
}

.mobile-search .navbar-search {
    position: relative;
    width: 100%;
    box-sizing: border-box;
}

.mobile-search input {
    width: 100%;
    padding: 15px 50px 15px 20px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 25px;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    font-size: 16px;
    backdrop-filter: blur(10px);
    box-sizing: border-box;
    max-width: 100%;
}

.mobile-search input::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.mobile-search input:focus {
    outline: none;
    border-color: var(--accent-dark);
    background: rgba(255, 255, 255, 0.15);
}

.mobile-search button {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    padding: 5px;
    z-index: 10;
}

/* Mobile Navigation Links */
.mobile-nav-links {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
    width: 100%;
    box-sizing: border-box;
}

.mobile-nav-links li {
    width: 100%;
    box-sizing: border-box;
}

.mobile-nav-links li a {
    display: block;
    padding: 16px 20px;
    color: white;
    text-decoration: none;
    font-size: 16px;
    font-weight: 600;
    border-radius: 12px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    background: rgba(255, 255, 255, 0.05);
    width: 100%;
    box-sizing: border-box;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.mobile-nav-links li a:hover,
.mobile-nav-links li a.active {
    background: rgba(255, 255, 255, 0.1);
    border-color: var(--accent-dark);
    color: var(--accent-dark);
    transform: translateX(8px);
    box-shadow: 0 4px 12px rgba(255, 214, 0, 0.2);
}

/* Mobile User Actions */
.mobile-user-actions {
    margin-top: auto;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    width: 100%;
    box-sizing: border-box;
}

.mobile-icons {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 20px;
    width: 100%;
    box-sizing: border-box;
}

.mobile-icon-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 16px 20px;
    color: white;
    text-decoration: none;
    border-radius: 12px;
    transition: all 0.3s ease;
    position: relative;
    background: rgba(255, 255, 255, 0.05);
    border: 2px solid transparent;
    width: 100%;
    box-sizing: border-box;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.mobile-icon-item:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(8px);
    border-color: var(--accent-dark);
}

.mobile-icon-item i {
    font-size: 18px;
    min-width: 20px;
    color: var(--accent-dark);
    flex-shrink: 0;
}

.mobile-icon-item span {
    font-size: 16px;
    font-weight: 600;
    flex: 1;
    min-width: 0;
}

.mobile-badge {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    background: var(--accent-dark);
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    box-shadow: 0 2px 8px rgba(255, 214, 0, 0.3);
    flex-shrink: 0;
}

.mobile-logout-btn,
.mobile-login-btn,
.mobile-signup-btn {
    display: block;
    width: 100%;
    padding: 16px 20px;
    text-align: center;
    text-decoration: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 16px;
    transition: all 0.3s ease;
    margin-bottom: 12px;
    border: 2px solid transparent;
    box-sizing: border-box;
    max-width: 100%;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.mobile-logout-btn,
.mobile-signup-btn {
    background: var(--accent-dark);
    color: white;
    box-shadow: 0 4px 12px rgba(255, 214, 0, 0.2);
}

.mobile-login-btn {
    background: transparent;
    color: var(--accent-dark);
    border: 2px solid var(--accent-dark);
}

.mobile-logout-btn:hover,
.mobile-signup-btn:hover {
    background: var(--accent);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(255, 214, 0, 0.3);
}

.mobile-login-btn:hover {
    background: var(--accent-dark);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 214, 0, 0.2);
}

.mobile-auth-buttons {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
    box-sizing: border-box;
}

/* Responsive Design */
@media (max-width: 768px) {
    .desktop-nav,
    .navbar-search-icons {
        display: none !important;
    }
    
    .mobile-menu-toggle {
        display: flex;
    }
    
    .logo-image {
        height: 45px;
    }
    
    .main-navbar {
        justify-content: space-between;
        padding: 15px 20px;
    }
    
    .topbar {
        display: none;
    }
    
    /* Mobile menu responsive adjustments */
    .mobile-menu-content {
        padding: 0;
    }
    
    .mobile-menu-header {
        padding: 15px 20px;
    }
    
    .mobile-menu-body {
        padding: 15px 20px;
        gap: 25px;
    }
    
    .mobile-nav-links li a {
        padding: 14px 18px;
        font-size: 15px;
    }
    
    .mobile-icon-item {
        padding: 14px 18px;
    }
    
    .mobile-icon-item span {
        font-size: 15px;
    }
    
    .mobile-logout-btn,
    .mobile-login-btn,
    .mobile-signup-btn {
        padding: 14px 18px;
        font-size: 15px;
    }
}

@media (max-width: 480px) {
    .mobile-menu-header {
        padding: 12px 15px;
    }
    
    .mobile-menu-body {
        padding: 12px 15px;
        gap: 20px;
    }
    
    .mobile-nav-links li a {
        padding: 12px 16px;
        font-size: 14px;
    }
    
    .mobile-icon-item {
        padding: 12px 16px;
    }
    
    .mobile-icon-item span {
        font-size: 14px;
    }
    
    .mobile-logout-btn,
    .mobile-login-btn,
    .mobile-signup-btn {
        padding: 12px 16px;
        font-size: 14px;
    }
    
    .mobile-logo .logo-image {
        height: 35px;
    }
    
    .mobile-menu-close {
        width: 40px !important;
        height: 40px !important;
        font-size: 18px !important;
    }
}

@media (min-width: 769px) {
    .mobile-menu-overlay {
        display: none;
    }
}

/* Prevent body scroll when mobile menu is open */
body.menu-open {
    overflow: hidden;
    position: fixed;
    width: 100%;
    height: 100%;
}

/* Ensure mobile menu covers full viewport */
@media (max-width: 768px) {
    .mobile-menu-overlay {
        width: 100vw !important;
        height: 100vh !important;
        left: 0 !important;
        right: 0 !important;
        top: 0 !important;
        bottom: 0 !important;
    }
    
    .mobile-menu-content {
        width: 100vw !important;
        height: 100vh !important;
    }
    
    /* Hide hamburger button when mobile menu is open */
    .mobile-menu-overlay.active ~ .main-navbar .mobile-menu-toggle,
    .mobile-menu-overlay.active + .main-navbar .mobile-menu-toggle {
        display: none !important;
    }
    
    /* Alternative approach - hide hamburger when body has menu-open class */
    body.menu-open .mobile-menu-toggle {
        display: none !important;
    }
}
</style>

<script>
// Simple and direct mobile menu functionality
function openMobileMenu() {
    document.getElementById('mobileMenuOverlay').classList.add('active');
    document.body.classList.add('menu-open');
    console.log('Menu opened');
}

function closeMobileMenu() {
    console.log('closeMobileMenu function called');
    const overlay = document.getElementById('mobileMenuOverlay');
    
    if (overlay) {
        overlay.classList.remove('active');
        overlay.style.display = ''; // Reset display property
        console.log('Removed active class from overlay');
    }
    
    document.body.classList.remove('menu-open');
    console.log('Menu closed');
}

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up mobile menu...');
    
    // Get elements
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
    const mobileMenuClose = document.getElementById('mobileMenuClose');
    
    console.log('Elements found:', {
        toggle: !!mobileMenuToggle,
        overlay: !!mobileMenuOverlay,
        close: !!mobileMenuClose
    });
    
    if (!mobileMenuToggle || !mobileMenuOverlay || !mobileMenuClose) {
        console.error('Some mobile menu elements not found!');
        return;
    }
    
    // Open menu
    mobileMenuToggle.addEventListener('click', function() {
        console.log('Toggle clicked');
        openMobileMenu();
        // Hide hamburger button when menu opens
        mobileMenuToggle.style.display = 'none';
    });
    
    // Simple close button handler - completely isolated
    mobileMenuClose.onclick = function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        console.log('CLOSE BUTTON CLICKED!');
        
        // Close the menu properly
        const overlay = document.getElementById('mobileMenuOverlay');
        if (overlay) {
            overlay.classList.remove('active');
            overlay.style.display = ''; // Reset display property
        }
        document.body.classList.remove('menu-open');
        
        // Show hamburger button when menu closes
        const toggle = document.getElementById('mobileMenuToggle');
        if (toggle) {
            toggle.style.display = 'flex';
        }
        
        console.log('Menu closed!');
        return false;
    };
    
    // Close when clicking overlay background
    mobileMenuOverlay.addEventListener('click', function(e) {
        if (e.target === mobileMenuOverlay) {
            console.log('Overlay background clicked');
            closeMobileMenu();
        }
    });
    
    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileMenuOverlay.classList.contains('active')) {
            console.log('Escape key pressed');
            closeMobileMenu();
        }
    });
    
    // Close on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && mobileMenuOverlay.classList.contains('active')) {
            closeMobileMenu();
        }
    });
    
    // Close when clicking navigation links
    const mobileNavLinks = document.querySelectorAll('.mobile-nav-links a');
    mobileNavLinks.forEach(link => {
        link.addEventListener('click', closeMobileMenu);
    });
    
    // Close when clicking user action links
    const mobileUserActions = document.querySelectorAll('.mobile-user-actions a');
    mobileUserActions.forEach(link => {
        link.addEventListener('click', closeMobileMenu);
    });
    
    console.log('Mobile menu setup complete');
    
    // Final close button handler - runs after everything else
    setTimeout(function() {
        const closeBtn = document.getElementById('mobileMenuClose');
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                console.log('FINAL CLOSE HANDLER TRIGGERED');
                
                // Close the overlay properly
                const overlay = document.getElementById('mobileMenuOverlay');
                if (overlay) {
                    overlay.classList.remove('active');
                    overlay.style.display = ''; // Reset display property
                }
                
                // Remove body class
                document.body.classList.remove('menu-open');
                
                // Show hamburger button when menu closes
                const toggle = document.getElementById('mobileMenuToggle');
                if (toggle) {
                    toggle.style.display = 'flex';
                }
                
                console.log('Menu forcefully closed');
                return false;
            }, true); // Use capture phase
        }
    }, 100);
});

// Make functions globally available
window.openMobileMenu = openMobileMenu;
window.closeMobileMenu = closeMobileMenu;
</script> 
