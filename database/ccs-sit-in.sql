-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2025 at 11:35 AM
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
-- Database: `ccs-sit-in`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `admin_username` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `date_posted` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `admin_username`, `content`, `date_posted`) VALUES
(1, 'admin', 'Sample', '2025-05-08 12:55:35'),
(0, 'admin', 'hello', '2025-05-15 12:18:42');

-- --------------------------------------------------------

--
-- Table structure for table `computers`
--

CREATE TABLE `computers` (
  `id` int(11) NOT NULL,
  `pc_number` varchar(10) NOT NULL,
  `lab_room_id` varchar(10) NOT NULL,
  `status` enum('available','in_use','maintenance') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `computers`
--

INSERT INTO `computers` (`id`, `pc_number`, `lab_room_id`, `status`, `created_at`) VALUES
(1, 'PC01', '524', 'available', '2025-05-08 19:05:35'),
(2, 'PC11', '524', 'available', '2025-05-08 19:05:35'),
(3, 'PC21', '524', 'available', '2025-05-08 19:05:35'),
(4, 'PC31', '524', 'available', '2025-05-08 19:05:35'),
(5, 'PC41', '524', 'available', '2025-05-08 19:05:35'),
(6, 'PC01', '526', 'available', '2025-05-08 19:05:35'),
(7, 'PC11', '526', 'available', '2025-05-08 19:05:35'),
(8, 'PC21', '526', 'available', '2025-05-08 19:05:35'),
(9, 'PC31', '526', 'available', '2025-05-08 19:05:35'),
(10, 'PC41', '526', 'available', '2025-05-08 19:05:35'),
(11, 'PC01', '528', 'available', '2025-05-08 19:05:35'),
(12, 'PC11', '528', 'available', '2025-05-08 19:05:35'),
(13, 'PC21', '528', 'available', '2025-05-08 19:05:35'),
(14, 'PC31', '528', 'available', '2025-05-08 19:05:35'),
(15, 'PC41', '528', 'available', '2025-05-08 19:05:35'),
(16, 'PC01', '530', 'available', '2025-05-08 19:05:35'),
(17, 'PC11', '530', 'available', '2025-05-08 19:05:35'),
(18, 'PC21', '530', 'available', '2025-05-08 19:05:35'),
(19, 'PC31', '530', 'available', '2025-05-08 19:05:35'),
(20, 'PC41', '530', 'available', '2025-05-08 19:05:35'),
(21, 'PC01', '542', 'available', '2025-05-08 19:05:35'),
(22, 'PC11', '542', 'available', '2025-05-08 19:05:35'),
(23, 'PC21', '542', 'available', '2025-05-08 19:05:35'),
(24, 'PC31', '542', 'available', '2025-05-08 19:05:35'),
(25, 'PC41', '542', 'available', '2025-05-08 19:05:35'),
(26, 'PC01', '544', 'available', '2025-05-08 19:05:35'),
(27, 'PC11', '544', 'available', '2025-05-08 19:05:35'),
(28, 'PC21', '544', 'available', '2025-05-08 19:05:35'),
(29, 'PC31', '544', 'available', '2025-05-08 19:05:35'),
(30, 'PC41', '544', 'available', '2025-05-08 19:05:35'),
(31, 'PC01', '517', 'available', '2025-05-08 19:05:35'),
(32, 'PC11', '517', 'available', '2025-05-08 19:05:35'),
(33, 'PC21', '517', 'available', '2025-05-08 19:05:35'),
(34, 'PC31', '517', 'available', '2025-05-08 19:05:35'),
(35, 'PC41', '517', 'available', '2025-05-08 19:05:35'),
(36, 'PC02', '524', 'available', '2025-05-08 19:05:35'),
(37, 'PC12', '524', 'available', '2025-05-08 19:05:35'),
(38, 'PC22', '524', 'available', '2025-05-08 19:05:35'),
(39, 'PC32', '524', 'available', '2025-05-08 19:05:35'),
(40, 'PC42', '524', 'available', '2025-05-08 19:05:35'),
(41, 'PC02', '526', 'available', '2025-05-08 19:05:35'),
(42, 'PC12', '526', 'available', '2025-05-08 19:05:35'),
(43, 'PC22', '526', 'available', '2025-05-08 19:05:35'),
(44, 'PC32', '526', 'available', '2025-05-08 19:05:35'),
(45, 'PC42', '526', 'available', '2025-05-08 19:05:35'),
(46, 'PC02', '528', 'available', '2025-05-08 19:05:35'),
(47, 'PC12', '528', 'available', '2025-05-08 19:05:35'),
(48, 'PC22', '528', 'available', '2025-05-08 19:05:35'),
(49, 'PC32', '528', 'available', '2025-05-08 19:05:35'),
(50, 'PC42', '528', 'available', '2025-05-08 19:05:35'),
(51, 'PC02', '530', 'available', '2025-05-08 19:05:35'),
(52, 'PC12', '530', 'available', '2025-05-08 19:05:35'),
(53, 'PC22', '530', 'available', '2025-05-08 19:05:35'),
(54, 'PC32', '530', 'available', '2025-05-08 19:05:35'),
(55, 'PC42', '530', 'available', '2025-05-08 19:05:35'),
(56, 'PC02', '542', 'available', '2025-05-08 19:05:35'),
(57, 'PC12', '542', 'available', '2025-05-08 19:05:35'),
(58, 'PC22', '542', 'available', '2025-05-08 19:05:35'),
(59, 'PC32', '542', 'available', '2025-05-08 19:05:35'),
(60, 'PC42', '542', 'available', '2025-05-08 19:05:35'),
(61, 'PC02', '544', 'available', '2025-05-08 19:05:35'),
(62, 'PC12', '544', 'available', '2025-05-08 19:05:35'),
(63, 'PC22', '544', 'available', '2025-05-08 19:05:35'),
(64, 'PC32', '544', 'available', '2025-05-08 19:05:35'),
(65, 'PC42', '544', 'available', '2025-05-08 19:05:35'),
(66, 'PC02', '517', 'available', '2025-05-08 19:05:35'),
(67, 'PC12', '517', 'available', '2025-05-08 19:05:35'),
(68, 'PC22', '517', 'available', '2025-05-08 19:05:35'),
(69, 'PC32', '517', 'available', '2025-05-08 19:05:35'),
(70, 'PC42', '517', 'available', '2025-05-08 19:05:35'),
(71, 'PC03', '524', 'available', '2025-05-08 19:05:35'),
(72, 'PC13', '524', 'available', '2025-05-08 19:05:35'),
(73, 'PC23', '524', 'available', '2025-05-08 19:05:35'),
(74, 'PC33', '524', 'available', '2025-05-08 19:05:35'),
(75, 'PC43', '524', 'available', '2025-05-08 19:05:35'),
(76, 'PC03', '526', 'available', '2025-05-08 19:05:35'),
(77, 'PC13', '526', 'available', '2025-05-08 19:05:35'),
(78, 'PC23', '526', 'available', '2025-05-08 19:05:35'),
(79, 'PC33', '526', 'available', '2025-05-08 19:05:35'),
(80, 'PC43', '526', 'available', '2025-05-08 19:05:35'),
(81, 'PC03', '528', 'available', '2025-05-08 19:05:35'),
(82, 'PC13', '528', 'available', '2025-05-08 19:05:35'),
(83, 'PC23', '528', 'available', '2025-05-08 19:05:35'),
(84, 'PC33', '528', 'available', '2025-05-08 19:05:35'),
(85, 'PC43', '528', 'available', '2025-05-08 19:05:35'),
(86, 'PC03', '530', 'available', '2025-05-08 19:05:35'),
(87, 'PC13', '530', 'available', '2025-05-08 19:05:35'),
(88, 'PC23', '530', 'available', '2025-05-08 19:05:35'),
(89, 'PC33', '530', 'available', '2025-05-08 19:05:35'),
(90, 'PC43', '530', 'available', '2025-05-08 19:05:35'),
(91, 'PC03', '542', 'available', '2025-05-08 19:05:35'),
(92, 'PC13', '542', 'available', '2025-05-08 19:05:35'),
(93, 'PC23', '542', 'available', '2025-05-08 19:05:35'),
(94, 'PC33', '542', 'available', '2025-05-08 19:05:35'),
(95, 'PC43', '542', 'available', '2025-05-08 19:05:35'),
(96, 'PC03', '544', 'available', '2025-05-08 19:05:35'),
(97, 'PC13', '544', 'available', '2025-05-08 19:05:35'),
(98, 'PC23', '544', 'available', '2025-05-08 19:05:35'),
(99, 'PC33', '544', 'available', '2025-05-08 19:05:35'),
(100, 'PC43', '544', 'available', '2025-05-08 19:05:35'),
(101, 'PC03', '517', 'available', '2025-05-08 19:05:35'),
(102, 'PC13', '517', 'available', '2025-05-08 19:05:35'),
(103, 'PC23', '517', 'available', '2025-05-08 19:05:35'),
(104, 'PC33', '517', 'available', '2025-05-08 19:05:35'),
(105, 'PC43', '517', 'available', '2025-05-08 19:05:35'),
(106, 'PC04', '524', 'available', '2025-05-08 19:05:35'),
(107, 'PC14', '524', 'available', '2025-05-08 19:05:35'),
(108, 'PC24', '524', 'available', '2025-05-08 19:05:35'),
(109, 'PC34', '524', 'available', '2025-05-08 19:05:35'),
(110, 'PC44', '524', 'available', '2025-05-08 19:05:35'),
(111, 'PC04', '526', 'available', '2025-05-08 19:05:35'),
(112, 'PC14', '526', 'available', '2025-05-08 19:05:35'),
(113, 'PC24', '526', 'available', '2025-05-08 19:05:35'),
(114, 'PC34', '526', 'available', '2025-05-08 19:05:35'),
(115, 'PC44', '526', 'available', '2025-05-08 19:05:35'),
(116, 'PC04', '528', 'available', '2025-05-08 19:05:35'),
(117, 'PC14', '528', 'available', '2025-05-08 19:05:35'),
(118, 'PC24', '528', 'available', '2025-05-08 19:05:35'),
(119, 'PC34', '528', 'available', '2025-05-08 19:05:35'),
(120, 'PC44', '528', 'available', '2025-05-08 19:05:35'),
(121, 'PC04', '530', 'available', '2025-05-08 19:05:35'),
(122, 'PC14', '530', 'available', '2025-05-08 19:05:35'),
(123, 'PC24', '530', 'available', '2025-05-08 19:05:35'),
(124, 'PC34', '530', 'available', '2025-05-08 19:05:35'),
(125, 'PC44', '530', 'available', '2025-05-08 19:05:35'),
(126, 'PC04', '542', 'available', '2025-05-08 19:05:35'),
(127, 'PC14', '542', 'available', '2025-05-08 19:05:35'),
(128, 'PC24', '542', 'available', '2025-05-08 19:05:35'),
(129, 'PC34', '542', 'available', '2025-05-08 19:05:35'),
(130, 'PC44', '542', 'available', '2025-05-08 19:05:35'),
(131, 'PC04', '544', 'available', '2025-05-08 19:05:35'),
(132, 'PC14', '544', 'available', '2025-05-08 19:05:35'),
(133, 'PC24', '544', 'available', '2025-05-08 19:05:35'),
(134, 'PC34', '544', 'available', '2025-05-08 19:05:35'),
(135, 'PC44', '544', 'available', '2025-05-08 19:05:35'),
(136, 'PC04', '517', 'available', '2025-05-08 19:05:35'),
(137, 'PC14', '517', 'available', '2025-05-08 19:05:35'),
(138, 'PC24', '517', 'available', '2025-05-08 19:05:35'),
(139, 'PC34', '517', 'available', '2025-05-08 19:05:35'),
(140, 'PC44', '517', 'available', '2025-05-08 19:05:35'),
(141, 'PC05', '524', 'available', '2025-05-08 19:05:35'),
(142, 'PC15', '524', 'available', '2025-05-08 19:05:35'),
(143, 'PC25', '524', 'available', '2025-05-08 19:05:35'),
(144, 'PC35', '524', 'available', '2025-05-08 19:05:35'),
(145, 'PC45', '524', 'available', '2025-05-08 19:05:35'),
(146, 'PC05', '526', 'available', '2025-05-08 19:05:35'),
(147, 'PC15', '526', 'available', '2025-05-08 19:05:35'),
(148, 'PC25', '526', 'available', '2025-05-08 19:05:35'),
(149, 'PC35', '526', 'available', '2025-05-08 19:05:35'),
(150, 'PC45', '526', 'available', '2025-05-08 19:05:35'),
(151, 'PC05', '528', 'available', '2025-05-08 19:05:35'),
(152, 'PC15', '528', 'available', '2025-05-08 19:05:35'),
(153, 'PC25', '528', 'available', '2025-05-08 19:05:35'),
(154, 'PC35', '528', 'available', '2025-05-08 19:05:35'),
(155, 'PC45', '528', 'available', '2025-05-08 19:05:35'),
(156, 'PC05', '530', 'available', '2025-05-08 19:05:35'),
(157, 'PC15', '530', 'available', '2025-05-08 19:05:35'),
(158, 'PC25', '530', 'available', '2025-05-08 19:05:35'),
(159, 'PC35', '530', 'available', '2025-05-08 19:05:35'),
(160, 'PC45', '530', 'available', '2025-05-08 19:05:35'),
(161, 'PC05', '542', 'available', '2025-05-08 19:05:35'),
(162, 'PC15', '542', 'available', '2025-05-08 19:05:35'),
(163, 'PC25', '542', 'available', '2025-05-08 19:05:35'),
(164, 'PC35', '542', 'available', '2025-05-08 19:05:35'),
(165, 'PC45', '542', 'available', '2025-05-08 19:05:35'),
(166, 'PC05', '544', 'available', '2025-05-08 19:05:35'),
(167, 'PC15', '544', 'available', '2025-05-08 19:05:35'),
(168, 'PC25', '544', 'available', '2025-05-08 19:05:35'),
(169, 'PC35', '544', 'available', '2025-05-08 19:05:35'),
(170, 'PC45', '544', 'available', '2025-05-08 19:05:35'),
(171, 'PC05', '517', 'available', '2025-05-08 19:05:35'),
(172, 'PC15', '517', 'available', '2025-05-08 19:05:35'),
(173, 'PC25', '517', 'available', '2025-05-08 19:05:35'),
(174, 'PC35', '517', 'available', '2025-05-08 19:05:35'),
(175, 'PC45', '517', 'available', '2025-05-08 19:05:35'),
(176, 'PC06', '524', 'available', '2025-05-08 19:05:35'),
(177, 'PC16', '524', 'available', '2025-05-08 19:05:35'),
(178, 'PC26', '524', 'available', '2025-05-08 19:05:35'),
(179, 'PC36', '524', 'available', '2025-05-08 19:05:35'),
(180, 'PC46', '524', 'available', '2025-05-08 19:05:35'),
(181, 'PC06', '526', 'available', '2025-05-08 19:05:35'),
(182, 'PC16', '526', 'available', '2025-05-08 19:05:35'),
(183, 'PC26', '526', 'available', '2025-05-08 19:05:35'),
(184, 'PC36', '526', 'available', '2025-05-08 19:05:35'),
(185, 'PC46', '526', 'available', '2025-05-08 19:05:35'),
(186, 'PC06', '528', 'available', '2025-05-08 19:05:35'),
(187, 'PC16', '528', 'available', '2025-05-08 19:05:35'),
(188, 'PC26', '528', 'available', '2025-05-08 19:05:35'),
(189, 'PC36', '528', 'available', '2025-05-08 19:05:35'),
(190, 'PC46', '528', 'available', '2025-05-08 19:05:35'),
(191, 'PC06', '530', 'available', '2025-05-08 19:05:35'),
(192, 'PC16', '530', 'available', '2025-05-08 19:05:35'),
(193, 'PC26', '530', 'available', '2025-05-08 19:05:35'),
(194, 'PC36', '530', 'available', '2025-05-08 19:05:35'),
(195, 'PC46', '530', 'available', '2025-05-08 19:05:35'),
(196, 'PC06', '542', 'available', '2025-05-08 19:05:35'),
(197, 'PC16', '542', 'available', '2025-05-08 19:05:35'),
(198, 'PC26', '542', 'available', '2025-05-08 19:05:35'),
(199, 'PC36', '542', 'available', '2025-05-08 19:05:35'),
(200, 'PC46', '542', 'available', '2025-05-08 19:05:35'),
(201, 'PC06', '544', 'available', '2025-05-08 19:05:35'),
(202, 'PC16', '544', 'available', '2025-05-08 19:05:35'),
(203, 'PC26', '544', 'available', '2025-05-08 19:05:35'),
(204, 'PC36', '544', 'available', '2025-05-08 19:05:35'),
(205, 'PC46', '544', 'available', '2025-05-08 19:05:35'),
(206, 'PC06', '517', 'available', '2025-05-08 19:05:35'),
(207, 'PC16', '517', 'available', '2025-05-08 19:05:35'),
(208, 'PC26', '517', 'available', '2025-05-08 19:05:35'),
(209, 'PC36', '517', 'available', '2025-05-08 19:05:35'),
(210, 'PC46', '517', 'available', '2025-05-08 19:05:35'),
(211, 'PC07', '524', 'available', '2025-05-08 19:05:35'),
(212, 'PC17', '524', 'available', '2025-05-08 19:05:35'),
(213, 'PC27', '524', 'available', '2025-05-08 19:05:35'),
(214, 'PC37', '524', 'available', '2025-05-08 19:05:35'),
(215, 'PC47', '524', 'available', '2025-05-08 19:05:35'),
(216, 'PC07', '526', 'available', '2025-05-08 19:05:35'),
(217, 'PC17', '526', 'available', '2025-05-08 19:05:35'),
(218, 'PC27', '526', 'available', '2025-05-08 19:05:35'),
(219, 'PC37', '526', 'available', '2025-05-08 19:05:35'),
(220, 'PC47', '526', 'available', '2025-05-08 19:05:35'),
(221, 'PC07', '528', 'available', '2025-05-08 19:05:35'),
(222, 'PC17', '528', 'available', '2025-05-08 19:05:35'),
(223, 'PC27', '528', 'available', '2025-05-08 19:05:35'),
(224, 'PC37', '528', 'available', '2025-05-08 19:05:35'),
(225, 'PC47', '528', 'available', '2025-05-08 19:05:35'),
(226, 'PC07', '530', 'available', '2025-05-08 19:05:35'),
(227, 'PC17', '530', 'available', '2025-05-08 19:05:35'),
(228, 'PC27', '530', 'available', '2025-05-08 19:05:35'),
(229, 'PC37', '530', 'available', '2025-05-08 19:05:35'),
(230, 'PC47', '530', 'available', '2025-05-08 19:05:35'),
(231, 'PC07', '542', 'available', '2025-05-08 19:05:35'),
(232, 'PC17', '542', 'available', '2025-05-08 19:05:35'),
(233, 'PC27', '542', 'available', '2025-05-08 19:05:35'),
(234, 'PC37', '542', 'available', '2025-05-08 19:05:35'),
(235, 'PC47', '542', 'available', '2025-05-08 19:05:35'),
(236, 'PC07', '544', 'available', '2025-05-08 19:05:35'),
(237, 'PC17', '544', 'available', '2025-05-08 19:05:35'),
(238, 'PC27', '544', 'available', '2025-05-08 19:05:35'),
(239, 'PC37', '544', 'available', '2025-05-08 19:05:35'),
(240, 'PC47', '544', 'available', '2025-05-08 19:05:35'),
(241, 'PC07', '517', 'available', '2025-05-08 19:05:35'),
(242, 'PC17', '517', 'available', '2025-05-08 19:05:35'),
(243, 'PC27', '517', 'available', '2025-05-08 19:05:35'),
(244, 'PC37', '517', 'available', '2025-05-08 19:05:35'),
(245, 'PC47', '517', 'available', '2025-05-08 19:05:35'),
(246, 'PC08', '524', 'available', '2025-05-08 19:05:35'),
(247, 'PC18', '524', 'available', '2025-05-08 19:05:35'),
(248, 'PC28', '524', 'available', '2025-05-08 19:05:35'),
(249, 'PC38', '524', 'available', '2025-05-08 19:05:35'),
(250, 'PC48', '524', 'available', '2025-05-08 19:05:35'),
(251, 'PC08', '526', 'available', '2025-05-08 19:05:35'),
(252, 'PC18', '526', 'available', '2025-05-08 19:05:35'),
(253, 'PC28', '526', 'available', '2025-05-08 19:05:35'),
(254, 'PC38', '526', 'available', '2025-05-08 19:05:35'),
(255, 'PC48', '526', 'available', '2025-05-08 19:05:35'),
(256, 'PC08', '528', 'available', '2025-05-08 19:05:35'),
(257, 'PC18', '528', 'available', '2025-05-08 19:05:35'),
(258, 'PC28', '528', 'available', '2025-05-08 19:05:35'),
(259, 'PC38', '528', 'available', '2025-05-08 19:05:35'),
(260, 'PC48', '528', 'available', '2025-05-08 19:05:35'),
(261, 'PC08', '530', 'available', '2025-05-08 19:05:35'),
(262, 'PC18', '530', 'available', '2025-05-08 19:05:35'),
(263, 'PC28', '530', 'available', '2025-05-08 19:05:35'),
(264, 'PC38', '530', 'available', '2025-05-08 19:05:35'),
(265, 'PC48', '530', 'available', '2025-05-08 19:05:35'),
(266, 'PC08', '542', 'available', '2025-05-08 19:05:35'),
(267, 'PC18', '542', 'available', '2025-05-08 19:05:35'),
(268, 'PC28', '542', 'available', '2025-05-08 19:05:35'),
(269, 'PC38', '542', 'available', '2025-05-08 19:05:35'),
(270, 'PC48', '542', 'available', '2025-05-08 19:05:35'),
(271, 'PC08', '544', 'available', '2025-05-08 19:05:35'),
(272, 'PC18', '544', 'available', '2025-05-08 19:05:35'),
(273, 'PC28', '544', 'available', '2025-05-08 19:05:35'),
(274, 'PC38', '544', 'available', '2025-05-08 19:05:35'),
(275, 'PC48', '544', 'available', '2025-05-08 19:05:35'),
(276, 'PC08', '517', 'available', '2025-05-08 19:05:35'),
(277, 'PC18', '517', 'available', '2025-05-08 19:05:35'),
(278, 'PC28', '517', 'available', '2025-05-08 19:05:35'),
(279, 'PC38', '517', 'available', '2025-05-08 19:05:35'),
(280, 'PC48', '517', 'available', '2025-05-08 19:05:35'),
(281, 'PC09', '524', 'available', '2025-05-08 19:05:35'),
(282, 'PC19', '524', 'available', '2025-05-08 19:05:35'),
(283, 'PC29', '524', 'available', '2025-05-08 19:05:35'),
(284, 'PC39', '524', 'available', '2025-05-08 19:05:35'),
(285, 'PC49', '524', 'available', '2025-05-08 19:05:35'),
(286, 'PC09', '526', 'available', '2025-05-08 19:05:35'),
(287, 'PC19', '526', 'available', '2025-05-08 19:05:35'),
(288, 'PC29', '526', 'available', '2025-05-08 19:05:35'),
(289, 'PC39', '526', 'available', '2025-05-08 19:05:35'),
(290, 'PC49', '526', 'available', '2025-05-08 19:05:35'),
(291, 'PC09', '528', 'available', '2025-05-08 19:05:35'),
(292, 'PC19', '528', 'available', '2025-05-08 19:05:35'),
(293, 'PC29', '528', 'available', '2025-05-08 19:05:35'),
(294, 'PC39', '528', 'available', '2025-05-08 19:05:35'),
(295, 'PC49', '528', 'available', '2025-05-08 19:05:35'),
(296, 'PC09', '530', 'available', '2025-05-08 19:05:35'),
(297, 'PC19', '530', 'available', '2025-05-08 19:05:35'),
(298, 'PC29', '530', 'available', '2025-05-08 19:05:35'),
(299, 'PC39', '530', 'available', '2025-05-08 19:05:35'),
(300, 'PC49', '530', 'available', '2025-05-08 19:05:35'),
(301, 'PC09', '542', 'available', '2025-05-08 19:05:35'),
(302, 'PC19', '542', 'available', '2025-05-08 19:05:35'),
(303, 'PC29', '542', 'available', '2025-05-08 19:05:35'),
(304, 'PC39', '542', 'available', '2025-05-08 19:05:35'),
(305, 'PC49', '542', 'available', '2025-05-08 19:05:35'),
(306, 'PC09', '544', 'available', '2025-05-08 19:05:35'),
(307, 'PC19', '544', 'available', '2025-05-08 19:05:35'),
(308, 'PC29', '544', 'available', '2025-05-08 19:05:35'),
(309, 'PC39', '544', 'available', '2025-05-08 19:05:35'),
(310, 'PC49', '544', 'available', '2025-05-08 19:05:35'),
(311, 'PC09', '517', 'available', '2025-05-08 19:05:35'),
(312, 'PC19', '517', 'available', '2025-05-08 19:05:35'),
(313, 'PC29', '517', 'available', '2025-05-08 19:05:35'),
(314, 'PC39', '517', 'available', '2025-05-08 19:05:35'),
(315, 'PC49', '517', 'available', '2025-05-08 19:05:35'),
(316, 'PC10', '524', 'available', '2025-05-08 19:05:35'),
(317, 'PC20', '524', 'available', '2025-05-08 19:05:35'),
(318, 'PC30', '524', 'available', '2025-05-08 19:05:35'),
(319, 'PC40', '524', 'available', '2025-05-08 19:05:35'),
(320, 'PC50', '524', 'available', '2025-05-08 19:05:35'),
(321, 'PC10', '526', 'available', '2025-05-08 19:05:35'),
(322, 'PC20', '526', 'available', '2025-05-08 19:05:35'),
(323, 'PC30', '526', 'available', '2025-05-08 19:05:35'),
(324, 'PC40', '526', 'available', '2025-05-08 19:05:35'),
(325, 'PC50', '526', 'available', '2025-05-08 19:05:35'),
(326, 'PC10', '528', 'available', '2025-05-08 19:05:35'),
(327, 'PC20', '528', 'available', '2025-05-08 19:05:35'),
(328, 'PC30', '528', 'available', '2025-05-08 19:05:35'),
(329, 'PC40', '528', 'available', '2025-05-08 19:05:35'),
(330, 'PC50', '528', 'available', '2025-05-08 19:05:35'),
(331, 'PC10', '530', 'available', '2025-05-08 19:05:35'),
(332, 'PC20', '530', 'available', '2025-05-08 19:05:35'),
(333, 'PC30', '530', 'available', '2025-05-08 19:05:35'),
(334, 'PC40', '530', 'available', '2025-05-08 19:05:35'),
(335, 'PC50', '530', 'available', '2025-05-08 19:05:35'),
(336, 'PC10', '542', 'available', '2025-05-08 19:05:35'),
(337, 'PC20', '542', 'available', '2025-05-08 19:05:35'),
(338, 'PC30', '542', 'available', '2025-05-08 19:05:35'),
(339, 'PC40', '542', 'available', '2025-05-08 19:05:35'),
(340, 'PC50', '542', 'available', '2025-05-08 19:05:35'),
(341, 'PC10', '544', 'in_use', '2025-05-08 19:05:35'),
(342, 'PC20', '544', 'available', '2025-05-08 19:05:35'),
(343, 'PC30', '544', 'available', '2025-05-08 19:05:35'),
(344, 'PC40', '544', 'available', '2025-05-08 19:05:35'),
(345, 'PC50', '544', 'available', '2025-05-08 19:05:35'),
(346, 'PC10', '517', 'available', '2025-05-08 19:05:35'),
(347, 'PC20', '517', 'available', '2025-05-08 19:05:35'),
(348, 'PC30', '517', 'available', '2025-05-08 19:05:35'),
(349, 'PC40', '517', 'available', '2025-05-08 19:05:35'),
(350, 'PC50', '517', 'available', '2025-05-08 19:05:35'),
(0, 'PC01', '2', 'in_use', '2025-05-16 23:58:08');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `session_id`, `user_id`, `rating`, `comments`, `created_at`) VALUES
(1, 1, 1, NULL, 'Yeah', '2025-05-08 21:48:19'),
(2, 1, 1, NULL, 'Okay na', '2025-05-08 21:48:31'),
(3, 2, 3, NULL, 'Okay Na', '2025-05-08 21:48:51');

-- --------------------------------------------------------

--
-- Table structure for table `lab_rooms`
--

CREATE TABLE `lab_rooms` (
  `id` int(11) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `total_computers` int(11) NOT NULL DEFAULT 50,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_rooms`
--

INSERT INTO `lab_rooms` (`id`, `room_number`, `total_computers`, `status`, `created_at`) VALUES
(1, '524', 50, 'active', '2025-05-08 16:45:53'),
(2, '526', 50, 'active', '2025-05-08 16:45:53'),
(3, '528', 50, 'active', '2025-05-08 16:45:53'),
(4, '530', 50, 'active', '2025-05-08 16:45:53'),
(5, '542', 50, 'active', '2025-05-08 16:45:53'),
(6, '544', 50, 'active', '2025-05-08 16:45:53'),
(7, '517', 50, 'active', '2025-05-08 16:45:53');

-- --------------------------------------------------------

--
-- Table structure for table `lab_schedules`
--

CREATE TABLE `lab_schedules` (
  `schedule_id` int(11) NOT NULL,
  `lab_room` varchar(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `schedule_image` varchar(255) NOT NULL,
  `uploaded_by` varchar(100) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_schedules`
--

INSERT INTO `lab_schedules` (`schedule_id`, `lab_room`, `title`, `description`, `schedule_image`, `uploaded_by`, `upload_date`) VALUES
(1, 'LAB 524', '524', '524', '1747296446_OIP.jpg', 'admin', '2025-05-15 08:07:26'),
(4, 'LAB 526', 'INFOSEC', '526', '1747452280_Screenshot 2025-05-16 145741.png', 'admin', '2025-05-17 03:24:40'),
(5, 'LAB 528', 'INTPROG', '528', '1747452335_Screenshot 2025-05-16 145741.png', 'admin', '2025-05-17 03:25:35'),
(6, 'LAB 530', 'SYSARC', '530', '1747452362_Screenshot 2025-05-16 145741.png', 'admin', '2025-05-17 03:26:02'),
(7, 'LAB 528', 'INTPROG', 'Leader', '1747457384_schedule_1747453282365.jpg', 'admin', '2025-05-17 04:49:44');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `ID_NUMBER` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` enum('reservation_approved','reservation_rejected','system') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `ID_NUMBER`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 22683320, 'Your lab reservation has been approved!', 'reservation_approved', 1, '2025-05-17 05:11:21'),
(2, 22683320, 'Your lab reservation has been approved!', 'reservation_approved', 1, '2025-05-17 05:20:55'),
(3, 11111111, 'Your lab reservation has been approved!', 'reservation_approved', 1, '2025-05-17 05:21:11'),
(4, 11111111, 'Your lab reservation has been approved!', 'reservation_approved', 1, '2025-05-17 05:36:47'),
(5, 11111111, 'Your lab reservation has been approved!', 'reservation_approved', 1, '2025-05-17 05:41:38'),
(6, 11111111, 'Your lab reservation has been approved!', 'reservation_approved', 1, '2025-05-17 05:51:11'),
(7, 11111111, 'Your lab reservation has been approved!', 'reservation_approved', 1, '2025-05-17 05:53:42'),
(8, 11111111, 'Your lab reservation has been approved!', 'reservation_approved', 1, '2025-05-17 06:47:15'),
(9, 22683320, 'Your lab reservation has been approved!', 'reservation_approved', 1, '2025-05-17 07:06:52'),
(10, 22683320, 'Your lab reservation has been approved!', 'reservation_approved', 1, '2025-05-17 07:08:14'),
(11, 22683320, 'Your lab reservation has been approved!', 'reservation_approved', 1, '2025-05-17 07:12:11'),
(12, 22683320, 'Your lab reservation has been approved!', 'reservation_approved', 1, '2025-05-17 07:14:53'),
(13, 22683320, 'Your lab reservation has been approved!', 'reservation_approved', 1, '2025-05-17 07:26:28'),
(14, 22683320, 'Your lab reservation has been approved!', 'reservation_approved', 0, '2025-05-17 07:32:40');

-- --------------------------------------------------------

--
-- Table structure for table `points`
--

CREATE TABLE `points` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `points_earned` int(11) NOT NULL,
  `points_reason` text NOT NULL,
  `awarded_by` varchar(50) NOT NULL,
  `awarded_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `converted_to_session` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `points`
--

INSERT INTO `points` (`id`, `student_id`, `points_earned`, `points_reason`, `awarded_by`, `awarded_date`, `converted_to_session`) VALUES
(1, 1, 1, 'None', 'admin', '2025-05-15 07:39:56', 1),
(2, 1, 2, 'nana', 'admin', '2025-05-15 07:40:08', 1),
(3, 2, 3, 'NIce', 'admin', '2025-05-16 23:40:29', 0),
(4, 1, 2, 'Nice\r\n', 'admin', '2025-05-16 23:41:02', 1),
(5, 1, 1, 'Good', 'admin', '2025-05-16 23:41:40', 1),
(6, 6, 2, 'Nice', 'admin', '2025-05-16 23:42:04', 0),
(7, 3, 1, 'Good', 'admin', '2025-05-16 23:42:18', 0),
(8, 2, 1, 'Good', 'admin', '2025-05-16 23:42:32', 0),
(9, 1, 3, 'But-an', 'admin', '2025-05-17 03:39:29', 1),
(10, 6, 1, 'Nice', 'admin', '2025-05-17 03:39:46', 0),
(11, 3, 1, 'Nice', 'admin', '2025-05-17 03:39:55', 0),
(12, 4, 1, 'Good', 'admin', '2025-05-17 03:40:08', 0),
(13, 1, 3, 'nice', 'admin', '2025-05-17 05:47:59', 1);

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `lab_room` varchar(10) NOT NULL,
  `pc_number` varchar(10) NOT NULL,
  `purpose` varchar(100) NOT NULL,
  `reservation_date` date NOT NULL,
  `time_in` time NOT NULL,
  `status` enum('pending','approved','disapproved') DEFAULT 'pending',
  `timeout_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `student_id`, `lab_room`, `pc_number`, `purpose`, `reservation_date`, `time_in`, `status`, `timeout_at`, `created_at`, `updated_at`) VALUES
(2, 26683320, '524', 'PC01', 'C', '2025-05-15', '10:30:00', 'approved', '2025-05-08 06:24:15', '2025-05-08 04:23:42', '2025-05-08 04:24:15'),
(3, 26683320, '524', 'PC02', 'C', '2025-05-13', '10:30:00', 'approved', '2025-05-08 06:31:27', '2025-05-08 04:30:42', '2025-05-08 04:31:27'),
(4, 26683320, '524', 'PC02', 'C', '2025-05-15', '10:30:00', 'approved', '2025-05-08 06:53:41', '2025-05-08 04:52:38', '2025-05-08 04:53:41'),
(0, 11111111, '526', 'PC01', 'Python', '2025-05-20', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-16 21:46:19', '2025-05-17 00:45:45'),
(0, 11111111, '526', 'PC01', 'Python', '2025-05-20', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-16 21:58:29', '2025-05-17 00:45:45'),
(0, 11111111, '530', 'PC01', 'PHP', '2025-05-21', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-16 22:04:06', '2025-05-17 00:45:45'),
(0, 22683320, '530', 'PC02', 'C#', '2025-05-21', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-16 22:04:42', '2025-05-17 00:45:45'),
(0, 22683320, '524', 'PC01', 'PHP', '2025-05-20', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-16 22:32:48', '2025-05-17 00:45:45'),
(0, 22683320, '526', 'PC01', 'Python', '2025-05-23', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-16 22:51:01', '2025-05-17 00:45:45'),
(0, 22683320, '530', 'PC01', 'C', '2025-05-22', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-16 23:00:46', '2025-05-17 00:45:45'),
(0, 22683320, '524', 'PC01', 'C#', '2025-05-19', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-16 23:25:52', '2025-05-17 00:45:45'),
(0, 22683320, '524', 'PC01', 'Web Design & Development', '2025-05-26', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-16 23:31:20', '2025-05-17 00:45:45'),
(0, 33333333, '524', 'PC01', 'Computer Application', '2025-05-19', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-16 23:37:38', '2025-05-17 00:45:45'),
(0, 33333333, '524', 'PC02', 'Web Design & Development', '2025-05-19', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-16 23:44:11', '2025-05-17 00:45:45'),
(0, 33333333, '528', 'PC01', 'Digital Logic & Design', '2025-05-19', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-16 23:46:06', '2025-05-17 00:45:45'),
(0, 22683320, '544', 'PC03', 'Computer Application', '2025-05-19', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-16 23:47:31', '2025-05-17 00:45:45'),
(0, 22683320, '530', 'PC04', 'Computer Application', '2025-05-20', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-17 00:04:51', '2025-05-17 00:45:45'),
(0, 11111111, '517', 'PC02', 'Database', '2025-05-20', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-17 00:13:04', '2025-05-17 00:45:45'),
(0, 11111111, '517', 'PC05', 'ASP.Net', '2025-05-21', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-17 00:19:37', '2025-05-17 00:45:45'),
(0, 22683320, '530', 'PC05', 'Java', '2025-05-21', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-17 00:20:07', '2025-05-17 00:45:45'),
(0, 22683320, '524', 'PC06', 'C', '2025-05-20', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-17 00:24:41', '2025-05-17 00:45:45'),
(0, 22683320, '530', 'PC06', 'Java', '2025-05-21', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-17 00:31:47', '2025-05-17 00:45:45'),
(0, 11111111, '517', 'PC06', 'ASP.Net', '2025-05-19', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-17 00:36:27', '2025-05-17 00:45:45'),
(0, 22683320, '544', 'PC10', 'Web Design & Development', '2025-05-23', '10:30:00', 'approved', '2025-05-17 02:45:45', '2025-05-17 00:41:22', '2025-05-17 00:45:45'),
(0, 22683320, '542', 'PC01', 'Computer Application', '2025-05-19', '10:30:00', 'approved', '2025-05-17 02:52:12', '2025-05-17 00:46:15', '2025-05-17 00:52:12'),
(0, 11111111, '542', 'PC02', 'Computer Application', '2025-05-20', '10:30:00', 'approved', '2025-05-17 02:52:08', '2025-05-17 00:46:42', '2025-05-17 00:52:08'),
(0, 11111111, '530', 'PC10', 'Computer Application', '2025-05-27', '10:30:00', 'approved', '2025-05-17 02:57:20', '2025-05-17 00:55:22', '2025-05-17 00:57:20'),
(0, 22683320, '526', 'PC10', 'Computer Application', '2025-05-19', '10:30:00', 'approved', '2025-05-17 02:57:22', '2025-05-17 00:55:46', '2025-05-17 00:57:22'),
(0, 22683320, '524', 'PC06', 'Database', '2025-05-21', '10:33:00', 'approved', '2025-05-17 02:59:50', '2025-05-17 00:58:25', '2025-05-17 00:59:50'),
(0, 11111111, '530', 'PC07', 'Python', '2025-05-21', '10:30:00', 'approved', '2025-05-17 02:59:41', '2025-05-17 00:59:03', '2025-05-17 00:59:41'),
(0, 222222222, '524', 'PC01', 'Python', '2025-05-27', '10:30:00', 'approved', '2025-05-17 04:42:11', '2025-05-17 02:41:53', '2025-05-17 02:42:11'),
(0, 222222222, '517', 'PC01', 'Digital Logic & Design', '2025-05-19', '10:30:00', 'approved', '2025-05-17 04:50:42', '2025-05-17 02:50:23', '2025-05-17 02:50:42'),
(0, 222222222, '530', 'PC01', 'Digital Logic & Design', '2025-05-19', '10:30:00', 'approved', '2025-05-17 05:54:39', '2025-05-17 03:53:21', '2025-05-17 03:54:39'),
(0, 222222222, '517', 'PC01', 'Database', '2025-05-19', '10:30:00', 'approved', '2025-05-17 05:56:40', '2025-05-17 03:55:39', '2025-05-17 03:56:40'),
(0, 222222222, '517', 'PC01', 'C', '2025-05-19', '10:30:00', 'approved', '2025-05-17 05:58:22', '2025-05-17 03:58:01', '2025-05-17 03:58:22'),
(0, 22683320, '524', 'PC03', 'Database', '2025-05-19', '10:30:00', 'approved', '2025-05-17 06:51:02', '2025-05-17 04:50:31', '2025-05-17 04:51:02'),
(0, 22683320, '517', 'PC06', 'Computer Application', '2025-05-20', '10:30:00', 'approved', '2025-05-17 07:10:02', '2025-05-17 04:58:43', '2025-05-17 05:10:02'),
(0, 22683320, '524', 'PC01', 'Computer Application', '2025-05-19', '10:30:00', 'approved', '2025-05-17 07:19:01', '2025-05-17 05:11:09', '2025-05-17 05:19:01'),
(0, 22683320, '524', 'PC01', 'C#', '2025-05-19', '10:30:00', 'approved', '2025-05-17 07:29:19', '2025-05-17 05:19:25', '2025-05-17 05:29:19'),
(0, 11111111, '517', 'PC01', 'Computer Application', '2025-05-19', '10:30:00', 'approved', '2025-05-17 07:29:18', '2025-05-17 05:20:27', '2025-05-17 05:29:18'),
(0, 11111111, '524', 'PC01', 'C', '2025-05-19', '10:30:00', 'approved', '2025-05-17 07:38:20', '2025-05-17 05:29:37', '2025-05-17 05:38:20'),
(0, 11111111, '524', 'PC01', 'Computer Application', '2025-05-19', '10:30:00', 'approved', '2025-05-17 07:47:01', '2025-05-17 05:41:22', '2025-05-17 05:47:01'),
(0, 11111111, '524', 'PC05', 'Computer Application', '2025-05-20', '10:30:00', 'approved', '2025-05-17 07:53:09', '2025-05-17 05:50:57', '2025-05-17 05:53:09'),
(0, 11111111, '524', 'PC01', 'Computer Application', '2025-05-19', '10:30:00', 'approved', '2025-05-17 08:41:00', '2025-05-17 05:53:27', '2025-05-17 06:41:00'),
(0, 11111111, '517', 'PC01', 'Database', '2025-05-19', '10:30:00', 'approved', '2025-05-17 08:48:13', '2025-05-17 06:41:20', '2025-05-17 06:48:13'),
(0, 22683320, '524', 'PC01', 'ASP.Net', '2025-05-19', '10:30:00', 'approved', '2025-05-17 09:06:57', '2025-05-17 06:52:13', '2025-05-17 07:06:57'),
(0, 22683320, '524', 'PC01', 'Python', '2025-05-19', '10:30:00', 'approved', '2025-05-17 09:09:59', '2025-05-17 07:07:29', '2025-05-17 07:09:59'),
(0, 22683320, '524', 'PC01', 'ASP.Net', '2025-05-19', '10:30:00', 'approved', '2025-05-17 09:12:25', '2025-05-17 07:10:49', '2025-05-17 07:12:25'),
(0, 22683320, '517', 'PC01', 'Web Design & Development', '2025-05-19', '10:30:00', 'approved', '2025-05-17 09:15:01', '2025-05-17 07:13:00', '2025-05-17 07:15:01'),
(0, 22683320, '524', 'PC01', 'Web Design & Development', '2025-05-19', '13:00:00', 'approved', '2025-05-17 09:26:46', '2025-05-17 07:26:13', '2025-05-17 07:26:46'),
(0, 22683320, '517', 'PC01', 'Computer Application', '2025-05-19', '10:30:00', 'approved', '2025-05-17 09:33:02', '2025-05-17 07:32:31', '2025-05-17 07:33:02');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `resource_type` enum('document','video','link') NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `year_level` enum('1st Year','2nd Year','3rd Year','4th Year') NOT NULL,
  `course` varchar(50) NOT NULL,
  `uploaded_by` varchar(50) NOT NULL,
  `upload_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `title`, `description`, `resource_type`, `file_path`, `link_url`, `year_level`, `course`, `uploaded_by`, `upload_date`) VALUES
(2, 'IT', 'Knowledge', 'link', NULL, 'https://www.w3schools.com/java/default.asp', '1st Year', 'BSIT', 'admin', '2025-05-15 15:08:41'),
(3, 'Java Syntax', 'W3school Java Syntax Practices', 'link', NULL, 'https://www.w3schools.com/java/java_syntax.asp', '1st Year', 'BSIT', 'admin', '2025-05-16 15:13:57'),
(4, 'Python', 'Python Syntax', 'link', NULL, 'https://www.w3schools.com/python/python_syntax.asp', '1st Year', 'BSIT', 'admin', '2025-05-16 16:48:55');

-- --------------------------------------------------------

--
-- Table structure for table `sit_in_sessions`
--

CREATE TABLE `sit_in_sessions` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `lab_room` varchar(50) NOT NULL,
  `purpose` varchar(100) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `status` enum('active','completed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sit_in_sessions`
--

INSERT INTO `sit_in_sessions` (`id`, `student_id`, `reservation_id`, `lab_room`, `purpose`, `start_time`, `end_time`, `status`, `created_at`) VALUES
(1, 26683320, NULL, '524', 'C', '2025-05-08 16:41:41', '2025-05-08 16:42:33', 'completed', '2025-05-08 14:41:41'),
(2, 11111111, NULL, '524', 'C', '2025-05-08 21:54:45', '2025-05-08 22:11:34', 'completed', '2025-05-08 19:54:45'),
(3, 26683320, NULL, '530', 'C', '2025-05-08 21:59:39', '2025-05-08 22:11:34', 'completed', '2025-05-08 19:59:39'),
(4, 26683320, NULL, '524', 'C', '2025-05-08 22:14:01', '2025-05-08 22:22:11', 'completed', '2025-05-08 20:14:01'),
(5, 26683320, NULL, 'MAC', 'ASP.Net', '2025-05-08 22:23:02', '2025-05-08 22:29:59', 'completed', '2025-05-08 20:23:02'),
(6, 26683320, NULL, 'MAC', 'C', '2025-05-08 22:33:11', '2025-05-08 22:45:38', 'completed', '2025-05-08 20:33:11'),
(7, 26683320, NULL, '524', 'C', '2025-05-08 22:55:28', '2025-05-08 23:10:44', 'completed', '2025-05-08 20:55:28'),
(8, 26683320, NULL, '547', 'C', '2025-05-08 23:13:37', '2025-05-08 23:28:11', 'completed', '2025-05-08 21:13:37'),
(0, 22683320, NULL, '530', 'PHP', '2025-05-15 09:39:29', '2025-05-17 02:52:15', 'completed', '2025-05-15 07:39:29'),
(0, 22683320, NULL, '524', 'Java', '2025-05-16 23:45:14', '2025-05-17 02:52:15', 'completed', '2025-05-16 21:45:14'),
(0, 222222222, NULL, '530', 'C#', '2025-05-16 23:47:22', '2025-05-17 02:52:15', 'completed', '2025-05-16 21:47:22'),
(0, 22683320, NULL, '547', 'PHP', '2025-05-16 23:55:05', '2025-05-17 02:52:15', 'completed', '2025-05-16 21:55:05'),
(0, 11111111, NULL, '530', 'PHP', '2025-05-16 23:55:23', '2025-05-17 02:52:15', 'completed', '2025-05-16 21:55:23'),
(0, 22683320, NULL, '530', 'Python', '2025-05-16 23:57:18', '2025-05-17 02:52:15', 'completed', '2025-05-16 21:57:18'),
(0, 222222222, NULL, '528', 'Python', '2025-05-16 23:59:27', '2025-05-17 02:52:15', 'completed', '2025-05-16 21:59:27'),
(0, 33333333, NULL, '524', 'PHP', '2025-05-16 23:59:45', '2025-05-17 02:52:15', 'completed', '2025-05-16 21:59:45'),
(0, 33333333, NULL, '526', 'Python', '2025-05-17 00:02:49', '2025-05-17 02:52:15', 'completed', '2025-05-16 22:02:49'),
(0, 44444444, NULL, '524', 'Python', '2025-05-17 00:03:05', '2025-05-17 02:52:15', 'completed', '2025-05-16 22:03:05'),
(0, 33333333, NULL, '524', 'C', '2025-05-17 00:06:16', '2025-05-17 02:52:15', 'completed', '2025-05-16 22:06:16'),
(0, 55555555, NULL, '526', 'C', '2025-05-17 00:06:35', '2025-05-17 02:52:15', 'completed', '2025-05-16 22:06:35'),
(0, 33333333, NULL, '526', 'Python', '2025-05-17 00:09:46', '2025-05-17 02:52:15', 'completed', '2025-05-16 22:09:46'),
(0, 55555555, NULL, '524', 'Python', '2025-05-17 00:10:05', '2025-05-17 02:52:15', 'completed', '2025-05-16 22:10:05'),
(0, 33333333, NULL, '524', 'PHP', '2025-05-17 00:15:33', '2025-05-17 02:52:15', 'completed', '2025-05-16 22:15:33'),
(0, 222222222, NULL, '526', 'PHP', '2025-05-17 00:15:48', '2025-05-17 02:52:15', 'completed', '2025-05-16 22:15:48'),
(0, 44444444, NULL, '528', 'Database', '2025-05-17 02:50:03', '2025-05-17 02:52:15', 'completed', '2025-05-17 00:50:03'),
(0, 222222222, NULL, '530', 'Computer Application', '2025-05-17 02:50:48', '2025-05-17 02:52:15', 'completed', '2025-05-17 00:50:48'),
(0, 44444444, NULL, '526', 'Computer Application', '2025-05-17 02:53:44', '2025-05-17 02:53:58', 'completed', '2025-05-17 00:53:44'),
(0, 55555555, NULL, '526', 'System Integration & Architecture', '2025-05-17 02:53:56', '2025-05-17 02:54:00', 'completed', '2025-05-17 00:53:56'),
(0, 44444444, NULL, '526', 'Computer Application', '2025-05-17 04:39:39', '2025-05-17 04:39:44', 'completed', '2025-05-17 02:39:39'),
(0, 44444444, NULL, '524', 'C', '2025-05-17 04:40:32', '2025-05-17 04:40:34', 'completed', '2025-05-17 02:40:32'),
(0, 55555555, NULL, '524', 'Database', '2025-05-17 06:07:33', '2025-05-17 06:11:43', 'completed', '2025-05-17 04:07:33'),
(0, 33333333, NULL, '524', 'Digital Logic & Design', '2025-05-17 06:09:11', '2025-05-17 06:09:18', 'completed', '2025-05-17 04:09:11');

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
  `user_type` enum('student','admin') NOT NULL DEFAULT 'student',
  `POINTS` int(11) DEFAULT 0,
  `current_points` int(11) DEFAULT 0,
  `total_points_earned` int(11) DEFAULT 0,
  `sessions_earned` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `ID_NUMBER`, `LASTNAME`, `FIRSTNAME`, `MIDDLENAME`, `COURSE`, `YEAR`, `USERNAME`, `PASSWORD`, `EMAIL`, `ADDRESS`, `SESSION`, `IMAGE`, `CREATED_AT`, `user_type`, `POINTS`, `current_points`, `total_points_earned`, `sessions_earned`) VALUES
(1, 22683320, 'Monreal', 'Jeff ', 'Ranido', 'BSIT', 3, 'j123', '$2y$10$nBxx8z1OXvFdLpUmzK8bl...HWuPZAOn33Au8sYjkG9PM4FqXY5le', 'jeff@gmail.com', 'Cebu City', 9, NULL, '2025-05-15 03:33:53', 'student', 0, 3, 12, 3),
(2, 11111111, 'Sagaral', 'Alexus', '', 'BSIT', 3, 'alex123', '$2y$10$R8LUTMfnppkVlbtnX438sOjWyg0Qr2ijW1pwu6VFcHln..4CkBok.', 'alex@gmail.com', 'Cebu City', 15, NULL, '2025-05-15 03:34:37', 'student', 0, 4, 4, 0),
(3, 222222222, 'Catubig', 'Mark', '', 'BSIT', 3, 'mark123', '$2y$10$HDVDfCzPpyNMCGaUfqf/ZOZwMyM9f5/qZyMRZv0ies978wDFdp98K', 'mark@gmail.com', 'Cebu City', 22, NULL, '2025-05-15 03:35:13', 'student', 0, 2, 2, 0),
(4, 33333333, 'Palacio', 'Real Jhon', '', 'BSIT', 3, 'real123', '$2y$10$beFM5LFnkLBPBcjJPXEDRuWPyYVNule9/.Klt5xhZmIgW8dVf99j6', 'real@gmail.com', 'Cebu City', 26, NULL, '2025-05-15 03:35:55', 'student', 0, 1, 1, 0),
(5, 44444444, 'Paraiso', 'Justine', '', 'BSIT', 3, 'just123', '$2y$10$lVHQY7ubKfbWxTfvjSb.FOi01kwy5fq4POMiJuvG/JiQsL1nRNjCm', 'just@gmail.com', 'Cebu City', 26, NULL, '2025-05-15 03:36:27', 'student', 0, 0, 0, 0),
(6, 55555555, 'Cabunilas', 'Vince', '', 'BSIT', 3, 'vince123', '$2y$10$eB2QC.ZjYT.c8GDNeO.D7OaT6ANNRlID6GoAqx1NFgS3mbOL2IUxm', 'vince@gmail.com', 'Cebu City', 26, NULL, '2025-05-15 03:37:13', 'student', 0, 3, 3, 0),
(10, 0, 'CSS', 'Admin', NULL, NULL, NULL, 'admin', '$2y$10$UEU1A58wuix.i8aaDVSd7.XjyheTwDnG0DvBTpwLTe.6YAKIFVNNS', 'admin@gmail.com', NULL, 30, NULL, '2025-05-15 04:15:08', 'admin', 0, 0, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lab_schedules`
--
ALTER TABLE `lab_schedules`
  ADD PRIMARY KEY (`schedule_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_fk` (`ID_NUMBER`);

--
-- Indexes for table `points`
--
ALTER TABLE `points`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sit_in_sessions`
--
ALTER TABLE `sit_in_sessions`
  ADD KEY `idx_reservation_id` (`reservation_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `unique_id_number` (`ID_NUMBER`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lab_schedules`
--
ALTER TABLE `lab_schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `points`
--
ALTER TABLE `points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_fk` FOREIGN KEY (`ID_NUMBER`) REFERENCES `users` (`ID_NUMBER`) ON DELETE CASCADE;

--
-- Constraints for table `points`
--
ALTER TABLE `points`
  ADD CONSTRAINT `points_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
