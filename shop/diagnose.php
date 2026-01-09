<?php
// Diagnostic script for shop troubleshooting
echo "<h1>Shop Diagnostic Report</h1>";

// Check PHP version and extensions
echo "<h2>PHP Environment</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>PDO Extension: " . (extension_loaded('pdo') ? '✓ Loaded' : '✗ Not Loaded') . "</p>";
echo "<p>PDO MySQL Extension: " . (extension_loaded('pdo_mysql') ? '✓ Loaded' : '✗ Not Loaded') . "</p>";

// Check file permissions
echo "<h2>File Permissions</h2>";
$files_to_check = [
    'config/config.php',
    'index.php',
    'error.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "<p>$file: ✓ Exists (" . (is_readable($file) ? 'Readable' : 'Not Readable') . ")</p>";
    } else {
        echo "<p>$file: ✗ Does not exist</p>";
    }
}

// Test database connection
echo "<h2>Database Connection Test</h2>";
try {
    require_once __DIR__ . '/config/config.php';
    echo "<p>Config file: ✓ Loaded successfully</p>";
    
    $db = new Database();
    echo "<p>Database class: ✓ Instantiated</p>";
    
    $conn = $db->getConnection();
    if ($conn) {
        echo "<p>Database connection: ✓ Successful</p>";
        
        // Test basic queries
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Tables found: " . count($tables) . "</p>";
        
        if (in_array('categories', $tables)) {
            $stmt = $conn->query("SELECT COUNT(*) as count FROM categories");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>Categories table: ✓ " . $result['count'] . " records</p>";
        } else {
            echo "<p>Categories table: ✗ Not found</p>";
        }
        
        if (in_array('products', $tables)) {
            $stmt = $conn->query("SELECT COUNT(*) as count FROM products");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>Products table: ✓ " . $result['count'] . " records</p>";
        } else {
            echo "<p>Products table: ✗ Not found</p>";
        }
    } else {
        echo "<p>Database connection: ✗ Failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Database Error: " . $e->getMessage() . "</p>";
}

// Check server configuration
echo "<h2>Server Configuration</h2>";
echo "<p>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script Name: " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>HTTP Host: " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// Check .htaccess
echo "<h2>.htaccess Check</h2>";
if (file_exists('.htaccess')) {
    echo "<p>.htaccess: ✓ Exists</p>";
} else {
    echo "<p>.htaccess: ✗ Does not exist</p>";
}

// Check for common issues
echo "<h2>Common Issues Check</h2>";
$issues = [];

if (!extension_loaded('pdo_mysql')) {
    $issues[] = "PDO MySQL extension not loaded";
}

if (!file_exists('config/config.php')) {
    $issues[] = "Config file missing";
}

if (empty($issues)) {
    echo "<p style='color: green;'>✓ No obvious issues detected</p>";
} else {
    echo "<p style='color: red;'>Issues found:</p>";
    echo "<ul>";
    foreach ($issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='index.php'>Try accessing main shop page</a></p>";
echo "<p><a href='test.php'>Run basic test</a></p>";
?> 