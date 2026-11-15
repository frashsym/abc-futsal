<?php
// ============================================================
//  admin/harga.php - atur harga sewa per lapangan per jam
//  Pilih lapangan -> isi harga tiap slot -> Simpan semua sekaligus.
// ============================================================
$level_wajib = 'admin';
require_once '../auth.php';

$judul_halaman = 'Harga Sewa';
$menu_aktif    = 'harga';

$daftar_lapangan = $pdo->query("SELECT * FROM tb_lapangan ORDER BY nama_lapangan")->fetchAll();
$daftar_waktu    = $pdo->query("SELECT * FROM tb_waktu ORDER BY jam_mulai")->fetchAll();

// Lapangan yang sedang diatur (default: lapangan pertama)
$id_lapangan = (int)($_GET['id_lapangan'] ?? 0);
if ($id_lapangan === 0 && $daftar_lapangan) $id_lapangan = (int)$daftar_lapangan[0]['id_lapangan'];

// Harga yang sudah tersimpan: [id_waktu => harga]
$stmt = $pdo->prepare("SELECT id_waktu, harga FROM tb_harga WHERE id_lapangan = ?");
$stmt->execute([$id_lapangan]);
$harga = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

include '../inc/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2>Harga Sewa per Jam</h2>
    </div>
    <div class="card-body">
        <?php if (!$daftar_lapangan || !$daftar_waktu): ?>
            <p class="text-muted mb-0">Isi dulu data <a href="lapangan.php">lapangan</a> dan <a href="waktu.php">jam operasional</a>.</p>
        <?php else: ?>

            <form method="get" class="row g-2 align-items-end mb-4">
                <div class="col-md-5">
                    <label class="form-label" for="pilih_lapangan">Pilih Lapangan</label>
                    <select class="form-select" id="pilih_lapangan" name="id_lapangan" onchange="this.form.submit()">
                        <?php foreach ($daftar_lapangan as $l): ?>
                            <option value="<?= (int)$l['id_lapangan'] ?>" <?= $id_lapangan === (int)$l['id_lapangan'] ? 'selected' : '' ?>>
                                <?= e($l['nama_lapangan']) ?> (<?= e($l['jenis_lantai']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>

            <form method="post" action="harga_proses.php" autocomplete="off">
                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
                <input type="hidden" name="id_lapangan" value="<?= $id_lapangan ?>">

                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    <span class="text-muted">Isi semua jam dengan harga:</span>
                    <input type="number" min="0" step="1000" class="form-control" style="width:160px" id="harga-semua" placeholder="100000">
                    <button type="button" class="btn btn-light btn-sm" id="btn-samakan">Samakan semua</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Jam</th>
                                <th>Keterangan</th>
                                <th style="width:240px">Harga (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($daftar_waktu as $w): ?>
                                <tr>
                                    <td class="nowrap"><b><?= e(jam($w['jam_mulai'])) ?> – <?= e(jam($w['jam_selesai'])) ?></b></td>
                                    <td><?= e($w['keterangan'] ?: '-') ?></td>
                                    <td>
                                        <input type="number" min="0" max="10000000" step="1000" class="form-control input-harga"
                                            name="harga[<?= (int)$w['id_waktu'] ?>]"
                                            value="<?= isset($harga[$w['id_waktu']]) ? (int)$harga[$w['id_waktu']] : '' ?>"
                                            placeholder="belum diatur">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="form-text">Kosongkan kolom harga kalau slot itu tidak dijual untuk lapangan ini. Perubahan harga tidak mengubah booking yang sudah tercatat.</p>
                <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Simpan Harga</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    // Tombol "Samakan semua": isi semua kolom harga dengan nilai yang sama
    var btnSamakan = document.getElementById('btn-samakan');
    if (btnSamakan) {
        btnSamakan.addEventListener('click', function() {
            var nilai = document.getElementById('harga-semua').value;
            document.querySelectorAll('.input-harga').forEach(function(el) {
                el.value = nilai;
            });
        });
    }
</script>
<?php include '../inc/footer.php'; ?>