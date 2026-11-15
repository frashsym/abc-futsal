<?php
// ============================================================
//  kasir/booking_simpan.php - menyimpan booking baru + pembayaran pertama
//
//  Urutan proses (ikuti nomor di komentar):
//    1. Ambil & rapikan input
//    2. Validasi input dasar (tanpa database)
//    3. Mulai transaksi, kunci lapangan supaya tidak terjadi double booking
//    4. Cek lapangan, jam, harga, tanggal, dan tabrakan jadwal
//    5. Hitung total & validasi nominal pembayaran (DP / Lunas)
//    6. Simpan booking + catat pembayaran
// ============================================================
$level_wajib = 'kasir';
require_once '../auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: booking_form.php');
    exit;
}

// ---------- 1. Ambil input ----------
$tanggal     = $_POST['tanggal_main'] ?? '';
$id_lapangan = (int)($_POST['id_lapangan'] ?? 0);
$id_waktu    = (int)($_POST['id_waktu'] ?? 0);
$nama        = trim($_POST['nama_penyewa'] ?? '');
$no_hp       = trim($_POST['no_hp'] ?? '');
$sumber      = $_POST['sumber'] ?? '';
$catatan     = trim($_POST['catatan'] ?? '');
$tipe_bayar  = $_POST['tipe_bayar'] ?? '';
$jumlah_bayar = (int)($_POST['jumlah_bayar'] ?? 0);
$metode      = $_POST['metode'] ?? '';

// ---------- 2. Validasi dasar ----------
$error = [];
$cek_tgl = DateTime::createFromFormat('Y-m-d', $tanggal);
if (!$cek_tgl || $cek_tgl->format('Y-m-d') !== $tanggal)  $error[] = 'Tanggal main tidak valid.';
elseif ($tanggal < date('Y-m-d'))                         $error[] = 'Tanggal main tidak boleh sudah lewat.';
if ($id_lapangan <= 0)                                    $error[] = 'Pilih lapangan dulu.';
if ($id_waktu <= 0)                                       $error[] = 'Pilih jam main dulu.';
if (mb_strlen($nama) < 3 || mb_strlen($nama) > 100)       $error[] = 'Nama penyewa harus 3–100 karakter.';
if (!preg_match('/^[0-9+\- ]{8,20}$/', $no_hp))           $error[] = 'Nomor HP tidak valid.';
if (!in_array($sumber, ['Offline', 'WhatsApp'], true))    $error[] = 'Sumber pesanan tidak valid.';
if (mb_strlen($catatan) > 255)                            $error[] = 'Catatan maksimal 255 karakter.';
if (!in_array($tipe_bayar, ['DP', 'Lunas'], true))        $error[] = 'Jenis pembayaran tidak valid.';
if (!in_array($metode, ['Tunai', 'Transfer'], true))      $error[] = 'Metode pembayaran tidak valid.';

// Kalau gagal, kembali ke form dengan isian yang tadi diketik
$kembali_ke_form = function ($pesan) use ($tanggal, $id_lapangan, $id_waktu, $nama, $no_hp, $sumber, $catatan, $tipe_bayar, $jumlah_bayar, $metode) {
    $_SESSION['old'] = [
        'tanggal_main' => $tanggal,
        'id_lapangan' => $id_lapangan,
        'id_waktu' => $id_waktu,
        'nama_penyewa' => $nama,
        'no_hp' => $no_hp,
        'sumber' => $sumber,
        'catatan' => $catatan,
        'tipe_bayar' => $tipe_bayar,
        'jumlah_bayar' => $jumlah_bayar,
        'metode' => $metode,
    ];
    $_SESSION['pesan'] = ['danger', $pesan];
    header('Location: booking_form.php');
    exit;
};
if ($error) $kembali_ke_form($error);

// ---------- 3. Mulai transaksi ----------
$pdo->beginTransaction();
try {
    // Kunci baris lapangan: kasir lain yang memesan lapangan yang sama harus menunggu
    // sampai transaksi ini selesai. Inilah yang mencegah double booking.
    $stmt = $pdo->prepare("SELECT * FROM tb_lapangan WHERE id_lapangan = ? FOR UPDATE");
    $stmt->execute([$id_lapangan]);
    $lapangan = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT * FROM tb_waktu WHERE id_waktu = ?");
    $stmt->execute([$id_waktu]);
    $waktu = $stmt->fetch();

    // ---------- 4. Cek lapangan, jam, harga ----------
    if (!$lapangan || !$waktu) {
        throw new Exception('Lapangan atau jam tidak ditemukan.');
    }
    if ($lapangan['status'] !== 'Tersedia') {
        throw new Exception($lapangan['nama_lapangan'] . ' sedang perbaikan, pilih lapangan lain.');
    }
    if ($tanggal === date('Y-m-d') && $waktu['jam_selesai'] <= date('H:i:s')) {
        throw new Exception('Jam itu sudah lewat, pilih jam yang masih berjalan.');
    }

    $stmt = $pdo->prepare("SELECT harga FROM tb_harga WHERE id_lapangan = ? AND id_waktu = ?");
    $stmt->execute([$id_lapangan, $id_waktu]);
    $total = (int)$stmt->fetchColumn();
    if ($total <= 0) {
        throw new Exception('Harga untuk lapangan dan jam ini belum diatur admin.');
    }

    // Cek tabrakan jadwal: ada booking lain (bukan Cancel) di lapangan & tanggal yang sama
    // dengan jam yang saling bertumpuk?
    //   wb.jam_mulai   < jam_selesai slot baru  -> booking lama mulai sebelum slot baru selesai
    //   wb.jam_selesai > jam_mulai slot baru    -> booking lama selesai setelah slot baru mulai
    $stmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM tb_booking b
         JOIN tb_waktu wb ON wb.id_waktu = b.id_waktu
         WHERE b.id_lapangan = ?
           AND b.tanggal_main = ?
           AND b.status <> 'Cancel'
           AND wb.jam_mulai < ?
           AND wb.jam_selesai > ?"
    );
    $stmt->execute([$id_lapangan, $tanggal, $waktu['jam_selesai'], $waktu['jam_mulai']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception('Jadwal itu sudah dibooking orang lain. Pilih jam atau lapangan lain.');
    }

    // ---------- 5. Hitung nominal pembayaran ----------
    if ($tipe_bayar === 'Lunas') {
        $jumlah_bayar = $total;                       // lunas = bayar sebesar total
    } else {
        $dp_minimal = (int)ceil($total * DP_MINIMAL_PERSEN / 100);
        if ($jumlah_bayar < $dp_minimal) {
            throw new Exception('DP minimal ' . DP_MINIMAL_PERSEN . '% dari total, yaitu ' . rupiah($dp_minimal) . '.');
        }
        if ($jumlah_bayar >= $total) {
            throw new Exception('Nominal DP harus kurang dari total (' . rupiah($total) . '). Pilih "Lunas" jika dibayar penuh.');
        }
    }

    // ---------- 6. Simpan ----------
    // 6a. Booking. Kode sementara dulu, nanti diganti kode final berdasarkan id.
    $kode_sementara = 'TMP' . bin2hex(random_bytes(6));
    $stmt = $pdo->prepare(
        "INSERT INTO tb_booking
           (kode_booking, id_kasir, id_lapangan, id_waktu, tanggal_main, nama_penyewa, no_hp, sumber,
            total_harga, jumlah_dibayar, status_bayar, status, catatan)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Booking', ?)"
    );
    $stmt->execute([
        $kode_sementara,
        $user['id'],
        $id_lapangan,
        $id_waktu,
        $tanggal,
        $nama,
        $no_hp,
        $sumber,
        $total,
        $jumlah_bayar,
        $tipe_bayar,
        $catatan ?: null,
    ]);
    $id_booking = (int)$pdo->lastInsertId();

    // 6b. Kode final
    $kode = 'BK-' . date('ymd') . '-' . str_pad($id_booking, 4, '0', STR_PAD_LEFT);
    $stmt = $pdo->prepare("UPDATE tb_booking SET kode_booking = ? WHERE id_booking = ?");
    $stmt->execute([$kode, $id_booking]);

    // 6c. Catat pembayaran pertama (DP atau Lunas)
    $stmt = $pdo->prepare("INSERT INTO tb_pembayaran (id_booking, id_kasir, tipe, jumlah, metode) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$id_booking, $user['id'], $tipe_bayar, $jumlah_bayar, $metode]);

    $pdo->commit();
} catch (PDOException $ex) {
    // Error database (bukan kesalahan input kasir): jangan tampilkan detail SQL ke layar
    $pdo->rollBack();
    error_log('booking_simpan: ' . $ex->getMessage());
    $kembali_ke_form(['Terjadi kesalahan saat menyimpan ke database. Coba lagi.']);
} catch (Exception $ex) {
    // Kesalahan aturan bisnis (jadwal penuh, DP kurang, dll.)
    $pdo->rollBack();
    $kembali_ke_form([$ex->getMessage()]);
}

$_SESSION['pesan'] = ['success', "Booking $kode berhasil disimpan ($tipe_bayar " . rupiah($jumlah_bayar) . "). Silakan cetak nota untuk penyewa."];
header('Location: ../booking/detail.php?id=' . $id_booking);
