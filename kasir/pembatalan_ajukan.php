<?php
// ============================================================
//  kasir/pembatalan_ajukan.php - kasir mengajukan pembatalan booking.
//  Kasir TIDAK bisa membatalkan langsung: harus disetujui admin
//  (di admin/pembatalan_proses.php). Selama menunggu, jadwal tetap terkunci.
// ============================================================
$level_wajib = 'kasir';
require_once '../auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../booking/daftar.php');
    exit;
}

$id_booking = (int)($_POST['id_booking'] ?? 0);
$alasan     = trim($_POST['alasan'] ?? '');
$refund     = (int)($_POST['refund_diajukan'] ?? 0);

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("SELECT * FROM tb_booking WHERE id_booking = ? FOR UPDATE");
    $stmt->execute([$id_booking]);
    $b = $stmt->fetch();

    if (!$b)                        throw new Exception('Booking tidak ditemukan.');
    if ($b['status'] !== 'Booking') throw new Exception('Hanya booking yang masih aktif yang bisa dibatalkan.');
    if (mb_strlen($alasan) < 5 || mb_strlen($alasan) > 255) throw new Exception('Alasan pembatalan harus 5–255 karakter.');
    if ($refund < 0 || $refund > (int)$b['jumlah_dibayar']) {
        throw new Exception('Refund tidak boleh minus atau melebihi uang yang sudah dibayar (' . rupiah($b['jumlah_dibayar']) . ').');
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_pembatalan WHERE id_booking = ? AND status = 'Menunggu'");
    $stmt->execute([$id_booking]);
    if ($stmt->fetchColumn() > 0) throw new Exception('Pengajuan pembatalan untuk booking ini sudah ada dan menunggu admin.');

    $stmt = $pdo->prepare("INSERT INTO tb_pembatalan (id_booking, id_kasir, alasan, refund_diajukan) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id_booking, $user['id'], $alasan, $refund]);

    $pdo->commit();
    $_SESSION['pesan'] = ['success', 'Pengajuan pembatalan dikirim ke admin. Jadwal tetap terkunci sampai admin memutuskan.'];
} catch (PDOException $ex) {
    $pdo->rollBack();
    error_log('pembatalan_ajukan: ' . $ex->getMessage());
    $_SESSION['pesan'] = ['danger', 'Terjadi kesalahan database. Coba lagi.'];
} catch (Exception $ex) {
    $pdo->rollBack();
    $_SESSION['pesan'] = ['danger', $ex->getMessage()];
}
header('Location: ../booking/detail.php?id=' . $id_booking);
