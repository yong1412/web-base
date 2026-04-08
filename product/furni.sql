-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 07, 2026 at 08:49 AM
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
-- Database: `furni`
--

-- --------------------------------------------------------

--
-- Table structure for table `bedroom`
--
-- Error reading structure for table furni.bedroom: #1932 - Table &#039;furni.bedroom&#039; doesn&#039;t exist in engine
-- Error reading data for table furni.bedroom: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `furni`.`bedroom`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `hover_image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `sub_category` varchar(100) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `video_path` varchar(255) DEFAULT NULL,
  `image_hover` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `product_name`, `price`, `image_path`, `hover_image`, `category`, `sub_category`, `stock`, `video_path`, `image_hover`) VALUES
(1, 'Modern Wooden Bed Frame', 799.00, 'uploads/pillow.jpg', 'uploads/69d1ed280c5e7.jpg', 'Bed', 'Bed Frame', 10, NULL, NULL),
(2, 'Luxury King Bed Frame', 1299.00, 'bed-pdt/bed2.jpg', NULL, 'bedroom', 'Bed Frame', 0, NULL, NULL),
(3, 'Premium Comfort Mattress', 599.00, 'bed-pdt/mattress1.jpg', NULL, 'bedroom', 'Mattress', 0, NULL, NULL),
(4, 'Orthopedic Mattress', 899.00, 'bed-pdt/mattress2.jpg', NULL, 'bedroom', 'Mattress', 0, NULL, NULL),
(5, 'Soft Pillow Set', 99.00, 'bed-pdt/pillow1.jpg', NULL, 'bedroom', 'Pillow', 0, NULL, NULL),
(6, 'Memory Foam Pillow', 149.00, 'bed-pdt/pillow2.jpg', NULL, 'bedroom', 'Pillow', 0, NULL, NULL),
(7, 'New Arrival Bed Set', 999.00, 'bed-pdt/newbed.jpg', NULL, 'bedroom', 'New', 0, NULL, NULL),
(8, 'Discount Bed Frame', 499.00, 'bed-pdt/salebed.jpg', NULL, 'bedroom', 'Sale', 0, NULL, NULL),
(9, 'Modern Grey Sofa', 899.00, 'uploads/1-001.jpg', 'uploads/69d151038985e.jpg', 'Sofa', '1-seater', 50, NULL, NULL),
(10, 'Luxury Leather Sofa', 1299.00, 'uploads/1775322429_main_M-002.jpg', 'uploads/1775322429_hover_4-sofa.webp', 'Sofa', '2-seater', 1, NULL, NULL),
(11, 'Single Cozy Sofa', 299.00, 'sofa-pdt/1seater.jpg', NULL, 'sofa', '1 Seater', 0, NULL, NULL),
(12, 'Compact 2 Seater Sofa', 499.00, 'sofa-pdt/2seater.jpg', NULL, 'sofa', '2 Seater', 0, NULL, NULL),
(13, 'Family 3 Seater Sofa', 699.00, 'sofa-pdt/3seater.jpg', NULL, 'sofa', '3 Seater', 0, NULL, NULL),
(14, 'Large 4 Seater Sofa', 999.00, 'sofa-pdt/4seater.jpg', NULL, 'sofa', '4 Seater', 0, NULL, NULL),
(15, 'Comfort Ottoman', 199.00, 'sofa-pdt/ottoman.jpg', NULL, 'sofa', 'Ottoman', 0, NULL, NULL),
(16, 'Recliner Sofa Deluxe', 1099.00, 'sofa-pdt/recliner.jpg', NULL, 'sofa', 'Recliner', 0, NULL, NULL),
(17, 'L-Shape Sectional Sofa', 1399.00, 'sofa-pdt/sectional.jpg', NULL, 'sofa', 'Sectionals', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--
-- Error reading structure for table furni.products: #1932 - Table &#039;furni.products&#039; doesn&#039;t exist in engine
-- Error reading data for table furni.products: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `furni`.`products`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`) VALUES
(1, 10, 'cate.png'),
(2, 10, 'sofa-pdt\\cat-sofa\\1-sofa.png'),
(3, 10, 'images/sofa4.jpg'),
(4, 9, 'sofa-pdt\\cat-sofa\\1-sofa.png'),
(5, 9, 'sofa-pdt\\cat-sofa\\2-sofa.png'),
(18, 9, 'uploads/69d2cc94754c1.png');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `product_id`, `created_at`) VALUES
(43, 9, '2026-04-04 16:05:26'),
(44, 10, '2026-04-05 05:06:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
