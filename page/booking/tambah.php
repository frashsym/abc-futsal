<?php
$lapangan_list = db_all("SELECT * FROM lapangan WHERE status = 'Tersedia' ORDER BY nama_lapangan");
$waktu_list = db_all("SELECT * FROM waktu ORDER BY jam_mulai");
$me = db_one("SELECT nama_lengkap, no_hp FROM pengguna WHERE id_pengguna = ?", [(int)$user['id']]);
$staff = is_staff();

$d = [
    'tanggal_main' => date('Y-m-d'),
    'id_lapangan'  => (string)(int)($_GET['lapangan'] ?? 0) ?: '',
    'id_waktu'     => '',
    'nama_penyewa' => $staff ? '' : (string)$me['nama_lengkap'],
    'no_hp'        => $staff ? '' : (string)$me['no_hp'],
    'catatan'      => '',
];
// Pembayaran: online wajib DP minimal, kasir boleh DP / lunas / bayar nanti
$tipe_bayar = $staff ? 'nanti' : 'dp';
$jumlah_dp = '';
$metode = $staff ? 'Tunai' : 'QRIS';
$err = [];
$tgl_min = date('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    foreach ($d as $k => $v) $d[$k] = trim($_POST[$k] ?? '');
    $tipe_bayar = (string)($_POST['tipe_bayar'] ?? '');
    $jumlah_dp = trim((string)($_POST['jumlah_dp'] ?? ''));
    $metode = (string)($_POST['metode'] ?? '');

    $tipe_ok = $staff ? ['nanti', 'dp', 'lunas'] : ['dp', 'lunas'];
    if (!in_array($tipe_bayar, $tipe_ok, true)) $err[] = 'Pilih cara bayar dulu.';
    $metode_ok = $staff ? METODE_BAYAR : ['Transfer', 'QRIS'];
    if ($tipe_bayar !== 'nanti' && !in_array($metode, $metode_ok, true)) $err[] = 'Pilih metode pembayaran.';

    // Transaksi + kunci baris lapangan supaya tidak ada double booking saat dua orang submit bersamaan
    mysqli_begin_transaction($koneksi);
    db_one("SELECT id_lapangan FROM lapangan WHERE id_lapangan = ? FOR UPDATE", [(int)$d['id_lapangan']]);
    list($e2, $lap, $wkt) = booking_validasi($d);
    $err = array_merge($err, $e2);

    $bayar_sekarang = 0;
    if (!$err) {
        $total = hitung_total($lap, $wkt);
        if ($tipe_bayar === 'lunas') {
            $bayar_sekarang = $total;
        } elseif ($tipe_bayar === 'dp') {
            $bayar_sekarang = angka($jumlah_dp);
            $min = $staff ? 1 : dp_minimum($total);
            if ($bayar_sekarang < $min) $err[] = 'DP minimal ' . rupiah($min) . ($staff ? '.' : ' (' . DP_MIN_PERSEN . '% dari total).');
            if ($bayar_sekarang >= $total) $err[] = 'Jumlah DP harus lebih kecil dari total. Pilih "Bayar lunas" kalau mau bayar penuh.';
        }
    }

    if (!$err) {
        $kanal = $staff ? 'Kasir' : 'Online';
        $kode = 'BK-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
        $ok = db_exec(
            "INSERT INTO booking (kode_booking, id_pengguna, id_lapangan, id_waktu, tanggal_main, nama_penyewa, no_hp, total_bayar, dibayar, sumber, status, catatan)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, 'Booking', ?)",
            [
                $kode,
                (int)$user['id'],
                (int)$lap['id_lapangan'],
                (int)$wkt['id_waktu'],
                $d['tanggal_main'],
                $d['nama_penyewa'],
                $d['no_hp'],
                $total,
                $kanal,
                $d['catatan']
            ]
        );
        if ($ok && $bayar_sekarang > 0) {
            $ok = catat_pembayaran(db_last_id(), $total, 0, $bayar_sekarang, $metode, $kanal, (int)$user['id']);
        }
        if ($ok) {
            mysqli_commit($koneksi);
            $sisa = $total - $bayar_sekarang;
            $info = $bayar_sekarang > 0
                ? ' Dibayar ' . rupiah($bayar_sekarang) . ($sisa > 0 ? ', sisa ' . rupiah($sisa) . ' dibayar di kasir.' : ' (lunas).')
                : ' Belum ada pembayaran.';
            flash('success', 'Booking berhasil! Kode: ' . $kode . '.' . $info);
            redirect('?page=booking');
        }
        $err[] = 'Gagal menyimpan booking, coba lagi.';
    }
    mysqli_rollback($koneksi);
}
$min_dp_persen = DP_MIN_PERSEN;
?>
<div class="card">
    <div class="header">
        <h2>BOOKING LAPANGAN BARU</h2>
        <div class="header-actions"><a class="btn btn-light btn-sm" href="?page=booking"><?= ikon('back') ?> Kembali</a></div>
    </div>
    <div class="body">
        <?php if ($err): ?>
            <div class="alert alert-danger">
                <ul><?php foreach ($err as $m) echo '<li>' . e($m) . '</li>'; ?></ul>
            </div>
        <?php endif; ?>
        <form method="post" id="form-booking" data-staff="<?= $staff ? 1 : 0 ?>" data-dp-persen="<?= (int)$min_dp_persen ?>" autocomplete="off">
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
                    <label for="nama_penyewa"><?= $staff ? 'Nama Customer' : 'Nama Penyewa' ?></label>
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

                <div class="form-group full">
                    <label>Pembayaran</label>
                    <div class="pay-opts">
                        <label class="pay-opt"><input type="radio" name="tipe_bayar" value="dp" <?= $tipe_bayar === 'dp' ? 'checked' : '' ?>><span><b>Bayar DP</b></span></label>
                        <label class="pay-opt"><input type="radio" name="tipe_bayar" value="lunas" <?= $tipe_bayar === 'lunas' ? 'checked' : '' ?>><span><b>Bayar lunas</b></span></label>
                    </div>
                </div>
                <div class="form-group" id="grp-dp">
                    <label for="jumlah_dp">Jumlah DP (Rp)</label>
                    <input class="form-control" id="jumlah_dp" name="jumlah_dp" inputmode="numeric" value="<?= e($jumlah_dp) ?>" placeholder="0">
                    <span class="form-hint" id="dp-hint"></span>
                </div>
                <div class="form-group" id="grp-metode">
                    <label for="metode"><?= $staff ? 'Metode Pembayaran' : 'Bayar Lewat' ?></label>
                    <select class="form-control" id="metode" name="metode">
                        <?php foreach ($staff ? METODE_BAYAR : ['QRIS', 'Transfer'] as $m): ?>
                            <option <?= $metode === $m ? 'selected' : '' ?>><?= e($m) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="total-box">
                    <div class="tb-row"><span>Total sewa</span><strong id="total-estimasi">Rp 0</strong></div>
                    <div class="tb-row sub"><span>Dibayar sekarang</span><b id="total-bayar">Rp 0</b></div>
                    <div class="tb-row sub"><span>Sisa (dibayar di kasir)</span><b id="total-sisa">Rp 0</b></div>
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit"><?= ikon('check') ?> <?= $staff ? 'Simpan Booking' : 'Booking &amp; Bayar' ?></button>
                <a class="btn btn-light" href="?page=booking">Batal</a>
            </div>
        </form>
    </div>
</div>
