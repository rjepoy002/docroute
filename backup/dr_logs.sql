-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 20, 2023 at 08:42 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dr_logs`
--

INSERT INTO `dr_logs` (`id`, `id_track`, `sender`, `receiver`, `status`, `date`, `remarks`) VALUES
(1, '202310-0001', 1, 2, 'Pending', '2023-10-10 05:58:45', 'remarks test'),
(2, '202310-0002', 2, 1, 'Pending', '2023-10-10 06:00:31', 'For Checking'),
(3, '202310-0003', 1, 2, 'Pending', '2023-10-10 07:46:07', 'for testing purposes only'),
(4, '202310-0004', 1, 2, 'Pending', '2023-10-10 07:48:22', 'for review'),
(5, '202310-0005', 1, 2, 'Pending', '2023-10-10 07:49:29', 'For checking'),
(11, '202310-0003', 1, 2, 'Received', '2023-10-10 10:50:49', NULL),
(18, '202310-0004', 1, 2, 'Received', '2023-10-11 04:42:47', NULL),
(19, '202310-0005', 1, 2, 'Received', '2023-10-11 04:55:24', NULL),
(20, '202310-0002', 2, 1, 'Received', '2023-10-11 04:56:44', NULL),
(21, '202310-0006', 2, 1, 'Pending', '2023-10-11 04:57:27', 'For drawing'),
(22, '202310-0007', 3, 1, 'Pending', '2023-10-11 05:00:27', 'For Preparation'),
(23, '202310-0008', 1, 3, 'Pending', '2023-10-11 05:08:07', 'For copy of record'),
(34, '202310-0006', 1, 2, 'Declined', '2023-10-16 08:11:24', NULL),
(35, '202310-0001', 2, 1, 'Declined', '2023-10-16 10:39:22', NULL),
(36, '202310-0009', 3, 1, 'Pending', '2023-10-17 05:47:47', 'For review'),
(37, '202310-0007', 3, 1, 'Received', '2023-10-17 07:14:03', NULL);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
