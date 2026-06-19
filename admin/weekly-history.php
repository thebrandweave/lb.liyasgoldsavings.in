```php
<?php
session_start();

require_once("../config/config.php");

$database = new Database();
$conn = $database->getConnection();

/*
|--------------------------------------------------------------------------
| GET ALL AVAILABLE WEEKS
|--------------------------------------------------------------------------
*/

$weeksStmt = $conn->prepare("
    SELECT DISTINCT
        WeekStartDate,
        WeekEndDate
    FROM WeeklyTopEarners
    ORDER BY WeekStartDate DESC
");

$weeksStmt->execute();
$weeks = $weeksStmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| SELECT WEEK
|--------------------------------------------------------------------------
*/

$selectedWeek = $_GET['week'] ?? '';

if(empty($selectedWeek) && !empty($weeks)){
    $selectedWeek = $weeks[0]['WeekStartDate'];
}

/*
|--------------------------------------------------------------------------
| FETCH WINNERS
|--------------------------------------------------------------------------
*/

$leaders = [];

if(!empty($selectedWeek)){

    $leaderStmt = $conn->prepare("
        SELECT *
        FROM WeeklyTopEarners
        WHERE WeekStartDate = ?
        ORDER BY RankNo ASC
    ");

    $leaderStmt->execute([$selectedWeek]);

    $leaders = $leaderStmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Weekly Winners History</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

body{
    margin:0;
    padding:0;
    background:#f8fafc;
    font-family:Segoe UI,sans-serif;
}

.container{
    max-width:1200px;
    margin:40px auto;
    padding:20px;
}

.header{
    text-align:center;
    margin-bottom:30px;
}

.header h1{
    margin:0;
    color:#0f172a;
}

.header p{
    color:#64748b;
}

.filter-box{
    text-align:center;
    margin-bottom:30px;
}

.filter-box select{
    padding:12px 15px;
    border-radius:10px;
    border:1px solid #ddd;
    min-width:300px;
    font-size:15px;
}

.card{
    background:#fff;
    border-radius:15px;
    padding:20px;
    margin-bottom:15px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 4px 15px rgba(0,0,0,.05);
}

.left{
    display:flex;
    align-items:center;
    gap:20px;
}

.rank{
    width:60px;
    text-align:center;
    font-size:28px;
    font-weight:bold;
}

.profile{
    width:70px;
    height:70px;
    border-radius:50%;
    object-fit:cover;
    border:3px solid #FFD700;
}

.info h4{
    margin:0;
    color:#0f172a;
}

.info p{
    margin:5px 0;
    color:#64748b;
}

.right{
    text-align:right;
}

.amount{
    font-size:24px;
    color:#10b981;
    font-weight:700;
}

.badge{
    font-size:32px;
}

@media(max-width:768px){

    .card{
        flex-direction:column;
        text-align:center;
        gap:15px;
    }

    .left{
        flex-direction:column;
    }

    .right{
        text-align:center;
    }
}

</style>
</head>

<body>

<div class="container">

    <div class="header">
        <h1>🏆 Weekly Winners History</h1>
        <p>View all previous weekly top earners</p>
    </div>

    <div class="filter-box">

        <form method="GET">

            <select
                name="week"
                onchange="this.form.submit()">

                <?php foreach($weeks as $week): ?>

                    <option
                        value="<?= $week['WeekStartDate']; ?>"
                        <?= ($selectedWeek == $week['WeekStartDate']) ? 'selected' : ''; ?>>

                        <?= date('d M Y', strtotime($week['WeekStartDate'])); ?>
                        -
                        <?= date('d M Y', strtotime($week['WeekEndDate'])); ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </form>

    </div>

    <?php foreach($leaders as $leader): ?>

        <?php

        $badge = '#'.$leader['RankNo'];

        if($leader['RankNo']==1){
            $badge='🥇';
        }

        if($leader['RankNo']==2){
            $badge='🥈';
        }

        if($leader['RankNo']==3){
            $badge='🥉';
        }

        $image = !empty($leader['ProfileImageURL'])
            ? $leader['ProfileImageURL']
            : '../assets/images/default-user.png';

        ?>

        <div class="card">

            <div class="left">

                <div class="rank">
                    <?= $badge ?>
                </div>

                <img
                    src="<?= htmlspecialchars($image) ?>"
                    class="profile">

                <div class="info">

                    <h4>
                        <?= htmlspecialchars($leader['Name']) ?>
                    </h4>

                    <p>
                        ID:
                        <?= htmlspecialchars($leader['PromoterUniqueID']) ?>
                    </p>

                    <p>
                        🎉 Weekly Champion
                    </p>

                </div>

            </div>

            <div class="right">

                <div class="amount">
                    ₹<?= number_format($leader['TotalEarnings'],2) ?>
                </div>

                <small>
                    Weekly Earnings
                </small>

            </div>

        </div>

    <?php endforeach; ?>

</div>

</body>
</html>
```
