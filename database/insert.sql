INSERT INTO `users` (`ID`, `ID_NUMBER`, `LASTNAME`, `FIRSTNAME`, `MIDDLENAME`, `COURSE`, `YEAR`, `USERNAME`, `PASSWORD`, `EMAIL`, `ADDRESS`, `SESSION`, `IMAGE`, `CREATED_AT`, `user_type`) VALUES
(0, 0, 'CSS', 'Admin', NULL, NULL, NULL, 'admin', '$2y$10$rKXnBT9B.Qd3dNh0oqHdzupRQdRcZ6pxALhKQo9GzSvYirqLOZB1y', '', NULL, 30, NULL, '2025-03-20 04:01:31', 'admin'),
(2, 22683320, 'Monreal', 'Jeff', 'Ranido', 'BSIT', 3, 'j123', '$2y$10$SBn6TczoE3LocgsOt8/8ieo0Z5BdREuO89gwr6xuMFSP.KLy6qc2C', 'monrealjeff2@gmail.com', 'Guadalupe Cebu City', 30, 'uploads/jeff.png', '2025-03-20 04:01:47', 'student'),
(3, 11111111, 'Moon', 'Jay', 'Sundae', 'BSIT', 3, 'jay123', '$2y$10$Ap9zWDWsUMv6Ubshx3UqguRHK2SLUSowVCCIGMX3JU/aVjC6fA/im', 'jay123@gmail.com', 'Guadalupe Cebu City', 30, NULL, '2025-03-20 04:03:41', 'student'),
(4, 22222222, 'Catubig', 'Mark Dave', '', 'BSIT', 3, 'mark123', '$2y$10$b56LhAnz4t2hN1PHdFyt..WYeif4ezSN8EpkgMELA2IqDjoZgqGP6', 'mark123@gmail.com', 'Guadalupe Cebu City', 30, NULL, '2025-03-20 04:16:30', 'student'),
(5, 33333333, 'Sagaral', 'Alexus', 'Sundae', 'BSIT', 3, 'alex123', '$2y$10$ubl9rTYHbEiiLg2LdDqUi.hq70nHPG82lZDwNxhXk40GxDYbqCTka', 'alex123@gmail.com', 'Guadalupe Cebu City', 30, NULL, '2025-03-20 04:18:01', 'student'),
(6, 44444444, 'Palacio', 'Kuya Real', '', 'BSIT', 3, 'real123', '$2y$10$wyYAlVz/Kct.a5lOyaqFYuCMo8ig2ddFYBFdufJYpgkuS2hrXC8ry', 'real123@gmail.com', 'Ramos ', 30, NULL, '2025-03-20 04:29:32', 'student');

INSERT INTO `announcements` (`id`, `admin_username`, `content`, `date_posted`) VALUES
(1, 'admin', 'New AnnouncementsZs', '2025-03-20 12:03:00'),
(2, 'admin', 'Hello! This is the ADMIN!', '2025-03-20 12:12:23'),
(3, 'admin', 'Post announcementszzzzzz', '2025-03-20 12:32:44');


INSERT INTO `feedback` (`posID`, `pos_Name`, `user_id`, `rating`, `comments`, `created_at`) VALUES
(1, 7, 2, NULL, 'asdasdasdasd', '2025-04-10 03:24:47'),
(2, 16, 2, NULL, 'Feedbacks Good', '2025-04-10 04:26:27'),
(3, 28, 2, NULL, 'Feedback!!', '2025-04-10 05:09:49');

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

INSERT INTO `users` (`ID`, `ID_NUMBER`, `LASTNAME`, `FIRSTNAME`, `MIDDLENAME`, `COURSE`, `YEAR`, `USERNAME`, `PASSWORD`, `EMAIL`, `ADDRESS`, `SESSION`, `IMAGE`, `CREATED_AT`, `user_type`) VALUES
(0, 0, 'CSS', 'Admin', NULL, NULL, NULL, 'admin', '$2y$10$rKXnBT9B.Qd3dNh0oqHdzupRQdRcZ6pxALhKQo9GzSvYirqLOZB1y', '', NULL, 30, NULL, '2025-03-20 04:01:31', 'admin'),
(2, 22683320, 'Monreal', 'Jeff', 'Ranido', 'BSIT', 3, 'j123', '$2y$10$SBn6TczoE3LocgsOt8/8ieo0Z5BdREuO89gwr6xuMFSP.KLy6qc2C', 'monrealjeff2@gmail.com', 'Guadalupe Cebu City', 30, 'uploads/jeff.png', '2025-03-20 04:01:47', 'student'),
(3, 11111111, 'Moon', 'Jay', 'Sundae', 'BSIT', 3, 'jay123', '$2y$10$Ap9zWDWsUMv6Ubshx3UqguRHK2SLUSowVCCIGMX3JU/aVjC6fA/im', 'jay123@gmail.com', 'Guadalupe Cebu City', 30, NULL, '2025-03-20 04:03:41', 'student'),
(4, 22222222, 'Catubig', 'Mark Dave', '', 'BSIT', 3, 'mark123', '$2y$10$b56LhAnz4t2hN1PHdFyt..WYeif4ezSN8EpkgMELA2IqDjoZgqGP6', 'mark123@gmail.com', 'Guadalupe Cebu City', 30, NULL, '2025-03-20 04:16:30', 'student'),
(5, 33333333, 'Sagaral', 'Alexus', 'Sundae', 'BSIT', 3, 'alex123', '$2y$10$ubl9rTYHbEiiLg2LdDqUi.hq70nHPG82lZDwNxhXk40GxDYbqCTka', 'alex123@gmail.com', 'Guadalupe Cebu City', 30, NULL, '2025-03-20 04:18:01', 'student'),
(6, 44444444, 'Palacio', 'Kuya Real', '', 'BSIT', 3, 'real123', '$2y$10$wyYAlVz/Kct.a5lOyaqFYuCMo8ig2ddFYBFdufJYpgkuS2hrXC8ry', 'real123@gmail.com', 'Ramos ', 30, NULL, '2025-03-20 04:29:32', 'student');

--
-- Indexes for dumped tables