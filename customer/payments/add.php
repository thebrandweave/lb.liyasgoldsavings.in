<?php
require_once '../config/config.php';
require_once '../config/session_check.php';
$c_path = "../";
$current_page = "payments";

$userData = checkSession();
$customerId = (int) $userData['customer_id'];

$database = new Database();
$db = $database->getConnection();

// Get all active schemes
$stmt = $db->prepare("
    SELECT SchemeID, SchemeName, MonthlyPayment, TotalPayments, StartDate
    FROM Schemes
    WHERE Status = 'Active'
    ORDER BY SchemeName ASC
");
$stmt->execute();
$schemes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get installments per scheme (all active installments)
$schemesWithInstallments = [];
foreach ($schemes as $scheme) {
    $stmt = $db->prepare("
        SELECT InstallmentID, InstallmentNumber, Amount, DrawDate
        FROM Installments
        WHERE SchemeID = ? AND Status = 'Active'
        ORDER BY InstallmentNumber ASC
    ");
    $stmt->execute([$scheme['SchemeID']]);
    $scheme['installments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $schemesWithInstallments[] = $scheme;
}

$autoSchemeId = 0;
$autoInstallmentId = 0;

// Auto-select latest active subscription and derive next installment from last submitted payment.
$stmt = $db->prepare("
    SELECT SubscriptionID, SchemeID
    FROM Subscriptions
    WHERE CustomerID = ? AND RenewalStatus = 'Active'
    ORDER BY StartDate DESC, SubscriptionID DESC
    LIMIT 1
");
$stmt->execute([$customerId]);
$latestSubscription = $stmt->fetch(PDO::FETCH_ASSOC);

if ($latestSubscription) {
    $autoSchemeId = (int) $latestSubscription['SchemeID'];

    $stmt = $db->prepare("
        SELECT InstallmentID
        FROM Installments i
        WHERE i.SchemeID = ? AND i.Status = 'Active'
          AND NOT EXISTS (
              SELECT 1
              FROM Payments p
              WHERE p.CustomerID = ?
                AND p.SchemeID = i.SchemeID
                AND p.InstallmentID = i.InstallmentID
                AND p.Status IN ('Pending', 'Verified')
          )
        ORDER BY i.InstallmentNumber ASC
        LIMIT 1
    ");
    $stmt->execute([$autoSchemeId, $customerId]);
    $autoInstallmentId = (int) ($stmt->fetchColumn() ?: 0);

    // Fallback to first active installment if all are already paid/pending or mapping is unavailable.
    if ($autoInstallmentId <= 0) {
        $stmt = $db->prepare("
            SELECT InstallmentID
            FROM Installments
            WHERE SchemeID = ? AND Status = 'Active'
            ORDER BY InstallmentNumber ASC
            LIMIT 1
        ");
        $stmt->execute([$autoSchemeId]);
        $autoInstallmentId = (int) ($stmt->fetchColumn() ?: 0);
    }
}

$preselectedSchemeId = isset($_GET['scheme_id']) ? (int) $_GET['scheme_id'] : $autoSchemeId;
$preselectedInstallmentId = isset($_GET['installment_id']) ? (int) $_GET['installment_id'] : $autoInstallmentId;

$isValidPreselectedScheme = false;
$isValidPreselectedInstallment = false;
foreach ($schemesWithInstallments as $scheme) {
    if ((int) $scheme['SchemeID'] === $preselectedSchemeId) {
        $isValidPreselectedScheme = true;
        foreach ($scheme['installments'] as $installment) {
            if ((int) $installment['InstallmentID'] === $preselectedInstallmentId) {
                $isValidPreselectedInstallment = true;
                break;
            }
        }
        break;
    }
}
if (!$isValidPreselectedScheme) {
    $preselectedSchemeId = 0;
}
if (!$isValidPreselectedInstallment) {
    $preselectedInstallmentId = 0;
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schemeId = isset($_POST['scheme_id']) ? (int) $_POST['scheme_id'] : 0;
    $installmentId = isset($_POST['installment_id']) ? (int) $_POST['installment_id'] : 0;
    $amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
    $utrNumber = trim($_POST['utr_number'] ?? '');
    $staffName = trim($_POST['staff_name'] ?? '');
    $payerRemark = trim($_POST['payer_remark'] ?? '');

    if (!$schemeId || !$installmentId || $amount <= 0) {
        $error_message = 'Please select scheme, installment and enter amount.';
    } elseif (empty($utrNumber)) {
        $error_message = 'UTR number is required.';
    } elseif (!isset($_FILES['screenshot']) || $_FILES['screenshot']['error'] !== UPLOAD_ERR_OK) {
        $error_message = 'Please upload payment screenshot (online payment only).';
    } else {
        $file = $_FILES['screenshot'];
        $allowedTypes = ['image/jpeg', 'image/png'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        if (!in_array($file['type'], $allowedTypes)) {
            $error_message = 'Only JPEG and PNG images allowed.';
        } elseif ($file['size'] > $maxSize) {
            $error_message = 'File size must be under 5MB.';
        } else {
            $uploadDir = __DIR__ . '/uploads/payments/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) ?: 'jpg';
            $fileName = uniqid() . '.' . $ext;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                try {
                    $db->beginTransaction();

                    // Ensure subscription exists (auto-subscribe)
                    $stmt = $db->prepare("
                        SELECT SubscriptionID FROM Subscriptions
                        WHERE CustomerID = ? AND SchemeID = ? AND RenewalStatus = 'Active'
                    ");
                    $stmt->execute([$customerId, $schemeId]);
                    if (!$stmt->fetch()) {
                        $stmt = $db->prepare("SELECT TotalPayments FROM Schemes WHERE SchemeID = ?");
                        $stmt->execute([$schemeId]);
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $totalMonths = $row ? (int) $row['TotalPayments'] : 12;
                        $stmt = $db->prepare("
                            INSERT INTO Subscriptions (CustomerID, SchemeID, StartDate, EndDate, RenewalStatus)
                            VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL ? MONTH), 'Active')
                        ");
                        $stmt->execute([$customerId, $schemeId, $totalMonths]);
                    }

                    // Insert payment (online only: UTR + screenshot)
                    $screenshotUrl = 'uploads/payments/' . $fileName;
                    $stmt = $db->prepare("
                        INSERT INTO Payments (CustomerID, SchemeID, InstallmentID, Amount, UTRNumber, StaffName, ScreenshotURL, Status, PayerRemark, SubmittedAt)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', ?, NOW())
                    ");
                    $stmt->execute([
                        $customerId,
                        $schemeId,
                        $installmentId,
                        $amount,
                        $utrNumber,
                        $staffName !== '' ? $staffName : null,
                        $screenshotUrl,
                        $payerRemark ?: null
                    ]);

                    $db->commit();
                    $_SESSION['success_message'] = 'Payment submitted successfully. It will be verified shortly.';
                    header('Location: index.php');
                    exit;
                } catch (Exception $e) {
                    $db->rollBack();
                    $error_message = 'Error saving payment: ' . $e->getMessage();
                }
            } else {
                $error_message = 'Failed to upload screenshot.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Payment - Golden Dream</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --dark-bg: #1A1D21;
            --card-bg: #222529;
            --accent-green: #2F9B7F;
            --text-primary: rgba(255, 255, 255, 0.9);
            --text-secondary: rgba(255, 255, 255, 0.7);
            --border-color: rgba(255, 255, 255, 0.05);
        }
        body { background: var(--dark-bg); color: var(--text-primary); min-height: 100vh; margin: 0; font-family: 'Inter', sans-serif; }
        .add-payment-container { padding: 24px; margin-top: 70px; max-width: 560px; margin-left: auto; margin-right: auto; }
        .add-payment-header {
            background: linear-gradient(135deg, #2F9B7F 0%, #1e6e59 100%);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            text-align: center;
        }
        .add-payment-header h2 { color: #fff; font-size: 22px; margin-bottom: 6px; }
        .add-payment-header p { color: rgba(255,255,255,0.9); margin: 0; font-size: 14px; }
        .add-payment-card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 24px;
            border: 1px solid var(--border-color);
        }
        .form-label { color: var(--text-primary); font-weight: 500; margin-bottom: 8px; }
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 12px;
            border-radius: 8px;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent-green);
            color: var(--text-primary);
            box-shadow: 0 0 0 3px rgba(47, 155, 127, 0.2);
        }
        .form-control::placeholder { color: var(--text-secondary); }
        /* Dropdown options: light bg + dark text so items are visible */
        .form-select option,
        .add-payment-card select option {
            background: #fff;
            color: #1a1a1a;
        }
        .btn-add { background: var(--accent-green); color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 500; }
        .btn-add:hover { background: #248c6f; color: white; transform: translateY(-2px); }
        .btn-back {
            background: rgba(47, 155, 127, 0.15);
            color: var(--accent-green);
            border: 1px solid var(--accent-green);
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-back:hover { background: var(--accent-green); color: white; }
        .alert-custom { border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; }
        .alert-danger-custom { background: rgba(220, 53, 69, 0.15); border: 1px solid rgba(220, 53, 69, 0.3); color: #dc3545; }
        small.text-muted { color: var(--text-secondary) !important; }
    </style>
</head>
<body>
    <?php include '../c_includes/sidebar.php'; ?>
    <?php include '../c_includes/topbar.php'; ?>

    <div class="main-content">
        <div class="add-payment-container">
            <a href="index.php" class="btn-back mb-3"><i class="fas fa-arrow-left"></i> Back to Payments</a>

            <div class="add-payment-header">
                <h2><i class="fas fa-plus-circle"></i> Add Payment</h2>
                <p>Online payment only. Enter UTR and upload screenshot.</p>
            </div>

            <?php if ($error_message): ?>
                <div class="alert-custom alert-danger-custom"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if (empty($schemesWithInstallments)): ?>
                <div class="add-payment-card text-center">
                    <p class="text-secondary mb-0">No schemes available at the moment.</p>
                    <a href="../schemes" class="btn-add mt-3">View Schemes</a>
                </div>
            <?php else: ?>
                <div class="add-payment-card">
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Scheme <span class="text-danger">*</span></label>
                            <select name="scheme_id" id="scheme_id" class="form-select form-control" required>
                                <option value="">Select scheme</option>
                                <?php foreach ($schemesWithInstallments as $s): ?>
                                    <option value="<?php echo $s['SchemeID']; ?>" data-monthly="<?php echo (float)$s['MonthlyPayment']; ?>" <?php echo ((int)$s['SchemeID'] === $preselectedSchemeId) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($s['SchemeName']); ?> (₹<?php echo number_format($s['MonthlyPayment'], 2); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Installment <span class="text-danger">*</span></label>
                            <select name="installment_id" id="installment_id" class="form-select form-control" required>
                                <option value="">Select scheme first</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0.01" required placeholder="0.00">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">UTR / Reference number <span class="text-danger">*</span></label>
                            <input type="text" name="utr_number" id="utr_number" class="form-control" maxlength="50" required placeholder="Bank UTR or reference">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Staff name</label>
                            <input type="text" name="staff_name" id="staff_name" class="form-control" maxlength="255" placeholder="Enter staff name (optional)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment screenshot <span class="text-danger">*</span></label>
                            <input type="file" name="screenshot" class="form-control" accept="image/jpeg,image/png" required>
                            <small class="text-muted">JPG or PNG, max 5MB. Online payment only.</small>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Remarks (optional)</label>
                            <textarea name="payer_remark" class="form-control" rows="2" placeholder="Any note about this payment"></textarea>
                        </div>
                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-add"><i class="fas fa-check"></i> Submit Payment</button>
                            <a href="index.php" class="btn-back">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const schemesData = <?php echo json_encode($schemesWithInstallments); ?>;
        const preselectedInstallmentId = <?php echo (int) $preselectedInstallmentId; ?>;
        const schemeSelect = document.getElementById('scheme_id');
        const installmentSelect = document.getElementById('installment_id');
        const amountInput = document.getElementById('amount');

        function getInstallments(schemeId) {
            const scheme = schemesData.find(s => s.SchemeID == schemeId);
            return scheme ? scheme.installments : [];
        }

        schemeSelect.addEventListener('change', function() {
            const schemeId = this.value;
            installmentSelect.innerHTML = '<option value="">Select installment</option>';
            amountInput.value = '';

            if (!schemeId) return;
            const installments = getInstallments(schemeId);
            installments.forEach(function(inst) {
                const opt = document.createElement('option');
                opt.value = inst.InstallmentID;
                opt.textContent = 'Installment ' + inst.InstallmentNumber + ' — ₹' + parseFloat(inst.Amount).toFixed(2);
                opt.dataset.amount = inst.Amount;
                installmentSelect.appendChild(opt);
            });

            if (installments.length > 0) {
                let selectedInstallment = installments[0];
                if (preselectedInstallmentId > 0) {
                    const matchedInstallment = installments.find(inst => parseInt(inst.InstallmentID, 10) === preselectedInstallmentId);
                    if (matchedInstallment) {
                        selectedInstallment = matchedInstallment;
                    }
                }
                installmentSelect.value = selectedInstallment.InstallmentID;
                amountInput.value = selectedInstallment.Amount;
            }
        });

        installmentSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.dataset.amount) amountInput.value = opt.dataset.amount;
        });

        if (schemeSelect.value) {
            schemeSelect.dispatchEvent(new Event('change'));
        }
    </script>
</body>
</html>
