-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 16, 2025 at 03:35 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pos_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `condiments`
--

CREATE TABLE `condiments` (
  `id` int(11) NOT NULL,
  `code` int(225) NOT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` >= 0),
  `product_name` varchar(255) NOT NULL,
  `expiration_date` date DEFAULT NULL,
  `price` decimal(10,2) NOT NULL CHECK (`price` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `condiments`
--

INSERT INTO `condiments` (`id`, `code`, `quantity`, `product_name`, `expiration_date`, `price`) VALUES
(1, 0, 9, 'UFC KETCHUP_200g', '2025-11-08', 20.00),
(2, 0, 9, 'UFC SWEET CHILI SAUCE_90g', '2025-02-08', 20.00),
(3, 0, 8, 'DATU PUTI SOY SAUCE_200ml', '2025-05-11', 13.00),
(4, 0, 9, 'DATU PUTI VINEGAR_200ml', '2025-12-09', 10.00),
(5, 0, 7, 'MARCA PINA SOY SAUCE_200ml', '2027-10-12', 13.00),
(6, 0, 15, 'CHEEZ WHIZ PIMIENTO_TWIN_24g', '2025-05-15', 15.00);

-- --------------------------------------------------------

--
-- Table structure for table `detergent`
--

CREATE TABLE `detergent` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` >= 0),
  `product_name` varchar(255) NOT NULL,
  `expiration_date` date DEFAULT NULL,
  `price` decimal(10,2) NOT NULL CHECK (`price` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `drinks`
--

CREATE TABLE `drinks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` >= 0),
  `product_name` varchar(255) NOT NULL,
  `expiration_date` date DEFAULT NULL,
  `price` decimal(10,2) NOT NULL CHECK (`price` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `napkins`
--

CREATE TABLE `napkins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` >= 0),
  `product_name` varchar(255) NOT NULL,
  `expiration_date` date DEFAULT NULL,
  `price` decimal(10,2) NOT NULL CHECK (`price` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `snacks`
--

CREATE TABLE `snacks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` >= 0),
  `product_name` varchar(255) NOT NULL,
  `expiration_date` date DEFAULT NULL,
  `price` decimal(10,2) NOT NULL CHECK (`price` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `toiletries`
--

CREATE TABLE `toiletries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` >= 0),
  `product_name` varchar(255) NOT NULL,
  `expiration_date` date DEFAULT NULL,
  `price` decimal(10,2) NOT NULL CHECK (`price` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(225) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','staff') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'azrael', 'azrael112', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `condiments`
--
ALTER TABLE `condiments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `detergent`
--
ALTER TABLE `detergent`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `drinks`
--
ALTER TABLE `drinks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `napkins`
--
ALTER TABLE `napkins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `snacks`
--
ALTER TABLE `snacks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `toiletries`
--
ALTER TABLE `toiletries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `condiments`
--
ALTER TABLE `condiments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `detergent`
--
ALTER TABLE `detergent`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `drinks`
--
ALTER TABLE `drinks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `napkins`
--
ALTER TABLE `napkins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `snacks`
--
ALTER TABLE `snacks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `toiletries`
--
ALTER TABLE `toiletries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
