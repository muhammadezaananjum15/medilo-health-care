<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Admin Control Panel Dashboard
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Admin Access Control
require_role('admin');

// Fetch System KPI Overview Stats
$total_doctors = fetchOne("SELECT COUNT(*) AS total FROM doctors")['total'] ?? 0;
$total_patients = fetchOne("SELECT COUNT(*) AS total FROM patients")['total'] ?? 0;
$total_appointments = fetchOne("SELECT COUNT(*) AS total FROM appointments")['total'] ?? 0;
$total_revenue = fetchOne("SELECT SUM(amount) AS total FROM payments WHERE status = 'Success'")['total'] ?? 0.00;
$total_cities = fetchOne("SELECT COUNT(*) AS total FROM cities")['total'] ?? 0;
$total_specialties = fetchOne("SELECT COUNT(*) AS total FROM specialties")['total'] ?? 0;

// Fetch Recent Appointments
$recent_appointments = fetchAll("
    SELECT a.*, u_pat.full_name AS patient_name, u_doc.full_name AS doctor_name, s.name AS specialty_name 
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.id 
    JOIN users u_pat ON p.user_id = u_pat.id 
    JOIN doctors d ON a.doctor_id = d.id 
    JOIN users u_doc ON d.user_id = u_doc.id 
    JOIN specialties s ON d.specialty_id = s.id 
    ORDER BY a.id DESC LIMIT 6
");

$page_title = "Admin Dashboard";
include_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row gy-4">
            
            <!-- Sidebar -->
            <div class="col-lg-3">
                <?php include_once __DIR__ . '/../includes/sidebar.php'; ?>
            </div>

            <!-- Dashboard Overview Content -->
            <div class="col-lg-9">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0 text-primary"><i class="fa-solid fa-gauge me-2"></i> System Oversight Dashboard</h3>
                    <span class="badge bg-success py-2 px-3 fs-7"><i class="fa-solid fa-shield-halved me-1"></i> Admin Privileges Active</span>
                </div>

                <!-- KPI Metric Cards -->
                <div class="row gy-3 mb-4">
                    <div class="col-md-4 col-sm-6">
                        <div class="card border-0 shadow-sm p-4 rounded-3 cs_blue_bg text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="text-white fw-bold mb-0"><?php echo $total_doctors; ?></h2>
                                    <span class="text-white-50 small">Total Doctors</span>
                                </div>
                                <i class="fa-solid fa-user-doctor fs-1 text-white-50"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="card border-0 shadow-sm p-4 rounded-3 bg-info text-dark">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="fw-bold mb-0"><?php echo $total_patients; ?></h2>
                                    <span class="text-dark-50 small">Registered Patients</span>
                                </div>
                                <i class="fa-solid fa-hospital-user fs-1 text-dark-50"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="card border-0 shadow-sm p-4 rounded-3 bg-success text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="text-white fw-bold mb-0"><?php echo $total_appointments; ?></h2>
                                    <span class="text-white-50 small">Total Bookings</span>
                                </div>
                                <i class="fa-solid fa-calendar-check fs-1 text-white-50"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="card border-0 shadow-sm p-4 rounded-3 bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="text-white fw-bold mb-0">$<?php echo number_format($total_revenue, 2); ?></h2>
                                    <span class="text-white-50 small">Gateway Revenue</span>
                                </div>
                                <i class="fa-solid fa-sack-dollar fs-1 text-white-50"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="card border-0 shadow-sm p-4 rounded-3 bg-secondary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="text-white fw-bold mb-0"><?php echo $total_cities; ?></h2>
                                    <span class="text-white-50 small">Master Cities</span>
                                </div>
                                <i class="fa-solid fa-city fs-1 text-white-50"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="card border-0 shadow-sm p-4 rounded-3 bg-dark text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h2 class="text-white fw-bold mb-0"><?php echo $total_specialties; ?></h2>
                                    <span class="text-white-50 small">Specialty Catalogs</span>
                                </div>
                                <i class="fa-solid fa-stethoscope fs-1 text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Module Management Buttons -->
                <div class="card border-0 shadow-sm p-4 mb-4 rounded-3">
                    <h5 class="border-bottom pb-2 mb-3 text-primary"><i class="fa-solid fa-bolt me-2"></i> Quick Administrative Shortcuts</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?php echo APP_URL; ?>/admin/doctors.php?action=add" class="btn btn-outline-primary py-2 px-3"><i class="fa-solid fa-user-plus me-1"></i> Add Doctor</a>
                        <a href="<?php echo APP_URL; ?>/admin/cities.php" class="btn btn-outline-secondary py-2 px-3"><i class="fa-solid fa-city me-1"></i> Add City</a>
                        <a href="<?php echo APP_URL; ?>/admin/specialties.php" class="btn btn-outline-info py-2 px-3"><i class="fa-solid fa-layer-group me-1"></i> Add Specialty</a>
                        <a href="<?php echo APP_URL; ?>/admin/content.php?action=add_health" class="btn btn-outline-success py-2 px-3"><i class="fa-solid fa-file-medical me-1"></i> Add Health Info</a>
                        <a href="<?php echo APP_URL; ?>/admin/reports.php" class="btn btn-outline-dark py-2 px-3"><i class="fa-solid fa-print me-1"></i> Generate Reports</a>
                    </div>
                </div>

                <!-- Recent System Appointments Table -->
                <div class="card border-0 shadow-sm rounded-3 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 text-primary"><i class="fa-solid fa-clock-rotate-left me-2"></i> Recent System Appointments</h5>
                        <a href="<?php echo APP_URL; ?>/admin/appointments.php" class="small text-primary fw-bold text-decoration-none">View All <i class="fa-solid fa-arrow-right ms-1"></i></a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Ref #</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Date</th>
                                    <th>Fee</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_appointments as $apt): ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?php echo htmlspecialchars($apt['appointment_number']); ?></td>
                                        <td><?php echo htmlspecialchars($apt['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($apt['doctor_name']); ?> <br><small class="text-muted"><?php echo htmlspecialchars($apt['specialty_name']); ?></small></td>
                                        <td><?php echo date('M d, Y', strtotime($apt['appointment_date'])) . ' ' . date('g:i A', strtotime($apt['appointment_time'])); ?></td>
                                        <td class="fw-bold text-success">$<?php echo number_format($apt['fee'], 2); ?></td>
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
