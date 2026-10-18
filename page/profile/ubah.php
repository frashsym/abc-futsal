<?php
$id = (int)$user['id'];
$me = db_one("SELECT * FROM pengguna WHERE id_pengguna = ?", [$id]);
$d = ['nama_lengkap' => $me['nama_lengkap'], 'no_hp' => (string)$me['no_hp']];
$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    foreach ($d as $k => $v) $d[$k] = trim($_POST[$k] ?? '');
    $lama  = (string)($_POST['password_lama'] ?? '');
    $baru  = (string)($_POST['password_baru'] ?? '');
    $baru2 = (string)($_POST['password_baru2'] ?? '');

    if (mb_strlen($d['nama_lengkap']) < 3 || mb_strlen($d['nama_lengkap']) > 100) $err[] = 'Nama lengkap 3–100 karakter.';
    if ($d['no_hp'] !== '' && !preg_match('/^[0-9+\- ]{8,20}$/', $d['no_hp'])) $err[] = 'Nomor HP tidak valid.';

    $ganti_pass = ($lama !== '' || $baru !== '' || $baru2 !== '');
    if ($ganti_pass) {
        if (!password_verify($lama, $me['password'])) $err[] = 'Password lama salah.';
        if (strlen($baru) < 6) $err[] = 'Password baru minimal 6 karakter.';
        if ($baru !== $baru2) $err[] = 'Konfirmasi password baru tidak sama.';
    }

    if (!$err) {
        if ($ganti_pass) {
            $ok = db_exec(
                "UPDATE pengguna SET nama_lengkap=?, no_hp=?, password=? WHERE id_pengguna=?",
                [$d['nama_lengkap'], $d['no_hp'], password_hash($baru, PASSWORD_DEFAULT), $id]
            );
        } else {
            $ok = db_exec("UPDATE pengguna SET nama_lengkap=?, no_hp=? WHERE id_pengguna=?", [$d['nama_lengkap'], $d['no_hp'], $id]);
        }
        if ($ok) {
            $_SESSION['user']['nama'] = $d['nama_lengkap'];
            flash('success', 'Profile berhasil diperbarui.');
            redirect('?page=profile');
        }
        $err[] = 'Gagal menyimpan perubahan.';
    }
}
?>
<div class="card">
    <div class="header">
        <h2>EDIT PROFILE</h2>
        <div class="header-actions"><a class="btn btn-light btn-sm" href="?page=profile"><?= ikon('back') ?> Kembali</a></div>
    </div>
    <div class="body">
        <?php if ($err): ?>
            <div class="alert alert-danger">
                <ul><?php foreach ($err as $m) echo '<li>' . e($m) . '</li>'; ?></ul>
            </div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-group">
                    <label for="nama_lengkap">Nama Lengkap</label>
                    <input class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= e($d['nama_lengkap']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="no_hp">No. HP</label>
                    <input class="form-control" id="no_hp" name="no_hp" value="<?= e($d['no_hp']) ?>">
                </div>
                <div class="form-group full"><span class="form-hint"><b>Ganti password</b> &mdash; isi tiga kolom di bawah hanya kalau mau ganti.</span></div>
                <div class="form-group">
                    <label for="password_lama">Password Lama</label>
                    <input class="form-control" type="password" id="password_lama" name="password_lama" autocomplete="current-password">
                </div>
                <div class="form-group"></div>
                <div class="form-group">
                    <label for="password_baru">Password Baru</label>
                    <input class="form-control" type="password" id="password_baru" name="password_baru" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="password_baru2">Ulangi Password Baru</label>
                    <input class="form-control" type="password" id="password_baru2" name="password_baru2" autocomplete="new-password">
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit"><?= ikon('check') ?> Simpan</button>
                <a class="btn btn-light" href="?page=profile">Batal</a>
            </div>
        </form>
    </div>
</div>