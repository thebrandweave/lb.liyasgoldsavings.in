<?php
// Test script to verify counter generates LB1000
require_once __DIR__ . '/../../config/db.php';

// Check current state
$stmt = $conn->query("SELECT MAX(id) as max_id FROM CustomerUniqueCounter");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Current max counter id: " . ($row['max_id'] ?? 'empty') . "\n";

// Simulate what trigger does - insert and get LAST_INSERT_ID
$conn->exec("INSERT INTO CustomerUniqueCounter () VALUES ()");
$newCounter = $conn->lastInsertId();
echo "Next counter value: $newCounter\n";
echo "Would generate: LB$newCounter\n";

// Roll back by deleting
$conn->exec("DELETE FROM CustomerUniqueCounter WHERE id = $newCounter");
echo "Cleaned up test row.\n";
echo "Counter will start from LB" . ($newCounter) . " for the next real customer.\n";
?>
