<?php
// admin/pelanggan_hapus.php - konfirmasi penghapusan pelanggan
$level_wajib = 'admin';
require_once '../auth.php';

$id = (int)($_GET['id'] ?? 0);
if ($id < 1) {
    $_SESSION['pesan'] = ['danger', 'ID pelanggan tidak valid.'];
    header('Location: pelanggan.php');
    exit;
}

$stmt = $pdo->prepare("SELECT id_pelanggan, nama, no_hp, alamat, email
                       FROM tb_pelanggan WHERE id_pelanggan = ?");
$stmt->execute([$id]);
$pelanggan = $stmt->fetch();
if (!$pelanggan) {
    $_SESSION['pesan'] = ['danger', 'Data pelanggan tidak ditemukan.'];
    header('Location: pelanggan.php');
    exit;
}

$judul_halaman = 'Hapus Pelanggan';
$menu_aktif    = 'pelanggan';
include '../inc/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2>Konfirmasi Hapus Pelanggan</h2>
        <a class="btn btn-light btn-sm" href="pelanggan.php"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
        <div class="alert alert-warning">Pastikan data di bawah benar sebelum menghapus. Data yang sudah dihapus tidak dapat dikembalikan.</div>
        <dl class="row">
            <dt class="col-sm-3">Nama</dt>
            <dd class="col-sm-9"><?= e($pelanggan['nama']) ?></dd>
            <dt class="col-sm-3">Nomor HP</dt>
            <dd class="col-sm-9"><?= e($pelanggan['no_hp']) ?></dd>
            <dt class="col-sm-3">Alamat</dt>
            <dd class="col-sm-9"><?= e($pelanggan['alamat']) ?></dd>
            <dt class="col-sm-3">Email</dt>
            <dd class="col-sm-9"><?= e($pelanggan['email'] ?: '-') ?></dd>
        </dl>
        <form method="post" action="pelanggan_proses.php">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
            <input type="hidden" name="aksi" value="hapus">
            <input type="hidden" name="id_pelanggan" value="<?= (int)$pelanggan['id_pelanggan'] ?>">
            <div class="d-flex gap-2">
                <button class="btn btn-danger" type="submit"><i class="bi bi-trash"></i> Hapus Permanen</button>
                <a class="btn btn-light" href="pelanggan.php">Batal</a>
            </div>
        </form>
    </div>
</div>
<?php include '../inc/footer.php'; ?>
