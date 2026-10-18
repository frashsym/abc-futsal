<?php
csrf_cek();
$id = (int)($_GET['id'] ?? 0);

if ($id === (int)$user['id']) {
    flash('danger', 'Akun yang sedang kamu pakai tidak bisa dihapus.');
} elseif (db_exec("DELETE FROM pengguna WHERE id_pengguna = ?", [$id])) {
    flash('success', 'Pengguna berhasil dihapus.');
} elseif ($GLOBALS['db_errno'] == 1451) {
    flash('danger', 'Pengguna ini sudah punya riwayat booking, jadi tidak bisa dihapus.');
} else {
    flash('danger', 'Gagal menghapus pengguna.');
}
redirect('?page=pengguna');
