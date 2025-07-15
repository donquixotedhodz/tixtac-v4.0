-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 14, 2025 at 05:43 AM
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
-- Database: `job_order_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `name`, `email`, `phone`, `profile_picture`, `password`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Administrator - Staff', 'student.joshmcdowelltrapal@gmail.com', '09958714112', 'uploads/profile_pictures/profile_683fca5baffdd.jpg', '$2y$10$G7lz8/ECY2SpEs1WYM6GrOAGvA7t9Cmd4oouJChZ5k627LN0kPHMS', '2025-06-02 12:30:32', '2025-06-04 08:55:31'),
(2, 'Angel', 'Angel', 'Angel@example.com', '0000000000', NULL, '$2y$10$aEPaITQLMLvo2x/kAbWq4OXr4UxTIuuNNadwuBms4Kys83j9JO1f2', '2025-06-04 00:00:36', '2025-06-04 04:23:03'),
(3, 'krisxan', 'krisxan', 'krisxan@example.com', '0000000000', NULL, '$2y$10$6hswZA.GPmnehtefTQ4mI.GNcP66tCaJ6/.VFPUDqzdKnFHtH.wJK', '2025-06-04 00:02:46', '2025-06-04 04:23:03'),
(4, 'ems', NULL, NULL, NULL, NULL, '$2y$10$E67AaGozMDU.S2apx6FN8.Y8jAWNPOJpEYQ.02.jz2xOufg7XCDgm', '2025-06-04 04:36:04', '2025-06-04 04:36:04'),
(5, 'eciel', 'Eciel Semeniano', 'joshmcdowelltrapal@gmail.com', '09958714112', 'uploads/profile_pictures/admin_683fcddf3d582.jpg', '$2y$10$0U90HfuS1rVFJQG1I6TkT.8M3w8865/Fep/bfoRtepzGAsOB3twbW', '2025-06-04 04:38:55', '2025-06-04 04:38:55');

-- --------------------------------------------------------

--
-- Table structure for table `aircon_installations`
--

CREATE TABLE `aircon_installations` (
  `id` int(11) NOT NULL,
  `job_order_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `has_filter` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `aircon_models`
--

CREATE TABLE `aircon_models` (
  `id` int(11) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aircon_models`
--

INSERT INTO `aircon_models` (`id`, `brand`, `model_name`, `price`, `created_at`) VALUES
(1, 'Carrier', 'Window Type 1.0HP', 15999.00, '2025-06-02 12:30:32'),
(2, 'Panasonic', 'Split Type 1.5HP', 24999.00, '2025-06-02 12:30:32'),
(3, 'LG', 'Inverter Split Type 2.0HP', 32999.00, '2025-06-02 12:30:32'),
(4, 'Samsung', 'Window Type 1.5HP', 18999.00, '2025-06-02 12:30:32'),
(5, 'Daikin', 'Split Type 1.0HP', 21999.00, '2025-06-02 12:30:32');

-- --------------------------------------------------------

--
-- Table structure for table `job_orders`
--

CREATE TABLE `job_orders` (
  `id` int(11) NOT NULL,
  `job_order_number` varchar(20) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_address` text NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `service_type` enum('installation','repair') NOT NULL,
  `aircon_model_id` int(11) DEFAULT NULL,
  `assigned_technician_id` int(11) DEFAULT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `price` decimal(10,2) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_orders`
--

INSERT INTO `job_orders` (`id`, `job_order_number`, `customer_name`, `customer_address`, `customer_phone`, `service_type`, `aircon_model_id`, `assigned_technician_id`, `status`, `price`, `created_by`, `due_date`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, '202500001', 'Josh McDowell Trapal', 'Ogbot, Bongabong, Oriental Mindoro', '09958714112', 'installation', 1, 1, 'completed', 15000.00, 1, '2025-06-03', '2025-06-02 10:16:40', '2025-06-02 11:06:16', '2025-06-02 11:06:16'),
(2, '202500002', 'Krisxan Castillon', 'Dangay', '09958714112', 'repair', 1, 2, 'completed', 150000.00, 1, '2025-06-11', '2025-06-02 12:32:23', '2025-06-02 12:31:51', '2025-06-02 12:32:23'),
(3, '202500003', 'Marianne Dela Cruz', 'Manaul, Oriental Mindoro', '09958714112', 'installation', 2, 3, 'completed', 15000.00, 1, '2025-06-04', '2025-06-03 10:21:52', '2025-06-03 10:04:45', '2025-06-03 10:21:52'),
(4, '202500004', 'Angel Lamadrid', 'Dangay, Roxas', '09958714112', 'installation', 1, 3, 'cancelled', 15000.00, 1, '2025-06-05', '2025-06-03 13:39:50', '2025-06-03 10:25:03', '2025-06-03 13:39:50'),
(5, '202500005', 'Ann Marisse Cuya', 'San Miguel, Roxas', '09958713112', 'installation', 5, 3, 'cancelled', 10000.00, 1, '2025-06-04', '2025-06-03 13:42:59', '2025-06-03 13:42:52', '2025-06-03 13:42:59'),
(6, '202500006', 'AJ Nicole Salamente', 'San Rafael, Roxas, Oriental Mindoro', '09958714112', 'repair', 1, 4, 'completed', 956.00, 1, '2025-06-05', '2025-06-03 23:45:08', '2025-06-03 23:44:33', '2025-06-03 23:45:08'),
(7, '202500007', 'Desiree Ortega', 'Mansalay', '09958714112', 'installation', 3, 4, 'completed', 45.00, 1, '2025-06-05', NULL, '2025-06-04 03:49:40', '2025-06-04 04:08:09'),
(8, '202500008', 'Josh McDowell Trapal', 'Ogbot\r\nOgbot', '09958714112', 'installation', 2, 2, 'completed', 45645.00, 1, '2025-06-30', '2025-06-04 04:09:04', '2025-06-04 03:56:14', '2025-06-04 04:09:04'),
(9, '202500009', 'Josh McDowell Trapal', 'Ogbot', '09958714112', 'installation', 3, 2, 'completed', 0.00, 1, '2025-06-11', '2025-06-10 05:22:45', '2025-06-10 05:21:48', '2025-06-10 05:22:45'),
(10, '202500010', 'Josh McDowell Trapal', 'Ogbot', '09958714112', 'installation', 4, 2, 'completed', 19099.00, 1, '2025-06-11', '2025-06-10 05:23:39', '2025-06-10 05:23:36', '2025-06-10 05:23:39');

-- --------------------------------------------------------

--
-- Table structure for table `technicians`
--

CREATE TABLE `technicians` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `technicians`
--

INSERT INTO `technicians` (`id`, `username`, `password`, `name`, `email`, `phone`, `profile_picture`, `created_at`, `updated_at`) VALUES
(2, 'josht', '$2y$10$KC75x8X077OCdD5JXLW5Su73xRmxp0c0cK3cf/O0ODYONSDU0uBai', 'Josh McDowell Trapal', 'josht@example.com', '09958714112', NULL, '2025-06-02 12:31:23', '2025-06-04 05:11:58'),
(3, 'eciel', '$2y$10$UObZIT9OlApxi4rd/C47f.1WmkZJr3Vgq/Rs3ByDf1OkYfCIErsb.', 'Eciel Semeniano', 'ecielsemeniano@gmail.com', '09958714113', 'uploads/profile_pictures/technician_3_1749013989.jpg', '2025-06-03 06:56:19', '2025-06-04 05:13:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `idx_username` (`username`),
  ADD UNIQUE KEY `idx_email` (`email`);

--
-- Indexes for table `aircon_installations`
--
ALTER TABLE `aircon_installations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_order_id` (`job_order_id`);

--
-- Indexes for table `aircon_models`
--
ALTER TABLE `aircon_models`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `job_orders`
--
ALTER TABLE `job_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `job_order_number` (`job_order_number`),
  ADD KEY `aircon_model_id` (`aircon_model_id`),
  ADD KEY `assigned_technician_id` (`assigned_technician_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `technicians`
--
ALTER TABLE `technicians`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `aircon_installations`
--
ALTER TABLE `aircon_installations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `aircon_models`
--
ALTER TABLE `aircon_models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `job_orders`
--
ALTER TABLE `job_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `technicians`
--
ALTER TABLE `technicians`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aircon_installations`
--
ALTER TABLE `aircon_installations`
  ADD CONSTRAINT `aircon_installations_ibfk_1` FOREIGN KEY (`job_order_id`) REFERENCES `job_orders` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
