<?php
// ============================================================
//  inc/header.php - kerangka atas halaman (navbar + sidebar)
//  Sebelum include, isi dulu:
//      $judul_halaman = 'Data Lapangan';
//      $menu_aktif    = 'lapangan';   // kunci menu yang disorot di sidebar
//  (variabel $user sudah disediakan oleh auth.php)
// ============================================================
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul_halaman ?? 'Halaman') ?> | ABC Futsal</title>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>images/ball.webp">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>css/style.css" rel="stylesheet">
</head>

<body>

    <!-- Top Bar -->
    <nav class="app-nav no-print">
        <button type="button" class="nav-btn side-toggle" aria-label="Menu"><i class="bi bi-list fs-4"></i></button>
        <a class="navbar-brand" href="<?= BASE_URL ?>index.php">
            <img src="<?= BASE_URL ?>images/ball.webp" alt="ABC"><span>Aplikasi Penyewaan Futsal “ABC”</span>
        </a>
        <div class="flex-grow-1"></div>
        <div class="nav-chip">
            <div class="avatar"><?= e(inisial($user['nama'])) ?></div><span><?= e($user['nama']) ?></span>
        </div>
    </nav>

    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="side-overlay"></div>

    <!-- Konten -->
    <main class="app-main">
        <?php
        // Pesan sekali tampil (diisi lewat $_SESSION['pesan'] = ['success', 'teks'] atau ['danger', [daftar error]])
        if (!empty($_SESSION['pesan'])):
            [$jenis_pesan, $isi_pesan] = $_SESSION['pesan'];
            unset($_SESSION['pesan']);
        ?>
            <div class="alert alert-<?= e($jenis_pesan) ?> alert-dismissible fade show no-print" data-autohide>
                <?php if (is_array($isi_pesan)): ?>
                    <ul class="mb-0"><?php foreach ($isi_pesan as $baris) echo '<li>' . e($baris) . '</li>'; ?></ul>
                <?php else: ?>
                    <?= e($isi_pesan) ?>
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
            </div>
        <?php endif; ?>