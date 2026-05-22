<?php
require_once '../auth.php';
require_once '../koneksi.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit(); }

// Ambil data obat
$stmt = $koneksi->prepare("SELECT * FROM obat WHERE id_obat = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$obat = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$obat) { header('Location: index.php?msg=gagal&type=danger'); exit(); }

$errors = [];
$data   = $obat; // isi awal dari database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['nama_obat']       = trim($_POST['nama_obat'] ?? '');
    $data['kategori']        = trim($_POST['kategori'] ?? '');
    $data['stok']            = trim($_POST['stok'] ?? '');
    $data['harga']           = trim($_POST['harga'] ?? '');
    $data['tanggal_expired'] = trim($_POST['tanggal_expired'] ?? '');

    if (empty($data['nama_obat']))       $errors[] = 'Nama obat wajib diisi.';
    if (empty($data['kategori']))        $errors[] = 'Kategori wajib diisi.';
    if (!is_numeric($data['stok']) || $data['stok'] < 0) $errors[] = 'Stok harus angka positif.';
    if (!is_numeric($data['harga']) || $data['harga'] < 0) $errors[] = 'Harga harus angka positif.';
    if (empty($data['tanggal_expired'])) $errors[] = 'Tanggal expired wajib diisi.';

    if (empty($errors)) {
        $stmt = $koneksi->prepare(
            "UPDATE obat SET nama_obat=?, kategori=?, stok=?, harga=?, tanggal_expired=? WHERE id_obat=?"
        );
        $stmt->bind_param('ssidsi', $data['nama_obat'], $data['kategori'], $data['stok'], $data['harga'], $data['tanggal_expired'], $id);
        if ($stmt->execute()) {
            header('Location: index.php?msg=edit_ok&type=success');
            exit();
        } else {
            $errors[] = 'Gagal memperbarui: ' . $stmt->error;
        }
        $stmt->close();
    }
}

$kategori_list = ['Analgesik','Antibiotik','Antasida','Vitamin','Antihistamin','Antidiabetik','Antihipertensi','Antiseptik','Lainnya'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Obat — <?= APP_NAME ?></title>
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
                    <h2>Edit Obat</h2>
                    <div class="topbar-breadcrumb">
                        <a href="../dashboard.php">Dashboard</a> <span class="sep">/</span>
                        <a href="index.php">Data Obat</a> <span class="sep">/</span>
                        <span class="cur">Edit</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="page-body">
            <div class="page-header">
                <div>
                    <h1>✏️ Edit Data Obat</h1>
                    <p>Perbarui informasi obat: <strong><?= htmlspecialchars($obat['nama_obat']) ?></strong></p>
                </div>
                <a href="index.php" class="btn btn-ghost">← Kembali</a>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <span class="alert-icon">❌</span>
                <div><strong>Terdapat kesalahan:</strong>
                    <ul style="margin:.3rem 0 0 1rem">
                        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-head">
                    <div class="card-title"><span class="card-icon">📝</span> Form Edit Obat</div>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nama_obat">Nama Obat <span style="color:var(--red)">*</span></label>
                                <input type="text" id="nama_obat" name="nama_obat" class="form-control"
                                    value="<?= htmlspecialchars($data['nama_obat']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="kategori">Kategori <span style="color:var(--red)">*</span></label>
                                <select id="kategori" name="kategori" class="form-control" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php foreach ($kategori_list as $k): ?>
                                    <option value="<?= $k ?>" <?= $data['kategori'] === $k ? 'selected' : '' ?>><?= $k ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="stok">Stok <span style="color:var(--red)">*</span></label>
                                <input type="number" id="stok" name="stok" class="form-control"
                                    value="<?= htmlspecialchars($data['stok']) ?>" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="harga">Harga (Rp) <span style="color:var(--red)">*</span></label>
                                <input type="number" id="harga" name="harga" class="form-control"
                                    value="<?= htmlspecialchars($data['harga']) ?>" min="0" step="0.01" required>
                            </div>
                            <div class="form-group full">
                                <label for="tanggal_expired">Tanggal Expired <span style="color:var(--red)">*</span></label>
                                <input type="date" id="tanggal_expired" name="tanggal_expired" class="form-control"
                                    value="<?= htmlspecialchars($data['tanggal_expired']) ?>" required>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                            <a href="index.php" class="btn btn-ghost">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>
<script>function toggleSidebar(){document.querySelector('.sidebar').classList.toggle('open');document.getElementById('overlay').classList.toggle('show');}</script>
</body>
</html>