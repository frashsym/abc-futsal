<?php
// ============================================================
//  profile_proses.php - menyimpan perubahan profil sendiri
// ============================================================
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}

$nama   = trim($_POST['nama_lengkap'] ?? '');
$no_hp  = trim($_POST['no_hp'] ?? '');
$lama   = (string)($_POST['password_lama'] ?? '');
$baru   = (string)($_POST['password_baru'] ?? '');
$ulang  = (string)($_POST['password_ulang'] ?? '');

$error = [];
if (mb_strlen($nama) < 3 || mb_strlen($nama) > 100) $error[] = 'Nama lengkap harus 3–100 karakter.';
if ($no_hp !== '' && !preg_match('/^[0-9+\- ]{8,20}$/', $no_hp)) $error[] = 'Nomor HP tidak valid.';

// Password hanya diproses kalau kolom "password baru" diisi
$ganti_password = ($baru !== '' || $lama !== '' || $ulang !== '');
if ($ganti_password) {
    $stmt = $pdo->prepare("SELECT password FROM tb_pengguna WHERE id_pengguna = ?");
    $stmt->execute([$user['id']]);
    $hash_lama = $stmt->fetchColumn();

    if (!password_verify($lama, $hash_lama)) $error[] = 'Password lama salah.';
    if (strlen($baru) < 6)                   $error[] = 'Password baru minimal 6 karakter.';
    if ($baru !== $ulang)                    $error[] = 'Ulangi password baru tidak sama.';
}

if ($error) {
    $_SESSION['old']   = ['nama_lengkap' => $nama, 'no_hp' => $no_hp];
    $_SESSION['pesan'] = ['danger', $error];
    header('Location: profile.php');
    exit;
}

if ($ganti_password) {
    $stmt = $pdo->prepare("UPDATE tb_pengguna SET nama_lengkap = ?, no_hp = ?, password = ? WHERE id_pengguna = ?");
    $stmt->execute([$nama, $no_hp ?: null, password_hash($baru, PASSWORD_DEFAULT), $user['id']]);
} else {
    $stmt = $pdo->prepare("UPDATE tb_pengguna SET nama_lengkap = ?, no_hp = ? WHERE id_pengguna = ?");
    $stmt->execute([$nama, $no_hp ?: null, $user['id']]);
}

$_SESSION['pesan'] = ['success', 'Profil berhasil diperbarui.'];
header('Location: profile.php');
