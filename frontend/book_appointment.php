<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Interactive Appointment Booking System with Real-Time Slot Validation & Payment Gateway
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Force patient login to complete appointment booking
require_login();

$user = get_current_user_data();

// Fetch patient record linked to logged in user
$patient = fetchOne("SELECT * FROM patients WHERE user_id = ?", [$user['id']]);

// If patient profile doesn't exist yet, create default
if (!$patient) {
    $patient_id = executeQuery("INSERT INTO patients (user_id) VALUES (?)", [$user['id']]);
    $patient = fetchOne("SELECT * FROM patients WHERE id = ?", [$patient_id]);
}

$selected_doctor_id = intval($_GET['doctor_id'] ?? 0);
$doctors = fetchAll("
    SELECT d.*, u.full_name, s.name AS specialty_name, c.name AS city_name 
    FROM doctors d 
    JOIN users u ON d.user_id = u.id 
    JOIN specialties s ON d.specialty_id = s.id 
    JOIN cities c ON d.city_id = c.id 
    ORDER BY u.full_name ASC
");

$selected_doctor = null;
if ($selected_doctor_id > 0) {
    $selected_doctor = fetchOne("
        SELECT d.*, u.full_name, s.name AS specialty_name, c.name AS city_name 
        FROM doctors d 
        JOIN users u ON d.user_id = u.id 
        JOIN specialties s ON d.specialty_id = s.id 
        JOIN cities c ON d.city_id = c.id 
        WHERE d.id = ?
    ", [$selected_doctor_id]);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = intval($_POST['doctor_id'] ?? 0);
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_time = $_POST['appointment_time'] ?? '';
    $reason = sanitize_input($_POST['reason'] ?? '');
    $payment_method = sanitize_input($_POST['payment_method'] ?? 'Stripe Card');

    // Patient mandatory fields validation
    $patient_name = sanitize_input($_POST['patient_name'] ?? '');
    $patient_phone = sanitize_input($_POST['patient_phone'] ?? '');
    $patient_email = sanitize_input($_POST['patient_email'] ?? '');
    $patient_address = sanitize_input($_POST['patient_address'] ?? '');

    if (empty($doctor_id) || empty($appointment_date) || empty($appointment_time) || empty($patient_name) || empty($patient_phone) || empty($patient_email) || empty($patient_address)) {
        $error = 'Please fill out all mandatory patient contact details, appointment date, and time slot.';
    } else {
        $doctor = fetchOne("SELECT * FROM doctors WHERE id = ?", [$doctor_id]);
        if (!$doctor) {
            $error = 'Selected doctor does not exist.';
        } else {
            // Check day of week matching schedule
            $day_of_week = date('l', strtotime($appointment_date));
            $schedule = fetchOne("SELECT * FROM doctor_schedules WHERE doctor_id = ? AND day_of_week = ? AND is_available = 1", [$doctor_id, $day_of_week]);

            if (!$schedule) {
                $error = 'Selected doctor is not available on ' . $day_of_week . 's. Please choose another date.';
            } else {
                // Check double booking for exact slot
                $existing = fetchOne(
                    "SELECT id FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'Cancelled'",
                    [$doctor_id, $appointment_date, $appointment_time]
                );

                if ($existing) {
                    $error = 'The requested time slot (' . date("g:i A", strtotime($appointment_time)) . ') on ' . $appointment_date . ' is already booked. Please choose a different time slot.';
                } else {
                    try {
                        $db = getDB();
                        $db->beginTransaction();

                        // Update patient info if needed
                        executeQuery(
                            "UPDATE users SET full_name = ?, phone = ?, address = ?, email = ? WHERE id = ?",
                            [$patient_name, $patient_phone, $patient_address, $patient_email, $user['id']]
                        );

                        // Generate Unique Appointment Number
                        $appointment_number = 'MED-' . date('Y') . '-' . rand(10000, 99999);
                        $fee = $doctor['consultation_fee'];
                        $transaction_id = 'TXN-' . strtoupper(substr(md5(uniqid()), 0, 10));

                        // Insert Appointment
                        $appointment_id = executeQuery(
                            "INSERT INTO appointments (appointment_number, patient_id, doctor_id, appointment_date, appointment_time, reason, fee, status, payment_status, payment_method, transaction_id) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, 'Confirmed', 'Paid', ?, ?)",
                            [$appointment_number, $patient['id'], $doctor_id, $appointment_date, $appointment_time, $reason, $fee, $payment_method, $transaction_id]
                        );

                        // Insert Payment Log
                        executeQuery(
                            "INSERT INTO payments (appointment_id, amount, payment_method, transaction_id, status) VALUES (?, ?, ?, ?, 'Success')",
                            [$appointment_id, $fee, $payment_method, $transaction_id]
                        );

                        // Insert Notification for Patient
                        executeQuery(
                            "INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)",
                            [$user['id'], 'Appointment Confirmed', 'Your appointment ' . $appointment_number . ' for ' . $appointment_date . ' at ' . date("g:i A", strtotime($appointment_time)) . ' is confirmed. Transaction ID: ' . $transaction_id]
                        );

                        $db->commit();
                        set_flash_message('success', 'Appointment successfully booked and paid! Booking Reference: ' . $appointment_number);
                        header('Location: ' . APP_URL . '/frontend/appointment_confirmation.php?ref=' . urlencode($appointment_number));
                        exit;

                    } catch (Exception $e) {
                        if (isset($db)) $db->rollBack();
                        $error = 'Booking Error: ' . $e->getMessage();
                    }
                }
            }
        }
    }
}

$page_title = "Book Appointment";
include_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5 bg-light">
    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="card border-0 shadow-lg rounded-3">
                    <div class="card-header cs_blue_bg text-white text-center py-4 rounded-top">
                        <h3 class="text-white mb-1"><i class="fa-solid fa-calendar-check me-2"></i> Medical Appointment Booking</h3>
                        <p class="mb-0 text-white-50">Select your doctor, pick an available slot, and confirm payment</p>
                    </div>
                    <div class="card-body p-4 p-md-5">

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo APP_URL; ?>/frontend/book_appointment.php" method="POST" id="bookingForm">

                            <!-- Step 1: Doctor Selection -->
                            <h5 class="border-bottom pb-2 mb-3 text-primary"><i class="fa-solid fa-user-doctor me-2"></i> 1. Select Doctor & Specialty</h5>
                            <div class="mb-4">
                                <label for="doctor_id" class="form-label fw-bold">Choose Doctor <span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg" id="doctor_id" name="doctor_id" required onchange="updateDoctorFee(this)">
                                    <option value="">-- Select a Doctor --</option>
                                    <?php foreach ($doctors as $doc): ?>
                                        <option value="<?php echo $doc['id']; ?>" data-fee="<?php echo $doc['consultation_fee']; ?>" <?php echo ($selected_doctor_id == $doc['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($doc['full_name'] . ' (' . $doc['specialty_name'] . ' - ' . $doc['city_name'] . ') - $' . number_format($doc['consultation_fee'], 2)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Step 2: Patient Mandatory Details -->
                            <h5 class="border-bottom pb-2 mb-3 text-primary"><i class="fa-solid fa-address-card me-2"></i> 2. Patient Mandatory Contact Details</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="patient_name" class="form-label fw-bold">Patient Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="patient_name" name="patient_name" required value="<?php echo htmlspecialchars($user['full_name']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="patient_phone" class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="patient_phone" name="patient_phone" required value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="patient_email" class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="patient_email" name="patient_email" required value="<?php echo htmlspecialchars($user['email']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="patient_address" class="form-label fw-bold">Residential Address <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="patient_address" name="patient_address" required value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                                </div>
                            </div>

                            <!-- Step 3: Date & Slot Selection -->
                            <h5 class="border-bottom pb-2 mb-3 text-primary"><i class="fa-solid fa-clock me-2"></i> 3. Select Appointment Date & Time Slot</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="appointment_date" class="form-label fw-bold">Appointment Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="appointment_date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="appointment_time" class="form-label fw-bold">Available Time Slot <span class="text-danger">*</span></label>
                                    <select class="form-select" id="appointment_time" name="appointment_time" required>
                                        <option value="09:00:00">09:00 AM</option>
                                        <option value="09:30:00">09:30 AM</option>
                                        <option value="10:00:00">10:00 AM</option>
                                        <option value="10:30:00">10:30 AM</option>
                                        <option value="11:00:00">11:00 AM</option>
                                        <option value="11:30:00">11:30 AM</option>
                                        <option value="14:00:00">02:00 PM</option>
                                        <option value="14:30:00">02:30 PM</option>
                                        <option value="15:00:00">03:00 PM</option>
                                        <option value="15:30:00">03:30 PM</option>
                                        <option value="16:00:00">04:00 PM</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="reason" class="form-label fw-bold">Reason for Visit / Symptoms</label>
                                <textarea class="form-control" id="reason" name="reason" rows="2" placeholder="Brief description of symptoms or consultation goal"></textarea>
                            </div>

                            <!-- Step 4: Payment Gateway Integration -->
                            <h5 class="border-bottom pb-2 mb-3 text-primary"><i class="fa-solid fa-credit-card me-2"></i> 4. Consultation Fee Payment Gateway</h5>
                            <div class="card bg-light border p-4 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fs-5 fw-bold text-dark">Consultation Fee Payable:</span>
                                    <span class="fs-3 fw-bold text-success" id="display_fee">$<?php echo number_format($selected_doctor['consultation_fee'] ?? 100.00, 2); ?></span>
                                </div>

                                <label class="form-label fw-bold mb-2">Choose Payment Gateway Method <span class="text-danger">*</span></label>
                                <div class="row gy-2">
                                    <div class="col-md-6">
                                        <div class="form-check card p-3 border shadow-sm">
                                            <input class="form-check-input" type="radio" name="payment_method" id="pay_stripe" value="Stripe Card" checked>
                                            <label class="form-check-label fw-bold d-flex align-items-center" for="pay_stripe">
                                                <i class="fa-brands fa-stripe text-primary fs-3 me-2"></i> Stripe Credit / Debit Card
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check card p-3 border shadow-sm">
                                            <input class="form-check-input" type="radio" name="payment_method" id="pay_paypal" value="PayPal" >
                                            <label class="form-check-label fw-bold d-flex align-items-center" for="pay_paypal">
                                                <i class="fa-brands fa-paypal text-info fs-3 me-2"></i> PayPal Wallet
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Simulated Card Input Details -->
                                <div class="mt-3 pt-3 border-top" id="card_details_box">
                                    <label class="form-label small fw-bold text-muted">Test Card Number</label>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text bg-white"><i class="fa-solid fa-credit-card text-muted"></i></span>
                                        <input type="text" class="form-control" placeholder="4242 •••• •••• 4242 (Stripe Test Card)" value="4242 4242 4242 4242" readonly>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <input type="text" class="form-control form-control-sm" placeholder="MM/YY" value="12/28" readonly>
                                        </div>
                                        <div class="col-6">
                                            <input type="text" class="form-control form-control-sm" placeholder="CVC" value="123" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="cs_btn cs_style_1 cs_color_1 w-100 py-3">
                                <span>Pay & Confirm Appointment</span> <i class="fa-solid fa-lock ms-2"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function updateDoctorFee(select) {
    const option = select.options[select.selectedIndex];
    const fee = option.getAttribute('data-fee') || '100.00';
    document.getElementById('display_fee').innerText = '$' + parseFloat(fee).toFixed(2);
}
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
