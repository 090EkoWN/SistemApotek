<?php
// ============================================================
// index.php — Halaman Login
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'koneksi.php';

// Sudah login → dashboard sesuai role
if (isset($_SESSION['id_user'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'pasien') {
        header('Location: pasien_dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

$error = $success = '';

// Pesan dari redirect
$msg = $_GET['msg'] ?? '';
if ($msg === 'login')   $error   = 'Silakan login terlebih dahulu.';
if ($msg === 'timeout') $error   = 'Sesi habis. Silakan login kembali.';
if ($msg === 'logout')  $success = 'Anda berhasil keluar dari sistem.';

// Proses form login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        // Prepared statement — aman dari SQL Injection
        $stmt = $koneksi->prepare(
            "SELECT id_user, username, password, nama_lengkap, role FROM users WHERE username = ? LIMIT 1"
        );
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $res  = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();

        if ($user) {
            // Cek password: dukung hash baru DAN plain text lama (migrasi)
            $valid = password_verify($password, $user['password'])
                  || ($password === $user['password']); // fallback plain text

            if ($valid) {
                $_SESSION['id_user']       = $user['id_user'];
                $_SESSION['username']      = $user['username'];
                $_SESSION['nama_lengkap']  = $user['nama_lengkap'];
                $_SESSION['role']          = $user['role'] ?? 'admin';
                $_SESSION['last_activity'] = time();
                
                // Redirect berdasarkan role
                if ($_SESSION['role'] === 'pasien') {
                    header('Location: pasien_dashboard.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit();
            } else {
                $error = 'Password salah. Coba lagi.';
            }
        } else {
            $error = 'Username tidak ditemukan.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="page-login">

<div class="login-wrap">

    <!-- ── Sisi Kiri ── -->
    <div class="login-side">
        <div class="login-side-inner">
            <div class="ls-logo">
                <span class="ls-logo-icon">+</span>
                <span class="ls-logo-text"><?= APP_NAME ?></span>
            </div>
            <h1 class="ls-title">
                Kelola Apotek Lebih Mudah
            </h1>
            <p class="ls-desc">
                Sistem informasi untuk mengelola data obat,
                pasien dan transaksi secara efisien.
            </p>
            <p class="ls-footer">&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</p>
        </div>
    </div>

    <!-- ── Sisi Kanan (Form) ── -->
    <div class="login-form-side">
        <div class="login-box">

            <div class="lb-header">
                <h2>Masuk ke Akun</h2>
                <p>Masukkan kredensial Anda untuk melanjutkan</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <span class="alert-icon">!</span>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success">
                <span class="alert-icon">✓</span>
                <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" autocomplete="off">

                <!-- Username -->
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-icon-wrap">
                        <span class="input-icon">U</span>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-control"
                            placeholder="Masukkan username"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">
                        Password
                        <button type="button" class="toggle-pw" id="togglePw" title="Tampilkan password">
                            <span id="eyeIcon">👁️</span> Tampilkan
                        </button>
                    </label>
                    <div class="input-icon-wrap">
                        <span class="input-icon">*</span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Masukkan password"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="btn-login">
                     Masuk ke Sistem
                </button>

            </form>

            <p class="lb-footer">
                 Hubungi administrator jika lupa password
            </p>

        </div>
    </div>

</div>

<script>
// Toggle show/hide password
document.getElementById('togglePw').addEventListener('click', function () {
    var pw   = document.getElementById('password');
    var icon = document.getElementById('eyeIcon');
    if (pw.type === 'password') {
        pw.type      = 'text';
        icon.textContent = '🙈';
        this.childNodes[1].textContent = ' Sembunyikan';
    } else {
        pw.type      = 'password';
        icon.textContent = '👁️';
        this.childNodes[1].textContent = ' Tampilkan';
    }
});
</script>

</body>
</html>