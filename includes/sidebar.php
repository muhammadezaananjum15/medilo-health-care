<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Dashboard Sidebar Include Template
 */

require_once __DIR__ . '/auth_check.php';
$user = get_current_user_data();
$current_page = basename($_SERVER['PHP_SELF']);

// Determine dynamic avatar based on role
$user_avatar = APP_URL . '/assets/img/team_1.jpg';
if ($user) {
    if ($user['role'] === 'doctor') {
        $doc_record = fetchOne("SELECT avatar FROM doctors WHERE user_id = ?", [$user['id']]);
        if ($doc_record && !empty($doc_record['avatar'])) {
            $user_avatar = APP_URL . '/' . htmlspecialchars($doc_record['avatar']);
        }
    } elseif ($user['role'] === 'patient') {
        $user_avatar = APP_URL . '/assets/img/team_2.jpg';
    } elseif ($user['role'] === 'admin') {
        $user_avatar = APP_URL . '/assets/img/team_4.jpg';
    }
}
?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body text-center p-4 cs_blue_bg text-white rounded-top">
        <div class="mb-3">
            <img src="<?php echo $user_avatar; ?>" class="rounded-circle border border-3 border-white shadow-sm object-fit-cover" width="80" height="80" alt="User Profile">
        </div>
        <h5 class="mb-1 text-white fw-bold"><?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?></h5>
        <span class="badge bg-info text-dark px-3 py-1 rounded-pill text-uppercase fw-semibold">
            <?php echo htmlspecialchars($user['role'] ?? 'Guest'); ?>
        </span>
    </div>
    <div class="list-group list-group-flush py-2">
        <?php if ($user['role'] === 'admin'): ?>
            <a href="<?php echo APP_URL; ?>/admin/dashboard.php" class="list-group-item list-group-item-action py-3 <?php echo ($current_page == 'dashboard.php') ? 'active cs_blue_bg text-white' : ''; ?>">
                <i class="fa-solid fa-gauge me-2"></i> Dashboard
            </a>
            <a href="<?php echo APP_URL; ?>/admin/cities.php" class="list-group-item list-group-item-action py-3 <?php echo ($current_page == 'cities.php') ? 'active cs_blue_bg text-white' : ''; ?>">
                <i class="fa-solid fa-city me-2"></i> Manage Cities
            </a>
            <a href="<?php echo APP_URL; ?>/admin/specialties.php" class="list-group-item list-group-item-action py-3 <?php echo ($current_page == 'specialties.php') ? 'active cs_blue_bg text-white' : ''; ?>">
                <i class="fa-solid fa-layer-group me-2"></i> Manage Specialties
            </a>
            <a href="<?php echo APP_URL; ?>/admin/doctors.php" class="list-group-item list-group-item-action py-3 <?php echo ($current_page == 'doctors.php') ? 'active cs_blue_bg text-white' : ''; ?>">
                <i class="fa-solid fa-user-doctor me-2"></i> Doctor Management
            </a>
            <a href="<?php echo APP_URL; ?>/admin/patients.php" class="list-group-item list-group-item-action py-3 <?php echo ($current_page == 'patients.php') ? 'active cs_blue_bg text-white' : ''; ?>">
                <i class="fa-solid fa-hospital-user me-2"></i> Patient Records
            </a>
            <a href="<?php echo APP_URL; ?>/admin/appointments.php" class="list-group-item list-group-item-action py-3 <?php echo ($current_page == 'appointments.php') ? 'active cs_blue_bg text-white' : ''; ?>">
                <i class="fa-solid fa-calendar-check me-2"></i> All Appointments
            </a>
            <a href="<?php echo APP_URL; ?>/admin/content.php" class="list-group-item list-group-item-action py-3 <?php echo ($current_page == 'content.php') ? 'active cs_blue_bg text-white' : ''; ?>">
                <i class="fa-solid fa-newspaper me-2"></i> Health & News Content
            </a>
            <a href="<?php echo APP_URL; ?>/admin/reports.php" class="list-group-item list-group-item-action py-3 <?php echo ($current_page == 'reports.php') ? 'active cs_blue_bg text-white' : ''; ?>">
                <i class="fa-solid fa-chart-line me-2"></i> Reports & Analytics
            </a>
            <a href="<?php echo APP_URL; ?>/admin/settings.php" class="list-group-item list-group-item-action py-3 <?php echo ($current_page == 'settings.php') ? 'active cs_blue_bg text-white' : ''; ?>">
                <i class="fa-solid fa-gear me-2"></i> System Settings
            </a>

        <?php elseif ($user['role'] === 'doctor'): ?>
            <a href="<?php echo APP_URL; ?>/frontend/doctor_dashboard.php" class="list-group-item list-group-item-action py-3 <?php echo ($current_page == 'doctor_dashboard.php' && (!isset($_GET['tab']) || $_GET['tab'] == 'overview')) ? 'active cs_blue_bg text-white' : ''; ?>">
                <i class="fa-solid fa-user-doctor me-2"></i> Doctor Overview
            </a>
            <a href="<?php echo APP_URL; ?>/frontend/doctor_dashboard.php?tab=schedule" class="list-group-item list-group-item-action py-3 <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'schedule') ? 'active cs_blue_bg text-white' : ''; ?>">
                <i class="fa-solid fa-clock me-2"></i> My Availability Schedule
            </a>
            <a href="<?php echo APP_URL; ?>/frontend/doctor_dashboard.php?tab=appointments" class="list-group-item list-group-item-action py-3 <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'appointments') ? 'active cs_blue_bg text-white' : ''; ?>">
                <i class="fa-solid fa-calendar-check me-2"></i> Patient Appointments
            </a>

        <?php elseif ($user['role'] === 'patient'): ?>
            <a href="<?php echo APP_URL; ?>/frontend/patient_dashboard.php" class="list-group-item list-group-item-action py-3 <?php echo ($current_page == 'patient_dashboard.php' && (!isset($_GET['tab']) || $_GET['tab'] == 'dashboard')) ? 'active cs_blue_bg text-white' : ''; ?>">
                <i class="fa-solid fa-user me-2"></i> My Dashboard
            </a>
            <a href="<?php echo APP_URL; ?>/frontend/book_appointment.php" class="list-group-item list-group-item-action py-3 <?php echo ($current_page == 'book_appointment.php') ? 'active cs_blue_bg text-white' : ''; ?>">
                <i class="fa-solid fa-plus-circle me-2"></i> Book New Appointment
            </a>
            <a href="<?php echo APP_URL; ?>/frontend/patient_dashboard.php?tab=appointments" class="list-group-item list-group-item-action py-3 <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'appointments') ? 'active cs_blue_bg text-white' : ''; ?>">
                <i class="fa-solid fa-calendar-days me-2"></i> My Appointments
            </a>
            <a href="<?php echo APP_URL; ?>/frontend/patient_dashboard.php?tab=records" class="list-group-item list-group-item-action py-3 <?php echo (isset($_GET['tab']) && $_GET['tab'] == 'records') ? 'active cs_blue_bg text-white' : ''; ?>">
                <i class="fa-solid fa-notes-medical me-2"></i> Medical & Vaccination Records
            </a>
        <?php endif; ?>

        <a href="<?php echo APP_URL; ?>/auth/logout.php" class="list-group-item list-group-item-action py-3 text-danger fw-bold">
            <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
        </a>
    </div>
</div>
