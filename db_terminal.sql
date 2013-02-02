-- phpMyAdmin SQL Dump
-- version 3.4.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 17, 2012 at 03:36 PM
-- Server version: 5.1.46
-- PHP Version: 5.3.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `db_terminal`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE IF NOT EXISTS `admin_users` (
  `ID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `NAME` varchar(100) NOT NULL DEFAULT '',
  `LOGIN` varchar(100) NOT NULL DEFAULT '',
  `PASSWORD` varchar(100) NOT NULL DEFAULT '',
  `EMAIL` varchar(100) NOT NULL DEFAULT '',
  `COMMENTS` text,
  `ACCESS` text,
  `PRIVATE` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `EMAIL_ORDERS` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `EMAIL_INVENTORY` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`ID`, `NAME`, `LOGIN`, `PASSWORD`, `EMAIL`, `COMMENTS`, `ACCESS`, `PRIVATE`, `EMAIL_ORDERS`, `EMAIL_INVENTORY`) VALUES
(1, 'Administrator', 'admin', '21232f297a57a5a743894a0e4a801fc3', 'webmaster@domain.com', '', 'control_modules,control_access,master,cms_docs,news,statistic,newsletter,backup,edit_templates,newslist,saverestore,skins,settings,dateselect,thumb,footercode,holdingpage,dashboard,events,users,terminals,mediabrowser,player,commands,classes,history,locations,methods,properties,objects,pvalues,shoutbox,shoutrooms,jobs,btdevices,weather,usbdevices,app_mediabrowser,app_products,app_tdwiki,app_weather,layouts,scripts,rss_channels,languages,pinghosts,watchfolders,app_player,app_gpstrack,webvars,patterns,onewire,app_calendar,xray', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `btdevices`
--

DROP TABLE IF EXISTS `btdevices`;
CREATE TABLE IF NOT EXISTS `btdevices` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `MAC` varchar(255) NOT NULL DEFAULT '',
  `LOG` text NOT NULL,
  `LAST_FOUND` datetime DEFAULT NULL,
  `FIRST_FOUND` datetime DEFAULT NULL,
  `USER_ID` int(10) NOT NULL DEFAULT '0',
  `SCRIPT_ID` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_categories`
--

DROP TABLE IF EXISTS `calendar_categories`;
CREATE TABLE IF NOT EXISTS `calendar_categories` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `ACTIVE` int(255) NOT NULL DEFAULT '0',
  `PRIORITY` int(10) NOT NULL DEFAULT '0',
  `ICON` varchar(70) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_events`
--

DROP TABLE IF EXISTS `calendar_events`;
CREATE TABLE IF NOT EXISTS `calendar_events` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `SYSTEM` varchar(255) NOT NULL DEFAULT '',
  `NOTES` text,
  `DUE` date DEFAULT NULL,
  `ADDED` datetime DEFAULT NULL,
  `DONE_WHEN` datetime DEFAULT NULL,
  `IS_TASK` int(3) NOT NULL DEFAULT '0',
  `IS_DONE` int(3) NOT NULL DEFAULT '0',
  `IS_NODATE` int(3) NOT NULL DEFAULT '0',
  `IS_REPEATING` int(3) NOT NULL DEFAULT '0',
  `REPEAT_TYPE` int(3) NOT NULL DEFAULT '0',
  `WEEK_DAYS` varchar(255) NOT NULL DEFAULT '',
  `IS_REPEATING_AFTER` int(3) NOT NULL DEFAULT '0',
  `REPEAT_IN` int(10) NOT NULL DEFAULT '0',
  `USER_ID` int(10) NOT NULL DEFAULT '0',
  `LOCATION_ID` int(10) NOT NULL DEFAULT '0',
  `CALENDAR_CATEGORY_ID` int(10) NOT NULL DEFAULT '0',
  `DONE_SCRIPT_ID` int(10) NOT NULL DEFAULT '0',
  `DONE_CODE` text,
  `LOG` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
CREATE TABLE IF NOT EXISTS `classes` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `PARENT_ID` int(10) NOT NULL DEFAULT '0',
  `SUB_LIST` text,
  `PARENT_LIST` text,
  `NOLOG` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`ID`, `TITLE`, `PARENT_ID`, `SUB_LIST`, `PARENT_LIST`, `NOLOG`) VALUES
(12, 'Skype', 0, '12', '0', 0),
(19, 'tempSensors', 0, '19', '0', 0),
(7, 'Timer', 0, '7', '0', 1),
(18, 'BlueToothDevice', 0, '18', '0', 0),
(9, 'USBDevice', 0, '9', '0', 0),
(10, 'Computer', 0, '10', '0', 0),
(20, 'WeatherStations', 0, '0', '0', 0),
(21, 'systemStates', 0, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `collections`
--

DROP TABLE IF EXISTS `collections`;
CREATE TABLE IF NOT EXISTS `collections` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PATH` varchar(255) NOT NULL DEFAULT '',
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table `commands`
--

DROP TABLE IF EXISTS `commands`;
CREATE TABLE IF NOT EXISTS `commands` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `COMMAND` varchar(255) NOT NULL DEFAULT '',
  `URL` varchar(255) NOT NULL DEFAULT '',
  `WIDTH` int(10) NOT NULL DEFAULT '0',
  `HEIGHT` int(10) NOT NULL DEFAULT '0',
  `PARENT_ID` int(10) NOT NULL DEFAULT '0',
  `SUB_LIST` text,
  `PARENT_LIST` text,
  `PRIORITY` int(10) NOT NULL DEFAULT '0',
  `WINDOW` varchar(255) NOT NULL DEFAULT '',
  `AUTOSTART` int(3) NOT NULL DEFAULT '0',
  `TYPE` char(50) NOT NULL DEFAULT '',
  `MIN_VALUE` int(10) NOT NULL DEFAULT '0',
  `MAX_VALUE` int(10) NOT NULL DEFAULT '0',
  `CUR_VALUE` varchar(255) NOT NULL DEFAULT '',
  `STEP_VALUE` int(10) NOT NULL DEFAULT '0',
  `LINKED_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `LINKED_PROPERTY` varchar(255) NOT NULL DEFAULT '',
  `ONCHANGE_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `ONCHANGE_METHOD` varchar(255) NOT NULL DEFAULT '',
  `ICON` varchar(50) NOT NULL DEFAULT '',
  `DATA` text,
  `SCRIPT_ID` int(10) NOT NULL DEFAULT '0',
  `AUTO_UPDATE` int(10) NOT NULL DEFAULT '0',
  `CODE` text,
  `SYSTEM` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=97 ;

--
-- Dumping data for table `commands`
--

INSERT INTO `commands` (`ID`, `TITLE`, `COMMAND`, `URL`, `WIDTH`, `HEIGHT`, `PARENT_ID`, `SUB_LIST`, `PARENT_LIST`, `PRIORITY`, `WINDOW`, `AUTOSTART`, `TYPE`, `MIN_VALUE`, `MAX_VALUE`, `CUR_VALUE`, `STEP_VALUE`, `LINKED_OBJECT`, `LINKED_PROPERTY`, `ONCHANGE_OBJECT`, `ONCHANGE_METHOD`, `ICON`, `DATA`, `SCRIPT_ID`, `AUTO_UPDATE`, `CODE`, `SYSTEM`) VALUES
(1, '<#LANG_APP_MEDIA_BROWSER#>', '', '', 0, 0, 0, '2,3', '0', 6000, '', 0, '', 0, 0, '0', 0, '', '', '', '', '1_iPhone_MUSIC_5_sm.png', '', 0, 0, '', ''),
(2, 'Browse', '', '/popup/mediabrowse.html', 600, 500, 1, '2', '1', 100, 'mediaBrowser', 0, 'url', 0, 0, '0', 0, '', '', '', '', '', '', 0, 0, '', ''),
(3, 'Player control', '', '', 0, 0, 1, '3', '1', 90, '', 0, 'custom', 0, 0, '0', 0, '', '', '', '', '', '[#module name="app_player" mode="menu"#]', 0, 0, '', ''),
(75, 'Module', '', '', 0, 0, 86, '75', '86', 0, '', 0, 'custom', 0, 0, '0', 0, '', '', '', '', '', '[#module name="layouts"#]', 0, 60, '', ''),
(11, '<#LANG_GENERAL_SECURITY_CAMERA#>', '', '', 0, 0, 0, '81', '0', 5000, '', 0, '', 0, 0, '0', 0, '', '', '', '', '', '', 0, 0, '', ''),
(82, '<#LANG_GENERAL_EVENTS_LOG#>', '', '', 0, 0, 0, '82', '0', 10, '', 0, 'label', 0, 0, '', 0, '', '', '', '', '', '', 0, 0, '', ''),
(63, '%ClockChime.time%', '', '', 0, 0, 66, '63', '66', 10000, '', 0, 'custom', 0, 0, '0', 0, '', '', '', '', '', '%ClockChime.time%<br>\r\nhello! <a href="/test">test</a>', 0, 60, '', ''),
(66, 'Demo controls', '', '', 0, 0, 0, '63,67,68,69,70,71,72,73,77,78,80', '0', 0, '', 0, '', 0, 0, '0', 0, '', '', '', '', '', '', 0, 0, '', ''),
(67, '<#LANG_LABEL#>', '', '', 0, 0, 66, '67', '66', 9000, '', 0, 'label', 0, 0, '0', 0, '', '', '', '', '', '', 0, 0, '', ''),
(68, 'New window', '', 'http://google.com/', 600, 600, 66, '68', '66', 8000, '', 0, 'window', 0, 0, '0', 0, '', '', '', '', '', '', 0, 0, '', ''),
(69, '<#LANG_URL#>', '', 'http://google.com/', 0, 0, 66, '69', '66', 7000, '', 0, 'url', 0, 0, '0', 0, '', '', '', '', '', '', 0, 0, '', ''),
(70, 'Buttons', '', '', 0, 0, 66, '70', '66', 6000, '', 0, 'button', 0, 0, 'clicked', 0, '', '', '', '', '', '', 0, 0, 'say("Привет");', ''),
(71, '<#LANG_ON_OFF_SWITCH#>', '', '', 0, 0, 66, '71', '66', 5000, '', 0, 'switch', 0, 0, '0', 0, '', '', '', '', '', '', 0, 0, '', ''),
(72, 'Select box', '', '', 0, 0, 66, '72', '66', 4000, '', 0, 'selectbox', 0, 0, '2', 0, '', '', '', '', '', '1|Item 1\r\n2|Item 2\r\n3|Item 3', 0, 0, '', ''),
(73, 'Plus minus box', '', '', 0, 0, 66, '73', '66', 3000, '', 0, 'plusminus', 0, 5, '3', 1, '', '', '', '', '', '', 0, 0, '', ''),
(74, '<#LANG_GENERAL_EVENTS_LOG#> (code)', '', '', 0, 0, 0, '74', '0', 9, '', 0, 'custom', 0, 0, '0', 0, '', '', '', '', '', '<div style="text-shadow:none;font-weight:normal;">[#module name="shoutbox" limit="10" reverse="1" mobile="1"#]</div>', 0, 0, '', ''),
(77, 'Alarm time', '', '', 0, 0, 66, '77', '66', 0, '', 0, 'timebox', 0, 0, '09:30', 0, 'ThisComputer', 'AlarmTime', '', '', '', '', 0, 0, '', ''),
(78, '<#LANG_TEXT_BOX#>', '', '', 0, 0, 66, '78', '66', 0, '', 0, 'textbox', 0, 0, '0', 0, 'ThisComputer', 'textBoxTest', '', '', '', '', 0, 10, '', ''),
(80, '<#LANG_SLIDER_BOX#>', '', '', 0, 0, 66, '80', '66', 0, '', 0, 'sliderbox', 0, 10, '0', 1, 'ThisComputer', 'textBoxTest', '', '', '', '', 0, 5, '', ''),
(81, 'Web-cam', '', '', 0, 0, 11, '81', '11', 0, '', 0, 'custom', 0, 0, '', 0, '', '', '', '', '', '<p align="center"><img src="http://abclocal.go.com/three/wabc/webcam/skycpk.jpg" width="270">\r\n<br/><br/>\r\n<img src="http://213.179.245.12/axis-cgi/mjpg/video.cgi?resolution=352x288&dummy=1340376440935" width="270">\r\n\r\n</p>', 0, 0, '', ''),
(86, '<#LANG_GENERAL_SERVICE#>', '', '', 0, 0, 0, '75', '0', 2, '', 0, '', 0, 0, '', 0, '', '', '', '', '', '', 0, 0, '', ''),
(87, '<#LANG_GENERAL_OPERATIONAL_MODES#>', '', '', 0, 0, 0, '88,89,90,94', '0', 10000, '', 0, '', 0, 0, '', 0, '', '', '', '', '', '', 0, 0, '', ''),
(88, '<#LANG_GENERAL_SECURITY_MODE#>', '', '', 0, 0, 87, '88', '87', 100, '', 0, 'switch', 0, 0, '0', 0, 'ThisComputer', 'securityMode', '', '', '', '', 0, 0, '', ''),
(89, '<#LANG_GENERAL_NOBODYS_HOME_MODE#>', '', '', 0, 0, 87, '89', '87', 90, '', 0, 'switch', 0, 0, '0', 0, 'ThisComputer', 'nobodyHome', '', '', '', '', 0, 0, '', ''),
(90, '<#LANG_GENERAL_WE_HAVE_GUESTS_MODE#>', '', '', 0, 0, 87, '90', '87', 80, '', 0, 'switch', 0, 0, '0', 0, 'ThisComputer', 'WeHaveGuests', '', '', '', '', 0, 0, '', ''),
(91, '<#LANG_GENERAL_CLIMATE#> (%TempOutside%°C)', '', '', 0, 0, 0, '92,93,95,96', '0', 8000, '', 0, '', 0, 0, '', 0, '', '', '', '', '', '', 0, 60, '', ''),
(92, '<#LANG_GENERAL_WEATHER_FORECAST#> (code)', '', '', 0, 0, 91, '92', '91', 1000, '', 0, 'custom', 0, 0, '', 0, '', '', '', '', '', '%ThisComputer.weatherFull%', 0, 0, '', ''),
(93, '<#LANG_GENERAL_WEATHER_FORECAST#>', '', '', 0, 0, 91, '93', '91', 1001, '', 0, 'label', 0, 0, '', 0, '', '', '', '', '', '', 0, 0, '', ''),
(94, '<#LANG_GENERAL_ENERGY_SAVING_MODE#>', '', '', 0, 0, 87, '94', '87', 110, '', 0, 'switch', 0, 0, '0', 0, 'ThisComputer', 'Econom', '', '', '', '', 0, 0, '', ''),
(95, '<#LANG_GENERAL_GRAPHICS#> (48h) (code)', '', '', 0, 0, 91, '95', '91', 900, '', 0, 'custom', 0, 0, '', 0, '', '', '', '', '', '<img src="/pChart/?p=ThisComputer.tempOutside&type=48h&width=280&gtype=curve&px=15&%rand%">', 0, 0, '', ''),
(96, '<#LANG_GENERAL_GRAPHICS#> (48h)', '', '', 0, 0, 91, '96', '91', 901, '', 0, 'label', 0, 0, '', 0, '', '', '', '', '', '', 0, 0, '', '');

-- --------------------------------------------------------

--
-- Table structure for table `elements`
--

DROP TABLE IF EXISTS `elements`;
CREATE TABLE IF NOT EXISTS `elements` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `SCENE_ID` int(10) NOT NULL DEFAULT '0',
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `TYPE` varchar(255) NOT NULL DEFAULT '',
  `TOP` int(10) NOT NULL DEFAULT '0',
  `LEFT` int(255) NOT NULL DEFAULT '0',
  `WIDTH` int(255) NOT NULL DEFAULT '0',
  `HEIGHT` int(255) NOT NULL DEFAULT '0',
  `CROSS_SCENE` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `elements`
--

INSERT INTO `elements` (`ID`, `SCENE_ID`, `TITLE`, `TYPE`, `TOP`, `LEFT`, `WIDTH`, `HEIGHT`, `CROSS_SCENE`) VALUES
(1, 1, 'Webcam Sample', 'html', 55, 392, 270, 210, 0),
(2, 2, 'Time', 'html', 10, 10, 300, 20, 0);

-- --------------------------------------------------------

--
-- Table structure for table `elm_states`
--

DROP TABLE IF EXISTS `elm_states`;
CREATE TABLE IF NOT EXISTS `elm_states` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ELEMENT_ID` int(10) NOT NULL DEFAULT '0',
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `IMAGE` varchar(255) NOT NULL DEFAULT '',
  `HTML` text,
  `IS_DYNAMIC` int(3) NOT NULL DEFAULT '0',
  `CURRENT_STATE` int(3) NOT NULL DEFAULT '0',
  `LINKED_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `LINKED_PROPERTY` varchar(255) NOT NULL DEFAULT '',
  `CONDITION` int(3) NOT NULL DEFAULT '0',
  `CONDITION_VALUE` varchar(255) NOT NULL DEFAULT '',
  `CONDITION_ADVANCED` text,
  `SCRIPT_ID` int(10) NOT NULL DEFAULT '0',
  `SWITCH_SCENE` int(3) NOT NULL DEFAULT '0',
  `CURRENT_STATUS` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `elm_states`
--

INSERT INTO `elm_states` (`ID`, `ELEMENT_ID`, `TITLE`, `IMAGE`, `HTML`, `IS_DYNAMIC`, `CURRENT_STATE`, `LINKED_OBJECT`, `LINKED_PROPERTY`, `CONDITION`, `CONDITION_VALUE`, `CONDITION_ADVANCED`, `SCRIPT_ID`, `SWITCH_SCENE`, `CURRENT_STATUS`) VALUES
(1, 1, 'Default', '', '<img src="http://abclocal.go.com/three/wabc/webcam/skycpk.jpg" width="270">', 0, 1, '', '', 1, '', '', 0, 0, 0),
(2, 2, 'Test', '', '<b>Sample element</b> %ClockChime.time%', 2, 0, '', '', 1, '', '$t=rand(0,1);\r\nif ($t) {\r\n $display=1;\r\n}', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `EVENT_TYPE` varchar(10) NOT NULL DEFAULT '',
  `TERMINAL_FROM` varchar(255) NOT NULL DEFAULT '',
  `TERMINAL_TO` varchar(255) NOT NULL DEFAULT '',
  `USER_FROM` varchar(255) NOT NULL DEFAULT '',
  `USER_TO` varchar(255) NOT NULL DEFAULT '',
  `WINDOW` varchar(255) NOT NULL DEFAULT '',
  `DETAILS` text,
  `ADDED` datetime DEFAULT NULL,
  `EXPIRE` datetime DEFAULT NULL,
  `PROCESSED` int(3) NOT NULL DEFAULT '0',
  `EVENT_NAME` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `gpsactions`
--

DROP TABLE IF EXISTS `gpsactions`;
CREATE TABLE IF NOT EXISTS `gpsactions` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `LOCATION_ID` int(10) NOT NULL DEFAULT '0',
  `USER_ID` int(10) NOT NULL DEFAULT '0',
  `ACTION_TYPE` int(255) NOT NULL DEFAULT '0',
  `SCRIPT_ID` int(10) NOT NULL DEFAULT '0',
  `CODE` text,
  `LOG` text,
  `EXECUTED` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `gpsdevices`
--

DROP TABLE IF EXISTS `gpsdevices`;
CREATE TABLE IF NOT EXISTS `gpsdevices` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `USER_ID` int(10) NOT NULL DEFAULT '0',
  `LAT` varchar(255) NOT NULL DEFAULT '',
  `LON` varchar(255) NOT NULL DEFAULT '',
  `UPDATED` datetime DEFAULT NULL,
  `DEVICEID` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `gpslocations`
--

DROP TABLE IF EXISTS `gpslocations`;
CREATE TABLE IF NOT EXISTS `gpslocations` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `LAT` float NOT NULL DEFAULT '0',
  `LON` float NOT NULL DEFAULT '0',
  `RANGE` float NOT NULL DEFAULT '0',
  `VIRTUAL_USER_ID` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `gpslog`
--

DROP TABLE IF EXISTS `gpslog`;
CREATE TABLE IF NOT EXISTS `gpslog` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ADDED` datetime DEFAULT NULL,
  `LAT` float NOT NULL DEFAULT '0',
  `LON` float NOT NULL DEFAULT '0',
  `ALT` float NOT NULL DEFAULT '0',
  `PROVIDER` varchar(30) NOT NULL DEFAULT '',
  `SPEED` float NOT NULL DEFAULT '0',
  `BATTLEVEL` int(3) NOT NULL DEFAULT '0',
  `CHARGING` int(3) NOT NULL DEFAULT '0',
  `DEVICEID` varchar(255) NOT NULL DEFAULT '',
  `DEVICE_ID` int(10) NOT NULL DEFAULT '0',
  `LOCATION_ID` int(10) NOT NULL DEFAULT '0',
  `ACCURACY` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

DROP TABLE IF EXISTS `history`;
CREATE TABLE IF NOT EXISTS `history` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ADDED` datetime DEFAULT NULL,
  `OBJECT_ID` int(10) NOT NULL DEFAULT '0',
  `METHOD_ID` int(10) NOT NULL DEFAULT '0',
  `VALUE_ID` int(10) NOT NULL DEFAULT '0',
  `OLD_VALUE` varchar(255) NOT NULL DEFAULT '',
  `NEW_VALUE` varchar(255) NOT NULL DEFAULT '',
  `DETAILS` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `COMMANDS` text NOT NULL,
  `RUNTIME` datetime DEFAULT NULL,
  `EXPIRE` datetime DEFAULT NULL,
  `PROCESSED` int(3) NOT NULL DEFAULT '0',
  `STARTED` datetime DEFAULT NULL,
  `EXPIRED` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `layouts`
--

DROP TABLE IF EXISTS `layouts`;
CREATE TABLE IF NOT EXISTS `layouts` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `PRIORITY` int(10) NOT NULL DEFAULT '0',
  `TYPE` varchar(255) NOT NULL DEFAULT '',
  `CODE` text,
  `APP` varchar(255) NOT NULL DEFAULT '',
  `URL` char(255) NOT NULL DEFAULT '',
  `DETAILS` text,
  `REFRESH` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `layouts`
--

INSERT INTO `layouts` (`ID`, `TITLE`, `PRIORITY`, `TYPE`, `CODE`, `APP`, `URL`, `DETAILS`, `REFRESH`) VALUES
(6, '<#LANG_APP_PRODUCTS#>', 70, 'app', '', 'app_products', '', '', 0),
(7, '<#LANG_MODULE_SCENES#>', 550, 'app', '', 'scenes', '', '', 0),
(8, '<#LANG_WELCOME_GREETING#>', 1000, 'html', '<div style="padding-left:50px;padding-top:30px">\r\n<h1><#LANG_WELCOME_GREETING#></h1>\r\n&nbsp;\r\n<p style="font-size:14px">\r\n<#LANG_WELCOME_TEXT#>\r\n</p>\r\n</div>', '', '', '', 0),
(9, '<#LANG_APP_CALENDAR#>', 100, 'app', '', 'app_calendar', '', '', 0),
(10, '<#LANG_APP_MEDIA_BROWSER#>', 200, 'app', '', 'app_mediabrowser', '', '', 0),
(12, 'GPS', 0, 'app', '', 'app_gpstrack', '', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

DROP TABLE IF EXISTS `locations`;
CREATE TABLE IF NOT EXISTS `locations` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`ID`, `TITLE`) VALUES
(1, 'Office');

-- --------------------------------------------------------

--
-- Table structure for table `media_favorites`
--

DROP TABLE IF EXISTS `media_favorites`;
CREATE TABLE IF NOT EXISTS `media_favorites` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PATH` varchar(255) NOT NULL DEFAULT '',
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `LIST_ID` int(10) unsigned NOT NULL DEFAULT '0',
  `COLLECTION_ID` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `methods`
--

DROP TABLE IF EXISTS `methods`;
CREATE TABLE IF NOT EXISTS `methods` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `OBJECT_ID` int(10) NOT NULL DEFAULT '0',
  `CLASS_ID` int(10) NOT NULL DEFAULT '0',
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `DESCRIPTION` text,
  `CODE` text,
  `CALL_PARENT` int(3) NOT NULL DEFAULT '0',
  `SCRIPT_ID` int(10) NOT NULL DEFAULT '0',
  `EXECUTED` datetime DEFAULT NULL,
  `EXECUTED_PARAMS` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=61 ;

--
-- Dumping data for table `methods`
--

INSERT INTO `methods` (`ID`, `OBJECT_ID`, `CLASS_ID`, `TITLE`, `DESCRIPTION`, `CODE`, `CALL_PARENT`, `SCRIPT_ID`, `EXECUTED`, `EXECUTED_PARAMS`) VALUES
(1, 0, 1, 'KeyPressed', 'событие, возникающее при нажатии на кнопку', '$this->setProperty("lastTimePressed",time());', 0, 0, NULL, NULL),
(23, 0, 11, 'say', '', 'echo "notify: ".utf2win($params[''say''])."<br>";\r\n$say=utf2win($params[''say'']);\r\n$say=str_replace('' '',''..'',$say);\r\necho (''"c:\\\\program files\\\\Growl for windows\\\\growlnotify.com" /t:Alice ''.$say.'''');\r\nexec(''"c:\\\\program files\\\\Growl for windows\\\\growlnotify.com" /t:Alice ''.$say.'''');', 0, 0, NULL, NULL),
(14, 0, 7, 'onNewMinute', '', '', 0, 0, '2012-11-17 15:36:00', 'a:3:{s:6:"object";s:10:"ClockChime";s:2:"op";s:1:"m";s:1:"m";s:11:"onNewMinute";}'),
(15, 0, 7, 'onNewHour', '', '', 0, 0, '2012-11-17 15:00:00', 'a:3:{s:6:"object";s:10:"ClockChime";s:2:"op";s:1:"m";s:1:"m";s:9:"onNewHour";}'),
(16, 0, 9, 'Connected', '', '', 0, 0, NULL, NULL),
(55, 0, 18, 'Lost', '', '', 0, 0, NULL, NULL),
(18, 6, 0, 'onNewMinute', '', '$h=(int)date(''G'',time());\r\n$m=date(''i'',time());\r\n\r\n\r\nif (isWeekDay()) {\r\n\r\n}\r\n\r\n\r\nif (($h>=8)) {\r\n if ($m=="00") {\r\n   say(timeNow());\r\n }\r\n}', 1, 0, '2012-11-17 15:36:00', 'a:3:{s:6:"object";s:10:"ClockChime";s:2:"op";s:1:"m";s:1:"m";s:11:"onNewMinute";}'),
(19, 4, 0, 'Connected', '', 'if ($params[''serial'']=='''' && $params[''devname'']!='''') {\r\n $params[''serial'']=$params[''devname''];\r\n}\r\n\r\n$device=SQLSelectOne("SELECT * FROM usbdevices WHERE SERIAL LIKE ''".$params[''serial'']."''");\r\nif (!$device[''ID'']) {\r\n // new device connected\r\n //say("Подключено новое устройство",1);\r\n $device=array();\r\n $device[''SERIAL'']=$params[''serial''];\r\n $device[''TITLE'']=''устройство ''.$params[''devname''];\r\n $device[''FIRST_FOUND'']=date(''Y-m-d H:i:s'');\r\n $device[''LAST_FOUND'']=date(''Y-m-d H:i:s'');\r\n $device[''LOG''].=$device[''FIRST_FOUND''].'' подключено (впервые)''."\\n";\r\n $device[''ID'']=SQLInsert(''usbdevices'',$device);\r\n} else {\r\n // device already in our database\r\n //say("Подключено ".$device[''TITLE''],1);\r\n if ($device[''USER_ID'']) {\r\n  $user=SQLSelectOne("SELECT * FROM users WHERE ID=''".$device[''USER_ID'']."''");\r\n  if ($user[''ID'']) {\r\n    //$user[''NAME'']; // теперь мы знаем имя пользователя, связанного с этим устройством\r\n  }\r\n }\r\n $device[''LAST_FOUND'']=date(''Y-m-d H:i:s'');\r\n $device[''LOG'']=$device[''LAST_FOUND''].'' подключено''."\\n".$device[''LOG''];\r\n SQLUpdate(''usbdevices'',$device);\r\n if ($device[''SCRIPT_ID'']!='''') {\r\n  runScript($device[''SCRIPT_ID''],$params);\r\n } elseif ($device[''SCRIPT'']!='''') {\r\n  eval($device[''SCRIPT'']);\r\n }\r\n}', 1, 0, NULL, NULL),
(20, 0, 10, 'WakedUp', '', '', 0, 0, NULL, NULL),
(25, 0, 10, 'onIdle', '', '', 0, 0, NULL, NULL),
(27, 0, 10, 'StartUp', '', '', 0, 0, '2012-11-17 15:34:04', 'a:3:{s:6:"object";s:12:"ThisComputer";s:2:"op";s:1:"m";s:1:"m";s:7:"StartUp";}'),
(29, 0, 10, 'commandReceived', 'получение новой команды', '', 0, 0, NULL, NULL),
(30, 7, 0, 'commandReceived', '', '$command=$params[''command''];\r\n\r\n$short_command='''';\r\n$dt=recognizeTime($command,$short_command);\r\n\r\nif (preg_match(''/скажи сколько время/is'',$command)) {\r\n if ($dt>0) {\r\n  addScheduledJob("command".$dt,"processCommand(''".$short_command."'');",$dt);\r\n  say(''Задача принята'');\r\n  return;\r\n }\r\n global $voicemode;\r\n $voicemode=''on'';\r\n say(''Сейчас ''.timeNow());\r\n} elseif (preg_match(''/сколько время/is'',$command)) {\r\n if ($dt>0) {\r\n  addScheduledJob("command".$dt,"processCommand(''".$short_command."'');",$dt);\r\n  say(''Задача принята'');\r\n  echo $short_command;\r\n  return;\r\n }\r\n say(''Сейчас ''.timeNow());\r\n} elseif (preg_match(''/повтори (.+)/is'',$command,$m) || preg_match(''/скажи (.+)/is'',$command,$m)) {\r\n if ($dt>0) {\r\n  addScheduledJob("command".$dt,"processCommand(''".$short_command."'');",$dt);\r\n  say(''Задача принята'');\r\n  return;\r\n }\r\n global $voicemode;\r\n $voicemode=''on'';\r\n say($m[1]);\r\n} else {\r\n say(''Неизвестная команда...'');\r\n}', 1, 0, NULL, NULL),
(31, 0, 12, 'onNewMessage', '', '', 0, 0, NULL, NULL),
(32, 0, 12, 'onStatusChange', '', '', 0, 0, NULL, NULL),
(33, 10, 0, 'onStatusChange', NULL, 'echo "status received: ".$params[''status''];', 1, 0, NULL, NULL),
(34, 10, 0, 'onNewMessage', '', ' global $voicemode;\r\n $voicemode=''off'';\r\n processCommand($params[''message'']);', 1, 0, NULL, NULL),
(45, 0, 10, 'powerConnected', '', '', 0, 0, NULL, NULL),
(46, 0, 10, 'powerDisconnected', '', '', 0, 0, NULL, NULL),
(48, 7, 0, 'powerDisconnected', NULL, 'say("Отключено питание");', 1, 0, NULL, NULL),
(54, 0, 18, 'Found', '', '', 0, 0, NULL, NULL),
(56, 13, 0, 'Found', '', '// new device\r\n\r\n$tm=registeredEventTime(''btcall''); \r\nif ((time()-$tm)>60 || $tm<0) {\r\n registerEvent(''btcall''); \r\n}\r\n\r\n\r\nif ($params[''new'']) {\r\n //say(''Обнаружено новое блютуз устройство'');\r\n}\r\nif ($params[''user'']!='''') {\r\n //say($params[''user''].'' где-то рядом'');\r\n}', 1, 0, NULL, NULL),
(57, 13, 0, 'Lost', '', '// device lost\r\n', 1, 1, NULL, NULL),
(59, 0, 21, 'checkState', '', '	', 0, 0, '2012-11-17 15:36:35', ''),
(60, 0, 21, 'stateChanged', '', '', 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `newsletter`
--

DROP TABLE IF EXISTS `newsletter`;
CREATE TABLE IF NOT EXISTS `newsletter` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `EMAIL` varchar(255) NOT NULL DEFAULT '',
  `LIST` varchar(255) NOT NULL DEFAULT '',
  `ADDED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `objects`
--

DROP TABLE IF EXISTS `objects`;
CREATE TABLE IF NOT EXISTS `objects` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `CLASS_ID` int(10) NOT NULL DEFAULT '0',
  `DESCRIPTION` text,
  `LOCATION_ID` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

--
-- Dumping data for table `objects`
--

INSERT INTO `objects` (`ID`, `TITLE`, `CLASS_ID`, `DESCRIPTION`, `LOCATION_ID`) VALUES
(4, 'USBDev', 9, '', 0),
(13, 'BlueDev', 18, '', 0),
(6, 'ClockChime', 7, '', 0),
(7, 'ThisComputer', 10, '', 0),
(10, 'mySkype', 12, '', 0),
(15, 'ws', 20, '', 0),
(16, 'Security', 21, '', 0),
(17, 'System', 21, '', 0),
(18, 'Communication', 21, '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `owdevices`
--

DROP TABLE IF EXISTS `owdevices`;
CREATE TABLE IF NOT EXISTS `owdevices` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `UDID` varchar(255) NOT NULL DEFAULT '',
  `STATUS` int(3) NOT NULL DEFAULT '0',
  `CHECK_LATEST` datetime DEFAULT NULL,
  `CHECK_NEXT` datetime DEFAULT NULL,
  `SCRIPT_ID` int(10) NOT NULL DEFAULT '0',
  `CODE` text,
  `ONLINE_INTERVAL` int(10) NOT NULL DEFAULT '0',
  `LOG` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `owdisplays`
--

DROP TABLE IF EXISTS `owdisplays`;
CREATE TABLE IF NOT EXISTS `owdisplays` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UDID` int(10) unsigned NOT NULL DEFAULT '0',
  `ROWS` int(3) unsigned NOT NULL DEFAULT '0',
  `COLS` int(3) unsigned NOT NULL DEFAULT '0',
  `UPDATE_INTERVAL` int(10) unsigned NOT NULL DEFAULT '0',
  `VALUE` text,
  `UPDATE_LATEST` int(10) unsigned NOT NULL DEFAULT '0',
  `UPDATE_NEXT` int(10) unsigned NOT NULL DEFAULT '0',
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `owproperties`
--

DROP TABLE IF EXISTS `owproperties`;
CREATE TABLE IF NOT EXISTS `owproperties` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `DEVICE_ID` int(10) unsigned NOT NULL DEFAULT '0',
  `SYSNAME` varchar(255) NOT NULL DEFAULT '',
  `VALUE` varchar(255) NOT NULL DEFAULT '',
  `CHECK_LATEST` datetime DEFAULT NULL,
  `UPDATED` datetime DEFAULT NULL,
  `LINKED_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `LINKED_PROPERTY` varchar(255) NOT NULL DEFAULT '',
  `PATH` varchar(255) NOT NULL DEFAULT '',
  `STARRED` int(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `patterns`
--

DROP TABLE IF EXISTS `patterns`;
CREATE TABLE IF NOT EXISTS `patterns` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `PATTERN` text,
  `SCRIPT_ID` int(10) NOT NULL DEFAULT '0',
  `SCRIPT` text,
  `LOG` text,
  `TIME_LIMIT` int(245) NOT NULL DEFAULT '0',
  `EXECUTED` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `phistory`
--

DROP TABLE IF EXISTS `phistory`;
CREATE TABLE IF NOT EXISTS `phistory` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `VALUE_ID` int(10) unsigned NOT NULL DEFAULT '0',
  `ADDED` datetime DEFAULT NULL,
  `VALUE` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `VALUE_ID` (`VALUE_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `pinghosts`
--

DROP TABLE IF EXISTS `pinghosts`;
CREATE TABLE IF NOT EXISTS `pinghosts` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `HOSTNAME` varchar(255) NOT NULL DEFAULT '',
  `TYPE` int(30) NOT NULL DEFAULT '0',
  `STATUS` int(3) NOT NULL DEFAULT '0',
  `SEARCH_WORD` varchar(255) NOT NULL DEFAULT '',
  `CHECK_LATEST` datetime DEFAULT NULL,
  `CHECK_NEXT` datetime DEFAULT NULL,
  `SCRIPT_ID_ONLINE` int(10) NOT NULL DEFAULT '0',
  `CODE_ONLINE` text,
  `SCRIPT_ID_OFFLINE` int(10) NOT NULL DEFAULT '0',
  `CODE_OFFLINE` text,
  `OFFLINE_INTERVAL` int(10) NOT NULL DEFAULT '0',
  `ONLINE_INTERVAL` int(10) NOT NULL DEFAULT '0',
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `LOG` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `pinghosts`
--

INSERT INTO `pinghosts` (`ID`, `HOSTNAME`, `TYPE`, `STATUS`, `SEARCH_WORD`, `CHECK_LATEST`, `CHECK_NEXT`, `SCRIPT_ID_ONLINE`, `CODE_ONLINE`, `SCRIPT_ID_OFFLINE`, `CODE_OFFLINE`, `OFFLINE_INTERVAL`, `ONLINE_INTERVAL`, `TITLE`, `LOG`) VALUES
(8, 'tut.by', 0, 1, '', '2012-11-17 15:27:08', '2012-11-17 15:37:08', 0, '', 0, '', 600, 600, 'tut.by', '2012-11-17 14:47:08 Host is online\n2012-11-17 14:37:06 Host is offline\n2012-11-17 14:27:05 Host is online\n2012-11-16 17:29:14 Host is offline\n2012-11-16 16:59:02 Host is online\n2012-11-16 16:49:01 Host is offline\n2012-11-16 15:08:49 Host is online\n2012-11-16 14:58:45 Host is offline\n2012-10-31 09:59:11 Host is online\n2012-10-31 09:49:11 Host is offline\n2012-10-29 09:48:24 Host is online\n2012-10-29 06:37:11 Host is offline\n2012-10-29 06:27:07 Host is online\n2012-10-29 05:46:55 Host is offline\n2012-10-29 05:26:51 Host is online\n2012-10-29 05:16:51 Host is offline\n2012-10-29 05:06:47 Host is online\n2012-10-29 04:06:27 Host is offline\n2012-10-29 03:56:23 Host is online\n2012-10-29 03:46:23 Host is offline\n2012-10-29 03:36:19 Host is online\n2012-10-29 03:16:15 Host is offline\n2012-10-29 03:06:11 Host is online\n2012-10-29 01:25:44 Host is offline\n2012-10-29 01:15:41 Host is online\n2012-10-29 00:05:17 Host is offline\n2012-10-23 14:53:36 Host is online\n2012-10-23 14:23:28 Host is offline\n2012-10-19 06:11:55 Host is online\n2012-10-19 06:01:55 Host is offline\n2012-10-16 11:55:17 Host is online\n');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `COMMENTS` varchar(255) NOT NULL DEFAULT '',
  `CATEGORY_ID` int(10) NOT NULL DEFAULT '0',
  `INSTOCK` int(3) NOT NULL DEFAULT '0',
  `IMAGE` varchar(70) NOT NULL DEFAULT '',
  `WILL_EXPIRE` int(3) NOT NULL DEFAULT '0',
  `EXPIRE_DATE` date DEFAULT NULL,
  `EXPIRE_DEFAULT` int(10) NOT NULL DEFAULT '0',
  `UPDATED` datetime DEFAULT NULL,
  `QTY` int(10) NOT NULL DEFAULT '0',
  `MIN_QTY` int(10) NOT NULL DEFAULT '0',
  `DETAILS` text,
  `DEFAULT_PRICE` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
CREATE TABLE IF NOT EXISTS `product_categories` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `PRIORITY` int(10) NOT NULL DEFAULT '0',
  `PARENT_ID` int(10) NOT NULL DEFAULT '0',
  `SUB_LIST` text,
  `PARENT_LIST` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`ID`, `TITLE`, `PRIORITY`, `PARENT_ID`, `SUB_LIST`, `PARENT_LIST`) VALUES
(1, '<#LANG_APP_PRODUCTS#>', 0, 0, '2,3,4,5,7,8', '0');

-- --------------------------------------------------------

--
-- Table structure for table `product_codes`
--

DROP TABLE IF EXISTS `product_codes`;
CREATE TABLE IF NOT EXISTS `product_codes` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `CODE` varchar(255) NOT NULL DEFAULT '',
  `PRODUCT_ID` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=35 ;

--
-- Dumping data for table `product_codes`
--

INSERT INTO `product_codes` (`ID`, `TITLE`, `CODE`, `PRODUCT_ID`) VALUES
(33, 'ХЛЕБ ', '4811002027133', 1),
(34, 'МОЛОЧНАЯ КАША ДЛЯ ДЕТСКОГО ПИТАНИЯ ', '4606272002061', 2);

-- --------------------------------------------------------

--
-- Table structure for table `product_log`
--

DROP TABLE IF EXISTS `product_log`;
CREATE TABLE IF NOT EXISTS `product_log` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `PRODUCT_ID` int(10) NOT NULL DEFAULT '0',
  `CODE_ID` int(10) NOT NULL DEFAULT '0',
  `ACTION` char(10) NOT NULL DEFAULT '',
  `UPDATED` datetime DEFAULT NULL,
  `QTY` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `prod_categories`
--

DROP TABLE IF EXISTS `prod_categories`;
CREATE TABLE IF NOT EXISTS `prod_categories` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `project_modules`
--

DROP TABLE IF EXISTS `project_modules`;
CREATE TABLE IF NOT EXISTS `project_modules` (
  `ID` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `NAME` varchar(50) NOT NULL DEFAULT '',
  `TITLE` varchar(100) NOT NULL DEFAULT '',
  `CATEGORY` varchar(50) NOT NULL DEFAULT '',
  `PARENT_NAME` varchar(50) NOT NULL DEFAULT '',
  `DATA` text,
  `HIDDEN` int(3) NOT NULL DEFAULT '0',
  `PRIORITY` int(10) NOT NULL DEFAULT '0',
  `ADDED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=86 ;

--
-- Dumping data for table `project_modules`
--

INSERT INTO `project_modules` (`ID`, `NAME`, `TITLE`, `CATEGORY`, `PARENT_NAME`, `DATA`, `HIDDEN`, `PRIORITY`, `ADDED`) VALUES
(1, 'control_modules', 'Project Modules', 'System', '', '', 1, 0, '2011-09-29 18:16:10'),
(67, 'scripts', '<#LANG_MODULE_SCRIPTS#>', '<#LANG_SECTION_OBJECTS#>', '', '', 0, 0, '2010-09-13 15:12:16'),
(4, 'control_access', 'Control Access', '<#LANG_SECTION_SYSTEM#>', '', '', 1, 0, '2011-09-29 18:16:01'),
(7, 'master', '<#LANG_MODULE_MASTER_LOGIN#>', '<#LANG_SECTION_SYSTEM#>', '', '', 0, 0, '2009-01-30 10:21:41'),
(70, 'pinghosts', '<#LANG_MODULE_PINGHOSTS#>', '<#LANG_SECTION_OBJECTS#>', '', '', 0, 0, '2011-01-05 22:02:57'),
(20, 'saverestore', '<#LANG_MODULE_SAVERESTORE#>', '<#LANG_SECTION_SYSTEM#>', '', '', 0, 0, '2009-02-07 11:35:05'),
(21, 'userlog', '<#LANG_MODULE_USERLOG#>', '<#LANG_SECTION_SYSTEM#>', '', '', 1, 0, '2009-02-07 11:45:52'),
(22, 'skins', '<#LANG_MODULE_SKINS#>', '<#LANG_SECTION_SYSTEM#>', '', '', 1, 0, '2009-02-07 12:02:54'),
(23, 'settings', '<#LANG_MODULE_SETTINGS#>', '<#LANG_SECTION_SETTINGS#>', '', '', 0, 0, '2009-02-07 12:05:40'),
(24, 'dateselect', 'Date Selector', 'System', '', '', 1, 0, '2009-02-07 12:47:32'),
(25, 'thumb', '<#LANG_MODULE_THUMB#>', '<#LANG_SECTION_SYSTEM#>', '', '', 1, 0, '2009-02-07 12:48:32'),
(74, 'app_gpstrack', '<#LANG_APP_GPSTRACK#>', '<#LANG_SECTION_APPLICATIONS#>', '', '', 0, 0, '2011-07-25 11:27:19'),
(71, 'watchfolders', '<#LANG_MODULE_WATCHFOLDERS#>', '<#LANG_SECTION_OBJECTS#>', '', '', 0, 0, '2011-01-13 22:08:25'),
(73, 'app_player', '<#LANG_APP_PLAYER#>', '<#LANG_SECTION_APPLICATIONS#>', '', '', 0, 0, '2011-05-03 11:02:57'),
(28, 'dashboard', 'Dashboard', 'CMS', '', 'a:5:{s:12:"CPANEL_STATS";i:0;s:15:"CPANEL_USERNAME";s:0:"";s:15:"CPANEL_PASSWORD";s:0:"";s:13:"CPANEL_DOMAIN";s:0:"";s:10:"CPANEL_URL";s:0:"";}', 1, 0, '2009-02-23 10:15:23'),
(29, 'events', '<#LANG_MODULE_EVENTS#>', '<#LANG_SECTION_SYSTEM#>', '', '', 0, 0, '2009-03-27 13:04:51'),
(30, 'users', '<#LANG_MODULE_USERS#>', '<#LANG_SECTION_SETTINGS#>', '', '', 0, 0, '2009-03-27 13:08:07'),
(31, 'terminals', '<#LANG_MODULE_TERMINALS#>', '<#LANG_SECTION_SETTINGS#>', '', '', 0, 0, '2009-03-27 13:10:00'),
(34, 'commands', '<#LANG_MODULE_CONTROL_MENU#>', '<#LANG_SECTION_OBJECTS#>', '', '', 0, 0, '2009-04-11 03:14:03'),
(37, 'classes', '<#LANG_MODULE_OBJECTS#>', '<#LANG_SECTION_OBJECTS#>', '', '', 0, 0, '2009-05-22 10:09:27'),
(38, 'history', '<#LANG_MODULE_OBJECTS_HISTORY#>', '<#LANG_SECTION_OBJECTS#>', '', '', 0, 0, '2009-05-22 10:09:51'),
(39, 'locations', '<#LANG_MODULE_LOCATIONS#>', '<#LANG_SECTION_SETTINGS#>', '', '', 0, 0, '2009-05-22 10:11:01'),
(40, 'methods', '<#LANG_MODULE_METHODS#>', '<#LANG_SECTION_OBJECTS#>', '', '', 1, 0, '2009-05-22 10:11:23'),
(41, 'properties', '<#LANG_MODULE_PROPERTIES#>', '<#LANG_SECTION_OBJECTS#>', '', '', 1, 0, '2009-05-22 10:11:47'),
(42, 'objects', '<#LANG_MODULE_OBJECT_INSTANCES#>', '<#LANG_SECTION_OBJECTS#>', '', '', 1, 0, '2009-05-22 10:12:04'),
(85, 'pvalues', '<#LANG_MODULE_PVALUES#>', '<#LANG_SECTION_OBJECTS#>', '', '', 1, 0, '2012-11-16 15:04:26'),
(44, 'shoutbox', '<#LANG_MODULE_SHOUTBOX#>', '<#LANG_SECTION_SYSTEM#>', '', '', 1, 0, '2009-07-29 13:53:13'),
(45, 'shoutrooms', '<#LANG_MODULE_SHOUTROOMS#>', '<#LANG_SECTION_SYSTEM#>', '', '', 1, 0, '2009-07-29 13:53:28'),
(46, 'jobs', '<#LANG_MODULE_JOBS#>', '<#LANG_SECTION_SYSTEM#>', '', '', 0, 0, '2009-10-13 18:29:16'),
(47, 'btdevices', '<#LANG_MODULE_BT_DEVICES#>', '<#LANG_SECTION_OBJECTS#>', '', '', 0, 0, '2009-12-13 18:06:47'),
(80, 'app_calendar', '<#LANG_APP_CALENDAR#>', '<#LANG_SECTION_APPLICATIONS#>', '', '', 0, 0, '2012-06-25 09:34:08'),
(81, 'scenes', '<#LANG_MODULE_SCENES#>', '<#LANG_SECTION_OBJECTS#>', '', '', 0, 0, '2012-06-25 09:34:26'),
(49, 'usbdevices', '<#LANG_MODULE_USB_DEVICES#>', '<#LANG_SECTION_OBJECTS#>', '', '', 0, 0, '2010-03-22 12:18:32'),
(68, 'rss_channels', '<#LANG_MODULE_RSS_CHANNELS#>', '<#LANG_SECTION_SETTINGS#>', '', '', 0, 0, '2010-09-22 10:54:52'),
(61, 'app_mediabrowser', '<#LANG_APP_MEDIA_BROWSER#>', '<#LANG_SECTION_APPLICATIONS#>', '', '', 0, 0, '2010-08-31 09:09:33'),
(64, 'app_tdwiki', '<#LANG_APP_TDWIKI#>', '<#LANG_SECTION_APPLICATIONS#>', '', '', 0, 0, '2010-08-31 09:11:38'),
(63, 'app_products', '<#LANG_APP_PRODUCTS#>', '<#LANG_SECTION_APPLICATIONS#>', '', '', 0, 0, '2010-08-31 09:11:24'),
(66, 'layouts', '<#LANG_MODULE_LAYOUTS#>', '<#LANG_SECTION_SETTINGS#>', '', '', 0, 0, '2010-09-13 15:03:49'),
(82, 'webvars', '<#LANG_MODULE_WEBVARS#>', '<#LANG_SECTION_OBJECTS#>', '', '', 0, 0, '2012-11-06 08:49:14'),
(77, 'patterns', '<#LANG_MODULE_PATTERNS#>', '<#LANG_SECTION_OBJECTS#>', '', '', 0, 0, '2011-12-13 14:36:03'),
(78, 'onewire', '<#LANG_MODULE_ONEWIRE#>', '<#LANG_SECTION_OBJECTS#>', '', '', 0, 0, '2012-03-21 20:04:32'),
(83, 'xray', 'X-Ray', '<#LANG_SECTION_SYSTEM#>', '', '', 0, 0, '2012-11-16 14:59:57');

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

DROP TABLE IF EXISTS `properties`;
CREATE TABLE IF NOT EXISTS `properties` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `CLASS_ID` int(10) NOT NULL DEFAULT '0',
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `DESCRIPTION` text,
  `OBJECT_ID` int(10) NOT NULL DEFAULT '0',
  `KEEP_HISTORY` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=48 ;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`ID`, `CLASS_ID`, `TITLE`, `DESCRIPTION`, `OBJECT_ID`, `KEEP_HISTORY`) VALUES
(1, 1, 'lastTimePressed', 'когда нажималась в последний раз', 0, 0),
(8, 5, 'СтепеньКрасноты', '', 0, 0),
(10, 10, 'checked', 'время последней проверки', 0, 0),
(17, 0, 'testProp', NULL, 7, 0),
(12, 7, 'time', 'текущее время', 0, 0),
(18, 10, 'minMsgLevel', '', 0, 0),
(19, 19, 'temp', '', 0, 7),
(20, 0, 'weatherFull', NULL, 7, 0),
(21, 0, 'AlarmTime', NULL, 7, 0),
(22, 0, 'textBoxTest', NULL, 7, 0),
(23, 0, '1w_temp', NULL, 7, 0),
(24, 20, 'tempOutside', NULL, 0, 7),
(25, 20, 'relHumOutside', NULL, 0, 7),
(26, 20, 'dewPoint', NULL, 0, 7),
(27, 20, 'windLatest', NULL, 0, 7),
(28, 20, 'windAverage', NULL, 0, 7),
(29, 20, 'rainfallRate', NULL, 0, 7),
(30, 20, 'rainfallHour', NULL, 0, 7),
(31, 20, 'rainfall24', NULL, 0, 7),
(32, 20, 'pressure', NULL, 0, 7),
(33, 20, 'pressureTrend', NULL, 0, 7),
(34, 20, 'windDirection', NULL, 0, 7),
(35, 20, 'windDirectionAverage', NULL, 0, 7),
(36, 20, 'tempInside', NULL, 0, 7),
(37, 20, 'relHumInside', NULL, 0, 7),
(38, 20, 'pressureRt', NULL, 0, 7),
(39, 20, 'updatedTime', NULL, 0, 7),
(40, 20, 'updatedDate', NULL, 0, 7),
(41, 21, 'stateDetails', 'details for the state', 0, 0),
(42, 21, 'stateColor', 'green / yellow / red', 0, 0),
(43, 0, 'TempOutside', NULL, 7, 0),
(44, 0, 'Econom', NULL, 7, 0),
(45, 0, 'securityMode', NULL, 7, 0),
(46, 0, 'nobodyHome', NULL, 7, 0),
(47, 0, 'WeHaveGuests', NULL, 7, 0);

-- --------------------------------------------------------

--
-- Table structure for table `pvalues`
--

DROP TABLE IF EXISTS `pvalues`;
CREATE TABLE IF NOT EXISTS `pvalues` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PROPERTY_ID` int(10) NOT NULL DEFAULT '0',
  `OBJECT_ID` int(10) NOT NULL DEFAULT '0',
  `VALUE` text NOT NULL,
  `UPDATED` datetime DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=85 ;

--
-- Dumping data for table `pvalues`
--

INSERT INTO `pvalues` (`ID`, `PROPERTY_ID`, `OBJECT_ID`, `VALUE`, `UPDATED`) VALUES
(59, 18, 7, '0', NULL),
(60, 17, 7, '-15', NULL),
(58, 10, 7, '1346415251', NULL),
(24, 12, 6, '2012-11-17 15:36:00', '2012-11-17 15:36:00'),
(61, 20, 7, '\n<br>  \n<b>Сегодня:</b><br />днем: +2&deg;...+4&deg;, облачно, ночью: +1&deg;...+3&deg;,\nоблачно, ветер: Ю — 4-6 м/с, давление: 768 мм.рт.ст, влажность: 95%<br /><br />\n<b>Завтра:</b><br />днем: +0&deg;...+2&deg;, облачно, ночью: +0&deg;,\nоблачно, ветер: Ю — 6-8 м/с, давление: 764 мм.рт.ст, влажность: 90%<br /><br />\n', '2012-11-17 15:01:40'),
(80, 43, 7, '+3.0', '2012-11-17 15:02:20'),
(62, 21, 7, '09:30', NULL),
(63, 22, 7, '0', NULL),
(64, 23, 7, '4', NULL),
(65, 24, 15, '', NULL),
(66, 25, 15, '', NULL),
(67, 26, 15, '', NULL),
(68, 27, 15, '', NULL),
(69, 28, 15, '', NULL),
(70, 29, 15, '', NULL),
(71, 30, 15, '', NULL),
(72, 31, 15, '', NULL),
(73, 32, 15, '', NULL),
(74, 33, 15, '', NULL),
(75, 34, 15, '', NULL),
(76, 35, 15, '', NULL),
(77, 36, 15, '', NULL),
(78, 37, 15, '', NULL),
(79, 38, 15, '0', NULL),
(81, 44, 7, '0', '2012-11-17 14:47:45'),
(82, 45, 7, '0', '2012-11-17 14:47:47'),
(83, 46, 7, '0', '2012-11-17 14:47:49'),
(84, 47, 7, '0', '2012-11-17 14:47:52');

-- --------------------------------------------------------

--
-- Table structure for table `rss_channels`
--

DROP TABLE IF EXISTS `rss_channels`;
CREATE TABLE IF NOT EXISTS `rss_channels` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `URL` char(255) NOT NULL DEFAULT '',
  `NEXT_UPDATE` datetime DEFAULT NULL,
  `LAST_UPDATE` datetime DEFAULT NULL,
  `UPDATE_EVERY` int(10) NOT NULL DEFAULT '0',
  `SCRIPT_ID` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `rss_items`
--

DROP TABLE IF EXISTS `rss_items`;
CREATE TABLE IF NOT EXISTS `rss_items` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `BODY` text,
  `URL` char(255) NOT NULL DEFAULT '',
  `GUID` varchar(255) NOT NULL DEFAULT '',
  `ADDED` varchar(255) NOT NULL DEFAULT '',
  `CHANNEL_ID` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11825 ;

-- --------------------------------------------------------

--
-- Table structure for table `safe_execs`
--

DROP TABLE IF EXISTS `safe_execs`;
CREATE TABLE IF NOT EXISTS `safe_execs` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `COMMAND` text NOT NULL,
  `ADDED` datetime DEFAULT NULL,
  `EXCLUSIVE` int(3) NOT NULL DEFAULT '0',
  `PRIORITY` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `scenes`
--

DROP TABLE IF EXISTS `scenes`;
CREATE TABLE IF NOT EXISTS `scenes` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `BACKGROUND` varchar(255) NOT NULL DEFAULT '',
  `PRIORITY` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `scenes`
--

INSERT INTO `scenes` (`ID`, `TITLE`, `BACKGROUND`, `PRIORITY`) VALUES
(1, 'Scene 1', '/cms/scenes/backgrounds/photolib.png', 100),
(2, 'Scene 2', '/cms/scenes/backgrounds/photolib2.png', 50);

-- --------------------------------------------------------

--
-- Table structure for table `scripts`
--

DROP TABLE IF EXISTS `scripts`;
CREATE TABLE IF NOT EXISTS `scripts` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `CODE` text,
  `DESCRIPTION` text,
  `TYPE` int(3) unsigned NOT NULL DEFAULT '0',
  `XML` text,
  `CATEGORY_ID` int(10) unsigned NOT NULL DEFAULT '0',
  `EXECUTED` datetime DEFAULT NULL,
  `EXECUTED_PARAMS` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `scripts`
--

INSERT INTO `scripts` (`ID`, `TITLE`, `CODE`, `DESCRIPTION`, `TYPE`, `XML`, `CATEGORY_ID`, `EXECUTED`, `EXECUTED_PARAMS`) VALUES
(1, 'morningGreeting', '', '', 1, '<xml xmlns="http://www.w3.org/1999/xhtml">\r\n  <block type="majordomo_say" inline="false" x="140" y="51">\r\n    <value name="TEXT">\r\n      <block type="text">\r\n        <title name="TEXT">Good morning!</title>\r\n      </block>\r\n    </value>\r\n    <next>\r\n      <block type="controls_if" inline="false">\r\n        <value name="IF0">\r\n          <block type="logic_negate" inline="false">\r\n            <value name="BOOL">\r\n              <block type="majordomo_getglobal">\r\n                <title name="TEXT">ThisComputer.WeHaveGuests</title>\r\n              </block>\r\n            </value>\r\n          </block>\r\n        </value>\r\n        <statement name="DO0">\r\n          <block type="majordomo_runscript" inline="false">\r\n            <title name="TEXT">sayTodayAgenda</title>\r\n            <next>\r\n              <block type="majordomo_runscript" inline="false">\r\n                <title name="TEXT">playFavoriteMusic</title>\r\n              </block>\r\n            </next>\r\n          </block>\r\n        </statement>\r\n      </block>\r\n    </next>\r\n  </block>\r\n</xml>', 0, NULL, NULL),
(8, 'timeNow', 'say(timeNow());', NULL, 0, NULL, 3, NULL, NULL),
(9, 'EconomChanged', '', '', 1, '<xml xmlns="http://www.w3.org/1999/xhtml">\r\n  <block type="controls_if" inline="false" x="197" y="88">\r\n    <mutation else="1"></mutation>\r\n    <value name="IF0">\r\n      <block type="majordomo_getglobal">\r\n        <title name="TEXT">ThisComputer.Econom</title>\r\n      </block>\r\n    </value>\r\n    <statement name="DO0">\r\n      <block type="majordomo_say" inline="false">\r\n        <value name="TEXT">\r\n          <block type="text">\r\n            <title name="TEXT">Перехожу в режим экономии</title>\r\n          </block>\r\n        </value>\r\n      </block>\r\n    </statement>\r\n    <statement name="ELSE">\r\n      <block type="majordomo_say" inline="false">\r\n        <value name="TEXT">\r\n          <block type="text">\r\n            <title name="TEXT">Выхожу из режима экономии</title>\r\n          </block>\r\n        </value>\r\n      </block>\r\n    </statement>\r\n  </block>\r\n</xml>', 4, NULL, NULL),
(3, 'rssProcess', '/*\r\n$params[''URL''] --link\r\n$params[''TITLE''] -- title\r\n$params[''BODY''] -- body\r\n$params[''CHANNEL_ID''] -- channel ID\r\n$params[''CHANNEL_TITLE''] -- channed title\r\n\r\n*/\r\n\r\n//say($params[''TITLE'']); // reading news', '', 0, '', 0, NULL, NULL),
(10, 'test', 'setTimeOut(''testTimer'',''say("Hello world!");'',30);', '', 0, '', 3, '2012-11-17 15:19:54', '');

-- --------------------------------------------------------

--
-- Table structure for table `script_categories`
--

DROP TABLE IF EXISTS `script_categories`;
CREATE TABLE IF NOT EXISTS `script_categories` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `script_categories`
--

INSERT INTO `script_categories` (`ID`, `TITLE`) VALUES
(3, 'Test'),
(4, '<#LANG_GENERAL_OPERATIONAL_MODES#>'),
(5, '<#LANG_GENERAL_SENSORS#>');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `PRIORITY` int(3) unsigned NOT NULL DEFAULT '0',
  `HR` int(3) unsigned NOT NULL DEFAULT '0',
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `NAME` varchar(50) NOT NULL DEFAULT '',
  `TYPE` varchar(59) NOT NULL DEFAULT '',
  `NOTES` text NOT NULL,
  `VALUE` varchar(255) NOT NULL DEFAULT '',
  `DEFAULTVALUE` varchar(255) NOT NULL DEFAULT '',
  `URL` varchar(255) NOT NULL DEFAULT '',
  `URL_TITLE` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=40 ;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`ID`, `PRIORITY`, `HR`, `TITLE`, `NAME`, `TYPE`, `NOTES`, `VALUE`, `DEFAULTVALUE`, `URL`, `URL_TITLE`) VALUES
(1, 255, 0, 'Site Title', 'SITE_TITLE', 'text', '', 'MajorDoMo', 'CMS1', '', ''),
(2, 254, 0, 'Site Domain Name', 'SITE_DOMAIN', 'text', '', 'domain.com', 'domain.com', '', ''),
(3, 0, 0, 'Contact E-mail', 'SITE_EMAIL', 'text', '', 'info@domain.com', 'info@domain.com', '', ''),
(7, 0, 0, 'Days to show in "soon" section', 'APP_CALENDAR_SOONLIMIT', 'text', '', '6', '6', '', ''),
(8, 0, 0, 'Show recently done items', 'APP_CALENDAR_SHOWDONE', 'yesno', '', '1', '1', '', ''),
(9, 0, 0, 'Scene width', 'SCENES_WIDTH', 'text', '', '803', '803', '', ''),
(24, 0, 0, 'Scene height', 'SCENES_HEIGHT', 'text', '', '606', '606', '', ''),
(29, 100, 0, 'Run bluetooth scanner', 'BLUETOOTH_CYCLE', 'onoff', '', '0', '0', '', ''),
(30, 100, 0, 'Run Skype script', 'SKYPE_CYCLE', 'onoff', '', '0', '0', '', ''),
(31, 30, 0, 'Twitter Consumer key', 'TWITTER_CKEY', 'text', '', '', '', '', ''),
(32, 29, 0, 'Twitter Consumer secret', 'TWITTER_CSECRET', 'text', '', '', '', '', ''),
(33, 28, 0, 'Twitter Access token', 'TWITTER_ATOKEN', 'text', '', '', '', '', ''),
(34, 27, 0, 'Twitter Access token secret', 'TWITTER_ASECRET', 'text', '', '', '', '', ''),
(35, 0, 0, 'Save debug information to history', 'DEBUG_HISTORY', 'onoff', '', '0', '0', '', ''),
(36, 60, 0, 'Use Google Text-to-Speech engine', 'TTS_GOOGLE', 'onoff', '', '1', '1', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `shoplist`
--

DROP TABLE IF EXISTS `shoplist`;
CREATE TABLE IF NOT EXISTS `shoplist` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `shopping_list_items`
--

DROP TABLE IF EXISTS `shopping_list_items`;
CREATE TABLE IF NOT EXISTS `shopping_list_items` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `PRODUCT_ID` int(10) NOT NULL DEFAULT '0',
  `PRICE` float NOT NULL DEFAULT '0',
  `CODE` varchar(255) NOT NULL DEFAULT '',
  `IN_CART` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- Table structure for table `shoutrooms`
--

DROP TABLE IF EXISTS `shoutrooms`;
CREATE TABLE IF NOT EXISTS `shoutrooms` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(250) NOT NULL DEFAULT '',
  `PRIORITY` int(10) NOT NULL DEFAULT '0',
  `ADDED_BY` int(10) NOT NULL DEFAULT '0',
  `ADDED` datetime DEFAULT NULL,
  `IS_PUBLIC` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `shouts`
--

DROP TABLE IF EXISTS `shouts`;
CREATE TABLE IF NOT EXISTS `shouts` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ROOM_ID` int(10) NOT NULL DEFAULT '0',
  `MEMBER_ID` int(10) NOT NULL DEFAULT '0',
  `MESSAGE` varchar(255) NOT NULL DEFAULT '',
  `ADDED` datetime DEFAULT NULL,
  `IMPORTANCE` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `tdwiki`
--

DROP TABLE IF EXISTS `tdwiki`;
CREATE TABLE IF NOT EXISTS `tdwiki` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `NAME` varchar(100) NOT NULL DEFAULT '',
  `CONTENT` text,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `tdwiki`
--

INSERT INTO `tdwiki` (`ID`, `NAME`, `CONTENT`) VALUES
(1, '', '<!--#\n @version 0.1 (auto-set)\n#-->\n<div tiddler="DefaultTiddlers" modified="200610311519" modifier="Serge J." tags="">[[Notes]]</div>\r\n<div tiddler="MainMenu" modified="200610311519" modifier="Serge J." tags="">[[Notes]]  [[FormattingInstructions]] [[MainMenu]] [[DefaultTiddlers]]</div>\r\n<div tiddler="FormattingInstructions" modified="200607061334" modifier="YourName" tags="">TiddlyWiki uses Wiki style markup, a way of lightly &quot;tagging&quot; plain text so it can be transformed into HTML. Edit this Tiddler to see samples.\\n\\n! Header Samples\\n!Header 1\\n!!Header 2\\n!!!Header 3\\n!!!!Header 4\\n!!!!!Header 5\\n\\n! Unordered Lists:\\n* Lists are where it''s at\\n* Just use an asterisk and you''re set\\n** To nest lists just add more asterisks...\\n***...like this\\n* The circle makes a great bullet because once you''ve printed a list you can mark off completed items\\n* You can also nest mixed list types\\n## Like this\\n\\n! Ordered Lists\\n# Ordered lists are pretty neat too\\n# If you''re handy with HTML and CSS you could customize the [[numbering scheme|http://www.w3schools.com/css/pr_list-style-type.asp]]\\n## To nest, just add more octothorpes (pound signs)...\\n### Like this\\n* You can also\\n** Mix list types\\n*** like this\\n# Pretty neat don''t you think?\\n\\n! Tiddler links\\nTo create a Tiddler link, just use mixed-case WikiWord, or use [[brackets]] for NonWikiWordLinks. This is how the GTD style [[@Action]] lists are created. \\n\\nNote that existing Tiddlers are in bold and empty Tiddlers are in italics. See CreatingTiddlers for details.\\n\\n! External Links\\nYou can link to [[external sites|http://google.com]] with brackets. You can also LinkToFolders on your machine or network shares.\\n\\n! Images\\nEdit this tiddler to see how it''s done.\\n[img[http://img110.echo.cx/img110/139/gorilla8nw.jpg]]\\n\\n!Tables\\n|!th1111111111|!th2222222222|\\n|&gt;| colspan |\\n| rowspan |left|\\n|~| right|\\n|colored| center |\\n|caption|c\\n\\nFor a complex table example, see PeriodicTable.\\n\\n! Horizontal Rules\\nYou can divide a tiddler into\\n----\\nsections by typing four dashes on a line by themselves.\\n\\n! Blockquotes\\n&lt;&lt;&lt;\\nThis is how you do an extended, wrapped blockquote so you don''t have to put angle quotes on every line.\\n&lt;&lt;&lt;\\n&gt;level 1\\n&gt;level 1\\n&gt;&gt;level 2\\n&gt;&gt;level 2\\n&gt;&gt;&gt;level 3\\n&gt;&gt;&gt;level 3\\n&gt;&gt;level 2\\n&gt;level 1\\n\\n! Other Formatting\\n''''Bold''''\\n==Strike==\\n__Underline__\\n//Italic//\\nSuperscript: 2^^3^^=8\\nSubscript: a~~ij~~ = -a~~ji~~\\n@@highlight@@ Unfortunately highlighting is broken right now.\\n@@color(green):green colored@@\\n@@bgcolor(#ff0000):color(#ffffff):red colored@@ Hex colors are also broken right now.\\n</div>');

-- --------------------------------------------------------

--
-- Table structure for table `terminals`
--

DROP TABLE IF EXISTS `terminals`;
CREATE TABLE IF NOT EXISTS `terminals` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) NOT NULL DEFAULT '',
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `HOST` varchar(255) NOT NULL DEFAULT '',
  `CANPLAY` int(3) NOT NULL DEFAULT '0',
  `PLAYER_TYPE` char(10) NOT NULL DEFAULT '',
  `PLAYER_PORT` varchar(255) NOT NULL DEFAULT '',
  `PLAYER_USERNAME` varchar(255) NOT NULL DEFAULT '',
  `PLAYER_PASSWORD` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `terminals`
--

INSERT INTO `terminals` (`ID`, `NAME`, `TITLE`, `HOST`, `CANPLAY`, `PLAYER_TYPE`, `PLAYER_PORT`, `PLAYER_USERNAME`, `PLAYER_PASSWORD`) VALUES
(2, 'MAIN', 'Server', 'localhost', 1, '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `usbdevices`
--

DROP TABLE IF EXISTS `usbdevices`;
CREATE TABLE IF NOT EXISTS `usbdevices` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `SERIAL` varchar(255) NOT NULL DEFAULT '',
  `LOG` text NOT NULL,
  `LAST_FOUND` datetime DEFAULT NULL,
  `FIRST_FOUND` datetime DEFAULT NULL,
  `USER_ID` int(10) NOT NULL DEFAULT '0',
  `SCRIPT` text NOT NULL,
  `SCRIPT_ID` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=67 ;

-- --------------------------------------------------------

--
-- Table structure for table `userlog`
--

DROP TABLE IF EXISTS `userlog`;
CREATE TABLE IF NOT EXISTS `userlog` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `USER_ID` int(10) NOT NULL DEFAULT '0',
  `MESSAGE` varchar(100) NOT NULL DEFAULT '',
  `IP` varchar(20) NOT NULL DEFAULT '',
  `ADDED` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `USERNAME` varchar(255) NOT NULL DEFAULT '',
  `NAME` varchar(255) NOT NULL DEFAULT '',
  `EMAIL` varchar(255) NOT NULL DEFAULT '',
  `SKYPE` varchar(255) NOT NULL DEFAULT '',
  `MOBILE` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `USERNAME`, `NAME`, `EMAIL`, `SKYPE`, `MOBILE`) VALUES
(1, 'user1', 'User1', 'user@domain.com', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `watchfolders`
--

DROP TABLE IF EXISTS `watchfolders`;
CREATE TABLE IF NOT EXISTS `watchfolders` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `FOLDER` varchar(255) NOT NULL DEFAULT '',
  `CHECK_MASK` varchar(255) NOT NULL DEFAULT '',
  `CHECK_LATEST` datetime DEFAULT NULL,
  `CHECK_NEXT` datetime DEFAULT NULL,
  `CHECK_INTERVAL` int(255) NOT NULL DEFAULT '0',
  `SCRIPT_ID` int(10) NOT NULL DEFAULT '0',
  `SCRIPT_TYPE` int(255) NOT NULL DEFAULT '0',
  `CHECK_RESULTS` longtext,
  `CHECK_SUB` int(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `webvars`
--

DROP TABLE IF EXISTS `webvars`;
CREATE TABLE IF NOT EXISTS `webvars` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `HOSTNAME` varchar(255) NOT NULL DEFAULT '',
  `TYPE` int(30) NOT NULL DEFAULT '0',
  `SEARCH_PATTERN` varchar(255) NOT NULL DEFAULT '',
  `CHECK_PATTERN` varchar(255) NOT NULL DEFAULT '',
  `LATEST_VALUE` text NOT NULL,
  `CHECK_LATEST` datetime DEFAULT NULL,
  `CHECK_NEXT` datetime DEFAULT NULL,
  `SCRIPT_ID` int(10) NOT NULL DEFAULT '0',
  `ONLINE_INTERVAL` int(10) NOT NULL DEFAULT '0',
  `LINKED_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `LINKED_PROPERTY` varchar(255) NOT NULL DEFAULT '',
  `CODE` text,
  `LOG` text,
  `ENCODING` varchar(50) NOT NULL DEFAULT '',
  `AUTH` int(3) NOT NULL DEFAULT '0',
  `USERNAME` varchar(100) NOT NULL DEFAULT '',
  `PASSWORD` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `webvars`
--

INSERT INTO `webvars` (`ID`, `TITLE`, `HOSTNAME`, `TYPE`, `SEARCH_PATTERN`, `CHECK_PATTERN`, `LATEST_VALUE`, `CHECK_LATEST`, `CHECK_NEXT`, `SCRIPT_ID`, `ONLINE_INTERVAL`, `LINKED_OBJECT`, `LINKED_PROPERTY`, `CODE`, `LOG`, `ENCODING`, `AUTH`, `USERNAME`, `PASSWORD`) VALUES
(1, '<#LANG_GENERAL_WEATHER_FORECAST#>', 'http://pogoda.tut.by/pda/city/26850/', 0, 'город<\\/a><br>(.+?)<br \\/><a', '', '\n<br>  \n<b>Сегодня:</b><br />днем: +2&deg;...+4&deg;, облачно, ночью: +1&deg;...+3&deg;,\nоблачно, ветер: Ю — 4-6 м/с, давление: 768 мм.рт.ст, влажность: 95%<br /><br />\n<b>Завтра:</b><br />днем: +0&deg;...+2&deg;, облачно, ночью: +0&deg;,\nоблачно, ветер: Ю — 6-8 м/с, давление: 764 мм.рт.ст, влажность: 90%<br /><br />\n', '2012-11-17 15:01:40', '2012-11-17 17:01:40', 0, 7200, 'ThisComputer', 'weatherFull', '', '2012-11-17 15:01:40 new value:\n<br>  \n<b>Сегодня:</b><br />днем: +2&deg;...+4&deg;, облачно, ночью: +1&deg;...+3&deg;,\nоблачно, ветер: Ю — 4-6 м/с, давление: 768 мм.рт.ст, влажность: 95%<br /><br />\n<b>Завтра:</b><br />днем: +0&deg;...+2&deg;, облачно, ночью: +0&deg;,\nоблачно, ветер: Ю — 6-8 м/с, давление: 764 мм.рт.ст, влажность: 90%<br /><br />\n\n2012-11-17 14:33:22 new value:\n<br>  \n<b>Сегодня:</b><br />днем: +2&deg;...+4&deg;, облачно, ночью: +1&deg;...+3&deg;,\nоблачно, ветер: Ю — 4-6 м/с, давление: 768 мм.рт.ст, влажность: 95%<br /><br />\n<b>Завтра:</b><br />днем: +0&deg;...+2&deg;, облачно, ночью: +0&deg;,\nоблачно, ветер: Ю — 6-8 м/с, давление: 764 мм.рт.ст, влажность: 90%<br /><br />\n\n2012-11-16 14:20:58 new value:\n<br>  \n<b>Сегодня:</b><br />днем: +3&deg;...+5&deg;, облачно, ночью: +0&deg;...+2&deg;,\nоблачно, ветер: З — 3-5 м/с, давление: 769 мм.рт.ст, влажность: 95%<br /><br />\n<b>Завтра:</b><br />днем: +1&deg;...+3&deg;, облачно, ночью: +1&deg;...+3&deg;,\nоблачно, ветер: Ю — 4-6 м/с, давление: 768 мм.рт.ст, влажность: 90%<br /><br />\n\n2012-11-16 14:15:56 incorrect value:\n2012-11-16 14:15:43 incorrect value:\n2012-11-16 13:58:39 incorrect value:\n2012-11-07 14:02:19 incorrect value:\n2012-11-07 12:02:19 incorrect value:\n2012-11-07 10:02:19 incorrect value:\n2012-11-07 08:02:19 incorrect value:\n2012-11-07 06:02:19 incorrect value:\n2012-11-07 04:02:19 incorrect value:\n2012-11-07 02:02:19 incorrect value:\n2012-11-07 00:02:19 incorrect value:\n2012-11-06 22:02:19 incorrect value:\n2012-11-06 20:02:19 incorrect value:\n2012-11-06 18:02:20 incorrect value:\n2012-11-03 13:58:16 incorrect value:\n2012-11-03 11:58:16 incorrect value:\n2012-11-03 09:58:16 incorrect value:\n2012-11-03 07:58:16 incorrect value:\n2012-11-03 05:58:16 incorrect value:\n2012-11-03 03:58:16 incorrect value:\n2012-11-03 01:58:16 incorrect value:\n2012-11-02 23:58:16 incorrect value:\n2012-11-02 21:58:16 incorrect value:\n2012-11-02 19:58:16 incorrect value:\n2012-11-02 17:58:16 incorrect value:\n2012-11-02 15:58:16 incorrect value:\n2012-11-02 13:58:15 incorrect value:\n2012-11-02 11:58:15 incorrect value:\n2012-11-02 09:58:22 incorrect value:\n2012-11-02 07:58:15 incorrect value:\n2012-11-02 05:58:15 incorrect value:\n2012-11-02 03:58:15 incorrect value:\n2012-11-02 01:58:15 incorrect value:\n2012-11-01 23:58:15 incorrect value:\n2012-11-01 21:58:15 incorrect value:\n2012-11-01 19:58:15 incorrect value:\n2012-11-01 17:58:15 incorrect value:\n2012-11-01 15:58:15 incorrect value:\n2012-11-01 13:58:16 incorrect value:\n2012-11-01 11:58:15 incorrect value:\n2012-11-01 09:58:15 incorrect value:\n2012-11-01 07:58:15 incorrect value:\n2012-11-01 05:58:16 incorrect value:\n2012-11-01 03:58:15 incorrect value:\n2012-11-01 01:58:16 incorrect value:\n2012-10-31 23:58:15 incorrect value:\n2012-10-31 21:58:16 incorrect value:\n2012-10-31 19:58:15 incorrect value:\n2012-10-31 17:58:15 incorrect value:\n2012-10-31 15:58:15 incorrect value:\n2012-10-31 13:58:15 incorrect value:\n2012-10-31 11:58:15 incorrect value:\n2012-10-31 09:58:15 incorrect value:\n2012-10-31 07:58:15 incorrect value:\n2012-10-31 05:58:15 incorrect value:\n2012-10-31 03:58:15 incorrect value:\n2012-10-31 01:58:16 incorrect value:\n2012-10-30 23:58:15 incorrect value:\n2012-10-30 21:58:15 incorrect value:\n2012-10-30 19:58:15 incorrect value:\n2012-10-30 17:58:14 incorrect value:\n2012-10-30 15:58:14 incorrect value:\n2012-10-30 13:58:14 incorrect value:\n2012-10-30 11:58:14 incorrect value:\n2012-10-30 09:58:14 incorrect value:\n2012-10-30 07:58:14 incorrect value:\n2012-10-30 05:58:14 incorrect value:\n2012-10-30 03:58:14 incorrect value:\n2012-10-30 01:58:15 incorrect value:\n2012-10-29 23:58:14 incorrect value:\n2012-10-29 21:58:14 incorrect value:\n2012-10-29 19:58:14 incorrect value:\n2012-10-29 17:58:14 incorrect value:\n2012-10-29 15:58:14 incorrect value:\n2012-10-29 13:58:14 incorrect value:\n2012-10-29 11:58:14 incorrect value:\n2012-10-29 09:58:15 incorrect value:\n2012-10-29 07:58:14 incorrect value:\n2012-10-29 05:59:16 incorrect value:\n2012-10-29 03:57:09 incorrect value:\n2012-10-29 01:57:09 incorrect value:\n2012-10-28 23:55:18 incorrect value:\n2012-10-28 21:55:18 incorrect value:\n2012-10-28 19:55:18 incorrect value:\n2012-10-28 17:55:18 incorrect value:\n2012-10-28 15:55:18 incorrect value:\n2012-10-28 13:55:19 incorrect value:\n2012-10-28 11:55:18 incorrect value:\n2012-10-28 09:55:19 incorrect value:\n2012-10-28 07:55:18 incorrect value:\n2012-10-28 05:55:18 incorrect value:\n2012-10-28 03:55:18 incorrect value:\n2012-10-28 01:55:18 incorrect value:\n2012-10-27 23:55:18 incorrect value:\n2012-10-27 21:55:18 incorrect value:\n2012-10-27 19:55:18 incorrect value:\n2012-10-27 17:55:18 incorrect value:\n2012-10-27 15:55:18 incorrect value:\n2012-10-27 13:55:18 incorrect value:\n2012-10-27 11:55:18 incorrect value:\n2012-10-27 09:55:18 incorrect value:\n2012-10-27 07:55:18 incorrect value:\n2012-10-27 05:55:18 incorrect value:\n2012-10-27 03:55:18 incorrect value:\n2012-10-27 01:55:18 incorrect value:\n2012-10-26 23:55:18 incorrect value:\n2012-10-26 21:55:18 incorrect value:\n2012-10-26 19:55:18 incorrect value:\n2012-10-26 17:55:18 incorrect value:\n2012-10-26 15:55:18 incorrect value:\n2012-10-26 13:55:18 incorrect value:\n2012-10-26 11:55:18 incorrect value:\n2012-10-26 09:55:19 incorrect value:\n2012-10-26 07:55:18 incorrect value:\n2012-10-26 05:55:18 incorrect value:\n2012-10-26 03:55:18 incorrect value:\n2012-10-26 01:55:19 incorrect value:\n2012-10-25 23:55:18 incorrect value:\n2012-10-25 21:55:18 incorrect value:\n2012-10-25 19:55:18 incorrect value:\n2012-10-25 17:55:18 incorrect value:\n2012-10-25 15:55:18 incorrect value:\n2012-10-25 13:55:19 incorrect value:\n2012-10-25 11:55:18 incorrect value:\n2012-10-25 09:55:18 incorrect value:\n2012-10-25 07:55:18 incorrect value:\n2012-10-25 05:55:18 incorrect value:\n2012-10-25 03:55:18 incorrect value:\n2012-10-25 01:55:18 incorrect value:\n2012-10-24 23:55:18 incorrect value:\n2012-10-24 21:55:18 incorrect value:\n2012-10-24 19:55:18 incorrect value:\n2012-10-24 17:55:18 incorrect value:\n2012-10-24 15:55:18 incorrect value:\n2012-10-24 13:55:18 incorrect value:\n2012-10-24 11:55:18 incorrect value:\n2012-10-24 09:55:19 incorrect value:\n2012-10-24 07:55:18 incorrect value:\n2012-10-24 05:55:18 incorrect value:\n2012-10-24 03:55:18 incorrect value:\n2012-10-24 01:55:18 incorrect value:\n2012-10-23 23:55:18 incorrect value:\n2012-10-23 21:55:18 incorrect value:\n2012-10-23 19:55:18 incorrect value:\n2012-10-23 17:55:19 incorrect value:\n2012-10-23 15:55:18 incorrect value:\n2012-10-23 13:55:18 incorrect value:\n2012-10-23 11:55:18 incorrect value:\n2012-10-23 09:55:18 incorrect value:\n2012-10-23 07:55:18 incorrect value:\n2012-10-23 05:55:18 incorrect value:\n2012-10-23 03:55:17 incorrect value:\n2012-10-23 01:55:17 incorrect value:\n2012-10-22 23:55:17 incorrect value:\n2012-10-22 21:55:17 incorrect value:\n2012-10-22 19:55:17 incorrect value:\n2012-10-22 17:55:18 incorrect value:\n2012-10-22 15:55:17 incorrect value:\n2012-10-22 13:55:20 incorrect value:\n2012-10-22 11:55:17 incorrect value:\n2012-10-22 09:55:17 incorrect value:\n2012-10-22 07:55:17 incorrect value:\n2012-10-22 05:55:17 incorrect value:\n2012-10-22 03:55:17 incorrect value:\n2012-10-22 01:55:18 incorrect value:\n2012-10-21 23:55:17 incorrect value:\n2012-10-21 21:55:17 incorrect value:\n2012-10-21 19:55:17 incorrect value:\n2012-10-21 17:55:18 incorrect value:\n2012-10-21 15:55:17 incorrect value:\n2012-10-21 13:55:17 incorrect value:\n2012-10-21 11:55:17 incorrect value:\n2012-10-21 09:55:17 incorrect value:\n2012-10-21 07:55:17 incorrect value:\n2012-10-21 05:55:17 incorrect value:\n2012-10-21 03:55:17 incorrect value:\n2012-10-21 01:55:17 incorrect value:\n2012-10-20 23:55:16 incorrect value:\n2012-10-20 21:55:16 incorrect value:\n2012-10-20 19:55:16 incorrect value:\n2012-10-20 17:55:16 incorrect value:\n2012-10-20 15:55:16 incorrect value:\n2012-10-20 13:55:16 incorrect value:\n2012-10-20 11:55:16 incorrect value:\n2012-10-20 09:55:16 incorrect value:\n2012-10-20 07:55:17 incorrect value:\n2012-10-20 05:55:16 incorrect value:\n2012-10-20 03:55:17 incorrect value:\n2012-10-20 01:55:16 incorrect value:\n2012-10-19 23:55:16 incorrect value:\n2012-10-19 21:55:16 incorrect value:\n2012-10-19 19:55:16 incorrect value:\n2012-10-19 17:55:16 incorrect value:\n2012-10-19 15:55:16 incorrect value:\n2012-10-19 13:55:16 incorrect value:\n2012-10-19 11:55:16 incorrect value:\n2012-10-19 09:55:16 incorrect value:\n2012-10-19 07:55:16 incorrect value:\n2012-10-19 05:55:16 incorrect value:\n2012-10-19 03:55:16 incorrect value:\n2012-10-19 01:55:16 incorrect value:\n2012-10-18 23:55:16 incorrect value:\n2012-10-18 21:55:16 incorrect value:\n2012-10-18 19:55:16 incorrect value:\n2012-10-18 17:55:16 incorrect value:\n2012-10-18 15:55:17 incorrect value:\n2012-10-18 13:55:16 incorrect value:\n2012-10-18 11:55:16 incorrect value:\n2012-10-18 09:55:16 incorrect value:\n2012-10-18 07:55:16 incorrect value:\n2012-10-18 05:55:16 incorrect value:\n2012-10-18 03:55:16 incorrect value:\n2012-10-18 01:55:16 incorrect value:\n2012-10-17 23:55:16 incorrect value:\n2012-10-17 21:55:16 incorrect value:\n2012-10-17 19:55:16 incorrect value:\n2012-10-17 17:55:16 incorrect value:\n2012-10-17 15:55:16 incorrect value:\n2012-10-17 13:55:16 incorrect value:\n2012-10-17 11:55:16 incorrect value:\n2012-10-17 09:55:16 incorrect value:\n2012-10-17 07:55:16 incorrect value:\n2012-10-17 05:55:16 incorrect value:\n2012-10-17 03:55:17 incorrect value:\n2012-10-17 01:55:16 incorrect value:\n2012-10-16 23:55:16 incorrect value:\n2012-10-16 21:55:16 incorrect value:\n2012-10-16 19:55:16 incorrect value:\n2012-10-16 17:55:16 incorrect value:\n2012-10-16 15:55:16 incorrect value:\n2012-10-16 13:55:16 incorrect value:\n2012-10-16 11:55:17 incorrect value:\n2012-10-04 17:56:25 incorrect value:\n2012-10-04 15:56:24 incorrect value:\n2012-10-04 13:56:25 incorrect value:\n2012-10-04 11:56:24 incorrect value:\n2012-10-04 09:56:24 incorrect value:\n2012-10-04 07:56:24 incorrect value:\n2012-10-04 05:56:24 incorrect value:\n2012-10-04 03:56:23 incorrect value:\n2012-10-04 01:56:23 incorrect value:\n2012-10-03 23:56:23 incorrect value:\n2012-10-03 21:56:24 incorrect value:\n2012-10-03 19:56:23 incorrect value:\n2012-10-03 17:56:23 incorrect value:\n2012-10-03 15:56:23 incorrect value:\n2012-10-03 13:56:23 incorrect value:\n2012-10-03 11:56:23 incorrect value:\n2012-10-03 09:56:23 incorrect value:\n2012-10-03 07:56:23 incorrect value:\n2012-10-03 05:56:24 incorrect value:\n2012-10-03 03:56:23 incorrect value:\n2012-10-03 01:56:23 incorrect value:\n2012-10-02 23:56:23 incorrect value:\n2012-10-02 21:56:23 incorrect value:\n2012-10-02 19:56:23 incorrect value:\n2012-10-02 17:56:23 incorrect value:\n2012-09-25 11:47:25 incorrect value:\n2012-09-25 09:47:27 incorrect value:\n2012-09-24 16:34:30 incorrect value:\n2012-09-24 14:34:29 incorrect value:\n2012-09-24 12:34:29 incorrect value:\n2012-09-24 10:34:29 incorrect value:\n2012-09-24 08:34:29 incorrect value:\n2012-09-24 06:34:29 incorrect value:\n2012-09-24 04:34:29 incorrect value:\n2012-09-24 02:34:29 incorrect value:\n2012-09-24 00:34:29 incorrect value:\n2012-09-23 22:34:29 incorrect value:\n2012-09-23 20:34:29 incorrect value:\n2012-09-23 18:34:29 incorrect value:\n2012-09-23 16:34:30 incorrect value:\n2012-09-23 14:34:29 incorrect value:\n2012-09-23 12:34:29 incorrect value:\n2012-09-23 10:34:29 incorrect value:\n2012-09-23 08:34:29 incorrect value:\n2012-09-23 06:34:29 incorrect value:\n2012-09-23 04:34:29 incorrect value:\n2012-09-23 02:34:29 incorrect value:\n2012-09-23 00:34:29 incorrect value:\n2012-09-22 22:34:29 incorrect value:\n2012-09-22 20:34:29 incorrect value:\n2012-09-22 18:34:29 incorrect value:\n2012-09-22 16:34:29 incorrect value:\n2012-09-22 14:34:28 incorrect value:\n2012-09-22 12:34:28 incorrect value:\n2012-09-22 10:34:29 incorrect value:\n2012-09-22 08:34:28 incorrect value:\n2012-09-22 06:34:28 incorrect value:\n2012-09-22 04:34:28 incorrect value:\n2012-09-22 02:34:28 incorrect value:\n2012-09-22 00:34:28 incorrect value:\n2012-09-21 22:34:28 incorrect value:\n2012-09-21 20:34:28 incorrect value:\n2012-09-21 18:34:28 incorrect value:\n2012-09-21 16:34:28 incorrect value:\n2012-09-21 14:34:28 incorrect value:\n2012-09-21 12:34:28 incorrect value:\n2012-09-21 10:34:28 incorrect value:\n2012-09-21 08:34:28 incorrect value:\n2012-09-21 06:34:28 incorrect value:\n2012-09-21 04:34:28 incorrect value:\n2012-09-21 02:34:28 incorrect value:\n2012-09-21 00:34:28 incorrect value:\n2012-09-20 22:34:28 incorrect value:\n2012-09-20 20:34:28 incorrect value:\n2012-09-20 18:34:28 incorrect value:\n2012-09-18 15:23:17 incorrect value:\n2012-09-04 09:27:44 incorrect value:\n2012-08-31 15:32:01 incorrect value:\n2012-08-31 13:30:44 incorrect value:\n2012-08-31 11:30:43 incorrect value:\n2012-08-31 09:30:43 incorrect value:\n2012-08-31 07:30:43 incorrect value:\n2012-08-31 05:30:43 incorrect value:\n2012-08-31 03:30:43 incorrect value:\n2012-08-31 01:30:43 incorrect value:\n2012-08-30 23:30:43 incorrect value:\n2012-08-30 21:30:43 incorrect value:\n2012-08-30 19:30:43 incorrect value:\n2012-08-30 17:30:44 incorrect value:\n2012-08-30 15:30:43 incorrect value:\n2012-08-30 13:30:43 incorrect value:\n2012-08-30 11:30:43 incorrect value:\n2012-08-30 09:30:44 incorrect value:\n2012-08-29 17:51:18 incorrect value:\n2012-08-29 15:51:18 incorrect value:\n2012-08-29 13:51:18 incorrect value:\n2012-08-29 11:51:18 incorrect value:\n2012-08-29 09:51:18 incorrect value:\n2012-08-29 07:51:19 incorrect value:\n2012-08-29 05:51:18 incorrect value:\n2012-08-29 03:51:18 incorrect value:\n2012-08-29 01:51:18 incorrect value:\n2012-08-28 23:51:19 incorrect value:\n2012-08-28 21:51:18 incorrect value:\n2012-08-28 19:51:18 incorrect value:\n2012-08-28 17:51:17 incorrect value:\n2012-08-28 15:51:17 incorrect value:\n2012-08-28 13:51:17 incorrect value:\n2012-08-28 11:51:17 incorrect value:\n2012-08-28 09:51:17 incorrect value:\n2012-08-27 19:29:44 incorrect value:\n2012-08-27 17:29:44 incorrect value:\n2012-07-31 17:40:36 incorrect value:\n2012-07-13 06:31:11 incorrect value:\n2012-07-13 04:31:12 incorrect value:\n2012-07-13 02:31:11 incorrect value:\n2012-07-13 00:31:11 incorrect value:\n2012-07-12 22:31:11 incorrect value:\n2012-07-12 20:31:11 incorrect value:\n2012-07-12 18:31:11 incorrect value:\n2012-07-12 16:31:12 incorrect value:\n2012-07-12 14:31:10 incorrect value:\n2012-07-12 12:31:10 incorrect value:\n2012-07-12 10:31:10 incorrect value:\n2012-07-12 08:31:10 incorrect value:\n2012-07-12 06:31:10 incorrect value:\n2012-07-12 04:31:11 incorrect value:\n2012-07-12 02:31:10 incorrect value:\n2012-07-12 00:31:10 incorrect value:\n2012-07-11 22:31:10 incorrect value:\n2012-07-11 20:31:10 incorrect value:\n2012-07-11 18:31:10 incorrect value:\n2012-07-11 16:31:10 incorrect value:\n2012-07-11 14:31:10 incorrect value:\n2012-07-11 12:31:10 incorrect value:\n2012-07-11 10:31:10 incorrect value:\n2012-07-11 08:31:10 incorrect value:\n2012-07-11 06:31:10 incorrect value:\n2012-07-11 04:31:11 incorrect value:\n2012-07-11 02:31:10 incorrect value:\n2012-07-11 00:31:10 incorrect value:\n2012-07-10 22:31:10 incorrect value:\n2012-07-10 20:31:10 incorrect value:\n2012-07-10 18:31:10 incorrect value:\n2012-07-10 16:31:10 incorrect value:\n2012-07-10 14:31:10 incorrect value:\n2012-07-10 12:31:10 incorrect value:\n2012-07-10 00:31:10 new value: \r\n<b>Сегодня:</b><br />днем: +25&deg;...+27&deg;, переменная облачность, небольшой дождь, ночью: +18&deg;...+20&deg;,\r\nясно, ветер: З — 4-6 м/с, давление: 758 мм.рт.ст, влажность: 50%<br /><br />\r\n<b>Завтра:</b><br />днем: +24&deg;...+26&deg;, переменная облачность, ночью: +16&deg;...+18&deg;,\r\nясно, ветер: З — 2-4 м/с, давление: 759 мм.рт.ст, влажность: 50%<br /><br />\r\n\n2012-07-09 13:17:12 new value: \r\n<b>Сегодня:</b><br />днем: +27&deg;...+29&deg;, переменная облачность, небольшой дождь, ночью: +19&deg;...+21&deg;,\r\nясно, ветер: ЮЗ — 4-6 м/с, давление: 756 мм.рт.ст, влажность: 60%<br /><br />\r\n<b>Завтра:</b><br />днем: +25&deg;...+27&deg;, переменная облачность, небольшой дождь, ночью: +18&deg;...+20&deg;,\r\nясно, ветер: З — 4-6 м/с, давление: 758 мм.рт.ст, влажность: 50%<br /><br />\r\n\n2012-07-09 01:17:11 new value: \r\n<b>Сегодня:</b><br />днем: +28&deg;...+30&deg;, дождь, гроза, ночью: +21&deg;...+23&deg;,\r\nпеременная облачность, ветер: Ю — 2-4 м/с, давление: 755 мм.рт.ст, влажность: 60%<br /><br />\r\n<b>Завтра:</b><br />днем: +25&deg;...+27&deg;, дождь, ночью: +21&deg;...+23&deg;,\r\nдождь, ветер: CЗ — 5-7 м/с, давление: 757 мм.рт.ст, влажность: 55%<br /><br />\r\n\n2012-07-08 11:17:10 new value: \r\n<b>Сегодня:</b><br />днем: +30&deg;...+32&deg;, дождь, гроза, ночью: +19&deg;...+21&deg;,\r\nясно, ветер: Ю — 2-4 м/с, давление: 758 мм.рт.ст, влажность: 45%<br /><br />\r\n<b>Завтра:</b><br />днем: +28&deg;...+30&deg;, дождь, гроза, ночью: +21&deg;...+23&deg;,\r\nпеременная облачность, ветер: Ю — 2-4 м/с, давление: 755 мм.рт.ст, влажность: 60%<br /><br />\r\n\n2012-07-08 01:17:09 new value: \r\n<b>Сегодня:</b><br />днем: +28&deg;...+30&deg;, переменная облачность, небольшой дождь, ночью: +20&deg;...+22&deg;,\r\nпеременная облачность, ветер: Ю — 2-4 м/с, давление: 758 мм.рт.ст, влажность: 55%<br /><br />\r\n<b>Завтра:</b><br />днем: +28&deg;...+30&deg;, дождь, ночью: +21&deg;...+23&deg;,\r\nясно, ветер: З — 3-5 м/с, давление: 755 мм.рт.ст, влажность: 55%<br /><br />\r\n\n2012-07-07 13:17:09 new value: \r\n<b>Сегодня:</b><br />днем: +27&deg;...+29&deg;, дождь, гроза, ночью: +19&deg;...+21&deg;,\r\nпеременная облачность, ветер: В — 1-3 м/с, давление: 761 мм.рт.ст, влажность: 55%<br /><br />\r\n<b>Завтра:</b><br />днем: +28&deg;...+30&deg;, переменная облачность, небольшой дождь, ночью: +20&deg;...+22&deg;,\r\nпеременная облачность, ветер: Ю — 2-4 м/с, давление: 758 мм.рт.ст, влажность: 55%<br /><br />\r\n\n2012-07-07 01:17:08 new value: \r\n<b>Сегодня:</b><br />днем: +27&deg;...+29&deg;, переменная облачность, ночью: +19&deg;...+21&deg;,\r\nясно, ветер: CВ — 2-4 м/с, давление: 761 мм.рт.ст, влажность: 55%<br /><br />\r\n<b>Завтра:</b><br />днем: +28&deg;...+30&deg;, переменная облачность, небольшой дождь, ночью: +19&deg;...+21&deg;,\r\nясно, ветер: ЮВ — 2-4 м/с, давление: 758 мм.рт.ст, влажность: 55%<br /><br />\r\n\n2012-07-06 13:17:07 new value: \r\n<b>Сегодня:</b><br />днем: +27&deg;...+29&deg;, переменная облачность, ночью: +18&deg;...+20&deg;,\r\nпеременная облачность, ветер: В — 2-4 м/с, давление: 762 мм.рт.ст, влажность: 55%<br /><br />\r\n<b>Завтра:</b><br />днем: +27&deg;...+29&deg;, переменная облачность, ночью: +19&deg;...+21&deg;,\r\nясно, ветер: CВ — 2-4 м/с, давление: 761 мм.рт.ст, влажность: 55%<br /><br />\r\n\n2012-07-06 01:17:07 new value: \r\n<b>Сегодня:</b><br />днем: +27&deg;...+29&deg;, переменная облачность, ночью: +19&deg;...+21&deg;,\r\nясно, ветер: В — 2-4 м/с, давление: 762 мм.рт.ст, влажность: 55%<br /><br />\r\n<b>Завтра:</b><br />днем: +26&deg;...+28&deg;, переменная облачность, небольшой дождь, ночью: +19&deg;...+21&deg;,\r\nясно, ветер: В — 3-5 м/с, давление: 760 мм.рт.ст, влажность: 55%<br /><br />\r\n\n2012-07-05 13:17:06 new value: \r\n<b>Сегодня:</b><br />днем: +27&deg;...+29&deg;, ясно, ночью: +17&deg;...+19&deg;,\r\nясно, ветер: В — 3-5 м/с, давление: 763 мм.рт.ст, влажность: 50%<br /><br />\r\n<b>Завтра:</b><br />днем: +27&deg;...+29&deg;, переменная облачность, ночью: +19&deg;...+21&deg;,\r\nясно, ветер: В — 2-4 м/с, давление: 762 мм.рт.ст, влажность: 55%<br /><br />\r\n\n2012-07-05 11:17:05 new value: \r\n<b>Сегодня:</b><br />днем: +26&deg;...+28&deg;, облачно, без существенных осадков, ночью: +18&deg;...+20&deg;,\r\nясно, ветер: ЮВ — 3-5 м/с, давление: 763 мм.рт.ст, влажность: 55%<br /><br />\r\n<b>Завтра:</b><br />днем: +27&deg;...+29&deg;, переменная облачность, ночью: +19&deg;...+21&deg;,\r\nясно, ветер: В — 3-5 м/с, давление: 761 мм.рт.ст, влажность: 55%<br /><br />\r\n\n2012-06-25 14:29:27 new value: \r\n<b>Сегодня:</b><br />днем: +17&deg;...+19&deg;, дождь, гроза, ночью: +13&deg;...+15&deg;,\r\nпеременная облачность, ветер: ЮЗ — 6-8 м/с, давление: 757 мм.рт.ст, влажность: 85%<br /><br />\r\n<b>Завтра:</b><br />днем: +17&deg;...+19&deg;, переменная облачность, ночью: +13&deg;...+15&deg;,\r\nпеременная облачность, ветер: ЮЗ — 7-9 м/с, давление: 754 мм.рт.ст, влажность: 60%<br /><br />\r\n\n2012-06-25 01:39:24 new value: \r\n<b>Сегодня:</b><br />днем: +16&deg;...+18&deg;, дождь, гроза, ночью: +14&deg;...+16&deg;,\r\nпеременная облачность, ветер: ЮЗ — 6-8 м/с, давление: 756 мм.рт.ст, влажность: 95%<br /><br />\r\n<b>Завтра:</b><br />днем: +16&deg;...+18&deg;, дождь, гроза, ночью: +12&deg;...+14&deg;,\r\nпеременная облачность, ветер: ЮЗ — 8-10 м/с, давление: 754 мм.рт.ст, влажность: 60%<br /><br />\r\n\n2012-06-24 13:39:23 new value: \r\n<b>Сегодня:</b><br />днем: +20&deg;...+22&deg;, дождь, гроза, ночью: +11&deg;...+13&deg;,\r\nясно, ветер: З — 5-7 м/с, давление: 764 мм.рт.ст, влажность: 55%<br /><br />\r\n<b>Завтра:</b><br />днем: +16&deg;...+18&deg;, дождь, гроза, ночью: +14&deg;...+16&deg;,\r\nпеременная облачность, ветер: ЮЗ — 6-8 м/с, давление: 756 мм.рт.ст, влажность: 95%<br /><br />\r\n\n2012-06-24 01:39:23 new value: \r\n<b>Сегодня:</b><br />днем: +19&deg;...+21&deg;, переменная облачность, ночью: +12&deg;...+14&deg;,\r\nясно, ветер: З — 5-7 м/с, давление: 764 мм.рт.ст, влажность: 55%<br /><br />\r\n<b>Завтра:</b><br />днем: +16&deg;...+18&deg;, дождь, гроза, ночью: +14&deg;...+16&deg;,\r\nоблачно, ветер: Ю — 7-9 м/с, давление: 754 мм.рт.ст, влажность: 95%<br /><br />\r\n\n2012-06-23 13:39:23 new value: \r\n<b>Сегодня:</b><br />днем: +18&deg;...+20&deg;, переменная облачность, ночью: +17&deg;...+19&deg;,\r\nоблачно, без существенных осадков, ветер: З — 8-10 м/с, давление: 762 мм.рт.ст, влажность: 70%<br /><br />\r\n<b>Завтра:</b><br />днем: +19&deg;...+21&deg;, переменная облачность, ночью: +12&deg;...+14&deg;,\r\nясно, ветер: З — 5-7 м/с, давление: 764 мм.рт.ст, влажность: 55%<br /><br />\r\n\n2012-06-23 07:39:22 new value: \r\n<b>Сегодня:</b><br />днем: +19&deg;...+21&deg;, переменная облачность, ночью: +17&deg;...+19&deg;,\r\nпеременная облачность, небольшой дождь, ветер: З — 7-9 м/с, давление: 762 мм.рт.ст, влажность: 70%<br /><br />\r\n<b>Завтра:</b><br />днем: +20&deg;...+22&deg;, переменная облачность, ночью: +13&deg;...+15&deg;,\r\nпеременная облачность, ветер: З — 5-7 м/с, давление: 764 мм.рт.ст, влажность: 50%<br /><br />\r\n\n2012-06-22 14:28:40 new value: \r\n<b>Сегодня:</b><br />днем: +21&deg;...+23&deg;, дождь, гроза, ночью: +13&deg;...+15&deg;,\r\nдождь, гроза, ветер: ЮВ — 4-6 м/с, давление: 759 мм.рт.ст, влажность: 80%<br /><br />\r\n<b>Завтра:</b><br />днем: +19&deg;...+21&deg;, переменная облачность, ночью: +17&deg;...+19&deg;,\r\nпеременная облачность, небольшой дождь, ветер: З — 7-9 м/с, давление: 762 мм.рт.ст, влажность: 70%<br /><br />\r\n\n2012-06-22 02:28:40 new value: \r\n<b>Сегодня:</b><br />днем: +21&deg;...+23&deg;, дождь, гроза, ночью: +15&deg;...+17&deg;,\r\nдождь, гроза, ветер: ЮВ — 5-7 м/с, давление: 758 мм.рт.ст, влажность: 80%<br /><br />\r\n<b>Завтра:</b><br />днем: +18&deg;...+20&deg;, переменная облачность, ночью: +17&deg;...+19&deg;,\r\nпеременная облачность, ветер: З — 7-9 м/с, давление: 761 мм.рт.ст, влажность: 70%<br /><br />\r\n\n2012-06-21 14:28:40 new value: \r\n<b>Сегодня:</b><br />днем: +16&deg;...+18&deg;, дождь, гроза, ночью: +14&deg;...+16&deg;,\r\nоблачно, ветер: В — 4-6 м/с, давление: 762 мм.рт.ст, влажность: 90%<br /><br />\r\n<b>Завтра:</b><br />днем: +21&deg;...+23&deg;, дождь, гроза, ночью: +15&deg;...+17&deg;,\r\nдождь, гроза, ветер: ЮВ — 5-7 м/с, давление: 758 мм.рт.ст, влажность: 80%<br /><br />\r\n\n2012-06-21 02:28:40 new value: \r\n<b>Сегодня:</b><br />днем: +17&deg;...+19&deg;, дождь, гроза, ночью: +15&deg;...+17&deg;,\r\nоблачно, ветер: В — 4-6 м/с, давление: 762 мм.рт.ст, влажность: 80%<br /><br />\r\n<b>Завтра:</b><br />днем: +19&deg;...+21&deg;, дождь, гроза, ночью: +13&deg;...+15&deg;,\r\nдождь, гроза, ветер: В — 5-7 м/с, давление: 757 мм.рт.ст, влажность: 80%<br /><br />\r\n\n2012-06-20 17:28:38 incorrect value:\n2012-06-20 14:28:38 new value: \r\n<b>Сегодня:</b><br />днем: +19&deg;...+21&deg;, переменная облачность, ночью: +12&deg;...+14&deg;,\r\nпеременная облачность, ветер: C — 2-4 м/с, давление: 764 мм.рт.ст, влажность: 60%<br /><br />\r\n<b>Завтра:</b><br />днем: +17&deg;...+19&deg;, дождь, гроза, ночью: +15&deg;...+17&deg;,\r\nоблачно, ветер: В — 4-6 м/с, давление: 762 мм.рт.ст, влажность: 80%<br /><br />\r\n\n2012-06-20 02:28:37 new value: \r\n<b>Сегодня:</b><br />днем: +20&deg;...+22&deg;, переменная облачность, ночью: +14&deg;...+16&deg;,\r\nпеременная облачность, ветер: C — 1-3 м/с, давление: 764 мм.рт.ст, влажность: 55%<br /><br />\r\n<b>Завтра:</b><br />днем: +18&deg;...+20&deg;, дождь, гроза, ночью: +15&deg;...+17&deg;,\r\nоблачно, без существенных осадков, ветер: В — 3-5 м/с, давление: 761 мм.рт.ст, влажность: 70%<br /><br />\r\n\n2012-06-19 14:28:36 new value: \r\n<b>Сегодня:</b><br />днем: +26&deg;...+28&deg;, переменная облачность, небольшой дождь, ночью: +15&deg;...+17&deg;,\r\nпеременная облачность, ветер: З — 5-7 м/с, давление: 761 мм.рт.ст, влажность: 65%<br /><br />\r\n<b>Завтра:</b><br />днем: +20&deg;...+22&deg;, переменная облачность, ночью: +14&deg;...+16&deg;,\r\nпеременная облачность, ветер: C — 1-3 м/с, давление: 764 мм.рт.ст, влажность: 55%<br /><br />\r\n\n2012-06-19 02:28:35 new value: \r\n<b>Сегодня:</b><br />днем: +26&deg;...+28&deg;, переменная облачность, небольшой дождь, ночью: +15&deg;...+17&deg;,\r\nясно, ветер: ЮЗ — 5-7 м/с, давление: 761 мм.рт.ст, влажность: 65%<br /><br />\r\n<b>Завтра:</b><br />днем: +21&deg;...+23&deg;, переменная облачность, ночью: +15&deg;...+17&deg;,\r\nясно, ветер: C — 2-4 м/с, давление: 763 мм.рт.ст, влажность: 50%<br /><br />\r\n\n2012-06-18 14:28:34 new value: \r\n<b>Сегодня:</b><br />днем: +21&deg;...+23&deg;, переменная облачность, ночью: +12&deg;...+14&deg;,\r\nясно, ветер: З — 3-5 м/с, давление: 767 мм.рт.ст, влажность: 50%<br /><br />\r\n<b>Завтра:</b><br />днем: +26&deg;...+28&deg;, переменная облачность, небольшой дождь, ночью: +15&deg;...+17&deg;,\r\nясно, ветер: ЮЗ — 5-7 м/с, давление: 761 мм.рт.ст, влажность: 65%<br /><br />\r\n\n2012-06-18 02:28:33 new value: \r\n<b>Сегодня:</b><br />днем: +21&deg;...+23&deg;, переменная облачность, ночью: +13&deg;...+15&deg;,\r\nясно, ветер: З — 3-5 м/с, давление: 767 мм.рт.ст, влажность: 50%<br /><br />\r\n<b>Завтра:</b><br />днем: +26&deg;...+28&deg;, дождь, гроза, ночью: +15&deg;...+17&deg;,\r\nясно, ветер: ЮЗ — 5-7 м/с, давление: 761 мм.рт.ст, влажность: 65%<br /><br />\r\n\n2012-06-17 20:28:32 incorrect value:\n2012-06-17 14:28:32 new value: \r\n<b>Сегодня:</b><br />днем: +25&deg;...+27&deg;, переменная облачность, ночью: +12&deg;...+14&deg;,\r\nпеременная облачность, ветер: ЮЗ — 6-8 м/с, давление: 763 мм.рт.ст, влажность: 65%<br /><br />\r\n<b>Завтра:</b><br />днем: +21&deg;...+23&deg;, переменная облачность, ночью: +13&deg;...+15&deg;,\r\nясно, ветер: З — 3-5 м/с, давление: 767 мм.рт.ст, влажность: 50%<br /><br />\r\n\n2012-06-17 08:28:32 incorrect value:\n2012-06-17 02:28:32 new value: \r\n<b>Сегодня:</b><br />днем: +25&deg;...+27&deg;, дождь, гроза, ночью: +12&deg;...+14&deg;,\r\nпеременная облачность, ветер: ЮЗ — 7-9 м/с, давление: 763 мм.рт.ст, влажность: 60%<br /><br />\r\n<b>Завтра:</b><br />днем: +22&deg;...+24&deg;, переменная облачность, ночью: +14&deg;...+16&deg;,\r\nясно, ветер: З — 3-5 м/с, давление: 767 мм.рт.ст, влажность: 50%<br /><br />\r\n\n2012-06-16 14:28:32 new value: \r\n<b>Сегодня:</b><br />днем: +20&deg;...+22&deg;, переменная облачность, ночью: +13&deg;...+15&deg;,\r\nоблачно, без существенных осадков, ветер: CЗ — 6-8 м/с, давление: 764 мм.рт.ст, влажность: 55%<br /><br />\r\n<b>Завтра:</b><br />днем: +25&deg;...+27&deg;, дождь, гроза, ночью: +12&deg;...+14&deg;,\r\nпеременная облачность, ветер: ЮЗ — 7-9 м/с, давление: 763 мм.рт.ст, влажность: 60%<br /><br />\r\n\n2012-06-16 02:28:32 new value: \r\n<b>Сегодня:</b><br />днем: +21&deg;...+23&deg;, переменная облачность, ночью: +14&deg;...+16&deg;,\r\nпеременная облачность, ветер: CЗ — 6-8 м/с, давление: 764 мм.рт.ст, влажность: 55%<br /><br />\r\n<b>Завтра:</b><br />днем: +26&deg;...+28&deg;, переменная облачность, ночью: +12&deg;...+14&deg;,\r\nясно, ветер: ЮЗ — 7-9 м/с, давление: 763 мм.рт.ст, влажность: 55%<br /><br />\r\n\n2012-06-15 20:28:35 incorrect value:\n2012-06-15 14:28:32 new value: \r\n<b>Сегодня:</b><br />днем: +16&deg;...+18&deg;, дождь, гроза, ночью: +13&deg;...+15&deg;,\r\nдождь, гроза, ветер: CЗ — 5-7 м/с, давление: 760 мм.рт.ст, влажность: 85%<br /><br />\r\n<b>Завтра:</b><br />днем: +21&deg;...+23&deg;, переменная облачность, ночью: +14&deg;...+16&deg;,\r\nпеременная облачность, ветер: CЗ — 6-8 м/с, давление: 764 мм.рт.ст, влажность: 55%<br /><br />\r\n\n2012-06-15 08:28:32 incorrect value:\n2012-06-15 02:28:32 new value: \r\n<b>Сегодня:</b><br />днем: +17&deg;...+19&deg;, дождь, гроза, ночью: +14&deg;...+16&deg;,\r\nоблачно, без существенных осадков, ветер: C — 3-5 м/с, давление: 759 мм.рт.ст, влажность: 80%<br /><br />\r\n<b>Завтра:</b><br />днем: +21&deg;...+23&deg;, переменная облачность, ночью: +13&deg;...+15&deg;,\r\nпеременная облачность, ветер: CЗ — 6-8 м/с, давление: 764 мм.рт.ст, влажность: 60%<br /><br />\r\n\n2012-06-14 17:28:32 new value: \r\n<b>Сегодня:</b><br />днем: +18&deg;...+20&deg;, дождь, гроза, ночью: +10&deg;...+12&deg;,\r\nпеременная облачность, ветер: CВ — 2-4 м/с, давление: 761 мм.рт.ст, влажность: 70%<br /><br />\r\n<b>Завтра:</b><br />днем: +17&deg;...+19&deg;, дождь, гроза, ночью: +14&deg;...+16&deg;,\r\nоблачно, без существенных осадков, ветер: C — 3-5 м/с, давление: 759 мм.рт.ст, влажность: 80%<br /><br />\r\n\n2012-06-01 18:39:32 new value: \r\n<b>Сегодня:</b><br />днем: +16&deg;...+18&deg;, облачно, ночью: +8&deg;...+10&deg;,\r\nоблачно, ветер: Ю — 6-8 м/с, давление: 755 мм.рт.ст, влажность: 60%<br /><br />\r\n<b>Завтра:</b><br />днем: +11&deg;...+13&deg;, облачно, ночью: +7&deg;...+9&deg;,\r\nдождь, ветер: ЮЗ — 8-10 м/с, давление: 753 мм.рт.ст, влажность: 50%<br /><br />\r\n\n2012-05-29 16:51:35 new value: \r\n<b>Сегодня:</b><br />днем: +22&deg;...+24&deg;, переменная облачность, ночью: +10&deg;...+12&deg;,\r\nпеременная облачность, ветер: ЮЗ — 5-7 м/с, давление: 755 мм.рт.ст, влажность: 45%<br /><br />\r\n<b>Завтра:</b><br />днем: +12&deg;...+14&deg;, облачно, ночью: +14&deg;...+16&deg;,\r\nдождь, гроза, ветер: CЗ — 6-8 м/с, давление: 758 мм.рт.ст, влажность: 60%<br /><br />\r\n\n2012-05-09 02:14:01 incorrect value:\n2012-05-08 23:14:01 incorrect value:\n2012-05-08 20:13:59 new value: \r\n<b>Сегодня:</b><br />днем: +10&deg;...+12&deg;, облачно, без существенных осадков, ночью: +7&deg;...+9&deg;,\r\nоблачно, ветер: CВ — 4-6 м/с, давление: 766 мм.рт.ст, влажность: 75%<br /><br />\r\n<b>Завтра:</b><br />днем: +16&deg;...+18&deg;, переменная облачность, ночью: +6&deg;...+8&deg;,\r\nпеременная облачность, ветер: CВ — 1-3 м/с, давление: 767 мм.рт.ст, влажность: 60%<br /><br />\r\n\n2012-05-08 17:14:00 incorrect value:\n2012-05-02 10:58:10 new value: \r\n<b>Сегодня:</b><br />днем: +16&deg;...+18&deg;, ясно, ночью: +7&deg;...+9&deg;,\r\nясно, ветер: ЮЗ — 1-3 м/с, давление: 763 мм.рт.ст, влажность: 45%<br /><br />\r\n<b>Завтра:</b><br />днем: +17&deg;...+19&deg;, ясно, ночью: +9&deg;...+11&deg;,\r\nясно, ветер: C — 1-3 м/с, давление: 758 мм.рт.ст, влажность: 40%<br /><br />\r\n\n2012-05-01 16:19:11 new value: \r\n<b>Сегодня:</b><br />днем: +20&deg;...+22&deg;, ясно, ночью: +11&deg;...+13&deg;,\r\nясно, ветер: CЗ — 6-8 м/с, давление: 763 мм.рт.ст, влажность: 50%<br /><br />\r\n<b>Завтра:</b><br />днем: +16&deg;...+18&deg;, ясно, ночью: +7&deg;...+9&deg;,\r\nясно, ветер: ЮЗ — 1-3 м/с, давление: 763 мм.рт.ст, влажность: 45%<br /><br />\r\n\n2012-05-01 00:22:24 incorrect value:\n2012-05-01 00:20:58 incorrect value:\n2012-05-01 00:12:03 incorrect value:\n2012-04-27 01:33:23 new value: \r\n<b>Сегодня:</b><br />днем: +19&deg;...+21&deg;, переменная облачность, ночью: +9&deg;...+11&deg;,\r\nпеременная облачность, ветер: ЮЗ — 5-7 м/с, давление: 767 мм.рт.ст, влажность: 55%<br /><br />\r\n<b>Завтра:</b><br />днем: +22&deg;...+24&deg;, переменная облачность, ночью: +12&deg;...+14&deg;,\r\nясно, ветер: З — 3-5 м/с, давление: 768 мм.рт.ст, влажность: 55%<br /><br />\r\n\n2012-04-26 19:33:23 new value: \r\n<b>Сегодня:</b><br />днем: +16&deg;...+18&deg;, ясно, ночью: +10&deg;...+12&deg;,\r\nдождь, ветер: З — 3-5 м/с, давление: 764 мм.рт.ст, влажность: 50%<br /><br />\r\n<b>Завтра:</b><br />днем: +19&deg;...+21&deg;, переменная облачность, ночью: +9&deg;...+11&deg;,\r\nпеременная облачность, ветер: ЮЗ — 5-7 м/с, давление: 767 мм.рт.ст, влажность: 55%<br /><br />\r\n\n2012-04-03 10:57:55 new value: \r\n<b>Сегодня:</b><br />днем: +3&deg;...+5&deg;, облачно, ночью: -2&deg;...-4&deg;,\r\nпеременная облачность, ветер: З — 5-7 м/с, давление: 758 мм.рт.ст, влажность: 45%<br /><br />\r\n<b>Завтра:</b><br />днем: +5&deg;...+7&deg;, облачно, ночью: +0&deg;...-2&deg;,\r\nпеременная облачность, ветер: ЮВ — 6-8 м/с, давление: 759 мм.рт.ст, влажность: 80%<br /><br />\r\n\n2012-04-02 15:09:28 new value: \r\n<b>Сегодня:</b><br />днем: +0&deg;...+2&deg;, снег, ночью: -1&deg;...-3&deg;,\r\nоблачно, ветер: З — 12-14 м/с, давление: 745 мм.рт.ст, влажность: 65%<br /><br />\r\n<b>Завтра:</b><br />днем: +3&deg;...+5&deg;, облачно, ночью: -2&deg;...-4&deg;,\r\nпеременная облачность, ветер: З — 5-7 м/с, давление: 758 мм.рт.ст, влажность: 45%<br /><br />\r\n\n2012-04-02 12:09:28 new value: \r\n<b>Сегодня:</b><br />днем: +0&deg;...+2&deg;, снег, ночью: -1&deg;...-3&deg;,\r\nоблачно, ветер: З — 10-12 м/с, давление: 745 мм.рт.ст, влажность: 65%<br /><br />\r\n<b>Завтра:</b><br />днем: +3&deg;...+5&deg;, облачно, ночью: -1&deg;...-3&deg;,\r\nпеременная облачность, ветер: З — 7-9 м/с, давление: 758 мм.рт.ст, влажность: 50%<br /><br />\r\n\n2012-04-01 14:15:21 new value: \r\n<b>Сегодня:</b><br />днем: +0&deg;...+2&deg;, облачно, небольшой снег, ночью: +0&deg;,\r\nоблачно, ветер: CЗ — 7-9 м/с, давление: 750 мм.рт.ст, влажность: 60%<br /><br />\r\n<b>Завтра:</b><br />днем: +0&deg;...+2&deg;, снег, ночью: -1&deg;...-3&deg;,\r\nоблачно, ветер: З — 10-12 м/с, давление: 745 мм.рт.ст, влажность: 65%<br /><br />\r\n\n2012-03-22 00:47:41 new value: \r\n<b>Сегодня:</b><br />днем: +8&deg;...+10&deg;, облачно, ночью: +3&deg;...+5&deg;,\r\nоблачно, ветер: CЗ — 5-7 м/с, давление: 770 мм.рт.ст, влажность: 75%<br /><br />\r\n<b>Завтра:</b><br />днем: +8&deg;...+10&deg;, облачно, ночью: +5&deg;...+7&deg;,\r\nоблачно, ветер: CЗ — 7-9 м/с, давление: 764 мм.рт.ст, влажность: 80%<br /><br />\r\n\n2012-03-21 22:46:52 new value: \r\n<b>Сегодня:</b><br />днем: +6&deg;...+8&deg;, облачно, ночью: +3&deg;...+5&deg;,\r\nоблачно, без существенных осадков, ветер: CЗ — 6-8 м/с, давление: 767 мм.рт.ст, влажность: 75%<br /><br />\r\n<b>Завтра:</b><br />днем: +8&deg;...+10&deg;, облачно, ночью: +3&deg;...+5&deg;,\r\nоблачно, ветер: CЗ — 5-7 м/с, давление: 770 мм.рт.ст, влажность: 75%<br /><br />\r\n\n2012-03-21 16:16:42 incorrect value:\n2012-03-16 00:09:12 new value: \r\n<b>Сегодня:</b><br />днем: +2&deg;...+4&deg;, облачно, ночью: +0&deg;...-2&deg;,\r\nоблачно, ветер: З — 3-5 м/с, давление: 764 мм.рт.ст, влажность: 90%<br /><br />\r\n<b>Завтра:</b><br />днем: +7&deg;...+9&deg;, облачно, без существенных осадков, ночью: +1&deg;...+3&deg;,\r\nоблачно, ветер: З — 5-7 м/с, давление: 762 мм.рт.ст, влажность: 90%<br /><br />\r\n\n2012-03-15 18:09:08 new value: \r\n<b>Сегодня:</b><br />днем: +2&deg;...+4&deg;, переменная облачность, ночью: -1&deg;...-3&deg;,\r\nпеременная облачность, ветер: C — 7-9 м/с, давление: 767 мм.рт.ст, влажность: 45%<br /><br />\r\n<b>Завтра:</b><br />днем: +2&deg;...+4&deg;, облачно, ночью: +0&deg;...-2&deg;,\r\nоблачно, ветер: З — 3-5 м/с, давление: 764 мм.рт.ст, влажность: 90%<br /><br />\r\n\n2012-03-15 01:40:29 new value: \r\n<b>Сегодня:</b><br />днем: +1&deg;...+3&deg;, облачно, ночью: -1&deg;...-3&deg;,\r\nоблачно, ветер: C — 7-9 м/с, давление: 766 мм.рт.ст, влажность: 55%<br /><br />\r\n<b>Завтра:</b><br />днем: +2&deg;...+4&deg;, облачно, ночью: -1&deg;...-3&deg;,\r\nпеременная облачность, ветер: ЮЗ — 3-5 м/с, давление: 766 мм.рт.ст, влажность: 85%<br /><br />\r\n\n2012-03-14 13:40:29 new value: \r\n<b>Сегодня:</b><br />днем: +2&deg;...+4&deg;, переменная облачность, ночью: +0&deg;...-2&deg;,\r\nпеременная облачность, ветер: CЗ — 9-11 м/с, давление: 761 мм.рт.ст, влажность: 50%<br /><br />\r\n<b>Завтра:</b><br />днем: +1&deg;...+3&deg;, облачно, ночью: -1&deg;...-3&deg;,\r\nоблачно, ветер: C — 7-9 м/с, давление: 766 мм.рт.ст, влажность: 55%<br /><br />\r\n\n2012-03-12 19:03:41 new value: \r\n<b>Сегодня:</b><br />днем: +3&deg;...+5&deg;, облачно, ночью: +0&deg;...+2&deg;,\r\nоблачно, ветер: З — 6-8 м/с, давление: 758 мм.рт.ст, влажность: 70%<br /><br />\r\n<b>Завтра:</b><br />днем: +9&deg;...+11&deg;, переменная облачность, ночью: +1&deg;...+3&deg;,\r\nясно, ветер: З — 10-12 м/с, давление: 757 мм.рт.ст, влажность: 65%<br /><br />\r\n\n2012-03-09 10:11:01 new value: \r\n<b>Сегодня:</b><br />днем: +0&deg;...-2&deg;, облачно, ночью: -3&deg;...-5&deg;,\r\nоблачно, ветер: ЮЗ — 2-4 м/с, давление: 775 мм.рт.ст, влажность: 55%<br /><br />\r\n<b>Завтра:</b><br />днем: +0&deg;...+2&deg;, облачно, небольшой снег, ночью: -1&deg;...-3&deg;,\r\nоблачно, ветер: ЮЗ — 6-8 м/с, давление: 770 мм.рт.ст, влажность: 95%<br /><br />\r\n\n2012-03-08 20:44:40 new value: \r\n<b>Сегодня:</b><br />днем: -2&deg;...-4&deg;, облачно, ночью: -7&deg;...-9&deg;,\r\nоблачно, ветер: ЮВ — 2-4 м/с, давление: 770 мм.рт.ст, влажность: 65%<br /><br />\r\n<b>Завтра:</b><br />днем: +0&deg;...-2&deg;, облачно, ночью: -3&deg;...-5&deg;,\r\nоблачно, ветер: ЮЗ — 2-4 м/с, давление: 775 мм.рт.ст, влажность: 55%<br /><br />\r\n\n2012-03-07 14:51:38 new value: \r\n<b>Сегодня:</b><br />днем: -3&deg;...-5&deg;, облачно, ночью: -6&deg;...-8&deg;,\r\nоблачно, ветер: C — 2-4 м/с, давление: 771 мм.рт.ст, влажность: 65%<br /><br />\r\n<b>Завтра:</b><br />днем: -1&deg;...-3&deg;, облачно, ночью: -3&deg;...-5&deg;,\r\nоблачно, ветер: Ю — 2-4 м/с, давление: 769 мм.рт.ст, влажность: 60%<br /><br />\r\n\n2012-03-07 10:51:32 new value: \r\n<b>Сегодня:</b><br />днем: -2&deg;...-4&deg;, облачно, ночью: -4&deg;...-6&deg;,\r\nоблачно, ветер: C — 2-4 м/с, давление: 771 мм.рт.ст, влажность: 60%<br /><br />\r\n<b>Завтра:</b><br />днем: -1&deg;...-3&deg;, облачно, небольшой снег, ночью: -3&deg;...-5&deg;,\r\nоблачно, ветер: Ю — 2-4 м/с, давление: 769 мм.рт.ст, влажность: 60%<br /><br />\r\n\n2012-03-06 10:25:26 new value: \r\n<b>Сегодня:</b><br />днем: -2&deg;...-4&deg;, облачно, ночью: -3&deg;...-5&deg;,\r\nоблачно, ветер: C — 5-7 м/с, давление: 770 мм.рт.ст, влажность: 55%<br /><br />\r\n<b>Завтра:</b><br />днем: -2&deg;...-4&deg;, облачно, ночью: -5&deg;...-7&deg;,\r\nоблачно, ветер: C — 2-4 м/с, давление: 772 мм.рт.ст, влажность: 55%<br /><br />\r\n\n2012-03-05 14:46:40 new value: \r\n<b>Сегодня:</b><br />днем: -1&deg;...-3&deg;, облачно, ночью: -2&deg;...-4&deg;,\r\nоблачно, ветер: C — 4-6 м/с, давление: 767 мм.рт.ст, влажность: 65%<br /><br />\r\n<b>Завтра:</b><br />днем: -2&deg;...-4&deg;, облачно, ночью: -3&deg;...-5&deg;,\r\nоблачно, ветер: C — 5-7 м/с, давление: 770 мм.рт.ст, влажность: 55%<br /><br />\r\n\n2012-03-05 10:46:40 new value: \r\n<b>Сегодня:</b><br />днем: -1&deg;...-3&deg;, облачно, ночью: -2&deg;...-4&deg;,\r\nоблачно, ветер: C — 4-6 м/с, давление: 765 мм.рт.ст, влажность: 60%<br /><br />\r\n<b>Завтра:</b><br />днем: -2&deg;...-4&deg;, облачно, ночью: -3&deg;...-5&deg;,\r\nоблачно, ветер: C — 5-7 м/с, давление: 769 мм.рт.ст, влажность: 55%<br /><br />\r\n\n2012-03-02 14:00:42 new value: \r\n<b>Сегодня:</b><br />днем: +3&deg;...+5&deg;, облачно, небольшой дождь, ночью: +0&deg;...+2&deg;,\r\nоблачно, небольшой снег, ветер: З — 6-8 м/с, давление: 754 мм.рт.ст, влажность: 95%<br /><br />\r\n<b>Завтра:</b><br />днем: +0&deg;, снег, ночью: +0&deg;...-2&deg;,\r\nметель, ветер: C — 8-10 м/с, давление: 760 мм.рт.ст, влажность: 95%<br /><br />\r\n\n2012-03-02 10:00:41 new value: \r\n<b>Сегодня:</b><br />днем: +3&deg;...+5&deg;, облачно, без существенных осадков, ночью: +0&deg;...+2&deg;,\r\nснег, ветер: З — 5-7 м/с, давление: 753 мм.рт.ст, влажность: 100%<br /><br />\r\n<b>Завтра:</b><br />днем: +0&deg;, снег, ночью: +0&deg;,\r\nснег, ветер: C — 10-12 м/с, давление: 762 мм.рт.ст, влажность: 80%<br /><br />\r\n\n2012-03-01 14:26:15 new value: \r\n<b>Сегодня:</b><br />днем: +0&deg;...+2&deg;, облачно, небольшой снег, ночью: -2&deg;...-4&deg;,\r\nпеременная облачность, ветер: ЮЗ — 4-6 м/с, давление: 762 мм.рт.ст, влажность: 95%<br /><br />\r\n<b>Завтра:</b><br />днем: +3&deg;...+5&deg;, облачно, без существенных осадков, ночью: +0&deg;...+2&deg;,\r\nснег, ветер: З — 5-7 м/с, давление: 753 мм.рт.ст, влажность: 100%<br /><br />\r\n\n2012-03-01 10:26:15 new value: \r\n<b>Сегодня:</b><br />днем: +0&deg;, переменная облачность, ночью: -6&deg;...-8&deg;,\r\nпеременная облачность, ветер: Ю — 2-4 м/с, давление: 764 мм.рт.ст, влажность: 100%<br /><br />\r\n<b>Завтра:</b><br />днем: +0&deg;...+2&deg;, снег, ночью: +0&deg;...-2&deg;,\r\nснег, ветер: ЮЗ — 2-4 м/с, давление: 754 мм.рт.ст, влажность: 100%<br /><br />\r\n\n2012-02-29 16:50:21 new value: \r\n<b>Сегодня:</b><br />днем: +0&deg;...-2&deg;, облачно, ночью: -5&deg;...-7&deg;,\r\nясно, ветер: CЗ — 1-3 м/с, давление: 761 мм.рт.ст, влажность: 90%<br /><br />\r\n<b>Завтра:</b><br />днем: +0&deg;, переменная облачность, ночью: -6&deg;...-8&deg;,\r\nпеременная облачность, ветер: Ю — 2-4 м/с, давление: 764 мм.рт.ст, влажность: 100%<br /><br />\r\n\n2012-02-28 00:40:59 new value: \r\n<b>Сегодня:</b><br />днем: -3&deg;...-5&deg;, ясно, ночью: -9&deg;...-11&deg;,\r\nпеременная облачность, ветер: ЮЗ — 2-4 м/с, давление: 764 мм.рт.ст, влажность: 85%<br /><br />\r\n<b>Завтра:</b><br />днем: +0&deg;...+2&deg;, облачно, ночью: -3&deg;...-5&deg;,\r\nоблачно, небольшой снег, ветер: CЗ — 4-6 м/с, давление: 758 мм.рт.ст, влажность: 100%<br /><br />\r\n\n2012-02-27 14:40:57 new value: \r\n<b>Сегодня:</b><br />днем: -3&deg;...-5&deg;, облачно, небольшой снег, ночью: -5&deg;...-7&deg;,\r\nясно, ветер: C — 5-7 м/с, давление: 761 мм.рт.ст, влажность: 85%<br /><br />\r\n<b>Завтра:</b><br />днем: -3&deg;...-5&deg;, ясно, ночью: -9&deg;...-11&deg;,\r\nпеременная облачность, ветер: ЮЗ — 2-4 м/с, давление: 764 мм.рт.ст, влажность: 85%<br /><br />\r\n\n2012-02-27 10:40:56 new value: \r\n<b>Сегодня:</b><br />днем: -3&deg;...-5&deg;, облачно, ночью: -5&deg;...-7&deg;,\r\nметель, ветер: C — 5-7 м/с, давление: 760 мм.рт.ст, влажность: 85%<br /><br />\r\n<b>Завтра:</b><br />днем: -3&deg;...-5&deg;, переменная облачность, ночью: -8&deg;...-10&deg;,\r\nоблачно, ветер: З — 2-4 м/с, давление: 764 мм.рт.ст, влажность: 85%<br /><br />\r\n\n2012-02-25 18:16:00 new value: \r\n<b>Сегодня:</b><br />днем: +0&deg;...+2&deg;, облачно, ночью: +0&deg;...+2&deg;,\r\nясно, ветер: З — 5-7 м/с, давление: 746 мм.рт.ст, влажность: 100%<br /><br />\r\n<b>Завтра:</b><br />днем: -1&deg;...-3&deg;, метель, ночью: +0&deg;...-2&deg;,\r\nметель, ветер: C — 5-7 м/с, давление: 751 мм.рт.ст, влажность: 85%<br /><br />\r\n\n2012-02-25 16:15:59 incorrect value:\n2012-02-25 14:15:59 incorrect value:\n2012-02-25 11:07:52 incorrect value:\n2012-02-24 00:01:46 new value: \r\n<b>Сегодня:</b><br />днем: +0&deg;, снег, ночью: +0&deg;...-2&deg;,\r\nметель, ветер: З — 3-5 м/с, давление: 747 мм.рт.ст, влажность: 100%<br /><br />\r\n<b>Завтра:</b><br />днем: +0&deg;...+2&deg;, облачно, ночью: +0&deg;,\r\nоблачно, небольшой снег, ветер: З — 5-7 м/с, давление: 749 мм.рт.ст, влажность: 100%<br /><br />\r\n\n2012-02-23 19:10:22 new value: \r\n<b>Сегодня:</b><br />днем: +0&deg;...+2&deg;, снег, ночью: +0&deg;...+2&deg;,\r\nясно, ветер: ЮЗ — 7-9 м/с, давление: 746 мм.рт.ст, влажность: 100%<br /><br />\r\n<b>Завтра:</b><br />днем: +0&deg;, снег, ночью: +0&deg;...-2&deg;,\r\nметель, ветер: З — 3-5 м/с, давление: 747 мм.рт.ст, влажность: 100%<br /><br />\r\n\n2012-02-05 13:52:29 new value: \r\n<b>Сегодня:</b><br />днем: -14&deg;...-16&deg;, облачно, ночью: -17&deg;...-19&deg;,\r\nясно, ветер: C — 4-6 м/с, давление: 779 мм.рт.ст, влажность: 85%<br /><br />\r\n<b>Завтра:</b><br />днем: -8&deg;...-10&deg;, облачно, небольшой снег, ночью: -14&deg;...-16&deg;,\r\nоблачно, ветер: В — 3-5 м/с, давление: 779 мм.рт.ст, влажность: 90%<br /><br />\r\n\n2012-02-04 12:45:53 incorrect value:\n2012-02-03 23:18:55 new value: \r\n<b>Сегодня:</b><br />днем: -19&deg;...-21&deg;, облачно, ночью: -24&deg;...-26&deg;,\r\nясно, ветер: ЮВ — 2-4 м/с, давление: 782 мм.рт.ст, влажность: 75%<br /><br />\r\n<b>Завтра:</b><br />днем: -16&deg;...-18&deg;, снег, ночью: -23&deg;...-25&deg;,\r\nпеременная облачность, ветер: CВ — 2-4 м/с, давление: 780 мм.рт.ст, влажность: 85%<br /><br />\r\n\n2012-01-26 14:30:15 new value: \r\n<b>Сегодня:</b><br />днем: -11&deg;...-13&deg;, облачно, ночью: -9&deg;...-11&deg;,\r\nоблачно, ветер: В — 5-7 м/с, давление: 777 мм.рт.ст, влажность: 80%<br /><br />\r\n<b>Завтра:</b><br />днем: -8&deg;...-10&deg;, облачно, ночью: -9&deg;...-11&deg;,\r\nоблачно, ветер: ЮВ — 5-7 м/с, давление: 779 мм.рт.ст, влажность: 85%<br /><br />\r\n\n2012-01-25 22:21:24 new value: \r\n<b>Сегодня:</b><br />днем: -5&deg;...-7&deg;, облачно, небольшой снег, ночью: -6&deg;...-8&deg;,\r\nметель, ветер: ЮВ — 4-6 м/с, давление: 770 мм.рт.ст, влажность: 90%<br /><br />\r\n<b>Завтра:</b><br />днем: -9&deg;...-11&deg;, облачно, ночью: -8&deg;...-10&deg;,\r\nоблачно, ветер: В — 5-7 м/с, давление: 776 мм.рт.ст, влажность: 80%<br /><br />\r\n\n2012-01-24 22:46:04 new value: \r\n<b>Сегодня:</b><br />днем: -1&deg;...-3&deg;, метель, ночью: -4&deg;...-6&deg;,\r\nметель, ветер: ЮВ — 5-7 м/с, давление: 762 мм.рт.ст, влажность: 95%<br /><br />\r\n<b>Завтра:</b><br />днем: -3&deg;...-5&deg;, метель, ночью: -2&deg;...-4&deg;,\r\nоблачно, небольшой снег, ветер: ЮВ — 5-7 м/с, давление: 769 мм.рт.ст, влажность: 95%<br /><br />\r\n\n2012-01-17 11:36:50 new value: \r\n<b>Сегодня:</b><br />днем: -4&deg;...-6&deg;, облачно, ночью: -4&deg;...-6&deg;,\r\nоблачно, небольшой снег, ветер: CВ — 1-3 м/с, давление: 767 мм.рт.ст, влажность: 80%<br /><br />\r\n<b>Завтра:</b><br />днем: -4&deg;...-6&deg;, метель, ночью: -7&deg;...-9&deg;,\r\nоблачно, ветер: ЮЗ — 5-7 м/с, давление: 769 мм.рт.ст, влажность: 95%<br /><br />\r\n\n2012-01-16 13:49:39 new value: \r\n<b>Сегодня:</b><br />днем: -2&deg;...-4&deg;, метель, ночью: -3&deg;...-5&deg;,\r\nметель, ветер: C — 4-6 м/с, давление: 761 мм.рт.ст, влажность: 90%<br /><br />\r\n<b>Завтра:</b><br />днем: -4&deg;...-6&deg;, облачно, ночью: -4&deg;...-6&deg;,\r\nоблачно, небольшой снег, ветер: CВ — 1-3 м/с, давление: 767 мм.рт.ст, влажность: 80%<br /><br />\r\n\n2012-01-15 23:48:12 new value: \r\n<b>Сегодня:</b><br />днем: -1&deg;...-3&deg;, облачно, ночью: -2&deg;...-4&deg;,\r\nоблачно, небольшой снег, ветер: CВ — 5-7 м/с, давление: 759 мм.рт.ст, влажность: 85%<br /><br />\r\n<b>Завтра:</b><br />днем: -2&deg;...-4&deg;, метель, ночью: -3&deg;...-5&deg;,\r\nоблачно, ветер: C — 4-6 м/с, давление: 762 мм.рт.ст, влажность: 90%<br /><br />\r\n\n2012-01-15 13:06:13 incorrect value:\n2012-01-14 13:03:21 incorrect value:\n2012-01-13 11:20:01 new value: \r\n<b>Сегодня:</b><br />днем: +0&deg;...+2&deg;, переменная облачность, ночью: +3&deg;...+5&deg;,\r\nоблачно, небольшой дождь, ветер: З — 8-10 м/с, давление: 746 мм.рт.ст, влажность: 75%<br /><br />\r\n<b>Завтра:</b><br />днем: +0&deg;...-2&deg;, облачно, небольшой снег, ночью: -1&deg;...-3&deg;,\r\nоблачно, небольшой снег, ветер: ЮЗ — 3-5 м/с, давление: 749 мм.рт.ст, влажность: 85%<br /><br />\r\n\n2012-01-12 13:21:57 new value: \r\n<b>Сегодня:</b><br />днем: +0&deg;...+2&deg;, облачно, ночью: +0&deg;...+2&deg;,\r\nоблачно, ветер: ЮЗ — 7-9 м/с, давление: 757 мм.рт.ст, влажность: 90%<br /><br />\r\n<b>Завтра:</b><br />днем: +0&deg;...+2&deg;, облачно, небольшой снег, ночью: +4&deg;...+6&deg;,\r\nдождь, ветер: З — 8-10 м/с, давление: 746 мм.рт.ст, влажность: 80%<br /><br />\r\n\n2012-01-12 11:21:56 incorrect value:\n2012-01-12 09:21:56 incorrect value:\n2012-01-11 11:44:30 new value: \r\n<b>Сегодня:</b><br />днем: -1&deg;...-3&deg;, переменная облачность, ночью: -3&deg;...-5&deg;,\r\nпеременная облачность, ветер: ЮЗ — 6-8 м/с, давление: 764 мм.рт.ст, влажность: 85%<br /><br />\r\n<b>Завтра:</b><br />днем: +0&deg;...+2&deg;, облачно, ночью: +1&deg;...+3&deg;,\r\nснег, ветер: ЮЗ — 7-9 м/с, давление: 757 мм.рт.ст, влажность: 90%<br /><br />\r\n\n2012-01-10 14:43:15 new value: \r\n<b>Сегодня:</b><br />днем: +0&deg;...-2&deg;, облачно, ночью: +0&deg;...-2&deg;,\r\nоблачно, ветер: CЗ — 3-5 м/с, давление: 766 мм.рт.ст, влажность: 90%<br /><br />\r\n<b>Завтра:</b><br />днем: +0&deg;...-2&deg;, облачно, ночью: -4&deg;...-6&deg;,\r\nпеременная облачность, ветер: ЮЗ — 7-9 м/с, давление: 764 мм.рт.ст, влажность: 85%<br /><br />\r\n\n2012-01-10 10:43:15 new value: \r\n<b>Сегодня:</b><br />днем: +0&deg;...-2&deg;, облачно, ночью: +0&deg;...-2&deg;,\r\nоблачно, ветер: CЗ — 4-6 м/с, давление: 766 мм.рт.ст, влажность: 90%<br /><br />\r\n<b>Завтра:</b><br />днем: +0&deg;...-2&deg;, облачно, ночью: -4&deg;...-6&deg;,\r\nпеременная облачность, ветер: ЮЗ — 6-8 м/с, давление: 764 мм.рт.ст, влажность: 90%<br /><br />\r\n\n2012-01-09 22:11:14 incorrect value:\n2012-01-09 18:15:44 new value: \r\n<b>Сегодня:</b><br />днем: -1&deg;...-3&deg;, облачно, ночью: -3&deg;...-5&deg;,\r\nоблачно, ветер: ЮЗ — 1-3 м/с, давление: 761 мм.рт.ст, влажность: 90%<br /><br />\r\n<b>Завтра:</b><br />днем: +0&deg;...-2&deg;, облачно, ночью: +0&deg;...-2&deg;,\r\nоблачно, ветер: CЗ — 4-6 м/с, давление: 766 мм.рт.ст, влажность: 90%<br /><br />\r\n\n2012-01-02 12:09:51 new value: \r\n<b>Сегодня:</b><br />днем: +0&deg;...-2&deg;, облачно, ночью: -2&deg;...-4&deg;,\r\nоблачно, ветер: Ю — 6-8 м/с, давление: 760 мм.рт.ст, влажность: 95%<br /><br />\r\n<b>Завтра:</b><br />днем: +3&deg;...+5&deg;, облачно, небольшой дождь, ночью: +1&deg;...+3&deg;,\r\nоблачно, небольшой дождь, ветер: CЗ — 5-7 м/с, давление: 758 мм.рт.ст, влажность: 95%<br /><br />\r\n\n2011-12-26 11:47:34 new value: \r\n<b>Сегодня:</b><br />днем: +6&deg;...+8&deg;, переменная облачность, ночью: +0&deg;,\r\nоблачно, ветер: З — 9-11 м/с, давление: 761 мм.рт.ст, влажность: 90%<br /><br />\r\n<b>Завтра:</b><br />днем: +8&deg;...+10&deg;, облачно, ночью: +6&deg;...+8&deg;,\r\nоблачно, ветер: З — 11-13 м/с, давление: 761 мм.рт.ст, влажность: 85%<br /><br />\r\n\n2011-12-24 23:05:18 incorrect value:\n2011-12-24 00:22:32 incorrect value:\n2011-12-23 13:06:23 new value: \r\n<b>Сегодня:</b><br />днем: -1&deg;...-3&deg;, облачно, ночью: -2&deg;...-4&deg;,\r\nоблачно, ветер: З — 2-4 м/с, давление: 767 мм.рт.ст, влажность: 85%<br /><br />\r\n<b>Завтра:</b><br />днем: -2&deg;...-4&deg;, облачно, ночью: -3&deg;...-5&deg;,\r\nоблачно, ветер: Ю — 6-8 м/с, давление: 761 мм.рт.ст, влажность: 85%<br /><br />\r\n\n2011-12-23 11:06:23 new value: \r\n<b>Сегодня:</b><br />днем: -1&deg;...-3&deg;, облачно, ночью: -1&deg;...-3&deg;,\r\nоблачно, ветер: CЗ — 2-4 м/с, давление: 767 мм.рт.ст, влажность: 75%<br /><br />\r\n<b>Завтра:</b><br />днем: -3&deg;...-5&deg;, переменная облачность, ночью: -4&deg;...-6&deg;,\r\nпеременная облачность, ветер: Ю — 6-8 м/с, давление: 763 мм.рт.ст, влажность: 80%<br /><br />\r\n\n2011-12-22 22:25:44 incorrect value:\n2011-12-22 17:31:03 new value: \r\n<b>Сегодня:</b><br />днем: -1&deg;...-3&deg;, облачно, ночью: -1&deg;...-3&deg;,\r\nоблачно, ветер: CЗ — 5-7 м/с, давление: 764 мм.рт.ст, влажность: 85%<br /><br />\r\n<b>Завтра:</b><br />днем: -1&deg;...-3&deg;, облачно, ночью: -1&deg;...-3&deg;,\r\nоблачно, ветер: CЗ — 2-4 м/с, давление: 767 мм.рт.ст, влажность: 75%<br /><br />\r\n\n2011-12-19 01:36:46 incorrect value:\n2011-12-18 23:36:46 incorrect value:\n2011-12-18 06:31:16 new value: \r\n<b>Сегодня:</b><br />днем: +1&deg;...+3&deg;, облачно, небольшой снег, ночью: +3&deg;...+5&deg;,\r\nдождь, ветер: ЮЗ — 8-10 м/с, давление: 747 мм.рт.ст, влажность: 90%<br /><br />\r\n<b>Завтра:</b><br />днем: +1&deg;...+3&deg;, облачно, небольшой снег, ночью: +0&deg;,\r\nоблачно, ветер: ЮЗ — 8-10 м/с, давление: 762 мм.рт.ст, влажность: 80%<br /><br />\r\n\n2011-12-17 22:26:23 new value: \r\n<b>Сегодня:</b><br />днем: +3&deg;...+5&deg;, облачно, небольшой дождь, ночью: +0&deg;...+2&deg;,\r\nоблачно, ветер: Ю — 7-9 м/с, давление: 743 мм.рт.ст, влажность: 95%<br /><br />\r\n<b>Завтра:</b><br />днем: +1&deg;...+3&deg;, облачно, небольшой снег, ночью: +3&deg;...+5&deg;,\r\nдождь, ветер: ЮЗ — 8-10 м/с, давление: 747 мм.рт.ст, влажность: 90%<br /><br />\r\n\n2011-12-17 01:27:28 new value: \r\n<b>Сегодня:</b><br />днем: +3&deg;...+5&deg;, облачно, небольшой дождь, ночью: +1&deg;...+3&deg;,\r\nоблачно, ветер: Ю — 8-10 м/с, давление: 740 мм.рт.ст, влажность: 95%<br /><br />\r\n<b>Завтра:</b><br />днем: +1&deg;...+3&deg;, облачно, небольшой снег, ночью: +1&deg;...+3&deg;,\r\nснег, ветер: ЮЗ — 8-10 м/с, давление: 749 мм.рт.ст, влажно', '', 0, '', '');
INSERT INTO `webvars` (`ID`, `TITLE`, `HOSTNAME`, `TYPE`, `SEARCH_PATTERN`, `CHECK_PATTERN`, `LATEST_VALUE`, `CHECK_LATEST`, `CHECK_NEXT`, `SCRIPT_ID`, `ONLINE_INTERVAL`, `LINKED_OBJECT`, `LINKED_PROPERTY`, `CODE`, `LOG`, `ENCODING`, `AUTH`, `USERNAME`, `PASSWORD`) VALUES
(4, '<#LANG_GENERAL_TEMPERATURE_OUTSIDE#>', 'http://pogoda.by/pda/?city=26850', 0, 'погода фактическая.+?Температура воздуха (.+?)[°&]', '', '+3.0', '2012-11-17 15:02:20', '2012-11-17 15:42:20', 0, 2400, 'ThisComputer', 'TempOutside', '', '2012-11-17 15:02:20 new value:+3.0\n2012-11-17 14:33:22 new value:+3.0\n2012-11-16 16:20:46 new value:+2.7\n2012-11-16 15:40:38 incorrect value:\n2012-11-16 15:40:35 incorrect value:\n2012-11-16 15:40:29 incorrect value:\n2012-11-16 15:39:59 incorrect value:\n2012-11-16 15:36:57 incorrect value:\n2012-11-16 15:36:30 incorrect value:\n2012-11-16 15:36:24 incorrect value:\n2012-11-16 15:36:08 incorrect value:\n2012-11-16 15:36:01 incorrect value:\n2012-11-16 15:35:15 incorrect value:\n2012-11-16 15:34:09 incorrect value:\n2012-11-16 15:29:02 incorrect value:\n2012-11-16 15:28:55 incorrect value:\n2012-11-16 15:28:13 incorrect value:\n2012-11-16 15:27:57 incorrect value:\n2012-11-16 15:27:36 incorrect value:\n2012-11-16 15:27:33 incorrect value:\n2012-11-16 15:27:17 incorrect value:\n2012-11-16 15:27:12 incorrect value:\n2012-11-16 15:26:39 incorrect value:\n2012-11-16 15:25:56 new value:+2.8\n', 'windows-1251', 0, '', '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
