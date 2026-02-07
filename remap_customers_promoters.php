<?php
/**
 * Re-maps Customers.PromoterID to the correct promoter using:
 * 1) WalletLogs (direct commission messages) – primary
 * 2) ActivityLogs (Registered new customer) – fallback
 *
 * Dry run:  http://localhost/la.goldendream.in/remap_customers_promoters.php
 * Apply:    http://localhost/la.goldendream.in/remap_customers_promoters.php?run=1
 */

require_once __DIR__ . '/config/config.php';

$database = new Database();
$conn = $database->getConnection();
if (!$conn) {
    die('Database connection failed.');
}

$doUpdate = isset($_GET['run']) && $_GET['run'] === '1';

// ---- 1) WalletLogs: direct commission (exclude "Parent commission")
$stmt = $conn->query("
    SELECT LogID, PromoterUniqueID, Message, CreatedAt
    FROM WalletLogs
    WHERE (Message LIKE '%commission from customer%' OR Message LIKE '%Commission earned from customer%')
      AND Message NOT LIKE '%Parent commission%'
    ORDER BY LogID ASC
");
$walletRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mapFromWallet = []; // CustomerUniqueID => [ PromoterUniqueID, source, logId ]
foreach ($walletRows as $row) {
    if (!preg_match('/customer\s+.+?\s+\(([^)]+)\)/', $row['Message'], $m)) {
        continue;
    }
    $customerUniqueId = trim($m[1]);
    if ($customerUniqueId === '') {
        continue;
    }
    // Keep earliest (first we see) per customer
    if (!isset($mapFromWallet[$customerUniqueId])) {
        $mapFromWallet[$customerUniqueId] = [
            'PromoterUniqueID' => trim($row['PromoterUniqueID']),
            'source'           => 'WalletLogs',
            'logId'            => $row['LogID'],
        ];
    }
}

// ---- 2) ActivityLogs: "Registered new customer: Name (ID: XXX)"
$stmt = $conn->query("
    SELECT al.LogID, al.UserID, al.Action, al.CreatedAt
    FROM ActivityLogs al
    WHERE al.UserType = 'Promoter'
      AND al.Action LIKE 'Registered new customer:%'
    ORDER BY al.LogID ASC
");
$activityRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$promoterIdToUnique = [];
$mapFromActivity = [];
foreach ($activityRows as $row) {
    if (!preg_match('/\(ID:\s*([A-Za-z0-9]+)\)/i', $row['Action'], $m)) {
        continue;
    }
    $customerUniqueId = trim($m[1]);
    if ($customerUniqueId === '') {
        continue;
    }
    if (!isset($promoterIdToUnique[$row['UserID']])) {
        $p = $conn->prepare("SELECT PromoterUniqueID FROM Promoters WHERE PromoterID = ?");
        $p->execute([$row['UserID']]);
        $r = $p->fetch(PDO::FETCH_ASSOC);
        $promoterIdToUnique[$row['UserID']] = $r ? trim($r['PromoterUniqueID']) : null;
    }
    $promoterUniqueId = $promoterIdToUnique[$row['UserID']];
    if ($promoterUniqueId === null) {
        continue;
    }
    if (!isset($mapFromActivity[$customerUniqueId])) {
        $mapFromActivity[$customerUniqueId] = [
            'PromoterUniqueID' => $promoterUniqueId,
            'source'           => 'ActivityLogs',
            'logId'            => $row['LogID'],
        ];
    }
}

// Merge: WalletLogs wins; then fill from ActivityLogs for customers not yet mapped
$finalMap = $mapFromWallet;
foreach ($mapFromActivity as $cid => $info) {
    if (!isset($finalMap[$cid])) {
        $finalMap[$cid] = $info;
    }
}

// Resolve CustomerUniqueID -> CustomerID (and check customer exists)
$updates = [];
foreach ($finalMap as $customerUniqueId => $info) {
    $s = $conn->prepare("SELECT CustomerID, PromoterID FROM Customers WHERE TRIM(CustomerUniqueID) = ?");
    $s->execute([$customerUniqueId]);
    $cust = $s->fetch(PDO::FETCH_ASSOC);
    if ($cust) {
        $updates[] = [
            'CustomerID'       => $cust['CustomerID'],
            'CustomerUniqueID' => $customerUniqueId,
            'currentPromoter'  => $cust['PromoterID'],
            'newPromoter'      => $info['PromoterUniqueID'],
            'source'           => $info['source'],
        ];
    }
}

// Output
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Re-map customers to promoters</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        h1 { color: #333; }
        .dry { background: #fff3cd; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .run { background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #37474f; color: #fff; }
        .count { margin: 10px 0; font-size: 16px; }
        a.btn { display: inline-block; margin-top: 15px; padding: 10px 20px; background: #2196F3; color: #fff; text-decoration: none; border-radius: 4px; }
        a.btn:hover { background: #1976D2; }
        .skip { color: #999; }
    </style>
</head>
<body>
    <h1>Re-map customers to promoters</h1>
    <?php if ($doUpdate): ?>
        <div class="run">
            <strong>Update mode.</strong> Below are the updates that were applied.
        </div>
    <?php else: ?>
        <div class="dry">
            <strong>Dry run.</strong> No changes made. Add <code>?run=1</code> to the URL to apply updates.
        </div>
    <?php endif; ?>

    <p class="count">
        Mappings found: <strong><?php echo count($finalMap); ?></strong> (WalletLogs: <?php echo count($mapFromWallet); ?>, ActivityLogs only: <?php echo count($finalMap) - count($mapFromWallet); ?>).<br>
        Customers that exist and will be updated: <strong><?php echo count($updates); ?></strong>
    </p>

    <?php if ($doUpdate && count($updates) > 0): ?>
        <?php
        $conn->beginTransaction();
        try {
            $upd = $conn->prepare("UPDATE Customers SET PromoterID = ?, UpdatedAt = NOW() WHERE CustomerID = ?");
            foreach ($updates as $u) {
                $upd->execute([$u['newPromoter'], $u['CustomerID']]);
            }
            $conn->commit();
            echo '<p style="color: green;">Updated ' . count($updates) . ' customer(s) successfully.</p>';
        } catch (Exception $e) {
            $conn->rollBack();
            echo '<p style="color: red;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        }
        ?>
    <?php endif; ?>

    <?php if (count($updates) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>CustomerUniqueID</th>
                    <th>CustomerID</th>
                    <th>Current PromoterID</th>
                    <th>New PromoterID</th>
                    <th>Source</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($updates as $u): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['CustomerUniqueID']); ?></td>
                        <td><?php echo (int)$u['CustomerID']; ?></td>
                        <td><?php echo htmlspecialchars($u['currentPromoter'] ?? 'NULL'); ?></td>
                        <td><?php echo htmlspecialchars($u['newPromoter']); ?></td>
                        <td><?php echo htmlspecialchars($u['source']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No customer mappings to apply (no matching WalletLogs/ActivityLogs or no matching Customers).</p>
    <?php endif; ?>

    <?php if (!empty($finalMap) && count($updates) < count($finalMap)): ?>
        <p class="skip">Some mapped CustomerUniqueIDs were not found in Customers table (e.g. ID format changed or record deleted).</p>
    <?php endif; ?>

    <?php if (!$doUpdate && count($updates) > 0): ?>
        <a href="?run=1" class="btn">Apply <?php echo count($updates); ?> update(s)</a>
    <?php endif; ?>
</body>
</html>
