<?php
// admin/payments/promoterCommision.php
$menuPath = "../";
require_once("../../config/config.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function fetchPromotersOfCustomer($customerUniqueID, $conn)
{
    $promoters = [];

    try {
        $stmt = $conn->prepare("SELECT PromoterID FROM Customers WHERE CustomerUniqueID = ?");
        $stmt->execute([$customerUniqueID]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer || empty($customer['PromoterID'])) {
            return [];
        }

        $currentPromoterID = trim($customer['PromoterID']);

        // Pre-load all promoters to build hierarchy matching string and numeric IDs
        $pStmt = $conn->prepare("SELECT PromoterID, PromoterUniqueID, ParentPromoterID, Commission, ParentCommission, Name, Contact FROM Promoters");
        $pStmt->execute();
        $allPromoters = $pStmt->fetchAll(PDO::FETCH_ASSOC);

        $promoterByRef = [];
        foreach ($allPromoters as $p) {
            $pID = (string)$p['PromoterID'];
            $uID = trim($p['PromoterUniqueID']);
            if (!empty($uID)) $promoterByRef[$uID] = $p;
            if (!empty($pID)) $promoterByRef[$pID] = $p;
        }

        $currRef = $currentPromoterID;
        $visited = [];

        while (!empty($currRef) && !isset($visited[$currRef])) {
            $visited[$currRef] = true;
            if (!isset($promoterByRef[$currRef])) break;
            $pData = $promoterByRef[$currRef];
            $promoters[] = $pData;
            $currRef = !empty($pData['ParentPromoterID']) ? trim($pData['ParentPromoterID']) : null;
        }
    } catch (Exception $e) {
        error_log("Error fetching promoters: " . $e->getMessage());
    }

    return $promoters;
}

function convertCommissionToInt($commission)
{
    return intval(preg_replace('/[^0-9]/', '', (string)$commission));
}

$error = null;
$customerDetails = null;
$promoterBreakdown = [];
$paymentAmount = 0;

try {
    $database = new Database();
    $conn = $database->getConnection();

    $customerUniqueID = isset($_GET['ref']) ? base64_decode($_GET['ref']) : '';
    if (empty($customerUniqueID)) {
        throw new Exception("Customer unique ID reference is required.");
    }

    require_once("../../config/commission_helper.php");
    processPromoterCommission($customerUniqueID, $conn);

    // Fetch customer details and payment info
    $stmt = $conn->prepare("
        SELECT c.*, s.SchemeName, p.Amount, p.PaymentID, p.VerifiedAt
        FROM Customers c 
        LEFT JOIN Payments p ON c.CustomerID = p.CustomerID 
        LEFT JOIN Schemes s ON p.SchemeID = s.SchemeID 
        WHERE c.CustomerUniqueID = ? 
        ORDER BY p.SubmittedAt DESC LIMIT 1
    ");
    $stmt->execute([$customerUniqueID]);
    $customerDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customerDetails) {
        throw new Exception("Customer details not found for ID: " . htmlspecialchars($customerUniqueID));
    }

    $paymentAmount = $customerDetails['Amount'] ?? 0;
    $promoters = fetchPromotersOfCustomer($customerUniqueID, $conn);

    if (!empty($promoters)) {
        for ($i = 0; $i < count($promoters); $i++) {
            $p = $promoters[$i];
            $uID = trim($p['PromoterUniqueID']);
            $numID = (string)$p['PromoterID'];

            // Get live wallet balance from PromoterWallet table
            $wStmt = $conn->prepare("SELECT BalanceAmount FROM PromoterWallet WHERE TRIM(PromoterUniqueID) = ? OR UserID = ?");
            $wStmt->execute([$uID, $numID]);
            $wRow = $wStmt->fetch(PDO::FETCH_ASSOC);
            $liveBal = floatval($wRow['BalanceAmount'] ?? 0);

            // Role label
            $role = ($i === 0) ? 'Direct Promoter' : (($i === 1) ? 'Parent Promoter' : 'Grandparent Promoter');

            // Commission credited for this payment
            $comm = 0;
            if ($i === 0) {
                $comm = convertCommissionToInt($p['Commission']);
            } else {
                $childComm = convertCommissionToInt($promoters[$i - 1]['Commission']);
                $parentComm = convertCommissionToInt($p['Commission']);
                if (!empty($promoters[$i - 1]['ParentCommission']) && convertCommissionToInt($promoters[$i - 1]['ParentCommission']) > 0) {
                    $comm = convertCommissionToInt($promoters[$i - 1]['ParentCommission']);
                } else if ($parentComm > $childComm) {
                    $comm = $parentComm - $childComm;
                }
            }

            $promoterBreakdown[] = [
                'role' => $role,
                'name' => $p['Name'],
                'id' => $uID,
                'contact' => $p['Contact'] ?? '',
                'commission_rate' => convertCommissionToInt($p['Commission']),
                'credited_amount' => $comm,
                'wallet_balance' => $liveBal
            ];
        }
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

include("../components/sidebar.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promoter Commission Summary</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .content-wrapper {
            padding: 25px;
            max-width: 1100px;
            margin: 0 auto;
            font-family: 'Poppins', sans-serif;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #2c3e50;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: #1a252f;
            transform: translateY(-2px);
        }

        .summary-card {
            background: white;
            border-radius: 14px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
        }

        .payment-banner {
            background: #e8f5e9;
            border-left: 5px solid #2ecc71;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .payment-banner h3 {
            color: #27ae60;
            margin: 0 0 10px 0;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .promoter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .promoter-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border-top: 4px solid #3498db;
            transition: all 0.3s ease;
        }

        .promoter-card.direct {
            border-top-color: #2ecc71;
        }

        .promoter-card.parent {
            border-top-color: #f39c12;
        }

        .promoter-role {
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #7f8c8d;
            margin-bottom: 6px;
        }

        .promoter-name {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 12px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #edf2f7;
            font-size: 14px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #718096;
        }

        .info-value {
            font-weight: 600;
            color: #2d3748;
        }

        .wallet-badge {
            background: #d1e7dd;
            color: #0f5132;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 16px;
        }

        .error-box {
            background: #fee2e2;
            border-left: 4px solid #e74c3c;
            padding: 15px;
            border-radius: 8px;
            color: #c0392b;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <a href="index.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Payments
        </a>

        <?php if (!empty($error)): ?>
            <div class="error-box">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($customerDetails): ?>
            <div class="payment-banner">
                <h3><i class="fas fa-check-circle"></i> Payment Verified & Commissions Updated</h3>
                <p style="margin: 0; color: #2c3e50; font-size: 15px;">
                    Customer: <strong><?php echo htmlspecialchars($customerDetails['Name']); ?></strong> 
                    (<code><?php echo htmlspecialchars($customerDetails['CustomerUniqueID']); ?></code>) | 
                    Scheme: <strong><?php echo htmlspecialchars($customerDetails['SchemeName']); ?></strong> | 
                    Amount Paid: <strong>₹<?php echo number_format($paymentAmount, 2); ?></strong>
                </p>
            </div>

            <div class="summary-card">
                <h2 style="font-size: 20px; color: #2c3e50; margin: 0 0 15px 0;">
                    <i class="fas fa-wallet me-2"></i> Promoters Updated Wallet Summary
                </h2>

                <?php if (!empty($promoterBreakdown)): ?>
                    <div class="promoter-grid">
                        <?php foreach ($promoterBreakdown as $idx => $p): ?>
                            <div class="promoter-card <?php echo ($idx === 0) ? 'direct' : 'parent'; ?>">
                                <div class="promoter-role"><?php echo htmlspecialchars($p['role']); ?></div>
                                <div class="promoter-name"><?php echo htmlspecialchars($p['name']); ?></div>
                                
                                <div class="info-row">
                                    <span class="info-label">Promoter Unique ID:</span>
                                    <span class="info-value"><code><?php echo htmlspecialchars($p['id']); ?></code></span>
                                </div>
                                
                                <div class="info-row">
                                    <span class="info-label">Base Commission Rate:</span>
                                    <span class="info-value">₹<?php echo number_format($p['commission_rate'], 2); ?></span>
                                </div>

                                <div class="info-row">
                                    <span class="info-label">Commission Credited:</span>
                                    <span class="info-value text-success" style="color: #27ae60;">+ ₹<?php echo number_format($p['credited_amount'], 2); ?></span>
                                </div>

                                <div class="info-row" style="margin-top: 10px; padding-top: 12px; border-top: 2px solid #e2e8f0;">
                                    <span class="info-label" style="font-weight: 600; color: #2c3e50;">Updated Wallet Balance:</span>
                                    <span class="wallet-badge">₹<?php echo number_format($p['wallet_balance'], 2); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: #7f8c8d; margin: 0;">No promoter associated with this customer record.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>