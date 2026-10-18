<?php
error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
ob_start();
session_start();
include "koneksi.php";
include "fungsi.php";
login_cek();

$page = $_GET['page'] ?? '';
$aksi = $_GET['aksi'] ?? '';

// Daftar halaman yang boleh dibuka: [hak akses, aksi yang tersedia]
$rute = [
    'pengguna' => ['admin', ['', 'tambah', 'ubah', 'hapus']],
    'lapangan' => ['admin', ['', 'tambah', 'ubah', 'hapus']],
    'waktu'    => ['admin', ['', 'tambah', 'ubah', 'hapus']],
    'booking'  => ['semua', ['', 'tambah', 'ubah', 'cancel', 'selesai']],
    'profile'  => ['semua', ['', 'ubah']],
    'laporan'  => ['admin', ['']],
];
$judul = [
    ''         => 'Home',
    'pengguna' => 'Data Pengguna',
    'lapangan' => 'Data Lapangan',
    'waktu'    => 'Data Waktu',
    'booking'  => 'Booking Lapangan',
    'profile'  => 'Profile',
    'laporan'  => 'Laporan Rekap Penyewaan',
];

$file = 'home.php';
$notfound = false;
if ($page !== '') {
    if (!isset($rute[$page]) || !in_array($aksi, $rute[$page][1], true)) {
        $notfound = true;
    } else {
        if ($rute[$page][0] === 'admin') admin_only();
        $file = "page/$page/" . ($aksi === '' ? $page : $aksi) . ".php";
    }
}

$user = $_SESSION['user'];
$aktif = $page;
$master_aktif = in_array($page, ['lapangan', 'waktu'], true);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul[$page] ?? 'Halaman') ?> | ABC Futsal</title>
    <link rel="icon" type="image/svg+xml" href="images/bola.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        try {
            var t = localStorage.getItem('abc-theme');
            if (t) document.documentElement.setAttribute('data-theme', t)
        } catch (e) {}
    </script>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include "icons.php"; ?>

    <!-- Top Bar -->
    <nav class="app-nav">
        <button type="button" class="nav-btn side-toggle" aria-label="Menu"><?= ikon('menu') ?></button>
        <a class="navbar-brand" href="index.php"><img src="images/bola.png" alt="ABC"><span>Aplikasi Administrasi Penyewaan Futsal “ABC”</span></a>
        <div class="nav-space"></div>
        <button type="button" class="nav-btn theme-toggle" aria-label="Ganti tema"><?= ikon('moon') ?></button>
        <div class="nav-chip">
            <div class="avatar"><?= e(inisial($user['nama'])) ?></div><span><?= e($user['nama']) ?></span>
        </div>
    </nav>
    <!-- #END# Top Bar -->

    <!-- Sidebar -->
    <aside class="app-side">
        <div class="user-info">
            <div class="avatar"><?= e(inisial($user['nama'])) ?></div>
            <div class="nama"><?= e($user['nama']) ?></div>
            <span class="role"><?= e($user['level']) ?></span>
        </div>

        <div class="menu-label">MAIN NAVIGATION</div>
        <ul class="menu">
            <li class="<?= $page === '' && !$notfound ? 'active' : '' ?>" style="--c:#2f7bff">
                <a class="item" href="index.php"><span class="tile"><?= ikon('home') ?></span>Home</a>
            </li>

            <?php if (is_admin()): ?>
                <li class="<?= $aktif === 'pengguna' ? 'active' : '' ?>" style="--c:#7c5cff">
                    <a class="item" href="?page=pengguna"><span class="tile"><?= ikon('users') ?></span>Data Pengguna</a>
                </li>
                <li class="<?= $master_aktif ? 'open active' : '' ?>" style="--c:#ff8a3d">
                    <a class="item menu-toggle"><span class="tile"><?= ikon('database') ?></span>Master<?= ikon('chevron', 'caret') ?></a>
                    <ul class="submenu">
                        <li class="<?= $aktif === 'lapangan' ? 'active' : '' ?>"><a href="?page=lapangan">Data Lapangan</a></li>
                        <li class="<?= $aktif === 'waktu' ? 'active' : '' ?>"><a href="?page=waktu">Data Waktu</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <li class="<?= $aktif === 'booking' ? 'active' : '' ?>" style="--c:#12b886">
                <a class="item" href="?page=booking"><span class="tile"><?= ikon('calendar') ?></span>Booking Lapangan</a>
            </li>

            <?php if (is_admin()): ?>
                <li class="<?= $aktif === 'laporan' ? 'open active' : '' ?>" style="--c:#ff5fa8">
                    <a class="item menu-toggle"><span class="tile"><?= ikon('report') ?></span>Laporan<?= ikon('chevron', 'caret') ?></a>
                    <ul class="submenu">
                        <li class="<?= $aktif === 'laporan' ? 'active' : '' ?>"><a data-modal-open="smallModal2">Laporan Rekap Penyewaan</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <li class="<?= $aktif === 'profile' ? 'active' : '' ?>" style="--c:#00b7e6">
                <a class="item" href="?page=profile"><span class="tile"><?= ikon('user') ?></span>Profile</a>
            </li>
            <li style="--c:#ff4d6d">
                <a class="item" href="logout.php" data-confirm="Yakin mau logout?"><span class="tile"><?= ikon('power') ?></span>Logout</a>
            </li>
        </ul>

        <div class="side-foot">&copy; <?= date('Y') ?> <b>ABC Futsal</b>.<br>Version: 1.0</div>
    </aside>
    <div class="side-overlay"></div>
    <!-- #END# Sidebar -->

    <!-- Konten -->
    <main class="app-main">
        <?php
        flash_tampil();
        if ($notfound) {
            echo '<div class="card"><div class="body" style="text-align:center;padding:50px 20px"><div style="font-size:54px">🫠</div>'
                . '<h2 style="margin:10px 0 6px">Halaman tidak ditemukan</h2><p class="muted">Link yang kamu buka nggak ada.</p>'
                . '<a class="btn btn-primary" href="index.php">Balik ke Home</a></div></div>';
        } else {
            include $file;
        }
        ?>
        <div class="app-foot no-print">ABC Futsal &middot; Administrasi Penyewaan Lapangan <?= date('Y') ?></div>
    </main>
    <!-- #END# Konten -->

    <?php if (is_admin()): ?>
        <!-- Modal Laporan Rekap Penyewaan -->
        <div class="modal" id="smallModal2">
            <form class="modal-box" method="get" action="index.php">
                <input type="hidden" name="page" value="laporan">
                <div class="modal-head">
                    <h3>Laporan Rekap Penyewaan</h3><button type="button" class="nav-btn" data-modal-close aria-label="Tutup"><?= ikon('x') ?></button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label for="m_dari">Dari Tanggal</label>
                        <input class="form-control" type="date" id="m_dari" name="dari" value="<?= date('Y-m-01') ?>" required>
                    </div>
                    <div class="form-group"><label for="m_sampai">Sampai Tanggal</label>
                        <input class="form-control" type="date" id="m_sampai" name="sampai" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group"><label for="m_status">Status</label>
                        <select class="form-control" id="m_status" name="status">
                            <option value="">Semua status</option>
                            <option>Booking</option>
                            <option>Selesai</option>
                            <option>Cancel</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit"><?= ikon('report') ?> Tampilkan</button>
                        <button class="btn btn-light" type="button" data-modal-close>Batal</button>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <script src="js/app.js"></script>
</body>

</html>