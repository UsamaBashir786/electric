-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 10, 2026 at 12:30 PM
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
-- Table structure for table `service_requests`
--

CREATE TABLE `service_requests` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `sub_service_id` int(11) NOT NULL,
  `service_date` date NOT NULL,
  `service_time` time NOT NULL,
  `address` text NOT NULL,
  `special_instructions` text DEFAULT NULL,
  `status` enum('pending','accepted','in_progress','completed','cancelled') DEFAULT 'pending',
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(5, 4, 'test', 1200.00, 'sample data', '2026-06-10 10:25:50'),
(6, 4, 'AC Repair', 1500.00, 'Sample Description', '2026-06-10 10:26:18'),
(7, 4, 'Wall Painting', 1300.00, 'Sample Description', '2026-06-10 10:26:34'),
(8, 5, 'Wiring Installation', 1500.00, 'Sample Data', '2026-06-10 10:27:16'),
(9, 6, 'AC Repair', 3500.00, 'Sample Data', '2026-06-10 10:28:14'),
(10, 6, 'Wall Painting', 1200.00, 'Sample Description', '2026-06-10 10:28:24'),
(11, 7, 'Wiring Installation', 3500.00, 'Sample Data', '2026-06-10 10:29:52'),
(12, 7, 'AC Repair', 1500.00, 'Sample Description', '2026-06-10 10:30:03');

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
(4, 'test test', 'test@test.com', '+923196977218', '$2y$12$M7Wwg8EHvNxqy5SwN.5cc.d7yK8MphQ9T5Txd1nTqo7vpTC6lFK4.', 'electrician', '2026-06-10 10:25:50'),
(5, 'new new', 'new@new.com', '+923196977218', '$2y$12$cqprObwrw9wb2rtQBXemW.OQtRhhzdmnopYZlS5.R9eQJV20WRP56', 'plumber', '2026-06-10 10:27:16'),
(6, 'test 2', 'test2@test.com', '03196977218', '$2y$12$dSqoTfxEjyC0MStFzGLhLuPKbqgYy.1E122VrLzFdKHVP2gSfpJo2', 'painter', '2026-06-10 10:28:14'),
(7, 'test 3', 'test3@test.com', '03196977218', '$2y$12$AztpOS6JC1TpAnfxqxhcSOxAq65OEcyKxS1sfttmVkV5QmxIgRyyS', 'painter', '2026-06-10 10:29:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `provider_id` (`provider_id`),
  ADD KEY `sub_service_id` (`sub_service_id`);

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
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sub_services`
--
ALTER TABLE `sub_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD CONSTRAINT `service_requests_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_requests_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_requests_ibfk_3` FOREIGN KEY (`sub_service_id`) REFERENCES `sub_services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sub_services`
--
ALTER TABLE `sub_services`
  ADD CONSTRAINT `sub_services_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
