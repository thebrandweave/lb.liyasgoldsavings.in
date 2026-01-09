<?php
session_start();
if (!isset($_SESSION['shop_admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/config.php';

$db = new Database();
$conn = $db->getConnection();
$orders = $conn->query('SELECT o.order_id, u.Name AS user_name, o.total_amount, o.order_status, o.created_at FROM orders o LEFT JOIN shop_users u ON o.CustomerUniqueID = u.CustomerUniqueID ORDER BY o.created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .orders-header-row {
            max-width: 1100px;
            margin: 40px auto 18px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .orders-title {
            font-size: var(--font-size-lg);
            font-weight: 700;
            color: var(--accent-dark);
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .orders-add-btn {
            background: linear-gradient(90deg,var(--accent) 0%,var(--accent-dark) 100%);
            color: #fff;
            padding: 8px 18px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: var(--font-size-base);
            transition: background var(--transition);
            box-shadow: 0 2px 8px rgba(123,97,255,0.10);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .orders-table-wrapper {
            max-width: 1100px;
            margin: 0 auto;
        }
        .orders-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
            font-size: var(--font-size-base);
            background: transparent;
            font-family: var(--font-main);
        }
        .orders-table th, .orders-table td {
            padding: 10px 12px;
            text-align: left;
        }
        .orders-table th.center, .orders-table td.center {
            text-align: center !important;
        }
        .orders-table th {
            background: #f7f8fa;
            color: var(--accent);
            font-weight: 700;
            border-bottom: 2px solid #ececec;
            font-size: var(--font-size-base);
        }
        .orders-table tr {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: 0 2px 8px rgba(31,38,135,0.04);
            transition: box-shadow 0.18s, background 0.18s;
        }
        .orders-table tr:hover {
            background: #f7f8fa;
            box-shadow: 0 4px 16px rgba(123,97,255,0.10);
        }
        .order-status-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 12px;
            font-size: var(--font-size-sm);
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .order-status-badge.pending {
            background: #fffbe6;
            color: #b59f00;
        }
        .order-status-badge.successful {
            background: #e6f9ed;
            color: #388e3c;
        }
        .order-status-badge.rejected {
            background: #fff0f0;
            color: #d32f2f;
        }
        .orders-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }
        .orders-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            font-size: 1.1rem;
            background: #f7f8fa;
            transition: background var(--transition), color var(--transition);
            box-shadow: 0 1px 4px rgba(123,97,255,0.04);
        }
        .orders-actions a[title="View"] { color: var(--accent); }
        .orders-actions a:hover { background: #eceef3; }
        .orders-search-bar-wrapper {
            width: 100%;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
            max-width: 1100px;
            margin-left: auto;
            margin-right: auto;
        }
        .orders-search-bar {
            width: 100%;
            max-width: 800px;
            position: relative;
            display: flex;
            align-items: center;    
            padding: 0 0 0 34px;
            height: 40px;
        }
        .orders-search-bar input {
            width: 100%;
            border: none;
            outline: none;
            background: transparent;
            font-size: 1rem;
            color: #232526;
            padding: 0 14px 0 0;
            height: 20px;
            border-radius: 999px;
            font-family: 'Montserrat', Arial, sans-serif;
            margin-left: 10px;
        }
        .orders-search-bar input:focus {
            box-shadow: 0 0 0 2px #7b61ff;
        }
        .orders-search-bar .bi-search {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #7b61ff;
            font-size: 1.2em;
            pointer-events: none;
        }
        .orders-status-filter {
            height: 40px;
            border-radius: 999px;
            border: none;
            background: #fff;
            box-shadow: 0 2px 12px rgba(123,97,255,0.06);
            font-size: 1rem;
            color: #7b61ff;
            padding: 0 24px 0 18px;
            font-family: 'Montserrat', Arial, sans-serif;
            transition: box-shadow 0.18s;
        }
        .orders-status-filter:focus {
            box-shadow: 0 0 0 2px #7b61ff;
            outline: none;
        }
        .orders-clear-btn {
            background: transparent;
            color: var(--accent);
            border-radius: 12px;
            font-size: var(--font-size-base);
            font-weight: 600;
            padding: 8px 18px;
            border: 2px solid var(--accent);
            box-shadow: none;
            display: flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
            transition: background var(--transition), color var(--transition), box-shadow var(--transition), border var(--transition);
        }
        .orders-clear-btn:hover, .orders-clear-btn:focus {
            background: rgba(123,97,255,0.08);
            color: var(--accent-dark);
            border-color: var(--accent-dark);
            box-shadow: none;
            outline: none;
        }
        @media (max-width: 700px) {
            .orders-header-row { padding: 10px 2vw; }
            .orders-title { font-size: var(--font-size-base); }
            .orders-add-btn { padding: 6px 10px; font-size: var(--font-size-sm); }
            .orders-table th, .orders-table td { padding: 7px 4px; font-size: var(--font-size-sm); }
            .orders-search-bar-wrapper { justify-content: center; flex-direction: column; gap: 10px; }
            .orders-search-bar { max-width: 100%; }
            .orders-status-filter { width: 100%; }
            .orders-clear-btn { width: 100%; justify-content: center; margin-top: 4px; }
        }
    </style>
    <script>
    function filterOrders() {
        var input = document.getElementById('orders-search-input');
        var filter = input.value.toLowerCase();
        var status = document.getElementById('orders-status-filter').value;
        var table = document.querySelector('.orders-table');
        var trs = table.querySelectorAll('tbody tr');
        trs.forEach(function(tr) {
            var user = tr.children[1].textContent.toLowerCase();
            var stat = tr.children[3].textContent.trim();
            var matchStatus = (status === 'All') || (stat === status);
            if (user.includes(filter) && matchStatus) {
                tr.style.display = '';
            } else {
                tr.style.display = 'none';
            }
        });
    }
    function clearOrderFilters() {
        document.getElementById('orders-search-input').value = '';
        document.getElementById('orders-status-filter').value = 'All';
        filterOrders();
    }
    </script>
</head>
<body>
<?php include __DIR__ . '/../components/sidebar.php'; ?>
<div class="main-content">
    <div class="orders-header-row">
        <div class="orders-title"><i class="bi bi-receipt" style="margin-right:8px;"></i>Orders</div>
    </div>
    <div class="orders-search-bar-wrapper">
        <select id="orders-status-filter" class="orders-status-filter" onchange="filterOrders()">
            <option value="All">All</option>
            <option value="pending">Pending</option>
            <option value="successful">Successful</option>
            <option value="rejected">Rejected</option>
        </select>
        <div class="orders-search-bar">
            <i class="bi bi-search"></i>
            <input id="orders-search-input" type="text" placeholder="Search by user..." onkeyup="filterOrders()" />
        </div>
        <div style="flex:1;"></div>
        <button class="orders-clear-btn" onclick="clearOrderFilters()" title="Clear filters"><i class="bi bi-x-circle"></i>Clear</button>
    </div>
    <div class="orders-table-wrapper">
        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Total</th>
                    <th class="center">Status</th>
                    <th class="center">Created</th>
                    <th class="center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?php echo $order['order_id']; ?></td>
                    <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                    <td>₹<?php echo number_format($order['total_amount'],2); ?></td>
                    <td class="center">
                        <span class="order-status-badge <?php echo $order['order_status']; ?>">
                            <?php echo ucfirst($order['order_status']); ?>
                        </span>
                    </td>
                    <td class="center"><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                    <td class="center">
                        <div class="orders-actions">
                            <a href="view.php?id=<?php echo $order['order_id']; ?>" title="View"><i class="bi bi-eye"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                <tr><td colspan="6" style="text-align:center; color:#aaa; padding:12px;">No orders found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html> 