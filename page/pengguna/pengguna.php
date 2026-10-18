<?php
$rows = db_all("SELECT * FROM pengguna ORDER BY level, nama_lengkap");
?>
<!-- Basic Examples -->
<div class="row clearfix">
    <div class="card">
        <div class="header">
            <h2>DATA PENGGUNA</h2>
            <div class="header-actions">
                <a class="btn btn-primary" href="?page=pengguna&aksi=tambah"><?= ikon('plus') ?> Tambah Pengguna</a>
            </div>
        </div>
        <div class="body">
            <div class="table-wrap">
                <table class="table js-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>No. HP</th>
                            <th>Level</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $i => $r): ?>
                            <tr>
                                <td class="no"><?= $i + 1 ?></td>
                                <td><b><?= e($r['nama_lengkap']) ?></b></td>
                                <td><?= e($r['username']) ?></td>
                                <td><?= e($r['no_hp'] ?: '-') ?></td>
                                <td><?= badge_status($r['level']) ?></td>
                                <td class="nowrap"><?= e(tgl_indo($r['created_at'])) ?></td>
                                <td>
                                    <div class="aksi">
                                        <a class="btn btn-warn btn-icon" title="Ubah" href="?page=pengguna&aksi=ubah&id=<?= (int)$r['id_pengguna'] ?>"><?= ikon('edit') ?></a>
                                        <?php if ((int)$r['id_pengguna'] !== (int)$user['id']): ?>
                                            <a class="btn btn-danger btn-icon" title="Hapus" href="<?= url_aksi('pengguna', 'hapus', $r['id_pengguna']) ?>" data-confirm="Hapus pengguna <?= e($r['nama_lengkap']) ?>?"><?= ikon('trash') ?></a>
                                        <?php endif; ?>
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
<!-- #END# Basic Examples -->