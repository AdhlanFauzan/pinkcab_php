-- phpMyAdmin SQL Dump
-- version 4.5.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Oct 14, 2016 at 12:11 PM
-- Server version: 5.7.11
-- PHP Version: 5.6.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pinkcabs`
--

-- --------------------------------------------------------

--
-- Table structure for table `driver_locate`
--

CREATE TABLE `driver_locate` (
  `dID` int(11) NOT NULL,
  `driver_name` varchar(40) NOT NULL,
  `driver_phone` int(10) DEFAULT NULL,
  `cab_number` varchar(15) DEFAULT NULL,
  `driver_fcm_id` text,
  `latitude` float(10,7) DEFAULT '0.0000000',
  `longitude` float(10,7) DEFAULT '0.0000000',
  `available` tinyint(4) DEFAULT '1',
  `passen_fcm_id` text,
  `passen_ctct_fcm_id` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `driver_locate`
--

INSERT INTO `driver_locate` (`dID`, `driver_name`, `driver_phone`, `cab_number`, `driver_fcm_id`, `latitude`, `longitude`, `available`, `passen_fcm_id`, `passen_ctct_fcm_id`) VALUES
(1, 'raj', 325523, 'DL-34s AF 3292', 'new fcm token is shit shit', 28.6519508, 77.2314911, 1, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `driver_locate`
--
ALTER TABLE `driver_locate`
  ADD PRIMARY KEY (`dID`),
  ADD UNIQUE KEY `cab_number` (`cab_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `driver_locate`
--
ALTER TABLE `driver_locate`
  MODIFY `dID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
