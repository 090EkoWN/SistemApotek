<?php
// ============================================================
// pasien/index.php — Daftar Data Pasien
// ============================================================
require_once '../auth.php';
require_once '../koneksi.php';

$msg    = $_GET['msg']    ?? '';
$type   = $_GET['type']   ?? 'success';
$search = trim($_GET['search'] ?? '');
$where  = '';

if ($search !== '') {
    $s     = $koneksi->real_escape_string($search);
    $where = "WHERE p.nama_pasien LIKE '%$s%' OR p.no_hp LIKE '%$s%'";
}

// JOIN dengan users untuk tampilkan username (jika kolom id_user ada)
$col_check = $koneksi->query("SHOW COLUMNS FROM pasien LIKE 'id_user'");
$has_id_user = ($col_check && $col_check->num_rows > 0);

if ($has_id_user) {
    $result = $koneksi->query(
        "SELECT p.*, u.username FROM pasien p
         LEFT JOIN users u ON p.id_user = u.id_user
         $where ORDER BY p.id_pasien DESC"
    );
} else {
    $result = $koneksi->query("SELECT * FROM pasien p $where ORDER BY id_pasien DESC");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pasien — <?= APP_NAME ?></title>
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
                <i class="fa-solid fa-circle-check"></i>
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
                    <h1>Manajemen Pasien</h1>
                    <p>Kelola seluruh data pasien apotek</p>
                </div>
                <a href="tambah.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Tambah Pasien</a>
            </div>

            <div class="card">
                <div class="card-head">
                    <div class="card-title"><i class="fa-solid fa-users"></i> Daftar Pasien</div>
                    <form method="GET" class="data-toolbar">
                        <input type="text" name="search" class="form-control"
                            placeholder="Cari nama pasien atau nomor telepon..."
                            value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-ghost btn-sm">Cari</button>
                        <?php if ($search): ?>
                        <a href="index.php" class="btn btn-ghost btn-sm">Reset</a>
                        <?php endif; ?>
                        <span class="data-count"><?= $result ? $result->num_rows : 0 ?> data</span>
                    </form>
                </div>
                <?php

                $total_pasien =
                $result
                ? $result->num_rows
                : 0;

                $q_login = $koneksi->query("
                SELECT COUNT(*) total
                FROM pasien
                WHERE id_user IS NOT NULL
                ");

                $pasien_login =
                $q_login->fetch_assoc()['total'];

                $q_nonlogin = $koneksi->query("
                SELECT COUNT(*) total
                FROM pasien
                WHERE id_user IS NULL
                ");

                $pasien_nonlogin =
                $q_nonlogin->fetch_assoc()['total'];

                ?>
                <div class="stats-row">

                    <div class="stat-card navy">
                        <div class="stat-info">
                            <div class="stat-num">
                                <?= $total_pasien ?>
                            </div>
                            <div class="stat-label">
                                Total Pasien
                            </div>
                        </div>
                    </div>

                    <div class="stat-card teal">
                        <div class="stat-info">
                            <div class="stat-num">
                                <?= $pasien_login ?>
                            </div>
                            <div class="stat-label">
                                Akun Aktif
                            </div>
                        </div>
                    </div>

                    <div class="stat-card amber">
                        <div class="stat-info">
                            <div class="stat-num">
                                <?= $pasien_nonlogin ?>
                            </div>
                            <div class="stat-label">
                                Belum Terhubung
                            </div>
                        </div>
                    </div>

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
                                <?php if ($has_id_user): ?>
                                <th>Akun Login</th>
                                <?php endif; ?>
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
                                <?php if ($has_id_user): ?>
                                    
                                <td>
                                    <?php if (!empty($row['username'])): ?>
                                    <span class="badge badge-teal">Aktif</span>
                                    <?php else: ?>
                                    <span class="badge badge-navy" style="opacity:.6">— Belum ada</span>
                                    <?php endif; ?>
                                    <div class="td-muted">
                                    <?= htmlspecialchars($row['username']) ?>
                                    </div>
                                </td>
                                
                                <?php endif; ?>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit.php?id=<?= $row['id_pasien'] ?>" class="btn btn-amber btn-sm"><i class="fa-solid fa-pen"></i> Edit</a>
                                        <a href="hapus.php?id=<?= $row['id_pasien'] ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Yakin hapus pasien ini?')">
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
                        <p><?= $search ? 'Tidak ada pasien yang cocok.' : 'Belum ada data pasien.' ?></p>
                        <a href="tambah.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Tambah Pasien Pertama</a>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="card-foot">
                    <span>Total: <strong><?= $result ? $result->num_rows : 0 ?></strong> pasien</span>
                    <a href="tambah.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Tambah Pasien</a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>
<script>function toggleSidebar(){document.querySelector('.sidebar').classList.toggle('open');document.getElementById('overlay').classList.toggle('show');}</script>
</body>
</html>
