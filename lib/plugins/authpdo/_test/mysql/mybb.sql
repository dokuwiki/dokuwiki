-- phpMyAdmin SQL Dump
-- version 4.4.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2016 at 04:02 PM
-- Server version: 5.5.45
-- PHP Version: 5.4.45

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mybb`
--

-- --------------------------------------------------------

--
-- Table structure for table `mybb_usergroups`
--

CREATE TABLE `mybb_usergroups` (
  `gid` smallint(5) unsigned NOT NULL,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '2',
  `title` varchar(120) NOT NULL DEFAULT '',
  `description` text NOT NULL DEFAULT '',
  `namestyle` varchar(200) NOT NULL DEFAULT '{username}',
  `usertitle` varchar(120) NOT NULL DEFAULT '',
  `stars` smallint(4) unsigned NOT NULL DEFAULT '0',
  `starimage` varchar(120) NOT NULL DEFAULT '',
  `image` varchar(120) NOT NULL DEFAULT '',
  `disporder` smallint(6) unsigned NOT NULL DEFAULT '0',
  `isbannedgroup` tinyint(1) NOT NULL DEFAULT '0',
  `canview` tinyint(1) NOT NULL DEFAULT '0',
  `canviewthreads` tinyint(1) NOT NULL DEFAULT '0',
  `canviewprofiles` tinyint(1) NOT NULL DEFAULT '0',
  `candlattachments` tinyint(1) NOT NULL DEFAULT '0',
  `canviewboardclosed` tinyint(1) NOT NULL DEFAULT '0',
  `canpostthreads` tinyint(1) NOT NULL DEFAULT '0',
  `canpostreplys` tinyint(1) NOT NULL DEFAULT '0',
  `canpostattachments` tinyint(1) NOT NULL DEFAULT '0',
  `canratethreads` tinyint(1) NOT NULL DEFAULT '0',
  `modposts` tinyint(1) NOT NULL DEFAULT '0',
  `modthreads` tinyint(1) NOT NULL DEFAULT '0',
  `mod_edit_posts` tinyint(1) NOT NULL DEFAULT '0',
  `modattachments` tinyint(1) NOT NULL DEFAULT '0',
  `caneditposts` tinyint(1) NOT NULL DEFAULT '0',
  `candeleteposts` tinyint(1) NOT NULL DEFAULT '0',
  `candeletethreads` tinyint(1) NOT NULL DEFAULT '0',
  `caneditattachments` tinyint(1) NOT NULL DEFAULT '0',
  `canpostpolls` tinyint(1) NOT NULL DEFAULT '0',
  `canvotepolls` tinyint(1) NOT NULL DEFAULT '0',
  `canundovotes` tinyint(1) NOT NULL DEFAULT '0',
  `canusepms` tinyint(1) NOT NULL DEFAULT '0',
  `cansendpms` tinyint(1) NOT NULL DEFAULT '0',
  `cantrackpms` tinyint(1) NOT NULL DEFAULT '0',
  `candenypmreceipts` tinyint(1) NOT NULL DEFAULT '0',
  `pmquota` int(3) unsigned NOT NULL DEFAULT '0',
  `maxpmrecipients` int(4) unsigned NOT NULL DEFAULT '5',
  `cansendemail` tinyint(1) NOT NULL DEFAULT '0',
  `cansendemailoverride` tinyint(1) NOT NULL DEFAULT '0',
  `maxemails` int(3) unsigned NOT NULL DEFAULT '5',
  `emailfloodtime` int(3) unsigned NOT NULL DEFAULT '5',
  `canviewmemberlist` tinyint(1) NOT NULL DEFAULT '0',
  `canviewcalendar` tinyint(1) NOT NULL DEFAULT '0',
  `canaddevents` tinyint(1) NOT NULL DEFAULT '0',
  `canbypasseventmod` tinyint(1) NOT NULL DEFAULT '0',
  `canmoderateevents` tinyint(1) NOT NULL DEFAULT '0',
  `canviewonline` tinyint(1) NOT NULL DEFAULT '0',
  `canviewwolinvis` tinyint(1) NOT NULL DEFAULT '0',
  `canviewonlineips` tinyint(1) NOT NULL DEFAULT '0',
  `cancp` tinyint(1) NOT NULL DEFAULT '0',
  `issupermod` tinyint(1) NOT NULL DEFAULT '0',
  `cansearch` tinyint(1) NOT NULL DEFAULT '0',
  `canusercp` tinyint(1) NOT NULL DEFAULT '0',
  `canuploadavatars` tinyint(1) NOT NULL DEFAULT '0',
  `canratemembers` tinyint(1) NOT NULL DEFAULT '0',
  `canchangename` tinyint(1) NOT NULL DEFAULT '0',
  `canbereported` tinyint(1) NOT NULL DEFAULT '0',
  `canchangewebsite` tinyint(1) NOT NULL DEFAULT '1',
  `showforumteam` tinyint(1) NOT NULL DEFAULT '0',
  `usereputationsystem` tinyint(1) NOT NULL DEFAULT '0',
  `cangivereputations` tinyint(1) NOT NULL DEFAULT '0',
  `candeletereputations` tinyint(1) NOT NULL DEFAULT '0',
  `reputationpower` int(10) unsigned NOT NULL DEFAULT '0',
  `maxreputationsday` int(10) unsigned NOT NULL DEFAULT '0',
  `maxreputationsperuser` int(10) unsigned NOT NULL DEFAULT '0',
  `maxreputationsperthread` int(10) unsigned NOT NULL DEFAULT '0',
  `candisplaygroup` tinyint(1) NOT NULL DEFAULT '0',
  `attachquota` int(10) unsigned NOT NULL DEFAULT '0',
  `cancustomtitle` tinyint(1) NOT NULL DEFAULT '0',
  `canwarnusers` tinyint(1) NOT NULL DEFAULT '0',
  `canreceivewarnings` tinyint(1) NOT NULL DEFAULT '0',
  `maxwarningsday` int(3) unsigned NOT NULL DEFAULT '3',
  `canmodcp` tinyint(1) NOT NULL DEFAULT '0',
  `showinbirthdaylist` tinyint(1) NOT NULL DEFAULT '0',
  `canoverridepm` tinyint(1) NOT NULL DEFAULT '0',
  `canusesig` tinyint(1) NOT NULL DEFAULT '0',
  `canusesigxposts` smallint(5) unsigned NOT NULL DEFAULT '0',
  `signofollow` tinyint(1) NOT NULL DEFAULT '0',
  `edittimelimit` int(4) unsigned NOT NULL DEFAULT '0',
  `maxposts` int(4) unsigned NOT NULL DEFAULT '0',
  `showmemberlist` tinyint(1) NOT NULL DEFAULT '1',
  `canmanageannounce` tinyint(1) NOT NULL DEFAULT '0',
  `canmanagemodqueue` tinyint(1) NOT NULL DEFAULT '0',
  `canmanagereportedcontent` tinyint(1) NOT NULL DEFAULT '0',
  `canviewmodlogs` tinyint(1) NOT NULL DEFAULT '0',
  `caneditprofiles` tinyint(1) NOT NULL DEFAULT '0',
  `canbanusers` tinyint(1) NOT NULL DEFAULT '0',
  `canviewwarnlogs` tinyint(1) NOT NULL DEFAULT '0',
  `canuseipsearch` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `mybb_usergroups`
--

INSERT INTO `mybb_usergroups` (`gid`, `type`, `title`, `description`, `namestyle`, `usertitle`, `stars`, `starimage`, `image`, `disporder`, `isbannedgroup`, `canview`, `canviewthreads`, `canviewprofiles`, `candlattachments`, `canviewboardclosed`, `canpostthreads`, `canpostreplys`, `canpostattachments`, `canratethreads`, `modposts`, `modthreads`, `mod_edit_posts`, `modattachments`, `caneditposts`, `candeleteposts`, `candeletethreads`, `caneditattachments`, `canpostpolls`, `canvotepolls`, `canundovotes`, `canusepms`, `cansendpms`, `cantrackpms`, `candenypmreceipts`, `pmquota`, `maxpmrecipients`, `cansendemail`, `cansendemailoverride`, `maxemails`, `emailfloodtime`, `canviewmemberlist`, `canviewcalendar`, `canaddevents`, `canbypasseventmod`, `canmoderateevents`, `canviewonline`, `canviewwolinvis`, `canviewonlineips`, `cancp`, `issupermod`, `cansearch`, `canusercp`, `canuploadavatars`, `canratemembers`, `canchangename`, `canbereported`, `canchangewebsite`, `showforumteam`, `usereputationsystem`, `cangivereputations`, `candeletereputations`, `reputationpower`, `maxreputationsday`, `maxreputationsperuser`, `maxreputationsperthread`, `candisplaygroup`, `attachquota`, `cancustomtitle`, `canwarnusers`, `canreceivewarnings`, `maxwarningsday`, `canmodcp`, `showinbirthdaylist`, `canoverridepm`, `canusesig`, `canusesigxposts`, `signofollow`, `edittimelimit`, `maxposts`, `showmemberlist`, `canmanageannounce`, `canmanagemodqueue`, `canmanagereportedcontent`, `canviewmodlogs`, `caneditprofiles`, `canbanusers`, `canviewwarnlogs`, `canuseipsearch`) VALUES
(1, 1, 'Guests', 'The default group that all visitors are assigned to unless they''re logged in.', '{username}', 'Unregistered', 0, '', '', 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 5, 0, 0, 5, 5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2, 1, 'Registered', 'After registration, all users are placed in this group by default.', '{username}', '', 0, 'images/star.png', '', 0, 0, 1, 1, 1, 1, 0, 1, 1, 1, 1, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 200, 5, 1, 0, 5, 5, 1, 1, 1, 0, 0, 1, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 5, 0, 0, 1, 0, 1, 0, 1, 0, 0, 1, 0, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(3, 1, 'Super Moderators', 'These users can moderate any forum.', '<span style="color: #CC00CC;"><strong>{username}</strong></span>', 'Super Moderator', 6, 'images/star.png', '', 0, 0, 1, 1, 1, 1, 0, 1, 1, 1, 1, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 250, 5, 1, 0, 10, 5, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 10, 0, 0, 1, 0, 1, 1, 1, 3, 1, 1, 0, 1, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1),
(4, 1, 'Administrators', 'The group all administrators belong to.', '<span style="color: green;"><strong><em>{username}</em></strong></span>', 'Administrator', 7, 'images/star.png', '', 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 2, 0, 0, 0, 1, 0, 1, 1, 1, 0, 1, 1, 0, 1, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1),
(5, 1, 'Awaiting Activation', 'Users that have not activated their account by email or manually been activated yet.', '{username}', 'Account not Activated', 0, 'images/star.png', '', 0, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 20, 5, 0, 0, 5, 5, 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0),
(6, 1, 'Moderators', 'These users moderate specific forums.', '<span style="color: #CC00CC;"><strong>{username}</strong></span>', 'Moderator', 5, 'images/star.png', '', 0, 0, 1, 1, 1, 1, 0, 1, 1, 1, 1, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 250, 5, 1, 0, 5, 5, 1, 1, 0, 0, 0, 1, 0, 0, 0, 0, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 10, 0, 0, 1, 0, 1, 1, 1, 3, 1, 1, 0, 1, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1),
(7, 1, 'Banned', 'The default user group to which members that are banned are moved to.', '<s>{username}</s>', 'Banned', 0, 'images/star.png', '', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 5, 5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `mybb_usergroups`
--
ALTER TABLE `mybb_usergroups`
  ADD PRIMARY KEY (`gid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mybb_usergroups`
--
ALTER TABLE `mybb_usergroups`
  MODIFY `gid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
-- phpMyAdmin SQL Dump
-- version 4.4.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2016 at 03:47 PM
-- Server version: 5.5.45
-- PHP Version: 5.4.45

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mybb`
--

-- --------------------------------------------------------

--
-- Table structure for table `mybb_users`
--

CREATE TABLE `mybb_users` (
  `uid` int(10) unsigned NOT NULL,
  `username` varchar(120) NOT NULL DEFAULT '',
  `password` varchar(120) NOT NULL DEFAULT '',
  `salt` varchar(10) NOT NULL DEFAULT '',
  `loginkey` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(220) NOT NULL DEFAULT '',
  `postnum` int(10) unsigned NOT NULL DEFAULT '0',
  `threadnum` int(10) unsigned NOT NULL DEFAULT '0',
  `avatar` varchar(200) NOT NULL DEFAULT '',
  `avatardimensions` varchar(10) NOT NULL DEFAULT '',
  `avatartype` varchar(10) NOT NULL DEFAULT '0',
  `usergroup` smallint(5) unsigned NOT NULL DEFAULT '0',
  `additionalgroups` varchar(200) NOT NULL DEFAULT '',
  `displaygroup` smallint(5) unsigned NOT NULL DEFAULT '0',
  `usertitle` varchar(250) NOT NULL DEFAULT '',
  `regdate` int(10) unsigned NOT NULL DEFAULT '0',
  `lastactive` int(10) unsigned NOT NULL DEFAULT '0',
  `lastvisit` int(10) unsigned NOT NULL DEFAULT '0',
  `lastpost` int(10) unsigned NOT NULL DEFAULT '0',
  `website` varchar(200) NOT NULL DEFAULT '',
  `icq` varchar(10) NOT NULL DEFAULT '',
  `aim` varchar(50) NOT NULL DEFAULT '',
  `yahoo` varchar(50) NOT NULL DEFAULT '',
  `skype` varchar(75) NOT NULL DEFAULT '',
  `google` varchar(75) NOT NULL DEFAULT '',
  `birthday` varchar(15) NOT NULL DEFAULT '',
  `birthdayprivacy` varchar(4) NOT NULL DEFAULT 'all',
  `signature` text NOT NULL DEFAULT '',
  `allownotices` tinyint(1) NOT NULL DEFAULT '0',
  `hideemail` tinyint(1) NOT NULL DEFAULT '0',
  `subscriptionmethod` tinyint(1) NOT NULL DEFAULT '0',
  `invisible` tinyint(1) NOT NULL DEFAULT '0',
  `receivepms` tinyint(1) NOT NULL DEFAULT '0',
  `receivefrombuddy` tinyint(1) NOT NULL DEFAULT '0',
  `pmnotice` tinyint(1) NOT NULL DEFAULT '0',
  `pmnotify` tinyint(1) NOT NULL DEFAULT '0',
  `buddyrequestspm` tinyint(1) NOT NULL DEFAULT '1',
  `buddyrequestsauto` tinyint(1) NOT NULL DEFAULT '0',
  `threadmode` varchar(8) NOT NULL DEFAULT '',
  `showimages` tinyint(1) NOT NULL DEFAULT '0',
  `showvideos` tinyint(1) NOT NULL DEFAULT '0',
  `showsigs` tinyint(1) NOT NULL DEFAULT '0',
  `showavatars` tinyint(1) NOT NULL DEFAULT '0',
  `showquickreply` tinyint(1) NOT NULL DEFAULT '0',
  `showredirect` tinyint(1) NOT NULL DEFAULT '0',
  `ppp` smallint(6) unsigned NOT NULL DEFAULT '0',
  `tpp` smallint(6) unsigned NOT NULL DEFAULT '0',
  `daysprune` smallint(6) unsigned NOT NULL DEFAULT '0',
  `dateformat` varchar(4) NOT NULL DEFAULT '',
  `timeformat` varchar(4) NOT NULL DEFAULT '',
  `timezone` varchar(5) NOT NULL DEFAULT '',
  `dst` tinyint(1) NOT NULL DEFAULT '0',
  `dstcorrection` tinyint(1) NOT NULL DEFAULT '0',
  `buddylist` text NOT NULL DEFAULT '',
  `ignorelist` text NOT NULL DEFAULT '',
  `style` smallint(5) unsigned NOT NULL DEFAULT '0',
  `away` tinyint(1) NOT NULL DEFAULT '0',
  `awaydate` int(10) unsigned NOT NULL DEFAULT '0',
  `returndate` varchar(15) NOT NULL DEFAULT '',
  `awayreason` varchar(200) NOT NULL DEFAULT '',
  `pmfolders` text NOT NULL DEFAULT '',
  `notepad` text NOT NULL DEFAULT '',
  `referrer` int(10) unsigned NOT NULL DEFAULT '0',
  `referrals` int(10) unsigned NOT NULL DEFAULT '0',
  `reputation` int(11) NOT NULL DEFAULT '0',
  `regip` varbinary(16) NOT NULL DEFAULT '',
  `lastip` varbinary(16) NOT NULL DEFAULT '',
  `language` varchar(50) NOT NULL DEFAULT '',
  `timeonline` int(10) unsigned NOT NULL DEFAULT '0',
  `showcodebuttons` tinyint(1) NOT NULL DEFAULT '1',
  `totalpms` int(10) unsigned NOT NULL DEFAULT '0',
  `unreadpms` int(10) unsigned NOT NULL DEFAULT '0',
  `warningpoints` int(3) unsigned NOT NULL DEFAULT '0',
  `moderateposts` tinyint(1) NOT NULL DEFAULT '0',
  `moderationtime` int(10) unsigned NOT NULL DEFAULT '0',
  `suspendposting` tinyint(1) NOT NULL DEFAULT '0',
  `suspensiontime` int(10) unsigned NOT NULL DEFAULT '0',
  `suspendsignature` tinyint(1) NOT NULL DEFAULT '0',
  `suspendsigtime` int(10) unsigned NOT NULL DEFAULT '0',
  `coppauser` tinyint(1) NOT NULL DEFAULT '0',
  `classicpostbit` tinyint(1) NOT NULL DEFAULT '0',
  `loginattempts` smallint(2) unsigned NOT NULL DEFAULT '1',
  `usernotes` text NOT NULL DEFAULT '',
  `sourceeditor` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=88 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `mybb_users`
--

INSERT INTO `mybb_users` (`uid`, `username`, `password`, `salt`, `loginkey`, `email`, `postnum`, `threadnum`, `avatar`, `avatardimensions`, `avatartype`, `usergroup`, `additionalgroups`, `displaygroup`, `usertitle`, `regdate`, `lastactive`, `lastvisit`, `lastpost`, `website`, `icq`, `aim`, `yahoo`, `skype`, `google`, `birthday`, `birthdayprivacy`, `signature`, `allownotices`, `hideemail`, `subscriptionmethod`, `invisible`, `receivepms`, `receivefrombuddy`, `pmnotice`, `pmnotify`, `buddyrequestspm`, `buddyrequestsauto`, `threadmode`, `showimages`, `showvideos`, `showsigs`, `showavatars`, `showquickreply`, `showredirect`, `ppp`, `tpp`, `daysprune`, `dateformat`, `timeformat`, `timezone`, `dst`, `dstcorrection`, `buddylist`, `ignorelist`, `style`, `away`, `awaydate`, `returndate`, `awayreason`, `pmfolders`, `notepad`, `referrer`, `referrals`, `reputation`, `regip`, `lastip`, `language`, `timeonline`, `showcodebuttons`, `totalpms`, `unreadpms`, `warningpoints`, `moderateposts`, `moderationtime`, `suspendposting`, `suspensiontime`, `suspendsignature`, `suspendsigtime`, `coppauser`, `classicpostbit`, `loginattempts`, `usernotes`, `sourceeditor`) VALUES
(84, 'Test One', '6e90cf918ebce3a577fd72cea919dc64', '0pBnrIIv', 'xALZxWcfw18AhO6M7YxptBrxZqyrJB04CWlyaIniO3ZyMn6P1f', 'no_one@nowhere.com', 0, 0, '', '', '', 2, '', 0, '', 1471614765, 1471614765, 1471614765, 0, '', '0', '', '', '', '', '', 'all', '', 1, 1, 0, 0, 1, 0, 1, 1, 1, 0, 'linear', 1, 1, 1, 1, 1, 1, 0, 0, 0, '0', '0', '0', 0, 2, '', '', 0, 0, 0, '0', '', '', '', 0, 0, 0, '', '', '', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, '', 0),
(85, 'Test Two', 'e85f6b7e5804b42d7c7d99329dc1a43f', 'NSX3xNT1', 'VucYxl7EGnsoqVW75COGNAdB0YgtWHc9RFqo4LxIhhtpEFxdIE', 'no_one@nowhere.com', 0, 0, '', '', '', 3, '', 0, '', 1471614850, 1471614850, 1471614850, 0, '', '0', '', '', '', '', '', 'all', '', 1, 1, 0, 0, 1, 0, 1, 1, 1, 0, 'linear', 1, 1, 1, 1, 1, 1, 0, 0, 0, '0', '0', '0', 0, 2, '', '', 0, 0, 0, '0', '', '', '', 0, 0, 0, '', '', '', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, '', 0),
(86, 'Test Three', '3669c9583702ca6e32c7817f4bc34f5f', 'CVEbGFXH', 'GivwOlOKuvpfTs8Dc263fNnPdSQW1k1C1fHt7gukTJdRvTZGca', 'no_one@nowhere.com', 0, 0, '', '', '', 4, '', 0, '', 1471615021, 1471615021, 1471615021, 0, '', '0', '', '', '', '', '', 'all', '', 1, 1, 0, 0, 1, 0, 1, 1, 1, 0, 'linear', 1, 1, 1, 1, 1, 1, 0, 0, 0, '0', '0', '0', 0, 2, '', '', 0, 0, 0, '0', '', '', '', 0, 0, 0, '', '', '', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, '', 0),
(87, 'Test Four', '693a0cd028c9adb4cb28d8a8be3dc7af', 'x6q7QFmU', 'S4oU92jET3yjvbiganAKCYde9ksoacJeb4sC247qvYftgwsYmu', 'no_one@nowhere.com', 0, 0, '', '', '', 6, '', 0, '', 1471615064, 1471615064, 1471615064, 0, '', '0', '', '', '', '', '', 'all', '', 1, 1, 0, 0, 1, 0, 1, 1, 1, 0, 'linear', 1, 1, 1, 1, 1, 1, 0, 0, 0, '0', '0', '0', 0, 2, '', '', 0, 0, 0, '0', '', '', '', 0, 0, 0, '', '', '', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, '', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `mybb_users`
--
ALTER TABLE `mybb_users`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `usergroup` (`usergroup`),
  ADD KEY `regip` (`regip`),
  ADD KEY `lastip` (`lastip`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mybb_users`
--
ALTER TABLE `mybb_users`
  MODIFY `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=88;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
