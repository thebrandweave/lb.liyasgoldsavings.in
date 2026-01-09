<?php
session_start();
if (!isset($_SESSION['shop_admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Redirect to registered users page by default
header('Location: registered.php');
exit();

// Prevent undefined variable warnings
$success = '';
$error = '';

// Get filter parameters and pagination
$userType = $_GET['type'] ?? 'all';
$searchTerm = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10; // Users per page

// Fetch users with pagination (only shop users)
$userManager = new UserManager();
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;
$searchTerm = $_GET['search'] ?? '';
$result = $userManager->getShopUsers($page, $perPage, $searchTerm);
$users = $result['users'];
$totalUsers = $result['total'];
$totalPages = $result['pages'];
$currentPage = $result['current_page'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Users</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .users-card {
            max-width: 1100px;
            margin: 40px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(31,38,135,0.06);
            padding: 36px 28px 32px 28px;
        }
        .users-header-row {
            max-width: 1100px;
            margin: 40px auto 18px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .users-title {
            font-size: var(--font-size-lg);
            font-weight: 700;
            color: var(--accent-dark);
            letter-spacing: 1px;
        }
        .users-add-btn {
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
        .users-add-btn:hover {
            background: linear-gradient(90deg,var(--accent-light) 0%,var(--accent) 100%);
            color: var(--accent-dark);
        }
        .users-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .users-nav a {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .users-nav a.active {
            background: var(--accent-dark);
            color: white;
        }
        .users-nav a:not(.active) {
            background: #f8f9fa;
            color: #666;
        }
        .users-nav a:not(.active):hover {
            background: #e9ecef;
        }
        .users-form {
            display: none;
            flex-wrap: wrap;
            gap: 18px;
            margin-bottom: 18px;
            background: #f7f8fa;
            border-radius: 10px;
            padding: 18px 18px 8px 18px;
        }
        .users-form.active { display: flex; }
        .users-form input, .users-form select {
            padding: 10px 12px;
            border: 1px solid #f2e9e1;
            border-radius: 7px;
            background: #fff;
            color: #232526;
            font-size: 1rem;
            flex: 1 1 180px;
        }
        .users-form button {
            padding: 10px 24px;
            border: none;
            border-radius: 7px;
            background: linear-gradient(90deg,#6a82fb 0%,#5f2c82 100%);
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0;
        }
        .users-form button:hover {
            background: linear-gradient(90deg,#5f2c82 0%,#6a82fb 100%);
        }
        .users-table-wrapper {
            max-width: 1100px;
            margin: 0 auto;
        }
        .users-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
            font-size: var(--font-size-base);
            background: transparent;
            font-family: var(--font-main);
        }
        .users-table th, .users-table td {
            padding: 10px 12px;
            text-align: left;
        }
        .users-table th.center, .users-table td.center {
            text-align: center !important;
        }
        .users-table th {
            background: #f7f8fa;
            color: var(--accent);
            font-weight: 700;
            border-bottom: 2px solid #ececec;
            font-size: var(--font-size-base);
        }
        .users-table tr {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: 0 2px 8px rgba(31,38,135,0.04);
            transition: box-shadow 0.18s, background 0.18s;
        }
        .users-table tr:hover {
            background: #f7f8fa;
            box-shadow: 0 4px 16px rgba(123,97,255,0.10);
        }
        .users-table tr:nth-child(even) {
            background: #fafbfc;
        }
        .users-table tr {
            border-bottom: 1px solid #f2e9e1;
        }
        .users-message {
            margin-bottom: 18px;
            padding: 10px 0;
            border-radius: 6px;
            text-align: center;
            font-size: 1rem;
        }
        .users-message.success { background: #e6f9ed; color: #388e3c; }
        .users-message.error { background: #fff0f0; color: #d32f2f; }
        .users-search-bar-wrapper {
            width: 100%;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
        }
        .users-search-bar {
            width: 100%;
            max-width: 800px;
            position: relative;
            display: flex;
            align-items: center;
            padding: 0 0 0 34px;
            height: 40px;
        }
        .users-search-bar input {
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
        .users-search-bar input:focus {
            box-shadow: 0 0 0 2px #7b61ff;
        }
        .users-search-bar .bi-search {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #7b61ff;
            font-size: 1.2em;
            pointer-events: none;
        }
        .users-status-filter {
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
        .users-status-filter:focus {
            box-shadow: 0 0 0 2px #7b61ff;
            outline: none;
        }
        .users-clear-btn {
            background: transparent;
            color: #7b61ff;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            padding: 8px 18px;
            border: 2px solid #7b61ff;
            box-shadow: none;
            display: flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
            transition: background 0.18s, color 0.18s, box-shadow 0.18s, border 0.18s;
        }
        .users-clear-btn:hover, .users-clear-btn:focus {
            background: rgba(123,97,255,0.08);
            color: #5f2c82;
            border-color: #5f2c82;
            box-shadow: none;
            outline: none;
        }
        .user-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
            height: 100%;
        }
        .user-actions a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            font-size: 1.1rem;
            background: #f7f8fa;
            transition: background 0.18s, color 0.18s;
            box-shadow: 0 1px 4px rgba(123,97,255,0.04);
        }
        .user-actions a[title="View"] { color: #7b61ff; }
        .user-actions a[title="Edit"] { color: #ff9800; }
        .user-actions a[title="Delete"] { color: #d32f2f; }
        .user-actions a:hover { background: #eceef3; }
        .users-table th.center, .users-table td.center {
            text-align: center !important;
            vertical-align: middle !important;
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        .pagination a, .pagination span {
            padding: 8px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            min-width: 40px;
            text-align: center;
        }
        .pagination a {
            background: #f8f9fa;
            color: #666;
            border: 1px solid #e9ecef;
        }
        .pagination a:hover {
            background: var(--accent-dark);
            color: white;
            border-color: var(--accent-dark);
        }
        .pagination .current {
            background: var(--accent-dark);
            color: white;
            border: 1px solid var(--accent-dark);
        }
        .pagination .disabled {
            background: #f1f3f4;
            color: #999;
            cursor: not-allowed;
            border: 1px solid #e9ecef;
        }
        .pagination-info {
            text-align: center;
            margin-bottom: 20px;
            color: #666;
            font-size: 0.9rem;
        }
        @media (max-width: 700px) {
            .users-header-row { padding: 10px 2vw; }
            .users-title { font-size: var(--font-size-base); }
            .users-add-btn { padding: 6px 10px; font-size: var(--font-size-sm); }
            .users-table th, .users-table td { padding: 7px 4px; font-size: var(--font-size-sm); }
            .users-form input, .users-form select { font-size: 0.95rem; }
            .users-search-bar-wrapper { justify-content: center; flex-direction: column; gap: 10px; }
            .users-search-bar { max-width: 100%; }
            .users-status-filter { width: 100%; }
            .users-clear-btn { width: 100%; justify-content: center; margin-top: 4px; }
            .pagination { gap: 4px; }
            .pagination a, .pagination span { padding: 6px 8px; min-width: 35px; font-size: 0.9rem; }
        }
    </style>
    <script>
        function filterUsers() {
            var input = document.getElementById('users-search-input');
            var filter = input.value.toLowerCase();
            var userType = document.getElementById('users-status-filter').value;
            var table = document.querySelector('.users-table');
            var trs = table.querySelectorAll('tbody tr');
            
            // Redirect to filtered view
            var currentUrl = new URL(window.location);
            currentUrl.searchParams.set('type', userType);
            if (filter) {
                currentUrl.searchParams.set('search', filter);
            } else {
                currentUrl.searchParams.delete('search');
            }
            window.location.href = currentUrl.toString();
        }
        
        function clearUserFilters() {
            window.location.href = window.location.pathname;
        }
    </script>
</head>
<body>
<?php include __DIR__ . '/../components/sidebar.php'; ?>
<div class="main-content">
    <div class="users-header-row">
        <div class="users-title"><i class="bi bi-people" style="margin-right:8px;"></i>All Users</div>
        <div style="display:flex;gap:10px;">
            <a href="add.php" class="users-add-btn"><i class="bi bi-plus-lg" style="margin-right:6px;"></i>Add Shop User</a>
            <a href="export.php?type=<?php echo $userType; ?>" class="users-add-btn" style="background:linear-gradient(90deg,#5eead4 0%,#7b61ff 100%);color:#232526;"><i class="bi bi-download" style="margin-right:6px;"></i>Export</a>
        </div>
    </div>
    
    <div class="users-nav" style="max-width:1100px;margin:0 auto;">
        <a href="registered.php">Registered Users</a>
    </div>
    <div class="users-search-bar-wrapper" style="max-width:1100px;margin:0 auto 18px auto;">
        <select id="users-status-filter" class="users-status-filter" onchange="filterUsers()">
            <option value="all" <?php echo $userType === 'all' ? 'selected' : ''; ?>>All Users</option>
            <option value="main" <?php echo $userType === 'main' ? 'selected' : ''; ?>>Main System Users</option>
            <option value="shop" <?php echo $userType === 'shop' ? 'selected' : ''; ?>>Shop Users</option>
        </select>
        <div class="users-search-bar">
            <i class="bi bi-search"></i>
            <input id="users-search-input" type="text" placeholder="Search users..." value="<?php echo htmlspecialchars($searchTerm); ?>" onkeyup="if(event.keyCode===13) filterUsers()" />
        </div>
        <div style="flex:1;"></div>
        <button class="users-clear-btn" onclick="clearUserFilters()" title="Clear filters"><i class="bi bi-x-circle"></i>Clear</button>
    </div>
    <?php if ($success): ?>
        <div class="users-message success" style="max-width:420px;margin:24px auto 0 auto;"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="users-message error" style="max-width:420px;margin:24px auto 0 auto;"><?php echo $error; ?></div>
    <?php endif; ?>
    <div class="users-table-wrapper">
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th class="center">Contact</th>
                    <th class="center">Unique ID</th>
                    <th class="center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>#<?php echo $user['CustomerID']; ?></td>
                    <td><?php echo htmlspecialchars($user['Name']); ?></td>
                    <td><?php echo htmlspecialchars($user['Email']); ?></td>
                    <td class="center"><?php echo htmlspecialchars($user['Contact']); ?></td>
                    <td class="center" style="font-size:0.9rem; color:#666;"><?php echo htmlspecialchars($user['CustomerUniqueID']); ?></td>
                    <td class="center">
                        <div class="user-actions">
                            <a href="view.php?id=<?php echo $user['CustomerID']; ?>" title="View"><i class="bi bi-eye"></i></a>
                            <a href="edit.php?id=<?php echo $user['CustomerID']; ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                            <a href="delete.php?id=<?php echo $user['CustomerID']; ?>" title="Delete" onclick="return confirm('Are you sure you want to delete this shop user?');"><i class="bi bi-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($users)): ?>
                <tr><td colspan="6" style="text-align:center; color:#aaa; padding:12px;">
                    <?php if (!empty($searchTerm)): ?>
                        No users found matching "<?php echo htmlspecialchars($searchTerm); ?>"
                    <?php elseif ($userType === 'main'): ?>
                        No main system users found
                    <?php elseif ($userType === 'shop'): ?>
                        No shop users found
                    <?php else: ?>
                        No users found
                    <?php endif; ?>
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if ($totalPages > 1): ?>
        <div class="pagination-info">
            Showing <?php echo (($currentPage - 1) * $perPage) + 1; ?> to <?php echo min($currentPage * $perPage, $totalUsers); ?> of <?php echo $totalUsers; ?> users
            <?php if ($userType !== 'all'): ?>
                (<?php echo ucfirst($userType); ?> users only)
            <?php endif; ?>
        </div>
        
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="?page=1&type=<?php echo $userType; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" title="First Page">&laquo;</a>
                <a href="?page=<?php echo $currentPage - 1; ?>&type=<?php echo $userType; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" title="Previous Page">&lsaquo;</a>
            <?php else: ?>
                <span class="disabled">&laquo;</span>
                <span class="disabled">&lsaquo;</span>
            <?php endif; ?>
            
            <?php
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
            
            if ($startPage > 1): ?>
                <a href="?page=1&type=<?php echo $userType; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>">1</a>
                <?php if ($startPage > 2): ?>
                    <span class="disabled">...</span>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <?php if ($i == $currentPage): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&type=<?php echo $userType; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($endPage < $totalPages): ?>
                <?php if ($endPage < $totalPages - 1): ?>
                    <span class="disabled">...</span>
                <?php endif; ?>
                <a href="?page=<?php echo $totalPages; ?>&type=<?php echo $userType; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>"><?php echo $totalPages; ?></a>
            <?php endif; ?>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?php echo $currentPage + 1; ?>&type=<?php echo $userType; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" title="Next Page">&rsaquo;</a>
                <a href="?page=<?php echo $totalPages; ?>&type=<?php echo $userType; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" title="Last Page">&raquo;</a>
            <?php else: ?>
                <span class="disabled">&rsaquo;</span>
                <span class="disabled">&raquo;</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html> 