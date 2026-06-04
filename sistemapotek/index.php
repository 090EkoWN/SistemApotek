<?php
// Mengaktifkan pelaporan semua jenis error untuk mempermudah debugging selama masa pengembangan
error_reporting(E_ALL);
// Memastikan error ditampilkan langsung ke layar browser
ini_set('display_errors', 1);
// Memulai sesi PHP agar sistem bisa membaca dan menyimpan data session login pengguna
session_start();
// Menyertakan file koneksi database MySQL
require_once 'koneksi.php';

// Proteksi Pengalihan: Jika user sudah dalam keadaan login, tendang langsung ke dashboard masing-masing
if (isset($_SESSION['id_user'])) {
    // Jika role-nya pasien pindah ke pasien_dashboard.php, jika admin/lainnya ke dashboard.php
    header('Location: ' . (($_SESSION['role'] ?? '') === 'pasien' ? 'pasien_dashboard.php' : 'dashboard.php'));
    exit(); // Menghentikan kelanjutan eksekusi script
}

// Inisialisasi variabel untuk menampung pesan error dan pesan sukses
$error = $success = '';
// Menangkap parameter 'msg' dari URL/metode GET untuk menampilkan notifikasi dinamis
$msg = $_GET['msg'] ?? '';
if ($msg === 'login')   $error   = 'Silakan login terlebih dahulu.';
if ($msg === 'timeout') $error   = 'Sesi habis. Silakan login kembali.';
if ($msg === 'logout')  $success = 'Anda berhasil keluar dari sistem.';

// Mengecek apakah form dikirim melalui metode POST (saat tombol login ditekan)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mengambil input username dan password serta membersihkan spasi di awal dan akhir teks
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Validasi awal: Memastikan kedua field tidak dibiarkan kosong
    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        // Menyiapkan prepared statement untuk mencari user berdasarkan username (mencegah SQL Injection)
        $stmt = $koneksi->prepare("SELECT id_user,username,password,nama_lengkap,role FROM users WHERE username=? LIMIT 1");
        $stmt->bind_param('s', $username); // Mengikat parameter username dengan tipe string ('s')
        $stmt->execute(); // Jalankan query
        $user = $stmt->get_result()->fetch_assoc(); // Ambil baris data pengguna dalam bentuk array asosiatif
        $stmt->close(); // Tutup statement query
        
        // Verifikasi keamanan ganda: password_verify (untuk hash modern) ATAU plain text (kompatibilitas data lama)
        if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
            // Jika valid, simpan data informasi user ke dalam superglobal $_SESSION
            $_SESSION['id_user']       = $user['id_user'];
            $_SESSION['username']      = $user['username'];
            $_SESSION['nama_lengkap']  = $user['nama_lengkap'];
            $_SESSION['role']          = $user['role'] ?? 'admin'; // Default role ke admin jika tidak diisi
            $_SESSION['last_activity'] = time(); // Mencatat stempel waktu aktivitas terakhir untuk fitur timeout
            
            // Mengarahkan pengguna ke dashboard yang sesuai dengan peran (role) mereka
            header('Location: ' . ($_SESSION['role'] === 'pasien' ? 'pasien_dashboard.php' : 'dashboard.php'));
            exit();
        } else {
            // Jika gagal, buat pesan error spesifik berdasarkan letak kesalahannya
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
<link class="sub-css" rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="login-page">

    <div class="login-left">
        <div class="login-logo">
            <div class="login-logo-icon"><i class="fa-solid fa-heart-pulse"></i></div>
            <div>
                <div class="login-logo-text"><?= APP_NAME ?></div>
                <div class="login-logo-sub">Management System</div>
            </div>
        </div>

        <div class="login-hero">
            <h1>Kelola Apotek<br>Lebih <span>Cerdas.</span></h1>
            <p>
                Sistem manajemen apotek modern untuk memudahkan pengelolaan
                stok obat, data pasien, dan transaksi secara efisien dan akurat.
            </p>
            
        </div>

        <div>
            <p class="login-copy" style="margin-top:1.25rem">
                &copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.
            </p>
        </div>
    </div>

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
/* Fitur Interaktif JavaScript untuk Mengubah Tipe Input (Show/Hide Password) */
document.getElementById('togglePw').addEventListener('click', function () {
    const pw  = document.getElementById('password'); // Elemen input password
    const ic  = document.getElementById('eyeIcon');  // Elemen icon mata
    const btn = this;                               // Merujuk ke tombol ini sendiri
    
    // Memeriksa status tipe input saat ini
    if (pw.type === 'password') {
        pw.type = 'text'; // Ubah tipe ke teks agar karakter terlihat
        btn.innerHTML = '<i class="fa-solid fa-eye-slash" id="eyeIcon"></i> Sembunyikan';
    } else {
        pw.type = 'password'; // Kembalikan ke tipe password agar tersembunyi (* / •)
        btn.innerHTML = '<i class="fa-solid fa-eye" id="eyeIcon"></i> Tampilkan';
    }
});
</script>
</body>
</html>