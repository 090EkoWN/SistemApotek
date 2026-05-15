<?php
// ============================================================
// auth.php — Proteksi Halaman dengan Session
// Include di awal setiap halaman yang butuh login
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hitung kedalaman path untuk redirect ke root
$depth = substr_count($_SERVER['PHP_SELF'], '/') - 1;
$root  = str_repeat('../', $depth);

// Cek apakah sudah login
if (!isset($_SESSION['id_user'])) {
    header('Location: ' . $root . 'index.php?msg=login');
    exit();
}

// Session timeout: 2 jam tidak aktif
if (isset($_SESSION['last_activity'])) {
    if ((time() - $_SESSION['last_activity']) > 7200) {
        session_unset();
        session_destroy();
        header('Location: ' . $root . 'index.php?msg=timeout');
        exit();
    }
}
$_SESSION['last_activity'] = time();
?>