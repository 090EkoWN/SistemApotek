<?php
// ============================================================
// transaksi/edit.php — Edit Data Pemberian Obat
// ============================================================
require_once '../auth.php';
require_once '../koneksi.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit(); }

// Ambil data transaksi
$stmt = $koneksi->prepare(
    "SELECT po.*, p.nama_pasien, o.nama_obat 
     FROM pemberian_obat po
     JOIN pasien p ON po.id_pasien = p.id_pasien
     JOIN obat o ON po.id_obat = o.id_obat
     WHERE po.id_transaksi = ?"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$transaksi = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$transaksi) { 
    header('Location: index.php?msg=Data tidak ditemukan&type=danger'); 
    exit(); 
}

$errors = [];
$data   = $transaksi; // isi awal dari database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['id_pasien']         = intval($_POST['id_pasien'] ?? 0);
    $data['id_obat']           = intval($_POST['id_obat'] ?? 0);
    $data['tanggal_pemberian'] = trim($_POST['tanggal_pemberian'] ?? '');
    $data['jumlah']            = trim($_POST['jumlah'] ?? '');
    $data['dosis']             = trim($_POST['dosis'] ?? '');
    $data['keterangan']        = trim($_POST['keterangan'] ?? '');

    // Validasi
    if ($data['id_pasien'] <= 0)           $errors[] = 'Pasien wajib dipilih.';
    if ($data['id_obat'] <= 0)             $errors[] = 'Obat wajib dipilih.';
    if (empty($data['tanggal_pemberian'])) $errors[] = 'Tanggal pemberian wajib diisi.';
    if (!is_numeric($data['jumlah']) || $data['jumlah'] <= 0) 
        $errors[] = 'Jumlah harus angka positif.';
    if (empty($data['dosis']))             $errors[] = 'Dosis wajib diisi.';

    // Cek stok obat
    if (empty($errors)) {
        $stmt = $koneksi->prepare("SELECT stok FROM obat WHERE id_obat = ?");
        $stmt->bind_param('i', $data['id_obat']);
        $stmt->execute();
        $obat = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Ambil jumlah lama untuk menghitung selisih
        $selisih = $data['jumlah'] - $transaksi['jumlah'];
        
        if ($obat && $selisih > 0 && $obat['stok'] < $selisih) {
            $errors[] = "Stok obat tidak cukup. Stok tersedia: {$obat['stok']}, tambahan dibutuhkan: {$selisih}";
        }
    }

    if (empty($errors)) {
        // Update stok obat (kembalikan stok lama, kurangi stok baru)
        $selisih = $data['jumlah'] - $transaksi['jumlah'];
        
        // Jika obat berbeda, kembalikan stok obat lama dan kurangi stok obat baru
        if ($data['id_obat'] != $transaksi['id_obat']) {
            // Kembalikan stok obat lama
            $koneksi->query("UPDATE obat SET stok = stok + {$transaksi['jumlah']} WHERE id_obat = {$transaksi['id_obat']}");
            // Kurangi stok obat baru
            $koneksi->query("UPDATE obat SET stok = stok - {$data['jumlah']} WHERE id_obat = {$data['id_obat']}");
        } else {
            // Obat sama, hanya update selisih
            if ($selisih != 0) {
                $koneksi->query("UPDATE obat SET stok = stok - {$selisih} WHERE id_obat = {$data['id_obat']}");
            }
        }

        // Update data transaksi
        $stmt = $koneksi->prepare(
            "UPDATE pemberian_obat 
             SET id_pasien=?, id_obat=?, tanggal_pemberian=?, jumlah=?, dosis=?, keterangan=? 
             WHERE id_transaksi=?"
        );
        $stmt->bind_param(
            'iisissi', 
            $data['id_pasien'], 
            $data['id_obat'], 
            $data['tanggal_pemberian'], 
            $data['jumlah'], 
            $data['dosis'], 
            $data['keterangan'],
            $id
        );
        
        if ($stmt->execute()) {
            header('Location: index.php?msg=Data pemberian obat berhasil diperbarui&type=success');
            exit();
        } else {
            $errors[] = 'Gagal memperbarui: ' . $stmt->error;
        }
        $stmt->close();
    }
}

// Ambil data pasien dan obat untuk dropdown
$pasien_list = $koneksi->query("SELECT id_pasien, nama_pasien FROM pasien ORDER BY nama_pasien ASC");
$obat_list   = $koneksi->query("SELECT id_obat, nama_obat, stok FROM obat ORDER BY nama_obat ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pemberian Obat — <?= APP_NAME ?></title>
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
                    <h2>Edit Pemberian Obat</h2>
                    <div class="topbar-breadcrumb">
                        <a href="../dashboard.php">Dashboard</a> <span class="sep">/</span>
                        <a href="index.php">Pemberian Obat</a> <span class="sep">/</span>
                        <span class="cur">Edit</span>
                    </div>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-info"><span class="topbar-dot"></span><?= date('d M Y') ?></div>
            </div>
        </div>

        <div class="page-body">

            <div class="page-header">
                <div>
                    <h1><i class="fa-solid fa-pen"></i> Edit Data Pemberian Obat</h1>
                    <p>Perbarui informasi pemberian obat untuk <strong><?= htmlspecialchars($transaksi['nama_pasien']) ?></strong></p>
                </div>
                <a href="index.php" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div>
                    <strong>Terdapat kesalahan:</strong>
                    <ul style="margin:.3rem 0 0 1rem">
                        <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-head">
                    <div class="card-title"><i class="fa-solid fa-pen-to-square"></i> Form Edit Pemberian Obat</div>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-grid">

                            <!-- Pasien -->
                            <div class="form-group">
                                <label for="id_pasien">Pasien <span style="color:var(--red)">*</span></label>
                                <select id="id_pasien" name="id_pasien" class="form-control" required>
                                    <option value="">-- Pilih Pasien --</option>
                                    <?php while ($p = $pasien_list->fetch_assoc()): ?>
                                    <option value="<?= $p['id_pasien'] ?>" 
                                        <?= $data['id_pasien'] == $p['id_pasien'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['nama_pasien']) ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Obat -->
                            <div class="form-group">
                                <label for="id_obat">Obat <span style="color:var(--red)">*</span></label>
                                <select id="id_obat" name="id_obat" class="form-control" required>
                                    <option value="">-- Pilih Obat --</option>
                                    <?php while ($o = $obat_list->fetch_assoc()): ?>
                                    <option value="<?= $o['id_obat'] ?>" 
                                        data-stok="<?= $o['stok'] ?>"
                                        <?= $data['id_obat'] == $o['id_obat'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($o['nama_obat']) ?> (Stok: <?= $o['stok'] ?>)
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Tanggal Pemberian -->
                            <div class="form-group">
                                <label for="tanggal_pemberian">Tanggal Pemberian <span style="color:var(--red)">*</span></label>
                                <input type="date" id="tanggal_pemberian" name="tanggal_pemberian" 
                                    class="form-control" 
                                    value="<?= htmlspecialchars($data['tanggal_pemberian']) ?>" 
                                    required>
                            </div>

                            <!-- Jumlah -->
                            <div class="form-group">
                                <label for="jumlah">Jumlah <span style="color:var(--red)">*</span></label>
                                <input type="number" id="jumlah" name="jumlah" class="form-control" 
                                    value="<?= htmlspecialchars($data['jumlah']) ?>" 
                                    min="1" required>
                            </div>

                            <!-- Dosis -->
                            <div class="form-group full">
                                <label for="dosis">Dosis <span style="color:var(--red)">*</span></label>
                                <input type="text" id="dosis" name="dosis" class="form-control" 
                                    placeholder="Contoh: 3x1 sehari sesudah makan"
                                    value="<?= htmlspecialchars($data['dosis']) ?>" 
                                    required>
                            </div>

                            <!-- Keterangan -->
                            <div class="form-group full">
                                <label for="keterangan">Keterangan</label>
                                <textarea id="keterangan" name="keterangan" class="form-control" 
                                    rows="3" placeholder="Informasi tambahan (opsional)"><?= htmlspecialchars($data['keterangan'] ?? '') ?></textarea>
                            </div>

                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan</button>
                            <a href="index.php" class="btn btn-ghost">Batal</a>
                        </div>

                    </form>
                </div>
            </div>

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