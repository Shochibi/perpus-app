-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 01, 2026 at 02:42 AM
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
-- Database: `db_lib`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_buku`
--

CREATE TABLE `tbl_buku` (
  `id_buku` int(11) NOT NULL,
  `judul_buku` varchar(200) NOT NULL,
  `penulis_buku` varchar(100) NOT NULL,
  `penerbit_buku` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `tahun_terbit` year(4) DEFAULT NULL,
  `cover_buku` varchar(255) DEFAULT NULL,
  `pdf_buku` varchar(255) DEFAULT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `id_kategori` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Table structure for table `tbl_detail_peminjaman`
--

CREATE TABLE `tbl_detail_peminjaman` (
  `id_detail` int(11) NOT NULL,
  `id_peminjaman` int(11) NOT NULL,
  `id_buku` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Table structure for table `tbl_kategori`
--

CREATE TABLE `tbl_kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_kategori`
--

INSERT INTO `tbl_kategori` (`id_kategori`, `nama_kategori`) VALUES
(4, 'Bahasa'),
(1, 'Fiksi'),
(3, 'Pelajaran'),
(2, 'Teknologi'),
(5, 'Umum');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_peminjaman`
--

CREATE TABLE `tbl_peminjaman` (
  `id_peminjaman` int(11) NOT NULL,
  `id_anggota` int(11) NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_tenggat` date NOT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `status` enum('dipinjam','selesai') NOT NULL DEFAULT 'dipinjam'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id_user` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `tanggal_daftar` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`id_user`, `fullname`, `username`, `password`, `role`, `tanggal_daftar`) VALUES
(1, 'admin', 'admin', '$2y$10$qz31LWl9LPhVIrkhpAPRW.oZJ/saT0OKKedmkQfaLf3FRdBIz9O0i', 'admin', '2026-08-11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_buku`
--
ALTER TABLE `tbl_buku`
  ADD PRIMARY KEY (`id_buku`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `tbl_detail_peminjaman`
--
ALTER TABLE `tbl_detail_peminjaman`
  ADD PRIMARY KEY (`id_detail`),
  ADD UNIQUE KEY `uk_peminjaman_buku` (`id_peminjaman`,`id_buku`),
  ADD KEY `id_buku` (`id_buku`);

--
-- Indexes for table `tbl_kategori`
--
ALTER TABLE `tbl_kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `nama_kategori` (`nama_kategori`);

--
-- Indexes for table `tbl_peminjaman`
--
ALTER TABLE `tbl_peminjaman`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `id_anggota` (`id_anggota`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_buku`
--
ALTER TABLE `tbl_buku`
  MODIFY `id_buku` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_detail_peminjaman`
--
ALTER TABLE `tbl_detail_peminjaman`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_kategori`
--
ALTER TABLE `tbl_kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_peminjaman`
--
ALTER TABLE `tbl_peminjaman`
  MODIFY `id_peminjaman` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_buku`
--
ALTER TABLE `tbl_buku`
  ADD CONSTRAINT `tbl_buku_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `tbl_kategori` (`id_kategori`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_detail_peminjaman`
--
ALTER TABLE `tbl_detail_peminjaman`
  ADD CONSTRAINT `tbl_detail_peminjaman_ibfk_1` FOREIGN KEY (`id_peminjaman`) REFERENCES `tbl_peminjaman` (`id_peminjaman`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tbl_detail_peminjaman_ibfk_2` FOREIGN KEY (`id_buku`) REFERENCES `tbl_buku` (`id_buku`) ON UPDATE CASCADE;

--
-- Constraints for table `tbl_peminjaman`
--
ALTER TABLE `tbl_peminjaman`
  ADD CONSTRAINT `tbl_peminjaman_ibfk_1` FOREIGN KEY (`id_anggota`) REFERENCES `tbl_user` (`id_user`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
