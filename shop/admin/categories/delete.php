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
$catId = (int)$_GET['id'];
$db = new Database();
$conn = $db->getConnection();
$stmt = $conn->prepare('DELETE FROM categories WHERE category_id = ?');
$stmt->execute([$catId]);
header('Location: index.php?deleted=1');
exit(); 