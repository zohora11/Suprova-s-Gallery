-- ============================================================
-- Suprova's Gallery — Complete Database
-- Database: suprovadb
-- Import directly into phpMyAdmin
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create and select database
CREATE DATABASE IF NOT EXISTS `suprovadb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `suprovadb`;

-- ============================================================
-- Table: users
-- ============================================================
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `avatar` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: categories
-- ============================================================
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: products
-- ============================================================
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(270) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL COMMENT 'JSON array of image paths',
  `sizes` varchar(255) DEFAULT NULL COMMENT 'JSON array of available sizes',
  `colors` varchar(255) DEFAULT NULL COMMENT 'JSON array of available colors',
  `stock` int(11) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_new_arrival` tinyint(1) NOT NULL DEFAULT 0,
  `is_bestseller` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `views` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_category` (`category_id`),
  KEY `idx_featured` (`is_featured`),
  KEY `idx_active` (`is_active`),
  KEY `idx_price` (`price`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: cart
-- ============================================================
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(20) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_session` (`session_id`),
  KEY `idx_product` (`product_id`),
  CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: orders
-- ============================================================
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `order_number` varchar(50) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `subtotal` decimal(10,2) NOT NULL,
  `shipping_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'cod',
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `shipping_name` varchar(100) NOT NULL,
  `shipping_email` varchar(150) NOT NULL,
  `shipping_phone` varchar(20) NOT NULL,
  `shipping_address` text NOT NULL,
  `shipping_city` varchar(100) NOT NULL,
  `shipping_state` varchar(100) NOT NULL,
  `shipping_zip` varchar(20) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: order_items
-- ============================================================
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `size` varchar(20) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`),
  KEY `idx_product` (`product_id`),
  CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: wishlist
-- ============================================================
CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  KEY `idx_product` (`product_id`),
  CONSTRAINT `fk_wish_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wish_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: contact_messages
-- ============================================================
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: newsletter_subscribers
-- ============================================================
CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Admin Account (password: admin123)
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`) VALUES
('Admin Suprova', 'admin@suprovasgallery.com', '01700000000', 'admin123', 'admin');

-- Sample Customer (password: admin123)
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`) VALUES
('Fatema Begum', 'fatema@example.com', '01711111111', 'admin123', 'customer');

-- Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `sort_order`, `is_active`) VALUES
(1, 'Sarees', 'sarees', 'Elegant and traditional sarees for every occasion', 1, 1),
(2, 'Salwar Kameez', 'salwar-kameez', 'Comfortable and stylish salwar kameez sets', 2, 1),
(3, 'Kurtis', 'kurtis', 'Trendy kurtis for casual and formal wear', 3, 1),
(4, 'Lehengas', 'lehengas', 'Bridal and party lehengas with exquisite embroidery', 4, 1),
(5, 'Tops & Blouses', 'tops-blouses', 'Modern tops and blouses for everyday fashion', 5, 1),
(6, 'Dupattas & Scarves', 'dupattas-scarves', 'Beautiful dupattas and scarves to complete your look', 6, 1);

-- Sample Products
INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `short_description`, `price`, `sale_price`, `image`, `sizes`, `colors`, `stock`, `is_featured`, `is_new_arrival`, `is_bestseller`, `is_active`) VALUES
(1, 1, 'Royal Banarasi Silk Saree', 'royal-banarasi-silk-saree', 'Luxurious Banarasi silk saree with intricate gold zari work. Perfect for weddings and festive occasions. Comes with matching blouse piece.', 'Premium Banarasi silk with gold zari embroidery', 4500.00, 3800.00, 'prod_6a34ac25edfbc.png', '["Free Size"]', '["Red","Maroon","Royal Blue","Emerald Green"]', 25, 1, 1, 1, 1),
(2, 1, 'Elegant Georgette Printed Saree', 'elegant-georgette-printed-saree', 'Lightweight georgette saree with beautiful floral print. Ideal for office wear and casual gatherings.', 'Floral georgette saree for all occasions', 1800.00, 1400.00, 'prod_6a34ac3cba895.png', '["Free Size"]', '["Pink","Lavender","Mint Green","Peach"]', 40, 1, 1, 0, 1),
(3, 2, 'Embroidered Anarkali Suit', 'embroidered-anarkali-suit', 'Stunning Anarkali suit with heavy embroidery on the yoke and hem. Includes dupatta and churidar.', 'Heavy embroidered Anarkali with dupatta', 3200.00, 2600.00, 'prod_6a34ac563bb5a.png', '["S","M","L","XL","XXL"]', '["Rose Gold","Teal","Purple","Navy"]', 30, 1, 0, 1, 1),
(4, 2, 'Cotton Printed Salwar Set', 'cotton-printed-salwar-set', 'Comfortable cotton salwar kameez with block print design. Perfect for summer days and casual outings.', 'Breezy cotton salwar set for daily wear', 1200.00, NULL, 'prod_6a34ac5e82c0c.png', '["S","M","L","XL","XXL"]', '["Yellow","Orange","Aqua","White"]', 60, 0, 1, 0, 1),
(5, 3, 'Floral Chiffon Kurti', 'floral-chiffon-kurti', 'Elegant chiffon kurti with delicate floral embroidery. Perfect for parties and festive occasions.', 'Elegant chiffon kurti with floral embroidery', 950.00, 799.00, 'prod_6a34ac6b19386.png', '["XS","S","M","L","XL","XXL"]', '["Blush Pink","Sky Blue","Mint","Ivory"]', 55, 1, 1, 1, 1),
(7, 4, 'Bridal Velvet Lehenga Set', 'bridal-velvet-lehenga-set', 'Opulent bridal lehenga in rich velvet fabric with heavy zari and stone embroidery. Includes choli and dupatta.', 'Royal bridal lehenga with heavy embellishments', 18000.00, 15000.00, 'prod_6a34ac83f3d67.png', '["S","M","L","XL"]', '["Red","Maroon","Royal Blue","Magenta"]', 10, 1, 0, 0, 1),
(12, 6, 'Printed Chiffon Stole', 'printed-chiffon-stole', 'Lightweight chiffon stole with digital print. Versatile accessory for both ethnic and western wear.', 'Versatile printed chiffon stole', 450.00, NULL, 'prod_6a34ac92cd8a9.png', '["Free Size"]', '["Multicolor","Pastel","Bold"]', 100, 0, 0, 1, 1);

COMMIT;
