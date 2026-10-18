<?php
$dari   = $_GET['dari'] ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$status = $_GET['status'] ?? '';
if (!tanggal_valid($dari))   $dari = date('Y-m-01');
if (!tanggal_valid($sampai)) $sampai = date('Y-m-d');
if ($dari > $sampai) {
    $t = $dari;
    $dari = $sampai;
    $sampai = $t;
}
if (!in_array($status, ['Booking', 'Selesai', 'Cancel'], true)) $status = '';

$sql = "SELECT b.*, l.nama_lapangan, w.jam_mulai, w.jam_selesai
        FROM booking b
        JOIN lapangan l ON l.id_lapangan = b.id_lapangan
        JOIN waktu w ON w.id_waktu = b.id_waktu
        WHERE b.tanggal_main BETWEEN ? AND ?";
$p = [$dari, $sampai];
if ($status !== '') {
    $sql .= " AND b.status = ?";
    $p[] = $status;
}
$sql .= " ORDER BY b.tanggal_main, w.jam_mulai";
$rows = db_all($sql, $p);

$n = ['Booking' => 0, 'Selesai' => 0, 'Cancel' => 0];
$pendapatan = 0;
$per_lap = [];
foreach ($rows as $r) {
    $n[$r['status']]++;
    $k = $r['nama_lapangan'];
    if (!isset($per_lap[$k])) $per_lap[$k] = ['jml' => 0, 'selesai' => 0, 'total' => 0];
    if ($r['status'] !== 'Cancel') $per_lap[$k]['jml']++;
    if ($r['status'] === 'Selesai') {
        $pendapatan += $r['total_bayar'];
        $per_lap[$k]['selesai']++;
        $per_lap[$k]['total'] += $r['total_bayar'];
    }
}
ksort($per_lap);
?>
<div class="card">
    <div class="header">
        <h2>LAPORAN REKAP PENYEWAAN</h2>
        <div class="header-actions no-print">
            <button class="btn btn-light btn-sm" type="button" data-modal-open="smallModal2"><?= ikon('calendar') ?> Ganti Periode</button>
            <button class="btn btn-primary btn-sm" type="button" onclick="window.print()"><?= ikon('printer') ?> Cetak</button>
        </div>
    </div>
    <div class="body">
        <div class="rekap-head">
            <h2>Laporan Rekap Penyewaan Lapangan Futsal “ABC”</h2>
            <div class="muted">Periode <?= e(tgl_indo($dari)) ?> s/d <?= e(tgl_indo($sampai)) ?><?= $status ? ' &middot; Status: ' . e($status) : '' ?></div>
        </div>

        <div class="grid grid-4" style="margin-bottom:22px">
            <div class="stat s1">
                <div class="label">Total Booking</div>
                <div class="num"><?= count($rows) ?></div><?= ikon('calendar', 'bg-ic') ?>
            </div>
            <div class="stat s2">
                <div class="label">Selesai</div>
                <div class="num"><?= $n['Selesai'] ?></div><?= ikon('check', 'bg-ic') ?>
            </div>
            <div class="stat s3">
                <div class="label">Cancel</div>
                <div class="num"><?= $n['Cancel'] ?></div><?= ikon('x', 'bg-ic') ?>
            </div>
            <div class="stat s4">
                <div class="label">Pendapatan (Selesai)</div>
                <div class="num"><?= rupiah($pendapatan) ?></div><?= ikon('dollar', 'bg-ic') ?>
            </div>
        </div>

        <h3 style="margin:0 0 10px">Rekap per Lapangan</h3>
        <div class="table-wrap" style="margin-bottom:26px">
            <table class="table">
                <thead>
                    <tr>
                        <th>Lapangan</th>
                        <th>Booking Aktif + Selesai</th>
                        <th>Selesai</th>
                        <th class="text-right">Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($per_lap as $nama => $v): ?>
                        <tr>
                            <td><b><?= e($nama) ?></b></td>
                            <td><?= $v['jml'] ?>x</td>
                            <td><?= $v['selesai'] ?>x</td>
                            <td class="text-right"><?= rupiah($v['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$per_lap): ?><tr>
                            <td class="empty" colspan="4">Tidak ada data di periode ini.</td>
                        </tr><?php endif; ?>
                </tbody>
                <?php if ($per_lap): ?>
                    <tfoot>
                        <tr class="tfoot-total">
                            <td colspan="3">Total Pendapatan</td>
                            <td class="text-right"><?= rupiah($pendapatan) ?></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>

        <h3 style="margin:0 0 10px">Rincian Booking</h3>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Lapangan</th>
                        <th>Jam</th>
                        <th>Penyewa</th>
                        <th>Status</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $i => $r): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= e($r['kode_booking']) ?></td>
                            <td class="nowrap"><?= e(tgl_indo($r['tanggal_main'])) ?></td>
                            <td><?= e($r['nama_lapangan']) ?></td>
                            <td class="nowrap"><?= e(jam($r['jam_mulai'])) ?>–<?= e(jam($r['jam_selesai'])) ?></td>
                            <td><?= e($r['nama_penyewa']) ?></td>
                            <td><?= badge_status($r['status']) ?></td>
                            <td class="text-right nowrap"><?= rupiah($r['total_bayar']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$rows): ?><tr>
                            <td class="empty" colspan="8">Tidak ada booking di periode ini.</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <p class="muted" style="margin-top:14px;font-size:12px">Dicetak <?= e(date('d-m-Y H:i')) ?> oleh <?= e($user['nama']) ?>. Pendapatan dihitung dari booking berstatus Selesai.</p>
    </div>
</div>