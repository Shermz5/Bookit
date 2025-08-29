-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 19, 2025 at 11:53 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `accommodation_rental`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(250) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password_hash`) VALUES
(12, 'root', '$2y$10$bTeSg6yiMnFYTXoowF1p0.Hon2K.7E4Iz4Lv2eq/pw.3sckq3NJ2a'),
(11, 'manager', '$2y$10$zl9WsF9ExeyeBEA8VRoJqOQVobUyaohZli7FqdjimhzOKpgeSOMM'),
(10, 'superuser', '$2y$10$H.67bOWVoMuTzVl5A8MpEeeAOkqDb4YgdWEtW.t8Z93IsY6Igf9Ka'),
(9, 'admin2', '$2y$10$3zHA.8PEbjA/68YPUJy8JuyrLsYWka6ZE2voL80TZmGaC0eF/7ori'),
(8, 'admin1', '$2y$10$Ks.vfX4Dw68RJ3J42h266OHyl7v/MaIkHWQgFpEdqLrfshRSsR9tK');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `property_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `listing_id` int NOT NULL,
  `user_id` int NOT NULL,
  `move_in` date NOT NULL,
  `move_out` date NOT NULL,
  `occupants` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `listing_id` (`listing_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `property_name`, `guest_name`, `listing_id`, `user_id`, `move_in`, `move_out`, `occupants`, `price`, `created_at`, `status`) VALUES
(8, '', '', 9, 8, '2025-07-01', '2025-09-05', 1, 460.00, '2025-06-18 14:28:19', 'confirmed'),
(10, '', '', 9, 8, '2025-06-19', '2025-08-19', 4, 460.00, '2025-06-19 11:28:11', 'confirmed'),
(9, '', '', 9, 6, '2025-06-19', '2025-07-19', 1, 460.00, '2025-06-19 11:24:02', 'cancelled'),
(7, '', '', 9, 8, '2025-07-02', '2025-07-11', 4, 460.00, '2025-06-16 15:30:35', 'confirmed');

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

DROP TABLE IF EXISTS `listings`;
CREATE TABLE IF NOT EXISTS `listings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `bedrooms` int NOT NULL,
  `bathrooms` int NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `zip` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `amenities` text,
  `rental_conditions` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `host_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_host` (`host_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `listings`
--

INSERT INTO `listings` (`id`, `title`, `type`, `price`, `bedrooms`, `bathrooms`, `address`, `city`, `state`, `zip`, `country`, `description`, `amenities`, `rental_conditions`, `created_at`, `host_id`) VALUES
(9, '4 roomed house', 'house', 460.00, 3, 1, '55 Avonlea Rd', 'Bulawayo', 'Bulawayo', '0000', 'Zimbabwe', 'Discover comfort and convenience in this generously sized 4-roomed apartment, perfect for families or professionals seeking extra space. Featuring a bright and airy layout, the apartment boasts a spacious living room ideal for relaxation and entertaining, along with three well-proportioned bedrooms, ensuring everyone has their own personal retreat.\r\n\r\nEnjoy the fully equipped kitchen, complete with modern appliances and ample counter space, making cooking a breeze. A sleek, contemporary bathroom adds a touch of luxury, while large windows throughout the unit bring in plenty of natural light.', 'solar,kitchen,backyard,parking', 'no_pets,short_term,couples,singles,bills_excluded', '2025-06-05 09:26:31', 6);

-- --------------------------------------------------------

--
-- Table structure for table `listing_images`
--

DROP TABLE IF EXISTS `listing_images`;
CREATE TABLE IF NOT EXISTS `listing_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `listing_id` int NOT NULL,
  `image_url` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `listing_id` (`listing_id`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `listing_images`
--

INSERT INTO `listing_images` (`id`, `listing_id`, `image_url`) VALUES
(30, 9, 'uploads/684162c7e7f35_house1.png'),
(29, 9, 'uploads/684162c7e7b82_house2.png'),
(28, 9, 'uploads/684162c7e7522_house3.png'),
(27, 9, 'uploads/684162c7e6ed3_house4.png'),
(26, 9, 'uploads/684162c7e6b10_house5.png');

-- --------------------------------------------------------

--
-- Table structure for table `payment_info`
--

DROP TABLE IF EXISTS `payment_info`;
CREATE TABLE IF NOT EXISTS `payment_info` (
  `id` int NOT NULL AUTO_INCREMENT,
  `mastercard_number` varchar(20) DEFAULT NULL,
  `ecocash_number` varchar(20) DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user_payment` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payment_info`
--

INSERT INTO `payment_info` (`id`, `mastercard_number`, `ecocash_number`, `submitted_at`, `user_id`) VALUES
(4, '', '0774003861', '2025-06-13 12:36:54', 7),
(6, '5555663348899222', '0774003861', '2025-06-16 19:48:02', 6);

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

DROP TABLE IF EXISTS `payment_methods`;
CREATE TABLE IF NOT EXISTS `payment_methods` (
  `id` int NOT NULL AUTO_INCREMENT,
  `method_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `method_name`) VALUES
(1, 'Mastercard'),
(2, 'EcoCash');

-- --------------------------------------------------------

--
-- Table structure for table `payment_records`
--

DROP TABLE IF EXISTS `payment_records`;
CREATE TABLE IF NOT EXISTS `payment_records` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `listing_id` int NOT NULL,
  `owner_id` int NOT NULL,
  `payment_method_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `booking_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `listing_id` (`listing_id`),
  KEY `owner_id` (`owner_id`),
  KEY `payment_method_id` (`payment_method_id`),
  KEY `booking_id` (`booking_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `payment_records`
--

INSERT INTO `payment_records` (`id`, `user_id`, `listing_id`, `owner_id`, `payment_method_id`, `amount`, `created_at`, `booking_id`) VALUES
(1, 8, 9, 6, 2, 460.00, '2025-06-16 19:22:43', 7),
(2, 8, 9, 6, 1, 460.00, '2025-06-16 19:24:08', 7),
(3, 8, 9, 6, 2, 460.00, '2025-06-16 19:26:11', 7),
(4, 8, 9, 6, 2, 460.00, '2025-06-18 14:31:07', 7),
(5, 8, 9, 6, 2, 450.00, '2025-06-19 11:43:11', 10);

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

DROP TABLE IF EXISTS `profile`;
CREATE TABLE IF NOT EXISTS `profile` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `bio` text,
  `profile_pic` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `profile`
--

INSERT INTO `profile` (`id`, `first_name`, `last_name`, `username`, `bio`, `profile_pic`) VALUES
(8, 'Amunike', 'Sibanibani', 'Amunikeqtip', 'Best Tenant you could ever find.', 'uploads/profile_images/profile_8_1750110542.png'),
(6, 'Sherman', 'Mehlo', 'Walter', 'The best landlord in town. Get the best property you want at an affordable price, just check all my  properties and choose for yourself', 'uploads/profile_images/profile_6_1749031516.png'),
(7, '', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `profile_contact_details`
--

DROP TABLE IF EXISTS `profile_contact_details`;
CREATE TABLE IF NOT EXISTS `profile_contact_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `profile_contact_details`
--

INSERT INTO `profile_contact_details` (`id`, `user_id`, `email`, `phone`, `address`, `country`, `city`, `state`, `zip`) VALUES
(1, 6, 'shermanmehlo2.0@gmail.com', '774003861', 'Earth', 'Zimbabwe', 'Harare', 'MAT NORTH', '00000'),
(2, 8, 'choplife@gmail.com', '0774003861', '123 Sydney Rd', 'Zimbabwe', 'Vic falls', 'MAT NORTH', '00000');

-- --------------------------------------------------------

--
-- Table structure for table `queries`
--

DROP TABLE IF EXISTS `queries`;
CREATE TABLE IF NOT EXISTS `queries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `queries`
--

INSERT INTO `queries` (`id`, `username`, `email`, `subject`, `message`, `submitted_at`) VALUES
(2, 'Walter', 'walterwhite@gmail.com', 'Property Pictures', 'the pictures sent are not enough to verify the property, please increase the amount of properties a user can post', '2025-06-16 13:59:19');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `listing_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` float NOT NULL,
  `review_text` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `listing_id` (`listing_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `listing_id`, `user_id`, `rating`, `review_text`, `created_at`) VALUES
(1, 6, 6, 4, 'I love this place. Perfect fit for me.', '2025-06-04 15:23:40'),
(2, 9, 8, 4, 'this has always been the property i have wanted', '2025-06-16 22:29:33');

-- --------------------------------------------------------

--
-- Table structure for table `suspended_users`
--

DROP TABLE IF EXISTS `suspended_users`;
CREATE TABLE IF NOT EXISTS `suspended_users` (
  `id` int NOT NULL DEFAULT '0',
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `country_code` varchar(5) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `agreed_to_terms` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `country_code` varchar(5) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `agreed_to_terms` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `country_code`, `phone`, `username`, `password_hash`, `agreed_to_terms`, `created_at`) VALUES
(6, 'Walter', 'White', 'example@example.com', '+263', '1234567890', 'Walter', '$2y$10$9U5u2HU/RdviO3FRw3dq5.pEf0EbUIosuVVYUdxoQJsmxiuDYTAYW', 0, '2025-05-27 18:09:34'),
(7, 'Jesse', 'Pinkman', 'jessepinkman@gmail.com', '+263', '774003861', 'Jesse', '$2y$10$z5WkXOTbImJVCj5JoMoWm.LvCyHmN4eXpdNfLEz5w36ZOC0uFhle.', 0, '2025-06-02 22:28:51'),
(8, 'Amunike', 'Sibanibani', 'homysbxsam@gmail.com', '+263', '774003861', 'Amunikeqtip', '$2y$10$Wi1z0z7e/qukk6cM/jcyJe5bF.Rr4ECwMRx0DIpfLwkxH5geZSlpC', 0, '2025-06-16 15:29:31');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
