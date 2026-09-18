<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Mark Notification As Read Endpoint (AJAX & Direct Redirect Handler)
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

require_login();

$user = get_current_user_data();
$notif_id = intval($_GET['id'] ?? ($_POST['id'] ?? 0));
$action = sanitize_input($_GET['action'] ?? ($_POST['action'] ?? ''));

if ($action === 'mark_all') {
    executeQuery("UPDATE notifications SET is_read = 1 WHERE user_id = ?", [$user['id']]);
} elseif ($notif_id > 0) {
    executeQuery("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?", [$notif_id, $user['id']]);
}

// Check if request is AJAX
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Notification marked as read']);
    exit;
}

// Otherwise redirect back
$return_url = $_SERVER['HTTP_REFERER'] ?? (APP_URL . '/frontend/patient_dashboard.php');
header('Location: ' . $return_url);
exit;
