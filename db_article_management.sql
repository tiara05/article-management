-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.7.39 - MySQL Community Server (GPL)
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


-- Dumping database structure for article_management
CREATE DATABASE IF NOT EXISTS `article_management` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `article_management`;

-- Dumping structure for table article_management.articles
CREATE TABLE IF NOT EXISTS `articles` (
  `article_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(500) NOT NULL,
  `content` longtext,
  `author` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `tags` text,
  `views` int(11) DEFAULT '0',
  `likes` int(11) DEFAULT '0',
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`article_id`),
  KEY `author` (`author`),
  KEY `category_id` (`category_id`),
  KEY `idx_articles_status` (`status`),
  KEY `idx_articles_views` (`views`),
  CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`author`) REFERENCES `users` (`user_id`),
  CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table article_management.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) DEFAULT NULL,
  `parent_category` int(11) DEFAULT NULL,
  PRIMARY KEY (`category_id`),
  KEY `parent_category` (`parent_category`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_category`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table article_management.comments
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `comment` text,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_approved` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_comments_article_id` (`article_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`article_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table article_management.media
CREATE TABLE IF NOT EXISTS `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` text,
  `path` text,
  `type` varchar(50) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `article_id` int(11) DEFAULT NULL,
  `uploaded` datetime DEFAULT CURRENT_TIMESTAMP,
  `uploader` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_media_article_id` (`article_id`),
  CONSTRAINT `media_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`article_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

-- Dumping structure for table article_management.users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` text NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_info` text,
  `role` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
