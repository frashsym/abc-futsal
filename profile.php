<?php
// ============================================================
//  profile.php - profil sendiri: ubah nama, no. HP, dan password
//  (berlaku untuk admin maupun kasir)
// ============================================================
require_once __DIR__ . '/auth.php';

$judul_halaman = 'Profile';
$menu_aktif    = 'profile';

$stmt = $pdo->prepare("SELECT * FROM tb_pengguna WHERE id_pengguna = ?");
$stmt->execute([$user['id']]);
$p = $stmt->fetch();

if (!empty($_SESSION['old'])) {
    $p = array_merge($p, $_SESSION['old']);
    unset($_SESSION['old']);
}

include __DIR__ . '/inc/header.php';
?>
<div class="row g-3">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h2>Akun Saya</h2>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="avatar" style="width:84px;height:84px;font-size:30px;border-color:var(--primary)"><?= e(inisial($p['nama_lengkap'])) ?></div>
                    <div>
                        <div class="h5 fw-bold mb-1"><?= e($p['nama_lengkap']) ?></div>
                        <?= badge($p['level']) ?> <?= badge($p['status']) ?>
                    </div>
                </div>
                <div class="info-list">
                    <div><small>Username</small><b><?= e($p['username']) ?></b></div>
                    <div><small>No. HP</small><b><?= e($p['no_hp'] ?: '-') ?></b></div>
                    <div><small>Terdaftar</small><b><?= e(tgl_indo(substr($p['created_at'], 0, 10))) ?></b></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <form class="card" method="post" action="profile_proses.php" autocomplete="off">
            <div class="card-header">
                <h2>Ubah Data</h2>
            </div>
            <div class="card-body">
                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="nama_lengkap">Nama Lengkap</label>
                        <input class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= e($p['nama_lengkap']) ?>" maxlength="100" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="no_hp">No. HP</label>
                        <input class="form-control" id="no_hp" name="no_hp" value="<?= e($p['no_hp']) ?>" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="col-12">
                        <hr class="my-1">
                        <div class="text-muted">Ganti password (kosongkan jika tidak ingin mengganti)</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="password_lama">Password Lama</label>
                        <input class="form-control" type="password" id="password_lama" name="password_lama" autocomplete="current-password">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="password_baru">Password Baru</label>
                        <input class="form-control" type="password" id="password_baru" name="password_baru" autocomplete="new-password">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="password_ulang">Ulangi Password Baru</label>
                        <input class="form-control" type="password" id="password_ulang" name="password_ulang" autocomplete="new-password">
                    </div>
                </div>
                <div class="mt-4"><button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Simpan Perubahan</button></div>
            </div>
        </form>
    </div>
</div>
<?php include __DIR__ . '/inc/footer.php'; ?>