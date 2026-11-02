<?php
staff_only();
csrf_cek();
$id = (int)($_GET['id'] ?? 0);
$row = db_one("SELECT * FROM booking WHERE id_booking = ?", [$id]);

if (!$row) {
    flash('danger', 'Data booking tidak ditemukan.');
} elseif ($row['status'] !== 'Booking') {
    flash('danger', 'Hanya booking berstatus "Booking" yang bisa ditandai selesai.');
} elseif (sisa_bayar($row) > 0) {
    flash('danger', 'Booking ' . $row['kode_booking'] . ' belum lunas (sisa ' . rupiah(sisa_bayar($row)) . '). Catat pembayarannya dulu.');
} elseif (db_exec("UPDATE booking SET status = 'Selesai' WHERE id_booking = ? AND status = 'Booking' AND dibayar >= total_bayar", [$id])) {
    flash('success', 'Booking ' . $row['kode_booking'] . ' ditandai selesai.');
} else {
    flash('danger', 'Gagal mengubah status booking.');
}
redirect('?page=booking');
