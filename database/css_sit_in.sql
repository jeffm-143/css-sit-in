-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2025 at 06:14 AM
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
-- Database: `css_sit_in`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `admin_username` varchar(50) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `date_posted` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `admin_username`, `content`, `date_posted`) VALUES
(1, 'admin', 'New AnnouncementsZs', '2025-03-20 12:03:00'),
(2, 'admin', 'Hello! This is the ADMIN!', '2025-03-20 12:12:23'),
(3, 'admin', 'Post announcementszzzzzz', '2025-03-20 12:32:44');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `posID` int(11) NOT NULL,
  `pos_Name` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`posID`, `pos_Name`, `user_id`, `rating`, `comments`, `created_at`) VALUES
(1, 7, 2, NULL, 'asdasdasdasd', '2025-04-10 03:24:47'),
(2, 16, 2, NULL, 'Feedbacks Good', '2025-04-10 04:26:27'),
(3, 28, 2, NULL, 'Feedback!!', '2025-04-10 05:09:49');

-- --------------------------------------------------------

--
-- Table structure for table `sit_in_sessions`
--

CREATE TABLE `sit_in_sessions` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `lab_room` varchar(50) DEFAULT NULL,
  `purpose` varchar(100) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `status` enum('active','completed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sit_in_sessions`
--

INSERT INTO `sit_in_sessions` (`id`, `student_id`, `lab_room`, `purpose`, `start_time`, `end_time`, `status`, `created_at`) VALUES
(1, 22683320, '524', 'C', '2025-03-20 05:02:26', '2025-03-20 05:02:44', 'completed', '2025-03-20 04:02:26'),
(2, 22683320, '547', 'Java', '2025-03-20 05:05:21', '2025-03-20 05:06:05', 'completed', '2025-03-20 04:05:21'),
(3, 11111111, '542', 'Python', '2025-03-20 05:05:34', '2025-03-20 05:06:05', 'completed', '2025-03-20 04:05:34'),
(4, 11111111, '524', 'ASP.Net', '2025-03-20 05:30:09', '2025-03-20 05:30:29', 'completed', '2025-03-20 04:30:09'),
(5, 22683320, 'MAC', 'ASP.Net', '2025-03-20 05:31:55', '2025-03-20 05:31:58', 'completed', '2025-03-20 04:31:55'),
(6, 44444444, '524', 'C#', '2025-03-20 05:33:30', '2025-03-20 05:33:35', 'completed', '2025-03-20 04:33:30'),
(7, 22683320, 'MAC', 'ASP.Net', '2025-03-25 05:06:48', '2025-03-25 05:08:58', 'completed', '2025-03-25 04:06:48'),
(8, 11111111, '547', 'Java', '2025-03-25 05:07:23', '2025-03-25 05:08:59', 'completed', '2025-03-25 04:07:23'),
(9, 22222222, '542', 'PHP', '2025-03-25 05:07:34', '2025-03-25 05:08:59', 'completed', '2025-03-25 04:07:34'),
(10, 33333333, '530', 'Python', '2025-03-25 05:07:48', '2025-03-25 05:08:59', 'completed', '2025-03-25 04:07:48'),
(11, 44444444, '528', 'C#', '2025-03-25 05:08:02', '2025-03-25 05:08:25', 'completed', '2025-03-25 04:08:02'),
(12, 22683320, '524', 'C', '2025-04-10 06:17:29', '2025-04-10 06:17:31', 'completed', '2025-04-10 04:17:29'),
(13, 22683320, '524', 'C#', '2025-04-10 06:17:41', '2025-04-10 06:17:42', 'completed', '2025-04-10 04:17:41'),
(14, 22683320, '524', 'C', '2025-04-10 06:22:09', '2025-04-10 06:22:10', 'completed', '2025-04-10 04:22:09'),
(15, 22683320, '526', 'Python', '2025-04-10 06:22:26', '2025-04-10 06:22:27', 'completed', '2025-04-10 04:22:26'),
(16, 22683320, '528', 'Python', '2025-04-10 06:22:42', '2025-04-10 06:22:43', 'completed', '2025-04-10 04:22:42'),
(17, 11111111, '528', 'C#', '2025-04-10 06:27:58', '2025-04-10 06:28:12', 'completed', '2025-04-10 04:27:58'),
(18, 33333333, '530', 'Python', '2025-04-10 06:31:09', '2025-04-10 06:31:11', 'completed', '2025-04-10 04:31:09'),
(19, 22683320, '526', 'ASP.Net', '2025-04-10 07:01:18', '2025-04-10 07:01:20', 'completed', '2025-04-10 05:01:18'),
(20, 11111111, '530', 'C#', '2025-04-10 07:02:00', '2025-04-10 07:02:00', 'completed', '2025-04-10 05:02:00'),
(21, 22222222, '528', 'C#', '2025-04-10 07:02:11', '2025-04-10 07:02:12', 'completed', '2025-04-10 05:02:11'),
(22, 22683320, '526', 'C#', '2025-04-10 07:03:51', '2025-04-10 07:03:52', 'completed', '2025-04-10 05:03:51'),
(23, 44444444, '526', 'C#', '2025-04-10 07:05:27', '2025-04-10 07:05:59', 'completed', '2025-04-10 05:05:27'),
(24, 33333333, '542', 'Python', '2025-04-10 07:05:35', '2025-04-10 07:06:00', 'completed', '2025-04-10 05:05:35'),
(25, 22222222, '530', 'C#', '2025-04-10 07:05:48', '2025-04-10 07:06:00', 'completed', '2025-04-10 05:05:48'),
(26, 11111111, '547', 'ASP.Net', '2025-04-10 07:05:58', '2025-04-10 07:06:01', 'completed', '2025-04-10 05:05:58'),
(27, 22683320, '542', 'PHP', '2025-04-10 07:08:42', '2025-04-10 07:08:43', 'completed', '2025-04-10 05:08:42'),
(28, 22683320, '528', 'C#', '2025-04-10 07:09:06', '2025-04-10 07:09:07', 'completed', '2025-04-10 05:09:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `ID_NUMBER` int(11) NOT NULL,
  `LASTNAME` varchar(50) NOT NULL,
  `FIRSTNAME` varchar(50) NOT NULL,
  `MIDDLENAME` varchar(50) DEFAULT NULL,
  `COURSE` varchar(100) DEFAULT NULL,
  `YEAR` int(11) DEFAULT NULL,
  `USERNAME` varchar(50) NOT NULL,
  `PASSWORD` varchar(100) NOT NULL,
  `EMAIL` varchar(100) NOT NULL,
  `ADDRESS` varchar(30) DEFAULT NULL,
  `SESSION` int(11) DEFAULT 30,
  `IMAGE` varchar(100) DEFAULT NULL,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_type` enum('student','admin') NOT NULL DEFAULT 'student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `ID_NUMBER`, `LASTNAME`, `FIRSTNAME`, `MIDDLENAME`, `COURSE`, `YEAR`, `USERNAME`, `PASSWORD`, `EMAIL`, `ADDRESS`, `SESSION`, `IMAGE`, `CREATED_AT`, `user_type`) VALUES
(0, 0, 'CSS', 'Admin', NULL, NULL, NULL, 'admin', '$2y$10$rKXnBT9B.Qd3dNh0oqHdzupRQdRcZ6pxALhKQo9GzSvYirqLOZB1y', '', NULL, 30, NULL, '2025-03-20 04:01:31', 'admin'),
(2, 22683320, 'Monreal', 'Jeff', 'Ranido', 'BSIT', 3, 'j123', '$2y$10$SBn6TczoE3LocgsOt8/8ieo0Z5BdREuO89gwr6xuMFSP.KLy6qc2C', 'monrealjeff2@gmail.com', 'Guadalupe Cebu City', 30, 'uploads/jeff.png', '2025-03-20 04:01:47', 'student'),
(3, 11111111, 'Moon', 'Jay', 'Sundae', 'BSIT', 3, 'jay123', '$2y$10$Ap9zWDWsUMv6Ubshx3UqguRHK2SLUSowVCCIGMX3JU/aVjC6fA/im', 'jay123@gmail.com', 'Guadalupe Cebu City', 30, NULL, '2025-03-20 04:03:41', 'student'),
(4, 22222222, 'Catubig', 'Mark Dave', '', 'BSIT', 3, 'mark123', '$2y$10$b56LhAnz4t2hN1PHdFyt..WYeif4ezSN8EpkgMELA2IqDjoZgqGP6', 'mark123@gmail.com', 'Guadalupe Cebu City', 30, NULL, '2025-03-20 04:16:30', 'student'),
(5, 33333333, 'Sagaral', 'Alexus', 'Sundae', 'BSIT', 3, 'alex123', '$2y$10$ubl9rTYHbEiiLg2LdDqUi.hq70nHPG82lZDwNxhXk40GxDYbqCTka', 'alex123@gmail.com', 'Guadalupe Cebu City', 30, NULL, '2025-03-20 04:18:01', 'student'),
(6, 44444444, 'Palacio', 'Kuya Real', '', 'BSIT', 3, 'real123', '$2y$10$wyYAlVz/Kct.a5lOyaqFYuCMo8ig2ddFYBFdufJYpgkuS2hrXC8ry', 'real123@gmail.com', 'Ramos ', 30, NULL, '2025-03-20 04:29:32', 'student');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_username` (`admin_username`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`posID`),
  ADD KEY `session_id` (`pos_Name`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `sit_in_sessions`
--
ALTER TABLE `sit_in_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `ID_NUMBER` (`ID_NUMBER`),
  ADD UNIQUE KEY `USERNAME` (`USERNAME`),
  ADD UNIQUE KEY `EMAIL` (`EMAIL`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `posID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sit_in_sessions`
--
ALTER TABLE `sit_in_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`admin_username`) REFERENCES `users` (`USERNAME`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`pos_Name`) REFERENCES `sit_in_sessions` (`id`),
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`ID`);

--
-- Constraints for table `sit_in_sessions`
--
ALTER TABLE `sit_in_sessions`
  ADD CONSTRAINT `sit_in_sessions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`ID_NUMBER`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
