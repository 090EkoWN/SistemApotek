<?php
// ============================================================
// sidebar.php — Komponen Sidebar (dipakai semua halaman)
// ============================================================

// Tentukan halaman aktif berdasarkan URL
$current = $_SERVER['PHP_SELF'];
$is_dashboard  = strpos($current, 'dashboard') !== false;
$is_obat       = strpos($current, '/obat/') !== false;
$is_pasien     = strpos($current, '/pasien/') !== false;
$is_transaksi  = strpos($current, '/transaksi/') !== false;

// Hitung inisial nama untuk avatar
$nama = $_SESSION['nama_lengkap'] ?? 'User';
$inisial = strtoupper(substr($nama, 0, 1));

// Deteksi base path aplikasi - ambil sampai folder aplikasi
$script_path = $_SERVER['SCRIPT_NAME'];
$base_path = '/';

// Cari posisi folder aplikasi dan ambil full path sebelumnya
// Contoh: /tugasakhir/SistemApotek1/sistemapotek/dashboard.php -> /tugasakhir/SistemApotek1/sistemapotek/
if (preg_match('#^(.*?/(sistemapotek|apotek|apotek_fix))/#', $script_path, $matches)) {
    $base_path = $matches[1] . '/';
} elseif (preg_match('#^(.*?sistemapotek)/#', $script_path, $matches)) {
    $base_path = $matches[1] . '/';
}
?>

<aside class="sidebar" id="sidebar">

    <!-- Brand -->
    <div class="sb-brand">
        <div class="sb-brand-icon">💊</div>
        <div>
            <div class="sb-brand-name"><?= APP_NAME ?></div>
            <div class="sb-brand-sub">Sistem Apotek</div>
        </div>
    </div>

    <!-- Navigasi -->
    <nav class="sb-nav">

        <div class="sb-group-label">MENU UTAMA</div>

        <a href="<?= $base_path ?>dashboard.php"
           class="sb-item <?= $is_dashboard ? 'active' : '' ?>">
            <span class="sb-icon">🏠</span>
            Dashboard
        </a>

        <div class="sb-group-label">DATA MASTER</div>

        <a href="<?= $base_path ?>obat/index.php"
           class="sb-item <?= $is_obat ? 'active' : '' ?>">
            <span class="sb-icon">💊</span>
            Data Obat
        </a>

        <a href="<?= $base_path ?>pasien/index.php"
           class="sb-item <?= $is_pasien ? 'active' : '' ?>">
            <span class="sb-icon">👤</span>
            Data Pasien
        </a>

        <div class="sb-group-label">TRANSAKSI</div>

        <a href="<?= $base_path ?>transaksi/index.php"
           class="sb-item <?= $is_transaksi ? 'active' : '' ?>">
            <span class="sb-icon">📋</span>
            Pemberian Obat
        </a>

    </nav>

    <!-- Footer: user info & logout -->
    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar"><?= $inisial ?></div>
            <div>
                <div class="sb-uname"><?= htmlspecialchars($nama) ?></div>
                <div class="sb-urole">Administrator</div>
            </div>
        </div>
        <a href="<?= $base_path ?>logout.php" class="sb-logout">
            🚪 Keluar
        </a>
    </div>

</aside>