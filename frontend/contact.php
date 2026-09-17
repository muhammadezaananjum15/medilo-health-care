<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Contact Us & Clinic Location Page
 */

require_once __DIR__ . '/../config/config.php';

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sent = true;
}

$page_title = "Contact Us";
include_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5 cs_blue_bg text-white">
    <div class="container text-center py-4">
        <h1 class="text-white mb-2">Contact Medilo Health Care</h1>
        <p class="text-white-50 mb-0">We are here to answer your medical inquiries and assist with doctor appointments</p>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container py-3">

        <?php if ($sent): ?>
            <div class="alert alert-success border-0 shadow-sm p-4 mb-4 text-center">
                <i class="fa-solid fa-circle-check fs-1 text-success mb-2 d-block"></i>
                <h4 class="alert-heading">Thank You for Contacting Medilo!</h4>
                <p class="mb-0">Your message has been dispatched to our patient care desk. We will respond via email shortly.</p>
            </div>
        <?php endif; ?>

        <div class="row gy-4">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-3 p-4 h-100">
                    <h4 class="border-bottom pb-2 mb-4 text-primary"><i class="fa-solid fa-hospital me-2"></i> Clinic Contact Details</h4>
                    
                    <div class="d-flex mb-4">
                        <div class="me-3 fs-3 text-info"><i class="fa-solid fa-location-dot"></i></div>
                        <div>
                            <h6 class="mb-1 fw-bold">Headquarters Address</h6>
                            <p class="text-muted mb-0">100 Healthcare Way, Medical Suite 1, New York, NY 10001, USA</p>
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <div class="me-3 fs-3 text-info"><i class="fa-solid fa-phone"></i></div>
                        <div>
                            <h6 class="mb-1 fw-bold">24/7 Helpline</h6>
                            <p class="text-muted mb-0">+1 (800) 555-MEDILO / +1 (555) 019-2831</p>
                        </div>
                    </div>

                    <div class="d-flex mb-4">
                        <div class="me-3 fs-3 text-info"><i class="fa-solid fa-envelope"></i></div>
                        <div>
                            <h6 class="mb-1 fw-bold">Email Support</h6>
                            <p class="text-muted mb-0">support@medilo.com / info@medilo.com</p>
                        </div>
                    </div>

                    <div class="d-flex">
                        <div class="me-3 fs-3 text-info"><i class="fa-solid fa-clock"></i></div>
                        <div>
                            <h6 class="mb-1 fw-bold">Working Hours</h6>
                            <p class="text-muted mb-0">Mon - Fri: 8:00 AM - 8:00 PM<br>Sat - Sun: 9:00 AM - 5:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-3 p-4">
                    <h4 class="border-bottom pb-2 mb-4 text-primary"><i class="fa-solid fa-paper-plane me-2"></i> Send Us a Direct Message</h4>
                    
                    <form action="<?php echo APP_URL; ?>/frontend/contact.php" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Your Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required placeholder="Enter full name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" required placeholder="name@example.com">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone" required placeholder="+1 (555) 000-0000">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Subject</label>
                            <input type="text" class="form-control" name="subject" placeholder="Inquiry topic">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Message Details <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="message" rows="4" required placeholder="How can we help you?"></textarea>
                        </div>

                        <button type="submit" class="cs_btn cs_style_1 cs_color_1 w-100 py-3">
                            <span>Send Message</span> <i class="fa-solid fa-paper-plane ms-1"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
