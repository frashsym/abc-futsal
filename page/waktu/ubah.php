<?php
$id = (int)($_GET['id'] ?? 0);
$row = db_one("SELECT * FROM waktu WHERE id_waktu = ?", [$id]);
if (!$row) {
    flash('danger', 'Data waktu tidak ditemukan.');
    redirect('?page=waktu');
}

$d = ['jam_mulai' => jam($row['jam_mulai']), 'jam_selesai' => jam($row['jam_selesai']), 'keterangan' => (string)$row['keterangan']];
$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    foreach ($d as $k => $v) $d[$k] = trim($_POST[$k] ?? '');

    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $d['jam_mulai'])) $err[] = 'Jam mulai tidak valid.';
    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $d['jam_selesai'])) $err[] = 'Jam selesai tidak valid.';
    if (!$err && $d['jam_selesai'] <= $d['jam_mulai']) $err[] = 'Jam selesai harus lebih besar dari jam mulai.';
    if (mb_strlen($d['keterangan']) > 50) $err[] = 'Keterangan maksimal 50 karakter.';
    if (!$err && db_one("SELECT 1 FROM waktu WHERE jam_mulai = ? AND jam_selesai = ? AND id_waktu <> ?", [$d['jam_mulai'] . ':00', $d['jam_selesai'] . ':00', $id])) $err[] = 'Slot waktu itu sudah ada.';

    if (!$err) {
        if (db_exec("UPDATE waktu SET jam_mulai=?, jam_selesai=?, keterangan=? WHERE id_waktu=?", [$d['jam_mulai'], $d['jam_selesai'], $d['keterangan'], $id])) {
            flash('success', 'Slot waktu berhasil diperbarui.');
            redirect('?page=waktu');
        }
        $err[] = 'Gagal memperbarui data.';
    }
}
$judul_form = 'UBAH WAKTU';
include __DIR__ . '/_form.php';
