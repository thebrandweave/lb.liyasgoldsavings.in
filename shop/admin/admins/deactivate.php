<?php
session_start();
if (!isset($_SESSION['shop_admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/config.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}
$adminId = (int)$_GET['id'];
$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare('UPDATE ShopAdmin SET Status = "Inactive" WHERE ShopAdminID = ?');
if ($stmt->execute([$adminId])) {
    $msg = urlencode('Account deactivated successfully!');
    header("Location: view.php?id=$adminId&msg=$msg");
    exit();
} else {
    $msg = urlencode('Failed to deactivate account.');
    header("Location: view.php?id=$adminId&msg=$msg");
    exit();
} 