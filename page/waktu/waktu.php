<?php
$rows = db_all("SELECT w.*, (SELECT COUNT(*) FROM booking b WHERE b.id_waktu = w.id_waktu) AS jml_booking FROM waktu w ORDER BY w.jam_mulai");
?>
<div class="row clearfix">
    <div class="card">
        <div class="header">
            <h2>DATA WAKTU</h2>
            <div class="header-actions">
                <a class="btn btn-primary" href="?page=waktu&aksi=tambah"><?= ikon('plus') ?> Tambah Waktu</a>
            </div>
        </div>
        <div class="body">
            <div class="table-wrap">
                <table class="table js-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <th>Durasi</th>
                            <th>Keterangan</th>
                            <th>Total Booking</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $i => $r): ?>
                            <tr>
                                <td class="no"><?= $i + 1 ?></td>
                                <td><b><?= e(jam($r['jam_mulai'])) ?></b></td>
                                <td><b><?= e(jam($r['jam_selesai'])) ?></b></td>
                                <td><?= rtrim(rtrim(number_format(durasi_jam($r['jam_mulai'], $r['jam_selesai']), 2, ',', ''), '0'), ',') ?> jam</td>
                                <td><?= e($r['keterangan'] ?: '-') ?></td>
                                <td><?= (int)$r['jml_booking'] ?>x</td>
                                <td>
                                    <div class="aksi">
                                        <a class="btn btn-warn btn-icon" title="Ubah" href="?page=waktu&aksi=ubah&id=<?= (int)$r['id_waktu'] ?>"><?= ikon('edit') ?></a>
                                        <a class="btn btn-danger btn-icon" title="Hapus" href="<?= url_aksi('waktu', 'hapus', $r['id_waktu']) ?>" data-confirm="Hapus slot <?= e(jam($r['jam_mulai'])) ?>–<?= e(jam($r['jam_selesai'])) ?>?"><?= ikon('trash') ?></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>