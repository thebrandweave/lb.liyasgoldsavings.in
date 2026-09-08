<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/commission_helper.php';

$db = new Database();
$conn = $db->getConnection();

// Handle CLI vs Web execution
if (php_sapi_name() !== 'cli') {
    echo "<pre>";
}

$promoterId = isset($_GET['promoter']) ? $_GET['promoter'] : 'GDP03123';

$stmt = $conn->prepare("SELECT CustomerUniqueID FROM Customers WHERE PromoterID = :promoterId");
$stmt->execute([':promoterId' => $promoterId]);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Processing commissions for Promoter: $promoterId\n";
echo "Found " . count($customers) . " customers associated with this promoter.\n\n";

foreach ($customers as $c) {
    $uid = $c['CustomerUniqueID'];
    echo "Processing customer: $uid\n";
    
    // Check if there is at least one Verified payment, as per business rule
    // (Uncomment the below lines if you only want to credit verified payments)
    /*
    $vStmt = $conn->prepare("SELECT COUNT(*) as v_count FROM Payments WHERE CustomerID = (SELECT CustomerID FROM Customers WHERE CustomerUniqueID = ?) AND Status = 'Verified'");
    $vStmt->execute([$uid]);
    $vCount = $vStmt->fetch(PDO::FETCH_ASSOC)['v_count'];
    if ($vCount == 0) {
        echo "  -> Skipped. No verified payments found for this customer.\n";
        continue;
    }
    */

    $result = processPromoterCommission($uid, $conn);
    if (!empty($result['credited'])) {
        foreach ($result['credited'] as $credit) {
            echo "  -> Credited Rs {$credit['amount']} to {$credit['role']} ({$credit['id']} - {$credit['name']})\n";
        }
    } else {
        echo "  -> No new credits needed or conditions not met. Reason: " . ($result['reason'] ?? 'Already Credited / No Action') . "\n";
    }
    echo "----------------------------------------\n";
}
echo "\nDone.\n";
if (php_sapi_name() !== 'cli') {
    echo "</pre>";
}
