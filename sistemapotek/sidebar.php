<?php
// Mengambil path/nama file URL yang saat ini sedang diakses (misal: /apotek/obat/index.php)
$current      = $_SERVER['PHP_SELF'];

// Mengecek apakah file yang sedang diakses berada di halaman 'dashboard'
$is_dashboard = strpos($current,'dashboard') !== false;

// Mengecek apakah file yang sedang diakses berada di dalam folder atau sub-direktori '/obat/'
$is_obat      = strpos($current,'/obat/') !== false;

// Mengecek apakah file yang sedang diakses berada di dalam folder atau sub-direktori '/pasien/'
$is_pasien    = strpos($current,'/pasien/') !== false;

// Mengecek apakah file yang sedang diakses berada di dalam folder atau sub-direktori '/transaksi/'
$is_transaksi = strpos($current,'/transaksi/') !== false;

// Mengambil nama lengkap dari session. Jika tidak ada, maka default-nya adalah 'User'
$nama         = $_SESSION['nama_lengkap'] ?? 'User';

// Mengambil huruf pertama dari nama pengguna dan mengubahnya menjadi huruf kapital untuk inisial avatar
$inisial      = strtoupper(substr($nama,0,1));

// Mengambil role/peran dari session dan mengubah huruf pertamanya menjadi kapital (misal: 'admin' jadi 'Admin')
$role         = ucfirst($_SESSION['role'] ?? 'Admin');

// Menentukan status apakah halaman saat ini berada di dalam sub-direktori (obat, pasien, atau transaksi)
$is_subdir    = ($is_obat||$is_pasien||$is_transaksi);

// Jika posisi saat ini di sub-direktori, base_path diatur mundur satu folder '../' untuk menyesuaikan link menu
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