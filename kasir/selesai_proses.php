<?php
// ============================================================
//  kasir/selesai_proses.php - tandai booking "Selesai" setelah penyewa main
//  Syarat: status Booking, sudah LUNAS, dan tanggal main sudah tiba.
// ============================================================
$level_wajib = 'kasir';
require_once '../auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../booking/daftar.php');
    exit;
}

$id_booking = (int)($_POST['id_booking'] ?? 0);

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("SELECT * FROM tb_booking WHERE id_booking = ? FOR UPDATE");
    $stmt->execute([$id_booking]);
    $b = $stmt->fetch();

    if (!$b)                             throw new Exception('Booking tidak ditemukan.');
    if ($b['status'] !== 'Booking')      throw new Exception('Booking ini sudah ' . $b['status'] . '.');
    if ($b['status_bayar'] !== 'Lunas')  throw new Exception('Booking belum lunas. Catat pelunasan dulu.');
    if ($b['tanggal_main'] > date('Y-m-d')) throw new Exception('Tanggal main belum tiba, belum bisa ditandai selesai.');

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_pembatalan WHERE id_booking = ? AND status = 'Menunggu'");
    $stmt->execute([$id_booking]);
    if ($stmt->fetchColumn() > 0) throw new Exception('Ada pengajuan pembatalan yang menunggu admin.');

    $stmt = $pdo->prepare("UPDATE tb_booking SET status = 'Selesai' WHERE id_booking = ?");
    $stmt->execute([$id_booking]);

    $pdo->commit();
    $_SESSION['pesan'] = ['success', 'Booking ' . $b['kode_booking'] . ' ditandai selesai.'];
} catch (PDOException $ex) {
    $pdo->rollBack();
    error_log('selesai_proses: ' . $ex->getMessage());
    $_SESSION['pesan'] = ['danger', 'Terjadi kesalahan database. Coba lagi.'];
} catch (Exception $ex) {
    $pdo->rollBack();
    $_SESSION['pesan'] = ['danger', $ex->getMessage()];
}
header('Location: ../booking/detail.php?id=' . $id_booking);
