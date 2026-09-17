<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Account Registration Endpoint
 */

require_once __DIR__ . '/../config/db.php';

// Fetch Cities and Specialties for registration dropdowns
$cities = fetchAll("SELECT * FROM cities ORDER BY name ASC");
$specialties = fetchAll("SELECT * FROM specialties ORDER BY name ASC");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Shared Mandatory Fields
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = sanitize_input($_POST['role'] ?? 'patient');

    // Validation
    if (empty($full_name) || empty($username) || empty($email) || empty($phone) || empty($address) || empty($password)) {
        $error = 'All fields marked with * (Name, Username, Email, Phone, Address, Password) are strictly required.';
    } elseif ($password !== $confirm_password) {
        $error = 'Password confirmation does not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Check if username or email already exists
        $existing_user = fetchOne("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
        if ($existing_user) {
            $error = 'Username or email address is already registered. Please choose another or login.';
        } else {
            try {
                $db = getDB();
                $db->beginTransaction();

                // Hash password securely
                $password_hash = password_hash($password, PASSWORD_BCRYPT);

                // Insert into users table
                $user_id = executeQuery(
                    "INSERT INTO users (username, password, email, full_name, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$username, $password_hash, $email, $full_name, $phone, $address, $role]
                );

                if ($role === 'patient') {
                    $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
                    $gender = sanitize_input($_POST['gender'] ?? 'Male');
                    $blood_group = sanitize_input($_POST['blood_group'] ?? 'O+');
                    $emergency_contact = sanitize_input($_POST['emergency_contact'] ?? $phone);
                    $medical_history = sanitize_input($_POST['medical_history'] ?? '');

                    executeQuery(
                        "INSERT INTO patients (user_id, dob, gender, blood_group, emergency_contact, medical_history) VALUES (?, ?, ?, ?, ?, ?)",
                        [$user_id, $dob, $gender, $blood_group, $emergency_contact, $medical_history]
                    );

                } elseif ($role === 'doctor') {
                    $specialty_id = intval($_POST['specialty_id'] ?? 1);
                    $city_id = intval($_POST['city_id'] ?? 1);
                    $qualification = sanitize_input($_POST['qualification'] ?? 'MD');
                    $experience_years = intval($_POST['experience_years'] ?? 1);
                    $consultation_fee = floatval($_POST['consultation_fee'] ?? 50.00);
                    $bio = sanitize_input($_POST['bio'] ?? '');

                    $doctor_id = executeQuery(
                        "INSERT INTO doctors (user_id, specialty_id, city_id, qualification, experience_years, consultation_fee, bio) VALUES (?, ?, ?, ?, ?, ?, ?)",
                        [$user_id, $specialty_id, $city_id, $qualification, $experience_years, $consultation_fee, $bio]
                    );

                    // Insert Default Weekly Schedule for Doctor (Mon-Fri 09:00 - 17:00)
                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                    foreach ($days as $day) {
                        executeQuery(
                            "INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, slot_duration, is_available) VALUES (?, ?, '09:00:00', '17:00:00', 30, 1)",
                            [$doctor_id, $day]
                        );
                    }
                }

                $db->commit();
                set_flash_message('success', 'Registration successful! Please login with your credentials.');
                header('Location: ' . APP_URL . '/auth/login.php');
                exit;

            } catch (Exception $e) {
                if (isset($db)) $db->rollBack();
                $error = 'Registration Error: ' . $e->getMessage();
            }
        }
    }
}

$page_title = "Account Registration";
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Register Section -->
<section class="py-5 bg-light">
    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-lg rounded-3">
                    <div class="card-header cs_blue_bg text-white text-center py-4 rounded-top">
                        <h3 class="text-white mb-1"><i class="fa-solid fa-user-plus me-2"></i> Create Medilo Account</h3>
                        <p class="mb-0 text-white-50">Join as a Patient or Doctor to manage your healthcare</p>
                    </div>
                    <div class="card-body p-4 p-md-5">

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo APP_URL; ?>/auth/register.php" method="POST" id="registerForm">
                            
                            <!-- Role Selection -->
                            <div class="mb-4 text-center">
                                <label class="form-label fw-bold d-block">Select Account Role <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="role" id="role_patient" value="patient" checked onchange="toggleRoleFields()">
                                    <label class="btn btn-outline-primary py-3 fw-bold" for="role_patient">
                                        <i class="fa-solid fa-hospital-user me-2 fs-5"></i> Patient Account
                                    </label>

                                    <input type="radio" class="btn-check" name="role" id="role_doctor" value="doctor" onchange="toggleRoleFields()">
                                    <label class="btn btn-outline-info py-3 fw-bold" for="role_doctor">
                                        <i class="fa-solid fa-user-doctor me-2 fs-5"></i> Doctor Account
                                    </label>
                                </div>
                            </div>

                            <h5 class="border-bottom pb-2 mb-3 text-primary"><i class="fa-solid fa-address-card me-2"></i> Personal & Mandatory Information</h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" required placeholder="e.g. John Doe" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label fw-bold">User ID / Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="username" name="username" required placeholder="Unique USERID" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label fw-bold">Email ID <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" required placeholder="name@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="phone" name="phone" required placeholder="+1 (555) 000-0000" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label fw-bold">Complete Street Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="address" name="address" rows="2" required placeholder="Enter residential/clinic address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password" name="password" required placeholder="At least 6 characters">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label fw-bold">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Re-type password">
                                </div>
                            </div>

                            <!-- Patient Specific Fields -->
                            <div id="patient_fields">
                                <h5 class="border-bottom pb-2 mb-3 mt-3 text-primary"><i class="fa-solid fa-notes-medical me-2"></i> Medical Details</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="dob" class="form-label fw-bold">Date of Birth</label>
                                        <input type="date" class="form-control" id="dob" name="dob">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="gender" class="form-label fw-bold">Gender</label>
                                        <select class="form-select" id="gender" name="gender">
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="blood_group" class="form-label fw-bold">Blood Group</label>
                                        <select class="form-select" id="blood_group" name="blood_group">
                                            <option value="O+">O+</option>
                                            <option value="O-">O-</option>
                                            <option value="A+">A+</option>
                                            <option value="A-">A-</option>
                                            <option value="B+">B+</option>
                                            <option value="B-">B-</option>
                                            <option value="AB+">AB+</option>
                                            <option value="AB-">AB-</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="emergency_contact" class="form-label fw-bold">Emergency Contact Phone</label>
                                    <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" placeholder="Emergency contact person number">
                                </div>
                                <div class="mb-3">
                                    <label for="medical_history" class="form-label fw-bold">Existing Medical History / Allergies</label>
                                    <textarea class="form-control" id="medical_history" name="medical_history" rows="2" placeholder="Mention any chronic conditions or allergies"></textarea>
                                </div>
                            </div>

                            <!-- Doctor Specific Fields -->
                            <div id="doctor_fields" style="display: none;">
                                <h5 class="border-bottom pb-2 mb-3 mt-3 text-info"><i class="fa-solid fa-user-doctor me-2"></i> Professional Doctor Profile</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="specialty_id" class="form-label fw-bold">Specialty <span class="text-danger">*</span></label>
                                        <select class="form-select" id="specialty_id" name="specialty_id">
                                            <?php foreach ($specialties as $spec): ?>
                                                <option value="<?php echo $spec['id']; ?>"><?php echo htmlspecialchars($spec['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="city_id" class="form-label fw-bold">Practice City <span class="text-danger">*</span></label>
                                        <select class="form-select" id="city_id" name="city_id">
                                            <?php foreach ($cities as $city): ?>
                                                <option value="<?php echo $city['id']; ?>"><?php echo htmlspecialchars($city['name'] . ', ' . $city['state']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="qualification" class="form-label fw-bold">Qualification & Degrees <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="qualification" name="qualification" placeholder="e.g. MD - Cardiology, MBBS">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="experience_years" class="form-label fw-bold">Experience (Years)</label>
                                        <input type="number" class="form-control" id="experience_years" name="experience_years" min="0" value="5">
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="consultation_fee" class="form-label fw-bold">Fee ($) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" id="consultation_fee" name="consultation_fee" min="0" value="100.00">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="bio" class="form-label fw-bold">Doctor Biography</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="Short biography and special interests"></textarea>
                                </div>
                            </div>

                            <button type="submit" class="cs_btn cs_style_1 cs_color_1 w-100 py-3 mt-4">
                                <span>Complete Registration</span> <i class="fa-solid fa-circle-check ms-1"></i>
                            </button>

                            <div class="text-center mt-3">
                                <p class="text-muted mb-0">Already registered? <a href="<?php echo APP_URL; ?>/auth/login.php" class="fw-bold text-primary text-decoration-none">Login to your account</a></p>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function toggleRoleFields() {
    const roleDoctor = document.getElementById('role_doctor').checked;
    const patientFields = document.getElementById('patient_fields');
    const doctorFields = document.getElementById('doctor_fields');

    if (roleDoctor) {
        patientFields.style.display = 'none';
        doctorFields.style.display = 'block';
    } else {
        patientFields.style.display = 'block';
        doctorFields.style.display = 'none';
    }
}
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
