<?php
session_start();

$menuPath = "../";
$currentPage = "careers_applications";

// Database connection
require_once("../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

// Check authentication
require_once("../middleware/auth.php");
verifyAuth();

$successMessage = "";
$errorMessage = "";

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        $appId = (int)$_POST['application_id'];
        $newStatus = trim($_POST['status']);
        
        $allowedStatuses = ['Pending', 'Reviewed', 'Accepted', 'Rejected'];
        if (!in_array($newStatus, $allowedStatuses)) {
            throw new Exception("Invalid status selection.");
        }
        
        // Fetch application details first
        $stmt = $conn->prepare("SELECT ca.FullName, ca.Email, ca.Position, ca.Status as PreviousStatus, co.Title as OpeningTitle 
                                FROM CareerApplications ca 
                                LEFT JOIN CareerOpenings co ON ca.OpeningID = co.OpeningID 
                                WHERE ca.ApplicationID = ? LIMIT 1");
        $stmt->execute([$appId]);
        $appDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$appDetails) {
            throw new Exception("Application not found.");
        }
        
        $updateStmt = $conn->prepare("UPDATE CareerApplications SET Status = ? WHERE ApplicationID = ?");
        $updateStmt->execute([$newStatus, $appId]);
        
        // Send email status update if it has changed to Accepted or Rejected
        if ($newStatus !== $appDetails['PreviousStatus']) {
            $positionName = $appDetails['OpeningTitle'] ? $appDetails['OpeningTitle'] : $appDetails['Position'];
            
            if ($newStatus === 'Accepted') {
                $emailSubject = "Application Status Update - Accepted";
                $emailBody = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                        <div style='text-align: center; margin-bottom: 20px;'>
                            <h2 style='color: #a36d16; margin: 0;'>Liyas Gold Savings</h2>
                            <p style='color: #666; font-size: 14px; margin: 5px 0 0 0;'>Careers & Opportunities</p>
                        </div>
                        <hr style='border: 0; border-top: 1px solid #f1f1f1; margin: 20px 0;'>
                        <p>Dear <strong>" . htmlspecialchars($appDetails['FullName']) . "</strong>,</p>
                        <p>We are pleased to inform you that your application for the <strong>" . htmlspecialchars($positionName) . "</strong> position at Liyas Gold Savings has been <strong>Accepted</strong>.</p>
                        <p>Our HR team will contact you shortly to discuss the next steps in our hiring process and schedule an onboarding session.</p>
                        <p>Congratulations, and we look forward to working with you!</p>
                        <p style='margin-top: 25px;'>Best regards,<br><strong>HR Team</strong><br>Liyas Gold Savings</p>
                        <hr style='border: 0; border-top: 1px solid #f1f1f1; margin: 20px 0;'>
                        <p style='font-size: 11px; color: #999; text-align: center;'>This is an automated email notification. Please do not reply directly to this message.</p>
                    </div>
                ";
                
                try {
                    require_once("../../config/email.php");
                    sendSMTPMail($appDetails['Email'], $emailSubject, $emailBody);
                } catch (Exception $mailEx) {
                    error_log("Failed to send acceptance email to " . $appDetails['Email'] . ": " . $mailEx->getMessage());
                }
            } elseif ($newStatus === 'Rejected') {
                $emailSubject = "Application Status Update - Liyas Gold Savings";
                $emailBody = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                        <div style='text-align: center; margin-bottom: 20px;'>
                            <h2 style='color: #a36d16; margin: 0;'>Liyas Gold Savings</h2>
                            <p style='color: #666; font-size: 14px; margin: 5px 0 0 0;'>Careers & Opportunities</p>
                        </div>
                        <hr style='border: 0; border-top: 1px solid #f1f1f1; margin: 20px 0;'>
                        <p>Dear <strong>" . htmlspecialchars($appDetails['FullName']) . "</strong>,</p>
                        <p>Thank you for your interest in the <strong>" . htmlspecialchars($positionName) . "</strong> position at Liyas Gold Savings and for taking the time to apply.</p>
                        <p>After careful review of all applications, we regret to inform you that we have decided to move forward with other candidates whose qualifications closely align with our current requirements.</p>
                        <p>We appreciate your interest in Liyas Gold Savings and wish you the best of luck in your job search and future professional endeavors.</p>
                        <p style='margin-top: 25px;'>Best regards,<br><strong>HR Team</strong><br>Liyas Gold Savings</p>
                        <hr style='border: 0; border-top: 1px solid #f1f1f1; margin: 20px 0;'>
                        <p style='font-size: 11px; color: #999; text-align: center;'>This is an automated email notification. Please do not reply directly to this message.</p>
                    </div>
                ";
                
                try {
                    require_once("../../config/email.php");
                    sendSMTPMail($appDetails['Email'], $emailSubject, $emailBody);
                } catch (Exception $mailEx) {
                    error_log("Failed to send rejection email to " . $appDetails['Email'] . ": " . $mailEx->getMessage());
                }
            }
        }
        
        // Log activity
        $logStmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Admin', ?, ?)");
        $logStmt->execute([$_SESSION['admin_id'], "Updated status of career application ID $appId to $newStatus", $_SERVER['REMOTE_ADDR']]);
        
        $_SESSION['success_message'] = "Application status updated successfully!";
        header("Location: applications.php");
        exit();
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

// Fetch all applications
$query = "SELECT ca.*, co.Title as OpeningTitle 
          FROM CareerApplications ca 
          LEFT JOIN CareerOpenings co ON ca.OpeningID = co.OpeningID 
          ORDER BY ca.CreatedAt DESC";
$applications = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Session messages
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Include sidebar and topbar
include("../components/sidebar.php");
include("../components/topbar.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Applications - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .app-container {
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        .header-title-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f1f1f1;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header-title-flex h2 {
            font-size: 24px;
            color: #333;
            margin: 0;
        }
        .alert-box {
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }
        .app-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .app-table th, .app-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        .app-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #444;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-pending { background: #ffeeba; color: #856404; }
        .badge-reviewed { background: #cce5ff; color: #004085; }
        .badge-accepted { background: #d4edda; color: #155724; }
        .badge-rejected { background: #f8d7da; color: #721c24; }

        .btn-action-small {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-view {
            background: #000;
            color: #fff;
        }
        .btn-view:hover {
            background: #222;
        }
        .btn-download {
            background: #17a2b8;
            color: #fff;
        }
        .btn-download:hover {
            background: #138496;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
        }
        .modal.show {
            display: flex;
        }
        .modal-content {
            background-color: #fff;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h3 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #aaa;
        }
        .modal-close:hover {
            color: #333;
        }
        .modal-body {
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
        }
        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            text-align: right;
        }
        .form-inline-status {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .modal-detail-row {
            margin-bottom: 12px;
            font-size: 14px;
        }
        .modal-detail-row strong {
            display: inline-block;
            width: 130px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <div class="container-fluid">
            
            <?php if (!empty($successMessage)): ?>
                <div class="alert-box alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="alert-box alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>
            
            <div class="app-container">
                <div class="header-title-flex">
                    <h2>Job Applications Received</h2>
                </div>
                
                <div class="table-responsive">
                    <table class="app-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Applicant</th>
                                <th>Position</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Applied Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($applications)): ?>
                                <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td><?php echo $app['ApplicationID']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($app['FullName']); ?></strong></td>
                                        <td>
                                            <?php 
                                            if ($app['OpeningTitle']) {
                                                echo htmlspecialchars($app['OpeningTitle']);
                                            } else {
                                                echo '<span style="color:#777;">' . htmlspecialchars($app['Position']) . ' (General)</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <div style="font-size: 12px;">
                                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($app['Email']); ?><br>
                                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($app['Phone']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo strtolower($app['Status']); ?>">
                                                <?php echo $app['Status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d M Y, h:i A', strtotime($app['CreatedAt'])); ?></td>
                                        <td>
                                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                                <button class="btn-action-small btn-view" onclick="openDetailsModal(<?php echo htmlspecialchars(json_encode($app)); ?>)">
                                                    <i class="fas fa-eye"></i> View Details
                                                </button>
                                                <a href="../../<?php echo htmlspecialchars($app['ResumeURL']); ?>" target="_blank" class="btn-action-small btn-download">
                                                    <i class="fas fa-file-pdf"></i> Resume
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: #777; padding: 30px;">No applications received yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>

    <!-- Application Details Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Application Details</h3>
                <button class="modal-close" onclick="closeDetailsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="modal-detail-row">
                    <strong>FullName:</strong> <span id="m_name"></span>
                </div>
                <div class="modal-detail-row">
                    <strong>Email Address:</strong> <span id="m_email"></span>
                </div>
                <div class="modal-detail-row">
                    <strong>Phone Number:</strong> <span id="m_phone"></span>
                </div>
                <div class="modal-detail-row">
                    <strong>Position:</strong> <span id="m_position"></span>
                </div>
                <div class="modal-detail-row">
                    <strong>Applied Date:</strong> <span id="m_date"></span>
                </div>
                
                <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">
                
                <h4 style="margin: 0 0 10px 0; font-size: 15px; color: #333;">Brief Statement / Cover Letter</h4>
                <div id="m_cover" style="background: #f8f9fa; padding: 15px; border-radius: 6px; font-size: 13.5px; line-height: 1.5; white-space: pre-wrap; color: #444; border: 1px solid #eef0f2;">
                </div>
                
                <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
                
                <form action="applications.php" method="POST" class="form-inline-status">
                    <input type="hidden" id="m_app_id" name="application_id">
                    <label for="m_status" style="font-weight: 500; font-size: 14px; color: #333;">Update Application Status:</label>
                    <select id="m_status" name="status" class="form-select" style="width: auto; padding: 6px 12px; font-size: 14px;">
                        <option value="Pending">Pending</option>
                        <option value="Reviewed">Reviewed</option>
                        <option value="Accepted">Accepted</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                    <button type="submit" name="update_status" class="btn-action-small btn-view" style="padding: 8px 16px;">
                        Update
                    </button>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-custom btn-outline" onclick="closeDetailsModal()" style="padding: 8px 16px; font-size: 14px;">Close</button>
            </div>
        </div>
    </div>

    <script>
        function openDetailsModal(app) {
            document.getElementById('m_app_id').value = app.ApplicationID;
            document.getElementById('m_name').textContent = app.FullName;
            document.getElementById('m_email').textContent = app.Email;
            document.getElementById('m_phone').textContent = app.Phone;
            document.getElementById('m_position').textContent = app.OpeningTitle ? app.OpeningTitle : app.Position + ' (General Application)';
            
            // Format date
            const date = new Date(app.CreatedAt);
            document.getElementById('m_date').textContent = date.toLocaleString();
            
            document.getElementById('m_cover').textContent = app.CoverLetter;
            document.getElementById('m_status').value = app.Status;
            
            document.getElementById('detailsModal').classList.add('show');
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.remove('show');
        }

        // Close modal when clicking outside content
        window.addEventListener('click', function(e) {
            const modal = document.getElementById('detailsModal');
            if (e.target === modal) {
                closeDetailsModal();
            }
        });
    </script>
</body>
</html>
