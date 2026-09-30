-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 28, 2025 at 08:33 AM
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
-- Database: `arsip_berkas`
--

-- --------------------------------------------------------

--
-- Table structure for table `disposisi_keluar`
--

CREATE TABLE `disposisi_keluar` (
  `id` int NOT NULL,
  `no` int DEFAULT NULL,
  `kode` varchar(50) DEFAULT NULL,
  `kategori_id` int DEFAULT '23',
  `tanggal` date DEFAULT NULL,
  `nomor_surat` varchar(100) DEFAULT NULL,
  `perihal` text,
  `ke` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `disposisi_keluar`
--

INSERT INTO `disposisi_keluar` (`id`, `no`, `kode`, `kategori_id`, `tanggal`, `nomor_surat`, `perihal`, `ke`, `file_path`) VALUES
(514, 1, '019', 19, '2025-02-19', '1112', 'Kerja Sama', 'Asuransi Anda', '\\\\172.16.34.5\\ftp\\DISPOSISI SURAT\\67c10864cb8d5.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `disposisi_surat`
--

CREATE TABLE `disposisi_surat` (
  `id` int NOT NULL,
  `no` int DEFAULT NULL,
  `kode` varchar(50) DEFAULT NULL,
  `kategori_id` int DEFAULT '23',
  `tanggal_surat` date DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `nomer_surat` varchar(100) DEFAULT NULL,
  `dari` varchar(255) DEFAULT NULL,
  `perihal` text,
  `instruksi` text,
  `instruksi_direksi_umum` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `instruksi_direksi_bisnis` text,
  `instruksi_direksi_kepatuhan` text,
  `diteruskan` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` text,
  `kabag_opinion` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `disposisi_surat`
--

INSERT INTO `disposisi_surat` (`id`, `no`, `kode`, `kategori_id`, `tanggal_surat`, `tanggal_masuk`, `nomer_surat`, `dari`, `perihal`, `instruksi`, `instruksi_direksi_umum`, `instruksi_direksi_bisnis`, `instruksi_direksi_kepatuhan`, `diteruskan`, `file_path`, `status`, `kabag_opinion`) VALUES
(535, 13, '001', 1, '2025-02-04', '2025-02-11', '1112', 'BI', 'Laporan Keuangan', 'acc', 'acc', '', '', 'kabag_operasional', '\\\\172.16.34.5\\ftp\\DISPOSISI SURAT\\67bfbc3ec1c16.pdf', '[\"kabag_operasional\"]', NULL),
(536, 14, '001', 1, '2025-02-05', '2025-02-14', '1111', 'Bank Indonesia', 'Laporan Keuangan', 'acc', 'acc', 'acc', 'acc', 'kabag_satker_kepatuhan,kabag_administrasi_umum,kabag_operasional', NULL, '[\"kabag_satker_kepatuhan\",\"kabag_administrasi_umum\",\"kabag_operasional\"]', NULL),
(537, 15, '019', 19, '2025-02-05', '2025-02-11', '11144', 'Asuransi Anda', 'Laporan', 'acc', 'acc', 'acc', 'acc', 'kabag_marketing', '\\\\172.16.34.5\\ftp\\DISPOSISI SURAT\\67c1100ae0877.pdf', '[\"kabag_marketing\"]', NULL),
(538, 16, '007', 7, '2025-02-11', '2025-02-17', '1121', 'Kecamatan Pengasih', 'Kerja Sama', 'acc', 'acc', 'acc', 'acc', 'kabag_satker_kepatuhan,kabag_administrasi_umum', '\\\\172.16.34.5\\ftp\\DISPOSISI SURAT\\67c11027250fa.pdf', '[\"kabag_satker_kepatuhan\",\"kabag_administrasi_umum\"]', NULL),
(539, 17, '010', 10, '2025-02-25', '2025-02-27', '2', 'BMPD', 'BMPDDD', '', '', '', '', NULL, '\\\\172.16.34.5\\ftp\\DISPOSISI SURAT\\67c150fe45e90.png', 'Belum Diterima', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kategori_surat`
--

CREATE TABLE `kategori_surat` (
  `id_kategori` int NOT NULL,
  `nama_kategori` varchar(255) NOT NULL,
  `kode_kategori` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori_surat`
--

INSERT INTO `kategori_surat` (`id_kategori`, `nama_kategori`, `kode_kategori`) VALUES
(1, 'BANK INDONESIA', '001'),
(2, 'OJK', '002'),
(3, 'DEWAN PENGAWAS', '003'),
(4, 'PEMERINTAH PROPINSI', '004'),
(5, 'PEMERINTAH DAERAH', '005'),
(6, 'INSTANSI VERTIKAL', '006'),
(7, 'KECAMATAN dan DESA', '007'),
(8, 'PERBARINDO', '008'),
(9, 'PERBAMIDA', '009'),
(10, 'BMPD', '010'),
(11, 'BANK UMUM', '011'),
(12, 'BPR', '012'),
(13, 'LKM', '013'),
(14, 'KOPERASI BINANGUN PRIMA', '014'),
(15, 'LPS', '015'),
(16, 'CERTIF', '016'),
(17, 'PAJAK', '017'),
(18, 'SGS', '018'),
(19, 'ASURANSI', '019'),
(20, 'DAPEN', '020'),
(21, 'SURAT NASABAH', '021'),
(22, 'SP BAPAS KP', '022'),
(23, 'LAIN-LAIN', '023');

-- --------------------------------------------------------

--
-- Table structure for table `kpp_table`
--

CREATE TABLE `kpp_table` (
  `id` int NOT NULL,
  `nomor_kpp` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `judul_kpp` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `tanggal_ditetapkan` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Berlaku',
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kpp_table`
--

INSERT INTO `kpp_table` (`id`, `nomor_kpp`, `judul_kpp`, `tanggal_ditetapkan`, `status`, `file_path`, `created_at`) VALUES
(7, '11121', 'KPP XYZ', '2025-02-28', 'Berlaku', '\\\\172.16.34.5\\ftp\\KPP\\KPP XYZ.pdf', '2025-02-28 07:07:40');

-- --------------------------------------------------------

--
-- Table structure for table `ped_table`
--

CREATE TABLE `ped_table` (
  `id` int NOT NULL,
  `nomor_ped` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `judul_ped` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `tanggal_ditetapkan` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Berlaku',
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ped_table`
--

INSERT INTO `ped_table` (`id`, `nomor_ped`, `judul_ped`, `tanggal_ditetapkan`, `status`, `file_path`, `created_at`) VALUES
(1, '1111', 'tes', '2025-02-03', 'Tidak Berlaku', '\\\\172.16.34.5\\ftp\\PED\\tes.pdf', '2025-02-27 04:29:07'),
(3, '190005', 'Peraturan Direksi AA', '2025-02-28', 'Berlaku', '\\\\172.16.34.5\\ftp\\PED\\Peraturan Direksi AA.pdf', '2025-02-28 02:01:48');

-- --------------------------------------------------------

--
-- Table structure for table `pep_table`
--

CREATE TABLE `pep_table` (
  `id` int NOT NULL,
  `nomor_pep` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `judul_pep` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `tanggal_ditetapkan` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Berlaku',
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pep_table`
--

INSERT INTO `pep_table` (`id`, `nomor_pep`, `judul_pep`, `tanggal_ditetapkan`, `status`, `file_path`, `created_at`) VALUES
(1, '11121', 'PEP KP', '2025-02-27', 'Tidak Berlaku', '\\\\172.16.34.5\\ftp\\PEP\\PEP KP.pdf', '2025-02-27 04:27:19'),
(2, '11121', 'PEP Sya', '2025-02-20', 'Tidak Berlaku', '\\\\172.16.34.5\\ftp\\PEP\\PEP Sya.pdf', '2025-02-28 07:14:51');

-- --------------------------------------------------------

--
-- Table structure for table `se_table`
--

CREATE TABLE `se_table` (
  `id` int NOT NULL,
  `nomor_se` varchar(50) NOT NULL,
  `kategori` varchar(255) NOT NULL,
  `judul_se` text NOT NULL,
  `tanggal_ditetapkan` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Berlaku',
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `se_table`
--

INSERT INTO `se_table` (`id`, `nomor_se`, `kategori`, `judul_se`, `tanggal_ditetapkan`, `status`, `file_path`, `created_at`) VALUES
(5, '11121', 'Lain-lain', 'SE Bupati KP', '2025-02-04', 'Berlaku', '\\\\172.16.34.5\\ftp\\SE\\SE Bupati KP.pdf', '2025-02-28 00:49:29');

-- --------------------------------------------------------

--
-- Table structure for table `sk_table`
--

CREATE TABLE `sk_table` (
  `id` int NOT NULL,
  `nomor_sk` varchar(50) NOT NULL,
  `kategori` varchar(255) NOT NULL,
  `judul_sk` text NOT NULL,
  `tanggal_ditetapkan` date DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'Berlaku',
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sk_table`
--

INSERT INTO `sk_table` (`id`, `nomor_sk`, `kategori`, `judul_sk`, `tanggal_ditetapkan`, `status`, `file_path`, `created_at`) VALUES
(21, '1111', 'Lain-lain', 'SK Penetapan Hari Libur', '2025-02-18', 'Tidak Berlaku', '\\\\172.16.34.5\\ftp\\SK\\SK Penetapan Hari Libur.pdf', '2025-02-25 06:45:19'),
(22, '1111123', 'Lain-lain', 'SK Gubernur', '2025-02-11', 'Berlaku', '\\\\172.16.34.5\\ftp\\SK\\SK Gubernur.pdf', '2025-02-25 06:54:50'),
(26, '1111', 'Lain-lain', 'SK Peraturan', '2025-02-28', 'Tidak Berlaku', '\\\\172.16.34.5\\ftp\\SK\\SK Peraturan.pdf', '2025-02-28 06:52:20'),
(27, '1121', 'Produk Tabungan', 'SK Tabungan', '2025-02-28', 'Berlaku', '\\\\172.16.34.5\\ftp\\SK\\SK Tabungan.pdf', '2025-02-28 07:15:45');

-- --------------------------------------------------------

--
-- Table structure for table `sop_table`
--

CREATE TABLE `sop_table` (
  `id` int NOT NULL,
  `nomor_sop` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `judul_sop` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `kategori` varchar(255) NOT NULL,
  `tanggal_ditetapkan` date NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'Berlaku',
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sop_table`
--

INSERT INTO `sop_table` (`id`, `nomor_sop`, `judul_sop`, `kategori`, `tanggal_ditetapkan`, `status`, `file_path`, `created_at`) VALUES
(26, '1112145', 'SOP Pembukaan Rekening', 'Produk Tabungan', '2025-02-27', 'Berlaku', '\\\\172.16.34.5\\ftp\\SOP\\SOP Pembukaan Rekening.pdf', '2025-02-28 02:01:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('direksi','adminkredit','marketing','ti_admin','teller','kredit','sekre','admin_dok','direksi_umum','direksi_bisnis','direksi_kepatuhan','kabag') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(3, 'marketing', '$2y$10$QaDvRmzqMU5c0iO5F3x/X.rmsBm44pkPbJlUcNmoc9c0cWajIUbyi', 'marketing'),
(4, 'adminti', '$2y$10$JVkL2pV/ml9OggMJ81rfHOQZbeYv6xMBU09ME8DhKHMdcKtRuXCgq', 'ti_admin'),
(9, 'teller', '$2y$10$PxeXOf6jDos/55SbQG.i9u78z24EIlaEIzGE87/N6pUiaue7wCj.2', 'teller'),
(12, 'adminkredit', '$2y$10$o8Pg.n6mq6WiU9OGnhe0V.Pc2oYt5uOv4ZOOCaZCpRp0SjlZR0Ilu', 'adminkredit'),
(16, 'admindok', '$2y$10$jihe0S4LHTM7fzwPaQC8ieXoDsIDsGVB/3XUudHspZXyFTonbIk3G', 'admin_dok'),
(28, 'sekre', '$2y$10$8LIfkZOPmop7J3eoLO3CIeLlO7qyuTWpYRS/ES9DMVJoeWJybqBQ2', 'sekre'),
(32, 'direksi', '$2y$10$C8JnEMBiNlMWiha7khozDOmBqCfoZ4vdmtUvuZh6LmuWNVLopGqJS', 'direksi'),
(33, 'kabag', '$2y$10$eoao6bdOFpOaihK2q1f4Du2vQanand7czEtTywTFhccYbskeSF0R2', 'kabag');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `disposisi_keluar`
--
ALTER TABLE `disposisi_keluar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `disposisi_surat`
--
ALTER TABLE `disposisi_surat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori_surat`
--
ALTER TABLE `kategori_surat`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `kode_kategori` (`kode_kategori`);

--
-- Indexes for table `kpp_table`
--
ALTER TABLE `kpp_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ped_table`
--
ALTER TABLE `ped_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pep_table`
--
ALTER TABLE `pep_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `se_table`
--
ALTER TABLE `se_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sk_table`
--
ALTER TABLE `sk_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sop_table`
--
ALTER TABLE `sop_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `disposisi_keluar`
--
ALTER TABLE `disposisi_keluar`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=515;

--
-- AUTO_INCREMENT for table `disposisi_surat`
--
ALTER TABLE `disposisi_surat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=540;

--
-- AUTO_INCREMENT for table `kategori_surat`
--
ALTER TABLE `kategori_surat`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `kpp_table`
--
ALTER TABLE `kpp_table`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ped_table`
--
ALTER TABLE `ped_table`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pep_table`
--
ALTER TABLE `pep_table`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `se_table`
--
ALTER TABLE `se_table`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sk_table`
--
ALTER TABLE `sk_table`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `sop_table`
--
ALTER TABLE `sop_table`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
