<?php
require_once 'config/config.php';

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
setMessage('success', 'Anda telah berhasil logout.');
redirect($base_url . '/login.php');
?>
