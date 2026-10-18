<?php
$id = (int)($_GET['id'] ?? 0);
$row = db_one("SELECT * FROM booking WHERE id_booking = ?", [$id]);

if (!$row || (!is_admin() && (int)$row['id_pengguna'] !== (int)$user['id'])) {
    flash('danger', 'Data booking tidak ditemukan.');
    redirect('?page=booking');
}
if ($row['status'] !== 'Booking') {
    flash('danger', 'Booking yang sudah selesai atau dibatalkan tidak bisa diubah.');
    redirect('?page=booking');
}

$lapangan_list = db_all("SELECT * FROM lapangan WHERE status = 'Tersedia' OR id_lapangan = ? ORDER BY nama_lapangan", [(int)$row['id_lapangan']]);
$waktu_list = db_all("SELECT * FROM waktu ORDER BY jam_mulai");

$d = [
    'tanggal_main' => $row['tanggal_main'],
    'id_lapangan' => (string)$row['id_lapangan'],
    'id_waktu' => (string)$row['id_waktu'],
    'nama_penyewa' => $row['nama_penyewa'],
    'no_hp' => (string)$row['no_hp'],
    'catatan' => (string)$row['catatan'],
];
$err = [];
$kecuali = $id;
$tgl_min = min(date('Y-m-d'), $row['tanggal_main']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    foreach ($d as $k => $v) $d[$k] = trim($_POST[$k] ?? '');

    // Cek jadwal hanya kalau tanggal / lapangan / jam diganti
    $berubah = $d['tanggal_main'] !== $row['tanggal_main']
        || (int)$d['id_lapangan'] !== (int)$row['id_lapangan']
        || (int)$d['id_waktu'] !== (int)$row['id_waktu'];

    mysqli_begin_transaction($koneksi);
    db_one("SELECT id_lapangan FROM lapangan WHERE id_lapangan = ? FOR UPDATE", [(int)$d['id_lapangan']]);
    list($err, $lap, $wkt) = booking_validasi($d, $id, $berubah);

    if (!$err) {
        $total = $berubah ? hitung_total($lap, $wkt) : (int)$row['total_bayar'];
        $ok = db_exec(
            "UPDATE booking SET id_lapangan=?, id_waktu=?, tanggal_main=?, nama_penyewa=?, no_hp=?, total_bayar=?, catatan=?
             WHERE id_booking=? AND status='Booking'",
            [(int)$lap['id_lapangan'], (int)$wkt['id_waktu'], $d['tanggal_main'], $d['nama_penyewa'], $d['no_hp'], $total, $d['catatan'], $id]
        );
        if ($ok) {
            mysqli_commit($koneksi);
            flash('success', 'Booking ' . $row['kode_booking'] . ' berhasil diperbarui.');
            redirect('?page=booking');
        }
        $err[] = 'Gagal memperbarui booking.';
    }
    mysqli_rollback($koneksi);
}
$judul_form = 'UBAH BOOKING ' . $row['kode_booking'];
include __DIR__ . '/_form.php';
