<?php
csrf_cek();
$id = (int)($_GET['id'] ?? 0);

if (db_exec("DELETE FROM lapangan WHERE id_lapangan = ?", [$id])) {
    flash('success', 'Lapangan berhasil dihapus.');
} elseif ($GLOBALS['db_errno'] == 1451) {
    flash('danger', 'Lapangan ini sudah punya riwayat booking, jadi tidak bisa dihapus. Ubah statusnya jadi "Perbaikan" kalau mau dinonaktifkan.');
} else {
    flash('danger', 'Gagal menghapus lapangan.');
}
redirect('?page=lapangan');
