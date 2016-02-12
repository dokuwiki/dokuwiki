-- phpMyAdmin SQL Dump
-- version 4.0.10.7
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Feb 10, 2016 at 02:02 PM
-- Server version: 10.0.23-MariaDB
-- PHP Version: 5.4.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `dokuwiki_wp240`
--

-- --------------------------------------------------------

--
-- Table structure for table `wpvk_usermeta`
--

CREATE TABLE IF NOT EXISTS `wpvk_usermeta` (
  `umeta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) DEFAULT NULL,
  `meta_value` longtext,
  PRIMARY KEY (`umeta_id`),
  KEY `user_id` (`user_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=52 ;

--
-- Dumping data for table `wpvk_usermeta`
--

INSERT INTO `wpvk_usermeta` (`umeta_id`, `user_id`, `meta_key`, `meta_value`) VALUES
(1, 1, 'nickname', 'admin'),
(2, 1, 'first_name', 'First'),
(3, 1, 'last_name', 'Last'),
(4, 1, 'description', ''),
(5, 1, 'rich_editing', 'true'),
(6, 1, 'comment_shortcuts', 'false'),
(7, 1, 'admin_color', 'fresh'),
(8, 1, 'use_ssl', '0'),
(9, 1, 'show_admin_bar_front', 'true'),
(10, 1, 'wpvk_capabilities', 'a:1:{s:13:"administrator";b:1;}'),
(11, 1, 'wpvk_user_level', '10'),
(12, 1, 'dismissed_wp_pointers', ''),
(13, 1, 'show_welcome_panel', '1'),
(14, 1, 'session_tokens', 'a:1:{s:64:"3e9f99a7068bf3fb79f50e111b6ef10f599beb466c27152205d4b89360c5004d";a:4:{s:10:"expiration";i:1456340157;s:2:"ip";s:12:"86.56.56.217";s:2:"ua";s:104:"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.82 Safari/537.36";s:5:"login";i:1455130557;}}'),
(15, 1, 'wpvk_dashboard_quick_press_last_post_id', '3'),
(16, 2, 'nickname', 'test1'),
(17, 2, 'first_name', 'Test1'),
(18, 2, 'last_name', 'Subscriber'),
(19, 2, 'description', ''),
(20, 2, 'rich_editing', 'true'),
(21, 2, 'comment_shortcuts', 'false'),
(22, 2, 'admin_color', 'fresh'),
(23, 2, 'use_ssl', '0'),
(24, 2, 'show_admin_bar_front', 'true'),
(25, 2, 'wpvk_capabilities', 'a:1:{s:10:"subscriber";b:1;}'),
(26, 2, 'wpvk_user_level', '0'),
(27, 2, 'dismissed_wp_pointers', ''),
(28, 3, 'nickname', 'test2'),
(29, 3, 'first_name', 'Test2'),
(30, 3, 'last_name', 'Contributor'),
(31, 3, 'description', ''),
(32, 3, 'rich_editing', 'true'),
(33, 3, 'comment_shortcuts', 'false'),
(34, 3, 'admin_color', 'fresh'),
(35, 3, 'use_ssl', '0'),
(36, 3, 'show_admin_bar_front', 'true'),
(37, 3, 'wpvk_capabilities', 'a:1:{s:11:"contributor";b:1;}'),
(38, 3, 'wpvk_user_level', '1'),
(39, 3, 'dismissed_wp_pointers', ''),
(40, 4, 'nickname', 'test3'),
(41, 4, 'first_name', 'Test3'),
(42, 4, 'last_name', 'Author'),
(43, 4, 'description', ''),
(44, 4, 'rich_editing', 'true'),
(45, 4, 'comment_shortcuts', 'false'),
(46, 4, 'admin_color', 'fresh'),
(47, 4, 'use_ssl', '0'),
(48, 4, 'show_admin_bar_front', 'true'),
(49, 4, 'wpvk_capabilities', 'a:1:{s:6:"author";b:1;}'),
(50, 4, 'wpvk_user_level', '2'),
(51, 4, 'dismissed_wp_pointers', '');

-- --------------------------------------------------------

--
-- Table structure for table `wpvk_users`
--

CREATE TABLE IF NOT EXISTS `wpvk_users` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_login` varchar(60) NOT NULL DEFAULT '',
  `user_pass` varchar(255) NOT NULL DEFAULT '',
  `user_nicename` varchar(50) NOT NULL DEFAULT '',
  `user_email` varchar(100) NOT NULL DEFAULT '',
  `user_url` varchar(100) NOT NULL DEFAULT '',
  `user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_activation_key` varchar(255) NOT NULL DEFAULT '',
  `user_status` int(11) NOT NULL DEFAULT '0',
  `display_name` varchar(250) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `user_login_key` (`user_login`),
  KEY `user_nicename` (`user_nicename`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `wpvk_users`
--

INSERT INTO `wpvk_users` (`ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `user_url`, `user_registered`, `user_activation_key`, `user_status`, `display_name`) VALUES
(1, 'admin', '$P$BlO2X5nM.djjfsPjOBHz97GHZmpBRr.', 'admin', 'admin@example.com', '', '2016-02-10 18:55:26', '', 0, 'admin'),
(2, 'test1', '$P$B3BfWySh.ymDeURK0OXMFo4vh4JprO0', 'test1', 'test1@example.com', '', '2016-02-10 18:57:47', '', 0, 'Test1 Subscriber'),
(3, 'test2', '$P$BMNEUEo5nalKEswryuP69KXEfz8Y.z.', 'test2', 'test2@example.com', '', '2016-02-10 18:58:32', '', 0, 'Test2 Contributor'),
(4, 'test3', '$P$B2PP3AP6NF/jLO0HYu3xf577rBnp2j.', 'test3', 'test3@example.com', '', '2016-02-10 18:59:19', '', 0, 'Test3 Author');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
