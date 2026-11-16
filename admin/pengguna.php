<?php
// admin/pengguna.php - daftar user (admin & kasir)
$level_wajib = 'admin';
require_once '../auth.php';

$judul_halaman = 'Data User';
$menu_aktif    = 'pengguna';

$daftar = $pdo->query("SELECT * FROM tb_pengguna ORDER BY level, nama_lengkap")->fetchAll();

include '../inc/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2>Data User / Kasir</h2>
        <a class="btn btn-primary btn-sm" href="pengguna_form.php"><i class="bi bi-plus-lg"></i> Tambah User</a>
    </div>
    <div class="card-body">
        <input type="search" class="form-control mb-3" style="max-width:280px" placeholder="Cari user..." data-cari-tabel="#tabel-pengguna">
        <div class="table-responsive">
            <table class="table table-hover" id="tabel-pengguna">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>No. HP</th>
                        <th>Level</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar as $i => $p): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><b><?= e($p['nama_lengkap']) ?></b><?= $p['id_pengguna'] == $user['id'] ? ' <span class="text-muted">(kamu)</span>' : '' ?></td>
                            <td><?= e($p['username']) ?></td>
                            <td><?= e($p['no_hp'] ?: '-') ?></td>
                            <td><?= badge($p['level']) ?></td>
                            <td><?= badge($p['status']) ?></td>
                            <td>
                                <div class="aksi">
                                    <a class="btn btn-warning btn-icon" title="Ubah" href="pengguna_form.php?id=<?= (int)$p['id_pengguna'] ?>"><i class="bi bi-pencil"></i></a>
                                    <?php if ($p['id_pengguna'] != $user['id']): ?>
                                        <form method="post" action="pengguna_proses.php" data-confirm="Hapus user ini?">
                                            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id_pengguna" value="<?= (int)$p['id_pengguna'] ?>">
                                            <button class="btn btn-danger btn-icon" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../inc/footer.php'; ?>