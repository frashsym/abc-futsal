<div class="card">
    <div class="header">
        <h2><?= e($judul_form) ?></h2>
        <div class="header-actions"><a class="btn btn-light btn-sm" href="?page=lapangan"><?= ikon('back') ?> Kembali</a></div>
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
                    <label for="nama_lapangan">Nama Lapangan</label>
                    <input class="form-control" id="nama_lapangan" name="nama_lapangan" value="<?= e($d['nama_lapangan']) ?>" placeholder="Contoh: Lapangan A" required>
                </div>
                <div class="form-group">
                    <label for="jenis_lantai">Jenis Lantai</label>
                    <select class="form-control" id="jenis_lantai" name="jenis_lantai">
                        <?php foreach (['Vinyl', 'Sintetis', 'Parquet', 'Semen'] as $j): ?>
                            <option <?= $d['jenis_lantai'] === $j ? 'selected' : '' ?>><?= $j ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="harga_per_jam">Harga per Jam (Rp)</label>
                    <input class="form-control" type="number" min="0" step="1000" id="harga_per_jam" name="harga_per_jam" value="<?= e($d['harga_per_jam']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option <?= $d['status'] === 'Tersedia' ? 'selected' : '' ?>>Tersedia</option>
                        <option <?= $d['status'] === 'Perbaikan' ? 'selected' : '' ?>>Perbaikan</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit"><?= ikon('check') ?> Simpan</button>
                <a class="btn btn-light" href="?page=lapangan">Batal</a>
            </div>
        </form>
    </div>
</div>