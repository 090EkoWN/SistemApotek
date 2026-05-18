<?php
// ============================================================
// obat/hapus.php — Hapus data obat
// ============================================================
require_once '../auth.php';
require_once '../koneksi.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $koneksi->prepare("DELETE FROM obat WHERE id_obat = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        header('Location: index.php?msg=hapus_ok&type=success');
    } else {
        header('Location: index.php?msg=gagal&type=danger');
    }
    $stmt->close();
} else {
    header('Location: index.php?msg=gagal&type=danger');
}
exit();
?>