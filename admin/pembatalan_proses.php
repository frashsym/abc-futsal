<?php
// ============================================================
//  admin/pembatalan_proses.php - keputusan admin atas pengajuan pembatalan
//
//  Setujui : booking jadi "Cancel", jam kembali kosong, refund dicatat.
//  Tolak   : booking tetap jalan seperti biasa.
// ============================================================
$level_wajib = 'admin';
require_once '../auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pembatalan.php');
    exit;
}

$id_pembatalan = (int)($_POST['id_pembatalan'] ?? 0);
$keputusan     = $_POST['keputusan'] ?? '';
$refund        = (int)($_POST['refund_disetujui'] ?? 0);
$catatan       = trim($_POST['catatan_admin'] ?? '');

if (!in_array($keputusan, ['setuju', 'tolak'], true) || mb_strlen($catatan) > 255) {
    $_SESSION['pesan'] = ['danger', 'Data tidak valid.'];
    header('Location: pembatalan.php');
    exit;
}

$pdo->beginTransaction();
try {
    // 1. Ambil pengajuan & kunci barisnya (FOR UPDATE) supaya tidak diproses dua kali
    $stmt = $pdo->prepare("SELECT * FROM tb_pembatalan WHERE id_pembatalan = ? FOR UPDATE");
    $stmt->execute([$id_pembatalan]);
    $c = $stmt->fetch();
    if (!$c || $c['status'] !== 'Menunggu') {
        throw new Exception('Pengajuan tidak ditemukan atau sudah diproses.');
    }

    // 2. Ambil booking terkait
    $stmt = $pdo->prepare("SELECT * FROM tb_booking WHERE id_booking = ? FOR UPDATE");
    $stmt->execute([$c['id_booking']]);
    $b = $stmt->fetch();

    if ($keputusan === 'setuju') {
        if ($b['status'] !== 'Booking') {
            throw new Exception('Booking ini sudah tidak aktif, tidak bisa dibatalkan.');
        }
        if ($refund < 0 || $refund > (int)$b['jumlah_dibayar']) {
            throw new Exception('Refund tidak boleh minus atau melebihi uang yang sudah dibayar (' . rupiah($b['jumlah_dibayar']) . ').');
        }

        $stmt = $pdo->prepare("UPDATE tb_pembatalan
                               SET status = 'Disetujui', refund_disetujui = ?, id_admin = ?, catatan_admin = ?, tgl_proses = NOW()
                               WHERE id_pembatalan = ?");
        $stmt->execute([$refund, $user['id'], $catatan ?: null, $id_pembatalan]);

        $stmt = $pdo->prepare("UPDATE tb_booking SET status = 'Cancel', jumlah_refund = ? WHERE id_booking = ?");
        $stmt->execute([$refund, $b['id_booking']]);

        $pesan = 'Pembatalan disetujui. Refund ' . rupiah($refund) . ' dikembalikan manual ke penyewa (' . $b['kode_booking'] . ').';
    } else {
        $stmt = $pdo->prepare("UPDATE tb_pembatalan
                               SET status = 'Ditolak', id_admin = ?, catatan_admin = ?, tgl_proses = NOW()
                               WHERE id_pembatalan = ?");
        $stmt->execute([$user['id'], $catatan ?: null, $id_pembatalan]);
        $pesan = 'Pengajuan pembatalan ditolak. Booking ' . $b['kode_booking'] . ' tetap berjalan.';
    }

    $pdo->commit();
    $_SESSION['pesan'] = ['success', $pesan];
} catch (Exception $ex) {
    $pdo->rollBack();
    $_SESSION['pesan'] = ['danger', $ex->getMessage()];
}
header('Location: pembatalan.php');
