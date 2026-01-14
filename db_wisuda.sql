-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 14, 2026 at 04:12 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_wisuda`
--

-- --------------------------------------------------------

--
-- Table structure for table `barcode`
--

CREATE TABLE `barcode` (
  `id_barcode` int NOT NULL,
  `id_proses` int NOT NULL,
  `barcode_file` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `barcode`
--

INSERT INTO `barcode` (`id_barcode`, `id_proses`, `barcode_file`) VALUES
(1, 1, 'disini barcodenya');

-- --------------------------------------------------------

--
-- Table structure for table `detail_wisuda`
--

CREATE TABLE `detail_wisuda` (
  `id_detail` int NOT NULL,
  `id_proses` int NOT NULL,
  `id_barcode` int NOT NULL,
  `id_kursi` int NOT NULL,
  `id_petugas` int DEFAULT NULL,
  `status_kehadiran` enum('hadir','tidak hadir') DEFAULT 'tidak hadir',
  `timestamp_scan` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_wisuda`
--

INSERT INTO `detail_wisuda` (`id_detail`, `id_proses`, `id_barcode`, `id_kursi`, `id_petugas`, `status_kehadiran`, `timestamp_scan`) VALUES
(1, 1, 1, 1, 1, 'hadir', '2026-01-05 16:43:05'),
(2, 1, 1, 2, 1, 'hadir', '2026-01-05 16:43:05');

-- --------------------------------------------------------

--
-- Table structure for table `fakultas`
--

CREATE TABLE `fakultas` (
  `id_fakultas` int NOT NULL,
  `nama_fakultas` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `fakultas`
--

INSERT INTO `fakultas` (`id_fakultas`, `nama_fakultas`) VALUES
(1, 'Teknologi dan Informatika'),
(2, 'Ekonomi dan Bisnis'),
(3, 'Komunikasi dan Desain'),
(4, 'Psikologi');

-- --------------------------------------------------------

--
-- Table structure for table `hak_akses`
--

CREATE TABLE `hak_akses` (
  `id_akses` int NOT NULL,
  `nama_akses` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `hak_akses`
--

INSERT INTO `hak_akses` (`id_akses`, `nama_akses`) VALUES
(1, 'Mahasiswa'),
(2, 'Petugas');

-- --------------------------------------------------------

--
-- Table structure for table `kursi`
--

CREATE TABLE `kursi` (
  `id_kursi` int NOT NULL,
  `id_proses` int NOT NULL,
  `no_kursi` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kursi`
--

INSERT INTO `kursi` (`id_kursi`, `id_proses`, `no_kursi`) VALUES
(1, 1, 'IF-1FTI'),
(2, 1, 'MHS1-FTI');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id_mahasiswa` int NOT NULL,
  `id_prodi` int NOT NULL,
  `id_fakultas` int NOT NULL,
  `id_akses` int NOT NULL,
  `nim` varchar(20) NOT NULL,
  `nama_mahasiswa` varchar(50) NOT NULL,
  `sk_wisuda` longblob,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`id_mahasiswa`, `id_prodi`, `id_fakultas`, `id_akses`, `nim`, `nama_mahasiswa`, `sk_wisuda`, `password`) VALUES
(1, 1, 1, 1, '123111001', 'Alviano Diego Ozbar', NULL, '123');

-- --------------------------------------------------------

--
-- Table structure for table `pendamping`
--

CREATE TABLE `pendamping` (
  `id_pendamping` int NOT NULL,
  `id_mahasiswa` int NOT NULL,
  `nama_pendamping` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pendamping`
--

INSERT INTO `pendamping` (`id_pendamping`, `id_mahasiswa`, `nama_pendamping`) VALUES
(1, 1, 'Tukiyem');

-- --------------------------------------------------------

--
-- Table structure for table `petugas`
--

CREATE TABLE `petugas` (
  `id_petugas` int NOT NULL,
  `id_akses` int NOT NULL,
  `nama_petugas` varchar(30) NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `petugas`
--

INSERT INTO `petugas` (`id_petugas`, `id_akses`, `nama_petugas`, `password`) VALUES
(1, 2, 'Sahrul Markonah', '123');

-- --------------------------------------------------------

--
-- Table structure for table `prodi`
--

CREATE TABLE `prodi` (
  `id_prodi` int NOT NULL,
  `id_fakultas` int NOT NULL,
  `nama_prodi` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `prodi`
--

INSERT INTO `prodi` (`id_prodi`, `id_fakultas`, `nama_prodi`) VALUES
(1, 1, 'Informatika'),
(2, 1, 'Sistem Informasi'),
(3, 2, 'Manajemenn'),
(4, 2, 'Akuntansi'),
(5, 3, 'Ilmu Komunikasi'),
(6, 3, 'Desain Komunikasi Visual'),
(7, 4, 'Psikologi');

-- --------------------------------------------------------

--
-- Table structure for table `proses_wisuda`
--

CREATE TABLE `proses_wisuda` (
  `id_proses` int NOT NULL,
  `id_mahasiswa` int NOT NULL,
  `id_pendamping` int DEFAULT NULL,
  `id_petugas` int DEFAULT NULL,
  `status_proses` enum('proses','selesai') DEFAULT 'proses',
  `is_edited` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `proses_wisuda`
--

INSERT INTO `proses_wisuda` (`id_proses`, `id_mahasiswa`, `id_pendamping`, `id_petugas`, `status_proses`, `is_edited`) VALUES
(1, 1, 1, 1, 'selesai', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barcode`
--
ALTER TABLE `barcode`
  ADD PRIMARY KEY (`id_barcode`),
  ADD KEY `id_proses` (`id_proses`);

--
-- Indexes for table `detail_wisuda`
--
ALTER TABLE `detail_wisuda`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_proses` (`id_proses`),
  ADD KEY `id_barcode` (`id_barcode`),
  ADD KEY `id_kursi` (`id_kursi`),
  ADD KEY `id_petugas` (`id_petugas`);

--
-- Indexes for table `fakultas`
--
ALTER TABLE `fakultas`
  ADD PRIMARY KEY (`id_fakultas`);

--
-- Indexes for table `hak_akses`
--
ALTER TABLE `hak_akses`
  ADD PRIMARY KEY (`id_akses`);

--
-- Indexes for table `kursi`
--
ALTER TABLE `kursi`
  ADD PRIMARY KEY (`id_kursi`),
  ADD KEY `id_proses` (`id_proses`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id_mahasiswa`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD KEY `id_prodi` (`id_prodi`),
  ADD KEY `id_fakultas` (`id_fakultas`),
  ADD KEY `id_akses` (`id_akses`);

--
-- Indexes for table `pendamping`
--
ALTER TABLE `pendamping`
  ADD PRIMARY KEY (`id_pendamping`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`);

--
-- Indexes for table `petugas`
--
ALTER TABLE `petugas`
  ADD PRIMARY KEY (`id_petugas`),
  ADD KEY `id_akses` (`id_akses`);

--
-- Indexes for table `prodi`
--
ALTER TABLE `prodi`
  ADD PRIMARY KEY (`id_prodi`),
  ADD KEY `id_fakultas` (`id_fakultas`);

--
-- Indexes for table `proses_wisuda`
--
ALTER TABLE `proses_wisuda`
  ADD PRIMARY KEY (`id_proses`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`),
  ADD KEY `id_pendamping` (`id_pendamping`),
  ADD KEY `id_petugas` (`id_petugas`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barcode`
--
ALTER TABLE `barcode`
  MODIFY `id_barcode` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `detail_wisuda`
--
ALTER TABLE `detail_wisuda`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fakultas`
--
ALTER TABLE `fakultas`
  MODIFY `id_fakultas` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `hak_akses`
--
ALTER TABLE `hak_akses`
  MODIFY `id_akses` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kursi`
--
ALTER TABLE `kursi`
  MODIFY `id_kursi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id_mahasiswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `pendamping`
--
ALTER TABLE `pendamping`
  MODIFY `id_pendamping` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `petugas`
--
ALTER TABLE `petugas`
  MODIFY `id_petugas` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `prodi`
--
ALTER TABLE `prodi`
  MODIFY `id_prodi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `proses_wisuda`
--
ALTER TABLE `proses_wisuda`
  MODIFY `id_proses` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barcode`
--
ALTER TABLE `barcode`
  ADD CONSTRAINT `barcode_ibfk_1` FOREIGN KEY (`id_proses`) REFERENCES `proses_wisuda` (`id_proses`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `detail_wisuda`
--
ALTER TABLE `detail_wisuda`
  ADD CONSTRAINT `detail_wisuda_ibfk_1` FOREIGN KEY (`id_proses`) REFERENCES `proses_wisuda` (`id_proses`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_wisuda_ibfk_2` FOREIGN KEY (`id_barcode`) REFERENCES `barcode` (`id_barcode`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_wisuda_ibfk_3` FOREIGN KEY (`id_kursi`) REFERENCES `kursi` (`id_kursi`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detail_wisuda_ibfk_4` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `kursi`
--
ALTER TABLE `kursi`
  ADD CONSTRAINT `kursi_ibfk_1` FOREIGN KEY (`id_proses`) REFERENCES `proses_wisuda` (`id_proses`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`id_prodi`) REFERENCES `prodi` (`id_prodi`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `mahasiswa_ibfk_2` FOREIGN KEY (`id_fakultas`) REFERENCES `fakultas` (`id_fakultas`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `mahasiswa_ibfk_3` FOREIGN KEY (`id_akses`) REFERENCES `hak_akses` (`id_akses`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `pendamping`
--
ALTER TABLE `pendamping`
  ADD CONSTRAINT `pendamping_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `petugas`
--
ALTER TABLE `petugas`
  ADD CONSTRAINT `petugas_ibfk_1` FOREIGN KEY (`id_akses`) REFERENCES `hak_akses` (`id_akses`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `prodi`
--
ALTER TABLE `prodi`
  ADD CONSTRAINT `prodi_ibfk_1` FOREIGN KEY (`id_fakultas`) REFERENCES `fakultas` (`id_fakultas`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `proses_wisuda`
--
ALTER TABLE `proses_wisuda`
  ADD CONSTRAINT `proses_wisuda_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `proses_wisuda_ibfk_2` FOREIGN KEY (`id_pendamping`) REFERENCES `pendamping` (`id_pendamping`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `proses_wisuda_ibfk_3` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
