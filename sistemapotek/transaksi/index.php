<?php
// ============================================================
// transaksi/index.php — Daftar Pemberian Obat
// ============================================================
// Menyertakan file keamanan auth.php untuk memastikan user telah terautentikasi (login)
require_once '../auth.php';
// Menyertakan berkas koneksi untuk berinteraksi dengan database MySQL
require_once '../koneksi.php';

// Menangkap parameter pesan (msg) dan tipe alert (type) dari URL (metode GET) untuk sistem notifikasi
$msg    = $_GET['msg']    ?? '';
$type   = $_GET['type']   ?? 'success';
// Menangkap kata kunci pencarian, sekaligus menghapus spasi di awal dan akhir teks
$search = trim($_GET['search'] ?? '');
$where  = '';

// Jika kolom pencarian diisi, buat klausa WHERE untuk memfilter nama pasien atau nama obat
if ($search !== '') {
    // Mengamankan input pencarian dari celah keamanan SQL Injection
    $s     = $koneksi->real_escape_string($search);
    $where = "WHERE p.nama_pasien LIKE '%$s%' OR o.nama_obat LIKE '%$s%'";
}

// Menjalankan query untuk mengambil data transaksi dengan menggabungkan tabel pasien dan obat (Relasi database)
$result = $koneksi->query(
    "SELECT po.*, p.nama_pasien, o.nama_obat, o.harga
     FROM pemberian_obat po
     JOIN pasien p ON po.id_pasien = p.id_pasien
     JOIN obat   o ON po.id_obat   = o.id_obat
     $where
     ORDER BY po.id_transaksi DESC"
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemberian Obat — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/sidebar_extra.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="app-wrap">
    <?php include '../sidebar.php'; ?>
    <div class="main-content">

        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
                <div class="topbar-title">
                    <h2>Pemberian Obat</h2>
                    <div class="topbar-breadcrumb">
                        <a href="../dashboard.php">Dashboard</a> <span class="sep">/</span>
                        <span class="cur">Pemberian Obat</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-info"><span class="topbar-dot"></span><?= date('d M Y') ?></div>
            </div>
        </div>

        <div class="page-body">

            <?php if ($msg): ?>
            <div class="alert alert-<?= $type === 'success' ? 'success' : 'danger' ?>">
                <i class="fa-solid fa-circle-check"></i>
                <?php
                // Array asosiatif bertindak sebagai kamus penerjemah kode pesan menjadi teks bahasa Indonesia
                $pesan = [
                    'tambah_ok'  => 'Pemberian obat berhasil dicatat.',
                    'edit_ok'    => 'Transaksi berhasil diperbarui.',
                    'hapus_ok'   => 'Transaksi berhasil dihapus.',
                    'stok_habis' => 'Gagal: stok obat tidak mencukupi.',
                    'gagal'      => 'Terjadi kesalahan. Silakan coba lagi.',
                ];
                // Menampilkan pesan yang sesuai, atau menampilkan isi asli parameter jika tidak terdaftar di kamus
                echo $pesan[$msg] ?? htmlspecialchars($msg);
                ?>
            </div>
            <?php endif; ?>

            <div class="page-header">
                <?php
                // Menghitung jumlah total baris transaksi hasil query saat ini
                $total_transaksi = $result ? $result->num_rows : 0;

                // Mengambil jumlah transaksi khusus yang terjadi pada tanggal hari ini
                $q_hari_ini = $koneksi->query("
                SELECT COUNT(*) total
                FROM pemberian_obat
                WHERE DATE(tanggal_pemberian)=CURDATE()
                ");
                $transaksi_hari_ini = $q_hari_ini->fetch_assoc()['total'];

                // Mengambil jumlah transaksi khusus pada bulan dan tahun berjalan saat ini
                $q_bulan_ini = $koneksi->query("
                SELECT COUNT(*) total
                FROM pemberian_obat
                WHERE MONTH(tanggal_pemberian)=MONTH(CURDATE())
                AND YEAR(tanggal_pemberian)=YEAR(CURDATE())
                ");
                $transaksi_bulan_ini = $q_bulan_ini->fetch_assoc()['total'];
                ?>
                <div>
                    <h1>Manajemen Transaksi</h1>
                    <p>Kelola dan pantau seluruh transaksi pemberian obat kepada pasien.</p>
                </div>
                <a href="tambah.php" class="btn btn-primary">+ Catat Pemberian</a>
            </div>

            <div class="card">
                <div class="card-head">
                    <div class="card-title"><i class="fa-solid fa-clipboard-list"></i> Daftar Transaksi</div>
                    <form method="GET" class="data-toolbar">
                        <input type="text" name="search" class="form-control"
                            placeholder=" Cari nama pasien atau obat..."
                            value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-ghost btn-sm">Cari</button>
                        <?php if ($search): ?>
                        <a href="index.php" class="btn btn-ghost btn-sm"> Reset</a>
                        <?php endif; ?>
                        <span class="data-count"><?= $result ? $result->num_rows : 0 ?> data</span>
                    </form>
                </div>
                
                <div class="stats-row">
                    <div class="stat-card navy">
                        <div class="stat-info">
                            <div class="stat-num"><?= $total_transaksi ?></div>
                            <div class="stat-label">Total Transaksi</div>
                        </div>
                    </div>
                    <div class="stat-card teal">
                        <div class="stat-info">
                            <div class="stat-num"><?= $transaksi_hari_ini ?></div>
                            <div class="stat-label">Hari Ini</div>
                        </div>
                    </div>
                    <div class="stat-card amber">
                        <div class="stat-info">
                            <div class="stat-num"><?= $transaksi_bulan_ini ?></div>
                            <div class="stat-label">Bulan Ini</div>
                        </div>
                    </div>
                </div>

                <div class="table-wrap">
                    <?php if ($result && $result->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th class="td-no">No</th>
                                <th>Tanggal</th>
                                <th>Pasien</th>
                                <th>Obat</th>
                                <th>Jumlah</th>
                                <th>Dosis</th>
                                <th>Total</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="td-no"><?= $no++ ?></td>
                                <td><?= date('d/m/Y', strtotime($row['tanggal_pemberian'])) ?></td>
                                <td class="td-bold"><?= htmlspecialchars($row['nama_pasien']) ?></td>
                                <td><?= htmlspecialchars($row['nama_obat']) ?></td>
                                <td><span class="badge badge-teal"><?= $row['jumlah'] ?></span></td>
                                <td><?= htmlspecialchars($row['dosis']) ?></td>
                                <td class="td-mono">Rp <?= number_format($row['harga'] * $row['jumlah'], 0, ',', '.') ?></td>
                                <td class="td-muted" style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                    <?= htmlspecialchars($row['keterangan'] ?? '-') ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit.php?id=<?= $row['id_transaksi'] ?>" class="btn btn-amber btn-sm"><i class="fa-solid fa-pen"></i> Edit</a>
                                        <a href="hapus.php?id=<?= $row['id_transaksi'] ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Yakin hapus transaksi ini? Stok obat akan dikembalikan.')">
                                             Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty">
                        <i class="fa-regular fa-clipboard"></i>
                        <p><?= $search ? 'Tidak ada transaksi yang cocok.' : 'Belum ada data pemberian obat.' ?></p>
                        <a href="tambah.php" class="btn btn-primary btn-sm">+ Catat Pemberian Pertama</a>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="card-foot">
                    <span>Total: <strong><?= $result ? $result->num_rows : 0 ?></strong> transaksi</span>
                    <a href="tambah.php" class="btn btn-primary btn-sm">+ Catat Pemberian</a>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<script>
/* Fungsi utilitas JavaScript untuk memicu buka-tutup navigasi sidebar menu mobile */
function toggleSidebar(){
    document.querySelector('.sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('show');
}
</script>
</body>
</html>