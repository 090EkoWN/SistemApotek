<?php
// ============================================================
// transaksi/tambah.php — Tambah Pemberian Obat
// ============================================================
require_once '../auth.php';
require_once '../koneksi.php';

$errors = [];
$data   = ['id_pasien' => '', 'id_obat' => '', 'tanggal_pemberian' => date('Y-m-d'), 'jumlah' => 1, 'dosis' => '', 'keterangan' => ''];

// Ambil daftar pasien dan obat untuk dropdown
$pasien_list = $koneksi->query("SELECT id_pasien, nama_pasien FROM pasien ORDER BY nama_pasien ASC");
$obat_list   = $koneksi->query("SELECT id_obat, nama_obat, stok FROM obat WHERE stok > 0 ORDER BY nama_obat ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['id_pasien']         = intval($_POST['id_pasien'] ?? 0);
    $data['id_obat']           = intval($_POST['id_obat'] ?? 0);
    $data['tanggal_pemberian'] = trim($_POST['tanggal_pemberian'] ?? '');
    $data['jumlah']            = intval($_POST['jumlah'] ?? 0);
    $data['dosis']             = trim($_POST['dosis'] ?? '');
    $data['keterangan']        = trim($_POST['keterangan'] ?? '');

    if ($data['id_pasien'] <= 0)           $errors[] = 'Pasien wajib dipilih.';
    if ($data['id_obat'] <= 0)             $errors[] = 'Obat wajib dipilih.';
    if (empty($data['tanggal_pemberian'])) $errors[] = 'Tanggal pemberian wajib diisi.';
    if ($data['jumlah'] <= 0)              $errors[] = 'Jumlah harus lebih dari 0.';
    if (empty($data['dosis']))             $errors[] = 'Dosis wajib diisi.';

    if (empty($errors)) {
        // Cek stok tersedia
        $stmt = $koneksi->prepare("SELECT stok FROM obat WHERE id_obat = ?");
        $stmt->bind_param('i', $data['id_obat']);
        $stmt->execute();
        $obat_row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$obat_row || $obat_row['stok'] < $data['jumlah']) {
            $errors[] = 'Stok obat tidak mencukupi. Stok tersedia: ' . ($obat_row['stok'] ?? 0);
        } else {
            // Simpan transaksi
            $stmt = $koneksi->prepare(
                "INSERT INTO pemberian_obat (id_pasien, id_obat, tanggal_pemberian, jumlah, dosis, keterangan)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('iiisss', $data['id_pasien'], $data['id_obat'], $data['tanggal_pemberian'], $data['jumlah'], $data['dosis'], $data['keterangan']);

            if ($stmt->execute()) {
                // Kurangi stok obat
                $koneksi->query("UPDATE obat SET stok = stok - {$data['jumlah']} WHERE id_obat = {$data['id_obat']}");
                header('Location: index.php?msg=tambah_ok&type=success');
                exit();
            } else {
                $errors[] = 'Gagal menyimpan: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat Pemberian Obat — <?= APP_NAME ?></title>
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
                    <h2>Catat Pemberian Obat</h2>
                    <div class="topbar-breadcrumb">
                        <a href="../dashboard.php">Dashboard</a> <span class="sep">/</span>
                        <a href="index.php">Pemberian Obat</a> <span class="sep">/</span>
                        <span class="cur">Tambah</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="page-header">
                <div>
                    <h1>Catat Pemberian Obat</h1>
                    <p>Isi form berikut untuk mencatat pemberian obat kepada pasien</p>
                </div>
                <a href="index.php" class="btn btn-ghost"><i class="fa-solid fa-arrow-left"></i> Kembali</a>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div><strong>Terdapat kesalahan:</strong>
                    <ul style="margin:.3rem 0 0 1rem">
                        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-head">
                    <div class="card-title"><i class="fa-solid fa-pen-to-square"></i> Form Pemberian Obat</div>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-grid">

                            <div class="form-group">
                                <label for="id_pasien">Pasien <span style="color:var(--red)">*</span></label>
                                <select id="id_pasien" name="id_pasien" class="form-control" required>
                                    <option value="">-- Pilih Pasien --</option>
                                    <?php
                                    if ($pasien_list && $pasien_list->num_rows > 0) {
                                        while ($p = $pasien_list->fetch_assoc()):
                                    ?>
                                    <option value="<?= $p['id_pasien'] ?>"
                                        <?= $data['id_pasien'] == $p['id_pasien'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['nama_pasien']) ?>
                                    </option>
                                    <?php endwhile;
                                    } else { ?>
                                    <option disabled>Belum ada data pasien</option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="id_obat">Obat <span style="color:var(--red)">*</span></label>
                                <select id="id_obat" name="id_obat" class="form-control" required>
                                    <option value="">-- Pilih Obat --</option>
                                    <?php
                                    if ($obat_list && $obat_list->num_rows > 0) {
                                        while ($o = $obat_list->fetch_assoc()):
                                    ?>
                                    <option value="<?= $o['id_obat'] ?>" data-stok="<?= $o['stok'] ?>"
                                        <?= $data['id_obat'] == $o['id_obat'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($o['nama_obat']) ?> (Stok: <?= $o['stok'] ?>)
                                    </option>
                                    <?php endwhile;
                                    } else { ?>
                                    <option disabled>Tidak ada obat tersedia</option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="tanggal_pemberian">Tanggal Pemberian <span style="color:var(--red)">*</span></label>
                                <input type="date" id="tanggal_pemberian" name="tanggal_pemberian"
                                    class="form-control"
                                    value="<?= htmlspecialchars($data['tanggal_pemberian']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="jumlah">Jumlah <span style="color:var(--red)">*</span></label>
                                <input type="number" id="jumlah" name="jumlah" class="form-control"
                                    value="<?= htmlspecialchars($data['jumlah']) ?>" min="1" required>
                                <small id="info-stok" style="color:#64748b"></small>
                            </div>

                            <div class="form-group full">
                                <label for="dosis">Dosis / Aturan Pakai <span style="color:var(--red)">*</span></label>
                                <input type="text" id="dosis" name="dosis" class="form-control"
                                    placeholder="Contoh: 3x1 sehari sesudah makan"
                                    value="<?= htmlspecialchars($data['dosis']) ?>" required>
                            </div>

                            <div class="form-group full">
                                <label for="keterangan">Keterangan</label>
                                <textarea id="keterangan" name="keterangan" class="form-control"
                                    rows="3" placeholder="Keterangan tambahan (opsional)"><?= htmlspecialchars($data['keterangan']) ?></textarea>
                            </div>

                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Simpan Pemberian</button>
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
// Tampilkan info stok saat obat dipilih
document.getElementById('id_obat').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const stok = opt.dataset.stok;
    const info = document.getElementById('info-stok');
    if (stok !== undefined) {
        info.textContent = 'Stok tersedia: ' + stok;
        info.style.color = parseInt(stok) <= 10 ? '#e53e3e' : '#38a169';
    } else {
        info.textContent = '';
    }
});
</script>
</body>
</html>
