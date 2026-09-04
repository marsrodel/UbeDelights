-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 04, 2026 at 05:13 PM
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

-- --------------------------------------------------------

--
-- Table structure for table `admin_privileges`
--

CREATE TABLE `admin_privileges` (
  `idNumber` varchar(15) NOT NULL,
  `can_manage_registrations` tinyint(1) NOT NULL DEFAULT 0,
  `can_update_accounts` tinyint(1) NOT NULL DEFAULT 0,
  `can_request_deletion` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(10, '2026-0001', 'Rodel Mae Qala', 'mat@gmail.com', '09811537767', 'Purok 3', 'Barangay 7', 'Cabadran City', 'Ambot', '8605', 1045.00, 0.00, 1045.00, 'delivered', '', '2026-09-02 11:19:43', '2026-09-03 15:10:54');

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
(39, 10, 7, 'Ube Latte', 1, 75.00, 75.00);

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
  `status` enum('active','blocked','pending','incomplete') DEFAULT 'pending',
  `is_logged_in` tinyint(1) DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `device_used` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `middle_name`, `last_name`, `extension_name`, `date_of_birth`, `age`, `sex`, `street`, `barangay`, `city_municipality`, `province`, `country`, `zip_code`, `username`, `email`, `password_hash`, `q1`, `a1`, `q2`, `a2`, `q3`, `a3`, `created_at`, `updated_at`, `is_active`, `profile_picture`, `role`, `otp_code`, `otp_expiry`, `status`, `is_logged_in`, `ip_address`, `device_used`) VALUES
('2024-0001', 'Juan', 'Santos', 'Dela Cruz', 'Jr.', '1995-06-15', 29, 'Male', '123 Main Street', 'Barangay 1', 'Manila', 'Metro Manila', 'Philippines', '1000', 'juan.delacruz', 'juan.delacruz@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'What is your favorite color?', '$2y$10$hashA', 'What is your mother\'s maiden name?', '$2y$10$hashB', 'What city were you born in?', '$2y$10$hashC', '2026-08-16 10:39:21', '2026-08-16 10:39:21', 1, '', 'customer', NULL, NULL, 'pending', 0, NULL, NULL),
('2026-0001', 'Rodel Mae', 'Karate', 'Qala', '', '2005-06-06', 21, 'Male', 'Purok 3', 'Barangay 7', 'Cabadran City', 'Ambot', 'Philip', '8605', 'matamat_123', 'mat@gmail.com', '$2y$10$xvWQzCPnghWU6pUJdJgEve8lua.aM.B1D69vkZ8TTBXPb3dVmGnk2', 'Who is your best friend in Elementary?', '$2y$10$kQhkyXJGY6rUlaV4zrXTPOMs50RfCzSFOrxDdNUQzkdHIkt8N5JEC', 'What is the name of your favorite pet?', '$2y$10$3rgKLaNZPOMc.TCEbB4iGu.OyLpOfwxBDXx/IkQTuKrkcUVYWiQzO', 'Who is your favorite teacher in high school?', '$2y$10$UfV22H8Z1HZ7vCkyxk8XB.l9abg1l7TSrB07J8WCrH00j113SFwjS', '2026-08-20 03:55:02', '2026-08-26 04:43:39', 1, '', 'customer', NULL, NULL, 'active', 0, NULL, NULL),
('2026-0002', 'Mary Rose', 'Rosmar', 'Lima', '', '2005-06-06', 21, 'Male', 'Purok 2', 'Barangay 7', 'Cabadbaran', 'Agusan Del Norte', 'Philippines', '8605', 'maraon_101', 'marsrodel@gmail.com', '$2y$10$duX08Va5b8aUY5iHOVZCHuRPjDAaCNNCCdSfJkP6BAGCqYWPANRFS', 'Who is your best friend in Elementary?', '$2y$10$Pak2WjknMbhzA.ltbUfls.gi9BVzSfoiOIRoywFZx98jkkvhRVf8W', 'What is the name of your favorite pet?', '$2y$10$PjYuHjWco71roMWZZDy8/.gAzwIGvvLwAmM70/NgAg5exMA4p8G7m', 'Who is your favorite teacher in high school?', '$2y$10$ADnZK6wcr1HY4we0w9QgOexIwCQeej..LZFLo.0rH.SascBRMiHIG', '2026-08-26 05:01:36', '2026-08-26 05:03:45', 1, '', 'admin', NULL, NULL, 'active', 0, NULL, NULL),
('2026-0003', 'Christian', 'Ocarez', 'Datig', '', '2005-06-06', 21, 'Male', 'Purok 2', 'Barangay 7', 'Cabadbaran City', 'Agusan Del Norte', 'Philippines', '8605', 'christian_123', 'bshakeys2@gmail.com', '$2y$10$hGQ3nkCBaPjVsJ1LXtNW.O7hzb8YpwDxFjneyQzuppandTxL50.pq', '', '', '', '', '', '', '2026-09-03 15:29:39', '2026-09-03 15:29:39', 0, '', 'customer', NULL, NULL, 'incomplete', 0, NULL, NULL),
('2026-0004', 'Rodel James', '', 'Maraon', '', '2005-06-06', 21, 'Male', 'Purok 2', 'Barangay 7', 'Cabadbaran City', 'Agusan Del Norte', 'Philippines', '8605', 'maraon_123', 'maraon101@gmail.com', '$2y$10$MjqEWv4gYwYeQ1u4oXu.DegsW6d1POBnpuCWblMj6NcEdt2p08yVW', 'What was the name of your first pet?', '$2y$10$jMcYrdYABJzblyyPkYEq1ehIhzA.k110nU6OyADyUbUET5Uj.760i', 'What is your favorite flower?', '$2y$10$xgFkdHVsFpdinf3ABwUEU.rT8r5orbIxUtjgPDzoWe9WK5dawoByG', 'What is your oldest sibling\'s first name?', '$2y$10$ghu8ckXp53/V7IHFAzzwZerrnUH6kwG2JyuEjJ9xloizmUD9S0eFW', '2026-09-04 14:20:03', '2026-09-04 14:20:03', 1, '', 'customer', NULL, NULL, 'pending', 0, NULL, NULL),
('2026-0005', 'Mela', 'June', 'Hubog', '', '2005-06-06', 21, 'Male', 'Purok 2', 'Barangay 7', 'Cabadbaran', 'Agusan Del Norte', 'Philippines', '8605', 'babies_123', 'baby@gmail.com', '$2y$10$SXY725NFy5WIsLte9jh60eauLbm1FPjhgzyKnJmGeUg8K5yg.RLgW', 'What was the name of your first pet?', '$2y$10$Eidv/394sTYMziAeZJgAt.c1ffAoEwRzWxRRmWIgN1hzkHWcLKVki', 'What is your favorite flower?', '$2y$10$4k/1rs7Cniq2SVNZ5OuG6e4mGPB80gNr3lepncSduYjeHlq1azWC6', 'What is your oldest sibling\'s first name?', '$2y$10$lc3D0OSP0sjka6p5KlRKHOK7b43O0f7b7TCGQWWD36goaoYJVwyeS', '2026-09-04 14:22:31', '2026-09-04 14:22:31', 1, '', 'customer', NULL, NULL, 'pending', 0, NULL, NULL),
('2026-0006', 'Rodel James', '', 'Maraon', '', '2005-06-06', 21, 'Male', 'Purok 2', 'Barangay 7', 'Cabadbaran City', 'Agusan Del Norte', 'Philippines', '8605', 'shakeys_2', 'bshakeys12@gmail.com', '$2y$10$2KZJnsF85eK8TZLQlXpzy.Fds.SYl9ZeP9xwgIM3uPNb2mHQQ07Y2', 'What was the name of your first pet?', '$2y$10$PRjK1iu53Ykz3pPGYKOwYOtocA/EZ1tXESAOeZAcNKYr9cF7g.E1C', 'What is your favorite flower?', '$2y$10$8eXRRjkwmjgW/Sk4YEczNOLSFDKkeUO4SjLBwOf8TDO9tMEMb9ytK', 'What is your oldest sibling\'s first name?', '$2y$10$OIqTnGZDyvKfwEBnRrXdjeR3bb4QGhwZn0tKdO4kY8bupY.N0YIoi', '2026-09-04 14:39:03', '2026-09-04 14:39:03', 1, '', 'customer', NULL, NULL, 'pending', 0, NULL, NULL);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deletion_requests`
--
ALTER TABLE `deletion_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `password_reset_otp`
--
ALTER TABLE `password_reset_otp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
