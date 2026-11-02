<?php
$filter = $_GET['status'] ?? '';
if (!in_array($filter, ['Booking', 'Selesai', 'Cancel'], true)) $filter = '';
$staff = is_staff();

$sql = "SELECT b.*, l.nama_lapangan, w.jam_mulai, w.jam_selesai
        FROM booking b
        JOIN lapangan l ON l.id_lapangan = b.id_lapangan
        JOIN waktu w ON w.id_waktu = b.id_waktu WHERE 1=1";
$p = [];
if (!$staff) {
    $sql .= " AND b.id_pengguna = ?";
    $p[] = (int)$user['id'];
}
if ($filter !== '') {
    $sql .= " AND b.status = ?";
    $p[] = $filter;
}
$sql .= " ORDER BY b.tanggal_main DESC, w.jam_mulai DESC, b.id_booking DESC";
$rows = db_all($sql, $p);
?>
<div class="row clearfix">
    <div class="card">
        <div class="header">
            <h2><?= is_kasir() ? 'BOOKING &amp; PEMBAYARAN' : 'BOOKING LAPANGAN' ?></h2>
            <div class="header-actions">
                <a class="btn btn-primary" href="?page=booking&aksi=tambah"><?= ikon('plus') ?> <?= $staff ? 'Booking Customer' : 'Booking Baru' ?></a>
            </div>
        </div>
        <div class="body">
            <div class="filter-chips" style="margin-bottom:16px">
                <a class="chip <?= $filter === '' ? 'on' : '' ?>" href="?page=booking">Semua</a>
                <a class="chip <?= $filter === 'Booking' ? 'on' : '' ?>" href="?page=booking&status=Booking">Booking</a>
                <a class="chip <?= $filter === 'Selesai' ? 'on' : '' ?>" href="?page=booking&status=Selesai">Selesai</a>
                <a class="chip <?= $filter === 'Cancel' ? 'on' : '' ?>" href="?page=booking&status=Cancel">Cancel</a>
            </div>
            <div class="table-wrap">
                <table class="table js-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Tanggal Main</th>
                            <th>Lapangan</th>
                            <th>Penyewa</th>
                            <th>Total &amp; Pembayaran</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $i => $r):
                            $aktif_b = $r['status'] === 'Booking';
                            $sisa = sisa_bayar($r); ?>
                            <tr>
                                <td class="no"><?= $i + 1 ?></td>
                                <td><b><?= e($r['kode_booking']) ?></b><br><small class="muted"><?= e($r['sumber']) ?></small></td>
                                <td class="nowrap"><?= e(hari_indo($r['tanggal_main']) . ', ' . tgl_indo($r['tanggal_main'])) ?></td>
                                <td><?= e($r['nama_lapangan']) ?><br><small class="muted nowrap"><?= e(jam($r['jam_mulai'])) ?>–<?= e(jam($r['jam_selesai'])) ?></small></td>
                                <td><?= e($r['nama_penyewa']) ?><?php if ($r['no_hp']): ?><br><small class="muted"><?= e($r['no_hp']) ?></small><?php endif; ?></td>
                                <td>
                                    <b class="nowrap"><?= rupiah($r['total_bayar']) ?></b> <?= badge_bayar($r) ?>
                                    <?php if ($r['status'] !== 'Cancel' && $r['dibayar'] > 0 && $r['dibayar'] < $r['total_bayar']): ?>
                                        <br><small class="muted">Dibayar <?= rupiah($r['dibayar']) ?>, sisa <?= rupiah($r['total_bayar'] - $r['dibayar']) ?></small>
                                    <?php elseif ($r['status'] === 'Cancel' && $r['dibayar'] > 0): ?>
                                        <br><small class="muted"><?= rupiah($r['dibayar']) ?> tidak dikembalikan</small>
                                    <?php endif; ?>
                                </td>
                                <td><?= badge_status($r['status']) ?></td>
                                <td>
                                    <div class="aksi">
                                        <?php if ($aktif_b): ?>
                                            <?php if ($staff && $sisa > 0): ?>
                                                <button type="button" class="btn btn-primary btn-icon" title="Catat pembayaran" data-bayar
                                                    data-action="<?= e('?page=booking&aksi=bayar&id=' . (int)$r['id_booking']) ?>"
                                                    data-kode="<?= e($r['kode_booking']) ?>" data-nama="<?= e($r['nama_penyewa']) ?>"
                                                    data-total="<?= (int)$r['total_bayar'] ?>" data-dibayar="<?= (int)$r['dibayar'] ?>"><?= ikon('dollar') ?></button>
                                            <?php elseif ($staff): ?>
                                                <a class="btn btn-ok btn-icon" title="Tandai selesai" href="<?= url_aksi('booking', 'selesai', $r['id_booking']) ?>"
                                                    data-confirm="Booking <?= e($r['kode_booking']) ?> atas nama <?= e($r['nama_penyewa']) ?> sudah lunas dan selesai dimainkan?"
                                                    data-confirm-title="Tandai selesai?" data-confirm-ok="Ya, selesai" data-confirm-type="ok" data-confirm-icon="check"><?= ikon('check') ?></a>
                                            <?php endif; ?>
                                            <?php if ($staff && $r['dibayar'] > 0): ?>
                                                <button type="button" class="btn btn-danger btn-icon" title="Batalkan" data-cancel-dp
                                                    data-action="<?= e('?page=booking&aksi=cancel&id=' . (int)$r['id_booking']) ?>"
                                                    data-kode="<?= e($r['kode_booking']) ?>" data-dibayar="<?= (int)$r['dibayar'] ?>"><?= ikon('x') ?></button>
                                            <?php else: ?>
                                                <a class="btn btn-danger btn-icon" title="Batalkan" href="<?= url_aksi('booking', 'cancel', $r['id_booking']) ?>"
                                                    data-confirm="<?= e('Booking ' . $r['kode_booking'] . ' akan dibatalkan dan slotnya dibuka lagi untuk orang lain.' . ($r['dibayar'] > 0 ? ' DP ' . rupiah($r['dibayar']) . ' tidak dikembalikan.' : '')) ?>"
                                                    data-confirm-title="Batalkan booking?" data-confirm-ok="Ya, batalkan" data-confirm-type="danger" data-confirm-icon="x"><?= ikon('x') ?></a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="muted">—</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
