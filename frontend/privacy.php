<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Privacy Policy Page
 */

require_once __DIR__ . '/../config/db.php';

$page_title = "Privacy Policy";
include_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5 cs_blue_bg text-white">
    <div class="container text-center py-4">
        <h1 class="text-white mb-2">Privacy Policy</h1>
        <p class="text-white-50 mb-0">How Medilo Healthcare System protects and manages your medical & personal data</p>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm rounded-3 p-4 p-md-5">
                    
                    <div class="mb-4 pb-3 border-bottom">
                        <span class="badge bg-primary px-3 py-2 fs-7 mb-2">Effective Date: January 1, 2026</span>
                        <h3 class="text-primary mb-2">Medilo Patient Data & Privacy Protection Policy</h3>
                        <p class="text-muted">At Medilo Healthcare, protecting your personal health information (PHI) and ensuring digital privacy is our highest priority.</p>
                    </div>

                    <div class="mb-4">
                        <h4 class="text-primary mb-3"><i class="fa-solid fa-user-shield me-2"></i> 1. Information We Collect</h4>
                        <p class="text-muted">We collect information necessary to facilitate medical appointment bookings and manage patient care records:</p>
                        <ul class="text-muted ps-4">
                            <li class="mb-2"><strong>Personal Identifiers:</strong> Full Name, Email Address, Phone Number, Date of Birth, Gender, and Physical Address.</li>
                            <li class="mb-2"><strong>Medical Records:</strong> Blood Group, Vaccination Logs, Past Medical History, and Consultation Notes entered by attending physicians.</li>
                            <li class="mb-2"><strong>Payment Information:</strong> Transaction IDs and payment status from online settlements via Stripe or PayPal. (We do not store full credit card numbers).</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h4 class="text-primary mb-3"><i class="fa-solid fa-lock me-2"></i> 2. How Your Data Is Used</h4>
                        <p class="text-muted">Your data is strictly utilized to deliver and improve medical services within Medilo:</p>
                        <ul class="text-muted ps-4">
                            <li class="mb-2">Scheduling, confirming, and updating appointments with participating doctors.</li>
                            <li class="mb-2">Generating electronic medical reports, digital receipts, and consultation history.</li>
                            <li class="mb-2">Communicating essential healthcare updates, appointment reminders, and system notifications.</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h4 class="text-primary mb-3"><i class="fa-solid fa-share-nodes me-2"></i> 3. Information Sharing & Disclosure</h4>
                        <p class="text-muted">Medilo does not sell, rent, or lease patient data to third parties. Information is only shared under the following conditions:</p>
                        <ul class="text-muted ps-4">
                            <li class="mb-2"><strong>With Healthcare Providers:</strong> Your assigned doctor will access relevant patient history during your consultation.</li>
                            <li class="mb-2"><strong>Legal Compliance:</strong> When required by law or judicial process to protect patient safety.</li>
                        </ul>
                    </div>

                    <div class="mb-4">
                        <h4 class="text-primary mb-3"><i class="fa-solid fa-shield-halved me-2"></i> 4. Data Security Standard</h4>
                        <p class="text-muted">All database interactions use PDO prepared statements to safeguard against SQL Injection. User authentication relies on secure hashed password algorithms, and web traffic is encrypted via HTTPS standard protocol.</p>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="text-muted small">Have privacy questions? Email us at <a href="mailto:privacy@medilo.com" class="text-primary text-decoration-none">privacy@medilo.com</a></span>
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
