-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2026 at 02:15 PM
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
-- Database: `abc_futsal`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `id_booking` int(11) NOT NULL,
  `kode_booking` varchar(20) NOT NULL,
  `id_pengguna` int(11) NOT NULL,
  `id_lapangan` int(11) NOT NULL,
  `id_waktu` int(11) NOT NULL,
  `tanggal_main` date NOT NULL,
  `nama_penyewa` varchar(100) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `total_bayar` int(11) NOT NULL DEFAULT 0,
  `status` enum('Booking','Selesai','Cancel') NOT NULL DEFAULT 'Booking',
  `catatan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`id_booking`, `kode_booking`, `id_pengguna`, `id_lapangan`, `id_waktu`, `tanggal_main`, `nama_penyewa`, `no_hp`, `total_bayar`, `status`, `catatan`, `created_at`) VALUES
(1, 'BK-DEMO001', 2, 1, 7, '2026-10-18', 'Budi Santoso', '081200000002', 100000, 'Booking', 'Sparing tim kantor', '2026-10-18 12:14:25'),
(2, 'BK-DEMO002', 1, 2, 8, '2026-10-18', 'Dimas Pratama', '081311112222', 90000, 'Booking', NULL, '2026-10-18 12:14:25'),
(3, 'BK-DEMO003', 1, 3, 9, '2026-10-18', 'Tim FC Kampus', '081322223333', 120000, 'Booking', 'Bawa bola sendiri', '2026-10-18 12:14:25'),
(4, 'BK-DEMO004', 2, 1, 4, '2026-10-19', 'Budi Santoso', '081200000002', 100000, 'Booking', NULL, '2026-10-18 12:14:25'),
(5, 'BK-DEMO005', 1, 2, 6, '2026-10-17', 'Rizky Maulana', '081344445555', 90000, 'Selesai', NULL, '2026-10-18 12:14:25'),
(6, 'BK-DEMO006', 1, 1, 8, '2026-10-16', 'Anisa Putri', '081355556666', 100000, 'Selesai', NULL, '2026-10-18 12:14:25'),
(7, 'BK-DEMO007', 2, 3, 7, '2026-10-15', 'Budi Santoso', '081200000002', 120000, 'Cancel', 'Hujan deras', '2026-10-18 12:14:25'),
(8, 'BK-DEMO008', 1, 2, 9, '2026-10-13', 'Fajar Nugraha', '081366667777', 90000, 'Selesai', NULL, '2026-10-18 12:14:25');

-- --------------------------------------------------------

--
-- Table structure for table `lapangan`
--

CREATE TABLE `lapangan` (
  `id_lapangan` int(11) NOT NULL,
  `nama_lapangan` varchar(50) NOT NULL,
  `jenis_lantai` varchar(30) NOT NULL DEFAULT 'Vinyl',
  `harga_per_jam` int(11) NOT NULL DEFAULT 0,
  `status` enum('Tersedia','Perbaikan') NOT NULL DEFAULT 'Tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lapangan`
--

INSERT INTO `lapangan` (`id_lapangan`, `nama_lapangan`, `jenis_lantai`, `harga_per_jam`, `status`) VALUES
(1, 'Lapangan A', 'Vinyl', 100000, 'Tersedia'),
(2, 'Lapangan B', 'Sintetis', 90000, 'Tersedia'),
(3, 'Lapangan C', 'Parquet', 120000, 'Tersedia'),
(4, 'Lapangan D', 'Semen', 70000, 'Perbaikan');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `level` enum('admin','pelanggan') NOT NULL DEFAULT 'pelanggan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id_pengguna`, `nama_lengkap`, `username`, `password`, `no_hp`, `level`, `created_at`) VALUES
(1, 'Administrator', 'admin', '$2y$10$XdusE1DDycwCd6gjaX4zSeeyB853826Z99UekakcxtxckkD39Psc.', '081200000001', 'admin', '2026-10-18 12:14:25'),
(2, 'Budi Santoso', 'budi', '$2y$10$LUgIbvRDRnwL5co46S.3Q.ZsxdJ17tVCk98Z8WVomisc2GC397Msa', '081200000002', 'pelanggan', '2026-10-18 12:14:25');

-- --------------------------------------------------------

--
-- Table structure for table `waktu`
--

CREATE TABLE `waktu` (
  `id_waktu` int(11) NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `keterangan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `waktu`
--

INSERT INTO `waktu` (`id_waktu`, `jam_mulai`, `jam_selesai`, `keterangan`) VALUES
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
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id_booking`),
  ADD UNIQUE KEY `kode_booking` (`kode_booking`),
  ADD KEY `idx_jadwal` (`tanggal_main`,`id_lapangan`),
  ADD KEY `fk_b_pengguna` (`id_pengguna`),
  ADD KEY `fk_b_lapangan` (`id_lapangan`),
  ADD KEY `fk_b_waktu` (`id_waktu`);

--
-- Indexes for table `lapangan`
--
ALTER TABLE `lapangan`
  ADD PRIMARY KEY (`id_lapangan`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `waktu`
--
ALTER TABLE `waktu`
  ADD PRIMARY KEY (`id_waktu`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `id_booking` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `lapangan`
--
ALTER TABLE `lapangan`
  MODIFY `id_lapangan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `waktu`
--
ALTER TABLE `waktu`
  MODIFY `id_waktu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `fk_b_lapangan` FOREIGN KEY (`id_lapangan`) REFERENCES `lapangan` (`id_lapangan`),
  ADD CONSTRAINT `fk_b_pengguna` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`),
  ADD CONSTRAINT `fk_b_waktu` FOREIGN KEY (`id_waktu`) REFERENCES `waktu` (`id_waktu`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
