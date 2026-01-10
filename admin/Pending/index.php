<?php
session_start();
$menuPath = "../";
$currentPage = "Pending";

require_once("../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

// Get latest active scheme
$stmt = $conn->prepare("
    SELECT s.*, 
           (SELECT COUNT(*) FROM Installments WHERE SchemeID = s.SchemeID) as total_installments
    FROM Schemes s 
    WHERE s.Status = 'Active' 
    ORDER BY s.CreatedAt DESC 
    LIMIT 1
");
$stmt->execute();
$latestScheme = $stmt->fetch(PDO::FETCH_ASSOC);

// Get all active schemes for filter
$stmt = $conn->prepare("SELECT SchemeID, SchemeName FROM Schemes WHERE Status = 'Active' ORDER BY SchemeName");
$stmt->execute();
$schemes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all active promoters for filter
$stmt = $conn->prepare("SELECT PromoterID, PromoterUniqueID, Name FROM Promoters WHERE Status = 'Active' ORDER BY Name");
$stmt->execute();
$promoters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected scheme (default to latest, but required)
$selectedSchemeId = isset($_GET['scheme_id']) ? $_GET['scheme_id'] : ($latestScheme['SchemeID'] ?? null);
$selectedPromoterId = isset($_GET['promoter_id']) ? $_GET['promoter_id'] : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Pagination settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

// Search and filtering
$search = isset($_GET['search']) ? $_GET['search'] : '';
// Installment is required - must select one
$installmentId = isset($_GET['installment_id']) ? $_GET['installment_id'] : '';

// If scheme is selected but no installment selected, get first installment as default and redirect
if (!empty($selectedSchemeId) && empty($installmentId)) {
    $stmt = $conn->prepare("SELECT InstallmentID FROM Installments WHERE SchemeID = ? AND Status = 'Active' ORDER BY InstallmentNumber ASC LIMIT 1");
    $stmt->execute([$selectedSchemeId]);
    $firstInstallment = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($firstInstallment) {
        $installmentId = $firstInstallment['InstallmentID'];
        // Build redirect URL preserving other filters
        $redirectUrl = "?scheme_id=" . urlencode($selectedSchemeId) . "&installment_id=" . urlencode($installmentId);
        if (!empty($search)) $redirectUrl .= "&search=" . urlencode($search);
        if (!empty($selectedPromoterId)) $redirectUrl .= "&promoter_id=" . urlencode($selectedPromoterId);
        if (!empty($startDate)) $redirectUrl .= "&start_date=" . urlencode($startDate);
        if (!empty($endDate)) $redirectUrl .= "&end_date=" . urlencode($endDate);
        header("Location: " . $redirectUrl);
        exit();
    }
}

// Build query conditions
$conditions = [];
$params = [];

if (!empty($search)) {
    $conditions[] = "(c.Name LIKE :search OR c.CustomerUniqueID LIKE :search OR c.Contact LIKE :search)";
    $params[':search'] = "%$search%";
}

// Scheme and Installment are both required
if (empty($selectedSchemeId) || empty($installmentId)) {
    // Force no results if scheme or installment is not selected
    $conditions[] = "1 = 0";
} else {
    $conditions[] = "i.SchemeID = :scheme_id";
    $params[':scheme_id'] = $selectedSchemeId;

    $conditions[] = "i.InstallmentID = :installment_id";
    $params[':installment_id'] = $installmentId;
}

if (!empty($selectedPromoterId)) {
    $conditions[] = "c.PromoterID = :promoter_id";
    $params[':promoter_id'] = $selectedPromoterId;
}

if (!empty($startDate)) {
    $conditions[] = "DATE(i.DrawDate) >= :startDate";
    $params[':startDate'] = $startDate;
}

if (!empty($endDate)) {
    $conditions[] = "DATE(i.DrawDate) <= :endDate";
    $params[':endDate'] = $endDate;
}

$whereClause = !empty($conditions) ? " AND " . implode(" AND ", $conditions) : "";

// Get total records count - All active customers with pending payments
// Simple logic: Show all active customers who have pending or not submitted payments
// Note: This shows all active customers for each installment in the selected scheme
$countQuery = "
    SELECT COUNT(DISTINCT CONCAT(c.CustomerID, '-', i.InstallmentID)) as total
    FROM Installments i
    JOIN Schemes s ON i.SchemeID = s.SchemeID
    CROSS JOIN Customers c
    LEFT JOIN Payments p ON p.CustomerID = c.CustomerID AND p.InstallmentID = i.InstallmentID
    WHERE c.Status = 'Active'
    AND (p.PaymentID IS NULL OR p.Status != 'Verified')
    $whereClause";

$stmt = $conn->prepare($countQuery);
$stmt->execute($params);
$totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get pending payments - Simple logic: All active customers with pending or not submitted payments
// Shows all active customers for each installment - no subscription restriction
$query = "
    SELECT 
        c.CustomerID,
        c.CustomerUniqueID,
        c.Name as CustomerName,
        c.Contact,
        c.PromoterID,
        s.SchemeName,
        i.InstallmentName,
        i.InstallmentNumber,
        i.Amount as InstallmentAmount,
        i.DrawDate,
        p.Status as PaymentStatus,
        p.SubmittedAt as PaymentSubmittedAt
    FROM Installments i
    JOIN Schemes s ON i.SchemeID = s.SchemeID
    CROSS JOIN Customers c
    LEFT JOIN Payments p ON p.CustomerID = c.CustomerID AND p.InstallmentID = i.InstallmentID
    WHERE c.Status = 'Active'
    AND (p.PaymentID IS NULL OR p.Status != 'Verified')
    $whereClause
    ORDER BY i.InstallmentNumber ASC, c.Name ASC
    LIMIT :offset, :limit";

$stmt = $conn->prepare($query);

// Bind parameters
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
$stmt->execute();

$pendingPayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get installments for the selected scheme
$installments = [];
if (!empty($selectedSchemeId)) {
    $stmt = $conn->prepare("
        SELECT InstallmentID, InstallmentName, InstallmentNumber, Amount, DrawDate 
        FROM Installments 
        WHERE SchemeID = ? 
        ORDER BY InstallmentNumber ASC
    ");
    $stmt->execute([$selectedSchemeId]);
    $installments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include("../components/sidebar.php");
include("../components/topbar.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Payments</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        body {
            background: #fff;
        }

        .content-wrapper {
            background: #fff;
        }

        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .scheme-installment-group {
            display: flex;
            gap: 15px;
            flex: 1 1 auto;
            align-items: flex-end;
        }

        .scheme-installment-group .filter-group {
            flex: 1;
        }

        .search-box {
            flex: 1 1 300px;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 10px 15px;
            padding-left: 40px;
            border: 1px solid #dfe6e9;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%23a4b0be" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>');
            background-repeat: no-repeat;
            background-position: 12px center;
        }

        .search-input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1 1 200px;
        }

        .filter-label {
            font-size: 14px;
            color: #576574;
            white-space: nowrap;
            font-weight: 500;
        }

        .filter-select,
        .filter-input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #dfe6e9;
            border-radius: 8px;
            font-size: 14px;
            background-color: white;
            color: #2d3436;
            transition: all 0.3s ease;
        }

        .filter-select:focus,
        .filter-input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .filter-select {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="%23a4b0be" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>');
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 30px;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }

        /* Standard dropdown style for promoter filter input */
        #promoterInput {
            background-color: #fff;
            color: #2d3436;
            border: 1px solid #dfe6e9;
            border-radius: 8px;
            padding: 8px 40px 8px 12px;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            transition: border-color 0.3s, box-shadow 0.3s;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            position: relative;
            background-image: url('data:image/svg+xml;utf8,<svg fill="none" height="20" viewBox="0 0 24 24" width="20" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5" stroke="%2399aabb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>');
            background-repeat: no-repeat;
            background-position: right 12px center;
            cursor: pointer;
        }

        #promoterInput:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15);
            outline: none;
        }

        @media (max-width: 768px) {
            .filter-container {
                flex-direction: column;
                gap: 12px;
            }

            .scheme-installment-group {
                flex-direction: column;
                width: 100%;
            }

            .filter-group {
                flex-wrap: wrap;
            }

            .filter-group:has(.filter-input) {
                flex-direction: column;
                align-items: flex-start;
            }

            .filter-group:has(.filter-input) .filter-label {
                margin-bottom: 5px;
            }

            .filter-group:has(.filter-input) span {
                margin: 5px 0;
            }
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .pending-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .pending-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .customer-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .customer-details {
            flex: 1;
        }

        .customer-name {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .customer-id {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 5px;
        }

        .contact-info {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #3498db;
            font-size: 14px;
        }

        .promoter-info {
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 5px;
        }

        .installment-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .installment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .installment-name {
            font-weight: 600;
            color: #2c3e50;
        }

        .installment-amount {
            font-weight: 600;
            color: #27ae60;
        }

        .installment-date {
            font-size: 14px;
            color: #7f8c8d;
            margin-top: 5px;
        }

        .payment-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-not-submitted {
            background: #f8d7da;
            color: #721c24;
        }

        .no-pending {
            text-align: center;
            padding: 50px 20px;
            color: #7f8c8d;
        }

        .no-pending i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #bdc3c7;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 30px;
        }

        .pagination a {
            padding: 8px 12px;
            border: 1px solid #dfe6e9;
            border-radius: 6px;
            color: #2c3e50;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background: #f8f9fa;
            border-color: #3498db;
        }

        .pagination .active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }

        .whatsapp-action {
            margin-bottom: 20px;
            text-align: right;
        }

        .btn-success {
            background: #25D366;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            background: #128C7E;
            transform: translateY(-2px);
        }

        .btn-success:disabled {
            background: #a8a8a8;
            cursor: not-allowed;
            transform: none;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-excel {
            background: #217346;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-excel:hover {
            background: #1e5e3a;
            transform: translateY(-2px);
            color: white;
        }

        .btn-clear-filter {
            background: linear-gradient(135deg, #fff, #f1f2f6);
            color: #576574;
            border: 1px solid #dfe6e9;
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        }

        .btn-clear-filter:hover {
            background: linear-gradient(135deg, #f1f2f6, #fff);
            color: #3498db;
            border-color: #3498db;
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.08);
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <div class="page-header">
            <h1 class="page-title">Pending Payments</h1>
        </div>

        <div class="filter-container">
            <form action="" method="GET" style="display: flex; gap: 15px; width: 100%; flex-wrap: wrap;">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" class="search-input"
                        placeholder="Search by customer name, ID or contact..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <!-- Scheme and Installment grouped together -->
                <div class="scheme-installment-group">
                    <div class="filter-group">
                        <label class="filter-label">Scheme: <span style="color: red;">*</span></label>
                        <select name="scheme_id" id="schemeSelect" class="filter-select" required>
                            <?php if (empty($selectedSchemeId)): ?>
                                <option value="">-- Select Scheme --</option>
                            <?php endif; ?>
                            <?php foreach ($schemes as $scheme): ?>
                                <option value="<?php echo $scheme['SchemeID']; ?>"
                                    <?php echo $selectedSchemeId == $scheme['SchemeID'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($scheme['SchemeName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Installment: <span style="color: red;">*</span></label>
                        <select name="installment_id" id="installmentSelect" class="filter-select" required <?php echo empty($selectedSchemeId) ? 'disabled' : ''; ?>>
                            <?php if (empty($selectedSchemeId)): ?>
                                <option value="">-- Select Scheme First --</option>
                            <?php elseif (empty($installmentId)): ?>
                                <option value="">-- Select Installment --</option>
                            <?php endif; ?>
                            <?php if (!empty($selectedSchemeId) && !empty($installments)): ?>
                                <?php foreach ($installments as $installment): ?>
                                    <option value="<?php echo $installment['InstallmentID']; ?>"
                                        <?php echo $installmentId == $installment['InstallmentID'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($installment['InstallmentName'] . ' (₹' . number_format($installment['Amount'], 2) . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="filter-group">
                    <label class="filter-label">Promoter:</label>
                    <input list="promoterList" class="filter-select" name="promoter_id" id="promoterInput" placeholder="Type promoter name or ID..." value="<?php echo htmlspecialchars($selectedPromoterId); ?>">
                    <datalist id="promoterList">
                        <option value="">All Promoters</option>
                        <?php foreach ($promoters as $promoter): ?>
                            <option value="<?php echo $promoter['PromoterUniqueID']; ?>">
                                <?php echo htmlspecialchars($promoter['PromoterUniqueID'] . ' - ' . $promoter['Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Date Range:</label>
                    <input type="date" class="filter-input" name="start_date" value="<?php echo $startDate; ?>">
                    <span>to</span>
                    <input type="date" class="filter-input" name="end_date" value="<?php echo $endDate; ?>">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Apply Filters
                </button>
                <?php if (!empty($search) || !empty($selectedPromoterId) || !empty($startDate) || !empty($endDate)): ?>
                    <a href="?scheme_id=<?php echo urlencode($selectedSchemeId ?? ''); ?><?php echo !empty($installmentId) ? '&installment_id=' . urlencode($installmentId) : ''; ?>" class="btn btn-clear-filter">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                <?php endif; ?>
            </form>

            <div class="action-buttons">
                <?php if (!empty($installmentId)): ?>
                    <button type="button" class="btn btn-success" onclick="sendWhatsAppReminders()">
                        <i class="fab fa-whatsapp"></i> Send WhatsApp Reminders
                    </button>
                <?php endif; ?>

                <a href="export_pending.php?scheme_id=<?php echo $selectedSchemeId; ?><?php echo !empty($installmentId) ? '&installment_id=' . $installmentId : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-excel">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </a>
            </div>
        </div>

        <?php if (count($pendingPayments) > 0): ?>
            <?php foreach ($pendingPayments as $payment): ?>
                <div class="pending-card">
                    <div class="customer-info">
                        <div class="customer-details">
                            <div class="customer-name"><?php echo htmlspecialchars($payment['CustomerName']); ?></div>
                            <div class="customer-id"><?php echo $payment['CustomerUniqueID']; ?></div>
                            <div class="contact-info">
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($payment['Contact']); ?>
                            </div>
                        </div>
                    </div>

                    <div class="installment-details">
                        <div class="installment-header">
                            <div class="installment-name">
                                <?php echo htmlspecialchars($payment['SchemeName']); ?> -
                                <?php echo htmlspecialchars($payment['InstallmentName']); ?>
                            </div>
                            <div class="installment-amount">
                                ₹<?php echo number_format($payment['InstallmentAmount'], 2); ?>
                            </div>
                        </div>
                        <div class="installment-date">
                            <i class="fas fa-calendar"></i> Draw Date: <?php echo date('M d, Y', strtotime($payment['DrawDate'])); ?>
                        </div>
                        <div style="margin-top: 10px;">
                            <span class="payment-status <?php echo $payment['PaymentStatus'] ? 'status-pending' : 'status-not-submitted'; ?>">
                                <?php echo $payment['PaymentStatus'] ? 'Payment Pending Verification' : 'Payment Not Submitted'; ?>
                            </span>
                            <?php if ($payment['PaymentSubmittedAt']): ?>
                                <span style="margin-left: 10px; color: #7f8c8d; font-size: 14px;">
                                    Submitted on: <?php echo date('M d, Y H:i', strtotime($payment['PaymentSubmittedAt'])); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($selectedSchemeId) ? '&scheme_id=' . $selectedSchemeId : ''; ?><?php echo !empty($installmentId) ? '&installment_id=' . $installmentId : ''; ?>">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                        <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($selectedSchemeId) ? '&scheme_id=' . $selectedSchemeId : ''; ?><?php echo !empty($installmentId) ? '&installment_id=' . $installmentId : ''; ?>">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);

                    for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($selectedSchemeId) ? '&scheme_id=' . $selectedSchemeId : ''; ?><?php echo !empty($installmentId) ? '&installment_id=' . $installmentId : ''; ?>"
                            class="<?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($selectedSchemeId) ? '&scheme_id=' . $selectedSchemeId : ''; ?><?php echo !empty($installmentId) ? '&installment_id=' . $installmentId : ''; ?>">
                            <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="?page=<?php echo $totalPages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($selectedSchemeId) ? '&scheme_id=' . $selectedSchemeId : ''; ?><?php echo !empty($installmentId) ? '&installment_id=' . $installmentId : ''; ?>">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="no-pending">
                <i class="fas fa-check-circle"></i>
                <p>No pending payments found</p>
                <?php if (!empty($search) || !empty($installmentId)): ?>
                    <a href="?scheme_id=<?php echo $selectedSchemeId; ?>" class="btn btn-primary">Clear Filters</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const schemeSelect = document.getElementById('schemeSelect');
        const installmentSelect = document.getElementById('installmentSelect');

        // Load installments when scheme is selected
        if (schemeSelect) {
            schemeSelect.addEventListener('change', function() {
                const schemeId = this.value;

                if (schemeId) {
                    // Fetch installments for the selected scheme
                    fetch(`get_installments.php?scheme_id=${schemeId}`)
                        .then(response => response.json())
                        .then(data => {
                            installmentSelect.innerHTML = '<option value="">-- Select Installment --</option>';
                            if (data.success && data.installments && data.installments.length > 0) {
                                data.installments.forEach(inst => {
                                    const option = document.createElement('option');
                                    option.value = inst.InstallmentID;
                                    option.textContent = `${inst.InstallmentName} (₹${parseFloat(inst.Amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})})`;
                                    installmentSelect.appendChild(option);
                                });
                                installmentSelect.disabled = false;
                                // Auto-select first installment if none is selected, then submit
                                if (data.installments.length > 0 && !installmentSelect.value) {
                                    installmentSelect.value = data.installments[0].InstallmentID;
                                    // Submit form to load data
                                    setTimeout(() => {
                                        installmentSelect.form.submit();
                                    }, 100);
                                }
                            } else {
                                installmentSelect.innerHTML = '<option value="">No installments available</option>';
                                installmentSelect.disabled = true;
                            }
                        })
                        .catch(error => {
                            console.error('Error loading installments:', error);
                            installmentSelect.innerHTML = '<option value="">Error loading installments</option>';
                            installmentSelect.disabled = false;
                        });
                } else {
                    installmentSelect.innerHTML = '<option value="">-- Select Scheme First --</option>';
                    installmentSelect.disabled = true;
                }
            });
        }

        // Auto-submit form when installment changes (if both scheme and installment are selected)
        if (installmentSelect) {
            installmentSelect.addEventListener('change', function() {
                if (schemeSelect.value && this.value) {
                    this.form.submit();
                }
            });
        }

        function sendWhatsAppReminders() {
            if (confirm('Are you sure you want to send WhatsApp reminders to all customers with pending payments for this installment?')) {
                // Show loading state
                const button = document.querySelector('.whatsapp-action button');
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                button.disabled = true;

                // Send AJAX request
                fetch('send_reminders.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            installment_id: <?php echo !empty($installmentId) ? $installmentId : 'null'; ?>,
                            scheme_id: <?php echo !empty($selectedSchemeId) ? $selectedSchemeId : 'null'; ?>
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('WhatsApp reminders sent successfully!');
                        } else {
                            alert('Error sending reminders: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error sending reminders. Please try again.');
                    })
                    .finally(() => {
                        // Reset button state
                        button.innerHTML = originalText;
                        button.disabled = false;
                    });
            }
        }
    </script>
</body>

</html>