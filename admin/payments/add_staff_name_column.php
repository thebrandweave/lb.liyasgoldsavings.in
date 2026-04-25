<?php
/**
 * One-time script: Add StaffName column to Payments (after UTRNumber if present, else after Amount).
 * Run once in browser, then delete this file for security.
 */

require_once __DIR__ . '/../../config/config.php';

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die('Database connection failed.');
}

function columnExists(PDO $conn, string $table, string $column): bool
{
    $stmt = $conn->prepare(
        "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?"
    );
    $stmt->execute([$table, $column]);
    return (int) $stmt->fetchColumn() > 0;
}

try {
    if (columnExists($conn, 'Payments', 'StaffName')) {
        echo 'Column StaffName already exists. Nothing to do.';
        exit;
    }

    $after = columnExists($conn, 'Payments', 'UTRNumber') ? 'UTRNumber' : 'Amount';
    $conn->exec("ALTER TABLE `Payments` ADD `StaffName` VARCHAR(255) NULL DEFAULT NULL AFTER `{$after}`");
    echo 'Column StaffName added successfully after ' . htmlspecialchars($after) . '. You can delete this file.';
} catch (PDOException $e) {
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}
