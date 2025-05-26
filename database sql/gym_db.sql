-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 24, 2025 at 08:38 AM
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
-- Database: `gym_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `adminlogin_tb`
--

CREATE TABLE `adminlogin_tb` (
  `a_login_id` int(11) NOT NULL,
  `a_name` varchar(60) NOT NULL,
  `a_email` varchar(60) NOT NULL,
  `a_password` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `adminlogin_tb`
--

INSERT INTO `adminlogin_tb` (`a_login_id`, `a_name`, `a_email`, `a_password`) VALUES
(1, 'Admin Kumar', 'admin@gmail.com', '123456');

-- --------------------------------------------------------

--
-- Table structure for table `memberlogin_tb`
--

CREATE TABLE `memberlogin_tb` (
  `m_login_id` int(11) NOT NULL,
  `m_name` varchar(60) NOT NULL,
  `m_email` varchar(60) NOT NULL,
  `m_password` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `memberlogin_tb`
--

INSERT INTO `memberlogin_tb` (`m_login_id`, `m_name`, `m_email`, `m_password`) VALUES
(9, '  Rajesh', 'raj@gmail.com', 'user'),
(10, '  User', 'user@gmail.com', 'user'),
(11, 'Jay', 'jay@gmail.com', 'jay123'),
(12, 'emmanuel', 'emmanuel@gmail.com', 'DDDDD'),
(13, 'ken', 'ken@gmail.com', 'ken'),
(14, 'riley', 'riley@gmail.com', '1234'),
(15, 'alex', 'alex@gmail.com', 'alex'),
(16, 'Ren', 'ren@gmail.com', 'ren'),
(17, 'Nabin', 'nabinbk282@gmail.com', '@nabin123'),
(18, 'Bimal', 'bimal12@gmail.com', 'nepal'),
(26, 'shubham', 'shubam@gmail.com', 'nepal'),
(27, 'fox', 'fox@gmail.com', 'arjun'),
(28, 'prabhat', 'prabhat@gmail.com', 'nepal');

-- --------------------------------------------------------

--
-- Table structure for table `submitbookingt_tb`
--

CREATE TABLE `submitbookingt_tb` (
  `Booking_id` int(11) NOT NULL,
  `member_name` varchar(90) DEFAULT NULL,
  `member_email` varchar(90) DEFAULT NULL,
  `booking_type` varchar(90) DEFAULT NULL,
  `trainer` varchar(90) DEFAULT NULL,
  `member_mobile` varchar(90) DEFAULT NULL,
  `member_add1` varchar(90) DEFAULT NULL,
  `member_date` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `submitbookingt_tb`
--

INSERT INTO `submitbookingt_tb` (`Booking_id`, `member_name`, `member_email`, `booking_type`, `trainer`, `member_mobile`, `member_add1`, `member_date`) VALUES
(1, 'rajeev shrestha', 'rajeevshrestha9823807554@gmail.com', 'Zumba class', 'Aashish Thapa', '9816763386', 'amarhaat 13', '2025-05-15'),
(2, 'Nabin Bk', 'nabinbk282@gmail.com', 'Cardio class', 'Aashish Thapa', '9818400974', 'jorpati', '2025-05-11'),
(3, 'rajeev shrestha', 'rajeevshrestha9823807554@gmail.com', 'Yoga class', 'Aashish Thapa', '9816763386', 'amarhaat 13', '2025-05-19'),
(4, 'Bimal Thapa', 'bimal12@gmail.com', 'Cardio class', 'Bikash Thapa', '9859645123', 'npj', '2025-05-13'),
(5, 'Roman', 'rajeevshrestha9823807554@gmail.com', 'Yoga class', 'Anupama', '9816763386', 'amarhaat 13', '2025-05-27'),
(6, 'shubham', 'shubam@gmail.com', 'Weight lifting', 'Bikash Thapa', '9858080312', 'gongabu', '2025-05-18');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_events`
--

CREATE TABLE `tbl_events` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_events`
--

INSERT INTO `tbl_events` (`id`, `title`, `start`, `end`) VALUES
(1, 'New class start', '2025-05-18 00:00:00', '2025-05-19 00:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adminlogin_tb`
--
ALTER TABLE `adminlogin_tb`
  ADD PRIMARY KEY (`a_email`);

--
-- Indexes for table `memberlogin_tb`
--
ALTER TABLE `memberlogin_tb`
  ADD PRIMARY KEY (`m_login_id`);

--
-- Indexes for table `submitbookingt_tb`
--
ALTER TABLE `submitbookingt_tb`
  ADD PRIMARY KEY (`Booking_id`);

--
-- Indexes for table `tbl_events`
--
ALTER TABLE `tbl_events`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `memberlogin_tb`
--
ALTER TABLE `memberlogin_tb`
  MODIFY `m_login_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `submitbookingt_tb`
--
ALTER TABLE `submitbookingt_tb`
  MODIFY `Booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_events`
--
ALTER TABLE `tbl_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
