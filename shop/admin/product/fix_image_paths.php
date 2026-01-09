<?php
session_start();
if (!isset($_SESSION['shop_admin_id'])) {
    header('Location: ../login.php');
    exit();
}
require_once '../../config/config.php';

$db = new Database();
$conn = $db->getConnection();

// Get all product images
$stmt = $conn->query('SELECT id, image_url FROM product_images');
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

$fixed = 0;
$errors = 0;

foreach ($images as $image) {
    $currentUrl = $image['image_url'];
    
    // Check if the URL contains the full path
    if (strpos($currentUrl, 'shop/uploads/products/') === 0) {
        // Extract just the filename
        $filename = basename($currentUrl);
        
        // Update the database
        $updateStmt = $conn->prepare('UPDATE product_images SET image_url = ? WHERE id = ?');
        if ($updateStmt->execute([$filename, $image['id']])) {
            $fixed++;
        } else {
            $errors++;
        }
    }
}

echo "Fixed $fixed image paths. Errors: $errors";
?> 