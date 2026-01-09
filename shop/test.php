<?php
// Simple test file to check shop accessibility
echo "<h1>Shop Test Page</h1>";
echo "<p>If you can see this, the shop directory is accessible.</p>";

// Test database connection
try {
    require_once __DIR__ . '/config/config.php';
    $db = new Database();
    $conn = $db->getConnection();
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    // Test a simple query
    $stmt = $conn->query("SELECT COUNT(*) as count FROM categories");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Categories count: " . $result['count'] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

// Show server information
echo "<h2>Server Information:</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script Name: " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// Show configuration
echo "<h2>Configuration:</h2>";
echo "<p>Base URL: " . Database::$baseUrl . "</p>";
echo "<p>Shop DB: " . Database::$shop_db . "</p>";
?> 