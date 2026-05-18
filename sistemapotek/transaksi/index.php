<?php
require_once '../auth.php';
require_once '../koneksi.php';

$msg    = $_GET['msg']    ?? '';
$type   = $_GET['type']   ?? 'success';
$search = trim($_GET['search'] ?? '');
$where  = '';

if ($search !== '') {
    $s     = $koneksi->real_escape_string($search);
    $where = "WHERE p.nama_pasien LIKE '%$s%' OR o.nama_obat LIKE '%$s%'";
}

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
</head>
<body>
<div class="app-wrap">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">

        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
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
                <span class="alert-icon"><?= $type === 'success' ? '✅' : '❌' ?></span>
                <?php
                $pesan = [
                    'tambah_ok' => 'Transaksi pemberian obat berhasil dicatat.',
                    'edit_ok'   => 'Transaksi berhasil diperbarui.',
                    'hapus_ok'  => 'Transaksi berhasil dihapus.',
                    'stok_habis'=> 'Gagal: stok obat tidak mencukupi.',
                    'gagal'     => 'Terjadi kesalahan. Silakan coba lagi.',
                ];
                echo $pesan[$msg] ?? htmlspecialchars($msg);
                ?>
            </div>
            <?php endif; ?>

            <div class="page-header">
                <div>
                    <h1>📋 Pemberian Obat</h1>
                    <p>Catatan transaksi pemberian obat kepada pasien</p>
                </div>
                <a href="tambah.php" class="btn btn-primary">+ Catat Pemberian</a>
            </div>

            <div class="card">
                <div class="card-head">
                    <div class="card-title"><span class="card-icon">📋</span> Daftar Transaksi</div>
                    <form method="GET" class="data-toolbar">
                        <input type="text" name="search" class="form-control"
                            placeholder="🔍 Cari nama pasien atau obat..."
                            value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-ghost btn-sm">Cari</button>
                        <?php if ($search): ?>
                        <a href="index.php" class="btn btn-ghost btn-sm">✕ Reset</a>
                        <?php endif; ?>
                        <span class="data-count"><?= $result ? $result->num_rows : 0 ?> data</span>
                    </form>
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
                                        <a href="edit.php?id=<?= $row['id_transaksi'] ?>" class="btn btn-amber btn-sm">✏️ Edit</a>
                                        <a href="hapus.php?id=<?= $row['id_transaksi'] ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Yakin hapus transaksi ini?')">
                                           🗑️ Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty">
                        <span class="empty-icon">📋</span>
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
<script>function toggleSidebar(){document.querySelector('.sidebar').classList.toggle('open');document.getElementById('overlay').classList.toggle('show');}</script>
</body>
</html>