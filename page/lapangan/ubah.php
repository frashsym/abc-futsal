<?php
$id = (int)($_GET['id'] ?? 0);
$row = db_one("SELECT * FROM lapangan WHERE id_lapangan = ?", [$id]);
if (!$row) {
    flash('danger', 'Data lapangan tidak ditemukan.');
    redirect('?page=lapangan');
}

$d = [
    'nama_lapangan' => $row['nama_lapangan'],
    'jenis_lantai' => $row['jenis_lantai'],
    'harga_per_jam' => (string)$row['harga_per_jam'],
    'status' => $row['status'],
];
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
            "UPDATE lapangan SET nama_lapangan=?, jenis_lantai=?, harga_per_jam=?, status=? WHERE id_lapangan=?",
            [$d['nama_lapangan'], $d['jenis_lantai'], (int)$d['harga_per_jam'], $d['status'], $id]
        )) {
            flash('success', 'Data lapangan berhasil diperbarui.');
            redirect('?page=lapangan');
        }
        $err[] = 'Gagal memperbarui data.';
    }
}
$judul_form = 'UBAH LAPANGAN';
include __DIR__ . '/_form.php';
