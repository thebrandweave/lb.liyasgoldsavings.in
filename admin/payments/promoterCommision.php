<?php
$menuPath = "../";
require_once("../../config/config.php");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if commission was already processed
if (isset($_SESSION['commission_processed']) && $_SESSION['commission_processed'] === true) {
    $_SESSION['info_message'] = "Commission has already been processed for this payment.";
    header("Location: index.php");
    exit();
}

// WhatsApp messaging feature removed

function fetchPromotersOfCustomer($customerUniqueID, $conn)
{
    $promoters = [];

    try {
        $stmt = $conn->prepare("SELECT PromoterID FROM Customers WHERE CustomerUniqueID = ?");
        $stmt->execute([$customerUniqueID]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            throw new Exception("No promoter found for the customer.");
        }

        $currentPromoterID = $customer['PromoterID'];

        while ($currentPromoterID) {
            $stmt = $conn->prepare("SELECT PromoterID, PromoterUniqueID, ParentPromoterID, Commission, ParentCommission, Name, Contact FROM Promoters WHERE PromoterUniqueID = ?");
            $stmt->execute([$currentPromoterID]);
            $promoter = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$promoter) {
                break;
            }

            $promoters[] = $promoter;
            $currentPromoterID = $promoter['ParentPromoterID'];
        }
    } catch (Exception $e) {
        error_log("Error fetching promoters: " . $e->getMessage());
    }

    return $promoters;
}

function convertCommissionToInt($commission)
{
    return intval(preg_replace('/[^0-9]/', '', $commission));
}

function updatePromoterWallet($promoters, $conn, $paymentAmount, $customerDetails)
{
    $directPromoter = $promoters[0];
    $commissionAmount = convertCommissionToInt($directPromoter['Commission']);

    $stmt = $conn->prepare("SELECT BalanceID FROM PromoterWallet WHERE PromoterUniqueID = ?");
    $stmt->execute([$directPromoter['PromoterUniqueID']]);
    $walletRecord = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($walletRecord) {
        $stmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = BalanceAmount + ?, LastUpdated = CURRENT_TIMESTAMP WHERE PromoterUniqueID = ?");
        $stmt->execute([$commissionAmount, $directPromoter['PromoterUniqueID']]);

        // Add wallet log for direct commission
        $logMessage = "Commission earned from customer " . $customerDetails['Name'] . " (" . $customerDetails['CustomerUniqueID'] . ") for " . $customerDetails['SchemeName'] . " scheme";
        $stmt = $conn->prepare("INSERT INTO WalletLogs (PromoterUniqueID, Amount, Message) VALUES (?, ?, ?)");
        $stmt->execute([$directPromoter['PromoterUniqueID'], $commissionAmount, $logMessage]);

        

        return ["type" => "update", "promoter" => $directPromoter, "amount" => $commissionAmount];
    } else {
        $stmt = $conn->prepare("INSERT INTO PromoterWallet (UserID, PromoterUniqueID, BalanceAmount, Message) VALUES (?, ?, ?, ?)");
        $message = "Commission from payment";
        $stmt->execute([$directPromoter['PromoterID'], $directPromoter['PromoterUniqueID'], $commissionAmount, $message]);

        // Add wallet log for new wallet creation
        $logMessage = "Initial wallet creation with commission from customer " . $customerDetails['Name'] . " (" . $customerDetails['CustomerUniqueID'] . ") for " . $customerDetails['SchemeName'] . " scheme";
        $stmt = $conn->prepare("INSERT INTO WalletLogs (PromoterUniqueID, Amount, Message) VALUES (?, ?, ?)");
        $stmt->execute([$directPromoter['PromoterUniqueID'], $commissionAmount, $logMessage]);

        

        return ["type" => "create", "promoter" => $directPromoter, "amount" => $commissionAmount];
    }
}

try {
    $database = new Database();
    $conn = $database->getConnection();
    $conn->beginTransaction();

    $customerUniqueID = base64_decode($_GET['ref']) ?? '';
    if (empty($customerUniqueID)) {
        throw new Exception("Customer unique ID is required");
    }

    // Fetch customer details
    $stmt = $conn->prepare("SELECT c.*, s.SchemeName, p.Amount 
                           FROM Customers c 
                           LEFT JOIN Payments p ON c.CustomerID = p.CustomerID 
                           LEFT JOIN Schemes s ON p.SchemeID = s.SchemeID 
                           WHERE c.CustomerUniqueID = ? 
                           ORDER BY p.SubmittedAt DESC LIMIT 1");
    $stmt->execute([$customerUniqueID]);
    $customerDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    $paymentAmount = $customerDetails['Amount'] ?? 850;
    $promoters = fetchPromotersOfCustomer($customerUniqueID, $conn);
    $walletUpdates = [];

    if (!empty($promoters)) {
        $directPromoterUpdate = updatePromoterWallet($promoters, $conn, $paymentAmount, $customerDetails);
        $walletUpdates[] = $directPromoterUpdate;

        for ($i = 0; $i < count($promoters) - 1; $i++) {
            $currentPromoter = $promoters[$i];
            $parentPromoter = $promoters[$i + 1];

            if (!empty($currentPromoter['ParentCommission'])) {
                $parentCommissionAmount = convertCommissionToInt($currentPromoter['ParentCommission']);

                $stmt = $conn->prepare("SELECT BalanceID FROM PromoterWallet WHERE PromoterUniqueID = ?");
                $stmt->execute([$parentPromoter['PromoterUniqueID']]);
                $parentWalletRecord = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($parentWalletRecord) {
                    $stmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = BalanceAmount + ?, LastUpdated = CURRENT_TIMESTAMP WHERE PromoterUniqueID = ?");
                    $stmt->execute([$parentCommissionAmount, $parentPromoter['PromoterUniqueID']]);

                    // Add wallet log for parent commission
                    $logMessage = "Parent commission earned from customer " . $customerDetails['Name'] . " (" . $customerDetails['CustomerUniqueID'] . ") for " . $customerDetails['SchemeName'] . " scheme";
                    $stmt = $conn->prepare("INSERT INTO WalletLogs (PromoterUniqueID, Amount, Message) VALUES (?, ?, ?)");
                    $stmt->execute([$parentPromoter['PromoterUniqueID'], $parentCommissionAmount, $logMessage]);

                    

                    $walletUpdates[] = ["type" => "update", "promoter" => $parentPromoter, "amount" => $parentCommissionAmount];
                } else {
                    $stmt = $conn->prepare("INSERT INTO PromoterWallet (UserID, PromoterUniqueID, BalanceAmount, Message) VALUES (?, ?, ?, ?)");
                    $message = "Parent commission from payment";
                    $stmt->execute([$parentPromoter['PromoterID'], $parentPromoter['PromoterUniqueID'], $parentCommissionAmount, $message]);

                    // Add wallet log for new parent wallet
                    $logMessage = "Initial wallet creation with parent commission from customer " . $customerDetails['Name'] . " (" . $customerDetails['CustomerUniqueID'] . ") for " . $customerDetails['SchemeName'] . " scheme";
                    $stmt = $conn->prepare("INSERT INTO WalletLogs (PromoterUniqueID, Amount, Message) VALUES (?, ?, ?)");
                    $stmt->execute([$parentPromoter['PromoterUniqueID'], $parentCommissionAmount, $logMessage]);

                    

                    $walletUpdates[] = ["type" => "create", "promoter" => $parentPromoter, "amount" => $parentCommissionAmount];
                }
            }
        }
    }

    $conn->commit();

    // Set the commission processed flag
    $_SESSION['commission_processed'] = true;
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    error_log("Error during promoter fetching or wallet update: " . $e->getMessage());
    $error = $e->getMessage();
}

include("../components/sidebar.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promoter Commission Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .content-wrapper {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .commission-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .commission-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .wallet-update {
            background: #e8f5e9;
            border-left: 4px solid #2ecc71;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .wallet-update.create {
            background: #e3f2fd;
            border-left-color: #3498db;
        }

        .wallet-update.update {
            background: #e8f5e9;
            border-left-color: #2ecc71;
        }

        .wallet-message {
            font-size: 14px;
            color: #2c3e50;
        }

        .wallet-amount {
            font-weight: 600;
            color: #27ae60;
            margin-top: 5px;
        }

        .error-message {
            background: #fee2e2;
            border-left: 4px solid #e74c3c;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #c0392b;
        }

        .success-message {
            background: #e8f5e9;
            border-left: 4px solid #2ecc71;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #27ae60;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .payment-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #3498db;
        }

        .payment-info h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .payment-info p {
            color: #34495e;
            line-height: 1.6;
            margin: 0;
        }

        .total-amount {
            font-weight: 600;
            color: #2c3e50;
            margin-top: 10px;
        }

        .info-message {
            background: #e3f2fd;
            border-left: 4px solid #3498db;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #2980b9;
        }

        .auto-redirect {
            text-align: center;
            margin-top: 20px;
            color: #7f8c8d;
            font-size: 14px;
        }

        .countdown {
            font-weight: 600;
            color: #3498db;
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <a href="index.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Payments
        </a>

        <div class="page-header">
            <h1 class="page-title">Promoter Commission Management</h1>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($promoters)): ?>
            <div class="commission-card">
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> No promoters found for this customer.
                </div>
            </div>
        <?php else: ?>
            <div class="payment-info">
                <h3>Payment Information</h3>
                <p>
                    Customer <strong><?php echo htmlspecialchars($customerDetails['CustomerUniqueID']); ?> - <?php echo htmlspecialchars($customerDetails['Name']); ?></strong>
                    has made their first payment to the <strong><?php echo htmlspecialchars($customerDetails['SchemeName']); ?></strong> scheme.
                </p>
                <p class="total-amount">
                    Total Payment Amount: ₹<?php echo number_format($paymentAmount, 2); ?>
                </p>
            </div>

            <?php if (!empty($walletUpdates)): ?>
                <div class="commission-card">
                    <h2 style="margin-bottom: 20px; color: #2c3e50;">Wallet Updates</h2>
                    <?php foreach ($walletUpdates as $update): ?>
                        <div class="wallet-update <?php echo $update['type']; ?>">
                            <div class="wallet-message">
                                <?php if ($update['type'] === 'create'): ?>
                                    <i class="fas fa-plus-circle"></i> Created new wallet for Promoter: <?php echo htmlspecialchars($update['promoter']['Name']); ?> (ID: <?php echo htmlspecialchars($update['promoter']['PromoterUniqueID']); ?>)
                                <?php else: ?>
                                    <i class="fas fa-sync-alt"></i> Updated wallet for Promoter: <?php echo htmlspecialchars($update['promoter']['Name']); ?> (ID: <?php echo htmlspecialchars($update['promoter']['PromoterUniqueID']); ?>)
                                <?php endif; ?>
                            </div>
                            <div class="wallet-amount">
                                Amount: ₹<?php echo number_format($update['amount'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- <div class="auto-redirect">
                    <p>You will be redirected to the payments page in <span class="countdown">15</span> seconds...</p>
                </div> -->
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- <script>
        // Auto redirect after 5 seconds
        let countdown = 15;
        const countdownElement = document.querySelector('.countdown');

        if (countdownElement) {
            const timer = setInterval(() => {
                countdown--;
                countdownElement.textContent = countdown;

                if (countdown <= 0) {
                    clearInterval(timer);
                    window.location.href = 'index.php';
                }
            }, 1000);
        }
    </script> -->
</body>

</html>