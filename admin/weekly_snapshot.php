<?php
/**
 * Weekly Leaderboard Snapshot Generator
 * Run this script via cron job every Monday to capture the top earners of the previous week.
 * Example cron entry:
 * 0 0 * * 1 php /path/to/project/admin/weekly_snapshot.php
 */

require_once("../config/config.php");

$database = new Database();
$conn = $database->getConnection();

try {
    // Calculate previous week's Monday (WeekStartDate)
    $weekStart = date('Y-m-d', strtotime('monday last week'));

    // Check if snapshot for this week already exists to prevent duplicate runs
    $check = $conn->prepare("
        SELECT COUNT(*)
        FROM WeeklyTopEarners
        WHERE WeekStartDate = ?
    ");
    $check->execute([$weekStart]);

    if ($check->fetchColumn() > 0) {
        exit("Weekly snapshot already exists for WeekStartDate: $weekStart\n");
    }

    echo "Generating weekly top earners snapshot for week starting $weekStart...\n";

    // SQL query requested by user to automate weekly earners snapshot
    $sql = "
        INSERT INTO WeeklyTopEarners
        (
            WeekStartDate,
            WeekEndDate,
            RankNo,
            PromoterUniqueID,
            Name,
            ProfileImageURL,
            TotalEarnings
        )
        SELECT
            DATE_SUB(CURDATE(), INTERVAL 7 DAY) AS WeekStartDate,
            DATE_SUB(CURDATE(), INTERVAL 1 DAY) AS WeekEndDate,
            ROW_NUMBER() OVER (
                ORDER BY SUM(
                    CASE
                        WHEN wl.TransactionType = 'Credit'
                        THEN wl.Amount
                        ELSE -wl.Amount
                    END
                ) DESC
            ) AS RankNo,
            p.PromoterUniqueID,
            p.Name,
            p.ProfileImageURL,
            SUM(
                CASE
                    WHEN wl.TransactionType = 'Credit'
                    THEN wl.Amount
                    ELSE -wl.Amount
                END
            ) AS TotalEarnings
        FROM WalletLogs wl
        JOIN Promoters p
            ON p.PromoterUniqueID = wl.PromoterUniqueID
        WHERE wl.CreatedAt >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
          AND wl.CreatedAt < CURDATE()
          AND p.PromoterUniqueID NOT IN (
                'GD012009',
                'GD012071',
                'GD012157',
                'GD012169',
                'GD011551',
                'GD011516',
                'GD012111',
                'GD011521',
                'GD012206',
                'GD012120',
                'GDP0904',
                'GD011946',
                'GD011811',
                'GD012198',
                'GDP0667',
                'GDP0816',
                'GDP0931',
                'GDP0822'
          )
        GROUP BY
            p.PromoterUniqueID,
            p.Name,
            p.ProfileImageURL
        ORDER BY TotalEarnings DESC
        LIMIT 10
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    echo "Weekly snapshot generated successfully! " . $stmt->rowCount() . " rows inserted.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>