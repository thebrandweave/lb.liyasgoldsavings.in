<?php
session_start();
if (!isset($_SESSION['shop_admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php?error=1');
    exit();
}
$product_id = (int)$_GET['id'];
$db = new Database();
$conn = $db->getConnection();
// Delete images from disk
$stmt = $conn->prepare('SELECT image_url FROM product_images WHERE product_id = ?');
$stmt->execute([$product_id]);
foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $img) {
    $imgPath = __DIR__ . '/../../uploads/products/' . basename($img);
    if (file_exists($imgPath)) { unlink($imgPath); }
}
// Delete images from DB
$conn->prepare('DELETE FROM product_images WHERE product_id = ?')->execute([$product_id]);
// Delete product
if ($conn->prepare('DELETE FROM products WHERE product_id = ?')->execute([$product_id])) {
    header('Location: index.php?deleted=1');
    exit();
} else {
    header('Location: index.php?error=1');
    exit();
} 