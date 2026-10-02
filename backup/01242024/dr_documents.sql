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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dr_documents`
--

INSERT INTO `dr_documents` (`id`, `id_track`, `title`, `description`, `author`, `datecreated`, `status`) VALUES
(1, '202310-0001', 'DTR Summary', 'September 1-15, 2023', 1, '2023-10-10 05:58:45', 'closed'),
(2, '202310-0002', 'GIS Map', 'Sample Mapping', 2, '2023-10-10 06:00:31', 'closed'),
(3, '202310-0003', 'Memorandum #12', 'Memo for scratch paper', 1, '2023-10-10 07:46:07', 'closed'),
(4, '202310-0004', 'Memorandum #13', 'Scratch again', 1, '2023-10-10 07:48:22', 'closed'),
(5, '202310-0005', 'Memorandum #14', 'Review scratch', 1, '2023-10-10 07:49:29', 'closed'),
(6, '202310-0006', 'Memorandum #01', 'Notebook', 2, '2023-10-11 04:57:27', 'closed'),
(7, '202310-0007', 'DTR Summary', 'September 16-30', 3, '2023-10-11 05:00:27', 'closed'),
(8, '202310-0008', 'DTR Card', 'Photocopy', 1, '2023-10-11 05:08:07', 'open'),
(9, '202310-0009', 'Office Order #1', 'Scratch paper', 3, '2023-10-17 05:47:47', 'open');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
