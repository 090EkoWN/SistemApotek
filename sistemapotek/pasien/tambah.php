<?php
// ============================================================
// pasien/tambah.php — Tambah Pasien + Buat Akun User Pasien
// ============================================================
require_once '../auth.php';
require_once '../koneksi.php';

$errors = [];
$data   = ['nama_pasien' => '', 'alamat' => '', 'no_hp' => '', 'tanggal_lahir' => '',
           'username' => '', 'password' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['nama_pasien']   = trim($_POST['nama_pasien']   ?? '');
    $data['alamat']        = trim($_POST['alamat']        ?? '');
    $data['no_hp']         = trim($_POST['no_hp']         ?? '');
    $data['tanggal_lahir'] = trim($_POST['tanggal_lahir'] ?? '');
    $data['username']      = trim($_POST['username']      ?? '');
    $data['password']      = trim($_POST['password']      ?? '');

    // Validasi data pasien
    if (empty($data['nama_pasien']))   $errors[] = 'Nama pasien wajib diisi.';
    if (empty($data['alamat']))        $errors[] = 'Alamat wajib diisi.';
    if (empty($data['no_hp']))         $errors[] = 'No. HP wajib diisi.';
    if (empty($data['tanggal_lahir'])) $errors[] = 'Tanggal lahir wajib diisi.';

    // Validasi akun user (jika diisi)
    $buat_akun = !empty($data['username']) || !empty($data['password']);
    if ($buat_akun) {
        if (empty($data['username'])) $errors[] = 'Username wajib diisi jika ingin membuat akun.';
        if (strlen($data['password']) < 6) $errors[] = 'Password minimal 6 karakter.';

        // Cek username sudah ada
        if (!empty($data['username'])) {
            $cek = $koneksi->prepare("SELECT id_user FROM users WHERE username = ?");
            $cek->bind_param('s', $data['username']);
            $cek->execute();
            $cek->store_result();
            if ($cek->num_rows > 0) $errors[] = 'Username sudah digunakan, pilih username lain.';
            $cek->close();
        }
    }

    if (empty($errors)) {
        $id_user = null;

        // Buat akun user pasien jika username diisi
        if ($buat_akun) {
            $hashed = password_hash($data['password'], PASSWORD_DEFAULT);
            // Cek apakah kolom role ada di tabel users
            $col_check = $koneksi->query("SHOW COLUMNS FROM users LIKE 'role'");
            if ($col_check && $col_check->num_rows > 0) {
                $role = 'pasien';
                $stmt_user = $koneksi->prepare(
                    "INSERT INTO users (username, password, role, nama_lengkap) VALUES (?, ?, ?, ?)"
                );
                $stmt_user->bind_param('ssss', $data['username'], $hashed, $role, $data['nama_pasien']);
            } else {
                $stmt_user = $koneksi->prepare(
                    "INSERT INTO users (username, password, nama_lengkap) VALUES (?, ?, ?)"
                );
                $stmt_user->bind_param('sss', $data['username'], $hashed, $data['nama_pasien']);
            }

            if ($stmt_user->execute()) {
                $id_user = $koneksi->insert_id;
            } else {
                $errors[] = 'Gagal membuat akun: ' . $stmt_user->error;
            }
            $stmt_user->close();
        }

        // Simpan data pasien
        if (empty($errors)) {
            // Cek apakah kolom id_user ada di tabel pasien
            $col_check2 = $koneksi->query("SHOW COLUMNS FROM pasien LIKE 'id_user'");
            if ($col_check2 && $col_check2->num_rows > 0) {
                $stmt = $koneksi->prepare(
                    "INSERT INTO pasien (nama_pasien, alamat, no_hp, tanggal_lahir, id_user) VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->bind_param('ssssi', $data['nama_pasien'], $data['alamat'], $data['no_hp'], $data['tanggal_lahir'], $id_user);
            } else {
                $stmt = $koneksi->prepare(
                    "INSERT INTO pasien (nama_pasien, alamat, no_hp, tanggal_lahir) VALUES (?, ?, ?, ?)"
                );
                $stmt->bind_param('ssss', $data['nama_pasien'], $data['alamat'], $data['no_hp'], $data['tanggal_lahir']);
            }

            if ($stmt->execute()) {
                header('Location: index.php?msg=tambah_ok&type=success');
                exit();
            } else {
                // Rollback: hapus user jika pasien gagal
                if ($id_user) $koneksi->query("DELETE FROM users WHERE id_user = $id_user");
                $errors[] = 'Gagal menyimpan pasien: ' . $stmt->error;
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
    <title>Tambah Pasien — <?= APP_NAME ?></title>
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
                    <h2>Tambah Pasien</h2>
                    <div class="topbar-breadcrumb">
                        <a href="../dashboard.php">Dashboard</a> <span class="sep">/</span>
                        <a href="index.php">Data Pasien</a> <span class="sep">/</span>
                        <span class="cur">Tambah</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="page-header">
                <div>
                    <h1>👤 Tambah Pasien Baru</h1>
                    <p>Isi form berikut untuk mendaftarkan pasien dan akun login-nya</p>
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
                    <div class="card-title"><span class="card-icon">👤</span> Data Pasien</div>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-grid">

                            <div class="form-group">
                                <label for="nama_pasien">Nama Pasien <span style="color:var(--red)">*</span></label>
                                <input type="text" id="nama_pasien" name="nama_pasien"
                                    class="form-control" placeholder="Nama lengkap pasien"
                                    value="<?= htmlspecialchars($data['nama_pasien']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="no_hp">No. HP <span style="color:var(--red)">*</span></label>
                                <input type="text" id="no_hp" name="no_hp"
                                    class="form-control" placeholder="Contoh: 081234567890"
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
                                <textarea id="alamat" name="alamat" class="form-control"
                                    placeholder="Alamat lengkap pasien" rows="3" required><?= htmlspecialchars($data['alamat']) ?></textarea>
                            </div>

                        </div>

                        <!-- AKUN LOGIN PASIEN -->
                        <div style="margin:1.5rem 0 1rem;padding:1rem;background:#f0fdf4;border:1px dashed #38a169;border-radius:10px">
                            <div style="font-weight:700;color:#276749;margin-bottom:0.5rem">🔐 Akun Login Pasien <span style="font-weight:400;font-size:0.85rem;color:#64748b">(opsional — jika pasien perlu akses portal)</span></div>
                            <div class="form-grid" style="margin-top:0.75rem">

                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" name="username"
                                        class="form-control" placeholder="Contoh: budi123"
                                        value="<?= htmlspecialchars($data['username']) ?>">
                                    <small style="color:#64748b">Kosongkan jika tidak perlu akun login</small>
                                </div>

                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" id="password" name="password"
                                        class="form-control" placeholder="Minimal 6 karakter">
                                    <small style="color:#64748b">Minimal 6 karakter</small>
                                </div>

                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">💾 Simpan Pasien</button>
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
