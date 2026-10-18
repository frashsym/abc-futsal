<?php
$lapangan_list = db_all("SELECT * FROM lapangan WHERE status = 'Tersedia' ORDER BY nama_lapangan");
$waktu_list = db_all("SELECT * FROM waktu ORDER BY jam_mulai");
$me = db_one("SELECT nama_lengkap, no_hp FROM pengguna WHERE id_pengguna = ?", [(int)$user['id']]);

$d = [
    'tanggal_main' => date('Y-m-d'),
    'id_lapangan'  => (string)(int)($_GET['lapangan'] ?? 0) ?: '',
    'id_waktu'     => '',
    'nama_penyewa' => is_admin() ? '' : (string)$me['nama_lengkap'],
    'no_hp'        => is_admin() ? '' : (string)$me['no_hp'],
    'catatan'      => '',
];
$err = [];
$kecuali = 0;
$tgl_min = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    foreach ($d as $k => $v) $d[$k] = trim($_POST[$k] ?? '');

    // Transaksi + kunci baris lapangan supaya tidak ada double booking saat dua orang submit bersamaan
    mysqli_begin_transaction($koneksi);
    db_one("SELECT id_lapangan FROM lapangan WHERE id_lapangan = ? FOR UPDATE", [(int)$d['id_lapangan']]);
    list($err, $lap, $wkt) = booking_validasi($d, 0, true);

    if (!$err) {
        $kode = 'BK-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
        $ok = db_exec(
            "INSERT INTO booking (kode_booking, id_pengguna, id_lapangan, id_waktu, tanggal_main, nama_penyewa, no_hp, total_bayar, status, catatan)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Booking', ?)",
            [
                $kode,
                (int)$user['id'],
                (int)$lap['id_lapangan'],
                (int)$wkt['id_waktu'],
                $d['tanggal_main'],
                $d['nama_penyewa'],
                $d['no_hp'],
                hitung_total($lap, $wkt),
                $d['catatan']
            ]
        );
        if ($ok) {
            mysqli_commit($koneksi);
            flash('success', 'Booking berhasil! Kode kamu: ' . $kode);
            redirect('?page=booking');
        }
        $err[] = 'Gagal menyimpan booking, coba lagi.';
    }
    mysqli_rollback($koneksi);
}
$judul_form = 'BOOKING LAPANGAN BARU';
include __DIR__ . '/_form.php';
