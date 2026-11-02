<?php
// Catat pembayaran (DP tambahan / pelunasan) di kasir. Khusus admin & kasir.
staff_only();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('?page=booking');
csrf_cek();

$id = (int)($_GET['id'] ?? 0);
$jumlah = angka($_POST['jumlah'] ?? '');
$metode = (string)($_POST['metode'] ?? '');

mysqli_begin_transaction($koneksi);
$row = db_one("SELECT * FROM booking WHERE id_booking = ? FOR UPDATE", [$id]);
$pesan = null;

if (!$row) {
    $pesan = 'Data booking tidak ditemukan.';
} elseif ($row['status'] !== 'Booking') {
    $pesan = 'Pembayaran hanya bisa dicatat untuk booking yang masih aktif.';
} elseif (!in_array($metode, METODE_BAYAR, true)) {
    $pesan = 'Pilih metode pembayaran dulu.';
} else {
    $sisa = sisa_bayar($row);
    if ($sisa <= 0) $pesan = 'Booking ini sudah lunas.';
    elseif ($jumlah < 1) $pesan = 'Jumlah pembayaran tidak valid.';
    elseif ($jumlah > $sisa) $pesan = 'Jumlah melebihi sisa tagihan (' . rupiah($sisa) . ').';
}

if ($pesan === null && catat_pembayaran($id, (int)$row['total_bayar'], (int)$row['dibayar'], $jumlah, $metode, 'Kasir', (int)$user['id'])) {
    mysqli_commit($koneksi);
    $sisa_baru = (int)$row['total_bayar'] - (int)$row['dibayar'] - $jumlah;
    flash('success', 'Pembayaran ' . rupiah($jumlah) . ' untuk ' . $row['kode_booking'] . ' tercatat. ' . ($sisa_baru > 0 ? 'Sisa tagihan ' . rupiah($sisa_baru) . '.' : 'Booking sudah lunas.'));
} else {
    mysqli_rollback($koneksi);
    flash('danger', $pesan ?? 'Gagal mencatat pembayaran, coba lagi.');
}
redirect('?page=booking');
