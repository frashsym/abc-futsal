<?php
// ============================================================
//  admin/waktu_proses.php - memproses form jam operasional (simpan & hapus)
// ============================================================
$level_wajib = 'admin';
require_once '../auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: waktu.php');
    exit;
}

$aksi = $_POST['aksi'] ?? '';
$id   = (int)($_POST['id_waktu'] ?? 0);

/* ---------------- SIMPAN ---------------- */
if ($aksi === 'simpan') {
    $mulai      = $_POST['jam_mulai'] ?? '';
    $selesai    = $_POST['jam_selesai'] ?? '';
    $keterangan = trim($_POST['keterangan'] ?? '');

    $error = [];
    if (!preg_match('/^\d{2}:\d{2}$/', $mulai) || !preg_match('/^\d{2}:\d{2}$/', $selesai)) {
        $error[] = 'Format jam tidak valid.';
    } else {
        if ($selesai <= $mulai) $error[] = 'Jam selesai harus lebih besar dari jam mulai.';

        // Slot tidak boleh bertabrakan dengan slot lain
        // (dua rentang bertabrakan kalau: mulai_A < selesai_B  DAN  selesai_A > mulai_B)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_waktu
                               WHERE jam_mulai < ? AND jam_selesai > ? AND id_waktu <> ?");
        $stmt->execute([$selesai, $mulai, $id]);
        if ($stmt->fetchColumn() > 0) $error[] = 'Jam ini bertabrakan dengan slot lain yang sudah ada.';
    }
    if (mb_strlen($keterangan) > 50) $error[] = 'Keterangan maksimal 50 karakter.';

    if ($error) {
        $_SESSION['old']   = ['jam_mulai' => $mulai, 'jam_selesai' => $selesai, 'keterangan' => $keterangan];
        $_SESSION['pesan'] = ['danger', $error];
        header('Location: waktu_form.php' . ($id ? "?id=$id" : ''));
        exit;
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE tb_waktu SET jam_mulai = ?, jam_selesai = ?, keterangan = ? WHERE id_waktu = ?");
        $stmt->execute([$mulai, $selesai, $keterangan ?: null, $id]);
        $_SESSION['pesan'] = ['success', 'Jam operasional berhasil diubah.'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO tb_waktu (jam_mulai, jam_selesai, keterangan) VALUES (?, ?, ?)");
        $stmt->execute([$mulai, $selesai, $keterangan ?: null]);
        $_SESSION['pesan'] = ['success', 'Jam operasional berhasil ditambah. Atur harganya di menu Master Data > Harga Sewa.'];
    }
    header('Location: waktu.php');
    exit;
}

/* ---------------- HAPUS ---------------- */
if ($aksi === 'hapus') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_booking WHERE id_waktu = ?");
    $stmt->execute([$id]);

    if ($stmt->fetchColumn() > 0) {
        $_SESSION['pesan'] = ['danger', 'Jam ini sudah dipakai di data booking, tidak bisa dihapus.'];
    } else {
        $stmt = $pdo->prepare("DELETE FROM tb_waktu WHERE id_waktu = ?");
        $stmt->execute([$id]);
        $_SESSION['pesan'] = ['success', 'Jam operasional berhasil dihapus.'];
    }
    header('Location: waktu.php');
    exit;
}

header('Location: waktu.php');
