<?php
session_start();
if (!isset($_SESSION['shop_admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/config.php';

$db = new Database();
$conn = $db->getConnection();
$admins = $conn->query('SELECT ShopAdminID, Name, Email, Status, CreatedAt FROM shopadmin ORDER BY CreatedAt DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admins List</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .admin-card {
            max-width: 1100px;
            margin: 40px auto;
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 28px 18px 18px 18px;
            backdrop-filter: var(--glass-blur);
            -webkit-backdrop-filter: var(--glass-blur);
        }
        .admin-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }
        .admin-title {
            font-size: var(--font-size-lg);
            font-weight: 700;
            color: var(--accent-dark);
            letter-spacing: 1px;
        }
        .admin-add-btn {
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
        .admin-add-btn:hover {
            background: linear-gradient(90deg,var(--accent-light) 0%,var(--accent) 100%);
            color: var(--accent-dark);
        }
        .admin-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
            font-size: var(--font-size-base);
            background: transparent;
            font-family: var(--font-main);
        }
        .admin-table th, .admin-table td {
            padding: 10px 12px;
            text-align: left;
        }
        .admin-table th.center, .admin-table td.center {
            text-align: center !important;
        }
        .admin-table th {
            background: #f7f8fa;
            color: var(--accent);
            font-weight: 700;
            border-bottom: 2px solid #ececec;
            font-size: var(--font-size-base);
        }
        .admin-table tr {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: 0 2px 8px rgba(31,38,135,0.04);
            transition: box-shadow 0.18s, background 0.18s;
        }
        .admin-table tr:hover {
            background: #f7f8fa;
            box-shadow: 0 4px 16px rgba(123,97,255,0.10);
        }
        .admin-status-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 12px;
            font-size: var(--font-size-sm);
            font-weight: 700;
            background: #e6f9ed;
            color: #388e3c;
            letter-spacing: 0.5px;
        }
        .admin-status-badge.inactive {
            background: #fff0f0;
            color: #d32f2f;
        }
        .admin-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }
        .admin-table td.center {
            vertical-align: middle !important;
        }
        .admin-actions a {
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
        .admin-actions a[title="View"] { color: var(--accent); }
        .admin-actions a[title="Edit"] { color: #ff9800; }
        .admin-actions a[title="Delete"] { color: #d32f2f; }
        .admin-actions a:hover { background: #eceef3; }
        .admin-search-bar-wrapper {
            width: 100%;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
        }
        .admin-search-bar {
            width: 100%;
            max-width: 800px;
            position: relative;
            display: flex;
            align-items: center;
            padding: 0 0 0 34px;
            height: 40px;
        }
        .admin-search-bar input {
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
        .admin-search-bar input:focus {
            box-shadow: 0 0 0 2px #7b61ff;
        }
        .admin-search-bar .bi-search {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #7b61ff;
            font-size: 1.2em;
            pointer-events: none;
        }
        .admin-status-filter {
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
        .admin-status-filter:focus {
            box-shadow: 0 0 0 2px #7b61ff;
            outline: none;
        }
        .admin-clear-btn {
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
        .admin-clear-btn:hover, .admin-clear-btn:focus {
            background: rgba(123,97,255,0.08);
            color: var(--accent-dark);
            border-color: var(--accent-dark);
            box-shadow: none;
            outline: none;
        }
        @media (max-width: 700px) {
            .admin-card { padding: 10px 2vw; }
            .admin-title { font-size: var(--font-size-base); }
            .admin-add-btn { padding: 6px 10px; font-size: var(--font-size-sm); }
            .admin-table th, .admin-table td { padding: 7px 4px; font-size: var(--font-size-sm); }
            .admin-search-bar-wrapper { justify-content: center; flex-direction: column; gap: 10px; }
            .admin-search-bar { max-width: 100%; }
            .admin-status-filter { width: 100%; }
            .admin-clear-btn { width: 100%; justify-content: center; margin-top: 4px; }
        }
    </style>
    <script>
    function filterAdmins() {
        var input = document.getElementById('admin-search-input');
        var filter = input.value.toLowerCase();
        var status = document.getElementById('admin-status-filter').value;
        var table = document.querySelector('.admin-table');
        var trs = table.querySelectorAll('tbody tr');
        trs.forEach(function(tr) {
            var name = tr.children[0].textContent.toLowerCase();
            var email = tr.children[1].textContent.toLowerCase();
            var stat = tr.children[2].textContent.trim();
            var matchStatus = (status === 'All') || (stat === status);
            if ((name.includes(filter) || email.includes(filter)) && matchStatus) {
                tr.style.display = '';
            } else {
                tr.style.display = 'none';
            }
        });
    }
    function clearAdminFilters() {
        document.getElementById('admin-search-input').value = '';
        document.getElementById('admin-status-filter').value = 'All';
        filterAdmins();
    }
    </script>
</head>
<body>
<?php include __DIR__ . '/../components/sidebar.php'; ?>
<div class="main-content">
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div id="admin-success-msg" style="max-width:420px;margin:24px auto 0 auto;display:flex;align-items:center;justify-content:center;gap:10px;background:#e6f9ed;color:#388e3c;padding:14px 0 14px 0;border-radius:10px;text-align:center;font-size:1.08rem;box-shadow:0 2px 12px rgba(56,142,60,0.08);font-weight:600;transition:opacity 0.5s;">
            <i class="bi bi-check-circle-fill" style="font-size:1.3em;"></i> Admin added successfully!
        </div>
        <script>
            setTimeout(function() {
                var msg = document.getElementById('admin-success-msg');
                if(msg) { msg.style.opacity = '0'; setTimeout(function(){ msg.style.display = 'none'; }, 500); }
            }, 3000);
        </script>
    <?php endif; ?>
    <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
        <div id="admin-deleted-msg" style="max-width:420px;margin:24px auto 0 auto;display:flex;align-items:center;justify-content:center;gap:10px;background:#fff0f0;color:#d32f2f;padding:14px 0 14px 0;border-radius:10px;text-align:center;font-size:1.08rem;box-shadow:0 2px 12px rgba(211,47,47,0.08);font-weight:600;transition:opacity 0.5s;">
            <i class="bi bi-trash-fill" style="font-size:1.3em;"></i> Admin deleted successfully!
        </div>
        <script>
            setTimeout(function() {
                var msg = document.getElementById('admin-deleted-msg');
                if(msg) { msg.style.opacity = '0'; setTimeout(function(){ msg.style.display = 'none'; }, 500); }
            }, 3000);
        </script>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] == 'self'): ?>
        <div id="admin-error-msg" style="max-width:420px;margin:24px auto 0 auto;display:flex;align-items:center;justify-content:center;gap:10px;background:#fff0f0;color:#d32f2f;padding:14px 0 14px 0;border-radius:10px;text-align:center;font-size:1.08rem;box-shadow:0 2px 12px rgba(211,47,47,0.08);font-weight:600;transition:opacity 0.5s;">
            <i class="bi bi-exclamation-circle-fill" style="font-size:1.3em;"></i> You cannot delete your own admin account!
        </div>
        <script>
            setTimeout(function() {
                var msg = document.getElementById('admin-error-msg');
                if(msg) { msg.style.opacity = '0'; setTimeout(function(){ msg.style.display = 'none'; }, 500); }
            }, 3500);
        </script>
    <?php endif; ?>
    <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
        <div id="admin-updated-msg" style="max-width:420px;margin:24px auto 0 auto;display:flex;align-items:center;justify-content:center;gap:10px;background:#e6f9ed;color:#388e3c;padding:14px 0 14px 0;border-radius:10px;text-align:center;font-size:1.08rem;box-shadow:0 2px 12px rgba(56,142,60,0.08);font-weight:600;transition:opacity 0.5s;">
            <i class="bi bi-check-circle-fill" style="font-size:1.3em;"></i> Admin updated successfully!
        </div>
        <script>
            setTimeout(function() {
                var msg = document.getElementById('admin-updated-msg');
                if(msg) { msg.style.opacity = '0'; setTimeout(function(){ msg.style.display = 'none'; }, 500); }
            }, 3000);
        </script>
    <?php endif; ?>
    <div class="admin-header-row" style="max-width:1100px;margin:40px auto 18px auto;display:flex;align-items:center;justify-content:space-between;">
        <div class="admin-title"><i class="bi bi-person-badge" style="margin-right:8px;"></i>Admins</div>
        <a href="add.php" class="admin-add-btn"><i class="bi bi-plus-lg"></i>Add Admin</a>
    </div>
    <div class="admin-search-bar-wrapper" style="max-width:1100px;margin:0 auto 18px auto;">
        <select id="admin-status-filter" class="admin-status-filter" onchange="filterAdmins()">
            <option value="All">All</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
        <div class="admin-search-bar">
            <i class="bi bi-search"></i>
            <input id="admin-search-input" type="text" placeholder="Search admins..." onkeyup="filterAdmins()" />
        </div>
        <div style="flex:1;"></div>
        <button class="admin-clear-btn" onclick="clearAdminFilters()" title="Clear filters"><i class="bi bi-x-circle"></i>Clear</button>
    </div>
    <div style="max-width:1100px;margin:0 auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th class="center">Status</th>
                    <th class="center">Created</th>
                    <th class="center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                <tr>
                    <td><?php echo htmlspecialchars($admin['Name']); ?></td>
                    <td><?php echo htmlspecialchars($admin['Email']); ?></td>
                    <td class="center"><span class="admin-status-badge<?php echo $admin['Status']==='Active'?'':' inactive'; ?>"> <?php echo $admin['Status']; ?> </span></td>
                    <td class="center"><?php echo date('d M Y', strtotime($admin['CreatedAt'])); ?></td>
                    <td class="center">
                        <div class="admin-actions">
                            <a href="view.php?id=<?php echo $admin['ShopAdminID']; ?>" title="View"><i class="bi bi-eye"></i></a>
                            <a href="edit.php?id=<?php echo $admin['ShopAdminID']; ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                            <?php if ($admin['ShopAdminID'] != $_SESSION['shop_admin_id']): ?>
                                <a href="delete.php?id=<?php echo $admin['ShopAdminID']; ?>" title="Delete" onclick="return confirm('Are you sure you want to delete this admin?');"><i class="bi bi-trash"></i></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($admins)): ?>
                <tr><td colspan="5" style="text-align:center; color:#aaa; padding:12px;">No admins found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html> 