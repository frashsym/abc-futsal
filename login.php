<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
session_start();
include "koneksi.php";
include "fungsi.php";

if (!empty($_SESSION['user'])) redirect('index.php');

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $u = db_one("SELECT * FROM pengguna WHERE username = ?", [$username]);

    if ($u && password_verify($password, $u['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'       => (int)$u['id_pengguna'],
            'nama'     => $u['nama_lengkap'],
            'username' => $u['username'],
            'level'    => $u['level'],
        ];
        redirect('index.php');
    }
    $error = 'Username atau password salah, coba cek lagi.';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | ABC Futsal</title>
    <link rel="icon" type="image/svg+xml" href="images/ball.webp">
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
            <div class="logo"><img src="images/ball.webp" alt="ABC"> ABC Futsal</div>
            <div>
                <div class="sticker">⚽</div>
                <h2 style="margin-top:14px">Booking lapangan, <em>gas main!</em></h2>
                <p>Aplikasi administrasi penyewaan lapangan futsal. Cek jadwal, booking, dan rekap pemasukan dalam satu tempat.</p>
            </div>
        </div>

        <form class="login-form" method="post" autocomplete="off">
            <h1>Masuk dulu, bro 👋</h1>
            <p class="lead">Login pakai akunmu untuk lanjut.</p>

            <?php flash_tampil(); ?>
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

            <div class="form-group">
                <label for="username">Username</label>
                <input class="form-control" type="text" id="username" name="username" value="<?= e($username) ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input class="form-control" type="password" id="password" name="password" required>
            </div>
            <button class="btn btn-primary btn-block" type="submit" style="padding:13px">Login</button>

            <p class="alt">Belum punya akun? <a href="register.php"><b>Daftar di sini</b></a></p>
        </form>
    </div>
</body>

</html>