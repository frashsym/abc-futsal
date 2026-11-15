<?php
// ============================================================
//  cek_jadwal.php - endpoint kecil (AJAX) untuk form booking kasir.
//  Mengembalikan JSON status tiap jam pada 1 lapangan + 1 tanggal, contoh:
//      {"1":"kosong","7":"terisi","8":"lewat"}
// ============================================================
require_once __DIR__ . '/auth.php';
header('Content-Type: application/json; charset=utf-8');

$tanggal = $_GET['tanggal'] ?? '';
$filter_lapangan = (int)($_GET['lapangan'] ?? 0);

$cek = DateTime::createFromFormat('Y-m-d', $tanggal);
if (!$cek || $cek->format('Y-m-d') !== $tanggal || $filter_lapangan <= 0) {
    echo '{}';
    exit;
}

include __DIR__ . '/inc/hitung_slot.php';

$hasil = [];
foreach ($slot[$filter_lapangan] ?? [] as $id_waktu => $s) {
    $hasil[$id_waktu] = $s['status'];
}
echo json_encode($hasil, JSON_FORCE_OBJECT);
