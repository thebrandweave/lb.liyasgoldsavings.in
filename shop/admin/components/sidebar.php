<?php
// Dynamically determine the base path for the admin panel
$script_name = $_SERVER['SCRIPT_NAME'];
$admin_base = substr($script_name, 0, strpos($script_name, '/admin/') + strlen('/admin/'));
$current_full_path = $_SERVER['PHP_SELF'];

$nav_items = [
    [
        'url' => $admin_base . 'index.php',
        'text' => 'Dashboard',
        'icon' => 'bi-speedometer2',
        'match' => '/admin/index.php'
    ],
    [
        'url' => $admin_base . 'product/index.php',
        'text' => 'Products',
        'icon' => 'bi-bag',
        'match' => '/admin/product'
    ],
    [
        'url' => $admin_base . 'categories/index.php',
        'text' => 'Categories',
        'icon' => 'bi-tag',
        'match' => '/admin/categories'
    ],
    [
        'url' => $admin_base . 'orders/index.php',
        'text' => 'Orders',
        'icon' => 'bi-cart',
        'match' => '/admin/orders'
    ],
    [
        'url' => $admin_base . 'users/index.php',
        'text' => 'Users',
        'icon' => 'bi-people',
        'match' => '/admin/users'
    ],
    [
        'url' => $admin_base . 'admins/index.php',
        'text' => 'Admins',
        'icon' => 'bi-person-badge',
        'match' => '/admin/admins'
    ],
    [
        'url' => $admin_base . 'notifications/index.php',
        'text' => 'Notifications',
        'icon' => 'bi-bell',
        'match' => '/admin/notifications'
    ],
];
$adminName = isset($_SESSION['shop_admin_name']) ? $_SESSION['shop_admin_name'] : 'admin';
?>
<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-title">Shop Admin</div>
        <div class="sidebar-subtitle">GD Store</div>
    </div>
    <nav class="sidebar-nav">
        <?php foreach ($nav_items as $item): ?>
            <a class="sidebar-link <?php echo (strpos($current_full_path, $item['match']) !== false) ? 'active' : ''; ?>" href="<?php echo $item['url']; ?>">
                <span class="icon"><i class="bi <?php echo $item['icon']; ?>"></i></span> <?php echo $item['text']; ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-divider"></div>
    <div class="sidebar-logout">
        <a class="logout-link" href="<?php echo $admin_base; ?>logout.php"><span class="icon"><i class="bi bi-box-arrow-right"></i></span> Logout</a>
    </div>
</div> 