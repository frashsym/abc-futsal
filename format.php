<?php
// ============================================================
//  format.php - HANYA fungsi kecil untuk menampilkan data.
//  (Tidak ada logika bisnis / query di sini.)
// ============================================================

// Amankan teks sebelum dicetak ke HTML (cegah XSS)
function e($teks)
{
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

// 150000 -> "Rp 150.000"
function rupiah($angka)
{
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

// "19:00:00" -> "19:00"
function jam($waktu)
{
    return substr((string)$waktu, 0, 5);
}

// "2026-11-14" -> "14 Nov 2026"
function tgl_indo($tanggal)
{
    if (!$tanggal) return '-';
    $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($tanggal);
    return date('d', $t) . ' ' . $bulan[(int)date('n', $t)] . ' ' . date('Y', $t);
}

function hari_indo($tanggal)
{
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    return $hari[(int)date('w', strtotime($tanggal))];
}

// Inisial untuk avatar: "Siti Aminah" -> "SA"
function inisial($nama)
{
    $kata = preg_split('/\s+/', trim((string)$nama));
    $hasil = strtoupper(mb_substr($kata[0], 0, 1));
    if (count($kata) > 1) $hasil .= strtoupper(mb_substr($kata[1], 0, 1));
    return $hasil;
}

// Label berwarna untuk status
function badge($teks)
{
    $warna = [
        'Booking' => 'primary',
        'DP' => 'warning',
        'Menunggu' => 'warning',
        'Selesai' => 'success',
        'Lunas' => 'success',
        'Tersedia' => 'success',
        'Aktif' => 'success',
        'Disetujui' => 'success',
        'Cancel'  => 'danger',
        'Perbaikan' => 'danger',
        'Nonaktif' => 'danger',
        'Ditolak' => 'danger',
        'admin'   => 'purple',
        'kasir' => 'lime',
        'WhatsApp' => 'success',
        'Offline' => 'secondary',
    ];
    $kelas = $warna[$teks] ?? 'secondary';
    return '<span class="badge badge-soft badge-' . $kelas . '">' . e($teks) . '</span>';
}
