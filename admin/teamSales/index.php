<?php
session_start();

$menuPath = "../";
$currentPage = "teamSales";

// Database connection
require_once("../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

// Get selected date range from GET or default to today
$fromDate = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-d');
$toDate = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

// Get selected promoter filter from GET
$selectedPromoter = isset($_GET['promoter']) ? $_GET['promoter'] : '';

// Get all active promoters for filter dropdown
$promoterQuery = "SELECT PromoterUniqueID, Name FROM Promoters WHERE Status = 'Active' ORDER BY Name";
$stmt = $conn->prepare($promoterQuery);
$stmt->execute();
$allPromoters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to get team statistics
function getTeamStats($conn, $fromDate = null, $toDate = null, $promoterId = null)
{
    if (!$fromDate) {
        $fromDate = date('Y-m-d');
    }
    if (!$toDate) {
        $toDate = date('Y-m-d');
    }

    // Get all unique team names (filtered by promoter if selected)
    $teamQuery = "SELECT DISTINCT TeamName FROM Customers WHERE TeamName IS NOT NULL";
    $teamParams = [];

    if ($promoterId) {
        $teamQuery .= " AND PromoterID = :promoterId";
        $teamParams[':promoterId'] = $promoterId;
    }

    $teamStmt = $conn->prepare($teamQuery);
    $teamStmt->execute($teamParams);
    $teams = $teamStmt->fetchAll(PDO::FETCH_COLUMN);

    $stats = [];
    foreach ($teams as $teamName) {
        // Build customer filter condition based on promoter
        $customerFilter = "TeamName = :teamName";
        $params = [':teamName' => $teamName, ':fromDate' => $fromDate, ':toDate' => $toDate];

        if ($promoterId) {
            $customerFilter .= " AND PromoterID = :promoterId";
            $params[':promoterId'] = $promoterId;
        }

        // New customers in date range
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Customers WHERE $customerFilter AND DATE(CreatedAt) BETWEEN :fromDate AND :toDate");
        $stmt->execute($params);
        $total_customers = $stmt->fetchColumn();

        // New promoters in date range
        $promoterParams = [':teamName' => $teamName, ':fromDate' => $fromDate, ':toDate' => $toDate];
        $promoterQuery = "SELECT COUNT(*) FROM Promoters WHERE TeamName = :teamName AND DATE(CreatedAt) BETWEEN :fromDate AND :toDate";
        if ($promoterId) {
            $promoterQuery .= " AND PromoterUniqueID = :promoterId";
            $promoterParams[':promoterId'] = $promoterId;
        }
        $stmt = $conn->prepare($promoterQuery);
        $stmt->execute($promoterParams);
        $total_promoters = $stmt->fetchColumn();

        // Payments in date range (for customers in this team and promoter if filtered)
        $paymentQuery = "
            SELECT 
                SUM(CASE WHEN Status = 'Verified' THEN Amount ELSE 0 END) as verified_amount,
                SUM(CASE WHEN Status = 'Pending' THEN Amount ELSE 0 END) as pending_amount,
                COUNT(CASE WHEN Status = 'Verified' THEN 1 END) as verified_payments,
                COUNT(CASE WHEN Status = 'Pending' THEN 1 END) as pending_payments,
                COUNT(*) as total_payments
            FROM Payments
            WHERE CustomerID IN (SELECT CustomerID FROM Customers WHERE $customerFilter)
            AND DATE(SubmittedAt) BETWEEN :fromDate AND :toDate
        ";
        $stmt = $conn->prepare($paymentQuery);
        $stmt->execute($params);
        $paymentStats = $stmt->fetch(PDO::FETCH_ASSOC);

        $stats[] = [
            'TeamName' => $teamName,
            'total_customers' => $total_customers,
            'total_promoters' => $total_promoters,
            'total_payments' => $paymentStats['total_payments'] ?? 0,
            'verified_amount' => $paymentStats['verified_amount'] ?? 0,
            'pending_amount' => $paymentStats['pending_amount'] ?? 0,
            'verified_payments' => $paymentStats['verified_payments'] ?? 0,
            'pending_payments' => $paymentStats['pending_payments'] ?? 0,
        ];
    }

    // Sort by verified_amount descending
    usort($stats, function ($a, $b) {
        return ($b['verified_amount'] ?? 0) <=> ($a['verified_amount'] ?? 0);
    });

    return $stats;
}

// Function to get team members (customers only)
function getTeamMembers($conn, $teamName, $fromDate = null, $toDate = null, $promoterId = null)
{
    if (!$fromDate) {
        $fromDate = date('Y-m-d');
    }
    if (!$toDate) {
        $toDate = date('Y-m-d');
    }

    // Build WHERE clause with optional promoter filter
    $whereClause = "WHERE c.TeamName = :teamName";
    $params = [':teamName' => $teamName, ':fromDate' => $fromDate, ':toDate' => $toDate];

    if ($promoterId) {
        $whereClause .= " AND c.PromoterID = :promoterId";
        $params[':promoterId'] = $promoterId;
    }

    $whereClause .= " AND (DATE(c.CreatedAt) BETWEEN :fromDate AND :toDate OR DATE(pay.SubmittedAt) BETWEEN :fromDate AND :toDate)";

    $query = "SELECT 
        c.CustomerUniqueID as unique_id,
        c.Name,
        c.Contact,
        c.Email,
        c.PromoterID as ParentPromoterID,
        CONCAT(p.PromoterUniqueID, ' - ', p.Name) as ParentName,
        COUNT(DISTINCT CASE WHEN DATE(pay.SubmittedAt) BETWEEN :fromDate AND :toDate THEN pay.PaymentID END) as total_payments,
        SUM(CASE WHEN DATE(pay.SubmittedAt) BETWEEN :fromDate AND :toDate AND pay.Status = 'Verified' THEN pay.Amount ELSE 0 END) as total_amount
        FROM Customers c
        LEFT JOIN Promoters p ON c.PromoterID = p.PromoterUniqueID
        LEFT JOIN Payments pay ON pay.CustomerID = c.CustomerID
        $whereClause
        GROUP BY c.CustomerID, c.CustomerUniqueID, c.Name, c.Contact, c.Email, c.PromoterID, p.Name, p.PromoterUniqueID";

    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get team statistics with promoter filter and date range
$teamStats = getTeamStats($conn, $fromDate, $toDate, $selectedPromoter);

// Include header and sidebar
include("../components/sidebar.php");
include("../components/topbar.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Sales Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-title {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }

        .stat-subtitle {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }

        .section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .amount {
            font-weight: 600;
            color: #333;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }

        .team-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .team-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .team-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .team-id {
            color: #666;
            font-size: 14px;
        }

        .team-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .team-stat {
            text-align: center;
        }

        .team-stat-value {
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }

        .team-stat-label {
            font-size: 12px;
            color: #666;
        }

        .member-type {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .type-promoter {
            background: #e3f2fd;
            color: #1976d2;
        }

        .type-customer {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #1976d2;
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background: #1565c0;
        }

        .btn i {
            margin-right: 8px;
        }

        /* Searchable Dropdown Styles */
        .searchable-dropdown {
            position: relative;
            display: inline-block;
            min-width: 200px;
        }

        .searchable-dropdown .select-wrapper {
            position: relative;
        }

        .searchable-dropdown input[type="text"] {
            width: 100%;
            padding: 6px 30px 6px 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .searchable-dropdown input[type="text"]:focus {
            outline: none;
            border-color: #1976d2;
            box-shadow: 0 0 0 2px rgba(25, 118, 210, 0.1);
        }

        .searchable-dropdown .dropdown-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #666;
        }

        .searchable-dropdown .dropdown-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ccc;
            border-top: none;
            border-radius: 0 0 5px 5px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: -1px;
        }

        .searchable-dropdown .dropdown-list.show {
            display: block;
        }

        .searchable-dropdown .dropdown-item {
            padding: 10px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
        }

        .searchable-dropdown .dropdown-item:last-child {
            border-bottom: none;
        }

        .searchable-dropdown .dropdown-item:hover {
            background-color: #f5f5f5;
        }

        .searchable-dropdown .dropdown-item.selected {
            background-color: #e3f2fd;
            color: #1976d2;
            font-weight: 500;
        }

        .searchable-dropdown .dropdown-item.hidden {
            display: none;
        }

        .searchable-dropdown .no-results {
            padding: 10px 12px;
            color: #999;
            text-align: center;
            font-style: italic;
            display: none;
        }

        .searchable-dropdown .no-results.show {
            display: block;
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Team Sales Overview</h1>
                <div class="date-display" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <form method="GET" id="filterForm" style="display:inline-flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <label for="promoter-search" style="font-weight: 500; color: #333;">Filter by Promoter:</label>
                        <div class="searchable-dropdown" id="promoterDropdown">
                            <input type="hidden" name="promoter" id="promoter" value="<?php echo htmlspecialchars($selectedPromoter); ?>">
                            <div class="select-wrapper">
                                <input type="text" id="promoter-search" placeholder="Search promoters..." autocomplete="off"
                                    value="<?php
                                            if ($selectedPromoter) {
                                                foreach ($allPromoters as $promoter) {
                                                    if ($promoter['PromoterUniqueID'] === $selectedPromoter) {
                                                        echo htmlspecialchars($promoter['Name'] . ' (' . $promoter['PromoterUniqueID'] . ')');
                                                        break;
                                                    }
                                                }
                                            }
                                            ?>">
                                <i class="fas fa-chevron-down dropdown-icon"></i>
                            </div>
                            <div class="dropdown-list" id="promoterList">
                                <div class="dropdown-item" data-value="" data-text="All Promoters">All Promoters</div>
                                <?php foreach ($allPromoters as $promoter): ?>
                                    <div class="dropdown-item <?php echo ($selectedPromoter === $promoter['PromoterUniqueID']) ? 'selected' : ''; ?>"
                                        data-value="<?php echo htmlspecialchars($promoter['PromoterUniqueID']); ?>"
                                        data-text="<?php echo htmlspecialchars($promoter['Name'] . ' (' . $promoter['PromoterUniqueID'] . ')'); ?>">
                                        <?php echo htmlspecialchars($promoter['Name']); ?> (<?php echo htmlspecialchars($promoter['PromoterUniqueID']); ?>)
                                    </div>
                                <?php endforeach; ?>
                                <div class="no-results">No promoters found</div>
                            </div>
                        </div>
                        <label for="from_date" style="font-weight: 500; color: #333;">From Date:</label>
                        <input type="date" name="from_date" id="from_date" value="<?php echo htmlspecialchars($fromDate); ?>" style="padding:6px 10px; border-radius:5px; border:1px solid #ccc;">
                        <label for="to_date" style="font-weight: 500; color: #333;">To Date:</label>
                        <input type="date" name="to_date" id="to_date" value="<?php echo htmlspecialchars($toDate); ?>" style="padding:6px 10px; border-radius:5px; border:1px solid #ccc;">
                        <button type="submit" class="btn btn-primary" style="padding: 6px 12px; font-size: 14px;">
                            <i class="fas fa-filter"></i> Apply
                        </button>
                    </form>
                    <span><i class="fas fa-calendar-alt"></i>
                        <?php
                        if ($fromDate === $toDate) {
                            echo date('F d, Y', strtotime($fromDate));
                        } else {
                            echo date('M d, Y', strtotime($fromDate)) . ' - ' . date('M d, Y', strtotime($toDate));
                        }
                        ?>
                    </span>
                    <?php if ($selectedPromoter): ?>
                        <?php
                        $selectedPromoterName = '';
                        foreach ($allPromoters as $promoter) {
                            if ($promoter['PromoterUniqueID'] === $selectedPromoter) {
                                $selectedPromoterName = $promoter['Name'];
                                break;
                            }
                        }
                        ?>
                        <span style="background: #e3f2fd; color: #1976d2; padding: 6px 12px; border-radius: 5px; font-size: 14px;">
                            <i class="fas fa-filter"></i> Filtered by: <?php echo htmlspecialchars($selectedPromoterName); ?>
                        </span>
                        <a href="?from_date=<?php echo urlencode($fromDate); ?>&to_date=<?php echo urlencode($toDate); ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 14px;">
                            <i class="fas fa-times"></i> Clear Promoter Filter
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Team Statistics -->
            <?php foreach ($teamStats as $team): ?>
                <div class="team-card">
                    <div class="team-header">
                        <div>
                            <div class="team-name"><?php echo htmlspecialchars($team['TeamName']); ?></div>
                        </div>
                        <div>
                            <a href="export.php?team=<?php echo urlencode($team['TeamName']); ?>&from_date=<?php echo urlencode($fromDate); ?>&to_date=<?php echo urlencode($toDate); ?><?php echo $selectedPromoter ? '&promoter=' . urlencode($selectedPromoter) : ''; ?>" class="btn btn-primary">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </a>
                        </div>
                    </div>

                    <div class="team-stats">
                        <div class="team-stat">
                            <div class="team-stat-value"><?php echo number_format($team['total_customers']); ?></div>
                            <div class="team-stat-label">New Customers (Period)</div>
                        </div>
                        <div class="team-stat">
                            <div class="team-stat-value"><?php echo number_format($team['total_promoters']); ?></div>
                            <div class="team-stat-label">New Promoters (Period)</div>
                        </div>
                        <div class="team-stat">
                            <div class="team-stat-value"><?php echo number_format($team['total_payments']); ?></div>
                            <div class="team-stat-label">Payments (Period)</div>
                        </div>
                        <div class="team-stat">
                            <div class="team-stat-value">₹<?php echo number_format($team['verified_amount']); ?></div>
                            <div class="team-stat-label">Verified Amount (Period)</div>
                        </div>
                        <div class="team-stat">
                            <div class="team-stat-value">₹<?php echo number_format($team['pending_amount']); ?></div>
                            <div class="team-stat-label">Pending Amount (Period)</div>
                        </div>
                    </div>

                    <!-- Team Members Table -->
                    <div class="section">
                        <h3 class="section-title">Team Activity (<?php echo date('M d', strtotime($fromDate)) . ' - ' . date('M d, Y', strtotime($toDate)); ?>)</h3>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Contact</th>
                                        <th>Email</th>
                                        <th>Parent</th>
                                        <th>Payments (Period)</th>
                                        <th>Amount (Period)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $teamMembers = getTeamMembers($conn, $team['TeamName'], $fromDate, $toDate, $selectedPromoter);
                                    if (!empty($teamMembers)):
                                        foreach ($teamMembers as $member):
                                    ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($member['unique_id']); ?></td>
                                                <td><?php echo htmlspecialchars($member['Name']); ?></td>
                                                <td><?php echo htmlspecialchars($member['Contact']); ?></td>
                                                <td><?php echo htmlspecialchars($member['Email']); ?></td>
                                                <td><?php echo htmlspecialchars($member['ParentName']); ?></td>
                                                <td><?php echo number_format($member['total_payments']); ?></td>
                                                <td class="amount">₹<?php echo number_format($member['total_amount']); ?></td>
                                            </tr>
                                        <?php
                                        endforeach;
                                    else:
                                        ?>
                                        <tr>
                                            <td colspan="7" class="no-data">No activity found for this team in the selected period</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($teamStats)): ?>
                <div class="no-data">No team data available for the selected period</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Searchable Dropdown Functionality
        (function() {
            const dropdown = document.getElementById('promoterDropdown');
            const searchInput = document.getElementById('promoter-search');
            const hiddenInput = document.getElementById('promoter');
            const dropdownList = document.getElementById('promoterList');
            const dropdownItems = dropdownList.querySelectorAll('.dropdown-item:not(.no-results)');
            const noResults = dropdownList.querySelector('.no-results');
            const dropdownIcon = dropdown.querySelector('.dropdown-icon');

            // Toggle dropdown on input click/focus
            searchInput.addEventListener('focus', function() {
                dropdownList.classList.add('show');
                filterItems();
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target)) {
                    dropdownList.classList.remove('show');
                }
            });

            // Filter items based on search input
            function filterItems() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let visibleCount = 0;
                let hasSelected = false;

                dropdownItems.forEach(item => {
                    const text = item.getAttribute('data-text').toLowerCase();
                    if (text.includes(searchTerm)) {
                        item.classList.remove('hidden');
                        visibleCount++;

                        // Highlight selected item
                        if (item.getAttribute('data-value') === hiddenInput.value) {
                            item.classList.add('selected');
                            hasSelected = true;
                        } else {
                            item.classList.remove('selected');
                        }
                    } else {
                        item.classList.add('hidden');
                    }
                });

                // Show/hide "All Promoters" option
                if (searchTerm === '' || 'all promoters'.includes(searchTerm)) {
                    dropdownItems[0].classList.remove('hidden');
                    visibleCount++;
                }

                // Show/hide "no results" message
                if (visibleCount === 0) {
                    noResults.classList.add('show');
                } else {
                    noResults.classList.remove('show');
                }
            }

            // Filter on input
            searchInput.addEventListener('input', function() {
                dropdownList.classList.add('show');
                filterItems();
            });

            // Handle item selection
            dropdownItems.forEach(item => {
                item.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    const text = this.getAttribute('data-text');

                    hiddenInput.value = value;
                    searchInput.value = value ? text : '';

                    // Update selected state
                    dropdownItems.forEach(i => i.classList.remove('selected'));
                    this.classList.add('selected');

                    // Close dropdown and submit form
                    dropdownList.classList.remove('show');
                    document.getElementById('filterForm').submit();
                });
            });

            // Handle keyboard navigation
            let selectedIndex = -1;

            searchInput.addEventListener('keydown', function(e) {
                const visibleItems = Array.from(dropdownItems).filter(item => !item.classList.contains('hidden'));

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, visibleItems.length - 1);
                    updateHighlight(visibleItems);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, -1);
                    updateHighlight(visibleItems);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (selectedIndex >= 0 && visibleItems[selectedIndex]) {
                        visibleItems[selectedIndex].click();
                    }
                } else if (e.key === 'Escape') {
                    dropdownList.classList.remove('show');
                    selectedIndex = -1;
                }
            });

            function updateHighlight(items) {
                items.forEach((item, index) => {
                    if (index === selectedIndex) {
                        item.style.backgroundColor = '#e3f2fd';
                        item.scrollIntoView({
                            block: 'nearest',
                            behavior: 'smooth'
                        });
                    } else {
                        item.style.backgroundColor = '';
                    }
                });
            }

            // Update icon on focus
            searchInput.addEventListener('focus', function() {
                dropdownIcon.classList.remove('fa-chevron-down');
                dropdownIcon.classList.add('fa-chevron-up');
            });

            searchInput.addEventListener('blur', function() {
                // Delay to allow click events on dropdown items
                setTimeout(function() {
                    if (!dropdownList.classList.contains('show')) {
                        dropdownIcon.classList.remove('fa-chevron-up');
                        dropdownIcon.classList.add('fa-chevron-down');
                    }
                }, 200);
            });
        })();
    </script>
</body>

</html>