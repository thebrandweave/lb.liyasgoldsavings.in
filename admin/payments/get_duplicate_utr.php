<?php
/**
 * Returns HTML fragment listing verified payments that share the same UTR as the given payment.
 * Used when admin is about to verify a payment (index.php modal).
 */

session_start();
require_once("../../config/config.php");

header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['payment_id']) || !ctype_digit($_GET['payment_id'])) {
    echo '';
    exit;
}

$paymentId = (int)$_GET['payment_id'];
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo '';
    exit;
}

// Get current payment's UTR
$stmt = $conn->prepare("SELECT UTRNumber FROM Payments WHERE PaymentID = ?");
$stmt->execute([$paymentId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row || empty(trim($row['UTRNumber'] ?? ''))) {
    echo '';
    exit;
}

$utr = trim($row['UTRNumber']);

// Get other Verified payments with same UTR (include Installment)
$stmt = $conn->prepare("
    SELECT p.PaymentID, p.Amount, p.VerifiedAt,
           c.Name as CustomerName, c.CustomerUniqueID,
           s.SchemeName,
           i.InstallmentName, i.InstallmentNumber
    FROM Payments p
    LEFT JOIN Customers c ON p.CustomerID = c.CustomerID
    LEFT JOIN Schemes s ON p.SchemeID = s.SchemeID
    LEFT JOIN Installments i ON p.InstallmentID = i.InstallmentID
    WHERE TRIM(p.UTRNumber) = ? AND p.Status = 'Verified' AND p.PaymentID != ?
    ORDER BY p.VerifiedAt DESC
");
$stmt->execute([$utr, $paymentId]);
$dupes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($dupes)) {
    echo '';
    exit;
}

?>
<div class="duplicate-utr-list" style="background:#fff3cd;border:1px solid #ffc107;border-radius:6px;padding:10px 12px;margin-top:10px;text-align:left;">
    <strong><i class="fas fa-exclamation-triangle"></i> This UTR was already used in verified payment(s):</strong>
    <table style="width:100%;margin-top:8px;font-size:13px;border-collapse:collapse;">
        <thead>
            <tr style="border-bottom:1px solid #dee2e6;">
                <th style="padding:4px 6px;text-align:left;">#</th>
                <th style="padding:4px 6px;text-align:left;">Customer</th>
                <th style="padding:4px 6px;text-align:left;">Scheme</th>
                <th style="padding:4px 6px;text-align:left;">Installment</th>
                <th style="padding:4px 6px;text-align:left;">Amount</th>
                <th style="padding:4px 6px;text-align:left;">Verified</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dupes as $d):
                $instLabel = '-';
                if (!empty($d['InstallmentName']) || isset($d['InstallmentNumber'])) {
                    $n = $d['InstallmentName'] ?? '';
                    $num = $d['InstallmentNumber'] ?? '';
                    $instLabel = $n ? ($num !== '' ? $n . ' (' . $num . ')' : $n) : ($num !== '' ? (string)$num : '-');
                }
            ?>
                <tr style="border-bottom:1px solid #eee;">
                    <td style="padding:4px 6px;">#<?php echo $d['PaymentID']; ?></td>
                    <td style="padding:4px 6px;"><?php echo htmlspecialchars($d['CustomerName'] . ' (' . $d['CustomerUniqueID'] . ')'); ?></td>
                    <td style="padding:4px 6px;"><?php echo htmlspecialchars($d['SchemeName'] ?? '-'); ?></td>
                    <td style="padding:4px 6px;"><?php echo htmlspecialchars($instLabel); ?></td>
                    <td style="padding:4px 6px;">₹<?php echo number_format($d['Amount'], 2); ?></td>
                    <td style="padding:4px 6px;"><?php echo $d['VerifiedAt'] ? date('M d, Y H:i', strtotime($d['VerifiedAt'])) : '-'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p style="margin:8px 0 0;font-size:12px;color:#856404;">You can still approve or reject this payment.</p>
</div>
