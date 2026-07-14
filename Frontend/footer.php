<footer id="footer" class="bg-dark text-white pt-5 mt-5 border-top border-4 border-success">
    <div class="container py-4">
        <div class="row g-4">
            
            <div class="col-lg-4 col-md-6">
                <h4 class="text-success fw-bold mb-3 footer-site-title">
                    <i class="fa fa-utensils me-2 animate-bounce"></i><?= __('site_title') ?>
                </h4>
                <p class="text-secondary small lh-lg mb-4 footer-description">
                    <?= __('footer_description') ?>
                </p>
                <div class="d-flex gap-2 pt-2">
                    <a href="#" class="btn btn-outline-success btn-sm rounded-circle text-white social-icon" style="width:36px; height:36px; display:flex; align-items:center; justify-content:center;"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn btn-outline-success btn-sm rounded-circle text-white social-icon" style="width:36px; height:36px; display:flex; align-items:center; justify-content:center;"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="btn btn-outline-success btn-sm rounded-circle text-white social-icon" style="width:36px; height:36px; display:flex; align-items:center; justify-content:center;"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="btn btn-outline-success btn-sm rounded-circle text-white social-icon" style="width:36px; height:36px; display:flex; align-items:center; justify-content:center;"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6">
                <h5 class="text-white fw-bold mb-3 pb-2 footer-heading"><?= __('quick_links') ?></h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="index.php" class="footer-link text-secondary text-decoration-none"><i class="fa fa-chevron-right me-2 text-success small"></i><?= __('home') ?></a></li>
                    <li class="mb-2"><a href="about.php" class="footer-link text-secondary text-decoration-none"><i class="fa fa-chevron-right me-2 text-success small"></i><?= __('about') ?></a></li>
                    <li class="mb-2"><a href="login.php" class="footer-link text-secondary text-decoration-none"><i class="fa fa-chevron-right me-2 text-success small"></i><?= __('login') ?></a></li>
                    <li class="mb-2"><a href="register.php" class="footer-link text-secondary text-decoration-none"><i class="fa fa-chevron-right me-2 text-success small"></i><?= __('register') ?></a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5 class="text-white fw-bold mb-3 pb-2 footer-heading"><?= __('contact') ?></h5>
                <ul class="list-unstyled small text-secondary">
                    <li class="mb-3 d-flex align-items-start"><i class="fa fa-map-marker-alt me-3 text-success mt-1 footer-icon"></i><span class="footer-text"><?= __('address_text') ?></span></li>
                    <li class="mb-3 d-flex align-items-start"><i class="fa fa-envelope me-3 text-success mt-1 footer-icon"></i><span class="footer-text"><?= __('support_email') ?></span></li>
                    <li class="mb-3 d-flex align-items-center"><i class="fa fa-server me-3 text-success footer-icon"></i><span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 db-status"><?= __('database_connected') ?></span></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5 class="text-white fw-bold mb-3 pb-2 footer-heading"><?= __('newsletter') ?></h5>
                <p class="text-secondary small mb-3 footer-text"><?= __('newsletter_text') ?></p>
                <div class="input-group input-group-sm">
                    <input type="email" class="form-control bg-secondary text-white border-0 px-3 newsletter-input" placeholder="<?= __('enter_email') ?>" style="border-radius: 8px 0 0 8px;">
                    <button class="btn btn-success newsletter-btn" type="button" style="border-radius: 0 8px 8px 0;"><i class="fa fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div class="py-3 bg-black mt-4 copyright-bar">
        <div class="container">
            <div class="row align-items-center small text-secondary">
                <div class="col-md-6 text-center text-md-start">
                    <span>&copy; <?php echo date('Y'); ?> <span class="text-success fw-bold site-name"><?= __('site_title') ?></span> <?= __('all_rights_reserved') ?></span>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" id="backToTop" class="text-success text-decoration-none back-to-top"><i class="fas fa-arrow-up me-1"></i> <?= __('back_to_top') ?></a>
                </div>
            </div>
        </div>
    </div>

    <!-- WhatsApp Button -->
    <a href="https://wa.me/923122551505" target="_blank" class="whatsapp-btn" style="position: fixed; bottom: 30px; right: 30px; background-color: #25D366; color: white; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 28px; box-shadow: 2px 2px 10px rgba(0,0,0,0.3); z-index: 9999; text-decoration: none;">
        <i class="fab fa-whatsapp"></i>
    </a>
</footer>

<style>
    /* Base Footer Styles */
    #footer { background-color: #111418 !important; transition: all 0.3s ease; }
    .footer-link { transition: all 0.3s ease-in-out; }
    .footer-link:hover { color: #198754 !important; padding-left: 6px; }
    .social-icon:hover { background-color: #198754 !important; border-color: #198754 !important; color: #fff !important; transform: translateY(-3px); }
    .back-to-top { transition: all 0.3s ease; }
    .back-to-top:hover { color: #198754 !important; transform: translateY(-3px); display: inline-block; }
    .footer-heading { position: relative; padding-bottom: 10px; }
    .footer-heading::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 40px;
        height: 2px;
        background-color: #198754;
        transition: width 0.3s ease;
    }
    #footer:hover .footer-heading::after { width: 60px; }
    
    /* Dark Mode Footer Styles */
    body.dark-mode #footer {
        background-color: #0a0a0f !important;
        border-top-color: #3b82f6 !important;
    }
    
    /* Fix for Site Title in Dark Mode - Change from green to blue */
    body.dark-mode #footer .footer-site-title {
        color: #60a5fa !important;
    }
    
    body.dark-mode #footer .footer-site-title i {
        color: #60a5fa !important;
    }
    
    body.dark-mode #footer .footer-heading {
        color: #e6edf3 !important;
    }
    
    body.dark-mode #footer .footer-heading::after {
        background-color: #3b82f6;
    }
    
    body.dark-mode #footer .footer-link {
        color: #8b949e !important;
    }
    
    body.dark-mode #footer .footer-link:hover {
        color: #60a5fa !important;
    }
    
    body.dark-mode #footer .footer-link i {
        color: #60a5fa !important;
    }
    
    body.dark-mode #footer .footer-text {
        color: #8b949e !important;
    }
    
    body.dark-mode #footer .footer-description {
        color: #8b949e !important;
    }
    
    body.dark-mode #footer .footer-icon {
        color: #60a5fa !important;
    }
    
    body.dark-mode #footer .social-icon {
        border-color: #30363d !important;
        color: #c9d1d9 !important;
    }
    
    body.dark-mode #footer .social-icon:hover {
        background-color: #3b82f6 !important;
        border-color: #3b82f6 !important;
    }
    
    body.dark-mode #footer .copyright-bar {
        background-color: #06060a !important;
    }
    
    body.dark-mode #footer .copyright-bar .site-name {
        color: #60a5fa !important;
    }
    
    body.dark-mode #footer .copyright-bar .text-secondary {
        color: #8b949e !important;
    }
    
    body.dark-mode #footer .back-to-top {
        color: #60a5fa !important;
    }
    
    body.dark-mode #footer .back-to-top:hover {
        color: #3b82f6 !important;
    }
    
    body.dark-mode #footer .newsletter-input {
        background-color: #1a1a2e !important;
        border-color: #30363d !important;
        color: #e6edf3 !important;
    }
    
    body.dark-mode #footer .newsletter-input::placeholder {
        color: #6c757d !important;
    }
    
    body.dark-mode #footer .newsletter-btn {
        background-color: #3b82f6 !important;
        border-color: #3b82f6 !important;
    }
    
    body.dark-mode #footer .newsletter-btn:hover {
        background-color: #2563eb !important;
    }
    
    body.dark-mode #footer .db-status {
        background-color: rgba(59, 130, 246, 0.15) !important;
        color: #60a5fa !important;
        border-color: rgba(59, 130, 246, 0.3) !important;
    }
    
    body.dark-mode #footer .whatsapp-btn {
        background-color: #075e54 !important;
        box-shadow: 2px 2px 10px rgba(0,0,0,0.5) !important;
    }
    
    body.dark-mode #footer .whatsapp-btn:hover {
        background-color: #128C7E !important;
    }
    
    /* Animations */
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-3px); }
    }
    .animate-bounce { animation: bounce 1s infinite; }
</style>

<script>
// Back to Top Button
document.getElementById('backToTop')?.addEventListener('click', function(e) {
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
</script>