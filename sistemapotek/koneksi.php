<?php
// ============================================================
// koneksi.php — Konfigurasi Koneksi Database
// ============================================================

define('DB_HOST',     'localhost');
define('DB_USER',     'root');
define('DB_PASS',     '');          // kosong untuk XAMPP default
define('DB_NAME',     'db_apotek');
define('APP_NAME',    'Apotek Sehat');

// Buat koneksi MySQLi
$koneksi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($koneksi->connect_error) {
    die('<div style="font-family:sans-serif;padding:2rem;color:#991b1b;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;max-width:500px;margin:2rem auto;">
        <strong>❌ Koneksi Database Gagal!</strong><br><br>
        Pastikan:<br>
        1. MySQL sudah aktif di XAMPP<br>
        2. Database <code>db_apotek</code> sudah diimport<br><br>
        Error: ' . htmlspecialchars($koneksi->connect_error) . '
    </div>');
}

// Set charset UTF-8
$koneksi->set_charset('utf8mb4');
?>