<?php
// ============================================================
// pasien_dashboard.php — Dashboard untuk Role Pasien
// ============================================================
require_once 'auth.php';
require_once 'koneksi.php';

// Cek apakah user adalah pasien
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pasien') {
    header('Location: dashboard.php'); // Redirect ke admin dashboard
    exit();
}

$id_user = $_SESSION['id_user'];

// Cari data pasien yang terhubung dengan user ini
$stmt = $koneksi->prepare("SELECT * FROM pasien WHERE id_user = ?");
$stmt->bind_param('i', $id_user);
$stmt->execute();
$data_pasien = $stmt->get_result()->fetch_assoc();
$stmt->close();

$id_pasien = $data_pasien['id_pasien'] ?? null;

// Statistik untuk pasien
$total_transaksi = 0;
$total_obat_diterima = 0;

if ($id_pasien) {
    $res = $koneksi->query("SELECT COUNT(*) as total FROM pemberian_obat WHERE id_pasien = $id_pasien");
    $total_transaksi = $res->fetch_assoc()['total'];
    
    $res = $koneksi->query("SELECT SUM(jumlah) as total FROM pemberian_obat WHERE id_pasien = $id_pasien");
    $total_obat_diterima = $res->fetch_assoc()['total'] ?? 0;
}

// Riwayat pemberian obat untuk pasien ini (5 terakhir)
$riwayat = null;
if ($id_pasien) {
    $riwayat = $koneksi->query(
        "SELECT po.*, o.nama_obat, o.kategori
         FROM pemberian_obat po
         JOIN obat o ON po.id_obat = o.id_obat
         WHERE po.id_pasien = $id_pasien
         ORDER BY po.tanggal_pemberian DESC
         LIMIT 5"
    );
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pasien — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="app-wrap">

    <!-- SIDEBAR KHUSUS PASIEN -->
    <aside class="sidebar" id="sidebar">
        <div class="sb-brand">
            <div class="sb-brand-icon">PS</div>
            <div>
                <div class="sb-brand-name"><?= APP_NAME ?></div>
                <div class="sb-brand-sub">Portal Pasien</div>
            </div>
        </div>

        <nav class="sb-nav">
            <div class="sb-group-label">MENU PASIEN</div>
            
            <a href="pasien_dashboard.php" class="sb-item active">
                <span class="sb-icon">▣</span>
                Dashboard
            </a>
            
            <a href="pasien_riwayat.php" class="sb-item">
                <span class="sb-icon">▤</span>
                Riwayat Obat
            </a>
            
            <a href="pasien_profil.php" class="sb-item">
                <span class="sb-icon">◌</span>
                Profil Saya
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
            <a href="logout.php" class="sb-logout">
                Keluar
            </a>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <div class="topbar-title">
                    <h2>Dashboard Pasien</h2>
                    <div class="topbar-breadcrumb">
                        <span><?= APP_NAME ?></span>
                        <span class="sep"> / </span>
                        <span class="cur">Dashboard</span>
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
                    <h1>
                        Portal Pasien
                    </h1>
                    <p>
                        Pantau riwayat pengobatan dan informasi kesehatan Anda.
                    </p>
                </div>
            </div>

            <?php if (!$id_pasien): ?>
            <!-- Peringatan jika data pasien belum terhubung -->
            <div class="alert alert-warning">
                <span class="alert-icon">!</span>
                <div>
                    <strong>Data Belum Lengkap</strong><br>
                    Akun Anda belum terhubung dengan data pasien. Silakan hubungi administrator untuk melengkapi data Anda.
                </div>
            </div>
            <?php endif; ?>

            <!-- Statistik -->
            <div class="stats-row">
                <div class="stat-card teal">
                    <div class="stat-icon">RJ</div>
                    <div class="stat-info">
                        <div class="stat-num"><?= $total_transaksi ?></div>
                        <div class="stat-label">Total Kunjungan</div>
                    </div>
                </div>
                <div class="stat-card navy">
                    <div class="stat-icon">OB</div>
                    <div class="stat-info">
                        <div class="stat-num"><?= $total_obat_diterima ?></div>
                        <div class="stat-label">Obat Diterima</div>
                    </div>
                </div>
            </div>

            <?php if ($id_pasien && $data_pasien): ?>
            <!-- Informasi Pasien -->
            <div class="card">
                <div class="card-head">
                    <div class="card-title">
                        Informasi Pribadi
                    </div>
                    <a href="pasien_profil.php" class="btn btn-sm btn-ghost">Edit Profil →</a>
                </div>
                <div class="card-body">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:1.5rem">
                        <div>
                            <div style="font-size:0.85rem;color:#64748b;margin-bottom:0.3rem">Nama Lengkap</div>
                            <div style="font-weight:600"><?= htmlspecialchars($data_pasien['nama_pasien']) ?></div>
                        </div>
                        <div>
                            <div style="font-size:0.85rem;color:#64748b;margin-bottom:0.3rem">Tanggal Lahir</div>
                            <div style="font-weight:600"><?= date('d F Y', strtotime($data_pasien['tanggal_lahir'])) ?></div>
                        </div>
                        <div>
                            <div style="font-size:0.85rem;color:#64748b;margin-bottom:0.3rem">No. HP</div>
                            <div style="font-weight:600"><?= htmlspecialchars($data_pasien['no_hp']) ?></div>
                        </div>
                        <div>
                            <div style="font-size:0.85rem;color:#64748b;margin-bottom:0.3rem">Alamat</div>
                            <div style="font-weight:600"><?= htmlspecialchars($data_pasien['alamat']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Riwayat Pemberian Obat Terbaru -->
            <div class="card">
                <div class="card-head">
                    <div class="card-title">
                        Riwayat Pemberian Obat Terbaru
                    </div>
                    <a href="pasien_riwayat.php" class="btn btn-sm btn-ghost">Lihat Semua →</a>
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
                                <td class="td-bold"><?= date('d/m/Y', strtotime($row['tanggal_pemberian'])) ?></td>
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
                        <p>Belum ada riwayat pemberian obat</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Informasi Penting -->
            <div class="card">
                <div class="card-head">
                    <div class="card-title">
                        Informasi Penting
                    </div>
                </div>
                <div class="card-body">
                    <div style="color:#64748b;line-height:1.8">
                        <p><strong> Petunjuk Penggunaan Portal Pasien:</strong></p>
                        <ul style="margin-left:1.5rem">
                            <li>Anda dapat melihat riwayat pengobatan Anda di menu <strong>"Riwayat Obat"</strong></li>
                            <li>Pastikan selalu mengikuti dosis yang tertera</li>
                            <li>Jika ada pertanyaan, hubungi apotek atau dokter Anda</li>
                            <li>Simpan username dan password Anda dengan aman</li>
                        </ul>
                        
                        <p style="margin-top:1rem"><strong>📞 Kontak Apotek:</strong></p>
                        <p>Telepon: 0821-XXXX-XXXX<br>
                        Email: info@apoteksehat.com</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('show');
}
</script>
</body>
</html>