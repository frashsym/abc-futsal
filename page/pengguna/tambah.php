<?php
$d = ['nama_lengkap' => '', 'username' => '', 'no_hp' => '', 'level' => 'pelanggan'];
$err = [];
$edit = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    foreach ($d as $k => $v) $d[$k] = trim($_POST[$k] ?? '');
    $pass = (string)($_POST['password'] ?? '');

    if (mb_strlen($d['nama_lengkap']) < 3 || mb_strlen($d['nama_lengkap']) > 100) $err[] = 'Nama lengkap 3–100 karakter.';
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $d['username'])) $err[] = 'Username 3–30 karakter, hanya huruf, angka, dan underscore.';
    if ($d['no_hp'] !== '' && !preg_match('/^[0-9+\- ]{8,20}$/', $d['no_hp'])) $err[] = 'Nomor HP tidak valid.';
    if (!in_array($d['level'], ['admin', 'pelanggan'], true)) $err[] = 'Level tidak valid.';
    if (strlen($pass) < 6) $err[] = 'Password minimal 6 karakter.';
    if (!$err && db_one("SELECT 1 FROM pengguna WHERE username = ?", [$d['username']])) $err[] = 'Username sudah dipakai.';

    if (!$err) {
        $ok = db_exec(
            "INSERT INTO pengguna (nama_lengkap, username, password, no_hp, level) VALUES (?, ?, ?, ?, ?)",
            [$d['nama_lengkap'], $d['username'], password_hash($pass, PASSWORD_DEFAULT), $d['no_hp'], $d['level']]
        );
        if ($ok) {
            flash('success', 'Pengguna ' . $d['nama_lengkap'] . ' berhasil ditambahkan.');
            redirect('?page=pengguna');
        }
        $err[] = 'Gagal menyimpan data.';
    }
}
$judul_form = 'TAMBAH PENGGUNA';
include __DIR__ . "/_form.php";
