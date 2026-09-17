<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Admin Report Generation Capabilities (Appointments, Doctor Schedules, Patient Summary)
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Admin Access Control
require_role('admin');

$export = sanitize_input($_GET['export'] ?? '');

// Handle CSV Export
if ($export === 'appointments_csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="appointments_report_' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Appointment Ref', 'Patient Name', 'Patient Phone', 'Doctor Name', 'Specialty', 'City', 'Date', 'Time', 'Fee', 'Status', 'Payment Method']);

    $rows = fetchAll("
        SELECT a.appointment_number, u_pat.full_name AS patient_name, u_pat.phone AS patient_phone, u_doc.full_name AS doctor_name, s.name AS specialty_name, c.name AS city_name, a.appointment_date, a.appointment_time, a.fee, a.status, a.payment_method 
        FROM appointments a 
        JOIN patients p ON a.patient_id = p.id 
        JOIN users u_pat ON p.user_id = u_pat.id 
        JOIN doctors d ON a.doctor_id = d.id 
        JOIN users u_doc ON d.user_id = u_doc.id 
        JOIN specialties s ON d.specialty_id = s.id 
        JOIN cities c ON d.city_id = c.id 
        ORDER BY a.id DESC
    ");

    foreach ($rows as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// Fetch Summaries for Report Rendering
$appointment_report = fetchAll("
    SELECT a.*, u_pat.full_name AS patient_name, u_doc.full_name AS doctor_name, s.name AS specialty_name 
    FROM appointments a 
    JOIN patients p ON a.patient_id = p.id 
    JOIN users u_pat ON p.user_id = u_pat.id 
    JOIN doctors d ON a.doctor_id = d.id 
    JOIN users u_doc ON d.user_id = u_doc.id 
    JOIN specialties s ON d.specialty_id = s.id 
    ORDER BY a.id DESC LIMIT 15
");

$doctor_schedules_report = fetchAll("
    SELECT u.full_name, s.name AS specialty_name, ds.day_of_week, ds.start_time, ds.end_time, ds.is_available 
    FROM doctor_schedules ds 
    JOIN doctors d ON ds.doctor_id = d.id 
    JOIN users u ON d.user_id = u.id 
    JOIN specialties s ON d.specialty_id = s.id 
    ORDER BY u.full_name ASC, FIELD(ds.day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')
");

$page_title = "Report Generation";
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
                    
                    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
                        <h4 class="mb-0 text-primary"><i class="fa-solid fa-chart-line me-2"></i> Report Generation & Analytics</h4>
                        <div class="d-flex gap-2">
                            <a href="<?php echo APP_URL; ?>/admin/reports.php?export=appointments_csv" class="btn btn-outline-success btn-sm fw-bold">
                                <i class="fa-solid fa-file-excel me-1"></i> Export CSV
                            </a>
                            <button onclick="window.print();" class="btn btn-outline-primary btn-sm fw-bold">
                                <i class="fa-solid fa-print me-1"></i> Print Report
                            </button>
                        </div>
                    </div>

                    <!-- Report 1: Appointments Summary -->
                    <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-calendar-check me-2"></i> 1. Appointment Activity Report</h5>
                    <div class="table-responsive mb-5">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Ref #</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Date & Time</th>
                                    <th>Fee ($)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointment_report as $apt): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo htmlspecialchars($apt['appointment_number']); ?></td>
                                        <td><?php echo htmlspecialchars($apt['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($apt['doctor_name']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($apt['appointment_date'])) . ' ' . date('g:i A', strtotime($apt['appointment_time'])); ?></td>
                                        <td class="fw-bold text-success">$<?php echo number_format($apt['fee'], 2); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($apt['status']); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Report 2: Doctor Availability Schedules -->
                    <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-clock me-2"></i> 2. Master Doctor Schedules Report</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Doctor Name</th>
                                    <th>Specialty</th>
                                    <th>Day</th>
                                    <th>Working Hours</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($doctor_schedules_report as $ds): ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo htmlspecialchars($ds['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($ds['specialty_name']); ?></td>
                                        <td><?php echo htmlspecialchars($ds['day_of_week']); ?></td>
                                        <td><?php echo date('g:i A', strtotime($ds['start_time'])) . ' - ' . date('g:i A', strtotime($ds['end_time'])); ?></td>
                                        <td><span class="badge bg-success">Active</span></td>
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
