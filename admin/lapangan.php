<?php
// admin/lapangan.php - daftar lapangan
$level_wajib = 'admin';
require_once '../auth.php';

$judul_halaman = 'Data Lapangan';
$menu_aktif    = 'lapangan';

$daftar = $pdo->query("SELECT * FROM tb_lapangan ORDER BY nama_lapangan")->fetchAll();

include '../inc/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2>Data Lapangan</h2>
        <a class="btn btn-primary btn-sm" href="lapangan_form.php"><i class="bi bi-plus-lg"></i> Tambah Lapangan</a>
    </div>
    <div class="card-body">
        <input type="search" class="form-control mb-3" style="max-width:280px" placeholder="Cari lapangan..." data-cari-tabel="#tabel-lapangan">
        <div class="table-responsive">
            <table class="table table-hover" id="tabel-lapangan">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Lapangan</th>
                        <th>Jenis Lantai</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar as $i => $l): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><b><?= e($l['nama_lapangan']) ?></b></td>
                            <td><?= e($l['jenis_lantai']) ?></td>
                            <td><?= badge($l['status']) ?></td>
                            <td>
                                <div class="aksi">
                                    <a class="btn btn-warning btn-icon" title="Ubah" href="lapangan_form.php?id=<?= (int)$l['id_lapangan'] ?>"><i class="bi bi-pencil"></i></a>
                                    <form method="post" action="lapangan_proses.php" data-confirm="Hapus lapangan ini? Harga sewanya juga ikut terhapus.">
                                        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id_lapangan" value="<?= (int)$l['id_lapangan'] ?>">
                                        <button class="btn btn-danger btn-icon" title="Hapus"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$daftar): ?><tr>
                            <td class="empty" colspan="5">Belum ada lapangan.</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../inc/footer.php'; ?>