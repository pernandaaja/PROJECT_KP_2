-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 30, 2025 at 01:19 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_absensi`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_pulang` time DEFAULT NULL,
  `status` enum('hadir','izin','sakit','tidak hadir','pulang','pending','ditolak') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `jenis_absen` enum('masuk','pulang','izin','sakit') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `bukti` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `admin_verifikasi_masuk` tinyint(1) DEFAULT NULL,
  `catatan_masuk` text,
  `admin_verifikasi_pulang` tinyint(1) DEFAULT NULL,
  `catatan_pulang` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `catatan_admin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `status_keterlambatan` tinyint(1) DEFAULT '0',
  `status_verifikasi` tinyint DEFAULT '0' COMMENT '0=menunggu, 1=disetujui, 2=ditolak',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `admin_verifikasi` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id`, `user_id`, `tanggal`, `jam_masuk`, `jam_pulang`, `status`, `jenis_absen`, `bukti`, `admin_verifikasi_masuk`, `catatan_masuk`, `admin_verifikasi_pulang`, `catatan_pulang`, `catatan_admin`, `status_keterlambatan`, `status_verifikasi`, `created_at`, `updated_at`, `admin_verifikasi`) VALUES
(2, 3, '2025-06-17', NULL, NULL, 'tidak hadir', 'masuk', NULL, NULL, NULL, NULL, NULL, '', 0, 0, '2025-06-17 15:58:20', '2025-06-17 15:58:20', 1),
(3, 4, '2025-06-17', NULL, NULL, 'tidak hadir', 'masuk', NULL, NULL, NULL, NULL, NULL, '', 0, 0, '2025-06-17 15:58:20', '2025-06-17 15:58:20', 1),
(4, 5, '2025-06-17', NULL, NULL, 'tidak hadir', 'masuk', NULL, NULL, NULL, NULL, NULL, '', 0, 0, '2025-06-17 15:58:20', '2025-06-17 15:58:20', 1),
(15, 3, '2025-06-18', NULL, NULL, 'izin', 'izin', '20250618_185022_izin_6852a7fe5f5c5.jpg', NULL, NULL, NULL, NULL, '', 0, 0, '2025-06-18 11:50:22', '2025-06-18 11:50:22', NULL),
(45, 2, '2025-06-19', '18:21:00', '18:21:08', 'hadir', 'masuk', NULL, NULL, 'macet', NULL, '', NULL, 1, 0, '2025-06-19 11:21:00', '2025-06-19 11:21:08', NULL),
(46, 4, '2025-06-19', '18:22:03', '18:22:21', 'hadir', 'masuk', NULL, NULL, 'maaf', NULL, 'ngantuk', NULL, 1, 0, '2025-06-19 11:22:03', '2025-06-19 11:22:21', NULL),
(47, 3, '2025-06-19', '19:18:04', '19:18:14', 'hadir', 'masuk', NULL, NULL, 'kesiangan', NULL, '', NULL, 1, 0, '2025-06-19 12:18:04', '2025-06-19 12:18:14', NULL),
(48, 32, '2025-06-19', '21:06:17', '21:08:08', 'hadir', 'masuk', NULL, NULL, 'kesiangan', NULL, 'capek', NULL, 1, 0, '2025-06-19 14:06:17', '2025-06-19 14:08:08', NULL),
(49, 31, '2025-06-19', NULL, NULL, 'tidak hadir', 'masuk', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-06-19 14:09:19', '2025-06-19 14:09:19', 1),
(50, 2, '2025-06-20', '06:52:05', '06:52:23', 'hadir', 'masuk', NULL, NULL, '', NULL, 'acara keluarga', NULL, 0, 0, '2025-06-19 23:52:05', '2025-06-19 23:52:23', NULL),
(51, 3, '2025-06-20', NULL, NULL, 'izin', 'izin', '20250620_075921_izin_6854b2695f064.jpeg', NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-06-20 00:59:21', '2025-06-20 00:59:21', NULL),
(52, 2, '2025-06-24', NULL, NULL, 'sakit', 'sakit', '20250624_204604_sakit_685aac1c7a473.png', NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-06-24 13:46:04', '2025-06-24 13:46:04', NULL),
(53, 2, '2025-07-09', '19:17:04', '19:17:45', 'hadir', 'masuk', NULL, NULL, 'ketiduran', NULL, '', NULL, 1, 0, '2025-07-09 12:17:04', '2025-07-09 12:17:45', NULL),
(54, 2, '2025-07-24', '13:29:58', '13:30:12', 'hadir', 'masuk', NULL, NULL, 'males', NULL, 'gabut', NULL, 1, 0, '2025-07-24 06:29:58', '2025-07-24 06:30:12', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `divisi`
--

CREATE TABLE `divisi` (
  `id` int NOT NULL,
  `nama_divisi` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `divisi`
--

INSERT INTO `divisi` (`id`, `nama_divisi`) VALUES
(1, 'IT'),
(2, 'Keuangan'),
(3, 'Marketing'),
(4, 'Produksi');

-- --------------------------------------------------------

--
-- Table structure for table `jam_kerja`
--

CREATE TABLE `jam_kerja` (
  `id` int NOT NULL,
  `nama_shift` varchar(50) NOT NULL,
  `jam_masuk` time NOT NULL,
  `jam_pulang` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jam_kerja`
--

INSERT INTO `jam_kerja` (`id`, `nama_shift`, `jam_masuk`, `jam_pulang`) VALUES
(1, 'Shift 1', '08:00:00', '17:00:00'),
(2, 'Shift 2', '18:00:00', '23:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nik` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `divisi_id` int DEFAULT NULL,
  `role` enum('admin','karyawan') NOT NULL,
  `jam_kerja_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nik`, `nama`, `email`, `password`, `divisi_id`, `role`, `jam_kerja_id`) VALUES
(1, 'ADM001', 'Admin', 'admin@gmail.com', '12345', NULL, 'admin', NULL),
(2, 'KRY001', 'Fernanda', 'nanda@gmail.com', '12345', 1, 'karyawan', 1),
(3, 'KRY002', 'Damar', 'damar@gmail.com', '11111', 2, 'karyawan', 1),
(4, 'KRY003', 'Bagas', 'bagas@gmail.com', '22222', 3, 'karyawan', 2),
(5, 'KRY004', 'Rahmanda', 'rahmanda@gmail.com', '33333', 4, 'karyawan', 1),
(31, 'KRY005', 'Dimas', 'dimas@gmail.com', '55555', 2, 'karyawan', 1),
(32, 'KRY000', 'Fahri', 'fahri@gmail.com', '000000', 1, 'karyawan', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_verifikasi` (`admin_verifikasi`);

--
-- Indexes for table `divisi`
--
ALTER TABLE `divisi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jam_kerja`
--
ALTER TABLE `jam_kerja`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `users_ibfk_2` (`jam_kerja_id`),
  ADD KEY `fk_users_divisi` (`divisi_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `divisi`
--
ALTER TABLE `divisi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `jam_kerja`
--
ALTER TABLE `jam_kerja`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `absensi_ibfk_2` FOREIGN KEY (`admin_verifikasi`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_divisi` FOREIGN KEY (`divisi_id`) REFERENCES `divisi` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`jam_kerja_id`) REFERENCES `jam_kerja` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
