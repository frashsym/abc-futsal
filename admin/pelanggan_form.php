<?php
// admin/pelanggan_form.php - form tambah / ubah pelanggan
$level_wajib = 'admin';
require_once '../auth.php';

$id = (int)($_GET['id'] ?? 0);
$d  = [
    'nama' => '',
    'no_hp' => '',
    'alamat' => '',
    'jk' => '',
    'email' => '',
    'tanggal_lahir' => '',
    'tanggal_bergabung' => date('Y-m-d\TH:i'),
];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT nama, no_hp, alamat, jk, email, tanggal_lahir, tanggal_bergabung
                           FROM tb_pelanggan WHERE id_pelanggan = ?");
    $stmt->execute([$id]);
    $pelanggan = $stmt->fetch();
    if (!$pelanggan) {
        $_SESSION['pesan'] = ['danger', 'Data pelanggan tidak ditemukan.'];
        header('Location: pelanggan.php');
        exit;
    }
    $d = $pelanggan;
    if (!empty($d['tanggal_bergabung'])) {
        $timestamp = strtotime($d['tanggal_bergabung']);
        $d['tanggal_bergabung'] = $timestamp === false ? '' : date('Y-m-d\TH:i', $timestamp);
    }
}

if (!empty($_SESSION['old'])) {
    $d = array_merge($d, $_SESSION['old']);
    unset($_SESSION['old']);
}

$judul_halaman = $id > 0 ? 'Ubah Pelanggan' : 'Tambah Pelanggan';
$menu_aktif    = 'pelanggan';
include '../inc/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2><?= e($judul_halaman) ?></h2>
        <a class="btn btn-light btn-sm" href="pelanggan.php"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
        <form method="post" action="pelanggan_proses.php" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
            <input type="hidden" name="aksi" value="simpan">
            <input type="hidden" name="id_pelanggan" value="<?= $id ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="nama">Nama</label>
                    <input class="form-control" id="nama" name="nama" value="<?= e($d['nama']) ?>" maxlength="100" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="no_hp">Nomor HP</label>
                    <input class="form-control" id="no_hp" name="no_hp" type="tel" value="<?= e($d['no_hp']) ?>" maxlength="20" placeholder="08xxxxxxxxxx" required>
                </div>
                <div class="col-12">
                    <label class="form-label" for="alamat">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?= e($d['alamat']) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="jk">Jenis Kelamin</label>
                    <select class="form-select" id="jk" name="jk">
                        <option value="">Pilih jenis kelamin</option>
                        <option value="Laki-laki" <?= $d['jk'] === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="Perempuan" <?= $d['jk'] === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" id="email" name="email" type="email" value="<?= e($d['email']) ?>" maxlength="100">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="tanggal_lahir">Tanggal Lahir</label>
                    <input class="form-control" id="tanggal_lahir" name="tanggal_lahir" type="date" value="<?= e($d['tanggal_lahir']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="tanggal_bergabung">Tanggal dan Jam Bergabung</label>
                    <input class="form-control" id="tanggal_bergabung" name="tanggal_bergabung" type="datetime-local" value="<?= e($d['tanggal_bergabung']) ?>" required>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Simpan</button>
                <a class="btn btn-light" href="pelanggan.php">Batal</a>
            </div>
        </form>
    </div>
</div>
<?php include '../inc/footer.php'; ?>
