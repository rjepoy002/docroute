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
-- Table structure for table `dr_users`
--

CREATE TABLE `dr_users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `designation` varchar(50) NOT NULL,
  `department` varchar(25) NOT NULL,
  `username` varchar(25) NOT NULL,
  `password` varchar(25) NOT NULL,
  `type` varchar(25) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dr_users`
--

INSERT INTO `dr_users` (`id`, `name`, `designation`, `department`, `username`, `password`, `type`, `datecreated`) VALUES
(1, 'Roer Jay G. Padrones', 'Software Architect', 'CPD', 'admin', 'password', 'admin', '2023-10-05 10:19:54'),
(2, 'Leobert C. Cabanting', 'GIS/SCADA Administrator', 'CPD', 'leobert', 'password', NULL, '2023-10-09 03:18:59'),
(3, 'Ralph Teleron', 'Business System Administrator', 'CPD', 'Ralph', 'password', NULL, '2023-10-11 04:59:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dr_users`
--
ALTER TABLE `dr_users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dr_users`
--
ALTER TABLE `dr_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
