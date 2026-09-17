<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Global Application Configuration File
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'medilo_db');

// Application Details
define('APP_NAME', 'Medilo Medical & Health');
define('APP_URL', 'http://localhost/medilo');

// Payment Gateway Test Keys (Simulated / Sandbox Integration)
define('STRIPE_PUBLIC_KEY', 'pk_test_51MzMediloSampleStripeKey123456');
define('STRIPE_SECRET_KEY', 'sk_test_51MzMediloSampleStripeKey123456');
define('PAYPAL_CLIENT_ID', 'sandbox_paypal_client_id_medilo_2026');

// Global Helper: Sanitize User Input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Global Helper: Flash Message Notification
function set_flash_message($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'info';
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_type'], $_SESSION['flash_message']);
        
        $alert_class = ($type === 'success') ? 'alert-success' : (($type === 'danger' || $type === 'error') ? 'alert-danger' : 'alert-info');
        echo '<div class="alert ' . $alert_class . ' alert-dismissible fade show m-3" role="alert">';
        echo '<strong>' . ucfirst($type) . ':</strong> ' . htmlspecialchars($msg);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}
