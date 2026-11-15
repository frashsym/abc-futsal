<?php
// ============================================================
//  admin/lapangan_proses.php - memproses form lapangan (simpan & hapus)
// ============================================================
$level_wajib = 'admin';
require_once '../auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: lapangan.php');
    exit;
}

$aksi = $_POST['aksi'] ?? '';
$id   = (int)($_POST['id_lapangan'] ?? 0);

/* ---------------- SIMPAN (tambah / ubah) ---------------- */
if ($aksi === 'simpan') {
    $nama   = trim($_POST['nama_lapangan'] ?? '');
    $lantai = trim($_POST['jenis_lantai'] ?? '');
    $status = $_POST['status'] ?? '';

    // 1. Validasi
    $error = [];
    if (mb_strlen($nama) < 2 || mb_strlen($nama) > 50)     $error[] = 'Nama lapangan harus 2–50 karakter.';
    if ($lantai === '' || mb_strlen($lantai) > 30)         $error[] = 'Jenis lantai wajib diisi (maks 30 karakter).';
    if (!in_array($status, ['Tersedia', 'Perbaikan'], true)) $error[] = 'Status tidak valid.';

    // 2. Nama tidak boleh kembar
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_lapangan WHERE nama_lapangan = ? AND id_lapangan <> ?");
    $stmt->execute([$nama, $id]);
    if ($stmt->fetchColumn() > 0) $error[] = 'Nama lapangan sudah dipakai.';

    if ($error) {
        $_SESSION['old']   = ['nama_lapangan' => $nama, 'jenis_lantai' => $lantai, 'status' => $status];
        $_SESSION['pesan'] = ['danger', $error];
        header('Location: lapangan_form.php' . ($id ? "?id=$id" : ''));
        exit;
    }

    // 3. Simpan
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE tb_lapangan SET nama_lapangan = ?, jenis_lantai = ?, status = ? WHERE id_lapangan = ?");
        $stmt->execute([$nama, $lantai, $status, $id]);
        $_SESSION['pesan'] = ['success', 'Data lapangan berhasil diubah.'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO tb_lapangan (nama_lapangan, jenis_lantai, status) VALUES (?, ?, ?)");
        $stmt->execute([$nama, $lantai, $status]);
        $_SESSION['pesan'] = ['success', 'Lapangan berhasil ditambah. Jangan lupa atur harga sewanya di menu Master Data > Harga Sewa.'];
    }
    header('Location: lapangan.php');
    exit;
}

/* ---------------- HAPUS ---------------- */
if ($aksi === 'hapus') {
    // Lapangan yang sudah pernah dibooking tidak boleh dihapus (riwayat transaksi harus aman)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_booking WHERE id_lapangan = ?");
    $stmt->execute([$id]);

    if ($stmt->fetchColumn() > 0) {
        $_SESSION['pesan'] = ['danger', 'Lapangan ini sudah punya riwayat booking, tidak bisa dihapus. Ubah statusnya jadi "Perbaikan" kalau tidak dipakai lagi.'];
    } else {
        $stmt = $pdo->prepare("DELETE FROM tb_lapangan WHERE id_lapangan = ?");
        $stmt->execute([$id]);
        $_SESSION['pesan'] = ['success', 'Lapangan berhasil dihapus.'];
    }
    header('Location: lapangan.php');
    exit;
}

header('Location: lapangan.php');
