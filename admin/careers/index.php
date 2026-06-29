<?php
session_start();

$menuPath = "../";
$currentPage = "careers";

// Database connection
require_once("../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

// Check authentication
require_once("../middleware/auth.php");
verifyAuth();

$successMessage = "";
$errorMessage = "";

// Handle Delete Action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $id = (int)$_GET['id'];
        
        $stmt = $conn->prepare("DELETE FROM CareerOpenings WHERE OpeningID = ?");
        $stmt->execute([$id]);
        
        // Log activity
        $logStmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Admin', ?, ?)");
        $logStmt->execute([$_SESSION['admin_id'], "Deleted career opening ID: $id", $_SERVER['REMOTE_ADDR']]);
        
        $_SESSION['success_message'] = "Career opening deleted successfully!";
        header("Location: index.php");
        exit();
    } catch (Exception $e) {
        $errorMessage = "Error deleting: " . $e->getMessage();
    }
}

// Handle Toggle Status Action
if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
    try {
        $id = (int)$_GET['id'];
        
        // Fetch current status
        $stmt = $conn->prepare("SELECT Status FROM CareerOpenings WHERE OpeningID = ?");
        $stmt->execute([$id]);
        $currentStatus = $stmt->fetchColumn();
        
        $newStatus = ($currentStatus === 'Active') ? 'Inactive' : 'Active';
        
        $updateStmt = $conn->prepare("UPDATE CareerOpenings SET Status = ? WHERE OpeningID = ?");
        $updateStmt->execute([$newStatus, $id]);
        
        // Log activity
        $logStmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Admin', ?, ?)");
        $logStmt->execute([$_SESSION['admin_id'], "Toggled career opening ID $id to $newStatus", $_SERVER['REMOTE_ADDR']]);
        
        $_SESSION['success_message'] = "Status updated successfully!";
        header("Location: index.php");
        exit();
    } catch (Exception $e) {
        $errorMessage = "Error updating status: " . $e->getMessage();
    }
}

// Handle Add/Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = trim($_POST['title']);
        $location = trim($_POST['location']);
        $type = trim($_POST['type']);
        $description = trim($_POST['description']);
        $requirements = trim($_POST['requirements']);
        $status = $_POST['status'] ?? 'Active';
        
        if (empty($title) || empty($location) || empty($type) || empty($description)) {
            throw new Exception("All required fields must be filled.");
        }
        
        if (isset($_POST['opening_id']) && !empty($_POST['opening_id'])) {
            // Edit mode
            $openingId = (int)$_POST['opening_id'];
            $stmt = $conn->prepare("UPDATE CareerOpenings SET Title = ?, Location = ?, Type = ?, Description = ?, Requirements = ?, Status = ? WHERE OpeningID = ?");
            $stmt->execute([$title, $location, $type, $description, $requirements, $status, $openingId]);
            
            // Log activity
            $logStmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Admin', ?, ?)");
            $logStmt->execute([$_SESSION['admin_id'], "Updated career opening: $title (ID: $openingId)", $_SERVER['REMOTE_ADDR']]);
            
            $_SESSION['success_message'] = "Career opening updated successfully!";
        } else {
            // Add mode
            $stmt = $conn->prepare("INSERT INTO CareerOpenings (Title, Location, Type, Description, Requirements, Status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $location, $type, $description, $requirements, $status]);
            
            // Log activity
            $logStmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Admin', ?, ?)");
            $logStmt->execute([$_SESSION['admin_id'], "Created career opening: $title", $_SERVER['REMOTE_ADDR']]);
            
            $_SESSION['success_message'] = "New career opening added successfully!";
        }
        
        header("Location: index.php");
        exit();
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

// Fetch all openings
$openings = $conn->query("SELECT * FROM CareerOpenings ORDER BY CreatedAt DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch opening if editing
$editOpening = null;
if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $stmt = $conn->prepare("SELECT * FROM CareerOpenings WHERE OpeningID = ?");
    $stmt->execute([$editId]);
    $editOpening = $stmt->fetch(PDO::FETCH_ASSOC);
}

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
    <title>Career Openings Management - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .careers-container {
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
        .form-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
        }
        .form-control, .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            box-sizing: border-box;
        }
        .btn-custom {
            padding: 10px 24px;
            border-radius: 6px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-black {
            background: #000;
            color: #fff;
        }
        .btn-black:hover {
            background: #222;
        }
        .btn-outline {
            border: 1px solid #ccc;
            background: transparent;
            color: #333;
        }
        .btn-outline:hover {
            background: #f8f9fa;
        }
        .table-responsive {
            width: 100%;
            overflow-x: auto;
            margin-top: 20px;
        }
        .careers-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .careers-table th, .careers-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        .careers-table th {
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
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        .badge-inactive {
            background: #e2e3e5;
            color: #383d41;
        }
        .actions-flex {
            display: flex;
            gap: 10px;
        }
        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            border: none;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-edit { background: #007bff; }
        .btn-edit:hover { background: #0056b3; }
        .btn-toggle { background: #28a745; }
        .btn-toggle:hover { background: #218838; }
        .btn-delete { background: #dc3545; }
        .btn-delete:hover { background: #c82333; }
        
        @media (max-width: 768px) {
            .form-row-2 {
                grid-template-columns: 1fr;
            }
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
            
            <!-- Create/Edit Section -->
            <div class="careers-container">
                <div class="header-title-flex">
                    <h2><?php echo $editOpening ? 'Edit Career Opening' : 'Add New Career Opening'; ?></h2>
                    <?php if ($editOpening): ?>
                        <a href="index.php" class="btn-custom btn-outline"><i class="fas fa-plus"></i> Add New Instead</a>
                    <?php endif; ?>
                </div>
                
                <form action="index.php" method="POST">
                    <?php if ($editOpening): ?>
                        <input type="hidden" name="opening_id" value="<?php echo $editOpening['OpeningID']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row-2">
                        <div class="form-group">
                            <label for="title">Job Title *</label>
                            <input type="text" id="title" name="title" class="form-control" required value="<?php echo $editOpening ? htmlspecialchars($editOpening['Title']) : ''; ?>" placeholder="e.g. Wealth Manager">
                        </div>
                        <div class="form-group">
                            <label for="location">Location *</label>
                            <input type="text" id="location" name="location" class="form-control" required value="<?php echo $editOpening ? htmlspecialchars($editOpening['Location']) : ''; ?>" placeholder="e.g. Bantwal, Karnataka (On-site)">
                        </div>
                    </div>
                    
                    <div class="form-row-2">
                        <div class="form-group">
                            <label for="type">Job Type *</label>
                            <input type="text" id="type" name="type" class="form-control" required value="<?php echo $editOpening ? htmlspecialchars($editOpening['Type']) : ''; ?>" placeholder="e.g. Full-time / Part-time / Remote">
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="Active" <?php echo ($editOpening && $editOpening['Status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?php echo ($editOpening && $editOpening['Status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Job Description *</label>
                        <textarea id="description" name="description" class="form-control" rows="4" required placeholder="Describe the responsibilities and role summary..."><?php echo $editOpening ? htmlspecialchars($editOpening['Description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="requirements">Requirements (Optional)</label>
                        <textarea id="requirements" name="requirements" class="form-control" rows="3" placeholder="List required skills, experience, or qualifications..."><?php echo $editOpening ? htmlspecialchars($editOpening['Requirements']) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn-custom btn-black">
                        <i class="fas fa-save"></i> <?php echo $editOpening ? 'Update Opening' : 'Save Opening'; ?>
                    </button>
                </form>
            </div>
            
            <!-- List Section -->
            <div class="careers-container">
                <div class="header-title-flex">
                    <h2>Manage Open Positions</h2>
                </div>
                
                <div class="table-responsive">
                    <table class="careers-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Location</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($openings)): ?>
                                <?php foreach ($openings as $op): ?>
                                    <tr>
                                        <td><?php echo $op['OpeningID']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($op['Title']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($op['Location']); ?></td>
                                        <td><?php echo htmlspecialchars($op['Type']); ?></td>
                                        <td>
                                            <span class="badge <?php echo ($op['Status'] === 'Active') ? 'badge-active' : 'badge-inactive'; ?>">
                                                <?php echo $op['Status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($op['CreatedAt'])); ?></td>
                                        <td>
                                            <div class="actions-flex">
                                                <a href="index.php?edit_id=<?php echo $op['OpeningID']; ?>" class="action-btn btn-edit" title="Edit Opening"><i class="fas fa-edit"></i></a>
                                                <a href="index.php?action=toggle&id=<?php echo $op['OpeningID']; ?>" class="action-btn btn-toggle" title="Toggle Status"><i class="fas fa-sync-alt"></i></a>
                                                <a href="index.php?action=delete&id=<?php echo $op['OpeningID']; ?>" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to delete this job opening?');" title="Delete Opening"><i class="fas fa-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: #777; padding: 30px;">No openings found. Add one above!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>
</body>
</html>
