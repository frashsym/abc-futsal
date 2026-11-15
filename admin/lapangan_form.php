<?php
// admin/lapangan_form.php - form tambah / ubah lapangan
//   tanpa ?id  -> tambah baru
//   dengan ?id -> ubah data
$level_wajib = 'admin';
require_once '../auth.php';

$id = (int)($_GET['id'] ?? 0);
$d  = ['nama_lapangan' => '', 'jenis_lantai' => 'Vinyl', 'status' => 'Tersedia'];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM tb_lapangan WHERE id_lapangan = ?");
    $stmt->execute([$id]);
    $d = $stmt->fetch();
    if (!$d) {
        $_SESSION['pesan'] = ['danger', 'Data lapangan tidak ditemukan.'];
        header('Location: lapangan.php');
        exit;
    }
}
// Kalau validasi gagal, isi form dikembalikan seperti yang tadi diketik
if (!empty($_SESSION['old'])) {
    $d = array_merge($d, $_SESSION['old']);
    unset($_SESSION['old']);
}

$judul_halaman = $id > 0 ? 'Ubah Lapangan' : 'Tambah Lapangan';
$menu_aktif    = 'lapangan';
include '../inc/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2><?= e($judul_halaman) ?></h2>
        <a class="btn btn-light btn-sm" href="lapangan.php"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
        <form method="post" action="lapangan_proses.php" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
            <input type="hidden" name="aksi" value="simpan">
            <input type="hidden" name="id_lapangan" value="<?= $id ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="nama_lapangan">Nama Lapangan</label>
                    <input class="form-control" id="nama_lapangan" name="nama_lapangan" value="<?= e($d['nama_lapangan']) ?>" maxlength="50" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="jenis_lantai">Jenis Lantai</label>
                    <input class="form-control" id="jenis_lantai" name="jenis_lantai" value="<?= e($d['jenis_lantai']) ?>" maxlength="30" placeholder="Vinyl / Sintetis / Parquet" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="status">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="Tersedia" <?= $d['status'] === 'Tersedia'  ? 'selected' : '' ?>>Tersedia</option>
                        <option value="Perbaikan" <?= $d['status'] === 'Perbaikan' ? 'selected' : '' ?>>Perbaikan (tidak bisa dibooking)</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Simpan</button>
                <a class="btn btn-light" href="lapangan.php">Batal</a>
            </div>
        </form>
    </div>
</div>
<?php include '../inc/footer.php'; ?>