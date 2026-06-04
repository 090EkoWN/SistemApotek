<?php
require_once 'auth.php';
require_once 'koneksi.php';
$jml_obat      = $koneksi->query("SELECT COUNT(*) total FROM obat")->fetch_assoc()['total'];
$jml_pasien    = $koneksi->query("SELECT COUNT(*) total FROM pasien")->fetch_assoc()['total'];
$jml_transaksi = $koneksi->query("SELECT COUNT(*) total FROM pemberian_obat")->fetch_assoc()['total'];
$stok_habis    = $koneksi->query("SELECT COUNT(*) total FROM obat WHERE stok=0")->fetch_assoc()['total'];
$transaksi_baru = $koneksi->query("SELECT po.id_transaksi,p.nama_pasien,o.nama_obat,po.jumlah,po.tanggal_pemberian FROM pemberian_obat po JOIN pasien p ON po.id_pasien=p.id_pasien JOIN obat o ON po.id_obat=o.id_obat ORDER BY po.id_transaksi DESC LIMIT 5");
$hampir_expired = $koneksi->query("SELECT nama_obat,stok,tanggal_expired FROM obat WHERE tanggal_expired<=DATE_ADD(CURDATE(),INTERVAL 30 DAY) AND tanggal_expired>=CURDATE() ORDER BY tanggal_expired ASC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Dashboard — <?= APP_NAME ?></title>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/sidebar_extra.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="app-wrap">
<?php include 'sidebar.php'; ?>
<div class="main-content">

    <div class="topbar">
        <div class="topbar-left">
            <button class="hamburger" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
            <div class="topbar-title">
                <h2>Dashboard</h2>
                <div class="topbar-breadcrumb">
                    <span><?= APP_NAME ?></span>
                    <span class="sep">/</span>
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
                <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?></h1>
                <p>Ringkasan data sistem apotek hari ini</p>
            </div>
        </div>

        <div class="stats-row">
            <div class="stat-card teal">
                <div class="stat-icon"><i class="fa-solid fa-pills"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= $jml_obat ?></div>
                    <div class="stat-label">Total Data Obat</div>
                </div>
            </div>
            <div class="stat-card navy">
                <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= $jml_pasien ?></div>
                    <div class="stat-label">Total Pasien</div>
                </div>
            </div>
            <div class="stat-card amber">
                <div class="stat-icon"><i class="fa-solid fa-clipboard-list"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= $jml_transaksi ?></div>
                    <div class="stat-label">Total Transaksi</div>
                </div>
            </div>
            <div class="stat-card red">
                <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="stat-info">
                    <div class="stat-num"><?= $stok_habis ?></div>
                    <div class="stat-label">Stok Habis</div>
                </div>
            </div>
        </div>

        <div class="dash-grid">
            <div class="card">
                <div class="card-head">
                    <div class="card-title">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        Transaksi Terbaru
                    </div>
                    <a href="transaksi/index.php" class="btn btn-sm btn-ghost">Lihat Semua <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                <div class="table-wrap">
                    <?php if ($transaksi_baru&&$transaksi_baru->num_rows>0): ?>
                    <table>
                        <thead><tr><th>Pasien</th><th>Obat</th><th>Jumlah</th><th>Tanggal</th></tr></thead>
                        <tbody>
                        <?php while ($row=$transaksi_baru->fetch_assoc()): ?>
                        <tr>
                            <td class="td-bold"><?= htmlspecialchars($row['nama_pasien']) ?></td>
                            <td><?= htmlspecialchars($row['nama_obat']) ?></td>
                            <td><span class="badge badge-teal"><?= $row['jumlah'] ?></span></td>
                            <td class="td-muted"><?= date('d/m/Y',strtotime($row['tanggal_pemberian'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty">
                        <i class="fa-regular fa-clipboard"></i>
                        <p>Belum ada transaksi pemberian obat</p>
                        <a href="transaksi/tambah.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Tambah Transaksi</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-head">
                    <div class="card-title">
                        <i class="fa-solid fa-calendar-xmark"></i>
                        Akan Expired (30 Hari)
                    </div>
                    <a href="obat/index.php" class="btn btn-sm btn-ghost">Kelola <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                <?php if ($hampir_expired&&$hampir_expired->num_rows>0): ?>
                    <?php while ($row=$hampir_expired->fetch_assoc()): $sisa=ceil((strtotime($row['tanggal_expired'])-time())/86400); ?>
                    <div class="list-item">
                        <div class="list-label"><i class="fa-solid fa-capsules"></i> <?= htmlspecialchars($row['nama_obat']) ?></div>
                        <span class="badge badge-red"><?= $sisa ?> hari</span>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                <div class="empty">
                    <i class="fa-solid fa-circle-check"></i>
                    <p>Semua obat masih dalam masa berlaku</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <div class="card-title"><i class="fa-solid fa-bolt"></i> Akses Cepat</div>
            </div>
            <div class="card-body">
                <div class="quick-links">
                    <a href="obat/tambah.php" class="quick-link-item ql-teal">
                        <i class="fa-solid fa-pills"></i>
                        <strong>Tambah Obat</strong>
                        <em>Input stok obat baru</em>
                    </a>
                    <a href="pasien/tambah.php" class="quick-link-item ql-navy">
                        <i class="fa-solid fa-user-plus"></i>
                        <strong>Tambah Pasien</strong>
                        <em>Daftar pasien baru</em>
                    </a>
                    <a href="transaksi/tambah.php" class="quick-link-item ql-amber">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <strong>Pemberian Obat</strong>
                        <em>Catat transaksi baru</em>
                    </a>
                    <a href="obat/index.php" class="quick-link-item ql-gray">
                        <i class="fa-solid fa-box-open"></i>
                        <strong>Data Obat</strong>
                        <em>Lihat semua stok obat</em>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>
<script>
function toggleSidebar(){
    document.querySelector('.sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('show');
}
</script>
</body>
</html>
