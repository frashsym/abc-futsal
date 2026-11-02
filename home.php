<?php
$hari_ini = date('Y-m-d');
$bulan_awal = date('Y-m-01');
$uid = (int)$user['id'];
$nama_depan = explode(' ', trim($user['nama']))[0];

if (is_staff()) {
    $n_lapangan = (int)db_val("SELECT COUNT(*) FROM lapangan WHERE status='Tersedia'");
    $n_hari_ini = (int)db_val("SELECT COUNT(*) FROM booking WHERE tanggal_main=? AND status<>'Cancel'", [$hari_ini]);
    // Pemasukan = uang yang benar-benar diterima (DP + pelunasan - refund), dihitung dari tanggal bayar
    $masuk_bulan = (int)db_val("SELECT COALESCE(SUM(jumlah),0) FROM pembayaran WHERE DATE(created_at) BETWEEN ? AND ?", [$bulan_awal, $hari_ini]);
    $masuk_hari  = (int)db_val("SELECT COALESCE(SUM(jumlah),0) FROM pembayaran WHERE DATE(created_at) = ?", [$hari_ini]);
    $piutang     = (int)db_val("SELECT COALESCE(SUM(total_bayar - dibayar),0) FROM booking WHERE status='Booking'");
    $n_belum     = (int)db_val("SELECT COUNT(*) FROM booking WHERE status='Booking' AND dibayar < total_bayar");
    if (is_admin()) {
        $stat = [
            ['s1', 'Lapangan Aktif', $n_lapangan, 'court'],
            ['s2', 'Booking Hari Ini', $n_hari_ini, 'calendar'],
            ['s3', 'Pemasukan Bulan Ini', rupiah($masuk_bulan), 'dollar'],
            ['s4', 'Sisa Tagihan', rupiah($piutang), 'clock'],
        ];
    } else {
        $stat = [
            ['s1', 'Booking Hari Ini', $n_hari_ini, 'calendar'],
            ['s2', 'Pemasukan Hari Ini', rupiah($masuk_hari), 'dollar'],
            ['s3', 'Booking Belum Lunas', $n_belum, 'clock'],
            ['s4', 'Sisa Tagihan', rupiah($piutang), 'report'],
        ];
    }
    $terbaru = db_all(
        "SELECT b.*, l.nama_lapangan, w.jam_mulai, w.jam_selesai FROM booking b
         JOIN lapangan l ON l.id_lapangan=b.id_lapangan JOIN waktu w ON w.id_waktu=b.id_waktu
         ORDER BY b.created_at DESC, b.id_booking DESC LIMIT 6"
    );
} else {
    $n_aktif   = (int)db_val("SELECT COUNT(*) FROM booking WHERE id_pengguna=? AND status='Booking'", [$uid]);
    $n_selesai = (int)db_val("SELECT COUNT(*) FROM booking WHERE id_pengguna=? AND status='Selesai'", [$uid]);
    $n_cancel  = (int)db_val("SELECT COUNT(*) FROM booking WHERE id_pengguna=? AND status='Cancel'", [$uid]);
    $sisa_user = (int)db_val("SELECT COALESCE(SUM(total_bayar - dibayar),0) FROM booking WHERE id_pengguna=? AND status='Booking'", [$uid]);
    $stat = [
        ['s1', 'Booking Aktif', $n_aktif, 'calendar'],
        ['s2', 'Main Selesai', $n_selesai, 'check'],
        ['s3', 'Dibatalkan', $n_cancel, 'x'],
        ['s4', 'Sisa Bayar di Kasir', rupiah($sisa_user), 'dollar'],
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
            ? 'Pantau jadwal lapangan dan pemasukan dari satu dashboard.'
            : (is_kasir()
                ? 'Catat booking customer yang datang langsung dan terima pelunasannya di sini.'
                : 'Mau main kapan? Cek slot yang masih kosong terus langsung booking, DP-nya bisa lewat online.') ?></p>
    <a class="btn btn-lime" href="?page=booking&aksi=tambah"><?= ikon('plus') ?> <?= is_staff() ? 'Booking Customer' : 'Booking Sekarang' ?></a>
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
        <h2><?= is_staff() ? 'Booking Terbaru' : 'Booking Kamu' ?></h2>
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
                        <th>Pembayaran</th>
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
                            <td><?= badge_bayar($b) ?></td>
                            <td><?= badge_status($b['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$terbaru): ?><tr>
                            <td class="empty" colspan="8">Belum ada booking. Yuk bikin yang pertama! ⚽</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>