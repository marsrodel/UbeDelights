-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2026 at 06:29 AM
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
-- Database: `ube_delights_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` varchar(9) NOT NULL COMMENT 'Format: xxxx-xxxx',
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `extension_name` varchar(10) DEFAULT NULL,
  `date_of_birth` date NOT NULL,
  `age` int(11) NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `street` varchar(100) NOT NULL COMMENT 'Purok/Street',
  `barangay` varchar(50) NOT NULL,
  `city_municipality` varchar(50) NOT NULL,
  `province` varchar(50) NOT NULL,
  `country` varchar(50) NOT NULL,
  `zip_code` varchar(10) NOT NULL,
  `username` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `q1` varchar(255) NOT NULL,
  `a1` varchar(255) NOT NULL,
  `q2` varchar(255) NOT NULL,
  `a2` varchar(255) NOT NULL,
  `q3` varchar(255) NOT NULL,
  `a3` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `profile_picture` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','customer') NOT NULL DEFAULT 'customer',
  `otp_code` varchar(255) DEFAULT NULL,
  `otp_expiry` timestamp NULL DEFAULT NULL,
  `status` enum('active','blocked','pending','incomplete') DEFAULT 'pending',
  `is_logged_in` tinyint(1) DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `device_used` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `middle_name`, `last_name`, `extension_name`, `date_of_birth`, `age`, `sex`, `street`, `barangay`, `city_municipality`, `province`, `country`, `zip_code`, `username`, `email`, `password_hash`, `q1`, `a1`, `q2`, `a2`, `q3`, `a3`, `created_at`, `updated_at`, `is_active`, `profile_picture`, `role`, `otp_code`, `otp_expiry`, `status`, `is_logged_in`, `ip_address`, `device_used`) VALUES
('2024-0001', 'Juan', 'Santos', 'Dela Cruz', 'Jr.', '1995-06-15', 29, 'Male', '123 Main Street', 'Barangay 1', 'Manila', 'Metro Manila', 'Philippines', '1000', 'juan.delacruz', 'juan.delacruz@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'What is your favorite color?', '$2y$10$hashA', 'What is your mother\'s maiden name?', '$2y$10$hashB', 'What city were you born in?', '$2y$10$hashC', '2026-08-16 10:39:21', '2026-08-16 10:39:21', 1, '', 'customer', NULL, NULL, 'pending', 0, NULL, NULL);

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `trg_validate_email_format` BEFORE INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+.[A-Za-z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid email format';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_validate_user_id_format` BEFORE INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.user_id NOT REGEXP '^[0-9]{4}-[0-9]{4}$' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'User ID must be in format xxxx-xxxx';
    END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_city` (`city_municipality`),
  ADD KEY `idx_province` (`province`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
