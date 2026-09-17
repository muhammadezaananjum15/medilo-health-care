<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Patient Personal Dashboard, Medical Records & Appointment Tracker
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Ensure user is patient or admin
require_role(['patient', 'admin']);

$user = get_current_user_data();

// Get patient details
$patient = fetchOne("
    SELECT p.*, u.full_name, u.email, u.phone, u.address 
    FROM patients p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.user_id = ?
", [$user['id']]);

if (!$patient) {
    // Auto-create patient record if missing
    $pid = executeQuery("INSERT INTO patients (user_id) VALUES (?)", [$user['id']]);
    $patient = fetchOne("SELECT p.*, u.full_name, u.email, u.phone, u.address FROM patients p JOIN users u ON p.user_id = u.id WHERE p.user_id = ?", [$user['id']]);
}

$tab = sanitize_input($_GET['tab'] ?? 'dashboard');
$action = sanitize_input($_GET['action'] ?? '');
$apt_id = intval($_GET['apt_id'] ?? 0);

// Handle Cancel Appointment Action
if ($action === 'cancel' && $apt_id > 0) {
    executeQuery("UPDATE appointments SET status = 'Cancelled' WHERE id = ? AND patient_id = ?", [$apt_id, $patient['id']]);
    set_flash_message('success', 'Appointment successfully cancelled.');
    header('Location: ' . APP_URL . '/frontend/patient_dashboard.php?tab=appointments');
    exit;
}

// Handle Profile Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $gender = sanitize_input($_POST['gender'] ?? 'Male');
    $blood_group = sanitize_input($_POST['blood_group'] ?? 'O+');
    $emergency_contact = sanitize_input($_POST['emergency_contact'] ?? '');
    $medical_history = sanitize_input($_POST['medical_history'] ?? '');
    $vaccination_records = sanitize_input($_POST['vaccination_records'] ?? '');

    executeQuery("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?", [$full_name, $phone, $address, $user['id']]);
    executeQuery("UPDATE patients SET dob = ?, gender = ?, blood_group = ?, emergency_contact = ?, medical_history = ?, vaccination_records = ? WHERE id = ?", 
        [$dob, $gender, $blood_group, $emergency_contact, $medical_history, $vaccination_records, $patient['id']]
    );

    set_flash_message('success', 'Personal profile and medical records updated successfully.');
    header('Location: ' . APP_URL . '/frontend/patient_dashboard.php?tab=records');
    exit;
}

// Fetch Patient Appointments
$appointments = fetchAll("
    SELECT a.*, d.consultation_fee, u_doc.full_name AS doctor_name, s.name AS specialty_name, c.name AS city_name 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    JOIN users u_doc ON d.user_id = u_doc.id 
    JOIN specialties s ON d.specialty_id = s.id 
    JOIN cities c ON d.city_id = c.id 
    WHERE a.patient_id = ? 
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
", [$patient['id']]);

// Fetch Patient Notifications
$notifications = fetchAll("SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 5", [$user['id']]);

$page_title = "Patient Dashboard";
include_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row gy-4">
            
            <!-- Sidebar -->
            <div class="col-lg-3">
                <?php include_once __DIR__ . '/../includes/sidebar.php'; ?>
            </div>

            <!-- Dashboard Content Area -->
            <div class="col-lg-9">

                <!-- Notifications Alert Box -->
                <?php if (count($notifications) > 0): ?>
                    <div class="alert alert-info border-0 shadow-sm mb-4">
                        <h6 class="fw-bold mb-2"><i class="fa-solid fa-bell me-2"></i> Recent System Reminders & Confirmations</h6>
                        <ul class="mb-0 ps-3 small">
                            <?php foreach ($notifications as $note): ?>
                                <li><strong><?php echo htmlspecialchars($note['title']); ?>:</strong> <?php echo htmlspecialchars($note['message']); ?> <span class="text-muted fs-7">(<?php echo date('M d, H:i', strtotime($note['created_at'])); ?>)</span></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($tab === 'appointments'): ?>
                    <!-- Appointments List -->
                    <div class="card border-0 shadow-sm rounded-3 p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                            <h4 class="mb-0 text-primary"><i class="fa-solid fa-calendar-days me-2"></i> My Booked Appointments</h4>
                            <a href="<?php echo APP_URL; ?>/frontend/book_appointment.php" class="cs_btn cs_style_1 cs_color_1 py-2 px-3">
                                <span>Book New Appointment</span> <i class="fa-solid fa-plus ms-1"></i>
                            </a>
                        </div>

                        <?php if (count($appointments) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Booking Ref</th>
                                            <th>Doctor & Specialty</th>
                                            <th>Date & Time</th>
                                            <th>Fee ($)</th>
                                            <th>Status</th>
                                            <th>Payment</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($appointments as $apt): ?>
                                            <tr>
                                                <td class="fw-bold text-primary"><?php echo htmlspecialchars($apt['appointment_number']); ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($apt['doctor_name']); ?></strong><br>
                                                    <span class="badge bg-secondary fs-7"><?php echo htmlspecialchars($apt['specialty_name']); ?></span>
                                                </td>
                                                <td>
                                                    <?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?><br>
                                                    <small class="text-muted"><i class="fa-solid fa-clock me-1"></i><?php echo date('g:i A', strtotime($apt['appointment_time'])); ?></small>
                                                </td>
                                                <td class="fw-bold text-success">$<?php echo number_format($apt['fee'], 2); ?></td>
                                                <td>
                                                    <?php if ($apt['status'] === 'Confirmed'): ?>
                                                        <span class="badge bg-success">Confirmed</span>
                                                    <?php elseif ($apt['status'] === 'Pending'): ?>
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    <?php elseif ($apt['status'] === 'Completed'): ?>
                                                        <span class="badge bg-info text-dark">Completed</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Cancelled</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($apt['payment_method'] ?? 'Card'); ?></span><br>
                                                    <small class="text-muted fs-7"><?php echo htmlspecialchars($apt['payment_status']); ?></small>
                                                </td>
                                                <td>
                                                    <?php if ($apt['status'] !== 'Cancelled' && $apt['status'] !== 'Completed'): ?>
                                                        <a href="<?php echo APP_URL; ?>/frontend/patient_dashboard.php?action=cancel&apt_id=<?php echo $apt['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to cancel this appointment?');">
                                                            Cancel
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted my-3">No appointment records found.</p>
                        <?php endif; ?>
                    </div>

                <?php elseif ($tab === 'records'): ?>
                    <!-- Medical Records & Profile Edit -->
                    <div class="card border-0 shadow-sm rounded-3 p-4">
                        <h4 class="border-bottom pb-2 mb-4 text-primary"><i class="fa-solid fa-notes-medical me-2"></i> Patient Profile & Medical History</h4>

                        <form action="<?php echo APP_URL; ?>/frontend/patient_dashboard.php" method="POST">
                            <input type="hidden" name="update_profile" value="1">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="full_name" required value="<?php echo htmlspecialchars($patient['full_name']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="phone" required value="<?php echo htmlspecialchars($patient['phone']); ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Email Address <span class="text-muted">(Read Only)</span></label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($patient['email']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Street Address <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="address" required value="<?php echo htmlspecialchars($patient['address']); ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Date of Birth</label>
                                    <input type="date" class="form-control" name="dob" value="<?php echo $patient['dob']; ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Gender</label>
                                    <select class="form-select" name="gender">
                                        <option value="Male" <?php echo ($patient['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($patient['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo ($patient['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Blood Group</label>
                                    <select class="form-select" name="blood_group">
                                        <option value="O+" <?php echo ($patient['blood_group'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                                        <option value="O-" <?php echo ($patient['blood_group'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                                        <option value="A+" <?php echo ($patient['blood_group'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                                        <option value="A-" <?php echo ($patient['blood_group'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                                        <option value="B+" <?php echo ($patient['blood_group'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                                        <option value="B-" <?php echo ($patient['blood_group'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                                        <option value="AB+" <?php echo ($patient['blood_group'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                        <option value="AB-" <?php echo ($patient['blood_group'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Emergency Contact Number</label>
                                <input type="text" class="form-control" name="emergency_contact" value="<?php echo htmlspecialchars($patient['emergency_contact'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Medical History / Allergies</label>
                                <textarea class="form-control" name="medical_history" rows="3"><?php echo htmlspecialchars($patient['medical_history'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Vaccination Records</label>
                                <textarea class="form-control" name="vaccination_records" rows="3"><?php echo htmlspecialchars($patient['vaccination_records'] ?? ''); ?></textarea>
                            </div>

                            <button type="submit" class="cs_btn cs_style_1 cs_color_1 py-3 px-4">
                                <span>Save Changes</span> <i class="fa-solid fa-floppy-disk ms-1"></i>
                            </button>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- Main Dashboard Overview KPI Cards -->
                    <div class="row gy-4 mb-4">
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm p-4 text-center rounded-3 cs_blue_bg text-white">
                                <h2 class="text-white fw-bold mb-1"><?php echo count($appointments); ?></h2>
                                <p class="text-white-50 mb-0">Total Bookings</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm p-4 text-center rounded-3 bg-success text-white">
                                <h2 class="text-white fw-bold mb-1">
                                    <?php echo count(array_filter($appointments, function($a) { return $a['status'] === 'Confirmed'; })); ?>
                                </h2>
                                <p class="text-white-50 mb-0">Confirmed Appointments</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm p-4 text-center rounded-3 bg-info text-dark">
                                <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($patient['blood_group'] ?? 'O+'); ?></h2>
                                <p class="mb-0 text-dark-50">Blood Group</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Overview Table -->
                    <div class="card border-0 shadow-sm rounded-3 p-4">
                        <h5 class="border-bottom pb-2 mb-3 text-primary"><i class="fa-solid fa-calendar-check me-2"></i> Upcoming Appointments Summary</h5>
                        <?php if (count($appointments) > 0): ?>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Ref #</th>
                                            <th>Doctor</th>
                                            <th>Date & Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($appointments, 0, 3) as $apt): ?>
                                            <tr>
                                                <td class="fw-bold"><?php echo htmlspecialchars($apt['appointment_number']); ?></td>
                                                <td><?php echo htmlspecialchars($apt['doctor_name']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($apt['appointment_date'])) . ' ' . date('g:i A', strtotime($apt['appointment_time'])); ?></td>
                                                <td><span class="badge bg-success"><?php echo htmlspecialchars($apt['status']); ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No upcoming appointments recorded.</p>
                        <?php endif; ?>
                    </div>

                <?php endif; ?>

            </div>

        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
