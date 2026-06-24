<?php
session_start();

// Check if promoter is logged in
if (!isset($_SESSION['promoter_id'])) {
    header("Location: ../login.php");
    exit();
}

$menuPath = "../";
$currentPage = "ListedCustomers";

// Database connection
require_once("../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

$message = '';
$messageType = '';
$showNotification = false;

// Get promoter details
try {
    $stmt = $conn->prepare("SELECT * FROM Promoters WHERE PromoterID = ?");
    $stmt->execute([$_SESSION['promoter_id']]);
    $promoter = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get promoter's unique ID
    $promoterUniqueID = $promoter['PromoterUniqueID'];
} catch (PDOException $e) {
    $message = "Error fetching promoter data";
    $messageType = "error";
}

// Get all customers under this promoter with comprehensive data
try {
    $stmt = $conn->prepare("
        SELECT 
            c.*,
            p.Name as PromoterName, 
            p.PromoterUniqueID,
            (SELECT COUNT(*) FROM Payments WHERE CustomerID = c.CustomerID) as total_payments,
            (SELECT COUNT(*) FROM Payments WHERE CustomerID = c.CustomerID AND Status = 'Verified') as verified_payments,
            (SELECT COUNT(*) FROM Payments WHERE CustomerID = c.CustomerID AND Status = 'Pending') as pending_payments,
            (SELECT SUM(Amount) FROM Payments WHERE CustomerID = c.CustomerID AND Status = 'Verified') as total_paid_amount,
            (SELECT COUNT(*) FROM Subscriptions WHERE CustomerID = c.CustomerID) as total_subscriptions,
            (SELECT COUNT(*) FROM Subscriptions WHERE CustomerID = c.CustomerID AND RenewalStatus = 'Active') as active_subscriptions
        FROM Customers c
        JOIN Promoters p ON c.PromoterID = p.PromoterUniqueID
        WHERE c.PromoterID = ? 
        ORDER BY c.CreatedAt DESC
    ");
    $stmt->execute([$promoterUniqueID]);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get installment-wise payment details for each customer
    foreach ($customers as &$customer) {
        $stmt = $conn->prepare("
            SELECT 
                i.InstallmentID,
                i.InstallmentName,
                i.InstallmentNumber,
                i.DrawDate,
                s.SchemeID,
                s.SchemeName,
                s.Status as SchemeStatus,
                sub.SubscriptionID,
                sub.RenewalStatus,
                p.PaymentID,
                p.Status as PaymentStatus,
                p.Amount,
                p.SubmittedAt,
                p.VerifiedAt
            FROM Installments i
            JOIN Schemes s ON i.SchemeID = s.SchemeID
            LEFT JOIN Subscriptions sub ON s.SchemeID = sub.SchemeID AND sub.CustomerID = ?
            LEFT JOIN Payments p ON i.InstallmentID = p.InstallmentID AND p.CustomerID = ?
            WHERE EXISTS (
                SELECT 1 FROM Subscriptions sub2
                WHERE sub2.CustomerID = ? 
                  AND sub2.SchemeID = s.SchemeID
            )
            ORDER BY i.DrawDate ASC, i.InstallmentNumber ASC
        ");
        $stmt->execute([$customer['CustomerID'], $customer['CustomerID'], $customer['CustomerID']]);
        $customer['installments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Unset the reference to prevent issues with the last element
    unset($customer);
} catch (PDOException $e) {
    $message = "Error fetching customer data";
    $messageType = "error";
}

// Get customer statistics
try {
    // Get total customers count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM Customers 
        WHERE PromoterID = ?
    ");
    $stmt->execute([$promoterUniqueID]);
    $totalCustomers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get active customers count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as active 
        FROM Customers 
        WHERE PromoterID = ? AND Status = 'Active'
    ");
    $stmt->execute([$promoterUniqueID]);
    $activeCustomers = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

    // Get total payments amount
    $stmt = $conn->prepare("
        SELECT SUM(p.Amount) as total_amount
        FROM Payments p
        JOIN Customers c ON p.CustomerID = c.CustomerID
        WHERE c.PromoterID = ? AND p.Status = 'Verified'
    ");
    $stmt->execute([$promoterUniqueID]);
    $totalAmount = $stmt->fetch(PDO::FETCH_ASSOC)['total_amount'] ?? 0;

    // Get pending payments count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as pending_count
        FROM Payments p
        JOIN Customers c ON p.CustomerID = c.CustomerID
        WHERE c.PromoterID = ? AND p.Status = 'Pending'
    ");
    $stmt->execute([$promoterUniqueID]);
    $pendingPayments = $stmt->fetch(PDO::FETCH_ASSOC)['pending_count'];
} catch (PDOException $e) {
    $message = "Error calculating customer statistics";
    $messageType = "error";
}

// Get available schemes for this promoter's customers
try {
    $stmt = $conn->prepare("\n        SELECT DISTINCT s.SchemeID, s.SchemeName\n        FROM Schemes s\n        JOIN Subscriptions sub ON s.SchemeID = sub.SchemeID\n        JOIN Customers c ON sub.CustomerID = c.CustomerID\n        WHERE c.PromoterID = ? AND s.Status = 'Active'\n        ORDER BY s.SchemeName ASC\n    ");
    $stmt->execute([$promoterUniqueID]);
    $availableSchemes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $availableSchemes = [];
}

// Get available installments for this promoter's customers
try {
    $stmt = $conn->prepare("
        SELECT DISTINCT i.InstallmentID, i.InstallmentName, i.InstallmentNumber, s.SchemeID, s.SchemeName
        FROM Installments i
        JOIN Schemes s ON i.SchemeID = s.SchemeID
        JOIN Payments p ON i.InstallmentID = p.InstallmentID
        JOIN Customers c ON p.CustomerID = c.CustomerID
        WHERE c.PromoterID = ? AND i.Status = 'Active'
        ORDER BY s.SchemeName ASC, i.InstallmentNumber ASC
    ");
    $stmt->execute([$promoterUniqueID]);
    $availableInstallments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $availableInstallments = [];
}

// Get payment status statistics for each customer
try {
    foreach ($customers as &$customer) {
        // Get payment status breakdown for this customer
        $stmt = $conn->prepare("
            SELECT 
                p.Status,
                COUNT(*) as count,
                SUM(p.Amount) as total_amount
            FROM Payments p
            WHERE p.CustomerID = ?
            GROUP BY p.Status
        ");
        $stmt->execute([$customer['CustomerID']]);
        $paymentStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $customer['payment_stats'] = [
            'verified' => ['count' => 0, 'amount' => 0],
            'pending' => ['count' => 0, 'amount' => 0],
            'rejected' => ['count' => 0, 'amount' => 0],
            'unpaid' => ['count' => 0, 'amount' => 0]
        ];
        
        foreach ($paymentStats as $stat) {
            $status = strtolower($stat['Status']);
            if (isset($customer['payment_stats'][$status])) {
                $customer['payment_stats'][$status]['count'] = $stat['count'];
                $customer['payment_stats'][$status]['amount'] = $stat['total_amount'];
            }
        }
        
        // Calculate unpaid installments
        $stmt = $conn->prepare("
            SELECT COUNT(*) as unpaid_count
            FROM Installments i
            JOIN Schemes s ON i.SchemeID = s.SchemeID
            WHERE NOT EXISTS (
                SELECT 1 FROM Payments p 
                WHERE p.InstallmentID = i.InstallmentID 
                AND p.CustomerID = ?
                AND p.Status IN ('Verified', 'Pending')
            )
            AND EXISTS (
                SELECT 1 FROM Subscriptions sub 
                WHERE sub.CustomerID = ? 
                AND sub.SchemeID = s.SchemeID
            )
        ");
        $stmt->execute([$customer['CustomerID'], $customer['CustomerID']]);
        $unpaidResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $customer['payment_stats']['unpaid']['count'] = $unpaidResult['unpaid_count'];
    }
    unset($customer);
} catch (PDOException $e) {
    // Handle error silently
}

// Handle search functionality
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$filterStatus = isset($_GET['status']) ? $_GET['status'] : '';
$filterScheme = isset($_GET['scheme']) ? $_GET['scheme'] : '';
$filterInstallment = isset($_GET['installment']) ? $_GET['installment'] : '';
$filterPaymentStatus = isset($_GET['payment_status']) ? $_GET['payment_status'] : '';

if (!empty($searchQuery) || !empty($filterStatus) || !empty($filterScheme) || !empty($filterInstallment) || !empty($filterPaymentStatus)) {
    $filteredCustomers = [];
    
    foreach ($customers as $customer) {
        $matchesSearch = empty($searchQuery) || 
            stripos($customer['Name'], $searchQuery) !== false || 
            stripos($customer['CustomerUniqueID'], $searchQuery) !== false || 
            stripos($customer['Contact'], $searchQuery) !== false ||
            stripos($customer['Email'], $searchQuery) !== false;
            
        $matchesStatus = empty($filterStatus) || $customer['Status'] === $filterStatus;
        
        // Check if customer matches selected scheme via subscription; support 'unsubscribed'
        $matchesScheme = empty($filterScheme);
        $matchesInstallment = empty($filterInstallment);

        if (!empty($filterScheme) && !empty($filterInstallment)) {
            // Both scheme and installment selected: check for a matching pair
            foreach ($customer['installments'] as $installment) {
                if (
                    isset($installment['SchemeID'], $installment['InstallmentID']) &&
                    $installment['SchemeID'] == $filterScheme &&
                    $installment['InstallmentID'] == $filterInstallment
                ) {
                    $matchesScheme = true;
                    $matchesInstallment = true;
                    break;
                }
            }
        } else {
            // Only scheme selected
            if (!empty($filterScheme)) {
                if ($filterScheme === 'unsubscribed') {
                    $matchesScheme = ((int)($customer['total_subscriptions'] ?? 0)) === 0 && ((int)($customer['total_payments'] ?? 0)) === 0;
                } else {
                    foreach ($customer['installments'] as $installment) {
                        if (isset($installment['SchemeID']) && $installment['SchemeID'] == $filterScheme) {
                            $matchesScheme = true;
                            break;
                        }
                    }
                }
            }
            // Only installment selected
            if (!empty($filterInstallment)) {
                foreach ($customer['installments'] as $installment) {
                    if (isset($installment['InstallmentID']) && $installment['InstallmentID'] == $filterInstallment) {
                        $matchesInstallment = true;
                        break;
                    }
                }
            }
        }
        
        // New: Check for specific installment-payment status combination
        $matchesInstallmentStatus = true;
        if (!empty($filterInstallment) && !empty($filterPaymentStatus)) {
            $matchesInstallmentStatus = false;
            foreach ($customer['installments'] as $installment) {
                if (
                    isset($installment['InstallmentID']) && $installment['InstallmentID'] == $filterInstallment &&
                    (
                        ($filterPaymentStatus === 'unpaid' && empty($installment['PaymentID'])) ||
                        (!empty($installment['PaymentID']) && strtolower($installment['PaymentStatus']) === $filterPaymentStatus)
                    )
                ) {
                    $matchesInstallmentStatus = true;
                    break;
                }
            }
        }
        
        // If only payment status is selected (no installment), fallback to old logic
        $matchesPaymentStatus = empty($filterPaymentStatus) || !empty($filterInstallment);
        if (!empty($filterPaymentStatus) && empty($filterInstallment)) {
            $matchesPaymentStatus = false;
            foreach ($customer['installments'] as $installment) {
                if (
                    (!empty($installment['PaymentID']) && strtolower($installment['PaymentStatus']) === $filterPaymentStatus) ||
                    ($filterPaymentStatus === 'unpaid' && empty($installment['PaymentID']))
                ) {
                    $matchesPaymentStatus = true;
                    break;
                }
            }
        }
        
        if (
            $matchesSearch &&
            $matchesStatus &&
            $matchesScheme &&
            $matchesInstallment &&
            $matchesPaymentStatus &&
            $matchesInstallmentStatus
        ) {
            $filteredCustomers[] = $customer;
        }
    }
    
    $customers = $filteredCustomers;
}

// Pagination logic
$perPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$totalCustomersFiltered = count($customers);
$totalPages = max(1, ceil($totalCustomersFiltered / $perPage));
$startIndex = ($page - 1) * $perPage;
$customers = array_slice($customers, $startIndex, $perPage);

// Helper for pagination links
function buildPageUrl($pageNum) {
    $params = $_GET;
    $params['page'] = $pageNum;
    return '?' . http_build_query($params);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listed Customers | Golden Dreams</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: rgb(155, 128, 18);
            --primary-light: rgba(13, 106, 80, 0.1);
            --secondary-color: #2c3e50;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
            --warning-color: #f1c40f;
            --border-color: #e0e0e0;
            --text-primary: #2c3e50;
            --text-secondary: #7f8c8d;
            --bg-light: #f8f9fa;
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #f5f7fa;
            font-family: 'Poppins', sans-serif;
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .content-wrapper {
            width: 100%;
            min-height: 100vh;
            padding: 20px;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
            padding-top: calc(var(--topbar-height) + 20px) !important;
            max-width: 100%;
            overflow-x: hidden;
        }

        .top-profile {
            background: white;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .profile-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .profile-image-container {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid var(--primary-light);
        }

        .profile-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info h2 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text-primary);
        }

        .profile-id {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .profile-stats {
            display: flex;
            gap: 30px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            display: block;
            font-size: 24px;
            font-weight: 600;
            color: var(--primary-color);
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .main-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: var(--card-shadow);
            width: 100%;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .section-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .section-icon i {
            font-size: 24px;
            color: var(--primary-color);
        }

        .section-info h2 {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .section-info p {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .search-filter-container {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            min-width: 250px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }

        .filter-box {
            min-width: 150px;
        }

        .filter-box select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 14px;
            background-color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-box select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .filter-box select option {
            padding: 8px;
            font-size: 14px;
        }

        .search-filter-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            width: 100%;
            align-items: flex-end;
        }

        .search-filter-form .btn-primary {
            height: 45px;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 106, 80, 0.3);
        }

        .customers-container {
            margin-top: 20px;
        }

        .customer-card {
            background: white;
            border-radius: 12px;
            margin-bottom: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            border-left: 3px solid var(--primary-color);
            transition: all 0.2s ease;
            overflow: hidden;
            padding: 20px 0;
        }

        .customer-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            min-height: 60px;
            position: relative;
        }

        .customer-header:hover {
            background-color: var(--bg-light);
        }

        .customer-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
        }

        .customer-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .customer-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .customer-name {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2px;
        }

        .customer-details {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 2px;
        }

        .customer-detail {
            font-size: 12px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .customer-detail i {
            color: var(--primary-color);
        }

        .customer-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 14px;
            font-size: 11px;
            font-weight: 500;
            align-self: flex-start;
            margin-top: 2px;
        }

        .status-active {
            background: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }

        .status-inactive {
            background: rgba(149, 165, 166, 0.1);
            color: #95a5a6;
        }

        .status-suspended {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .customer-stats {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        .customer-payment-stats {
            margin-top: 6px;
        }

        .payment-stat-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            cursor: help;
            transition: all 0.3s ease;
        }

        .payment-stat-badge.verified {
            background: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
            border: 1px solid rgba(46, 204, 113, 0.2);
        }

        .payment-stat-badge.pending {
            background: rgba(241, 196, 15, 0.1);
            color: #f1c40f;
            border: 1px solid rgba(241, 196, 15, 0.2);
        }

        .payment-stat-badge.rejected {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.2);
        }

        .payment-stat-badge.unpaid {
            background: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
            border: 1px solid rgba(155, 89, 182, 0.2);
        }

        .payment-stat-badge.no-payments {
            background: rgba(149, 165, 166, 0.1);
            color: #95a5a6;
            border: 1px solid rgba(149, 165, 166, 0.2);
        }

        .payment-stat-badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .payment-stat-badge i {
            font-size: 10px;
        }

        .expand-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            transition: transform 0.2s ease;
            font-size: 15px;
        }

        .expand-icon.expanded {
            transform: rotate(180deg);
        }

        .customer-details-expanded {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background: var(--bg-light);
        }

        .customer-details-expanded.expanded {
            max-height: 2000px;
        }

        .details-content {
            padding: 20px;
        }

        .details-section {
            margin-bottom: 25px;
        }

        .details-section h4 {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .details-section h4 i {
            color: var(--primary-color);
        }

        .customer-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 15px;
        }

        .detail-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .detail-card-header {
            background: var(--primary-light);
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .detail-card-header i {
            color: var(--primary-color);
            font-size: 18px;
        }

        .detail-card-header span {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .detail-card-content {
            padding: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 8px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-row .detail-label {
            font-size: 14px;
            color: var(--text-secondary);
            font-weight: 500;
            min-width: 120px;
        }

        .detail-row .detail-value {
            font-size: 14px;
            color: var(--text-primary);
            font-weight: 500;
            text-align: right;
            flex: 1;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .details-table th {
            background: var(--primary-color);
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-size: 14px;
            font-weight: 500;
        }

        .details-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: top;
        }

        .details-table tr:last-child td {
            border-bottom: none;
        }

        .details-table .detail-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
            width: 25%;
            background: var(--bg-light);
        }

        .details-table .detail-value {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-primary);
            width: 25%;
        }

        .payments-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .payments-table th {
            background: var(--primary-color);
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 14px;
            font-weight: 500;
        }

        .payments-table td {
            padding: 12px;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
        }

        .payments-table tr:last-child td {
            border-bottom: none;
        }

        .payment-status {
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }

        .payment-status.verified {
            background: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }

        .payment-status.pending {
            background: rgba(241, 196, 15, 0.1);
            color: #f1c40f;
        }

        .payment-status.rejected {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .payment-status.unpaid {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .no-customers {
            text-align: center;
            padding: 50px 0;
            color: var(--text-secondary);
        }

        .no-customers i {
            font-size: 50px;
            margin-bottom: 20px;
            color: var(--border-color);
        }

        .no-customers h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .no-customers p {
            font-size: 14px;
            max-width: 400px;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
                padding: 15px;
            }

            .top-profile {
                flex-direction: column;
                gap: 20px;
                text-align: center;
                padding: 15px;
            }

            .profile-left {
                flex-direction: column;
                align-items: center;
            }

            .profile-stats {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
                width: 100%;
            }

            .stat-item {
                min-width: unset;
                padding: 10px;
                background: var(--bg-light);
                border-radius: 10px;
            }

            .main-content {
                padding: 15px;
            }
            
            .section-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .search-filter-container {
                flex-direction: column;
                gap: 10px;
            }

            .search-box, .filter-box {
                width: 100%;
                min-width: unset;
            }
            
            .search-filter-form {
                flex-direction: column !important;
                gap: 10px !important;
            }
            
            .search-filter-form .btn-primary {
                width: 100%;
                justify-content: center;
            }
            
            .customer-card {
                border-radius: 10px;
                margin-bottom: 18px;
                box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            }
            .customer-header {
                flex-direction: row;
                align-items: flex-start;
                justify-content: space-between;
                padding: 14px 10px;
                gap: 10px;
                min-height: unset;
            }
            .customer-avatar {
                width: 48px;
                height: 48px;
            }
            .customer-info {
                width: 100%;
            }
            .customer-details {
                flex-direction: column;
                gap: 6px;
                margin-bottom: 4px;
            }
            .customer-payment-stats {
                flex-wrap: wrap;
                gap: 5px;
                margin-top: 6px;
            }
            .payment-stat-badge {
                font-size: 10px;
                padding: 2px 5px;
            }
            .edit-btn-inline .btn-primary {
                min-width: 70px;
                font-size: 12px;
                padding: 6px 10px;
            }
            .customer-actions-group {
                flex-direction: column;
                align-items: flex-end;
                gap: 6px;
            }
            .customer-details-expanded {
                padding: 0 4px;
            }
            .details-content {
                padding: 10px 2px;
            }
            .details-section {
                margin-bottom: 16px;
            }
            .details-section h4 {
                font-size: 14px;
                margin-bottom: 8px;
            }
            .customer-details-grid {
                grid-template-columns: 1fr;
                gap: 10px;
                margin-top: 8px;
            }
            .detail-card {
                border-radius: 8px;
                box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            }
            .detail-card-header {
                padding: 10px 12px;
                font-size: 13px;
            }
            .detail-card-header i {
                font-size: 15px;
            }
            .detail-card-header span {
                font-size: 13px;
            }
            .detail-card-content {
                padding: 10px 12px;
            }
            .detail-row .detail-label,
            .detail-row .detail-value {
                font-size: 12px;
            }
            .details-table th,
            .details-table td {
                padding: 8px 6px;
                font-size: 12px;
            }
            .payments-table th,
            .payments-table td {
                padding: 8px 6px;
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .profile-stats {
                grid-template-columns: 1fr;
            }

            .stat-item {
                padding: 12px;
            }

            .stat-value {
                font-size: 20px;
            }

            .customer-card {
                border-radius: 8px;
                margin-bottom: 12px;
            }
            .customer-header {
                flex-direction: row;
                align-items: flex-start;
                justify-content: space-between;
                padding: 8px 6px;
                gap: 8px;
            }
            .customer-avatar {
                width: 38px;
                height: 38px;
            }
            .customer-name {
                font-size: 13px;
            }
            .customer-detail {
                font-size: 11px;
            }
            .customer-status {
                font-size: 10px;
                padding: 2px 5px;
            }
            .edit-btn-inline .btn-primary {
                min-width: 60px;
                font-size: 11px;
                padding: 5px 8px;
            }
            .customer-actions-group {
                flex-direction: column;
                align-items: flex-end;
                gap: 4px;
            }
            .customer-details-expanded {
                padding: 0 2px;
            }
            .details-content {
                padding: 6px 0;
            }
            .details-section {
                margin-bottom: 10px;
            }
            .details-section h4 {
                font-size: 12px;
                margin-bottom: 5px;
            }
            .customer-details-grid {
                grid-template-columns: 1fr;
                gap: 6px;
                margin-top: 4px;
            }
            .detail-card {
                border-radius: 6px;
                box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            }
            .detail-card-header {
                padding: 7px 8px;
                font-size: 11px;
            }
            .detail-card-header i {
                font-size: 12px;
            }
            .detail-card-header span {
                font-size: 11px;
            }
            .detail-card-content {
                padding: 7px 8px;
            }
            .detail-row .detail-label,
            .detail-row .detail-value {
                font-size: 10px;
            }
            .details-table,
            .payments-table {
                display: block;
                width: 100%;
                overflow-x: auto;
            }
            .details-table th,
            .details-table td,
            .payments-table th,
            .payments-table td {
                padding: 6px 4px;
                font-size: 10px;
                white-space: nowrap;
            }
        }

        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .pagination a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 35px;
            height: 35px;
            padding: 0 10px;
            border-radius: 8px;
            background: white;
            color: #2c3e50;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border: 1px solid #e0e0e0;
        }

        .pagination a:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        }

        .pagination a.active {
            background: var(--primary-color);
            color: white;
            border-color:var(--primary-color);
            box-shadow: 0 4px 8px rgba(52, 152, 219, 0.3);
        }

        .pagination a:first-child,
        .pagination a:last-child {
            font-size: 16px;
            font-weight: 600;
        }

        @media (max-width: 480px) {
            .pagination {
                gap: 5px;
            }

            .pagination a {
                min-width: 30px;
                height: 30px;
                font-size: 13px;
            }
        }

        .edit-btn-inline {
            margin-left: auto;
            display: flex;
            align-items: center;
            height: 100%;
        }
        .edit-btn-inline .btn-primary {
            min-width: 80px;
            font-size: 13px;
            padding: 7px 14px;
        }

        .customer-actions-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-edit-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--primary-color);
            color: #fff;
            border: none;
            font-size: 14px;
            transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
            box-shadow: 0 2px 8px rgba(13, 106, 80, 0.08);
            text-decoration: none;
            cursor: pointer;
        }
        .btn-edit-icon:hover {
            background: var(--secondary-color);
            color: #fff;
            transform: translateY(-2px) scale(1.08);
            box-shadow: 0 4px 16px rgba(44, 62, 80, 0.12);
        }

        @media (max-width: 480px) {
            .btn-edit-icon {
                width: 30px;
                height: 30px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <?php include('../components/sidebar.php'); ?>
    <?php include('../components/topbar.php'); ?>

    <div class="content-wrapper">
        <div class="top-profile">
            <div class="profile-left">
                <div class="profile-image-container">
                    <img src="<?php 
                        if (!empty($promoter['ProfileImageURL']) && $promoter['ProfileImageURL'] !== '-'): 
                            echo '../../' . htmlspecialchars($promoter['ProfileImageURL']);
                        else:
                            echo '../../uploads/profile/default.png';
                        endif;
                    ?>" alt="Profile" class="profile-image">
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($promoter['Name']); ?></h2>
                    <div class="profile-id">ID: <?php echo htmlspecialchars($promoter['PromoterUniqueID']); ?></div>
                </div>
            </div>
            <div class="profile-stats">
                <div class="stat-item">
                    <span class="stat-value"><?php echo $totalCustomers; ?></span>
                    <span class="stat-label">Total Customers</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo $activeCustomers; ?></span>
                    <span class="stat-label">Active</span>
                </div>
                <div class="stat-item">

                </div>
            </div>
        </div>

        <div class="main-content">
            <?php if ($showNotification && $message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="section-header">
                <div class="section-title">
                    <div class="section-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="section-info">
                        <h2>Listed Customers</h2>
                        <p>View detailed information about all your customers</p>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-bottom: 15px;">
                <a href="export_customers_payments.php<?php echo empty($_SERVER['QUERY_STRING']) ? '' : '?' . htmlspecialchars($_SERVER['QUERY_STRING']); ?>" class="btn-primary" style="background: #28a745; color: #fff; min-width: 120px; text-align: center;">
                    <i class="fas fa-file-export"></i> Export
                </a>
            </div>

            <div class="search-filter-container">
                <form method="GET" action="" class="search-filter-form" style="display: flex; gap: 15px; flex-wrap: wrap; width: 100%;">
                    <div class="search-box" style="flex: 1; min-width: 250px;">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Search by name, ID, contact or email..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                    </div>
                    <div class="filter-box" style="min-width: 150px;">
                        <select name="status" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="Active" <?php echo $filterStatus === 'Active' ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo $filterStatus === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="Suspended" <?php echo $filterStatus === 'Suspended' ? 'selected' : ''; ?>>Suspended</option>
                        </select>
                    </div>
                    <div class="filter-box" style="min-width: 150px;">
                        <select name="scheme" onchange="this.form.submit()">
                            <option value="">All Schemes</option>
                            <option value="unsubscribed" <?php echo $filterScheme === 'unsubscribed' ? 'selected' : ''; ?>>Unsubscribed</option>
                            <?php foreach ($availableSchemes as $scheme): ?>
                                <option value="<?php echo $scheme['SchemeID']; ?>" <?php echo $filterScheme == $scheme['SchemeID'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($scheme['SchemeName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-box" style="min-width: 150px;">
                        <select name="installment" id="installmentSelect" onchange="this.form.submit()">
                            <option value="">All Installments</option>
                            <?php foreach ($availableInstallments as $installment): ?>
                                <option value="<?php echo $installment['InstallmentID']; ?>" 
                                        data-scheme-id="<?php echo $installment['SchemeID']; ?>" 
                                        <?php echo $filterInstallment == $installment['InstallmentID'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($installment['InstallmentName']); ?> (<?php echo htmlspecialchars($installment['SchemeName']); ?> #<?php echo $installment['InstallmentNumber']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-box" style="min-width: 150px;">
                        <select name="payment_status" onchange="this.form.submit()">
                            <option value="">All Payment Statuses</option>
                            <option value="verified" <?php echo $filterPaymentStatus === 'verified' ? 'selected' : ''; ?>>Verified</option>
                            <option value="pending" <?php echo $filterPaymentStatus === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="rejected" <?php echo $filterPaymentStatus === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="unpaid" <?php echo $filterPaymentStatus === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary" style="min-width: 100px;">
                        <i class="fas fa-search"></i>
                        Search
                    </button>
                    <?php if (!empty($searchQuery) || !empty($filterStatus) || !empty($filterScheme) || !empty($filterInstallment) || !empty($filterPaymentStatus)): ?>
                        <a href="?" class="btn-primary" style="background: var(--text-secondary); min-width: 100px;">
                            <i class="fas fa-times"></i>
                            Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="customers-container">
                <?php if (!empty($searchQuery) || !empty($filterStatus) || !empty($filterScheme) || !empty($filterInstallment) || !empty($filterPaymentStatus)): ?>
                    <div style="margin-bottom: 20px; padding: 10px 15px; background: var(--primary-light); border-radius: 10px; color: var(--primary-color); font-size: 14px;">
                        <i class="fas fa-filter"></i>
                        Showing <?php echo count($customers); ?> customer<?php echo count($customers) !== 1 ? 's' : ''; ?> 
                        <?php 
                        $filterDescriptions = [];
                        if (!empty($searchQuery)) {
                            $filterDescriptions[] = 'matching "' . htmlspecialchars($searchQuery) . '"';
                        }
                        if (!empty($filterStatus)) {
                            $filterDescriptions[] = 'with status "' . htmlspecialchars($filterStatus) . '"';
                        }
                        if (!empty($filterScheme)) {
                            $schemeName = '';
                            foreach ($availableSchemes as $scheme) {
                                if ($scheme['SchemeID'] == $filterScheme) {
                                    $schemeName = $scheme['SchemeName'];
                                    break;
                                }
                            }
                            $filterDescriptions[] = 'in scheme "' . htmlspecialchars($schemeName) . '"';
                        }
                        if (!empty($filterInstallment)) {
                            $installmentName = '';
                            foreach ($availableInstallments as $installment) {
                                if ($installment['InstallmentID'] == $filterInstallment) {
                                    $installmentName = $installment['InstallmentName'] . ' (' . $installment['SchemeName'] . ' #' . $installment['InstallmentNumber'] . ')';
                                    break;
                                }
                            }
                            $filterDescriptions[] = 'in installment "' . htmlspecialchars($installmentName) . '"';
                        }
                        if (!empty($filterPaymentStatus)) {
                            $filterDescriptions[] = 'with payment status "' . htmlspecialchars($filterPaymentStatus) . '"';
                        }
                        echo implode(', ', $filterDescriptions);
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($customers)): ?>
                    <div class="no-customers">
                        <i class="fas fa-users"></i>
                        <h3>No Customers Found</h3>
                        <p>No customers match your current search criteria.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($customers as $customer): ?>
                        <div class="customer-card">
                            <div class="customer-header" onclick="toggleCustomerDetails(<?php echo $customer['CustomerID']; ?>)">
                                <div class="customer-avatar">
                                    <img src="<?php 
                                        if (!empty($customer['ProfileImageURL']) && $customer['ProfileImageURL'] !== '-'): 
                                            echo '../../' . htmlspecialchars($customer['ProfileImageURL']);
                                        else:
                                            echo '../image.png';
                                        endif;
                                    ?>" alt="Customer Avatar">
                                </div>
                                <div class="customer-info">
                                    <h3 class="customer-name"><?php echo htmlspecialchars($customer['Name']); ?></h3>
                                    <div class="customer-details">
                                        <div class="customer-detail">
                                            <i class="fas fa-id-badge"></i>
                                            <?php echo htmlspecialchars($customer['CustomerUniqueID']); ?>
                                        </div>
                                        <div class="customer-detail">
                                            <i class="fas fa-phone"></i>
                                            <?php echo htmlspecialchars($customer['Contact']); ?>
                                        </div>
                                        <div class="customer-detail">
                                            <i class="fas fa-envelope"></i>
                                            <?php echo htmlspecialchars($customer['Email'] ?: 'N/A'); ?>
                                        </div>
                                    </div>
                                    <span class="customer-status status-<?php echo strtolower($customer['Status']); ?>">
                                        <?php echo htmlspecialchars($customer['Status']); ?>
                                    </span>
                                    <div class="customer-payment-stats">
                                        <?php 
                                        $paymentStats = $customer['payment_stats'] ?? [];
                                        $hasPayments = false;
                                        ?>
                                        <?php if (isset($paymentStats['verified']) && $paymentStats['verified']['count'] > 0): ?>
                                            <span class="payment-stat-badge verified" title="Verified Payments: <?php echo $paymentStats['verified']['count']; ?>">
                                                <i class="fas fa-check-circle"></i> <?php echo $paymentStats['verified']['count']; ?>
                                            </span>
                                            <?php $hasPayments = true; ?>
                                        <?php endif; ?>
                                        <?php if (isset($paymentStats['pending']) && $paymentStats['pending']['count'] > 0): ?>
                                            <span class="payment-stat-badge pending" title="Pending Payments: <?php echo $paymentStats['pending']['count']; ?>">
                                                <i class="fas fa-clock"></i> <?php echo $paymentStats['pending']['count']; ?>
                                            </span>
                                            <?php $hasPayments = true; ?>
                                        <?php endif; ?>
                                        <?php if (isset($paymentStats['rejected']) && $paymentStats['rejected']['count'] > 0): ?>
                                            <span class="payment-stat-badge rejected" title="Rejected Payments: <?php echo $paymentStats['rejected']['count']; ?>">
                                                <i class="fas fa-times-circle"></i> <?php echo $paymentStats['rejected']['count']; ?>
                                            </span>
                                            <?php $hasPayments = true; ?>
                                        <?php endif; ?>
                                        <?php if (isset($paymentStats['unpaid']) && $paymentStats['unpaid']['count'] > 0): ?>
                                            <span class="payment-stat-badge unpaid" title="Unpaid Installments: <?php echo $paymentStats['unpaid']['count']; ?>">
                                                <i class="fas fa-exclamation-triangle"></i> <?php echo $paymentStats['unpaid']['count']; ?>
                                            </span>
                                            <?php $hasPayments = true; ?>
                                        <?php endif; ?>
                                        <?php if (!$hasPayments): ?>
                                            <span class="payment-stat-badge no-payments" title="No payments recorded">
                                                <i class="fas fa-info-circle"></i> No payments
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="customer-actions-group">
                                    <!-- <div class="edit-btn-inline" onclick="event.stopPropagation();">
                                        <a href="../Customers/edit.php?id=<?php echo $customer['CustomerID']; ?>" class="btn-edit-icon" title="Edit Customer">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div> -->
                                    <div class="expand-icon" id="expand-icon-<?php echo $customer['CustomerID']; ?>">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="customer-details-expanded" id="details-<?php echo $customer['CustomerID']; ?>">
                                <div class="details-content">
                                    <!-- Customer Details Section -->
                                    <div class="details-section">
                                        <h4><i class="fas fa-user"></i> Customer Details</h4>
                                        <div class="customer-details-grid">
                                            <div class="detail-card">
                                                <div class="detail-card-header">
                                                    <i class="fas fa-user-circle"></i>
                                                    <span>Personal Information</span>
                                                </div>
                                                <div class="detail-card-content">
                                                    <div class="detail-row">
                                                        <span class="detail-label">Full Name:</span>
                                                        <span class="detail-value"><?php echo htmlspecialchars($customer['Name']); ?></span>
                                                    </div>
                                                    <div class="detail-row">
                                                        <span class="detail-label">Customer ID:</span>
                                                        <span class="detail-value"><?php echo htmlspecialchars($customer['CustomerUniqueID']); ?></span>
                                                    </div>
                                                    <div class="detail-row">
                                                        <span class="detail-label">Date of Birth:</span>
                                                        <span class="detail-value"><?php echo $customer['DateOfBirth'] ? date('d M Y', strtotime($customer['DateOfBirth'])) : 'Not provided'; ?></span>
                                                    </div>
                                                    <div class="detail-row">
                                                        <span class="detail-label">Gender:</span>
                                                        <span class="detail-value"><?php echo htmlspecialchars($customer['Gender'] ?: 'Not specified'); ?></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="detail-card">
                                                <div class="detail-card-header">
                                                    <i class="fas fa-address-book"></i>
                                                    <span>Contact Information</span>
                                                </div>
                                                <div class="detail-card-content">
                                                    <div class="detail-row">
                                                        <span class="detail-label">Contact Number:</span>
                                                        <span class="detail-value"><?php echo htmlspecialchars($customer['Contact']); ?></span>
                                                    </div>
                                                    <div class="detail-row">
                                                        <span class="detail-label">Email Address:</span>
                                                        <span class="detail-value"><?php echo htmlspecialchars($customer['Email'] ?: 'Not provided'); ?></span>
                                                    </div>
                                                    <div class="detail-row">
                                                        <span class="detail-label">Address:</span>
                                                        <span class="detail-value"><?php echo htmlspecialchars($customer['Address'] ?: 'Not provided'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Installment Payment Status Section -->
                                    <div class="details-section">
                                        <h4><i class="fas fa-money-bill-wave"></i> Installment Payment Status</h4>
                                        <table class="details-table">
                                            <thead>
                                                <tr>
                                                    <th>Scheme</th>
                                                    <th>Installment</th>
                                                    <th>Draw Date</th>
                                                    <th>Status</th>
                                                    <th>Amount</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($customer['installments'])): ?>
                                                    <tr>
                                                        <td colspan="6" style="text-align: center; color: var(--text-secondary); padding: 20px;">
                                                            No installments found
                                                        </td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($customer['installments'] as $installment): ?>
                                                        <tr>
                                                            <td class="detail-value"><?php echo htmlspecialchars($installment['SchemeName']); ?></td>
                                                            <td class="detail-value">
                                                                <?php echo htmlspecialchars($installment['InstallmentName']); ?>
                                                                <div style="font-size: 12px; color: var(--text-secondary);">#<?php echo $installment['InstallmentNumber']; ?></div>
                                                            </td>
                                                            <td class="detail-value"><?php echo date('d M Y', strtotime($installment['DrawDate'])); ?></td>
                                                            <td class="detail-value">
                                                                <?php if ($installment['PaymentID']): ?>
                                                                    <span class="payment-status <?php echo strtolower($installment['PaymentStatus']); ?>">
                                                                        <?php echo htmlspecialchars($installment['PaymentStatus']); ?>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="payment-status unpaid">Not Paid</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="detail-value">
                                                                <?php if ($installment['PaymentID']): ?>
                                                                    ₹<?php echo number_format($installment['Amount'], 2); ?>
                                                                <?php else: ?>
                                                                    -
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="detail-value">
                                                                <?php if ($installment['PaymentID']): ?>
                                                                    <?php echo date('d M Y', strtotime($installment['SubmittedAt'])); ?>
                                                                <?php else: ?>
                                                                    -
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination at the very bottom -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    // Build query string for pagination links
                    $queryParams = $_GET;
                    unset($queryParams['page']); // Remove page parameter
                    $queryString = http_build_query($queryParams);
                    $queryString = !empty($queryString) ? '&' . $queryString : '';
                    ?>

                    <?php if ($page > 1): ?>
                        <a href="?page=1<?php echo $queryString; ?>">&laquo;</a>
                        <a href="?page=<?php echo $page - 1 . $queryString; ?>">&lsaquo;</a>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="?page=<?php echo $i . $queryString; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1 . $queryString; ?>">&rsaquo;</a>
                        <a href="?page=<?php echo $totalPages . $queryString; ?>">&raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Ensure proper topbar integration
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const content = document.querySelector('.content-wrapper');

            function adjustContent() {
                if (sidebar.classList.contains('collapsed')) {
                    content.style.marginLeft = 'var(--sidebar-collapsed-width)';
                } else {
                    content.style.marginLeft = 'var(--sidebar-width)';
                }
            }

            // Initial adjustment
            adjustContent();

            // Watch for sidebar changes
            const observer = new MutationObserver(adjustContent);
            observer.observe(sidebar, { attributes: true });

            // Enhanced filter functionality
            const searchInput = document.querySelector('input[name="search"]');
            const statusSelect = document.querySelector('select[name="status"]');
            const schemeSelect = document.querySelector('select[name="scheme"]');
            const installmentSelect = document.querySelector('select[name="installment"]');
            const paymentStatusSelect = document.querySelector('select[name="payment_status"]');
            const searchForm = document.querySelector('.search-filter-form');

            // Add loading state to form submission
            searchForm.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
                submitBtn.disabled = true;
                
                // Re-enable after a short delay (in case of errors)
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 3000);
            });

            // Auto-submit on filter changes (with debounce for search)
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (this.value.length >= 2 || this.value.length === 0) {
                        searchForm.submit();
                    }
                }, 500);
            });

            // Auto-submit on status change
            statusSelect.addEventListener('change', function() {
                searchForm.submit();
            });

            // Auto-submit on scheme change
            schemeSelect.addEventListener('change', function() {
                // Filter installments based on selected scheme
                filterInstallmentsByScheme(this.value);
                // Don't auto-submit for unsubscribed filter to allow manual filtering
                if (this.value !== 'unsubscribed') {
                    searchForm.submit();
                }
            });

            // Auto-submit on installment change
            installmentSelect.addEventListener('change', function() {
                searchForm.submit();
            });

            // Auto-submit on payment status change
            paymentStatusSelect.addEventListener('change', function() {
                searchForm.submit();
            });

            // Highlight active filters
            const activeFilters = [];
            if (searchInput.value) activeFilters.push('Search');
            if (statusSelect.value) activeFilters.push('Status');
            if (schemeSelect.value) activeFilters.push('Scheme');
            if (installmentSelect.value) activeFilters.push('Installment');
            if (paymentStatusSelect.value) activeFilters.push('Payment Status');

            if (activeFilters.length > 0) {
                const filterInfo = document.querySelector('.search-filter-container');
                const filterBadge = document.createElement('div');
                filterBadge.style.cssText = 'margin-top: 10px; font-size: 12px; color: var(--text-secondary);';
                filterBadge.innerHTML = `<i class="fas fa-filter"></i> Active filters: ${activeFilters.join(', ')}`;
                filterInfo.appendChild(filterBadge);
            }

            // Add tooltips to filter options
            const filterSelects = document.querySelectorAll('.filter-box select');
            filterSelects.forEach(select => {
                select.addEventListener('change', function() {
                    if (this.value) {
                        this.style.borderColor = 'var(--primary-color)';
                        this.style.backgroundColor = 'var(--primary-light)';
                    } else {
                        this.style.borderColor = 'var(--border-color)';
                        this.style.backgroundColor = 'white';
                    }
                });
                
                // Apply initial styling
                if (select.value) {
                    select.style.borderColor = 'var(--primary-color)';
                    select.style.backgroundColor = 'var(--primary-light)';
                }
            });

            // Function to filter installments based on selected scheme
            function filterInstallmentsByScheme(selectedSchemeId) {
                const installmentSelect = document.getElementById('installmentSelect');
                const options = installmentSelect.querySelectorAll('option');
                let visibleCount = 0;
                
                // Show/hide options based on scheme
                options.forEach(option => {
                    if (option.value === '') {
                        // Always show "All Installments" option
                        option.style.display = '';
                        visibleCount++;
                    } else {
                        const schemeId = option.getAttribute('data-scheme-id');
                        if (selectedSchemeId === '' || selectedSchemeId === 'unsubscribed' || schemeId === selectedSchemeId) {
                            option.style.display = '';
                            visibleCount++;
                        } else {
                            option.style.display = 'none';
                        }
                    }
                });
                
                // Reset installment selection if current selection doesn't match scheme
                if (selectedSchemeId !== '' && selectedSchemeId !== 'unsubscribed' && installmentSelect.value !== '') {
                    const selectedOption = installmentSelect.querySelector('option[value="' + installmentSelect.value + '"]');
                    if (selectedOption && selectedOption.getAttribute('data-scheme-id') !== selectedSchemeId) {
                        installmentSelect.value = '';
                    }
                }
                
                // Update placeholder text to show filtering status
                if (selectedSchemeId === 'unsubscribed') {
                    installmentSelect.setAttribute('data-original-placeholder', installmentSelect.options[0].text);
                    installmentSelect.options[0].text = 'All Installments (Unsubscribed customers have no installments)';
                } else if (selectedSchemeId !== '') {
                    const schemeName = schemeSelect.options[schemeSelect.selectedIndex].text;
                    installmentSelect.setAttribute('data-original-placeholder', installmentSelect.options[0].text);
                    if (visibleCount <= 1) {
                        installmentSelect.options[0].text = `No installments available for ${schemeName}`;
                    } else {
                        installmentSelect.options[0].text = `All Installments (${schemeName})`;
                    }
                } else {
                    if (installmentSelect.hasAttribute('data-original-placeholder')) {
                        installmentSelect.options[0].text = installmentSelect.getAttribute('data-original-placeholder');
                    }
                }
            }

            // Initialize installment filtering on page load
            filterInstallmentsByScheme(schemeSelect.value);
        });

        // Toggle customer details
        function toggleCustomerDetails(customerId) {
            const detailsElement = document.getElementById(`details-${customerId}`);
            const expandIcon = document.getElementById(`expand-icon-${customerId}`);
            
            if (detailsElement.classList.contains('expanded')) {
                // Collapse
                detailsElement.classList.remove('expanded');
                expandIcon.classList.remove('expanded');
            } else {
                // Expand
                detailsElement.classList.add('expanded');
                expandIcon.classList.add('expanded');
                
                // Load payments if not already loaded
                loadCustomerPayments(customerId);
            }
        }

        // Load customer payments via AJAX
        function loadCustomerPayments(customerId) {
            const paymentsContainer = document.getElementById(`details-${customerId}`);
            // Implementation of loadCustomerPayments function
        }
    </script>
</body>
</html>