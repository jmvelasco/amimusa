-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2+deb7u1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 02, 2015 at 10:15 PM
-- Server version: 5.5.44
-- PHP Version: 5.4.41-0+deb7u1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `amimusa`
--

-- --------------------------------------------------------

--
-- Table structure for table `contributors`
--

CREATE TABLE IF NOT EXISTS `contributors` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `email` varchar(80) NOT NULL,
  `description` mediumtext,
  `link_to_profile` varchar(80) DEFAULT NULL,
  `inscription_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `username` varchar(45) NOT NULL,
  `password` varchar(128) NOT NULL,
  `security_token` varchar(128) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `username_UNIQUE` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=47 ;

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE IF NOT EXISTS `likes` (
  `id_publication` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `referer` varchar(255) NOT NULL,
  KEY `id_publication` (`id_publication`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `musas`
--

CREATE TABLE IF NOT EXISTS `musas` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`),
  KEY `name_IDX` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=225 ;

-- --------------------------------------------------------

--
-- Table structure for table `publications`
--

CREATE TABLE IF NOT EXISTS `publications` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_contributor` int(11) unsigned NOT NULL,
  `id_writting` int(11) unsigned NOT NULL,
  `id_state` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_contributor_idx` (`id_contributor`),
  KEY `fk_writting_idx` (`id_writting`),
  KEY `fk_state_idx` (`id_state`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=91 ;

-- --------------------------------------------------------

--
-- Table structure for table `publications_musas`
--

CREATE TABLE IF NOT EXISTS `publications_musas` (
  `id_publication` int(11) unsigned NOT NULL,
  `id_musa` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id_publication`,`id_musa`),
  KEY `fk_musa_idx` (`id_musa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `publications_type`
--

CREATE TABLE IF NOT EXISTS `publications_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE IF NOT EXISTS `states` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `writtings`
--

CREATE TABLE IF NOT EXISTS `writtings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(80) NOT NULL,
  `body` longtext,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modification_date` timestamp NULL DEFAULT NULL,
  `publication_type` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_publication_type_idx` (`publication_type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=111 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `publications`
--
ALTER TABLE `publications`
  ADD CONSTRAINT `fk_contributor` FOREIGN KEY (`id_contributor`) REFERENCES `contributors` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_state` FOREIGN KEY (`id_state`) REFERENCES `states` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_writting` FOREIGN KEY (`id_writting`) REFERENCES `writtings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `publications_musas`
--
ALTER TABLE `publications_musas`
  ADD CONSTRAINT `fk_musa` FOREIGN KEY (`id_musa`) REFERENCES `musas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_publication` FOREIGN KEY (`id_publication`) REFERENCES `publications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `writtings`
--
ALTER TABLE `writtings`
  ADD CONSTRAINT `fk_publication_type` FOREIGN KEY (`publication_type`) REFERENCES `publications_type` (`id`) ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
