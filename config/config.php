<?php
// Start session
session_start();

// Include database configuration
require_once 'database.php';

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// Base URL
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/helpdesk';

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to display messages
function setMessage($type, $message) {
    $_SESSION['message'] = [
        'type' => $type,
        'text' => $message
    ];
}

// Function to display and clear messages
function showMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        return '<div class="alert alert-' . $message['type'] . ' alert-dismissible fade show" role="alert">
                ' . $message['text'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    }
    return '';
}
?>
