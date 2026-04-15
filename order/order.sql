-- Create Orders Table if not exists, ensuring it has all Premium columns
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  -- Standard Enum for Order Flow
  `status` enum('Pending','Processing','Shipped','Cancelled') DEFAULT 'Pending',
  -- Premium Column 1: Payment Status (Fixed Error #1054)
  `payment_status` varchar(50) DEFAULT 'Pending',
  -- Premium Column 2: Shipping Address (Added after existing columns)
  `shipping_address` text NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
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

-- Insert 5 Orders (Using valid user_ids from existing users 4, 5, 6)
-- We must also populate the new Premium columns
INSERT INTO `orders` (`id`, `user_id`, `total_price`, `status`, `payment_status`, `shipping_address`, `created_at`) VALUES
-- Order 1: Pending Payment, Waiting Pay
(1, 5, 550.00, 'Pending', 'Pending', '123 Main St, KL', '2026-03-01 10:00:00'),
-- Order 2: Fully Paid, Ready to Process/Ship
(2, 6, 1200.00, 'Processing', 'Paid', '456 Oak Ave, Penang', '2026-03-05 14:30:00'),
-- Order 3: Shipped (Eligible for Tracking)
(3, 5, 150.00, 'Shipped', 'Paid', '123 Main St, KL', '2026-03-08 09:15:00'),
-- Order 4: Fully Cancelled
(4, 9, 899.99, 'Cancelled', 'Refunded', '789 Pine Rd, JB', '2026-03-09 16:45:00'),
-- Order 5: Waiting Pay
(5, 6, 320.00, 'Pending', 'Pending', '456 Oak Ave, Penang', '2026-03-10 11:00:00');

-- 2. Insert Furniture Items (Unchanged logic, connects to order IDs 1-5 above)
INSERT INTO `order_details` (`order_id`, `product_name`, `quantity`, `price`) VALUES
(1, 'Modern Oak Dining Table', 1, 400.00),
(1, 'Fabric Dining Chair', 2, 75.00),
(2, 'Leather L-Shape Sofa', 1, 1200.00),
(3, 'Glass Coffee Table', 1, 150.00),
(4, 'King Size Memory Foam Mattress', 1, 899.99),
(5, 'Wooden Bookshelf', 1, 250.00),
(5, 'Minimalist Bedside Lamp', 2, 35.00);