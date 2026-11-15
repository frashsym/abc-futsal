<?php
// ============================================================
//  kasir/bayar_simpan.php - kasir mencatat pembayaran lanjutan (pelunasan)
//  Dipakai saat penyewa yang tadinya DP datang membayar sisanya.
// ============================================================
$level_wajib = 'kasir';
require_once '../auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../booking/daftar.php');
    exit;
}

$id_booking = (int)($_POST['id_booking'] ?? 0);
$jumlah     = (int)($_POST['jumlah_bayar'] ?? 0);
$metode     = $_POST['metode'] ?? '';

$pdo->beginTransaction();
try {
    // Kunci baris booking supaya dua kasir tidak mencatat pembayaran bersamaan
    $stmt = $pdo->prepare("SELECT * FROM tb_booking WHERE id_booking = ? FOR UPDATE");
    $stmt->execute([$id_booking]);
    $b = $stmt->fetch();

    if (!$b)                          throw new Exception('Booking tidak ditemukan.');
    if ($b['status'] !== 'Booking')   throw new Exception('Booking ini sudah ' . $b['status'] . ', tidak bisa menerima pembayaran.');
    if ($b['status_bayar'] === 'Lunas') throw new Exception('Booking ini sudah lunas.');
    if (!in_array($metode, ['Tunai', 'Transfer'], true)) throw new Exception('Metode pembayaran tidak valid.');

    // Tidak boleh bayar saat ada pengajuan pembatalan yang belum diputuskan admin
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_pembatalan WHERE id_booking = ? AND status = 'Menunggu'");
    $stmt->execute([$id_booking]);
    if ($stmt->fetchColumn() > 0) throw new Exception('Ada pengajuan pembatalan yang menunggu admin. Pembayaran ditahan dulu.');

    $sisa = (int)$b['total_harga'] - (int)$b['jumlah_dibayar'];
    if ($jumlah <= 0 || $jumlah > $sisa) {
        throw new Exception('Nominal harus lebih dari 0 dan tidak boleh melebihi sisa tagihan (' . rupiah($sisa) . ').');
    }

    // Kalau nominal = sisa -> Pelunasan (booking jadi Lunas). Kalau kurang -> masih DP tambahan.
    $lunas = ($jumlah === $sisa);
    $tipe  = $lunas ? 'Pelunasan' : 'DP';

    $stmt = $pdo->prepare("INSERT INTO tb_pembayaran (id_booking, id_kasir, tipe, jumlah, metode) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$id_booking, $user['id'], $tipe, $jumlah, $metode]);

    $stmt = $pdo->prepare("UPDATE tb_booking SET jumlah_dibayar = jumlah_dibayar + ?, status_bayar = ? WHERE id_booking = ?");
    $stmt->execute([$jumlah, $lunas ? 'Lunas' : 'DP', $id_booking]);

    $pdo->commit();
    $_SESSION['pesan'] = ['success', $lunas
        ? 'Pelunasan ' . rupiah($jumlah) . ' dicatat. Booking ' . $b['kode_booking'] . ' sekarang LUNAS.'
        : 'Pembayaran ' . rupiah($jumlah) . ' dicatat. Sisa tagihan ' . rupiah($sisa - $jumlah) . '.'];
} catch (PDOException $ex) {
    $pdo->rollBack();
    error_log('bayar_simpan: ' . $ex->getMessage());
    $_SESSION['pesan'] = ['danger', 'Terjadi kesalahan database. Coba lagi.'];
} catch (Exception $ex) {
    $pdo->rollBack();
    $_SESSION['pesan'] = ['danger', $ex->getMessage()];
}
header('Location: ../booking/detail.php?id=' . $id_booking);
