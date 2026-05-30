<?php
// ============================================================
// obat/index.php — Daftar Data Obat
// ============================================================
require_once '../auth.php';
require_once '../koneksi.php';

$msg    = $_GET['msg']    ?? '';
$type   = $_GET['type']   ?? 'success';
$search = trim($_GET['search'] ?? '');
$where  = '';

if ($search !== '') {
    $s     = $koneksi->real_escape_string($search);
    $where = "WHERE nama_obat LIKE '%$s%' OR kategori LIKE '%$s%'";
}

$result = $koneksi->query("SELECT * FROM obat $where ORDER BY id_obat DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Obat — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="app-wrap">
    <?php include '../sidebar.php'; ?>
    <div class="main-content">

        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <div class="topbar-title">
                    <h2>Data Obat</h2>
                    <div class="topbar-breadcrumb">
                        <a href="../dashboard.php">Dashboard</a>
                        <span class="sep">/</span>
                        <span class="cur">Data Obat</span>
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
                    'tambah_ok' => 'Obat berhasil ditambahkan.',
                    'edit_ok'   => 'Data obat berhasil diperbarui.',
                    'hapus_ok'  => 'Obat berhasil dihapus.',
                    'gagal'     => 'Terjadi kesalahan. Silakan coba lagi.',
                ];
                echo $pesan[$msg] ?? htmlspecialchars($msg);
                ?>
            </div>
            <?php endif; ?>

            <div class="page-header">
                <div>
                    <h1>💊 Data Obat</h1>
                    <p>Kelola seluruh data stok obat apotek</p>
                </div>
                <a href="tambah.php" class="btn btn-primary">+ Tambah Obat</a>
            </div>

            <div class="card">
                <div class="card-head">
                    <div class="card-title"><span class="card-icon">💊</span> Daftar Obat</div>
                    <form method="GET" class="data-toolbar">
                        <input type="text" name="search" class="form-control"
                            placeholder="🔍 Cari nama obat atau kategori..."
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
                                <th>Nama Obat</th>
                                <th>Kategori</th>
                                <th>Stok</th>
                                <th>Harga</th>
                                <th>Expired</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while ($row = $result->fetch_assoc()):
                                $expired    = strtotime($row['tanggal_expired']);
                                $sekarang   = time();
                                $sisa_hari  = ($expired - $sekarang) / 86400;
                                $cls_exp    = $sisa_hari < 0 ? 'badge-danger' : ($sisa_hari <= 30 ? 'badge-warning' : 'badge-teal');
                                $cls_stok   = $row['stok'] == 0 ? 'badge-danger' : ($row['stok'] <= 10 ? 'badge-warning' : 'badge-teal');
                            ?>
                            <tr>
                                <td class="td-no"><?= $no++ ?></td>
                                <td class="td-bold"><?= htmlspecialchars($row['nama_obat']) ?></td>
                                <td><span class="badge badge-navy"><?= htmlspecialchars($row['kategori']) ?></span></td>
                                <td><span class="badge <?= $cls_stok ?>"><?= $row['stok'] ?></span></td>
                                <td class="td-mono">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                <td><span class="badge <?= $cls_exp ?>"><?= date('d/m/Y', $expired) ?></span></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit.php?id=<?= $row['id_obat'] ?>" class="btn btn-amber btn-sm">✏️ Edit</a>
                                        <a href="hapus.php?id=<?= $row['id_obat'] ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Yakin hapus obat ini?')">
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
                        <span class="empty-icon">💊</span>
                        <p><?= $search ? 'Tidak ada obat yang cocok.' : 'Belum ada data obat.' ?></p>
                        <a href="tambah.php" class="btn btn-primary btn-sm">+ Tambah Obat Pertama</a>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="card-foot">
                    <span>Total: <strong><?= $result ? $result->num_rows : 0 ?></strong> obat</span>
                    <a href="tambah.php" class="btn btn-primary btn-sm">+ Tambah Obat</a>
                </div>
            </div>

        </div>
    </div>
</div>
<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>
<script>function toggleSidebar(){document.querySelector('.sidebar').classList.toggle('open');document.getElementById('overlay').classList.toggle('show');}</script>
</body>
</html>
