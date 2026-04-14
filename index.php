<?php
session_start();
require_once __DIR__ . '/includes/config.php';

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    header("Location: " . APP_URL . "/$role/dashboard.php");
} else {
    header("Location: " . APP_URL . "/auth/login.php");
}
exit;