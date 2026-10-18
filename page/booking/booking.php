<?php
$filter = $_GET['status'] ?? '';
if (!in_array($filter, ['Booking', 'Selesai', 'Cancel'], true)) $filter = '';

$sql = "SELECT b.*, l.nama_lapangan, w.jam_mulai, w.jam_selesai
        FROM booking b
        JOIN lapangan l ON l.id_lapangan = b.id_lapangan
        JOIN waktu w ON w.id_waktu = b.id_waktu WHERE 1=1";
$p = [];
if (!is_admin()) {
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
            <h2>BOOKING LAPANGAN</h2>
            <div class="header-actions">
                <a class="btn btn-primary" href="?page=booking&aksi=tambah"><?= ikon('plus') ?> Booking Baru</a>
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
                            <th>Jam</th>
                            <th>Penyewa</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $i => $r): $aktif_b = $r['status'] === 'Booking'; ?>
                            <tr>
                                <td class="no"><?= $i + 1 ?></td>
                                <td><b><?= e($r['kode_booking']) ?></b></td>
                                <td class="nowrap"><?= e(hari_indo($r['tanggal_main']) . ', ' . tgl_indo($r['tanggal_main'])) ?></td>
                                <td><?= e($r['nama_lapangan']) ?></td>
                                <td class="nowrap"><?= e(jam($r['jam_mulai'])) ?>–<?= e(jam($r['jam_selesai'])) ?></td>
                                <td><?= e($r['nama_penyewa']) ?><?php if ($r['no_hp']): ?><br><small class="muted"><?= e($r['no_hp']) ?></small><?php endif; ?></td>
                                <td class="nowrap"><?= rupiah($r['total_bayar']) ?></td>
                                <td><?= badge_status($r['status']) ?></td>
                                <td>
                                    <div class="aksi">
                                        <?php if ($aktif_b): ?>
                                            <?php if (is_admin()): ?>
                                                <a class="btn btn-ok btn-icon" title="Tandai selesai" href="<?= url_aksi('booking', 'selesai', $r['id_booking']) ?>" data-confirm="Tandai booking <?= e($r['kode_booking']) ?> sebagai selesai?"><?= ikon('check') ?></a>
                                            <?php endif; ?>
                                            <a class="btn btn-warn btn-icon" title="Ubah" href="?page=booking&aksi=ubah&id=<?= (int)$r['id_booking'] ?>"><?= ikon('edit') ?></a>
                                            <a class="btn btn-danger btn-icon" title="Cancel" href="<?= url_aksi('booking', 'cancel', $r['id_booking']) ?>" data-confirm="Batalkan booking <?= e($r['kode_booking']) ?>?"><?= ikon('x') ?></a>
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