<div class="card">
    <div class="header">
        <h2><?= e($judul_form) ?></h2>
        <div class="header-actions"><a class="btn btn-light btn-sm" href="?page=pengguna"><?= ikon('back') ?> Kembali</a></div>
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
                    <label for="username">Username</label>
                    <input class="form-control" id="username" name="username" value="<?= e($d['username']) ?>" required>
                    <span class="form-hint">Huruf, angka, underscore (3–30 karakter).</span>
                </div>
                <div class="form-group">
                    <label for="no_hp">No. HP</label>
                    <input class="form-control" id="no_hp" name="no_hp" value="<?= e($d['no_hp']) ?>" placeholder="08xxxxxxxxxx">
                </div>
                <div class="form-group">
                    <label for="level">Level</label>
                    <select class="form-control" id="level" name="level">
                        <option value="pelanggan" <?= $d['level'] === 'pelanggan' ? 'selected' : '' ?>>Pelanggan</option>
                        <option value="admin" <?= $d['level'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="form-group full">
                    <label for="password">Password <?= $edit ? '(kosongkan kalau tidak diganti)' : '' ?></label>
                    <input class="form-control" type="password" id="password" name="password" <?= $edit ? '' : 'required' ?> autocomplete="new-password">
                    <span class="form-hint">Minimal 6 karakter.</span>
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit"><?= ikon('check') ?> Simpan</button>
                <a class="btn btn-light" href="?page=pengguna">Batal</a>
            </div>
        </form>
    </div>
</div>