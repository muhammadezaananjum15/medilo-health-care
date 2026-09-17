<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Admin All Appointments & Payment Gateway Tracking Module
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Admin Access Control
require_role('admin');

$action = sanitize_input($_GET['action'] ?? '');
$apt_id = intval($_GET['id'] ?? 0);
$status_val = sanitize_input($_GET['status'] ?? '');

// Update Appointment Status
if ($action === 'update_status' && $apt_id > 0 && !empty($status_val)) {
    executeQuery("UPDATE appointments SET status = ? WHERE id = ?", [$status_val, $apt_id]);
    set_flash_message('success', 'Appointment #' . $apt_id . ' status updated to ' . $status_val);
    header('Location: ' . APP_URL . '/admin/appointments.php');
    exit;
}

// Fetch all appointments with full patient & doctor details
$appointments = fetchAll("
    SELECT a.*, u_pat.full_name AS patient_name, u_pat.phone AS patient_phone, u_doc.full_name AS doctor_name, s.name AS specialty_name, c.name AS city_name 
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.id 
    JOIN users u_pat ON p.user_id = u_pat.id 
    JOIN doctors d ON a.doctor_id = d.id 
    JOIN users u_doc ON d.user_id = u_doc.id 
    JOIN specialties s ON d.specialty_id = s.id 
    JOIN cities c ON d.city_id = c.id 
    ORDER BY a.id DESC
");

$page_title = "Manage Appointments";
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
                    <h4 class="border-bottom pb-2 mb-4 text-primary"><i class="fa-solid fa-calendar-check me-2"></i> All System Appointments & Gateway Logs</h4>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Ref #</th>
                                    <th>Patient</th>
                                    <th>Doctor & Specialty</th>
                                    <th>Date & Time</th>
                                    <th>Fee / Gateway</th>
                                    <th>Status</th>
                                    <th>Actions</th>
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
                                            <strong><?php echo htmlspecialchars($apt['doctor_name']); ?></strong><br>
                                            <span class="badge bg-secondary fs-7"><?php echo htmlspecialchars($apt['specialty_name']); ?> (<?php echo htmlspecialchars($apt['city_name']); ?>)</span>
                                        </td>
                                        <td>
                                            <?php echo date('M d, Y', strtotime($apt['appointment_date'])); ?><br>
                                            <small class="text-muted"><i class="fa-solid fa-clock me-1"></i><?php echo date('g:i A', strtotime($apt['appointment_time'])); ?></small>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">$<?php echo number_format($apt['fee'], 2); ?></span><br>
                                            <small class="text-muted fs-7"><?php echo htmlspecialchars($apt['payment_method'] ?? 'Stripe'); ?> (<?php echo htmlspecialchars($apt['payment_status']); ?>)</small>
                                        </td>
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
                                                <a href="<?php echo APP_URL; ?>/admin/appointments.php?action=update_status&id=<?php echo $apt['id']; ?>&status=Confirmed" class="btn btn-outline-success">Confirm</a>
                                                <a href="<?php echo APP_URL; ?>/admin/appointments.php?action=update_status&id=<?php echo $apt['id']; ?>&status=Completed" class="btn btn-outline-info">Complete</a>
                                                <a href="<?php echo APP_URL; ?>/admin/appointments.php?action=update_status&id=<?php echo $apt['id']; ?>&status=Cancelled" class="btn btn-outline-danger">Cancel</a>
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
