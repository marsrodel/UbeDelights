-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 12, 2026 at 07:26 AM
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
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `idNumber` varchar(15) DEFAULT NULL,
  `username` varchar(30) DEFAULT NULL,
  `fullName` varchar(100) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `module` varchar(50) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `severity` enum('INFO','WARNING','ERROR','CRITICAL') NOT NULL DEFAULT 'INFO',
  `browser` varchar(50) DEFAULT NULL,
  `device` varchar(50) DEFAULT NULL,
  `os` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `idNumber`, `username`, `fullName`, `role`, `module`, `action`, `details`, `ip_address`, `created_at`, `severity`, `browser`, `device`, `os`) VALUES
(1, '2026-0001', 'matamat_123', 'Rodel Karate Qala', 'customer', 'Authentication', 'login', 'matamat_123 logged in successfully | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 09:25:02', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(2, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Orders', 'ORDER_STATUS', 'maraon_101 changed order #11 status to \'confirmed\' | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 09:26:10', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(3, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Orders', 'ORDER_STATUS', 'maraon_101 changed order #11 status to \'delivered\' | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 09:26:11', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(4, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 09:30:18', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(5, '2026-0005', 'babies_123', 'Mela June Hubog', 'customer', 'User Management', 'BLOCK_USER', 'maraon_101 blocked babies_123 | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 09:35:48', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(6, '2026-0003', 'christian_123', 'Christian Ocarez Datig', 'customer', 'User Management', 'APPROVE_USER', 'maraon_101 approved christian_123 | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 09:46:03', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(7, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'UNBLOCK_USER', 'maraon_101 unblocked babies_123 | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 10:01:33', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(8, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'UPDATE_USER', 'maraon_101 updated user maraon_123 | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 10:15:12', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(9, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'BLOCK_USER', 'maraon_101 blocked christian_123 | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 10:21:17', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(10, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 10:30:22', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(11, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'RESET_PASSWORD', 'maraon_101 reset password for babies_123 | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 10:36:03', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(12, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'BLOCK_USER', 'maraon_101 blocked juan.delacruz | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 10:40:17', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(13, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'UPDATE_USER', 'maraon_101 updated user babies_123 | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 10:46:28', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(14, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'RESET_PASSWORD', 'maraon_101 reset password for babies_123 | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 10:57:14', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(15, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'DELETION_REQUEST', 'maraon_101 requested deletion of babies_123 (ID: 2026-0005) | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 11:10:00', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(16, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 12:54:51', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(17, '2026-0001', 'matamat_123', 'Rodel Karate Qala', 'customer', 'Authentication', 'login', 'matamat_123 logged in successfully | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 12:55:51', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(18, '2026-0001', 'matamat_123', 'Rodel Karate Qala', 'customer', 'Authentication', 'logout', 'User logged out | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 12:56:03', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(19, '2026-0001', 'matamat_123', 'Rodel Karate Qala', 'customer', 'Authentication', 'login', 'matamat_123 logged in successfully | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 13:04:19', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(20, '2026-0001', 'matamat_123', 'Rodel Karate Qala', 'customer', 'Authentication', 'logout', 'User logged out | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 13:04:28', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(21, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 13:07:01', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(22, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'logout', 'User logged out | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 13:07:04', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(23, '2026-0001', 'matamat_123', 'Rodel Karate Qala', 'customer', 'Authentication', 'login', 'matamat_123 logged in successfully | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 13:07:23', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(24, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 13:46:14', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(25, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'UNBLOCK_USER', 'maraon_101 unblocked christian_123 | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 13:56:06', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(26, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'logout', 'User logged out | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 14:01:17', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(27, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 14:01:27', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(28, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Authentication', 'failed_login', 'Wrong password attempt | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 14:52:41', 'WARNING', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(29, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 14:53:05', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(30, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'RESET_PASSWORD', 'maraon_101 reset password for maraon_123 | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 14:55:35', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(31, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'logout', 'User logged out | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 15:01:32', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(32, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Authentication', 'login', 'maraon_123 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 15:03:48', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(33, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 15:05:01', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(34, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Authentication', 'logout', 'User logged out | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 15:06:55', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(35, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 15:07:02', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(36, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'logout', 'User logged out | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 15:19:11', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(37, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Authentication', 'login', 'maraon_123 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 15:19:23', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(38, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'logout', 'User logged out | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 16:05:46', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(39, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 16:05:54', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(40, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 16:36:34', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(41, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'logout', 'User logged out | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-06 16:36:59', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(42, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Authentication', 'login', 'maraon_123 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 02:18:57', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(43, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'UNBLOCK_USER', 'maraon_123 unblocked juan.delacruz | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 02:19:29', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(44, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'UPDATE_USER', 'maraon_123 updated user babies_123 | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 02:26:03', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(45, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'CREATE_ACCOUNT', 'maraon_123 created account for rodeljames_101 (ID: 2026-0007) with role customer | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 03:34:50', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(46, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'CREATE_ACCOUNT', 'maraon_123 created account for rodeljames_101 (ID: 2026-0007) with role customer | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 03:51:13', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(47, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'CREATE_ACCOUNT', 'maraon_123 created account for rodeljames_101 (ID: 2026-0007) with role customer | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 03:55:26', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(48, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'APPROVE_USER', 'maraon_123 approved juan.delacruz | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 03:58:23', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(49, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 04:50:54', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(50, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 04:55:12', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(51, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'REJECT_USER', 'maraon_101 rejected matamat_123 | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 05:05:24', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(52, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'APPROVE_USER', 'maraon_101 approved matamat_123 | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 05:06:55', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(53, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'logout', 'User logged out | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 05:08:43', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(54, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'failed_login', 'Wrong password attempt | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 05:08:50', 'WARNING', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(55, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'failed_login', 'Wrong password attempt | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 05:46:42', 'WARNING', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(56, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'failed_login', 'Wrong password attempt | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 05:47:44', 'WARNING', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(57, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'failed_login', 'Wrong password attempt | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 05:47:46', 'WARNING', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(58, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 07:27:59', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(59, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 07:32:11', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(60, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'logout', 'User logged out | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 07:32:13', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(61, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'failed_login', 'Wrong password attempt | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 07:32:18', 'WARNING', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(62, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'logout', 'User logged out | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 07:32:27', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(63, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'failed_login', 'Wrong password attempt | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 07:32:33', 'WARNING', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(64, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'failed_login', 'Wrong password attempt | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-07 07:32:36', 'WARNING', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(65, NULL, 'marsrodel', NULL, NULL, 'Authentication', 'failed_login', 'Unknown user: marsrodel | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-08 01:50:09', 'WARNING', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(66, NULL, 'marsrodel', NULL, NULL, 'Authentication', 'failed_login', 'Unknown user: marsrodel | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-08 01:50:11', 'WARNING', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(67, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-08 01:52:34', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(68, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'logout', 'User logged out | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-08 01:53:54', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(69, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Authentication', 'login', 'maraon_123 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-08 01:54:09', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(70, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Deletion Requests', 'DELETION_REVIEW', 'maraon_123 approvedd deletion request for christian_123 (ID: 2026-0003) | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-08 01:54:24', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(71, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Deletion Requests', 'DELETION_REVIEW', 'maraon_123 rejectedd deletion request for babies_123 (ID: 2026-0005) | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-08 02:35:58', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(72, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-08 02:36:24', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(73, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'UNBLOCK_USER', 'maraon_101 unblocked christian_123 | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-08 02:36:37', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(74, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'DELETION_REQUEST', 'maraon_101 requested deletion of christian_123 (ID: 2026-0003) | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-08 02:36:51', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(75, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Deletion Requests', 'DELETION_REVIEW', 'maraon_123 rejectedd deletion request for christian_123 (ID: 2026-0003) | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-08 02:37:22', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(76, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'DELETION_REQUEST', 'maraon_101 requested deletion of christian_123 (ID: 2026-0003) | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-08 02:38:10', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(77, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Deletion Requests', 'DELETION_REVIEW', 'maraon_123 approvedd deletion request for christian_123 (ID: 2026-0003) | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-08 02:38:42', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(78, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'DELETION_REQUEST', 'maraon_101 requested deletion of christian_123 (ID: 2026-0003) | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-08 02:51:01', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(79, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Deletion Requests', 'DELETION_REVIEW', 'maraon_123 approvedd deletion request for christian_123 (ID: 2026-0003) | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-08 02:51:22', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(80, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'DELETION_REQUEST', 'maraon_101 requested deletion of rodeljames_101 (ID: 2026-0007) | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-08 02:52:45', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(81, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Deletion Requests', 'DELETION_REVIEW', 'maraon_123 rejectedd deletion request for rodeljames_101 (ID: 2026-0007) | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-08 02:52:57', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(82, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Authentication', 'login', 'maraon_123 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-08 03:03:29', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(83, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'DELETE_USER', 'maraon_123 deleted rodeljames_101 (ID: 2026-0007) | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-08 03:12:22', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(84, NULL, 'asdas', NULL, NULL, 'Authentication', 'failed_login', 'Unknown user: asdas | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 03:40:23', 'WARNING', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(85, NULL, 'asdas', NULL, NULL, 'Authentication', 'failed_login', 'Unknown user: asdas | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 03:40:39', 'WARNING', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(86, NULL, 'asdas', NULL, NULL, 'Authentication', 'failed_login', 'Unknown user: asdas | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 03:40:40', 'WARNING', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(87, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Authentication', 'failed_login', 'Wrong password attempt | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 04:01:23', 'WARNING', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(88, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Authentication', 'login', 'maraon_123 logged in successfully | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 04:02:42', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(89, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'CREATE_ACCOUNT', 'maraon_123 created account for rodel_123 (ID: 2026-0007) with role customer | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 04:03:22', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(90, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Authentication', 'logout', 'User logged out | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 04:03:40', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(91, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Authentication', 'failed_login', 'Wrong password attempt | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 04:36:19', 'WARNING', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(92, '2026-0007', 'rodel_123', '', 'customer', 'Authentication', 'logout', 'User logged out | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 05:37:04', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(93, '2026-0007', 'rodel_123', '', 'customer', 'Authentication', 'logout', 'User logged out | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 05:48:39', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(94, '2026-0007', 'rodel_123', '', 'customer', 'Authentication', 'logout', 'User logged out | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 05:49:26', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(95, '2026-0007', 'rodel_123', '', 'customer', 'Authentication', 'logout', 'User logged out | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 07:58:10', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(96, '2026-0007', 'rodel_123', '', 'customer', 'Authentication', 'logout', 'User logged out | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 08:33:24', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(97, '2026-0007', 'rodel_123', '', 'customer', 'Authentication', 'logout', 'User logged out | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 09:26:43', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(98, '2026-0007', 'rodel_123', '', 'customer', 'Authentication', 'logout', 'User logged out | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 09:40:03', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(99, '2026-0007', 'rodel_123', '', 'customer', 'Authentication', 'logout', 'User logged out | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:15:41', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(100, '2026-0007', 'rodel_123', 'Educator  Maraon', 'customer', 'Authentication', 'login', 'rodel_123 completed account setup and logged in | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:17:08', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(101, '2026-0007', 'rodel_123', 'Educator  Maraon', 'customer', 'Authentication', 'logout', 'User logged out | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:19:32', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(102, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Authentication', 'login', 'maraon_123 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:19:42', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(103, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'DELETE_USER', 'maraon_123 deleted shakeys_2 (ID: 2026-0006) | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:20:08', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(104, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'CREATE_ACCOUNT', 'maraon_123 created account for shake_123 (ID: 2026-0008) with role customer | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:21:12', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(105, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Authentication', 'logout', 'User logged out | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:21:22', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(106, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'failed_login', 'Wrong password attempt | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:22:51', 'WARNING', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(107, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'failed_login', 'Wrong password attempt | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:22:53', 'WARNING', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(108, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:23:05', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(109, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'logout', 'User logged out | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:24:19', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(110, '2026-0008', 'shake_123', '', 'customer', 'Authentication', 'failed_login', 'Wrong password attempt | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:24:29', 'WARNING', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(111, '2026-0008', 'shake_123', '', 'customer', 'Authentication', 'failed_login', 'Wrong password attempt | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:24:56', 'WARNING', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(112, '2026-0008', 'shake_123', '', 'customer', 'Authentication', 'logout', 'User logged out | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:25:14', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(113, '2026-0009', 'hacker_101', 'Apple  Bitcs', 'customer', 'Authentication', 'login', 'hacker_101 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:27:06', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(114, '2026-0008', 'shake_123', '', 'customer', 'Authentication', 'failed_login', 'Wrong password attempt | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:36:05', 'WARNING', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(115, '2026-0009', 'hacker_101', 'Apple  Bitcs', 'customer', 'Authentication', 'login_blocked', 'Pending user attempted login | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:36:53', 'ERROR', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(116, '2026-0009', 'hacker_101', 'Apple  Bitcs', 'customer', 'Authentication', 'login_blocked', 'Pending user attempted login | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:37:13', 'ERROR', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(117, '2026-0009', 'hacker_101', 'Apple  Bitcs', 'customer', 'Authentication', 'login_blocked', 'Pending user attempted login | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:37:18', 'ERROR', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(118, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:46:06', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(119, '2026-0009', 'hacker_101', 'Apple  Bitcs', 'customer', 'Authentication', 'login_blocked', 'Pending user attempted login | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:46:32', 'ERROR', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(120, '2026-0009', 'hacker_101', 'Apple  Bitcs', 'customer', 'Authentication', 'login_blocked', 'Pending user attempted login | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:46:35', 'ERROR', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(121, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'BLOCK_USER', 'maraon_101 blocked rodel_123 | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:47:07', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(122, '2026-0007', 'rodel_123', 'Educator  Maraon', 'customer', 'Authentication', 'login_blocked', 'Inactive user attempted login | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:47:21', 'ERROR', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(123, '2026-0007', 'rodel_123', 'Educator  Maraon', 'customer', 'Authentication', 'login_blocked', 'Inactive user attempted login | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:47:25', 'ERROR', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(124, '2026-0007', 'rodel_123', 'Educator  Maraon', 'customer', 'Authentication', 'login_blocked', 'Inactive user attempted login | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:47:26', 'ERROR', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(125, '2026-0007', 'rodel_123', 'Educator  Maraon', 'customer', 'Authentication', 'login_blocked', 'Inactive user attempted login | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:47:27', 'ERROR', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(126, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'REJECT_USER', 'maraon_101 rejected hacker_101 | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:48:31', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(127, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'User Management', 'REJECT_USER', 'maraon_101 rejected hacker_101 | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:54:49', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(128, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Authentication', 'login', 'maraon_123 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-09 10:56:28', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(129, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-12 04:22:19', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(130, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'logout', 'User logged out | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-12 04:23:17', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(131, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'Authentication', 'login', 'maraon_123 logged in successfully | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-12 04:23:27', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(132, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'SAVE_PRIVILEGES', 'maraon_123 updated role & privileges for maraon_101 (role: admin) | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-12 05:02:00', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(133, '2026-0002', 'maraon_101', 'Mary Rose Rosmar Lima', 'admin', 'Authentication', 'login', 'maraon_101 logged in successfully | Browser: Edge 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-12 05:02:25', 'INFO', 'Edge 152.0.0.0', 'Desktop', 'Windows'),
(134, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'SAVE_PRIVILEGES', 'maraon_123 updated role & privileges for maraon_101 (role: admin) | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-12 05:02:56', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(135, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'SAVE_PRIVILEGES', 'maraon_123 updated role & privileges for maraon_101 (role: admin) | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-12 05:04:08', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(136, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'SAVE_PRIVILEGES', 'maraon_123 updated role & privileges for maraon_101 (role: admin) | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-12 05:04:46', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(137, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'SAVE_PRIVILEGES', 'maraon_123 updated role & privileges for maraon_101 (role: admin) | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-12 05:05:37', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(138, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'SAVE_PRIVILEGES', 'maraon_123 updated role & privileges for maraon_101 (role: admin) | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-12 05:15:13', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(139, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'CREATE_ACCOUNT', 'maraon_123 created account for edrian_123 (ID: 2026-0009) with role admin | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-12 05:23:01', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(140, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'SAVE_PRIVILEGES', 'maraon_123 updated role & privileges for maraon_101 (role: super_admin) | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-12 05:23:36', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(141, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'SAVE_PRIVILEGES', 'maraon_123 updated role & privileges for maraon_101 (role: admin) | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-12 05:24:08', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows'),
(142, '2026-0004', 'maraon_123', 'Rodel Jame  Maraon', 'super_admin', 'User Management', 'SAVE_PRIVILEGES', 'maraon_123 updated role & privileges for maraon_101 (role: admin) | Browser: Chrome 152.0.0.0 | OS: Windows | Device: Desktop', '::1', '2026-09-12 05:24:19', 'INFO', 'Chrome 152.0.0.0', 'Desktop', 'Windows');

-- --------------------------------------------------------

--
-- Table structure for table `admin_privileges`
--

CREATE TABLE `admin_privileges` (
  `idNumber` varchar(15) NOT NULL,
  `can_manage_registrations` tinyint(1) NOT NULL DEFAULT 0,
  `can_update_accounts` tinyint(1) NOT NULL DEFAULT 0,
  `can_request_deletion` tinyint(1) NOT NULL DEFAULT 0,
  `can_block` tinyint(1) NOT NULL DEFAULT 0,
  `can_reset_password` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_privileges`
--

INSERT INTO `admin_privileges` (`idNumber`, `can_manage_registrations`, `can_update_accounts`, `can_request_deletion`, `can_block`, `can_reset_password`, `updated_at`) VALUES
('2026-0002', 1, 1, 1, 1, 1, '2026-09-12 05:24:08'),
('2026-0009', 1, 1, 1, 1, 1, '2026-09-12 05:23:01');

-- --------------------------------------------------------

--
-- Table structure for table `deletion_requests`
--

CREATE TABLE `deletion_requests` (
  `id` int(11) NOT NULL,
  `target_id_number` varchar(15) NOT NULL,
  `requested_by` varchar(15) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` varchar(15) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deletion_requests`
--

INSERT INTO `deletion_requests` (`id`, `target_id_number`, `requested_by`, `reason`, `status`, `reviewed_by`, `reviewed_at`, `created_at`) VALUES
(3, '', '2026-0002', 'Failing', 'rejected', '2026-0004', '2026-09-08 04:37:22', '2026-09-08 02:36:51'),
(4, '', '2026-0002', 'Apple', 'approved', '2026-0004', '2026-09-08 04:38:42', '2026-09-08 02:38:10'),
(5, '', '2026-0002', 'Amnp', 'approved', '2026-0004', '2026-09-08 04:51:22', '2026-09-08 02:51:01'),
(6, '', '2026-0002', 'jey', 'rejected', '2026-0004', '2026-09-08 04:52:57', '2026-09-08 02:52:45');

-- --------------------------------------------------------

--
-- Table structure for table `login_otp`
--

CREATE TABLE `login_otp` (
  `id` int(11) NOT NULL,
  `idNumber` varchar(15) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `attempts` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `action_type` varchar(50) NOT NULL DEFAULT 'system',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` varchar(9) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `street` varchar(100) NOT NULL,
  `barangay` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `province` varchar(50) NOT NULL,
  `zip_code` varchar(10) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `street`, `barangay`, `city`, `province`, `zip_code`, `subtotal`, `shipping_fee`, `total_amount`, `status`, `notes`, `order_date`, `updated_at`) VALUES
(1, '2026-0001', 'Rodel Mae Qala', 'mat@gmail.com', '09811537756', 'Purok 3', 'Barangay 7', 'Cabadran City', 'Ambot', '8605', 1350.00, 0.00, 1350.00, 'delivered', '', '2026-08-26 10:49:58', '2026-08-26 10:51:01'),
(2, '2026-0001', 'Rodel Mae Qala', 'mat@gmail.com', '09811537756', 'Purok 3', 'Barangay 7', 'Cabadran City', 'Ambot', '8605', 1045.00, 0.00, 1045.00, 'cancelled', 'Amnott', '2026-08-26 10:51:53', '2026-08-26 10:52:26'),
(3, '2026-0001', 'Rodel Mae Qala', 'mat@gmail.com', '09811537756', 'Purok 3', 'Barangay 7', 'Cabadran City', 'Ambot', '8605', 2095.00, 0.00, 2095.00, 'cancelled', 'hehe', '2026-08-26 11:07:21', '2026-08-26 11:14:07'),
(4, '2026-0001', 'Rodel Mae Qala', 'mat@gmail.com', '09811537756', 'Purok 3', 'Barangay 7', 'Cabadran City', 'Ambot', '8605', 800.00, 0.00, 800.00, 'cancelled', '', '2026-08-26 11:14:32', '2026-08-31 06:30:46'),
(5, '2026-0001', 'Rodel Mae Qala', 'mat@gmail.com', '09811537767', 'Purok 3', 'Barangay 7', 'Cabadran City', 'Ambot', '8605', 500.00, 0.00, 500.00, 'cancelled', '', '2026-09-02 08:00:59', '2026-09-02 08:01:04'),
(6, '2026-0001', 'Rodel Mae Qala', 'mat@gmail.com', '09811537767', 'Purok 3', 'Barangay 7', 'Cabadran City', 'Ambot', '8605', 500.00, 0.00, 500.00, 'delivered', '', '2026-09-02 08:01:13', '2026-09-02 08:01:29'),
(7, '2026-0001', 'Rodel Mae Qala', 'mat@gmail.com', '09811537767', 'Purok 3', 'Barangay 7', 'Cabadran City', 'Ambot', '8605', 1350.00, 0.00, 1350.00, 'delivered', 'Yey', '2026-09-02 08:01:45', '2026-09-02 08:01:55'),
(8, '2026-0001', 'Rodel Mae Qala', 'mat@gmail.com', '09811537767', 'Purok 3', 'Barangay 7', 'Cabadran City', 'Ambot', '8605', 1045.00, 0.00, 1045.00, 'cancelled', 'huhu', '2026-09-02 08:02:44', '2026-09-02 08:02:55'),
(9, '2026-0001', 'Rodel Mae Qala', 'mat@gmail.com', '09811537767', 'Purok 3', 'Barangay 7', 'Cabadran City', 'Ambot', '8605', 1045.00, 0.00, 1045.00, 'delivered', 'sd', '2026-09-02 08:03:07', '2026-09-02 08:03:33'),
(10, '2026-0001', 'Rodel Mae Qala', 'mat@gmail.com', '09811537767', 'Purok 3', 'Barangay 7', 'Cabadran City', 'Ambot', '8605', 1045.00, 0.00, 1045.00, 'delivered', '', '2026-09-02 11:19:43', '2026-09-03 15:10:54'),
(11, '2026-0001', 'Rodel Qala', 'mat@gmail.com', '09811537767', 'Purok 3', 'Barangay 7', 'Cabadran City', 'Ambot', '8605', 115.00, 99.00, 214.00, 'delivered', '', '2026-09-06 09:25:12', '2026-09-06 09:26:11');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `product_name`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 2, 'Classic Ube Cake', 1, 350.00, 350.00),
(2, 1, 1, 'Ube Cheesecake', 2, 500.00, 1000.00),
(3, 2, 2, 'Classic Ube Cake', 1, 350.00, 350.00),
(4, 2, 1, 'Ube Cheesecake', 1, 500.00, 500.00),
(5, 2, 6, 'Ube Crinkles', 1, 15.00, 15.00),
(6, 2, 5, 'Ube Halo-Halo', 1, 100.00, 100.00),
(7, 2, 3, 'Ube Pandesal', 1, 5.00, 5.00),
(8, 2, 7, 'Ube Latte', 1, 75.00, 75.00),
(9, 3, 2, 'Classic Ube Cake', 1, 350.00, 350.00),
(10, 3, 1, 'Ube Cheesecake', 1, 500.00, 500.00),
(11, 3, 6, 'Ube Crinkles', 1, 15.00, 15.00),
(12, 3, 5, 'Ube Halo-Halo', 1, 100.00, 100.00),
(13, 3, 4, 'Ube Roll', 1, 250.00, 250.00),
(14, 3, 3, 'Ube Pandesal', 1, 5.00, 5.00),
(15, 3, 8, 'Ube Macapuno', 1, 800.00, 800.00),
(16, 3, 7, 'Ube Latte', 1, 75.00, 75.00),
(17, 4, 8, 'Ube Macapuno', 1, 800.00, 800.00),
(18, 5, 1, 'Ube Cheesecake', 1, 500.00, 500.00),
(19, 6, 1, 'Ube Cheesecake', 1, 500.00, 500.00),
(20, 7, 2, 'Classic Ube Cake', 1, 350.00, 350.00),
(21, 7, 1, 'Ube Cheesecake', 2, 500.00, 1000.00),
(22, 8, 2, 'Classic Ube Cake', 1, 350.00, 350.00),
(23, 8, 1, 'Ube Cheesecake', 1, 500.00, 500.00),
(24, 8, 6, 'Ube Crinkles', 1, 15.00, 15.00),
(25, 8, 5, 'Ube Halo-Halo', 1, 100.00, 100.00),
(26, 8, 3, 'Ube Pandesal', 1, 5.00, 5.00),
(27, 8, 7, 'Ube Latte', 1, 75.00, 75.00),
(28, 9, 2, 'Classic Ube Cake', 1, 350.00, 350.00),
(29, 9, 1, 'Ube Cheesecake', 1, 500.00, 500.00),
(30, 9, 6, 'Ube Crinkles', 1, 15.00, 15.00),
(31, 9, 5, 'Ube Halo-Halo', 1, 100.00, 100.00),
(32, 9, 3, 'Ube Pandesal', 1, 5.00, 5.00),
(33, 9, 7, 'Ube Latte', 1, 75.00, 75.00),
(34, 10, 2, 'Classic Ube Cake', 1, 350.00, 350.00),
(35, 10, 1, 'Ube Cheesecake', 1, 500.00, 500.00),
(36, 10, 6, 'Ube Crinkles', 1, 15.00, 15.00),
(37, 10, 5, 'Ube Halo-Halo', 1, 100.00, 100.00),
(38, 10, 3, 'Ube Pandesal', 1, 5.00, 5.00),
(39, 10, 7, 'Ube Latte', 1, 75.00, 75.00),
(40, 11, 6, 'Ube Crinkles', 1, 15.00, 15.00),
(41, 11, 5, 'Ube Halo-Halo', 1, 100.00, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_otp`
--

CREATE TABLE `password_reset_otp` (
  `id` int(11) NOT NULL,
  `idNumber` varchar(15) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `attempts` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_otp`
--

INSERT INTO `password_reset_otp` (`id`, `idNumber`, `otp_hash`, `expires_at`, `used`, `attempts`, `created_at`) VALUES
(8, '2026-0004', '$2y$10$DR3douLR/OiTt2UEvqvtueCSFEWdCZZhT7UnIhATDlDzzi8yv9rSy', '2026-09-07 09:44:34', 1, 1, '2026-09-07 07:39:34'),
(9, '2026-0004', '$2y$10$wAydK9Ko777SVNCdJkmvNufiLFtfdD74z8SfwMOmx03k38pjJ78AK', '2026-09-08 03:55:21', 1, 1, '2026-09-08 01:50:22'),
(10, '2026-0001', '$2y$10$DvxmbOshb8jQ2hSUWgKMnugggYEX8Hl24i3R088TTw97r9PN3glce', '2026-09-09 05:45:58', 0, 0, '2026-09-09 03:40:58'),
(11, '2026-0007', '$2y$10$ySR0TeHTCh5DwNYL1gELIulyE/zdQ8W7MMW4zoqpzJ0eg.z0jrg/C', '2026-09-09 06:08:53', 1, 1, '2026-09-09 04:03:53'),
(12, '2026-0007', '$2y$10$8tEbHbSBHGoCpk4MeqIzN.vWJoF0O8cFvQYxt4WhXLGIIHZ47yq9W', '2026-09-09 06:11:20', 1, 2, '2026-09-09 04:06:20'),
(13, '2026-0007', '$2y$10$/0JU2aD6O8VdSUNxT6dj9OAfNyfsGgcM6UjkwqjTLSO2S4sZDFB.O', '2026-09-09 06:31:10', 1, 1, '2026-09-09 04:26:10'),
(14, '2026-0007', '$2y$10$wlQoh3nJXnoQljz/odeD2OJG4WpBl4BBjVSp84AQgI12alBU86vDe', '2026-09-09 06:40:41', 1, 0, '2026-09-09 04:35:41'),
(16, '2026-0007', '$2y$10$6U1O9Wv4l2i.p297Tm6qo.we2mYmX8i..DyRa8voetJ4KPOpcLFfO', '2026-09-09 06:41:26', 1, 1, '2026-09-09 04:36:26'),
(18, '2026-0007', '$2y$10$bzCuCLbbbTJiT6dFc2UzrOYA01VnOYEKBFr.SvCXOuAal9JD8cN1y', '2026-09-09 06:52:06', 1, 1, '2026-09-09 04:47:06'),
(19, '2026-0007', '$2y$10$DMnnkHoMv7KwNx9D9XG.Gu5knGG0gz9zwsKi0dfkiw8Mh1AATKvL.', '2026-09-09 06:56:32', 1, 1, '2026-09-09 04:51:32'),
(20, '2026-0007', '$2y$10$ZR98nzTRPmiLSlnTlgZPg.3gTB6wYnVMqoWglFbypOoul16.2J2kO', '2026-09-09 07:01:20', 1, 1, '2026-09-09 04:56:20'),
(21, '2026-0007', '$2y$10$jV8sa.qTyoa8BMhuTZU/i.q1HDiCSc8nAaKDimpEzT6EtyWvazXKq', '2026-09-09 07:08:22', 1, 1, '2026-09-09 05:03:22'),
(22, '2026-0007', '$2y$10$jRiahp.3x0R3led2YC.Pd.P/YnsxdiHzpvPHj9T1wgpj7jWJaBW7G', '2026-09-09 07:15:25', 1, 0, '2026-09-09 05:10:25'),
(24, '2026-0007', '$2y$10$vDSeQzMiKcPUtIZqBg9CHOFDkm36GKzk4upxTLHF.YUgun4gIEXp6', '2026-09-09 07:16:06', 1, 1, '2026-09-09 05:11:06'),
(25, '2026-0007', '$2y$10$JVO/Mj18RQCrHwLU8p3NF.MOUf0F4QTJmLPRdLBnBfYqzSj/.3dDO', '2026-09-09 07:19:17', 1, 1, '2026-09-09 05:14:17'),
(28, '2026-0007', '$2y$10$LRFUdt0WNGLQxUbTuK56GeYxtJtBaicK5WXF/YF2imSv/0leqLU4q', '2026-09-09 07:24:56', 1, 1, '2026-09-09 05:19:56'),
(30, '2026-0007', '$2y$10$LzR9gESSUMK0Zt8KRWflfu4wW0cx/7tQgHhCYtAbGBKLWw.FCVvDO', '2026-09-09 07:42:15', 1, 1, '2026-09-09 05:37:15'),
(31, '2026-0007', '$2y$10$23RpEdAgxVXG/KY5AkaQfejKEDN4LMGAMMtmFijfGcTwcR9NYKIGC', '2026-09-09 07:43:58', 1, 1, '2026-09-09 05:38:58'),
(34, '2026-0007', '$2y$10$51uHi1bh.gOGQ9plpObAz.b9ekkc8zBJnZC6PYlvLMGH25ZRvf.lO', '2026-09-09 07:54:41', 1, 1, '2026-09-09 05:49:41'),
(35, '2026-0007', '$2y$10$6l6tXr4QEE9UEvOECotAQ.BMKi/75qQySZBNot72KnpQHYWRlQGSi', '2026-09-09 09:55:04', 1, 1, '2026-09-09 07:50:04'),
(36, '2026-0007', '$2y$10$qqWhgUs2WgnLG1Rt0Rk9KuTIaDZPGfHCAxSUmlk5RONEq2V8EFiU6', '2026-09-09 10:03:26', 1, 1, '2026-09-09 07:58:26'),
(37, '2026-0007', '$2y$10$p.ZSdUmVaxVkAqNgrmhEYeoDlaSoIb/GKk25X8ZBpKgRvhfpNlOn2', '2026-09-09 10:16:06', 1, 0, '2026-09-09 08:11:06'),
(38, '2026-0007', '$2y$10$fWml0vhBkvtM7CDO5H7u0OgMfRwpISXxBodEmklDWQu77iTbWI7De', '2026-09-09 10:16:23', 1, 1, '2026-09-09 08:11:23'),
(39, '2026-0007', '$2y$10$da9.KYjfbuKGxj.RfuIG/./mggbasgsPiQcUOth7TF5KEUDSkkZzS', '2026-09-09 10:20:59', 1, 1, '2026-09-09 08:15:59'),
(40, '2026-0007', '$2y$10$0ZXWtQuqNXdyLw0KzK/FleRIkQjZQwQMZJgAcaDpF/MUzp3eBR26G', '2026-09-09 10:38:36', 1, 1, '2026-09-09 08:33:36'),
(41, '2026-0007', '$2y$10$lg1CzHiQGuF5lbotKLk6MOHI9GStdKK/rbkbxy.jXDHsA6wf.757i', '2026-09-09 11:17:52', 1, 1, '2026-09-09 09:12:52'),
(42, '2026-0007', '$2y$10$Qm6YagMuIfW31C6cyzoeB.ZX9UrBAa1eIZgdYQDCovMpldC3KzGee', '2026-09-09 11:31:54', 1, 1, '2026-09-09 09:26:54'),
(43, '2026-0007', '$2y$10$r8XVKl.SS89Ca0xAZWV75.kK.3Nadaj1rzMFOhz3af8Q/bTM1ND/W', '2026-09-09 11:45:23', 1, 1, '2026-09-09 09:40:23'),
(44, '2026-0007', '$2y$10$/H0k.X7MZQpAY0xBPFbTMeEwtZp.gkX0XfIVa7Y1qcFeXP5aRXk2m', '2026-09-09 11:48:48', 1, 1, '2026-09-09 09:43:48'),
(45, '2026-0007', '$2y$10$093KTdYWuntrOiE/26.xkeqn4cYDsQCwfzy3VitZw5al5NE/Zsxay', '2026-09-09 11:52:09', 1, 1, '2026-09-09 09:47:09'),
(46, '2026-0007', '$2y$10$pjr.VZhT/nje8LeiAQLdLe7QzQ4bAjbBSW1Wepv0sNHPMOx5zNJKK', '2026-09-09 12:20:55', 1, 1, '2026-09-09 10:15:55'),
(48, '2026-0008', '$2y$10$sw/9T4qYxpZGvL2jSgj5R.rd1dDJvDa3vmkb7Iis4WA9NOQvUHKAK', '2026-09-09 12:30:06', 0, 0, '2026-09-09 10:25:06');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL,
  `status` varchar(50) DEFAULT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `price`, `category`, `status`, `description`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Ube Cheesecake', 500.00, 'cakes', 'Best Seller', 'Creamy cheesecake with authentic ube swirl on a graham crust.', 'images/item_uploads/1787717365_cheesecake.jpg', '2026-08-26 04:08:17', '2026-08-26 04:09:32'),
(2, 'Classic Ube Cake', 350.00, 'cakes', '', 'Soft ube macapuno sponge layered with rich ube buttercream frosting.', 'images/item_uploads/1787717946_classic.jpg', '2026-08-26 04:19:06', '2026-08-26 04:19:06'),
(3, 'Ube Pandesal', 5.00, 'pastries', 'Popular', 'Warm, pillowy pandesal filled with premium ube halaya, baked fresh daily.', 'images/item_uploads/1787717998_pandesal.jpg', '2026-08-26 04:19:58', '2026-08-26 04:25:52'),
(4, 'Ube Roll', 250.00, 'rolls', '', 'Fluffy ube sponge roll wrapped around smooth ube buttercream filling.', 'images/item_uploads/1787718128_uberoll.jpg', '2026-08-26 04:22:08', '2026-08-26 11:06:39'),
(5, 'Ube Halo-Halo', 100.00, 'beverages', 'New', 'Classic Filipino shaved ice dessert topped with creamy ube halaya.', 'images/item_uploads/1787718183_halohalo.jpg', '2026-08-26 04:23:03', '2026-08-26 04:23:03'),
(6, 'Ube Crinkles', 15.00, 'pastries', '', 'Chewy sugar-dusted crinkle cookies bursting with ube flavor.', 'images/item_uploads/1787718278_crinkles.jpg', '2026-08-26 04:24:38', '2026-08-26 04:24:38'),
(7, 'Ube Latte', 75.00, 'beverages', '', 'Espresso blended with steamed milk and house-made ube syrup.', 'images/item_uploads/1787718338_latte.jpg', '2026-08-26 04:25:38', '2026-08-26 04:25:38'),
(8, 'Ube Macapuno', 800.00, 'cakes', 'Premium', 'Sweet ube and macapuno preserves in a soft, buttery pastry shell.', 'images/item_uploads/1787718412_macapuno.jpg', '2026-08-26 04:26:52', '2026-08-26 11:06:22');

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
  `status` enum('active','blocked','pending','incomplete','rejected') DEFAULT 'pending',
  `is_logged_in` tinyint(1) DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `device_used` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `middle_name`, `last_name`, `extension_name`, `date_of_birth`, `age`, `sex`, `street`, `barangay`, `city_municipality`, `province`, `country`, `zip_code`, `username`, `email`, `password_hash`, `q1`, `a1`, `q2`, `a2`, `q3`, `a3`, `created_at`, `updated_at`, `is_active`, `profile_picture`, `role`, `otp_code`, `otp_expiry`, `status`, `is_logged_in`, `ip_address`, `device_used`) VALUES
('2024-0001', 'Juan', 'Santos', 'Dela Cruz', 'Jr.', '1995-06-15', 29, 'Male', '123 Main Street', 'Barangay 1', 'Manila', 'Metro Manila', 'Philippines', '1000', 'juan.delacruz', 'juan.delacruz@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'What is your favorite color?', '$2y$10$hashA', 'What is your mother\'s maiden name?', '$2y$10$hashB', 'What city were you born in?', '$2y$10$hashC', '2026-08-16 10:39:21', '2026-09-07 03:58:23', 1, '', 'customer', NULL, NULL, 'active', 0, NULL, NULL),
('2026-0001', 'Rodel', 'Karate', 'Qala', '', '2005-06-06', 21, 'Male', 'Purok 3', 'Barangay 7', 'Cabadran City', 'Ambot', 'Philip', '8605', 'matamat_123', 'mat@gmail.com', '$2y$10$xvWQzCPnghWU6pUJdJgEve8lua.aM.B1D69vkZ8TTBXPb3dVmGnk2', 'Who is your best friend in Elementary?', '$2y$10$kQhkyXJGY6rUlaV4zrXTPOMs50RfCzSFOrxDdNUQzkdHIkt8N5JEC', 'What is the name of your favorite pet?', '$2y$10$3rgKLaNZPOMc.TCEbB4iGu.OyLpOfwxBDXx/IkQTuKrkcUVYWiQzO', 'Who is your favorite teacher in high school?', '$2y$10$UfV22H8Z1HZ7vCkyxk8XB.l9abg1l7TSrB07J8WCrH00j113SFwjS', '2026-08-20 03:55:02', '2026-09-07 05:06:55', 1, '', 'customer', NULL, NULL, 'active', 0, NULL, NULL),
('2026-0002', 'Mary Rose', 'Rosmar', 'Lima', '', '2005-06-06', 21, 'Male', 'Purok 2', 'Barangay 7', 'Cabadbaran', 'Agusan Del Norte', 'Philippines', '8605', 'maraon_101', 'marsrodel@gmail.com', '$2y$10$qbjF4IMlME33xkPuAFL4gOeM8RKxPmArIiYw.RXu7BV0/7lltmfVS', 'Who is your best friend in Elementary?', '$2y$10$Pak2WjknMbhzA.ltbUfls.gi9BVzSfoiOIRoywFZx98jkkvhRVf8W', 'What is the name of your favorite pet?', '$2y$10$PjYuHjWco71roMWZZDy8/.gAzwIGvvLwAmM70/NgAg5exMA4p8G7m', 'Who is your favorite teacher in high school?', '$2y$10$ADnZK6wcr1HY4we0w9QgOexIwCQeej..LZFLo.0rH.SascBRMiHIG', '2026-08-26 05:01:36', '2026-09-12 05:24:08', 1, '', 'admin', NULL, NULL, 'active', 0, NULL, NULL),
('2026-0004', 'Rodel Jame', '', 'Maraon', '', '2005-06-06', 21, 'Male', 'Purok 2', 'Barangay 7', 'Cabadbaran City', 'Agusan Del Norte', 'Philippines', '8605', 'maraon_123', 'maraon101@gmail.com', '$2y$10$prCKoJ89wTD9M1TgUqUjyOLSGUtxuAoYwqpGRdRZZz0hVXXKsexTW', 'What was the name of your first pet?', '$2y$10$jMcYrdYABJzblyyPkYEq1ehIhzA.k110nU6OyADyUbUET5Uj.760i', 'What is your favorite flower?', '$2y$10$xgFkdHVsFpdinf3ABwUEU.rT8r5orbIxUtjgPDzoWe9WK5dawoByG', 'What is your oldest sibling\'s first name?', '$2y$10$ghu8ckXp53/V7IHFAzzwZerrnUH6kwG2JyuEjJ9xloizmUD9S0eFW', '2026-09-04 14:20:03', '2026-09-08 01:52:26', 1, '', 'super_admin', NULL, NULL, 'active', 0, NULL, NULL),
('2026-0005', 'Melani', 'June', 'Hubog', '', '2005-06-06', 21, 'Male', 'Purok 2', 'Barangay 7', 'Cabadbaran', 'Agusan Del Norte', 'Philippines', '8606', 'babies_123', 'baby@gmail.com', '$2y$10$cBEW.p9G1sFGLplPFeYgLOjmMJ6IgicSHDFn/aOFgTCiMZh8UAucC', 'What was the name of your first pet?', '$2y$10$Eidv/394sTYMziAeZJgAt.c1ffAoEwRzWxRRmWIgN1hzkHWcLKVki', 'What is your favorite flower?', '$2y$10$4k/1rs7Cniq2SVNZ5OuG6e4mGPB80gNr3lepncSduYjeHlq1azWC6', 'What is your oldest sibling\'s first name?', '$2y$10$lc3D0OSP0sjka6p5KlRKHOK7b43O0f7b7TCGQWWD36goaoYJVwyeS', '2026-09-04 14:22:31', '2026-09-07 02:26:03', 1, '', 'customer', NULL, NULL, 'active', 0, NULL, NULL),
('2026-0007', 'Educator', '', 'Maraon', '', '2005-06-06', 21, 'Male', 'Purok 2', 'Barangay 7', 'Cabadbaran City', 'Agusan Del Norte', 'Philippines', '8605', 'rodel_123', 'rodeljames.maraon@csucc.edu.ph', '$2y$10$C75QD1WXxKZEVjAuX2kAqeDKPWY90TdDPHLotp4pdLG73eY.7x.WK', 'What was the name of your first pet?', '$2y$10$GXNIKm6e/dpM01a6ZR9tlekgVT0EV0IXD.Cuhxoa2wW2F4rpQcQjW', 'What is your favorite flower?', '$2y$10$PPm2rLOpASnQgGlx3yYFBuMF73CNRL3xehD53I6231o97bFCL905u', 'What is your oldest sibling\'s first name?', '$2y$10$qk0LJptodi.0lzBQfhCobOfzpKAWmMtrgn9QY09LZy.Ofss1Kv.66', '2026-09-09 04:03:22', '2026-09-12 05:03:45', 0, '', 'customer', NULL, NULL, 'pending', 0, NULL, NULL),
('2026-0008', '', NULL, '', NULL, '0000-00-00', 0, '', '', '', '', '', '', '', 'shake_123', 'bshakeys2@gmail.com', '$2y$10$ldBu3MAu6WhuyCtg31c63OtFucQPhNr7wxIb0zDbMbkHUxIY77ecC', '', '', '', '', '', '', '2026-09-09 10:21:12', '2026-09-09 10:21:12', 0, '', 'customer', NULL, NULL, 'incomplete', 0, NULL, NULL),
('2026-0009', '', NULL, '', NULL, '0000-00-00', 0, '', '', '', '', '', '', '', 'edrian_123', 'edrian.prones@csucc.edu.ph', '$2y$10$DHI.Jo/XK2NstL8DxogmJOFvCQq0UpfR7PBPpe9XNHMgthV0B5LCG', '', '', '', '', '', '', '2026-09-12 05:23:01', '2026-09-12 05:23:01', 0, '', 'admin', NULL, NULL, 'incomplete', 0, NULL, NULL);

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
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_logs_created` (`created_at`),
  ADD KEY `idx_logs_action` (`action`),
  ADD KEY `idx_logs_idNumber` (`idNumber`);

--
-- Indexes for table `admin_privileges`
--
ALTER TABLE `admin_privileges`
  ADD PRIMARY KEY (`idNumber`);

--
-- Indexes for table `deletion_requests`
--
ALTER TABLE `deletion_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_deletion_requests_status` (`status`),
  ADD KEY `idx_deletion_requests_target` (`target_id_number`);

--
-- Indexes for table `login_otp`
--
ALTER TABLE `login_otp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_otp_user` (`idNumber`),
  ADD KEY `idx_login_otp_expires` (`expires_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_read` (`is_read`),
  ADD KEY `idx_notifications_created` (`created_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_order_date` (`order_date`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_order_id` (`order_id`);

--
-- Indexes for table `password_reset_otp`
--
ALTER TABLE `password_reset_otp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_idNumber` (`idNumber`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`);

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

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `deletion_requests`
--
ALTER TABLE `deletion_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `login_otp`
--
ALTER TABLE `login_otp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `password_reset_otp`
--
ALTER TABLE `password_reset_otp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
