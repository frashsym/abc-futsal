<?php
// ============================================================
//  inc/hitung_slot.php
//  Menghitung status setiap slot (lapangan x jam) pada satu tanggal.
//
//  Isi dulu sebelum include:
//      $tanggal           = '2026-11-14';
//      $filter_lapangan   = 2;   // (opsional) hanya satu lapangan
//
//  Hasil:
//      $daftar_lapangan, $daftar_waktu
//      $slot[id_lapangan][id_waktu] = ['status' => ..., 'harga' => ..., 'booking' => ...]
//
//  Arti status:
//      kosong      -> bisa dipesan
//      terisi      -> sudah ada booking (bukan Cancel)
//      lewat       -> jamnya sudah lewat
//      perbaikan   -> lapangan sedang diperbaiki
//      tanpa_harga -> admin belum mengatur harga untuk slot ini
// ============================================================

// 1. Daftar lapangan
$sql = "SELECT * FROM tb_lapangan";
$param = [];
if (!empty($filter_lapangan)) {
    $sql .= " WHERE id_lapangan = ?";
    $param[] = (int)$filter_lapangan;
}
$sql .= " ORDER BY nama_lapangan";
$stmt = $pdo->prepare($sql);
$stmt->execute($param);
$daftar_lapangan = $stmt->fetchAll();

// 2. Daftar jam operasional
$daftar_waktu = $pdo->query("SELECT * FROM tb_waktu ORDER BY jam_mulai")->fetchAll();

// 3. Harga: $harga[id_lapangan][id_waktu] = 100000
$harga = [];
foreach ($pdo->query("SELECT id_lapangan, id_waktu, harga FROM tb_harga") as $h) {
    $harga[$h['id_lapangan']][$h['id_waktu']] = (int)$h['harga'];
}

// 4. Booking aktif (selain Cancel) pada tanggal tsb
$stmt = $pdo->prepare(
    "SELECT b.id_booking, b.id_lapangan, b.kode_booking, b.nama_penyewa, b.status_bayar, b.status,
            w.jam_mulai, w.jam_selesai
     FROM tb_booking b
     JOIN tb_waktu w ON w.id_waktu = b.id_waktu
     WHERE b.tanggal_main = ? AND b.status <> 'Cancel'"
);
$stmt->execute([$tanggal]);
$booking_tanggal_ini = $stmt->fetchAll();

// 5. Tentukan status tiap slot
$hari_ini = date('Y-m-d');
$jam_sekarang = date('H:i:s');
$slot = [];

foreach ($daftar_lapangan as $lap) {
    foreach ($daftar_waktu as $w) {
        $s = [
            'status'  => 'kosong',
            'harga'   => $harga[$lap['id_lapangan']][$w['id_waktu']] ?? 0,
            'booking' => null,
        ];

        if ($lap['status'] === 'Perbaikan') {
            $s['status'] = 'perbaikan';
        } else {
            // Slot dianggap terisi kalau jamnya bertabrakan dengan booking lain
            foreach ($booking_tanggal_ini as $b) {
                if (
                    $b['id_lapangan'] == $lap['id_lapangan']
                    && $b['jam_mulai'] < $w['jam_selesai']
                    && $b['jam_selesai'] > $w['jam_mulai']
                ) {
                    $s['status']  = 'terisi';
                    $s['booking'] = $b;
                    break;
                }
            }
            if ($s['status'] === 'kosong') {
                if ($tanggal < $hari_ini || ($tanggal === $hari_ini && $w['jam_selesai'] <= $jam_sekarang)) {
                    $s['status'] = 'lewat';
                } elseif ($s['harga'] <= 0) {
                    $s['status'] = 'tanpa_harga';
                }
            }
        }
        $slot[$lap['id_lapangan']][$w['id_waktu']] = $s;
    }
}
