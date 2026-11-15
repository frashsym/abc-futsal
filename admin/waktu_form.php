<?php
// admin/waktu_form.php - form tambah / ubah jam operasional
$level_wajib = 'admin';
require_once '../auth.php';

$id = (int)($_GET['id'] ?? 0);
$d  = ['jam_mulai' => '', 'jam_selesai' => '', 'keterangan' => ''];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM tb_waktu WHERE id_waktu = ?");
    $stmt->execute([$id]);
    $d = $stmt->fetch();
    if (!$d) {
        $_SESSION['pesan'] = ['danger', 'Data jam tidak ditemukan.'];
        header('Location: waktu.php');
        exit;
    }
    $d['jam_mulai']   = jam($d['jam_mulai']);
    $d['jam_selesai'] = jam($d['jam_selesai']);
}
if (!empty($_SESSION['old'])) {
    $d = array_merge($d, $_SESSION['old']);
    unset($_SESSION['old']);
}

$judul_halaman = $id > 0 ? 'Ubah Jam Operasional' : 'Tambah Jam Operasional';
$menu_aktif    = 'waktu';
include '../inc/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2><?= e($judul_halaman) ?></h2>
        <a class="btn btn-light btn-sm" href="waktu.php"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
        <form method="post" action="waktu_proses.php" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
            <input type="hidden" name="aksi" value="simpan">
            <input type="hidden" name="id_waktu" value="<?= $id ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="jam_mulai">Jam Mulai</label>
                    <input class="form-control" type="time" id="jam_mulai" name="jam_mulai" value="<?= e($d['jam_mulai']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="jam_selesai">Jam Selesai</label>
                    <input class="form-control" type="time" id="jam_selesai" name="jam_selesai" value="<?= e($d['jam_selesai']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="keterangan">Keterangan (opsional)</label>
                    <input class="form-control" id="keterangan" name="keterangan" value="<?= e($d['keterangan']) ?>" maxlength="50" placeholder="Siang / Sore / Malam">
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Simpan</button>
                <a class="btn btn-light" href="waktu.php">Batal</a>
            </div>
        </form>
    </div>
</div>
<?php include '../inc/footer.php'; ?>