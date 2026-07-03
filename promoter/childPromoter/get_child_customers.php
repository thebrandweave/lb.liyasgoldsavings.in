<?php
session_start();

if (!isset($_SESSION['promoter_id'])) {
    http_response_code(403);
    exit("Unauthorized");
}

require_once("../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

$promoterUniqueID = $_GET['id'] ?? '';

if (empty($promoterUniqueID)) {
    http_response_code(400);
    exit("Missing ID");
}

// Fetch active customers under this child promoter
$query = "
    SELECT CustomerID, CustomerUniqueID, Name, Contact, Email, JoinedDate, Status, ProfileImageURL
    FROM Customers
    WHERE PromoterID = :promoterUniqueId AND Status = 'Active'
    ORDER BY CreatedAt DESC
";
$stmt = $conn->prepare($query);
$stmt->bindParam(':promoterUniqueId', $promoterUniqueID);
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return HTML directly for easy rendering inside accordion
?>
<div class="expanded-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding: 0 5px;">
    <h4 style="font-weight: 600; color: var(--text-primary); font-size: 14px; margin: 0; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-user-friends" style="color: var(--primary-color);"></i>
        Active Customers (<?php echo count($customers); ?>)
    </h4>
</div>
<?php if (empty($customers)): ?>
    <div style="text-align: center; padding: 25px; color: var(--text-secondary); font-size: 13px; background: white; border-radius: 8px; border: 1px dashed var(--border-color);">
        <i class="fas fa-info-circle" style="margin-right: 5px; color: var(--primary-color);"></i> No active customers found for this promoter.
    </div>
<?php else: ?>
    <div style="overflow-x: auto; border: 1px solid var(--border-color); border-radius: 8px; background: white;">
        <table class="inner-customers-table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="background: var(--bg-light); border-bottom: 1px solid var(--border-color);">
                    <th style="padding: 10px 15px; text-align: left; font-weight: 600; color: var(--text-primary);">Customer</th>
                    <th style="padding: 10px 15px; text-align: left; font-weight: 600; color: var(--text-primary);">Contact</th>
                    <th style="padding: 10px 15px; text-align: left; font-weight: 600; color: var(--text-primary);">Joined Date</th>
                    <th style="padding: 10px 15px; text-align: center; font-weight: 600; color: var(--text-primary); width: 80px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr style="border-bottom: 1px solid #f1f1f1; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#fafafa'" onmouseout="this.style.backgroundColor='transparent'">
                        <td style="padding: 10px 15px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; overflow: hidden; background: #e0e0e0; flex-shrink: 0; border: 1px solid #e0e0e0;">
                                    <?php if (!empty($customer['ProfileImageURL']) && $customer['ProfileImageURL'] !== '-'): ?>
                                        <img src="../../<?php echo htmlspecialchars($customer['ProfileImageURL']); ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <img src="../image.png" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--text-primary);"><?php echo htmlspecialchars($customer['Name']); ?></div>
                                    <div style="font-size: 11px; color: var(--text-secondary);"><?php echo htmlspecialchars($customer['CustomerUniqueID']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 10px 15px;">
                            <div style="color: var(--text-primary);"><i class="fas fa-phone" style="font-size: 11px; color: var(--text-secondary); margin-right: 4px;"></i> <?php echo htmlspecialchars($customer['Contact']); ?></div>
                            <div style="font-size: 11px; color: var(--text-secondary);"><i class="fas fa-envelope" style="font-size: 11px; color: var(--text-secondary); margin-right: 4px;"></i> <?php echo htmlspecialchars($customer['Email'] ?: 'N/A'); ?></div>
                        </td>
                        <td style="padding: 10px 15px; color: var(--text-secondary);">
                            <i class="far fa-calendar-alt" style="margin-right: 4px;"></i> <?php echo htmlspecialchars($customer['JoinedDate'] ?: 'N/A'); ?>
                        </td>
                        <td style="padding: 10px 15px; text-align: center;">
                            <a href="../Customers/view.php?id=<?php echo $customer['CustomerID']; ?>" class="action-btn" style="display: inline-flex; width: 28px; height: 28px; font-size: 11px; border-radius: 6px; align-items: center; justify-content: center;" title="View Customer Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
