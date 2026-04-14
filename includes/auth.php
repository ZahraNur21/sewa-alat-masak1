<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_role() {
    return $_SESSION['role'] ?? '';
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function require_role($roles) {
    require_login();
    if (!in_array(get_role(), (array)$roles)) {
        header('Location: ' . APP_URL . '/unauthorized.php');
        exit;
    }
}

function current_user() {
    return [
        'id'   => $_SESSION['user_id'] ?? null,
        'nama' => $_SESSION['nama'] ?? '',
        'role' => $_SESSION['role'] ?? '',
        'email'=> $_SESSION['email'] ?? '',
    ];
}
?>