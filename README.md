# Aplikasi Penyewaan Lapangan Futsal "ABC" — Versi 2 (Admin & Kasir)

PHP Native (prosedural) + MySQL (PDO) + Bootstrap 5 (CDN) + Vanilla JS.

## Akun Contoh
| Level | Username | Password |
|-------|----------|----------|
| Admin | admin    | admin123 |
| Kasir | kasir1   | kasir123 |
| Kasir | kasir2   | kasir123 |

## Alur Bisnis
1. Penyewa datang langsung / chat WhatsApp ke kasir.
2. Kasir buka **Cek Jadwal** → pilih slot hijau (kosong).
3. Kasir isi data penyewa + pembayaran (**DP** minimal 30% atau **Lunas**) → tersimpan.
4. Kasir cetak **Nota** (atau kirim rincian via tombol WhatsApp).
5. DP → kasir catat **Pelunasan** di halaman Detail Booking.
6. Setelah main & lunas → kasir klik **Tandai Selesai**.
7. Batal? Kasir **Ajukan Pembatalan** (+ usulan refund) → **Admin** menyetujui/menolak di menu *Pembatalan & Refund*. Refund dikembalikan manual.

## Hak Akses
| Fitur | Admin | Kasir |
|-------|:----:|:----:|
| Master lapangan, jam operasional, harga | ✔ | |
| Kelola user / kasir | ✔ | |
| Laporan transaksi + cetak | ✔ | |
| Setujui / tolak pembatalan & refund | ✔ | |
| Lihat daftar & detail booking, nota | ✔ | ✔ |
| Cek jadwal, input booking (offline/WA) | | ✔ |
| Catat pelunasan, tandai selesai | | ✔ |
| Ajukan pembatalan | | ✔ |
| Profile (ubah data & password) | ✔ | ✔ |

## Struktur Folder
```
futsal/
├─ koneksi.php          koneksi PDO + pengaturan (BASE_URL, DP minimal)
├─ auth.php             wajib login, cek level, cek token CSRF (di-include paling atas tiap halaman)
├─ format.php           fungsi kecil untuk TAMPILAN saja: e(), rupiah(), tgl_indo(), badge()
├─ login.php  logout.php  index.php (dashboard)  profile.php  profile_proses.php
├─ cek_jadwal.php       endpoint AJAX status jam (dipakai form booking)
├─ inc/                 header.php, sidebar.php, footer.php, hitung_slot.php
├─ admin/               lapangan*, waktu*, harga*, pengguna*, pembatalan*, laporan
├─ kasir/               jadwal, booking_form, booking_simpan, bayar_simpan,
│                       selesai_proses, pembatalan_ajukan, pembatalan
├─ booking/             daftar, detail, nota   (dipakai admin & kasir)
├─ css/ js/ images/
└─ futsal.sql
```
Pola tiap modul: `xxx.php` (daftar) → `xxx_form.php` (tambah/ubah) → `xxx_proses.php` (simpan/hapus).
Semua query SQL ditulis langsung di file yang memakainya (prepared statement `?`).

## Aturan Logika Penting
- **Anti double booking**: sebelum simpan, lapangan dikunci (`SELECT ... FOR UPDATE`) lalu jam dicek tabrakan dengan booking lain yang bukan Cancel.
- **Harga** disimpan di `tb_booking.total_harga` saat transaksi, jadi perubahan harga master tidak mengubah transaksi lama.
- **DP** minimal `DP_MINIMAL_PERSEN` (di `koneksi.php`) dan harus < total. Pembayaran tercatat per baris di `tb_pembayaran`.
- **Selesai** hanya bisa jika status bayar Lunas dan tanggal main sudah tiba.
- **Pembatalan**: jadwal tetap terkunci selama pengajuan menunggu admin. Disetujui → status Cancel, jam kembali kosong, refund tercatat (maks. sebesar uang yang sudah dibayar).
- **Pendapatan bersih** = total uang masuk − total refund.
- Data master/user yang sudah punya riwayat transaksi tidak bisa dihapus (user cukup dinonaktifkan).
