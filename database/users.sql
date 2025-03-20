-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 27, 2025 at 01:39 PM
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
  `SESSION` varchar(50) DEFAULT NULL,
  `IMAGE` varchar(100) DEFAULT NULL,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_type` ENUM('student', 'admin') DEFAULT 'student' NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50),
    date DATE,
    laboratory VARCHAR(50),
    time_slot VARCHAR(50),
    purpose TEXT,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (username) REFERENCES users(USERNAME)
);

-- Announcements table
CREATE TABLE announcements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    admin_username VARCHAR(50),
    content TEXT,
    date_posted DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_username) REFERENCES users(USERNAME)
);
--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `USERNAME` (`USERNAME`),
  ADD UNIQUE KEY `EMAIL` (`EMAIL`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

-- Insert default admin account
INSERT INTO users (ID_NUMBER, LASTNAME, FIRSTNAME, USERNAME, PASSWORD, user_type, EMAIL) 
VALUES (
    '999999', 
    'Administrator', 
    'System', 
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- This is the hash for 'password'
    'admin',
    'admin@uc.edu.ph'
) ON DUPLICATE KEY UPDATE user_type = 'admin';

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
