<?php
/**
 * Lists all database tables and shows up to 20 rows from each in basic HTML.
 * Run: https://la.goldendream.inlist_tables_sample.php
 * Remove this file when not needed.
 */

require_once __DIR__ . '/config/config.php';

$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die('Database connection failed.');
}

$limit = 20;

// Get all table names
$stmt = $conn->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

$output = [];

foreach ($tables as $table) {
    try {
        $stmt = $conn->query("SELECT * FROM `" . str_replace('`', '``', $table) . "` LIMIT " . (int)$limit);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $output[$table] = $rows;
    } catch (PDOException $e) {
        $output[$table] = ['_error' => $e->getMessage()];
    }
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database tables – 20 rows each</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background: #f5f5f5; }
        h1 { color: #333; }
        h2 { color: #555; margin-top: 2em; border-bottom: 1px solid #ccc; padding-bottom: 4px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 2em; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ddd; padding: 6px 10px; text-align: left; font-size: 13px; }
        th { background: #37474f; color: #fff; }
        tr:nth-child(even) { background: #fafafa; }
        .count { color: #666; font-size: 14px; }
        .error { color: #c62828; }
        .toc { background: #fff; padding: 15px; margin-bottom: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .toc a { color: #1565c0; text-decoration: none; }
        .toc a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>Database tables – up to <?php echo $limit; ?> rows each</h1>
    <p class="count">Total tables: <?php echo count($tables); ?></p>

    <div class="toc">
        <strong>Jump to table:</strong>
        <?php foreach ($tables as $t): ?>
            <a href="#table-<?php echo htmlspecialchars($t); ?>"><?php echo htmlspecialchars($t); ?></a>
            <?php echo $t !== end($tables) ? ' | ' : ''; ?>
        <?php endforeach; ?>
    </div>

    <?php foreach ($output as $tableName => $rows): ?>
        <h2 id="table-<?php echo htmlspecialchars($tableName); ?>"><?php echo htmlspecialchars($tableName); ?></h2>
        <?php if (isset($rows['_error'])): ?>
            <p class="error">Error: <?php echo htmlspecialchars($rows['_error']); ?></p>
        <?php elseif (empty($rows)): ?>
            <p class="count">(0 rows)</p>
        <?php else: ?>
            <p class="count"><?php echo count($rows); ?> row(s)</p>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <?php foreach (array_keys($rows[0]) as $col): ?>
                                <th><?php echo htmlspecialchars($col); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <?php foreach ($row as $val): ?>
                                    <td><?php echo htmlspecialchars($val !== null ? $val : 'NULL'); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</body>
</html>
