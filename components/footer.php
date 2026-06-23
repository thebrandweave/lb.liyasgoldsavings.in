<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <style>
        
        /* Footer Styles */
        footer {
            background: #0a0a0a;
            color: #fff;
            padding: 80px 0 0;
            position: relative;
            overflow: hidden;
            max-width: 100vw !important;
            overflow-x: hidden;
        }

        /* .cta-card::before{
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(135deg, #e6ca9f 0%, #a16b14 60%, #a16b14 100%);
        } */

        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(135deg, #e6ca9f 0%, #fff5e5 60%, #ffeed2 100%);
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .footer-section {
            margin-bottom: 40px;
        }

        .footer-section h3 {
            background:linear-gradient(180deg, #e6ca9f 0%, #a16b14 60%, #a16b14 100%);
            font-size: 1.2rem;
            margin-bottom: 24px;
            position: relative;
            font-weight:600;
            padding-bottom: 12px;
              -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
        }

        .footer-section h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background:linear-gradient(135deg, #e6ca9f 0%, #a16b14 60%, #a16b14 100%);
        }

        .footer-section p {
            color: #e5e7eb;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-section ul li {
            margin-bottom: 12px;
        }

        .footer-section ul li a {
            color: #e5e7eb;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-section ul li a:hover {
            background: linear-gradient(180deg, #e6ca9f 0%, #a16b14 60%, #a16b14 100%);
              -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
            transform: translateX(5px);
        }

        .footer-section ul li a i {
            font-size: 0.9rem;
            background:linear-gradient(135deg, #e6ca9f 0%, #a16b14 60%, #a16b14 100%);
              -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
        }


             /* Responsive Footer */
        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
                gap: 24px;
            }
}

.footer-logo {
    width: 104px;
    height: 100px;
    margin: 0 0 0 -20px;

    filter: brightness(0) invert(1);
}
    </style>


 <!-- Footer -->
    <footer id="contact">
        <div class="footer-content">
            <div class="footer-section">
                <img src="./landing_assets/images/1gdlogo.png" alt="logo" class="footer-logo">
                <p>Empowering your financial future with secure investment opportunities and expert guidance. Join us in building a prosperous tomorrow.</p>
                <div class="social-links">
                    <a href="https://www.instagram.com/pro_gd_ventures_pvt/"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.youtube.com/@goldendream23"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="#features"><i class="fas fa-chevron-right"></i> Features</a></li>
                    <li><a href="#how-it-works"><i class="fas fa-chevron-right"></i> How It Works</a></li>
                    <li><a onclick="openLoginModal()"><i class="fas fa-chevron-right"></i> Login</a></li>
                    <!-- <li><a href="https://lb.liyasgoldsavings.in//refer?id=GDP0001&ref=NTAw"><i class="fas fa-chevron-right"></i> Register</a></li> -->
                    <li><a href="#contact"><i class="fas fa-chevron-right"></i> Contact Us</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Investment Plans</h3>
                <ul>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Short Term Plans</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Long Term Plans</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> High Return Plans</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Fixed Deposit Plans</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right"></i> Custom Plans</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Info</h3>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>2-108/C-7, Ground Floor, Sri Mantame Complex, Near Soorya Infotech Park, Kurnadu Post, Mudipu Road, Bantwal- 574153</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+91 99951 94472</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>goldendream175@gmail.com</span>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <span>Mon - Sun: 9:30 AM - 6:00 PM</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="copyright">
            <!-- <div class="developer-credit">
                <span>Developed by <a style="text-decoration: none;color:teal;" href="https://intelexsolutions.in/"> <img src="../landing_assets/images/intelex.png" style="height: 20px;margin-bottom:-5px;" alt=""> Intelex Solutions</a></span>
            </div> -->
           <p>
  <span class="gold-text">&copy;</span> 2026 Golden Dream. All rights reserved.
</p>
            <!-- <div class="last-updated">
                <i class="fas fa-clock"></i>
                <span>Last updated: <?php echo date('F d, Y'); ?></span>
            </div> -->
        </div>
    </footer>
</body>
</html>