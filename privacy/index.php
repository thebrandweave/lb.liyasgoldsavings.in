<?php $lastUpdated = date('F d, Y'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy | Golden Dream</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --line: #e5e7eb;
            --primary: #1f4b8f;
            --accent: #0f766e;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            color: var(--text);
            background: linear-gradient(180deg, #eef3fb 0%, #f8fafc 100%);
        }
        .wrap {
            max-width: 980px;
            margin: 28px auto;
            padding: 0 16px 30px;
        }
        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 14px;
            box-shadow: 0 8px 28px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }
        .hero {
            padding: 28px 28px 22px;
            border-bottom: 1px solid var(--line);
            background: radial-gradient(circle at top right, #dbeafe 0%, #ffffff 52%);
            text-align: center;
        }
        .logo {
            height: 64px;
            width: auto;
            margin-bottom: 12px;
        }
        h1 {
            margin: 6px 0 6px;
            color: var(--primary);
            font-size: 30px;
        }
        .meta {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }
        .content {
            padding: 26px 28px 10px;
        }
        h2 {
            margin: 24px 0 10px;
            font-size: 21px;
            color: #243b63;
        }
        p, li {
            font-size: 15px;
            line-height: 1.7;
        }
        ul {
            margin: 8px 0 0;
            padding-left: 20px;
        }
        .notice {
            margin-top: 14px;
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: #14532d;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 14px;
        }
        .contact {
            margin-top: 16px;
            padding: 14px;
            border: 1px dashed #9ca3af;
            border-radius: 8px;
            background: #f9fafb;
        }
        .contact a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }
        .contact a:hover {
            text-decoration: underline;
        }
        .footer-note {
            text-align: center;
            color: var(--muted);
            font-size: 13px;
            padding: 10px 16px 24px;
        }
        @media (max-width: 768px) {
            .hero, .content { padding: 20px; }
            h1 { font-size: 26px; }
            h2 { font-size: 19px; }
            .logo { height: 56px; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="hero">
                <img class="logo" src="../landing/landing_assets/images/gdLogo.png" alt="Golden Dream Logo">
                <h1>Privacy Policy</h1>
                <p class="meta">Last updated: <?php echo htmlspecialchars($lastUpdated); ?></p>
            </div>

            <div class="content">
                <p>
                    Golden Dream ("we", "our", "us") values your privacy. This Privacy Policy explains
                    how we collect, use, store, and protect your information when you use our website and services.
                </p>

                <div class="notice">
                    By continuing to use our services, you agree to this Privacy Policy.
                </div>

                <h2>1. Information We Collect</h2>
                <ul>
                    <li>Personal details such as name, contact number, address, and account login details.</li>
                    <li>KYC and verification information where required for compliance.</li>
                    <li>Payment, subscription, and transaction-related records.</li>
                    <li>Communication logs for SMS/WhatsApp notifications and support communication.</li>
                    <li>Basic technical data such as IP address and session activity for security.</li>
                </ul>

                <h2>2. How We Use Information</h2>
                <ul>
                    <li>To register and manage your account.</li>
                    <li>To process payments, subscriptions, and withdrawals.</li>
                    <li>To send service notifications and transaction alerts.</li>
                    <li>To improve platform performance and system security.</li>
                    <li>To meet legal and regulatory requirements.</li>
                </ul>

                <h2>3. Sharing of Data</h2>
                <p>
                    We do not sell your data. We may share information only with trusted service providers
                    (payment/messaging), verification partners, and authorities when legally required.
                </p>

                <h2>4. Data Security</h2>
                <p>
                    We apply reasonable administrative and technical safeguards to protect your information.
                    However, no online transmission or storage method is completely secure.
                </p>

                <h2>5. Data Retention</h2>
                <p>
                    Data is retained as required for operations, legal compliance, audits, and dispute resolution.
                </p>

                <h2>6. Your Rights</h2>
                <p>
                    You may request correction of inaccurate information and raise privacy concerns through support.
                </p>

                <h2>7. Third-Party Services</h2>
                <p>
                    Our website integrates third-party services such as payment gateways and SMS/WhatsApp APIs.
                    Their own privacy terms also apply where relevant.
                </p>

                <h2>8. Policy Updates</h2>
                <p>
                    We may update this policy periodically. Continued use after updates means acceptance of the revised policy.
                </p>

                <h2>9. Contact</h2>
                <div class="contact">
                    For privacy-related questions, contact us at:
                    <a href="mailto:support@la.goldendream.in">support@la.goldendream.in</a>
                </div>
            </div>

            <div class="footer-note">
                Golden Dream &copy; <?php echo date('Y'); ?>. All rights reserved.
            </div>
        </div>
    </div>
</body>
</html>
