<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
session_start();
include "koneksi.php";
include "fungsi.php";

if (!empty($_SESSION['user'])) redirect('index.php');

$d = ['nama_lengkap' => '', 'username' => '', 'no_hp' => ''];
$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    foreach ($d as $k => $v) $d[$k] = trim($_POST[$k] ?? '');
    $pass  = (string)($_POST['password'] ?? '');
    $pass2 = (string)($_POST['password2'] ?? '');

    if (mb_strlen($d['nama_lengkap']) < 3 || mb_strlen($d['nama_lengkap']) > 100) $err[] = 'Nama lengkap 3–100 karakter.';
    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $d['username'])) $err[] = 'Username 3–30 karakter, hanya huruf, angka, dan underscore.';
    if ($d['no_hp'] !== '' && !preg_match('/^[0-9+\- ]{8,20}$/', $d['no_hp'])) $err[] = 'Nomor HP tidak valid.';
    if (strlen($pass) < 6) $err[] = 'Password minimal 6 karakter.';
    if ($pass !== $pass2) $err[] = 'Konfirmasi password tidak sama.';
    if (!$err && db_one("SELECT 1 FROM pengguna WHERE username = ?", [$d['username']])) $err[] = 'Username sudah dipakai orang lain.';

    if (!$err) {
        $ok = db_exec(
            "INSERT INTO pengguna (nama_lengkap, username, password, no_hp, level) VALUES (?, ?, ?, ?, 'pelanggan')",
            [$d['nama_lengkap'], $d['username'], password_hash($pass, PASSWORD_DEFAULT), $d['no_hp']]
        );
        if ($ok) {
            flash('success', 'Akun berhasil dibuat. Silakan login!');
            redirect('login.php');
        }
        $err[] = 'Gagal menyimpan akun, coba lagi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar | ABC Futsal</title>
    <link rel="icon" type="image/svg+xml" href="images/bola.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        try {
            var t = localStorage.getItem('abc-theme');
            if (t) document.documentElement.setAttribute('data-theme', t)
        } catch (e) {}
    </script>
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="login-page">
    <?php include "icons.php"; ?>
    <span class="blob b1"></span><span class="blob b2"></span><span class="blob b3"></span>

    <div class="login-card">
        <div class="login-art">
            <div class="logo"><img src="images/bola.png" alt="ABC"> ABC Futsal</div>
            <div>
                <div class="sticker">🥅</div>
                <h2 style="margin-top:14px">Bikin akun, <em>langsung booking!</em></h2>
                <p>Daftar gratis buat pesan lapangan favorit kamu kapan aja.</p>
            </div>
        </div>

        <form class="login-form" method="post" autocomplete="off">
            <?= csrf_field() ?>
            <h1>Daftar akun ✨</h1>
            <p class="lead">Isi data singkat di bawah ini.</p>

            <?php if ($err): ?>
                <div class="alert alert-danger">
                    <ul><?php foreach ($err as $m) echo '<li>' . e($m) . '</li>'; ?></ul>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= e($d['nama_lengkap']) ?>" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input class="form-control" id="username" name="username" value="<?= e($d['username']) ?>" required>
            </div>
            <div class="form-group">
                <label for="no_hp">No. HP</label>
                <input class="form-control" id="no_hp" name="no_hp" value="<?= e($d['no_hp']) ?>" placeholder="08xxxxxxxxxx">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input class="form-control" type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="password2">Ulangi Password</label>
                <input class="form-control" type="password" id="password2" name="password2" required>
            </div>
            <button class="btn btn-primary btn-block" type="submit" style="padding:13px">Daftar</button>
            <p class="alt">Udah punya akun? <a href="login.php"><b>Login</b></a></p>
        </form>
    </div>
</body>

</html>