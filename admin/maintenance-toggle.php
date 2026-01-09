<?php
session_start();
require_once '../config/config.php';
require_once '../config/maintenance.php';

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Handle AJAX requests
if (isset($_POST['action'])) {
    $response = ['success' => false, 'message' => '', 'status' => false];
    
    if ($_POST['action'] === 'toggle') {
        $current_status = is_maintenance_enabled();
        
        if ($current_status) {
            // Currently enabled, so disable
            if (disable_maintenance()) {
                $response = [
                    'success' => true, 
                    'message' => 'Maintenance mode disabled successfully!',
                    'status' => false
                ];
            } else {
                $response = ['success' => false, 'message' => 'Failed to disable maintenance mode.'];
            }
        } else {
            // Currently disabled, so enable
            if (enable_maintenance()) {
                $response = [
                    'success' => true, 
                    'message' => 'Maintenance mode enabled successfully!',
                    'status' => true
                ];
            } else {
                $response = ['success' => false, 'message' => 'Failed to enable maintenance mode.'];
            }
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Get current maintenance status
$current_status = is_maintenance_enabled();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Toggle - Golden Dream Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            border: 1px solid #e2e8f0;
        }

        .header {
            margin-bottom: 32px;
        }

        .header h1 {
            color: #1a202c;
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .header p {
            color: #718096;
            font-size: 1rem;
        }

        .status-card {
            background: #f7fafc;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
            border: 2px solid #e2e8f0;
            position: relative;
            transition: all 0.3s ease;
        }

        .status-card.updating {
            animation: statusUpdate 0.5s ease;
        }

        @keyframes statusUpdate {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-bottom: 12px;
            font-size: 20px;
            color: white;
            position: relative;
            transition: all 0.3s ease;
        }

        .status-indicator.changing {
            animation: statusChange 0.6s ease;
        }

        @keyframes statusChange {
            0% { transform: scale(1) rotate(0deg); }
            50% { transform: scale(1.1) rotate(180deg); }
            100% { transform: scale(1) rotate(360deg); }
        }

        .status-active {
            background: #e53e3e;
            animation: pulse 2s infinite;
        }

        .status-inactive {
            background: #38a169;
        }

        .status-active::after {
            content: '';
            position: absolute;
            top: -3px;
            left: -3px;
            right: -3px;
            bottom: -3px;
            border-radius: 50%;
            background: #e53e3e;
            opacity: 0.3;
            animation: pulse-ring 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes pulse-ring {
            0% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.2); opacity: 0.1; }
            100% { transform: scale(1.4); opacity: 0; }
        }

        .status-text {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
            transition: all 0.3s ease;
        }

        .status-description {
            color: #718096;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .toggle-button {
            width: 100%;
            padding: 20px 32px;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            position: relative;
            overflow: hidden;
            transform: translateY(0);
            margin-bottom: 32px;
        }

        .toggle-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .toggle-button:hover::before {
            left: 100%;
        }

        .toggle-button:hover {
            transform: translateY(-3px);
        }

        .toggle-button:active {
            transform: translateY(-1px);
        }

        .toggle-button.clicked {
            animation: buttonClick 0.3s ease;
        }

        @keyframes buttonClick {
            0% { transform: translateY(-3px) scale(1); }
            50% { transform: translateY(-1px) scale(0.95); }
            100% { transform: translateY(-3px) scale(1); }
        }

        .toggle-button:disabled {
            background: #a0aec0 !important;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
            opacity: 0.6;
        }

        .toggle-button.loading {
            pointer-events: none;
            opacity: 0.7;
            transform: translateY(-2px);
        }

        .toggle-button.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .toggle-enabled {
            background: #e53e3e;
            color: white;
        }

        .toggle-enabled:hover:not(:disabled):not(.loading) {
            box-shadow: 0 8px 20px rgba(229, 62, 62, 0.3);
        }

        .toggle-enabled:active:not(:disabled):not(.loading) {
            box-shadow: 0 4px 12px rgba(229, 62, 62, 0.4);
        }

        .toggle-disabled {
            background: #38a169;
            color: white;
        }

        .toggle-disabled:hover:not(:disabled):not(.loading) {
            box-shadow: 0 8px 20px rgba(56, 161, 105, 0.3);
        }

        .toggle-disabled:active:not(:disabled):not(.loading) {
            box-shadow: 0 4px 12px rgba(56, 161, 105, 0.4);
        }

        .back-btn {
            background: #718096;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: #4a5568;
            transform: translateY(-1px);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: slideIn 0.3s ease-out;
            transition: all 0.3s ease;
        }

        .alert.hide {
            opacity: 0;
            transform: translateY(-10px);
            pointer-events: none;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #68d391;
        }

        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #fc8181;
        }

        .info-box {
            background: #ebf8ff;
            border: 1px solid #90cdf4;
            border-radius: 8px;
            padding: 16px;
            margin-top: 24px;
            text-align: left;
        }

        .info-box h3 {
            color: #2b6cb0;
            margin-bottom: 8px;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-box p {
            color: #4a5568;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .container {
                padding: 24px 20px;
                margin: 16px;
            }

            .header h1 {
                font-size: 1.75rem;
            }

            .status-card {
                padding: 20px;
            }

            .toggle-button {
                padding: 18px 24px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-cogs"></i> Maintenance Control</h1>
            <p>Toggle website maintenance mode</p>
        </div>

        <div id="alert-container"></div>

        <div class="status-card" id="status-card">
            <div class="status-indicator" id="status-indicator">
                <i class="fas" id="status-icon"></i>
            </div>
            <div class="status-text" id="status-text">
                Maintenance Mode: <span id="status-value"><?php echo $current_status ? 'ACTIVE' : 'INACTIVE'; ?></span>
            </div>
            <div class="status-description" id="status-description">
                <?php echo $current_status ? 'Website is currently under maintenance' : 'Website is running normally'; ?>
            </div>
        </div>

        <button type="button" id="toggle-btn" class="toggle-button <?php echo $current_status ? 'toggle-enabled' : 'toggle-disabled'; ?>">
            <i class="fas" id="toggle-icon"></i>
            <span id="toggle-text"></span>
        </button>

        <a href="dashboard/" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Dashboard</span>
        </a>

        <div class="info-box">
            <h3>
                <i class="fas fa-info-circle"></i>
                How it works
            </h3>
            <p>
                Click the button to toggle maintenance mode. When enabled, all users (except allowed IPs) will be redirected to the maintenance page. 
                This affects the customer panel, promoter panel, and admin panel.
            </p>
        </div>
    </div>

    <script>
        let currentStatus = <?php echo $current_status ? 'true' : 'false'; ?>;
        
        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alert-container');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                <span>${message}</span>
            `;
            
            alertContainer.appendChild(alert);
            
            // Auto-hide after 3 seconds
            setTimeout(() => {
                alert.classList.add('hide');
                setTimeout(() => alert.remove(), 300);
            }, 3000);
        }
        
        function updateStatus(newStatus) {
            const statusCard = document.getElementById('status-card');
            const statusIndicator = document.getElementById('status-indicator');
            const statusIcon = document.getElementById('status-icon');
            const statusValue = document.getElementById('status-value');
            const statusDescription = document.getElementById('status-description');
            const toggleBtn = document.getElementById('toggle-btn');
            const toggleIcon = document.getElementById('toggle-icon');
            const toggleText = document.getElementById('toggle-text');
            
            // Add update animation to status card
            statusCard.classList.add('updating');
            statusIndicator.classList.add('changing');
            
            // Update status after animation
            setTimeout(() => {
                // Update status
                currentStatus = newStatus;
                
                // Update status indicator
                statusIndicator.className = `status-indicator ${newStatus ? 'status-active' : 'status-inactive'}`;
                statusIcon.className = `fas ${newStatus ? 'fa-exclamation-triangle' : 'fa-check'}`;
                
                // Update text
                statusValue.textContent = newStatus ? 'ACTIVE' : 'INACTIVE';
                statusDescription.textContent = newStatus ? 'Website is currently under maintenance' : 'Website is running normally';
                
                // Update toggle button
                toggleBtn.className = `toggle-button ${newStatus ? 'toggle-enabled' : 'toggle-disabled'}`;
                toggleIcon.className = `fas ${newStatus ? 'fa-stop-circle' : 'fa-play-circle'}`;
                toggleText.textContent = newStatus ? 'Disable Maintenance' : 'Enable Maintenance';
                
                // Remove animation classes
                statusCard.classList.remove('updating');
                statusIndicator.classList.remove('changing');
            }, 300);
        }
        
        function toggleMaintenance() {
            const toggleBtn = document.getElementById('toggle-btn');
            
            // Add click animation
            toggleBtn.classList.add('clicked');
            setTimeout(() => toggleBtn.classList.remove('clicked'), 300);
            
            // Add loading state
            toggleBtn.classList.add('loading');
            toggleBtn.disabled = true;
            
            // Send AJAX request
            fetch('maintenance-toggle.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=toggle'
            })
            .then(response => response.json())
            .then(data => {
                // Remove loading state
                toggleBtn.classList.remove('loading');
                toggleBtn.disabled = false;
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    updateStatus(data.status);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toggleBtn.classList.remove('loading');
                toggleBtn.disabled = false;
                showAlert('An error occurred. Please try again.', 'error');
            });
        }
        
        // Event listener
        document.getElementById('toggle-btn').addEventListener('click', toggleMaintenance);
        
        // Initialize status and button
        updateStatus(currentStatus);
    </script>
</body>
</html>
