<?php
$conn = new PDO("mysql:host=localhost;dbname=lbnew", "root", "");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

require_once __DIR__ . '/config/commission_helper.php';

$stmt = $conn->prepare("SELECT CustomerUniqueID FROM Customers WHERE PromoterID = 'GDP03123'");
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Processing commissions for GDP03123...\n";

foreach ($customers as $c) {
    $uid = $c['CustomerUniqueID'];
    echo "Processing customer: $uid\n";
    $result = processPromoterCommission($uid, $conn);
    if (!empty($result['credited'])) {
        foreach ($result['credited'] as $credit) {
            echo "  -> Credited {$credit['amount']} to {$credit['role']} ({$credit['id']} - {$credit['name']})\n";
        }
    } else {
        echo "  -> No new credits or conditions not met. Reason: " . ($result['reason'] ?? 'None/Pending') . "\n";
    }
}
echo "Done.\n";
