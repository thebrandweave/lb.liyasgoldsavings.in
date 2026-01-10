<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../../login.php");
    exit();
}

// Check if the logged-in admin has SuperAdmin privileges
if ($_SESSION['admin_role'] !== 'SuperAdmin') {
    $_SESSION['error_message'] = "You don't have permission to access SMS settings.";
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
    $stmt = $conn->query("SELECT * FROM SMSAPIConfig ORDER BY ConfigID DESC LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no settings exist, create a default record
    if (!$settings) {
        $stmt = $conn->prepare("INSERT INTO SMSAPIConfig (APIProviderName, APIEndpoint, Username, Password, CustomerID, SourceAddress, DLTEntityID, DLTTemplateID, MessageType, MessageTemplate, Status) VALUES ('Airtel', 'https://iqsms.airtel.in/api/v1/send-prepaid-sms', '', '', '', '', '', '', 'SERVICE_EXPLICIT', '', 'Active')");
        $stmt->execute();
        $settings = [
            'ConfigID' => $conn->lastInsertId(),
            'APIProviderName' => 'Airtel',
            'APIEndpoint' => 'https://iqsms.airtel.in/api/v1/send-prepaid-sms',
            'Username' => '',
            'Password' => '',
            'CustomerID' => '',
            'SourceAddress' => '',
            'DLTEntityID' => '',
            'DLTTemplateID' => '',
            'MessageType' => 'SERVICE_EXPLICIT',
            'MessageTemplate' => '',
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

        // Update existing record
        $stmt = $conn->prepare("
            UPDATE SMSAPIConfig SET 
                APIProviderName = :providerName,
                APIEndpoint = :endpoint,
                Username = :username,
                Password = :password,
                CustomerID = :customerId,
                SourceAddress = :sourceAddress,
                DLTEntityID = :dltEntityId,
                DLTTemplateID = :dltTemplateId,
                MessageType = :messageType,
                MessageTemplate = :messageTemplate,
                Status = :status
            WHERE ConfigID = :configId
        ");

        $params = [
            ':configId' => $settings['ConfigID'],
            ':providerName' => $_POST['provider_name'],
            ':endpoint' => $_POST['api_endpoint'],
            ':username' => $_POST['username'],
            ':password' => $_POST['password'],
            ':customerId' => $_POST['customer_id'],
            ':sourceAddress' => $_POST['source_address'],
            ':dltEntityId' => $_POST['dlt_entity_id'],
            ':dltTemplateId' => $_POST['dlt_template_id'],
            ':messageType' => $_POST['message_type'],
            ':messageTemplate' => $_POST['message_template'],
            ':status' => $_POST['status']
        ];

        $stmt->execute($params);

        // Log the activity
        $action = "Updated SMS API settings";
        $stmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Admin', ?, ?)");
        $stmt->execute([$_SESSION['admin_id'], $action, $_SERVER['REMOTE_ADDR']]);

        $conn->commit();
        $_SESSION['success_message'] = "SMS API settings updated successfully.";
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
    <title>SMS API Settings</title>
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

        .test-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }

        .test-section h4 {
            margin-bottom: 15px;
            color: var(--text-dark);
        }

        .test-form {
            display: flex;
            gap: 10px;
            align-items: end;
        }

        .test-form .form-group {
            margin-bottom: 0;
            flex: 1;
        }

        .test-form .btn {
            margin-bottom: 0;
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

            .test-form {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <div class="page-header">
            <h1 class="page-title">SMS API Settings</h1>
            <a href="../" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Settings
            </a>
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
                    <i class="fas fa-sms"></i>
                    Airtel SMS API Configuration
                </h2>
                <div class="form-group">
                    <label for="provider_name">API Provider Name</label>
                    <input type="text" id="provider_name" name="provider_name" class="form-control"
                        value="<?php echo htmlspecialchars($settings['APIProviderName'] ?? ''); ?>" required>
                    <p class="help-text">Enter the name of your SMS API provider (e.g., Airtel)</p>
                </div>

                <div class="form-group">
                    <label for="api_endpoint">API Endpoint</label>
                    <input type="url" id="api_endpoint" name="api_endpoint" class="form-control"
                        value="<?php echo htmlspecialchars($settings['APIEndpoint'] ?? ''); ?>" required>
                    <p class="help-text">The base URL for the SMS API service</p>
                </div>
            </div>

            <!-- Authentication Section -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-key"></i>
                    Authentication Details
                </h2>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control"
                        value="<?php echo htmlspecialchars($settings['Username'] ?? ''); ?>" required>
                    <p class="help-text">Your Airtel SMS API username</p>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control"
                        value="<?php echo htmlspecialchars($settings['Password'] ?? ''); ?>" required>
                    <p class="help-text">Your Airtel SMS API password</p>
                </div>

                <div class="form-group">
                    <label for="customer_id">Customer ID</label>
                    <input type="text" id="customer_id" name="customer_id" class="form-control"
                        value="<?php echo htmlspecialchars($settings['CustomerID'] ?? ''); ?>" required>
                    <p class="help-text">Your Airtel customer ID</p>
                </div>
            </div>

            <!-- SMS Configuration Section -->
            <div class="form-section">
                <h2 class="section-title">
                    <i class="fas fa-cog"></i>
                    SMS Configuration
                </h2>
                <div class="form-group">
                    <label for="source_address">Source Address (Sender ID)</label>
                    <input type="text" id="source_address" name="source_address" class="form-control"
                        value="<?php echo htmlspecialchars($settings['SourceAddress'] ?? ''); ?>" required>
                    <p class="help-text">The sender ID that will appear on SMS messages (max 6 characters)</p>
                </div>

                <div class="form-group">
                    <label for="dlt_entity_id">DLT Entity ID</label>
                    <input type="text" id="dlt_entity_id" name="dlt_entity_id" class="form-control"
                        value="<?php echo htmlspecialchars($settings['DLTEntityID'] ?? ''); ?>" required>
                    <p class="help-text">Your DLT (Distributed Ledger Technology) entity ID</p>
                </div>

                <div class="form-group">
                    <label for="dlt_template_id">DLT Template ID</label>
                    <input type="text" id="dlt_template_id" name="dlt_template_id" class="form-control"
                        value="<?php echo htmlspecialchars($settings['DLTTemplateID'] ?? ''); ?>" required>
                    <p class="help-text">Your DLT template ID for SMS content</p>
                </div>

                <div class="form-group">
                    <label for="message_type">Message Type</label>
                    <select id="message_type" name="message_type" class="form-control" required>
                        <option value="SERVICE_EXPLICIT" <?php echo ($settings['MessageType'] ?? '') === 'SERVICE_EXPLICIT' ? 'selected' : ''; ?>>Service Explicit</option>
                        <option value="text" <?php echo ($settings['MessageType'] ?? '') === 'text' ? 'selected' : ''; ?>>Text</option>
                        <option value="unicode" <?php echo ($settings['MessageType'] ?? '') === 'unicode' ? 'selected' : ''; ?>>Unicode</option>
                    </select>
                    <p class="help-text">Type of message content (SERVICE_EXPLICIT for DLT templates)</p>
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
                    <p class="help-text">Enable or disable the SMS API integration</p>
                </div>
            </div>

            <!-- Test Section -->
            <div class="test-section">
                <h4><i class="fas fa-flask"></i> Test SMS Configuration</h4>
                <p class="help-text">Test your SMS configuration by sending a test message</p>
                <div class="test-form">
                    <div class="form-group">
                        <label for="test_phone">Phone Number</label>
                        <input type="tel" id="test_phone" class="form-control" placeholder="Enter phone number">
                    </div>
                    <div class="form-group">
                        <label for="test_message">Test Message</label>
                        <input type="text" id="test_message" class="form-control" placeholder="Enter test message" value="Test message from Golden Dream">
                    </div>
                    <button type="button" class="btn btn-primary" onclick="testSMS()">
                        <i class="fas fa-paper-plane"></i> Send Test
                    </button>
                </div>
                <div id="test_result" style="margin-top: 15px; display: none;"></div>
            </div>

            <!-- Logs Information Section -->
            <div class="test-section" style="margin-top: 20px;">
                <h4><i class="fas fa-file-alt"></i> SMS Error Logs</h4>
                <p class="help-text">SMS errors are logged to PHP error log. Check the following locations:</p>
                <ul style="list-style: none; padding: 0; margin: 10px 0;">
                    <li style="padding: 5px 0;"><i class="fas fa-file" style="color: #3498db; margin-right: 8px;"></i> <strong>XAMPP Apache:</strong> <code>C:\xampp\apache\logs\error.log</code></li>
                    <li style="padding: 5px 0;"><i class="fas fa-file" style="color: #3498db; margin-right: 8px;"></i> <strong>XAMPP PHP:</strong> <code>C:\xampp\php\logs\php_error_log</code></li>
                    <li style="padding: 5px 0;"><i class="fas fa-info-circle" style="color: #f39c12; margin-right: 8px;"></i> <strong>Note:</strong> Log file location may vary based on your PHP configuration</li>
                </ul>
                <p class="help-text" style="margin-top: 10px; color: #e74c3c;">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Tip:</strong> When testing SMS, check the error details shown in the alert dialog. The error message will contain specific details about what went wrong.
                </p>
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
    </div>

    <script>
        // Add fade-out effect for alerts
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 500);
                });
            }, 3000);
        });

        function testSMS() {
            const phone = document.getElementById('test_phone').value;
            const message = document.getElementById('test_message').value;

            if (!phone || !message) {
                alert('Please enter both phone number and message');
                return;
            }

            // Show loading state
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            btn.disabled = true;

            fetch('test_sms.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        phone: phone,
                        message: message
                    })
                })
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('test_result');
                    resultDiv.style.display = 'block';

                    if (data.success) {
                        resultDiv.innerHTML = '<div style="padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 6px; color: #155724;"><i class="fas fa-check-circle"></i> <strong>Success!</strong> ' + (data.message || 'Test SMS sent successfully') + '</div>';
                        alert('Test SMS sent successfully!');
                    } else {
                        // Build detailed error message
                        let errorHtml = '<div style="padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 6px; color: #721c24;">';
                        errorHtml += '<i class="fas fa-exclamation-circle"></i> <strong>Failed to send test SMS</strong><br><br>';

                        // Get error message - prioritize error field over message field
                        let errorMsg = data.error || data.message || 'Unknown error occurred';
                        if (errorMsg === 'Failed to send test SMS') {
                            errorMsg = 'No detailed error information available. Check debug info below.';
                        }
                        errorHtml += '<strong>Error:</strong> ' + errorMsg + '<br>';

                        if (data.httpCode && data.httpCode !== 'N/A') {
                            errorHtml += '<strong>HTTP Code:</strong> ' + data.httpCode + '<br>';
                        }

                        // Show debug info if available
                        if (data.debug) {
                            errorHtml += '<br><strong>Debug Information:</strong><br>';
                            errorHtml += '<small style="color: #666;">Result Type: ' + (data.debug.result_type || 'unknown') + '<br>';
                            if (data.debug.result_keys) {
                                errorHtml += 'Result Keys: ' + data.debug.result_keys.join(', ') + '<br>';
                            }
                            errorHtml += '</small>';
                        }

                        if (data.logPath) {
                            errorHtml += '<br><strong>Log File Location:</strong><br>';
                            errorHtml += '<code style="background: #fff; padding: 5px; border-radius: 3px; display: inline-block; margin-top: 5px; font-size: 11px;">' + data.logPath + '</code><br>';
                            if (data.logPathExists) {
                                errorHtml += '<span style="color: #28a745;"><i class="fas fa-check"></i> File exists</span><br>';
                            } else {
                                errorHtml += '<span style="color: #dc3545;"><i class="fas fa-times"></i> File not found (check PHP error_log setting)</span><br>';
                            }
                        }

                        if (data.apiResponse) {
                            errorHtml += '<br><strong>API Response:</strong><br>';
                            let responseStr = typeof data.apiResponse === 'object' ? JSON.stringify(data.apiResponse, null, 2) : data.apiResponse;
                            errorHtml += '<pre style="background: #fff; padding: 10px; border-radius: 3px; overflow-x: auto; max-height: 200px; font-size: 11px; white-space: pre-wrap;">' + responseStr + '</pre>';
                        }

                        errorHtml += '</div>';
                        resultDiv.innerHTML = errorHtml;

                        // Also show alert with key info - make sure we show the actual error, not the generic message
                        let alertMsg = 'Failed to send test SMS\n\n';
                        if (data.error && data.error !== 'Unknown error occurred' && data.error !== 'Failed to send test SMS') {
                            alertMsg += 'Error: ' + data.error + '\n';
                        } else if (data.message && data.message !== 'Failed to send test SMS') {
                            alertMsg += 'Message: ' + data.message + '\n';
                        } else {
                            alertMsg += 'Error: Unable to determine specific error. Check details below.\n';
                        }
                        if (data.httpCode && data.httpCode !== 'N/A') {
                            alertMsg += 'HTTP Code: ' + data.httpCode + '\n';
                        }
                        if (data.logPath) {
                            alertMsg += '\nCheck detailed error in the result box below.';
                        }
                        alert(alertMsg);
                    }

                    // Scroll to result
                    resultDiv.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                })
                .catch(error => {
                    const resultDiv = document.getElementById('test_result');
                    resultDiv.style.display = 'block';
                    resultDiv.innerHTML = '<div style="padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 6px; color: #721c24;"><i class="fas fa-exclamation-circle"></i> <strong>Error:</strong> ' + error.message + '</div>';
                    alert('Error sending test SMS: ' + error.message);
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }
    </script>
</body>

</html>