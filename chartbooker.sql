-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 21, 2013 at 09:04 AM
-- Server version: 5.5.29
-- PHP Version: 5.3.10-1ubuntu3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `chartbooker`
--

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE IF NOT EXISTS `comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` varchar(20) NOT NULL,
  `author` varchar(225) NOT NULL,
  `fair` int(11) NOT NULL,
  `exhibitor` int(11) NOT NULL,
  `position` varchar(50) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE IF NOT EXISTS `currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` varchar(20) NOT NULL,
  `to` varchar(20) NOT NULL,
  `value` varchar(50) NOT NULL,
  `lastupdate` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `exhibitor`
--

CREATE TABLE IF NOT EXISTS `exhibitor` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(9) unsigned NOT NULL,
  `fair` int(9) unsigned NOT NULL,
  `position` int(9) unsigned NOT NULL,
  `category` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `presentation` text COLLATE utf8_unicode_ci NOT NULL,
  `commodity` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `arranger_message` text COLLATE utf8_unicode_ci NOT NULL,
  `approved` tinyint(1) NOT NULL,
  `invoice_sent` tinyint(1) NOT NULL,
  `invoice_message` int(11) NOT NULL,
  `booking_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `user` (`user`),
  KEY `position` (`position`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exhibitor_category`
--

CREATE TABLE IF NOT EXISTS `exhibitor_category` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `fair` int(9) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fair` (`fair`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exhibitor_category_rel`
--

CREATE TABLE IF NOT EXISTS `exhibitor_category_rel` (
  `exhibitor` int(9) unsigned NOT NULL,
  `category` int(9) unsigned NOT NULL,
  KEY `exhibitor` (`exhibitor`,`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fair`
--

CREATE TABLE IF NOT EXISTS `fair` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `logotype` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `windowtitle` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `contact_info` text COLLATE utf8_unicode_ci NOT NULL,
  `created_by` int(9) unsigned NOT NULL,
  `creation_time` int(10) unsigned NOT NULL,
  `closing_time` int(10) unsigned NOT NULL,
  `page_views` int(9) unsigned NOT NULL,
  `approved` tinyint(4) NOT NULL,
  `auto_publish` int(10) unsigned NOT NULL,
  `auto_close` int(10) unsigned NOT NULL,
  `max_positions` int(9) unsigned NOT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fair_map`
--

CREATE TABLE IF NOT EXISTS `fair_map` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `fair` int(9) unsigned NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fair` (`fair`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fair_map_position`
--

CREATE TABLE IF NOT EXISTS `fair_map_position` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `map` int(9) unsigned NOT NULL,
  `x` double NOT NULL,
  `y` double NOT NULL,
  `area` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `information` text COLLATE utf8_unicode_ci NOT NULL,
  `status` int(2) NOT NULL,
  `expires` date NOT NULL,
  `created_by` int(9) unsigned NOT NULL,
  `being_edited` int(9) unsigned NOT NULL,
  `edit_started` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `map` (`map`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fair_user_relation`
--

CREATE TABLE IF NOT EXISTS `fair_user_relation` (
  `fair` int(9) unsigned NOT NULL,
  `user` int(9) unsigned NOT NULL,
  `fair_presentation` text COLLATE utf8_swedish_ci NOT NULL,
  `map_access` varchar(128) COLLATE utf8_swedish_ci NOT NULL,
  `connected_time` int(11) NOT NULL,
  KEY `fair` (`fair`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `language`
--

CREATE TABLE IF NOT EXISTS `language` (
  `id` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `language_string`
--

CREATE TABLE IF NOT EXISTS `language_string` (
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lang` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `group` int(9) NOT NULL,
  KEY `group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `user` int(9) unsigned NOT NULL,
  `action` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `data` longtext COLLATE utf8_swedish_ci NOT NULL,
  KEY `user` (`user`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mail_content`
--

CREATE TABLE IF NOT EXISTS `mail_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mail` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `language` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mail` (`mail`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_content`
--

CREATE TABLE IF NOT EXISTS `page_content` (
  `page` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `language` varchar(4) COLLATE utf8_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
  KEY `page` (`page`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `preliminary_booking`
--

CREATE TABLE IF NOT EXISTS `preliminary_booking` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(9) unsigned NOT NULL,
  `fair` int(9) unsigned NOT NULL,
  `position` int(9) unsigned NOT NULL,
  `categories` varchar(255) COLLATE utf8_swedish_ci NOT NULL,
  `commodity` varchar(64) COLLATE utf8_swedish_ci NOT NULL,
  `arranger_message` varchar(255) COLLATE utf8_swedish_ci NOT NULL,
  `booking_time` int(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `alias` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `customer_nr` varchar(24) COLLATE utf8_unicode_ci NOT NULL,
  `company` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `orgnr` varchar(24) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `zipcode` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `invoice_company` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `invoice_address` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `invoice_zipcode` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `invoice_city` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `invoice_email` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `invoice_country` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `presentation` text COLLATE utf8_unicode_ci NOT NULL,
  `category` int(9) unsigned NOT NULL,
  `phone1` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `contact_phone` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `phone2` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `contact_phone2` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `contact_email` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `fax` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `level` varchar(24) COLLATE utf8_unicode_ci NOT NULL,
  `owner` int(9) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `last_login` int(10) unsigned NOT NULL,
  `total_logins` int(9) unsigned NOT NULL,
  `password_changed` int(10) NOT NULL,
  `locked` tinyint(1) NOT NULL,
  `commodity` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `alias` (`alias`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_ban`
--

CREATE TABLE IF NOT EXISTS `user_ban` (
  `user` int(9) unsigned NOT NULL,
  `organizer` int(9) unsigned NOT NULL,
  `reason` text COLLATE utf8_swedish_ci NOT NULL,
  KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms`
--

CREATE TABLE `sms` (
`id` int(10) unsigned NOT NULL,
  `fair_id` int(10) unsigned NOT NULL,
  `author_user_id` int(10) unsigned NOT NULL,
  `text` text COLLATE utf8_swedish_ci NOT NULL,
  `num_texts` tinyint(3) unsigned NOT NULL,
  `sent_time` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

ALTER TABLE `sms`
	ADD PRIMARY KEY (`id`), ADD KEY `fair` (`fair_id`,`author_user_id`);
ALTER TABLE `sms`
	MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Table structure for table `sms_recipient`
--

CREATE TABLE `sms_recipient` (
`sms_id` int(11) NOT NULL,
  `rec_user_id` int(11) NOT NULL,
  `phone` varchar(50) COLLATE utf8_swedish_ci NOT NULL,
  `sent_status` tinyint(3) unsigned NOT NULL,
  `delivery_status` tinyint(3) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

ALTER TABLE `sms_recipient`
	ADD PRIMARY KEY (`sms_id`,`phone`);
ALTER TABLE `sms_recipient`
	MODIFY `sms_id` int(11) NOT NULL AUTO_INCREMENT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
