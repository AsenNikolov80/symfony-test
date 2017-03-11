/*
SQLyog Ultimate v11.5 (64 bit)
MySQL - 5.6.21 : Database - symfony-test
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `bank_account_transactions` */

DROP TABLE IF EXISTS `bank_account_transactions`;

CREATE TABLE `bank_account_transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bank_account` int(10) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `balance` decimal(10,2) NOT NULL,
  `date_created` datetime NOT NULL,
  `parent_tx` int(10) unsigned DEFAULT NULL,
  `revert_transaction` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bank_account` (`bank_account`),
  KEY `bank_account_transactions_ibfk_3` (`revert_transaction`),
  KEY `parent_tx` (`parent_tx`),
  CONSTRAINT `bank_account_transactions_ibfk_1` FOREIGN KEY (`bank_account`) REFERENCES `bank_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bank_account_transactions_ibfk_3` FOREIGN KEY (`revert_transaction`) REFERENCES `bank_account_transactions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bank_account_transactions_ibfk_4` FOREIGN KEY (`parent_tx`) REFERENCES `bank_account_transactions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Data for the table `bank_account_transactions` */

/*Table structure for table `bank_accounts` */

DROP TABLE IF EXISTS `bank_accounts`;

CREATE TABLE `bank_accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `name` varchar(50) NOT NULL,
  `balance` decimal(10,2) NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `bank_accounts_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;

/*Data for the table `bank_accounts` */

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `date_created` datetime NOT NULL,
  `password` varchar(200) NOT NULL,
  `role` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8;

/*Data for the table `users` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
