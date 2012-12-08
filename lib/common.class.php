<?
/*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.7
*/


/**
* Title
*
* Description
*
* @access public
*/
 function say($ph, $level=0) {
 global $commandLine;
 global $voicemode;

 /*
  if ($commandLine) {
   echo utf2win($ph);
  } else {
   echo $ph;
  }
  */

  $rec=array();
  $rec['MESSAGE']=$ph;
  $rec['ADDED']=date('Y-m-d H:i:s');
  $rec['ROOM_ID']=0;
  $rec['MEMBER_ID']=0;
  if ($level>0) {
   $rec['IMPORTANCE']=$level;
  }
  $rec['ID']=SQLInsert('shouts', $rec);

  if ($level>=(int)getGlobal('minMsgLevel')) { //$voicemode!='off' && 

   $lang='en';
   if (defined('SETTINGS_SITE_LANGUAGE')) {
    $lang=SETTINGS_SITE_LANGUAGE;
   }
   if (defined('SETTINGS_VOICE_LANGUAGE')) {
    $lang=SETTINGS_VOICE_LANGUAGE;
   }

   if (!defined('SETTINGS_TTS_GOOGLE') || SETTINGS_TTS_GOOGLE) {
    $google_file=GoogleTTS($ph, $lang);
   } else {
    $google_file=false;
   }

   if (!defined('SETTINGS_SPEAK_SIGNAL') || SETTINGS_SPEAK_SIGNAL=='1') {
    $passed=SQLSelectOne("SELECT (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(ADDED)) as PASSED FROM shouts WHERE ID!='".$rec['ID']."' ORDER BY ID DESC LIMIT 1");
    if ($passed['PASSED']>20) { // play intro-sound only if more than 30 seconds passed from the last one
     playSound('dingdong', 1, $level);
    }
   }

   if ($google_file) {
    @touch($google_file);
    playSound($google_file, 1, $level);
   } else {
    safe_exec('cscript '.DOC_ROOT.'/rc/sapi.js '.$ph, 1, $level);
   }
  }

  global $noPatternMode;
  if (!$noPatternMode) {
   include_once(DIR_MODULES.'patterns/patterns.class.php');
   $pt=new patterns();
   $pt->checkAllPatterns();
  }

  postToTwitter($ph);

 }

/**
* Title
*
* Description
*
* @access public
*/
 function processCommand($command) {
  global $pattern_matched;
  if (!$pattern_matched) {
   getObject("ThisComputer")->callMethod("commandReceived", array("command"=>$command));
  }
 }

/**
* Title
*
* Description
*
* @access public
*/
 function timeConvert($tm) {
  $tm=trim($tm);
  if (preg_match('/^(\d+):(\d+)$/', $tm, $m)) {
   $hour=$m[1];
   $minute=$m[2];
   $trueTime=mktime($hour, $minute, 0, date('m'), date('d'), date('Y'));
  } elseif (preg_match('/^(\d+)$/', $tm, $m)) {
   $trueTime=$tm;
  }
  return $trueTime;
 }


/**
* Title
*
* Description
*
* @access public
*/
 function timeNow($tm=0) {
  if (!$tm) {
   $tm=time();
  }
  $h=(int)date('G',$tm);
  if ($h==0) {
   $hw='часов';
  } elseif ($h==1) {
   $hw='час';
  } elseif ($h<5) {
   $hw='часа';
  } elseif ($h<21) {
   $hw='часов';
  } elseif ($h==21) {
   $hw='час';
  } elseif ($h>=21) {
   $hw='часа';
  }
  
  $m=(int)date('i',$tm);
  if ($m==0) {
   $ms='ровно';
  } else {
   $ms=$m." минут";
  }
  $res="$h ".win2utf($hw)." ".win2utf($ms);
  return $res;
 }

/**
* Title
*
* Description
*
* @access public
*/
 function isWeekEnd() {
  if (date('w')==0 || date('w')==6) {
   return true; // sunday, saturday
  } else {
   return false;
  }
  
 }
/**
* Title
*
* Description
*
* @access public
*/
 function isWeekDay() {
  if (IsWeekEnd()) {
   return false;
  } else {
   return true;
  }
 }

/**
* Title
*
* Description
*
* @access public
*/
 function timeIs($tm) {
  if (date('H:i')==$tm) {
   return true;
  } else {
   return false;
  }
 }

/**
* Title
*
* Description
*
* @access public
*/
 function timeBefore($tm) {
  $trueTime=timeConvert($tm);
  if (time()<=$trueTime) {
   return true;
  } else {
   return false;
  }
 }
/**
* Title
*
* Description
*
* @access public
*/
 function timeAfter($tm) {
  $trueTime=timeConvert($tm);
  if (time()>=$trueTime) {
   return true;
  } else {
   return false;
  }
 }

/**
* Title
*
* Description
*
* @access public
*/
 function timeBetween($tm1, $tm2) {
  $trueTime1=timeConvert($tm1);
  $trueTime2=timeConvert($tm2);
  if ($trueTime1>$trueTime2) {
   //$trueTime1-=24*60*60;
   if ($trueTime2<time()) {
    $trueTime2+=24*60*60;
   } else {
    $trueTime1-=24*60*60;
   }
  }

  /*
  echo date('Y-m-d H:i:s', $trueTime1);
  echo " - ";
  echo date('Y-m-d H:i:s', $trueTime2);
  echo "<br>";
  */

  if ((time()>=$trueTime1) && (time()<=$trueTime2)) {
   return true;
  } else {
   return false;
  }
 }

/**
* Title
*
* Description
*
* @access public
*/
 function addScheduledJob($title, $commands, $datetime, $expire=60) {
  $rec=array();
  $rec['TITLE']=$title;
  $rec['COMMANDS']=$commands;
  $rec['RUNTIME']=date('Y-m-d H:i:s', $datetime);
  $rec['EXPIRE']=date('Y-m-d H:i:s', $datetime+$expire);
  $rec['ID']=SQLInsert('jobs', $rec);
  return $rec['ID'];
 }

/**
* Title
*
* Description
*
* @access public
*/
 function clearScheduledJob($title) {
  SQLExec("DELETE FROM jobs WHERE TITLE LIKE '".DBSafe($title)."'"); // AND RUNTIME>='".date('Y-m-d H:i:s')."'
 }

 function deleteScheduledJob($id) {
  SQLExec("DELETE FROM jobs WHERE ID=".(int)$id);
 }


/**
* Title
*
* Description
*
* @access public
*/
 function setTimeOut($title, $commands, $timeout) {
  return addScheduledJob($title,$commands, time()+$timeout);
 }

/**
* Title
*
* Description
*
* @access public
*/
 function clearTimeOut($title) {
  return clearScheduledJob($title);
 }

/**
* Title
*
* Description
*
* @access public
*/
 function runScheduledJobs() {
  SQLExec("UPDATE jobs SET EXPIRED=1 WHERE PROCESSED=0 AND EXPIRE<='".date('Y-m-d H:i:s')."'");
  $jobs=SQLSelect("SELECT * FROM jobs WHERE PROCESSED=0 AND EXPIRED=0 AND RUNTIME<='".date('Y-m-d H:i:s')."'");
  $total=count($jobs);
  for($i=0;$i<$total;$i++) {
   echo "Running job: ".$jobs[$i]['TITLE']."\n";
   $jobs[$i]['PROCESSED']=1;
   $jobs[$i]['STARTED']=date('Y-m-d H:i:s');
   SQLUpdate('jobs', $jobs[$i]);
   eval($jobs[$i]['COMMANDS']);
  }
 }

/**
* Title
*
* Description
*
* @access public
*/
 function textToNumbers($text) {
  $newtext=utf2win($text);
  $newtext=win2utf($newtext);
  return $newtext;
 }

/**
* Title
*
* Description
*
* @access public
*/
 function recognizeTime($text, &$newText) {
  $result=0;
  $found=0;

  $new_time=time();

  $text=utf2win($text);
  if (preg_match('/через (\d+) секунд.?/is', $text, $m)) {
   $new_time=time()+$m[1];
   $newText=trim(str_replace($m[0], '', $text));
   $found=1;
  } elseif (preg_match('/через (\d+) минут.?/is', $text, $m)) {
   $new_time=time()+$m[1]*60;
   $newText=trim(str_replace($m[0], '', $text));
   $found=1;
  } elseif (preg_match('/через (\d+) час.?/is', $text, $m)) {
   $new_time=time()+$m[1]*60*60;
   $newText=trim(str_replace($m[0], '', $text));
   $found=1;
  } elseif (preg_match('/в (\d+):(\d+)/is', $text, $m)) {
   $new_time=mktime($m[1], $m[2], 0, date('m'), date('d'), date('Y'));
   $newText=trim(str_replace($m[0], '', $text));
   $found=1;
  }

  $newText=win2utf($newText);
  if ($found) {
   $result=$new_time;
  }

  return $result;

 }


/**
* Title
*
* Description
*
* @access public
*/
 function registerEvent($eventName, $details='', $expire_in=365) {
  
  $rec=array();
  $rec['EVENT_NAME']=$eventName;
  $rec['EVENT_TYPE']='system';
  $rec['DETAILS']=$details;
  $rec['ADDED']=date('Y-m-d H:i:s');
  $rec['EXPIRE']=date('Y-m-d H:i:s', time()+$expire_in*24*60*60);
  $rec['PROCESSED']=1;
  $rec['ID']=SQLInsert('events', $rec);
  return $rec['ID'];
 }

/**
* Title
*
* Description
*
* @access public
*/
 function registeredEventTime($eventName) {
  $rec=SQLSelectOne("SELECT ID, UNIX_TIMESTAMP(ADDED) as TM FROM events WHERE EVENT_TYPE='system' AND EVENT_NAME='".DBSafe($eventName)."' ORDER BY ADDED DESC LIMIT 1");
  if ($rec['ID']) {
   return $rec['TM'];
  } else {
   return -1;
  }
 }

/**
* Title
*
* Description
*
* @access public
*/
 function getRandomLine($filename) {
  if (file_exists(ROOT.'texts/'.$filename.'.txt')) {
   $filename=ROOT.'texts/'.$filename.'.txt';
  }

  if (file_exists($filename)) {
   $data=LoadFile($filename);
   $data=str_replace("\r", '', $data);
   $lines=explode("\n", $data);
   $total=count($lines);
   $line=$lines[round(rand(0, $total))];
   if ($line!='') {
    return $line;
   }
  }

 }

/**
* Title
*
* Description
*
* @access public
*/
 function playSound($filename, $exclusive=0, $priority=0) {
  if (file_exists(ROOT.'sounds/'.$filename.'.mp3')) {
   $filename=ROOT.'sounds/'.$filename.'.mp3';
  }
  if (file_exists($filename)) {
   if (substr(php_uname(), 0, 7) == "Windows") {
    safe_exec(DOC_ROOT.'/rc/madplay.exe '.$filename, $exclusive, $priority);
   } else {
    safe_exec('mplayer '.$filename, $exclusive, $priority);
   }
  }
 }

/**
* Title
*
* Description
*
* @access public
*/
 function playMedia($path, $host='localhost') {
  $terminal=SQLSelectOne("SELECT * FROM terminals WHERE HOST LIKE '".DBSafe($host)."' OR NAME LIKE '".DBSafe($host)."' OR TITLE LIKE '".DBSafe($host)."'");
  if (!$terminal['ID']) {
   $terminal=SQLSelectOne("SELECT * FROM terminals WHERE CANPLAY=1 ORDER BY ID");
  }
  if (!$terminal['ID']) {
   $terminal=SQLSelectOne("SELECT * FROM terminals WHERE 1 ORDER BY ID");
  }

  if (!$terminal['ID']) {
   return 0;
  }

  include_once(DIR_MODULES.'app_player/app_player.class.php');
  $player=new app_player();
  $player->terminal_id=$terminal['ID'];
  $player->play=$path;

  global $ajax;
  $ajax=1;

  global $command;
  $command='refresh';

  $player->intCall=1;
  $player->usual($out);

 }

/**
* Title
*
* Description
*
* @access public
*/
 function runScript($id, $params='') {
  include_once(DIR_MODULES.'scripts/scripts.class.php');
  $sc=new scripts();
  $sc->runScript($id, $params);
 }

/**
* Title
*
* Description
*
* @access public
*/
 function getURL($url, $cache=600, $username='', $password='') {
  $cache_file=ROOT.'cached/urls/'.preg_replace('/\W/is', '_', str_replace('http://', '', $url)).'.html';
  if (!$cache || !is_file($cache_file) || ((time()-filemtime($cache_file))>$cache)) {
   //download
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
   if ($username!='') {
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
    curl_setopt($ch, CURLOPT_USERPWD, $username.":".$password); 
   }
   $result = curl_exec($ch);
   if ($cache>0) {
    if (!is_dir(ROOT.'cached/urls')) {
     @mkdir(ROOT.'cached/urls', 0777);
    }
    SaveFile($cache_file, $result);
   }
  } else {
   $result=LoadFile($cache_file);
  }
  return $result;
 }

/**
* Title
*
* Description
*
* @access public
*/
 function safe_exec($command, $exclusive=0, $priority=0) {
  $rec=array();
  $rec['ADDED']=date('Y-m-d H:i:s');
  $rec['COMMAND']=$command;
  $rec['EXCLUSIVE']=$exclusive;
  $rec['PRIORITY']=$priority;
  $rec['ID']=SQLInsert('safe_execs', $rec);
  return $rec['ID'];
 }

/**
* Title
*
* Description
*
* @access public
*/
 function execInBackground($cmd) {
    if (substr(php_uname(), 0, 7) == "Windows"){
        //pclose(popen("start /B ". $cmd, "r")); 
        $WshShell = new COM("WScript.Shell");
        $oExec = $WshShell->Run("cmd /C ".$cmd, 0, false);
    }
    else {
        exec($cmd . " > /dev/null &");  
    }
} 

 function getFilesTree($destination) {

  if (substr($destination, -1)=='/' || substr($destination, -1)=='\\') {
   $destination=substr($destination, 0, strlen($destination)-1);
  }

  $res=array();

  if (!Is_Dir($destination)) {
    return $res;
  }

 if ($dir = @opendir($destination)) { 
  while (($file = readdir($dir)) !== false) { 
    if (Is_Dir($destination."/".$file) && ($file!='.') && ($file!='..')) {
     $tmp=getFilesTree($destination."/".$file);
     if (is_array($tmp)) {
      foreach($tmp as $elem) {
       $res[]=$elem;
      }
     }
    } elseif (Is_File($destination."/".$file)) {
     $res[]=($destination."/".$file);
    }
  }     
  closedir($dir); 
 }
 return $res;
 }


/**
* Title
*
* Description
*
* @access public
*/
 function isOnline($host) {
  $rec=SQLSelectOne("SELECT * FROM pinghosts WHERE HOSTNAME LIKE '".DBSafe($host)."' OR TITLE LIKE '".DBSafe($host)."'");
  if (!$rec['STATUS'] || $rec['STATUS']==2) {
   return 0;
  } else {
   return 1;
  }
 }

?>