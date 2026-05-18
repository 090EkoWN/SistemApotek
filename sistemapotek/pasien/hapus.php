<?php
require_once '../auth.php';
require_once '../koneksi.php';

$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = $koneksi->prepare("DELETE FROM pasien WHERE id_pasien = ?");
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