<?php
session_start();
if (!isset($_SESSION['shop_admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/config.php';

$db = new Database();
$conn = $db->getConnection();
$categories = $conn->query('SELECT * FROM categories ORDER BY category_id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Categories</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .category-header-row {
            max-width: 1100px;
            margin: 40px auto 18px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .category-title {
            font-size: var(--font-size-lg);
            font-weight: 700;
            color: var(--accent-dark);
            letter-spacing: 1px;
        }
        .category-add-btn {
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
        .category-add-btn:hover {
            background: linear-gradient(90deg,var(--accent-light) 0%,var(--accent) 100%);
            color: var(--accent-dark);
        }
        .category-table-wrapper {
            max-width: 1100px;
            margin: 0 auto;
        }
        .category-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
            font-size: var(--font-size-base);
            background: transparent;
            font-family: var(--font-main);
        }
        .category-table th, .category-table td {
            padding: 10px 12px;
            text-align: left;
        }
        .category-table th.center, .category-table td.center {
            text-align: center !important;
        }
        .category-table th {
            background: #f7f8fa;
            color: var(--accent);
            font-weight: 700;
            border-bottom: 2px solid #ececec;
            font-size: var(--font-size-base);
        }
        .category-table tr {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: 0 2px 8px rgba(31,38,135,0.04);
            transition: box-shadow 0.18s, background 0.18s;
        }
        .category-table tr:hover {
            background: #f7f8fa;
            box-shadow: 0 4px 16px rgba(123,97,255,0.10);
        }
        .category-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }
        .category-actions a {
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
        .category-actions a[title="Edit"] { color: #ff9800; }
        .category-actions a[title="Delete"] { color: #d32f2f; }
        .category-actions a:hover { background: #eceef3; }
        @media (max-width: 700px) {
            .category-header-row { padding: 10px 2vw; }
            .category-title { font-size: var(--font-size-base); }
            .category-add-btn { padding: 6px 10px; font-size: var(--font-size-sm); }
            .category-table th, .category-table td { padding: 7px 4px; font-size: var(--font-size-sm); }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/sidebar.php'; ?>
<div class="main-content">
    <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
        <div id="category-error-msg" style="max-width:420px;margin:24px auto 0 auto;display:flex;align-items:center;justify-content:center;gap:10px;background:#fff0f0;color:#d32f2f;padding:14px 0 14px 0;border-radius:10px;text-align:center;font-size:1.08rem;box-shadow:0 2px 12px rgba(211,47,47,0.08);font-weight:600;transition:opacity 0.5s;">
            <i class="bi bi-exclamation-circle-fill" style="font-size:1.3em;"></i> <?php echo isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'An error occurred.'; ?>
        </div>
        <script>
            setTimeout(function() {
                var msg = document.getElementById('category-error-msg');
                if(msg) { msg.style.opacity = '0'; setTimeout(function(){ msg.style.display = 'none'; }, 500); }
            }, 3500);
        </script>
    <?php endif; ?>
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div id="category-success-msg" style="max-width:420px;margin:24px auto 0 auto;display:flex;align-items:center;justify-content:center;gap:10px;background:#e6f9ed;color:#388e3c;padding:14px 0 14px 0;border-radius:10px;text-align:center;font-size:1.08rem;box-shadow:0 2px 12px rgba(56,142,60,0.08);font-weight:600;transition:opacity 0.5s;">
            <i class="bi bi-check-circle-fill" style="font-size:1.3em;"></i> Category added successfully!
        </div>
        <script>
            setTimeout(function() {
                var msg = document.getElementById('category-success-msg');
                if(msg) { msg.style.opacity = '0'; setTimeout(function(){ msg.style.display = 'none'; }, 500); }
            }, 3000);
        </script>
    <?php endif; ?>
    <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
        <div id="category-updated-msg" style="max-width:420px;margin:24px auto 0 auto;display:flex;align-items:center;justify-content:center;gap:10px;background:#e6f9ed;color:#388e3c;padding:14px 0 14px 0;border-radius:10px;text-align:center;font-size:1.08rem;box-shadow:0 2px 12px rgba(56,142,60,0.08);font-weight:600;transition:opacity 0.5s;">
            <i class="bi bi-check-circle-fill" style="font-size:1.3em;"></i> Category updated successfully!
        </div>
        <script>
            setTimeout(function() {
                var msg = document.getElementById('category-updated-msg');
                if(msg) { msg.style.opacity = '0'; setTimeout(function(){ msg.style.display = 'none'; }, 500); }
            }, 3000);
        </script>
    <?php endif; ?>
    <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
        <div id="category-deleted-msg" style="max-width:420px;margin:24px auto 0 auto;display:flex;align-items:center;justify-content:center;gap:10px;background:#fff0f0;color:#d32f2f;padding:14px 0 14px 0;border-radius:10px;text-align:center;font-size:1.08rem;box-shadow:0 2px 12px rgba(211,47,47,0.08);font-weight:600;transition:opacity 0.5s;">
            <i class="bi bi-trash-fill" style="font-size:1.3em;"></i> Category deleted successfully!
        </div>
        <script>
            setTimeout(function() {
                var msg = document.getElementById('category-deleted-msg');
                if(msg) { msg.style.opacity = '0'; setTimeout(function(){ msg.style.display = 'none'; }, 500); }
            }, 3000);
        </script>
    <?php endif; ?>
    <div class="category-header-row">
        <div class="category-title"><i class="bi bi-tag" style="margin-right:8px;"></i>Categories</div>
        <a href="add.php" class="category-add-btn"><i class="bi bi-plus-lg" style="margin-right:6px;"></i>Add Category</a>
    </div>
    <div class="category-table-wrapper">
        <table class="category-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th class="center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?php echo htmlspecialchars($cat['name']); ?></td>
                    <td><?php echo htmlspecialchars($cat['description']); ?></td>
                    <td class="center">
                        <div class="category-actions">
                            <a href="edit.php?id=<?php echo $cat['category_id']; ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                            <a href="delete.php?id=<?php echo $cat['category_id']; ?>" title="Delete" onclick="return confirm('Are you sure you want to delete this category?');"><i class="bi bi-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                <tr><td colspan="3" style="text-align:center; color:#aaa; padding:12px;">No categories found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html> 