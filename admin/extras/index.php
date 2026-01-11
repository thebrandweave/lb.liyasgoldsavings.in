<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$menuPath = "../";
$currentPage = "extras";

// Database connection
require_once($menuPath . "../config/config.php");
$database = new Database();
$conn = $database->getConnection();

include($menuPath . "components/sidebar.php");
include($menuPath . "components/topbar.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Extras</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .extras-container {
            padding: 20px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .extras-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 25px;
        }

        .extras-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 30px;
        }

        .extra-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            padding: 32px 24px 24px 24px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            transition: box-shadow 0.3s, transform 0.3s;
            position: relative;
        }

        .extra-card:hover {
            box-shadow: 0 8px 20px rgba(58, 123, 213, 0.13);
            transform: translateY(-4px) scale(1.02);
        }

        .extra-icon {
            font-size: 32px;
            color: var(--primary-color);
            margin-bottom: 18px;
        }

        .extra-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }

        .extra-desc {
            font-size: 14px;
            color: #666;
            margin-bottom: 18px;
        }

        .extra-action {
            background: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px 22px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }

        .extra-action:hover {
            background: var(--hover-color);
            color: #fff;
        }

        @media (max-width: 700px) {
            .extras-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="content-wrapper">
        <div class="extras-container">
            <div class="extras-title"><i class="fas fa-toolbox"></i> Admin Extras & Tools</div>
            <div class="extras-grid">
                <!-- Repayment Excel Download -->
                <div class="extra-card">
                    <div class="extra-icon"><i class="fas fa-file-excel"></i></div>
                    <div class="extra-title">Get montly payments Excel</div>
                    <div class="extra-desc">Download monthly payments data in Excel format for reconciliation and reporting.</div>
                    <a href="./get_repayment_excel.php" class="extra-action">Open</a>
                </div>
                <!-- Upload Repayment Commission -->
                <div class="extra-card">
                    <div class="extra-icon"><i class="fas fa-upload"></i></div>
                    <div class="extra-title">Upload Repayment Commission</div>
                    <div class="extra-desc">Upload commission data for repayments to update promoter/customer records.</div>
                    <a href="#" class="extra-action">Upload File</a>
                </div>
                <!-- KYC Management -->
                <!-- <div class="extra-card">
                    <div class="extra-icon"><i class="fas fa-id-card"></i></div>
                    <div class="extra-title">KYC Management</div>
                    <div class="extra-desc">Manage and verify KYC documents for customers and promoters.</div>
                    <a href="#" class="extra-action">Manage KYC</a>
                </div> -->
                <!-- Wallet/Commission Logs -->
                <!-- <div class="extra-card">
                    <div class="extra-icon"><i class="fas fa-wallet"></i></div>
                    <div class="extra-title">Wallet & Commission Logs</div>
                    <div class="extra-desc">View detailed logs of wallet transactions and commission payouts.</div>
                    <a href="#" class="extra-action">View Logs</a>
                </div> -->
                <!-- Backup/Restore -->
                <!-- <div class="extra-card">
                    <div class="extra-icon"><i class="fas fa-database"></i></div>
                    <div class="extra-title">Backup & Restore</div>
                    <div class="extra-desc">Create and restore backups of your database and important files.</div>
                    <a href="#" class="extra-action">Backup/Restore</a>
                </div> -->
                <!-- WhatsApp API Config -->
                <!-- <div class="extra-card">
                    <div class="extra-icon"><i class="fab fa-whatsapp"></i></div>
                    <div class="extra-title">WhatsApp API Config</div>
                    <div class="extra-desc">Configure WhatsApp API settings for automated notifications and alerts.</div>
                    <a href="#" class="extra-action">Configure</a>
                </div> -->
            </div>
        </div>
    </div>
</body>

</html>