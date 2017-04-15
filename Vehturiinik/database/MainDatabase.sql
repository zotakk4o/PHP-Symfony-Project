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

-- Dumping structure for table vehturiinik.category
DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `dateAdded` datetime NOT NULL,
  `dateDeleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_64C19C15E237E06` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table vehturiinik.category: ~5 rows (approximately)
DELETE FROM `category`;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;
INSERT INTO `category` (`id`, `name`, `description`, `dateAdded`, `dateDeleted`) VALUES
	(1, '123', 'E Dobre De D', '0000-00-00 00:00:00', NULL),
	(2, 'Kompiutri Edited', 'Gotini neshtica koito da se haresat na vashite dechica', '0000-00-00 00:00:00', NULL),
	(3, 'Koli', 'Neshta za vas :)', '0000-00-00 00:00:00', NULL),
	(4, 'Lubakaksi', 'Sirtaksiasd', '2017-04-12 09:05:30', NULL),
	(5, 'asdasda', 'sdada', '2017-04-12 09:06:38', NULL),
	(6, 'Elektronni Shemotehniki', 'Igraem qko cs a vladi ima da uchi', '2017-04-13 13:38:32', NULL);
/*!40000 ALTER TABLE `category` ENABLE KEYS */;

-- Dumping structure for table vehturiinik.products
DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `price` double NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `discount` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `categoryId` int(11) NOT NULL,
  `dateAdded` datetime NOT NULL,
  `dateDeleted` datetime DEFAULT NULL,
  `dateDiscountExpires` datetime DEFAULT NULL,
  `discountAdded` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_B3BA5A5A5E237E06` (`name`),
  KEY `IDX_B3BA5A5A9C370B71` (`categoryId`),
  CONSTRAINT `FK_B3BA5A5A9C370B71` FOREIGN KEY (`categoryId`) REFERENCES `category` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table vehturiinik.products: ~10 rows (approximately)
DELETE FROM `products`;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` (`id`, `name`, `price`, `description`, `discount`, `quantity`, `categoryId`, `dateAdded`, `dateDeleted`, `dateDiscountExpires`, `discountAdded`) VALUES
	(2, 'Audi s8', 1750, 'Mnogo si e burzo nqma kvo da sel afim', 0, 30133, 3, '0000-00-00 00:00:00', NULL, NULL, 0),
	(3, 'Kompec', 1000, 'E ne e s gtx 1080 ama pak staa', 0, 11, 2, '0000-00-00 00:00:00', NULL, NULL, 0),
	(5, 'Furna za jenata', 150, 'Da ne vi trovi nervite', 0, 19, 1, '0000-00-00 00:00:00', NULL, NULL, 0),
	(6, 'BMW M6', 2500, 'Da vozite prizlujnicata', 0, 13, 3, '0000-00-00 00:00:00', NULL, NULL, 0),
	(10, 'Novata Kola na mitko', 11, 'Bezcenna ama aide', 0, 12, 3, '0000-00-00 00:00:00', NULL, NULL, 0),
	(11, 'New Product', 12345, 'New ProductNew ProductNew ProductNew ProductNew Product', 0, 12, 3, '2017-04-12 10:58:22', NULL, NULL, 0),
	(12, 'New Product 2', 200, 'test', 0, 14, 5, '2017-04-12 11:08:49', NULL, NULL, 0),
	(14, 'BipoliarenTranzistor', 420, 'Mnogo gotini procesorcheta se pravqt s tezi bipoliarni tranzistori', 0, 42, 6, '2017-04-13 13:40:16', '2017-04-14 08:37:40', NULL, 0),
	(15, 'Test', 500, 'Test', 0, 2, 6, '2017-04-14 08:43:59', NULL, NULL, 0),
	(16, 'AAide tii i tiq kato teeb', 420, 'Dnes bez teb sum i na kef sum', 0, 20, 5, '2017-04-14 11:56:10', NULL, NULL, 0);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;

-- Dumping structure for table vehturiinik.purchases
DROP TABLE IF EXISTS `purchases`;
CREATE TABLE IF NOT EXISTS `purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quantity` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `productId` int(11) NOT NULL,
  `datePurchased` datetime NOT NULL,
  `quantityForSale` int(11) NOT NULL,
  `discount` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_AA6431FE64B64DCC` (`userId`),
  KEY `IDX_AA6431FE36799605` (`productId`),
  CONSTRAINT `FK_AA6431FE36799605` FOREIGN KEY (`productId`) REFERENCES `products` (`id`),
  CONSTRAINT `FK_AA6431FE64B64DCC` FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table vehturiinik.purchases: ~0 rows (approximately)
DELETE FROM `purchases`;
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
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
  `fullName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `money` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1483A5E9F85E0677` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table vehturiinik.users: ~8 rows (approximately)
DELETE FROM `users`;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`, `username`, `password`, `fullName`, `money`) VALUES
	(2, 'hacked', '$2y$13$wo1.NDtBGVGkmAxAflBuL.MDOoDULichB/BYyzxRB/rrbBsqpkWM2', 'Zotakk Funbazov', 28700),
	(3, 'Gega', '$2y$13$95HZyDdQCpt/miBb/eVNs.y9rJOUTWY1bHTEZUuyvfIaOD9esyi9S', 'Gegata Nashiq', 246946),
	(4, 'Tarikat', '$2y$13$28Buad2dLjSQROi6WyAaW.hooUxi3RpVoj5iQUIkgMAJ7jtBTCZdm', 'Tarikat', 7700),
	(5, 'draganka', '$2y$13$NdSgrZ3BYwi4IV/27SDVc.ZnTRey08t8YyzAi.sFnYCfpOvTg3HKu', 'Dragana Mirkovic', 4200),
	(16, 'asd', '$2y$13$estKpS.mETxQ35MNIIC21ux6giyuSQ6sXB9lBmd0Ky1ea7YjUruj6', 'Tarikat', 4200),
	(17, 'funbazov', '$2y$13$cy7srP39nNNCcgbtePvT0eKw0FN/MpxBCvIK1PAqvD.kj7Zm1.TWe', 'Lubomir Borisov', 503320),
	(18, 'G', '$2y$13$tsNGwo/SrRx.8ifEUC9Ss.ej2ykMR96XAVwEnfoe54wrUTTW1uPR.', 'g', 11700),
	(19, 'Username Shall Not Pass', '$2y$13$kUSVW3thwFAzqh4OUKX4qeybe5QwYspb9zdxXkBrmf0epIKINfRUO', 'Kolko po gotin full name ot nicknamea mi', 4200);
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

-- Dumping data for table vehturiinik.users_roles: ~9 rows (approximately)
DELETE FROM `users_roles`;
/*!40000 ALTER TABLE `users_roles` DISABLE KEYS */;
INSERT INTO `users_roles` (`user_id`, `role_id`) VALUES
	(2, 1),
	(2, 2),
	(3, 1),
	(3, 2),
	(4, 1),
	(5, 1),
	(16, 1),
	(17, 1),
	(17, 3),
	(18, 1),
	(19, 1);
/*!40000 ALTER TABLE `users_roles` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
