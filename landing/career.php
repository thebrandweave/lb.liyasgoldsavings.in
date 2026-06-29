<?php 
include("../admin/components/loader.php");
require_once("../config/config.php");
require_once("../config/email.php");

$successMessage = "";
$errorMessage = "";

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Fetch active openings
    $stmt = $conn->prepare("SELECT * FROM CareerOpenings WHERE Status = 'Active' ORDER BY CreatedAt DESC");
    $stmt->execute();
    $openings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_application'])) {
        $fullName = trim($_POST['fullName']);
        $email = trim($_POST['emailAddress']);
        $phone = trim($_POST['phoneNumber']);
        $targetPosition = trim($_POST['targetPosition']);
        $coverLetter = trim($_POST['coverLetter']);
        
        // Find opening ID if matched
        $openingID = null;
        if ($targetPosition !== 'Other') {
            $stmt = $conn->prepare("SELECT OpeningID FROM CareerOpenings WHERE Title = ? LIMIT 1");
            $stmt->execute([$targetPosition]);
            $foundId = $stmt->fetchColumn();
            if ($foundId) {
                $openingID = $foundId;
            }
        }
        
        // Handle file upload
        if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Resume file upload is required.");
        }
        
        $file = $_FILES['resume'];
        $allowedExtensions = ['pdf', 'doc', 'docx'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileSize = $file['size'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception("Invalid file type. Only PDF and Word documents (.doc, .docx) are allowed.");
        }
        
        if ($fileSize > $maxSize) {
            throw new Exception("File size exceeds the 5MB limit.");
        }
        
        // Create directory if it doesn't exist
        $uploadDir = '../uploads/resumes/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Unique filename
        $fileName = 'resume_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;
        
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception("Failed to save the uploaded file.");
        }
        
        $resumeURL = 'uploads/resumes/' . $fileName;
        
        // Insert into db
        $insertStmt = $conn->prepare("INSERT INTO CareerApplications (OpeningID, FullName, Email, Phone, Position, CoverLetter, ResumeURL) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insertStmt->execute([$openingID, $fullName, $email, $phone, $targetPosition, $coverLetter, $resumeURL]);
        
        // Send Confirmation Email
        $emailSubject = "Application Received - " . $targetPosition;
        $emailBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                <div style='text-align: center; margin-bottom: 20px;'>
                    <h2 style='color: #a36d16; margin: 0;'>Golden Dream</h2>
                    <p style='color: #666; font-size: 14px; margin: 5px 0 0 0;'>Careers & Opportunities</p>
                </div>
                <hr style='border: 0; border-top: 1px solid #f1f1f1; margin: 20px 0;'>
                <p>Dear <strong>" . htmlspecialchars($fullName) . "</strong>,</p>
                <p>Thank you for submitting your application for the <strong>" . htmlspecialchars($targetPosition) . "</strong> position at Golden Dream.</p>
                <p>We have successfully received your details and uploaded resume. Our Human Resources team will review your qualifications and contact you if your profile aligns with our current requirements.</p>
                <p style='margin-top: 25px;'>Best regards,<br><strong>Human Resources Team</strong><br>Golden Dream</p>
                <hr style='border: 0; border-top: 1px solid #f1f1f1; margin: 20px 0;'>
                <p style='font-size: 11px; color: #999; text-align: center;'>This is an automated email notification. Please do not reply directly to this message.</p>
            </div>
        ";
        
        try {
            sendSMTPMail($email, $emailSubject, $emailBody);
        } catch (Exception $mailEx) {
            // Log mail failure but do not halt candidate success flow
            error_log("Failed to send career application email to $email: " . $mailEx->getMessage());
        }
        
        $successMessage = "Thank you for your interest! Your application has been successfully submitted. Our HR team will reach out to you shortly.";
        
    }
} catch (Exception $e) {
    $errorMessage = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Golden Dream - Careers</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #000000;
            --secondary-color: #ffffff;
            --accent-color: #a36d16;
            --gold-gradient: linear-gradient(135deg, #BF953F 0%, #FCF6BA 25%, #B38728 50%, #FBF5B7 75%, #AA771C 100%);
            --text-color: #111111;
            --subtext-color: #555555;
            --border-color: #e5e5e5;
            --btn-bg: #000000;
            --btn-text: #ffffff;
            --btn-hover-bg: #222222;
            --success: #00b67a;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--secondary-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            max-width: 100% !important;
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* Hero Section */
        .career-hero {
            position: relative;
            background: linear-gradient(rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0.85)), url('https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=1600&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            height: 60vh;
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #fff;
            margin-top: 80px; /* offset navbar */
        }

        .hero-content h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-content p {
            font-size: 1.25rem;
            max-width: 800px;
            margin: 0 auto;
            color: #e5e5e5;
        }

        /* Why Join Us Section */
        .benefits-section {
            padding: 80px 0;
            background: #fafafa;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-header h2 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }

        .section-header h2::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--accent-color);
        }

        .section-header p {
            color: var(--subtext-color);
            font-size: 1.1rem;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .benefit-card {
            background: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
            text-align: center;
        }

        .benefit-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-color: var(--accent-color);
        }

        .benefit-icon {
            font-size: 2.5rem;
            color: var(--accent-color);
            margin-bottom: 20px;
        }

        .benefit-card h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .benefit-card p {
            color: var(--subtext-color);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* Open Positions Section */
        .positions-section {
            padding: 80px 0;
        }

        .position-card {
            background: #fff;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .position-card:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            border-color: var(--accent-color);
        }

        .position-details h3 {
            font-size: 1.4rem;
            font-weight: 600;
            margin: 0 0 10px 0;
        }

        .position-meta {
            display: flex;
            gap: 20px;
            color: var(--subtext-color);
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .position-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .position-desc {
            color: var(--subtext-color);
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0;
        }

        .btn-apply-now {
            background: var(--btn-bg);
            color: var(--btn-text);
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-apply-now:hover {
            background: var(--accent-color);
            color: #fff;
        }

        /* Application Form Section */
        .apply-section {
            padding: 80px 0;
            background: #fafafa;
            border-top: 1px solid var(--border-color);
        }

        .apply-form-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            font-weight: 500;
            font-size: 0.95rem;
        }

        .form-control, .form-select {
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(163, 109, 22, 0.15);
        }

        .btn-submit {
            background: var(--btn-bg);
            color: var(--btn-text);
            border: none;
            padding: 14px 40px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-submit:hover {
            background: var(--accent-color);
        }

        /* Modal Overlay Styles (required by navbar) */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
        }

        .login-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            background: #1f2937;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            z-index: 1001;
            width: 90%;
            max-width: 400px;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .login-modal.active {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .modal-header h2 {
            font-size: 1.5rem;
            color: #ffffff;
            margin: 0;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #ffffff;
            cursor: pointer;
            padding: 8px;
            transition: color 0.3s ease;
        }

        .close-modal:hover {
            color: var(--accent-color);
        }

        .login-options {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .login-option {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px;
            border-radius: 12px;
            background: #374151;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: #ffffff;
        }

        .login-option:hover {
            background: #4b5563;
            transform: translateX(5px);
        }

        .login-option i {
            font-size: 1.5rem;
            color: var(--accent-color);
        }

        .option-content {
            flex: 1;
        }

        .option-title {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .option-description {
            font-size: 0.85rem;
            color: #d1d5db;
        }

        /* Mobile Menu */
        .mobile-menu {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 1001;
            padding: 80px 24px 24px;
        }

        .mobile-menu.active {
            display: block;
        }

        .mobile-menu-close {
            position: absolute;
            top: 24px;
            right: 24px;
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .mobile-nav-links {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .mobile-nav-links a {
            color: #fff;
            text-decoration: none;
            font-size: 1.2rem;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .mobile-nav-links a i {
            color: var(--accent-color);
        }

        /* Responsive Breakpoints */
        @media (max-width: 992px) {
            .career-hero {
                height: 50vh;
            }
            .hero-content h1 {
                font-size: 2.2rem;
            }
            .hero-content p {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 768px) {
            .career-hero {
                margin-top: 70px;
                height: 45vh;
            }
            .position-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }
            .btn-apply-now {
                width: 100%;
                text-align: center;
            }
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
            .form-group.full-width {
                grid-column: span 1;
            }
            .apply-form-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Header / Navbar -->
    <?php include '../components/navbar.php'; ?>

    <!-- Login Modal -->
    <div class="modal-overlay" id="loginModal">
        <div class="login-modal">
            <div class="modal-header">
                <h2>Choose Login Type</h2>
                <button class="close-modal" onclick="closeLoginModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="login-options">
                <a href="../../customer" class="login-option">
                    <i class="fas fa-user-circle"></i>
                    <div class="option-content">
                        <div class="option-title">Login as Customer</div>
                        <div class="option-description">Access your investment dashboard</div>
                    </div>
                </a>
                <a href="../../promoter/" class="login-option">
                    <i class="fas fa-user-tie"></i>
                    <div class="option-content">
                        <div class="option-title">Login as Promoter</div>
                        <div class="option-description">Manage your promoter account</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu" id="mobileMenu">
        <button class="mobile-menu-close" onclick="toggleMobileMenu()">
            <i class="fas fa-times"></i>
        </button>
        <div class="mobile-nav-links">
            <a href="./"><i class="fas fa-home"></i> Home</a>
            <a href="./about.php"><i class="fas fa-info-circle"></i> About Us</a>
            <a href="./career.php"><i class="fas fa-briefcase"></i> Careers</a>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="career-hero">
        <div class="container hero-content">
            <h1>Build Your Future with Golden Dream</h1>
            <p>Join a fast-growing team of visionaries and innovators dedicated to shaping the future of high-yield strategic investments.</p>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits-section">
        <div class="container">
            <div class="section-header">
                <h2>Why Work with Us?</h2>
                <p>We nurture talent, value creativity, and foster a culture of professional growth.</p>
            </div>
            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-chart-line"></i></div>
                    <h3>Growth Opportunities</h3>
                    <p>We provide continuous learning resources, mentorship programs, and clear growth paths to accelerate your career.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-wallet"></i></div>
                    <h3>Competitive Pay</h3>
                    <p>Receive premium compensation packages, performance bonuses, and dynamic commission schemes.</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon"><i class="fas fa-handshake"></i></div>
                    <h3>Inclusive Culture</h3>
                    <p>Collaborate in a supportive, transparent environment where every idea counts and diversity is celebrated.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Open Positions Section -->
    <section class="positions-section" id="openings">
        <div class="container">
            <div class="section-header">
                <h2>Current Openings</h2>
                <p>Find the role that fits your skills and ambition.</p>
            </div>

            <?php if (!empty($openings)): ?>
                <?php foreach ($openings as $opening): ?>
                    <div class="position-card">
                        <div class="position-details">
                            <h3><?php echo htmlspecialchars($opening['Title']); ?></h3>
                            <div class="position-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($opening['Location']); ?></span>
                                <span><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($opening['Type']); ?></span>
                            </div>
                            <p class="position-desc"><?php echo htmlspecialchars($opening['Description']); ?></p>
                            <?php if (!empty($opening['Requirements'])): ?>
                                <p class="position-desc" style="margin-top: 10px;"><strong>Requirements:</strong> <?php echo htmlspecialchars($opening['Requirements']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <a href="#apply" class="btn-apply-now" onclick="preselectPosition('<?php echo htmlspecialchars(addslashes($opening['Title'])); ?>')">Apply Now</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="position-card" style="text-align: center; padding: 40px; background: rgba(0,0,0,0.02); border: 1px dashed #ccc;">
                    <p style="margin: 0; color: #666; font-size: 1.1rem;">We don't have any active openings right now. However, you can still submit a general application below!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Application Form Section -->
    <section class="apply-section" id="apply">
        <div class="container">
            <div class="section-header">
                <h2>Submit Your Application</h2>
                <p>Take the first step towards a rewarding career with Golden Dream.</p>
            </div>

            <div class="apply-form-container">
                <?php if (!empty($successMessage)): ?>
                    <div style="background: rgba(0, 182, 122, 0.15); border: 1px solid rgba(0, 182, 122, 0.3); color: #00b67a; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 500;">
                        <?php echo htmlspecialchars($successMessage); ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($errorMessage)): ?>
                    <div style="background: rgba(220, 53, 69, 0.15); border: 1px solid rgba(220, 53, 69, 0.3); color: #dc3545; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 500;">
                        Error: <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                <?php endif; ?>

                <form id="careerForm" action="" method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fullName">Full Name *</label>
                            <input type="text" id="fullName" name="fullName" class="form-control" placeholder="Enter your full name" required>
                        </div>
                        <div class="form-group">
                            <label for="emailAddress">Email Address *</label>
                            <input type="email" id="emailAddress" name="emailAddress" class="form-control" placeholder="Enter your email" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phoneNumber">Phone Number *</label>
                            <input type="tel" id="phoneNumber" name="phoneNumber" class="form-control" placeholder="10-digit mobile number" required>
                        </div>
                        <div class="form-group">
                            <label for="targetPosition">Position Applied For *</label>
                            <select id="targetPosition" name="targetPosition" class="form-select" required>
                                <option value="" disabled selected>Select a position</option>
                                <?php if (!empty($openings)): ?>
                                    <?php foreach ($openings as $opening): ?>
                                        <option value="<?php echo htmlspecialchars($opening['Title']); ?>"><?php echo htmlspecialchars($opening['Title']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <option value="Other">Other / General Application</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="coverLetter">Brief Statement / Experience Summary *</label>
                        <textarea id="coverLetter" name="coverLetter" class="form-control" rows="4" placeholder="Summarize your relevant experience..." required></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="resume">Upload Resume (PDF/DOCX, max 5MB) *</label>
                        <input type="file" id="resume" name="resume" class="form-control" accept=".pdf,.doc,.docx" required>
                    </div>

                    <button type="submit" name="submit_application" class="btn-submit">Submit Application</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include '../components/footer.php'; ?>

    <script>
        function openLoginModal() {
            const modal = document.getElementById('loginModal');
            modal.style.display = 'block';
            setTimeout(() => {
                modal.classList.add('active');
                document.querySelector('.login-modal').classList.add('active');
            }, 10);
        }

        function closeLoginModal() {
            const modal = document.getElementById('loginModal');
            modal.classList.remove('active');
            document.querySelector('.login-modal').classList.remove('active');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        // Close modal when clicking outside
        document.getElementById('loginModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLoginModal();
            }
        });

        // Mobile Menu Toggle
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('active');
            document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
        }

        // Close mobile menu when clicking outside
        document.getElementById('mobileMenu').addEventListener('click', function(e) {
            if (e.target === this) {
                toggleMobileMenu();
            }
        });

        // Preselect position in form
        function preselectPosition(positionName) {
            const select = document.getElementById('targetPosition');
            select.value = positionName;
        }

    </script>
</body>
</html>
