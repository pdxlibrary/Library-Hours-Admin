-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: mysql.lib.pdx.edu
-- Generation Time: Sep 23, 2014 at 04:18 PM
-- Server version: 5.1.73
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `library_hours`
--

-- --------------------------------------------------------

--
-- Table structure for table `psu_hours`
--

CREATE TABLE IF NOT EXISTS `psu_hours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dept` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'library',
  `date` date NOT NULL,
  `open_hour` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `close_hour` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `closed` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `exception` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT 'Are the hours for this day irregular?',
  `exception_reason` mediumtext COLLATE utf8_unicode_ci,
  `entry_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5034 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
