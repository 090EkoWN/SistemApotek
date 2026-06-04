<?php
// ============================================================
// pasien_profil.php — Profil Pasien
// ============================================================
// Menyertakan file keamanan auth.php untuk memastikan pengguna sudah login
require_once 'auth.php';
// Menyertakan file koneksi database agar script bisa berinteraksi dengan MySQL
require_once 'koneksi.php';

// Memastikan bahwa pengguna yang mengakses memiliki level/peran (role) sebagai 'pasien'
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pasien') {
    // Jika bukan pasien, alihkan secara paksa ke halaman dashboard.php
    header('Location: dashboard.php');
    exit(); // Menghentikan seluruh proses eksekusi kode di bawahnya
}

// Mengambil ID pengguna dari session login aktif
$id_user = $_SESSION['id_user'];

// Mengambil data detail pasien dari database berdasarkan id_user yang sedang login
$stmt = $koneksi->prepare("SELECT * FROM pasien WHERE id_user = ?");
$stmt->bind_param('i', $id_user); // Mengikat variabel id_user dengan tipe integer ('i')
$stmt->execute(); // Menjalankan perintah SQL
$pasien = $stmt->get_result()->fetch_assoc(); // Mengambil data hasil query dalam bentuk array asosiatif
$stmt->close(); // Menutup statement untuk membebaskan resource memori

// Menyiapkan variabel array untuk menampung pesan error validasi form
$errors = [];
// Menyiapkan variabel string untuk menampung pesan sukses
$success = '';

// Proses penanganan form jika ada data yang dikirim dengan metode POST dan data pasien ditemukan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pasien) {
    // Mengambil data No HP dan Alamat dari form input, lalu membersihkan spasi di awal/akhir teks
    $no_hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    
    // Validasi input: Jika kosong, masukkan pesan error yang sesuai ke dalam array $errors
    if (empty($no_hp)) $errors[] = 'No. HP wajib diisi';
    if (empty($alamat)) $errors[] = 'Alamat wajib diisi';
    
    // Jika tidak ada error sama sekali, lakukan proses update ke database
    if (empty($errors)) {
        // Menyiapkan query UPDATE untuk mengubah data No HP dan Alamat pasien
        $stmt = $koneksi->prepare("UPDATE pasien SET no_hp=?, alamat=? WHERE id_pasien=?");
        $stmt->bind_param('ssi', $no_hp, $alamat, $pasien['id_pasien']); // 'ssi' berarti string, string, integer
        
        // Mengeksekusi query update tersebut
        if ($stmt->execute()) {
            // Jika berhasil, isi pesan sukses
            $success = 'Profil berhasil diperbarui!';
            
            // Mengambil kembali data terbaru dari database (Refresh Data) agar langsung tampil di form
            $stmt = $koneksi->prepare("SELECT * FROM pasien WHERE id_user = ?");
            $stmt->bind_param('i', $id_user);
            $stmt->execute();
            $pasien = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } else {
            // Jika query gagal dieksekusi oleh database, masukkan pesan error
            $errors[] = 'Gagal memperbarui profil';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/sidebar_extra.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="app-wrap">

    <aside class="sidebar" id="sidebar">
        <div class="sb-brand">
            <div class="sb-brand-icon"><i class="fa-solid fa-users"></i></div>
            <div>
                <div class="sb-brand-name"><?= APP_NAME ?></div>
                <div class="sb-brand-sub">Portal Pasien</div>
            </div>
        </div>

        <nav class="sb-nav">
            <div class="sb-group-label">MENU PASIEN</div>
            
            <a href="pasien_dashboard.php" class="sb-item">
                <i class="fa-solid fa-gauge"></i> Dashboard
            </a>
            <a href="pasien_riwayat.php" class="sb-item">
                <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Obat
            </a>
            <a href="pasien_profil.php" class="sb-item active">
                <i class="fa-solid fa-user-pen"></i> Profil Saya
            </a>
        </nav>

        <div class="sb-footer">
            <div class="sb-user">
                <div class="sb-avatar"><?= strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)) ?></div>
                <div>
                    <div class="sb-uname"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></div>
                    <div class="sb-urole">Pasien</div>
                </div>
            </div>
            <a href="logout.php" class="sb-logout"> Keluar</a>
        </div>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
                <div class="topbar-title">
                    <h2>Profil Saya</h2>
                    <div class="topbar-breadcrumb">
                        <a href="pasien_dashboard.php">Dashboard</a>
                        <span class="sep">/</span>
                        <span class="cur">Profil</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-info">
                    <span class="topbar-dot"></span>
                    <?= date('d M Y') ?>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="page-header">
                <div>
                    <h1>Profil Saya</h1>
                    <p>Kelola informasi pribadi Anda</p>
                </div>
            </div>

            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <div><?= htmlspecialchars($success) ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
               <i class="fa-solid fa-circle-exclamation"></i>
                <div>
                    <strong>Terdapat kesalahan:</strong>
                    <ul style="margin:.3rem 0 0 1rem">
                        <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!$pasien): ?>
            <div class="alert alert-warning">
                <div class="card-title">
                    Informasi Akun
                </div>
                <div>
                    <strong>Data Tidak Tersedia</strong><br>
                    Akun Anda belum terhubung dengan data pasien. Hubungi administrator.
                </div>
            </div>
            <?php else: ?>

            <div class="card">
                <div class="card-head">
                    <div class="card-title">
                        Informasi Akun
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" 
                                   value="<?= htmlspecialchars($_SESSION['username']) ?>" 
                                   disabled>
                        </div>
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" class="form-control" 
                                   value="<?= htmlspecialchars($_SESSION['nama_lengkap']) ?>" 
                                   disabled>
                        </div>
                    </div>
                    <p style="color:#64748b;font-size:0.9rem;margin-top:1rem">
                        <strong>Catatan:</strong> Username dan nama lengkap tidak dapat diubah. Hubungi administrator jika perlu mengubah data ini.
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <div class="card-title">
                        Data Pribadi
                    </div>
                </div>
                
                <div class="card-body">
                    <form method="POST">
                        <div class="form-grid">
                            
                            <div class="form-group">
                                <label>Tanggal Lahir</label>
                                <input type="date" class="form-control" 
                                       value="<?= htmlspecialchars($pasien['tanggal_lahir']) ?>" 
                                       disabled>
                                <small style="color:#64748b">Tidak dapat diubah</small>
                            </div>

                            <div class="form-group">
                                <label for="no_hp">No. HP <span style="color:var(--red)">*</span></label>
                                <input type="text" id="no_hp" name="no_hp" class="form-control" 
                                       value="<?= htmlspecialchars($pasien['no_hp']) ?>" 
                                       placeholder="08xx-xxxx-xxxx" required>
                            </div>

                            <div class="form-group full">
                                <label for="alamat">Alamat <span style="color:var(--red)">*</span></label>
                                <textarea id="alamat" name="alamat" class="form-control" 
                                          rows="3" required><?= htmlspecialchars($pasien['alamat']) ?></textarea>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="pasien_dashboard.php" class="btn btn-ghost">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>

            <?php endif; ?>
        </div>
    </div>

</div>

<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<script>
/* Fungsi JavaScript untuk memanipulasi class CSS agar sidebar muncul/sembunyi pada perangkat mobile */
function toggleSidebar() {
    // Menambah atau menghapus class 'open' pada elemen ber-class 'sidebar'
    document.querySelector('.sidebar').classList.toggle('open');
    // Menambah atau menghapus class 'show' pada elemen ber-id 'overlay'
    document.getElementById('overlay').classList.toggle('show');
}
</script>
</body>
</html>