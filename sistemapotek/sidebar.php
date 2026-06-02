<?php
// ============================================================
// sidebar.php — Komponen Sidebar
// ============================================================

$current     = $_SERVER['PHP_SELF'];
$is_dashboard  = strpos($current, 'dashboard') !== false;
$is_obat       = strpos($current, '/obat/') !== false;
$is_pasien     = strpos($current, '/pasien/') !== false;
$is_transaksi  = strpos($current, '/transaksi/') !== false;

$nama    = $_SESSION['nama_lengkap'] ?? 'User';
$inisial = strtoupper(substr($nama, 0, 1));

// ─── Deteksi base path yang robust untuk semua environment ───
// Strategi: hitung posisi folder app dari SCRIPT_NAME
// Contoh SCRIPT_NAME: /sistemapotek/obat/index.php
// Contoh SCRIPT_NAME di hosting: /obat/index.php
// Contoh SCRIPT_NAME nested: /tugasakhir/sistemapotek/obat/index.php
$script = $_SERVER['SCRIPT_NAME'];
$depth  = substr_count($script, '/') - 1; // kedalaman folder file ini
// Kalau di dalam subfolder (obat/, pasien/, transaksi/) maka naik 1 level
$is_subdir = ($is_obat || $is_pasien || $is_transaksi);
$base_path = $is_subdir ? '../' : '';
?>

<aside class="sidebar" id="sidebar">

    <div class="sb-brand">
        <div class="sb-brand-icon">
            AP
        </div>
        <div>
            <div class="sb-brand-name"><?= APP_NAME ?></div>
            <div class="sb-brand-sub">Sistem Apotek</div>
        </div>
    </div>

    <nav class="sb-nav">
        <div class="sb-group-label">MENU UTAMA</div>
        <a href="<?= $base_path ?>dashboard.php"
        class="sb-item <?= $is_dashboard ? 'active' : '' ?>">
        <span class="sb-icon">▣</span>
             Dashboard
        </a>

        <a href="<?= $base_path ?>obat/index.php"
        class="sb-item <?= $is_obat ? 'active' : '' ?>">
        <span class="sb-icon">◉</span>
            Data Obat
        </a>

        <a href="<?= $base_path ?>pasien/index.php"
        class="sb-item <?= $is_pasien ? 'active' : '' ?>">
        <span class="sb-icon">◌</span>
            Data Pasien
        </a>

        <a href="<?= $base_path ?>transaksi/index.php"
        class="sb-item <?= $is_transaksi ? 'active' : '' ?>">
        <span class="sb-icon">▤</span>
            Pemberian Obat
        </a>
    </nav>

    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar"><?= $inisial ?></div>
            <div>
                <div class="sb-uname"><?= htmlspecialchars($nama) ?></div>
                <div class="sb-urole">
                    <?= ucfirst($_SESSION['role'] ?? 'Admin') ?>
                </div>
            </div>
        </div>
        <a href="<?= $base_path ?>logout.php" class="sb-logout">
            Keluar
        </a>
    </div>

</aside>
