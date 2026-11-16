<?php
// admin/pelanggan_proses.php - memproses form pelanggan (simpan & hapus)
$level_wajib = 'admin';
require_once '../auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pelanggan.php');
    exit;
}

$aksi = $_POST['aksi'] ?? '';
$id   = (int)($_POST['id_pelanggan'] ?? 0);

if ($aksi === 'simpan') {
    $data = [
        'nama' => trim($_POST['nama'] ?? ''),
        'no_hp' => trim($_POST['no_hp'] ?? ''),
        'alamat' => trim($_POST['alamat'] ?? ''),
        'jk' => $_POST['jk'] ?? '',
        'email' => trim($_POST['email'] ?? ''),
        'tanggal_lahir' => $_POST['tanggal_lahir'] ?? '',
        'tanggal_bergabung' => $_POST['tanggal_bergabung'] ?? '',
    ];

    $error = [];
    if (mb_strlen($data['nama']) < 1 || mb_strlen($data['nama']) > 100) {
        $error[] = 'Nama wajib diisi dan maksimal 100 karakter.';
    }
    if (!preg_match('/^[0-9+() \-]{8,20}$/', $data['no_hp'])) {
        $error[] = 'Nomor HP wajib diisi dan hanya boleh berisi angka, spasi, tanda +, tanda kurung, atau tanda hubung (8–20 karakter).';
    }
    if ($data['alamat'] === '') {
        $error[] = 'Alamat wajib diisi.';
    }
    if (!in_array($data['jk'], ['', 'Laki-laki', 'Perempuan'], true)) {
        $error[] = 'Jenis kelamin tidak valid.';
    }
    if ($data['email'] !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $error[] = 'Format email tidak valid.';
    }

    $tanggal_valid = static function ($tanggal) {
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $tanggal, $bagian)) {
            return false;
        }
        return checkdate((int)$bagian[2], (int)$bagian[3], (int)$bagian[1]);
    };
    $tanggal_waktu_valid = static function ($tanggal) {
        if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})$/', $tanggal, $bagian)) {
            return false;
        }
        if (!checkdate((int)$bagian[2], (int)$bagian[3], (int)$bagian[1])
            || (int)$bagian[4] > 23
            || (int)$bagian[5] > 59) {
            return false;
        }
        return $bagian[1] . '-' . $bagian[2] . '-' . $bagian[3] . ' ' . $bagian[4] . ':' . $bagian[5] . ':00';
    };

    if ($data['tanggal_lahir'] !== '' && !$tanggal_valid($data['tanggal_lahir'])) {
        $error[] = 'Tanggal lahir tidak valid.';
    }
    $tanggal_bergabung = $tanggal_waktu_valid($data['tanggal_bergabung']);
    if ($tanggal_bergabung === false) {
        $error[] = 'Tanggal dan jam bergabung wajib diisi dengan nilai yang valid.';
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_pelanggan WHERE id_pelanggan = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetchColumn()) {
            $_SESSION['pesan'] = ['danger', 'Data pelanggan tidak ditemukan.'];
            header('Location: pelanggan.php');
            exit;
        }
    }

    if ($error) {
        $_SESSION['old'] = $data;
        $_SESSION['pesan'] = ['danger', $error];
        header('Location: pelanggan_form.php' . ($id > 0 ? "?id=$id" : ''));
        exit;
    }

    $jk = $data['jk'] !== '' ? $data['jk'] : null;
    $email = $data['email'] !== '' ? $data['email'] : null;
    $tanggal_lahir = $data['tanggal_lahir'] !== '' ? $data['tanggal_lahir'] : null;

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE tb_pelanggan
                               SET nama = ?, no_hp = ?, alamat = ?, jk = ?, email = ?, tanggal_lahir = ?, tanggal_bergabung = ?
                               WHERE id_pelanggan = ?");
        $stmt->execute([
            $data['nama'],
            $data['no_hp'],
            $data['alamat'],
            $jk,
            $email,
            $tanggal_lahir,
            $tanggal_bergabung,
            $id,
        ]);
        $_SESSION['pesan'] = ['success', 'Data pelanggan berhasil diubah.'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO tb_pelanggan (nama, no_hp, alamat, jk, email, tanggal_lahir, tanggal_bergabung)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['nama'],
            $data['no_hp'],
            $data['alamat'],
            $jk,
            $email,
            $tanggal_lahir,
            $tanggal_bergabung,
        ]);
        $_SESSION['pesan'] = ['success', 'Pelanggan baru berhasil ditambahkan.'];
    }

    header('Location: pelanggan.php');
    exit;
}

if ($aksi === 'hapus') {
    if ($id < 1) {
        $_SESSION['pesan'] = ['danger', 'ID pelanggan tidak valid.'];
        header('Location: pelanggan.php');
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM tb_pelanggan WHERE id_pelanggan = ?");
    $stmt->execute([$id]);
    $_SESSION['pesan'] = $stmt->rowCount() > 0
        ? ['success', 'Data pelanggan berhasil dihapus.']
        : ['danger', 'Data pelanggan tidak ditemukan.'];
    header('Location: pelanggan.php');
    exit;
}

$_SESSION['pesan'] = ['danger', 'Aksi pelanggan tidak valid.'];
header('Location: pelanggan.php');
exit;
