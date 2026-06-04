<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'koneksi.php';

if (isset($_SESSION['id_user'])) {
    header('Location: ' . (($_SESSION['role'] ?? '') === 'pasien' ? 'pasien_dashboard.php' : 'dashboard.php'));
    exit();
}

$error = $success = '';
$msg = $_GET['msg'] ?? '';
if ($msg === 'login')   $error   = 'Silakan login terlebih dahulu.';
if ($msg === 'timeout') $error   = 'Sesi habis. Silakan login kembali.';
if ($msg === 'logout')  $success = 'Anda berhasil keluar dari sistem.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $koneksi->prepare("SELECT id_user,username,password,nama_lengkap,role FROM users WHERE username=? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
            $_SESSION['id_user']       = $user['id_user'];
            $_SESSION['username']      = $user['username'];
            $_SESSION['nama_lengkap']  = $user['nama_lengkap'];
            $_SESSION['role']          = $user['role'] ?? 'admin';
            $_SESSION['last_activity'] = time();
            header('Location: ' . ($_SESSION['role'] === 'pasien' ? 'pasien_dashboard.php' : 'dashboard.php'));
            exit();
        } else {
            $error = $user ? 'Password salah. Coba lagi.' : 'Username tidak ditemukan.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Login — <?= APP_NAME ?></title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="login-page">

    <!-- KIRI — hero & branding -->
    <div class="login-left">
        <!-- Logo -->
        <div class="login-logo">
            <div class="login-logo-icon"><i class="fa-solid fa-heart-pulse"></i></div>
            <div>
                <div class="login-logo-text"><?= APP_NAME ?></div>
                <div class="login-logo-sub">Management System</div>
            </div>
        </div>

        <!-- Hero teks -->
        <div class="login-hero">
            <h1>Kelola Apotek<br>Lebih <span>Cerdas.</span></h1>
            <p>
                Sistem manajemen apotek modern untuk memudahkan pengelolaan
                stok obat, data pasien, dan transaksi secara efisien dan akurat.
            </p>
            
        </div>

        <!-- Stats -->
        <div>
            <p class="login-copy" style="margin-top:1.25rem">
                &copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.
            </p>
        </div>
    </div>

    <!-- KANAN — form login -->
    <div class="login-right">
        <div class="login-card">

            <div class="lc-header">
                <div class="lc-icon"><i class="fa-solid fa-right-to-bracket"></i></div>
                <h2>Masuk ke Akun</h2>
                <p>Masukkan kredensial Anda untuk melanjutkan</p>
            </div>

            <?php if ($error): ?>
            <div class="lf-alert danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="lf-alert success">
                <i class="fa-solid fa-circle-check"></i>
                <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">

                <div class="lf-group">
                    <label class="lf-label" for="username">Username</label>
                    <div class="lf-wrap">
                        <i class="fa-solid fa-user lf-icon"></i>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="lf-input"
                            placeholder="Masukkan username"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                            required autofocus>
                    </div>
                </div>

                <div class="lf-group">
                    <label class="lf-label" for="password">
                        Password
                        <button type="button" class="lf-toggle" id="togglePw">
                            <i class="fa-solid fa-eye" id="eyeIcon"></i> Tampilkan
                        </button>
                    </label>
                    <div class="lf-wrap">
                        <i class="fa-solid fa-lock lf-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="lf-input"
                            placeholder="Masukkan password"
                            required>
                    </div>
                </div>

                <button type="submit" class="lf-btn">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Masuk ke Sistem
                </button>

            </form>

            <p class="lc-footer">
                <i class="fa-solid fa-headset"></i>
                Hubungi administrator jika lupa password
            </p>

        </div>
    </div>

</div>

<script>
// Toggle show/hide password
document.getElementById('togglePw').addEventListener('click', function () {
    const pw  = document.getElementById('password');
    const ic  = document.getElementById('eyeIcon');
    const btn = this;
    if (pw.type === 'password') {
        pw.type = 'text';
        btn.innerHTML = '<i class="fa-solid fa-eye-slash" id="eyeIcon"></i> Sembunyikan';
    } else {
        pw.type = 'password';
        btn.innerHTML = '<i class="fa-solid fa-eye" id="eyeIcon"></i> Tampilkan';
    }
});
</script>
</body>
</html>
