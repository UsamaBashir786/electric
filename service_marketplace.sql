-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 10, 2026 at 11:50 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `service_marketplace`
--

-- --------------------------------------------------------

--
-- Table structure for table `sub_services`
--

CREATE TABLE `sub_services` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sub_service_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_services`
--

INSERT INTO `sub_services` (`id`, `user_id`, `sub_service_name`, `price`, `description`, `created_at`) VALUES
(2, 1, 'test', 1500.00, 'sample text', '2026-06-10 09:22:16'),
(3, 2, 'test 1', 1200.00, 'sample data', '2026-06-10 09:44:19'),
(5, 2, 'test', 1500.00, 'test', '2026-06-10 09:46:59'),
(6, 2, 'test 2', 2300.00, 'sample data', '2026-06-10 09:47:11');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `service_type` enum('electrician','plumber','painter') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `service_type`, `created_at`) VALUES
(1, 'test', 'test@test.com', '03196977218', '$2y$12$ZrlvJDA3m0.UGC9Sh8EIIeMMJpzR69tyfqL6z1X2MoyW8xAYLssgq', 'electrician', '2026-06-10 09:16:33'),
(2, 'new', 'new@new.com', '03196977218', '$2y$12$qt7lUiv6OT.2FhF6qjRXTeHJASQBEyCRxnKOEa3VIKNv2hd74WTk6', 'electrician', '2026-06-10 09:44:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sub_services`
--
ALTER TABLE `sub_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sub_services`
--
ALTER TABLE `sub_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sub_services`
--
ALTER TABLE `sub_services`
  ADD CONSTRAINT `sub_services_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
