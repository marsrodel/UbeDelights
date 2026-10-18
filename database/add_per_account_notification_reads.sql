CREATE TABLE `notification_reads` (
  `notification_id` int(11) NOT NULL,
  `user_id` varchar(9) NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`notification_id`, `user_id`),
  KEY `idx_notification_reads_user` (`user_id`),
  CONSTRAINT `fk_notification_reads_notification` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notification_reads_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `notification_reads` (`notification_id`, `user_id`)
SELECT n.`id`, u.`user_id`
FROM `notifications` n
JOIN `users` u ON u.`role` IN ('admin', 'super_admin')
WHERE n.`is_read` = 1
  AND (u.`role` = 'super_admin' OR n.`role` IS NULL OR n.`role` <> 'super_admin');

ALTER TABLE `notifications`
  DROP INDEX `idx_notifications_read`,
  DROP COLUMN `is_read`;
