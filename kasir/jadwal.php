<?php
// ============================================================
//  kasir/jadwal.php - kasir mengecek ketersediaan jadwal
//  Langkah pertama alur booking: penyewa tanya (WhatsApp / langsung), kasir cek di sini.
//  Klik slot hijau -> langsung ke form Input Booking dengan data terisi.
// ============================================================
$level_wajib = 'kasir';
require_once '../auth.php';

$judul_halaman = 'Cek Jadwal';
$menu_aktif    = 'jadwal';

// Tanggal yang dilihat (default: hari ini)
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$cek = DateTime::createFromFormat('Y-m-d', $tanggal);
if (!$cek || $cek->format('Y-m-d') !== $tanggal) $tanggal = date('Y-m-d');

include '../inc/hitung_slot.php';   // hasil: $daftar_lapangan, $daftar_waktu, $slot

$label_status = ['lewat' => 'Lewat', 'perbaikan' => 'Perbaikan', 'tanpa_harga' => 'Harga belum diatur'];

include '../inc/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2>Jadwal Lapangan &middot; <?= e(hari_indo($tanggal) . ', ' . tgl_indo($tanggal)) ?></h2>
        <form method="get" class="d-flex gap-2 align-items-center">
            <a class="btn btn-light btn-sm" href="?tanggal=<?= date('Y-m-d', strtotime($tanggal . ' -1 day')) ?>"><i class="bi bi-chevron-left"></i></a>
            <input class="form-control form-control-sm" type="date" name="tanggal" value="<?= e($tanggal) ?>" onchange="this.form.submit()" style="width:160px">
            <a class="btn btn-light btn-sm" href="?tanggal=<?= date('Y-m-d', strtotime($tanggal . ' +1 day')) ?>"><i class="bi bi-chevron-right"></i></a>
            <a class="btn btn-light btn-sm" href="?tanggal=<?= date('Y-m-d') ?>">Hari ini</a>
        </form>
    </div>
    <div class="card-body">
        <div class="legend mb-3">
            <span><i style="background:rgba(196,245,66,.7)"></i>Kosong (klik untuk booking)</span>
            <span><i style="background:var(--pink)"></i>Sudah dibooking</span>
            <span><i style="background:var(--line)"></i>Lewat / tidak tersedia</span>
        </div>

        <?php if (!$daftar_lapangan || !$daftar_waktu): ?>
            <p class="text-muted mb-0">Data lapangan atau jam operasional belum diisi oleh admin.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table matrix">
                    <thead>
                        <tr>
                            <th class="text-start">Jam</th>
                            <?php foreach ($daftar_lapangan as $lap): ?>
                                <th><?= e($lap['nama_lapangan']) ?><br><span class="fw-normal text-muted text-capitalize"><?= e($lap['jenis_lantai']) ?></span></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daftar_waktu as $w): ?>
                            <tr>
                                <td class="jam"><?= e(jam($w['jam_mulai'])) ?> – <?= e(jam($w['jam_selesai'])) ?></td>
                                <?php foreach ($daftar_lapangan as $lap):
                                    $s = $slot[$lap['id_lapangan']][$w['id_waktu']]; ?>
                                    <td>
                                        <?php if ($s['status'] === 'kosong'): ?>
                                            <a class="cell kosong"
                                                href="booking_form.php?tanggal=<?= e($tanggal) ?>&lapangan=<?= (int)$lap['id_lapangan'] ?>&waktu=<?= (int)$w['id_waktu'] ?>">
                                                Kosong<small><?= rupiah($s['harga']) ?></small>
                                            </a>
                                        <?php elseif ($s['status'] === 'terisi'): ?>
                                            <a class="cell terisi" href="../booking/detail.php?id=<?= (int)$s['booking']['id_booking'] ?>">
                                                <?= e($s['booking']['nama_penyewa']) ?>
                                                <small><?= e($s['booking']['kode_booking']) ?> · <?= e($s['booking']['status_bayar']) ?></small>
                                            </a>
                                        <?php else: ?>
                                            <span class="cell <?= e($s['status']) ?>"><?= e($label_status[$s['status']] ?? '-') ?></span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include '../inc/footer.php'; ?>