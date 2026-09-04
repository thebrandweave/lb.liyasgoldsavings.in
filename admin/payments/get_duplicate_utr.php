<?php
/**
 * Returns HTML fragment listing promoter commission details and duplicate UTR warning.
 * Used when admin is about to verify a payment (index.php modal).
 */

session_start();
require_once("../../config/config.php");

header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['payment_id']) || !ctype_digit($_GET['payment_id'])) {
    echo '<div style="background:#f8f9fa;border:1px solid #dee2e6;border-radius:6px;padding:10px;margin-top:10px;text-align:left;font-size:13px;color:#666;"><i class="fas fa-info-circle me-1"></i> Invalid Payment ID.</div>';
    exit;
}

$paymentId = (int)$_GET['payment_id'];
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo '<div style="background:#f8f9fa;border:1px solid #dee2e6;border-radius:6px;padding:10px;margin-top:10px;text-align:left;font-size:13px;color:#666;"><i class="fas fa-info-circle me-1"></i> Database connection error.</div>';
    exit;
}

function convertCommissionToIntLocal($commission) {
    return intval(preg_replace('/[^0-9]/', '', (string)$commission));
}

// Fetch payment and customer details using LEFT JOIN
$pStmt = $conn->prepare("
    SELECT p.PaymentID, p.Amount, p.UTRNumber, c.CustomerID, c.CustomerUniqueID, c.Name as CustomerName, TRIM(c.PromoterID) as DirectPromoterRef
    FROM Payments p
    LEFT JOIN Customers c ON p.CustomerID = c.CustomerID
    WHERE p.PaymentID = ?
");
$pStmt->execute([$paymentId]);
$paymentData = $pStmt->fetch(PDO::FETCH_ASSOC);

if (!$paymentData) {
    echo '<div style="background:#f8f9fa;border:1px solid #dee2e6;border-radius:6px;padding:10px;margin-top:10px;text-align:left;font-size:13px;color:#666;"><i class="fas fa-info-circle me-1"></i> Payment record not found.</div>';
    exit;
}

// Pre-load all promoters (from both Promoters and mp_promoters if available)
$allPStmt = $conn->prepare("SELECT PromoterID, PromoterUniqueID, ParentPromoterID, Commission, ParentCommission, Name FROM Promoters");
$allPStmt->execute();
$allPromoters = $allPStmt->fetchAll(PDO::FETCH_ASSOC);

$promoterByRef = [];
foreach ($allPromoters as $p) {
    $pID = (string)$p['PromoterID'];
    $uID = trim($p['PromoterUniqueID']);
    if (!empty($uID)) $promoterByRef[$uID] = $p;
    if (!empty($pID)) $promoterByRef[$pID] = $p;
}

$directRef = trim($paymentData['DirectPromoterRef'] ?? '');
$hierarchy = [];
$currRef = $directRef;
$visited = [];

while (!empty($currRef) && !isset($visited[$currRef])) {
    $visited[$currRef] = true;
    if (!isset($promoterByRef[$currRef])) break;
    $pData = $promoterByRef[$currRef];
    $hierarchy[] = $pData;
    $currRef = !empty($pData['ParentPromoterID']) ? trim($pData['ParentPromoterID']) : null;
}

$commissionItems = [];
if (!empty($hierarchy)) {
    // Direct Promoter
    $directPromoter = $hierarchy[0];
    $directID = trim($directPromoter['PromoterUniqueID']);
    $directComm = convertCommissionToIntLocal($directPromoter['Commission']);
    $commissionItems[] = [
        'role' => 'Direct Promoter',
        'name' => $directPromoter['Name'],
        'id' => $directID,
        'amount' => $directComm
    ];

    // Parent Promoters
    for ($i = 0; $i < count($hierarchy) - 1; $i++) {
        $child = $hierarchy[$i];
        $parent = $hierarchy[$i + 1];
        $parentID = trim($parent['PromoterUniqueID']);
        $childComm = convertCommissionToIntLocal($child['Commission']);
        $parentComm = convertCommissionToIntLocal($parent['Commission']);

        $gap = 0;
        if (!empty($child['ParentCommission']) && convertCommissionToIntLocal($child['ParentCommission']) > 0) {
            $gap = convertCommissionToIntLocal($child['ParentCommission']);
        } else if ($parentComm > $childComm) {
            $gap = $parentComm - $childComm;
        }

        $roleLabel = ($i === 0) ? 'Parent Promoter' : 'Grandparent Promoter';
        $commissionItems[] = [
            'role' => $roleLabel,
            'name' => $parent['Name'],
            'id' => $parentID,
            'amount' => $gap
        ];
    }
}

// Get Duplicate UTRs
$utr = trim($paymentData['UTRNumber'] ?? '');
$dupes = [];
if (!empty($utr)) {
    $dStmt = $conn->prepare("
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
    $dStmt->execute([$utr, $paymentId]);
    $dupes = $dStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!-- Promoter Commission Information Card -->
<div style="background:#e8f5e9;border:1px solid #a5d6a7;border-radius:8px;padding:12px;margin-top:12px;text-align:left;">
    <strong style="color:#2e7d32;font-size:13px;display:block;margin-bottom:6px;">
        <i class="fas fa-coins me-1"></i> Promoter Commissions Allocation:
    </strong>
    <?php if (!empty($commissionItems)): ?>
        <div style="display:flex;flex-direction:column;gap:6px;">
            <?php foreach ($commissionItems as $item): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;background:#ffffff;padding:6px 10px;border-radius:6px;border-left:3px solid #2e7d32;font-size:13px;">
                    <div>
                        <strong><?php echo htmlspecialchars($item['role']); ?>:</strong> <?php echo htmlspecialchars($item['name']); ?> 
                        <span style="color:#666;font-size:12px;">(<?php echo htmlspecialchars($item['id']); ?>)</span>
                    </div>
                    <span style="color:#2e7d32;font-weight:700;">+ ₹<?php echo number_format($item['amount'], 2); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="font-size:12px;color:#555;background:#fff;padding:6px 10px;border-radius:6px;">
            No promoter associated with this customer record.
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($dupes)): ?>
    <div class="duplicate-utr-list" style="background:#fff3cd;border:1px solid #ffc107;border-radius:8px;padding:10px 12px;margin-top:12px;text-align:left;">
        <strong style="color:#856404;font-size:13px;"><i class="fas fa-exclamation-triangle me-1"></i> This UTR was already used in verified payment(s):</strong>
        <table style="width:100%;margin-top:8px;font-size:12px;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid #dee2e6;color:#856404;">
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
    </div>
<?php endif; ?>
