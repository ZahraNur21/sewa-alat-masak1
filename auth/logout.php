<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

// cek kalau user login
if (isset($_SESSION['user_id'])) {
    log_activity($conn, $_SESSION['user_id'], 'Logout', 'User logout');
}

// hapus semua session
$_SESSION = [];
session_unset();
session_destroy();

// redirect ke login
header('Location: ' . APP_URL . '/auth/login.php');
exit;