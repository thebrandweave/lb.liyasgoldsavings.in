<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../login.php");
    exit();
}
if ($_SESSION['admin_role'] !== 'SuperAdmin') {
    $_SESSION['error_message'] = "You don't have permission to access notification settings.";
    header("Location: ../../dashboard/index.php");
    exit();
}

$menuPath = "../../";
$currentPage = "settings";

require_once("../../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

try {
    $stmt = $conn->query("SELECT * FROM NotificationChannelSettings ORDER BY SettingID DESC LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$settings) {
        $conn->exec("INSERT INTO NotificationChannelSettings (IsSMSEnabled, IsWhatsAppEnabled) VALUES (1, 1)");
        $stmt = $conn->query("SELECT * FROM NotificationChannelSettings ORDER BY SettingID DESC LIMIT 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Failed to fetch notification settings: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        $isSMS = isset($_POST['is_sms_enabled']) ? 1 : 0;
        $isWA = isset($_POST['is_whatsapp_enabled']) ? 1 : 0;

        if ($isSMS === 0 && $isWA === 0) {
            throw new Exception("At least one channel must be enabled.");
        }

        $stmt = $conn->prepare("UPDATE NotificationChannelSettings SET IsSMSEnabled = ?, IsWhatsAppEnabled = ? WHERE SettingID = ?");
        $stmt->execute([$isSMS, $isWA, $settings['SettingID']]);

        $action = "Updated notification channels: SMS=" . $isSMS . ", WhatsApp=" . $isWA;
        $stmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Admin', ?, ?)");
        $stmt->execute([$_SESSION['admin_id'], $action, $_SERVER['REMOTE_ADDR']]);

        $conn->commit();
        $_SESSION['success_message'] = "Notification channel settings updated successfully.";
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $_SESSION['error_message'] = "Failed to update notification settings: " . $e->getMessage();
    }
    header("Location: index.php");
    exit();
}

include("../../components/sidebar.php");
include("../../components/topbar.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Channels</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <style>
        .settings-form { background: white; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); padding: 30px; max-width: 700px; margin: 0 auto; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .page-title { font-size: 24px; font-weight: 600; margin: 0; }
        .switch-row { display: flex; align-items: center; justify-content: space-between; padding: 16px; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 15px; }
        .switch-label { font-weight: 600; }
        .switch-sub { font-size: 12px; color: #7f8c8d; margin-top: 4px; }
        .btn-group { display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px; }
        .btn { padding: 10px 18px; border-radius: 6px; border: none; cursor: pointer; }
        .btn-primary { background: #3a7bd5; color: #fff; }
        .btn-secondary { background: #fff; color: #2c3e50; border: 1px solid #e0e0e0; text-decoration: none; }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="page-header">
            <h1 class="page-title">Notification Channels</h1>
            <a href="../" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Settings</a>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <form class="settings-form" method="POST">
            <div class="switch-row">
                <div>
                    <div class="switch-label"><i class="fas fa-sms"></i> SMS Notifications</div>
                    <div class="switch-sub">Send alerts using Airtel SMS integration.</div>
                </div>
                <input type="checkbox" name="is_sms_enabled" <?php echo !empty($settings['IsSMSEnabled']) ? 'checked' : ''; ?>>
            </div>

            <div class="switch-row">
                <div>
                    <div class="switch-label"><i class="fab fa-whatsapp"></i> WhatsApp Notifications</div>
                    <div class="switch-sub">Send alerts using Meta WhatsApp Cloud API.</div>
                </div>
                <input type="checkbox" name="is_whatsapp_enabled" <?php echo !empty($settings['IsWhatsAppEnabled']) ? 'checked' : ''; ?>>
            </div>

            <div class="btn-group">
                <a href="../" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Channels</button>
            </div>
        </form>
    </div>
</body>
</html>

