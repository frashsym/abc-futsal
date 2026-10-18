<?php
$me = db_one("SELECT * FROM pengguna WHERE id_pengguna = ?", [(int)$user['id']]);
$jml = (int)db_val("SELECT COUNT(*) FROM booking WHERE id_pengguna = ?", [(int)$user['id']]);
?>
<div class="card">
    <div class="header">
        <h2>PROFILE</h2>
        <div class="header-actions"><a class="btn btn-primary btn-sm" href="?page=profile&aksi=ubah"><?= ikon('edit') ?> Edit Profile</a></div>
    </div>
    <div class="body">
        <div class="profile-hero">
            <div class="avatar"><?= e(inisial($me['nama_lengkap'])) ?></div>
            <div>
                <h2 style="font-size:22px;font-weight:800"><?= e($me['nama_lengkap']) ?></h2>
                <div style="margin-top:6px">@<?= e($me['username']) ?> &nbsp; <?= badge_status($me['level']) ?></div>
            </div>
        </div>
        <div class="info-list">
            <div><small>Nama Lengkap</small><b><?= e($me['nama_lengkap']) ?></b></div>
            <div><small>Username</small><b><?= e($me['username']) ?></b></div>
            <div><small>No. HP</small><b><?= e($me['no_hp'] ?: '-') ?></b></div>
            <div><small>Bergabung</small><b><?= e(tgl_indo($me['created_at'])) ?></b></div>
            <div><small>Total Booking</small><b><?= $jml ?>x</b></div>
            <div><small>Level Akses</small><b><?= e(ucfirst($me['level'])) ?></b></div>
        </div>
    </div>
</div>