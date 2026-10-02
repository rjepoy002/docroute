-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 30, 2024 at 02:34 AM
-- Server version: 10.1.37-MariaDB
-- PHP Version: 7.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `local_pal_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `dr_documents`
--

CREATE TABLE `dr_documents` (
  `id` int(11) NOT NULL,
  `id_track` varchar(15) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` varchar(120) NOT NULL,
  `author` int(11) NOT NULL,
  `datecreated` datetime NOT NULL,
  `status` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `dr_documents`
--

INSERT INTO `dr_documents` (`id`, `id_track`, `title`, `description`, `author`, `datecreated`, `status`) VALUES
(1, '202401-0001', 'Test Outgoing', 'Outgoing Test', 1, '2024-01-09 09:45:04', 'open'),
(2, '202401-0002', 'PRF - Personal Protective Equipment', 'Safety Shoes', 4, '2024-01-19 07:05:57', 'closed'),
(3, '202401-0003', 'Test lang', 'Test lang uli', 3, '2024-01-19 07:20:43', 'closed'),
(4, '202401-0004', 'DECEMBER 2023 GENSTAT', 'GENSTAT ', 3, '2024-01-19 08:17:22', 'open');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dr_documents`
--
ALTER TABLE `dr_documents`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dr_documents`
--
ALTER TABLE `dr_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
