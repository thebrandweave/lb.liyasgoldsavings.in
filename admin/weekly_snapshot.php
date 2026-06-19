<?php

require_once("../config/config.php");

$database = new Database();
$conn = $database->getConnection();

try {

    // Previous week Monday
    $weekStart = date(
        'Y-m-d',
        strtotime('monday last week')
    );

    // Previous week Sunday
    $weekEnd = date(
        'Y-m-d',
        strtotime('sunday last week')
    );

    // Check if already generated
    $check = $conn->prepare("
        SELECT COUNT(*)
        FROM WeeklyTopEarners
        WHERE WeekStartDate = ?
    ");

    $check->execute([$weekStart]);

    if ($check->fetchColumn() > 0) {
        exit("Weekly snapshot already exists.");
    }

    // Get Top 10 earners of previous week
    $query = "
    SELECT
        p.PromoterUniqueID,
        p.Name,
        p.ProfileImageURL,

        SUM(
            CASE
                WHEN wl.TransactionType='Credit'
                THEN wl.Amount
                ELSE -wl.Amount
            END
        ) AS TotalEarnings

    FROM WalletLogs wl

    INNER JOIN Promoters p
        ON p.PromoterUniqueID = wl.PromoterUniqueID

    WHERE DATE(wl.CreatedAt)
        BETWEEN ? AND ?

    GROUP BY
        p.PromoterUniqueID,
        p.Name,
        p.ProfileImageURL

    ORDER BY TotalEarnings DESC

    LIMIT 10
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute([$weekStart, $weekEnd]);

    $topEarners = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $rank = 1;

    foreach ($topEarners as $earner) {

        $insert = $conn->prepare("
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
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?
            )
        ");

        $insert->execute([
            $weekStart,
            $weekEnd,
            $rank,
            $earner['PromoterUniqueID'],
            $earner['Name'],
            $earner['ProfileImageURL'],
            $earner['TotalEarnings']
        ]);

        $rank++;
    }

    echo "Weekly leaderboard snapshot created successfully.";

} catch (Exception $e) {

    echo "Error: " . $e->getMessage();
}