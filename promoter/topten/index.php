<?php
session_start();

// Check if promoter is logged in
if (!isset($_SESSION['promoter_id'])) {
    header("Location: ../login.php");
    exit();
}

$menuPath = "../";
$currentPage = "leaderboard"; 
$promoterUniqueID = $_SESSION['promoter_id'];

require_once("../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

// Fetch distinct weeks available
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
} else {
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
}

$stmt->execute();
$topEarners = $stmt->fetchAll(PDO::FETCH_ASSOC);

$podiumEarners = array_slice($topEarners, 0, 3);
$remainingEarners = array_slice($topEarners, 3);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elite Performance Leaderboard | Golden Dreams</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <style>
        :root {
            --primary-emerald: rgb(155, 128, 18);
            --primary-glow: rgba(13, 106, 80, 0.15);
            --bg-surface: #ffffff;
            --bg-body: #f8fafc;
            --border-subtle: #f1f5f9;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --gold-gradient: linear-gradient(135deg, #f3df46 0%, #f3df46 100%);
            --silver-gradient: linear-gradient(135deg, #717272 0%, #b5bdc7 100%);
            --bronze-gradient: linear-gradient(135deg, #f8c480 0%, #ca8a04 100%);
            --card-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.04);
            --radius-lg: 24px;
            --radius-md: 16px;
            --transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-dark);
            -webkit-font-smoothing: antialiased;
        }

        .content-wrapper {
            padding: 40px;
            margin-left: var(--sidebar-width);
            transition: var(--transition);
            padding-top: calc(var(--topbar-height) + 40px) !important;
        }

        .leaderboard-container {
            max-width: 1381px;
            margin: 0 auto;
        }

        /* Modernized Header Area Layout */
        .board-top-bar {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 40px;
            gap: 24px;
        }

        .title-group h1 {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: -0.75px;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .title-group p {
            color: var(--text-muted);
            font-size: 15px;
            margin-top: 4px;
        }

        .week-selector {
            padding: 14px 20px;
            border-radius: var(--radius-md);
            border: 1px solid #e2e8f0;
            min-width: 300px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
            background-color: var(--bg-surface);
            cursor: pointer;
            outline: none;
            transition: var(--transition);
            box-shadow: var(--card-shadow);
        }

        .week-selector:focus {
            border-color: var(--primary-emerald);
            box-shadow: 0 0 0 4px var(--primary-glow);
        }

        /* Luxury Podium Design */
        .podium-wrapper {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            margin-bottom: 48px;
            align-items: end;
        }

        .podium-card {
            background: var(--bg-surface);
            border-radius: var(--radius-lg);
            padding: 36px 24px;
            text-align: center;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-subtle);
            position: relative;
            transition: var(--transition);
            cursor: pointer;
            overflow: hidden;
        }

        .podium-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: transparent;
        }

        .podium-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 40px 60px -20px rgba(13, 106, 80, 0.12);
        }

        /* Advanced Order Handling & Height Hierarchies */
        .rank-first {
            order: 2;
            padding: 56px 28px 44px 28px;
            border: 1px solid rgba(234, 179, 8, 0.3);
            background: linear-gradient(180deg, rgba(254, 240, 138, 0.1) 0%, #ffffff 100%);
        }
        .rank-first::before { background: var(--gold-gradient); }

        .rank-second { 
            order: 1; 
            border: 1px solid rgba(203, 213, 225, 0.4);
        }
        .rank-second::before { background: var(--silver-gradient); }

        .rank-third { 
            order: 3; 
            border: 1px solid rgba(202, 138, 4, 0.15);
        }
        .rank-third::before { background: var(--bronze-gradient); }

        /* Avatars and Badging styling */
        .avatar-container {
            position: relative;
            width: fit-content;
            margin: 0 auto 24px auto;
        }

        .podium-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--bg-surface);
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
            transition: var(--transition);
        }
        .rank-first .podium-img { width: 130px; height: 130px; }

        .podium-badge-pill {
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .rank-first .podium-badge-pill { background: var(--gold-gradient); color: #713f12; font-size: 13px;}
        .rank-second .podium-badge-pill { background: var(--silver-gradient); color: #334155; }
        .rank-third .podium-badge-pill { background: var(--bronze-gradient); color: #7c2d12; }

        .podium-name {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 4px;
            letter-spacing: -0.25px;
        }
        .rank-first .podium-name { font-size: 24px; }

        .podium-id {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
            margin-bottom: 20px;
        }

        .podium-earnings {
            font-size: 26px;
            font-weight: 800;
            color: var(--primary-emerald);
            letter-spacing: -0.5px;
        }
        .rank-first .podium-earnings { font-size: 34px; }

        .applaud-trigger {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px 20px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 24px;
            color: var(--text-muted);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }
        .podium-card:hover .applaud-trigger {
            background: var(--primary-emerald);
            color: #fff;
            border-color: var(--primary-emerald);
            box-shadow: 0 10px 20px -5px rgba(13, 106, 80, 0.3);
        }

        /* Modernized List rows (Ranks 4 - 10) */
        .list-wrapper {
            background: var(--bg-surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-subtle);
            padding: 12px;
        }

        .list-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 24px;
            border-radius: var(--radius-md);
            transition: var(--transition);
            cursor: pointer;
        }

        .list-row:not(:last-child) {
            margin-bottom: 4px;
        }

        .list-row:hover {
            background: #f8fafc;
        }

        .list-left {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .list-rank {
            font-size: 14px;
            font-weight: 800;
            color: var(--text-muted);
            width: 32px;
            text-align: center;
        }

        .list-img {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
        }

        .list-meta h4 {
            color: var(--text-dark);
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .list-meta p {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .list-right {
            text-align: right;
        }

        .list-earnings {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .list-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            .board-top-bar {
                flex-direction: column;
                align-items: flex-start;
            }
            .week-selector {
                width: 100%;
            }
            .podium-wrapper {
                grid-template-columns: 1fr;
                gap: 24px;
            }
            .podium-card {
                width: 100% !important;
                order: unset !important;
                padding: 32px !important;
            }
            .podium-img {
                width: 100px !important;
                height: 100px !important;
            }
        }

        @media (max-width: 768px) {
            .content-wrapper {
                padding: 20px;
                margin-left: 0;
            }
            .title-group h1 {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>
    <?php include('../components/sidebar.php'); ?>
    <?php include('../components/topbar.php'); ?>

    <div class="content-wrapper">
        <div class="leaderboard-container">
            
            <div class="board-top-bar">
                <div class="title-group">
                    <h1><i class="fas fa-trophy" style="color: #eab308;"></i>Weekly Top Earners</h1>
                    <p>Honoring the top earners and active network drivers across the system</p>
                </div>
                <form method="GET">
                    <select name="week" onchange="this.form.submit()" class="week-selector">
                        <option value="">Current Tracking Week</option>
                        <?php foreach($weeks as $week): ?>
                            <option value="<?= $week['WeekStartDate'] ?>" <?= ($selectedWeek == $week['WeekStartDate']) ? 'selected' : '' ?>>
                                <?= date('d M Y', strtotime($week['WeekStartDate'])) ?> &mdash; <?= date('d M Y', strtotime($week['WeekEndDate'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <div class="podium-wrapper">
                <?php 
                $podiumClasses = [1 => 'rank-first', 2 => 'rank-second', 3 => 'rank-third'];
                $podiumLabels = [1 => '1st Place', 2 => '2nd Place', 3 => '3rd Place'];
                
                foreach($podiumEarners as $index => $row): 
                    $rank = $index + 1;
                    $cardClass = $podiumClasses[$rank];
                    $pillLabel = $podiumLabels[$rank];
                    
                    $profileImage = !empty($row['ProfileImageURL']) 
                        ? $row['ProfileImageURL'] 
                        : '../assets/images/default-user.png';
                ?>
                    <div class="podium-card <?= $cardClass ?>" onclick="popConfetti(event)">
                        <div class="avatar-container">
                            <img src="<?= htmlspecialchars($profileImage) ?>" class="podium-img" alt="Profile">
                            <span class="podium-badge-pill"><?= $pillLabel ?></span>
                        </div>
                        <div class="podium-name"><?= htmlspecialchars($row['Name']) ?></div>
                        <div class="podium-id">Promoter ID: <?= htmlspecialchars($row['PromoterUniqueID']) ?></div>
                        <div class="podium-earnings">₹<?= number_format($row['TotalEarnings'], 2) ?></div>
                        <button class="applaud-trigger"><i class="fa-solid fa-hands-clapping"></i> Applaud Performance</button>
                    </div>
                <?php endforeach; ?>
            </div>

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
                            <div class="list-rank">#<?= sprintf("%02d", $currentRank) ?></div>
                            <img src="<?= htmlspecialchars($profileImage) ?>" class="list-img" alt="Profile">
                            <div class="list-meta">
                                <h4><?= htmlspecialchars($row['Name']) ?></h4>
                                <p>ID: <?= htmlspecialchars($row['PromoterUniqueID']) ?></p>
                            </div>
                        </div>
                        <div class="list-right">
                            <div class="list-earnings">₹<?= number_format($row['TotalEarnings'], 2) ?></div>
                            <div class="list-label">Gross Earnings</div>
                        </div>
                    </div>
                <?php 
                    $currentRank++;
                endforeach; 
                ?>
                
                <?php if (empty($topEarners)): ?>
                    <div style="text-align: center; padding: 64px 24px;">
                        <i class="fas fa-crown" style="font-size: 40px; color: #cbd5e1; margin-bottom: 16px;"></i>
                        <h3 style="color: var(--text-dark); margin-bottom: 4px; font-weight: 700;">No Submissions Posted</h3>
                        <p style="color: var(--text-muted); font-size: 14px;">Leaderboard logs are currently blank for this week track.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Smooth Canvas Confetti Loop Execution Engine
        window.addEventListener('DOMContentLoaded', () => {
            const end = Date.now() + (1200);

            (function frame() {
                confetti({
                    particleCount: 3,
                    angle: 60,
                    spread: 55,
                    origin: { x: 0, y: 0.85 },
                    colors: ['#000000', '#eab308', '#8a8a8a']
                });
                confetti({
                    particleCount: 3,
                    angle: 120,
                    spread: 55,
                    origin: { x: 1, y: 0.85 },
                    colors: ['#000000', '#eab308', '#8a8a8a']
                });

                if (Date.now() < end) {
                    requestAnimationFrame(frame);
                }
            }());
        });

        function popConfetti(event) {
            const xPosition = event.clientX / window.innerWidth;
            const yPosition = event.clientY / window.innerHeight;

            confetti({
                particleCount: 30,
                spread: 60,
                origin: { x: xPosition, y: yPosition },
                colors: ['#000000', '#eab308']
            });
        }

        // Structural Sidebar mutation listening adjustments
        const sidebar = document.getElementById('sidebar');
        const content = document.querySelector('.content-wrapper');

        function adjustContent() {
            if (!sidebar || !content) return;

            if (sidebar.classList.contains('collapsed')) {
                content.style.marginLeft = 'var(--sidebar-collapsed-width)';
            } else {
                content.style.marginLeft = 'var(--sidebar-width)';
            }
        }

        adjustContent();

        if (sidebar) {
            const observer = new MutationObserver(adjustContent);
            observer.observe(sidebar, {
                attributes: true
            });
        }
    </script>
</body>
</html>