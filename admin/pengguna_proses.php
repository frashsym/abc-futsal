<?php
// ============================================================
//  admin/pengguna_proses.php - memproses form user (simpan & hapus)
// ============================================================
$level_wajib = 'admin';
require_once '../auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pengguna.php');
    exit;
}

$aksi = $_POST['aksi'] ?? '';
$id   = (int)($_POST['id_pengguna'] ?? 0);

/* ---------------- SIMPAN ---------------- */
if ($aksi === 'simpan') {
    $nama     = trim($_POST['nama_lengkap'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $no_hp    = trim($_POST['no_hp'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $level    = $_POST['level'] ?? 'kasir';
    $status   = $_POST['status'] ?? 'Aktif';

    // Akun sendiri: level & status dikunci (supaya admin tidak mengunci dirinya sendiri)
    if ($id === $user['id']) {
        $level  = $user['level'];
        $status = 'Aktif';
    }

    // 1. Validasi
    $error = [];
    if (mb_strlen($nama) < 3 || mb_strlen($nama) > 100)      $error[] = 'Nama lengkap harus 3–100 karakter.';
    if (!preg_match('/^[A-Za-z0-9_.]{4,30}$/', $username))   $error[] = 'Username 4–30 karakter: huruf, angka, titik, atau underscore.';
    if ($no_hp !== '' && !preg_match('/^[0-9+\- ]{8,20}$/', $no_hp)) $error[] = 'Nomor HP tidak valid.';
    if (!in_array($level, ['admin', 'kasir'], true))         $error[] = 'Level tidak valid.';
    if (!in_array($status, ['Aktif', 'Nonaktif'], true))     $error[] = 'Status tidak valid.';

    // Password wajib saat tambah baru; saat ubah boleh kosong (= tidak diganti)
    if ($id === 0 && $password === '')                       $error[] = 'Password wajib diisi.';
    if ($password !== '' && strlen($password) < 6)           $error[] = 'Password minimal 6 karakter.';

    // 2. Username tidak boleh kembar
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_pengguna WHERE username = ? AND id_pengguna <> ?");
    $stmt->execute([$username, $id]);
    if ($stmt->fetchColumn() > 0) $error[] = 'Username sudah dipakai.';

    if ($error) {
        $_SESSION['old']   = ['nama_lengkap' => $nama, 'username' => $username, 'no_hp' => $no_hp, 'level' => $level, 'status' => $status];
        $_SESSION['pesan'] = ['danger', $error];
        header('Location: pengguna_form.php' . ($id ? "?id=$id" : ''));
        exit;
    }

    // 3. Simpan
    if ($id > 0) {
        if ($password !== '') {
            $stmt = $pdo->prepare("UPDATE tb_pengguna SET nama_lengkap=?, username=?, no_hp=?, level=?, status=?, password=? WHERE id_pengguna=?");
            $stmt->execute([$nama, $username, $no_hp ?: null, $level, $status, password_hash($password, PASSWORD_DEFAULT), $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE tb_pengguna SET nama_lengkap=?, username=?, no_hp=?, level=?, status=? WHERE id_pengguna=?");
            $stmt->execute([$nama, $username, $no_hp ?: null, $level, $status, $id]);
        }
        $_SESSION['pesan'] = ['success', 'Data user berhasil diubah.'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO tb_pengguna (nama_lengkap, username, password, no_hp, level, status) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$nama, $username, password_hash($password, PASSWORD_DEFAULT), $no_hp ?: null, $level, $status]);
        $_SESSION['pesan'] = ['success', 'User baru berhasil ditambah.'];
    }
    header('Location: pengguna.php');
    exit;
}

/* ---------------- HAPUS ---------------- */
if ($aksi === 'hapus') {
    if ($id === $user['id']) {
        $_SESSION['pesan'] = ['danger', 'Akun yang sedang kamu pakai tidak bisa dihapus.'];
        header('Location: pengguna.php');
        exit;
    }

    // User yang sudah pernah mencatat transaksi tidak boleh dihapus (riwayat harus utuh)
    $stmt = $pdo->prepare("SELECT (SELECT COUNT(*) FROM tb_booking     WHERE id_kasir = ?)
                                + (SELECT COUNT(*) FROM tb_pembayaran  WHERE id_kasir = ?)
                                + (SELECT COUNT(*) FROM tb_pembatalan  WHERE id_kasir = ? OR id_admin = ?)");
    $stmt->execute([$id, $id, $id, $id]);

    if ($stmt->fetchColumn() > 0) {
        $_SESSION['pesan'] = ['danger', 'User ini sudah punya riwayat transaksi, tidak bisa dihapus. Ubah statusnya jadi "Nonaktif" saja.'];
    } else {
        $stmt = $pdo->prepare("DELETE FROM tb_pengguna WHERE id_pengguna = ?");
        $stmt->execute([$id]);
        $_SESSION['pesan'] = ['success', 'User berhasil dihapus.'];
    }
    header('Location: pengguna.php');
    exit;
}

header('Location: pengguna.php');
