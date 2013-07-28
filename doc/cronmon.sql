-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 29, 2013 at 03:22 AM
-- Server version: 5.5.32
-- PHP Version: 5.4.6-1ubuntu1.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cronmon`
--

-- --------------------------------------------------------

--
-- Table structure for table `check_options`
--

DROP TABLE IF EXISTS `check_options`;
CREATE TABLE IF NOT EXISTS `check_options` (
  `cron_id` int(11) NOT NULL,
  `check_type` varchar(20) NOT NULL,
  `check_name` varchar(50) NOT NULL,
  `param` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`cron_id`,`check_type`,`check_name`,`param`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cronjob`
--

DROP TABLE IF EXISTS `cronjob`;
CREATE TABLE IF NOT EXISTS `cronjob` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'cron job id',
  `name` varchar(500) DEFAULT NULL COMMENT 'cron job human name',
  `command` text,
  `user` varchar(100) DEFAULT NULL,
  `host` varchar(200) DEFAULT NULL COMMENT 'hostname where the cron is executed',
  `schedule` varchar(50) DEFAULT '* * * * *' COMMENT 'cron schedule plan',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=176 ;

-- --------------------------------------------------------

--
-- Table structure for table `execution`
--

DROP TABLE IF EXISTS `execution`;
CREATE TABLE IF NOT EXISTS `execution` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'cron execution id',
  `cron_id` int(11) NOT NULL COMMENT 'cron id',
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time of the execution',
  `status` tinyint(1) NOT NULL COMMENT '0=waiting, 1=ok, 2=problem, 3=acknowledged',
  `error` text,
  PRIMARY KEY (`id`),
  KEY `cron_id` (`cron_id`),
  KEY `datetime` (`datetime`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18553 ;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

DROP TABLE IF EXISTS `notification`;
CREATE TABLE IF NOT EXISTS `notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `message` text,
  `destinataire` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- Table structure for table `proof`
--

DROP TABLE IF EXISTS `proof`;
CREATE TABLE IF NOT EXISTS `proof` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exec_id` int(11) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `type` varchar(50) NOT NULL,
  `log` text,
  `status` tinyint(4) NOT NULL,
  `error` text,
  PRIMARY KEY (`id`),
  KEY `exec_id` (`exec_id`),
  KEY `type` (`type`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Proof of a cron execution' AUTO_INCREMENT=18553 ;

-- --------------------------------------------------------

--
-- Table structure for table `unmatched_proof`
--

DROP TABLE IF EXISTS `unmatched_proof`;
CREATE TABLE IF NOT EXISTS `unmatched_proof` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `type` varchar(50) NOT NULL,
  `fromuser` varchar(100) DEFAULT NULL,
  `fromhost` varchar(100) DEFAULT NULL,
  `senderIP` int(11) DEFAULT NULL,
  `command` text,
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Proof of a cron execution' AUTO_INCREMENT=3873 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
