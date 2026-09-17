<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * System Settings & Payment Gateway Configuration
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Admin Access Control
require_role('admin');

$updated = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updated = true;
    set_flash_message('success', 'System and Payment Gateway settings saved successfully.');
}

$page_title = "System Settings";
include_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row gy-4">
            
            <div class="col-lg-3">
                <?php include_once __DIR__ . '/../includes/sidebar.php'; ?>
            </div>

            <div class="col-lg-9">
                <div class="card border-0 shadow-sm rounded-3 p-4">
                    <h4 class="border-bottom pb-2 mb-4 text-primary"><i class="fa-solid fa-gear me-2"></i> System & Payment Gateway Settings</h4>

                    <form action="<?php echo APP_URL; ?>/admin/settings.php" method="POST">
                        <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-globe me-2"></i> Website General Configuration</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Application Name</label>
                                <input type="text" class="form-control" name="app_name" value="Medilo Medical & Health">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Support Email ID</label>
                                <input type="email" class="form-control" name="app_email" value="support@medilo.com">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">24/7 Helpline Phone</label>
                                <input type="text" class="form-control" name="app_phone" value="+1 (800) 555-MEDILO">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Clinic Address</label>
                                <input type="text" class="form-control" name="app_address" value="100 Healthcare Way, New York, NY">
                            </div>
                        </div>

                        <h5 class="fw-bold text-primary mb-3 mt-4"><i class="fa-solid fa-credit-card me-2"></i> Payment Gateway API Credentials</h5>
                        
                        <div class="card bg-light p-3 border mb-3">
                            <h6 class="fw-bold text-dark"><i class="fa-brands fa-stripe text-primary me-1"></i> Stripe Payment Gateway Configuration</h6>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold">Stripe Publishable Key</label>
                                    <input type="text" class="form-control form-control-sm" value="pk_test_51MzMediloSampleStripeKey123456">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold">Stripe Secret Key</label>
                                    <input type="password" class="form-control form-control-sm" value="sk_test_51MzMediloSampleStripeKey123456">
                                </div>
                            </div>
                        </div>

                        <div class="card bg-light p-3 border mb-4">
                            <h6 class="fw-bold text-dark"><i class="fa-brands fa-paypal text-info me-1"></i> PayPal Payment Gateway Configuration</h6>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold">PayPal Client ID</label>
                                    <input type="text" class="form-control form-control-sm" value="sandbox_paypal_client_id_medilo_2026">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-bold">Mode</label>
                                    <select class="form-select form-select-sm">
                                        <option value="sandbox" selected>Sandbox (Testing)</option>
                                        <option value="live">Live Production</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="cs_btn cs_style_1 cs_color_1 py-3 px-4">
                            <span>Save Configuration</span> <i class="fa-solid fa-floppy-disk ms-1"></i>
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
