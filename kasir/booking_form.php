<?php
// ============================================================
//  kasir/booking_form.php - kasir menginput pesanan (offline / WhatsApp)

//  Alur: pilih tanggal + lapangan + jam -> isi data penyewa
//        -> isi pembayaran (DP / Lunas) -> Simpan.
//  Data dikirim ke booking_simpan.php
// ============================================================
$level_wajib = 'kasir';
require_once '../auth.php';

$judul_halaman = 'Input Booking';
$menu_aktif    = 'booking_baru';

// Daftar lapangan yang bisa dipakai + semua jam operasional
$daftar_lapangan = $pdo->query("SELECT * FROM tb_lapangan WHERE status = 'Tersedia' ORDER BY nama_lapangan")->fetchAll();
$daftar_waktu    = $pdo->query("SELECT * FROM tb_waktu ORDER BY jam_mulai")->fetchAll();

// Peta harga untuk JavaScript: harga[id_lapangan][id_waktu]
$harga = [];
foreach ($pdo->query("SELECT id_lapangan, id_waktu, harga FROM tb_harga") as $h) {
    $harga[$h['id_lapangan']][$h['id_waktu']] = (int)$h['harga'];
}

// Nilai awal form: dari klik slot di Cek Jadwal (?tanggal&lapangan&waktu), atau dari input sebelumnya yang gagal
$d = [
    'tanggal_main' => $_GET['tanggal'] ?? date('Y-m-d'),
    'id_lapangan'  => $_GET['lapangan'] ?? '',
    'id_waktu'     => $_GET['waktu'] ?? '',
    'nama_penyewa' => '',
    'no_hp' => '',
    'sumber' => 'WhatsApp',
    'catatan' => '',
    'tipe_bayar'   => 'DP',
    'jumlah_bayar' => '',
    'metode' => 'Tunai',
];
if (!empty($_SESSION['old'])) {
    $d = array_merge($d, $_SESSION['old']);
    unset($_SESSION['old']);
}

include '../inc/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2>Input Booking Baru</h2>
        <a class="btn btn-light btn-sm" href="jadwal.php"><i class="bi bi-grid-3x3-gap"></i> Cek Jadwal</a>
    </div>
    <div class="card-body">
        <form method="post" action="booking_simpan.php" id="form-booking" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

            <h3 class="h6 fw-bold text-primary mb-3">1. Jadwal</h3>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label" for="tanggal_main">Tanggal Main</label>
                    <input class="form-control" type="date" id="tanggal_main" name="tanggal_main" min="<?= date('Y-m-d') ?>" value="<?= e($d['tanggal_main']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="id_lapangan">Lapangan</label>
                    <select class="form-select" id="id_lapangan" name="id_lapangan" required>
                        <option value="">— Pilih lapangan —</option>
                        <?php foreach ($daftar_lapangan as $l): ?>
                            <option value="<?= (int)$l['id_lapangan'] ?>" <?= (string)$d['id_lapangan'] === (string)$l['id_lapangan'] ? 'selected' : '' ?>>
                                <?= e($l['nama_lapangan']) ?> · <?= e($l['jenis_lantai']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="id_waktu">Jam Main</label>
                    <select class="form-select" id="id_waktu" name="id_waktu" required>
                        <option value="">— Pilih jam —</option>
                        <?php foreach ($daftar_waktu as $w): ?>
                            <option value="<?= (int)$w['id_waktu'] ?>" <?= (string)$d['id_waktu'] === (string)$w['id_waktu'] ? 'selected' : '' ?>>
                                <?= e(jam($w['jam_mulai'])) ?> – <?= e(jam($w['jam_selesai'])) ?><?= $w['keterangan'] ? ' (' . e($w['keterangan']) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Jam yang sudah dibooking / lewat otomatis tidak bisa dipilih.</div>
                </div>
            </div>

            <h3 class="h6 fw-bold text-primary mb-3">2. Data Penyewa</h3>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label" for="nama_penyewa">Nama Penyewa</label>
                    <input class="form-control" id="nama_penyewa" name="nama_penyewa" value="<?= e($d['nama_penyewa']) ?>" maxlength="100" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="no_hp">No. HP / WhatsApp</label>
                    <input class="form-control" id="no_hp" name="no_hp" value="<?= e($d['no_hp']) ?>" placeholder="08xxxxxxxxxx" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="sumber">Pesanan Masuk Dari</label>
                    <select class="form-select" id="sumber" name="sumber">
                        <option value="WhatsApp" <?= $d['sumber'] === 'WhatsApp' ? 'selected' : '' ?>>WhatsApp</option>
                        <option value="Offline" <?= $d['sumber'] === 'Offline'  ? 'selected' : '' ?>>Datang langsung (Offline)</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label" for="catatan">Catatan (opsional)</label>
                    <input class="form-control" id="catatan" name="catatan" value="<?= e($d['catatan']) ?>" maxlength="255" placeholder="Contoh: sparing tim kantor">
                </div>
            </div>

            <h3 class="h6 fw-bold text-primary mb-3">3. Pembayaran</h3>
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label" for="tipe_bayar">Jenis Pembayaran</label>
                    <select class="form-select" id="tipe_bayar" name="tipe_bayar">
                        <option value="DP" <?= $d['tipe_bayar'] === 'DP'    ? 'selected' : '' ?>>DP (bayar sebagian)</option>
                        <option value="Lunas" <?= $d['tipe_bayar'] === 'Lunas' ? 'selected' : '' ?>>Lunas</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="jumlah_bayar">Nominal Diterima (Rp)</label>
                    <input class="form-control" type="number" id="jumlah_bayar" name="jumlah_bayar" value="<?= e($d['jumlah_bayar']) ?>" min="0" step="1000" required>
                    <div class="form-text" id="hint-bayar"></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="metode">Metode</label>
                    <select class="form-select" id="metode" name="metode">
                        <option value="Tunai" <?= $d['metode'] === 'Tunai'    ? 'selected' : '' ?>>Tunai</option>
                        <option value="Transfer" <?= $d['metode'] === 'Transfer' ? 'selected' : '' ?>>Transfer</option>
                    </select>
                </div>
            </div>

            <div class="total-box mb-2">
                <span>Total harga sewa</span><strong id="total-harga">Rp 0</strong>
            </div>
            <div class="text-end text-muted">Sisa yang harus dibayar: <b id="sisa-bayar">Rp 0</b></div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Simpan Booking</button>
                <a class="btn btn-light" href="../index.php">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
    // Data untuk js/app.js (hitung total, cek jadwal)
    window.DATA_BOOKING = {
        harga: <?= json_encode($harga, JSON_FORCE_OBJECT) ?>,
        dpPersen: <?= (int)DP_MINIMAL_PERSEN ?>,
        urlCek: '<?= BASE_URL ?>cek_jadwal.php'
    };
</script>
<?php include '../inc/footer.php'; ?>