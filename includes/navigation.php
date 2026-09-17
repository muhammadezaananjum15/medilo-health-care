<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Dynamic Navigation Menu Include
 */

$user = get_current_user_data();
?>
<ul class="cs_nav_list">
    <li><a href="<?php echo APP_URL; ?>/index.php">Home</a></li>
    <li><a href="<?php echo APP_URL; ?>/frontend/doctors.php">Doctors</a></li>
    <li><a href="<?php echo APP_URL; ?>/frontend/services.php">Services</a></li>
    <li><a href="<?php echo APP_URL; ?>/frontend/health_info.php">Health Info</a></li>
    <li><a href="<?php echo APP_URL; ?>/frontend/news.php">Medical News</a></li>
    <li><a href="<?php echo APP_URL; ?>/frontend/contact.php">Contact</a></li>
    <?php if ($user && $user['role'] === 'patient'): ?>
        <li><a href="<?php echo APP_URL; ?>/frontend/book_appointment.php" class="text-primary fw-bold">Book Appointment</a></li>
    <?php endif; ?>
</ul>
