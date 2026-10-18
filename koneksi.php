<?php
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'abc_futsal';
$db_port = 3306;

mysqli_report(MYSQLI_REPORT_OFF);
$koneksi = @mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

if (!$koneksi) {
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><title>Koneksi gagal</title>'
        . '<meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<style>body{font-family:system-ui,sans-serif;background:#eef1f9;display:grid;place-items:center;min-height:100vh;margin:0;color:#1d2340}'
        . '.b{background:#fff;padding:32px 36px;border-radius:20px;max-width:460px;box-shadow:0 10px 40px rgba(47,123,255,.15)}'
        . 'code{background:#eef1f9;padding:2px 8px;border-radius:8px}</style></head><body><div class="b">'
        . '<h2 style="margin-top:0">Database belum nyambung 😵</h2>'
        . '<p>Pastikan <b>MySQL</b> di XAMPP sudah <b>Start</b>, lalu import file <code>db_app_futsal.sql</code> lewat phpMyAdmin.</p>'
        . '<p>Kalau user/password MySQL kamu beda, ubah di file <code>koneksi.php</code>.</p></div></body></html>';
    exit;
}
mysqli_set_charset($koneksi, 'utf8mb4');
