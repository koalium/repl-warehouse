-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for esmartis_inv_db
CREATE DATABASE IF NOT EXISTS `esmartis_inv_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `esmartis_inv_db`;

-- Dumping structure for view esmartis_inv_db.active_contracts
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `active_contracts` (
	`id` INT(10) NOT NULL,
	`user_id` INT(10) NOT NULL,
	`contract_id` INT(10) NOT NULL,
	`start_time` TIMESTAMP NULL,
	`end_time` TIMESTAMP NULL,
	`amount` DECIMAL(18,8) NOT NULL,
	`status` ENUM('active','completed') NULL COLLATE 'utf8mb4_general_ci',
	`name` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_general_ci',
	`duration` INT(10) NOT NULL,
	`profit` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_general_ci'
) ENGINE=MyISAM;

-- Dumping structure for table esmartis_inv_db.contract
CREATE TABLE IF NOT EXISTS `contract` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `duration` int NOT NULL,
  `profit` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `img` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table esmartis_inv_db.contract: ~0 rows (approximately)

-- Dumping structure for table esmartis_inv_db.transactions
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `txhash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `txhash` (`txhash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table esmartis_inv_db.transactions: ~0 rows (approximately)

-- Dumping structure for table esmartis_inv_db.users_reg
CREATE TABLE IF NOT EXISTS `users_reg` (
  `id` int NOT NULL AUTO_INCREMENT,
  `wallet` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `parentid` int DEFAULT NULL,
  `reg_ip` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallet` (`wallet`),
  KEY `parentid` (`parentid`),
  CONSTRAINT `users_reg_ibfk_1` FOREIGN KEY (`parentid`) REFERENCES `users_reg` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table esmartis_inv_db.users_reg: ~0 rows (approximately)

-- Dumping structure for table esmartis_inv_db.user_contract
CREATE TABLE IF NOT EXISTS `user_contract` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `contract_id` int NOT NULL,
  `start_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `end_time` timestamp NULL DEFAULT NULL,
  `amount` decimal(18,8) NOT NULL,
  `status` enum('active','completed') COLLATE utf8mb4_general_ci DEFAULT 'active',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `contract_id` (`contract_id`),
  CONSTRAINT `user_contract_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_reg` (`id`),
  CONSTRAINT `user_contract_ibfk_2` FOREIGN KEY (`contract_id`) REFERENCES `contract` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table esmartis_inv_db.user_contract: ~0 rows (approximately)

-- Dumping structure for table esmartis_inv_db.user_transactions
CREATE TABLE IF NOT EXISTS `user_transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `tx_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `amount` decimal(18,8) NOT NULL,
  `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','confirmed','failed') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users_reg` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table esmartis_inv_db.user_transactions: ~0 rows (approximately)

-- Dumping structure for view esmartis_inv_db.active_contracts
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `active_contracts`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `active_contracts` AS select `uc`.`id` AS `id`,`uc`.`user_id` AS `user_id`,`uc`.`contract_id` AS `contract_id`,`uc`.`start_time` AS `start_time`,`uc`.`end_time` AS `end_time`,`uc`.`amount` AS `amount`,`uc`.`status` AS `status`,`c`.`name` AS `name`,`c`.`duration` AS `duration`,`c`.`profit` AS `profit` from (`user_contract` `uc` join `contract` `c` on((`uc`.`contract_id` = `c`.`id`))) where (`uc`.`status` = 'active');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
