-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Хост: mysql:3306
-- Время создания: Июл 08 2020 г., 14:55
-- Версия сервера: 10.4.12-MariaDB-1:10.4.12+maria~bionic-log
-- Версия PHP: 7.4.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `db_terminal`
--

-- --------------------------------------------------------

--
-- Структура таблицы `actions_log`
--

CREATE TABLE `actions_log` (
  `ID` int(10) UNSIGNED NOT NULL,
  `ADDED` datetime DEFAULT NULL,
  `ACTION_TYPE` varchar(100) NOT NULL DEFAULT '',
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `USER` varchar(255) NOT NULL DEFAULT '',
  `TERMINAL` varchar(255) NOT NULL DEFAULT '',
  `IP` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `admin_users`
--

CREATE TABLE `admin_users` (
  `ID` tinyint(3) UNSIGNED NOT NULL,
  `NAME` varchar(100) NOT NULL DEFAULT '',
  `LOGIN` varchar(100) NOT NULL DEFAULT '',
  `PASSWORD` varchar(100) NOT NULL DEFAULT '',
  `EMAIL` varchar(100) NOT NULL DEFAULT '',
  `COMMENTS` text DEFAULT NULL,
  `ACCESS` text DEFAULT NULL,
  `PRIVATE` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `EMAIL_ORDERS` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `EMAIL_INVENTORY` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `admin_users`
--

INSERT INTO `admin_users` (`ID`, `NAME`, `LOGIN`, `PASSWORD`, `EMAIL`, `COMMENTS`, `ACCESS`, `PRIVATE`, `EMAIL_ORDERS`, `EMAIL_INVENTORY`) VALUES
(1, 'Administrator', 'admin', '21232f297a57a5a743894a0e4a801fc3', 'webmaster@domain.com', '', 'control_modules,control_access,master,cms_docs,news,statistic,newsletter,backup,edit_templates,newslist,saverestore,skins,settings,dateselect,thumb,footercode,holdingpage,dashboard,events,users,terminals,mediabrowser,player,commands,classes,history,locations,methods,properties,objects,pvalues,shoutbox,shoutrooms,jobs,btdevices,weather,usbdevices,app_mediabrowser,app_products,app_tdwiki,app_weather,layouts,scripts,rss_channels,languages,pinghosts,watchfolders,app_player,app_gpstrack,webvars,patterns,onewire,app_calendar,xray', 0, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `blockly_code`
--

CREATE TABLE `blockly_code` (
  `ID` int(10) UNSIGNED NOT NULL,
  `SYSTEM_NAME` varchar(255) NOT NULL DEFAULT '',
  `CODE_TYPE` int(3) NOT NULL DEFAULT 0,
  `CODE` text DEFAULT NULL,
  `XML` text DEFAULT NULL,
  `UPDATED` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `blockly_code`
--

INSERT INTO `blockly_code` (`ID`, `SYSTEM_NAME`, `CODE_TYPE`, `CODE`, `XML`, `UPDATED`) VALUES
(1, 'script14', 0, '', '', '2014-09-03 12:28:53'),
(7, 'script16', 0, '// got data\r\n$sensor=$params[\'sensor\'];\r\nif (!$sensor) return 0;\r\n\r\n$hum=(int)str_replace(\'-\',\'\',$params[\'other_params\'][6]);\r\nif ($hum>0 && $hum<=100) {\r\n callMethod(\'hum\'.$sensor.\'.humChanged\',array(\'h\'=>$hum));\r\n}\r\n\r\n//$temp=((int)str_replace(\'-\',\'\',$params[\'other_params\'][4]))/10;\r\n$b1 =(int)str_replace(\'-\',\'\',$params[\'other_params\'][4]);\r\n$b2 =(int)str_replace(\'-\',\'\',$params[\'other_params\'][5]);\r\n$y_temp=256*($b2 & 15)+$b1;\r\nif  (($b2 & 8) != 0 ) { // отрицательное значение температуры\r\n $y_temp=4096-$y_temp;\r\n $temp = -1*($y_temp)/10;\r\n} else { // положительное значение температуры\r\n $temp = $y_temp/10;\r\n}\r\n\r\nif ($temp>-30 && $temp<=100) {\r\n callMethod(\'temp\'.$sensor.\'.tempChanged\',array(\'t\'=>$temp));\r\n}', '', '2014-09-03 16:07:57'),
(10, 'method88', 0, '$rooms=getObjectsByClass(\"Rooms\");\r\n$total=count($rooms);\r\nfor($i=0;$i<$total;$i++) {\r\n $rooms[$i][\'room\']=getGlobal($rooms[$i][\'TITLE\'].\'.Title\');\r\n if (!$rooms[$i][\'room\']) {\r\n  $rooms[$i][\'room\']=$rooms[$i][\'TITLE\'];\r\n } \r\n $rooms[$i][\'active\']=getGlobal($rooms[$i][\'TITLE\'].\'.SomebodyHere\');\r\n $rooms[$i][\'time\']=getGlobal($rooms[$i][\'TITLE\'].\'.LatestActivity\'); \r\n $rooms[$i][\'diff\']=time()-$rooms[$i][\'time\'];\r\n} \r\n\r\nfunction rooms_cmp($a, $b)\r\n{\r\n    if ($a[\'diff\'] == $b[\'diff\']) {\r\n        return 0;\r\n    }\r\n    return ($a[\'diff\'] < $b[\'diff\']) ? -1 : 1;\r\n}\r\nusort($rooms,\"rooms_cmp\");\r\n\r\nif (!$rooms[0][\'active\']) {\r\n $somebodyHomeText=\"Никого нет дома. Были в \".date(\'H:i\',$rooms[0][\'time\']).\" (\".$rooms[0][\'room\'].\")\";\r\n} else {\r\n $res_rooms=array();\r\n for($i=0;$i<$total;$i++) {\r\n  if ($rooms[$i][\'active\']) {\r\n   $res_rooms[]=$rooms[$i][\'room\'];\r\n  } \r\n }\r\n $somebodyHomeText=\"Кто-то дома: \".implode(\", \",$res_rooms); \r\n}\r\nsetGlobal(\'somebodyHomeText\',$somebodyHomeText);\r\n//echo \"Updated\";', '', '2014-09-04 16:05:37'),
(9, 'method66', 0, '$latestActivity=$this->getProperty(\'LatestActivity\');\r\n$this->setProperty(\'LatestActivity\',time());\r\n$this->setProperty(\'LatestActivityTime\',date(\'H:i\'));\r\n\r\n$this->setProperty(\'SomebodyHere\',1);\r\n$ot=$this->object_title;\r\nif ($this->getProperty(\"IdleDelay\")) {\r\n $activity_timeout=(int)$this->getProperty(\"IdleDelay\");\r\n} else {\r\n $activity_timeout=10*60;\r\n}\r\nclearTimeOut($ot.\"_activity_timeout\");\r\nsetTimeOut($ot.\"_activity_timeout\",\"callMethod(\'\".$ot.\".onIdle\');\",$activity_timeout);\r\n$this->callMethod(\"updateActivityStatus\");\r\n', '', '2014-09-04 16:05:03'),
(84, 'object17_method59', 0, '$details=array();\r\n$red_state=0;\r\n$yellow_state=0;\r\n\r\n$cycles=array(\'states\'=>\'states\',\'main\'=>\'main\',\'execs\'=>\'exec\',\'scheduler\'=>\'scheduler\');\r\nforeach($cycles as $k=>$v) {\r\n $tm=getGlobal(\'ThisComputer.cycle_\'.$k.\'Run\');\r\n if (time()-(int)$tm>5*60) {\r\n  $red_state=1;\r\n  $details[]=$v.\" \".LANG_GENERAL_CYCLE.\" \".LANG_GENERAL_STOPPED.\".\";\r\n }\r\n}\r\n\r\n$cycles=array(\'ping\'=>\'ping\',\'webvars\'=>\'webvars\');\r\nforeach($cycles as $k=>$v) {\r\n $tm=getGlobal(\'ThisComputer.cycle_\'.$k.\'Run\');\r\n if (time()-(int)$tm>10*60) {\r\n  $yellow_state=1;\r\n  $details[]=$v.\" \".LANG_GENERAL_CYCLE.\" \".LANG_GENERAL_STOPPED.\".\";  \r\n }\r\n}\r\n\r\nif ($red_state) {\r\n $state=\'red\';\r\n $state_title=LANG_GENERAL_RED; \r\n} elseif ($yellow_state) {\r\n $state=\'yellow\';\r\n $state_title=LANG_GENERAL_YELLOW;  \r\n} else {\r\n $state=\'green\';\r\n $state_title=LANG_GENERAL_GREEN;   \r\n}\r\n\r\n$new_details=implode(\". \",$details);\r\nif ($this->getProperty(\"stateDetails\")!=$new_details) {\r\n $this->setProperty(\'stateDetails\',$new_details);\r\n}\r\n\r\nif ($this->getProperty(\'stateColor\')!=$state) {\r\n $this->setProperty(\'stateColor\',$state);\r\n $this->setProperty(\'stateTitle\',$state_title);\r\n if ($state!=\'green\') {\r\n  say(LANG_GENERAL_SYSTEM_STATE.\" \".LANG_GENERAL_CHANGED_TO.\" \".$state_title.\".\");\r\n  say(implode(\". \",$details));\r\n } else {\r\n  say(LANG_GENERAL_SYSTEM_STATE.\" \".LANG_GENERAL_RESTORED_TO.\" \".$state_title);\r\n }\r\n $this->callMethod(\'stateChanged\');\r\n}', '', '2015-12-03 14:46:44'),
(74, 'pattern6', 0, '', '', '2014-10-31 15:22:41'),
(75, 'pattern5', 0, '', '', '2014-10-31 15:22:55'),
(73, 'object18_method59', 0, '$details=array();\r\n$red_state=0;\r\n$yellow_state=0;\r\n\r\nif (!isOnline(\'Internet\')) {\r\n $yellow_state=1;\r\n $details[]=LANG_GENERAL_NO_INTERNET_ACCESS;\r\n}\r\n\r\nif ($red_state) {\r\n $state=\'red\';\r\n $state_title=LANG_GENERAL_RED; \r\n} elseif ($yellow_state) {\r\n $state=\'yellow\';\r\n $state_title=LANG_GENERAL_YELLOW;  \r\n} else {\r\n $state=\'green\';\r\n $state_title=LANG_GENERAL_GREEN;   \r\n}\r\n\r\n$new_details=implode(\". \",$details);\r\nif ($this->getProperty(\"stateDetails\")!=$new_details) {\r\n $this->setProperty(\'stateDetails\',$new_details);\r\n}\r\n\r\nif ($this->getProperty(\'stateColor\')!=$state) {\r\n $this->setProperty(\'stateColor\',$state);\r\n $this->setProperty(\'stateTitle\',$state_title);\r\n if ($state!=\'green\') {\r\n  say(LANG_GENERAL_COMMUNICATION_STATE.\" \".LANG_GENERAL_CHANGED_TO.\" \".$state_title.\".\");\r\n  say(implode(\". \",$details));\r\n } else {\r\n  say(LANG_GENERAL_COMMUNICATION_STATE.\" \".LANG_GENERAL_RESTORED_TO.\" \".$state_title);\r\n }\r\n $this->callMethod(\'stateChanged\');\r\n}', '', '2014-10-31 15:10:21'),
(60, 'script15', 0, '$weather.=\"Сегодня ожидается \".str_replace(\'&deg;\',\' \',getGlobal(\'weatherToday\'));\r\n$weather.=\". Завтра \".str_replace(\'&deg;\',\' \',getGlobal(\'weatherTomorrow\'));\r\n$weather.=\". Сейчас на улице \".getGlobal(\'TempOutside\').\'.\';\r\n$weather=str_replace(\'&deg;\',\'\',$weather);\r\nsay($weather,2);', '', '2014-10-30 15:32:00'),
(26, 'object72_method97', 0, 'say(\"Нажата на пульте кнопка Цэ!\",2);', '', '2014-09-04 17:38:10'),
(27, 'object70_method97', 0, 'say(\"Нажата на пульте кнопка А!\",2);', '', '2014-09-04 17:38:30'),
(28, 'object71_method97', 0, 'say(\"Нажата на пульте кнопка Бэ!\",2);', '', '2014-09-04 17:38:48'),
(61, 'script17', 0, '$lastRead=getGlobal(\'lastNewsRead\');\r\n$limit=10;\r\n\r\n$items=SQLSelect(\"SELECT TITLE FROM rss_items WHERE ADDED>\'\".date(\'Y-m-d H:i:s\',$lastRead).\"\' ORDER BY ADDED DESC LIMIT $limit\");\r\n$total=count($items);\r\nif ($total==0) {\r\n $items=SQLSelect(\"SELECT TITLE FROM rss_items WHERE 1 ORDER BY ADDED DESC LIMIT 2\");\r\n $total=count($items); \r\n}\r\n\r\n$res=array_reverse($items);\r\nfor($i=0;$i<$total;$i++) {\r\n say($res[$i][\'TITLE\'],2);\r\n}\r\nsetGlobal(\'lastNewsRead\',time());', '', '2014-10-30 15:32:13'),
(35, 'object72_method98', 0, 'say(\"Нажата на пульте кнопка Цэ!\",2);\r\nrunScript(\'readLatestNews\');', '', '2014-09-04 18:04:04'),
(63, 'script18', 0, '// вытягиваем историю из переменной\r\n$alreadyPlayed=gg(\"AlreadyPlayedMusic\");\r\nif (!$alreadyPlayed) {\r\n $alreadyPlayed=\'0\';\r\n}\r\n\r\n// выбираем случайную папку\r\n$rec=SQLSelectOne(\"SELECT * FROM media_favorites WHERE ID NOT IN (\".$alreadyPlayed.\") ORDER BY RAND()\");\r\n\r\nif (!$rec[\'ID\']) {\r\n // папок больше не осталось, поэтому выбираем случайную и сбрасываем историю\r\n $rec=SQLSelectOne(\"SELECT * FROM media_favorites ORDER BY RAND()\");\r\n $alreadyPlayed=\'0\';\r\n}\r\n\r\n\r\nif ($rec[\'ID\']) {\r\n\r\n // добавляем выбранную папку в историю\r\n $alreadyPlayed.=\',\'.$rec[\'ID\'];\r\n sg(\"AlreadyPlayedMusic\",$alreadyPlayed);\r\n\r\n // запускаем на проигрывание\r\n $collection=SQLSelectOne(\"SELECT * FROM collections WHERE ID=\".(int)$rec[\'COLLECTION_ID\']);\r\n $path=$collection[\'PATH\'].$rec[\'PATH\'];\r\n playMedia($path);\r\n //setTimeOut(\'VLCPlayer_update\',\"callMethod(\'VLCPlayer.update\');\",10);\r\n\r\n}', '', '2014-10-30 15:32:40'),
(62, 'script19', 0, 'getURL(\'http://localhost/rc/?command=vlc_pause\',0);', '', '2014-10-30 15:32:28'),
(39, 'object19_method64', 0, 'callMethod(\'noo1.turnOff\'); // выключаем тёплый пол\r\ncallMethod(\'noo2.turnOff\'); // выключаем бойлер на кухне', '', '2014-09-05 11:10:07'),
(40, 'object19_method65', 0, 'callMethod(\'noo1.turnOn\'); // включаем тёплый пол\r\ncallMethod(\'noo2.turnOn\'); // включаем бойлер на кухне', '', '2014-09-05 11:10:32'),
(76, 'script20', 0, 'say(LANG_GENERAL_SETTING_UP_LIGHTS,2);\r\n// to-do', '', '2014-10-31 15:25:11'),
(69, 'object17_method73', 0, '$details=array();\r\n$red_state=0;\r\n$yellow_state=0;\r\n\r\n$cycles=array(\'states\'=>\'states cycle\',\'main\'=>\'main cycle\',\'execs\'=>\'exec cycle\',\'scheduler\'=>\'scheduler cycle\');\r\nforeach($cycles as $k=>$v) {\r\n $tm=getGlobal(\'ThisComputer.cycle_\'.$k.\'Run\');\r\n if (time()-(int)$tm>5*60) {\r\n  $red_state=1;\r\n  $details[]=$v.\" stopped.\";\r\n }\r\n}\r\n\r\n$cycles=array(\'ping\'=>\'ping cycle\',\'webvars\'=>\'webvars cycle\',\'watchfolders\'=>\'watch folders cycle\',\'rss\'=>\'RSS cycle\');\r\nforeach($cycles as $k=>$v) {\r\n $tm=getGlobal(\'ThisComputer.cycle_\'.$k.\'Run\');\r\n if (time()-(int)$tm>10*60) {\r\n  $yellow_state=1;\r\n  $details[]=$v.\" stopped.\";\r\n }\r\n}\r\n\r\nif ($red_state) {\r\n $state=\'red\';\r\n $state_title=\'Red\'; \r\n} elseif ($yellow_state) {\r\n $state=\'yellow\';\r\n $state_title=\'Yellow\';  \r\n} else {\r\n $state=\'green\';\r\n $state_title=\'Green\';   \r\n}\r\n\r\n$new_details=implode(\". \",$details);\r\nif ($this->getProperty(\"stateDetails\")!=$new_details) {\r\n $this->setProperty(\'stateDetails\',$new_details);\r\n}\r\n\r\nif ($this->getProperty(\'stateColor\')!=$state) {\r\n $this->setProperty(\'stateColor\',$state);\r\n $this->setProperty(\'stateTitle\',$state_title);\r\n if ($state!=\'green\') {\r\n  say(\"System state changed to \".$state_title.\".\");\r\n  say(implode(\". \",$details));\r\n } else {\r\n  say(\"System state restored to \".$state_title);\r\n }\r\n $this->callMethod(\'stateChanged\');\r\n}', '', '2014-10-30 15:44:01'),
(70, 'object16_method75', 0, '$details=array();\r\n$red_state=0;\r\n$yellow_state=0;\r\n\r\nif ($red_state) {\r\n $state=\'red\';\r\n $state_title=LANG_GENERAL_RED; \r\n} elseif ($yellow_state) {\r\n $state=\'yellow\';\r\n $state_title=LANG_GENERAL_YELLOW;  \r\n} else {\r\n $state=\'green\';\r\n $state_title=LANG_GENERAL_GREEN;   \r\n}\r\n\r\n$new_details=implode(\". \",$details);\r\nif ($this->getProperty(\"stateDetails\")!=$new_details) {\r\n $this->setProperty(\'stateDetails\',$new_details);\r\n}\r\n\r\nif ($this->getProperty(\'stateColor\')!=$state) {\r\n $this->setProperty(\'stateColor\',$state);\r\n $this->setProperty(\'stateTitle\',$state_title);\r\n if ($state!=\'green\') {\r\n  say(LANG_GENERAL_SYSTEM_STATE.\" \".LANG_GENERAL_CHANGED_TO.\" \".$state_title.\".\");\r\n  say(implode(\". \",$details));\r\n } else {\r\n  say(LANG_GENERAL_SYSTEM_STATE.\" \".LANG_GENERAL_RESTORED_TO.\" \".$state_title);\r\n }\r\n $this->callMethod(\'stateChanged\');\r\n}', '', '2014-10-31 14:57:24'),
(59, 'script21', 0, '$res=\'\';\r\n if (gg(\'Security.stateColor\')==\'green\' && gg(\'System.stateColor\')==\'green\' && gg(\'Communication.stateColor\')==\'green\') {\r\n  $res=\'Все системы работают в штатном режиме\';\r\n } else {\r\n  if (gg(\'Security.stateColor\')!=\'green\') {\r\n   $res.=\" Проблема безопасности: \".getGlobal(\'Security.stateDetails\');\r\n  }\r\n  if (gg(\'System.stateColor\')!=\'green\') {\r\n   $res.=\" Системная проблема: \".getGlobal(\'System.stateDetails\');\r\n  }  \r\n  if (gg(\'Communication.stateColor\')!=\'green\') {\r\n   $res.=\" Проблема связи: \".getGlobal(\'Communication.stateDetails\');\r\n  }  \r\n }\r\n say($res,5);', '', '2014-10-30 15:31:46'),
(48, 'script1', 0, 'say(getRandomLine(\'morning\'),2);\r\n//runScript(\'readWunderTasks\');\r\nrunScript(\'readWeatherToday\');\r\nrunScript(\'reportStatus\');', '<xml xmlns=\"http://www.w3.org/1999/xhtml\">\r\n  <block type=\"majordomo_say\" inline=\"false\" x=\"140\" y=\"51\">\r\n    <value name=\"TEXT\">\r\n      <block type=\"text\">\r\n        <title name=\"TEXT\">Good morning!</title>\r\n      </block>\r\n    </value>\r\n    <next>\r\n      <block type=\"controls_if\" inline=\"false\">\r\n        <value name=\"IF0\">\r\n          <block type=\"logic_negate\" inline=\"false\">\r\n            <value name=\"BOOL\">\r\n              <block type=\"majordomo_getglobal\">\r\n                <title name=\"TEXT\">ThisComputer.WeHaveGuests</title>\r\n              </block>\r\n            </value>\r\n          </block>\r\n        </value>\r\n        <statement name=\"DO0\">\r\n          <block type=\"majordomo_runscript\" inline=\"false\">\r\n            <title name=\"TEXT\">sayTodayAgenda</title>\r\n            <next>\r\n              <block type=\"majordomo_runscript\" inline=\"false\">\r\n                <title name=\"TEXT\">playFavoriteMusic</title>\r\n              </block>\r\n            </next>\r\n          </block>\r\n        </statement>\r\n      </block>\r\n    </next>\r\n  </block>\r\n</xml>', '2014-09-05 12:30:58'),
(47, 'script13', 0, 'say(getRandomLine(\'evening\'),2);\r\nrunScript(\'reportStatus\');', '<xml xmlns=\"http://www.w3.org/1999/xhtml\">\r\n  <block type=\"majordomo_say\" inline=\"false\" x=\"140\" y=\"51\">\r\n    <value name=\"TEXT\">\r\n      <block type=\"text\">\r\n        <title name=\"TEXT\">Good morning!</title>\r\n      </block>\r\n    </value>\r\n    <next>\r\n      <block type=\"controls_if\" inline=\"false\">\r\n        <value name=\"IF0\">\r\n          <block type=\"logic_negate\" inline=\"false\">\r\n            <value name=\"BOOL\">\r\n              <block type=\"majordomo_getglobal\">\r\n                <title name=\"TEXT\">ThisComputer.WeHaveGuests</title>\r\n              </block>\r\n            </value>\r\n          </block>\r\n        </value>\r\n        <statement name=\"DO0\">\r\n          <block type=\"majordomo_runscript\" inline=\"false\">\r\n            <title name=\"TEXT\">sayTodayAgenda</title>\r\n            <next>\r\n              <block type=\"majordomo_runscript\" inline=\"false\">\r\n                <title name=\"TEXT\">playFavoriteMusic</title>\r\n              </block>\r\n            </next>\r\n          </block>\r\n        </statement>\r\n      </block>\r\n    </next>\r\n  </block>\r\n</xml>', '2014-09-05 12:30:34'),
(52, 'script12', 0, '$id=$params[\'rcswitch\'];\r\nif ($id==\'12345\') {\r\n //sensor 1\r\n}', '', '2014-10-30 15:10:27'),
(54, 'object8_method', 0, '', '', '2014-10-30 15:29:14'),
(55, 'object7_method', 0, '', '', '2014-10-30 15:29:39'),
(67, 'script22', 1, 'runScript(\"reportStatus\", array());\r\n', '<xml xmlns=\"http://www.w3.org/1999/xhtml\">\r\n  <block type=\"majordomo_script_21\" id=\"7\" inline=\"true\" x=\"148\" y=\"87\"></block>\r\n</xml>', '2014-10-30 15:35:12'),
(68, 'object6_method18', 0, '$h=(int)date(\'G\',time());\r\n$m=date(\'i\',time());\r\n\r\n\r\nif (isWeekDay()) {\r\n\r\n}\r\n\r\n\r\nif (($h>=8) && getGlobal(\'clockChimeEnabled\')) {\r\n if ($m==\"00\") {\r\n   say(timeNow(),1);\r\n }\r\n}\r\n\r\n\r\nsetGlobal(\'timeNow\',date(\'H:i\'));\r\n\r\n$homeStatus=date(\'H:i\');\r\nif (getGlobal(\'NobodyHomeMode.active\')) {\r\n $homeStatus.=\' Дома никого\';\r\n} else {\r\n $homeStatus.=\' Дома кто-то есть\';\r\n}\r\n\r\n$homeStatus.=\' \'.getGlobal(\'Security.stateDetails\');\r\n$homeStatus.=\' \'.getGlobal(\'System.stateDetails\');\r\n$homeStatus.=\' \'.getGlobal(\'Communication.stateDetails\');\r\nsetGlobal(\'HomeStatus\',$homeStatus);\r\n\r\n if (timeBetween(getGlobal(\'SunRiseTime\'),getGlobal(\'SunSetTime\')) && getGlobal(\'isDark\')==\"1\") {\r\n  setGlobal(\"isDark\",0);\r\n  callMethod(\'DarknessMode.deactivate\');  \r\n } elseif (!timeBetween(getGlobal(\'SunRiseTime\'),getGlobal(\'SunSetTime\')) && getGlobal(\'isDark\')!=\"1\") {\r\n  setGlobal(\"isDark\",1);\r\n  callMethod(\'DarknessMode.activate\');    \r\n }\r\n \r\n  if (timeIs(getGlobal(\'SunRiseTime\'))) {\r\n  say(\'Всходит солнце\');\r\n }\r\n if (timeIs(getGlobal(\'SunSetTime\'))) {\r\n  say(\'Солнце заходит\',2);\r\n }\r\n \r\nif (timeIs(\"23:30\") && (gg(\"EconomMode.active\")!=\"1\") && (gg(\"NobodyHomeMode.active\")==\"1\")) {\r\n say(\"Похоже никого нет сегодня, можно сэкономить немного.\");\r\n callMethod(\'EconomMode.activate\');\r\n}\r\n\r\nif (timeIs(\'20:00\')) {\r\n callMethod(\'NightMode.activate\');\r\n} elseif (timeIs(\'08:00\')) {\r\n callMethod(\'NightMode.deactivate\');\r\n}\r\n\r\nif (timeIs(\"03:00\")) {\r\n runScript(\"systemMaintenance\");\r\n}\r\n\r\nif (gg(\'ThisComputer.AlarmStatus\') && timeIs(gg(\'ThisComputer.AlarmTime\'))) {\r\n runScript(\'MorningAlarm\');\r\n}', '', '2014-10-30 15:38:26'),
(71, 'object16_method59', 0, '$details=array();\r\n$red_state=0;\r\n$yellow_state=0;\r\n\r\nif ($red_state) {\r\n $state=\'red\';\r\n $state_title=LANG_GENERAL_RED; \r\n} elseif ($yellow_state) {\r\n $state=\'yellow\';\r\n $state_title=LANG_GENERAL_YELLOW;  \r\n} else {\r\n $state=\'green\';\r\n $state_title=LANG_GENERAL_GREEN;   \r\n}\r\n\r\n$new_details=implode(\". \",$details);\r\nif ($this->getProperty(\"stateDetails\")!=$new_details) {\r\n $this->setProperty(\'stateDetails\',$new_details);\r\n}\r\n\r\nif ($this->getProperty(\'stateColor\')!=$state) {\r\n $this->setProperty(\'stateColor\',$state);\r\n $this->setProperty(\'stateTitle\',$state_title);\r\n if ($state!=\'green\') {\r\n  say(LANG_GENERAL_SECURITY_STATE.\" \".LANG_GENERAL_CHANGED_TO.\" \".$state_title.\".\");\r\n  say(implode(\". \",$details));\r\n } else {\r\n  say(LANG_GENERAL_SECURITY_STATE.\" \".LANG_GENERAL_RESTORED_TO.\" \".$state_title);\r\n }\r\n $this->callMethod(\'stateChanged\');\r\n}', '', '2014-10-31 15:02:09'),
(78, 'object18_method74', 0, '$details=array();\r\n$red_state=0;\r\n$yellow_state=0;\r\n\r\nif (!isOnline(\'Internet\')) {\r\n $yellow_state=1;\r\n $details[]=LANG_GENERAL_NO_INTERNET_ACCESS;\r\n}\r\n\r\nif ($red_state) {\r\n $state=\'red\';\r\n $state_title=LANG_GENERAL_RED; \r\n} elseif ($yellow_state) {\r\n $state=\'yellow\';\r\n $state_title=LANG_GENERAL_YELLOW;  \r\n} else {\r\n $state=\'green\';\r\n $state_title=LANG_GENERAL_GREEN;   \r\n}\r\n\r\n$new_details=implode(\". \",$details);\r\nif ($this->getProperty(\"stateDetails\")!=$new_details) {\r\n $this->setProperty(\'stateDetails\',$new_details);\r\n}\r\n\r\nif ($this->getProperty(\'stateColor\')!=$state) {\r\n $this->setProperty(\'stateColor\',$state);\r\n $this->setProperty(\'stateTitle\',$state_title);\r\n if ($state!=\'green\') {\r\n  say(LANG_GENERAL_COMMUNICATION_STATE.\" \".LANG_GENERAL_CHANGED_TO.\" \".$state_title.\".\");\r\n  say(implode(\". \",$details));\r\n } else {\r\n  say(LANG_GENERAL_COMMUNICATION_STATE.\" \".LANG_GENERAL_RESTORED_TO.\" \".$state_title);\r\n }\r\n $this->callMethod(\'stateChanged\');\r\n}', '', '2014-10-31 15:33:53'),
(80, 'pattern8', 0, '', '', '2014-10-31 15:38:58'),
(81, 'pattern9', 0, '', '', '2014-10-31 15:39:25');

-- --------------------------------------------------------

--
-- Структура таблицы `cached_values`
--

CREATE TABLE `cached_values` (
  `KEYWORD` char(100) NOT NULL,
  `DATAVALUE` char(255) NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Структура таблицы `cached_ws`
--

CREATE TABLE `cached_ws` (
  `PROPERTY` char(100) NOT NULL,
  `DATAVALUE` varchar(20000) NOT NULL,
  `POST_ACTION` char(100) NOT NULL,
  `ADDED` datetime NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `classes`
--

CREATE TABLE `classes` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `PARENT_ID` int(10) NOT NULL DEFAULT 0,
  `SUB_LIST` text DEFAULT NULL,
  `PARENT_LIST` text DEFAULT NULL,
  `NOLOG` int(3) NOT NULL DEFAULT 0,
  `DESCRIPTION` text DEFAULT NULL,
  `TEMPLATE` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `classes`
--

INSERT INTO `classes` (`ID`, `TITLE`, `PARENT_ID`, `SUB_LIST`, `PARENT_LIST`, `NOLOG`, `DESCRIPTION`, `TEMPLATE`) VALUES
(7, 'Timer', 0, '7', '0', 1, '', NULL),
(10, 'Computer', 0, '10', '0', 0, '', NULL),
(21, 'systemStates', 0, '21', '0', 0, '', NULL),
(24, 'OperationalModes', 0, '24', '0', 0, '', NULL),
(25, 'Rooms', 0, '25', '0', 0, 'Rooms/Locations', NULL),
(32, 'Users', 0, '32', '0', 0, '', NULL),
(34, 'SDevices', 0, '35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60', '0', 0, 'General Devices Class', NULL),
(35, 'SControllers', 34, '36,37,38,39,40', '34', 0, 'Controllable device', NULL),
(36, 'SGroups', 35, '36', '34,35', 0, NULL, NULL),
(37, 'SRelays', 35, '37', '34,35', 0, NULL, NULL),
(38, 'SThermostats', 35, '38', '34,35', 0, NULL, NULL),
(39, 'SDimmers', 35, '39', '34,35', 0, NULL, NULL),
(40, 'SRGB', 35, '40', '34,35', 0, NULL, NULL),
(41, 'SMotions', 34, '41', '34', 0, NULL, NULL),
(42, 'SCameras', 34, '42', '34', 0, NULL, NULL),
(43, 'SOpenClose', 34, '43', '34', 0, NULL, NULL),
(44, 'SOpenable', 34, '44', '34', 0, NULL, NULL),
(45, 'SLeak', 34, '45', '34', 0, NULL, NULL),
(46, 'SSmoke', 34, '46', '34', 0, NULL, NULL),
(47, 'SCounters', 34, '47', '34', 0, NULL, NULL),
(48, 'SButtons', 34, '48', '34', 0, NULL, NULL),
(49, 'SSensors', 34, '50,51,52,53,54,55,56,57,58,59', '34', 0, NULL, NULL),
(50, 'SGeneralSensors', 49, '50', '34,49', 0, NULL, NULL),
(51, 'STempSensors', 49, '51', '34,49', 0, NULL, NULL),
(52, 'SHumSensors', 49, '52', '34,49', 0, NULL, NULL),
(53, 'SStateSensors', 49, '53', '34,49', 0, NULL, NULL),
(54, 'SPercentageSensors', 49, '54', '34,49', 0, NULL, NULL),
(55, 'SPressureSensors', 49, '55', '34,49', 0, NULL, NULL),
(56, 'SPowerSensors', 49, '56', '34,49', 0, NULL, NULL),
(57, 'SVoltageSensors', 49, '57', '34,49', 0, NULL, NULL),
(58, 'SCurrentSensors', 49, '58', '34,49', 0, NULL, NULL),
(59, 'SLightSensors', 49, '59', '34,49', 0, NULL, NULL),
(60, 'SHTML', 34, '60', '34', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `collections`
--

CREATE TABLE `collections` (
  `ID` int(10) UNSIGNED NOT NULL,
  `PATH` varchar(255) NOT NULL DEFAULT '',
  `TITLE` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `commands`
--

CREATE TABLE `commands` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `COMMAND` varchar(255) NOT NULL DEFAULT '',
  `URL` varchar(255) NOT NULL DEFAULT '',
  `WIDTH` int(10) NOT NULL DEFAULT 0,
  `HEIGHT` int(10) NOT NULL DEFAULT 0,
  `PARENT_ID` int(10) NOT NULL DEFAULT 0,
  `SUB_LIST` text DEFAULT NULL,
  `PARENT_LIST` text DEFAULT NULL,
  `PRIORITY` int(10) NOT NULL DEFAULT 0,
  `WINDOW` varchar(255) NOT NULL DEFAULT '',
  `AUTOSTART` int(3) NOT NULL DEFAULT 0,
  `TYPE` char(50) NOT NULL DEFAULT '',
  `MIN_VALUE` float NOT NULL DEFAULT 0,
  `MAX_VALUE` float NOT NULL DEFAULT 0,
  `CUR_VALUE` varchar(255) NOT NULL DEFAULT '',
  `STEP_VALUE` float NOT NULL DEFAULT 0,
  `LINKED_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `LINKED_PROPERTY` varchar(255) NOT NULL DEFAULT '',
  `ONCHANGE_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `ONCHANGE_METHOD` varchar(255) NOT NULL DEFAULT '',
  `ICON` varchar(50) NOT NULL DEFAULT '',
  `DATA` text DEFAULT NULL,
  `SCRIPT_ID` int(10) NOT NULL DEFAULT 0,
  `AUTO_UPDATE` int(10) NOT NULL DEFAULT 0,
  `CODE` text DEFAULT NULL,
  `SYSTEM` varchar(255) NOT NULL DEFAULT '',
  `EXT_ID` int(10) NOT NULL DEFAULT 0,
  `VISIBLE_DELAY` int(10) NOT NULL DEFAULT 0,
  `INLINE` int(3) NOT NULL DEFAULT 0,
  `SUB_PRELOAD` int(3) NOT NULL DEFAULT 0,
  `RENDER_TITLE` varchar(255) NOT NULL DEFAULT '',
  `RENDER_DATA` text DEFAULT NULL,
  `RENDER_UPDATED` datetime DEFAULT NULL,
  `SMART_REPEAT` int(3) NOT NULL DEFAULT 0,
  `READ_ONLY` int(3) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `commands`
--

INSERT INTO `commands` (`ID`, `TITLE`, `COMMAND`, `URL`, `WIDTH`, `HEIGHT`, `PARENT_ID`, `SUB_LIST`, `PARENT_LIST`, `PRIORITY`, `WINDOW`, `AUTOSTART`, `TYPE`, `MIN_VALUE`, `MAX_VALUE`, `CUR_VALUE`, `STEP_VALUE`, `LINKED_OBJECT`, `LINKED_PROPERTY`, `ONCHANGE_OBJECT`, `ONCHANGE_METHOD`, `ICON`, `DATA`, `SCRIPT_ID`, `AUTO_UPDATE`, `CODE`, `SYSTEM`, `EXT_ID`, `VISIBLE_DELAY`, `INLINE`, `SUB_PRELOAD`, `RENDER_TITLE`, `RENDER_DATA`, `RENDER_UPDATED`, `SMART_REPEAT`, `READ_ONLY`) VALUES
(1, '<#LANG_APP_MEDIA_BROWSER#>', '', '', 0, 0, 0, '2,3', '0', 6000, '', 0, '', 0, 0, '0', 0, '', '', '', '', '1_iPhone_MUSIC_5_sm.png', '', 0, 0, '', '', 0, 0, 0, 0, 'Медиа', '', '2015-12-03 14:42:25', 0, 0),
(2, 'Browse', '', '/popup/app_mediabrowser.html', 0, 0, 1, '2', '1', 100, '', 0, 'url', 0, 0, '0', 0, '', '', '', '', '', '', 0, 0, '', '', 0, 0, 0, 0, 'Browse', '', '2014-07-15 21:13:34', 0, 0),
(3, 'Player control', '', '', 0, 0, 1, '3', '1', 90, '', 0, 'custom', 0, 0, '0', 0, '', '', '', '', '', '[#module name=\"app_player\" mode=\"menu\"#]', 0, 0, '', '', 0, 0, 0, 0, 'Player control', '<!-- begin of file inner_code --><!-- begin of file ./templates/app_player/app_player.html -->\n\n<!-- Frontend -->\n <!-- begin of file ./templates/app_player/action_usual.html -->\n<script language=\"javascript\">\n function playerCommandProcessed(id, data) {\n  var elem=document.getElementById(\'playerStatus\');\n  elem.innerHTML=data;  \n  return false;\n }\n function playerCommand(pcmd) {\n  if ($(\"#selPlayTerminal\").val()==\'\') return false;\n  if ($(\"#hidPlayPath\").val()==\'\' && pcmd==\'refresh\') return false;\n  var elem=document.getElementById(\'playerStatus\');\n  elem.innerHTML=pcmd+\'...\';\n  var url=\"/popup/app_player.html?ajax=1\";\n  url+=\'&command=\'+pcmd;\n  url+=\'&play_terminal=\'+$(\"#selPlayTerminal\").val();\n  url+=\'&play=\'+URLencode($(\"#hidPlayPath\").val());\n  url+=\'&rnd=\'+URLencode($(\"#hidPlayerRnd\").val());\n  if (pcmd==\'volume\') {\n   url+=\'&volume=\'+URLencode($(\"#selVolume\").val());\n  }\n  //prompt(url, url);\n  AJAXRequest(url, \'playerCommandProcessed\', \'\');\n  return false;\n }\n</script>\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n <tr>\n  <form action=\"/nf.php?pd=pz_Y29tbWFuZHM6e3BhcmVudF9pdGVtPTF9pz_YXBwbGljYXRpb246e2FjdGlvbj1tZW51fQ%3D%3Dpz_&md=app_player&inst=&\" method=\"get\" name=\"frmPlayerControl\" id=\"frmPlayerControl\">\n  <td align=\"right\" style=\"vertical-align:middle\">\n  <div style=\"display:none\">\n   <select name=\"play_terminal\" id=\"selPlayTerminal\">\n    <option value=\"\">\n    \n    <option value=\"MAIN\" selected>Server\n    \n   </select>\n  </div>\n   <div id=\'playerStatus\' style=\"color:white\"></div>\n\n  </td>\n  </tr><tr>\n  <td nowrap><select name=\"volume\" id=\'selVolume\' onChange=\"playerCommand(\'volume\')\">\n   \n   <option value=\"0\">0%\n   \n   <option value=\"5\">5%\n   \n   <option value=\"10\">10%\n   \n   <option value=\"15\">15%\n   \n   <option value=\"20\">20%\n   \n   <option value=\"25\">25%\n   \n   <option value=\"30\">30%\n   \n   <option value=\"35\">35%\n   \n   <option value=\"40\">40%\n   \n   <option value=\"45\">45%\n   \n   <option value=\"50\">50%\n   \n   <option value=\"55\">55%\n   \n   <option value=\"60\">60%\n   \n   <option value=\"65\">65%\n   \n   <option value=\"70\">70%\n   \n   <option value=\"75\">75%\n   \n   <option value=\"80\">80%\n   \n   <option value=\"85\">85%\n   \n   <option value=\"90\" selected>90%\n   \n   <option value=\"95\">95%\n   \n   <option value=\"100\">100%\n   \n  </select></td>\n  <td valign=\"top\">\n   <div style=\"float:left;width:48px;height:48px;text-align:center\"><a href=\"#\" onClick=\'return playerCommand(\"pause\");\' style=\"padding:0;margin:0;display:inline;\"><img src=\"/img/icons/playback_pause.png\" border=\"0\"></a></div>\n   <div style=\"float:left;width:48px;height:48px;text-align:center\"><a href=\"#\" onClick=\'return playerCommand(\"prev\");\' style=\"padding:0;margin:0;display:inline;\"><img src=\"/img/icons/playback_prev.png\" border=\"0\"></a></div>\n   <div style=\"float:left;width:48px;height:48px;text-align:center\"><a href=\"#\" onClick=\'return playerCommand(\"refresh\");\' style=\"padding:0;margin:0;display:inline;\"><img src=\"/img/icons/playback_play.png\" border=\"0\"></a></div> \n   <div style=\"float:left;width:48px;height:48px;text-align:center\"><a href=\"#\" onClick=\'return playerCommand(\"next\");\' style=\"padding:0;margin:0;display:inline;\"><img src=\"/img/icons/playback_next.png\" border=\"0\"></a></div>\n   <div style=\"float:left;width:48px;height:48px;text-align:center\"><a href=\"#\" onClick=\'return playerCommand(\"fullscreen\");\' style=\"padding:0;margin:0;display:inline;\"><img src=\"/img/icons/eye.png\" border=\"0\"></a></div>\n   <div style=\"float:left;width:48px;height:48px;text-align:center\"><a href=\"#\" onClick=\'return playerCommand(\"close\");\' style=\"padding:0;margin:0;display:inline;\"><img src=\"/img/icons/delete.png\" border=\"0\"></a></div>\n  </td>\n </tr>\n <input type=\"hidden\" name=\"play\" value=\"\" id=\"hidPlayPath\">\n <input type=\"hidden\" name=\"rnd\" value=\"\" id=\"hidPlayerRnd\">\n <input type=\"hidden\" name=\"pd\" value=\"pz_Y29tbWFuZHM6e3BhcmVudF9pdGVtPTF9pz_YXBwbGljYXRpb246e2FjdGlvbj1tZW51fQ%3D%3Dpz_\">\n<input type=\"hidden\" name=\"md\" value=\"app_player\">\n<input type=\"hidden\" name=\"inst\" value=\"\">\n</FORM><!-- modified -->\n</table>\n<!-- end of file ./templates/app_player/action_usual.html -->\n\n\n<!-- end of file ./templates/app_player/app_player.html --><!-- end of file inner_code -->', '2014-10-30 15:42:22', 0, 0),
(75, '<#LANG_MODULE_LAYOUTS#>', '', '', 0, 0, 86, '75', '86', 1000, '', 0, 'custom', 0, 0, '0', 0, '', '', '', '', '', '[#module name=\"layouts\"#]', 0, 60, '', '', 0, 0, 0, 0, 'Домашние страницы', '<!-- begin of file inner_code --><!-- begin of file ./templates/layouts/layouts.html -->\n\n\n<!-- Frontend -->\n <!-- begin of file ./templates/layouts/action_usual.html -->\n\n <!-- action usual -->\n <!-- table layouts search -->\n \n  <!-- begin of file ./templates/layouts/layouts_search_site.html --><!-- results -->\n\n\n<ul data-role=\"listview\">\n\n <li>\n  \n  <a href=\"/page/8.html\" target=_blank>Добро пожаловать!</a>\n  \n  \n  \n  </li>\n\n <li>\n  \n  \n  \n  <a href=\"/popup/scenes.html\" target=_blank>Сцены</a>\n  \n  </li>\n\n <li>\n  \n  \n  \n  <a href=\"/popup/app_mediabrowser.html\" target=_blank>Медиа</a>\n  \n  </li>\n\n <li>\n  \n  \n  \n  <a href=\"/popup/app_calendar.html\" target=_blank>Календарь</a>\n  \n  </li>\n\n <li>\n  \n  \n  \n  <a href=\"/popup/app_products.html\" target=_blank>Продукты</a>\n  \n  </li>\n\n <li>\n  \n  \n  \n  <a href=\"/popup/app_gpstrack.html\" target=_blank>GPS</a>\n  \n  </li>\n\n</ul>\n\n<!-- end of file ./templates/layouts/layouts_search_site.html -->\n \n <!-- / table layouts search -->\n <!-- table layouts edit -->\n \n <!-- / table layouts edit -->\n <!-- table layouts view --> \n \n <!-- / table layouts view -->\n<!-- end of file ./templates/layouts/action_usual.html -->\n\n\n<!-- end of file ./templates/layouts/layouts.html --><!-- end of file inner_code -->', '2014-10-30 14:58:15', 0, 0),
(82, '<#LANG_GENERAL_EVENTS_LOG#>', '', '', 0, 0, 0, '82', '0', 10, '', 0, 'label', 0, 0, '', 0, '', '', '', '', '', '', 0, 0, '', '', 0, 0, 0, 0, 'История событий', '', '2015-12-03 14:42:25', 0, 0),
(63, '%ClockChime.time%', '', '', 0, 0, 66, '63', '86,66', 10000, '', 0, 'custom', 0, 0, '0', 0, '', '', '', '', '', '%ClockChime.time%<br>\r\nhello! <a href=\"/test\">test</a>', 0, 60, '', '', 0, 0, 0, 0, '2014-10-30 14:58:00', '2014-10-30 14:58:00<br>\r\nhello! <a href=\"/test\">test</a>', '2014-10-30 14:58:15', 0, 0),
(66, 'Demo controls', '', '', 0, 0, 86, '63,67,68,69,70,71,72,73,77,78,80', '86', 0, '', 0, '', 0, 0, '0', 0, '', '', '', '', '', '', 0, 0, '', '', 0, 0, 0, 0, 'Demo controls', '', '2014-07-15 12:25:40', 0, 0),
(67, '<#LANG_LABEL#>', '', '', 0, 0, 66, '67', '86,66', 9000, '', 0, 'label', 0, 0, '0', 0, '', '', '', '', '', '', 0, 0, '', '', 0, 0, 0, 0, 'Подпись', '', '2014-09-04 16:17:08', 0, 0),
(68, 'New window', '', 'http://google.com/', 600, 600, 66, '68', '86,66', 8000, '', 0, 'window', 0, 0, '0', 0, '', '', '', '', '', '', 0, 0, '', '', 0, 0, 0, 0, 'New window', '', '2014-09-04 16:17:08', 0, 0),
(69, '<#LANG_URL#>', '', 'http://google.com/', 0, 0, 66, '69', '86,66', 7000, '', 0, 'url', 0, 0, '0', 0, '', '', '', '', '', '', 0, 0, '', '', 0, 0, 0, 0, 'Ссылка', '', '2014-09-04 16:17:08', 0, 0),
(70, 'Buttons', '', '', 0, 0, 66, '70', '86,66', 6000, '', 0, 'button', 0, 0, 'clicked', 0, '', '', '', '', '', '', 0, 0, 'say(\"Привет\");', '', 0, 0, 0, 0, 'Buttons', '', '2014-09-04 16:17:08', 0, 0),
(71, '<#LANG_ON_OFF_SWITCH#>', '', '', 0, 0, 66, '71', '86,66', 5000, '', 0, 'switch', 0, 0, '0', 0, '', '', '', '', '', '', 0, 0, '', '', 0, 0, 0, 0, 'Выключатель', '', '2014-09-04 16:17:08', 0, 0),
(72, 'Select box', '', '', 0, 0, 66, '72', '86,66', 4000, '', 0, 'selectbox', 0, 0, '2', 0, '', '', '', '', '', '1|Item 1\r\n2|Item 2\r\n3|Item 3', 0, 0, '', '', 0, 0, 0, 0, 'Select box', '1|Item 1\r\n2|Item 2\r\n3|Item 3', '2014-09-04 16:17:08', 0, 0),
(73, 'Plus minus box', '', '', 0, 0, 66, '73', '86,66', 3000, '', 0, 'plusminus', 0, 5, '3', 1, '', '', '', '', '', '', 0, 0, '', '', 0, 0, 0, 0, 'Plus minus box', '', '2014-09-04 16:17:08', 0, 0),
(74, '<#LANG_GENERAL_EVENTS_LOG#> (code)', '', '', 0, 0, 0, '74', '0', 9, '', 0, 'custom', 0, 0, '0', 0, '', '', '', '', '', '<div style=\"text-shadow:none;font-weight:normal;\">[#module name=\"shoutbox\" limit=\"10\" reverse=\"1\" mobile=\"1\"#]</div>', 0, 60, '', '', 0, 0, 0, 0, 'История событий (code)', '<!-- begin of file inner_code --><div style=\"text-shadow:none;font-weight:normal;\"><!-- begin of file ./templates/shoutbox/shoutbox.html -->\n\r\n<!-- Frontend -->\r\n \n\r\n <!-- action usual -->\r\n <!-- table shouts search -->\r\n \r\n  \r\n<!-- table shouts search -->\r\n<!-- results -->\r\n\r\n<div class=\"shout_box\">  <!-- begin shout_box -->\r\n\r\n<!-- search results (list) -->\r\n<div id=\"shoutboxContent\" class=\"shout_content\">\r\nЗагрузка......\r\n</div>\r\n<!-- / search results (list) -->\r\n<!-- / results -->\r\n\r\n\r\n<div class=\"shout_form\">\r\n<form action=\"/nf.php?pd=pz_pz_YXBwbGljYXRpb246e2FjdGlvbj1tZW51fQ%3D%3Dpz_&md=shoutbox&inst=&\" name=\"frmShoutBoxMsg\" method=\"get\" style=\"margin:0px\" onSubmit=\'return false;\'>\r\n<input type=\"text\" name=\"message\" class=\"shout_input\" placeholder=\"Команда\"><input type=\"submit\" name=\"submit\" value=\"Отправить\" onClick=\"return sendShoutMessage();\"  class=\"shout_button\">\r\n<input type=\"hidden\" name=\"pd\" value=\"pz_pz_YXBwbGljYXRpb246e2FjdGlvbj1tZW51fQ%3D%3Dpz_\">\n<input type=\"hidden\" name=\"md\" value=\"shoutbox\">\n<input type=\"hidden\" name=\"inst\" value=\"\">\n</FORM><!-- modified -->\r\n</div>\r\n\r\n</div> <!-- end shout_box -->\r\n<script language=\"javascript\">\r\n\r\nvar shoutTimer;\r\n\r\n function updateShoutContent() {\r\n  clearTimeout(shoutTimer);\r\n\r\n  var url=\"/popup/shoutbox.html?\";\r\n\r\n   url=url+\'&limit=10&getdata=1&reverse=1\';\r\n   $.ajax({\r\n    url: url,\r\n    }).done(function(data) { \r\n     if (data!=\'\') {\r\n      old_data=$(\'#shoutboxContent\').html();\r\n      if (old_data!=data) {\r\n       $(\'#shoutboxContent\').html(data);\r\n      }\r\n     }\r\n\r\n  \r\n  shoutTimer=setTimeout(\"updateShoutContent(\'\', \'\');\", 10000);\r\n  \r\n\r\n\r\n    });\r\n\r\n }\r\n\r\n function sendShoutMessage() {\r\n  if (document.frmShoutBoxMsg.message.value == \'\') return false; \r\n  var msg=URLencode(document.frmShoutBoxMsg.message.value);\r\n  document.frmShoutBoxMsg.message.value=\'\';\r\n  var url=\"/popup/shoutbox.html?\";\r\n  url=url+\'&limit=10&msg=\'+msg;\r\n  $.ajax({\r\n   url: url,\r\n   }).done(function(data) { \r\n    if (data!=\'OK\' && data!=\'\') {\r\n     $(\'#shoutboxContent\').html(data);\r\n    }\r\n   });\r\n  return false;\r\n }\r\n</script>\r\n\r\n\r\n<script language=\"javascript\">\r\n  updateShoutContent();\r\n</script>\r\n\r\n \r\n <!-- / table shouts search -->\r\n <!-- table shouts edit -->\r\n \r\n <!-- / table shouts edit -->\r\n <!-- table shouts view --> \r\n \r\n <!-- / table shouts view -->\r\n\r\n\r\n\r\n<!-- end of file ./templates/shoutbox/shoutbox.html --></div><!-- end of file inner_code -->', '2015-12-03 14:48:23', 0, 0),
(77, 'Alarm time', '', '', 0, 0, 66, '77', '86,66', 0, '', 0, 'timebox', 0, 0, '09:30', 0, 'ThisComputer', 'AlarmTime', '', '', '', '', 0, 0, '', '', 0, 0, 0, 0, 'Alarm time', '', '2014-09-04 16:17:08', 0, 0),
(78, '<#LANG_TEXT_BOX#>', '', '', 0, 0, 66, '78', '86,66', 0, '', 0, 'textbox', 0, 0, '0', 0, 'ThisComputer', 'textBoxTest', '', '', '', '', 0, 10, '', '', 0, 0, 0, 0, 'Текстовое поле', '', '2014-10-30 14:58:15', 0, 0),
(80, '<#LANG_SLIDER_BOX#>', '', '', 0, 0, 66, '80', '86,66', 0, '', 0, 'sliderbox', 0, 10, '0', 1, 'ThisComputer', 'textBoxTest', '', '', '', '', 0, 5, '', '', 0, 0, 0, 0, 'Слайдер', '', '2014-10-30 14:58:15', 0, 0),
(86, '<#LANG_GENERAL_SERVICE#>', '', '', 0, 0, 0, '75,66,63,67,68,69,70,71,72,73,77,78,80,98,99', '0', 2, '', 0, '', 0, 0, '', 0, '', '', '', '', '', '', 0, 0, '', '', 0, 0, 0, 0, 'Сервис', '', '2015-12-03 14:42:25', 0, 0),
(106, '<#LANG_GENERAL_CONTROL#>', '', '', 0, 0, 0, '108', '0', 7000, '', 0, '', 0, 0, '', 0, '', '', '', '', '', '', 0, 0, '', '', 0, 0, 0, 1, 'Управление', '', '2015-12-03 14:42:25', 0, 0),
(114, 'Admin (status)', '', '', 0, 0, 110, '114', '110', 950, '', 0, 'custom', 0, 0, '', 0, '', '', '', '', '', '%Admin.seenAt% (%Admin.CoordinatesUpdated%)\r\n<br><br>\r\n<a href=\"http://maps.google.com/maps?q=loc:%Admin.Coordinates%\" target=_blank><img src=\"http://maps.googleapis.com/maps/api/staticmap?center=%Admin.Coordinates%&size=300x300&maptype=hybrid&sensor=false&zoom=16&markers=%Admin.Coordinates%\"></a>', 0, 0, '', '', 0, 0, 0, 0, 'Admin (status)', ' ()\r\n<br><br>\r\n<a href=\"http://maps.google.com/maps?q=loc:\" target=_blank><img src=\"http://maps.googleapis.com/maps/api/staticmap?center=&size=300x300&maptype=hybrid&sensor=false&zoom=16&markers=\"></a>', '2014-10-30 15:40:58', 0, 0),
(91, '<#LANG_GENERAL_CLIMATE#> (<#LANG_GENERAL_OUTSIDE#>: %ThisComputer.TempOutside%°C / <#LANG_GENERAL_INSIDE#>: %Livingroom.Temperature%°C)', '', '', 0, 0, 0, '92,93,104,105', '0', 8000, '', 0, '', 0, 0, '', 0, '', '', '', '', '', '', 0, 60, '', '', 0, 0, 0, 1, 'Климат (На улице: -0.9°C / Дома: 22.4°C)', '', '2015-12-03 14:43:28', 0, 0),
(92, '<#LANG_GENERAL_WEATHER_FORECAST#> (code)', '', '', 0, 0, 91, '92', '91', 1000, '', 0, 'custom', 0, 0, '', 0, '', '', '', '', '', '%ThisComputer.weatherFull%', 0, 0, '', '', 0, 0, 0, 0, 'Прогноз погоды (code)', '\n<b>Сегодня:</b><br />\nднем: +0&deg;...+2&deg;, пасмурно, туман, ночью: +0&deg;...-2&deg;, переменная облачность, туман, ветер: ЮЗ — 3-5 м/с, давление: 770 мм.рт.ст, влажность: 100%<br />\n<br />\n<b>Завтра:</b><br />\nднем: +4&deg;...+6&deg;, пасмурно, ночью: +2&deg;...+4&deg;, пасмурно, без существенных осадков, ветер: Ю — 6-8 м/с, давление: 768 мм.рт.ст, влажность: 100%<br />\n<br />\n<br />\n', '2015-12-03 14:43:28', 0, 0),
(93, '<#LANG_GENERAL_WEATHER_FORECAST#>', '', '', 0, 0, 91, '93', '91', 1001, '', 0, 'label', 0, 0, '', 0, '', '', '', '', '', '', 0, 0, '', '', 0, 0, 0, 0, 'Прогноз погоды', '', '2015-12-03 14:42:25', 0, 0),
(104, '<#LANG_GENERAL_INSIDE#>', '', '', 0, 0, 91, '104', '91', 2000, '', 0, 'label', 0, 0, '', 0, '', '', '', '', '', '', 0, 0, '', '', 0, 0, 0, 0, 'Дома', '', '2015-12-03 14:42:25', 0, 0),
(105, '<#LANG_GENERAL_INSIDE#> (data)', '', '', 0, 0, 91, '105', '91', 1900, '', 0, 'custom', 0, 0, '', 0, '', '', '', '', '', 'Livingroom: %Livingroom.Temperature%&deg;C / %Livingroom.Humidity%%', 0, 30, '', '', 0, 0, 0, 0, 'Дома (data)', 'Livingroom: 22.4&deg;C / 42%', '2015-12-03 14:43:28', 0, 0),
(97, 'State', '', '', 0, 0, 0, '97', '0', 100010, '', 0, 'custom', 0, 0, '', 0, '', '', '', '', '', '<big style=\"font-size:24px\">%ThisComputer.timeNow%</big>\r\n\r\n<img src=\"/img/icons/status/lock_32_%Security.stateColor%.png\" align=\"absmiddle\"> %Security.stateDetails%\r\n<img src=\"/img/icons/status/system_32_%System.stateColor%.png\" align=\"absmiddle\"> %System.stateDetails%\r\n<img src=\"/img/icons/status/network_32_%Communication.stateColor%.png\" align=\"absmiddle\"> %Communication.stateDetails%\r\n<br/>\r\n%ThisComputer.somebodyHomeText%\r\n<br/>\r\nAdmin -- %Admin.seenAt% (%Admin.CoordinatesUpdated%)', 0, 10, '', '', 0, 0, 0, 0, 'State', '<big style=\"font-size:24px\">14:48</big>\r\n\r\n<img src=\"/img/icons/status/lock_32_green.png\" align=\"absmiddle\"> \r\n<img src=\"/img/icons/status/system_32_green.png\" align=\"absmiddle\"> \r\n<img src=\"/img/icons/status/network_32_green.png\" align=\"absmiddle\"> \r\n<br/>\r\n\r\n<br/>\r\nAdmin -- Home (10:00)', '2015-12-03 14:48:02', 0, 0),
(98, '<#LANG_SECTION_SETTINGS#>', '', '', 0, 0, 86, '99', '86', 20000, '', 0, '', 0, 0, '', 0, '', '', '', '', '', '', 0, 0, '', '', 0, 0, 0, 0, 'Настройки', '', '2014-08-25 17:04:27', 0, 0),
(99, 'Говорить время', '', '', 0, 0, 98, '99', '86,98', 1000, '', 0, 'switch', 0, 0, '1', 0, 'ThisComputer', 'clockChimeEnabled', '', '', '', '', 0, 0, '', '', 0, 0, 0, 0, 'Говорить время', '', '2014-08-25 17:04:33', 0, 0),
(108, 'Switch 1', '', '', 0, 0, 106, '108', '106', 0, '', 0, 'switch', 0, 0, '0', 0, 'Switch1', 'status', '', 'refresh', '', '', 0, 300, '', '', 0, 0, 0, 0, 'Switch 1', '', '2014-10-31 15:30:26', 0, 0),
(110, '<#LANG_MODULE_USERS#>', '', '', 0, 0, 0, '114,111', '0', 5000, '', 0, '', 0, 0, '', 0, '', '', '', '', '', '', 0, 0, '', '', 0, 0, 0, 0, 'Пользователи', '', '2015-12-03 14:42:25', 0, 0),
(111, 'Admin', '', '', 0, 0, 110, '111', '110', 1000, '', 0, 'label', 0, 0, '', 0, '', '', '', '', '', '', 0, 0, '', '', 0, 0, 0, 0, 'Admin', '', '2014-10-30 15:40:58', 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `country`
--

CREATE TABLE `country` (
  `COUNTRY_ID` int(10) NOT NULL,
  `COUNTRY_GUID` varchar(48) NOT NULL,
  `COUNTRY_NAME` varchar(64) NOT NULL,
  `LM_DATE` datetime NOT NULL,
  `COUNTRY_CODE` varchar(8) DEFAULT NULL,
  `COUNTRY_PHONE_CODE` varchar(8) DEFAULT NULL,
  `LATITUDE` float(18,5) DEFAULT NULL,
  `LONGITUDE` float(18,5) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `country`
--

INSERT INTO `country` (`COUNTRY_ID`, `COUNTRY_GUID`, `COUNTRY_NAME`, `LM_DATE`, `COUNTRY_CODE`, `COUNTRY_PHONE_CODE`, `LATITUDE`, `LONGITUDE`) VALUES
(1, '25459617-F4D3-EB0A-6777-2D7CB5F876B9', 'Andorra', '2015-04-14 12:09:36', 'AD', '376', NULL, NULL),
(2, '24E45AEB-7FD7-783A-2008-F5A35CA064F7', 'United Arab Emirates', '2015-04-14 12:09:36', 'AE', '971', NULL, NULL),
(3, '3F039DE6-2908-4731-96FE-041E04A7E962', 'Afghanistan', '2015-04-14 12:09:36', 'AF', '93', NULL, NULL),
(4, 'F1FEB2CF-618A-59D0-0DDC-B4F8B4ED976E', 'Antigua and Barbuda', '2015-04-14 12:09:36', 'AG', '1268', NULL, NULL),
(5, '909E8639-771A-D2CD-4405-7A8368A9D04A', 'Anguilla', '2015-04-14 12:09:36', 'AI', '1264', NULL, NULL),
(6, '35173F09-0E98-0C3E-77A6-2AD8F8F5DE89', 'Albania', '2015-04-14 12:09:36', 'AL', '355', NULL, NULL),
(7, '632C5757-664D-108D-802F-3F62E78F41B9', 'Armenia', '2015-04-14 12:09:36', 'AM', '374', NULL, NULL),
(8, '33C045E6-E7F0-6886-FE2D-46C5EAA79042', 'Angola', '2015-04-14 12:09:36', 'AO', '244', NULL, NULL),
(9, '8E7E291E-B21C-ABAC-7791-4733C412D3CA', 'Argentina', '2015-04-14 12:09:36', 'AR', '54', NULL, NULL),
(10, 'F4EC01D6-D793-EE6C-58EA-D66DABE0E15F', 'American Samoa', '2015-04-14 12:09:36', 'AS', '1684', NULL, NULL),
(11, 'DA24D62E-C926-A91D-E11A-96AAD3E5DDE1', 'Austria', '2015-04-14 12:09:36', 'AT', '43', NULL, NULL),
(12, 'F193E97D-B13C-9506-D976-CCB52BC864BF', 'Australia', '2015-04-14 12:09:36', 'AU', '61', NULL, NULL),
(13, '363B89ED-E346-3EEE-67E1-77A9ACA104BE', 'Aruba', '2015-04-14 12:09:36', 'AW', '297', NULL, NULL),
(14, '0129859C-6BC4-C7C6-F0E7-212E8BFFE04F', 'Åland Islands', '2015-04-14 12:09:36', 'AX', '358', NULL, NULL),
(15, '96C23A30-8A7D-D545-6E33-1EF6070EAA2A', 'Azerbaijan', '2015-04-14 12:09:36', 'AZ', '994', NULL, NULL),
(16, '1C37B968-7D44-2AED-7171-116232E0376F', 'Bosnia and Herzegovina', '2015-04-14 12:09:36', 'BA', '387', NULL, NULL),
(17, '784A4C7F-8B44-353A-8B27-996E1B38791C', 'Barbados', '2015-04-14 12:09:36', 'BB', '1246', NULL, NULL),
(18, '735D20D8-45D8-B861-6B2B-9F1B22F86447', 'Bangladesh', '2015-04-14 12:09:36', 'BD', '880', NULL, NULL),
(19, '3B71F5B5-2AC2-9154-404D-191ABFBC4729', 'Belgium', '2015-04-14 12:09:36', 'BE', '32', NULL, NULL),
(20, 'B5CB258B-4F64-D109-E9D5-60A78ED0A637', 'Burkina Faso', '2015-04-14 12:09:36', 'BF', '226', NULL, NULL),
(21, '1B6E6DAD-F17E-C6C0-61A4-41B20394BDBE', 'Bulgaria', '2015-04-14 12:09:36', 'BG', '359', NULL, NULL),
(22, '6AB06D58-6177-06A7-0937-AA363E22FE30', 'Bahrain', '2015-04-14 12:09:36', 'BH', '973', NULL, NULL),
(23, 'D1581E51-B2B3-8078-F5EC-8463B5484393', 'Burundi', '2015-04-14 12:09:36', 'BI', '257', NULL, NULL),
(24, '28966864-22F6-35BD-5145-1B79E5FE95C0', 'Benin', '2015-04-14 12:09:36', 'BJ', '229', NULL, NULL),
(25, 'A0910149-4E0D-D1EF-9280-90C7CA4F713A', 'Saint Barthelemy', '2015-04-14 12:09:36', 'BL', '590', NULL, NULL),
(26, 'D10E4FAB-B35B-36E2-1B11-BD87D19D493E', 'Bermuda', '2015-04-14 12:09:36', 'BM', '1441', NULL, NULL),
(27, '2EF66919-5A42-5E57-9540-254370BCFA7F', 'Brunei', '2015-04-14 12:09:36', 'BN', '673', NULL, NULL),
(28, 'B17DC404-815D-16D1-3EE6-01CFF6108DA6', 'Bolivia', '2015-04-14 12:09:36', 'BO', '597', NULL, NULL),
(29, '1677F424-62EB-CDCA-59AE-82E6BE4CD643', 'Bonaire, Sint Eustatius and Saba', '2015-04-14 12:09:36', 'BQ', '599', NULL, NULL),
(30, '364A13CD-9DD4-EB69-B481-CAF5C67AD0F3', 'Brazil', '2015-04-14 12:09:36', 'BR', '55', NULL, NULL),
(31, '3287034B-9EF0-21E6-290F-4C1435488B14', 'Bahamas', '2015-04-14 12:09:36', 'BS', '1242', NULL, NULL),
(32, '3ECFA6CC-B473-444F-60B2-F2C325726A59', 'Bhutan', '2015-04-14 12:09:36', 'BT', '975', NULL, NULL),
(33, '5CB7E864-AA89-D6A6-6483-5A0B4BF4ECE7', 'Botswana', '2015-04-14 12:09:36', 'BW', '267', NULL, NULL),
(34, '549B645E-602C-E8DC-E661-0BC58B872507', 'Belarus', '2015-04-14 12:09:36', 'BY', '375', NULL, NULL),
(35, '6EBC4137-4D87-7153-6380-1917E5AB5CC8', 'Belize', '2015-04-14 12:09:36', 'BZ', '501', NULL, NULL),
(36, '62B9E5BF-CD36-7BF9-8C86-7145E0BB87C3', 'Canada', '2015-04-14 12:09:36', 'CA', '1', NULL, NULL),
(37, 'CE623909-C6BB-FBDB-AAC4-E93B1905A157', 'Cocos (Keeling) Islands', '2015-04-14 12:09:36', 'CC', '61', NULL, NULL),
(38, 'EE5B1165-2914-906F-FC6F-CAAE4D346925', 'Democratic Republic of the Congo', '2015-04-14 12:09:36', 'CD', '243', NULL, NULL),
(39, '2DAEE215-8156-00B0-9BCF-2A11D900E923', 'Central African Republic', '2015-04-14 12:09:36', 'CF', '236', NULL, NULL),
(40, 'EDADCB74-E7C3-1081-29BA-4F59B60FF6B2', 'Republic of the Congo', '2015-04-14 12:09:36', 'CG', '242', NULL, NULL),
(41, '5024DE57-E5BB-5A9D-917D-26A1749B8D60', 'Switzerland', '2015-04-14 12:09:36', 'CH', '41', NULL, NULL),
(42, 'ABA5802C-8FE2-211F-8002-BB96E7A364DB', 'Ivory Coast', '2015-04-14 12:09:36', 'CI', '225', NULL, NULL),
(43, '00C8EB2B-8085-1D54-BE4D-35EF87A255E6', 'Cook Islands', '2015-04-14 12:09:36', 'CK', '682', NULL, NULL),
(44, '834BD2B5-93D2-17A7-91BE-CE4DEEC43366', 'Chile', '2015-04-14 12:09:36', 'CL', '56', NULL, NULL),
(45, '28A21890-3B5D-8B00-9EBE-DED06FA1ECFB', 'Cameroon', '2015-04-14 12:09:36', 'CM', '237', NULL, NULL),
(46, 'CE0E5440-B3C0-12A5-1BA3-2387A270C634', 'China', '2015-04-14 12:09:36', 'CN', '86', NULL, NULL),
(47, '622727AA-9C09-1CBB-9C8F-5F23E4F6E7AF', 'Colombia', '2015-04-14 12:09:36', 'CO', '57', NULL, NULL),
(48, 'D3860772-0731-CCE8-3F3C-8DFC229DA443', 'Costa Rica', '2015-04-14 12:09:36', 'CR', '506', NULL, NULL),
(49, 'A394D714-E73A-8327-EB0C-C1A03AD5B75C', 'Cuba', '2015-04-14 12:09:36', 'CU', '53', NULL, NULL),
(50, '38DE8E73-B254-F18B-098D-FA063DB064DE', 'Cape Verde', '2015-04-14 12:09:36', 'CV', '238', NULL, NULL),
(51, 'E05C1047-00F5-9447-4AA4-F4D231A60CDF', 'Christmas Island', '2015-04-14 12:09:36', 'CX', '61', NULL, NULL),
(52, 'E29DE5EF-A293-9921-DB69-1AE56C94789C', 'Curaçao', '2015-04-14 12:09:36', 'CW', '', NULL, NULL),
(53, '91F38946-68B4-F218-A93C-87F8018AD49A', 'Cyprus', '2015-04-14 12:09:36', 'CY', '357', NULL, NULL),
(54, '673F53C2-5C02-9B22-E956-29879756BF15', 'Czech Republic', '2015-04-14 12:09:36', 'CZ', '420', NULL, NULL),
(55, '60C952C3-0504-DC75-9E1D-499EC374B0F0', 'Germany', '2015-04-14 12:09:36', 'DE', '49', NULL, NULL),
(56, '1A3A54C9-F2E1-3E5F-079C-999D110E8EEC', 'Djibouti', '2015-04-14 12:09:36', 'DJ', '253', NULL, NULL),
(57, '7547B15D-E21A-4A2E-5F9B-1DC727C53F4A', 'Denmark', '2015-04-14 12:09:36', 'DK', '45', NULL, NULL),
(58, '7E2CF84D-1879-374A-6A41-F03318A3FB25', 'Dominica', '2015-04-14 12:09:36', 'DM', '1767', NULL, NULL),
(59, 'A07E10D2-DB62-436D-E203-A884ED728CBC', 'Dominican Republic', '2015-04-14 12:09:36', 'DO', '1809', NULL, NULL),
(60, '6CFEBD8D-4ADA-B4FF-9160-90D0C2B2118E', 'Algeria', '2015-04-14 12:09:36', 'DZ', '213', NULL, NULL),
(61, 'BC4A1696-47D1-2617-ECF6-824BADEF4C4E', 'Ecuador', '2015-04-14 12:09:36', 'EC', '593', NULL, NULL),
(62, 'AFF8DFA6-F5EE-6A43-DC59-8213D20E198A', 'Estonia', '2015-04-14 12:09:36', 'EE', '372', NULL, NULL),
(63, 'ED2AC84C-40C7-F14D-378E-16EB10094294', 'Egypt', '2015-04-14 12:09:36', 'EG', '20', NULL, NULL),
(64, '25C7A12B-226A-8989-F33B-98FEAB37B18F', 'Western Sahara', '2015-04-14 12:09:36', 'EH', '', NULL, NULL),
(65, '0C0F61A7-CED3-882E-440F-0B8B6ED939DB', 'Eritrea', '2015-04-14 12:09:36', 'ER', '291', NULL, NULL),
(66, '81387FC3-0AD1-42E7-241E-2AA939641B95', 'Spain', '2015-04-14 12:09:36', 'ES', '34', NULL, NULL),
(67, '7D8BD697-9E59-4861-4BE9-61A8AD8B4936', 'Ethiopia', '2015-04-14 12:09:36', 'ET', '251', NULL, NULL),
(68, '561A54FF-8F65-8B92-483E-2DBE528429A4', 'Finland', '2015-04-14 12:09:36', 'FI', '358', NULL, NULL),
(69, '4D930C83-DEFD-ADEA-0D64-501B4E4911CF', 'Fiji', '2015-04-14 12:09:36', 'FJ', '679', NULL, NULL),
(70, '3EF989F2-6700-B8DA-F2B5-B4A831B30BCD', 'Falkland Islands', '2015-04-14 12:09:36', 'FK', '500', NULL, NULL),
(71, '6E21E535-BF55-E53C-3CAC-7C2B621EF05B', 'Micronesia', '2015-04-14 12:09:36', 'FM', '691', NULL, NULL),
(72, '25F1AC03-B255-50A9-654C-E043D61E7DA1', 'Faroe Islands', '2015-04-14 12:09:36', 'FO', '298', NULL, NULL),
(73, '06A3DB9C-B358-74A7-BBA2-99F760FDE24E', 'France', '2015-04-14 12:09:36', 'FR', '33', NULL, NULL),
(74, '7D2D339B-5568-2B6C-28F6-1CAD95AE685E', 'Gabon', '2015-04-14 12:09:36', 'GA', '241', NULL, NULL),
(75, '73D1D5BB-571F-08E7-2A4F-4AB63AA9793C', 'United Kingdom', '2015-04-14 12:09:36', 'GB', '44', NULL, NULL),
(76, '52764C53-FF3F-BA7C-14BF-7E6262C26BB6', 'Grenada', '2015-04-14 12:09:36', 'GD', '1473', NULL, NULL),
(77, 'ABAFEB9B-9DB0-5261-4625-53C6A32A8392', 'Georgia', '2015-04-14 12:09:36', 'GE', '995', NULL, NULL),
(78, '93A6ABF8-C217-B219-0AEF-F63F51330113', 'French Guiana', '2015-04-14 12:09:36', 'GF', '', NULL, NULL),
(79, 'DD5B81B6-DF46-6B93-40DD-32B63B1C4482', 'Guernsey', '2015-04-14 12:09:36', 'GG', '', NULL, NULL),
(80, 'AE89DC78-DD22-0165-1F4A-37757DC2C6A6', 'Ghana', '2015-04-14 12:09:36', 'GH', '233', NULL, NULL),
(81, '691A673C-A72B-45C6-EBA5-AE4D48128B0C', 'Gibraltar', '2015-04-14 12:09:36', 'GI', '350', NULL, NULL),
(82, 'E1C9DDCE-DFC6-C80E-9E6D-2CFACD5A3EF3', 'Greenland', '2015-04-14 12:09:36', 'GL', '299', NULL, NULL),
(83, 'DC6434F3-07BD-D0AF-5EF1-021830DD761A', 'Gambia', '2015-04-14 12:09:36', 'GM', '220', NULL, NULL),
(84, '0EB68AA9-0863-564B-E8B9-7BE51B19C627', 'Guinea', '2015-04-14 12:09:36', 'GN', '224', NULL, NULL),
(85, '64BAD731-A2B6-D774-4382-6A3F69041EB2', 'Guadeloupe ', '2015-04-14 12:09:36', 'GP', '', NULL, NULL),
(86, '72FFFB07-88AA-6CA5-C9A1-664AAE69E39F', 'Equatorial Guinea', '2015-04-14 12:09:36', 'GQ', '240', NULL, NULL),
(87, 'EBC8E95D-EFA7-A62F-E9D0-1230876D82AD', 'Greece', '2015-04-14 12:09:36', 'GR', '30', NULL, NULL),
(88, '589A3D09-2DAA-54DD-B119-8A8D0CD9E9B1', 'South Georgia and the South Sandwich Islands', '2015-04-14 12:09:36', 'GS', '', NULL, NULL),
(89, '6119B99F-E4B7-0B13-45F3-293FD0A84862', 'Guatemala', '2015-04-14 12:09:36', 'GT', '502', NULL, NULL),
(90, '6BC5F673-1D11-85D4-FF9E-3C43604EC2D0', 'Guam', '2015-04-14 12:09:36', 'GU', '1671', NULL, NULL),
(91, 'D94C617D-E792-CAAF-2E00-5A8EEA208C8C', 'Guinea-Bissau', '2015-04-14 12:09:36', 'GW', '245', NULL, NULL),
(92, '9313337B-D700-D83C-CA29-5F48D8726308', 'Guyana', '2015-04-14 12:09:36', 'GY', '592', NULL, NULL),
(93, 'CEFBA783-88BA-FFDE-77F3-43B3FF801484', 'Hong Kong', '2015-04-14 12:09:36', 'HK', '852', NULL, NULL),
(94, 'CBA2E631-D4D1-B735-81D3-5DAC86AE8C76', 'Honduras', '2015-04-14 12:09:36', 'HN', '504', NULL, NULL),
(95, 'FEA02A43-5185-C6A7-A26C-935578D43E6D', 'Croatia', '2015-04-14 12:09:36', 'HR', '385', NULL, NULL),
(96, '02927AD4-8894-FE99-C9DD-E0E7F4DC63B1', 'Haiti', '2015-04-14 12:09:36', 'HT', '509', NULL, NULL),
(97, '42405C6A-097F-BAF2-93A6-86513986110A', 'Hungary', '2015-04-14 12:09:36', 'HU', '36', NULL, NULL),
(98, '79798E7A-4327-469A-5EC1-81319DDC1E90', 'Indonesia', '2015-04-14 12:09:36', 'ID', '62', NULL, NULL),
(99, '2A8985F3-8891-5685-A0FF-972A5DF8620B', 'Ireland', '2015-04-14 12:09:36', 'IE', '353', NULL, NULL),
(100, 'EEBDEB95-6B76-B2B0-F2C9-2BFB1499E9FD', 'Israel', '2015-04-14 12:09:36', 'IL', '972', NULL, NULL),
(101, '71739793-0365-6479-F9A2-9B6F57239816', 'Isle of Man', '2015-04-14 12:09:36', 'IM', '44', NULL, NULL),
(102, '18AC27F9-4025-C8CF-DA36-36004B0C5CF3', 'India', '2015-04-14 12:09:36', 'IN', '91', NULL, NULL),
(103, '01BB783C-2123-8D92-36E3-397F1E9D5F7D', 'Iraq', '2015-04-14 12:09:36', 'IQ', '964', NULL, NULL),
(104, '422B82CA-FC9F-6FFF-151E-E342CBD26A32', 'Iran', '2015-04-14 12:09:36', 'IR', '98', NULL, NULL),
(105, 'CD0DAF28-6A3C-0CE3-1A06-218A7AA11408', 'Iceland', '2015-04-14 12:09:36', 'IS', '354', NULL, NULL),
(106, '8BA4B4DC-9E82-331B-7EE2-6577413FCFDE', 'Italy', '2015-04-14 12:09:36', 'IT', '39', NULL, NULL),
(107, 'D8777867-1C0B-ACF1-337A-6DD4B68B902B', 'Jersey', '2015-04-14 12:09:36', 'JE', '', NULL, NULL),
(108, '0DEFE08A-DFF2-7222-F6BC-C10BB2C2B3E3', 'Jamaica', '2015-04-14 12:09:36', 'JM', '1876', NULL, NULL),
(109, '8864615C-5A8F-56C1-262C-97CB8739E154', 'Jordan', '2015-04-14 12:09:36', 'JO', '962', NULL, NULL),
(110, 'A8DFE7EB-BA50-DF73-C88A-EB7A83636C98', 'Japan', '2015-04-14 12:09:36', 'JP', '81', NULL, NULL),
(111, 'D6F5CF13-36E4-E7D7-A6AE-32D622E2E905', 'Kenya', '2015-04-14 12:09:36', 'KE', '254', NULL, NULL),
(112, '6C1BE74C-56D3-ADFE-B75E-4DD174378C3B', 'Kyrgyzstan', '2015-04-14 12:09:36', 'KG', '996', NULL, NULL),
(113, '88F59F28-C9DC-F8B5-0AA3-6EE2F5BA2680', 'Cambodia', '2015-04-14 12:09:36', 'KH', '855', NULL, NULL),
(114, '221ACA06-D996-AA61-8EF8-A883B5EF088D', 'Kiribati', '2015-04-14 12:09:36', 'KI', '686', NULL, NULL),
(115, '50711EC0-9C17-A53D-C8D5-7BFCB17B9F74', 'Comoros', '2015-04-14 12:09:36', 'KM', '269', NULL, NULL),
(116, '5A63C8A4-AED9-E465-C42D-6B35D815788E', 'Saint Kitts and Nevis', '2015-04-14 12:09:36', 'KN', '1869', NULL, NULL),
(117, 'E2231181-F5A7-9C0D-A07D-DF498953BDB7', 'North Korea', '2015-04-14 12:09:36', 'KP', '850', NULL, NULL),
(118, '51E443A4-BBB7-45B3-BCCB-5D900AAC9A10', 'South Korea', '2015-04-14 12:09:36', 'KR', '82', NULL, NULL),
(119, '163246AC-AF1E-7EF1-ABD7-CA7C76048C0D', 'Kuwait', '2015-04-14 12:09:36', 'KW', '965', NULL, NULL),
(120, '627EF7AE-33FA-CBB0-5165-B69DE5D88639', 'Cayman Islands', '2015-04-14 12:09:36', 'KY', '1345', NULL, NULL),
(121, '4AF2B679-80E6-AE7A-6F8C-E30BB82170CB', 'Kazakhstan', '2015-04-14 12:09:36', 'KZ', '7', NULL, NULL),
(122, '5B49382E-7792-4FB2-5841-BDF46D215FB1', 'Laos', '2015-04-14 12:09:36', 'LA', '856', NULL, NULL),
(123, '4F9A2D48-4692-A93C-6A6C-A450F6B15B52', 'Lebanon', '2015-04-14 12:09:36', 'LB', '961', NULL, NULL),
(124, 'A969C15C-FDB8-B9BF-7A70-CA0B20F6E746', 'Saint Lucia', '2015-04-14 12:09:36', 'LC', '1758', NULL, NULL),
(125, 'D3A607AA-9E18-3D77-0504-8FB3762B08E9', 'Liechtenstein', '2015-04-14 12:09:36', 'LI', '423', NULL, NULL),
(126, '08186EF5-A816-5F3F-6334-2ACAFFC1036F', 'Sri Lanka', '2015-04-14 12:09:36', 'LK', '94', NULL, NULL),
(127, 'FEB0132D-0FA3-0A05-3088-59E88DFB6FCC', 'Liberia', '2015-04-14 12:09:36', 'LR', '231', NULL, NULL),
(128, 'D82F3778-BA0B-F2D7-87F1-F455816C0B10', 'Lesotho', '2015-04-14 12:09:36', 'LS', '266', NULL, NULL),
(129, 'ACC7107B-FEE4-C49E-AB16-5BBADE9707BB', 'Lithuania', '2015-04-14 12:09:36', 'LT', '370', NULL, NULL),
(130, 'D46FBEE9-CA64-2513-460C-6DE7B82E70AD', 'Luxembourg', '2015-04-14 12:09:36', 'LU', '352', NULL, NULL),
(131, 'EF9EF912-DBDC-2A60-3ABA-8F6413AB376C', 'Latvia', '2015-04-14 12:09:36', 'LV', '371', NULL, NULL),
(132, '331657A7-D80A-E3D9-306B-7B04580BE97A', 'Libya', '2015-04-14 12:09:36', 'LY', '218', NULL, NULL),
(133, '6F6551A5-A0D2-9614-2570-DB098D41795B', 'Morocco', '2015-04-14 12:09:36', 'MA', '212', NULL, NULL),
(134, 'D2C3C719-CFEF-92F9-1C75-4674404E2129', 'Monaco', '2015-04-14 12:09:36', 'MC', '377', NULL, NULL),
(135, 'AF24CA6E-08CF-9103-A408-198419511606', 'Moldova', '2015-04-14 12:09:36', 'MD', '373', NULL, NULL),
(136, '0B9CDE3D-E309-F36C-6C92-7A5A3CDB5C78', 'Montenegro', '2015-04-14 12:09:36', 'ME', '382', NULL, NULL),
(137, '7E7C60D7-8187-45FF-5856-FD4FE0A9F7F2', 'Saint Martin', '2015-04-14 12:09:36', 'MF', '1599', NULL, NULL),
(138, 'D0D90087-2CB8-BBF9-A0A7-7B9F83F0842A', 'Madagascar', '2015-04-14 12:09:36', 'MG', '261', NULL, NULL),
(139, '2148A143-2703-2688-50AC-4D449CC478C6', 'Marshall Islands', '2015-04-14 12:09:36', 'MH', '692', NULL, NULL),
(140, '4EDCA486-8363-D905-2223-0DD536C64FB1', 'Macedonia', '2015-04-14 12:09:36', 'MK', '389', NULL, NULL),
(141, 'D10BC835-779F-63B5-CD7F-77715C93EF5B', 'Mali', '2015-04-14 12:09:36', 'ML', '223', NULL, NULL),
(142, 'C6DE57ED-3F9E-5ECE-BED8-750D635B32D6', 'Burma (Myanmar)', '2015-04-14 12:09:36', 'MM', '95', NULL, NULL),
(143, '79B936B1-2C70-1B2B-CF22-5DF0EDA7BFF6', 'Mongolia', '2015-04-14 12:09:36', 'MN', '976', NULL, NULL),
(144, '340E0027-3A88-B6EF-1389-9830FF0111A1', 'Macau', '2015-04-14 12:09:36', 'MO', '853', NULL, NULL),
(145, '79F9CD19-DD9D-215E-C834-6896D595D1DB', 'Northern Mariana Islands', '2015-04-14 12:09:36', 'MP', '1670', NULL, NULL),
(146, '82B9D34C-D138-D44F-C78F-66E27B0B9DB5', 'Martinique', '2015-04-14 12:09:36', 'MQ', '', NULL, NULL),
(147, 'DE5DF1CD-0634-464F-0417-6E78249A9EFF', 'Mauritania', '2015-04-14 12:09:36', 'MR', '222', NULL, NULL),
(148, '6185D85F-EFD1-F321-5C88-FD92070CC6F9', 'Montserrat', '2015-04-14 12:09:36', 'MS', '1664', NULL, NULL),
(149, 'E06F0201-CA6B-EF55-EE95-7422AE9455EA', 'Malta', '2015-04-14 12:09:36', 'MT', '356', NULL, NULL),
(150, 'FB9AE8C0-1A45-2E86-6787-73467681C533', 'Mauritius', '2015-04-14 12:09:36', 'MU', '230', NULL, NULL),
(151, 'CCCFD4DB-F847-EFB5-A954-95C3E84C8810', 'Maldives', '2015-04-14 12:09:36', 'MV', '960', NULL, NULL),
(152, 'BAD2B0BE-60A9-CBF1-8650-F709F3F71937', 'Malawi', '2015-04-14 12:09:36', 'MW', '265', NULL, NULL),
(153, '8FB4F911-221D-C172-CD4A-2E3DDFC91E31', 'Mexico', '2015-04-14 12:09:36', 'MX', '52', NULL, NULL),
(154, '21DE1250-CA05-4ABE-5402-5AF7CEDEF190', 'Malaysia', '2015-04-14 12:09:36', 'MY', '60', NULL, NULL),
(155, '05CBFC51-7A5E-C473-BD2D-64E99EEF31AE', 'Mozambique', '2015-04-14 12:09:36', 'MZ', '258', NULL, NULL),
(156, '0CAA4FA5-DC6D-601A-001C-6D44E0DC5317', 'Namibia', '2015-04-14 12:09:36', 'NA', '264', NULL, NULL),
(157, '66330F5B-F3BC-C59D-E138-81AD487DCF5E', 'New Caledonia', '2015-04-14 12:09:36', 'NC', '687', NULL, NULL),
(158, 'D691B3AE-3239-514F-3A7C-82167837EA79', 'Niger', '2015-04-14 12:09:36', 'NE', '227', NULL, NULL),
(159, '0F9225AD-F626-4D0B-6201-E299B8BD215A', 'Norfolk Island', '2015-04-14 12:09:36', 'NF', '', NULL, NULL),
(160, '146B312A-59D0-71F1-FE10-6DF2E9FF3B13', 'Nigeria', '2015-04-14 12:09:36', 'NG', '234', NULL, NULL),
(161, 'D1B612B1-9C6B-C0FA-D185-9EF37FDD5B6B', 'Nicaragua', '2015-04-14 12:09:36', 'NI', '505', NULL, NULL),
(162, 'DBAE55F1-92C1-618E-32DD-FCFBCA5287F2', 'Netherlands', '2015-04-14 12:09:36', 'NL', '31', NULL, NULL),
(163, 'AA1566AC-5FBB-4165-5297-E3D1F56D23A2', 'Norway', '2015-04-14 12:09:36', 'NO', '47', NULL, NULL),
(164, '4BF12324-809E-BB44-C6B9-B7FCA9DC0160', 'Nepal', '2015-04-14 12:09:36', 'NP', '977', NULL, NULL),
(165, 'BE8F1645-08F9-9FBD-5885-1C0C6BC93848', 'Niue', '2015-04-14 12:09:36', 'NU', '683', NULL, NULL),
(166, '8761839C-0257-21D2-C497-382E5888F299', 'New Zealand', '2015-04-14 12:09:36', 'NZ', '64', NULL, NULL),
(167, '8AAA2CE8-5DDF-EE89-F73E-4757ED7AC246', 'Oman', '2015-04-14 12:09:36', 'OM', '968', NULL, NULL),
(168, 'CFFD5046-148C-6ED1-B68E-7A0FD7C591B7', 'Panama', '2015-04-14 12:09:36', 'PA', '507', NULL, NULL),
(169, 'CBF62F2B-70CD-EA41-2256-E45675C78C03', 'Peru', '2015-04-14 12:09:36', 'PE', '51', NULL, NULL),
(170, '6503A050-3EA4-38FA-FCE8-992DCE1B590B', 'French Polynesia', '2015-04-14 12:09:36', 'PF', '689', NULL, NULL),
(171, 'BA0C9E60-2EFE-AD8E-C0E7-29FCA7267DF5', 'Papua New Guinea', '2015-04-14 12:09:36', 'PG', '675', NULL, NULL),
(172, '88E336DC-5D75-752D-C77F-93A42D5B7C03', 'Philippines', '2015-04-14 12:09:36', 'PH', '63', NULL, NULL),
(173, '55FE4DF8-9BB2-126B-1F00-4334F8F7C640', 'Pakistan', '2015-04-14 12:09:36', 'PK', '92', NULL, NULL),
(174, '91DD425D-10F6-0FAC-73D3-5E3F5EFC86A2', 'Poland', '2015-04-14 12:09:36', 'PL', '48', NULL, NULL),
(175, '24354927-BE69-9B63-7324-7AE896674FF6', 'Saint Pierre and Miquelon', '2015-04-14 12:09:36', 'PM', '508', NULL, NULL),
(176, 'A21AA655-6CF5-A7A2-0CF3-374E511B7516', 'Pitcairn Islands', '2015-04-14 12:09:36', 'PN', '870', NULL, NULL),
(177, '7A0DF8B2-C5EC-FC58-9FC4-63FEC95F2DB9', 'Puerto Rico', '2015-04-14 12:09:36', 'PR', '1', NULL, NULL),
(178, '2BEE3A29-B3C8-7FE8-95F5-5DA81ECE0E75', 'State of Palestine', '2015-04-14 12:09:36', 'PS', '', NULL, NULL),
(179, '3BB8EFB5-BB65-F239-28B9-9B38E8FE83AE', 'Portugal', '2015-04-14 12:09:36', 'PT', '351', NULL, NULL),
(180, '7DFEFB58-159B-34CE-7B6E-5F5F74C0ED0B', 'Palau', '2015-04-14 12:09:36', 'PW', '680', NULL, NULL),
(181, '4E63C338-52F7-D693-39C8-6D9B09ECD5A8', 'Paraguay', '2015-04-14 12:09:36', 'PY', '595', NULL, NULL),
(182, '20319E02-E2D1-2FB9-B2B9-7CF8B7506DDF', 'Qatar', '2015-04-14 12:09:36', 'QA', '974', NULL, NULL),
(183, '0DF59336-53F1-F7C7-AB18-8DBF084DA125', 'Réunion', '2015-04-14 12:09:36', 'RE', '', NULL, NULL),
(184, 'E40912F1-CC15-4F2A-D8D1-FDC335CBC8CA', 'Romania', '2015-04-14 12:09:36', 'RO', '40', NULL, NULL),
(185, '38AC3FA4-283A-B36C-DC3D-1B59E7052A25', 'Serbia', '2015-04-14 12:09:36', 'RS', '381', NULL, NULL),
(186, '18A6D74E-6BE8-4CC8-8473-28EF91B436B2', 'Russia', '2015-04-14 12:09:36', 'RU', '7', NULL, NULL),
(187, '5E1EAB16-2E2F-39D3-0B0E-BF709AC39056', 'Rwanda', '2015-04-14 12:09:36', 'RW', '250', NULL, NULL),
(188, 'FDAB4E51-45A0-E5DF-1152-5DEAEC73C3AB', 'Saudi Arabia', '2015-04-14 12:09:36', 'SA', '966', NULL, NULL),
(189, 'CF21FB5C-9723-94F7-9F2B-F45CCAFC260D', 'Solomon Islands', '2015-04-14 12:09:36', 'SB', '677', NULL, NULL),
(190, '93C95D15-F10E-B87A-81DE-E9AAD84FC53A', 'Seychelles', '2015-04-14 12:09:36', 'SC', '248', NULL, NULL),
(191, '16302452-E1A6-D22C-5C99-133C48B5C980', 'Sudan', '2015-04-14 12:09:36', 'SD', '249', NULL, NULL),
(192, '472AA972-37BF-1FCF-5B13-03EAE9434088', 'Sweden', '2015-04-14 12:09:36', 'SE', '46', NULL, NULL),
(193, 'B2251D82-D868-DEAE-C811-4064951956F7', 'Singapore', '2015-04-14 12:09:36', 'SG', '65', NULL, NULL),
(194, 'F453E634-1155-9BCD-D903-08BEEC64C0A8', 'Saint Helena', '2015-04-14 12:09:36', 'SH', '290', NULL, NULL),
(195, 'B3D4C1FF-8E1D-DEEE-9A1A-39C83008F889', 'Slovenia', '2015-04-14 12:09:36', 'SI', '386', NULL, NULL),
(196, '8BF3EE1A-9967-AEDD-BA78-17C4087B28D9', 'Svalbard', '2015-04-14 12:09:36', 'SJ', '', NULL, NULL),
(197, '6FFA1E78-2CB2-B1CF-9D58-E0C9DEDA18AC', 'Slovakia', '2015-04-14 12:09:36', 'SK', '421', NULL, NULL),
(198, 'D6A4C632-C93A-839F-B09E-5480C4FAF1AD', 'Sierra Leone', '2015-04-14 12:09:36', 'SL', '232', NULL, NULL),
(199, '3DF96067-1DB6-B7CC-84B9-BAB7101F7131', 'San Marino', '2015-04-14 12:09:36', 'SM', '378', NULL, NULL),
(200, '2F0BC615-1C65-C7AA-47E8-9B5CA054D867', 'Senegal', '2015-04-14 12:09:36', 'SN', '221', NULL, NULL),
(201, 'C561F1FE-B072-5973-1DEC-F67D965DE27E', 'Somalia', '2015-04-14 12:09:36', 'SO', '252', NULL, NULL),
(202, '0775258C-EDC1-135D-E35C-01BD85F45F29', 'Suriname', '2015-04-14 12:09:36', 'SR', '597', NULL, NULL),
(203, 'AD0BAC3A-1DA4-23DA-0010-431A279304BE', 'Sao Tome and Principe', '2015-04-14 12:09:36', 'SS', '239', NULL, NULL),
(204, 'A4BDF3BD-B7F1-1B9B-CD98-A0C78ED53511', 'El Salvador', '2015-04-14 12:09:36', 'ST', '503', NULL, NULL),
(205, 'F4CEADD9-25F5-45F2-2E19-5B1795CE9D69', 'El Salvador', '2015-04-14 12:09:36', 'SV', '503', NULL, NULL),
(206, '46AD5507-C8B7-9F6D-571A-9E6E1C044CD6', 'Sint Maarten', '2015-04-14 12:09:36', 'SX', '', NULL, NULL),
(207, '3B28BF51-D190-F7CE-1BBA-49F72481AE3B', 'Syria', '2015-04-14 12:09:36', 'SY', '963', NULL, NULL),
(208, '1C48EC65-1073-25CD-5F53-7E4D22282B1B', 'Swaziland', '2015-04-14 12:09:36', 'SZ', '268', NULL, NULL),
(209, 'D3C33017-0BAE-B451-1190-B5A9A6D81265', 'Turks and Caicos Islands', '2015-04-14 12:09:36', 'TC', '1649', NULL, NULL),
(210, '436517D3-FF50-05BE-04A4-6314683C29BF', 'Chad', '2015-04-14 12:09:36', 'TD', '235', NULL, NULL),
(211, '373E212C-A17A-3D03-ACB3-588E2ECE1120', 'French Southern Territories', '2015-04-14 12:09:36', 'TF', '', NULL, NULL),
(212, '1DD74013-D596-D740-60F5-E007CFE9EE59', 'Togo', '2015-04-14 12:09:36', 'TG', '228', NULL, NULL),
(213, 'BF1DDCD3-0F3A-A81E-0508-910B20ED3822', 'Thailand', '2015-04-14 12:09:36', 'TH', '66', NULL, NULL),
(214, 'FB3A6E3C-53A9-CD6C-0F7C-302AB483B4BD', 'Tajikistan', '2015-04-14 12:09:36', 'TJ', '992', NULL, NULL),
(215, 'C343A6AB-0FAD-E093-7AF3-9589F5E58F64', 'Timor-Leste', '2015-04-14 12:09:36', 'TL', '670', NULL, NULL),
(216, 'DD3F11FD-CE04-AE45-D4DF-CB1324DDE122', 'Turkmenistan', '2015-04-14 12:09:36', 'TM', '993', NULL, NULL),
(217, '0AEF23A3-F684-B964-4D93-B507C40B66B4', 'Tunisia', '2015-04-14 12:09:36', 'TN', '216', NULL, NULL),
(218, 'A0662AD7-4934-E321-563F-6476311DD70C', 'Tonga', '2015-04-14 12:09:36', 'TO', '676', NULL, NULL),
(219, '5D0ECEE1-03F5-6D19-F5F3-F07F186DDF04', 'Turkey', '2015-04-14 12:09:36', 'TR', '90', NULL, NULL),
(220, 'F468D4C0-D571-5462-D0F1-6F0E4064AF46', 'Trinidad and Tobago', '2015-04-14 12:09:36', 'TT', '1868', NULL, NULL),
(221, 'BD92ECEC-C1D0-2516-23BF-75D5C2B05988', 'Tuvalu', '2015-04-14 12:09:36', 'TV', '688', NULL, NULL),
(222, 'C378CD23-31C4-6D56-EBA9-C38D38F74068', 'Taiwan', '2015-04-14 12:09:36', 'TW', '886', NULL, NULL),
(223, '11BAD1B7-D5B5-6F57-AE04-F483B2DEDAAB', 'Tanzania', '2015-04-14 12:09:36', 'TZ', '255', NULL, NULL),
(224, '4E7C6962-8A60-18F5-6380-6178B1BD4D1A', 'Ukraine', '2015-04-14 12:09:36', 'UA', '380', NULL, NULL),
(225, '2795DDE4-B42F-E010-4444-6F9AD1F42E87', 'Uganda', '2015-04-14 12:09:36', 'UG', '256', NULL, NULL),
(226, 'EF77733B-A972-EC78-8799-0BACF0ABF529', 'United States', '2015-04-14 12:09:36', 'US', '1', NULL, NULL),
(227, 'B1395F88-00C0-939B-DDFA-9DCBFB467EA4', 'Uruguay', '2015-04-14 12:09:36', 'UY', '598', NULL, NULL),
(228, '81E0BAA8-C899-C67F-9A1C-7F93971F181D', 'Uzbekistan', '2015-04-14 12:09:36', 'UZ', '998', NULL, NULL),
(229, '2ED293A8-00CF-E7F0-CA85-771A46E9F0B1', 'Holy See (Vatican City)', '2015-04-14 12:09:36', 'VA', '39', NULL, NULL),
(230, 'B34513B1-18A0-6F0F-9F93-5EBF96B73ADB', 'Saint Vincent and the Grenadines', '2015-04-14 12:09:36', 'VC', '1784', NULL, NULL),
(231, 'B4F3DA05-56D3-25AE-F8A9-8506B67C9004', 'Venezuela', '2015-04-14 12:09:36', 'VE', '58', NULL, NULL),
(232, '301B703B-B60C-4652-FB06-19CD778F0C74', 'British Virgin Islands', '2015-04-14 12:09:36', 'VG', '1284', NULL, NULL),
(233, 'C4ECE65F-CDFC-F50E-DCD9-3DB7CAB7C95E', 'US Virgin Islands', '2015-04-14 12:09:36', 'VI', '1340', NULL, NULL),
(234, 'C7245281-9AEA-4F60-73E4-0582061C77E9', 'Vietnam', '2015-04-14 12:09:36', 'VN', '84', NULL, NULL),
(235, 'BD92A44B-875F-79D2-3E12-D5175612D928', 'Vanuatu', '2015-04-14 12:09:36', 'VU', '678', NULL, NULL),
(236, 'E325ABAA-4150-836C-061D-6679EFEF3595', 'Wallis and Futuna', '2015-04-14 12:09:36', 'WF', '681', NULL, NULL),
(237, 'D8432DE9-535D-E306-0741-835AC0420A5B', 'Samoa', '2015-04-14 12:09:36', 'WS', '685', NULL, NULL),
(238, 'B133FA3B-7130-B4AF-373E-DEB720102D9B', 'Kosovo', '2015-04-14 12:09:36', 'XK', '', NULL, NULL),
(239, '3ABF1436-FAE0-3FD9-8948-501028332B17', 'Yemen', '2015-04-14 12:09:36', 'YE', '967', NULL, NULL),
(240, '7FA28C16-AE62-FBC2-7CEF-A5E4B71B826A', 'Mayotte', '2015-04-14 12:09:36', 'YT', '262', NULL, NULL),
(241, '38690943-EB49-39BB-09C7-1F987A3DDB85', 'South Africa', '2015-04-14 12:09:36', 'ZA', '27', NULL, NULL),
(242, '99795989-6422-28FE-D1A6-054605E86075', 'Zambia', '2015-04-14 12:09:36', 'ZM', '260', NULL, NULL),
(243, 'A6852D18-8C62-9B94-7F4B-2E65FE64EE88', 'Zimbabwe', '2015-04-14 12:09:36', 'ZW', '263', NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `devices`
--

CREATE TABLE `devices` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TITLE` varchar(100) NOT NULL DEFAULT '',
  `ALT_TITLES` varchar(255) NOT NULL DEFAULT '',
  `TYPE` varchar(100) NOT NULL DEFAULT '',
  `LINKED_OBJECT` varchar(100) NOT NULL DEFAULT '',
  `LOCATION_ID` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `FAVORITE` int(3) UNSIGNED NOT NULL DEFAULT 0,
  `SYSTEM_DEVICE` int(3) UNSIGNED NOT NULL DEFAULT 0,
  `CLICKED` datetime DEFAULT NULL,
  `SYSTEM` varchar(255) NOT NULL DEFAULT '',
  `SUBTYPE` varchar(100) NOT NULL DEFAULT '',
  `ENDPOINT_MODULE` varchar(255) NOT NULL DEFAULT '',
  `ENDPOINT_NAME` varchar(255) NOT NULL DEFAULT '',
  `ENDPOINT_TITLE` varchar(255) NOT NULL DEFAULT '',
  `ROLES` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `devices_groups`
--

CREATE TABLE `devices_groups` (
  `ID` int(10) UNSIGNED NOT NULL,
  `SYS_NAME` varchar(100) NOT NULL DEFAULT '',
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `APPLY_TYPES` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `devices_linked`
--

CREATE TABLE `devices_linked` (
  `ID` int(10) UNSIGNED NOT NULL,
  `DEVICE1_ID` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `DEVICE2_ID` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `LINK_TYPE` varchar(100) NOT NULL DEFAULT '',
  `LINK_SETTINGS` text DEFAULT NULL,
  `COMMENT` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `devices_scheduler_points`
--

CREATE TABLE `devices_scheduler_points` (
  `ID` int(10) UNSIGNED NOT NULL,
  `LINKED_METHOD` varchar(255) NOT NULL DEFAULT '',
  `VALUE` varchar(255) NOT NULL DEFAULT '',
  `SET_TIME` varchar(50) NOT NULL DEFAULT '',
  `SET_DAYS` varchar(50) NOT NULL DEFAULT '',
  `DEVICE_ID` int(10) NOT NULL DEFAULT 0,
  `ACTIVE` int(3) NOT NULL DEFAULT 1,
  `LATEST_RUN` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `elements`
--

CREATE TABLE `elements` (
  `ID` int(10) UNSIGNED NOT NULL,
  `SCENE_ID` int(10) NOT NULL DEFAULT 0,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `TYPE` varchar(255) NOT NULL DEFAULT '',
  `TOP` int(10) NOT NULL DEFAULT 0,
  `LEFT` int(255) NOT NULL DEFAULT 0,
  `WIDTH` int(255) NOT NULL DEFAULT 0,
  `HEIGHT` int(255) NOT NULL DEFAULT 0,
  `CROSS_SCENE` int(3) NOT NULL DEFAULT 0,
  `BACKGROUND` int(3) NOT NULL DEFAULT 1,
  `JAVASCRIPT` text DEFAULT NULL,
  `CSS` text DEFAULT NULL,
  `DX` int(10) NOT NULL DEFAULT 0,
  `DY` int(10) NOT NULL DEFAULT 0,
  `LINKED_ELEMENT_ID` int(10) NOT NULL DEFAULT 0,
  `PRIORITY` int(10) NOT NULL DEFAULT 0,
  `CSS_STYLE` varchar(255) NOT NULL DEFAULT '',
  `POSITION_TYPE` int(3) NOT NULL DEFAULT 0,
  `CONTAINER_ID` int(10) NOT NULL DEFAULT 0,
  `S3D_SCENE` varchar(255) NOT NULL DEFAULT '',
  `SMART_REPEAT` int(3) NOT NULL DEFAULT 0,
  `SYSTEM` varchar(255) NOT NULL DEFAULT '',
  `DEVICE_ID` int(10) NOT NULL DEFAULT 0,
  `LINKED_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `LINKED_PROPERTY` varchar(255) NOT NULL DEFAULT '',
  `LINKED_METHOD` varchar(255) NOT NULL DEFAULT '',
  `WIZARD_DATA` text DEFAULT NULL,
  `EASY_CONFIG` int(3) NOT NULL DEFAULT 0,
  `APPEAR_ANIMATION` int(3) NOT NULL DEFAULT 0,
  `CLASS_TEMPLATE` varchar(50) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `elements`
--

INSERT INTO `elements` (`ID`, `SCENE_ID`, `TITLE`, `TYPE`, `TOP`, `LEFT`, `WIDTH`, `HEIGHT`, `CROSS_SCENE`, `BACKGROUND`, `JAVASCRIPT`, `CSS`, `DX`, `DY`, `LINKED_ELEMENT_ID`, `PRIORITY`, `CSS_STYLE`, `POSITION_TYPE`, `CONTAINER_ID`, `S3D_SCENE`, `SMART_REPEAT`, `SYSTEM`, `DEVICE_ID`, `LINKED_OBJECT`, `LINKED_PROPERTY`, `LINKED_METHOD`, `WIZARD_DATA`, `EASY_CONFIG`, `APPEAR_ANIMATION`, `CLASS_TEMPLATE`) VALUES
(1, 1, 'Webcam Sample', 'html', 55, 392, 270, 210, 0, 1, NULL, NULL, 0, 0, 0, 0, '', 0, 0, '', 0, '', 0, '', '', '', NULL, 0, 0, ''),
(6, 1, 'Switch 1', 'switch', 405, 465, 0, 0, 0, 0, '', '', 0, 0, 0, 0, 'default', 0, 0, '', 0, '', 0, '', '', '', NULL, 0, 0, '');

-- --------------------------------------------------------

--
-- Структура таблицы `elm_states`
--

CREATE TABLE `elm_states` (
  `ID` int(10) UNSIGNED NOT NULL,
  `ELEMENT_ID` int(10) NOT NULL DEFAULT 0,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `IMAGE` varchar(255) NOT NULL DEFAULT '',
  `HTML` text DEFAULT NULL,
  `IS_DYNAMIC` int(3) NOT NULL DEFAULT 0,
  `CURRENT_STATE` int(3) NOT NULL DEFAULT 0,
  `LINKED_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `LINKED_PROPERTY` varchar(255) NOT NULL DEFAULT '',
  `CONDITION` int(3) NOT NULL DEFAULT 0,
  `CONDITION_VALUE` varchar(255) NOT NULL DEFAULT '',
  `CONDITION_ADVANCED` text DEFAULT NULL,
  `SCRIPT_ID` int(10) NOT NULL DEFAULT 0,
  `SWITCH_SCENE` int(3) NOT NULL DEFAULT 0,
  `CURRENT_STATUS` int(3) NOT NULL DEFAULT 0,
  `ACTION_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `ACTION_METHOD` varchar(255) NOT NULL DEFAULT '',
  `MENU_ITEM_ID` int(10) NOT NULL DEFAULT 0,
  `WINDOW_POSX` int(10) NOT NULL DEFAULT 0,
  `WINDOW_POSY` int(10) NOT NULL DEFAULT 0,
  `WINDOW_WIDTH` int(10) NOT NULL DEFAULT 0,
  `WINDOW_HEIGHT` int(10) NOT NULL DEFAULT 0,
  `HOMEPAGE_ID` int(10) NOT NULL DEFAULT 0,
  `EXT_URL` varchar(255) NOT NULL DEFAULT '',
  `PRIORITY` int(10) NOT NULL DEFAULT 0,
  `CODE` text DEFAULT NULL,
  `OPEN_SCENE_ID` int(10) NOT NULL DEFAULT 0,
  `S3D_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `S3D_CAMERA` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `elm_states`
--

INSERT INTO `elm_states` (`ID`, `ELEMENT_ID`, `TITLE`, `IMAGE`, `HTML`, `IS_DYNAMIC`, `CURRENT_STATE`, `LINKED_OBJECT`, `LINKED_PROPERTY`, `CONDITION`, `CONDITION_VALUE`, `CONDITION_ADVANCED`, `SCRIPT_ID`, `SWITCH_SCENE`, `CURRENT_STATUS`, `ACTION_OBJECT`, `ACTION_METHOD`, `MENU_ITEM_ID`, `WINDOW_POSX`, `WINDOW_POSY`, `WINDOW_WIDTH`, `WINDOW_HEIGHT`, `HOMEPAGE_ID`, `EXT_URL`, `PRIORITY`, `CODE`, `OPEN_SCENE_ID`, `S3D_OBJECT`, `S3D_CAMERA`) VALUES
(1, 1, 'Default', '', '<img src=\"http://abclocal.go.com/three/wabc/webcam/skycpk.jpg\" width=\"270\">', 0, 1, '', '', 1, '', '', 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, '', 0, NULL, 0, '', ''),
(4, 6, 'off', '', 'Switch 1', 1, 1, 'Switch1', 'status', 4, '1', NULL, 0, 0, 0, 'Switch1', 'turnOn', 0, 0, 0, 0, 0, 0, '', 0, NULL, 0, '', ''),
(5, 6, 'on', '', 'Switch 1', 1, 0, 'Switch1', 'status', 1, '1', NULL, 0, 0, 0, 'Switch1', 'turnOff', 0, 0, 0, 0, 0, 0, '', 0, NULL, 0, '', '');

-- --------------------------------------------------------

--
-- Структура таблицы `events`
--

CREATE TABLE `events` (
  `ID` int(10) UNSIGNED NOT NULL,
  `EVENT_TYPE` varchar(10) NOT NULL DEFAULT '',
  `TERMINAL_FROM` varchar(255) NOT NULL DEFAULT '',
  `TERMINAL_TO` varchar(255) NOT NULL DEFAULT '',
  `USER_FROM` varchar(255) NOT NULL DEFAULT '',
  `USER_TO` varchar(255) NOT NULL DEFAULT '',
  `WINDOW` varchar(255) NOT NULL DEFAULT '',
  `DETAILS` text DEFAULT NULL,
  `ADDED` datetime DEFAULT NULL,
  `EXPIRE` datetime DEFAULT NULL,
  `PROCESSED` int(3) NOT NULL DEFAULT 0,
  `EVENT_NAME` varchar(255) NOT NULL DEFAULT '',
  `DESCRIPTION` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `events_params`
--

CREATE TABLE `events_params` (
  `ID` int(10) UNSIGNED NOT NULL,
  `EVENT_ID` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `VALUE` varchar(255) NOT NULL DEFAULT '',
  `UPDATED` datetime DEFAULT NULL,
  `LINKED_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `LINKED_PROPERTY` varchar(255) NOT NULL DEFAULT '',
  `LINKED_METHOD` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `history`
--

CREATE TABLE `history` (
  `ID` int(10) UNSIGNED NOT NULL,
  `ADDED` datetime DEFAULT NULL,
  `OBJECT_ID` int(10) NOT NULL DEFAULT 0,
  `METHOD_ID` int(10) NOT NULL DEFAULT 0,
  `VALUE_ID` int(10) NOT NULL DEFAULT 0,
  `OLD_VALUE` varchar(255) NOT NULL DEFAULT '',
  `NEW_VALUE` varchar(255) NOT NULL DEFAULT '',
  `DETAILS` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `ignore_updates`
--

CREATE TABLE `ignore_updates` (
  `ID` tinyint(3) UNSIGNED NOT NULL,
  `NAME` varchar(50) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `jobs`
--

CREATE TABLE `jobs` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `COMMANDS` text NOT NULL,
  `RUNTIME` datetime DEFAULT NULL,
  `EXPIRE` datetime DEFAULT NULL,
  `PROCESSED` int(3) NOT NULL DEFAULT 0,
  `STARTED` datetime DEFAULT NULL,
  `EXPIRED` int(3) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Структура таблицы `layouts`
--

CREATE TABLE `layouts` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `PRIORITY` int(10) NOT NULL DEFAULT 0,
  `TYPE` varchar(255) NOT NULL DEFAULT '',
  `CODE` text DEFAULT NULL,
  `APP` varchar(255) NOT NULL DEFAULT '',
  `URL` char(255) NOT NULL DEFAULT '',
  `DETAILS` text DEFAULT NULL,
  `REFRESH` int(10) NOT NULL DEFAULT 0,
  `ICON` varchar(50) NOT NULL DEFAULT '',
  `HIDDEN` int(1) NOT NULL DEFAULT 0,
  `BACKGROUND_IMAGE` varchar(255) NOT NULL DEFAULT '',
  `THEME` varchar(50) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `layouts`
--

INSERT INTO `layouts` (`ID`, `TITLE`, `PRIORITY`, `TYPE`, `CODE`, `APP`, `URL`, `DETAILS`, `REFRESH`, `ICON`, `HIDDEN`, `BACKGROUND_IMAGE`, `THEME`) VALUES
(7, '<#LANG_MODULE_SCENES#>', 550, 'app', '', 'scenes', '', '', 0, '', 0, '', ''),
(8, '<#LANG_WELCOME_GREETING#>', 1000, 'html', '<div style=\"padding-left:50px;padding-top:30px\">\r\n<h1><#LANG_WELCOME_GREETING#></h1>\r\n&nbsp;\r\n<p style=\"font-size:14px\">\r\n<#LANG_WELCOME_TEXT#>\r\n</p>\r\n</div>', '', '', '', 0, '', 0, '', ''),
(10, '<#LANG_APP_MEDIA_BROWSER#>', 200, 'app', '', 'app_mediabrowser', '', '', 0, '', 0, '', '');

-- --------------------------------------------------------

--
-- Структура таблицы `locations`
--

CREATE TABLE `locations` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `PRIORITY` int(10) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `locations`
--

INSERT INTO `locations` (`ID`, `TITLE`, `PRIORITY`) VALUES
(1, 'Kitchen', 0),
(4, 'Livingroom', 0),
(5, 'Bedroom', 0),
(6, 'Hall', 0),
(7, 'Bathroom', 0),
(8, 'Toilet', 0),
(10, 'Child\'s bedroom', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `media_favorites`
--

CREATE TABLE `media_favorites` (
  `ID` int(10) UNSIGNED NOT NULL,
  `PATH` varchar(255) NOT NULL DEFAULT '',
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `LIST_ID` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `COLLECTION_ID` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Структура таблицы `media_history`
--

CREATE TABLE `media_history` (
  `ID` int(10) UNSIGNED NOT NULL,
  `PATH` varchar(255) NOT NULL DEFAULT '',
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `LIST_ID` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `COLLECTION_ID` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `PLAYED` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `methods`
--

CREATE TABLE `methods` (
  `ID` int(10) UNSIGNED NOT NULL,
  `OBJECT_ID` int(10) NOT NULL DEFAULT 0,
  `CLASS_ID` int(10) NOT NULL DEFAULT 0,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `DESCRIPTION` text DEFAULT NULL,
  `CODE` text DEFAULT NULL,
  `CALL_PARENT` int(3) NOT NULL DEFAULT 0,
  `SCRIPT_ID` int(10) NOT NULL DEFAULT 0,
  `EXECUTED` datetime DEFAULT NULL,
  `EXECUTED_PARAMS` varchar(255) DEFAULT NULL,
  `EXECUTED_SRC` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `methods`
--

INSERT INTO `methods` (`ID`, `OBJECT_ID`, `CLASS_ID`, `TITLE`, `DESCRIPTION`, `CODE`, `CALL_PARENT`, `SCRIPT_ID`, `EXECUTED`, `EXECUTED_PARAMS`, `EXECUTED_SRC`) VALUES
(14, 0, 7, 'onNewMinute', '', '', 0, 0, '2015-12-03 14:49:00', 'a:4:{s:6:\"object\";s:10:\"ClockChime\";s:2:\"op\";s:1:\"m\";s:1:\"m\";s:11:\"onNewMinute\";s:21:\"ORIGINAL_OBJECT_TITLE\";s:10:\"ClockChime\";}', NULL),
(15, 0, 7, 'onNewHour', '', '', 0, 0, '2015-02-04 15:00:00', 'a:4:{s:6:\"object\";s:10:\"ClockChime\";s:2:\"op\";s:1:\"m\";s:1:\"m\";s:9:\"onNewHour\";s:21:\"ORIGINAL_OBJECT_TITLE\";s:10:\"ClockChime\";}', NULL),
(18, 6, 0, 'onNewMinute', '', '$h=(int)date(\'G\',time());\r\n$m=date(\'i\',time());\r\n\r\n\r\nif (isWeekDay()) {\r\n\r\n}\r\n\r\n\r\nif (($h>=8) && getGlobal(\'clockChimeEnabled\')) {\r\n if ($m==\"00\") {\r\n   say(timeNow(),1);\r\n }\r\n}\r\n\r\n\r\nsetGlobal(\'timeNow\',date(\'H:i\'));\r\n\r\n$homeStatus=date(\'H:i\');\r\nif (getGlobal(\'NobodyHomeMode.active\')) {\r\n $homeStatus.=\' Дома никого\';\r\n} else {\r\n $homeStatus.=\' Дома кто-то есть\';\r\n}\r\n\r\n$homeStatus.=\' \'.getGlobal(\'Security.stateDetails\');\r\n$homeStatus.=\' \'.getGlobal(\'System.stateDetails\');\r\n$homeStatus.=\' \'.getGlobal(\'Communication.stateDetails\');\r\nsetGlobal(\'HomeStatus\',$homeStatus);\r\n\r\n if (timeBetween(getGlobal(\'SunRiseTime\'),getGlobal(\'SunSetTime\')) && getGlobal(\'isDark\')==\"1\") {\r\n  setGlobal(\"isDark\",0);\r\n  callMethod(\'DarknessMode.deactivate\');  \r\n } elseif (!timeBetween(getGlobal(\'SunRiseTime\'),getGlobal(\'SunSetTime\')) && getGlobal(\'isDark\')!=\"1\") {\r\n  setGlobal(\"isDark\",1);\r\n  callMethod(\'DarknessMode.activate\');    \r\n }\r\n \r\n  if (timeIs(getGlobal(\'SunRiseTime\'))) {\r\n  say(\'Всходит солнце\');\r\n }\r\n if (timeIs(getGlobal(\'SunSetTime\'))) {\r\n  say(\'Солнце заходит\',2);\r\n }\r\n \r\nif (timeIs(\"23:30\") && (gg(\"EconomMode.active\")!=\"1\") && (gg(\"NobodyHomeMode.active\")==\"1\")) {\r\n say(\"Похоже никого нет сегодня, можно сэкономить немного.\");\r\n callMethod(\'EconomMode.activate\');\r\n}\r\n\r\nif (timeIs(\'20:00\')) {\r\n callMethod(\'NightMode.activate\');\r\n} elseif (timeIs(\'08:00\')) {\r\n callMethod(\'NightMode.deactivate\');\r\n}\r\n\r\nif (timeIs(\"03:00\")) {\r\n runScript(\"systemMaintenance\");\r\n}\r\n\r\nif (gg(\'ThisComputer.AlarmStatus\') && timeIs(gg(\'ThisComputer.AlarmTime\'))) {\r\n runScript(\'MorningAlarm\');\r\n}', 1, 0, '2015-12-03 14:49:00', 'a:3:{s:6:\"object\";s:10:\"ClockChime\";s:2:\"op\";s:1:\"m\";s:1:\"m\";s:11:\"onNewMinute\";}', NULL),
(20, 0, 10, 'WakedUp', '', '', 0, 0, NULL, NULL, NULL),
(25, 0, 10, 'onIdle', '', '', 0, 0, NULL, NULL, NULL),
(27, 0, 10, 'StartUp', '', '', 0, 0, '2020-07-08 17:55:15', '{\"ORIGINAL_OBJECT_TITLE\":\"ThisComputer\"}', ''),
(29, 0, 10, 'commandReceived', 'получение новой команды', '', 0, 0, '2014-09-05 12:07:08', 'a:2:{s:7:\"command\";s:14:\"статусы\";s:21:\"ORIGINAL_OBJECT_TITLE\";s:12:\"ThisComputer\";}', NULL),
(45, 0, 10, 'powerConnected', '', '', 0, 0, NULL, NULL, NULL),
(46, 0, 10, 'powerDisconnected', '', '', 0, 0, NULL, NULL, NULL),
(48, 7, 0, 'powerDisconnected', NULL, 'say(\"Отключено питание\");', 1, 0, NULL, NULL, NULL),
(59, 0, 21, 'checkState', '', ' ', 0, 0, '2020-07-08 17:55:40', '{\"ORIGINAL_OBJECT_TITLE\":\"Communication\"}', ''),
(60, 0, 21, 'stateChanged', '', '', 0, 0, '2015-12-03 14:46:48', 'a:2:{s:5:\"STATE\";s:5:\"green\";s:21:\"ORIGINAL_OBJECT_TITLE\";s:6:\"System\";}', NULL),
(63, 0, 24, 'modeChanged', '', '$this->setProperty(\"updated\",time());\r\n$this->setProperty(\"updatedTime\",date(\'H:i\'));\r\nif ($this->getProperty(\'active\')) {\r\n say(\"Режим \".$this->getProperty(\'title\').\" активирован.\");\r\n} else {\r\n say(\"Режим \".$this->getProperty(\'title\').\" выключен.\");\r\n}', 0, 0, '2015-01-29 12:51:00', 'a:4:{s:8:\"PROPERTY\";s:6:\"active\";s:9:\"NEW_VALUE\";s:1:\"0\";s:9:\"OLD_VALUE\";s:1:\"1\";s:21:\"ORIGINAL_OBJECT_TITLE\";s:12:\"DarknessMode\";}', NULL),
(64, 0, 24, 'activate', '', '$this->setProperty(\'active\',1);', 0, 0, '2015-01-29 08:50:00', 'a:1:{s:21:\"ORIGINAL_OBJECT_TITLE\";s:12:\"DarknessMode\";}', NULL),
(65, 0, 24, 'deactivate', '', '$this->setProperty(\'active\',0);', 0, 0, '2015-01-29 12:51:00', 'a:1:{s:21:\"ORIGINAL_OBJECT_TITLE\";s:12:\"DarknessMode\";}', NULL),
(66, 0, 25, 'onActivity', 'Rooms Activity', '$latestActivity=$this->getProperty(\'LatestActivity\');\r\n$this->setProperty(\'LatestActivity\',time());\r\n$this->setProperty(\'LatestActivityTime\',date(\'H:i\'));\r\n\r\n$this->setProperty(\'SomebodyHere\',1);\r\n$ot=$this->object_title;\r\nif ($this->getProperty(\"IdleDelay\")) {\r\n $activity_timeout=(int)$this->getProperty(\"IdleDelay\");\r\n} else {\r\n $activity_timeout=10*60;\r\n}\r\nclearTimeOut($ot.\"_activity_timeout\");\r\nsetTimeOut($ot.\"_activity_timeout\",\"callMethod(\'\".$ot.\".onIdle\');\",$activity_timeout);\r\n$this->callMethod(\"updateActivityStatus\");\r\n\n/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/Rooms_onActivity.php\');\n/* end injection of {SDevices} */', 0, 0, '2014-09-05 13:01:00', 'a:1:{s:21:\"ORIGINAL_OBJECT_TITLE\";s:10:\"Kinderroom\";}', NULL),
(67, 0, 25, 'onIdle', 'Rooms Idle', '$this->setProperty(\'SomebodyHere\',0);\n/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/Rooms_onIdle.php\');\n/* end injection of {SDevices} */', 0, 0, '2014-09-05 12:24:46', 'a:1:{s:21:\"ORIGINAL_OBJECT_TITLE\";s:10:\"Kinderroom\";}', NULL),
(73, 17, 0, 'checkState', '', '$details=array();\r\n$red_state=0;\r\n$yellow_state=0;\r\n\r\n$cycles=array(\'states\'=>\'states\',\'main\'=>\'main\',\'execs\'=>\'exec\',\'scheduler\'=>\'scheduler\');\r\nforeach($cycles as $k=>$v) {\r\n $tm=getGlobal(\'ThisComputer.cycle_\'.$k.\'Run\');\r\n if (time()-(int)$tm>5*60) {\r\n  $red_state=1;\r\n  $details[]=$v.\" \".LANG_GENERAL_CYCLE.\" \".LANG_GENERAL_STOPPED.\".\";\r\n }\r\n}\r\n\r\n$cycles=array(\'ping\'=>\'ping\');\r\nforeach($cycles as $k=>$v) {\r\n $tm=getGlobal(\'ThisComputer.cycle_\'.$k.\'Run\');\r\n if (time()-(int)$tm>10*60) {\r\n  $yellow_state=1;\r\n  $details[]=$v.\" \".LANG_GENERAL_CYCLE.\" \".LANG_GENERAL_STOPPED.\".\";  \r\n }\r\n}\r\n\r\nif ($red_state) {\r\n $state=\'red\';\r\n $state_title=LANG_GENERAL_RED; \r\n} elseif ($yellow_state) {\r\n $state=\'yellow\';\r\n $state_title=LANG_GENERAL_YELLOW;  \r\n} else {\r\n $state=\'green\';\r\n $state_title=LANG_GENERAL_GREEN;   \r\n}\r\n\r\n$new_details=implode(\". \",$details);\r\nif ($this->getProperty(\"stateDetails\")!=$new_details) {\r\n $this->setProperty(\'stateDetails\',$new_details);\r\n}\r\n\r\nif ($this->getProperty(\'stateColor\')!=$state) {\r\n $this->setProperty(\'stateColor\',$state);\r\n $this->setProperty(\'stateTitle\',$state_title);\r\n if ($state!=\'green\') {\r\n  say(LANG_GENERAL_SYSTEM_STATE.\" \".LANG_GENERAL_CHANGED_TO.\" \".$state_title.\".\");\r\n  say(implode(\". \",$details));\r\n } else {\r\n  say(LANG_GENERAL_SYSTEM_STATE.\" \".LANG_GENERAL_RESTORED_TO.\" \".$state_title);\r\n }\r\n $this->callMethod(\'stateChanged\');\r\n}\n/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/system_checkstate.php\');\n/* end injection of {SDevices} */', 1, 0, '2020-07-08 17:55:40', '', ''),
(74, 18, 0, 'checkState', '', '$details=array();\r\n$red_state=0;\r\n$yellow_state=0;\r\n\r\nif (!isOnline(\'Internet\')) {\r\n $yellow_state=1;\r\n $details[]=LANG_GENERAL_NO_INTERNET_ACCESS;\r\n}\r\n\r\nif ($red_state) {\r\n $state=\'red\';\r\n $state_title=LANG_GENERAL_RED; \r\n} elseif ($yellow_state) {\r\n $state=\'yellow\';\r\n $state_title=LANG_GENERAL_YELLOW;  \r\n} else {\r\n $state=\'green\';\r\n $state_title=LANG_GENERAL_GREEN;   \r\n}\r\n\r\n$new_details=implode(\". \",$details);\r\nif ($this->getProperty(\"stateDetails\")!=$new_details) {\r\n $this->setProperty(\'stateDetails\',$new_details);\r\n}\r\n\r\nif ($this->getProperty(\'stateColor\')!=$state) {\r\n $this->setProperty(\'stateColor\',$state);\r\n $this->setProperty(\'stateTitle\',$state_title);\r\n if ($state!=\'green\') {\r\n  say(LANG_GENERAL_COMMUNICATION_STATE.\" \".LANG_GENERAL_CHANGED_TO.\" \".$state_title.\".\");\r\n  say(implode(\". \",$details));\r\n } else {\r\n  say(LANG_GENERAL_COMMUNICATION_STATE.\" \".LANG_GENERAL_RESTORED_TO.\" \".$state_title);\r\n }\r\n $this->callMethod(\'stateChanged\');\r\n}', 1, 0, '2020-07-08 17:55:40', '', ''),
(75, 16, 0, 'checkState', '', '$details=array();\r\n$red_state=0;\r\n$yellow_state=0;\r\n\r\nif ($red_state) {\r\n $state=\'red\';\r\n $state_title=LANG_GENERAL_RED; \r\n} elseif ($yellow_state) {\r\n $state=\'yellow\';\r\n $state_title=LANG_GENERAL_YELLOW;  \r\n} else {\r\n $state=\'green\';\r\n $state_title=LANG_GENERAL_GREEN;   \r\n}\r\n\r\n$new_details=implode(\". \",$details);\r\nif ($this->getProperty(\"stateDetails\")!=$new_details) {\r\n $this->setProperty(\'stateDetails\',$new_details);\r\n}\r\n\r\nif ($this->getProperty(\'stateColor\')!=$state) {\r\n $this->setProperty(\'stateColor\',$state);\r\n $this->setProperty(\'stateTitle\',$state_title);\r\n if ($state!=\'green\') {\r\n  say(LANG_GENERAL_SECURITY_STATE.\" \".LANG_GENERAL_CHANGED_TO.\" \".$state_title.\".\");\r\n  say(implode(\". \",$details));\r\n } else {\r\n  say(LANG_GENERAL_SECURITY_STATE.\" \".LANG_GENERAL_RESTORED_TO.\" \".$state_title);\r\n }\r\n $this->callMethod(\'stateChanged\');\r\n}', 1, 0, '2020-07-08 17:55:40', '', ''),
(77, 0, 10, 'VolumeLevelChanged', '', '$volume=round(65535*$params[\'VALUE\']/100);\r\n$this->setProperty(\'volumeLevel\',$params[\'VALUE\']);\r\nsafe_exec(\'..\\\\apps\\\\nircmd\\\\nircmdc setsysvolume \'.$volume);\r\nsay(\"Изменилась громкость до \".$params[\'VALUE\'].\" процентов\");', 0, 0, '2014-07-31 21:15:03', 'a:3:{s:5:\"VALUE\";s:2:\"90\";s:4:\"HOST\";s:9:\"localhost\";s:21:\"ORIGINAL_OBJECT_TITLE\";s:12:\"ThisComputer\";}', NULL),
(81, 47, 0, 'activate', '', 'setGlobal(\'minMsgLevel\',\'2\');\n/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/nightmode_activate.php\');\n/* end injection of {SDevices} */', 1, 0, '2014-09-04 20:00:00', '', NULL),
(82, 47, 0, 'deactivate', '', 'setGlobal(\'minMsgLevel\',\'1\');', 1, 0, '2014-09-05 08:00:00', '', NULL),
(88, 0, 25, 'updateActivityStatus', 'Update activity status', '$rooms=getObjectsByClass(\"Rooms\");\r\n$total=count($rooms);\r\nfor($i=0;$i<$total;$i++) {\r\n $rooms[$i][\'room\']=getGlobal($rooms[$i][\'TITLE\'].\'.Title\');\r\n if (!$rooms[$i][\'room\']) {\r\n  $rooms[$i][\'room\']=$rooms[$i][\'TITLE\'];\r\n } \r\n $rooms[$i][\'active\']=getGlobal($rooms[$i][\'TITLE\'].\'.SomebodyHere\');\r\n $rooms[$i][\'time\']=getGlobal($rooms[$i][\'TITLE\'].\'.LatestActivity\'); \r\n $rooms[$i][\'diff\']=time()-$rooms[$i][\'time\'];\r\n} \r\n\r\nfunction rooms_cmp($a, $b)\r\n{\r\n    if ($a[\'diff\'] == $b[\'diff\']) {\r\n        return 0;\r\n    }\r\n    return ($a[\'diff\'] < $b[\'diff\']) ? -1 : 1;\r\n}\r\nusort($rooms,\"rooms_cmp\");\r\n\r\nif (!$rooms[0][\'active\']) {\r\n $somebodyHomeText=\"Никого нет дома. Были в \".date(\'H:i\',$rooms[0][\'time\']).\" (\".$rooms[0][\'room\'].\")\";\r\n} else {\r\n $res_rooms=array();\r\n for($i=0;$i<$total;$i++) {\r\n  if ($rooms[$i][\'active\']) {\r\n   $res_rooms[]=$rooms[$i][\'room\'];\r\n  } \r\n }\r\n $somebodyHomeText=\"Кто-то дома: \".implode(\", \",$res_rooms); \r\n}\r\nsetGlobal(\'somebodyHomeText\',$somebodyHomeText);\r\n//echo \"Updated\";\n/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/Rooms_updateActivityStatus.php\');\n/* end injection of {SDevices} */', 0, 0, '2014-09-05 13:01:00', 'a:1:{s:21:\"ORIGINAL_OBJECT_TITLE\";s:10:\"Kinderroom\";}', NULL),
(92, 0, 32, 'alarm', '', '', 0, 0, NULL, NULL, NULL),
(93, 0, 32, 'goingHome', '', '', 0, 0, NULL, NULL, NULL),
(94, 0, 32, 'gotHome', '', '', 0, 0, NULL, NULL, NULL),
(95, 0, 32, 'Moving', '', '', 0, 0, NULL, NULL, NULL),
(96, 0, 32, 'outOfHome', '', '', 0, 0, NULL, NULL, NULL),
(103, 0, 34, 'statusUpdated', 'Status updated event', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SDevices_statusUpdated.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(104, 0, 34, 'setUpdatedText', 'Change updated text', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SDevices_setUpdatedText.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(105, 0, 34, 'logicAction', 'Logic Action', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SDevices_logicAction.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(106, 0, 34, 'keepAlive', 'Alive update', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SDevices_keepAlive.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(107, 19, 0, 'activate', NULL, '\n/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/econommode_activate.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(108, 19, 0, 'deactivate', NULL, '\n/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/econommode_deactivate.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(109, 20, 0, 'activate', NULL, '\n/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/nobodyhomemode_activate.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(110, 20, 0, 'deactivate', NULL, '\n/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/nobodyhomemode_deactivate.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(111, 23, 0, 'activate', NULL, '\n/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/darknessmode_activate.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(112, 23, 0, 'deactivate', NULL, '\n/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/darknessmode_deactivate.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(113, 0, 35, 'turnOn', 'Turn On', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SControllers_turnOn.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(114, 0, 35, 'turnOff', 'Turn Off', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SControllers_turnOff.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(115, 0, 35, 'switch', 'Switch', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SControllers_switch.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(116, 0, 36, 'turnOn', 'Turn On', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SGroups_turnOn.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(117, 0, 36, 'turnOff', 'Turn Off', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SGroups_turnOff.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(118, 0, 36, 'switch', 'Switch', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SGroups_switch.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(119, 0, 36, 'statusUpdated', 'Status Updated', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SGroups_statusUpdated.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(120, 0, 38, 'setTargetTemperature', 'Set target temperature', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SThermostats_setTargetTemperature.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(121, 0, 38, 'valueUpdated', 'Value Updated', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SThermostats_valueUpdated.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(122, 0, 38, 'statusUpdated', 'Status Updated', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SThermostats_statusUpdated.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(123, 0, 38, 'tempUp', 'Increase target temperature', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SThermostats_tempUp.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(124, 0, 38, 'tempDown', 'Descrease target temperature', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SThermostats_tempDown.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(125, 0, 38, 'switchEnable', 'Switch Enable', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SThermostats_switchEnable.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(126, 0, 38, 'enable', 'Enable', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SThermostats_enable.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(127, 0, 38, 'disable', 'Mode: Off', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SThermostats_disable.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(128, 0, 38, 'turnOn', 'Mode: Normal', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SThermostats_turnOn.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(129, 0, 38, 'turnOff', 'Mode: Eco', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SThermostats_turnOff.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(130, 0, 39, 'setLevel', 'Set brightness level', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SDimmers_setLevel.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(131, 0, 39, 'statusUpdated', 'Status Updated', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SDimmers_statusUpdated.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(132, 0, 39, 'levelUpdated', 'Level Updated', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SDimmers_levelUpdated.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(133, 0, 39, 'levelWorkUpdated', 'Level Work Updated', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SDimmers_levelWorkUpdated.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(134, 0, 39, 'turnOn', 'Turn On', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SDimmers_turnOn.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(135, 0, 39, 'turnOff', 'Turn Off', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SDimmers_turnOff.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(136, 0, 40, 'colorUpdated', 'Color Updated', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SRGB_colorUpdated.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(137, 0, 40, 'setColor', 'Color Set', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SRGB_setColor.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(138, 0, 40, 'turnOn', 'Turn On', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SRGB_turnOn.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(139, 0, 40, 'turnOff', 'Turn Off', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SRGB_turnOff.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(140, 0, 41, 'motionDetected', 'Motion Detected', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SMotions_motionDetected.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(141, 0, 41, 'blockSensor', 'Block sensor', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SMotions_blockSensor.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(142, 0, 41, 'unblockSensor', 'Un-block sensor', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SMotions_unblockSensor.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(143, 0, 42, 'motionDetected', 'Motion Detected', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SCameras_motionDetected.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(144, 0, 42, 'updatePreview', 'Update preview code', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SCameras_updatePreview.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(145, 0, 42, 'takeSnapshot', 'Takes snapshot', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SCameras_takeSnapshot.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(146, 0, 42, 'takeSeries', 'Takes image series', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SCameras_takeSeries.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(147, 0, 43, 'statusUpdated', 'Status updated event', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SOpenClose_statusUpdated.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(148, 0, 43, 'blockSensor', 'Block sensor', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SOpenClose_blockSensor.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(149, 0, 43, 'unblockSensor', 'Un-block sensor', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SOpenClose_unblockSensor.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(150, 0, 44, 'statusUpdated', 'Status updated event', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SOpenable_statusUpdated.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(151, 0, 44, 'switch', 'Switch', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SOpenable_switch.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(152, 0, 44, 'open', 'Open', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SOpenable_open.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(153, 0, 44, 'close', 'Close', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SOpenable_close.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(154, 0, 45, 'statusUpdated', 'Status updated event', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SLeak_statusUpdated.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(155, 0, 45, 'alert', 'Sensor alert', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SLeak_alert.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(156, 0, 45, 'blockSensor', 'Block sensor', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SLeak_blockSensor.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(157, 0, 45, 'unblockSensor', 'Un-block sensor', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SLeak_unblockSensor.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(158, 0, 46, 'statusUpdated', 'Status updated event', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SSmoke_statusUpdated.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(159, 0, 46, 'alert', 'Sensor alert', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SSmoke_alert.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(160, 0, 46, 'blockSensor', 'Block sensor', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SSmoke_blockSensor.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(161, 0, 46, 'unblockSensor', 'Un-block sensor', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SSmoke_unblockSensor.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(162, 0, 47, 'valueUpdated', 'Data Value updated event', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SCounters_valueUpdated.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(163, 0, 47, 'valueWorkUpdated', 'Work Value updated event', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SCounters_valueWorkUpdated.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(164, 0, 47, 'refreshStats', 'Refreshes daily/monthly stats', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SCounters_refreshStats.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(165, 0, 48, 'pressed', 'Press', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SButtons_pressed.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(166, 0, 49, 'valueUpdated', 'Value Updated', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SSensors_valueUpdated.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(167, 0, 49, 'alert', 'Sensor alert', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SSensors_alert.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(168, 0, 49, 'blockSensor', 'Block sensor', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SSensors_blockSensor.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(169, 0, 49, 'unblockSensor', 'Un-block sensor', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SSensors_unblockSensor.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(170, 0, 56, 'valueUpdated', 'Value Updated', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SPowerSensors_valueUpdated.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(171, 0, 56, 'loadStatusChanged', 'Load Status Changed', '/* begin injection of {SDevices} */\nrequire(DIR_MODULES.\'devices/SPowerSensors_loadStatusChanged.php\');\n/* end injection of {SDevices} */', 0, 0, NULL, NULL, NULL),
(174, 0, 32, 'enteredLocation', NULL, '//$params[\"LOCATION_OBJECT\"], $params[\"LOCATION\"]\n', 0, 0, NULL, NULL, NULL),
(175, 0, 32, 'leftLocation', NULL, '//$params[\"LOCATION_OBJECT\"], $params[\"LOCATION\"]\n', 0, 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `myblocks`
--

CREATE TABLE `myblocks` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `CATEGORY_ID` int(10) NOT NULL DEFAULT 0,
  `BLOCK_TYPE` char(10) NOT NULL DEFAULT '',
  `BLOCK_COLOR` int(10) NOT NULL DEFAULT 0,
  `SCRIPT_ID` int(10) NOT NULL DEFAULT 0,
  `LINKED_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `LINKED_PROPERTY` varchar(255) NOT NULL DEFAULT '',
  `SYSTEM` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `myblocks_categories`
--

CREATE TABLE `myblocks_categories` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `SYSTEM` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `objects`
--

CREATE TABLE `objects` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `CLASS_ID` int(10) NOT NULL DEFAULT 0,
  `DESCRIPTION` text DEFAULT NULL,
  `LOCATION_ID` int(10) NOT NULL DEFAULT 0,
  `KEEP_HISTORY` int(10) NOT NULL DEFAULT 0,
  `SYSTEM` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `objects`
--

INSERT INTO `objects` (`ID`, `TITLE`, `CLASS_ID`, `DESCRIPTION`, `LOCATION_ID`, `KEEP_HISTORY`, `SYSTEM`) VALUES
(6, 'ClockChime', 7, '', 0, 0, ''),
(7, 'ThisComputer', 10, '', 0, 0, ''),
(16, 'Security', 21, '', 0, 0, ''),
(17, 'System', 21, '', 0, 0, ''),
(18, 'Communication', 21, '', 0, 0, ''),
(19, 'EconomMode', 24, '', 0, 0, ''),
(20, 'NobodyHomeMode', 24, '', 0, 0, ''),
(21, 'SecurityArmedMode', 24, '', 0, 0, ''),
(22, 'GuestsMode', 24, '', 0, 0, ''),
(23, 'DarknessMode', 24, '', 0, 0, ''),
(24, 'Livingroom', 25, '', 4, 0, ''),
(28, 'Bedroom', 25, '', 5, 0, ''),
(29, 'Kitchen', 25, '', 1, 0, ''),
(30, 'Hall', 25, '', 6, 0, ''),
(53, 'Bathroom', 25, '', 7, 0, ''),
(47, 'NightMode', 24, '', 0, 0, ''),
(54, 'Kinderroom', 25, '', 10, 0, ''),
(55, 'Toilet', 25, '', 8, 0, ''),
(68, 'admin', 32, '', 0, 0, '');

-- --------------------------------------------------------

--
-- Структура таблицы `operations_queue`
--

CREATE TABLE `operations_queue` (
  `TOPIC` char(255) NOT NULL,
  `DATANAME` char(255) NOT NULL,
  `DATAVALUE` char(255) NOT NULL,
  `EXPIRE` datetime NOT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `patterns`
--

CREATE TABLE `patterns` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `PATTERN` text DEFAULT NULL,
  `SCRIPT_ID` int(10) NOT NULL DEFAULT 0,
  `SCRIPT` text DEFAULT NULL,
  `LOG` text DEFAULT NULL,
  `TIME_LIMIT` int(245) NOT NULL DEFAULT 0,
  `EXECUTED` int(10) NOT NULL DEFAULT 0,
  `IS_CONTEXT` int(3) NOT NULL DEFAULT 0,
  `IS_COMMON_CONTEXT` int(3) NOT NULL DEFAULT 0,
  `MATCHED_CONTEXT_ID` int(10) NOT NULL DEFAULT 0,
  `TIMEOUT` int(10) NOT NULL DEFAULT 0,
  `TIMEOUT_CONTEXT_ID` int(10) NOT NULL DEFAULT 0,
  `TIMEOUT_SCRIPT` text DEFAULT NULL,
  `PARENT_ID` int(10) NOT NULL DEFAULT 0,
  `IS_LAST` int(3) NOT NULL DEFAULT 0,
  `PRIORITY` int(10) NOT NULL DEFAULT 0,
  `SCRIPT_EXIT` text DEFAULT NULL,
  `SKIPSYSTEM` int(3) NOT NULL DEFAULT 0,
  `ONETIME` int(3) NOT NULL DEFAULT 0,
  `PATTERN_TYPE` int(3) NOT NULL DEFAULT 0,
  `LINKED_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `LINKED_PROPERTY` varchar(255) NOT NULL DEFAULT '',
  `CONDITION` int(3) NOT NULL DEFAULT 0,
  `CONDITION_VALUE` varchar(255) NOT NULL DEFAULT '',
  `LATEST_VALUE` varchar(255) NOT NULL DEFAULT '',
  `ACTIVE_STATE` int(3) NOT NULL DEFAULT 0,
  `USEMORPHY` int(3) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `patterns`
--

INSERT INTO `patterns` (`ID`, `TITLE`, `PATTERN`, `SCRIPT_ID`, `SCRIPT`, `LOG`, `TIME_LIMIT`, `EXECUTED`, `IS_CONTEXT`, `IS_COMMON_CONTEXT`, `MATCHED_CONTEXT_ID`, `TIMEOUT`, `TIMEOUT_CONTEXT_ID`, `TIMEOUT_SCRIPT`, `PARENT_ID`, `IS_LAST`, `PRIORITY`, `SCRIPT_EXIT`, `SKIPSYSTEM`, `ONETIME`, `PATTERN_TYPE`, `LINKED_OBJECT`, `LINKED_PROPERTY`, `CONDITION`, `CONDITION_VALUE`, `LATEST_VALUE`, `ACTIVE_STATE`, `USEMORPHY`) VALUES
(9, '(start|play) music', '', 0, 'runScript(\'playFavoriteMusic\');', '', 0, 1409904443, 0, 0, 0, 0, 0, '', 0, 0, 0, NULL, 0, 0, 0, '', '', 0, '', '', 0, 0),
(8, 'report system state', '', 21, '', '2014-09-05 12:07:19 Pattern matched\n', 0, 1409908039, 0, 0, 0, 0, 0, '', 0, 0, 0, NULL, 0, 0, 0, '', '', 0, '', '', 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `performance_log`
--

CREATE TABLE `performance_log` (
  `ID` int(10) UNSIGNED NOT NULL,
  `OPERATION` varchar(255) NOT NULL DEFAULT '',
  `COUNTER` int(10) NOT NULL DEFAULT 0,
  `TIMEUSED` float NOT NULL DEFAULT 0,
  `SOURCE` char(10) NOT NULL DEFAULT '',
  `ADDED` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `phistory`
--

CREATE TABLE `phistory` (
  `ID` int(10) UNSIGNED NOT NULL,
  `VALUE_ID` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `ADDED` datetime DEFAULT NULL,
  `VALUE` varchar(255) NOT NULL,
  `SOURCE` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `phistory_queue`
--

CREATE TABLE `phistory_queue` (
  `ID` int(10) UNSIGNED NOT NULL,
  `VALUE_ID` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `VALUE` text DEFAULT NULL,
  `OLD_VALUE` text DEFAULT NULL,
  `KEEP_HISTORY` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `SOURCE` varchar(255) NOT NULL DEFAULT '',
  `ADDED` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `pinghosts`
--

CREATE TABLE `pinghosts` (
  `ID` int(10) UNSIGNED NOT NULL,
  `HOSTNAME` varchar(255) NOT NULL DEFAULT '',
  `TYPE` int(30) NOT NULL DEFAULT 0,
  `STATUS` int(3) NOT NULL DEFAULT 0,
  `SEARCH_WORD` varchar(255) NOT NULL DEFAULT '',
  `CHECK_LATEST` datetime DEFAULT NULL,
  `CHECK_NEXT` datetime DEFAULT NULL,
  `SCRIPT_ID_ONLINE` int(10) NOT NULL DEFAULT 0,
  `CODE_ONLINE` text DEFAULT NULL,
  `SCRIPT_ID_OFFLINE` int(10) NOT NULL DEFAULT 0,
  `CODE_OFFLINE` text DEFAULT NULL,
  `OFFLINE_INTERVAL` int(10) NOT NULL DEFAULT 0,
  `ONLINE_INTERVAL` int(10) NOT NULL DEFAULT 0,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `LOG` text DEFAULT NULL,
  `LINKED_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `LINKED_PROPERTY` varchar(255) NOT NULL DEFAULT '',
  `COUNTER_CURRENT` int(10) NOT NULL DEFAULT 0,
  `COUNTER_REQUIRED` int(10) NOT NULL DEFAULT 0,
  `STATUS_EXPECTED` int(3) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Дамп таблицы `pinghosts`
--

INSERT INTO `pinghosts` (`ID`, `HOSTNAME`, `TYPE`, `STATUS`, `SEARCH_WORD`, `CHECK_LATEST`, `CHECK_NEXT`, `SCRIPT_ID_ONLINE`, `CODE_ONLINE`, `SCRIPT_ID_OFFLINE`, `CODE_OFFLINE`, `OFFLINE_INTERVAL`, `ONLINE_INTERVAL`, `TITLE`, `LOG`, `LINKED_OBJECT`, `LINKED_PROPERTY`, `COUNTER_CURRENT`, `COUNTER_REQUIRED`, `STATUS_EXPECTED`) VALUES
(1, 'www.google.com', 0, 1, '', '2015-12-03 14:42:32', '2015-12-03 14:52:32', 0, '', 0, '', 600, 600, 'Internet', '', '', '', 0, 0, 1);
-- --------------------------------------------------------

--
-- Структура таблицы `plans`
--

CREATE TABLE `plans` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `IMAGE` varchar(255) NOT NULL DEFAULT '',
  `NEED_ZOOM` int(3) NOT NULL DEFAULT 0,
  `AUTO_ZOOM` int(3) NOT NULL DEFAULT 0,
  `CUSTOM_CSS` text DEFAULT NULL,
  `CUSTOM_JAVASCRIPT` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `plan_components`
--

CREATE TABLE `plan_components` (
  `ID` int(10) UNSIGNED NOT NULL,
  `PLAN_ID` int(10) NOT NULL DEFAULT 0,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `COMPONENT_NAME` varchar(255) NOT NULL DEFAULT '',
  `REPLACE_NAME` varchar(255) NOT NULL DEFAULT '',
  `ACTION_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `ACTION_METHOD` varchar(255) NOT NULL DEFAULT '',
  `CODE` text DEFAULT NULL,
  `SCRIPT_ID` int(10) NOT NULL DEFAULT 0,
  `MENU_ITEM_ID` int(10) NOT NULL DEFAULT 0,
  `HOMEPAGE_ID` int(10) NOT NULL DEFAULT 0,
  `EXT_URL` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `plan_components_data`
--

CREATE TABLE `plan_components_data` (
  `ID` int(10) UNSIGNED NOT NULL,
  `COMPONENT_ID` int(10) NOT NULL DEFAULT 0,
  `PROPERTY_NAME` varchar(255) NOT NULL DEFAULT '',
  `PROPERTY_VALUE` varchar(255) NOT NULL DEFAULT '',
  `LINKED_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `LINKED_PROPERTY` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `plan_states`
--

CREATE TABLE `plan_states` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `PLAN_ID` int(10) NOT NULL DEFAULT 0,
  `ITEM` varchar(255) NOT NULL DEFAULT '',
  `LINKED_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `LINKED_PROPERTY` varchar(255) NOT NULL DEFAULT '',
  `CONDITION` int(3) NOT NULL DEFAULT 0,
  `CONDITION_VALUE` varchar(255) NOT NULL DEFAULT '',
  `CONDITION_ADVANCED` text DEFAULT NULL,
  `CSS_CLASS` varchar(255) NOT NULL DEFAULT '',
  `CSS_CLASS_INVERSE` varchar(255) NOT NULL DEFAULT '',
  `ACTION_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `ACTION_METHOD` varchar(255) NOT NULL DEFAULT '',
  `CODE` text DEFAULT NULL,
  `SCRIPT_ID` int(10) NOT NULL DEFAULT 0,
  `MENU_ITEM_ID` int(10) NOT NULL DEFAULT 0,
  `HOMEPAGE_ID` int(10) NOT NULL DEFAULT 0,
  `EXT_URL` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `plugins`
--

CREATE TABLE `plugins` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `MODULE_NAME` varchar(255) NOT NULL DEFAULT '',
  `REPOSITORY_URL` char(255) NOT NULL DEFAULT '',
  `AUTHOR` varchar(255) NOT NULL DEFAULT '',
  `SUPPORT_URL` char(255) NOT NULL DEFAULT '',
  `DESCRIPTION_RU` text DEFAULT NULL,
  `DESCRIPTION_EN` text DEFAULT NULL,
  `CURRENT_VERSION` varchar(255) NOT NULL DEFAULT '',
  `LATEST_VERSION` varchar(255) NOT NULL DEFAULT '',
  `IS_INSTALLED` int(3) NOT NULL DEFAULT 0,
  `WHATSNEW` text DEFAULT NULL,
  `LATEST_UPDATE` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `project_modules`
--

CREATE TABLE `project_modules` (
  `ID` int(10) UNSIGNED NOT NULL,
  `NAME` varchar(50) NOT NULL DEFAULT '',
  `TITLE` varchar(100) NOT NULL DEFAULT '',
  `CATEGORY` varchar(50) NOT NULL DEFAULT '',
  `PARENT_NAME` varchar(50) NOT NULL DEFAULT '',
  `DATA` text DEFAULT NULL,
  `HIDDEN` int(3) NOT NULL DEFAULT 0,
  `PRIORITY` int(10) NOT NULL DEFAULT 0,
  `ADDED` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `project_modules`
--

INSERT INTO `project_modules` (`ID`, `NAME`, `TITLE`, `CATEGORY`, `PARENT_NAME`, `DATA`, `HIDDEN`, `PRIORITY`, `ADDED`) VALUES
(1, 'control_modules', '<#LANG_MODULE_MODULES#>', '<#LANG_SECTION_SYSTEM#>', '', '', 0, 0, '2015-01-29 08:24:27'),
(67, 'scripts', '<#LANG_MODULE_SCRIPTS#>', '<#LANG_SECTION_OBJECTS#>', '', '', 0, 0, '2010-09-13 15:12:16'),
(4, 'control_access', 'Control Access', '<#LANG_SECTION_SYSTEM#>', '', '', 1, 0, '2011-09-29 18:16:01'),
(7, 'master', '<#LANG_MODULE_MASTER_LOGIN#>', '<#LANG_SECTION_SYSTEM#>', '', '', 1, 0, '2014-10-30 11:57:40'),
(70, 'pinghosts', '<#LANG_MODULE_PINGHOSTS#>', '<#LANG_SECTION_DEVICES#>', '', '', 0, 0, '2011-01-05 22:02:57'),
(20, 'saverestore', '<#LANG_MODULE_SAVERESTORE#>', '<#LANG_SECTION_SYSTEM#>', '', 'a:1:{s:17:\"LATEST_UPDATED_ID\";s:40:\"14d4bd4fd3c4cfc18f28094d3cb9eaa4861c62c1\";}', 0, 0, '2009-02-07 11:35:05'),
(21, 'userlog', '<#LANG_MODULE_USERLOG#>', '<#LANG_SECTION_SYSTEM#>', '', '', 1, 0, '2009-02-07 11:45:52'),
(22, 'skins', '<#LANG_MODULE_SKINS#>', '<#LANG_SECTION_SYSTEM#>', '', '', 1, 0, '2009-02-07 12:02:54'),
(23, 'settings', '<#LANG_MODULE_SETTINGS#>', '<#LANG_SECTION_SETTINGS#>', '', '', 0, 0, '2009-02-07 12:05:40'),
(24, 'dateselect', 'Date Selector', 'System', '', '', 1, 0, '2009-02-07 12:47:32'),
(25, 'thumb', '<#LANG_MODULE_THUMB#>', '<#LANG_SECTION_SYSTEM#>', '', '', 1, 0, '2009-02-07 12:48:32'),
(73, 'app_player', '<#LANG_APP_PLAYER#>', '<#LANG_SECTION_APPLICATIONS#>', '', '', 0, 0, '2011-05-03 11:02:57'),
(28, 'dashboard', 'Dashboard', 'CMS', '', 'a:5:{s:12:\"CPANEL_STATS\";i:0;s:15:\"CPANEL_USERNAME\";s:0:\"\";s:15:\"CPANEL_PASSWORD\";s:0:\"\";s:13:\"CPANEL_DOMAIN\";s:0:\"\";s:10:\"CPANEL_URL\";s:0:\"\";}', 1, 0, '2009-02-23 10:15:23'),
(29, 'events', '<#LANG_MODULE_EVENTS#>', '<#LANG_SECTION_SYSTEM#>', '', '', 0, 0, '2020-07-08 14:02:17'),
(30, 'users', '<#LANG_MODULE_USERS#>', '<#LANG_SECTION_SETTINGS#>', '', '', 0, 0, '2009-03-27 13:08:07'),
(31, 'terminals', '<#LANG_MODULE_TERMINALS#>', '<#LANG_SECTION_SETTINGS#>', '', '', 0, 0, '2009-03-27 13:10:00'),
(34, 'commands', '<#LANG_MODULE_CONTROL_MENU#>', '<#LANG_SECTION_OBJECTS#>', '', '', 0, 0, '2009-04-11 03:14:03'),
(37, 'classes', '<#LANG_MODULE_OBJECTS#>', '<#LANG_SECTION_OBJECTS#>', '', 'a:3:{s:12:\"DEFAULT_VIEW\";s:0:\"\";s:15:\"FILTER_CLASS_ID\";i:0;s:18:\"FILTER_LOCATION_ID\";i:0;}', 0, 0, '2009-05-22 10:09:27'),
(38, 'history', '<#LANG_MODULE_OBJECTS_HISTORY#>', '<#LANG_SECTION_OBJECTS#>', '', '', 1, 0, '2009-05-22 10:09:51'),
(39, 'locations', '<#LANG_MODULE_LOCATIONS#>', '<#LANG_SECTION_SETTINGS#>', '', '', 0, 0, '2009-05-22 10:11:01'),
(40, 'methods', '<#LANG_MODULE_METHODS#>', '<#LANG_SECTION_OBJECTS#>', '', '', 1, 0, '2009-05-22 10:11:23'),
(41, 'properties', '<#LANG_MODULE_PROPERTIES#>', '<#LANG_SECTION_OBJECTS#>', '', '', 1, 0, '2009-05-22 10:11:47'),
(42, 'objects', '<#LANG_MODULE_OBJECT_INSTANCES#>', '<#LANG_SECTION_OBJECTS#>', '', '', 1, 0, '2009-05-22 10:12:04'),
(85, 'pvalues', '<#LANG_MODULE_PVALUES#>', '<#LANG_SECTION_OBJECTS#>', '', '', 1, 0, '2012-11-16 15:04:26'),
(44, 'shoutbox', '<#LANG_MODULE_SHOUTBOX#>', '<#LANG_SECTION_SYSTEM#>', '', '', 1, 0, '2009-07-29 13:53:13'),
(45, 'shoutrooms', '<#LANG_MODULE_SHOUTROOMS#>', '<#LANG_SECTION_SYSTEM#>', '', '', 1, 0, '2009-07-29 13:53:28'),
(46, 'jobs', '<#LANG_MODULE_JOBS#>', '<#LANG_SECTION_SYSTEM#>', '', '', 1, 0, '2014-10-30 11:57:40'),
(81, 'scenes', '<#LANG_MODULE_SCENES#>', '<#LANG_SECTION_OBJECTS#>', '', '', 0, 0, '2012-06-25 09:34:26'),
(61, 'app_mediabrowser', '<#LANG_APP_MEDIA_BROWSER#>', '<#LANG_SECTION_APPLICATIONS#>', '', '', 0, 0, '2010-08-31 09:09:33'),
(66, 'layouts', '<#LANG_MODULE_LAYOUTS#>', '<#LANG_SECTION_SETTINGS#>', '', '', 0, 0, '2010-09-13 15:03:49'),
(77, 'patterns', '<#LANG_MODULE_PATTERNS#>', '<#LANG_SECTION_OBJECTS#>', '', '', 0, 0, '2011-12-13 14:36:03'),
(83, 'xray', 'X-Ray', '<#LANG_SECTION_SYSTEM#>', '', '', 0, 0, '2012-11-16 14:59:57'),
(88, 'security_rules', '<#LANG_MODULE_SECURITY_RULES#>', '<#LANG_SECTION_SETTINGS#>', '', '', 0, 0, '2013-05-18 11:31:37'),
(90, 'connect', '<#LANG_MODULE_CONNECT#>', '<#LANG_SECTION_SYSTEM#>', '', 'a:7:{s:16:\"CONNECT_USERNAME\";s:0:\"\";s:16:\"CONNECT_PASSWORD\";s:0:\"\";s:12:\"CONNECT_SYNC\";i:0;s:9:\"SEND_MENU\";i:1;s:12:\"SEND_OBJECTS\";i:1;s:12:\"SEND_SCRIPTS\";i:1;s:13:\"SEND_PATTERNS\";i:1;}', 0, 0, '2013-08-09 10:02:04'),
(93, 'market', '<#LANG_MODULE_MARKET#>', '<#LANG_SECTION_SYSTEM#>', '', '', 0, 0, '2014-01-25 14:04:20'),
(97, 'blockly_code', 'Blockly code', '<#LANG_SECTION_SYSTEM#>', '', '', 1, 0, '2014-09-03 09:28:23'),
(98, 'myblocks', '<#LANG_MODULE_MYBLOCKS#>', '<#LANG_SECTION_SETTINGS#>', '', '', 0, 0, '2014-10-30 11:57:40'),
(99, 'soundfiles', '<#LANG_MODULE_SOUNDFILES#>', '<#LANG_SECTION_SETTINGS#>', '', '', 0, 0, '2014-10-30 11:57:41'),
(100, 'textfiles', '<#LANG_MODULE_TEXTFILES#>', '<#LANG_SECTION_SETTINGS#>', '', '', 0, 0, '2014-10-30 11:57:41'),
(101, 'linkedobject', 'LinkedObject', '<#LANG_SECTION_SYSTEM#>', '', '', 1, 0, '2015-01-29 08:24:25'),
(102, 'system_errors', '<#LANG_MODULE_SYSTEM_ERRORS#>', '<#LANG_SECTION_SYSTEM#>', '', '', 0, 0, '2015-01-29 08:24:26'),
(108, 'devices', 'Simple Devices', '<#LANG_SECTION_DEVICES#>', '', NULL, 0, 0, '2020-07-08 14:55:07'),
(109, 'actions_log', '<#LANG_MODULE_ACTIONS_LOG#>', '<#LANG_SECTION_SYSTEM#>', '', NULL, 0, 0, '2020-07-08 14:02:17'),
(110, 'plans', '<#LANG_MODULE_PLANS#>', '<#LANG_SECTION_OBJECTS#>', '', NULL, 0, 0, '2020-07-08 14:02:18');

-- --------------------------------------------------------

--
-- Структура таблицы `properties`
--

CREATE TABLE `properties` (
  `ID` int(10) UNSIGNED NOT NULL,
  `CLASS_ID` int(10) NOT NULL DEFAULT 0,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `DESCRIPTION` text DEFAULT NULL,
  `OBJECT_ID` int(10) NOT NULL DEFAULT 0,
  `KEEP_HISTORY` int(10) NOT NULL DEFAULT 0,
  `ONCHANGE` varchar(255) NOT NULL DEFAULT '',
  `SYSTEM` varchar(255) NOT NULL DEFAULT '',
  `DATA_KEY` int(3) NOT NULL DEFAULT 0,
  `DATA_TYPE` int(3) NOT NULL DEFAULT 0,
  `VALIDATION_TYPE` int(3) NOT NULL DEFAULT 0,
  `VALIDATION_NUM_MIN` varchar(20) NOT NULL DEFAULT '',
  `VALIDATION_NUM_MAX` varchar(20) NOT NULL DEFAULT '',
  `VALIDATION_LIST` varchar(255) NOT NULL DEFAULT '',
  `VALIDATION_CODE` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `properties`
--

INSERT INTO `properties` (`ID`, `CLASS_ID`, `TITLE`, `DESCRIPTION`, `OBJECT_ID`, `KEEP_HISTORY`, `ONCHANGE`, `SYSTEM`, `DATA_KEY`, `DATA_TYPE`, `VALIDATION_TYPE`, `VALIDATION_NUM_MIN`, `VALIDATION_NUM_MAX`, `VALIDATION_LIST`, `VALIDATION_CODE`) VALUES
(255, 0, 'cycle_wscacheRun', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(256, 0, 'WSClientsTotal', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(10, 10, 'checked', 'время последней проверки', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(17, 0, 'testProp', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(12, 7, 'time', 'текущее время', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(18, 10, 'minMsgLevel', '', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(20, 0, 'weatherFull', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(21, 0, 'AlarmTime', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(22, 0, 'textBoxTest', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(23, 0, '1w_temp', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(257, 0, 'uptimeText', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(41, 21, 'stateDetails', 'details for the state', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(42, 21, 'stateColor', 'green / yellow / red', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(43, 0, 'TempOutside', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(44, 0, 'Econom', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(45, 0, 'securityMode', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(46, 0, 'nobodyHome', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(47, 0, 'WeHaveGuests', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(250, 0, 'cycle_pingRun', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(55, 24, 'active', '', 0, 0, 'modeChanged', '', 0, 0, 0, '', '', '', NULL),
(56, 24, 'title', '', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(57, 24, 'updated', '', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(58, 24, 'updatedTime', '', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(59, 25, 'LatestActivity', '', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(60, 25, 'LatestActivityTime', '', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(61, 25, 'SomebodyHere', 'Somebody in the room', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(62, 25, 'Temperature', 'Temperature', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(253, 0, 'cycle_phistoryRun', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(251, 0, 'cycle_statesRun', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(249, 0, 'cycle_mainRun', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(248, 0, 'cycle_schedulerRun', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(76, 0, 'stateTitle', NULL, 17, 0, '', '', 0, 0, 0, '', '', '', NULL),
(77, 0, 'stateTitle', NULL, 18, 0, '', '', 0, 0, 0, '', '', '', NULL),
(78, 0, 'stateTitle', NULL, 16, 0, '', '', 0, 0, 0, '', '', '', NULL),
(83, 0, 'volumeLevel', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(152, 0, 'volumeMediaLevel', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(98, 0, 'somebodyHome', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(101, 0, 'timeNow', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(102, 0, 'HomeStatus', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(103, 0, 'isDark', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(104, 0, 'SunSetTime', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(105, 0, 'SunRiseTime', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(106, 0, 'somebodyHomeText', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(254, 0, 'cycle_connectRun', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(108, 0, 'wunderHost', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(110, 0, 'weatherTomorrow', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(111, 0, 'weatherTomorrowweatherToday', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(112, 0, 'weatherToday', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(113, 0, 'connect_manualRun', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(114, 0, 'clockChimeEnabled', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(127, 25, 'Humidity', 'Humidity', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(132, 25, 'Title', '', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(134, 32, 'atHome', '', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(135, 32, 'Coordinates', '', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(136, 32, 'CoordinatesUpdated', '', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(137, 32, 'CoordinatesUpdatedTimestamp', '', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(138, 32, 'fullName', '', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(139, 32, 'isMoving', '', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(140, 32, 'seenAt', '', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(143, 0, 'lastNewsRead', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(144, 0, 'NoolightCommandSend', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(145, 0, 'AlreadyPlayedMusic', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(252, 0, 'cycle_websocketsRun', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(247, 0, 'cycle_execsRun', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(148, 0, 'lastSayTime', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(149, 0, 'uptime', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(150, 0, 'started_time', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL),
(153, 25, 'IdleDelay', 'Nobody here idle delay', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(154, 34, 'status', 'Status', 0, 365, 'statusUpdated', '', 1, 0, 0, '', '', '', NULL),
(155, 34, 'alive', 'Alive', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(156, 34, 'aliveTimeout', 'Possible inactivity timeout (hours)', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(157, 34, 'linkedRoom', 'LinkedRoom', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(158, 34, 'updated', 'Updated Timestamp', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(159, 34, 'updatedText', 'Updated Time (text)', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(160, 35, 'groupEco', 'Turn it off in ECO mode', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(161, 35, 'groupEcoOn', 'Turn it on leaving ECO mode', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(162, 35, 'groupSunrise', 'Turn it off with Sunrise', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(163, 35, 'groupSunset', 'Turn it on with Sunset', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(164, 35, 'groupNight', 'Turn it off entering Night mode', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(165, 35, 'isActivity', 'Status change means activity in the room', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(166, 35, 'loadType', 'Device type', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(167, 35, 'icon', 'Image', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(168, 36, 'groupName', 'Group system name', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(169, 38, 'relay_status', 'Relay status', 0, 365, '', '', 1, 0, 0, '', '', '', NULL),
(170, 38, 'value', 'Current temperature', 0, 365, 'valueUpdated', '', 1, 0, 0, '', '', '', NULL),
(171, 38, 'currentTargetValue', 'Current target temperature', 0, 0, '', '', 1, 0, 0, '', '', '', NULL),
(172, 38, 'normalTargetValue', 'Normal target temperature', 0, 0, 'valueUpdated', '', 0, 0, 0, '', '', '', NULL),
(173, 38, 'ecoTargetValue', 'ECO target temperature', 0, 0, 'valueUpdated', '', 0, 0, 0, '', '', '', NULL),
(174, 38, 'threshold', 'Temperature threshold (0.25 by default)', 0, 0, 'valueUpdated', '', 0, 0, 0, '', '', '', NULL),
(175, 38, 'ncno', 'Device/Sensor type', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(176, 38, 'disabled', 'Disabled', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(177, 39, 'level', 'Current brightness level', 0, 0, 'levelUpdated', '', 1, 0, 0, '', '', '', NULL),
(178, 39, 'levelSaved', 'Latest level saved', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(179, 39, 'levelWork', 'Brightness level (work)', 0, 0, 'levelWorkUpdated', '', 0, 0, 0, '', '', '', NULL),
(180, 39, 'minWork', 'Min level (work)', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(181, 39, 'maxWork', 'Max level (work)', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(182, 39, 'setMaxTurnOn', 'Set maximum level when turning on', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(183, 40, 'color', 'Current color', 0, 0, 'colorUpdated', '', 1, 0, 0, '', '', '', NULL),
(184, 40, 'colorSaved', 'Saved color', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(185, 40, 'brightness', 'Current brightness', 0, 0, 'colorUpdated', '', 0, 0, 0, '', '', '', NULL),
(186, 41, 'ignoreNobodysHome', 'Ignore device events when nobody\'s home', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(187, 41, 'resetNobodysHome', 'Reset \"nobody home\" status w/o setting timer', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(188, 41, 'timeout', 'Activity timeout (sec)', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(189, 41, 'blocked', 'Is blocked', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(190, 42, 'streamURL', 'Stream URL (LQ)', 0, 0, 'updatePreview', '', 0, 0, 0, '', '', '', NULL),
(191, 42, 'streamURL_HQ', 'Stream URL (HQ)', 0, 0, 'updatePreview', '', 0, 0, 0, '', '', '', NULL),
(192, 42, 'cameraUsername', 'Camera Username', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(193, 42, 'cameraPassword', 'Camera Password', 0, 0, 'updatePreview', '', 0, 0, 0, '', '', '', NULL),
(194, 42, 'streamTransport', 'Stream transport', 0, 0, 'updatePreview', '', 0, 0, 0, '', '', '', NULL),
(195, 42, 'previewType', 'Preview image', 0, 0, 'updatePreview', '', 0, 0, 0, '', '', '', NULL),
(196, 42, 'clickAction', 'OnClick action', 0, 0, 'updatePreview', '', 0, 0, 0, '', '', '', NULL),
(197, 42, 'snapshotURL', 'Snapshot URL', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(198, 42, 'snapshot', 'Snapshot', 0, 365, '', '', 0, 5, 0, '', '', '', NULL),
(199, 42, 'series', 'Snapshot', 0, 30, '', '', 0, 5, 0, '', '', '', NULL),
(200, 42, 'snapshotPreviewURL', 'Snapshot Preview URL', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(201, 42, 'previewHTML', 'Preview HTML', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(202, 42, 'activeHTML', 'Active HTML', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(203, 42, 'ignoreNobodysHome', 'Ignore device events when nobody\'s home', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(204, 42, 'timeout', 'Activity timeout (sec)', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(205, 43, 'isActivity', 'Status change means activity in the room', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(206, 43, 'ncno', 'Device/Sensor type', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(207, 43, 'notify_status', 'Notify status', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(208, 43, 'notify_nc', 'Notify when left open', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(209, 43, 'blocked', 'Is blocked', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(210, 44, 'notify_status', 'Notify status', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(211, 44, 'notify_nc', 'Notify when left open', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(212, 44, 'openType', 'Device type', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(213, 44, 'notify_msg_opening', 'Opening message text', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(214, 44, 'notify_msg_closing', 'Closing message text', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(215, 44, 'notify_msg_reminder', 'Reminder message text', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(216, 45, 'notify_eliminated', 'Notify every minute until problem solved', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(217, 45, 'blocked', 'Is blocked', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(218, 46, 'notify_eliminated', 'Notify every minute until problem solved', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(219, 46, 'blocked', 'Is blocked', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(220, 47, 'unit', 'Units', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(221, 47, 'value', 'Data Value', 0, 365, 'valueUpdated', '', 1, 0, 0, '', '', '', NULL),
(222, 47, 'valueWork', 'Work Value', 0, 0, 'valueWorkUpdated', '', 0, 0, 0, '', '', '', NULL),
(223, 47, 'valueHour', 'Hour Value', 0, 365, '', '', 0, 0, 0, '', '', '', NULL),
(224, 47, 'valueDay', 'Day Value', 0, 1825, '', '', 0, 0, 0, '', '', '', NULL),
(225, 47, 'valueMonth', 'Month Value', 0, 1825, '', '', 0, 0, 0, '', '', '', NULL),
(226, 47, 'conversion', 'Conversion coefficient (work-to-data)', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(227, 48, 'icon', 'Image', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(228, 48, 'isActivity', 'Status change means activity in the room', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(229, 49, 'value', 'Current Sensor Value', 0, 365, 'valueUpdated', '', 1, 0, 0, '', '', '', NULL),
(230, 49, 'minValue', 'Minimum value', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(231, 49, 'maxValue', 'Maximum value', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(232, 49, 'notify', 'Notify when value out of range', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(233, 49, 'notify_eliminated', 'Notify every minute until problem solved', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(234, 49, 'mainSensor', 'Main sensor for the room', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(235, 49, 'normalValue', 'Value within range', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(236, 49, 'direction', 'Direction of changes', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(237, 49, 'directionTimeout', 'Direction tracking time period (seconds)', 0, 0, 'valueUpdated', '', 0, 0, 0, '', '', '', NULL),
(238, 49, 'blocked', 'Is blocked', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(239, 50, 'unit', 'Units', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(240, 56, 'loadStatusTimeout', 'Load status timeout', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(241, 59, 'unit', 'Units', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(242, 60, 'data', 'Data', 0, 0, '', '', 0, 0, 0, '', '', '', NULL),
(243, 0, 'Serial', NULL, 7, 0, '', '', 0, 0, 0, '', '', '', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `public_calls`
--

CREATE TABLE `public_calls` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `LINKED_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `LINKED_METHOD` varchar(255) NOT NULL DEFAULT '',
  `PUBLIC_USERNAME` varchar(255) NOT NULL DEFAULT '',
  `PUBLIC_PASSWORD` varchar(255) NOT NULL DEFAULT '',
  `PUBLIC_KEY` varchar(255) NOT NULL DEFAULT '',
  `PROTECTED` int(3) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `pvalues`
--

CREATE TABLE `pvalues` (
  `ID` int(10) UNSIGNED NOT NULL,
  `PROPERTY_ID` int(10) NOT NULL DEFAULT 0,
  `OBJECT_ID` int(10) NOT NULL DEFAULT 0,
  `VALUE` text NOT NULL,
  `UPDATED` datetime DEFAULT NULL,
  `PROPERTY_NAME` varchar(100) NOT NULL DEFAULT '',
  `LINKED_MODULES` varchar(255) NOT NULL DEFAULT '',
  `SOURCE` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `pvalues`
--

INSERT INTO `pvalues` (`ID`, `PROPERTY_ID`, `OBJECT_ID`, `VALUE`, `UPDATED`, `PROPERTY_NAME`, `LINKED_MODULES`, `SOURCE`) VALUES
(59, 18, 7, '1', '2014-10-30 15:02:45', 'ThisComputer.minMsgLevel', '', ''),
(60, 17, 7, '-15', '2014-10-30 15:02:45', 'ThisComputer.testProp', '', ''),
(58, 10, 7, '1346415251', '2014-10-30 15:02:45', 'ThisComputer.checked', '', ''),
(24, 12, 6, '2015-12-03 14:49:00', '2015-12-03 14:49:00', 'ClockChime.time', '', ''),
(61, 20, 7, '\n<b>Сегодня:</b><br />\nднем: +0&deg;...+2&deg;, пасмурно, туман, ночью: +0&deg;...-2&deg;, переменная облачность, туман, ветер: ЮЗ — 3-5 м/с, давление: 770 мм.рт.ст, влажность: 100%<br />\n<br />\n<b>Завтра:</b><br />\nднем: +4&deg;...+6&deg;, пасмурно, ночью: +2&deg;...+4&deg;, пасмурно, без существенных осадков, ветер: Ю — 6-8 м/с, давление: 768 мм.рт.ст, влажность: 100%<br />\n<br />\n<br />\n', '2015-12-03 14:42:32', 'ThisComputer.weatherFull', '', ''),
(80, 43, 7, '-0.9', '2015-12-03 14:42:33', 'ThisComputer.TempOutside', '', ''),
(62, 21, 7, '09:30', '2014-10-30 15:02:45', 'ThisComputer.AlarmTime', 'commands', ''),
(63, 22, 7, '0', '2014-10-30 15:02:45', 'ThisComputer.textBoxTest', 'commands', ''),
(64, 23, 7, '4', '2014-10-30 15:02:45', 'ThisComputer.1w_temp', '', ''),
(326, 257, 7, '', '2020-07-08 17:55:45', 'ThisComputer.uptimeText', '', ''),
(81, 44, 7, '0', '2014-10-30 15:02:45', 'ThisComputer.Econom', '', ''),
(82, 45, 7, '0', '2014-10-30 15:02:45', 'ThisComputer.securityMode', '', ''),
(83, 46, 7, '0', '2014-10-30 15:02:45', 'ThisComputer.nobodyHome', '', ''),
(84, 47, 7, '0', '2014-10-30 15:02:45', 'ThisComputer.WeHaveGuests', '', ''),
(320, 251, 7, '1594220111', '2020-07-08 17:55:11', 'ThisComputer.cycle_statesRun', '', '/scripts/cycle_states.php?once=1'),
(321, 252, 7, '1594220107', '2020-07-08 17:55:07', 'ThisComputer.cycle_websocketsRun', '', ''),
(322, 253, 7, '1594220108', '2020-07-08 17:55:08', 'ThisComputer.cycle_phistoryRun', '', ''),
(323, 254, 7, '', '2020-07-08 17:55:10', 'ThisComputer.cycle_connectRun', '', ''),
(318, 249, 7, '1594220107', '2020-07-08 17:55:07', 'ThisComputer.cycle_mainRun', '', ''),
(319, 250, 7, '1594220107', '2020-07-08 17:55:07', 'ThisComputer.cycle_pingRun', '', ''),
(317, 248, 7, '1594220107', '2020-07-08 17:55:07', 'ThisComputer.cycle_schedulerRun', '', ''),
(93, 42, 17, 'green', '2015-12-03 14:46:48', 'System.stateColor', '', ''),
(94, 76, 17, 'Зелёный', '2015-12-03 14:46:48', 'System.stateTitle', '', ''),
(95, 42, 18, 'green', '2014-10-31 15:33:55', 'Communication.stateColor', '', ''),
(96, 77, 18, 'Green', '2014-10-31 15:33:55', 'Communication.stateTitle', '', ''),
(97, 42, 16, 'green', '2013-08-09 13:29:00', 'Security.stateColor', '', ''),
(98, 78, 16, 'Green', '2013-08-09 13:29:00', 'Security.stateTitle', '', ''),
(99, 41, 17, '', '2015-12-03 14:46:48', 'System.stateDetails', '', ''),
(310, 136, 68, '10:00', '2015-12-03 14:47:55', 'admin.CoordinatesUpdated', '', ''),
(308, 134, 68, '', '2015-12-03 14:47:55', 'admin.atHome', '', ''),
(309, 135, 68, '', '2015-12-03 14:47:55', 'admin.Coordinates', '', ''),
(100, 83, 7, '90', '2014-10-30 15:02:45', 'ThisComputer.volumeLevel', '', ''),
(157, 110, 7, ' +4&deg;...+6&deg;, пасмурно', '2015-12-03 14:42:36', 'ThisComputer.weatherTomorrow', '', ''),
(191, 62, 29, '22.5', '2014-09-05 12:44:59', 'Kitchen.Temperature', '', ''),
(120, 59, 30, '1409901138', '2014-09-05 10:12:18', 'Hall.LatestActivity', '', ''),
(121, 60, 30, '10:12', '2014-09-05 10:12:18', 'Hall.LatestActivityTime', '', ''),
(122, 61, 30, '0', '2014-09-05 10:22:19', 'Hall.SomebodyHere', '', ''),
(123, 98, 7, '1', '2014-10-30 15:02:45', 'ThisComputer.somebodyHome', '', ''),
(124, 55, 20, '0', '2014-09-05 10:12:16', 'NobodyHomeMode.active', '', ''),
(125, 57, 20, '1409901136', '2014-09-05 10:12:16', 'NobodyHomeMode.updated', '', ''),
(126, 58, 20, '10:12', '2014-09-05 10:12:16', 'NobodyHomeMode.updatedTime', '', ''),
(128, 59, 24, '1409838134', '2014-09-04 16:42:14', 'Livingroom.LatestActivity', '', ''),
(129, 60, 24, '16:42', '2014-09-04 16:42:14', 'Livingroom.LatestActivityTime', '', ''),
(130, 61, 24, '0', '2014-09-04 16:52:14', 'Livingroom.SomebodyHere', '', ''),
(132, 59, 29, '1409838113', '2014-09-04 16:41:53', 'Kitchen.LatestActivity', '', ''),
(133, 60, 29, '16:41', '2014-09-04 16:41:53', 'Kitchen.LatestActivityTime', '', ''),
(134, 61, 29, '0', '2014-09-04 16:51:53', 'Kitchen.SomebodyHere', '', ''),
(135, 56, 20, 'Никого нет дома', '2014-07-21 12:43:30', 'NobodyHomeMode.title', '', ''),
(136, 55, 19, '0', '2014-09-05 10:12:16', 'EconomMode.active', '', ''),
(137, 57, 19, '1409901136', '2014-09-05 10:12:16', 'EconomMode.updated', '', ''),
(138, 58, 19, '10:12', '2014-09-05 10:12:16', 'EconomMode.updatedTime', '', ''),
(139, 56, 19, 'Экономия', '2014-07-21 12:44:15', 'EconomMode.title', '', ''),
(140, 55, 23, '0', '2015-01-29 12:51:00', 'DarknessMode.active', '', ''),
(141, 57, 23, '1422521460', '2015-01-29 12:51:00', 'DarknessMode.updated', '', ''),
(142, 58, 23, '12:51', '2015-01-29 12:51:00', 'DarknessMode.updatedTime', '', ''),
(143, 56, 23, 'Темное время суток', '2014-07-21 12:44:48', 'DarknessMode.title', '', ''),
(144, 101, 7, '14:49', '2015-12-03 14:49:00', 'ThisComputer.timeNow', '', ''),
(145, 102, 7, '14:49 Дома кто-то есть   ', '2015-12-03 14:49:00', 'ThisComputer.HomeStatus', '', ''),
(146, 103, 7, '0', '2015-01-29 12:51:00', 'ThisComputer.isDark', '', ''),
(147, 55, 47, '0', '2014-09-05 08:00:00', 'NightMode.active', '', ''),
(148, 57, 47, '1409893200', '2014-09-05 08:00:00', 'NightMode.updated', '', ''),
(149, 58, 47, '08:00', '2014-09-05 08:00:00', 'NightMode.updatedTime', '', ''),
(150, 56, 47, 'Ночной', '2014-07-21 12:51:25', 'NightMode.title', '', ''),
(151, 104, 7, '16:45', '2015-12-03 14:42:34', 'ThisComputer.SunSetTime', '', ''),
(152, 105, 7, '09:12', '2015-12-03 14:42:35', 'ThisComputer.SunRiseTime', '', ''),
(153, 106, 7, '', '2014-10-30 15:02:45', 'ThisComputer.somebodyHomeText', '', ''),
(158, 111, 7, ' +31°...+33°, переменная облачность', '2014-10-30 15:02:45', 'ThisComputer.weatherTomorrowweatherToday', '', ''),
(325, 256, 7, '2', '2020-07-08 17:55:15', 'ThisComputer.WSClientsTotal', '', ''),
(155, 108, 7, '', '2014-10-30 15:02:45', 'ThisComputer.wunderHost', '', ''),
(159, 112, 7, ' +0&deg;...+2&deg;, пасмурно, туман', '2015-12-03 14:42:37', 'ThisComputer.weatherToday', '', ''),
(160, 113, 7, '1407249215', '2014-10-30 15:02:45', 'ThisComputer.connect_manualRun', '', ''),
(161, 114, 7, '1', '2014-10-30 15:02:45', 'ThisComputer.clockChimeEnabled', 'commands', ''),
(213, 59, 54, '1409911260', '2014-09-05 13:01:00', 'Kinderroom.LatestActivity', '', ''),
(214, 60, 54, '13:01', '2014-09-05 13:01:00', 'Kinderroom.LatestActivityTime', '', ''),
(215, 61, 54, '1', '2014-09-05 13:01:00', 'Kinderroom.SomebodyHere', '', ''),
(223, 59, 28, '1409838048', '2014-09-04 16:40:48', 'Bedroom.LatestActivity', '', ''),
(224, 60, 28, '16:40', '2014-09-04 16:40:48', 'Bedroom.LatestActivityTime', '', ''),
(225, 61, 28, '0', '2014-09-04 16:50:48', 'Bedroom.SomebodyHere', '', ''),
(242, 62, 24, '22.4', '2014-09-05 12:26:26', 'Livingroom.Temperature', '', ''),
(243, 127, 29, '43', '2014-09-05 12:44:59', 'Kitchen.Humidity', '', ''),
(251, 62, 28, '22.5', '2014-09-05 12:54:30', 'Bedroom.Temperature', '', ''),
(253, 127, 24, '42', '2014-09-05 12:26:26', 'Livingroom.Humidity', '', ''),
(266, 59, 53, '', '2014-09-04 16:07:54', 'Bathroom.LatestActivity', '', ''),
(267, 60, 53, '', '2014-09-04 16:07:54', 'Bathroom.LatestActivityTime', '', ''),
(268, 61, 53, '', '2014-09-04 16:07:54', 'Bathroom.SomebodyHere', '', ''),
(269, 62, 53, '', '2014-09-04 16:07:54', 'Bathroom.Temperature', '', ''),
(270, 127, 53, '', '2014-09-04 16:07:54', 'Bathroom.Humidity', '', ''),
(271, 132, 53, 'Ванная', '2014-09-04 16:07:54', 'Bathroom.Title', '', ''),
(272, 127, 28, '42', '2014-09-05 12:54:30', 'Bedroom.Humidity', '', ''),
(273, 132, 28, 'Спальня', '2014-09-04 16:08:08', 'Bedroom.Title', '', ''),
(274, 62, 30, '', '2014-09-04 16:08:23', 'Hall.Temperature', '', ''),
(275, 127, 30, '', '2014-09-04 16:08:23', 'Hall.Humidity', '', ''),
(276, 132, 30, 'Коридор', '2014-09-04 16:08:23', 'Hall.Title', '', ''),
(277, 62, 54, '', '2014-09-04 16:08:36', 'Kinderroom.Temperature', '', ''),
(278, 127, 54, '', '2014-09-04 16:08:36', 'Kinderroom.Humidity', '', ''),
(279, 132, 54, 'Детская', '2014-09-04 16:08:36', 'Kinderroom.Title', '', ''),
(280, 132, 29, 'Кухня', '2014-09-04 16:08:48', 'Kitchen.Title', '', ''),
(281, 132, 24, 'Гостиная', '2014-09-04 16:09:01', 'Livingroom.Title', '', ''),
(282, 59, 55, '', '2014-09-04 16:09:14', 'Toilet.LatestActivity', '', ''),
(283, 60, 55, '', '2014-09-04 16:09:14', 'Toilet.LatestActivityTime', '', ''),
(284, 61, 55, '', '2014-09-04 16:09:14', 'Toilet.SomebodyHere', '', ''),
(285, 62, 55, '', '2014-09-04 16:09:14', 'Toilet.Temperature', '', ''),
(286, 127, 55, '', '2014-09-04 16:09:14', 'Toilet.Humidity', '', ''),
(287, 132, 55, 'Туалет', '2014-09-04 16:09:14', 'Toilet.Title', '', ''),
(294, 41, 18, '', '2014-10-31 15:33:55', 'Communication.stateDetails', '', ''),
(299, 143, 7, '1409902937', '2014-10-30 15:02:45', 'ThisComputer.lastNewsRead', '', ''),
(300, 144, 7, '', '2014-10-30 15:02:45', 'ThisComputer.NoolightCommandSend', '', ''),
(301, 145, 7, '0,2', '2014-10-30 15:02:45', 'ThisComputer.AlreadyPlayedMusic', '', ''),
(324, 255, 7, '1594220108', '2020-07-08 17:55:08', 'ThisComputer.cycle_wscacheRun', '', ''),
(316, 247, 7, '1594220107', '2020-07-08 17:55:07', 'ThisComputer.cycle_execsRun', '', ''),
(304, 148, 7, '1423047600', '2015-02-04 15:00:00', 'ThisComputer.lastSayTime', '', ''),
(305, 149, 7, '31', '2020-07-08 17:55:45', 'ThisComputer.uptime', '', ''),
(306, 150, 7, '1594220114', '2020-07-08 17:55:15', 'ThisComputer.started_time', '', ''),
(311, 137, 68, '', '2015-12-03 14:47:55', 'admin.CoordinatesUpdatedTimestamp', '', ''),
(312, 138, 68, '', '2015-12-03 14:47:55', 'admin.fullName', '', ''),
(313, 139, 68, '', '2015-12-03 14:47:55', 'admin.isMoving', '', ''),
(314, 140, 68, 'Home', '2015-12-03 14:47:55', 'admin.seenAt', '', ''),
(315, 243, 7, 'uniq5f05d1f1970e9', '2020-07-08 17:02:25', 'ThisComputer.Serial', '', '/ajax/market.html?op=check_updates');

-- --------------------------------------------------------

--
-- Структура таблицы `safe_execs`
--

CREATE TABLE `safe_execs` (
  `ID` int(10) UNSIGNED NOT NULL,
  `COMMAND` text NOT NULL,
  `ADDED` datetime DEFAULT NULL,
  `EXCLUSIVE` int(3) NOT NULL DEFAULT 0,
  `PRIORITY` int(10) NOT NULL DEFAULT 0,
  `ON_COMPLETE` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `scenes`
--

CREATE TABLE `scenes` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `BACKGROUND` varchar(255) NOT NULL DEFAULT '',
  `PRIORITY` int(10) NOT NULL DEFAULT 0,
  `HIDDEN` int(3) NOT NULL DEFAULT 0,
  `WALLPAPER` varchar(255) NOT NULL DEFAULT '',
  `WALLPAPER_FIXED` int(3) NOT NULL DEFAULT 0,
  `WALLPAPER_NOREPEAT` int(3) NOT NULL DEFAULT 0,
  `AUTO_SCALE` int(3) NOT NULL DEFAULT 0,
  `SYSTEM` varchar(255) NOT NULL DEFAULT '',
  `DEVICES_BACKGROUND` varchar(10) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `scenes`
--

INSERT INTO `scenes` (`ID`, `TITLE`, `BACKGROUND`, `PRIORITY`, `HIDDEN`, `WALLPAPER`, `WALLPAPER_FIXED`, `WALLPAPER_NOREPEAT`, `AUTO_SCALE`, `SYSTEM`, `DEVICES_BACKGROUND`) VALUES
(1, 'Scene 1', '/cms/scenes/backgrounds/photolib.png', 100, 0, '', 0, 0, 0, '', '');

-- --------------------------------------------------------

--
-- Структура таблицы `scripts`
--

CREATE TABLE `scripts` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `CODE` text DEFAULT NULL,
  `DESCRIPTION` text DEFAULT NULL,
  `TYPE` int(3) UNSIGNED NOT NULL DEFAULT 0,
  `XML` text DEFAULT NULL,
  `CATEGORY_ID` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `EXECUTED` datetime DEFAULT NULL,
  `EXECUTED_PARAMS` varchar(255) DEFAULT NULL,
  `RUN_PERIODICALLY` int(3) UNSIGNED NOT NULL DEFAULT 0,
  `RUN_DAYS` char(30) NOT NULL DEFAULT '',
  `RUN_TIME` char(30) NOT NULL DEFAULT '',
  `UPDATED` datetime DEFAULT NULL,
  `EXECUTED_SRC` varchar(255) DEFAULT NULL,
  `AUTO_LINK` int(3) UNSIGNED NOT NULL DEFAULT 0,
  `AUTO_LINK_AVAILABLE` int(3) UNSIGNED NOT NULL DEFAULT 0,
  `LINKED_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `LINKED_PROPERTY` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `scripts`
--

INSERT INTO `scripts` (`ID`, `TITLE`, `CODE`, `DESCRIPTION`, `TYPE`, `XML`, `CATEGORY_ID`, `EXECUTED`, `EXECUTED_PARAMS`, `RUN_PERIODICALLY`, `RUN_DAYS`, `RUN_TIME`, `UPDATED`, `EXECUTED_SRC`, `AUTO_LINK`, `AUTO_LINK_AVAILABLE`, `LINKED_OBJECT`, `LINKED_PROPERTY`) VALUES
(8, 'timeNow', 'say(timeNow());', NULL, 0, NULL, 3, NULL, NULL, 0, '', '', NULL, NULL, 0, 0, '', ''),
(3, 'rssProcess', '/*\r\n$params[\'URL\'] --link\r\n$params[\'TITLE\'] -- title\r\n$params[\'BODY\'] -- body\r\n$params[\'CHANNEL_ID\'] -- channel ID\r\n$params[\'CHANNEL_TITLE\'] -- channed title\r\n\r\n*/\r\n\r\n//say($params[\'TITLE\']); // reading news', '', 0, '', 0, '2014-10-30 14:57:24', 'a:10:{s:10:\"CHANNEL_ID\";s:1:\"4\";s:5:\"ADDED\";s:19:\"2014-10-16 16:36:00\";s:5:\"TITLE\";s:184:\"Белтелерадиокомпания: Надеемся, решение украинской стороны о закрытии \"Беларусь 24\" не окончательное\";s:4:\"BODY\";s:386:\"<img src=\"http://img.tyt.by/thumbnails/n/07/2', 0, '', '', NULL, NULL, 0, 0, '', ''),
(10, 'test', 'setTimeOut(\'testTimer\',\'say(\"Hello world!\");\',30);', '', 0, '', 3, '2012-11-17 15:19:54', '', 0, '', '', NULL, NULL, 0, 0, '', ''),
(11, 'easyRF', '$device_id=$params[\'did\'];\r\n$destination_id=$params[\'dest\'];\r\n$packet_id=$params[\'pid\'];\r\n$command_id=$params[\'c\'];\r\n$data=$params[\'d\'];\r\n\r\nif ($device_id==0) {\r\n if ($command_id==10) {\r\n  //temp\r\n setGlobal(\'ws.tempInside\',round($data/100));\r\n } elseif ($command_id==12) {\r\n  //motion\r\n  callMethod(\'intSensor.statusChanged\',array(\'status\'=>1));\r\n  clearTimeOut($id.\'_move\');\r\n  setTimeOut($id.\'_move\',\"callMethod(\'intSensor.statusChanged\',array(\'status\'=>0));\",20);  \r\n }\r\n}', '', 0, '', 5, '2014-09-03 12:12:37', 'a:6:{s:6:\"script\";s:6:\"easyRF\";s:3:\"did\";s:1:\"0\";s:4:\"dest\";s:1:\"0\";s:3:\"pid\";s:4:\"8339\";s:1:\"c\";s:2:\"10\";s:1:\"d\";s:4:\"2600\";}', 0, '', '00:00', NULL, NULL, 0, 0, '', ''),
(12, 'RCSwitch', '$id=$params[\'rcswitch\'];\r\nif ($id==\'12345\') {\r\n //sensor 1\r\n}', '', 0, '', 5, '1970-01-01 00:00:00', 'a:2:{s:6:\"script\";s:8:\"RCSwitch\";s:8:\"rcswitch\";s:7:\"7689557\";}', 0, '', '00:00', NULL, NULL, 0, 0, '', ''),
(15, 'readWeatherToday', '$weather.=\"Сегодня ожидается \".str_replace(\'&deg;\',\' \',getGlobal(\'weatherToday\'));\r\n$weather.=\". Завтра \".str_replace(\'&deg;\',\' \',getGlobal(\'weatherTomorrow\'));\r\n$weather.=\". Сейчас на улице \".getGlobal(\'TempOutside\').\'.\';\r\n$weather=str_replace(\'&deg;\',\'\',$weather);\r\nsay($weather,2);', '', 0, '', 0, '1970-01-01 00:00:00', 'a:2:{i:0;s:12:\"погода\";i:1;s:2:\"а\";}', 0, '', '00:00', NULL, NULL, 0, 0, '', ''),
(18, 'playFavoriteMusic', '// вытягиваем историю из переменной\r\n$alreadyPlayed=gg(\"AlreadyPlayedMusic\");\r\nif (!$alreadyPlayed) {\r\n $alreadyPlayed=\'0\';\r\n}\r\n\r\n// выбираем случайную папку\r\n$rec=SQLSelectOne(\"SELECT * FROM media_favorites WHERE ID NOT IN (\".$alreadyPlayed.\") ORDER BY RAND()\");\r\n\r\nif (!$rec[\'ID\']) {\r\n // папок больше не осталось, поэтому выбираем случайную и сбрасываем истоирю\r\n $rec=SQLSelectOne(\"SELECT * FROM media_favorites ORDER BY RAND()\");\r\n $alreadyPlayed=\'0\';\r\n}\r\n\r\n\r\nif ($rec[\'ID\']) {\r\n\r\n // добавляем выбранную папку в историю\r\n $alreadyPlayed.=\',\'.$rec[\'ID\'];\r\n sg(\"AlreadyPlayedMusic\",$alreadyPlayed);\r\n\r\n // запускаем на проигрывание\r\n $collection=SQLSelectOne(\"SELECT * FROM collections WHERE ID=\".(int)$rec[\'COLLECTION_ID\']);\r\n $path=$collection[\'PATH\'].$rec[\'PATH\'];\r\n playMedia($path);\r\n //setTimeOut(\'VLCPlayer_update\',\"callMethod(\'VLCPlayer.update\');\",10);\r\n\r\n}', '', 0, '', 0, '1970-01-01 00:00:00', '', 0, '', '00:00', NULL, NULL, 0, 0, '', ''),
(22, 'Greeting', 'runScript(\"reportStatus\", array());\r\n', '', 0, '', 0, '1970-01-01 00:00:00', '', 0, '', '00:00', NULL, NULL, 0, 0, '', ''),
(19, 'playPause', 'getURL(\'http://localhost/rc/?command=vlc_pause\',0);', '', 0, '', 0, '1970-01-01 00:00:00', '', 0, '', '00:00', NULL, NULL, 0, 0, '', ''),
(20, 'Watching movie', 'say(LANG_GENERAL_SETTING_UP_LIGHTS,2);\r\n// to-do', '', 0, '', 6, '1970-01-01 00:00:00', '', 0, '', '00:00', NULL, NULL, 0, 0, '', ''),
(21, 'reportStatus', '$res=\'\';\r\n if (gg(\'Security.stateColor\')==\'green\' && gg(\'System.stateColor\')==\'green\' && gg(\'Communication.stateColor\')==\'green\') {\r\n  $res=\'Все системы работают в штатном режиме\';\r\n } else {\r\n  if (gg(\'Security.stateColor\')!=\'green\') {\r\n   $res.=\" Проблема безопасности: \".getGlobal(\'Security.stateDetails\');\r\n  }\r\n  if (gg(\'System.stateColor\')!=\'green\') {\r\n   $res.=\" Системная проблема: \".getGlobal(\'System.stateDetails\');\r\n  }  \r\n  if (gg(\'Communication.stateColor\')!=\'green\') {\r\n   $res.=\" Проблема связи: \".getGlobal(\'Communication.stateDetails\');\r\n  }  \r\n }\r\n say($res,5);', '', 0, '', 0, '1970-01-01 00:00:00', 'a:1:{i:0;s:27:\"статус системы\";}', 0, '', '00:00', NULL, NULL, 0, 0, '', '');

-- --------------------------------------------------------

--
-- Структура таблицы `script_categories`
--

CREATE TABLE `script_categories` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TITLE` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `script_categories`
--

INSERT INTO `script_categories` (`ID`, `TITLE`) VALUES
(3, 'Test'),
(4, '<#LANG_GENERAL_OPERATIONAL_MODES#>'),
(5, '<#LANG_GENERAL_SENSORS#>'),
(6, 'Scenarios');

-- --------------------------------------------------------

--
-- Структура таблицы `security_rules`
--

CREATE TABLE `security_rules` (
  `ID` int(10) UNSIGNED NOT NULL,
  `OBJECT_TYPE` char(20) NOT NULL DEFAULT '',
  `OBJECT_ID` int(10) NOT NULL DEFAULT 0,
  `TERMINALS` varchar(255) NOT NULL DEFAULT '',
  `TERMINALS_EXCEPT` int(3) NOT NULL DEFAULT 0,
  `USERS` varchar(255) NOT NULL DEFAULT '',
  `USERS_EXCEPT` int(3) NOT NULL DEFAULT 0,
  `TIMES` varchar(255) NOT NULL DEFAULT '',
  `TIMES_EXCEPT` int(3) NOT NULL DEFAULT 0,
  `CONDITION_ACTIVE` int(3) NOT NULL DEFAULT 0,
  `CONDITION_LINKED_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `CONDITION_LINKED_PROPERTY` varchar(255) NOT NULL DEFAULT '',
  `CONDITION` int(3) NOT NULL DEFAULT 0,
  `CONDITION_VALUE` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `settings`
--

CREATE TABLE `settings` (
  `ID` int(10) UNSIGNED NOT NULL,
  `PRIORITY` int(3) UNSIGNED NOT NULL DEFAULT 0,
  `HR` int(3) UNSIGNED NOT NULL DEFAULT 0,
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `NAME` varchar(50) NOT NULL DEFAULT '',
  `TYPE` varchar(59) NOT NULL DEFAULT '',
  `NOTES` text NOT NULL,
  `VALUE` text DEFAULT NULL,
  `DEFAULTVALUE` varchar(255) NOT NULL DEFAULT '',
  `URL` varchar(255) NOT NULL DEFAULT '',
  `URL_TITLE` varchar(255) NOT NULL DEFAULT '',
  `DATA` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `settings`
--

INSERT INTO `settings` (`ID`, `PRIORITY`, `HR`, `TITLE`, `NAME`, `TYPE`, `NOTES`, `VALUE`, `DEFAULTVALUE`, `URL`, `URL_TITLE`, `DATA`) VALUES
(7, 0, 0, 'Days to show in \"soon\" section', 'APP_CALENDAR_SOONLIMIT', 'text', '', '6', '6', '', '', ''),
(8, 0, 0, 'Show recently done items', 'APP_CALENDAR_SHOWDONE', 'yesno', '', '1', '1', '', '', ''),
(9, 0, 0, 'Scene width', 'SCENES_WIDTH', 'text', '', '803', '803', '', '', ''),
(24, 0, 0, 'Scene height', 'SCENES_HEIGHT', 'text', '', '606', '606', '', '', ''),
(88, 0, 0, 'Homepage Layout', 'GENERAL_START_LAYOUT', 'select', '', NULL, '', '', '', '=Default|homepages=Home Pages|menu=Menu|apps=Applications|cp=Control Panel'),
(87, 0, 0, 'HOOK_EVENT_SAY_CACHED_READY', 'HOOK_EVENT_SAY_CACHED_READY', 'json', '', '{\"terminals\":{\"filter\":0}}', '', '', '', ''),
(86, 0, 0, 'HOOK_EVENT_ASK', 'HOOK_EVENT_ASK', 'json', '', '{\"terminals\":{\"filter\":\"\"}}', '', '', '', ''),
(61, 0, 0, 'Voice notifications language', 'VOICE_LANGUAGE', 'text', '', 'ru', 'en', '', '', ''),
(62, 0, 0, 'Color theme', 'THEME', 'text', '', 'light', 'dark', '', '', ''),
(44, 0, 0, 'Play sound signal before speaking', 'SPEAK_SIGNAL', 'onoff', '', '1', '1', '', '', ''),
(85, 0, 0, 'HOOK_EVENT_SAYTO', 'HOOK_EVENT_SAYTO', 'json', '', '{\"terminals\":{\"filter\":\"\"}}', '', '', '', ''),
(84, 0, 0, 'HOOK_EVENT_SAYREPLY', 'HOOK_EVENT_SAYREPLY', 'json', '', '{\"terminals\":{\"filter\":\"\"}}', '', '', '', ''),
(83, 0, 0, 'HOOK_EVENT_SAY', 'HOOK_EVENT_SAY', 'json', '', '{\"connect\":{\"filter\":\"\"},\"patterns\":{\"filter\":\"\"},\"terminals\":{\"filter\":\"\"}}', '', '', '', ''),
(60, 29, 0, 'After SAY (code)', 'HOOK_AFTER_SAY', 'text', '', '', '', '', '', ''),
(82, 0, 0, 'HOOK_EVENT_HOURLY', 'HOOK_EVENT_HOURLY', 'json', '', '{\"connect\":{\"filter\":\"\"},\"terminals\":{\"filter\":\"\"}}', '', '', '', ''),
(81, 0, 0, 'HOOK_EVENT_MINUTELY', 'HOOK_EVENT_MINUTELY', 'json', '', '{\"devices\":{\"filter\":\"\"}}', '', '', '', ''),
(69, 0, 0, 'Path to store backup', 'BACKUP_PATH', 'text', '', '', '', '', '', ''),
(72, 0, 0, 'Computer\'s name', 'GENERAL_ALICE_NAME', 'text', '', '', '', '', '', ''),
(80, 0, 0, 'HOOK_EVENT_COMMAND', 'HOOK_EVENT_COMMAND', 'json', '', '{\"devices\":{\"filter\":\"\"},\"patterns\":{\"filter\":\"\"},\"scripts\":{\"filter\":\"\"}}', '', '', '', ''),
(79, 0, 0, 'HOOK_EVENT_DAILY', 'HOOK_EVENT_DAILY', 'json', '', '{\"objects\":{\"filter\":\"\"}}', '', '', '', '');

-- --------------------------------------------------------

--
-- Структура таблицы `shoutrooms`
--

CREATE TABLE `shoutrooms` (
  `ID` int(10) UNSIGNED NOT NULL,
  `TITLE` varchar(250) NOT NULL DEFAULT '',
  `PRIORITY` int(10) NOT NULL DEFAULT 0,
  `ADDED_BY` int(10) NOT NULL DEFAULT 0,
  `ADDED` datetime DEFAULT NULL,
  `IS_PUBLIC` int(3) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `shouts`
--

CREATE TABLE `shouts` (
  `ID` int(10) UNSIGNED NOT NULL,
  `ROOM_ID` int(10) NOT NULL DEFAULT 0,
  `MEMBER_ID` int(10) NOT NULL DEFAULT 0,
  `MESSAGE` varchar(255) NOT NULL DEFAULT '',
  `ADDED` datetime DEFAULT NULL,
  `IMPORTANCE` int(10) NOT NULL DEFAULT 0,
  `SOURCE` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `system_errors`
--

CREATE TABLE `system_errors` (
  `ID` int(10) UNSIGNED NOT NULL,
  `CODE` varchar(50) NOT NULL DEFAULT '',
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `DETAILS` text DEFAULT NULL,
  `ACTIVE` int(3) NOT NULL DEFAULT 0,
  `LATEST_UPDATE` datetime DEFAULT NULL,
  `KEEP_HISTORY` int(3) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `system_errors_data`
--

CREATE TABLE `system_errors_data` (
  `ID` int(10) UNSIGNED NOT NULL,
  `ERROR_ID` int(10) NOT NULL DEFAULT 0,
  `COMMENTS` text DEFAULT NULL,
  `ADDED` datetime DEFAULT NULL,
  `PROPERTIES_DATA` text DEFAULT NULL,
  `METHODS_DATA` text DEFAULT NULL,
  `SCRIPTS_DATA` text DEFAULT NULL,
  `TIMERS_DATA` text DEFAULT NULL,
  `EVENTS_DATA` text DEFAULT NULL,
  `DEBUG_DATA` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `terminals`
--

CREATE TABLE `terminals` (
  `ID` int(10) UNSIGNED NOT NULL,
  `NAME` varchar(255) NOT NULL DEFAULT '',
  `TITLE` varchar(255) NOT NULL DEFAULT '',
  `HOST` varchar(255) NOT NULL DEFAULT '',
  `CANPLAY` int(3) NOT NULL DEFAULT 0,
  `PLAYER_TYPE` char(10) NOT NULL DEFAULT '',
  `PLAYER_PORT` varchar(255) NOT NULL DEFAULT '',
  `PLAYER_USERNAME` varchar(255) NOT NULL DEFAULT '',
  `PLAYER_PASSWORD` varchar(255) NOT NULL DEFAULT '',
  `LATEST_ACTIVITY` datetime DEFAULT NULL,
  `IS_ONLINE` int(1) NOT NULL DEFAULT 0,
  `CANTTS` int(3) NOT NULL DEFAULT 0,
  `MIN_MSG_LEVEL` varchar(255) NOT NULL DEFAULT '',
  `TTS_TYPE` char(20) NOT NULL DEFAULT '',
  `PLAYER_CONTROL_ADDRESS` varchar(255) NOT NULL DEFAULT '',
  `MAJORDROID_API` int(3) NOT NULL DEFAULT 0,
  `LATEST_REQUEST` varchar(255) NOT NULL DEFAULT '',
  `LATEST_REQUEST_TIME` datetime DEFAULT NULL,
  `LINKED_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `LEVEL_LINKED_PROPERTY` varchar(255) NOT NULL DEFAULT '',
  `LOCATION_ID` int(5) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `ID` int(10) UNSIGNED NOT NULL,
  `USERNAME` varchar(255) NOT NULL DEFAULT '',
  `NAME` varchar(255) NOT NULL DEFAULT '',
  `EMAIL` varchar(255) NOT NULL DEFAULT '',
  `SKYPE` varchar(255) NOT NULL DEFAULT '',
  `MOBILE` varchar(255) NOT NULL DEFAULT '',
  `AVATAR` varchar(255) NOT NULL DEFAULT '',
  `PASSWORD` varchar(255) NOT NULL DEFAULT '',
  `IS_ADMIN` tinyint(3) NOT NULL DEFAULT 0,
  `IS_DEFAULT` tinyint(3) NOT NULL DEFAULT 0,
  `LINKED_OBJECT` varchar(255) NOT NULL DEFAULT '',
  `HOST` varchar(255) NOT NULL DEFAULT '',
  `ACTIVE_CONTEXT_ID` int(10) NOT NULL DEFAULT 0,
  `ACTIVE_CONTEXT_EXTERNAL` int(3) NOT NULL DEFAULT 0,
  `ACTIVE_CONTEXT_UPDATED` datetime DEFAULT NULL,
  `ACTIVE_CONTEXT_HISTORY` text DEFAULT NULL,
  `COLOR` char(20) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`ID`, `USERNAME`, `NAME`, `EMAIL`, `SKYPE`, `MOBILE`, `AVATAR`, `PASSWORD`, `IS_ADMIN`, `IS_DEFAULT`, `LINKED_OBJECT`, `HOST`, `ACTIVE_CONTEXT_ID`, `ACTIVE_CONTEXT_EXTERNAL`, `ACTIVE_CONTEXT_UPDATED`, `ACTIVE_CONTEXT_HISTORY`, `COLOR`) VALUES
(1, 'admin', 'Admin', 'admin@smartliving.com', '', '', '', 'cf83e1357eefb8bdf1542850d66d8007d620e4050b5715dc83f4a921d36ce9ce47d0d13c5d85f2b0ff8318d2877eec2f63b931bd47417a81a538327af927da3e', 1, 1, 'admin', '', 0, 0, '2014-09-05 12:07:19', NULL, '');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `actions_log`
--
ALTER TABLE `actions_log`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ACTION_TYPE` (`ACTION_TYPE`);

--
-- Индексы таблицы `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `blockly_code`
--
ALTER TABLE `blockly_code`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `cached_values`
--
ALTER TABLE `cached_values`
  ADD PRIMARY KEY (`KEYWORD`);

--
-- Индексы таблицы `cached_ws`
--
ALTER TABLE `cached_ws`
  ADD PRIMARY KEY (`PROPERTY`);

--
-- Индексы таблицы `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `PARENT_ID` (`PARENT_ID`);

--
-- Индексы таблицы `collections`
--
ALTER TABLE `collections`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `commands`
--
ALTER TABLE `commands`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `country`
--
ALTER TABLE `country`
  ADD PRIMARY KEY (`COUNTRY_ID`),
  ADD KEY `AK_COUNTRY__GUID` (`COUNTRY_GUID`);

--
-- Индексы таблицы `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `devices_groups`
--
ALTER TABLE `devices_groups`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `devices_linked`
--
ALTER TABLE `devices_linked`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `devices_scheduler_points`
--
ALTER TABLE `devices_scheduler_points`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `elements`
--
ALTER TABLE `elements`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `elm_states`
--
ALTER TABLE `elm_states`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `events_params`
--
ALTER TABLE `events_params`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `ignore_updates`
--
ALTER TABLE `ignore_updates`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `layouts`
--
ALTER TABLE `layouts`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `media_favorites`
--
ALTER TABLE `media_favorites`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `media_history`
--
ALTER TABLE `media_history`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `methods`
--
ALTER TABLE `methods`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `OBJECT_ID` (`OBJECT_ID`),
  ADD KEY `CLASS_ID` (`CLASS_ID`);

--
-- Индексы таблицы `myblocks`
--
ALTER TABLE `myblocks`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `myblocks_categories`
--
ALTER TABLE `myblocks_categories`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `objects`
--
ALTER TABLE `objects`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `patterns`
--
ALTER TABLE `patterns`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `performance_log`
--
ALTER TABLE `performance_log`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `phistory`
--
ALTER TABLE `phistory`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `VALUE_ID` (`VALUE_ID`);

--
-- Индексы таблицы `phistory_queue`
--
ALTER TABLE `phistory_queue`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `pinghosts`
--
ALTER TABLE `pinghosts`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `plan_components`
--
ALTER TABLE `plan_components`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `plan_components_data`
--
ALTER TABLE `plan_components_data`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `plan_states`
--
ALTER TABLE `plan_states`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `plugins`
--
ALTER TABLE `plugins`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `project_modules`
--
ALTER TABLE `project_modules`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `CLASS_ID` (`CLASS_ID`),
  ADD KEY `OBJECT_ID` (`OBJECT_ID`);

--
-- Индексы таблицы `public_calls`
--
ALTER TABLE `public_calls`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `pvalues`
--
ALTER TABLE `pvalues`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `PROPERTY_ID` (`PROPERTY_ID`),
  ADD KEY `OBJECT_ID` (`OBJECT_ID`),
  ADD KEY `PROPERTY_NAME` (`PROPERTY_NAME`);

--
-- Индексы таблицы `safe_execs`
--
ALTER TABLE `safe_execs`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `scenes`
--
ALTER TABLE `scenes`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `scripts`
--
ALTER TABLE `scripts`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `script_categories`
--
ALTER TABLE `script_categories`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `security_rules`
--
ALTER TABLE `security_rules`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `shoutrooms`
--
ALTER TABLE `shoutrooms`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `shouts`
--
ALTER TABLE `shouts`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `system_errors`
--
ALTER TABLE `system_errors`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `system_errors_data`
--
ALTER TABLE `system_errors_data`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `terminals`
--
ALTER TABLE `terminals`
  ADD PRIMARY KEY (`ID`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `actions_log`
--
ALTER TABLE `actions_log`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `ID` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `blockly_code`
--
ALTER TABLE `blockly_code`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT для таблицы `classes`
--
ALTER TABLE `classes`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT для таблицы `collections`
--
ALTER TABLE `collections`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `commands`
--
ALTER TABLE `commands`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT для таблицы `devices`
--
ALTER TABLE `devices`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `devices_groups`
--
ALTER TABLE `devices_groups`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `devices_linked`
--
ALTER TABLE `devices_linked`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `devices_scheduler_points`
--
ALTER TABLE `devices_scheduler_points`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `elements`
--
ALTER TABLE `elements`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `elm_states`
--
ALTER TABLE `elm_states`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `events`
--
ALTER TABLE `events`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `events_params`
--
ALTER TABLE `events_params`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `history`
--
ALTER TABLE `history`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `ignore_updates`
--
ALTER TABLE `ignore_updates`
  MODIFY `ID` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `jobs`
--
ALTER TABLE `jobs`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `layouts`
--
ALTER TABLE `layouts`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `locations`
--
ALTER TABLE `locations`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `media_favorites`
--
ALTER TABLE `media_favorites`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `media_history`
--
ALTER TABLE `media_history`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `methods`
--
ALTER TABLE `methods`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=176;

--
-- AUTO_INCREMENT для таблицы `myblocks`
--
ALTER TABLE `myblocks`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `myblocks_categories`
--
ALTER TABLE `myblocks_categories`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `objects`
--
ALTER TABLE `objects`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT для таблицы `patterns`
--
ALTER TABLE `patterns`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `performance_log`
--
ALTER TABLE `performance_log`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `phistory`
--
ALTER TABLE `phistory`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `phistory_queue`
--
ALTER TABLE `phistory_queue`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `pinghosts`
--
ALTER TABLE `pinghosts`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `plans`
--
ALTER TABLE `plans`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `plan_components`
--
ALTER TABLE `plan_components`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `plan_components_data`
--
ALTER TABLE `plan_components_data`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `plan_states`
--
ALTER TABLE `plan_states`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `plugins`
--
ALTER TABLE `plugins`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `project_modules`
--
ALTER TABLE `project_modules`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT для таблицы `properties`
--
ALTER TABLE `properties`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=258;

--
-- AUTO_INCREMENT для таблицы `public_calls`
--
ALTER TABLE `public_calls`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `pvalues`
--
ALTER TABLE `pvalues`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=327;

--
-- AUTO_INCREMENT для таблицы `safe_execs`
--
ALTER TABLE `safe_execs`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `scenes`
--
ALTER TABLE `scenes`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `scripts`
--
ALTER TABLE `scripts`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT для таблицы `script_categories`
--
ALTER TABLE `script_categories`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `security_rules`
--
ALTER TABLE `security_rules`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `settings`
--
ALTER TABLE `settings`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT для таблицы `shoutrooms`
--
ALTER TABLE `shoutrooms`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `shouts`
--
ALTER TABLE `shouts`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `system_errors`
--
ALTER TABLE `system_errors`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `system_errors_data`
--
ALTER TABLE `system_errors_data`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT для таблицы `terminals`
--
ALTER TABLE `terminals`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
