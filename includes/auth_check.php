<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Role-Based Access Control (RBAC) Authentication Middleware
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Checks if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged in user details
 */
function get_current_user_data() {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'email' => $_SESSION['email'] ?? '',
        'full_name' => $_SESSION['full_name'] ?? '',
        'role' => $_SESSION['role'] ?? 'patient'
    ];
}

/**
 * Requires user to be logged in, otherwise redirects to login page
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash_message('danger', 'Please log in to access this page.');
        header('Location: ' . APP_URL . '/auth/login.php');
        exit;
    }
}

/**
 * Requires user to have a specific role (admin, doctor, or patient)
 */
function require_role($required_role) {
    require_login();
    $current_role = $_SESSION['role'] ?? '';
    
    if (is_array($required_role)) {
        if (!in_array($current_role, $required_role)) {
            set_flash_message('danger', 'Access denied: Unauthorized access attempt.');
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }
    } else {
        if ($current_role !== $required_role) {
            set_flash_message('danger', 'Access denied: You do not have permission to view this page.');
            header('Location: ' . APP_URL . '/index.php');
            exit;
        }
    }
}
