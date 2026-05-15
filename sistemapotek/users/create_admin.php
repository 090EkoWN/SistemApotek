<?php
// ============================================================
// users/create_admin.php
// Jalankan sekali untuk membuat hash password yang benar
// Akses: http://localhost/apotek/users/create_admin.php
// ============================================================
require_once '../koneksi.php';

$username     = 'eko';
$password_raw = '1234';
$nama         = 'Eko Wahyu Nugroho';
$hash         = password_hash($password_raw, PASSWORD_DEFAULT);

// Hapus dulu jika sudah ada, lalu insert ulang
$koneksi->query("DELETE FROM users WHERE username = '$username'");
$stmt = $koneksi->prepare("INSERT INTO users (username, password, nama_lengkap) VALUES (?, ?, ?)");
$stmt->bind_param('sss', $username, $hash, $nama);

if ($stmt->execute()) {
    echo '<div style="font-family:sans-serif;padding:2rem;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;max-width:500px;margin:2rem auto;">';
    echo '<h3 style="color:#065f46">✅ User berhasil dibuat!</h3>';
    echo '<p><strong>Username:</strong> ' . $username . '</p>';
    echo '<p><strong>Password:</strong> ' . $password_raw . '</p>';
    echo '<p><strong>Hash:</strong> <code style="font-size:.75rem">' . $hash . '</code></p>';
    echo '<br><a href="../index.php" style="background:#10b981;color:white;padding:.5rem 1.2rem;border-radius:6px;text-decoration:none;">→ Login Sekarang</a>';
    echo '</div>';
} else {
    echo '<p style="color:red">Gagal: ' . $stmt->error . '</p>';
}
$stmt->close();
?>