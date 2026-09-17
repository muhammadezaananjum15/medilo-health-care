<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Doctor Portal: Availability Schedule & Patient Appointment Roster
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Ensure user is doctor or admin
require_role(['doctor', 'admin']);

$user = get_current_user_data();

// Get Doctor Record linked to user
$doctor = fetchOne("
    SELECT d.*, u.full_name, u.email, u.phone, u.address, s.name AS specialty_name, c.name AS city_name 
    FROM doctors d 
    JOIN users u ON d.user_id = u.id 
    JOIN specialties s ON d.specialty_id = s.id 
    JOIN cities c ON d.city_id = c.id 
    WHERE d.user_id = ?
", [$user['id']]);

if (!$doctor) {
    die("Doctor profile error: User account is not properly linked to a doctor record.");
}

$tab = sanitize_input($_GET['tab'] ?? 'dashboard');
$action = sanitize_input($_GET['action'] ?? '');
$apt_id = intval($_GET['apt_id'] ?? 0);
$status_val = sanitize_input($_GET['status'] ?? '');

// Handle Appointment Status Change
if ($action === 'update_status' && $apt_id > 0 && !empty($status_val)) {
    executeQuery("UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?", [$status_val, $apt_id, $doctor['id']]);
    set_flash_message('success', 'Appointment status updated to ' . $status_val);
    header('Location: ' . APP_URL . '/frontend/doctor_dashboard.php?tab=appointments');
    exit;
}

// Handle Add/Update Schedule Slot POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_schedule'])) {
    $day_of_week = sanitize_input($_POST['day_of_week'] ?? 'Monday');
    $start_time = $_POST['start_time'] ?? '09:00:00';
    $end_time = $_POST['end_time'] ?? '17:00:00';
    $slot_duration = intval($_POST['slot_duration'] ?? 30);
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    // Check if schedule for day exists
    $existing_sched = fetchOne("SELECT id FROM doctor_schedules WHERE doctor_id = ? AND day_of_week = ?", [$doctor['id'], $day_of_week]);

    if ($existing_sched) {
        executeQuery(
            "UPDATE doctor_schedules SET start_time = ?, end_time = ?, slot_duration = ?, is_available = ? WHERE id = ?",
            [$start_time, $end_time, $slot_duration, $is_available, $existing_sched['id']]
        );
    } else {
        executeQuery(
            "INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, slot_duration, is_available) VALUES (?, ?, ?, ?, ?, ?)",
            [$doctor['id'], $day_of_week, $start_time, $end_time, $slot_duration, $is_available]
        );
    }

    set_flash_message('success', 'Availability schedule updated for ' . $day_of_week);
    header('Location: ' . APP_URL . '/frontend/doctor_dashboard.php?tab=schedule');
    exit;
}

// Handle Doctor Profile Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_doctor_profile'])) {
    $qualification = sanitize_input($_POST['qualification'] ?? '');
    $experience_years = intval($_POST['experience_years'] ?? 0);
    $consultation_fee = floatval($_POST['consultation_fee'] ?? 50.00);
    $bio = sanitize_input($_POST['bio'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');

    executeQuery("UPDATE users SET phone = ?, address = ? WHERE id = ?", [$phone, $address, $user['id']]);
    executeQuery("UPDATE doctors SET qualification = ?, experience_years = ?, consultation_fee = ?, bio = ? WHERE id = ?",
        [$qualification, $experience_years, $consultation_fee, $bio, $doctor['id']]
    );

    set_flash_message('success', 'Doctor profile details updated successfully.');
    header('Location: ' . APP_URL . '/frontend/doctor_dashboard.php');
    exit;
}

// Fetch Schedules & Appointments
$schedules = fetchAll("SELECT * FROM doctor_schedules WHERE doctor_id = ? ORDER BY FIELD(day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')", [$doctor['id']]);
$appointments = fetchAll("
    SELECT a.*, u_pat.full_name AS patient_name, u_pat.phone AS patient_phone, u_pat.email AS patient_email, p.blood_group, p.medical_history 
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.id 
    JOIN users u_pat ON p.user_id = u_pat.id 
    WHERE a.doctor_id = ? 
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
", [$doctor['id']]);

$page_title = "Doctor Portal";
include_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row gy-4">
            
            <!-- Sidebar -->
            <div class="col-lg-3">
                <?php include_once __DIR__ . '/../includes/sidebar.php'; ?>
            </div>

            <!-- Content -->
            <div class="col-lg-9">

                <?php if ($tab === 'schedule'): ?>
                    <!-- Doctor Schedule Manager -->
                    <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                        <h4 class="border-bottom pb-2 mb-4 text-primary"><i class="fa-solid fa-clock me-2"></i> Manage Weekly Availability Schedule</h4>

                        <!-- Add/Edit Schedule Slot Form -->
                        <form action="<?php echo APP_URL; ?>/frontend/doctor_dashboard.php" method="POST" class="card bg-light p-3 border mb-4">
                            <input type="hidden" name="save_schedule" value="1">
                            <h6 class="fw-bold mb-3"><i class="fa-solid fa-pen-to-square me-1"></i> Add / Update Day Slot</h6>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Day of Week</label>
                                    <select class="form-select" name="day_of_week" required>
                                        <option value="Monday">Monday</option>
                                        <option value="Tuesday">Tuesday</option>
                                        <option value="Wednesday">Wednesday</option>
                                        <option value="Thursday">Thursday</option>
                                        <option value="Friday">Friday</option>
                                        <option value="Saturday">Saturday</option>
                                        <option value="Sunday">Sunday</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Start Time</label>
                                    <input type="time" class="form-control" name="start_time" required value="09:00">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">End Time</label>
                                    <input type="time" class="form-control" name="end_time" required value="17:00">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Slot Duration</label>
                                    <select class="form-select" name="slot_duration">
                                        <option value="15">15 Mins</option>
                                        <option value="30" selected>30 Mins</option>
                                        <option value="45">45 Mins</option>
                                        <option value="60">60 Mins</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_available" id="is_available" value="1" checked>
                                    <label class="form-check-label fw-bold" for="is_available">Available for Bookings</label>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm px-4">
                                    Save Schedule Slot <i class="fa-solid fa-save ms-1"></i>
                                </button>
                            </div>
                        </form>

                        <!-- Existing Schedule List -->
                        <h6 class="fw-bold mb-3">Current Active Availability Roster</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Day</th>
                                        <th>Shift Timing</th>
                                        <th>Slot Size</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($schedules as $sch): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($sch['day_of_week']); ?></td>
                                            <td><?php echo date('g:i A', strtotime($sch['start_time'])) . ' - ' . date('g:i A', strtotime($sch['end_time'])); ?></td>
                                            <td><?php echo $sch['slot_duration']; ?> mins</td>
                                            <td>
                                                <?php if ($sch['is_available']): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Unavailable</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php elseif ($tab === 'appointments'): ?>
                    <!-- Patient Appointment Management -->
                    <div class="card border-0 shadow-sm rounded-3 p-4">
                        <h4 class="border-bottom pb-2 mb-4 text-primary"><i class="fa-solid fa-calendar-check me-2"></i> Booked Patient Appointments</h4>

                        <?php if (count($appointments) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ref #</th>
                                            <th>Patient Name</th>
                                            <th>Date & Time</th>
                                            <th>Reason</th>
                                            <th>Status</th>
                                            <th>Change Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($appointments as $apt): ?>
                                            <tr>
                                                <td class="fw-bold text-primary"><?php echo htmlspecialchars($apt['appointment_number']); ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($apt['patient_name']); ?></strong><br>
                                                    <small class="text-muted"><i class="fa-solid fa-phone me-1"></i><?php echo htmlspecialchars($apt['patient_phone']); ?></small>
                                                </td>
                                                <td>
                                                    <?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?><br>
                                                    <small class="text-muted"><i class="fa-solid fa-clock me-1"></i><?php echo date('g:i A', strtotime($apt['appointment_time'])); ?></small>
                                                </td>
                                                <td><small class="text-muted"><?php echo htmlspecialchars($apt['reason'] ?? 'Routine consultation'); ?></small></td>
                                                <td>
                                                    <?php if ($apt['status'] === 'Confirmed'): ?>
                                                        <span class="badge bg-success">Confirmed</span>
                                                    <?php elseif ($apt['status'] === 'Completed'): ?>
                                                        <span class="badge bg-info text-dark">Completed</span>
                                                    <?php elseif ($apt['status'] === 'Cancelled'): ?>
                                                        <span class="badge bg-danger">Cancelled</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="<?php echo APP_URL; ?>/frontend/doctor_dashboard.php?action=update_status&apt_id=<?php echo $apt['id']; ?>&status=Confirmed" class="btn btn-outline-success">Confirm</a>
                                                        <a href="<?php echo APP_URL; ?>/frontend/doctor_dashboard.php?action=update_status&apt_id=<?php echo $apt['id']; ?>&status=Completed" class="btn btn-outline-info">Complete</a>
                                                        <a href="<?php echo APP_URL; ?>/frontend/doctor_dashboard.php?action=update_status&apt_id=<?php echo $apt['id']; ?>&status=Cancelled" class="btn btn-outline-danger">Cancel</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted my-3">No patient bookings recorded yet.</p>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <!-- Main Dashboard Overview & Profile Edit -->
                    <div class="row gy-4 mb-4">
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm p-4 text-center rounded-3 cs_blue_bg text-white">
                                <h2 class="text-white fw-bold mb-1"><?php echo count($appointments); ?></h2>
                                <p class="text-white-50 mb-0">Total Patients</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm p-4 text-center rounded-3 bg-success text-white">
                                <h2 class="text-white fw-bold mb-1">
                                    $<?php echo number_format(array_sum(array_column($appointments, 'fee')), 2); ?>
                                </h2>
                                <p class="text-white-50 mb-0">Total Earnings</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm p-4 text-center rounded-3 bg-info text-dark">
                                <h2 class="fw-bold mb-1"><?php echo count($schedules); ?> Days</h2>
                                <p class="mb-0 text-dark-50">Active Shifts</p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Doctor Profile Form -->
                    <div class="card border-0 shadow-sm rounded-3 p-4">
                        <h5 class="border-bottom pb-2 mb-4 text-primary"><i class="fa-solid fa-user-doctor me-2"></i> Update Doctor Profile Details</h5>
                        <form action="<?php echo APP_URL; ?>/frontend/doctor_dashboard.php" method="POST">
                            <input type="hidden" name="update_doctor_profile" value="1">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Doctor Name</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($doctor['full_name']); ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Specialty</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($doctor['specialty_name']); ?>" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Qualification & Degrees</label>
                                    <input type="text" class="form-control" name="qualification" required value="<?php echo htmlspecialchars($doctor['qualification']); ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Experience (Years)</label>
                                    <input type="number" class="form-control" name="experience_years" min="0" value="<?php echo $doctor['experience_years']; ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Consultation Fee ($)</label>
                                    <input type="number" step="0.01" class="form-control" name="consultation_fee" required value="<?php echo $doctor['consultation_fee']; ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Phone Number</label>
                                    <input type="text" class="form-control" name="phone" required value="<?php echo htmlspecialchars($doctor['phone']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Clinic Address</label>
                                    <input type="text" class="form-control" name="address" required value="<?php echo htmlspecialchars($doctor['address']); ?>">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Biography & Special Interests</label>
                                <textarea class="form-control" name="bio" rows="4"><?php echo htmlspecialchars($doctor['bio']); ?></textarea>
                            </div>

                            <button type="submit" class="cs_btn cs_style_1 cs_color_1 py-3 px-4">
                                <span>Save Doctor Details</span> <i class="fa-solid fa-floppy-disk ms-1"></i>
                            </button>
                        </form>
                    </div>

                <?php endif; ?>

            </div>

        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
