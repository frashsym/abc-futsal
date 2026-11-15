<?php
// kasir/pembatalan.php - kasir melihat status pengajuan pembatalan miliknya
$level_wajib = 'kasir';
require_once '../auth.php';

$judul_halaman = 'Pengajuan Batal';
$menu_aktif    = 'pembatalan';

$stmt = $pdo->prepare(
    "SELECT c.*, b.id_booking, b.kode_booking, b.nama_penyewa, b.tanggal_main, b.jumlah_dibayar,
            l.nama_lapangan, w.jam_mulai, w.jam_selesai
     FROM tb_pembatalan c
     JOIN tb_booking b  ON b.id_booking = c.id_booking
     JOIN tb_lapangan l ON l.id_lapangan = b.id_lapangan
     JOIN tb_waktu w    ON w.id_waktu = b.id_waktu
     WHERE c.id_kasir = ?
     ORDER BY c.tgl_pengajuan DESC"
);
$stmt->execute([$user['id']]);
$daftar = $stmt->fetchAll();

include '../inc/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2>Pengajuan Pembatalan Saya</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Diajukan</th>
                        <th>Booking</th>
                        <th>Jadwal</th>
                        <th>Alasan</th>
                        <th>Refund Diajukan</th>
                        <th>Status</th>
                        <th>Keputusan Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar as $c): ?>
                        <tr>
                            <td class="nowrap"><?= e(tgl_indo(substr($c['tgl_pengajuan'], 0, 10))) ?></td>
                            <td><a href="../booking/detail.php?id=<?= (int)$c['id_booking'] ?>"><b><?= e($c['kode_booking']) ?></b></a><br><?= e($c['nama_penyewa']) ?></td>
                            <td class="nowrap"><?= e(tgl_indo($c['tanggal_main'])) ?><br><small><?= e($c['nama_lapangan']) ?>, <?= e(jam($c['jam_mulai'])) ?></small></td>
                            <td><?= e($c['alasan']) ?></td>
                            <td class="nowrap"><?= rupiah($c['refund_diajukan']) ?></td>
                            <td><?= badge($c['status']) ?></td>
                            <td>
                                <?php if ($c['status'] === 'Disetujui'): ?>
                                    Refund disetujui: <b><?= rupiah($c['refund_disetujui']) ?></b><br><small class="text-muted">Kembalikan uang ke penyewa secara manual.</small>
                                <?php elseif ($c['status'] === 'Ditolak'): ?>
                                    Ditolak
                                <?php else: ?>
                                    <span class="text-muted">Menunggu admin</span>
                                <?php endif; ?>
                                <?php if ($c['catatan_admin']): ?><br><small class="text-muted">“<?= e($c['catatan_admin']) ?>”</small><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$daftar): ?><tr>
                            <td class="empty" colspan="7">Kamu belum pernah mengajukan pembatalan.</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../inc/footer.php'; ?>