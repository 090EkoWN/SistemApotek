<?php
require_once '../auth.php';
require_once '../koneksi.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit(); }

// Ambil data pasien beserta id_user
$stmt = $koneksi->prepare("SELECT p.*, u.username FROM pasien p LEFT JOIN users u ON p.id_user=u.id_user WHERE p.id_pasien=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$pasien = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pasien) { header('Location: index.php?msg=gagal&type=danger'); exit(); }

$errors  = [];
$success = '';
$data    = $pasien;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'data';

    /* ── Simpan Data Pasien ── */
    if ($action === 'data') {
        $data['nama_pasien']   = trim($_POST['nama_pasien']   ?? '');
        $data['alamat']        = trim($_POST['alamat']        ?? '');
        $data['no_hp']         = trim($_POST['no_hp']         ?? '');
        $data['tanggal_lahir'] = trim($_POST['tanggal_lahir'] ?? '');

        if (empty($data['nama_pasien']))   $errors[] = 'Nama pasien wajib diisi.';
        if (empty($data['alamat']))        $errors[] = 'Alamat wajib diisi.';
        if (empty($data['no_hp']))         $errors[] = 'No. HP wajib diisi.';
        if (empty($data['tanggal_lahir'])) $errors[] = 'Tanggal lahir wajib diisi.';

        if (empty($errors)) {
            $stmt = $koneksi->prepare("UPDATE pasien SET nama_pasien=?,alamat=?,no_hp=?,tanggal_lahir=? WHERE id_pasien=?");
            $stmt->bind_param('ssssi', $data['nama_pasien'], $data['alamat'], $data['no_hp'], $data['tanggal_lahir'], $id);
            if ($stmt->execute()) {
                header('Location: index.php?msg=edit_ok&type=success'); exit();
            } else {
                $errors[] = 'Gagal memperbarui: ' . $stmt->error;
            }
            $stmt->close();
        }
    }

    /* ── Ubah Password ── */
    if ($action === 'password') {
        $pw_baru   = trim($_POST['password_baru']   ?? '');
        $pw_ulang  = trim($_POST['password_ulang']  ?? '');

        if (empty($pw_baru))               $errors[] = 'Password baru wajib diisi.';
        elseif (strlen($pw_baru) < 6)      $errors[] = 'Password minimal 6 karakter.';
        elseif ($pw_baru !== $pw_ulang)    $errors[] = 'Konfirmasi password tidak cocok.';

        if (empty($errors)) {
            if (!$pasien['id_user']) {
                $errors[] = 'Pasien ini tidak memiliki akun login.';
            } else {
                $hash = password_hash($pw_baru, PASSWORD_DEFAULT);
                $stmt = $koneksi->prepare("UPDATE users SET password=? WHERE id_user=?");
                $stmt->bind_param('si', $hash, $pasien['id_user']);
                if ($stmt->execute()) {
                    $success = 'Password berhasil diubah.';
                } else {
                    $errors[] = 'Gagal mengubah password: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Edit Pasien — <?= APP_NAME ?></title>
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
                    <h2>Edit Pasien</h2>
                    <div class="topbar-breadcrumb">
                        <a href="../dashboard.php">Dashboard</a>
                        <span class="sep">/</span>
                        <a href="index.php">Data Pasien</a>
                        <span class="sep">/</span>
                        <span class="cur">Edit</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">

            <div class="page-header">
                <div>
                    <h1><i class="fa-solid fa-user-pen" style="color:var(--green);font-size:1rem"></i> Edit Data Pasien</h1>
                    <p>Perbarui informasi pasien: <strong><?= htmlspecialchars($pasien['nama_pasien']) ?></strong></p>
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

            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
            </div>
            <?php endif; ?>

            <!-- FORM DATA PASIEN -->
            <div class="card">
                <div class="card-head">
                    <div class="card-title">
                        <i class="fa-solid fa-id-card"></i> Data Pasien
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="data">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nama_pasien">Nama Pasien <span style="color:var(--red)">*</span></label>
                                <input type="text" id="nama_pasien" name="nama_pasien"
                                    class="form-control" placeholder="Nama lengkap pasien"
                                    value="<?= htmlspecialchars($data['nama_pasien']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="no_hp">No. HP <span style="color:var(--red)">*</span></label>
                                <div class="input-icon-wrap">
                                    <i class="fa-solid fa-phone input-icon"></i>
                                    <input type="text" id="no_hp" name="no_hp"
                                        class="form-control" placeholder="08xx-xxxx-xxxx"
                                        value="<?= htmlspecialchars($data['no_hp']) ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="tanggal_lahir">Tanggal Lahir <span style="color:var(--red)">*</span></label>
                                <input type="date" id="tanggal_lahir" name="tanggal_lahir"
                                    class="form-control"
                                    value="<?= htmlspecialchars($data['tanggal_lahir']) ?>" required>
                            </div>
                            <div class="form-group full">
                                <label for="alamat">Alamat <span style="color:var(--red)">*</span></label>
                                <textarea id="alamat" name="alamat" class="form-control" rows="3"
                                    placeholder="Alamat lengkap" required><?= htmlspecialchars($data['alamat']) ?></textarea>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan
                            </button>
                            <a href="index.php" class="btn btn-ghost">Batal</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- FORM UBAH PASSWORD -->
            <div class="card">
                <div class="card-head">
                    <div class="card-title">
                        <i class="fa-solid fa-key"></i> Ubah Password Login
                    </div>
                    <?php if (!$pasien['id_user']): ?>
                    <span class="badge badge-amber"><i class="fa-solid fa-triangle-exclamation"></i> Pasien belum punya akun</span>
                    <?php else: ?>
                    <span class="badge badge-teal"><i class="fa-solid fa-circle-check"></i> Akun: <?= htmlspecialchars($pasien['username']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!$pasien['id_user']): ?>
                    <div class="alert alert-warning">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Pasien ini belum memiliki akun login. Hapus dan tambah ulang pasien untuk membuat akun.
                    </div>
                    <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="password">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="password_baru">
                                    Password Baru <span style="color:var(--red)">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fa-solid fa-lock input-icon"></i>
                                    <input type="password" id="password_baru" name="password_baru"
                                        class="form-control" placeholder="Minimal 6 karakter">
                                </div>
                                <span class="form-hint">Minimal 6 karakter</span>
                            </div>
                            <div class="form-group">
                                <label for="password_ulang">
                                    Konfirmasi Password <span style="color:var(--red)">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fa-solid fa-lock input-icon"></i>
                                    <input type="password" id="password_ulang" name="password_ulang"
                                        class="form-control" placeholder="Ulangi password baru">
                                </div>
                                <span class="form-hint" id="pw_match_hint"></span>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-amber">
                                <i class="fa-solid fa-key"></i> Ubah Password
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>
<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>
<script>
function toggleSidebar(){
    document.querySelector('.sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('show');
}

// Validasi konfirmasi password real-time
const pw1 = document.getElementById('password_baru');
const pw2 = document.getElementById('password_ulang');
const hint = document.getElementById('pw_match_hint');

if (pw1 && pw2 && hint) {
    function checkPw() {
        if (!pw2.value) { hint.textContent=''; hint.style.color=''; return; }
        if (pw1.value === pw2.value) {
            hint.textContent = 'Password cocok';
            hint.style.color = 'var(--green-dk)';
        } else {
            hint.textContent = 'Password tidak cocok';
            hint.style.color = 'var(--red)';
        }
    }
    pw1.addEventListener('input', checkPw);
    pw2.addEventListener('input', checkPw);
}
</script>
</body>
</html>
