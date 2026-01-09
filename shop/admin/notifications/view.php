<?php
session_start();
if (!isset($_SESSION['shop_admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}
$adminId = $_SESSION['shop_admin_id'];
$notificationId = (int)$_GET['id'];

$db = new Database();
$conn = $db->getConnection();
// Mark as read
$stmt = $conn->prepare('UPDATE shopnotifications SET is_read = 1 WHERE notification_id = ? AND admin_id = ?');
$stmt->execute([$notificationId, $adminId]);

header('Location: index.php');
exit(); 