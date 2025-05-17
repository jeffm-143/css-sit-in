-- First add unique index to users.ID_NUMBER
ALTER TABLE `users` 
ADD UNIQUE KEY `unique_id_number` (`ID_NUMBER`);

-- Then create the notifications table
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ID_NUMBER` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` enum('reservation_approved', 'reservation_rejected', 'system') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `notifications_user_fk` (`ID_NUMBER`),
  CONSTRAINT `notifications_user_fk` 
    FOREIGN KEY (`ID_NUMBER`) 
    REFERENCES `users` (`ID_NUMBER`) 
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;