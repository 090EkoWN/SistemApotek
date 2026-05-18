<?php
require_once '../auth.php';
require_once '../koneksi.php';

$msg    = $_GET['msg']    ?? '';
$type   = $_GET['type']   ?? 'success';
$search = trim($_GET['search'] ?? '');
$where  = '';

if ($search !== '') {
    $s     = $koneksi->real_escape_string($search);
    $where = "WHERE nama_pasien LIKE '%$s%' OR no_hp LIKE '%$s%'";
}

$result = $koneksi->query("SELECT * FROM pasien $where ORDER BY id_pasien DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pasien — <?= APP_NAME ?></title>
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
                    <h2>Data Pasien</h2>
                    <div class="topbar-breadcrumb">
                        <a href="../dashboard.php">Dashboard</a> <span class="sep">/</span>
                        <span class="cur">Data Pasien</span>
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
                    'tambah_ok' => 'Data pasien berhasil ditambahkan.',
                    'edit_ok'   => 'Data pasien berhasil diperbarui.',
                    'hapus_ok'  => 'Data pasien berhasil dihapus.',
                    'gagal'     => 'Terjadi kesalahan. Silakan coba lagi.',
                ];
                echo $pesan[$msg] ?? htmlspecialchars($msg);
                ?>
            </div>
            <?php endif; ?>

            <div class="page-header">
                <div>
                    <h1>👤 Data Pasien</h1>
                    <p>Kelola seluruh data pasien apotek</p>
                </div>
                <a href="tambah.php" class="btn btn-primary">+ Tambah Pasien</a>
            </div>

            <div class="card">
                <div class="card-head">
                    <div class="card-title"><span class="card-icon">👤</span> Daftar Pasien</div>
                    <form method="GET" class="data-toolbar">
                        <input type="text" name="search" class="form-control"
                            placeholder="🔍 Cari nama atau no HP..."
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
                                <th>Nama Pasien</th>
                                <th>No. HP</th>
                                <th>Tgl. Lahir</th>
                                <th>Alamat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="td-no"><?= $no++ ?></td>
                                <td class="td-bold"><?= htmlspecialchars($row['nama_pasien']) ?></td>
                                <td class="td-mono"><?= htmlspecialchars($row['no_hp']) ?></td>
                                <td><?= date('d/m/Y', strtotime($row['tanggal_lahir'])) ?></td>
                                <td class="td-muted" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                                    <?= htmlspecialchars($row['alamat']) ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit.php?id=<?= $row['id_pasien'] ?>" class="btn btn-amber btn-sm">✏️ Edit</a>
                                        <a href="hapus.php?id=<?= $row['id_pasien'] ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Yakin hapus pasien <?= htmlspecialchars(addslashes($row['nama_pasien'])) ?>?')">
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
                        <span class="empty-icon">👤</span>
                        <p><?= $search ? 'Tidak ada pasien yang cocok.' : 'Belum ada data pasien.' ?></p>
                        <a href="tambah.php" class="btn btn-primary btn-sm">+ Tambah Pasien</a>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="card-foot">
                    <span>Total: <strong><?= $result ? $result->num_rows : 0 ?></strong> pasien</span>
                    <a href="tambah.php" class="btn btn-primary btn-sm">+ Tambah Pasien</a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>
<script>function toggleSidebar(){document.querySelector('.sidebar').classList.toggle('open');document.getElementById('overlay').classList.toggle('show');}</script>
</body>
</html>