<?php
require_once '../auth.php';
require_once '../koneksi.php';

$errors = [];
$data   = ['id_pasien' => '', 'id_obat' => '', 'tanggal_pemberian' => date('Y-m-d'), 'jumlah' => 1, 'dosis' => '', 'keterangan' => ''];

// Ambil daftar pasien
$pasien_list = $koneksi->query("SELECT id_pasien, nama_pasien FROM pasien ORDER BY nama_pasien ASC");
// Ambil daftar obat yang masih ada stok
$obat_list   = $koneksi->query("SELECT id_obat, nama_obat, stok, harga FROM obat WHERE stok > 0 ORDER BY nama_obat ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['id_pasien']          = intval($_POST['id_pasien']          ?? 0);
    $data['id_obat']            = intval($_POST['id_obat']            ?? 0);
    $data['tanggal_pemberian']  = trim($_POST['tanggal_pemberian']    ?? '');
    $data['jumlah']             = intval($_POST['jumlah']             ?? 0);
    $data['dosis']              = trim($_POST['dosis']                ?? '');
    $data['keterangan']         = trim($_POST['keterangan']           ?? '');

    // Validasi
    if ($data['id_pasien'] <= 0)         $errors[] = 'Pilih pasien terlebih dahulu.';
    if ($data['id_obat']   <= 0)         $errors[] = 'Pilih obat terlebih dahulu.';
    if (empty($data['tanggal_pemberian'])) $errors[] = 'Tanggal pemberian wajib diisi.';
    if ($data['jumlah'] <= 0)            $errors[] = 'Jumlah harus lebih dari 0.';
    if (empty($data['dosis']))           $errors[] = 'Dosis wajib diisi.';

    // Cek stok mencukupi
    if (empty($errors)) {
        $cek = $koneksi->prepare("SELECT stok FROM obat WHERE id_obat = ?");
        $cek->bind_param('i', $data['id_obat']);
        $cek->execute();
        $stok_db = $cek->get_result()->fetch_assoc()['stok'] ?? 0;
        $cek->close();

        if ($data['jumlah'] > $stok_db) {
            $errors[] = "Stok obat tidak mencukupi. Stok tersedia: $stok_db.";
        }
    }

    if (empty($errors)) {
        // Simpan transaksi
        $stmt = $koneksi->prepare(
            "INSERT INTO pemberian_obat (id_pasien, id_obat, tanggal_pemberian, jumlah, dosis, keterangan)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('iisiss', $data['id_pasien'], $data['id_obat'], $data['tanggal_pemberian'], $data['jumlah'], $data['dosis'], $data['keterangan']);

        if ($stmt->execute()) {
            // Kurangi stok obat
            $upd = $koneksi->prepare("UPDATE obat SET stok = stok - ? WHERE id_obat = ?");
            $upd->bind_param('ii', $data['jumlah'], $data['id_obat']);
            $upd->execute();
            $upd->close();

            header('Location: index.php?msg=tambah_ok&type=success');
            exit();
        } else {
            $errors[] = 'Gagal menyimpan: ' . $stmt->error;
        }
        $stmt->close();
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
</head>
<body>
<div class="app-wrap">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="hamburger" onclick="toggleSidebar()">☰</button>
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
                    <h1>📋 Catat Pemberian Obat</h1>
                    <p>Isi form berikut untuk mencatat pemberian obat kepada pasien</p>
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
                    <div class="card-title"><span class="card-icon">📝</span> Form Pemberian Obat</div>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-grid">

                            <!-- Pasien -->
                            <div class="form-group">
                                <label for="id_pasien">Pilih Pasien <span style="color:var(--red)">*</span></label>
                                <select id="id_pasien" name="id_pasien" class="form-control" required>
                                    <option value="">-- Pilih Pasien --</option>
                                    <?php
                                    if ($pasien_list && $pasien_list->num_rows > 0):
                                        while ($p = $pasien_list->fetch_assoc()):
                                    ?>
                                    <option value="<?= $p['id_pasien'] ?>"
                                        <?= $data['id_pasien'] == $p['id_pasien'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['nama_pasien']) ?>
                                    </option>
                                    <?php endwhile; else: ?>
                                    <option disabled>Belum ada data pasien</option>
                                    <?php endif; ?>
                                </select>
                                <?php if (!$pasien_list || $pasien_list->num_rows == 0): ?>
                                <span class="form-hint">⚠️ <a href="../pasien/tambah.php">Tambah pasien dulu</a></span>
                                <?php endif; ?>
                            </div>

                            <!-- Obat -->
                            <div class="form-group">
                                <label for="id_obat">Pilih Obat <span style="color:var(--red)">*</span></label>
                                <select id="id_obat" name="id_obat" class="form-control" required onchange="updateStokInfo(this)">
                                    <option value="">-- Pilih Obat --</option>
                                    <?php
                                    if ($obat_list && $obat_list->num_rows > 0):
                                        while ($o = $obat_list->fetch_assoc()):
                                    ?>
                                    <option value="<?= $o['id_obat'] ?>"
                                        data-stok="<?= $o['stok'] ?>"
                                        data-harga="<?= $o['harga'] ?>"
                                        <?= $data['id_obat'] == $o['id_obat'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($o['nama_obat']) ?> (Stok: <?= $o['stok'] ?>)
                                    </option>
                                    <?php endwhile; else: ?>
                                    <option disabled>Tidak ada obat yang tersedia</option>
                                    <?php endif; ?>
                                </select>
                                <span class="form-hint" id="stok-info">Pilih obat untuk melihat stok</span>
                            </div>

                            <!-- Tanggal -->
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
                                <input type="number" id="jumlah" name="jumlah"
                                    class="form-control" placeholder="Jumlah obat"
                                    value="<?= htmlspecialchars($data['jumlah']) ?>"
                                    min="1" required>
                            </div>

                            <!-- Dosis -->
                            <div class="form-group">
                                <label for="dosis">Dosis <span style="color:var(--red)">*</span></label>
                                <input type="text" id="dosis" name="dosis"
                                    class="form-control" placeholder="Contoh: 3x1 setelah makan"
                                    value="<?= htmlspecialchars($data['dosis']) ?>"
                                    required>
                            </div>

                            <!-- Keterangan -->
                            <div class="form-group full">
                                <label for="keterangan">Keterangan</label>
                                <textarea id="keterangan" name="keterangan"
                                    class="form-control" rows="3"
                                    placeholder="Keterangan tambahan (opsional)"><?= htmlspecialchars($data['keterangan']) ?></textarea>
                            </div>

                        </div>

                        <!-- Info total harga -->
                        <div id="total-info" style="display:none;margin-bottom:1rem;padding:.75rem 1rem;background:var(--teal-pale);border:1px solid var(--teal-light);border-radius:var(--radius);font-size:.85rem;color:var(--teal-dark)">
                            💰 Estimasi Total: <strong id="total-harga">Rp 0</strong>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">💾 Simpan Transaksi</button>
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

function updateStokInfo(sel) {
    var opt   = sel.options[sel.selectedIndex];
    var stok  = opt.getAttribute('data-stok');
    var harga = opt.getAttribute('data-harga');
    var info  = document.getElementById('stok-info');
    var totalDiv = document.getElementById('total-info');

    if (stok !== null) {
        info.textContent = '📦 Stok tersedia: ' + stok + ' unit';
        info.style.color = stok > 10 ? 'var(--teal-dark)' : stok > 0 ? '#92400e' : '#991b1b';
        document.getElementById('jumlah').max = stok;
        totalDiv.style.display = 'block';
        hitungTotal(harga);
    } else {
        info.textContent = 'Pilih obat untuk melihat stok';
        info.style.color = '';
        totalDiv.style.display = 'none';
    }
}

function hitungTotal(harga) {
    var jumlah = parseInt(document.getElementById('jumlah').value) || 0;
    var total  = jumlah * parseFloat(harga || 0);
    document.getElementById('total-harga').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

document.getElementById('jumlah').addEventListener('input', function() {
    var sel   = document.getElementById('id_obat');
    var opt   = sel.options[sel.selectedIndex];
    var harga = opt.getAttribute('data-harga');
    if (harga) hitungTotal(harga);
});
</script>
</body>
</html>