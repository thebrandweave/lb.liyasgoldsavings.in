<?php
/**
 * One-time script: Add UTRNumber column to Payments table.
 * Run once: http://localhost/la.goldendream.in/admin/payments/add_utr_column.php
 * Delete this file after running.
 */

require_once __DIR__ . '/../../config/config.php';

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die('Database connection failed.');
}

try {
    // Use DEFAULT '' so existing rows are valid (NOT NULL column)
    $conn->exec("ALTER TABLE `Payments` ADD `UTRNumber` VARCHAR(50) NOT NULL DEFAULT '' AFTER `Amount`");
    echo 'Column UTRNumber added successfully. You can delete this file.';
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo 'Column UTRNumber already exists. Nothing to do.';
    } else {
        echo 'Error: ' . htmlspecialchars($e->getMessage());
    }
}
