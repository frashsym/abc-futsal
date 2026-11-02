<?php
date_default_timezone_set('Asia/Jakarta');

/* ---------- Format & output ---------- */
function e($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
function rupiah($n)
{
    return 'Rp ' . number_format((float)$n, 0, ',', '.');
}
function jam($t)
{
    return substr((string)$t, 0, 5);
}
function tgl_indo($tgl)
{
    if (!$tgl) return '-';
    $bln = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($tgl);
    return date('d', $t) . ' ' . $bln[(int)date('n', $t)] . ' ' . date('Y', $t);
}
function hari_indo($tgl)
{
    $h = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    return $h[(int)date('w', strtotime($tgl))];
}
function inisial($nama)
{
    $p = preg_split('/\s+/', trim((string)$nama));
    $i = strtoupper(mb_substr($p[0] ?? '?', 0, 1));
    if (count($p) > 1) $i .= strtoupper(mb_substr($p[1], 0, 1));
    return $i;
}
function ikon($nama, $kelas = '')
{
    return '<svg class="ic ' . e($kelas) . '" aria-hidden="true"><use href="#i-' . e($nama) . '"/></svg>';
}
function badge_status($s)
{
    return '<span class="badge badge-' . strtolower(e($s)) . '">' . e($s) . '</span>';
}
function level_label($l)
{
    return ['admin' => 'Admin', 'kasir' => 'Kasir', 'pelanggan' => 'User'][$l] ?? ucfirst((string)$l);
}
function badge_level($l)
{
    return '<span class="badge badge-' . strtolower(e($l)) . '">' . e(level_label($l)) . '</span>';
}
// Status pembayaran sebuah booking -> [label, kelas css]
function info_bayar($b)
{
    $total = (int)$b['total_bayar'];
    $dibayar = (int)$b['dibayar'];
    if ($b['status'] === 'Cancel') return $dibayar > 0 ? ['DP Hangus', 'hangus'] : ['Dibatalkan', 'cancel'];
    if ($dibayar >= $total) return ['Lunas', 'lunas'];
    if ($dibayar > 0) return ['DP', 'dp'];
    return ['Belum Bayar', 'belum'];
}
function badge_bayar($b)
{
    $i = info_bayar($b);
    return '<span class="badge badge-bayar-' . $i[1] . '">' . e($i[0]) . '</span>';
}
function sisa_bayar($b)
{
    return $b['status'] === 'Booking' ? max(0, (int)$b['total_bayar'] - (int)$b['dibayar']) : 0;
}

/* ---------- Navigasi, flash, CSRF ---------- */
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function flash($tipe, $pesan)
{
    $_SESSION['flash'][] = [$tipe, $pesan];
}
// Flash ditampilkan sebagai toast (lihat js/app.js), bukan banner statis
function flash_tampil()
{
    if (empty($_SESSION['flash'])) return;
    echo '<div class="toast-stack" aria-live="polite">';
    foreach ($_SESSION['flash'] as $f) {
        echo '<div class="toast toast-' . e($f[0]) . '" role="status" data-toast>'
            . '<span class="toast-ic">' . ikon($f[0] === 'success' ? 'check' : ($f[0] === 'danger' ? 'x' : 'alert')) . '</span>'
            . '<div class="toast-txt"><b>' . ($f[0] === 'success' ? 'Berhasil' : ($f[0] === 'danger' ? 'Gagal' : 'Info')) . '</b><span>' . e($f[1]) . '</span></div>'
            . '<button type="button" class="toast-x" aria-label="Tutup">&times;</button><i class="toast-bar"></i></div>';
    }
    echo '</div>';
    unset($_SESSION['flash']);
}

function csrf_token()
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}
function csrf_cek()
{
    $t = $_POST['csrf'] ?? $_GET['t'] ?? '';
    if (!hash_equals(csrf_token(), (string)$t)) {
        flash('danger', 'Sesi tidak valid, silakan ulangi aksi tadi.');
        redirect('index.php');
    }
}
// URL aksi berbahaya (hapus/cancel/selesai) -> otomatis bawa token CSRF
function url_aksi($page, $aksi, $id)
{
    return '?page=' . urlencode($page) . '&aksi=' . urlencode($aksi) . '&id=' . (int)$id . '&t=' . csrf_token();
}

/* ---------- Login & hak akses ---------- */
function login_cek()
{
    if (empty($_SESSION['user'])) redirect('login.php');
}
function is_admin()
{
    return (($_SESSION['user']['level'] ?? '') === 'admin');
}
function is_kasir()
{
    return (($_SESSION['user']['level'] ?? '') === 'kasir');
}
// Admin atau kasir = petugas yang boleh mencatat transaksi di tempat
function is_staff()
{
    return is_admin() || is_kasir();
}
function admin_only()
{
    if (!is_admin()) {
        flash('danger', 'Halaman itu khusus admin.');
        redirect('index.php');
    }
}
function staff_only()
{
    if (!is_staff()) {
        flash('danger', 'Aksi itu khusus admin dan kasir.');
        redirect('index.php');
    }
}

/* ---------- Database (prepared statement) ---------- */
$GLOBALS['db_errno'] = 0;

function db_bind($st, $params)
{
    if (!$params) return;
    $tipe = '';
    foreach ($params as $p) $tipe .= is_int($p) ? 'i' : 's';
    mysqli_stmt_bind_param($st, $tipe, ...$params);
}
function db_all($sql, $params = [])
{
    global $koneksi;
    $st = mysqli_prepare($koneksi, $sql);
    if (!$st) {
        error_log('SQL error: ' . mysqli_error($koneksi));
        return [];
    }
    db_bind($st, $params);
    mysqli_stmt_execute($st);
    $r = mysqli_stmt_get_result($st);
    $rows = $r ? mysqli_fetch_all($r, MYSQLI_ASSOC) : [];
    mysqli_stmt_close($st);
    return $rows;
}
function db_one($sql, $params = [])
{
    $r = db_all($sql, $params);
    return $r[0] ?? null;
}
function db_val($sql, $params = [])
{
    $r = db_one($sql, $params);
    return $r ? array_values($r)[0] : null;
}
function db_exec($sql, $params = [])
{
    global $koneksi;
    $GLOBALS['db_errno'] = 0;
    $st = mysqli_prepare($koneksi, $sql);
    if (!$st) {
        $GLOBALS['db_errno'] = mysqli_errno($koneksi);
        error_log('SQL error: ' . mysqli_error($koneksi));
        return false;
    }
    db_bind($st, $params);
    $ok = mysqli_stmt_execute($st);
    if (!$ok) $GLOBALS['db_errno'] = mysqli_stmt_errno($st);
    mysqli_stmt_close($st);
    return $ok;
}
function db_last_id()
{
    global $koneksi;
    return mysqli_insert_id($koneksi);
}

/* ---------- Logika booking ---------- */
function durasi_jam($mulai, $selesai)
{
    return (strtotime($selesai) - strtotime($mulai)) / 3600;
}
function tanggal_valid($t)
{
    $d = DateTime::createFromFormat('Y-m-d', (string)$t);
    return $d && $d->format('Y-m-d') === $t;
}

// Daftar id_waktu yang TIDAK bisa dipesan di lapangan + tanggal tertentu.
// Return: [id_waktu => 'penuh' | 'lewat']
function waktu_tidak_tersedia($tanggal, $id_lapangan)
{
    $hasil = [];
    $rows = db_all(
        "SELECT w2.id_waktu FROM waktu w2 WHERE EXISTS (
            SELECT 1 FROM booking b JOIN waktu w ON w.id_waktu = b.id_waktu
            WHERE b.tanggal_main = ? AND b.id_lapangan = ? AND b.status <> 'Cancel'
              AND w.jam_mulai < w2.jam_selesai AND w.jam_selesai > w2.jam_mulai)",
        [$tanggal, (int)$id_lapangan]
    );
    foreach ($rows as $r) $hasil[(int)$r['id_waktu']] = 'penuh';
    if ($tanggal === date('Y-m-d')) {
        foreach (db_all("SELECT id_waktu FROM waktu WHERE jam_selesai <= ?", [date('H:i:s')]) as $r) {
            if (!isset($hasil[(int)$r['id_waktu']])) $hasil[(int)$r['id_waktu']] = 'lewat';
        }
    }
    return $hasil;
}

// Validasi input form booking.
// Return: [daftar_error, data_lapangan, data_waktu]
function booking_validasi($d)
{
    $err = [];

    if (!tanggal_valid($d['tanggal_main'])) $err[] = 'Tanggal main tidak valid.';
    if (mb_strlen($d['nama_penyewa']) < 3) $err[] = 'Nama penyewa minimal 3 karakter.';
    if (mb_strlen($d['nama_penyewa']) > 100) $err[] = 'Nama penyewa terlalu panjang.';
    if ($d['no_hp'] !== '' && !preg_match('/^[0-9+\- ]{8,20}$/', $d['no_hp'])) $err[] = 'Nomor HP tidak valid.';
    if (mb_strlen($d['catatan']) > 255) $err[] = 'Catatan maksimal 255 karakter.';

    $lap = db_one("SELECT * FROM lapangan WHERE id_lapangan = ?", [(int)$d['id_lapangan']]);
    $wkt = db_one("SELECT * FROM waktu WHERE id_waktu = ?", [(int)$d['id_waktu']]);
    if (!$lap) $err[] = 'Pilih lapangan dulu.';
    if (!$wkt) $err[] = 'Pilih jam main dulu.';

    if (!$err) {
        if ($lap['status'] !== 'Tersedia') $err[] = $lap['nama_lapangan'] . ' sedang perbaikan, pilih lapangan lain.';
        if ($d['tanggal_main'] < date('Y-m-d')) $err[] = 'Tanggal main tidak boleh sudah lewat.';
        if (!$err) {
            $tak = waktu_tidak_tersedia($d['tanggal_main'], $lap['id_lapangan']);
            $alasan = $tak[(int)$wkt['id_waktu']] ?? '';
            if ($alasan === 'penuh') $err[] = 'Jadwal itu sudah dibooking orang lain. Coba jam atau lapangan lain.';
            if ($alasan === 'lewat') $err[] = 'Jam itu sudah lewat, pilih jam yang masih jalan.';
        }
    }
    return [$err, $lap, $wkt];
}

function hitung_total($lap, $wkt)
{
    return (int)round($lap['harga_per_jam'] * durasi_jam($wkt['jam_mulai'], $wkt['jam_selesai']));
}


/* ---------- Pembayaran & DP ---------- */
// DP minimal untuk booking online (persen dari total). Ubah angka ini kalau kebijakan tempat futsal beda.
const DP_MIN_PERSEN = 30;
const METODE_BAYAR = ['Tunai', 'Transfer', 'QRIS'];

// DP minimal dibulatkan ke atas per Rp 1.000, tidak pernah melebihi total
function dp_minimum($total)
{
    return min((int)$total, (int)(ceil($total * DP_MIN_PERSEN / 100 / 1000) * 1000));
}
// Ambil angka rupiah dari input ("50.000", "Rp 50000", dll) -> int
function angka($v)
{
    return (int)preg_replace('/\D/', '', (string)$v);
}
// Catat uang masuk ke sebuah booking. Panggil di dalam transaksi.
// $sudah = jumlah yang sudah dibayar SEBELUM pembayaran ini.
function catat_pembayaran($id_booking, $total, $sudah, $jumlah, $metode, $kanal, $id_pengguna)
{
    $sesudah = $sudah + $jumlah;
    $jenis = $sesudah >= $total ? 'Pelunasan' : 'DP';
    $ok = db_exec(
        "INSERT INTO pembayaran (id_booking, jumlah, jenis, metode, kanal, id_pengguna) VALUES (?, ?, ?, ?, ?, ?)",
        [(int)$id_booking, (int)$jumlah, $jenis, $metode, $kanal, (int)$id_pengguna]
    );
    return $ok && db_exec("UPDATE booking SET dibayar = dibayar + ? WHERE id_booking = ?", [(int)$jumlah, (int)$id_booking]);
}
