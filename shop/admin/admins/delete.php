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
$adminId = (int)$_GET['id'];
if ($adminId == $_SESSION['shop_admin_id']) {
    // Prevent self-deletion
    header('Location: index.php?error=self');
    exit();
}
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->prepare('DELETE FROM shopadmin WHERE ShopAdminID = ?');
$stmt->execute([$adminId]);
header('Location: index.php?deleted=1');
exit(); 