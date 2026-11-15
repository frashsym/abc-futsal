<?php
// ============================================================
//  admin/pembatalan.php - daftar pengajuan pembatalan dari kasir.
//  Admin menyetujui (+ menentukan nominal refund manual) atau menolak.
// ============================================================
$level_wajib = 'admin';
require_once '../auth.php';

$judul_halaman = 'Pembatalan & Refund';
$menu_aktif    = 'pembatalan';

$filter = $_GET['status'] ?? 'Menunggu';
if (!in_array($filter, ['Menunggu', 'Disetujui', 'Ditolak', 'Semua'], true)) $filter = 'Menunggu';

$sql = "SELECT c.*, b.kode_booking, b.nama_penyewa, b.tanggal_main, b.total_harga, b.jumlah_dibayar,
               l.nama_lapangan, w.jam_mulai, w.jam_selesai, k.nama_lengkap AS nama_kasir
        FROM tb_pembatalan c
        JOIN tb_booking b   ON b.id_booking = c.id_booking
        JOIN tb_lapangan l  ON l.id_lapangan = b.id_lapangan
        JOIN tb_waktu w     ON w.id_waktu = b.id_waktu
        JOIN tb_pengguna k  ON k.id_pengguna = c.id_kasir";
$param = [];
if ($filter !== 'Semua') {
    $sql .= " WHERE c.status = ?";
    $param[] = $filter;
}
$sql .= " ORDER BY c.tgl_pengajuan DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($param);
$daftar = $stmt->fetchAll();

include '../inc/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2>Pengajuan Pembatalan</h2>
        <div class="d-flex gap-2 flex-wrap">
            <?php foreach (['Menunggu', 'Disetujui', 'Ditolak', 'Semua'] as $f): ?>
                <a class="btn btn-sm <?= $filter === $f ? 'btn-primary' : 'btn-light' ?>" href="?status=<?= $f ?>"><?= $f ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Diajukan</th>
                        <th>Booking</th>
                        <th>Jadwal</th>
                        <th>Sudah Dibayar</th>
                        <th>Alasan</th>
                        <th>Refund Diajukan</th>
                        <th>Kasir</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar as $c): ?>
                        <tr>
                            <td class="nowrap"><?= e(tgl_indo(substr($c['tgl_pengajuan'], 0, 10))) ?><br><small class="text-muted"><?= e(substr($c['tgl_pengajuan'], 11, 5)) ?></small></td>
                            <td><b><?= e($c['kode_booking']) ?></b><br><?= e($c['nama_penyewa']) ?></td>
                            <td class="nowrap"><?= e(tgl_indo($c['tanggal_main'])) ?><br><small><?= e($c['nama_lapangan']) ?>, <?= e(jam($c['jam_mulai'])) ?>–<?= e(jam($c['jam_selesai'])) ?></small></td>
                            <td class="nowrap"><?= rupiah($c['jumlah_dibayar']) ?></td>
                            <td><?= e($c['alasan']) ?></td>
                            <td class="nowrap"><?= rupiah($c['refund_diajukan']) ?></td>
                            <td><?= e($c['nama_kasir']) ?></td>
                            <td>
                                <?= badge($c['status']) ?>
                                <?php if ($c['status'] === 'Disetujui'): ?><br><small>Refund: <?= rupiah($c['refund_disetujui']) ?></small><?php endif; ?>
                                <?php if ($c['catatan_admin']): ?><br><small class="text-muted"><?= e($c['catatan_admin']) ?></small><?php endif; ?>
                            </td>
                            <td>
                                <?php if ($c['status'] === 'Menunggu'): ?>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalProses"
                                        data-id="<?= (int)$c['id_pembatalan'] ?>"
                                        data-kode="<?= e($c['kode_booking']) ?> — <?= e($c['nama_penyewa']) ?>"
                                        data-dibayar="<?= (int)$c['jumlah_dibayar'] ?>"
                                        data-diajukan="<?= (int)$c['refund_diajukan'] ?>">Proses</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$daftar): ?><tr>
                            <td class="empty" colspan="9">Tidak ada pengajuan.</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: keputusan admin -->
<div class="modal fade" id="modalProses" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="post" action="pembatalan_proses.php" style="border-radius:20px">
            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
            <input type="hidden" name="id_pembatalan" id="m-id">
            <div class="modal-header">
                <h5 class="modal-title">Proses Pembatalan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3"><b id="m-kode"></b><br><span class="text-muted">Sudah dibayar: <b id="m-dibayar"></b></span></p>
                <div class="mb-3">
                    <label class="form-label" for="m-refund">Nominal refund yang disetujui (Rp)</label>
                    <input class="form-control" type="number" min="0" step="1000" name="refund_disetujui" id="m-refund" value="0">
                    <div class="form-text">Isi 0 jika uang tidak dikembalikan. Tidak boleh melebihi yang sudah dibayar. Uang dikembalikan manual oleh kasir.</div>
                </div>
                <div class="mb-1">
                    <label class="form-label" for="m-catatan">Catatan admin</label>
                    <input class="form-control" name="catatan_admin" id="m-catatan" maxlength="255" placeholder="Contoh: Refund 50% dari DP">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="keputusan" value="tolak" class="btn btn-light">Tolak</button>
                <button type="submit" name="keputusan" value="setuju" class="btn btn-primary">Setujui Pembatalan</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Isi modal dengan data baris yang diklik
    document.getElementById('modalProses').addEventListener('show.bs.modal', function(ev) {
        var b = ev.relatedTarget;
        document.getElementById('m-id').value = b.getAttribute('data-id');
        document.getElementById('m-kode').textContent = b.getAttribute('data-kode');
        var dibayar = parseInt(b.getAttribute('data-dibayar'), 10);
        document.getElementById('m-dibayar').textContent = 'Rp ' + dibayar.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        var refund = document.getElementById('m-refund');
        refund.max = dibayar;
        refund.value = b.getAttribute('data-diajukan');
        document.getElementById('m-catatan').value = '';
    });
</script>
<?php include '../inc/footer.php'; ?>