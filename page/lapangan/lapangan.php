<?php
$rows = db_all("SELECT l.*, (SELECT COUNT(*) FROM booking b WHERE b.id_lapangan = l.id_lapangan) AS jml_booking FROM lapangan l ORDER BY l.nama_lapangan");
?>
<div class="row clearfix">
    <div class="card">
        <div class="header">
            <h2>DATA LAPANGAN</h2>
            <div class="header-actions">
                <a class="btn btn-primary" href="?page=lapangan&aksi=tambah"><?= ikon('plus') ?> Tambah Lapangan</a>
            </div>
        </div>
        <div class="body">
            <div class="table-wrap">
                <table class="table js-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Lapangan</th>
                            <th>Jenis Lantai</th>
                            <th>Harga / Jam</th>
                            <th>Status</th>
                            <th>Total Booking</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $i => $r): ?>
                            <tr>
                                <td class="no"><?= $i + 1 ?></td>
                                <td><b><?= e($r['nama_lapangan']) ?></b></td>
                                <td><?= e($r['jenis_lantai']) ?></td>
                                <td class="nowrap"><?= rupiah($r['harga_per_jam']) ?></td>
                                <td><?= badge_status($r['status']) ?></td>
                                <td><?= (int)$r['jml_booking'] ?>x</td>
                                <td>
                                    <div class="aksi">
                                        <a class="btn btn-warn btn-icon" title="Ubah" href="?page=lapangan&aksi=ubah&id=<?= (int)$r['id_lapangan'] ?>"><?= ikon('edit') ?></a>
                                        <a class="btn btn-danger btn-icon" title="Hapus" href="<?= url_aksi('lapangan', 'hapus', $r['id_lapangan']) ?>" data-confirm="Hapus <?= e($r['nama_lapangan']) ?>?"><?= ikon('trash') ?></a>
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