<?php
// ============================================================
// dashboard.php — Halaman Dashboard Utama
// ============================================================
require_once 'auth.php';        // cek session
require_once 'koneksi.php';

// Ambil statistik jumlah data
$jml_obat     = $koneksi->query("SELECT COUNT(*) AS total FROM obat")->fetch_assoc()['total'];
$jml_pasien   = $koneksi->query("SELECT COUNT(*) AS total FROM pasien")->fetch_assoc()['total'];
$jml_transaksi= $koneksi->query("SELECT COUNT(*) AS total FROM pemberian_obat")->fetch_assoc()['total'];
$stok_habis   = $koneksi->query("SELECT COUNT(*) AS total FROM obat WHERE stok = 0")->fetch_assoc()['total'];

// 5 transaksi terbaru
$transaksi_baru = $koneksi->query(
    "SELECT po.id_transaksi, p.nama_pasien, o.nama_obat, po.jumlah, po.tanggal_pemberian
     FROM pemberian_obat po
     JOIN pasien p ON po.id_pasien = p.id_pasien
     JOIN obat   o ON po.id_obat   = o.id_obat
     ORDER BY po.id_transaksi DESC LIMIT 5"
);

// Obat hampir expired (30 hari ke depan)
$hampir_expired = $koneksi->query(
    "SELECT nama_obat, stok, tanggal_expired
     FROM obat
     WHERE tanggal_expired <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
       AND tanggal_expired >= CURDATE()
     ORDER BY tanggal_expired ASC LIMIT 5"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="app-wrap">

    <!-- ── SIDEBAR ── -->
    <?php include 'sidebar.php'; ?>

    <!-- ── KONTEN UTAMA ── -->
    <div class="main-content">

        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <div class="topbar-title">
                    <h2>Dashboard</h2>
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

        <!-- Body -->
        <div class="page-body">

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>
                        Selamat Datang,
                        <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>
                    </h1>
                    <p>Ringkasan data sistem apotek hari ini</p>
                </div>
            </div>

            <!-- Statistik -->
            <div class="stats-row">
                <div class="stat-card teal">
                    <div class="stat-icon">OB</div>
                    <div class="stat-info">
                        <div class="stat-num"><?= $jml_obat ?></div>
                        <div class="stat-label">Total Data Obat</div>
                    </div>
                </div>
                <div class="stat-card navy">
                    <div class="stat-icon">PS</div>
                    <div class="stat-info">
                        <div class="stat-num"><?= $jml_pasien ?></div>
                        <div class="stat-label">Total Pasien</div>
                    </div>
                </div>
                <div class="stat-card amber">
                    <div class="stat-icon">TR</div>
                    <div class="stat-info">
                        <div class="stat-num"><?= $jml_transaksi ?></div>
                        <div class="stat-label">Total Transaksi</div>
                    </div>
                </div>
                <div class="stat-card red">
                    <div class="stat-icon">ST</div>
                    <div class="stat-info">
                        <div class="stat-num"><?= $stok_habis ?></div>
                        <div class="stat-label">Stok Habis</div>
                    </div>
                </div>
            </div>

            <!-- Grid: Transaksi Terbaru + Hampir Expired -->
            <div class="dash-grid">

                <!-- Transaksi Terbaru -->
                <div class="card">
                    <div class="card-head">
                        <div class="card-title">
                            <span class="card-icon"></span>
                            Transaksi Pemberian Obat Terbaru
                        </div>
                        <a href="transaksi/index.php" class="btn btn-sm btn-ghost">Lihat Semua →</a>
                    </div>
                    <div class="table-wrap">
                        <?php if ($transaksi_baru && $transaksi_baru->num_rows > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Pasien</th>
                                    <th>Obat</th>
                                    <th>Jml</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $transaksi_baru->fetch_assoc()): ?>
                                <tr>
                                    <td class="td-bold"><?= htmlspecialchars($row['nama_pasien']) ?></td>
                                    <td><?= htmlspecialchars($row['nama_obat']) ?></td>
                                    <td><span class="badge badge-teal"><?= $row['jumlah'] ?></span></td>
                                    <td class="td-muted"><?= date('d/m/Y', strtotime($row['tanggal_pemberian'])) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <div class="empty">
                            <span class="empty-icon">—</span>
                            <p>Belum ada transaksi pemberian obat</p>
                            <a href="transaksi/tambah.php" class="btn btn-primary btn-sm">+ Tambah Transaksi</a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Obat Hampir Expired -->
                <div class="card">
                    <div class="card-head">
                        <div class="card-title">
                            Obat Akan Expired (30 Hari)
                        </div>
                        <a href="obat/index.php" class="btn btn-sm btn-ghost">Kelola →</a>
                    </div>
                    <?php if ($hampir_expired && $hampir_expired->num_rows > 0): ?>
                        <?php while ($row = $hampir_expired->fetch_assoc()):
                            $sisa = (strtotime($row['tanggal_expired']) - time()) / 86400;
                        ?>
                        <div class="list-item">
                            <div class="list-label"><?= htmlspecialchars($row['nama_obat']) ?></div>
                            <div class="list-val">
                                <span class="badge badge-red"><?= ceil($sisa) ?> hari</span>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                    <div class="empty">
                        <span class="empty-icon">✓</span>
                        <p>Semua obat masih dalam masa berlaku</p>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
            <!-- /dash-grid -->

            <!-- Akses Cepat -->
            <div class="card">
                <div class="card-head">
                    <div class="card-title">
                        <span class="card-icon"></span>
                        Akses Cepat
                    </div>
                </div>
                <div class="card-body">
                    <div class="quick-links">
                        <a href="obat/tambah.php" class="quick-link-item ql-teal">
                            <div class="card-title">
                        
                            </div>
                            <strong>Tambah Obat</strong>
                            <em>Input stok obat baru</em>
                        </a>
                        <a href="pasien/tambah.php" class="quick-link-item ql-navy">
                            <strong>Tambah Pasien</strong>
                            <em>Daftar pasien baru</em>
                        </a>
                        <a href="transaksi/tambah.php" class="quick-link-item ql-amber">
                            <strong>Pemberian Obat</strong>
                            <em>Catat transaksi baru</em>
                        </a>
                        <a href="obat/index.php" class="quick-link-item ql-gray">
                            <strong>Data Obat</strong>
                            <em>Lihat semua stok obat</em>
                        </a>
                    </div>
                </div>
            </div>

        </div>
        <!-- /page-body -->

    </div>
    <!-- /main-content -->

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