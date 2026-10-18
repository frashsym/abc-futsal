<?php
$d = ['jam_mulai' => '', 'jam_selesai' => '', 'keterangan' => ''];
$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    foreach ($d as $k => $v) $d[$k] = trim($_POST[$k] ?? '');

    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $d['jam_mulai'])) $err[] = 'Jam mulai tidak valid.';
    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $d['jam_selesai'])) $err[] = 'Jam selesai tidak valid.';
    if (!$err && $d['jam_selesai'] <= $d['jam_mulai']) $err[] = 'Jam selesai harus lebih besar dari jam mulai.';
    if (mb_strlen($d['keterangan']) > 50) $err[] = 'Keterangan maksimal 50 karakter.';
    if (!$err && db_one("SELECT 1 FROM waktu WHERE jam_mulai = ? AND jam_selesai = ?", [$d['jam_mulai'] . ':00', $d['jam_selesai'] . ':00'])) $err[] = 'Slot waktu itu sudah ada.';

    if (!$err) {
        if (db_exec("INSERT INTO waktu (jam_mulai, jam_selesai, keterangan) VALUES (?, ?, ?)", [$d['jam_mulai'], $d['jam_selesai'], $d['keterangan']])) {
            flash('success', 'Slot waktu berhasil ditambahkan.');
            redirect('?page=waktu');
        }
        $err[] = 'Gagal menyimpan data.';
    }
}
$judul_form = 'TAMBAH WAKTU';
include __DIR__ . '/_form.php';
