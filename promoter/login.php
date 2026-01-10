<?php
session_start();

// If promoter is already logged in, redirect to dashboard
if (isset($_SESSION['promoter_id'])) {
    header("Location: dashboard/index.php");
    exit();
}

require_once("./components/loader.php");

// Database connection
require_once("../config/config.php");
$database = new Database();
$conn = $database->getConnection();

// Initialize variables
$contact = $password = "";
$contactErr = $passwordErr = $loginErr = "";
$rememberMe = false;

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate contact
    if (empty($_POST["contact"])) {
        $contactErr = "Contact number is required";
    } else {
        $contact = trim($_POST["contact"]);
    }

    // Validate password
    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
    } else {
        $password = $_POST["password"];
    }

    // Check remember me
    $rememberMe = isset($_POST["remember_me"]);

    // Proceed if no validation errors
    if (empty($contactErr) && empty($passwordErr)) {
        try {
            // Check if contact exists and get promoter info
            $stmt = $conn->prepare("SELECT PromoterID, Name, Contact, Email, PasswordHash, Status FROM Promoters WHERE Contact = ?");
            $stmt->execute([$contact]);
            $promoter = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($promoter) {
                // Verify password
                if (password_verify($password, $promoter['PasswordHash'])) {
                    // Check if account is active
                    if ($promoter['Status'] === 'Active') {
                        // Set up the user session
                        $_SESSION['promoter_id'] = $promoter['PromoterID'];
                        $_SESSION['promoter_name'] = $promoter['Name'];
                        $_SESSION['promoter_contact'] = $promoter['Contact'];
                        $_SESSION['promoter_email'] = $promoter['Email'];

                        // Log the activity
                        $action = "Logged in";
                        $stmt = $conn->prepare("INSERT INTO ActivityLogs (UserID, UserType, Action, IPAddress) VALUES (?, 'Promoter', ?, ?)");
                        $stmt->execute([$promoter['PromoterID'], $action, $_SERVER['REMOTE_ADDR']]);

                        // Regenerate the session ID to prevent session fixation
                        session_regenerate_id(true);

                        // Redirect to dashboard
                        header("Location: dashboard/index.php");
                        exit();
                    } else {
                        $loginErr = "Your account is inactive. Please contact the administrator.";
                    }
                } else {
                    $loginErr = "Invalid contact number or password";
                }
            } else {
                $loginErr = "Invalid contact number or password";
            }
        } catch (PDOException $e) {
            $loginErr = "An error occurred. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Promoter Login | Golden Dreams</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: rgb(13, 106, 80);
            --secondary-color: #2c3e50;
            --hover-color: rgb(175, 234, 161);
            --text-color: #333;
            --light-text: #f0f0f0;
            --light-color: #f5f7fa;
            --success-color: #2ecc71;
            --error-color: #e74c3c;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition-speed: 0.3s;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(180deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            background: white;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M 40 0 L 0 0 0 40" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            opacity: 0.5;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .login-header {
            background: linear-gradient(180deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: var(--light-text);
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .login-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--light-text);
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.9;
            color: var(--light-text);
        }

        .login-header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            right: 0;
            height: 20px;
            background: white;
            clip-path: ellipse(50% 50% at 50% 0);
        }

        .login-form {
            padding: 30px;
            background: white;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--secondary-color);
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius);
            font-size: 14px;
            transition: all var(--transition-speed) ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(13, 106, 80, 0.1);
            outline: none;
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            transition: color var(--transition-speed) ease;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .remember-me {
            display: flex;
            align-items: center;
        }

        .remember-me input {
            margin-right: 8px;
            accent-color: var(--primary-color);
        }

        .remember-me label {
            color: var(--secondary-color);
            cursor: pointer;
        }

        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            transition: color var(--transition-speed) ease;
            font-weight: 500;
        }

        .forgot-password:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            padding: 12px 15px;
            background: linear-gradient(180deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            color: var(--light-text);
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            box-shadow: 0 4px 15px rgba(13, 106, 80, 0.3);
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(13, 106, 80, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn i {
            margin-right: 8px;
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #6c757d;
        }

        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .login-footer a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .error-text {
            color: var(--error-color);
            font-size: 12px;
            margin-top: 5px;
        }

        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert i {
            font-size: 16px;
        }

        .alert-danger {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--error-color);
            border: 1px solid rgba(231, 76, 60, 0.2);
        }

        .alert-success {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(46, 204, 113, 0.2);
        }

        .logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: transform var(--transition-speed) ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo i {
            color: var(--primary-color);
            font-size: 32px;
        }

        .logo img {
            width: 60px;
            height: auto;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .login-container {
                max-width: 100%;
            }

            .login-header {
                padding: 20px;
            }

            .login-form {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-users"></i>
            </div>
            <h1>Promoter Login</h1>
            <p>Golden Dreams Promoter Portal (LA)</p>
        </div>

        <div class="login-form">
            <?php if (!empty($loginErr)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $loginErr; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="contact">Contact Number</label>
                    <input type="text" id="contact" name="contact" class="form-control"
                        value="<?php echo htmlspecialchars($contact); ?>"
                        autocomplete="tel">
                    <?php if (!empty($contactErr)): ?>
                        <div class="error-text"><?php echo $contactErr; ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" class="form-control"
                            autocomplete="current-password">
                        <i class="password-toggle fas fa-eye" id="toggle-password"></i>
                    </div>
                    <?php if (!empty($passwordErr)): ?>
                        <div class="error-text"><?php echo $passwordErr; ?></div>
                    <?php endif; ?>
                </div>

                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="remember_me" name="remember_me"
                            <?php if ($rememberMe) echo "checked"; ?>>
                        <label for="remember_me">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="login-footer">
                <p>&copy; <?php echo date('Y'); ?> Golden Dreams. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('toggle-password');
        const passwordField = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Auto-fade alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 500);
            }, 5000);
        });
    </script>
</body>

</html>