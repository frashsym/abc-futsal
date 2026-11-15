<?php
// ============================================================
//  index.php - Dashboard (tampilan berbeda untuk admin dan kasir)
// ============================================================
require_once __DIR__ . '/auth.php';

$judul_halaman = 'Home';
$menu_aktif    = 'home';

$tanggal    = date('Y-m-d');
$awal_bulan = date('Y-m-01');
$nama_depan = explode(' ', trim($user['nama']))[0];

// -------- Kartu statistik --------
if ($user['level'] === 'admin') {
    $lapangan_aktif = $pdo->query("SELECT COUNT(*) FROM tb_lapangan WHERE status = 'Tersedia'")->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_booking WHERE tanggal_main = ? AND status <> 'Cancel'");
    $stmt->execute([$tanggal]);
    $booking_hari_ini = $stmt->fetchColumn();

    // Pendapatan bersih bulan ini = uang masuk - refund (berdasarkan tanggal main)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(jumlah_dibayar - jumlah_refund), 0)
                           FROM tb_booking WHERE tanggal_main BETWEEN ? AND ?");
    $stmt->execute([$awal_bulan, $tanggal]);
    $pendapatan_bulan = $stmt->fetchColumn();

    $menunggu = $pdo->query("SELECT COUNT(*) FROM tb_pembatalan WHERE status = 'Menunggu'")->fetchColumn();

    $kartu = [
        ['s1', 'Lapangan Aktif',        $lapangan_aktif,            'bi-bullseye'],
        ['s2', 'Booking Hari Ini',      $booking_hari_ini,          'bi-calendar-check'],
        ['s3', 'Pendapatan Bulan Ini',  rupiah($pendapatan_bulan),  'bi-cash-stack'],
        ['s4', 'Pembatalan Menunggu',   $menunggu,                  'bi-arrow-counterclockwise'],
    ];
} else {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_booking WHERE tanggal_main = ? AND status <> 'Cancel'");
    $stmt->execute([$tanggal]);
    $booking_hari_ini = $stmt->fetchColumn();

    // Booking aktif yang belum lunas (perlu ditagih pelunasan)
    $belum_lunas = $pdo->query("SELECT COUNT(*) FROM tb_booking WHERE status = 'Booking' AND status_bayar = 'DP'")->fetchColumn();

    // Uang yang diterima kasir ini hari ini
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(jumlah), 0) FROM tb_pembayaran
                           WHERE id_kasir = ? AND DATE(tanggal_bayar) = ?");
    $stmt->execute([$user['id'], $tanggal]);
    $setoran_saya = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_pembatalan WHERE id_kasir = ? AND status = 'Menunggu'");
    $stmt->execute([$user['id']]);
    $batal_menunggu = $stmt->fetchColumn();

    $kartu = [
        ['s1', 'Booking Hari Ini',        $booking_hari_ini,    'bi-calendar-check'],
        ['s2', 'Belum Lunas (DP)',        $belum_lunas,         'bi-hourglass-split'],
        ['s3', 'Uang Masuk Saya Hari Ini', rupiah($setoran_saya), 'bi-cash-stack'],
        ['s4', 'Pengajuan Batal Saya',    $batal_menunggu,      'bi-arrow-counterclockwise'],
    ];
}

// -------- Jadwal hari ini (ringkas) --------
include __DIR__ . '/inc/hitung_slot.php';   // hasil: $daftar_lapangan, $daftar_waktu, $slot

// -------- Daftar booking hari ini --------
$stmt = $pdo->prepare(
    "SELECT b.*, l.nama_lapangan, w.jam_mulai, w.jam_selesai
     FROM tb_booking b
     JOIN tb_lapangan l ON l.id_lapangan = b.id_lapangan
     JOIN tb_waktu w ON w.id_waktu = b.id_waktu
     WHERE b.tanggal_main = ? AND b.status <> 'Cancel'
     ORDER BY w.jam_mulai, l.nama_lapangan"
);
$stmt->execute([$tanggal]);
$booking_list = $stmt->fetchAll();

include __DIR__ . '/inc/header.php';
?>

<div class="welcome">
    <h1>Halo, <?= e($nama_depan) ?>! 👋</h1>
    <p><?= $user['level'] === 'admin'
            ? 'Pantau jadwal lapangan, pemasukan, dan pengajuan pembatalan dari satu dashboard.'
            : 'Ada pesanan masuk? Cek jadwal dulu, lalu catat bookingnya.' ?></p>
    <?php if ($user['level'] === 'kasir'): ?>
        <a class="btn btn-lime" href="kasir/booking_form.php"><i class="bi bi-plus-lg"></i> Input Booking</a>
    <?php else: ?>
        <a class="btn btn-lime" href="admin/laporan.php"><i class="bi bi-file-earmark-bar-graph"></i> Lihat Laporan</a>
    <?php endif; ?>
</div>

<div class="row g-3 mb-4">
    <?php foreach ($kartu as $k): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat <?= $k[0] ?>">
                <div class="label"><?= e($k[1]) ?></div>
                <div class="num"><?= e($k[2]) ?></div>
                <i class="bi <?= $k[3] ?> bg-ic"></i>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header">
        <h2>Jadwal Hari Ini &middot; <?= e(hari_indo($tanggal) . ', ' . tgl_indo($tanggal)) ?></h2>
        <div class="legend">
            <span><i style="background:rgba(196,245,66,.7)"></i>Kosong</span>
            <span><i style="background:var(--pink)"></i>Terisi</span>
            <span><i style="background:var(--line)"></i>Lewat / tidak tersedia</span>
        </div>
    </div>
    <div class="card-body">
        <?php if (!$daftar_lapangan || !$daftar_waktu): ?>
            <p class="text-muted mb-0">Data lapangan atau jam operasional belum diisi.</p>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($daftar_lapangan as $lap): ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="court">
                            <h3><?= e($lap['nama_lapangan']) ?> <?= badge($lap['status']) ?></h3>
                            <div class="meta"><?= e($lap['jenis_lantai']) ?></div>
                            <div class="slots">
                                <?php foreach ($daftar_waktu as $w): ?>
                                    <span class="slot <?= e($slot[$lap['id_lapangan']][$w['id_waktu']]['status']) ?>"><?= e(jam($w['jam_mulai'])) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Booking Hari Ini</h2>
        <a class="btn btn-light btn-sm" href="booking/daftar.php">Lihat semua</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Jam</th>
                        <th>Lapangan</th>
                        <th>Penyewa</th>
                        <th>Sumber</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($booking_list as $b): ?>
                        <tr>
                            <td><b><?= e($b['kode_booking']) ?></b></td>
                            <td class="nowrap"><?= e(jam($b['jam_mulai'])) ?>–<?= e(jam($b['jam_selesai'])) ?></td>
                            <td><?= e($b['nama_lapangan']) ?></td>
                            <td><?= e($b['nama_penyewa']) ?></td>
                            <td><?= badge($b['sumber']) ?></td>
                            <td class="nowrap"><?= rupiah($b['total_harga']) ?></td>
                            <td><?= badge($b['status_bayar']) ?></td>
                            <td><a class="btn btn-light btn-sm" href="booking/detail.php?id=<?= (int)$b['id_booking'] ?>">Detail</a></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$booking_list): ?><tr>
                            <td class="empty" colspan="8">Belum ada booking untuk hari ini.</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/inc/footer.php'; ?>