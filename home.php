<?php
$hari_ini = date('Y-m-d');
$bulan_awal = date('Y-m-01');
$uid = (int)$user['id'];
$nama_depan = explode(' ', trim($user['nama']))[0];

if (is_admin()) {
    $n_lapangan = (int)db_val("SELECT COUNT(*) FROM lapangan WHERE status='Tersedia'");
    $n_hari_ini = (int)db_val("SELECT COUNT(*) FROM booking WHERE tanggal_main=? AND status<>'Cancel'", [$hari_ini]);
    $omzet      = (int)db_val("SELECT COALESCE(SUM(total_bayar),0) FROM booking WHERE status='Selesai' AND tanggal_main>=? AND tanggal_main<=?", [$bulan_awal, $hari_ini]);
    $n_pelanggan = (int)db_val("SELECT COUNT(*) FROM pengguna WHERE level='pelanggan'");
    $stat = [
        ['s1', 'Lapangan Aktif', $n_lapangan, 'court'],
        ['s2', 'Booking Hari Ini', $n_hari_ini, 'calendar'],
        ['s3', 'Pendapatan Bulan Ini', rupiah($omzet), 'dollar'],
        ['s4', 'Pelanggan', $n_pelanggan, 'users'],
    ];
    $terbaru = db_all(
        "SELECT b.*, l.nama_lapangan, w.jam_mulai, w.jam_selesai FROM booking b
         JOIN lapangan l ON l.id_lapangan=b.id_lapangan JOIN waktu w ON w.id_waktu=b.id_waktu
         ORDER BY b.created_at DESC, b.id_booking DESC LIMIT 6"
    );
} else {
    $n_aktif   = (int)db_val("SELECT COUNT(*) FROM booking WHERE id_pengguna=? AND status='Booking'", [$uid]);
    $n_selesai = (int)db_val("SELECT COUNT(*) FROM booking WHERE id_pengguna=? AND status='Selesai'", [$uid]);
    $n_cancel  = (int)db_val("SELECT COUNT(*) FROM booking WHERE id_pengguna=? AND status='Cancel'", [$uid]);
    $belanja   = (int)db_val("SELECT COALESCE(SUM(total_bayar),0) FROM booking WHERE id_pengguna=? AND status='Selesai'", [$uid]);
    $stat = [
        ['s1', 'Booking Aktif', $n_aktif, 'calendar'],
        ['s2', 'Main Selesai', $n_selesai, 'check'],
        ['s3', 'Dibatalkan', $n_cancel, 'x'],
        ['s4', 'Total Bayar', rupiah($belanja), 'dollar'],
    ];
    $terbaru = db_all(
        "SELECT b.*, l.nama_lapangan, w.jam_mulai, w.jam_selesai FROM booking b
         JOIN lapangan l ON l.id_lapangan=b.id_lapangan JOIN waktu w ON w.id_waktu=b.id_waktu
         WHERE b.id_pengguna=? ORDER BY b.tanggal_main DESC, w.jam_mulai DESC LIMIT 6",
        [$uid]
    );
}
$lapangan = db_all("SELECT * FROM lapangan ORDER BY nama_lapangan");
$semua_waktu = db_all("SELECT * FROM waktu ORDER BY jam_mulai");
?>
<div class="welcome">
    <h1>Halo, <?= e($nama_depan) ?>! 👋</h1>
    <p><?= is_admin()
            ? 'Pantau jadwal lapangan dan pemasukan hari ini dari satu dashboard.'
            : 'Mau main kapan? Cek slot yang masih kosong terus langsung booking.' ?></p>
    <a class="btn btn-lime" href="?page=booking&aksi=tambah"><?= ikon('plus') ?> Booking Sekarang</a>
</div>

<div class="grid grid-4" style="margin-bottom:22px">
    <?php foreach ($stat as $s): ?>
        <div class="stat <?= $s[0] ?>">
            <div class="label"><?= e($s[1]) ?></div>
            <div class="num"><?= e($s[2]) ?></div>
            <?= ikon($s[3], 'bg-ic') ?>
        </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="header">
        <h2>Jadwal Hari Ini &middot; <?= e(hari_indo($hari_ini) . ', ' . tgl_indo($hari_ini)) ?></h2>
        <div class="legend">
            <span><i style="background:rgba(196,245,66,.7)"></i>Kosong</span>
            <span><i style="background:var(--pink)"></i>Terisi</span>
            <span><i style="background:var(--line)"></i>Sudah lewat</span>
        </div>
    </div>
    <div class="body">
        <?php if (!$lapangan || !$semua_waktu): ?>
            <p class="muted">Data lapangan atau jam belum diisi.</p>
        <?php else: ?>
            <div class="grid grid-3">
                <?php foreach ($lapangan as $l):
                    $tak = $l['status'] === 'Tersedia' ? waktu_tidak_tersedia($hari_ini, $l['id_lapangan']) : []; ?>
                    <div class="court">
                        <h3><?= e($l['nama_lapangan']) ?> <?= badge_status($l['status']) ?></h3>
                        <div class="meta"><?= e($l['jenis_lantai']) ?> &middot; <?= rupiah($l['harga_per_jam']) ?>/jam</div>
                        <?php if ($l['status'] !== 'Tersedia'): ?>
                            <div class="muted">Lagi diperbaiki, belum bisa dipakai.</div>
                        <?php else: ?>
                            <div class="slots">
                                <?php foreach ($semua_waktu as $w):
                                    $st = $tak[(int)$w['id_waktu']] ?? 'free'; ?>
                                    <span class="slot <?= $st ?>"><?= e(jam($w['jam_mulai'])) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="header">
        <h2><?= is_admin() ? 'Booking Terbaru' : 'Booking Kamu' ?></h2>
        <div class="header-actions"><a class="btn btn-light btn-sm" href="?page=booking">Lihat semua</a></div>
    </div>
    <div class="body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Lapangan</th>
                        <th>Jam</th>
                        <th>Penyewa</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($terbaru as $b): ?>
                        <tr>
                            <td><b><?= e($b['kode_booking']) ?></b></td>
                            <td class="nowrap"><?= e(tgl_indo($b['tanggal_main'])) ?></td>
                            <td><?= e($b['nama_lapangan']) ?></td>
                            <td class="nowrap"><?= e(jam($b['jam_mulai'])) ?>–<?= e(jam($b['jam_selesai'])) ?></td>
                            <td><?= e($b['nama_penyewa']) ?></td>
                            <td class="nowrap"><?= rupiah($b['total_bayar']) ?></td>
                            <td><?= badge_status($b['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$terbaru): ?><tr>
                            <td class="empty" colspan="7">Belum ada booking. Yuk bikin yang pertama! ⚽</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>