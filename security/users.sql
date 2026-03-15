-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 15, 2026 at 11:19 AM
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
-- Database: `furnihome`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `dob` date DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `role` enum('Admin','Member') DEFAULT 'Member',
  `status` enum('active','blocked','inactive') DEFAULT 'active',
  `remember_token` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `email_token` varchar(64) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `login_attempts` int(11) DEFAULT 0,
  `lockout_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `dob`, `contact_number`, `role`, `status`, `remember_token`, `email_verified`, `email_token`, `photo`, `created_at`, `updated_at`, `login_attempts`, `lockout_until`) VALUES
(4, 'Yong', 'Kai Quan', 'kaiquan1412@gmail.com', 'f9a054f03a3b6efb98f279a0fa74d98491df3a26', '2005-03-09', '014-2461428', 'Admin', 'active', NULL, 0, NULL, 'profile_69b665a6005b3.jpg', '2026-03-15 06:28:23', '2026-03-15 10:11:53', 0, NULL),
(5, 'Alice', 'Tan', 'alice@example.com', 'f0bd251b08338c230d420f33106faf13a12cace5', '2000-01-15', '012-3456789', 'Member', 'active', NULL, 1, NULL, 'profile_69b68355c5070.jpg', '2026-03-15 09:50:09', '2026-03-15 10:11:34', 0, NULL),
(6, 'Marcus', 'Lim', 'marcus@furni.com', '6ce9d2c28e487c77fc4143de919faf84ef411412', '1998-11-22', '011-12345678', 'Member', 'active', NULL, 1, NULL, 'profile_69b6871e5898a.jpg', '2026-03-15 09:50:09', '2026-03-15 10:17:02', 0, NULL),
(7, 'Sophia', 'Wong', 'sophia@example.com', '9e0d4995bc8b540346e8ce3e7c50ba71812faf34', '2002-05-30', '019-9876543', 'Member', 'active', NULL, 0, NULL, 'profile_69b687a77f23e.jpg', '2026-03-15 09:50:09', '2026-03-15 10:19:19', 0, NULL),
(8, 'David', 'Teoh', 'david@furni.com', '5ad7ac9412efd3cb9bc0fa558b7b880443ec30bd', '1990-07-12', '014-5642518', 'Admin', 'active', NULL, 1, NULL, 'profile_69b68770b03da.jpg', '2026-03-15 09:50:09', '2026-03-15 10:18:24', 0, NULL),
(9, 'Chloe', 'Ng', 'chloe@example.com', 'ff1fbfa801396f2f18692ff0fa86f860bfcdc35f', '2001-03-25', '012-8542157', 'Member', 'active', NULL, 1, NULL, 'profile_69b6874de821d.jpg', '2026-03-15 09:50:09', '2026-03-15 10:17:49', 0, NULL);

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
