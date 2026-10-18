<div class="card">
    <div class="header">
        <h2><?= e($judul_form) ?></h2>
        <div class="header-actions"><a class="btn btn-light btn-sm" href="?page=waktu"><?= ikon('back') ?> Kembali</a></div>
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
                    <label for="jam_mulai">Jam Mulai</label>
                    <input class="form-control" type="time" id="jam_mulai" name="jam_mulai" value="<?= e($d['jam_mulai']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="jam_selesai">Jam Selesai</label>
                    <input class="form-control" type="time" id="jam_selesai" name="jam_selesai" value="<?= e($d['jam_selesai']) ?>" required>
                </div>
                <div class="form-group full">
                    <label for="keterangan">Keterangan</label>
                    <input class="form-control" id="keterangan" name="keterangan" value="<?= e($d['keterangan']) ?>" placeholder="Contoh: Siang / Sore / Malam">
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit"><?= ikon('check') ?> Simpan</button>
                <a class="btn btn-light" href="?page=waktu">Batal</a>
            </div>
        </form>
    </div>
</div>