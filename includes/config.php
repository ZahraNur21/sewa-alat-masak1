<?php
// includes/config.php

// ================= CONFIG =================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sewa_alat_masak');

define('APP_NAME', 'SewaAlat Masak');
define('APP_URL', 'http://localhost/sewa-alat-masak');

// ================= KONEKSI =================
$conn = new mysqli("localhost", "root", "", "sewa_alat_masak", 4306);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:2rem;color:#dc2626;">
        <h2>❌ Koneksi Database Gagal</h2>
        <p>' . $conn->connect_error . '</p>
        <p>Pastikan MySQL aktif dan database <strong>' . DB_NAME . '</strong> sudah dibuat.</p>
        <p><a href="' . APP_URL . '/database/database.sql">Import database.sql</a></p>
    </div>');
}

$conn->set_charset('utf8mb4');

// ================= FUNCTION =================

// log aktivitas user
function log_activity($conn, $user_id, $aksi, $detail = '') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $conn->prepare("INSERT INTO log_aktifitas (user_id, aksi, detail, ip_address) VALUES (?,?,?,?)");
    
    if ($stmt) {
        $stmt->bind_param("isss", $user_id, $aksi, $detail, $ip);
        $stmt->execute();
    }
}

// format rupiah
function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// generate kode peminjaman
function kode_pinjam() {
    return 'PJM-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
}
?>