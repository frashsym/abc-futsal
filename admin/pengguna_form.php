<?php
// admin/pengguna_form.php - form tambah / ubah user
$level_wajib = 'admin';
require_once '../auth.php';

$id = (int)($_GET['id'] ?? 0);
$d  = ['nama_lengkap' => '', 'username' => '', 'no_hp' => '', 'level' => 'kasir', 'status' => 'Aktif'];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM tb_pengguna WHERE id_pengguna = ?");
    $stmt->execute([$id]);
    $d = $stmt->fetch();
    if (!$d) {
        $_SESSION['pesan'] = ['danger', 'User tidak ditemukan.'];
        header('Location: pengguna.php');
        exit;
    }
}
if (!empty($_SESSION['old'])) {
    $d = array_merge($d, $_SESSION['old']);
    unset($_SESSION['old']);
}
$ini_diri_sendiri = ($id === $user['id']);

$judul_halaman = $id > 0 ? 'Ubah User' : 'Tambah User';
$menu_aktif    = 'pengguna';
include '../inc/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2><?= e($judul_halaman) ?></h2>
        <a class="btn btn-light btn-sm" href="pengguna.php"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>
    <div class="card-body">
        <form method="post" action="pengguna_proses.php" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
            <input type="hidden" name="aksi" value="simpan">
            <input type="hidden" name="id_pengguna" value="<?= $id ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="nama_lengkap">Nama Lengkap</label>
                    <input class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= e($d['nama_lengkap']) ?>" maxlength="100" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="username">Username</label>
                    <input class="form-control" id="username" name="username" value="<?= e($d['username']) ?>" maxlength="30" required>
                    <div class="form-text">Huruf, angka, titik, atau underscore (4–30 karakter).</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="no_hp">No. HP</label>
                    <input class="form-control" id="no_hp" name="no_hp" value="<?= e($d['no_hp']) ?>" placeholder="08xxxxxxxxxx">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password">Password <?= $id > 0 ? '(kosongkan jika tidak diganti)' : '' ?></label>
                    <input class="form-control" type="password" id="password" name="password" <?= $id > 0 ? '' : 'required' ?> autocomplete="new-password">
                    <div class="form-text">Minimal 6 karakter.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="level">Level</label>
                    <select class="form-select" id="level" name="level" <?= $ini_diri_sendiri ? 'disabled' : '' ?>>
                        <option value="kasir" <?= $d['level'] === 'kasir' ? 'selected' : '' ?>>Kasir</option>
                        <option value="admin" <?= $d['level'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                    <?php if ($ini_diri_sendiri): ?><div class="form-text">Level akun sendiri tidak bisa diubah.</div><?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="status">Status Akun</label>
                    <select class="form-select" id="status" name="status" <?= $ini_diri_sendiri ? 'disabled' : '' ?>>
                        <option value="Aktif" <?= $d['status'] === 'Aktif'    ? 'selected' : '' ?>>Aktif</option>
                        <option value="Nonaktif" <?= $d['status'] === 'Nonaktif' ? 'selected' : '' ?>>Nonaktif (tidak bisa login)</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Simpan</button>
                <a class="btn btn-light" href="pengguna.php">Batal</a>
            </div>
        </form>
    </div>
</div>
<?php include '../inc/footer.php'; ?>