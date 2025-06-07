-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 07, 2025 at 04:42 PM
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
  `m_password` varchar(60) NOT NULL,
  `status` varchar(10) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `memberlogin_tb`
--

INSERT INTO `memberlogin_tb` (`m_login_id`, `m_name`, `m_email`, `m_password`, `status`) VALUES
(31, ' Satkrit Bhandari', 'satkrit@gmail.com', '$2y$10$iSzxRrKt682e0lJXvFkoEe/c26YvxLNV.n59OUeRC9LSShDipMrky', 'active'),
(32, 'satkrit bhandari', 'satkrit15@gmail.com', '$2y$10$UZyXjMrXNHnu12WZQJHaRetxByQ.PPJ33mLLrvCo7JMAfVElZ1fba', 'active');

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
  `member_date` date DEFAULT NULL,
  `subscription_months` int(11) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `subscription_start_date` date DEFAULT NULL,
  `subscription_end_date` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `submitbookingt_tb`
--

INSERT INTO `submitbookingt_tb` (`Booking_id`, `member_name`, `member_email`, `booking_type`, `trainer`, `member_mobile`, `member_add1`, `member_date`, `subscription_months`, `payment_status`, `subscription_start_date`, `subscription_end_date`) VALUES
(15, ' Satkrit Bhandari', 'satkrit@gmail.com', 'Zumba class', 'Aashish Thapa (4:00AM-9:00AM)', '9874563210', 'jorpati', '2025-06-06', 1, 'paid', NULL, NULL),
(16, ' Satkrit Bhandari', 'satkrit@gmail.com', 'Weight Lifting', 'Bikash Thapa', NULL, NULL, '2025-06-08', 1, 'pending', NULL, '2025-07-08'),
(13, ' Satkrit Bhandari', 'satkrit@gmail.com', 'Weight lifting', 'Aashish Thapa (4:00AM-9:00AM)', '9874563211', 'jorpati', '2025-05-30', 3, 'pending', NULL, NULL),
(11, 'satkrit bhandari', 'satkrit@gmail.com', 'Yoga class', 'Aashish Thapa (4:00AM-9:00AM)', '9874563210', 'jorpati', '2025-05-30', 1, 'pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_bookings`
--

CREATE TABLE `tbl_bookings` (
  `id` int(11) NOT NULL,
  `member_email` varchar(255) NOT NULL,
  `class_id` int(11) NOT NULL,
  `class_title` varchar(255) NOT NULL,
  `class_date` date NOT NULL,
  `class_time` time NOT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','cancelled') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_bookings`
--

INSERT INTO `tbl_bookings` (`id`, `member_email`, `class_id`, `class_title`, `class_date`, `class_time`, `booking_date`, `status`) VALUES
(4, 'satkrit@gmail.com', 6, 'Weight Lifting', '2025-06-08', '01:42:00', '2025-06-07 14:28:59', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_events`
--

CREATE TABLE `tbl_events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `start` datetime NOT NULL,
  `end` datetime NOT NULL,
  `trainer` varchar(255) DEFAULT NULL,
  `capacity` int(11) DEFAULT 20,
  `color` varchar(7) DEFAULT '#17a2b8',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_events`
--

INSERT INTO `tbl_events` (`id`, `title`, `start`, `end`, `trainer`, `capacity`, `color`, `description`, `created_at`, `updated_at`) VALUES
(6, 'Weight Lifting', '2025-06-08 01:42:00', '2025-06-08 02:42:00', 'Bikash Thapa', 20, '#ffc107', 'please', '2025-06-07 05:58:14', '2025-06-07 05:58:14');

-- --------------------------------------------------------

--
-- Table structure for table `trainers`
--

CREATE TABLE `trainers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trainers`
--

INSERT INTO `trainers` (`id`, `name`, `email`, `phone`, `gender`, `dob`, `address`, `image`, `created_at`) VALUES
(1, 'Aashish Thapa', 'aashishthapatrainer@gmail.com', '9874123650', 'Male', '1985-04-15', 'Nepal,Kathmandu', 'avtar1.jpeg', '2025-06-07 06:28:31'),
(2, 'Anupama', 'anupamatrainer@gmail.com', '9876543210', 'Female', '1990-07-22', 'Nepal,Kathmandu', 'avtar2.jpeg', '2025-06-07 06:28:31'),
(3, 'Bikash Shrestha', 'bikashtrainer@gmail.com', '9698979495', 'Male', '1988-11-03', 'Nepal,Kathmandu', 'avtar3.jpeg', '2025-06-07 06:28:31'),
(4, 'Santoshi', 'santoshitrainer@gmail.com', '9739876543', 'Female', '1992-02-17', 'Nepal,Kathmandu', 'avtar4.jpeg', '2025-06-07 06:28:31');

-- --------------------------------------------------------

--
-- Table structure for table `trainers_tb`
--

CREATE TABLE `trainers_tb` (
  `id` int(11) NOT NULL,
  `trainer_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `hire_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trainers_tb`
--

INSERT INTO `trainers_tb` (`id`, `trainer_name`, `email`, `phone`, `specialization`, `experience_years`, `status`, `hire_date`, `created_at`, `updated_at`) VALUES
(1, 'Aashish Thapa', 'aashish@gym.com', '9841234567', 'Weight Training, Cardio', 5, 'active', '2020-01-15', '2025-06-07 14:38:58', '2025-06-07 14:38:58'),
(2, 'Bikash Thapa', 'bikash@gym.com', '9841234568', 'Personal Training, Fitness', 3, 'active', '2021-03-10', '2025-06-07 14:38:58', '2025-06-07 14:38:58'),
(3, 'Anupama', 'anupama@gym.com', '9841234569', 'Yoga, Zumba', 4, 'active', '2020-06-20', '2025-06-07 14:38:58', '2025-06-07 14:38:58'),
(4, 'Santoshi', 'santoshi@gym.com', '9841234570', 'Group Fitness, Endurance', 2, 'active', '2022-01-05', '2025-06-07 14:38:58', '2025-06-07 14:38:58');

-- --------------------------------------------------------

--
-- Table structure for table `trainer_availability`
--

CREATE TABLE `trainer_availability` (
  `id` int(11) NOT NULL,
  `trainer_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trainer_availability`
--

INSERT INTO `trainer_availability` (`id`, `trainer_id`, `day_of_week`, `start_time`, `end_time`) VALUES
(1, 1, 'Monday', '09:00:00', '12:00:00'),
(2, 1, 'Tuesday', '09:00:00', '12:00:00'),
(3, 1, 'Wednesday', '09:00:00', '12:00:00'),
(4, 1, 'Thursday', '09:00:00', '12:00:00'),
(5, 1, 'Friday', '09:00:00', '12:00:00'),
(6, 2, 'Monday', '14:00:00', '18:00:00'),
(7, 2, 'Wednesday', '14:00:00', '18:00:00'),
(8, 2, 'Friday', '14:00:00', '18:00:00'),
(9, 3, 'Tuesday', '10:00:00', '15:00:00'),
(10, 3, 'Thursday', '10:00:00', '15:00:00'),
(11, 4, 'Saturday', '08:00:00', '11:00:00'),
(12, 4, 'Sunday', '08:00:00', '11:00:00'),
(13, 1, 'Monday', '05:00:00', '12:00:00'),
(14, 1, 'Tuesday', '05:00:00', '12:00:00'),
(15, 1, 'Wednesday', '05:00:00', '12:00:00'),
(16, 1, 'Thursday', '05:00:00', '12:00:00'),
(17, 1, 'Friday', '05:00:00', '12:00:00'),
(18, 2, 'Sunday', '05:00:00', '12:00:00'),
(19, 2, 'Tuesday', '05:00:00', '12:00:00'),
(20, 2, 'Friday', '05:00:00', '12:00:00'),
(21, 3, 'Sunday', '12:00:00', '08:00:00'),
(22, 3, 'Tuesday', '12:00:00', '08:00:00'),
(23, 3, 'Wednesday', '12:00:00', '08:00:00'),
(24, 3, 'Thursday', '12:00:00', '08:00:00'),
(25, 4, 'Friday', '12:00:00', '08:00:00'),
(26, 4, 'Saturday', '08:00:00', '11:00:00'),
(27, 4, 'Sunday', '08:00:00', '11:00:00'),
(28, 4, 'Monday', '12:00:00', '08:00:00');

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
-- Indexes for table `tbl_bookings`
--
ALTER TABLE `tbl_bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_booking` (`member_email`,`class_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `tbl_events`
--
ALTER TABLE `tbl_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trainers`
--
ALTER TABLE `trainers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `trainers_tb`
--
ALTER TABLE `trainers_tb`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `trainer_availability`
--
ALTER TABLE `trainer_availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trainer_id` (`trainer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `memberlogin_tb`
--
ALTER TABLE `memberlogin_tb`
  MODIFY `m_login_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `submitbookingt_tb`
--
ALTER TABLE `submitbookingt_tb`
  MODIFY `Booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tbl_bookings`
--
ALTER TABLE `tbl_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_events`
--
ALTER TABLE `tbl_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `trainers`
--
ALTER TABLE `trainers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `trainers_tb`
--
ALTER TABLE `trainers_tb`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `trainer_availability`
--
ALTER TABLE `trainer_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_bookings`
--
ALTER TABLE `tbl_bookings`
  ADD CONSTRAINT `tbl_bookings_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `tbl_events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trainer_availability`
--
ALTER TABLE `trainer_availability`
  ADD CONSTRAINT `trainer_availability_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
