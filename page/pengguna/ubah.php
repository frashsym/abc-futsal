<?php
$id = (int)($_GET['id'] ?? 0);
$row = db_one("SELECT * FROM pengguna WHERE id_pengguna = ?", [$id]);
if (!$row) {
    flash('danger', 'Data pengguna tidak ditemukan.');
    redirect('?page=pengguna');
}

$d = [
    'nama_lengkap' => $row['nama_lengkap'],
    'username' => $row['username'],
    'no_hp' => (string)$row['no_hp'],
    'level' => $row['level'],
];
$err = [];
$edit = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    foreach ($d as $k => $v) $d[$k] = trim($_POST[$k] ?? '');
    $pass = (string)($_POST['password'] ?? '');

    if (mb_strlen($d['nama_lengkap']) < 3 || mb_strlen($d['nama_lengkap']) > 100) $err[] = 'Nama lengkap 3–100 karakter.';
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $d['username'])) $err[] = 'Username 3–30 karakter, hanya huruf, angka, dan underscore.';
    if ($d['no_hp'] !== '' && !preg_match('/^[0-9+\- ]{8,20}$/', $d['no_hp'])) $err[] = 'Nomor HP tidak valid.';
    if (!in_array($d['level'], ['admin', 'pelanggan'], true)) $err[] = 'Level tidak valid.';
    if ($pass !== '' && strlen($pass) < 6) $err[] = 'Password baru minimal 6 karakter.';
    if ($id === (int)$user['id'] && $d['level'] !== 'admin') $err[] = 'Kamu tidak bisa menurunkan level akun yang sedang dipakai.';
    if (!$err && db_one("SELECT 1 FROM pengguna WHERE username = ? AND id_pengguna <> ?", [$d['username'], $id])) $err[] = 'Username sudah dipakai.';

    if (!$err) {
        if ($pass !== '') {
            $ok = db_exec(
                "UPDATE pengguna SET nama_lengkap=?, username=?, no_hp=?, level=?, password=? WHERE id_pengguna=?",
                [$d['nama_lengkap'], $d['username'], $d['no_hp'], $d['level'], password_hash($pass, PASSWORD_DEFAULT), $id]
            );
        } else {
            $ok = db_exec(
                "UPDATE pengguna SET nama_lengkap=?, username=?, no_hp=?, level=? WHERE id_pengguna=?",
                [$d['nama_lengkap'], $d['username'], $d['no_hp'], $d['level'], $id]
            );
        }
        if ($ok) {
            if ($id === (int)$user['id']) {
                $_SESSION['user']['nama'] = $d['nama_lengkap'];
                $_SESSION['user']['username'] = $d['username'];
            }
            flash('success', 'Data pengguna berhasil diperbarui.');
            redirect('?page=pengguna');
        }
        $err[] = 'Gagal memperbarui data.';
    }
}
$judul_form = 'UBAH PENGGUNA';
include __DIR__ . "/_form.php";
