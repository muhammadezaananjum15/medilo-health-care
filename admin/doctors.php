<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Admin Doctors Management Module (Full CRUD & Account Creation)
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Admin Access Control
require_role('admin');

$action = sanitize_input($_GET['action'] ?? '');
$doc_id = intval($_GET['id'] ?? 0);

// Fetch Cities and Specialties
$cities = fetchAll("SELECT * FROM cities ORDER BY name ASC");
$specialties = fetchAll("SELECT * FROM specialties ORDER BY name ASC");

// Handle Delete Doctor Record
if ($action === 'delete' && $doc_id > 0) {
    $doctor = fetchOne("SELECT user_id FROM doctors WHERE id = ?", [$doc_id]);
    if ($doctor) {
        executeQuery("DELETE FROM users WHERE id = ?", [$doctor['user_id']]);
    }
    set_flash_message('success', 'Doctor record and user login deleted successfully.');
    header('Location: ' . APP_URL . '/admin/doctors.php');
    exit;
}

// Handle Add / Edit Doctor POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $specialty_id = intval($_POST['specialty_id'] ?? 1);
    $city_id = intval($_POST['city_id'] ?? 1);
    $qualification = sanitize_input($_POST['qualification'] ?? 'MD');
    $experience_years = intval($_POST['experience_years'] ?? 5);
    $consultation_fee = floatval($_POST['consultation_fee'] ?? 100.00);
    $bio = sanitize_input($_POST['bio'] ?? '');
    $edit_doc_id = intval($_POST['doctor_id'] ?? 0);

    if (empty($full_name) || empty($email) || empty($phone) || empty($address)) {
        set_flash_message('danger', 'Name, Email, Phone, and Address are required mandatory fields.');
    } else {
        try {
            $db = getDB();
            $db->beginTransaction();

            if ($edit_doc_id > 0) {
                // Update Doctor
                $doc = fetchOne("SELECT user_id FROM doctors WHERE id = ?", [$edit_doc_id]);
                executeQuery("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?", [$full_name, $phone, $address, $doc['user_id']]);
                
                if (!empty($password)) {
                    $pass_hash = password_hash($password, PASSWORD_BCRYPT);
                    executeQuery("UPDATE users SET password = ? WHERE id = ?", [$pass_hash, $doc['user_id']]);
                }

                executeQuery("UPDATE doctors SET specialty_id = ?, city_id = ?, qualification = ?, experience_years = ?, consultation_fee = ?, bio = ? WHERE id = ?",
                    [$specialty_id, $city_id, $qualification, $experience_years, $consultation_fee, $bio, $edit_doc_id]
                );

                set_flash_message('success', 'Doctor record updated successfully.');
            } else {
                // Add New Doctor Account
                if (empty($username) || empty($password)) {
                    throw new Exception("Username and Password are required for new doctor account creation.");
                }

                $pass_hash = password_hash($password, PASSWORD_BCRYPT);
                $user_id = executeQuery(
                    "INSERT INTO users (username, password, email, full_name, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, 'doctor')",
                    [$username, $pass_hash, $email, $full_name, $phone, $address]
                );

                $new_doc_id = executeQuery(
                    "INSERT INTO doctors (user_id, specialty_id, city_id, qualification, experience_years, consultation_fee, bio) VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$user_id, $specialty_id, $city_id, $qualification, $experience_years, $consultation_fee, $bio]
                );

                // Auto-create default weekly schedule (Mon-Fri 09:00 - 17:00)
                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                foreach ($days as $d) {
                    executeQuery("INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, slot_duration, is_available) VALUES (?, ?, '09:00:00', '17:00:00', 30, 1)", [$new_doc_id, $d]);
                }

                set_flash_message('success', 'New doctor profile and login created successfully.');
            }

            $db->commit();
            header('Location: ' . APP_URL . '/admin/doctors.php');
            exit;

        } catch (Exception $e) {
            if (isset($db)) $db->rollBack();
            set_flash_message('danger', 'Error: ' . $e->getMessage());
        }
    }
}

// Fetch doctor record for editing if requested
$edit_doctor = null;
if ($action === 'edit' && $doc_id > 0) {
    $edit_doctor = fetchOne("SELECT d.*, u.full_name, u.email, u.phone, u.address, u.username FROM doctors d JOIN users u ON d.user_id = u.id WHERE d.id = ?", [$doc_id]);
}

// Fetch all doctors list
$doctors = fetchAll("
    SELECT d.*, u.full_name, u.email, u.phone, s.name AS specialty_name, c.name AS city_name 
    FROM doctors d 
    JOIN users u ON d.user_id = u.id 
    JOIN specialties s ON d.specialty_id = s.id 
    JOIN cities c ON d.city_id = c.id 
    ORDER BY d.id DESC
");

$page_title = "Doctor Management";
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
                    <h4 class="border-bottom pb-2 mb-4 text-primary d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-user-doctor me-2"></i> Doctor Management Module (CRUD)</span>
                    </h4>

                    <!-- Add / Edit Form -->
                    <form action="<?php echo APP_URL; ?>/admin/doctors.php" method="POST" class="card bg-light p-4 border mb-4">
                        <input type="hidden" name="doctor_id" value="<?php echo $edit_doctor['id'] ?? 0; ?>">
                        <h6 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-user-plus me-1"></i> <?php echo $edit_doctor ? 'Edit Doctor Profile & Credentials' : 'Add New Doctor Account'; ?></h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="full_name" required placeholder="Dr. First Last" value="<?php echo htmlspecialchars($edit_doctor['full_name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" required placeholder="doctor@example.com" value="<?php echo htmlspecialchars($edit_doctor['email'] ?? ''); ?>" <?php echo $edit_doctor ? 'readonly' : ''; ?>>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="phone" required placeholder="+1 (555) 000-0000" value="<?php echo htmlspecialchars($edit_doctor['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Street / Clinic Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="address" required placeholder="Clinic full address" value="<?php echo htmlspecialchars($edit_doctor['address'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Username / USERID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="username" placeholder="dr_username" value="<?php echo htmlspecialchars($edit_doctor['username'] ?? ''); ?>" <?php echo $edit_doctor ? 'readonly' : 'required'; ?>>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Password <?php echo $edit_doctor ? '<small class="text-muted">(Leave blank to keep current)</small>' : '<span class="text-danger">*</span>'; ?></label>
                                <input type="password" class="form-control" name="password" <?php echo $edit_doctor ? '' : 'required'; ?> placeholder="Account password">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Specialty <span class="text-danger">*</span></label>
                                <select class="form-select" name="specialty_id" required>
                                    <?php foreach ($specialties as $spec): ?>
                                        <option value="<?php echo $spec['id']; ?>" <?php echo (isset($edit_doctor['specialty_id']) && $edit_doctor['specialty_id'] == $spec['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($spec['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Practice City <span class="text-danger">*</span></label>
                                <select class="form-select" name="city_id" required>
                                    <?php foreach ($cities as $city): ?>
                                        <option value="<?php echo $city['id']; ?>" <?php echo (isset($edit_doctor['city_id']) && $edit_doctor['city_id'] == $city['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($city['name'] . ', ' . $city['state']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Qualification & Degrees <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="qualification" required placeholder="MD - Cardiology" value="<?php echo htmlspecialchars($edit_doctor['qualification'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Experience (Yrs)</label>
                                <input type="number" class="form-control" name="experience_years" min="0" value="<?php echo $edit_doctor['experience_years'] ?? 5; ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Fee ($) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="consultation_fee" required value="<?php echo $edit_doctor['consultation_fee'] ?? 100.00; ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Doctor Biography</label>
                            <textarea class="form-control" name="bio" rows="2"><?php echo htmlspecialchars($edit_doctor['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <?php if ($edit_doctor): ?>
                                <a href="<?php echo APP_URL; ?>/admin/doctors.php" class="btn btn-outline-secondary btn-sm">Cancel Edit</a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary btn-sm px-4">
                                <?php echo $edit_doctor ? 'Update Doctor Profile' : 'Create Doctor Account'; ?> <i class="fa-solid fa-save ms-1"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Doctors Roster List -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Doctor Name</th>
                                    <th>Specialty & City</th>
                                    <th>Contact</th>
                                    <th>Fee</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($doctors as $doc): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-primary"><?php echo htmlspecialchars($doc['full_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($doc['qualification']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark"><?php echo htmlspecialchars($doc['specialty_name']); ?></span><br>
                                            <small class="text-muted"><i class="fa-solid fa-location-dot me-1"></i><?php echo htmlspecialchars($doc['city_name']); ?></small>
                                        </td>
                                        <td>
                                            <small class="d-block"><i class="fa-solid fa-envelope me-1"></i><?php echo htmlspecialchars($doc['email']); ?></small>
                                            <small class="d-block"><i class="fa-solid fa-phone me-1"></i><?php echo htmlspecialchars($doc['phone']); ?></small>
                                        </td>
                                        <td class="fw-bold text-success">$<?php echo number_format($doc['consultation_fee'], 2); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo APP_URL; ?>/admin/doctors.php?action=edit&id=<?php echo $doc['id']; ?>" class="btn btn-outline-primary" title="Edit"><i class="fa-solid fa-pen"></i></a>
                                                <a href="<?php echo APP_URL; ?>/admin/doctors.php?action=delete&id=<?php echo $doc['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Deleting doctor will remove login access. Continue?');" title="Delete"><i class="fa-solid fa-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
