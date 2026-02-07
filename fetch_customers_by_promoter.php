<?php
/**
 * Temporary script: fetch all customers where PromoterID (promoter unique id) = GDP0527
 * Run: http://localhost/la.goldendream.in/fetch_customers_by_promoter.php
 * Delete this file when done.
 */

require_once __DIR__ . '/config/config.php';

$promoterUniqueId = 'GDP0527';
$database = new Database();
$conn = $database->getConnection();

$stmt = $conn->prepare("
    SELECT CustomerID, CustomerUniqueID, Name, Contact, Email, PromoterID, Status, JoinedDate
    FROM Customers
    WHERE TRIM(PromoterID) = ?
    ORDER BY JoinedDate DESC
");
$stmt->execute([$promoterUniqueId]);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
$count = count($customers);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customers by promoter <?php echo htmlspecialchars($promoterUniqueId); ?></title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        h1 { color: #333; }
        .count { margin: 10px 0; color: #666; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; }
        th { background: #f5f5f5; }
        tr:nth-child(even) { background: #fafafa; }
    </style>
</head>
<body>
    <h1>Customers with promoter: <?php echo htmlspecialchars($promoterUniqueId); ?></h1>
    <p class="count"><strong>Total: <?php echo $count; ?></strong> customer(s)</p>
    <?php if ($count > 0): ?>
    <table>
        <thead>
            <tr>
                <th>CustomerID</th>
                <th>CustomerUniqueID</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Email</th>
                <th>PromoterID</th>
                <th>Status</th>
                <th>JoinedDate</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $c): ?>
            <tr>
                <td><?php echo (int)$c['CustomerID']; ?></td>
                <td><?php echo htmlspecialchars($c['CustomerUniqueID'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($c['Name'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($c['Contact'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($c['Email'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($c['PromoterID'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($c['Status'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($c['JoinedDate'] ?? '-'); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No customers found for this promoter.</p>
    <?php endif; ?>
</body>
</html>
