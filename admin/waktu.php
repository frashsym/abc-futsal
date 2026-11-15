<?php
// admin/waktu.php - daftar jam operasional
$level_wajib = 'admin';
require_once '../auth.php';

$judul_halaman = 'Jam Operasional';
$menu_aktif    = 'waktu';

$daftar = $pdo->query("SELECT * FROM tb_waktu ORDER BY jam_mulai")->fetchAll();

include '../inc/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2>Jam Operasional (Slot Sewa)</h2>
        <a class="btn btn-primary btn-sm" href="waktu_form.php"><i class="bi bi-plus-lg"></i> Tambah Jam</a>
    </div>
    <div class="card-body">
        <p class="text-muted">Setiap baris adalah satu slot yang bisa disewa. Harga tiap slot diatur di menu <a href="harga.php">Harga Sewa</a>.</p>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jam Mulai</th>
                        <th>Jam Selesai</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar as $i => $w): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><b><?= e(jam($w['jam_mulai'])) ?></b></td>
                            <td><?= e(jam($w['jam_selesai'])) ?></td>
                            <td><?= e($w['keterangan'] ?: '-') ?></td>
                            <td>
                                <div class="aksi">
                                    <a class="btn btn-warning btn-icon" title="Ubah" href="waktu_form.php?id=<?= (int)$w['id_waktu'] ?>"><i class="bi bi-pencil"></i></a>
                                    <form method="post" action="waktu_proses.php" data-confirm="Hapus jam ini? Harga untuk jam ini juga ikut terhapus.">
                                        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id_waktu" value="<?= (int)$w['id_waktu'] ?>">
                                        <button class="btn btn-danger btn-icon" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$daftar): ?><tr>
                            <td class="empty" colspan="5">Belum ada jam operasional.</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../inc/footer.php'; ?>