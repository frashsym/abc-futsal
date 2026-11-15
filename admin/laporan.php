<?php
// ============================================================
//  admin/laporan.php - laporan transaksi keseluruhan (bisa dicetak)
//  Filter: periode tanggal main, status, kasir, sumber pesanan.
// ============================================================
$level_wajib = 'admin';
require_once '../auth.php';

$judul_halaman = 'Laporan Transaksi';
$menu_aktif    = 'laporan';

// ---------- 1. Baca filter dari URL ----------
$dari   = $_GET['dari']   ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
$status = $_GET['status'] ?? '';
$sumber = $_GET['sumber'] ?? '';
$kasir  = (int)($_GET['kasir'] ?? 0);

$tanggal_ok = function ($t) {
    $d = DateTime::createFromFormat('Y-m-d', $t);
    return $d && $d->format('Y-m-d') === $t;
};
if (!$tanggal_ok($dari))   $dari   = date('Y-m-01');
if (!$tanggal_ok($sampai)) $sampai = date('Y-m-d');
if ($dari > $sampai) {
    $tmp = $dari;
    $dari = $sampai;
    $sampai = $tmp;
}
if (!in_array($status, ['Booking', 'Selesai', 'Cancel'], true)) $status = '';
if (!in_array($sumber, ['Offline', 'WhatsApp'], true))          $sumber = '';

// ---------- 2. Ambil data transaksi ----------
$sql = "SELECT b.*, l.nama_lapangan, w.jam_mulai, w.jam_selesai, k.nama_lengkap AS nama_kasir
        FROM tb_booking b
        JOIN tb_lapangan l ON l.id_lapangan = b.id_lapangan
        JOIN tb_waktu w    ON w.id_waktu = b.id_waktu
        JOIN tb_pengguna k ON k.id_pengguna = b.id_kasir
        WHERE b.tanggal_main BETWEEN ? AND ?";
$param = [$dari, $sampai];
if ($status !== '') {
    $sql .= " AND b.status = ?";
    $param[] = $status;
}
if ($sumber !== '') {
    $sql .= " AND b.sumber = ?";
    $param[] = $sumber;
}
if ($kasir > 0) {
    $sql .= " AND b.id_kasir = ?";
    $param[] = $kasir;
}
$sql .= " ORDER BY b.tanggal_main, w.jam_mulai";

$stmt = $pdo->prepare($sql);
$stmt->execute($param);
$rows = $stmt->fetchAll();

$daftar_kasir = $pdo->query("SELECT id_pengguna, nama_lengkap FROM tb_pengguna ORDER BY nama_lengkap")->fetchAll();

// ---------- 3. Hitung ringkasan ----------
$jml_aktif = 0;        // booking yang tidak dibatalkan
$jml_cancel = 0;
$uang_masuk = 0;       // total semua pembayaran yang diterima
$total_refund = 0;     // total refund yang dikembalikan
$piutang = 0;          // sisa tagihan booking yang masih berjalan (belum lunas)
$per_lapangan = [];    // rekap per lapangan

foreach ($rows as $r) {
    $uang_masuk   += $r['jumlah_dibayar'];
    $total_refund += $r['jumlah_refund'];

    $nama_lap = $r['nama_lapangan'];
    if (!isset($per_lapangan[$nama_lap])) $per_lapangan[$nama_lap] = ['jumlah' => 0, 'bersih' => 0];
    $per_lapangan[$nama_lap]['bersih'] += $r['jumlah_dibayar'] - $r['jumlah_refund'];

    if ($r['status'] === 'Cancel') {
        $jml_cancel++;
    } else {
        $jml_aktif++;
        $per_lapangan[$nama_lap]['jumlah']++;
    }
    if ($r['status'] === 'Booking') $piutang += $r['total_harga'] - $r['jumlah_dibayar'];
}
$pendapatan_bersih = $uang_masuk - $total_refund;
ksort($per_lapangan);

include '../inc/header.php';
?>
<div class="card no-print">
    <div class="card-header">
        <h2>Filter Laporan</h2>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-6 col-md-2"><label class="form-label">Dari</label><input class="form-control" type="date" name="dari" value="<?= e($dari) ?>"></div>
            <div class="col-6 col-md-2"><label class="form-label">Sampai</label><input class="form-control" type="date" name="sampai" value="<?= e($sampai) ?>"></div>
            <div class="col-6 col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">Semua</option>
                    <?php foreach (['Booking', 'Selesai', 'Cancel'] as $s): ?><option <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Sumber</label>
                <select class="form-select" name="sumber">
                    <option value="">Semua</option>
                    <?php foreach (['Offline', 'WhatsApp'] as $s): ?><option <?= $sumber === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Kasir</label>
                <select class="form-select" name="kasir">
                    <option value="0">Semua</option>
                    <?php foreach ($daftar_kasir as $k): ?>
                        <option value="<?= (int)$k['id_pengguna'] ?>" <?= $kasir === (int)$k['id_pengguna'] ? 'selected' : '' ?>><?= e($k['nama_lengkap']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2 d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Tampilkan</button>
                <button class="btn btn-light" type="button" onclick="window.print()"><i class="bi bi-printer"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="text-center mb-4">
            <h2 class="h5 fw-bold mb-1">Laporan Transaksi Penyewaan Lapangan Futsal “ABC”</h2>
            <div class="text-muted">Jadwal main: <?= e(tgl_indo($dari)) ?> s/d <?= e(tgl_indo($sampai)) ?>
                <?= $status ? ' · Status: ' . e($status) : '' ?><?= $sumber ? ' · Sumber: ' . e($sumber) : '' ?></div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat s1">
                    <div class="label">Booking Aktif</div>
                    <div class="num"><?= $jml_aktif ?></div><i class="bi bi-calendar-check bg-ic"></i>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat s2">
                    <div class="label">Uang Masuk</div>
                    <div class="num"><?= rupiah($uang_masuk) ?></div><i class="bi bi-cash-stack bg-ic"></i>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat s3">
                    <div class="label">Refund</div>
                    <div class="num"><?= rupiah($total_refund) ?></div><i class="bi bi-arrow-counterclockwise bg-ic"></i>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat s4">
                    <div class="label">Pendapatan Bersih</div>
                    <div class="num"><?= rupiah($pendapatan_bersih) ?></div><i class="bi bi-wallet2 bg-ic"></i>
                </div>
            </div>
        </div>
        <p class="text-muted">Dibatalkan: <b><?= $jml_cancel ?></b> booking &middot; Sisa tagihan booking yang belum lunas: <b><?= rupiah($piutang) ?></b></p>

        <div class="table-responsive mb-4">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Kode</th>
                        <th>Lapangan</th>
                        <th>Jam</th>
                        <th>Penyewa</th>
                        <th>Sumber</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Dibayar</th>
                        <th class="text-end">Refund</th>
                        <th>Bayar</th>
                        <th>Status</th>
                        <th>Kasir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $i => $r): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="nowrap"><?= e(tgl_indo($r['tanggal_main'])) ?></td>
                            <td><?= e($r['kode_booking']) ?></td>
                            <td><?= e($r['nama_lapangan']) ?></td>
                            <td class="nowrap"><?= e(jam($r['jam_mulai'])) ?>–<?= e(jam($r['jam_selesai'])) ?></td>
                            <td><?= e($r['nama_penyewa']) ?></td>
                            <td><?= e($r['sumber']) ?></td>
                            <td class="text-end nowrap"><?= rupiah($r['total_harga']) ?></td>
                            <td class="text-end nowrap"><?= rupiah($r['jumlah_dibayar']) ?></td>
                            <td class="text-end nowrap"><?= $r['jumlah_refund'] ? rupiah($r['jumlah_refund']) : '-' ?></td>
                            <td><?= badge($r['status_bayar']) ?></td>
                            <td><?= badge($r['status']) ?></td>
                            <td><?= e($r['nama_kasir']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$rows): ?><tr>
                            <td class="empty" colspan="13">Tidak ada transaksi pada periode ini.</td>
                        </tr><?php endif; ?>
                </tbody>
                <?php if ($rows): ?>
                    <tfoot>
                        <tr>
                            <td colspan="8" class="text-end">TOTAL</td>
                            <td class="text-end nowrap"><?= rupiah($uang_masuk) ?></td>
                            <td class="text-end nowrap"><?= rupiah($total_refund) ?></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>

        <?php if ($per_lapangan): ?>
            <h3 class="h6 fw-bold">Rekap per Lapangan</h3>
            <div class="table-responsive">
                <table class="table" style="max-width:520px">
                    <thead>
                        <tr>
                            <th>Lapangan</th>
                            <th class="text-end">Booking Aktif</th>
                            <th class="text-end">Pendapatan Bersih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($per_lapangan as $nama => $x): ?>
                            <tr>
                                <td><?= e($nama) ?></td>
                                <td class="text-end"><?= $x['jumlah'] ?></td>
                                <td class="text-end"><?= rupiah($x['bersih']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include '../inc/footer.php'; ?>