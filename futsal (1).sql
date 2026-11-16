-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 16, 2026 at 03:13 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `futsal`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_booking`
--

CREATE TABLE `tb_booking` (
  `id_booking` int(11) NOT NULL,
  `kode_booking` varchar(20) NOT NULL,
  `id_kasir` int(11) NOT NULL,
  `id_lapangan` int(11) NOT NULL,
  `id_waktu` int(11) NOT NULL,
  `tanggal_main` date NOT NULL,
  `nama_penyewa` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `sumber` enum('Offline','WhatsApp') NOT NULL DEFAULT 'Offline',
  `total_harga` int(11) NOT NULL DEFAULT 0,
  `jumlah_dibayar` int(11) NOT NULL DEFAULT 0,
  `jumlah_refund` int(11) NOT NULL DEFAULT 0,
  `status_bayar` enum('DP','Lunas') NOT NULL DEFAULT 'DP',
  `status` enum('Booking','Selesai','Cancel') NOT NULL DEFAULT 'Booking',
  `catatan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_booking`
--

INSERT INTO `tb_booking` (`id_booking`, `kode_booking`, `id_kasir`, `id_lapangan`, `id_waktu`, `tanggal_main`, `nama_penyewa`, `no_hp`, `sumber`, `total_harga`, `jumlah_dibayar`, `jumlah_refund`, `status_bayar`, `status`, `catatan`, `created_at`) VALUES
(1, 'BK-DEMO001', 2, 1, 7, '2026-11-14', 'Budi Santoso', '081211110001', 'WhatsApp', 120000, 50000, 0, 'DP', 'Booking', 'Sparing tim kantor', '2026-11-14 07:18:23'),
(2, 'BK-DEMO002', 2, 2, 8, '2026-11-14', 'Dimas Pratama', '081311112222', 'Offline', 110000, 110000, 0, 'Lunas', 'Booking', NULL, '2026-11-14 07:18:23'),
(3, 'BK-DEMO003', 3, 3, 4, '2026-11-14', 'Tim FC Kampus', '081322223333', 'WhatsApp', 120000, 120000, 0, 'Lunas', 'Booking', 'Bawa bola sendiri', '2026-11-14 07:18:23'),
(4, 'BK-DEMO004', 2, 1, 6, '2026-11-15', 'Anisa Putri', '081355556666', 'WhatsApp', 100000, 40000, 0, 'DP', 'Booking', NULL, '2026-11-14 07:18:23'),
(5, 'BK-DEMO005', 3, 2, 6, '2026-11-13', 'Rizky Maulana', '081344445555', 'Offline', 90000, 90000, 0, 'Lunas', 'Selesai', NULL, '2026-11-14 07:18:23'),
(6, 'BK-DEMO006', 2, 1, 8, '2026-11-12', 'Fajar Nugraha', '081366667777', 'WhatsApp', 120000, 120000, 0, 'Lunas', 'Selesai', NULL, '2026-11-14 07:18:23'),
(7, 'BK-DEMO007', 3, 3, 7, '2026-11-11', 'Hendra Wijaya', '081377778888', 'Offline', 140000, 60000, 30000, 'DP', 'Cancel', 'Hujan deras', '2026-11-14 07:18:23'),
(8, 'BK-DEMO008', 3, 2, 9, '2026-11-16', 'Tim Alumni SMA', '081388889999', 'WhatsApp', 110000, 40000, 40000, 'DP', 'Cancel', NULL, '2026-11-14 07:18:23'),
(9, 'BK-261114-0009', 4, 5, 8, '2026-11-14', 'Hasyim', '08888888888', 'Offline', 100000, 100000, 0, 'Lunas', 'Selesai', 'Main biasa', '2026-11-14 07:36:16'),
(10, 'BK-261114-0010', 4, 5, 9, '2026-11-14', 'qwe', '0898867683', 'WhatsApp', 100000, 50000, 50000, 'DP', 'Cancel', NULL, '2026-11-14 07:37:42'),
(11, 'BK-261114-0011', 4, 5, 9, '2026-11-14', 'hayaa', '0897554163', 'Offline', 100000, 100000, 0, 'Lunas', 'Selesai', NULL, '2026-11-14 07:40:19');

-- --------------------------------------------------------

--
-- Table structure for table `tb_harga`
--

CREATE TABLE `tb_harga` (
  `id_harga` int(11) NOT NULL,
  `id_lapangan` int(11) NOT NULL,
  `id_waktu` int(11) NOT NULL,
  `harga` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_harga`
--

INSERT INTO `tb_harga` (`id_harga`, `id_lapangan`, `id_waktu`, `harga`) VALUES
(1, 1, 1, 100000),
(2, 2, 1, 90000),
(3, 3, 1, 120000),
(4, 4, 1, 70000),
(5, 1, 2, 100000),
(6, 2, 2, 90000),
(7, 3, 2, 120000),
(8, 4, 2, 70000),
(9, 1, 3, 100000),
(10, 2, 3, 90000),
(11, 3, 3, 120000),
(12, 4, 3, 70000),
(13, 1, 4, 100000),
(14, 2, 4, 90000),
(15, 3, 4, 120000),
(16, 4, 4, 70000),
(17, 1, 5, 100000),
(18, 2, 5, 90000),
(19, 3, 5, 120000),
(20, 4, 5, 70000),
(21, 1, 6, 100000),
(22, 2, 6, 90000),
(23, 3, 6, 120000),
(24, 4, 6, 70000),
(25, 1, 7, 120000),
(26, 2, 7, 110000),
(27, 3, 7, 140000),
(28, 4, 7, 90000),
(29, 1, 8, 120000),
(30, 2, 8, 110000),
(31, 3, 8, 140000),
(32, 4, 8, 90000),
(33, 1, 9, 120000),
(34, 2, 9, 110000),
(35, 3, 9, 140000),
(36, 4, 9, 90000),
(37, 1, 10, 120000),
(38, 2, 10, 110000),
(39, 3, 10, 140000),
(40, 4, 10, 90000),
(65, 5, 1, 100000),
(66, 5, 2, 100000),
(67, 5, 3, 100000),
(68, 5, 4, 100000),
(69, 5, 5, 100000),
(70, 5, 6, 100000),
(71, 5, 7, 100000),
(72, 5, 8, 100000),
(73, 5, 9, 100000),
(74, 5, 10, 100000);

-- --------------------------------------------------------

--
-- Table structure for table `tb_lapangan`
--

CREATE TABLE `tb_lapangan` (
  `id_lapangan` int(11) NOT NULL,
  `nama_lapangan` varchar(50) NOT NULL,
  `jenis_lantai` varchar(30) NOT NULL DEFAULT 'Vinyl',
  `status` enum('Tersedia','Perbaikan') NOT NULL DEFAULT 'Tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_lapangan`
--

INSERT INTO `tb_lapangan` (`id_lapangan`, `nama_lapangan`, `jenis_lantai`, `status`) VALUES
(1, 'Lapangan A', 'Vinyl', 'Tersedia'),
(2, 'Lapangan B', 'Sintetis', 'Tersedia'),
(3, 'Lapangan C', 'Parquet', 'Tersedia'),
(4, 'Lapangan D', 'Semen', 'Perbaikan'),
(5, 'Bisa banget', 'Vinyl', 'Tersedia');

-- --------------------------------------------------------

--
-- Table structure for table `tb_pelanggan`
--

CREATE TABLE `tb_pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `alamat` varchar(100) NOT NULL,
  `jk` enum('Laki-laki','Perempuan') NOT NULL,
  `email` varchar(50) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `tanggal_bergabung` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_pelanggan`
--

INSERT INTO `tb_pelanggan` (`id_pelanggan`, `nama`, `no_hp`, `alamat`, `jk`, `email`, `tanggal_lahir`, `tanggal_bergabung`) VALUES
(4, 'Darrell', '081111111111', 'Perum Bagus', 'Laki-laki', 'darrell@gmail.com', '2026-11-01', '2026-11-16 09:02:00'),
(5, 'Sandya', '082222222222', 'Perum jelek', 'Perempuan', 'sandya@yahoo.com', '2026-11-02', '2026-11-16 09:02:00'),
(6, 'Mahes', '083333333333', 'Lobunta jalan rusak', 'Laki-laki', 'mahes@ugj.ac.id', '2026-11-03', '2026-11-16 09:03:00'),
(7, 'Wara', '08444444444', 'CL', 'Perempuan', 'wara@kampus.co.uk', '2026-11-04', '2026-11-16 09:04:00');

-- --------------------------------------------------------

--
-- Table structure for table `tb_pembatalan`
--

CREATE TABLE `tb_pembatalan` (
  `id_pembatalan` int(11) NOT NULL,
  `id_booking` int(11) NOT NULL,
  `id_kasir` int(11) NOT NULL,
  `alasan` varchar(255) NOT NULL,
  `refund_diajukan` int(11) NOT NULL DEFAULT 0,
  `refund_disetujui` int(11) NOT NULL DEFAULT 0,
  `status` enum('Menunggu','Disetujui','Ditolak') NOT NULL DEFAULT 'Menunggu',
  `id_admin` int(11) DEFAULT NULL,
  `catatan_admin` varchar(255) DEFAULT NULL,
  `tgl_pengajuan` datetime NOT NULL DEFAULT current_timestamp(),
  `tgl_proses` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_pembatalan`
--

INSERT INTO `tb_pembatalan` (`id_pembatalan`, `id_booking`, `id_kasir`, `alasan`, `refund_diajukan`, `refund_disetujui`, `status`, `id_admin`, `catatan_admin`, `tgl_pengajuan`, `tgl_proses`) VALUES
(1, 7, 3, 'Hujan deras, penyewa minta batal', 60000, 30000, 'Disetujui', 1, 'Refund 50% dari DP', '2026-11-14 14:18:24', '2026-11-14 14:18:24'),
(2, 8, 3, 'Tim belum cukup pemain', 40000, 40000, 'Disetujui', 1, NULL, '2026-11-14 14:18:24', '2026-11-14 14:38:44'),
(3, 10, 4, 'berhalangan', 50000, 50000, 'Disetujui', 1, NULL, '2026-11-14 14:38:02', '2026-11-14 14:38:41');

-- --------------------------------------------------------

--
-- Table structure for table `tb_pembayaran`
--

CREATE TABLE `tb_pembayaran` (
  `id_pembayaran` int(11) NOT NULL,
  `id_booking` int(11) NOT NULL,
  `id_kasir` int(11) NOT NULL,
  `tipe` enum('DP','Pelunasan','Lunas') NOT NULL,
  `jumlah` int(11) NOT NULL,
  `metode` enum('Tunai','Transfer') NOT NULL DEFAULT 'Tunai',
  `tanggal_bayar` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_pembayaran`
--

INSERT INTO `tb_pembayaran` (`id_pembayaran`, `id_booking`, `id_kasir`, `tipe`, `jumlah`, `metode`, `tanggal_bayar`) VALUES
(1, 1, 2, 'DP', 50000, 'Transfer', '2026-11-14 14:18:24'),
(2, 2, 2, 'Lunas', 110000, 'Tunai', '2026-11-14 14:18:24'),
(3, 3, 3, 'Lunas', 120000, 'Transfer', '2026-11-14 14:18:24'),
(4, 4, 2, 'DP', 40000, 'Transfer', '2026-11-14 14:18:24'),
(5, 5, 3, 'Lunas', 90000, 'Tunai', '2026-11-14 14:18:24'),
(6, 6, 2, 'Lunas', 120000, 'Transfer', '2026-11-14 14:18:24'),
(7, 7, 3, 'DP', 60000, 'Tunai', '2026-11-14 14:18:24'),
(8, 8, 3, 'DP', 40000, 'Transfer', '2026-11-14 14:18:24'),
(9, 9, 4, 'Lunas', 100000, 'Tunai', '2026-11-14 14:36:16'),
(10, 10, 4, 'DP', 50000, 'Tunai', '2026-11-14 14:37:42'),
(11, 11, 4, 'DP', 50000, 'Tunai', '2026-11-14 14:40:19'),
(12, 11, 4, 'Pelunasan', 50000, 'Tunai', '2026-11-14 14:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `tb_pengguna`
--

CREATE TABLE `tb_pengguna` (
  `id_pengguna` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `level` enum('admin','kasir') NOT NULL DEFAULT 'kasir',
  `status` enum('Aktif','Nonaktif') NOT NULL DEFAULT 'Aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_pengguna`
--

INSERT INTO `tb_pengguna` (`id_pengguna`, `nama_lengkap`, `username`, `password`, `no_hp`, `level`, `status`, `created_at`) VALUES
(1, 'Administrator', 'admin', '$2y$10$XdusE1DDycwCd6gjaX4zSeeyB853826Z99UekakcxtxckkD39Psc.', '081200000001', 'admin', 'Aktif', '2026-11-14 07:18:23'),
(2, 'Siti Aminah', 'kasir1', '$2b$12$A3cvGduBBe.Q/THruTh14OpicmKPJrZNmnILaCNF/eiTkbX/VKujG', '081200000002', 'kasir', 'Aktif', '2026-11-14 07:18:23'),
(3, 'Rudi Hartono', 'kasir2', '$2b$12$A3cvGduBBe.Q/THruTh14OpicmKPJrZNmnILaCNF/eiTkbX/VKujG', '081200000003', 'kasir', 'Aktif', '2026-11-14 07:18:23'),
(4, 'Farras', 'frash', '$2y$10$MmnK3uZhTCAyOphdQScKOO6H37SBubxDvDmhli/4Mz4YIb3K4zQdW', '08123456789', 'kasir', 'Aktif', '2026-11-14 07:26:45');

-- --------------------------------------------------------

--
-- Table structure for table `tb_waktu`
--

CREATE TABLE `tb_waktu` (
  `id_waktu` int(11) NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `keterangan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_waktu`
--

INSERT INTO `tb_waktu` (`id_waktu`, `jam_mulai`, `jam_selesai`, `keterangan`) VALUES
(1, '10:00:00', '11:00:00', 'Siang'),
(2, '11:00:00', '12:00:00', 'Siang'),
(3, '13:00:00', '14:00:00', 'Siang'),
(4, '15:00:00', '16:00:00', 'Sore'),
(5, '16:00:00', '17:00:00', 'Sore'),
(6, '17:00:00', '18:00:00', 'Sore'),
(7, '19:00:00', '20:00:00', 'Malam'),
(8, '20:00:00', '21:00:00', 'Malam'),
(9, '21:00:00', '22:00:00', 'Malam'),
(10, '22:00:00', '23:00:00', 'Malam');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_booking`
--
ALTER TABLE `tb_booking`
  ADD PRIMARY KEY (`id_booking`),
  ADD UNIQUE KEY `kode_booking` (`kode_booking`),
  ADD KEY `idx_jadwal` (`tanggal_main`,`id_lapangan`),
  ADD KEY `fk_b_kasir` (`id_kasir`),
  ADD KEY `fk_b_lapangan` (`id_lapangan`),
  ADD KEY `fk_b_waktu` (`id_waktu`);

--
-- Indexes for table `tb_harga`
--
ALTER TABLE `tb_harga`
  ADD PRIMARY KEY (`id_harga`),
  ADD UNIQUE KEY `uq_harga` (`id_lapangan`,`id_waktu`),
  ADD KEY `fk_h_waktu` (`id_waktu`);

--
-- Indexes for table `tb_lapangan`
--
ALTER TABLE `tb_lapangan`
  ADD PRIMARY KEY (`id_lapangan`);

--
-- Indexes for table `tb_pelanggan`
--
ALTER TABLE `tb_pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`);

--
-- Indexes for table `tb_pembatalan`
--
ALTER TABLE `tb_pembatalan`
  ADD PRIMARY KEY (`id_pembatalan`),
  ADD KEY `fk_c_booking` (`id_booking`),
  ADD KEY `fk_c_kasir` (`id_kasir`),
  ADD KEY `fk_c_admin` (`id_admin`);

--
-- Indexes for table `tb_pembayaran`
--
ALTER TABLE `tb_pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `fk_p_booking` (`id_booking`),
  ADD KEY `fk_p_kasir` (`id_kasir`);

--
-- Indexes for table `tb_pengguna`
--
ALTER TABLE `tb_pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `tb_waktu`
--
ALTER TABLE `tb_waktu`
  ADD PRIMARY KEY (`id_waktu`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_booking`
--
ALTER TABLE `tb_booking`
  MODIFY `id_booking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tb_harga`
--
ALTER TABLE `tb_harga`
  MODIFY `id_harga` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `tb_lapangan`
--
ALTER TABLE `tb_lapangan`
  MODIFY `id_lapangan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tb_pelanggan`
--
ALTER TABLE `tb_pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tb_pembatalan`
--
ALTER TABLE `tb_pembatalan`
  MODIFY `id_pembatalan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tb_pembayaran`
--
ALTER TABLE `tb_pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tb_pengguna`
--
ALTER TABLE `tb_pengguna`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tb_waktu`
--
ALTER TABLE `tb_waktu`
  MODIFY `id_waktu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_booking`
--
ALTER TABLE `tb_booking`
  ADD CONSTRAINT `fk_b_kasir` FOREIGN KEY (`id_kasir`) REFERENCES `tb_pengguna` (`id_pengguna`),
  ADD CONSTRAINT `fk_b_lapangan` FOREIGN KEY (`id_lapangan`) REFERENCES `tb_lapangan` (`id_lapangan`),
  ADD CONSTRAINT `fk_b_waktu` FOREIGN KEY (`id_waktu`) REFERENCES `tb_waktu` (`id_waktu`);

--
-- Constraints for table `tb_harga`
--
ALTER TABLE `tb_harga`
  ADD CONSTRAINT `fk_h_lapangan` FOREIGN KEY (`id_lapangan`) REFERENCES `tb_lapangan` (`id_lapangan`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_h_waktu` FOREIGN KEY (`id_waktu`) REFERENCES `tb_waktu` (`id_waktu`) ON DELETE CASCADE;

--
-- Constraints for table `tb_pembatalan`
--
ALTER TABLE `tb_pembatalan`
  ADD CONSTRAINT `fk_c_admin` FOREIGN KEY (`id_admin`) REFERENCES `tb_pengguna` (`id_pengguna`),
  ADD CONSTRAINT `fk_c_booking` FOREIGN KEY (`id_booking`) REFERENCES `tb_booking` (`id_booking`),
  ADD CONSTRAINT `fk_c_kasir` FOREIGN KEY (`id_kasir`) REFERENCES `tb_pengguna` (`id_pengguna`);

--
-- Constraints for table `tb_pembayaran`
--
ALTER TABLE `tb_pembayaran`
  ADD CONSTRAINT `fk_p_booking` FOREIGN KEY (`id_booking`) REFERENCES `tb_booking` (`id_booking`),
  ADD CONSTRAINT `fk_p_kasir` FOREIGN KEY (`id_kasir`) REFERENCES `tb_pengguna` (`id_pengguna`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
