<?php
// ============================================================
//  auth.php - WAJIB di-include paling atas di setiap halaman setelah login.
//
//  Cara pakai di halaman yang khusus admin:
//      $level_wajib = 'admin';
//      require_once '../auth.php';
//  Kalau $level_wajib tidak diisi, semua user yang sudah login boleh masuk.
// ============================================================
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/format.php';

// 1. Harus sudah login
if (empty($_SESSION['user'])) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// 2. Ambil data user terbaru dari database (supaya kalau akun dinonaktifkan,
//    langsung tidak bisa dipakai walaupun masih punya session)
$stmt = $pdo->prepare("SELECT id_pengguna, nama_lengkap, username, level, status
                       FROM tb_pengguna WHERE id_pengguna = ?");
$stmt->execute([$_SESSION['user']['id']]);
$u = $stmt->fetch();

if (!$u || $u['status'] !== 'Aktif') {
    session_destroy();
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

$user = [
    'id'       => (int)$u['id_pengguna'],
    'nama'     => $u['nama_lengkap'],
    'username' => $u['username'],
    'level'    => $u['level'],
];

// 3. Cek hak akses halaman
if (isset($level_wajib) && $user['level'] !== $level_wajib) {
    $_SESSION['pesan'] = ['danger', 'Halaman itu khusus ' . $level_wajib . '.'];
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

// 4. Token CSRF: setiap form POST wajib membawa <input name="csrf">
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) {
        $_SESSION['pesan'] = ['danger', 'Sesi form tidak valid, silakan ulangi.'];
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}
