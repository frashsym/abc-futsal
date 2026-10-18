<?php
error_reporting(0);
session_start();
include "koneksi.php";
include "fungsi.php";

header('Content-Type: application/json; charset=utf-8');
if (empty($_SESSION['user'])) {
    http_response_code(401);
    echo '{}';
    exit;
}

$tgl = $_GET['tanggal'] ?? '';
$lap = (int)($_GET['lapangan'] ?? 0);
$kec = (int)($_GET['kecuali'] ?? 0);

if (!tanggal_valid($tgl) || $lap <= 0) {
    echo '{}';
    exit;
}
echo json_encode(waktu_tidak_tersedia($tgl, $lap, $kec), JSON_FORCE_OBJECT);
