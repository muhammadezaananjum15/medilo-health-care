<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Terms of Service Page
 */

require_once __DIR__ . '/../config/db.php';

$page_title = "Terms of Service";
include_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5 cs_blue_bg text-white">
    <div class="container text-center py-4">
        <h1 class="text-white mb-2">Terms of Service</h1>
        <p class="text-white-50 mb-0">Terms and conditions governing the use of Medilo Medical Management Platform</p>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm rounded-3 p-4 p-md-5">
                    
                    <div class="mb-4 pb-3 border-bottom">
                        <span class="badge bg-primary px-3 py-2 fs-7 mb-2">Effective Date: January 1, 2026</span>
                        <h3 class="text-primary mb-2">Medilo System Terms & Usage Agreement</h3>
                        <p class="text-muted">By registering or accessing the Medilo Web Application, you agree to comply with the terms set forth below.</p>
                    </div>

                    <div class="mb-4">
                        <h4 class="text-primary mb-3"><i class="fa-solid fa-file-contract me-2"></i> 1. Acceptance of Terms</h4>
                        <p class="text-muted">By creating a Patient, Doctor, or Administrator account on Medilo, you acknowledge that you have read, understood, and agreed to be bound by these Terms of Service and our Privacy Policy.</p>
                    </div>

                    <div class="mb-4">
                        <h4 class="text-primary mb-3"><i class="fa-solid fa-calendar-check me-2"></i> 2. Appointment Booking & Cancellation Policy</h4>
                        <ul class="text-muted ps-4">
                            <li class="mb-2"><strong>Slot Availability:</strong> Doctor schedules are published in real time. Slots are reserved upon successful payment or booking confirmation.</li>
                            <li class="mb-2"><strong>Consultation Fees:</strong> Patients agree to settle fees via integrated payment gateways (Stripe/PayPal) as specified during booking.</li>
                            <li class="mb-2"><strong>Cancellations & Rescheduling:</strong> Cancellations must be submitted at least 2 hours prior to the appointment shift. Refunds are processed according to clinic guidelines.</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h4 class="text-primary mb-3"><i class="fa-solid fa-user-check me-2"></i> 3. User Responsibilities & Account Integrity</h4>
                        <p class="text-muted">Users are required to provide accurate, up-to-date information during account registration. Patients and Doctors are responsible for maintaining the confidentiality of their login credentials.</p>
                    </div>

                    <div class="mb-4">
                        <h4 class="text-primary mb-3"><i class="fa-solid fa-notes-medical me-2"></i> 4. Emergency Medical Disclaimer</h4>
                        <div class="alert alert-warning border-0 p-3 fs-7">
                            <strong><i class="fa-solid fa-triangle-exclamation me-1"></i> Medical Emergency Notice:</strong>
                            Medilo is an online booking and management platform. It is <strong>NOT</strong> designed for life-threatening medical emergencies. If you are experiencing a medical emergency, please call local emergency services (+1 800 555 MEDILO) or visit the nearest hospital emergency room immediately.
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="text-muted small">Questions about our Terms? Contact our legal compliance department.</span>
                        <a href="<?php echo APP_URL; ?>/frontend/contact.php" class="cs_btn cs_style_1 cs_color_1 py-2 px-3">
                            <span>Contact Care Desk</span> <i class="fa-solid fa-headset"></i>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
