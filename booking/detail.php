<?php
// ============================================================
//  booking/detail.php - rincian satu booking.
//  Kasir bisa: catat pelunasan, tandai selesai, ajukan pembatalan, cetak nota.
//  Admin hanya melihat (pembatalan diputuskan di menu Pembatalan & Refund).
// ============================================================
require_once '../auth.php';

$judul_halaman = 'Detail Booking';
$menu_aktif    = 'booking';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT b.*, l.nama_lapangan, l.jenis_lantai, w.jam_mulai, w.jam_selesai, k.nama_lengkap AS nama_kasir
     FROM tb_booking b
     JOIN tb_lapangan l ON l.id_lapangan = b.id_lapangan
     JOIN tb_waktu w    ON w.id_waktu = b.id_waktu
     JOIN tb_pengguna k ON k.id_pengguna = b.id_kasir
     WHERE b.id_booking = ?"
);
$stmt->execute([$id]);
$b = $stmt->fetch();
if (!$b) {
    $_SESSION['pesan'] = ['danger', 'Booking tidak ditemukan.'];
    header('Location: daftar.php');
    exit;
}

// Riwayat pembayaran
$stmt = $pdo->prepare("SELECT p.*, k.nama_lengkap AS nama_kasir FROM tb_pembayaran p
                       JOIN tb_pengguna k ON k.id_pengguna = p.id_kasir
                       WHERE p.id_booking = ? ORDER BY p.tanggal_bayar, p.id_pembayaran");
$stmt->execute([$id]);
$pembayaran = $stmt->fetchAll();

// Riwayat pengajuan pembatalan
$stmt = $pdo->prepare("SELECT * FROM tb_pembatalan WHERE id_booking = ? ORDER BY tgl_pengajuan DESC");
$stmt->execute([$id]);
$pembatalan = $stmt->fetchAll();
$ada_pengajuan = false;
foreach ($pembatalan as $c) {
    if ($c['status'] === 'Menunggu') $ada_pengajuan = true;
}

$sisa = (int)$b['total_harga'] - (int)$b['jumlah_dibayar'];
if ($b['status'] === 'Cancel') $sisa = 0;

// Link kirim nota lewat WhatsApp (nomor 08xx diubah jadi 628xx)
$nomor_wa = preg_replace('/\D/', '', $b['no_hp']);
if (substr($nomor_wa, 0, 1) === '0') $nomor_wa = '62' . substr($nomor_wa, 1);
$teks_wa = "Halo {$b['nama_penyewa']}, berikut rincian booking di ABC Futsal:\n"
    . "Kode: {$b['kode_booking']}\n"
    . "Lapangan: {$b['nama_lapangan']}\n"
    . "Jadwal: " . hari_indo($b['tanggal_main']) . ", " . tgl_indo($b['tanggal_main']) . " pukul " . jam($b['jam_mulai']) . "-" . jam($b['jam_selesai']) . "\n"
    . "Total: " . rupiah($b['total_harga']) . "\n"
    . "Dibayar: " . rupiah($b['jumlah_dibayar']) . " (" . $b['status_bayar'] . ")\n"
    . ($sisa > 0 ? "Sisa bayar: " . rupiah($sisa) . "\n" : "")
    . "Terima kasih!";
$link_wa = 'https://wa.me/' . $nomor_wa . '?text=' . rawurlencode($teks_wa);

$bisa_kelola = ($user['level'] === 'kasir' && $b['status'] === 'Booking');

include '../inc/header.php';
?>
<div class="row g-3">
    <!-- Kolom kiri: rincian -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h2><?= e($b['kode_booking']) ?></h2>
                <div class="d-flex gap-2 flex-wrap">
                    <a class="btn btn-light btn-sm" href="daftar.php"><i class="bi bi-arrow-left"></i> Kembali</a>
                    <a class="btn btn-primary btn-sm" href="nota.php?id=<?= $id ?>"><i class="bi bi-printer"></i> Cetak Nota</a>
                    <a class="btn btn-success btn-sm" href="<?= e($link_wa) ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Kirim via WA</a>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3"><?= badge($b['status']) ?> <?= badge($b['status_bayar']) ?> <?= badge($b['sumber']) ?></div>
                <div class="info-list">
                    <div><small>Penyewa</small><b><?= e($b['nama_penyewa']) ?></b></div>
                    <div><small>No. HP</small><b><?= e($b['no_hp']) ?></b></div>
                    <div><small>Lapangan</small><b><?= e($b['nama_lapangan']) ?></b> <span class="text-muted">(<?= e($b['jenis_lantai']) ?>)</span></div>
                    <div><small>Jadwal</small><b><?= e(hari_indo($b['tanggal_main'])) ?>, <?= e(tgl_indo($b['tanggal_main'])) ?></b><br><?= e(jam($b['jam_mulai'])) ?> – <?= e(jam($b['jam_selesai'])) ?></div>
                    <div><small>Total Harga</small><b><?= rupiah($b['total_harga']) ?></b></div>
                    <div><small>Sudah Dibayar</small><b><?= rupiah($b['jumlah_dibayar']) ?></b></div>
                    <div><small>Sisa Tagihan</small><b><?= rupiah($sisa) ?></b></div>
                    <div><small>Dicatat Oleh</small><b><?= e($b['nama_kasir']) ?></b><br><span class="text-muted"><?= e(substr($b['created_at'], 0, 16)) ?></span></div>
                    <?php if ($b['jumlah_refund'] > 0): ?><div><small>Refund</small><b><?= rupiah($b['jumlah_refund']) ?></b></div><?php endif; ?>
                    <?php if ($b['catatan']): ?><div style="grid-column:1/-1"><small>Catatan</small><b><?= e($b['catatan']) ?></b></div><?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Riwayat Pembayaran</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Metode</th>
                                <th class="text-end">Jumlah</th>
                                <th>Kasir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pembayaran as $p): ?>
                                <tr>
                                    <td class="nowrap"><?= e(tgl_indo(substr($p['tanggal_bayar'], 0, 10))) ?> <?= e(substr($p['tanggal_bayar'], 11, 5)) ?></td>
                                    <td><?= e($p['tipe']) ?></td>
                                    <td><?= e($p['metode']) ?></td>
                                    <td class="text-end nowrap"><?= rupiah($p['jumlah']) ?></td>
                                    <td><?= e($p['nama_kasir']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$pembayaran): ?><tr>
                                    <td class="empty" colspan="5">Belum ada pembayaran.</td>
                                </tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($pembatalan): ?>
            <div class="card">
                <div class="card-header">
                    <h2>Riwayat Pembatalan</h2>
                </div>
                <div class="card-body">
                    <?php foreach ($pembatalan as $c): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <?= badge($c['status']) ?> <span class="text-muted">diajukan <?= e(tgl_indo(substr($c['tgl_pengajuan'], 0, 10))) ?></span><br>
                            Alasan: <?= e($c['alasan']) ?><br>
                            Refund diajukan: <b><?= rupiah($c['refund_diajukan']) ?></b>
                            <?php if ($c['status'] === 'Disetujui'): ?> &middot; Refund disetujui: <b><?= rupiah($c['refund_disetujui']) ?></b><?php endif; ?>
                            <?php if ($c['catatan_admin']): ?><br><span class="text-muted">Catatan admin: <?= e($c['catatan_admin']) ?></span><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($user['level'] === 'admin' && $ada_pengajuan): ?>
                        <a class="btn btn-primary btn-sm" href="../admin/pembatalan.php">Proses di menu Pembatalan &amp; Refund</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Kolom kanan: tindakan kasir -->
    <?php if ($bisa_kelola): ?>
        <div class="col-lg-5">
            <?php if ($ada_pengajuan): ?>
                <div class="alert alert-warning">Ada pengajuan pembatalan yang menunggu keputusan admin. Pembayaran dan penyelesaian ditahan dulu.</div>
            <?php else: ?>

                <?php if ($b['status_bayar'] === 'DP'): ?>
                    <div class="card">
                        <div class="card-header">
                            <h2>Catat Pelunasan</h2>
                        </div>
                        <div class="card-body">
                            <form method="post" action="../kasir/bayar_simpan.php" autocomplete="off">
                                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                                <input type="hidden" name="id_booking" value="<?= $id ?>">
                                <div class="mb-3">
                                    <label class="form-label" for="jumlah_bayar">Nominal Diterima (Rp)</label>
                                    <input class="form-control" type="number" id="jumlah_bayar" name="jumlah_bayar" value="<?= $sisa ?>" min="1" max="<?= $sisa ?>" data-maks="<?= $sisa ?>" required>
                                    <div class="form-text">Sisa tagihan <?= rupiah($sisa) ?>. Isi sebesar sisa untuk melunasi.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="metode">Metode</label>
                                    <select class="form-select" id="metode" name="metode">
                                        <option>Tunai</option>
                                        <option>Transfer</option>
                                    </select>
                                </div>
                                <div class="text-muted mb-3">Sisa setelah pembayaran: <b data-total-tampil="jumlah_bayar"></b></div>
                                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-check-lg"></i> Simpan Pembayaran</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-header">
                            <h2>Selesaikan Booking</h2>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">Booking sudah <b>LUNAS</b>. Setelah penyewa selesai main, tandai booking ini selesai.</p>
                            <?php if ($b['tanggal_main'] > date('Y-m-d')): ?>
                                <button class="btn btn-light w-100" disabled>Menunggu tanggal main (<?= e(tgl_indo($b['tanggal_main'])) ?>)</button>
                            <?php else: ?>
                                <form method="post" action="../kasir/selesai_proses.php" data-confirm="Tandai booking ini selesai?">
                                    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                                    <input type="hidden" name="id_booking" value="<?= $id ?>">
                                    <button class="btn btn-success w-100" type="submit"><i class="bi bi-check2-circle"></i> Tandai Selesai</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h2>Ajukan Pembatalan</h2>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Pembatalan harus disetujui admin. Refund (jika ada) dikembalikan manual setelah disetujui.</p>
                        <form method="post" action="../kasir/pembatalan_ajukan.php" autocomplete="off" data-confirm="Kirim pengajuan pembatalan ke admin?">
                            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                            <input type="hidden" name="id_booking" value="<?= $id ?>">
                            <div class="mb-3">
                                <label class="form-label" for="alasan">Alasan</label>
                                <input class="form-control" id="alasan" name="alasan" maxlength="255" required placeholder="Contoh: penyewa berhalangan">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="refund_diajukan">Refund yang diajukan (Rp)</label>
                                <input class="form-control" type="number" id="refund_diajukan" name="refund_diajukan" value="0" min="0" max="<?= (int)$b['jumlah_dibayar'] ?>" step="1000">
                                <div class="form-text">Maksimal sebesar uang yang sudah dibayar (<?= rupiah($b['jumlah_dibayar']) ?>).</div>
                            </div>
                            <button class="btn btn-danger w-100" type="submit"><i class="bi bi-x-octagon"></i> Ajukan Pembatalan</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php include '../inc/footer.php'; ?>