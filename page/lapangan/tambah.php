<?php
$d = ['nama_lapangan' => '', 'jenis_lantai' => 'Vinyl', 'harga_per_jam' => '100000', 'status' => 'Tersedia'];
$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    foreach ($d as $k => $v) $d[$k] = trim($_POST[$k] ?? '');

    if (mb_strlen($d['nama_lapangan']) < 2 || mb_strlen($d['nama_lapangan']) > 50) $err[] = 'Nama lapangan 2–50 karakter.';
    if (!in_array($d['jenis_lantai'], ['Vinyl', 'Sintetis', 'Parquet', 'Semen'], true)) $err[] = 'Jenis lantai tidak valid.';
    if (!ctype_digit($d['harga_per_jam']) || (int)$d['harga_per_jam'] > 100000000) $err[] = 'Harga harus berupa angka bulat (tanpa titik/koma).';
    if (!in_array($d['status'], ['Tersedia', 'Perbaikan'], true)) $err[] = 'Status tidak valid.';

    if (!$err) {
        if (db_exec(
            "INSERT INTO lapangan (nama_lapangan, jenis_lantai, harga_per_jam, status) VALUES (?, ?, ?, ?)",
            [$d['nama_lapangan'], $d['jenis_lantai'], (int)$d['harga_per_jam'], $d['status']]
        )) {
            flash('success', $d['nama_lapangan'] . ' berhasil ditambahkan.');
            redirect('?page=lapangan');
        }
        $err[] = 'Gagal menyimpan data.';
    }
}
$judul_form = 'TAMBAH LAPANGAN';
include __DIR__ . '/_form.php';
