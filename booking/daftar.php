<?php
// ============================================================
//  booking/daftar.php - daftar semua booking (admin: lihat saja, kasir: lihat + kelola lewat Detail)
//  Ada filter + halaman (pagination).
// ============================================================
require_once '../auth.php';

$judul_halaman = $user['level'] === 'admin' ? 'Data Booking' : 'Daftar Booking';
$menu_aktif    = 'booking';

// ---------- 1. Filter dari URL ----------
$dari   = $_GET['dari']   ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-t');
$status = $_GET['status'] ?? '';
$bayar  = $_GET['bayar']  ?? '';
$sumber = $_GET['sumber'] ?? '';
$cari   = trim($_GET['cari'] ?? '');
$halaman = max(1, (int)($_GET['hal'] ?? 1));
$per_halaman = 15;

$tanggal_ok = function ($t) {
    $d = DateTime::createFromFormat('Y-m-d', $t);
    return $d && $d->format('Y-m-d') === $t;
};
if (!$tanggal_ok($dari))   $dari   = date('Y-m-01');
if (!$tanggal_ok($sampai)) $sampai = date('Y-m-t');
if ($dari > $sampai) {
    $tmp = $dari;
    $dari = $sampai;
    $sampai = $tmp;
}
if (!in_array($status, ['Booking', 'Selesai', 'Cancel'], true)) $status = '';
if (!in_array($bayar, ['DP', 'Lunas'], true))                   $bayar = '';
if (!in_array($sumber, ['Offline', 'WhatsApp'], true))          $sumber = '';

// ---------- 2. Susun WHERE ----------
$where = "b.tanggal_main BETWEEN ? AND ?";
$param = [$dari, $sampai];
if ($status !== '') {
    $where .= " AND b.status = ?";
    $param[] = $status;
}
if ($bayar !== '') {
    $where .= " AND b.status_bayar = ?";
    $param[] = $bayar;
}
if ($sumber !== '') {
    $where .= " AND b.sumber = ?";
    $param[] = $sumber;
}
if ($cari !== '') {
    $where .= " AND (b.kode_booking LIKE ? OR b.nama_penyewa LIKE ? OR b.no_hp LIKE ?)";
    $like = '%' . $cari . '%';
    array_push($param, $like, $like, $like);
}

// ---------- 3. Hitung total data lalu ambil satu halaman ----------
$stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_booking b WHERE $where");
$stmt->execute($param);
$total_data  = (int)$stmt->fetchColumn();
$total_hal   = max(1, (int)ceil($total_data / $per_halaman));
if ($halaman > $total_hal) $halaman = $total_hal;
$offset = ($halaman - 1) * $per_halaman;

// LIMIT/OFFSET diisi langsung (sudah dipastikan angka lewat (int))
$stmt = $pdo->prepare(
    "SELECT b.*, l.nama_lapangan, w.jam_mulai, w.jam_selesai
     FROM tb_booking b
     JOIN tb_lapangan l ON l.id_lapangan = b.id_lapangan
     JOIN tb_waktu w    ON w.id_waktu = b.id_waktu
     WHERE $where
     ORDER BY b.tanggal_main DESC, w.jam_mulai DESC
     LIMIT " . (int)$per_halaman . " OFFSET " . (int)$offset
);
$stmt->execute($param);
$daftar = $stmt->fetchAll();

// Link halaman: pertahankan filter, ganti nomor halaman saja
$link_hal = function ($n) {
    $q = $_GET;
    $q['hal'] = $n;
    return '?' . http_build_query($q);
};

include '../inc/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2><?= e($judul_halaman) ?></h2>
        <?php if ($user['level'] === 'kasir'): ?>
            <a class="btn btn-primary btn-sm" href="../kasir/booking_form.php"><i class="bi bi-plus-lg"></i> Input Booking</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end mb-4">
            <div class="col-6 col-md-2"><label class="form-label">Dari</label><input class="form-control" type="date" name="dari" value="<?= e($dari) ?>"></div>
            <div class="col-6 col-md-2"><label class="form-label">Sampai</label><input class="form-control" type="date" name="sampai" value="<?= e($sampai) ?>"></div>
            <div class="col-4 col-md-1">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">Semua</option>
                    <?php foreach (['Booking', 'Selesai', 'Cancel'] as $s): ?><option <?= $status === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-4 col-md-1">
                <label class="form-label">Bayar</label>
                <select class="form-select" name="bayar">
                    <option value="">Semua</option>
                    <?php foreach (['DP', 'Lunas'] as $s): ?><option <?= $bayar === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-4 col-md-2">
                <label class="form-label">Sumber</label>
                <select class="form-select" name="sumber">
                    <option value="">Semua</option>
                    <?php foreach (['Offline', 'WhatsApp'] as $s): ?><option <?= $sumber === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2"><label class="form-label">Cari</label><input class="form-control" name="cari" value="<?= e($cari) ?>" placeholder="Kode / nama / HP"></div>
            <div class="col-12 col-md-2"><button class="btn btn-primary w-100" type="submit"><i class="bi bi-search"></i> Filter</button></div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Tanggal</th>
                        <th>Lapangan</th>
                        <th>Jam</th>
                        <th>Penyewa</th>
                        <th>Sumber</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Dibayar</th>
                        <th>Bayar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar as $b): ?>
                        <tr>
                            <td><b><?= e($b['kode_booking']) ?></b></td>
                            <td class="nowrap"><?= e(tgl_indo($b['tanggal_main'])) ?></td>
                            <td><?= e($b['nama_lapangan']) ?></td>
                            <td class="nowrap"><?= e(jam($b['jam_mulai'])) ?>–<?= e(jam($b['jam_selesai'])) ?></td>
                            <td><?= e($b['nama_penyewa']) ?><br><small class="text-muted"><?= e($b['no_hp']) ?></small></td>
                            <td><?= badge($b['sumber']) ?></td>
                            <td class="text-end nowrap"><?= rupiah($b['total_harga']) ?></td>
                            <td class="text-end nowrap"><?= rupiah($b['jumlah_dibayar']) ?></td>
                            <td><?= badge($b['status_bayar']) ?></td>
                            <td><?= badge($b['status']) ?></td>
                            <td>
                                <div class="aksi">
                                    <a class="btn btn-primary btn-icon" title="Detail" href="detail.php?id=<?= (int)$b['id_booking'] ?>"><i class="bi bi-eye"></i></a>
                                    <a class="btn btn-light btn-icon" title="Nota" href="nota.php?id=<?= (int)$b['id_booking'] ?>"><i class="bi bi-receipt"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$daftar): ?><tr>
                            <td class="empty" colspan="11">Tidak ada booking yang cocok dengan filter.</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
            <span class="text-muted">Total <?= $total_data ?> data &middot; Halaman <?= $halaman ?> dari <?= $total_hal ?></span>
            <?php if ($total_hal > 1): ?>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= $halaman <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= e($link_hal($halaman - 1)) ?>">&laquo;</a></li>
                        <?php for ($n = max(1, $halaman - 2); $n <= min($total_hal, $halaman + 2); $n++): ?>
                            <li class="page-item <?= $n === $halaman ? 'active' : '' ?>"><a class="page-link" href="<?= e($link_hal($n)) ?>"><?= $n ?></a></li>
                        <?php endfor; ?>
                        <li class="page-item <?= $halaman >= $total_hal ? 'disabled' : '' ?>"><a class="page-link" href="<?= e($link_hal($halaman + 1)) ?>">&raquo;</a></li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../inc/footer.php'; ?>