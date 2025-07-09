-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 10, 2025 at 07:16 AM
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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aircon_models`
--
ALTER TABLE `aircon_models`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aircon_models`
--
ALTER TABLE `aircon_models`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
