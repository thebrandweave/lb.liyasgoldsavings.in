<?php
// Premium dashboard redesign with advanced features (no sales trend, no revenue)
session_start();
if (!isset($_SESSION['shop_admin_id'])) {
    header('Location: login.php');
    exit();
}
require_once '../config/config.php';

$db = new Database();
$conn = $db->getConnection();

// Overview cards (no revenue)
$totalProducts = $conn->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalOrders = $conn->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalUsers = $conn->query('SELECT COUNT(*) FROM shop_users')->fetchColumn();
$pendingOrders = $conn->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")->fetchColumn();
$lowStock = $conn->query('SELECT COUNT(*) FROM products WHERE stock < 10')->fetchColumn();

// Top selling products
$topProducts = $conn->query("SELECT p.name, SUM(oi.quantity) as sold FROM order_items oi JOIN products p ON oi.product_id = p.product_id GROUP BY oi.product_id ORDER BY sold DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Recent signups - using shop_users table
$recentSignups = $conn->query('SELECT Name as name, Email as email, CustomerID as created_at FROM shop_users ORDER BY CustomerID DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);

// Recent orders - updated to use shop_users table
$recentOrders = $conn->query('SELECT o.order_id, u.Name as name, o.total_amount, o.order_status, o.created_at FROM orders o JOIN shop_users u ON o.CustomerUniqueID = u.CustomerUniqueID ORDER BY o.created_at DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);

// Low stock products for notifications
$lowStockProducts = $conn->query('SELECT name, stock FROM products WHERE stock < 10')->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shop Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .dashboard-section { display: flex; flex-wrap: wrap; gap: 32px; margin: 36px 7vw 0 7vw; align-items: flex-start; }
        .dashboard-analytics-section { display: flex; flex-wrap: wrap; gap: 32px; margin: 36px 7vw 0 7vw; }
        .dashboard-overview-cards { display: flex; gap: 24px; justify-content: center; margin: 36px 7vw 0 7vw; flex-wrap: wrap; }
        .dashboard-overview-card { background: var(--card-bg); border-radius: var(--border-radius); box-shadow: var(--card-shadow); min-width: 160px; max-width: 200px; flex: 1 1 160px; padding: 18px 12px 14px 12px; display: flex; flex-direction: column; align-items: center; margin-bottom: 18px; border: 1px solid #ececec; backdrop-filter: var(--glass-blur); -webkit-backdrop-filter: var(--glass-blur); }
        .dashboard-overview-card .card-icon { font-size: 1.3rem; margin-bottom: 6px; color: var(--icon-color); }
        .dashboard-overview-card .card-title { color: var(--accent-dark); font-size: var(--font-size-sm); font-weight: 600; margin-bottom: 2px; }
        .dashboard-overview-card .card-value { color: var(--accent); font-size: 1.1rem; font-weight: 700; letter-spacing: 1px; margin-bottom: 2px; }
        .dashboard-top-products-card, .dashboard-recent-signups-card { flex: 1 1 220px; min-width: 180px; background: var(--card-bg); border-radius: var(--border-radius); box-shadow: var(--card-shadow); padding: 28px 24px 24px 24px; display: flex; flex-direction: column; gap: 12px; align-items: stretch; backdrop-filter: var(--glass-blur); -webkit-backdrop-filter: var(--glass-blur); }
        .dashboard-top-products-card h3, .dashboard-recent-signups-card h3 { margin-top: 0; font-size: var(--font-size-lg); color: var(--accent-dark); font-weight: 700; margin-bottom: 12px; }
        .dashboard-top-products-list, .dashboard-signups-list { list-style: none; padding: 0; margin: 0; }
        .dashboard-top-products-list li, .dashboard-signups-list li { font-size: var(--font-size-base); margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between; }
        .dashboard-bottom-section { display: flex; flex-wrap: wrap; gap: 32px; margin: 36px 7vw 0 7vw; }
        .dashboard-orders-card { flex: 2 1 350px; min-width: 320px; background: var(--card-bg); border-radius: var(--border-radius); box-shadow: var(--card-shadow); padding: 28px 24px 24px 24px; backdrop-filter: var(--glass-blur); -webkit-backdrop-filter: var(--glass-blur); display: flex; flex-direction: column; }
        .dashboard-orders-card h3 { margin-top: 0; font-size: var(--font-size-lg); color: var(--accent-dark); font-weight: 700; margin-bottom: 18px; }
        .dashboard-orders-table { width: 100%; border-collapse: collapse; font-size: var(--font-size-base); background: transparent; }
        .dashboard-orders-table th, .dashboard-orders-table td { padding: 8px 6px; text-align: left; }
        .dashboard-orders-table th { background: #f7f8fa; color: var(--accent); font-weight: 600; border-bottom: 2px solid #ececec; position: sticky; top: 0; z-index: 1; }
        .dashboard-orders-table tr { border-bottom: 1px solid #ececec; }
        .dashboard-orders-table tr:last-child { border-bottom: none; }
        .status-badge { display: inline-block; padding: 4px 14px; border-radius: 12px; font-size: var(--font-size-sm); font-weight: 600; text-transform: capitalize; }
        .status-pending { background: #fff7e6; color: #f59e42; }
        .status-confirmed { background: #e6f0ff; color: var(--accent); }
        .status-shipped { background: #e6f9ed; color: #388e3c; }
        .status-cancelled { background: #fff0f0; color: #d32f2f; }
        .dashboard-actions-card { flex: 1 1 220px; min-width: 180px; background: var(--card-bg); border-radius: var(--border-radius); box-shadow: var(--card-shadow); padding: 28px 24px 24px 24px; display: flex; flex-direction: column; gap: 18px; align-items: stretch; backdrop-filter: var(--glass-blur); -webkit-backdrop-filter: var(--glass-blur); }
        .dashboard-actions-card h3 { margin-top: 0; font-size: var(--font-size-lg); color: var(--accent-dark); font-weight: 700; margin-bottom: 18px; }
        .dashboard-action-btn { background: linear-gradient(90deg, var(--accent) 0%, var(--accent-dark) 100%); color: #fff; padding: 10px 0; border-radius: 12px; text-align: center; text-decoration: none; font-weight: 600; font-size: var(--font-size-base); transition: background var(--transition), color var(--transition); margin-bottom: 8px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 2px 8px rgba(123,97,255,0.10); }
        .dashboard-action-btn:hover { background: linear-gradient(90deg, var(--accent-light) 0%, var(--accent) 100%); color: var(--accent-dark); }
        .dashboard-alerts { margin-bottom: 18px; }
        .dashboard-alert { background: #fff0f0; color: #d32f2f; border-radius: 8px; padding: 8px 12px; font-size: var(--font-size-sm); margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
        .dashboard-alert-info { background: #e6f9ed; color: #388e3c; }
        @media (max-width: 900px) { .dashboard-section, .dashboard-analytics-section, .dashboard-bottom-section, .dashboard-overview-cards { flex-direction: column; gap: 18px; } }
    </style>
</head>
<body>
    <?php include __DIR__ . '/components/sidebar.php'; ?>
    <div class="main-content">
        <div class="dashboard-header">
            <span class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['shop_admin_name']); ?>!</span>
        </div>
        <div class="dashboard-overview-cards">
            <div class="dashboard-overview-card"><div class="card-icon"><i class="bi bi-box-seam"></i></div><div class="card-title">Products</div><div class="card-value"><?php echo $totalProducts; ?></div></div>
            <div class="dashboard-overview-card"><div class="card-icon"><i class="bi bi-cart-check"></i></div><div class="card-title">Orders</div><div class="card-value"><?php echo $totalOrders; ?></div></div>
            <div class="dashboard-overview-card"><div class="card-icon"><i class="bi bi-people"></i></div><div class="card-title">Users</div><div class="card-value"><?php echo $totalUsers; ?></div></div>
            <div class="dashboard-overview-card"><div class="card-icon"><i class="bi bi-hourglass-split"></i></div><div class="card-title">Pending Orders</div><div class="card-value"><?php echo $pendingOrders; ?></div></div>
            <div class="dashboard-overview-card"><div class="card-icon"><i class="bi bi-exclamation-triangle"></i></div><div class="card-title">Low Stock</div><div class="card-value"><?php echo $lowStock; ?></div></div>
            </div>
        <div class="dashboard-analytics-section">
            <div class="dashboard-top-products-card">
                <h3>Top Selling Products</h3>
                <ul class="dashboard-top-products-list">
                    <?php foreach ($topProducts as $prod): ?>
                        <li><span><?php echo htmlspecialchars($prod['name']); ?></span> <span style="color:var(--accent);font-weight:600;">×<?php echo $prod['sold']; ?></span></li>
                    <?php endforeach; ?>
                    <?php if (empty($topProducts)): ?><li>No sales data</li><?php endif; ?>
                </ul>
            </div>
            <div class="dashboard-recent-signups-card">
                <h3>Recent Signups</h3>
                <ul class="dashboard-signups-list">
                    <?php foreach ($recentSignups as $signup): ?>
                        <li><span><?php echo htmlspecialchars($signup['name']); ?></span> <span style="color:var(--text-muted);font-size:var(--font-size-sm);"><?php echo date('d M', strtotime($signup['created_at'])); ?></span></li>
                    <?php endforeach; ?>
                    <?php if (empty($recentSignups)): ?><li>No recent signups</li><?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="dashboard-bottom-section">
            <div class="dashboard-orders-card">
                <h3>Recent Orders</h3>
                <table class="dashboard-orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($order['name']); ?></td>
                            <td>₹<?php echo number_format($order['total_amount'],2); ?></td>
                            <td>
                                <?php
                                $statusClass = 'status-badge ';
                                if ($order['order_status'] === 'pending') $statusClass .= 'status-pending';
                                else if ($order['order_status'] === 'confirmed') $statusClass .= 'status-confirmed';
                                else if ($order['order_status'] === 'shipped') $statusClass .= 'status-shipped';
                                else $statusClass .= 'status-cancelled';
                                ?>
                                <span class="<?php echo $statusClass; ?>"> <?php echo $order['order_status']; ?> </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recentOrders)): ?>
                        <tr><td colspan="5" style="text-align:center; color:#aaa; padding:12px;">No recent orders</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="dashboard-actions-card">
                <div class="dashboard-alerts">
                    <?php if ($pendingOrders > 0): ?>
                        <div class="dashboard-alert"><i class="bi bi-hourglass-split"></i> <?php echo $pendingOrders; ?> order(s) pending confirmation.</div>
                    <?php endif; ?>
                    <?php foreach ($lowStockProducts as $prod): ?>
                        <div class="dashboard-alert dashboard-alert-info"><i class="bi bi-exclamation-triangle"></i> Low stock: <?php echo htmlspecialchars($prod['name']); ?> (<?php echo $prod['stock']; ?> left)</div>
                    <?php endforeach; ?>
                </div>
                <h3>Quick Actions</h3>
                <a href="products.php" class="dashboard-action-btn"><i class="bi bi-plus-circle"></i> Add Product</a>
                <a href="../admin/categories/index.php" class="dashboard-action-btn"><i class="bi bi-tags"></i> Add Category</a>
                <a href="../admin/admins/add.php" class="dashboard-action-btn"><i class="bi bi-person-plus"></i> Add Admin</a>
                <a href="export.php" class="dashboard-action-btn"><i class="bi bi-download"></i> Export Data</a>
            </div>
        </div>
    </div>
</body>
</html>
