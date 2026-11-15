<?php
// ============================================================
//  admin/harga_proses.php - menyimpan harga semua slot untuk 1 lapangan
// ============================================================
$level_wajib = 'admin';
require_once '../auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: harga.php');
    exit;
}

$id_lapangan = (int)($_POST['id_lapangan'] ?? 0);
$input_harga = $_POST['harga'] ?? [];       // bentuk: [id_waktu => "100000", ...]

// 1. Lapangan harus ada
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_lapangan WHERE id_lapangan = ?");
$stmt->execute([$id_lapangan]);
if ($stmt->fetchColumn() == 0) {
    $_SESSION['pesan'] = ['danger', 'Lapangan tidak ditemukan.'];
    header('Location: harga.php');
    exit;
}

// 2. Cek semua nilai harga dulu sebelum menyimpan apa pun
$daftar_waktu = $pdo->query("SELECT id_waktu FROM tb_waktu")->fetchAll(PDO::FETCH_COLUMN);
$simpan = [];   // [id_waktu => harga]  (yang akan di-insert/update)
$hapus  = [];   // [id_waktu]           (kolom dikosongkan)

foreach ($daftar_waktu as $id_waktu) {
    $nilai = trim((string)($input_harga[$id_waktu] ?? ''));
    if ($nilai === '') {
        $hapus[] = $id_waktu;
    } elseif (!ctype_digit($nilai) || (int)$nilai > 10000000) {
        $_SESSION['pesan'] = ['danger', 'Harga harus berupa angka bulat 0 – 10.000.000.'];
        header('Location: harga.php?id_lapangan=' . $id_lapangan);
        exit;
    } else {
        $simpan[$id_waktu] = (int)$nilai;
    }
}

// 3. Simpan semuanya dalam satu transaksi (semua berhasil, atau tidak sama sekali)
$pdo->beginTransaction();
try {
    // INSERT ... ON DUPLICATE KEY UPDATE: kalau harga sudah ada -> diubah, kalau belum -> ditambah
    $upsert = $pdo->prepare("INSERT INTO tb_harga (id_lapangan, id_waktu, harga) VALUES (?, ?, ?)
                             ON DUPLICATE KEY UPDATE harga = VALUES(harga)");
    foreach ($simpan as $id_waktu => $harga) {
        $upsert->execute([$id_lapangan, $id_waktu, $harga]);
    }

    $del = $pdo->prepare("DELETE FROM tb_harga WHERE id_lapangan = ? AND id_waktu = ?");
    foreach ($hapus as $id_waktu) {
        $del->execute([$id_lapangan, $id_waktu]);
    }

    $pdo->commit();
    $_SESSION['pesan'] = ['success', 'Harga sewa berhasil disimpan.'];
} catch (Exception $ex) {
    $pdo->rollBack();
    $_SESSION['pesan'] = ['danger', 'Gagal menyimpan harga, coba lagi.'];
}
header('Location: harga.php?id_lapangan=' . $id_lapangan);
