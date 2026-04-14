-- Create the orders table
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('Pending','Processing','Shipped','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Create the order details table
CREATE TABLE `order_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 1. Insert 5 Orders
INSERT INTO `orders` (`id`, `user_id`, `total_price`, `status`, `created_at`) VALUES
(1, 5, 550.00, 'Pending', '2026-03-01 10:00:00'),
(2, 6, 1200.00, 'Shipped', '2026-03-05 14:30:00'),
(3, 7, 150.00, 'Processing', '2026-03-08 09:15:00'),
(4, 8, 899.99, 'Cancelled', '2026-03-09 16:45:00'),
(5, 9, 320.00, 'Pending', '2026-03-10 11:00:00');

-- 2. Insert the specific furniture items inside those orders
INSERT INTO `order_details` (`order_id`, `product_name`, `quantity`, `price`) VALUES
-- Order 1 items (Alice bought a dining set)
(1, 'Modern Oak Dining Table', 1, 400.00),
(1, 'Fabric Dining Chair', 2, 75.00),

-- Order 2 item (Marcus bought a sofa)
(2, 'Leather L-Shape Sofa', 1, 1200.00),

-- Order 3 item (Alice bought a coffee table)
(3, 'Glass Coffee Table', 1, 150.00),

-- Order 4 item (Yong ordered and cancelled a mattress)
(4, 'King Size Memory Foam Mattress', 1, 899.99),

-- Order 5 items (Marcus bought a bookshelf and lamps)
(5, 'Wooden Bookshelf', 1, 250.00),
(5, 'Minimalist Bedside Lamp', 2, 35.00);