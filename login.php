<?php
// ============================================================
//  login.php - halaman masuk (admin & kasir)
// ============================================================
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/format.php';

// Sudah login? langsung ke dashboard
if (!empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM tb_pengguna WHERE username = ?");
    $stmt->execute([$username]);
    $u = $stmt->fetch();

    if (!$u || !password_verify($password, $u['password'])) {
        $error = 'Username atau password salah.';
    } elseif ($u['status'] !== 'Aktif') {
        $error = 'Akun kamu dinonaktifkan. Hubungi admin.';
    } else {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$u['id_pengguna']];
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | ABC Futsal</title>
    <link rel="icon" type="image/svg+xml" href="images/ball.webp">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<body class="login-page">
    <span class="blob b1"></span><span class="blob b2"></span><span class="blob b3"></span>

    <div class="login-card">
        <div class="login-art">
            <div class="logo"><img src="images/ball.webp" alt="ABC"> ABC Futsal</div>
            <div>
                <h2 class="mt-3">Cek jadwal, catat booking, <em>gas main!</em></h2>
                <p>Sistem kasir penyewaan lapangan futsal. Pesanan dari WhatsApp maupun datang langsung dicatat di sini.</p>
            </div>
        </div>

        <form class="login-form" method="post" autocomplete="off">
            <h1>Masuk dulu 👋</h1>
            <p class="text-muted mb-4">Login sebagai admin atau kasir.</p>

            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

            <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input class="form-control" type="text" id="username" name="username" value="<?= e($username) ?>" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" type="password" id="password" name="password" required>
            </div>
            <button class="btn btn-primary w-100 py-2" type="submit">Login</button>
        </form>
    </div>
</body>

</html>