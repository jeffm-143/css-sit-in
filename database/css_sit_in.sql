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

/*pupose: C programming,c#,java,php,Database, DIgital Logic & Design, Embedded Systems & Iot, Python Programming, Systems Integration & Architecture, Computer Application, Web Design & Development
Labs: 524, 526,528,530,542,544,517 */
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

-- Create table for computer labs
CREATE TABLE lab_rooms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_number VARCHAR(10) NOT NULL,
    total_computers INT NOT NULL DEFAULT 50,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create table for computers
CREATE TABLE computers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pc_number VARCHAR(10) NOT NULL,
    lab_room_id VARCHAR(10) NOT NULL,
    status ENUM('available', 'in_use', 'maintenance') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_pc_lab (pc_number, lab_room_id)
);

-- Create table for reservations
CREATE TABLE reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    lab_room VARCHAR(10) NOT NULL,
    pc_number VARCHAR(10) NOT NULL,
    purpose VARCHAR(100) NOT NULL,
    reservation_date DATE NOT NULL,
    time_in TIME NOT NULL,
    status ENUM('pending', 'approved', 'completed', 'disapproved') DEFAULT 'pending',
    timeout_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(ID_NUMBER)
);
ALTER TABLE sit_in_sessions 
ADD COLUMN reservation_id INT,
ADD FOREIGN KEY (reservation_id) REFERENCES reservations(id);

-- Insert default lab rooms
INSERT INTO lab_rooms (room_number, total_computers, status) VALUES
('524', 50, 'active'),
('526', 50, 'active'),
('528', 50, 'active'),
('530', 50, 'active'),
('542', 50, 'active'),
('544', 50, 'active'),
('517', 50, 'active');

-- Insert sample computers for each lab room
INSERT INTO computers (pc_number, lab_room_id, status) 
SELECT 
    CONCAT('PC', LPAD(numbers.n, 2, '0')), 
    lab_rooms.room_number,
    'available'
FROM 
    (SELECT 1 + tens.i + ones.i AS n
     FROM 
         (SELECT 0 i UNION SELECT 10 UNION SELECT 20 UNION SELECT 30 UNION SELECT 40) tens,
         (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) ones
     WHERE 1 + tens.i + ones.i <= 50
    ) numbers
CROSS JOIN lab_rooms;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

-- Create feedback table with all required columns
CREATE TABLE feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT,
    user_id INT NOT NULL,
    rating INT,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sit_in_sessions(id),
    FOREIGN KEY (user_id) REFERENCES users(ID)
);


--
-- Dumping data for table `feedback`
--



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
