<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Admin Report Generation & Interactive Analytics Dashboard
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
    fputcsv($output, ['Appointment Ref', 'Patient Name', 'Patient Phone', 'Doctor Name', 'Specialty', 'City', 'Date', 'Time', 'Fee', 'Status', 'Payment Method', 'Transaction ID']);

    $rows = fetchAll("
        SELECT a.appointment_number, u_pat.full_name AS patient_name, u_pat.phone AS patient_phone, u_doc.full_name AS doctor_name, s.name AS specialty_name, c.name AS city_name, a.appointment_date, a.appointment_time, a.fee, a.status, a.payment_method, a.transaction_id 
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

// Analytics Data for Chart.js
$monthly_stats = fetchAll("
    SELECT DATE_FORMAT(appointment_date, '%b %Y') AS month_label, 
           COUNT(*) AS total_bookings, 
           SUM(CASE WHEN payment_status = 'Paid' THEN fee ELSE 0 END) AS total_revenue 
    FROM appointments 
    GROUP BY DATE_FORMAT(appointment_date, '%Y-%m'), DATE_FORMAT(appointment_date, '%b %Y')
    ORDER BY MIN(appointment_date) ASC 
    LIMIT 6
");

$specialty_stats = fetchAll("
    SELECT s.name AS specialty_name, 
           COUNT(a.id) AS appointment_count, 
           COALESCE(SUM(a.fee), 0) AS total_revenue
    FROM specialties s
    LEFT JOIN doctors d ON s.id = d.specialty_id
    LEFT JOIN appointments a ON d.id = a.doctor_id
    GROUP BY s.id, s.name
    ORDER BY total_revenue DESC
");

$payment_stats = fetchAll("
    SELECT COALESCE(payment_method, 'Stripe Card') AS method, 
           COUNT(*) AS count, 
           COALESCE(SUM(fee), 0) AS total_amount 
    FROM appointments 
    GROUP BY payment_method
");

$status_stats = fetchAll("
    SELECT status, COUNT(*) AS count 
    FROM appointments 
    GROUP BY status
");

// Prepare Chart JSON Data
$months_labels = array_column($monthly_stats, 'month_label');
$monthly_revenue = array_map('floatval', array_column($monthly_stats, 'total_revenue'));
$monthly_bookings = array_map('intval', array_column($monthly_stats, 'total_bookings'));

$spec_labels = array_column($specialty_stats, 'specialty_name');
$spec_revenues = array_map('floatval', array_column($specialty_stats, 'total_revenue'));

$pay_labels = array_column($payment_stats, 'method');
$pay_counts = array_map('intval', array_column($payment_stats, 'count'));

$page_title = "Reports & Analytics";
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<style>
@media print {
    .no-print, header, footer, .cs_header, .cs_footer, .col-lg-3, .btn {
        display: none !important;
    }
    .col-lg-9 {
        width: 100% !important;
    }
    body, .bg-light {
        background-color: #fff !important;
        padding: 0 !important;
    }
    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
}
</style>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row gy-4">
            
            <div class="col-lg-3 no-print">
                <?php include_once __DIR__ . '/../includes/sidebar.php'; ?>
            </div>

            <div class="col-lg-9">
                <div class="card border-0 shadow-sm rounded-3 p-4">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3 flex-wrap gap-2">
                        <div>
                            <h4 class="mb-1 text-primary fw-bold"><i class="fa-solid fa-chart-line me-2"></i> System Analytics & Financial Reports</h4>
                            <p class="text-muted small mb-0">Real-time consultation activity, revenue breakdown, and master schedules</p>
                        </div>
                        <div class="d-flex gap-2 no-print">
                            <a href="<?php echo APP_URL; ?>/admin/reports.php?export=appointments_csv" class="btn btn-outline-success btn-sm fw-bold">
                                <i class="fa-solid fa-file-excel me-1"></i> Export Appointments CSV
                            </a>
                            <button onclick="window.print();" class="btn btn-outline-primary btn-sm fw-bold">
                                <i class="fa-solid fa-print me-1"></i> Print / Save PDF
                            </button>
                        </div>
                    </div>

                    <!-- Interactive Analytics Visualizations -->
                    <div class="row gy-4 mb-5 no-print">
                        <div class="col-md-7">
                            <div class="card border p-3 rounded-3 h-100 bg-white">
                                <h6 class="text-primary fw-bold mb-3"><i class="fa-solid fa-chart-column me-2"></i> Monthly Revenue ($) & Appointments Trend</h6>
                                <div style="height: 260px; position: relative;">
                                    <canvas id="revenueTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="card border p-3 rounded-3 h-100 bg-white">
                                <h6 class="text-primary fw-bold mb-3"><i class="fa-solid fa-chart-pie me-2"></i> Revenue by Medical Specialty</h6>
                                <div style="height: 260px; position: relative;">
                                    <canvas id="specialtyChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Report 1: Appointments Summary -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-primary mb-0"><i class="fa-solid fa-calendar-check me-2"></i> 1. Recent Appointment Activity Roster</h5>
                        <span class="badge bg-secondary">Latest 15 Entries</span>
                    </div>
                    <div class="table-responsive mb-5">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Ref #</th>
                                    <th>Patient</th>
                                    <th>Doctor & Specialty</th>
                                    <th>Date & Time</th>
                                    <th>Fee ($)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointment_report as $apt): ?>
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

                    <!-- Report 2: Doctor Availability Schedules -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-primary mb-0"><i class="fa-solid fa-clock me-2"></i> 2. Master Doctor Schedules & Working Hours</h5>
                        <span class="badge bg-success">All Active Roster</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Doctor Name</th>
                                    <th>Specialty</th>
                                    <th>Day of Week</th>
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
                                        <td><span class="badge bg-success">Active Availability</span></td>
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

<!-- Chart.js Setup Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Revenue & Bookings Trend Chart
    const revenueCtx = document.getElementById('revenueTrendChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(count($months_labels) ? $months_labels : [date('M Y')]); ?>,
                datasets: [
                    {
                        label: 'Total Revenue ($)',
                        data: <?php echo json_encode(count($monthly_revenue) ? $monthly_revenue : [270.00]); ?>,
                        backgroundColor: 'rgba(42, 150, 230, 0.75)',
                        borderColor: '#2a96e6',
                        borderWidth: 1.5,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Bookings (Count)',
                        data: <?php echo json_encode(count($monthly_bookings) ? $monthly_bookings : [2]); ?>,
                        type: 'line',
                        borderColor: '#002261',
                        backgroundColor: '#002261',
                        borderWidth: 2,
                        tension: 0.3,
                        pointRadius: 4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) { return '$' + value; }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    // 2. Revenue by Specialty Pie Chart
    const specCtx = document.getElementById('specialtyChart');
    if (specCtx) {
        new Chart(specCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(count($spec_labels) ? $spec_labels : ['Cardiology', 'Neurology', 'Pediatrics']); ?>,
                datasets: [{
                    data: <?php echo json_encode(count($spec_revenues) ? $spec_revenues : [120, 150, 90]); ?>,
                    backgroundColor: [
                        '#2a96e6',
                        '#002261',
                        '#20c997',
                        '#fd7e14',
                        '#6f42c1',
                        '#0dcaf0'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': $' + context.raw.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
