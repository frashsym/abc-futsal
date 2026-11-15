<?php
// inc/sidebar.php - menu kiri. Isi menu berbeda untuk admin dan kasir.
$menu_aktif = $menu_aktif ?? '';
$master_aktif = in_array($menu_aktif, ['lapangan', 'waktu', 'harga'], true);

// Admin: hitung pengajuan pembatalan yang menunggu persetujuan (untuk badge merah)
$jumlah_pengajuan = 0;
if ($user['level'] === 'admin') {
    $jumlah_pengajuan = (int)$pdo->query("SELECT COUNT(*) FROM tb_pembatalan WHERE status = 'Menunggu'")->fetchColumn();
}
?>
<aside class="app-side no-print">
    <div class="user-info">
        <div class="avatar"><?= e(inisial($user['nama'])) ?></div>
        <div class="nama"><?= e($user['nama']) ?></div>
        <span class="role"><?= e($user['level']) ?></span>
    </div>

    <div class="menu-label">MENU UTAMA</div>
    <ul class="menu">
        <li class="<?= $menu_aktif === 'home' ? 'active' : '' ?>" style="--c:#2f7bff">
            <a class="item" href="<?= BASE_URL ?>index.php"><span class="tile"><i class="bi bi-house-door"></i></span>Home</a>
        </li>

        <?php if ($user['level'] === 'admin'): ?>
            <li class="<?= $menu_aktif === 'pengguna' ? 'active' : '' ?>" style="--c:#7c5cff">
                <a class="item" href="<?= BASE_URL ?>admin/pengguna.php"><span class="tile"><i class="bi bi-people"></i></span>Data User / Kasir</a>
            </li>
            <li class="<?= $master_aktif ? 'active' : '' ?>" style="--c:#ff8a3d">
                <a class="item" data-bs-toggle="collapse" href="#menuMaster" role="button">
                    <span class="tile"><i class="bi bi-database"></i></span>Master Data
                    <i class="bi bi-chevron-down caret"></i>
                </a>
                <ul class="submenu collapse <?= $master_aktif ? 'show' : '' ?>" id="menuMaster">
                    <li class="<?= $menu_aktif === 'lapangan' ? 'active' : '' ?>"><a href="<?= BASE_URL ?>admin/lapangan.php">Data Lapangan</a></li>
                    <li class="<?= $menu_aktif === 'waktu' ? 'active' : '' ?>"><a href="<?= BASE_URL ?>admin/waktu.php">Jam Operasional</a></li>
                    <li class="<?= $menu_aktif === 'harga' ? 'active' : '' ?>"><a href="<?= BASE_URL ?>admin/harga.php">Harga Sewa</a></li>
                </ul>
            </li>
            <li class="<?= $menu_aktif === 'booking' ? 'active' : '' ?>" style="--c:#12b886">
                <a class="item" href="<?= BASE_URL ?>booking/daftar.php"><span class="tile"><i class="bi bi-calendar-check"></i></span>Data Booking</a>
            </li>
            <li class="<?= $menu_aktif === 'pembatalan' ? 'active' : '' ?>" style="--c:#ffb020">
                <a class="item" href="<?= BASE_URL ?>admin/pembatalan.php">
                    <span class="tile"><i class="bi bi-arrow-counterclockwise"></i></span>Pembatalan &amp; Refund
                    <?php if ($jumlah_pengajuan > 0): ?><span class="badge rounded-pill bg-danger ms-auto"><?= $jumlah_pengajuan ?></span><?php endif; ?>
                </a>
            </li>
            <li class="<?= $menu_aktif === 'laporan' ? 'active' : '' ?>" style="--c:#ff5fa8">
                <a class="item" href="<?= BASE_URL ?>admin/laporan.php"><span class="tile"><i class="bi bi-file-earmark-bar-graph"></i></span>Laporan Transaksi</a>
            </li>
        <?php else: ?>
            <li class="<?= $menu_aktif === 'jadwal' ? 'active' : '' ?>" style="--c:#7c5cff">
                <a class="item" href="<?= BASE_URL ?>kasir/jadwal.php"><span class="tile"><i class="bi bi-grid-3x3-gap"></i></span>Cek Jadwal</a>
            </li>
            <li class="<?= $menu_aktif === 'booking_baru' ? 'active' : '' ?>" style="--c:#ff8a3d">
                <a class="item" href="<?= BASE_URL ?>kasir/booking_form.php"><span class="tile"><i class="bi bi-calendar-plus"></i></span>Input Booking</a>
            </li>
            <li class="<?= $menu_aktif === 'booking' ? 'active' : '' ?>" style="--c:#12b886">
                <a class="item" href="<?= BASE_URL ?>booking/daftar.php"><span class="tile"><i class="bi bi-calendar-check"></i></span>Daftar Booking</a>
            </li>
            <li class="<?= $menu_aktif === 'pembatalan' ? 'active' : '' ?>" style="--c:#ffb020">
                <a class="item" href="<?= BASE_URL ?>kasir/pembatalan.php"><span class="tile"><i class="bi bi-arrow-counterclockwise"></i></span>Pengajuan Batal</a>
            </li>
        <?php endif; ?>

        <li class="<?= $menu_aktif === 'profile' ? 'active' : '' ?>" style="--c:#00b7e6">
            <a class="item" href="<?= BASE_URL ?>profile.php"><span class="tile"><i class="bi bi-person-circle"></i></span>Profile</a>
        </li>
        <li style="--c:#ff4d6d">
            <a class="item" href="<?= BASE_URL ?>logout.php" data-confirm="Yakin mau logout?"><span class="tile"><i class="bi bi-power"></i></span>Logout</a>
        </li>
    </ul>

    <div class="side-foot">&copy; <?= date('Y') ?> <b>ABC Futsal</b><br>Versi 2.0 &middot; Admin &amp; Kasir</div>
</aside>