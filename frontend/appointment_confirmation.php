<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Appointment Confirmation & Printable Receipt Card
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_login();

$user = get_current_user_data();
$ref = sanitize_input($_GET['ref'] ?? '');
$apt_id = intval($_GET['id'] ?? 0);

$appointment = null;

if (!empty($ref)) {
    $appointment = fetchOne("
        SELECT a.*, p.blood_group, p.dob, p.emergency_contact, u_pat.full_name AS patient_name, u_pat.email AS patient_email, u_pat.phone AS patient_phone, u_pat.address AS patient_address,
               u_doc.full_name AS doctor_name, u_doc.phone AS doctor_phone, u_doc.email AS doctor_email, d.qualification, d.avatar AS doctor_avatar,
               s.name AS specialty_name, c.name AS city_name, c.state AS city_state
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        JOIN users u_pat ON p.user_id = u_pat.id
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users u_doc ON d.user_id = u_doc.id
        JOIN specialties s ON d.specialty_id = s.id
        JOIN cities c ON d.city_id = c.id
        WHERE a.appointment_number = ?
    ", [$ref]);
} elseif ($apt_id > 0) {
    $appointment = fetchOne("
        SELECT a.*, p.blood_group, p.dob, p.emergency_contact, u_pat.full_name AS patient_name, u_pat.email AS patient_email, u_pat.phone AS patient_phone, u_pat.address AS patient_address,
               u_doc.full_name AS doctor_name, u_doc.phone AS doctor_phone, u_doc.email AS doctor_email, d.qualification, d.avatar AS doctor_avatar,
               s.name AS specialty_name, c.name AS city_name, c.state AS city_state
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        JOIN users u_pat ON p.user_id = u_pat.id
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users u_doc ON d.user_id = u_doc.id
        JOIN specialties s ON d.specialty_id = s.id
        JOIN cities c ON d.city_id = c.id
        WHERE a.id = ?
    ", [$apt_id]);
}

if (!$appointment) {
    set_flash_message('danger', 'Appointment booking reference not found.');
    header('Location: ' . APP_URL . '/frontend/patient_dashboard.php');
    exit;
}

$page_title = "Appointment Confirmation - " . $appointment['appointment_number'];
include_once __DIR__ . '/../includes/header.php';
?>

<style>
@media print {
    .no-print, header, footer, .cs_header, .cs_footer, .cs_top_header {
        display: none !important;
    }
    body, .bg-light {
        background-color: #fff !important;
        padding: 0 !important;
    }
    .print-card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
}
</style>

<section class="py-5 bg-light">
    <div class="container py-3">
        
        <!-- Header Controls (No Print) -->
        <div class="row justify-content-center mb-4 no-print">
            <div class="col-lg-8 d-flex justify-content-between align-items-center">
                <a href="<?php echo APP_URL; ?>/frontend/patient_dashboard.php?tab=appointments" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> My Appointments
                </a>
                <div class="d-flex gap-2">
                    <button onclick="window.print();" class="btn btn-primary fw-bold">
                        <i class="fa-solid fa-print me-1"></i> Print Receipt Card
                    </button>
                    <a href="<?php echo APP_URL; ?>/frontend/book_appointment.php" class="btn btn-outline-primary">
                        <i class="fa-solid fa-calendar-plus me-1"></i> Book Another
                    </a>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- Success Banner -->
                <div class="alert alert-success d-flex align-items-center mb-4 no-print shadow-sm rounded-3" role="alert">
                    <i class="fa-solid fa-circle-check fs-2 me-3 text-success"></i>
                    <div>
                        <h5 class="alert-heading mb-1 fw-bold">Appointment Successfully Confirmed!</h5>
                        <p class="mb-0 small">A confirmation notification and digital receipt have been recorded. Please arrive 10 minutes before your scheduled time.</p>
                    </div>
                </div>

                <!-- Printable Card / Receipt -->
                <div class="card border-0 shadow-lg rounded-3 overflow-hidden print-card">
                    <div class="card-header cs_blue_bg text-white p-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h3 class="text-white mb-0 fw-bold"><i class="fa-solid fa-hospital me-2"></i> <?php echo APP_NAME; ?></h3>
                                <p class="text-white-50 small mb-0">Official Digital Consultation Pass & Payment Receipt</p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success fs-6 px-3 py-2">
                                    <i class="fa-solid fa-check me-1"></i> Confirmed & Paid
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        
                        <!-- Reference Number Banner -->
                        <div class="bg-light p-3 rounded-3 border d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                            <div>
                                <span class="text-muted small d-block">APPOINTMENT REFERENCE NUMBER</span>
                                <span class="fs-4 fw-bold text-primary font-monospace"><?php echo htmlspecialchars($appointment['appointment_number']); ?></span>
                            </div>
                            <div class="text-md-end">
                                <span class="text-muted small d-block">BOOKED ON</span>
                                <span class="fw-semibold text-dark"><?php echo date('M d, Y h:i A', strtotime($appointment['created_at'])); ?></span>
                            </div>
                        </div>

                        <div class="row gy-4 mb-4">
                            <!-- Patient Information -->
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 h-100 bg-white">
                                    <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">
                                        <i class="fa-solid fa-user me-2"></i> Patient Information
                                    </h6>
                                    <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($appointment['patient_name']); ?></p>
                                    <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($appointment['patient_phone']); ?></p>
                                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($appointment['patient_email']); ?></p>
                                    <p class="mb-1"><strong>Address:</strong> <?php echo htmlspecialchars($appointment['patient_address'] ?: 'Not Provided'); ?></p>
                                    <?php if (!empty($appointment['blood_group'])): ?>
                                        <p class="mb-0"><strong>Blood Group:</strong> <span class="badge bg-danger"><?php echo htmlspecialchars($appointment['blood_group']); ?></span></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Doctor Information -->
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 h-100 bg-white">
                                    <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">
                                        <i class="fa-solid fa-user-doctor me-2"></i> Doctor & Clinic Details
                                    </h6>
                                    <p class="mb-1"><strong>Doctor:</strong> <?php echo htmlspecialchars($appointment['doctor_name']); ?></p>
                                    <p class="mb-1"><strong>Specialty:</strong> <span class="badge bg-info text-dark"><?php echo htmlspecialchars($appointment['specialty_name']); ?></span></p>
                                    <p class="mb-1"><strong>Qualification:</strong> <?php echo htmlspecialchars($appointment['qualification']); ?></p>
                                    <p class="mb-1"><strong>Location:</strong> <?php echo htmlspecialchars($appointment['city_name'] . ', ' . $appointment['city_state']); ?></p>
                                    <p class="mb-0"><strong>Doctor Phone:</strong> <?php echo htmlspecialchars($appointment['doctor_phone']); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Appointment Schedule & Location -->
                        <div class="card bg-light border-0 p-4 rounded-3 mb-4">
                            <h6 class="text-primary fw-bold mb-3"><i class="fa-solid fa-clock me-2"></i> Appointment Schedule</h6>
                            <div class="row text-center gy-3">
                                <div class="col-sm-4">
                                    <div class="p-3 bg-white rounded-3 border">
                                        <span class="text-muted small d-block">DATE</span>
                                        <h5 class="mb-0 text-primary fw-bold"><?php echo date('M d, Y', strtotime($appointment['appointment_date'])); ?></h5>
                                        <small class="text-muted"><?php echo date('l', strtotime($appointment['appointment_date'])); ?></small>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-3 bg-white rounded-3 border">
                                        <span class="text-muted small d-block">TIME SLOT</span>
                                        <h5 class="mb-0 text-success fw-bold"><?php echo date('g:i A', strtotime($appointment['appointment_time'])); ?></h5>
                                        <small class="text-muted">Expected 30 min duration</small>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-3 bg-white rounded-3 border">
                                        <span class="text-muted small d-block">FEE STATUS</span>
                                        <h5 class="mb-0 text-dark fw-bold">$<?php echo number_format($appointment['fee'], 2); ?></h5>
                                        <small class="badge bg-success">Paid via <?php echo htmlspecialchars($appointment['payment_method']); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reason for Visit -->
                        <?php if (!empty($appointment['reason'])): ?>
                            <div class="mb-4">
                                <h6 class="text-primary fw-bold mb-2"><i class="fa-solid fa-notes-medical me-2"></i> Consultation Purpose / Symptoms</h6>
                                <p class="text-muted bg-white p-3 border rounded-3 mb-0"><?php echo nl2br(htmlspecialchars($appointment['reason'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Transaction Audit Details -->
                        <div class="border-top pt-3 text-muted small">
                            <div class="row">
                                <div class="col-sm-6">
                                    <strong>Gateway Transaction ID:</strong> <?php echo htmlspecialchars($appointment['transaction_id'] ?? 'N/A'); ?>
                                </div>
                                <div class="col-sm-6 text-sm-end">
                                    <strong>Payment Channel:</strong> <?php echo htmlspecialchars($appointment['payment_method'] ?? 'Stripe Card'); ?>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="card-footer bg-light p-3 text-center text-muted small">
                        <i class="fa-solid fa-circle-info me-1"></i> Please present this receipt or reference number upon arrival at the medical clinic reception.
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
