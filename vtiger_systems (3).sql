-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 08, 2023 at 11:57 PM
-- Server version: 10.1.38-MariaDB
-- PHP Version: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `iconfluxdb_forex_crm_v2_7`
--

-- --------------------------------------------------------

--
-- Table structure for table `vtiger_systems`
--

CREATE TABLE `vtiger_systems` (
  `id` int(11) NOT NULL,
  `server` varchar(100) DEFAULT NULL,
  `server_port` int(11) DEFAULT NULL,
  `server_username` varchar(100) DEFAULT NULL,
  `server_password` text,
  `server_type` varchar(20) DEFAULT NULL,
  `smtp_auth` varchar(5) DEFAULT NULL,
  `server_path` varchar(256) DEFAULT NULL,
  `from_email_field` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `vtiger_systems`
--

INSERT INTO `vtiger_systems` (`id`, `server`, `server_port`, `server_username`, `server_password`, `server_type`, `smtp_auth`, `server_path`, `from_email_field`) VALUES
(4, 'tls://smtp.sendgrid.net:', 0, 'apikey', '$ve$z87Pzs_Pzs7Pzs_Pz87Ozs_Pzs_Ozs7Pz8_Ozs_Ozs_Pzs7Pz8_Ozs_Oz8_Oz8_Oz87Ozs7Pzs_Pzs7Oz87Oz8_Oz8_Pzs7Pz87Ozs_Oz8_Pzs7Pzs_Pzs_Oz87Pzs_Pz8_Ozs_Oz8_Pzs_Pz87Ozs_Pzs7Pzs_Pz8_Ozs_Oz87Pzs7Pzs_Ozs_Pzs7Pzs_Oz87Pzs7Pz87Pzs7Pzs_Pzs_Pzs7Pz87Pz87Oz8_Ozs7Pzs_Pz8_Pzs_Oz8_Oz8_Pz87Oz8_Ozs7Pz87Pzs7Oz8_Ozs_Ozs_Pz87Pz87Pzs7Pzs7Pzs7Oz8_Oz87Pzs7Oz87Pz8_Pzs_Pzs7Pz8_Ozs_Ozs_Oz8_Oz8_Ozs_Ozs_Pzs_Pz8_Pzs_Oz87Pz87Oz87Pz87Pz8_Pzs_Pzs_Oz8_Ozs_Ozs_Pz87Oz87Oz8_Pzs7Oz87Pzs_Ozs_Pz87Pz87Oz87Oz8_Pz87Oz8_Ozs_Ozs_Oz87Pz87Pz87Pzs7Pzs7Ozs_Pz8_Ozs7Oz87Pz87Pz87Pzs7Pzs_Pzs_Pz8_Oz8_Oz87Pz87Oz8_Ozs7Pzs7Pz87Oz8_Oz87Pz87Oz87Ozs_Pz8_Pz87Oz8_Oz8_Ozs7Oz8_Pz87Pzs_Pz8_Pzs_Pz8_Pzs_Pzs7Oz8_Pz8_Ozs_Pz87Pz87Ozs_Pzs_Oz87Pzs_Pz87Pzs_Ozs_Pzs_Oz87Pzs_Ozs_Pzs_Pz87Oz87Ozs_Pzs7Pz87Pz8_Pzs7Pzs_P', 'email', '1', NULL, 'admin@cloudforexcrm.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `vtiger_systems`
--
ALTER TABLE `vtiger_systems`
  ADD PRIMARY KEY (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
