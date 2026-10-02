-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 07, 2024 at 01:55 AM
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
(1, '', 'Roer Jay', 'Gaviana', 'Padrones', 'Software Architect', 'CPD', 'admin', 'password2636', 'admin', 'For Approval', '2024-01-30 09:42:08'),
(2, '', 'Renato', 'Balmonte', 'Briones', 'Area Manager', 'ANOD', 'renatobriones24', '@renatobriones24', NULL, 'For Approval', '2024-01-30 10:12:34'),
(3, '', 'Sairha', 'Mendoza', 'Madarcos', 'Area Consumer Welfare Desk Officer', 'ANOD', 'Sair', 'SaiSai#1001', NULL, 'For Approval', '2024-01-30 10:35:43'),
(4, '', 'Rey Joseph', 'Vicente', 'Nepomuceno', 'Financial And Management Audit Assistant', 'IAD', 'rjoseph', 'joseph123', NULL, 'For Approval', '2024-01-30 10:46:24'),
(5, '', 'Erica', 'Macmac', 'Magat', 'Accounting Specialist', 'FSD', 'Erica', 'MonRay', NULL, 'For Approval', '2024-01-30 01:59:50'),
(6, '', 'Marvin', 'Castro', 'Buban', 'Engineering System Officer', 'TSD', 'AgentFrozt', 'Pass032344', NULL, 'For Approval', '2024-01-31 08:28:06'),
(7, '', 'Jeg', 'Osabel', 'Mendoza', 'Row Specialist', 'TSD', 'JOMENDOZA', '123456', NULL, 'For Approval', '2024-01-31 09:19:53'),
(8, '', 'Celeste Jay ', 'Negosa', 'Cajiles', 'Technical And System Operation Audit Officer', 'IAD', 'cncajiles', 'cnc!1234', NULL, 'For Approval', '2024-02-01 08:26:53'),
(9, '', 'Leobert', 'Caigoy', 'Cabanting', 'Gis/scada Administrator', 'CPD', 'leobert', 'lcc!1234', NULL, 'For Approval', '2024-02-01 08:31:47'),
(10, '', 'Ralph', 'Bacaser', 'Teleron', 'Business System Administrator', 'CPD', 'rbteleron', 'sde!1234', NULL, 'For Approval', '2024-02-01 08:33:18'),
(11, '', 'Ryan Albert', 'Alcantara', 'Socrates', 'Records And Data Control Officer', 'CPD', 'Ryan', 'rye1388', NULL, 'For Approval', '2024-02-01 08:35:44'),
(12, '', 'Alexandra', 'Billones', 'Adelantar', 'Gm Staff', 'OGM', 'alexandraadelantar', 'alex1234', NULL, 'For Approval', '2024-02-01 08:57:58'),
(13, '', 'Maria Regina', 'Seguritan', 'Libao', 'Gm Secretary', 'OGM', 'r.s.libao.paleco', 'PotsieGMSEC', NULL, 'For Approval', '2024-02-01 09:13:23'),
(14, '', 'Princess Shara Jade', 'Nangnang', 'Caliguid', 'Office Clerk', 'ACOD', 'jade', 'helios', NULL, 'For Approval', '2024-02-01 09:16:36'),
(15, '', 'Janice', 'Simbahan', 'Sanchez', 'Financial Planning And Cashiering Officer', 'FSD', 'Jss4', 'jss!1234', NULL, 'For Approval', '2024-02-01 09:26:30'),
(16, '', 'Al John', 'Calates', 'Balaoro', 'Accounting Specialist', 'FSD', 'acbalaoro', '20100416CPANGELA', NULL, 'For Approval', '2024-02-01 09:35:32'),
(17, '', 'Jayric', 'Duloroc', 'Eser', 'Treasury Assistant', 'FSD', 'Jayric04', 'eser08', NULL, 'For Approval', '2024-02-01 09:51:32'),
(18, '', 'Maricel', 'Ibang Ibang', 'Hamora', 'Hr Assistant', 'ISD', 'tabz', 'tabz1234', NULL, 'For Approval', '2024-02-01 09:58:56'),
(19, '', 'Janrey', 'Laguna', 'Mayor', 'Ict Division Chief', 'CPD', 'jlmayor', '824655', NULL, 'For Approval', '2024-02-01 10:53:14'),
(20, '', 'Jazzy', 'Zamora', 'Ilao', 'Procurement Assistant', 'ISD', 'jzilao', 'jzi!1234', NULL, 'For Approval', '2024-02-01 11:01:07'),
(21, '', 'John Kalvin', 'Abid', 'Manongsong', 'Materials Management Officer', 'ISD', 'jamanongsong', 'jam!1234', NULL, 'For Approval', '2024-02-01 11:09:33'),
(22, '', 'Harley Cyrus', 'Dador', 'Flores', 'Materials & Management Assistant', 'ISD', 'harleycyrus', 'Warehouse123', NULL, 'For Approval', '2024-02-01 11:20:12'),
(23, '', 'John', 'Paul', 'Cuering', 'Tsoash', 'IAD', 'jscuering', 'jamjam', NULL, 'For Approval', '2024-02-01 01:30:24'),
(24, '', 'Russel John', 'Cuba', 'Maranan', 'Tsoao', 'IAD', 'Russ08', '111111', NULL, 'For Approval', '2024-02-01 01:30:33'),
(25, '', 'Rachile', 'Osiana', 'Cebuano', 'Area North-housewiring Officer', 'ANOD', 'CHE16', '011688', NULL, 'For Approval', '2024-02-01 01:56:25'),
(26, '', 'Ma. Janelle', 'Ordiales', 'Rebusada', 'Information Officer', 'ISD', 'MJanelleOR', '1307Forever.', NULL, 'For Approval', '2024-02-02 08:31:00'),
(27, '', 'Leizl', 'Anape', 'Herrera', 'Member Services Officer', 'ISD', 'laherrera', 'Kelix0308!', NULL, 'For Approval', '2024-02-02 08:43:15'),
(28, '', 'Rother', 'Remo', 'Omilda', 'Area Central Div. Chief', 'ACOD', 'rromilda', 'rro!1234', NULL, 'For Approval', '2024-02-02 10:02:21'),
(29, '', 'Ma. Louise', 'Lazaro', 'Calapardo', 'Member Services Assistant', 'ISD', 'maria.louise', 'psalm73:26', NULL, 'For Approval', '2024-02-02 11:11:25'),
(30, '', 'Ma. Ailyn', 'Peã‘afiel', 'Repe', 'Area Billing Officer', 'ANOD', 'Ailyn', 'schubertz2003', NULL, 'For Approval', '2024-02-02 11:19:26'),
(31, '', 'Vicky Monette', 'Alcantara', 'Basilio', 'Member Services Division Chief', 'ISD', 'vkymonette', 'Bruce', NULL, 'For Approval', '2024-02-02 01:06:29'),
(32, '', 'Albert', 'Tabang', 'Palao', 'Cwd Section Head', 'ISD', 'abetpalao', '252378albTP!', NULL, 'For Approval', '2024-02-02 01:40:48'),
(33, '', 'Nieko Jae', 'Rey', 'Lat', 'Billing Officer', 'ACOD', 'njae13', 'Annika', NULL, 'For Approval', '2024-02-02 03:59:17'),
(34, '', 'Ryan Egxiel', 'Pe', 'Palay', 'Procurement Specialist', 'ISD', 'rppalay', '!rppalay25', NULL, 'For Approval', '2024-02-02 04:57:52'),
(35, '', 'Myra', 'Caabay', 'Padon', 'Financial And Management Audit Section Head', 'IAD', 'myra', 'mcp!1234', NULL, 'For Approval', '2024-02-05 09:24:11'),
(36, '', 'Joana Colleen', 'P', 'Tungpalan', 'Financial And Management Audit Officer', 'IAD', 'Cols', 'RpJc2513', NULL, 'For Approval', '2024-02-05 10:05:30'),
(37, '', 'Angelique Joyce', 'Rosas', 'Flores', 'Nurse', 'ISD', 'AJRFLORES', 'AJRF1234!', NULL, 'For Approval', '2024-02-05 11:55:25'),
(38, '', 'John Rhey', 'Ponce De Leon', 'Sales', 'Substation & Special Equipment Head', 'TSD', 'johnrhey', 'jps!1234', NULL, 'For Approval', '2024-02-05 02:17:54'),
(39, '', 'Rolando Jr.', 'Leaã±o', 'Nicolas', 'Tsoao', 'IAD', 'ghoyhong', 'rowil0306', NULL, 'For Approval', '2024-02-05 02:46:44'),
(40, '', 'Ida Chariz', 'Mandapat', 'Espino', 'Member Service Assistant', 'ANOD', 'imespino23', 'imespino23', NULL, 'For Approval', '2024-02-05 04:20:16'),
(41, '', 'Timothy', 'Tabi', 'Badenas', 'Metering Section Head', 'ACOD', 'TimBadenas', 'ttbadenas@02', NULL, 'For Approval', '2024-02-05 04:41:35'),
(42, '', 'Jocelyn', 'Rabaja', 'Sarmiento', 'Member Services Specialist', 'ACOD', 'jors!', '1234', NULL, 'For Approval', '2024-02-05 05:08:39'),
(43, '', 'Mary Eustelia', 'Sendaydiego', 'Bundac', 'Acod Manager', 'ACOD', 'Telly', 'UPSCA-LB', NULL, 'For Approval', '2024-02-05 06:35:55'),
(44, '', 'Marinela', 'Castulo', 'Llavan', 'Hr Clerk', 'ISD', 'ella', '0714', NULL, 'For Approval', '2024-02-06 09:23:50'),
(45, '', 'Darwin', 'Batac', 'Pineda', 'Building And Ground Administrator', 'ISD', 'Darwin25', 'DPsmd1325', NULL, 'For Approval', '2024-02-06 09:30:03'),
(46, '', 'Cesary', 'Estero', 'Jaramilla', 'Gssh', 'ISD', 'cejaramilla', 'cej!1234', NULL, 'For Approval', '2024-02-06 09:35:11'),
(47, '', 'Ma. Alfie', 'Ramirez', 'Miras', 'Teller', 'ACOD', 'faye', '070718', NULL, 'For Approval', '2024-02-06 11:17:49');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
