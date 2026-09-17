<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Global Footer Include Template
 */
?>
    <!-- Start Footer Section -->
    <footer class="cs_footer cs_style_1 cs_heading_color cs_bg_filed" data-src="<?php echo APP_URL; ?>/assets/img/footer_bg.jpg">
        <div class="cs_footer_main">
            <div class="container">
                <div class="row gy-4 gy-lg-0">
                    <!-- Column 1: About Medilo -->
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="cs_footer_widget">
                            <div class="cs_footer_logo">
                                <img src="<?php echo APP_URL; ?>/assets/img/footer_logo.svg" alt="Medilo Logo">
                            </div>
                            <p class="cs_footer_text">
                                Medilo is a premier healthcare platform bringing top medical specialists, real-time appointment booking, and patient record management together into one seamless experience.
                            </p>
                            <div class="cs_social_btns cs_style_1">
                                <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="cs_center" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                                <a href="https://pinterest.com" target="_blank" rel="noopener noreferrer" class="cs_center" title="Pinterest"><i class="fa-brands fa-pinterest-p"></i></a>
                                <a href="https://twitter.com" target="_blank" rel="noopener noreferrer" class="cs_center" title="Twitter"><i class="fa-brands fa-twitter"></i></a>
                                <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="cs_center" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Quick Links -->
                    <div class="col-lg-2 col-md-6 col-12">
                        <div class="cs_footer_widget">
                            <h3 class="cs_footer_widget_title">Quick Links</h3>
                            <ul class="cs_footer_widget_nav cs_mp_0">
                                <li><a href="<?php echo APP_URL; ?>/index.php"><i class="fa-solid fa-angle-right me-1"></i> Home</a></li>
                                <li><a href="<?php echo APP_URL; ?>/frontend/doctors.php"><i class="fa-solid fa-angle-right me-1"></i> Find Doctors</a></li>
                                <li><a href="<?php echo APP_URL; ?>/frontend/services.php"><i class="fa-solid fa-angle-right me-1"></i> Our Services</a></li>
                                <li><a href="<?php echo APP_URL; ?>/frontend/health_info.php"><i class="fa-solid fa-angle-right me-1"></i> Health Library</a></li>
                                <li><a href="<?php echo APP_URL; ?>/frontend/news.php"><i class="fa-solid fa-angle-right me-1"></i> Medical News</a></li>
                                <li><a href="<?php echo APP_URL; ?>/frontend/contact.php"><i class="fa-solid fa-angle-right me-1"></i> Contact Us</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Column 3: Working Hours -->
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="cs_footer_widget">
                            <h3 class="cs_footer_widget_title">Clinic Hours</h3>
                            <ul class="cs_footer_widget_nav cs_mp_0">
                                <li><strong>Monday - Friday:</strong> 8:00 AM - 8:00 PM</li>
                                <li><strong>Saturday:</strong> 9:00 AM - 5:00 PM</li>
                                <li><strong>Sunday:</strong> Emergency Care Only</li>
                                <li class="mt-3"><i class="fa-solid fa-phone text-info me-2"></i> <strong>Emergency:</strong><br><a href="tel:+18005556334" class="text-white text-decoration-none">+1 (800) 555-MEDILO</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Column 4: Newsletter -->
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="cs_footer_widget">
                            <h3 class="cs_footer_widget_title">Medical Updates</h3>
                            <p class="mb-3 text-white-50">Subscribe to receive health tips, doctor schedules, and medical news updates.</p>
                            <form action="#" class="cs_newsletter_form" onsubmit="event.preventDefault(); alert('Thank you for subscribing to Medilo Health Updates!');">
                                <div class="cs_newsletter_input_wrap">
                                    <input type="email" placeholder="Enter your email address" required class="cs_newsletter_input">
                                </div>
                                <button type="submit" class="cs_btn cs_style_1 cs_color_1 w-100 mt-2">
                                    <span>Subscribe Now</span> <i class="fa-solid fa-paper-plane ms-1"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="cs_footer_bottom cs_white_bg">
            <div class="container">
                <div class="cs_footer_bottom_in d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                    <p class="cs_copyright cs_mp_0 text-muted">
                        Copyright &copy; <?php echo date('Y'); ?> <strong class="text-primary">Medilo Healthcare System</strong>. All rights reserved.
                    </p>
                    <ul class="cs_footer_bottom_nav cs_mp_0 d-flex gap-3 list-unstyled mb-0">
                        <li><a href="<?php echo APP_URL; ?>/frontend/privacy.php" class="text-secondary text-decoration-none">Privacy Policy</a></li>
                        <li><a href="<?php echo APP_URL; ?>/frontend/terms.php" class="text-secondary text-decoration-none">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    <!-- End Footer Section -->

    <!-- Scripts -->
    <!-- Bootstrap 5 JS Bundle (required for alerts, dropdowns, modals) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/jquery-3.6.0.min.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/jquery.slick.min.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/odometer.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/wow.min.js"></script>
    <script src="<?php echo APP_URL; ?>/assets/js/main.js"></script>
</body>

</html>
