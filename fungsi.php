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
function flash_tampil()
{
    if (empty($_SESSION['flash'])) return;
    foreach ($_SESSION['flash'] as $f) {
        echo '<div class="alert alert-' . e($f[0]) . '" data-autohide>' . e($f[1]) . '<button type="button" class="alert-x" aria-label="Tutup">&times;</button></div>';
    }
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
function admin_only()
{
    if (!is_admin()) {
        flash('danger', 'Halaman itu khusus admin.');
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
function waktu_tidak_tersedia($tanggal, $id_lapangan, $kecuali = 0)
{
    $hasil = [];
    $rows = db_all(
        "SELECT w2.id_waktu FROM waktu w2 WHERE EXISTS (
            SELECT 1 FROM booking b JOIN waktu w ON w.id_waktu = b.id_waktu
            WHERE b.tanggal_main = ? AND b.id_lapangan = ? AND b.status <> 'Cancel'
              AND b.id_booking <> ? AND w.jam_mulai < w2.jam_selesai AND w.jam_selesai > w2.jam_mulai)",
        [$tanggal, (int)$id_lapangan, (int)$kecuali]
    );
    foreach ($rows as $r) $hasil[(int)$r['id_waktu']] = 'penuh';
    if ($tanggal === date('Y-m-d')) {
        foreach (db_all("SELECT id_waktu FROM waktu WHERE jam_selesai <= ?", [date('H:i:s')]) as $r) {
            if (!isset($hasil[(int)$r['id_waktu']])) $hasil[(int)$r['id_waktu']] = 'lewat';
        }
    }
    return $hasil;
}

// Validasi input form booking. $cek_jadwal=false -> lewati cek tanggal/slot (dipakai saat jadwal tidak diubah)
// Return: [daftar_error, data_lapangan, data_waktu]
function booking_validasi($d, $kecuali = 0, $cek_jadwal = true)
{
    $err = [];
    $lap = null;
    $wkt = null;

    if (!tanggal_valid($d['tanggal_main'])) $err[] = 'Tanggal main tidak valid.';
    if (mb_strlen($d['nama_penyewa']) < 3) $err[] = 'Nama penyewa minimal 3 karakter.';
    if (mb_strlen($d['nama_penyewa']) > 100) $err[] = 'Nama penyewa terlalu panjang.';
    if ($d['no_hp'] !== '' && !preg_match('/^[0-9+\- ]{8,20}$/', $d['no_hp'])) $err[] = 'Nomor HP tidak valid.';
    if (mb_strlen($d['catatan']) > 255) $err[] = 'Catatan maksimal 255 karakter.';

    $lap = db_one("SELECT * FROM lapangan WHERE id_lapangan = ?", [(int)$d['id_lapangan']]);
    $wkt = db_one("SELECT * FROM waktu WHERE id_waktu = ?", [(int)$d['id_waktu']]);
    if (!$lap) $err[] = 'Pilih lapangan dulu.';
    if (!$wkt) $err[] = 'Pilih jam main dulu.';

    if ($cek_jadwal && !$err) {
        if ($lap['status'] !== 'Tersedia') $err[] = $lap['nama_lapangan'] . ' sedang perbaikan, pilih lapangan lain.';
        if ($d['tanggal_main'] < date('Y-m-d')) $err[] = 'Tanggal main tidak boleh sudah lewat.';
        if (!$err) {
            $tak = waktu_tidak_tersedia($d['tanggal_main'], $lap['id_lapangan'], $kecuali);
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
