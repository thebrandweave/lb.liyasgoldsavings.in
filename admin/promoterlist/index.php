<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$menuPath = '../'; // This will make $menuPath.'../config/JWT.php' resolve to '../../config/JWT.php'
$currentPage = "promoterlist";

require_once '../../config/config.php';

// Get search parameter and pagination
$searchTerm = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10; // Promoters per page

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Build search condition
    $searchCondition = '';
    $params = [];
    if (!empty($searchTerm)) {
        $searchCondition = 'WHERE p.Name LIKE ? OR p.Email LIKE ? OR p.Contact LIKE ? OR p.PromoterUniqueID LIKE ?';
        $searchParam = '%' . $searchTerm . '%';
        $params = [$searchParam, $searchParam, $searchParam, $searchParam];
    }
    
    // Get total count
    $countQuery = 'SELECT COUNT(*) FROM Promoters p ' . $searchCondition;
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetchColumn();
    
    // Calculate pagination
    $offset = ($page - 1) * $perPage;
    $totalPages = ceil($totalRecords / $perPage);
    
    // Get promoters with customer count and child promoter count
    $query = 'SELECT 
                p.PromoterID,
                p.PromoterUniqueID,
                p.Name,
                p.Contact,
                p.Email,
                p.Address,
                p.TeamName,
                p.Status,
                p.CreatedAt,
                COUNT(CASE WHEN c.Status = "Active" THEN c.CustomerID END) as CustomerCount,
                COUNT(CASE WHEN cp.Status = "Active" THEN cp.PromoterID END) as ChildPromoterCount
              FROM Promoters p
              LEFT JOIN Customers c ON p.PromoterUniqueID = c.PromoterID
              LEFT JOIN Promoters cp ON p.PromoterUniqueID = cp.ParentPromoterID
              ' . $searchCondition . '
              GROUP BY p.PromoterID, p.PromoterUniqueID, p.Name, p.Contact, p.Email, p.Address, p.TeamName, p.Status, p.CreatedAt
              ORDER BY p.CreatedAt DESC
              LIMIT ' . (int)$perPage . ' OFFSET ' . (int)$offset;
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $promoters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $promoters = [];
    $totalRecords = 0;
    $totalPages = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Promoter List - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .promoters-card {
            max-width: 1200px;
            margin: 40px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(31,38,135,0.06);
            padding: 36px 28px 32px 28px;
        }
        .promoters-header-row {
            max-width: 1200px;
            margin: 40px auto 18px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .promoters-title {
            font-size: var(--font-size-lg);
            font-weight: 700;
            color: var(--accent-dark);
            letter-spacing: 1px;
        }
        .promoters-add-btn {
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
        .promoters-add-btn:hover {
            background: linear-gradient(90deg,var(--accent-light) 0%,var(--accent) 100%);
            color: var(--accent-dark);
        }
        .promoters-search-bar-wrapper {
            width: 100%;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
        }
        .promoters-search-bar {
            width: 100%;
            max-width: 800px;
            position: relative;
            display: flex;
            align-items: center;
            padding: 0 0 0 34px;
            height: 40px;
        }
        .promoters-search-bar input {
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
        .promoters-search-bar input:focus {
            box-shadow: 0 0 0 2px #7b61ff;
        }
        .promoters-search-bar .bi-search {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #7b61ff;
            font-size: 1.2em;
            pointer-events: none;
        }
        .promoters-clear-btn {
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
        .promoters-clear-btn:hover, .promoters-clear-btn:focus {
            background: rgba(123,97,255,0.08);
            color: #5f2c82;
            border-color: #5f2c82;
            box-shadow: none;
            outline: none;
        }
        .promoters-table-wrapper {
            max-width: 1200px;
            margin: 0 auto;
        }
        .promoters-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
            font-size: var(--font-size-base);
            background: transparent;
            font-family: var(--font-main);
        }
        .promoters-table th, .promoters-table td {
            padding: 12px 16px;
            text-align: left;
        }
        .promoters-table th.center, .promoters-table td.center {
            text-align: center !important;
        }
        .promoters-table th {
            background: #f7f8fa;
            color: var(--accent);
            font-weight: 700;
            border-bottom: 2px solid #ececec;
            font-size: var(--font-size-base);
        }
        .promoters-table tr {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: 0 2px 8px rgba(31,38,135,0.04);
            transition: box-shadow 0.18s, background 0.18s;
        }
        .promoters-table tr:hover {
            background: #f7f8fa;
            box-shadow: 0 4px 16px rgba(123,97,255,0.10);
        }
        .promoters-table tr:nth-child(even) {
            background: #fafbfc;
        }
        .promoters-table tr {
            border-bottom: 1px solid #f2e9e1;
        }
        .promoter-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
            height: 100%;
        }
        .promoter-actions a {
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
        .promoter-actions a[title="View"] { color: #7b61ff; }
        .promoter-actions a[title="Edit"] { color: #ff9800; }
        .promoter-actions a[title="Delete"] { color: #d32f2f; }
        .promoter-actions a:hover { background: #eceef3; }
        .promoters-table th.center, .promoters-table td.center {
            text-align: center !important;
            vertical-align: middle !important;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-active {
            background: #e6f9ed;
            color: #388e3c;
        }
        .status-inactive {
            background: #fff0f0;
            color: #d32f2f;
        }
        .customer-count {
            background: #f0f4ff;
            color: #7b61ff;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .promoter-count {
            background: #fff3e0;
            color: #f57c00;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
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
            margin-bottom: 20px 0;
            padding: 10px 0;
            color: #666;
            font-size: 0.9rem;
        }
        @media (max-width: 700px) {
            .promoters-header-row { padding: 10px 2vw; }
            .promoters-title { font-size: var(--font-size-base); }
            .promoters-add-btn { padding: 6px 10px; font-size: var(--font-size-sm); }
            .promoters-table th, .promoters-table td { padding: 7px 4px; font-size: var(--font-size-sm); }
            .promoters-search-bar-wrapper { justify-content: center; flex-direction: column; gap: 10px; }
            .promoters-search-bar { max-width: 100%; }
            .promoters-clear-btn { width: 100%; justify-content: center; margin-top: 4px; }
            .pagination { gap: 4px; }
            .pagination a, .pagination span { padding: 6px 8px; min-width: 35px; font-size: 0.9rem; }
        }
    </style>
    <script>
        function searchPromoters() {
            var input = document.getElementById('promoters-search-input');
            var filter = input.value;
            
            // Redirect to filtered view
            var currentUrl = new URL(window.location);
            if (filter) {
                currentUrl.searchParams.set('search', filter);
            } else {
                currentUrl.searchParams.delete('search');
            }
            window.location.href = currentUrl.toString();
        }
        
        function clearPromoterFilters() {
            window.location.href = window.location.pathname;
        }
    </script>
</head>
<body>
<div class="main-content">
    <div class="promoters-header-row">
        <div class="promoters-title"><i class="bi bi-people-fill" style="margin-right:8px;"></i>Promoter List</div>
        <div style="display:flex;gap:10px;">
            <a href="../promoter/add/" class="promoters-add-btn"><i class="bi bi-plus-lg" style="margin-right:6px;"></i>Add Promoter</a>
            <a href="export.php<?php echo !empty($searchTerm) ? '?search=' . urlencode($searchTerm) : ''; ?>" class="promoters-add-btn" style="background:linear-gradient(90deg,#5eead4 0%,#7b61ff 100%);color:#232526;"><i class="bi bi-download" style="margin-right:6px;"></i>Export</a>
        </div>
    </div>
    
    <div class="promoters-search-bar-wrapper" style="max-width:1200px;margin:0 auto 18px auto;">
        <div class="promoters-search-bar">
            <i class="bi bi-search"></i>
            <input id="promoters-search-input" type="text" placeholder="Search promoters..." value="<?php echo htmlspecialchars($searchTerm); ?>" onkeyup="if(event.keyCode===13) searchPromoters()" />
        </div>
        <div style="flex:1;"></div>
        <button class="promoters-clear-btn" onclick="clearPromoterFilters()" title="Clear filters"><i class="bi bi-x-circle"></i>Clear</button>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="users-message error" style="max-width:420px;margin:24px auto 0 auto;"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="promoters-table-wrapper">
        <table class="promoters-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th class="center">Contact</th>
                    <th class="center">Team</th>
                    <th class="center">Customers</th>
                    <th class="center">Child Promoters</th>
                    <th class="center">Status</th>
                    <th class="center">Joined</th>
                    <th class="center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($promoters as $promoter): ?>
                <tr>
                    <td>#<?php echo $promoter['PromoterID']; ?></td>
                    <td>
                        <div style="font-weight:600;color:#232526;"><?php echo htmlspecialchars($promoter['Name']); ?></div>
                        <div style="font-size:0.85rem;color:#666;"><?php echo htmlspecialchars($promoter['PromoterUniqueID']); ?></div>
                    </td>
                    <td><?php echo htmlspecialchars($promoter['Email']); ?></td>
                    <td class="center"><?php echo htmlspecialchars($promoter['Contact']); ?></td>
                    <td class="center"><?php echo htmlspecialchars($promoter['TeamName'] ?: 'N/A'); ?></td>
                    <td class="center">
                        <span class="customer-count"><?php echo $promoter['CustomerCount']; ?> customers</span>
                    </td>
                    <td class="center">
                        <span class="promoter-count"><?php echo $promoter['ChildPromoterCount']; ?> promoters</span>
                    </td>
                    <td class="center">
                        <span class="status-badge <?php echo strtolower($promoter['Status']) === 'active' ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $promoter['Status']; ?>
                        </span>
                    </td>
                    <td class="center" style="font-size:0.9rem; color:#666;">
                        <?php echo date('d M Y', strtotime($promoter['CreatedAt'])); ?>
                    </td>
                    <td class="center">
                        <div class="promoter-actions">
                            <a href="../promoter/view.php?id=<?php echo $promoter['PromoterID']; ?>" title="View"><i class="bi bi-eye"></i></a>
                            <a href="../promoter/edit.php?id=<?php echo $promoter['PromoterID']; ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                            <a href="../promoter/delete.php?id=<?php echo $promoter['PromoterID']; ?>" title="Delete" onclick="return confirm('Are you sure you want to delete this promoter?');"><i class="bi bi-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($promoters)): ?>
                <tr><td colspan="10" style="text-align:center; color:#aaa; padding:12px;">
                    <?php if (!empty($searchTerm)): ?>
                        No promoters found matching "<?php echo htmlspecialchars($searchTerm); ?>"
                    <?php else: ?>
                        No promoters found
                    <?php endif; ?>
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if ($totalPages > 1): ?>
        <div class="pagination-info">
            Showing <?php echo (($page - 1) * $perPage) + 1; ?> to <?php echo min($page * $perPage, $totalRecords); ?> of <?php echo $totalRecords; ?> promoters
        </div>
        
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=1<?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" title="First Page">&laquo;</a>
                <a href="?page=<?php echo $page - 1; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" title="Previous Page">&lsaquo;</a>
            <?php else: ?>
                <span class="disabled">&laquo;</span>
                <span class="disabled">&lsaquo;</span>
            <?php endif; ?>
            
            <?php
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            
            if ($startPage > 1): ?>
                <a href="?page=1<?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>">1</a>
                <?php if ($startPage > 2): ?>
                    <span class="disabled">...</span>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($endPage < $totalPages): ?>
                <?php if ($endPage < $totalPages - 1): ?>
                    <span class="disabled">...</span>
                <?php endif; ?>
                <a href="?page=<?php echo $totalPages; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>"><?php echo $totalPages; ?></a>
            <?php endif; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" title="Next Page">&rsaquo;</a>
                <a href="?page=<?php echo $totalPages; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" title="Last Page">&raquo;</a>
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