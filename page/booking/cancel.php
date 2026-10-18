<?php
csrf_cek();
$id = (int)($_GET['id'] ?? 0);
$row = db_one("SELECT * FROM booking WHERE id_booking = ?", [$id]);

if (!$row || (!is_admin() && (int)$row['id_pengguna'] !== (int)$user['id'])) {
    flash('danger', 'Data booking tidak ditemukan.');
} elseif ($row['status'] !== 'Booking') {
    flash('danger', 'Hanya booking berstatus "Booking" yang bisa dibatalkan.');
} elseif (db_exec("UPDATE booking SET status = 'Cancel' WHERE id_booking = ? AND status = 'Booking'", [$id])) {
    flash('success', 'Booking ' . $row['kode_booking'] . ' dibatalkan. Slotnya kosong lagi.');
} else {
    flash('danger', 'Gagal membatalkan booking.');
}
redirect('?page=booking');
