<!-- Modal: catat pembayaran (diisi lewat JS dari tombol [data-bayar]) -->
<div class="modal" id="modalBayar" role="dialog" aria-modal="true" aria-labelledby="mbTitle">
    <form class="modal-box" method="post" id="formBayar" autocomplete="off">
        <?= csrf_field() ?>
        <div class="modal-head">
            <h3 id="mbTitle">Catat Pembayaran</h3><button type="button" class="nav-btn" data-modal-close aria-label="Tutup"><?= ikon('x') ?></button>
        </div>
        <div class="modal-body">
            <div class="bayar-sum">
                <div><small>Kode</small><b id="mb-kode">-</b></div>
                <div><small>Penyewa</small><b id="mb-nama">-</b></div>
                <div><small>Total</small><b id="mb-total">-</b></div>
                <div><small>Sudah dibayar</small><b id="mb-dibayar">-</b></div>
                <div class="wide hl"><small>Sisa tagihan</small><b id="mb-sisa">-</b></div>
            </div>
            <div class="form-group">
                <label for="mb-jumlah">Jumlah dibayar (Rp)</label>
                <div class="input-act">
                    <input class="form-control" id="mb-jumlah" name="jumlah" inputmode="numeric" required>
                    <button type="button" class="btn btn-light btn-sm" id="mb-lunasi">Lunasi</button>
                </div>
                <span class="form-hint" id="mb-hint"></span>
            </div>
            <div class="form-group">
                <label for="mb-metode">Metode</label>
                <select class="form-control" id="mb-metode" name="metode">
                    <?php foreach (METODE_BAYAR as $m): ?><option><?= e($m) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit"><?= ikon('check') ?> Simpan Pembayaran</button>
                <button class="btn btn-light" type="button" data-modal-close>Batal</button>
            </div>
        </div>
    </form>
</div>

<!-- Modal: batalkan booking yang sudah ada DP-nya -->
<div class="modal" id="modalCancel" role="dialog" aria-modal="true" aria-labelledby="mcTitle">
    <form class="modal-box" method="post" id="formCancel">
        <?= csrf_field() ?>
        <div class="modal-body cf-body">
            <div class="cf-icon danger"><?= ikon('x') ?></div>
            <h3 id="mcTitle">Batalkan booking?</h3>
            <p class="cf-msg">Booking <b id="mc-kode"></b> sudah punya DP <b id="mc-dp"></b>. Gimana nasib DP-nya?</p>
            <label class="pay-opt block"><input type="radio" name="dp" value="hangus" checked><span><b>DP hangus</b><small>Uang tetap masuk kas, tidak dikembalikan</small></span></label>
            <label class="pay-opt block"><input type="radio" name="dp" value="kembali"><span><b>DP dikembalikan</b><small>Dicatat sebagai refund tunai ke customer</small></span></label>
            <div class="cf-actions">
                <button class="btn btn-light" type="button" data-modal-close>Jangan batalkan</button>
                <button class="btn btn-danger" type="submit">Ya, batalkan</button>
            </div>
        </div>
    </form>
</div>
