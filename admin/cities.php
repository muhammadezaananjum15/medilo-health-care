<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Admin Cities CRUD Module (Add, Modify, Delete Cities)
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Admin Access Control
require_role('admin');

$action = sanitize_input($_GET['action'] ?? '');
$city_id = intval($_GET['id'] ?? 0);

// Handle Delete City
if ($action === 'delete' && $city_id > 0) {
    executeQuery("DELETE FROM cities WHERE id = ?", [$city_id]);
    set_flash_message('success', 'City record deleted successfully.');
    header('Location: ' . APP_URL . '/admin/cities.php');
    exit;
}

// Handle Add / Edit POST Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name'] ?? '');
    $state = sanitize_input($_POST['state'] ?? '');
    $country = sanitize_input($_POST['country'] ?? 'USA');
    $edit_id = intval($_POST['city_id'] ?? 0);

    if (empty($name)) {
        set_flash_message('danger', 'City name is required.');
    } else {
        if ($edit_id > 0) {
            executeQuery("UPDATE cities SET name = ?, state = ?, country = ? WHERE id = ?", [$name, $state, $country, $edit_id]);
            set_flash_message('success', 'City updated successfully.');
        } else {
            executeQuery("INSERT INTO cities (name, state, country) VALUES (?, ?, ?)", [$name, $state, $country]);
            set_flash_message('success', 'New city added to master database.');
        }
        header('Location: ' . APP_URL . '/admin/cities.php');
        exit;
    }
}

// Fetch single city for edit if requested
$edit_city = null;
if ($action === 'edit' && $city_id > 0) {
    $edit_city = fetchOne("SELECT * FROM cities WHERE id = ?", [$city_id]);
}

// Fetch all cities list
$cities = fetchAll("SELECT c.*, COUNT(d.id) AS doctor_count FROM cities c LEFT JOIN doctors d ON c.id = d.city_id GROUP BY c.id ORDER BY c.name ASC");

$page_title = "Manage Master Cities";
include_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row gy-4">
            
            <!-- Sidebar -->
            <div class="col-lg-3">
                <?php include_once __DIR__ . '/../includes/sidebar.php'; ?>
            </div>

            <!-- Main Content Area -->
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm rounded-3 p-4">
                    
                    <h4 class="border-bottom pb-2 mb-4 text-primary d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-city me-2"></i> Master Cities Directory (CRUD)</span>
                    </h4>

                    <!-- Add / Edit Form -->
                    <form action="<?php echo APP_URL; ?>/admin/cities.php" method="POST" class="card bg-light p-3 border mb-4">
                        <input type="hidden" name="city_id" value="<?php echo $edit_city['id'] ?? 0; ?>">
                        <h6 class="fw-bold mb-3 text-primary">
                            <i class="fa-solid fa-pen-to-square me-1"></i> <?php echo $edit_city ? 'Edit City Record' : 'Add New Master City'; ?>
                        </h6>
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label class="form-label fw-bold">City Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required placeholder="e.g. Chicago" value="<?php echo htmlspecialchars($edit_city['name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">State / Province</label>
                                <input type="text" class="form-control" name="state" placeholder="e.g. IL" value="<?php echo htmlspecialchars($edit_city['state'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">Country</label>
                                <input type="text" class="form-control" name="country" value="<?php echo htmlspecialchars($edit_city['country'] ?? 'USA'); ?>">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <?php if ($edit_city): ?>
                                <a href="<?php echo APP_URL; ?>/admin/cities.php" class="btn btn-outline-secondary btn-sm">Cancel</a>
                            <?php endif; ?>
                            <button type="submit" class="btn btn-primary btn-sm px-4">
                                <?php echo $edit_city ? 'Update City' : 'Save City Record'; ?> <i class="fa-solid fa-save ms-1"></i>
                            </button>
                        </div>
                    </form>

                    <!-- Cities Table List -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>City Name</th>
                                    <th>State</th>
                                    <th>Country</th>
                                    <th>Doctors Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cities as $c): ?>
                                    <tr>
                                        <td><?php echo $c['id']; ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($c['name']); ?></td>
                                        <td><?php echo htmlspecialchars($c['state'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($c['country']); ?></td>
                                        <td><span class="badge bg-info text-dark"><?php echo $c['doctor_count']; ?> Doctors</span></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo APP_URL; ?>/admin/cities.php?action=edit&id=<?php echo $c['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                                <a href="<?php echo APP_URL; ?>/admin/cities.php?action=delete&id=<?php echo $c['id']; ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this city?');" title="Delete">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
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
