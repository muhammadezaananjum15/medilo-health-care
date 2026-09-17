<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * User Session Logout Handler
 */

require_once __DIR__ . '/../config/config.php';

// Unset all session variables
$_SESSION = array();

// Destroy session cookie if set
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Start clean session for flash message
session_start();
set_flash_message('success', 'You have been logged out successfully.');

header('Location: ' . APP_URL . '/index.php');
exit;
