<?php
$current      = $_SERVER['PHP_SELF'];
$is_dashboard = strpos($current,'dashboard') !== false;
$is_obat      = strpos($current,'/obat/') !== false;
$is_pasien    = strpos($current,'/pasien/') !== false;
$is_transaksi = strpos($current,'/transaksi/') !== false;
$nama         = $_SESSION['nama_lengkap'] ?? 'User';
$inisial      = strtoupper(substr($nama,0,1));
$role         = ucfirst($_SESSION['role'] ?? 'Admin');
$is_subdir    = ($is_obat||$is_pasien||$is_transaksi);
$base_path    = $is_subdir ? '../' : '';
?>
<aside class="sidebar" id="sidebar">
    <div class="sb-brand">
        <div class="sb-brand-icon"><i class="fa-solid fa-heart-pulse"></i></div>
        <div>
            <div class="sb-brand-name"><?= APP_NAME ?></div>
            <div class="sb-brand-sub">Management System</div>
        </div>
    </div>
    <nav class="sb-nav">
        <div class="sb-group-label">Main Menu</div>
        <a href="<?= $base_path ?>dashboard.php" class="sb-item <?= $is_dashboard?'active':'' ?>">
            <i class="fa-solid fa-gauge"></i> Dashboard
        </a>
        <div class="sb-group-label">Data</div>
        <a href="<?= $base_path ?>obat/index.php" class="sb-item <?= $is_obat?'active':'' ?>">
            <i class="fa-solid fa-pills"></i> Data Obat
        </a>
        <a href="<?= $base_path ?>pasien/index.php" class="sb-item <?= $is_pasien?'active':'' ?>">
            <i class="fa-solid fa-users"></i> Data Pasien
        </a>
        <div class="sb-group-label">Transaksi</div>
        <a href="<?= $base_path ?>transaksi/index.php" class="sb-item <?= $is_transaksi?'active':'' ?>">
            <i class="fa-solid fa-clipboard-list"></i> Transaksi
        </a>
    </nav>
    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar"><?= $inisial ?></div>
            <div>
                <div class="sb-uname"><?= htmlspecialchars($nama) ?></div>
                <div class="sb-urole"><?= $role ?></div>
            </div>
        </div>
        <a href="<?= $base_path ?>logout.php" class="sb-logout">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
</aside>
