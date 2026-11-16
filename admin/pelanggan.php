<?php
// admin/pelanggan.php - daftar pelanggan
$level_wajib = 'admin';
require_once '../auth.php';

$judul_halaman = 'Data Pelanggan';
$menu_aktif    = 'pelanggan';

$daftar = $pdo->query("SELECT id_pelanggan, nama, no_hp, alamat, jk, email, tanggal_lahir, tanggal_bergabung
                       FROM tb_pelanggan ORDER BY nama")->fetchAll();

include '../inc/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2>Data Pelanggan</h2>
        <a class="btn btn-primary btn-sm" href="pelanggan_form.php"><i class="bi bi-plus-lg"></i> Tambah Pelanggan</a>
    </div>
    <div class="card-body">
        <input type="search" class="form-control mb-3" style="max-width:280px" placeholder="Cari pelanggan..." data-cari-tabel="#tabel-pelanggan">
        <div class="table-responsive">
            <table class="table table-hover" id="tabel-pelanggan">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>No. HP</th>
                        <th>Alamat</th>
                        <th>Jenis Kelamin</th>
                        <th>Email</th>
                        <th>Tanggal Lahir</th>
                        <th>Tanggal dan Jam Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar as $i => $p): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><b><?= e($p['nama']) ?></b></td>
                            <td><?= e($p['no_hp']) ?></td>
                            <td><?= e($p['alamat']) ?></td>
                            <td><?= e($p['jk'] ?: '-') ?></td>
                            <td><?= e($p['email'] ?: '-') ?></td>
                            <td><?= e($p['tanggal_lahir'] ? tgl_indo($p['tanggal_lahir']) : '-') ?></td>
                            <td><?= e(tgl_indo($p['tanggal_bergabung']) . ' ' . date('H:i', strtotime($p['tanggal_bergabung']))) ?></td>
                            <td>
                                <div class="aksi">
                                    <a class="btn btn-warning btn-icon" title="Ubah" href="pelanggan_form.php?id=<?= (int)$p['id_pelanggan'] ?>"><i class="bi bi-pencil"></i></a>
                                    <a class="btn btn-danger btn-icon" title="Hapus" href="pelanggan_hapus.php?id=<?= (int)$p['id_pelanggan'] ?>"><i class="bi bi-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$daftar): ?>
                        <tr><td class="empty" colspan="9">Belum ada data pelanggan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../inc/footer.php'; ?>
