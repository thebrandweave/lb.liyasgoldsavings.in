<?php
session_start();

$menuPath = "../";
$currentPage = "popups";

// Database connection
require_once("../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

// Check authentication
require_once("../middleware/auth.php");
verifyAuth();

// Get current active popup
$stmt = $conn->prepare("SELECT p.*, a.Name as CreatedByName FROM Popups p LEFT JOIN Admins a ON p.CreatedBy = a.AdminID WHERE p.IsActive = 1 LIMIT 1");
$stmt->execute();
$activePopup = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['popup_image'])) {
    try {
        $conn->beginTransaction();

        // Validate file upload
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES['popup_image']['type'];
        $fileSize = $_FILES['popup_image']['size'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception("Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.");
        }

        if ($fileSize > $maxSize) {
            throw new Exception("File size exceeds 5MB limit.");
        }

        // Create uploads directory if it doesn't exist
        $uploadDir = '../../uploads/popups/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate unique filename
        $fileExtension = pathinfo($_FILES['popup_image']['name'], PATHINFO_EXTENSION);
        $fileName = 'popup_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;

        // Move uploaded file
        if (!move_uploaded_file($_FILES['popup_image']['tmp_name'], $uploadPath)) {
            throw new Exception("Failed to upload image. Please try again.");
        }

        // Get relative path for database
        $imageURL = 'uploads/popups/' . $fileName;

        // Deactivate all existing popups first
        $stmt = $conn->prepare("UPDATE Popups SET IsActive = 0 WHERE IsActive = 1");
        $stmt->execute();

        // Insert new popup as active
        $stmt = $conn->prepare("INSERT INTO Popups (ImageURL, IsActive, CreatedBy) VALUES (?, 1, ?)");
        $stmt->execute([$imageURL, $_SESSION['admin_id']]);

        // Log activity
        $stmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Admin', ?, ?)");
        $stmt->execute([
            $_SESSION['admin_id'],
            "Updated promoter popup image",
            $_SERVER['REMOTE_ADDR']
        ]);

        $conn->commit();
        $_SESSION['success_message'] = "Popup updated successfully!";

        // Redirect to avoid resubmission
        header("Location: index.php");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
}

// Include header and sidebar
include("../components/sidebar.php");
include("../components/topbar.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promoter Popups Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .popup-container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .popup-container h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: #333;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .file-upload-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            z-index: 2;
        }

        .file-upload-display {
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-upload-display:hover {
            border-color: #000000;
            background: #f0f4ff;
        }

        .file-upload-display.has-image {
            border: 2px solid #000000;
            background: white;
            padding: 20px;
        }

        .file-upload-display i {
            font-size: 48px;
            color: #000000;
            margin-bottom: 15px;
        }

        .file-upload-display p {
            color: #666;
            margin: 10px 0;
            font-size: 14px;
        }

        .file-upload-display .file-name {
            color: #000000;
            font-weight: 500;
            margin-top: 10px;
        }

        .preview-image {
            max-width: 100%;
            max-height: 400px;
            border-radius: 8px;
            margin-top: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #000000, #878b8b);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(58, 123, 213, 0.3);
        }

        .btn-primary:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .current-popup {
            background: #e8f5e9;
            border: 2px solid #4caf50;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .current-popup h3 {
            color: #2e7d32;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .current-popup img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .current-popup .popup-info {
            margin-top: 15px;
            color: #666;
            font-size: 14px;
        }

        .current-popup .popup-info strong {
            color: #2e7d32;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
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

        .info-text {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-top: 20px;
            border-radius: 4px;
            color: #1565c0;
            font-size: 14px;
        }

        .info-text i {
            margin-right: 8px;
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Promoter Popups Management</h1>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Current Active Popup -->
            <?php if ($activePopup): ?>
                <div class="current-popup">
                    <h3><i class="fas fa-check-circle"></i> Current Active Popup</h3>
                    <img src="../../<?php echo htmlspecialchars($activePopup['ImageURL']); ?>" alt="Active Popup">
                    <div class="popup-info">
                        <p><strong>Uploaded by:</strong> <?php echo htmlspecialchars($activePopup['CreatedByName'] ?? 'Unknown'); ?></p>
                        <p><strong>Created at:</strong> <?php echo date('F d, Y h:i A', strtotime($activePopup['CreatedAt'])); ?></p>
                        <p><strong>Updated at:</strong> <?php echo date('F d, Y h:i A', strtotime($activePopup['UpdatedAt'])); ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <i class="fas fa-info-circle"></i> No active popup found. Please upload a popup image.
                </div>
            <?php endif; ?>

            <!-- Upload Form -->
            <div class="popup-container">
                <h2><i class="fas fa-upload"></i> Upload New Popup</h2>
                <p style="color: #666; margin-bottom: 20px;">Upload a new popup image. The new popup will automatically replace the current active popup.</p>

                <form method="POST" enctype="multipart/form-data" id="popupForm">
                    <div class="form-group">
                        <label for="popup_image">Popup Image *</label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="popup_image" name="popup_image" class="file-upload-input" accept="image/*" required>
                            <div class="file-upload-display" id="fileDisplay">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p><strong>Click to upload</strong> or drag and drop</p>
                                <p>PNG, JPG, GIF, WebP (MAX. 5MB)</p>
                            </div>
                            <div id="imagePreview" style="display: none;">
                                <img src="" alt="Preview" class="preview-image" id="previewImg">
                                <p class="file-name" id="fileName"></p>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-upload"></i> Upload & Activate Popup
                    </button>
                </form>

                <div class="info-text">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> Only one popup can be active at a time. Uploading a new popup will automatically deactivate the previous one.
                </div>
            </div>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('popup_image');
        const fileDisplay = document.getElementById('fileDisplay');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        const fileName = document.getElementById('fileName');
        const form = document.getElementById('popupForm');

        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.');
                    fileInput.value = '';
                    return;
                }

                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size exceeds 5MB limit.');
                    fileInput.value = '';
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    fileName.textContent = file.name;
                    fileDisplay.classList.add('has-image');
                    fileDisplay.style.display = 'none';
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Allow clicking on file display to trigger file input
        fileDisplay.addEventListener('click', function() {
            fileInput.click();
        });

        // Form submission
        form.addEventListener('submit', function(e) {
            if (!fileInput.files.length) {
                e.preventDefault();
                alert('Please select an image file.');
                return;
            }
        });
    </script>
</body>

</html>

