<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Check if the logged-in admin has SuperAdmin privileges
if ($_SESSION['admin_role'] !== 'SuperAdmin') {
    $_SESSION['error_message'] = "You don't have permission to access WhatsApp settings.";
    header("Location: ../../dashboard/index.php");
    exit();
}

$menuPath = "../../";
$currentPage = "settings";

// Database connection
require_once("../../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

// Get current settings
try {
    // Ensure VerifyToken column exists in database table
    $stmt = $conn->query("SHOW COLUMNS FROM WhatsAppAPIConfig LIKE 'VerifyToken'");
    if (!$stmt->fetch()) {
        $conn->exec("ALTER TABLE WhatsAppAPIConfig ADD COLUMN VerifyToken VARCHAR(255) DEFAULT 'liyas_whatsapp_verify_token_2026'");
    }

    $stmt = $conn->query("SELECT * FROM WhatsAppAPIConfig ORDER BY ConfigID DESC LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no settings exist, create a default record
    if (!$settings) {
        $stmt = $conn->prepare("INSERT INTO WhatsAppAPIConfig (APIProviderName, APIEndpoint, AccessToken, Token, InstanceID, PhoneNumberID, DefaultTemplateName, TemplateLanguageCode, VerifyToken, Status) VALUES ('Meta Cloud API', 'https://graph.facebook.com/v25.0', '', '', '', '', 'hello_world', 'en_US', 'liyas_whatsapp_verify_token_2026', 'Active')");
        $stmt->execute();
        $settings = [
            'ConfigID' => $conn->lastInsertId(),
            'APIProviderName' => 'Meta Cloud API',
            'APIEndpoint' => 'https://graph.facebook.com/v25.0',
            'AccessToken' => '',
            'PhoneNumberID' => '',
            'DefaultTemplateName' => 'hello_world',
            'TemplateLanguageCode' => 'en_US',
            'VerifyToken' => 'liyas_whatsapp_verify_token_2026',
            'Status' => 'Active'
        ];
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Failed to fetch settings: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();

        $verifyToken = trim($_POST['verify_token'] ?? '');
        if (empty($verifyToken)) {
            $verifyToken = 'liyas_whatsapp_verify_token_2026';
        }

        // Update existing record
        $stmt = $conn->prepare("
            UPDATE WhatsAppAPIConfig SET 
                APIProviderName = 'Meta Cloud API',
                APIEndpoint = :endpoint,
                AccessToken = :accessToken,
                PhoneNumberID = :phoneNumberId,
                DefaultTemplateName = :defaultTemplateName,
                TemplateLanguageCode = :templateLanguageCode,
                VerifyToken = :verifyToken,
                Status = :status
            WHERE ConfigID = :configId
        ");

        $params = [
            ':configId' => $settings['ConfigID'],
            ':endpoint' => $_POST['api_endpoint'],
            ':accessToken' => $_POST['access_token'],
            ':phoneNumberId' => $_POST['phone_number_id'],
            ':defaultTemplateName' => $_POST['default_template_name'],
            ':templateLanguageCode' => $_POST['template_language_code'],
            ':verifyToken' => $verifyToken,
            ':status' => $_POST['status']
        ];

        $stmt->execute($params);

        // Log the activity
        $action = "Updated WhatsApp API settings";
        $stmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Admin', ?, ?)");
        $stmt->execute([$_SESSION['admin_id'], $action, $_SERVER['REMOTE_ADDR']]);

        $conn->commit();
        $_SESSION['success_message'] = "WhatsApp API settings updated successfully.";
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error_message'] = "Failed to update settings: " . $e->getMessage();
    }

    header("Location: index.php");
    exit();
}


// Include header and sidebar
include("../../components/sidebar.php");
include("../../components/topbar.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Meta Cloud API Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <style>
        .settings-form {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--ad_primary-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-medium);
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--ad_primary-color);
            box-shadow: 0 0 0 3px rgba(58, 123, 213, 0.1);
            outline: none;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--ad_primary-color);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background: var(--ad_primary-hover);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: white;
            color: var(--text-dark);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--bg-light);
            transform: translateY(-2px);
        }

        .help-text {
            font-size: 12px;
            color: var(--text-light);
            margin-top: 5px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-dark);
            margin: 0;
        }

        @media (max-width: 768px) {
            .settings-form {
                padding: 20px;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h1 class="page-title">WhatsApp Meta Cloud API Settings</h1>
            <div style="display: flex; gap: 10px;">
                <a href="replies.php" class="btn btn-primary" style="background: #25D366; border: none;">
                    <i class="fab fa-whatsapp"></i> View Customer Replies
                </a>
                <a href="../" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Settings
                </a>
            </div>
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

        <form class="settings-form" method="POST">
            <!-- API Configuration Section -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fab fa-whatsapp"></i>
                    Meta Cloud API Configuration
                </h2>

                <div class="form-group">
                    <label for="api_endpoint">API Endpoint</label>
                    <input type="url" id="api_endpoint" name="api_endpoint" class="form-control"
                        value="<?php echo htmlspecialchars($settings['APIEndpoint'] ?? ''); ?>" required>
                    <p class="help-text">Meta Graph API base URL (example: https://graph.facebook.com/v25.0)</p>
                </div>
            </div>

            <!-- Authentication Section -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-key"></i>
                    Authentication Details
                </h2>
                <div class="form-group">
                    <label for="access_token">Access Token</label>
                    <div style="position: relative;">
                        <input type="password" id="access_token" name="access_token" class="form-control" style="padding-right: 40px;"
                            value="<?php echo htmlspecialchars($settings['AccessToken'] ?? ''); ?>" required placeholder="EAAG...">
                        <button type="button" id="toggleToken" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666;">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <p class="help-text">Copy from Meta Developer Dashboard -> <strong>Step 1: Access token</strong> (or System User token)</p>
                </div>
                <div class="form-group">
                    <label for="phone_number_id">Phone Number ID</label>
                    <input type="text" id="phone_number_id" name="phone_number_id" class="form-control"
                        value="<?php echo htmlspecialchars($settings['PhoneNumberID'] ?? ''); ?>" required placeholder="e.g. 105432198765432">
                    <p class="help-text">Copy from Meta Developer Dashboard -> <strong>Step 2: Send a message</strong> -> <strong>Phone number ID</strong></p>
                </div>
                <div class="form-group">
                    <label for="default_template_name">Default Template Name</label>
                    <input type="text" id="default_template_name" name="default_template_name" class="form-control"
                        value="<?php echo htmlspecialchars($settings['DefaultTemplateName'] ?? 'hello_world'); ?>" required>
                    <p class="help-text">Fallback template name for generic test notifications (example: hello_world)</p>
                </div>
                <div class="form-group">
                    <label for="template_language_code">Template Language Code</label>
                    <input type="text" id="template_language_code" name="template_language_code" class="form-control"
                        value="<?php echo htmlspecialchars($settings['TemplateLanguageCode'] ?? 'en_US'); ?>" required>
                    <p class="help-text">Template language code (example: en_US or en)</p>
                </div>
            </div>

            <!-- Incoming Webhook Section -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-reply-all"></i>
                    Incoming Messages Webhook Setup
                </h2>
                <div class="form-group">
                    <label>Webhook Callback URL</label>
                    <input type="text" class="form-control" value="https://lb.liyasgoldsavings.in/api/whatsapp_webhook.php" readonly style="background-color: #f8f9fa;">
                    <p class="help-text">Paste this URL into Meta Developer Console $\rightarrow$ <strong>WhatsApp</strong> $\rightarrow$ <strong>Configuration</strong> $\rightarrow$ <strong>Edit Webhook</strong>.</p>
                </div>
                <div class="form-group">
                    <label for="verify_token">Webhook Verify Token</label>
                    <input type="text" id="verify_token" name="verify_token" class="form-control"
                        value="<?php echo htmlspecialchars($settings['VerifyToken'] ?? 'liyas_whatsapp_verify_token_2026'); ?>" required>
                    <p class="help-text">Token used by Meta to verify your webhook subscription.</p>
                </div>
            </div>

            <!-- Status Section -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-toggle-on"></i>
                    API Status
                </h2>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="Active" <?php echo ($settings['Status'] ?? '') === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo ($settings['Status'] ?? '') === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                    <p class="help-text">Enable or disable the WhatsApp API integration</p>
                </div>
            </div>


            <div class="btn-group">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='../'">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>

        <!-- Test Connection Section -->
        <div class="settings-form" style="margin-top: 30px;">
            <div class="form-section" style="border-bottom: none; padding-bottom: 0;">
                <h2 class="section-title">
                    <i class="fas fa-paper-plane"></i>
                    Test WhatsApp Connection
                </h2>
                <p class="help-text" style="margin-bottom: 15px; font-size: 13px;">
                    Send a test <code>hello_world</code> template message to verify your Meta WhatsApp Cloud API credentials. Make sure you saved your settings above before testing.
                </p>

                <div id="testAlert" style="display: none; padding: 12px; border-radius: 6px; margin-bottom: 15px; font-size: 14px;"></div>

                <div class="form-group">
                    <label for="test_phone">Recipient Mobile Number</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="test_phone" class="form-control" placeholder="e.g. 917902796976 or 7902796976" style="flex: 1;">
                        <button type="button" id="btnTestWA" class="btn btn-primary" style="white-space: nowrap;">
                            <i class="fas fa-paper-plane"></i> Send Test Message
                        </button>
                    </div>
                    <p class="help-text">Include country code (e.g. 917902796976). Note: For unverified Meta apps, recipient number must be added to your Meta test recipient list.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fade-out alerts
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 500);
                });
            }, 3000);

            // Toggle Access Token visibility
            const toggleBtn = document.getElementById('toggleToken');
            const tokenInput = document.getElementById('access_token');
            if (toggleBtn && tokenInput) {
                toggleBtn.addEventListener('click', function() {
                    const isPwd = tokenInput.type === 'password';
                    tokenInput.type = isPwd ? 'text' : 'password';
                    this.innerHTML = isPwd ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
                });
            }

            // Test WhatsApp message execution
            const btnTestWA = document.getElementById('btnTestWA');
            const testPhoneInput = document.getElementById('test_phone');
            const testAlert = document.getElementById('testAlert');

            if (btnTestWA) {
                btnTestWA.addEventListener('click', function() {
                    const phone = testPhoneInput.value.trim();
                    if (!phone) {
                        testAlert.style.display = 'block';
                        testAlert.style.background = '#f8d7da';
                        testAlert.style.color = '#721c24';
                        testAlert.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Please enter a valid recipient phone number.';
                        return;
                    }

                    btnTestWA.disabled = true;
                    btnTestWA.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
                    testAlert.style.display = 'block';
                    testAlert.style.background = '#e2e3e5';
                    testAlert.style.color = '#383d41';
                    testAlert.innerHTML = '<i class="fas fa-sync fa-spin"></i> Sending test message to Meta WhatsApp API...';

                    fetch('test_hello_world.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ phone: phone })
                    })
                    .then(res => res.json())
                    .then(data => {
                        btnTestWA.disabled = false;
                        btnTestWA.innerHTML = '<i class="fas fa-paper-plane"></i> Send Test Message';

                        if (data.success) {
                            testAlert.style.background = '#d4edda';
                            testAlert.style.color = '#155724';
                            testAlert.innerHTML = '<i class="fas fa-check-circle"></i> <strong>Success!</strong> ' + data.message;
                        } else {
                            testAlert.style.background = '#f8d7da';
                            testAlert.style.color = '#721c24';
                            let errDetail = data.message || 'Failed to send message.';
                            if (data.response && data.response.error && data.response.error.message) {
                                errDetail += ' Details: ' + data.response.error.message;
                            }
                            testAlert.innerHTML = '<i class="fas fa-times-circle"></i> <strong>Error:</strong> ' + errDetail;
                        }
                    })
                    .catch(err => {
                        btnTestWA.disabled = false;
                        btnTestWA.innerHTML = '<i class="fas fa-paper-plane"></i> Send Test Message';
                        testAlert.style.background = '#f8d7da';
                        testAlert.style.color = '#721c24';
                        testAlert.innerHTML = '<i class="fas fa-times-circle"></i> Network error: ' + err.message;
                    });
                });
            }
        });
    </script>
</body>

</html>