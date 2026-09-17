<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Admin Patients Management Module (Modify and Delete Patient Records)
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Admin Access Control
require_role('admin');

$action = sanitize_input($_GET['action'] ?? '');
$pat_id = intval($_GET['id'] ?? 0);

// Handle Delete Patient Record
if ($action === 'delete' && $pat_id > 0) {
    $patient = fetchOne("SELECT user_id FROM patients WHERE id = ?", [$pat_id]);
    if ($patient) {
        executeQuery("DELETE FROM users WHERE id = ?", [$patient['user_id']]);
    }
    set_flash_message('success', 'Patient record and user account deleted successfully.');
    header('Location: ' . APP_URL . '/admin/patients.php');
    exit;
}

// Handle Update Patient POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_pat_id = intval($_POST['patient_id'] ?? 0);
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $gender = sanitize_input($_POST['gender'] ?? 'Male');
    $blood_group = sanitize_input($_POST['blood_group'] ?? 'O+');
    $emergency_contact = sanitize_input($_POST['emergency_contact'] ?? '');
    $medical_history = sanitize_input($_POST['medical_history'] ?? '');
    $vaccination_records = sanitize_input($_POST['vaccination_records'] ?? '');

    if ($edit_pat_id > 0) {
        $pat = fetchOne("SELECT user_id FROM patients WHERE id = ?", [$edit_pat_id]);
        executeQuery("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?", [$full_name, $phone, $address, $pat['user_id']]);
        executeQuery("UPDATE patients SET dob = ?, gender = ?, blood_group = ?, emergency_contact = ?, medical_history = ?, vaccination_records = ? WHERE id = ?",
            [$dob, $gender, $blood_group, $emergency_contact, $medical_history, $vaccination_records, $edit_pat_id]
        );
        set_flash_message('success', 'Patient record updated successfully.');
    }
    header('Location: ' . APP_URL . '/admin/patients.php');
    exit;
}

// Fetch record for editing
$edit_patient = null;
if ($action === 'edit' && $pat_id > 0) {
    $edit_patient = fetchOne("SELECT p.*, u.full_name, u.email, u.phone, u.address, u.username FROM patients p JOIN users u ON p.user_id = u.id WHERE p.id = ?", [$pat_id]);
}

// Fetch all patients list
$patients = fetchAll("
    SELECT p.*, u.full_name, u.email, u.phone, u.address, u.username, COUNT(a.id) AS appointment_count 
    FROM patients p 
    JOIN users u ON p.user_id = u.id 
    LEFT JOIN appointments a ON p.id = a.patient_id 
    GROUP BY p.id ORDER BY p.id DESC
");

$page_title = "Manage Patient Records";
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
                    <h4 class="border-bottom pb-2 mb-4 text-primary"><i class="fa-solid fa-hospital-user me-2"></i> Patient Records Management (Modify & Delete)</h4>

                    <?php if ($edit_patient): ?>
                        <!-- Edit Patient Form -->
                        <form action="<?php echo APP_URL; ?>/admin/patients.php" method="POST" class="card bg-light p-4 border mb-4">
                            <input type="hidden" name="patient_id" value="<?php echo $edit_patient['id']; ?>">
                            <h6 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-user-pen me-1"></i> Edit Patient Record - <?php echo htmlspecialchars($edit_patient['full_name']); ?></h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="full_name" required value="<?php echo htmlspecialchars($edit_patient['full_name']); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="phone" required value="<?php echo htmlspecialchars($edit_patient['phone']); ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Street Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="address" required value="<?php echo htmlspecialchars($edit_patient['address']); ?>">
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Date of Birth</label>
                                    <input type="date" class="form-control" name="dob" value="<?php echo $edit_patient['dob']; ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Gender</label>
                                    <select class="form-select" name="gender">
                                        <option value="Male" <?php echo ($edit_patient['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($edit_patient['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo ($edit_patient['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Blood Group</label>
                                    <select class="form-select" name="blood_group">
                                        <option value="O+" <?php echo ($edit_patient['blood_group'] == 'O+') ? 'selected' : ''; ?>>O+</option>
                                        <option value="O-" <?php echo ($edit_patient['blood_group'] == 'O-') ? 'selected' : ''; ?>>O-</option>
                                        <option value="A+" <?php echo ($edit_patient['blood_group'] == 'A+') ? 'selected' : ''; ?>>A+</option>
                                        <option value="A-" <?php echo ($edit_patient['blood_group'] == 'A-') ? 'selected' : ''; ?>>A-</option>
                                        <option value="B+" <?php echo ($edit_patient['blood_group'] == 'B+') ? 'selected' : ''; ?>>B+</option>
                                        <option value="B-" <?php echo ($edit_patient['blood_group'] == 'B-') ? 'selected' : ''; ?>>B-</option>
                                        <option value="AB+" <?php echo ($edit_patient['blood_group'] == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                        <option value="AB-" <?php echo ($edit_patient['blood_group'] == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Emergency Contact Phone</label>
                                <input type="text" class="form-control" name="emergency_contact" value="<?php echo htmlspecialchars($edit_patient['emergency_contact'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Medical History / Allergies</label>
                                <textarea class="form-control" name="medical_history" rows="2"><?php echo htmlspecialchars($edit_patient['medical_history'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Vaccination Records</label>
                                <textarea class="form-control" name="vaccination_records" rows="2"><?php echo htmlspecialchars($edit_patient['vaccination_records'] ?? ''); ?></textarea>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?php echo APP_URL; ?>/admin/patients.php" class="btn btn-outline-secondary btn-sm">Cancel Edit</a>
                                <button type="submit" class="btn btn-primary btn-sm px-4">Update Patient Record <i class="fa-solid fa-save ms-1"></i></button>
                            </div>
                        </form>
                    <?php endif; ?>

                    <!-- Patients Table Roster -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Blood & Gender</th>
                                    <th>Contact</th>
                                    <th>Bookings</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($patients as $p): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-primary"><?php echo htmlspecialchars($p['full_name']); ?></strong><br>
                                            <small class="text-muted">User ID: <?php echo htmlspecialchars($p['username']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger"><?php echo htmlspecialchars($p['blood_group'] ?? 'O+'); ?></span>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($p['gender'] ?? 'Male'); ?></span>
                                        </td>
                                        <td>
                                            <small class="d-block"><i class="fa-solid fa-envelope me-1"></i><?php echo htmlspecialchars($p['email']); ?></small>
                                            <small class="d-block"><i class="fa-solid fa-phone me-1"></i><?php echo htmlspecialchars($p['phone']); ?></small>
                                        </td>
                                        <td><span class="badge bg-info text-dark"><?php echo $p['appointment_count']; ?> Bookings</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo APP_URL; ?>/admin/patients.php?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-outline-primary" title="Modify Record"><i class="fa-solid fa-pen"></i></a>
                                                <a href="<?php echo APP_URL; ?>/admin/patients.php?action=delete&id=<?php echo $p['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Deleting patient will remove account and medical records. Continue?');" title="Delete"><i class="fa-solid fa-trash"></i></a>
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
