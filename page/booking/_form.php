<div class="card">
    <div class="header">
        <h2><?= e($judul_form) ?></h2>
        <div class="header-actions"><a class="btn btn-light btn-sm" href="?page=booking"><?= ikon('back') ?> Kembali</a></div>
    </div>
    <div class="body">
        <?php if ($err): ?>
            <div class="alert alert-danger">
                <ul><?php foreach ($err as $m) echo '<li>' . e($m) . '</li>'; ?></ul>
            </div>
        <?php endif; ?>
        <form method="post" id="form-booking" data-kecuali="<?= (int)$kecuali ?>" autocomplete="off">
            <?= csrf_field() ?>
            <div class="form-grid">
                <div class="form-group">
                    <label for="tanggal_main">Tanggal Main</label>
                    <input class="form-control" type="date" id="tanggal_main" name="tanggal_main" min="<?= e($tgl_min) ?>" value="<?= e($d['tanggal_main']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="id_lapangan">Lapangan</label>
                    <select class="form-control" id="id_lapangan" name="id_lapangan" required>
                        <option value="">— Pilih lapangan —</option>
                        <?php foreach ($lapangan_list as $l): ?>
                            <option value="<?= (int)$l['id_lapangan'] ?>" data-harga="<?= (int)$l['harga_per_jam'] ?>" <?= (string)$d['id_lapangan'] === (string)$l['id_lapangan'] ? 'selected' : '' ?>>
                                <?= e($l['nama_lapangan']) ?> · <?= e($l['jenis_lantai']) ?> · <?= rupiah($l['harga_per_jam']) ?>/jam
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="id_waktu">Jam Main</label>
                    <select class="form-control" id="id_waktu" name="id_waktu" required>
                        <option value="">— Pilih jam —</option>
                        <?php foreach ($waktu_list as $w): ?>
                            <option value="<?= (int)$w['id_waktu'] ?>" data-durasi="<?= e(durasi_jam($w['jam_mulai'], $w['jam_selesai'])) ?>" <?= (string)$d['id_waktu'] === (string)$w['id_waktu'] ? 'selected' : '' ?>><?= e(jam($w['jam_mulai'])) ?> – <?= e(jam($w['jam_selesai'])) ?><?= $w['keterangan'] ? ' (' . e($w['keterangan']) . ')' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="form-hint">Jam yang sudah penuh otomatis nggak bisa dipilih.</span>
                </div>
                <div class="form-group">
                    <label for="nama_penyewa">Nama Penyewa</label>
                    <input class="form-control" id="nama_penyewa" name="nama_penyewa" value="<?= e($d['nama_penyewa']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="no_hp">No. HP</label>
                    <input class="form-control" id="no_hp" name="no_hp" value="<?= e($d['no_hp']) ?>" placeholder="08xxxxxxxxxx">
                </div>
                <div class="form-group">
                    <label for="catatan">Catatan (opsional)</label>
                    <input class="form-control" id="catatan" name="catatan" value="<?= e($d['catatan']) ?>" maxlength="255" placeholder="Contoh: sparing tim kantor">
                </div>
                <div class="total-box">
                    <span>Estimasi total bayar</span>
                    <strong id="total-estimasi">Rp 0</strong>
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit"><?= ikon('check') ?> Simpan Booking</button>
                <a class="btn btn-light" href="?page=booking">Batal</a>
            </div>
        </form>
    </div>
</div>