<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Admin Doctor Specialties CRUD Module
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Admin Access Control
require_role('admin');

$action = sanitize_input($_GET['action'] ?? '');
$spec_id = intval($_GET['id'] ?? 0);

// Delete Specialty
if ($action === 'delete' && $spec_id > 0) {
    executeQuery("DELETE FROM specialties WHERE id = ?", [$spec_id]);
    set_flash_message('success', 'Specialty deleted successfully.');
    header('Location: ' . APP_URL . '/admin/specialties.php');
    exit;
}

// Add / Edit Specialty POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $icon = sanitize_input($_POST['icon'] ?? 'fa-solid fa-stethoscope');
    $edit_id = intval($_POST['spec_id'] ?? 0);

    if (empty($name)) {
        set_flash_message('danger', 'Specialty name is required.');
    } else {
        if ($edit_id > 0) {
            executeQuery("UPDATE specialties SET name = ?, description = ?, icon = ? WHERE id = ?", [$name, $description, $icon, $edit_id]);
            set_flash_message('success', 'Specialty updated successfully.');
        } else {
            executeQuery("INSERT INTO specialties (name, description, icon) VALUES (?, ?, ?)", [$name, $description, $icon]);
            set_flash_message('success', 'New medical specialty catalog created.');
        }
        header('Location: ' . APP_URL . '/admin/specialties.php');
        exit;
    }
}

// Fetch single record for editing
$edit_spec = null;
if ($action === 'edit' && $spec_id > 0) {
    $edit_spec = fetchOne("SELECT * FROM specialties WHERE id = ?", [$spec_id]);
}

$specialties = fetchAll("SELECT s.*, COUNT(d.id) AS doctor_count FROM specialties s LEFT JOIN doctors d ON s.id = d.specialty_id GROUP BY s.id ORDER BY s.name ASC");

$page_title = "Manage Doctor Specialties";
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
                    <h4 class="border-bottom pb-2 mb-4 text-primary"><i class="fa-solid fa-layer-group me-2"></i> Doctor Specialties Directory (CRUD)</h4>

                    <!-- Add/Edit Form -->
                    <form action="<?php echo APP_URL; ?>/admin/specialties.php" method="POST" class="card bg-light p-3 border mb-4">
                        <input type="hidden" name="spec_id" value="<?php echo $edit_spec['id'] ?? 0; ?>">
                        <h6 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-pen-to-square me-1"></i> <?php echo $edit_spec ? 'Edit Specialty' : 'Add New Specialty'; ?></h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Specialty Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required placeholder="e.g. Cardiology" value="<?php echo htmlspecialchars($edit_spec['name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">FontAwesome Icon Class</label>
                                <input type="text" class="form-control" name="icon" placeholder="fa-solid fa-heart-pulse" value="<?php echo htmlspecialchars($edit_spec['icon'] ?? 'fa-solid fa-stethoscope'); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Brief summary of medical specialty focus"><?php echo htmlspecialchars($edit_spec['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <?php if ($edit_spec): ?>
                                <a href="<?php echo APP_URL; ?>/admin/specialties.php" class="btn btn-outline-secondary btn-sm">Cancel</a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary btn-sm px-4">
                                <?php echo $edit_spec ? 'Update Specialty' : 'Save Specialty'; ?> <i class="fa-solid fa-save ms-1"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Specialties Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Icon</th>
                                    <th>Specialty Name</th>
                                    <th>Description</th>
                                    <th>Doctors Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($specialties as $s): ?>
                                    <tr>
                                        <td class="fs-4 text-info"><i class="<?php echo htmlspecialchars($s['icon']); ?>"></i></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($s['name']); ?></td>
                                        <td><small class="text-muted"><?php echo htmlspecialchars(substr($s['description'], 0, 70)); ?>...</small></td>
                                        <td><span class="badge bg-info text-dark"><?php echo $s['doctor_count']; ?> Doctors</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo APP_URL; ?>/admin/specialties.php?action=edit&id=<?php echo $s['id']; ?>" class="btn btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                                                <a href="<?php echo APP_URL; ?>/admin/specialties.php?action=delete&id=<?php echo $s['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this specialty?');"><i class="fa-solid fa-trash"></i></a>
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
