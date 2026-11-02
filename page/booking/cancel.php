<?php
csrf_cek();
$id = (int)($_GET['id'] ?? 0);

mysqli_begin_transaction($koneksi);
$row = db_one("SELECT * FROM booking WHERE id_booking = ? FOR UPDATE", [$id]);
$ok = false;

if (!$row || (!is_staff() && (int)$row['id_pengguna'] !== (int)$user['id'])) {
    flash('danger', 'Data booking tidak ditemukan.');
} elseif ($row['status'] !== 'Booking') {
    flash('danger', 'Hanya booking berstatus "Booking" yang bisa dibatalkan.');
} else {
    $dp = (int)$row['dibayar'];
    // Kasir/admin boleh memilih DP dikembalikan; user online: DP otomatis hangus
    $kembalikan = is_staff() && $dp > 0 && ($_POST['dp'] ?? '') === 'kembali';
    $ok = db_exec("UPDATE booking SET status = 'Cancel' WHERE id_booking = ? AND status = 'Booking'", [$id]);
    if ($ok && $kembalikan) {
        $ok = db_exec(
            "INSERT INTO pembayaran (id_booking, jumlah, jenis, metode, kanal, id_pengguna) VALUES (?, ?, 'Refund', 'Tunai', 'Kasir', ?)",
            [$id, -$dp, (int)$user['id']]
        ) && db_exec("UPDATE booking SET dibayar = 0 WHERE id_booking = ?", [$id]);
    }
    if ($ok) {
        mysqli_commit($koneksi);
        $info = $dp > 0 ? ($kembalikan ? ' DP ' . rupiah($dp) . ' dikembalikan ke customer.' : ' DP ' . rupiah($dp) . ' tidak dikembalikan (hangus).') : '';
        flash('success', 'Booking ' . $row['kode_booking'] . ' dibatalkan. Slotnya kosong lagi.' . $info);
        redirect('?page=booking');
    }
    flash('danger', 'Gagal membatalkan booking.');
}
mysqli_rollback($koneksi);
redirect('?page=booking');
