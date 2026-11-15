<?php
// ============================================================
//  booking/nota.php - nota / struk pembayaran (siap cetak)
//  Berisi data booking + semua pembayaran yang sudah masuk.
// ============================================================
require_once '../auth.php';

$judul_halaman = 'Nota Booking';
$menu_aktif    = 'booking';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT b.*, l.nama_lapangan, w.jam_mulai, w.jam_selesai, k.nama_lengkap AS nama_kasir
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

$stmt = $pdo->prepare("SELECT * FROM tb_pembayaran WHERE id_booking = ? ORDER BY tanggal_bayar, id_pembayaran");
$stmt->execute([$id]);
$pembayaran = $stmt->fetchAll();

$sisa = ($b['status'] === 'Cancel') ? 0 : (int)$b['total_harga'] - (int)$b['jumlah_dibayar'];

include '../inc/header.php';
?>
<div class="no-print d-flex justify-content-center gap-2 mb-3">
    <a class="btn btn-light" href="detail.php?id=<?= $id ?>"><i class="bi bi-arrow-left"></i> Kembali</a>
    <button class="btn btn-primary" type="button" onclick="window.print()"><i class="bi bi-printer"></i> Cetak</button>
</div>

<div class="nota-wrap">
    <h3>ABC FUTSAL</h3>
    <div class="sub">Nota Penyewaan Lapangan</div>
    <hr>
    <table>
        <tr>
            <td>No. Booking</td>
            <td><?= e($b['kode_booking']) ?></td>
        </tr>
        <tr>
            <td>Dicetak</td>
            <td><?= e(tgl_indo(date('Y-m-d'))) ?> <?= date('H:i') ?></td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td><?= e($b['nama_kasir']) ?></td>
        </tr>
    </table>
    <hr>
    <table>
        <tr>
            <td>Penyewa</td>
            <td><?= e($b['nama_penyewa']) ?></td>
        </tr>
        <tr>
            <td>No. HP</td>
            <td><?= e($b['no_hp']) ?></td>
        </tr>
        <tr>
            <td>Lapangan</td>
            <td><?= e($b['nama_lapangan']) ?></td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td><?= e(hari_indo($b['tanggal_main'])) ?>, <?= e(tgl_indo($b['tanggal_main'])) ?></td>
        </tr>
        <tr>
            <td>Jam</td>
            <td><?= e(jam($b['jam_mulai'])) ?> - <?= e(jam($b['jam_selesai'])) ?></td>
        </tr>
    </table>
    <hr>
    <table class="tebal">
        <tr>
            <td>Total Harga</td>
            <td><?= rupiah($b['total_harga']) ?></td>
        </tr>
    </table>
    <hr>
    <div>Pembayaran:</div>
    <table>
        <?php foreach ($pembayaran as $p): ?>
            <tr>
                <td><?= e(date('d/m/y', strtotime($p['tanggal_bayar']))) ?> <?= e($p['tipe']) ?> (<?= e($p['metode']) ?>)</td>
                <td><?= rupiah($p['jumlah']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <hr>
    <table>
        <tr>
            <td>Total Dibayar</td>
            <td><?= rupiah($b['jumlah_dibayar']) ?></td>
        </tr>
        <?php if ($b['jumlah_refund'] > 0): ?><tr>
                <td>Refund</td>
                <td><?= rupiah($b['jumlah_refund']) ?></td>
            </tr><?php endif; ?>
        <tr class="tebal">
            <td>Sisa Bayar</td>
            <td><?= rupiah($sisa) ?></td>
        </tr>
    </table>
    <div class="stempel">
        <?= $b['status'] === 'Cancel' ? 'DIBATALKAN' : ($b['status_bayar'] === 'Lunas' ? 'LUNAS' : 'DP - BELUM LUNAS') ?>
    </div>
    <div class="sub">Harap datang tepat waktu.<br>Terima kasih telah bermain di ABC Futsal!</div>
</div>
<?php include '../inc/footer.php'; ?>