<?php
// Menyertakan file keamanan auth.php untuk memastikan status login user
require_once 'auth.php';
// Menyertakan file koneksi database MySQL
require_once 'koneksi.php';

// Memeriksa apakah user yang masuk memiliki hak akses (role) sebagai 'pasien'
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pasien') {
    // Jika bukan pasien, alihkan paksa ke halaman dashboard utama dan hentikan script
    header('Location: dashboard.php'); exit();
}

// Mengambil ID pengguna dari data session yang aktif
$id_user = $_SESSION['id_user'];

// Menyiapkan statement SQL untuk mengambil data profil pasien berdasarkan id_user
$stmt = $koneksi->prepare("SELECT * FROM pasien WHERE id_user = ?");
$stmt->bind_param('i', $id_user); // Mengikat nilai parameter dengan tipe data integer
$stmt->execute(); // Mengeksekusi query ke database
$data_pasien = $stmt->get_result()->fetch_assoc(); // Mengonversi hasil query menjadi array asosiatif
$stmt->close(); // Menutup statement query

// Menyimpan ID pasien jika ditemukan, jika tidak diset menjadi null
$id_pasien = $data_pasien['id_pasien'] ?? null;

// Menginisialisasi variabel counter statistik awal dengan nilai 0
$total_transaksi    = 0;
$total_obat_diterima = 0;

// Jika ID pasien valid/ada di database, lakukan perhitungan statistik ringkas
if ($id_pasien) {
    // Query untuk menghitung jumlah baris/total kunjungan pemberian obat pada pasien terkait
    $res = $koneksi->query("SELECT COUNT(*) total FROM pemberian_obat WHERE id_pasien=$id_pasien");
    $total_transaksi = $res->fetch_assoc()['total'];

    // Query untuk menjumlahkan (SUM) total kuantitas obat yang pernah diterima pasien terkait
    $res = $koneksi->query("SELECT SUM(jumlah) total FROM pemberian_obat WHERE id_pasien=$id_pasien");
    $total_obat_diterima = $res->fetch_assoc()['total'] ?? 0; // Jika belum pernah menerima obat, default ke 0
}

// Menginisialisasi variabel riwayat awal
$riwayat = null;
// Jika ID pasien valid/ada, ambil 5 data riwayat pemberian obat yang paling terbaru
if ($id_pasien) {
    $riwayat = $koneksi->query(
        "SELECT po.*, o.nama_obat, o.kategori
         FROM pemberian_obat po
         JOIN obat o ON po.id_obat=o.id_obat
         WHERE po.id_pasien=$id_pasien
         ORDER BY po.tanggal_pemberian DESC LIMIT 5" // Dibatasi hanya menampilkan maksimal 5 baris data terbaru
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Dashboard Pasien — <?= APP_NAME ?></title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/sidebar_extra.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="app-wrap">

    <aside class="sidebar" id="sidebar">
        <div class="sb-brand">
            <div class="sb-brand-icon"><i class="fa-solid fa-heart-pulse"></i></div>
            <div>
                <div class="sb-brand-name"><?= APP_NAME ?></div>
                <div class="sb-brand-sub">Portal Pasien</div>
            </div>
        </div>
        <nav class="sb-nav">
            <div class="sb-group-label">Menu Pasien</div>
            <a href="pasien_dashboard.php" class="sb-item active">
                <i class="fa-solid fa-gauge"></i> Dashboard
            </a>
            <a href="pasien_riwayat.php" class="sb-item">
                <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Obat
            </a>
            <a href="pasien_profil.php" class="sb-item">
                <i class="fa-solid fa-user-pen"></i> Profil Saya
            </a>
        </nav>
        <div class="sb-footer">
            <div class="sb-user">
                <div class="sb-avatar"><?= strtoupper(substr($_SESSION['nama_lengkap'],0,1)) ?></div>
                <div>
                    <div class="sb-uname"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></div>
                    <div class="sb-urole">Pasien</div>
                </div>
            </div>
            <a href="logout.php" class="sb-logout">
                <i class="fa-solid fa-right-from-bracket"></i> Keluar
            </a>
        </div>
    </aside>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
                <div class="topbar-title">
                    <h2>Dashboard Pasien</h2>
                    <div class="topbar-breadcrumb">
                        <span><?= APP_NAME ?></span>
                        <span class="sep">/</span>
                        <span class="cur">Dashboard</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-info">
                    <span class="topbar-dot"></span><?= date('d M Y') ?>
                </div>
            </div>
        </div>

        <div class="page-body">

            <div class="page-header">
                <div>
                    <h1>Portal Pasien</h1>
                    <p>Pantau riwayat pengobatan dan informasi kesehatan Anda.</p>
                </div>
            </div>

            <?php if (!$id_pasien): ?>
            <div class="alert alert-warning">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div><strong>Data Belum Lengkap</strong><br>
                Akun Anda belum terhubung dengan data pasien. Silakan hubungi administrator.</div>
            </div>
            <?php endif; ?>

            <div class="stats-row" style="grid-template-columns:repeat(2,1fr);max-width:540px">
                <div class="stat-card teal">
                    <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
                    <div class="stat-info">
                        <div class="stat-num"><?= $total_transaksi ?></div>
                        <div class="stat-label">Total Kunjungan</div>
                    </div>
                </div>
                <div class="stat-card navy">
                    <div class="stat-icon"><i class="fa-solid fa-pills"></i></div>
                    <div class="stat-info">
                        <div class="stat-num"><?= $total_obat_diterima ?></div>
                        <div class="stat-label">Obat Diterima</div>
                    </div>
                </div>
            </div>

            <?php if ($id_pasien && $data_pasien): ?>
            <div class="card">
                <div class="card-head">
                    <div class="card-title">
                        <i class="fa-solid fa-id-card"></i> Informasi Pribadi
                    </div>
                    <a href="pasien_profil.php" class="btn btn-sm btn-ghost">
                        <i class="fa-solid fa-pen"></i> Edit Profil
                    </a>
                </div>
                <div class="card-body">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.25rem">
                        <div>
                            <div style="font-size:.75rem;color:var(--g400);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">Nama Lengkap</div>
                            <div style="font-weight:600;color:var(--g900)"><?= htmlspecialchars($data_pasien['nama_pasien']) ?></div>
                        </div>
                        <div>
                            <div style="font-size:.75rem;color:var(--g400);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">Tanggal Lahir</div>
                            <div style="font-weight:600;color:var(--g900)"><?= date('d F Y',strtotime($data_pasien['tanggal_lahir'])) ?></div>
                        </div>
                        <div>
                            <div style="font-size:.75rem;color:var(--g400);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">No. HP</div>
                            <div style="font-weight:600;color:var(--g900)"><?= htmlspecialchars($data_pasien['no_hp']) ?></div>
                        </div>
                        <div>
                            <div style="font-size:.75rem;color:var(--g400);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem">Alamat</div>
                            <div style="font-weight:600;color:var(--g900)"><?= htmlspecialchars($data_pasien['alamat']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-head">
                    <div class="card-title">
                        <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Pemberian Obat Terbaru
                    </div>
                    <a href="pasien_riwayat.php" class="btn btn-sm btn-ghost">
                        Lihat Semua <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
                <div class="table-wrap">
                    <?php if ($riwayat && $riwayat->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Obat</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Dosis</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = $riwayat->fetch_assoc()): ?>
                        <tr>
                            <td class="td-bold"><?= date('d/m/Y',strtotime($row['tanggal_pemberian'])) ?></td>
                            <td><?= htmlspecialchars($row['nama_obat']) ?></td>
                            <td><span class="badge badge-navy"><?= htmlspecialchars($row['kategori']) ?></span></td>
                            <td><span class="badge badge-teal"><?= $row['jumlah'] ?></span></td>
                            <td class="td-muted"><?= htmlspecialchars($row['dosis']) ?></td>
                            <td class="td-muted"><?= htmlspecialchars($row['keterangan'] ?: '-') ?></td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty">
                        <i class="fa-regular fa-clipboard"></i>
                        <p>Belum ada riwayat pemberian obat</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <div class="card-title">
                        <i class="fa-solid fa-circle-info"></i> Informasi Penting
                    </div>
                </div>
                <div class="card-body">
                    <p style="font-weight:600;color:var(--green-dk);margin-bottom:.75rem">
                        <i class="fa-solid fa-lightbulb"></i> Petunjuk Penggunaan Portal Pasien:
                    </p>
                    <ul style="margin-left:1.25rem;color:var(--g600);line-height:1.9;font-size:.85rem">
                        <li>Lihat riwayat pengobatan Anda di menu <strong>Riwayat Obat</strong></li>
                        <li>Pastikan selalu mengikuti dosis yang tertera</li>
                        <li>Hubungi apotek atau dokter jika ada pertanyaan</li>
                        <li>Simpan username dan password Anda dengan aman</li>
                    </ul>
                    <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--g100)">
                        <p style="font-weight:600;color:var(--g800);margin-bottom:.4rem">
                            <i class="fa-solid fa-phone" style="color:var(--green)"></i> Kontak Apotek
                        </p>
                        <p style="font-size:.84rem;color:var(--g600)">
                            Telepon: 0821-XXXX-XXXX<br>Email: info@apoteksehat.com
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<script>
/* Fungsi JavaScript pemicu event buka/tutup menu navigasi samping (Sidebar Mobile) */
function toggleSidebar(){
    // Menambah atau menghapus class 'open' pada komponen bersangkutan
    document.querySelector('.sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('show');
}
</script>
</body>
</html>