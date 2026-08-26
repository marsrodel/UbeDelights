-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 26, 2026 at 06:51 AM
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
  `payment_method` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(4, 'Ube Roll', 250.00, 'rolls', 'Not Available', 'Fluffy ube sponge roll wrapped around smooth ube buttercream filling.', 'images/item_uploads/1787718128_uberoll.jpg', '2026-08-26 04:22:08', '2026-08-26 04:28:53'),
(5, 'Ube Halo-Halo', 100.00, 'beverages', 'New', 'Classic Filipino shaved ice dessert topped with creamy ube halaya.', 'images/item_uploads/1787718183_halohalo.jpg', '2026-08-26 04:23:03', '2026-08-26 04:23:03'),
(6, 'Ube Crinkles', 15.00, 'pastries', '', 'Chewy sugar-dusted crinkle cookies bursting with ube flavor.', 'images/item_uploads/1787718278_crinkles.jpg', '2026-08-26 04:24:38', '2026-08-26 04:24:38'),
(7, 'Ube Latte', 75.00, 'beverages', '', 'Espresso blended with steamed milk and house-made ube syrup.', 'images/item_uploads/1787718338_latte.jpg', '2026-08-26 04:25:38', '2026-08-26 04:25:38'),
(8, 'Ube Macapuno', 800.00, 'cakes', 'Not Available', 'Sweet ube and macapuno preserves in a soft, buttery pastry shell.', 'images/item_uploads/1787718412_macapuno.jpg', '2026-08-26 04:26:52', '2026-08-26 04:43:25');

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
('2026-0001', 'Rodel Mae', 'Karate', 'Qala', '', '2005-06-06', 21, 'Male', 'Purok 3', 'Barangay 7', 'Cabadran City', 'Ambot', 'Philip', '8605', 'matamat_123', 'mat@gmail.com', '$2y$10$xvWQzCPnghWU6pUJdJgEve8lua.aM.B1D69vkZ8TTBXPb3dVmGnk2', 'Who is your best friend in Elementary?', '$2y$10$kQhkyXJGY6rUlaV4zrXTPOMs50RfCzSFOrxDdNUQzkdHIkt8N5JEC', 'What is the name of your favorite pet?', '$2y$10$3rgKLaNZPOMc.TCEbB4iGu.OyLpOfwxBDXx/IkQTuKrkcUVYWiQzO', 'Who is your favorite teacher in high school?', '$2y$10$UfV22H8Z1HZ7vCkyxk8XB.l9abg1l7TSrB07J8WCrH00j113SFwjS', '2026-08-20 03:55:02', '2026-08-26 04:43:39', 1, '', 'customer', NULL, NULL, 'active', 0, NULL, NULL);

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

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `device` varchar(100) DEFAULT NULL,
  `browser` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

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
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
