<?php
session_start();

require_once("../config/config.php");
$database = new Database();
$conn = $database->getConnection();


$weeksQuery = "
SELECT DISTINCT
    WeekStartDate,
    WeekEndDate
FROM WeeklyTopEarners
ORDER BY WeekStartDate DESC
";

$weeksStmt = $conn->prepare($weeksQuery);
$weeksStmt->execute();

$weeks = $weeksStmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| TOP 10 WEEKLY EARNERS
|--------------------------------------------------------------------------
*/

$selectedWeek = $_GET['week'] ?? '';

if ($selectedWeek != '') {

    $query = "
        SELECT *
        FROM WeeklyTopEarners
        WHERE WeekStartDate = :week
        ORDER BY RankNo ASC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':week', $selectedWeek);
    $stmt->execute();
}
else {

    $query = "
        SELECT *
        FROM WeeklyTopEarners
        WHERE WeekStartDate = (
            SELECT MAX(WeekStartDate)
            FROM WeeklyTopEarners
        )
        ORDER BY RankNo ASC
    ";

    $stmt = $conn->prepare($query);
    $stmt->execute();
}

$topEarners = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare($query);
$stmt->execute();

$topEarners = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Split earners into separate arrays for our podium vs grid architecture
$podiumEarners = array_slice($topEarners, 0, 3);
$remainingEarners = array_slice($topEarners, 3);

?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Weekly Leaderboard</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<style>
:root {
    --gold: #FFD700;
    --silver: #B0BEC5;
    --bronze: #CD7F32;
    --primary-dark: #0f172a;
    --text-main: #334155;
    --success: #10b981;
}

body {
    background-color: #f1f5f9;
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    margin: 0;
    padding: 0;
    color: var(--text-main);
}

.leaderboard-container {
    max-width: 1100px;
    margin: 40px auto;
    padding: 0 20px;
}

.leaderboard-header {
    text-align: center;
    margin-bottom: 50px;
}

.leaderboard-header h1 {
    margin: 0;
    font-size: 40px;
    color: var(--primary-dark);
    font-weight: 800;
    letter-spacing: -1px;
}

.leaderboard-header p {
    color: #64748b;
    margin-top: 10px;
    font-size: 16px;
}

/* ==========================================
   PODIUM SECTION (RANKS 1 - 3)
   ========================================== */
.podium-wrapper {
    display: flex;
    justify-content: center;
    align-items: flex-end;
    gap: 24px;
    margin-bottom: 40px;
    padding-bottom: 20px;
}

.podium-card {
    background: #fff;
    border-radius: 24px;
    padding: 30px 24px;
    text-align: center;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
    border: 2px solid transparent;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    cursor: pointer;
    position: relative;
    width: 28%;
}

.podium-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 35px -5px rgba(0,0,0,0.1);
}

/* Specific heights and visual weight for priority scaling */
.podium-rank-1 {
    order: 2; /* Forces #1 to the middle position */
    width: 34%;
    padding: 45px 30px;
    border-color: var(--gold);
    background: linear-gradient(180deg, #fffbeb 0%, #ffffff 100%);
}

.podium-rank-2 {
    order: 1; /* Left position */
    border-color: var(--silver);
    background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
}

.podium-rank-3 {
    order: 3; /* Right position */
    border-color: var(--bronze);
    background: linear-gradient(180deg, #fdf8f6 0%, #ffffff 100%);
}

/* Visual identity crowns/medals */
.podium-badge {
    font-size: 42px;
    display: block;
    margin-bottom: 12px;
}
.podium-rank-1 .podium-badge { font-size: 56px; margin-bottom: 15px; }

.podium-img {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.podium-rank-1 .podium-img { width: 120px; height: 120px; }

.podium-name {
    margin: 15px 0 4px 0;
    font-size: 20px;
    color: var(--primary-dark);
    font-weight: 700;
}
.podium-rank-1 .podium-name { font-size: 24px; }

.podium-id {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 15px;
}

.podium-earnings {
    font-size: 24px;
    font-weight: 800;
    color: var(--success);
}
.podium-rank-1 .podium-earnings { font-size: 32px; }

.action-bubble {
    background: #f1f5f9;
    border: none;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    margin-top: 15px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #475569;
    transition: background 0.2s;
}
.podium-card:hover .action-bubble {
    background: var(--primary-dark);
    color: #fff;
}

/* ==========================================
   LIST SECTION (RANKS 4 - 10)
   ========================================== */
.list-wrapper {
    background: #fff;
    border-radius: 24px;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
    padding: 10px;
}

.list-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    border-radius: 16px;
    transition: background 0.2s, transform 0.2s;
    cursor: pointer;
}

.list-row:not(:last-child) {
    border-bottom: 1px solid #f1f5f9;
}

.list-row:hover {
    background: #f8fafc;
    transform: scale(1.005);
}

.list-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.list-rank {
    font-size: 16px;
    font-weight: 800;
    color: #94a3b8;
    width: 35px;
    height: 35px;
    background: #f8fafc;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.list-img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.list-meta h4 {
    margin: 0 0 2px 0;
    color: var(--primary-dark);
    font-size: 16px;
}

.list-meta p {
    margin: 0;
    font-size: 13px;
    color: #64748b;
}

.list-right {
    text-align: right;
}

.list-earnings {
    font-size: 18px;
    font-weight: 700;
    color: var(--success);
}

.list-label {
    font-size: 11px;
    color: #94a3b8;
    text-transform: uppercase;
    font-weight: 600;
}

/* ==========================================
   RESPONSIVE OVERRIDES
   ========================================== */
@media (max-width: 992px) {
    .podium-wrapper {
        flex-direction: column;
        align-items: center;
    }
    .podium-card {
        width: 100% !important;
        order: unset !important;
        padding: 30px !important;
    }
    .podium-img {
        width: 90px !important;
        height: 90px !important;
    }
}
</style>

</head>

<body>

<div class="leaderboard-container">
<form method="GET" style="margin-top:20px;">
    <select
        name="week"
        onchange="this.form.submit()"
        style="
            padding:10px;
            border-radius:8px;
            border:1px solid #ddd;
            min-width:280px;
        ">

        <option value="">Latest Week</option>

        <?php foreach($weeks as $week): ?>

            <option
                value="<?= $week['WeekStartDate'] ?>"
                <?= ($selectedWeek == $week['WeekStartDate']) ? 'selected' : '' ?>>

                <?= date('d M Y', strtotime($week['WeekStartDate'])) ?>
                -
                <?= date('d M Y', strtotime($week['WeekEndDate'])) ?>

            </option>

        <?php endforeach; ?>

    </select>
</form>
    <div class="leaderboard-header">
        <h1>🏆 Top Weekly Earners</h1>
        <p>Honoring our top 10 performance champions across the network</p>
    </div>

    <!-- Tier 1 Priority: The Podium Structure -->
    <div class="podium-wrapper">
        <?php 
        $podiumRanks = [1 => 'podium-rank-1', 2 => 'podium-rank-2', 3 => 'podium-rank-3'];
        $podiumBadges = [1 => '🥇', 2 => '🥈', 3 => '🥉'];
        
        foreach($podiumEarners as $index => $row): 
            $currentRank = $index + 1;
            $cardStyleClass = $podiumRanks[$currentRank];
            $badgeIcon = $podiumBadges[$currentRank];
            
            $profileImage = !empty($row['ProfileImageURL']) 
                ? $row['ProfileImageURL'] 
                : '../assets/images/default-user.png';
        ?>
            <div class="podium-card <?= $cardStyleClass ?>" onclick="popConfetti(event)">
                <span class="podium-badge"><?= $badgeIcon ?></span>
                <img src="<?= htmlspecialchars($profileImage) ?>" class="podium-img" alt="Profile">
                <div class="podium-name"><?= htmlspecialchars($row['Name']) ?></div>
                <div class="podium-id">ID: <?= htmlspecialchars($row['PromoterUniqueID']) ?></div>
                <div class="podium-earnings">₹<?= number_format($row['TotalEarnings'], 2) ?></div>
                <button class="action-bubble"><i class="fa-solid fa-hands-clapping"></i> Applaud</button>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Tier 2 Priority: Ranks 4 to 10 Grid -->
    <div class="list-wrapper">
        <?php 
        $currentRank = 4;
        foreach($remainingEarners as $row): 
            $profileImage = !empty($row['ProfileImageURL']) 
                ? $row['ProfileImageURL'] 
                : '../assets/images/default-user.png';
        ?>
            <div class="list-row" onclick="popConfetti(event)">
                <div class="list-left">
                    <div class="list-rank">#<?= $currentRank ?></div>
                    <img src="<?= htmlspecialchars($profileImage) ?>" class="list-img" alt="Profile">
                    <div class="list-meta">
                        <h4><?= htmlspecialchars($row['Name']) ?></h4>
                        <p>ID: <?= htmlspecialchars($row['PromoterUniqueID']) ?></p>
                    </div>
                </div>
                <div class="list-right">
                    <div class="list-earnings">₹<?= number_format($row['TotalEarnings'], 2) ?></div>
                    <div class="list-label">Earnings</div>
                </div>
            </div>
        <?php 
            $currentRank++;
        endforeach; 
        ?>
    </div>

</div>

<script>
// On-load: Vibrant crossfire confetti focusing up towards the podium cards
window.addEventListener('DOMContentLoaded', () => {
    const end = Date.now() + (1.5 * 1000);

    (function frame() {
        confetti({
            particleCount: 4,
            angle: 60,
            spread: 55,
            origin: { x: 0, y: 0.9 }
        });
        confetti({
            particleCount: 4,
            angle: 120,
            spread: 55,
            origin: { x: 1, y: 0.9 }
        });

        if (Date.now() < end) {
            requestAnimationFrame(frame);
        }
    }());
});

// Click Handler: Interactive spot-bursting explosion directly over targeted cards
function popConfetti(event) {
    const xPosition = event.clientX / window.innerWidth;
    const yPosition = event.clientY / window.innerHeight;

    confetti({
        particleCount: 40,
        spread: 70,
        origin: { x: xPosition, y: yPosition }
    });
}
</script>

</body>
</html>