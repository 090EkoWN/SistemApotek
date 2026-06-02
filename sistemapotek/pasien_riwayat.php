<?php
// ============================================================
// pasien_riwayat.php — Riwayat Pemberian Obat (Pasien)
// ============================================================
require_once 'auth.php';
require_once 'koneksi.php';

// Cek role pasien
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pasien') {
    header('Location: dashboard.php');
    exit();
}

$id_user = $_SESSION['id_user'];

// Cari id_pasien
$stmt = $koneksi->prepare("SELECT id_pasien FROM pasien WHERE id_user = ?");
$stmt->bind_param('i', $id_user);
$stmt->execute();
$result = $stmt->get_result();
$pasien_data = $result->fetch_assoc();
$stmt->close();

$id_pasien = $pasien_data['id_pasien'] ?? null;

// Search
$search = trim($_GET['search'] ?? '');
$where_clause = $id_pasien ? "WHERE po.id_pasien = $id_pasien" : "WHERE 1=0";

if ($search) {
    $search_safe = $koneksi->real_escape_string($search);
    $where_clause .= " AND (o.nama_obat LIKE '%$search_safe%' OR po.dosis LIKE '%$search_safe%')";
}

// Query riwayat
$query = "SELECT po.*, o.nama_obat, o.kategori
          FROM pemberian_obat po
          JOIN obat o ON po.id_obat = o.id_obat
          $where_clause
          ORDER BY po.tanggal_pemberian DESC";

$riwayat = $koneksi->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Obat — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="app-wrap">

    <!-- SIDEBAR -->
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
            
            <a href="pasien_dashboard.php" class="sb-item">
                <span class="sb-icon">▣</span>
                Dashboard
            </a>
            
            <a href="pasien_riwayat.php" class="sb-item active">
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
            <a href="logout.php" class="sb-logout"> Keluar</a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
                <div class="topbar-title">
                    <h2>Riwayat Pemberian Obat</h2>
                    <div class="topbar-breadcrumb">
                        <a href="pasien_dashboard.php">Dashboard</a>
                        <span class="sep">/</span>
                        <span class="cur">Riwayat Obat</span>
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
                    <h1>Riwayat Pemberian Obat</h1>
                    <p>Daftar seluruh obat yang pernah diberikan kepada Anda.</p>
                </div>
            </div>

            <?php if (!$id_pasien): ?>
            <div class="alert alert-warning">
                <<span class="alert-icon">!</span>
                <div>
                    <strong>Data Tidak Tersedia</strong><br>
                    Akun Anda belum terhubung dengan data pasien. Hubungi administrator.
                </div>
            </div>
            <?php else: ?>

            <!-- Search Bar -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="search-form">
                        <input type="text" name="search" class="form-control" 
                               placeholder=" Cari nama obat atau dosis..." 
                               value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-primary">Cari</button>
                        <?php if ($search): ?>
                        <a href="pasien_riwayat.php" class="btn btn-ghost">Reset</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <div class="stats-row">

            <div class="stat-card teal">
                <div class="stat-info">
                    <div class="stat-num">
                        <?= $riwayat ? $riwayat->num_rows : 0 ?>
                    </div>
                <div class="stat-label">
                    Total Riwayat
                </div>
            </div>
            </div>

            </div>
            <!-- Tabel Riwayat -->
            <div class="card">
                <div class="card-head">
                    <div class="card-title">
                        Daftar Riwayat Pemberian Obat
                        <?php if ($search): ?>
                        <span style="font-size:0.9rem;color:#64748b;font-weight:normal">
                            — Hasil pencarian: "<?= htmlspecialchars($search) ?>"
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="table-wrap">
                    <?php if ($riwayat && $riwayat->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama Obat</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Dosis</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($row = $riwayat->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="td-bold"><?= date('d/m/Y', strtotime($row['tanggal_pemberian'])) ?></td>
                                <td><?= htmlspecialchars($row['nama_obat']) ?></td>
                                <td><span class="badge badge-navy"><?= htmlspecialchars($row['kategori']) ?></span></td>
                                <td><span class="badge badge-teal"><?= $row['jumlah'] ?> pcs</span></td>
                                <td class="td-muted"><?= htmlspecialchars($row['dosis']) ?></td>
                                <td class="td-muted"><?= htmlspecialchars($row['keterangan'] ?: '-') ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty">
                        <span class="empty-icon">—</span>
                        <p><?= $search ? 'Tidak ada hasil pencarian' : 'Belum ada riwayat pemberian obat' ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php endif; ?>
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