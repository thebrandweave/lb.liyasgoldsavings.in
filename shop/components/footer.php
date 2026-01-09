<?php
// Dynamically determine the base path for shop components
$script_name = $_SERVER['SCRIPT_NAME'];
$shop_pos = strpos($script_name, '/shop/');

if ($shop_pos !== false) {
    $shop_base = substr($script_name, 0, $shop_pos + strlen('/shop/'));
} else {
    // Fallback for live server where /shop/ might not be in the path
    $shop_base = '/';
}
?>
<section class="newsletter-section">
    <div class="newsletter-overlay"></div>
    <div class="newsletter-content">
    <h2 class="newsletter-title">Sign up for updates</h2>
    <p class="newsletter-subtitle">Sign up for early sale access, new in, promotions and more</p>
    <form class="newsletter-form">
        <input type="email" placeholder="Enter your e-mail" class="newsletter-input" required>
        <button type="submit" class="newsletter-btn">SUBSCRIBE</button>
    </form>
    </div>
</section>
<footer class="shop-footer">
  <div class="footer-container">
    <div class="footer-content">
      <div class="footer-section footer-brand">
        <div class="footer-logo">
          <img src="<?php echo $shop_base; ?>assets/image/gd-store-logo2.png" alt="GD Store" class="footer-logo-image">
        </div>
        <p class="footer-description">Premium gold and diamond jewelry for every occasion. We offer the finest collection of traditional and modern jewelry designs, crafted with excellence and delivered with trust.</p>
        <div class="company-info">
          <div class="info-item">
            <i class="bi bi-award"></i>
            <span>Trusted</span>
          </div>
          <div class="info-item">
            <i class="bi bi-gem"></i>
            <span>Certified Quality</span>
          </div>
          <div class="info-item">
            <i class="bi bi-shield-check"></i>
            <span>Secure Shopping</span>
          </div>
        </div>
      </div>
      
      <div class="footer-section">
        <h4 class="footer-heading">Quick Links</h4>
        <ul class="footer-links">
          <li><a href="<?php echo $shop_base; ?>index.php">Home</a></li>
          <li><a href="<?php echo $shop_base; ?>category/index.php">Categories</a></li>
          <li><a href="<?php echo $shop_base; ?>products/index.php">Products</a></li>
          <li><a href="<?php echo $shop_base; ?>about/index.php">About</a></li>
          <li><a href="<?php echo $shop_base; ?>contact/index.php">Contact</a></li>
        </ul>
      </div>
      
      <div class="footer-section">
        <h4 class="footer-heading">Contact Info</h4>
        <div class="contact-item">
          <i class="bi bi-geo-alt"></i>
          <span>2-108/C-7, Ground Floor, Sri Mantame Complex, Near Soorya Infotech Park, Kurnadu Post, Mudipu Road, Bantwal- 574153</span>
        </div>
        <div class="contact-item">
          <i class="bi bi-envelope"></i>
          <span>goldendream175@gmail.com</span>
        </div>
      </div>
      
      <div class="footer-section">
        <h4 class="footer-heading">Follow Us</h4>
        <div class="social-links">
          <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
          <a href="#" class="social-link"><i class="bi bi-whatsapp"></i></a>
          <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
        </div>
      </div>
    </div>
    
    <div class="footer-bottom">
      <p class="copyright">Golden Dream © <?php echo date('Y'); ?>. All Rights Reserved.</p>
      <div class="developed-by">
        <span>Developed by</span>
        <img src="<?php echo $shop_base; ?>assets/image/developer_logo.png" alt="Developer Logo" class="developer-logo">
      </div>
    </div>
  </div>
</footer>

<button id="backToTopBtn" title="Back to Top"><i class="bi bi-chevron-up"></i></button>

<style>
.newsletter-section {
    width: 100vw;
    margin-left: calc(-50vw + 50%);
    margin-right: calc(-50vw + 50%);
    background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    padding: 80px 20px;
    text-align: center;
    position: relative;
    color: #fff;
    left: 0;
    right: 0;
}

.newsletter-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
    z-index: 1;
}

.newsletter-content {
    position: relative;
    z-index: 2;
    max-width: 600px;
    margin: 0 auto;
}

.newsletter-title {
    color: #fff;
    font-size: 2.6rem;
    font-weight: 800;
    margin-bottom: 18px;
    line-height: 1.15;
    letter-spacing: 0.01em;
}

.newsletter-title .accent {
    color: #ffd600;
    background: none;
    padding: 0 4px;
    border-radius: 4px;
}

.newsletter-subtitle {
    color: #fff;
    font-size: 1.15rem;
    margin-bottom: 38px;
    font-weight: 400;
    opacity: 0.9;
}

.newsletter-form {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0;
    width: 100%;
    max-width: 540px;
    margin: 0 auto;
    background: #fff;
    border-radius: 999px;
    box-shadow: 0 2px 16px 0 rgba(0,0,0,0.06);
    border: 1.5px solid #23211a;
    padding: 0;
}

.newsletter-input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    padding: 18px 24px;
    font-size: 1.08rem;
    border-radius: 999px 0 0 999px;
    color: #232526;
    font-family: 'Montserrat', sans-serif;
}

.newsletter-input::placeholder {
    color: #888;
    font-size: 1.08rem;
    font-family: 'Montserrat', sans-serif;
}

.newsletter-btn {
    background: #ffd600;
    color: #23211a;
    font-weight: 700;
    border: none;
    border-radius: 0 999px 999px 0;
    padding: 18px 38px;
    font-size: 1.08rem;
    letter-spacing: 0.12em;
    cursor: pointer;
    transition: background 0.18s, color 0.18s, transform 0.18s;
    font-family: 'Montserrat', sans-serif;
    box-shadow: 0 2px 8px 0 rgba(255,214,0,0.08);
}

.newsletter-btn:hover {
    background: #ffe066;
    color: #23211a;
    transform: scale(0.97);
}

@media (max-width: 700px) {
    .newsletter-title { 
        font-size: 1.8rem; 
    }
    .newsletter-form { 
        flex-direction: column; 
        border-radius: 32px; 
        max-width: 98vw; 
    }
    .newsletter-input, .newsletter-btn { 
        border-radius: 32px; 
        width: 100%; 
        padding: 14px 16px; 
        font-size: 1rem; 
    }
    .newsletter-btn { 
        margin-top: 10px; 
    }
}

.shop-footer {
  background: #fff;
  border-top: 1px solid #f0f0f0;
  padding: 60px 0 20px 0;
  font-family: 'Montserrat', sans-serif;
  color: #333;
}

.footer-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.footer-content {
  display: grid;
  grid-template-columns: 2fr 1fr 1.5fr 1fr;
  gap: 40px;
  margin-bottom: 40px;
}

.footer-section {
  display: flex;
  flex-direction: column;
}

.footer-brand {
  grid-column: 1;
}

.footer-logo {
  display: flex;
  align-items: center;
  margin-bottom: 12px;
}

.footer-logo-image {
  height: 80px;
  width: 80px;
  object-fit: contain;
  border-radius: 50%;
  border: 3px solid #ffd600;
  padding: 8px;
  background: #fff;
  box-shadow: 0 4px 16px rgba(255, 214, 0, 0.15);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.footer-logo-image:hover {
  transform: scale(1.05);
  box-shadow: 0 6px 20px rgba(255, 214, 0, 0.25);
}

.footer-description {
  color: #666;
  font-size: 0.9rem;
  line-height: 1.5;
  margin: 0 0 20px 0;
}

.company-info {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 16px;
}

.info-item {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #666;
  font-size: 0.9rem;
}

.info-item i {
  color: #ffd600;
  font-size: 1rem;
  width: 16px;
}

.info-item span {
  font-weight: 500;
}

.footer-heading {
  font-size: 1rem;
  font-weight: 600;
  color: #23211a;
  margin-bottom: 16px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.footer-links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-links li {
  margin-bottom: 8px;
}

.footer-links a {
  color: #666;
  text-decoration: none;
  font-size: 0.9rem;
  transition: color 0.2s ease;
}

.footer-links a:hover {
  color: #ffd600;
}

.contact-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 12px;
  font-size: 0.9rem;
  color: #666;
  line-height: 1.4;
}

.contact-item i {
  color: #ffd600;
  font-size: 1rem;
  margin-top: 2px;
  flex-shrink: 0;
}

.social-links {
  display: flex;
  gap: 15px;
}

.social-link {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: #f8f8f8;
  border-radius: 50%;
  color: #666;
  text-decoration: none;
  transition: all 0.2s ease;
}

.social-link:hover {
  background: #ffd600;
  color: #23211a;
  transform: translateY(-2px);
}

.social-link i {
  font-size: 1.1rem;
}

.footer-bottom {
  border-top: 1px solid #f0f0f0;
  padding-top: 20px;
  text-align: center;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}

.copyright {
  color: #999;
  font-size: 0.85rem;
  margin: 0;
}

.developed-by {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #999;
  font-size: 0.85rem;
}

.developer-logo {
  height: 30px;
  width: auto;
  filter: grayscale(0.3);
  transition: filter 0.2s ease;
}

.developer-logo:hover {
  filter: grayscale(0);
}

#backToTopBtn {
  position: fixed;
  bottom: 32px;
  right: 32px;
  z-index: 1000;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #ffd600;
  color: #23211a;
  border: none;
  box-shadow: 0 2px 12px rgba(0,0,0,0.10);
  display: none;
  align-items: center;
  justify-content: center;
  font-size: 1.7rem;
  cursor: pointer;
  transition: background 0.18s, color 0.18s, transform 0.18s;
  margin: 0;
  padding: 0;
}
#backToTopBtn:hover {
  background: #23211a;
  color: #ffd600;
  transform: translateY(-3px) scale(1.08);
}

@media (max-width: 768px) {
  .footer-content {
    grid-template-columns: 1fr;
    gap: 30px;
  }
  
  .footer-brand {
    grid-column: 1;
    text-align: center;
  }
  
  .footer-logo-image {
    height: 70px;
    width: 70px;
  }
  
  .social-links {
    justify-content: center;
  }
  
  .footer-bottom {
    text-align: center;
    flex-direction: column;
    gap: 15px;
  }
  
  .footer-container {
    padding: 0 15px;
  }
  
  .shop-footer {
    padding: 40px 0 20px 0;
  }
  
  .footer-heading {
    font-size: 0.95rem;
    margin-bottom: 12px;
  }
  
  .footer-description {
    font-size: 0.85rem;
    margin-bottom: 15px;
  }
  
  .company-info {
    gap: 10px;
    margin-top: 12px;
  }
  
  .info-item {
    font-size: 0.85rem;
    gap: 8px;
  }
  
  .footer-links li {
    margin-bottom: 6px;
  }
  
  .footer-links a {
    font-size: 0.85rem;
  }
  
  .contact-item {
    font-size: 0.85rem;
    margin-bottom: 10px;
    gap: 8px;
  }
  
  .social-links {
    gap: 12px;
  }
  
  .social-link {
    width: 36px;
    height: 36px;
  }
  
  .social-link i {
    font-size: 1rem;
  }
  
  .copyright {
    font-size: 0.8rem;
  }
  
  .developed-by {
    font-size: 0.8rem;
    gap: 6px;
  }
  
  .developer-logo {
    height: 25px;
  }
  
  #backToTopBtn {
    bottom: 20px;
    right: 20px !important;
    width: 44px;
    height: 44px;
    font-size: 1.5rem;
    margin: 20px !important;
    padding: 0;
  }
}

@media (max-width: 480px) {
  .newsletter-section {
    padding: 60px 15px;
  }
  
  .newsletter-title {
    font-size: 1.6rem;
    margin-bottom: 15px;
  }
  
  .newsletter-subtitle {
    font-size: 1rem;
    margin-bottom: 30px;
  }
  
  .newsletter-form {
    max-width: 100%;
    border-radius: 20px;
  }
  
  .newsletter-input, .newsletter-btn {
    border-radius: 20px;
    padding: 12px 14px;
    font-size: 0.95rem;
  }
  
  .newsletter-btn {
    margin-top: 8px;
  }
  
  .footer-content {
    gap: 25px;
  }
  
  .footer-logo-image {
    height: 60px;
    width: 60px;
  }
  
  .footer-description {
    font-size: 0.8rem;
    line-height: 1.4;
  }
  
  .company-info {
    gap: 8px;
  }
  
  .info-item {
    font-size: 0.8rem;
  }
  
  .info-item i {
    font-size: 0.9rem;
    width: 14px;
  }
  
  .footer-heading {
    font-size: 0.9rem;
    margin-bottom: 10px;
  }
  
  .footer-links li {
    margin-bottom: 5px;
  }
  
  .footer-links a {
    font-size: 0.8rem;
  }
  
  .contact-item {
    font-size: 0.8rem;
    margin-bottom: 8px;
    line-height: 1.3;
  }
  
  .contact-item i {
    font-size: 0.9rem;
  }
  
  .social-links {
    gap: 10px;
  }
  
  .social-link {
    width: 32px;
    height: 32px;
  }
  
  .social-link i {
    font-size: 0.9rem;
  }
  
  .footer-bottom {
    padding-top: 15px;
    gap: 12px;
  }
  
  .copyright {
    font-size: 0.75rem;
  }
  
  .developed-by {
    font-size: 0.75rem;
  }
  
  .developer-logo {
    height: 22px;
  }
  
  #backToTopBtn {
    bottom: 15px;
    right: 15px;
    width: 40px;
    height: 40px;
    font-size: 1.3rem;
    margin: 0;
    padding: 0;
  }
  
  .shop-footer {
    padding: 30px 0 15px 0;
  }
  
  .footer-container {
    padding: 0 12px;
  }
}

@media (max-width: 360px) {
  #backToTopBtn {
    bottom: 15px;
    right: 15px;
    width: 38px;
    height: 38px;
    font-size: 1.2rem;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    max-width: calc(100vw - 30px);
    max-height: calc(100vh - 30px);
  }
  
  .newsletter-title {
    font-size: 1.4rem;
  }
  
  .newsletter-subtitle {
    font-size: 0.9rem;
  }
  
  .newsletter-input, .newsletter-btn {
    font-size: 0.9rem;
    padding: 10px 12px;
  }
  
  .footer-logo-image {
    height: 55px;
    width: 55px;
  }
  
  .footer-description {
    font-size: 0.75rem;
  }
  
  .info-item {
    font-size: 0.75rem;
  }
  
  .footer-heading {
    font-size: 0.85rem;
  }
  
  .footer-links a {
    font-size: 0.75rem;
  }
  
  .contact-item {
    font-size: 0.75rem;
  }
  
  .social-link {
    width: 30px;
    height: 30px;
  }
  
  .social-link i {
    font-size: 0.85rem;
  }
  
  .copyright {
    font-size: 0.7rem;
  }
  
  .developed-by {
    font-size: 0.7rem;
  }
  
  .developer-logo {
    height: 20px;
  }
  
  #backToTopBtn {
    bottom: 15px;
    right: 15px;
    width: 38px;
    height: 38px;
    font-size: 1.2rem;
    margin: 0;
    padding: 0;
  }
}



</style> 

<script>
// Show/hide back to top button
window.addEventListener('scroll', function() {
  const btn = document.getElementById('backToTopBtn');
  if (window.scrollY > 100) {
    btn.style.display = 'flex';
  } else {
    btn.style.display = 'none';
  }
});
// Smooth scroll to top
const backToTopBtn = document.getElementById('backToTopBtn');
if (backToTopBtn) {
  backToTopBtn.onclick = function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };
}
</script> 