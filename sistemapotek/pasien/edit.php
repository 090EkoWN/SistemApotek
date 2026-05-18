<?php
require_once '../auth.php';
require_once '../koneksi.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit(); }

$stmt = $koneksi->prepare("SELECT * FROM pasien WHERE id_pasien = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$pasien = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pasien) { header('Location: index.php?msg=gagal&type=danger'); exit(); }

$errors = [];
$data   = $pasien;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['nama_pasien']   = trim($_POST['nama_pasien']   ?? '');
    $data['alamat']        = trim($_POST['alamat']        ?? '');
    $data['no_hp']         = trim($_POST['no_hp']         ?? '');
    $data['tanggal_lahir'] = trim($_POST['tanggal_lahir'] ?? '');

    if (empty($data['nama_pasien']))   $errors[] = 'Nama pasien wajib diisi.';
    if (empty($data['alamat']))        $errors[] = 'Alamat wajib diisi.';
    if (empty($data['no_hp']))         $errors[] = 'No. HP wajib diisi.';
    if (empty($data['tanggal_lahir'])) $errors[] = 'Tanggal lahir wajib diisi.';

    if (empty($errors)) {
        $stmt = $koneksi->prepare(
            "UPDATE pasien SET nama_pasien=?, alamat=?, no_hp=?, tanggal_lahir=? WHERE id_pasien=?"
        );
        $stmt->bind_param('ssssi', $data['nama_pasien'], $data['alamat'], $data['no_hp'], $data['tanggal_lahir'], $id);
        if ($stmt->execute()) {
            header('Location: index.php?msg=edit_ok&type=success');
            exit();
        } else {
            $errors[] = 'Gagal memperbarui: ' . $stmt->error;
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
    <title>Edit Pasien — <?= APP_NAME ?></title>
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
                    <h2>Edit Pasien</h2>
                    <div class="topbar-breadcrumb">
                        <a href="../dashboard.php">Dashboard</a> <span class="sep">/</span>
                        <a href="index.php">Data Pasien</a> <span class="sep">/</span>
                        <span class="cur">Edit</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="page-header">
                <div>
                    <h1>✏️ Edit Data Pasien</h1>
                    <p>Perbarui informasi pasien: <strong><?= htmlspecialchars($pasien['nama_pasien']) ?></strong></p>
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
                    <div class="card-title"><span class="card-icon">📝</span> Form Edit Pasien</div>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nama_pasien">Nama Pasien <span style="color:var(--red)">*</span></label>
                                <input type="text" id="nama_pasien" name="nama_pasien"
                                    class="form-control"
                                    value="<?= htmlspecialchars($data['nama_pasien']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="no_hp">No. HP <span style="color:var(--red)">*</span></label>
                                <input type="text" id="no_hp" name="no_hp"
                                    class="form-control"
                                    value="<?= htmlspecialchars($data['no_hp']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="tanggal_lahir">Tanggal Lahir <span style="color:var(--red)">*</span></label>
                                <input type="date" id="tanggal_lahir" name="tanggal_lahir"
                                    class="form-control"
                                    value="<?= htmlspecialchars($data['tanggal_lahir']) ?>" required>
                            </div>
                            <div class="form-group full">
                                <label for="alamat">Alamat <span style="color:var(--red)">*</span></label>
                                <textarea id="alamat" name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($data['alamat']) ?></textarea>
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