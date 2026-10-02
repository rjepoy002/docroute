-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 30, 2024 at 02:36 AM
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
-- Table structure for table `dr_users`
--

CREATE TABLE `dr_users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `mname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `designation` varchar(50) NOT NULL,
  `department` varchar(25) NOT NULL,
  `username` varchar(25) NOT NULL,
  `password` varchar(25) NOT NULL,
  `type` varchar(25) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `datecreated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `dr_users`
--

INSERT INTO `dr_users` (`id`, `name`, `fname`, `mname`, `lname`, `designation`, `department`, `username`, `password`, `type`, `status`, `datecreated`) VALUES
(1, 'Roer Jay G. Padrones', 'Roer Jay', 'Gaviana', 'Padrones', 'Software Architect', 'CPD', 'admin', 'password', 'admin', '', '2023-10-05 10:19:54'),
(2, 'Leobert C. Cabanting', 'Leobert', 'C', 'Cabanting', 'GIS/SCADA Administrator', 'CPD', 'leobert', 'password', NULL, '', '2024-01-09 09:40:07'),
(3, 'Al John Balaoro', 'Al John', '', 'Balaoro', 'Accounting Specialist ', 'FSD', 'acbalaoro', '20100416CPANGELA', NULL, '', '2024-01-19 07:01:29'),
(4, 'BRIAN M. BAGOOD', 'Brian', 'M', 'Bagood', 'FOREMAN', 'ACOD', 'BRY4', '1234', NULL, '', '2024-01-19 07:02:06'),
(5, 'ANGEL DIEGO M. AYALA', 'Angel Diego', 'M', 'Ayala', 'BILLING ANALYST', 'ASOD', 'amayala', 'ama!1234', NULL, '', '2024-01-19 07:05:55'),
(6, 'FAITH S. TAHUM', 'Faith', 'S', 'Tahum', 'BILLING ANALYST', 'ASOD', 'fstahum', 'fst*1924', NULL, '', '2024-01-19 07:11:33'),
(7, 'Erica Magat', 'Erica', '', 'Magat', 'Accounting Specialist', 'FSD', 'Eya', 'monray', NULL, '', '2024-01-19 07:13:16'),
(8, 'Rey Joseph Nepomuceno', 'Rey Joseph', '', 'Nepomuceno', 'Audit Assistant', 'IAD', 'rvnepomuceno', 'joseph123', NULL, '', '2024-01-19 07:15:09'),
(9, '', 'Ralphh', 'B', 'Teleron', 'Business Admin', 'CPD', 'ralph', 'password', NULL, 'For Approval', '2024-01-24 11:31:01'),
(10, '', 'Marvin', 'N', 'Salamanes', 'Ict Support Sh', 'CPD', 'marvs', 'password', NULL, 'For Approval', '2024-01-26 10:06:58');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
