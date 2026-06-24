<?php
session_start();

// Check if promoter is logged in
if (!isset($_SESSION['promoter_id'])) {
    header("Location: ../login.php");
    exit();
}

$menuPath = "../";
$currentPage = "withdrawals";

// Database connection
require_once("../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

$message = '';
$messageType = '';

try {
    // Get promoter's PromoterUniqueID (same as dashboard)
    $stmt = $conn->prepare("SELECT PromoterUniqueID FROM Promoters WHERE PromoterID = ?");
    $stmt->execute([$_SESSION['promoter_id']]);
    $promoter = $stmt->fetch(PDO::FETCH_ASSOC);
    $promoterUniqueID = $promoter ? $promoter['PromoterUniqueID'] : null;

    // --- ADDED: Fetch Top 10 Earners dataset safely for the popup component ---
    $popupEarners = [];
    if ($promoterUniqueID) {
        $popupQuery = "
            SELECT Name, PromoterUniqueID, TotalEarnings, ProfileImageURL 
            FROM WeeklyTopEarners 
            WHERE WeekStartDate = (SELECT MAX(WeekStartDate) FROM WeeklyTopEarners)
            ORDER BY RankNo ASC 
            LIMIT 10
        ";
        $popupStmt = $conn->prepare($popupQuery);
        $popupStmt->execute();
        $popupEarners = $popupStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get promoter's wallet balance by PromoterUniqueID (same as dashboard - UserID can be NULL in some rows)
    $wallet = null;
    if ($promoterUniqueID) {
        $stmt = $conn->prepare("
            SELECT 
                pw.BalanceAmount,
                pw.Message,
                pw.LastUpdated,
                (
                    SELECT COUNT(*) 
                    FROM WalletLogs wl 
                    WHERE wl.PromoterUniqueID = pw.PromoterUniqueID
                ) as TotalTransactions
            FROM PromoterWallet pw
            WHERE pw.PromoterUniqueID = ?
        ");
        $stmt->execute([$promoterUniqueID]);
        $wallet = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Set default values if no wallet found
    $balance = $wallet ? $wallet['BalanceAmount'] : 0;
    $walletMessage = $wallet ? $wallet['Message'] : '';
    $lastUpdated = $wallet ? $wallet['LastUpdated'] : null;
    $totalTransactions = $wallet ? $wallet['TotalTransactions'] : 0;
    // Debug log
    error_log("Promoter ID from session: " . $_SESSION['promoter_id']);
    error_log("Promoter Unique ID from wallet: " . $promoterUniqueID);

    // Pagination params for full history
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    // Get small recent snapshot for the balance card
    $stmt = $conn->prepare("
        SELECT Amount, Message, CreatedAt, TransactionType
        FROM WalletLogs 
        WHERE PromoterUniqueID = ?
        ORDER BY CreatedAt DESC
        LIMIT 5
    ");
    $stmt->execute([$promoterUniqueID]);
    $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get paginated full transactions list for the history table
    $stmt = $conn->prepare("
        SELECT Amount, Message, CreatedAt, TransactionType
        FROM WalletLogs 
        WHERE PromoterUniqueID = :puid
        ORDER BY CreatedAt DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':puid', $promoterUniqueID, PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalPages = $totalTransactions > 0 ? (int)ceil($totalTransactions / $perPage) : 1;

    // Get promoter's bank details
    $stmt = $conn->prepare("
        SELECT BankAccountName, BankAccountNumber, IFSCCode, BankName 
        FROM Promoters 
        WHERE PromoterID = ?
    ");
    $stmt->execute([$_SESSION['promoter_id']]);
    $bankDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    // Handle withdrawal request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['withdraw_amount'])) {
        $amount = floatval($_POST['withdraw_amount']);

        // Validate amount
        if ($amount <= 0) {
            throw new Exception("Invalid withdrawal amount.");
        }

        if ($amount > $balance) {
            throw new Exception("Insufficient balance.");
        }

        // Start transaction
        $conn->beginTransaction();

        try {
            // Debug log before withdrawal
            error_log("Creating withdrawal with PromoterUniqueID: " . $promoterUniqueID);

            // Create withdrawal request
            $stmt = $conn->prepare("
                INSERT INTO Withdrawals (UserID, UserType, Amount, Status, Remarks)
                VALUES (?, 'Promoter', ?, 'Pending', ?)
            ");
            $remarks = "Withdrawal request for ₹" . number_format($amount, 2);
            $stmt->execute([$_SESSION['promoter_id'], $amount, $remarks]);

            // Update wallet balance (by PromoterUniqueID to match wallet lookup)
            $stmt = $conn->prepare("
                UPDATE PromoterWallet 
                SET BalanceAmount = BalanceAmount - ?,
                    LastUpdated = CURRENT_TIMESTAMP
                WHERE PromoterUniqueID = ?
            ");
            $stmt->execute([$amount, $promoterUniqueID]);

            // Add wallet log entry
            $stmt = $conn->prepare("
                INSERT INTO WalletLogs (PromoterUniqueID, Amount, Message, TransactionType)
                VALUES (?, ?, ?, 'Debit')
            ");
            $logMessage = "Withdrawal request of ₹" . number_format($amount, 2);
            $stmt->execute([$promoterUniqueID, -$amount, $logMessage]);

            // Add activity log
            $stmt = $conn->prepare("
                INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress)
                VALUES (?, 'Promoter', ?, ?)
            ");
            $action = "Requested withdrawal of ₹" . number_format($amount, 2);
            $stmt->execute([$_SESSION['promoter_id'], $action, $_SERVER['REMOTE_ADDR']]);

            // Commit transaction
            $conn->commit();

            $message = "Withdrawal request submitted successfully.";
            $messageType = "success";

            // Refresh the page to show updated balance
            header("Location: index.php?success=1");
            exit();
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollBack();
            throw new Exception("Failed to process withdrawal: " . $e->getMessage());
        }
    }
} catch (Exception $e) {
    $message = $e->getMessage();
    $messageType = "error";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdrawals | Golden Dreams</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Kannada:wght@400;500;600&display=swap" rel="stylesheet">
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
            --transition-speed: 0.3s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--text-primary);
            line-height: 1.6;
        }

        .content-wrapper {
            padding: 24px;
            margin-left: var(--sidebar-width);
            transition: margin-left var(--transition-speed) ease;
            padding-top: calc(var(--topbar-height) + 24px) !important;
        }

        .main-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .balance-card {
            background: white;
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 24px;
            box-shadow: var(--card-shadow);
            text-align: center;
            transition: transform var(--transition-speed) ease;
            animation: slideIn 0.5s ease;
        }

        .balance-card:hover {
            transform: translateY(-5px);
        }

        .balance-title {
            font-size: 18px;
            color: var(--text-secondary);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .balance-title i {
            color: var(--primary-color);
        }

        .balance-amount {
            font-size: 42px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 24px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .withdraw-form {
            background: white;
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 24px;
            box-shadow: var(--card-shadow);
            animation: slideIn 0.5s ease 0.1s backwards;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 15px;
        }

        .form-control {
            width: 100%;
            padding: 14px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 15px;
            transition: all var(--transition-speed) ease;
            background: var(--bg-light);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-light);
            background: white;
        }

        .btn {
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--primary-color);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 106, 80, 0.2);
        }

        .btn:disabled {
            background: var(--border-color);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .withdrawal-history {
            background: white;
            border-radius: 20px;
            padding: 32px;
            box-shadow: var(--card-shadow);
            animation: slideIn 0.5s ease 0.2s backwards;
        }

        .history-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 24px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .history-title i {
            color: var(--primary-color);
        }

        .history-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .history-table th,
        .history-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .history-table th {
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .history-table tr:hover {
            background: var(--bg-light);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-pending {
            background: rgba(241, 196, 15, 0.1);
            color: #f1c40f;
        }

        .status-approved {
            background: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }

        .status-rejected {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .alert {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.5s ease;
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
            border: 1px solid rgba(46, 204, 113, 0.2);
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.2);
        }

        /* --- ADDED: Top 10 Earners Modal Box Design Tokens --- */
        .earners-popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .earners-popup-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .earners-popup-box {
            background: #ffffff;
            width: 90%;
            max-width: 440px;
            border-radius: 20px;
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.2);
            padding: 24px;
            transform: scale(0.9) translateY(20px);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            max-height: 80vh;
            display: flex;
            flex-direction: column;
        }

        .earners-popup-overlay.show .earners-popup-box {
            transform: scale(1) translateY(0);
        }

        .popup-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 16px;
        }

        .popup-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .popup-close-btn {
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-secondary);
            transition: all 0.2s ease;
        }

        .popup-close-btn:hover {
            background: #e2e8f0;
            color: var(--text-primary);
        }

        .popup-list-scroll {
            overflow-y: auto;
            flex: 1;
            padding-right: 4px;
        }

        .popup-list-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .popup-list-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .popup-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 7px 12px;
            border-radius: 12px;
            margin-bottom: 8px;
            background: var(--bg-light);
        }

        .popup-row-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .popup-rank {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-secondary);
            width: 22px;
        }

        .popup-row:nth-child(1) .popup-rank { color: #f59e0b; }
        .popup-row:nth-child(2) .popup-rank { color: #94a3b8; }
        .popup-row:nth-child(3) .popup-rank { color: #b45309; }

        .popup-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .popup-meta h4 {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .popup-meta p {
            font-size: 11px;
            color: var(--text-secondary);
            margin: 0;
        }

        .popup-earnings {
            font-size: 14px;
            font-weight: 700;
            color: var(--primary-color);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .content-wrapper {
                padding: 16px;
            }

            .balance-card,
            .withdraw-form,
            .withdrawal-history {
                padding: 24px;
                border-radius: 16px;
            }

            .balance-amount {
                font-size: 32px;
            }

            .history-table {
                display: block;
                overflow-x: auto;
            }
        }

        /* Testing notice styles */
        .testing-notice {
            background: linear-gradient(135deg, #ffd700, #ffa500);
            color: #000;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            box-shadow: var(--shadow-sm);
            animation: pulse 2s infinite;
        }

        .testing-notice i {
            font-size: 20px;
            margin-top: 2px;
        }

        .notice-content {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .notice-text {
            font-size: 14px;
            font-weight: 500;
            line-height: 1.4;
        }

        .notice-text.kannada {
            font-family: 'Noto Sans Kannada', sans-serif;
            font-size: 13px;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }

            100% {
                opacity: 1;
            }
        }
    </style>
</head>

<body>
    <?php include('../components/sidebar.php'); ?>
    <?php include('../components/topbar.php'); ?>

    <div class="earners-popup-overlay" id="topEarnersPopup">
        <div class="earners-popup-box">
            <div class="popup-header">
                <div class="popup-title">
                    <i class="fas fa-trophy" style="color: #f59e0b;"></i>
                    <span>Top Weekly Earners</span>
                </div>
                <button class="popup-close-btn" id="closePopupBtn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="popup-list-scroll">
                <?php 
                if (!empty($popupEarners)):
                    $rank = 1;
                    foreach ($popupEarners as $earner): 
                        $imgUrl = !empty($earner['ProfileImageURL']) ? $earner['ProfileImageURL'] : '../assets/images/default-user.png';
                ?>
                        <div class="popup-row">
                            <div class="popup-row-left">
                                <div class="popup-rank">#<?= sprintf("%02d", $rank) ?></div>
                                <img src="<?= htmlspecialchars($imgUrl) ?>" class="popup-avatar" alt="User">
                                <div class="popup-meta">
                                    <h4><?= htmlspecialchars($earner['Name']) ?></h4>
                                    <p>ID: <?= htmlspecialchars($earner['PromoterUniqueID']) ?></p>
                                </div>
                            </div>
                            <div class="popup-earnings">₹<?= number_format($earner['TotalEarnings'], 2) ?></div>
                        </div>
                <?php 
                        $rank++;
                    endforeach; 
                else:
                ?>
                    <p style="text-align: center; font-size: 13px; color: var(--text-secondary); padding: 20px;">No performance logs recorded for this frame.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="content-wrapper">
    <div class="main-content">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="balance-card">
            <h2 class="balance-title">
                <i class="fas fa-wallet"></i>
                Available Balance
            </h2>
            <div class="balance-amount">₹<?php echo number_format($balance, 2); ?></div>
            <?php if ($walletMessage): ?>
                <div style="color: var(--text-secondary); font-size: 14px; margin-top: 8px;">
                    <?php echo htmlspecialchars($walletMessage); ?>
                </div>
            <?php endif; ?>
            <?php if ($lastUpdated): ?>
                <div style="color: var(--text-secondary); font-size: 12px; margin-top: 4px;">
                    Last updated: <?php echo date('d M Y, h:i A', strtotime($lastUpdated)); ?>
                </div>
            <?php endif; ?>
            <div style="color: var(--text-secondary); font-size: 12px; margin-top: 4px;">
                Total Transactions: <?php echo number_format($totalTransactions); ?>
            </div>

            <?php if (!empty($recentTransactions)): ?>
                <div style="margin-top: 24px; border-top: 1px solid var(--border-color); padding-top: 24px;">
                    <h3 style="font-size: 14px; color: var(--text-secondary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-history"></i>
                        Recent Transactions
                    </h3>
                    <?php foreach ($recentTransactions as $transaction): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; font-size: 13px; padding: 8px; border-radius: 8px; background: var(--bg-light);">
                            <span style="color: var(--text-secondary);">
                                <?php echo date('d M Y', strtotime($transaction['CreatedAt'])); ?>
                            </span>
                            <span style="color: <?php echo $transaction['TransactionType'] === 'Debit' ? 'var(--error-color)' : 'var(--success-color)'; ?>; font-weight: 500;">
                                <?php echo ($transaction['TransactionType'] === 'Credit' ? '+' : '') . '₹' . number_format($transaction['Amount'], 2); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="withdraw-form">
            <h2 class="history-title">
                <i class="fas fa-money-bill-wave"></i>
                Request Withdrawal
            </h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="withdraw_amount">Withdrawal Amount</label>
                    <input type="number" id="withdraw_amount" name="withdraw_amount" class="form-control"
                        min="1" max="<?php echo $balance; ?>" step="0.01" required>
                </div>
                <button type="submit" class="btn" <?php echo $balance <= 0 ? 'disabled' : ''; ?>>
                    <i class="fas fa-money-bill-wave"></i>
                    Request Withdrawal
                </button>
            </form>
        </div>

        <div class="withdrawal-history">
            <h2 class="history-title">
                <i class="fas fa-history"></i>
                Transaction History
            </h2>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 32px; color: var(--text-secondary);">
                                <i class="fas fa-inbox" style="font-size: 24px; margin-bottom: 8px;"></i>
                                <p>No transaction history found</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?php echo date('d M Y, h:i A', strtotime($transaction['CreatedAt'])); ?></td>
                                <td style="color: <?php echo $transaction['TransactionType'] === 'Debit' ? 'var(--error-color)' : 'var(--success-color)'; ?>">
                                    <?php echo ($transaction['TransactionType'] === 'Credit' ? '+' : '') . '₹' . number_format($transaction['Amount'], 2); ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($transaction['TransactionType']); ?>">
                                        <i class="fas fa-<?php echo $transaction['TransactionType'] === 'Credit' ? 'arrow-down' : 'arrow-up'; ?>"></i>
                                        <?php echo $transaction['TransactionType']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($transaction['Message']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if ($totalPages > 1): ?>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px;">
                    <div style="font-size:13px; color: var(--text-secondary);">
                        Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <?php if ($page > 1): ?>
                            <a class="btn" href="?page=<?php echo $page - 1; ?>" style="text-decoration:none;">Prev</a>
                        <?php endif; ?>
                        <?php if ($page < $totalPages): ?>
                            <a class="btn" href="?page=<?php echo $page + 1; ?>" style="text-decoration:none;">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- ADDED: Auto-trigger pop-up logic engine ---
            const popupOverlay = document.getElementById('topEarnersPopup');
            const closePopupBtn = document.getElementById('closePopupBtn');

            // Wait 300ms for clean entrance fade-in
            setTimeout(() => {
                if (popupOverlay) {
                    popupOverlay.classList.add('show');
                }
            }, 300);

            // Close listeners
            if (closePopupBtn) {
                closePopupBtn.addEventListener('click', () => {
                    popupOverlay.classList.remove('show');
                });
            }

            if (popupOverlay) {
                popupOverlay.addEventListener('click', (e) => {
                    if (e.target === popupOverlay) {
                        popupOverlay.classList.remove('show');
                    }
                });
            }

            // Existing content wrapper spacing calculator logic
            const sidebar = document.getElementById('sidebar');
            const content = document.querySelector('.content-wrapper');

            function adjustContent() {
                if (sidebar.classList.contains('collapsed')) {
                    content.style.marginLeft = 'var(--sidebar-collapsed-width)';
                } else {
                    content.style.marginLeft = 'var(--sidebar-width)';
                }
            }

            adjustContent();
            const observer = new MutationObserver(adjustContent);
            observer.observe(sidebar, {
                attributes: true
            });
        });
    </script>
</body>

</html>