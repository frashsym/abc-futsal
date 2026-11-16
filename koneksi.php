<?php
// ============================================================
//  koneksi.php  -  pengaturan aplikasi + koneksi database (PDO)
//  File ini di-include oleh semua halaman (lewat auth.php / login.php).
// ============================================================

// --- Pengaturan database (XAMPP default: user root, password kosong) ---
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'futsal';

// --- Pengaturan aplikasi ---
// Nama folder di htdocs. Kalau folder kamu ganti nama, ubah di sini juga.
define('BASE_URL', '/futsal/');
// Aturan DP: minimal berapa persen dari total harga
define('DP_MINIMAL_PERSEN', 30);

date_default_timezone_set('Asia/Jakarta');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // error SQL jadi exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // hasil query berupa array asosiatif
            PDO::ATTR_EMULATE_PREPARES   => false,                    // pakai prepared statement asli
        ]
    );
    $pdo->exec("SET time_zone = '+07:00'");   // samakan jam MySQL dengan WIB
} catch (PDOException $e) {
    http_response_code(500);
    echo '<h3>Database belum nyambung</h3>'
        . '<p>Pastikan MySQL di XAMPP sudah Start dan file <code>futsal.sql</code> sudah di-import.</p>'
        . '<p>Kalau user/password MySQL berbeda, ubah di <code>koneksi.php</code>.</p>';
    exit;
}
