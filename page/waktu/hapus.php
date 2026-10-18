<?php
csrf_cek();
$id = (int)($_GET['id'] ?? 0);

if (db_exec("DELETE FROM waktu WHERE id_waktu = ?", [$id])) {
    flash('success', 'Slot waktu berhasil dihapus.');
} elseif ($GLOBALS['db_errno'] == 1451) {
    flash('danger', 'Slot waktu ini sudah dipakai di data booking, jadi tidak bisa dihapus.');
} else {
    flash('danger', 'Gagal menghapus slot waktu.');
}
redirect('?page=waktu');
