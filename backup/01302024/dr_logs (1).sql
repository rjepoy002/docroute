-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 30, 2024 at 02:35 AM
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
-- Table structure for table `dr_logs`
--

CREATE TABLE `dr_logs` (
  `id` int(11) NOT NULL,
  `id_track` varchar(15) NOT NULL,
  `sender` int(11) NOT NULL,
  `receiver` int(11) NOT NULL,
  `status` varchar(25) NOT NULL,
  `date` datetime NOT NULL,
  `remarks` varchar(225) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `dr_logs`
--

INSERT INTO `dr_logs` (`id`, `id_track`, `sender`, `receiver`, `status`, `date`, `remarks`) VALUES
(1, '202401-0001', 1, 2, 'Pending', '2024-01-09 09:45:04', 'for testing purposes only'),
(2, '202401-0001', 1, 2, 'Received', '2024-01-10 02:31:55', NULL),
(3, '202401-0002', 4, 3, 'Pending', '2024-01-19 07:05:57', 'testing'),
(4, '202401-0002', 4, 3, 'Received', '2024-01-19 07:07:15', NULL),
(5, '202401-0002', 3, 1, 'Pending', '2024-01-19 07:08:35', 'Test'),
(6, '202401-0002', 1, 3, 'Declined', '2024-01-19 07:10:27', 'trip lang'),
(7, '202401-0003', 3, 1, 'Pending', '2024-01-19 07:20:43', 'Testiiiing'),
(8, '202401-0003', 3, 1, 'Received', '2024-01-19 07:20:59', NULL),
(9, '202401-0003', 1, 3, 'Pending', '2024-01-19 07:22:03', 'mali'),
(10, '202401-0003', 1, 3, 'Received', '2024-01-19 07:22:21', NULL),
(11, '202401-0004', 3, 7, 'Pending', '2024-01-19 08:17:22', 'TEST'),
(12, '202401-0001', 2, 9, 'Pending', '2024-01-26 14:05:13', 'test forward');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dr_logs`
--
ALTER TABLE `dr_logs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dr_logs`
--
ALTER TABLE `dr_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
