-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.19-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             9.4.0.5125
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for vehturiinik
DROP DATABASE IF EXISTS `vehturiinik`;
CREATE DATABASE IF NOT EXISTS `vehturiinik` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `vehturiinik`;

-- Dumping structure for table vehturiinik.categories
DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `date_added` datetime NOT NULL,
  `date_deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_3AF346685E237E06` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table vehturiinik.categories: ~2 rows (approximately)
DELETE FROM `categories`;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` (`id`, `name`, `description`, `date_added`, `date_deleted`) VALUES
	(1, 'PC COMPONENTS', 'PC COMPONENTS OFC', '2017-04-27 15:39:16', NULL),
	(2, 'Неща за жената', 'Да не ви  се  катери по главата', '2017-04-27 15:39:46', NULL),
	(3, 'Domashni Potrebnosti', 'Каквото се сетиш има тук', '2017-04-27 15:40:12', NULL),
	(4, 'Неща за жената срещу мъжа', 'Когато тя се madne', '2017-04-29 19:50:32', NULL);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;

-- Dumping structure for table vehturiinik.comments
DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  `date_added` datetime NOT NULL,
  `date_deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_5F9E962AF675F31B` (`author_id`),
  KEY `IDX_5F9E962A4584665A` (`product_id`),
  CONSTRAINT `FK_5F9E962A4584665A` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `FK_5F9E962AF675F31B` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table vehturiinik.comments: ~17 rows (approximately)
DELETE FROM `comments`;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` (`id`, `author_id`, `product_id`, `content`, `date_added`, `date_deleted`) VALUES
	(1, 1, 2, 'New Comment User Edited', '2017-04-27 15:52:27', '2017-04-27 16:02:33'),
	(2, 1, 1, 'Another One Edited', '2017-04-27 15:52:42', '2017-04-27 16:44:15'),
	(3, 1, 1, 'asfggga', '2017-04-27 16:45:15', NULL),
	(4, 1, 2, 'gdgasgas', '2017-04-27 16:46:32', '2017-04-28 14:14:28'),
	(5, 1, 2, 'Oshte edin comment', '2017-04-27 16:46:48', '2017-04-27 16:47:10'),
	(6, 1, 2, 'afafadg User Edited', '2017-04-27 17:21:44', NULL),
	(7, 3, 2, 'From gegata 1.5', '2017-04-27 17:26:28', NULL),
	(8, 3, 2, 'From Gegata 2', '2017-04-27 17:26:36', '2017-04-29 08:02:53'),
	(9, 1, 6, 'Nice And cool', '2017-04-28 14:32:32', NULL),
	(10, 1, 6, 'gasdsagsa', '2017-04-28 14:57:13', NULL),
	(11, 3, 8, 'Dano da me ostavi na mira', '2017-04-28 15:36:14', NULL),
	(12, 3, 8, 'След като и я взема', '2017-04-28 15:36:27', NULL),
	(13, 3, 6, 'ghfasafhs', '2017-04-28 15:42:00', '2017-04-28 15:42:42'),
	(14, 3, 6, 'jkjkjkjk;\'', '2017-04-28 15:42:12', NULL),
	(15, 3, 6, 'asdgsadgdsag', '2017-04-28 15:42:17', NULL),
	(16, 3, 6, 'gasfas', '2017-04-28 15:42:29', NULL),
	(17, 3, 6, 'asdgasdgas', '2017-04-28 15:43:29', NULL),
	(18, 3, 6, 'huhgkhgh', '2017-04-28 15:45:27', '2017-04-29 08:02:33'),
	(19, 4, 8, 'gagdassgsag', '2017-04-28 19:36:36', NULL),
	(20, 4, 6, 'testchence', '2017-04-28 19:37:20', '2017-04-28 19:37:32'),
	(21, 5, 8, 'Колко да е готина тази количка', '2017-04-29 07:28:40', '2017-04-29 07:29:33');
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;

-- Dumping structure for table vehturiinik.products
DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `price` double NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `quantity` int(11) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_deleted` datetime DEFAULT NULL,
  `discount` int(11) NOT NULL,
  `discount_added` tinyint(1) NOT NULL,
  `date_discount_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B3BA5A5A5E237E06` (`name`),
  KEY `IDX_B3BA5A5A12469DE2` (`category_id`),
  CONSTRAINT `FK_B3BA5A5A12469DE2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table vehturiinik.products: ~8 rows (approximately)
DELETE FROM `products`;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` (`id`, `category_id`, `name`, `price`, `description`, `quantity`, `date_added`, `date_deleted`, `discount`, `discount_added`, `date_discount_expires`) VALUES
	(1, 1, 'Mnogo divashki', 1200, 'Qko burz komp za igrici', 10, '2017-04-27 15:41:18', NULL, 0, 0, NULL),
	(2, 1, 'Oshte edin', 1000, 'Malko desktop kompecche', 100, '2017-04-27 15:42:25', NULL, 0, 0, NULL),
	(3, 1, 'New Product lol', 300, 'New Product lolNew Product lolNew Product lol', 5, '2017-04-27 22:05:39', NULL, 0, 0, NULL),
	(4, 1, 'asdaffsa', 2300, 'asfasfa', 5, '2017-04-27 22:06:09', NULL, 0, 0, NULL),
	(5, 1, 'Test It Dude', 100, 'You SHould', 5, '2017-04-27 22:15:38', NULL, 0, 0, NULL),
	(6, 3, 'fsfas', 400, 'gasgsa', 10, '2017-04-27 22:45:13', NULL, 0, 0, NULL),
	(7, 2, 'Furna', 250, 'Za Jenichkata mi', 5, '2017-04-28 13:51:00', '2017-04-29 08:24:58', 0, 0, NULL),
	(8, 2, 'Oshte edna furnichka', 200, 'Za sushtata jena  e de', 5, '2017-04-28 13:51:50', '2017-04-29 08:24:55', 0, 0, NULL),
	(9, 1, 'New', 241903, 'Product', 134, '2017-04-28 17:45:50', '2017-04-28 17:45:56', 0, 0, NULL),
	(10, 4, 'Podsilena Tochilka', 25, 'Qko metal da go boli svinqta', 10, '2017-04-19 19:51:21', NULL, 0, 0, NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;

-- Dumping structure for table vehturiinik.purchases
DROP TABLE IF EXISTS `purchases`;
CREATE TABLE IF NOT EXISTS `purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity_bought` int(11) NOT NULL,
  `current_quantity` int(11) NOT NULL,
  `quantity_for_sale` int(11) NOT NULL,
  `discount` int(11) NOT NULL,
  `date_purchased` datetime NOT NULL,
  `date_deleted` datetime DEFAULT NULL,
  `price_per_piece` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_AA6431FEA76ED395` (`user_id`),
  KEY `IDX_AA6431FE4584665A` (`product_id`),
  CONSTRAINT `FK_AA6431FE4584665A` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `FK_AA6431FEA76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table vehturiinik.purchases: ~9 rows (approximately)
DELETE FROM `purchases`;
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
INSERT INTO `purchases` (`id`, `user_id`, `product_id`, `quantity_bought`, `current_quantity`, `quantity_for_sale`, `discount`, `date_purchased`, `date_deleted`, `price_per_piece`) VALUES
	(1, 1, 2, 5, 0, 0, 10, '2017-04-27 15:59:53', '2017-04-27 16:00:59', 1000),
	(2, 2, 8, 5, 5, 5, 5, '2017-04-29 08:01:52', NULL, 200),
	(3, 1, 6, 5, 0, 0, 0, '2017-04-28 14:58:36', '2017-04-28 14:58:47', 400),
	(4, 5, 8, 5, 0, 0, 0, '2017-04-29 07:31:40', '2017-04-29 07:31:53', 200),
	(5, 5, 7, 3, 0, 0, 0, '2017-04-29 07:28:03', '2017-04-29 07:30:28', 250),
	(6, 1, 5, 5, 0, 0, 42, '2017-04-29 07:38:11', '2017-04-29 07:42:11', 100),
	(7, 3, 7, 5, 5, 5, 5, '2017-04-29 08:01:52', NULL, 250),
	(16, 1, 4, 2, 0, 0, 0, '2017-04-29 07:59:52', '2017-04-29 07:59:59', 2300),
	(17, 1, 8, 5, 0, 0, 5, '2017-04-29 08:20:54', '2017-04-29 08:21:18', 200);
/*!40000 ALTER TABLE `purchases` ENABLE KEYS */;

-- Dumping structure for table vehturiinik.roles
DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B63E2EC75E237E06` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table vehturiinik.roles: ~2 rows (approximately)
DELETE FROM `roles`;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` (`id`, `name`) VALUES
	(3, 'ROLE_ADMIN'),
	(2, 'ROLE_EDITOR'),
	(1, 'ROLE_USER');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;

-- Dumping structure for table vehturiinik.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `money` double NOT NULL,
  `date_registered` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1483A5E9F85E0677` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table vehturiinik.users: ~2 rows (approximately)
DELETE FROM `users`;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `money`, `date_registered`) VALUES
	(1, 'funbazov', '$2y$13$5D/BcbY44ZxRf.72guBmZOxl7VlxE7X0nKjplL00BG2nxZoC.p/d6', 'funbazeto ve', 8092.5, '2017-04-27 15:37:15'),
	(2, 'gesh', '$2y$13$nr7zEjjJnm4obHWTWz3kxeA2eP/PDJqbEffEOu3jItWfmnR/KAzKu', 'geshata geshov', 4200, '2017-04-27 15:38:09'),
	(3, 'gega', '$2y$13$MNu7LHRepSioHY5VGZR1d.dvN795yebvMjxB.CQLH1HF07HxSaMzy', 'gegata', 4200, '2017-04-27 15:38:21'),
	(4, 'Opashati mi priqteliu', '$2y$13$dDPzMYbUgyCh9vYXdzWI8.d/YXtG6z9Q.xj0blbBiu2vXfd4pY9ZW', 'Opashati mi priqteliu', 4200, '2017-04-28 19:35:28'),
	(5, 'test', '$2y$13$BbFKYoEiMITd3ZhmVof9qu/jwJTVnrKOxfIWhuNWjDUfIVcMPq0O.', 'Test User', 4200, '2017-04-29 07:24:26');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

-- Dumping structure for table vehturiinik.users_roles
DROP TABLE IF EXISTS `users_roles`;
CREATE TABLE IF NOT EXISTS `users_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `IDX_51498A8EA76ED395` (`user_id`),
  KEY `IDX_51498A8ED60322AC` (`role_id`),
  CONSTRAINT `FK_51498A8EA76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `FK_51498A8ED60322AC` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table vehturiinik.users_roles: ~6 rows (approximately)
DELETE FROM `users_roles`;
/*!40000 ALTER TABLE `users_roles` DISABLE KEYS */;
INSERT INTO `users_roles` (`user_id`, `role_id`) VALUES
	(1, 1),
	(1, 2),
	(1, 3),
	(2, 1),
	(2, 2),
	(3, 1),
	(4, 1),
	(5, 1);
/*!40000 ALTER TABLE `users_roles` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
