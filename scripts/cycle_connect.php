<?php
/**
* Test script
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 1.3
*/
chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); 

// get settings
$settings = SQLSelect('SELECT NAME, VALUE FROM settings');
$total    = count($settings);

for ($i = 0; $i < $total; $i ++)
   Define('SETTINGS_' . $settings[$i]['NAME'], $settings[$i]['VALUE']);

// language selection by settings
if (SETTINGS_SITE_LANGUAGE && file_exists(ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php')) 
   include_once (ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php');

include_once (ROOT . 'languages/default.php');

if (defined('SETTINGS_SITE_TIMEZONE')) 
{
   ini_set('date.timezone', SETTINGS_SITE_TIMEZONE);
}

$session=new session("prj");

set_time_limit(0);

$address = '83.169.6.78';
$port = 11444;

while (1) {

include_once(DIR_MODULES.'connect/connect.class.php');
$connect=new connect();
$connect->getConfig();

if (!$connect->config['CONNECT_SYNC']) {
 echo "Connect sync turned off.";
 exit;
} else {
 //global $send_menu;
 //$send_menu=1;
 //$out=array();
 //$connect->sendData($out, 1);
 $connect->sendMenu();
 $commands=SQLSelect("SELECT * FROM commands");
 $total=count($commands);
 for($i=0;$i<$total;$i++) {
  $cmd_values[$commands[$i]['ID']]=$commands[$i]['CUR_VALUE'];
  $cmd_titles[$commands[$i]['ID']]=$commands[$i]['RENDER_TITLE'];
  $cmd_data[$commands[$i]['ID']]=$commands[$i]['RENDER_DATA'];
 }
}


/* Create a TCP/IP socket. */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    echo date('Y-m-d H:i:s ')."socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
    continue;
} else {
    echo "OK.\n";
}

echo date('Y-m-d H:i:s ')."Attempting to connect to '$address' on port '$port'...";
$result = socket_connect($socket, $address, $port);
if ($result === false) {
    echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
    continue;
} else {
    echo "OK.\n";
}

$in='Hello, world!'."\n";
echo date('Y-m-d H:i:s ')."Sending: ".$in;
socket_write($socket, $in, strlen($in));
echo "OK.\n";

$out = socket_read($socket, 2048, PHP_NORMAL_READ);
echo date('Y-m-d H:i:s ')."Response: ".trim($out)."\n";

$in='auth:'.$connect->config['CONNECT_USERNAME'].'|'.md5(md5($connect->config['CONNECT_PASSWORD']))."\n";

echo date('Y-m-d H:i:s ')."Sending: ".$in;
socket_write($socket, $in, strlen($in));
echo "OK.\n";

$out = socket_read($socket, 2048, PHP_NORMAL_READ);
echo date('Y-m-d H:i:s ')."Response: ".trim($out)."\n";

$in='Hello again :)'."\n";
echo date('Y-m-d H:i:s ')."Sending: ".$in;
socket_write($socket, $in, strlen($in));
echo "OK.\n";

//$out = socket_read($socket, 2048, PHP_NORMAL_READ);
//echo "Response: ".trim($out)."\n";

$checked_time=0;
$menu_sent_time=time();

while(1) {
    $read=array();
    $read[0] = $socket;
    socket_set_option($socket,SOL_SOCKET, SO_RCVTIMEO, array("sec"=>60, "usec"=>0));
    $num_changed_sockets = socket_select($read, $write = NULL, $except = NULL, 0, 1);
    if ( $num_changed_sockets > 0 ) {
        $out = socket_read($socket, 2048, PHP_NORMAL_READ);
        if ($out === false) {
         break;
        }
        $out=trim($out);
        processResponse($out);
       }

    if (date('Y-m-d H:i:s')!=$last_echo) {
     $last_echo=date('Y-m-d H:i:s');
     echo $last_echo." Listening...\n";
    }

   if (time()-$menu_sent_time>30*60) {
    echo "Updating full menu\n";
    $menu_sent_time=time();
    $connect->sendMenu();
    $commands=SQLSelect("SELECT * FROM commands");
    $total=count($commands);
    for($i=0;$i<$total;$i++) {
     $cmd_values[$commands[$i]['ID']]=$commands[$i]['CUR_VALUE'];
     $cmd_titles[$commands[$i]['ID']]=$commands[$i]['RENDER_TITLE'];
     $cmd_data[$commands[$i]['ID']]=$commands[$i]['RENDER_DATA'];
     }
   }

   if (time()-$checked_time>10) {
    $checked_time=time();


    // update data
    $commands=SQLSelect("SELECT * FROM commands WHERE AUTO_UPDATE>0 AND (NOW()-RENDER_UPDATED)>AUTO_UPDATE");
    $total=count($commands);
    for($i=0;$i<$total;$i++) {
     echo date('Y-m-d H:i:s ')."Updating auto update item (id ".$commands[$i]['ID']." time ".$commands[$i]['AUTO_UPDATE']."): ".$commands[$i]['TITLE']."\n";
     $commands[$i]['RENDER_TITLE']=processTitle($commands[$i]['TITLE']);
     $commands[$i]['RENDER_DATA']=processTitle($commands[$i]['DATA']);
     $commands[$i]['RENDER_UPDATED']=date('Y-m-d H:i:s');
     SQLUpdate('commands', $commands[$i]);
    }

    // sending changes if any
    $commands=SQLSelect("SELECT * FROM commands");
    $total=count($commands);
    $changed_data=array();
    for($i=0;$i<$total;$i++) {
     if ($cmd_values[$commands[$i]['ID']]!=$commands[$i]['CUR_VALUE']) {
      $cmd_values[$commands[$i]['ID']]=$commands[$i]['CUR_VALUE'];
      $changed_data[]=array('TYPE'=>'value', 'ID'=>$commands[$i]['ID'], 'DATA'=>$commands[$i]['CUR_VALUE']);
     }
     if ($cmd_titles[$commands[$i]['ID']]!=$commands[$i]['RENDER_TITLE']) {
      $cmd_titles[$commands[$i]['ID']]=$commands[$i]['RENDER_TITLE'];
      $changed_data[]=array('TYPE'=>'title','ID'=>$commands[$i]['ID'], 'DATA'=>$commands[$i]['RENDER_TITLE']);
     }     
     if ($cmd_data[$commands[$i]['ID']]!=$commands[$i]['RENDER_DATA']) {
      $cmd_data[$commands[$i]['ID']]=$commands[$i]['RENDER_DATA'];
      $changed_data[]=array('TYPE'=>'data','ID'=>$commands[$i]['ID'], 'DATA'=>$commands[$i]['RENDER_DATA']);
     }     
    }
    $total=count($changed_data);
    for($i=0;$i<$total;$i++) {
     $changed_data[$i]['DATA']=str_replace("\n", ' ', $changed_data[$i]['DATA']);
     $changed_data[$i]['DATA']=str_replace("\r", '', $changed_data[$i]['DATA']);
     $changed_data[$i]['DATA']=preg_replace("/<!--(.+?)-->/is", '', $changed_data[$i]['DATA']);
     $in='serial:'.serialize($changed_data[$i])."\n";
     echo date('Y-m-d H:i:s ')."$i. Sending: ".$in;
     socket_write($socket, $in, strlen($in));
     echo "OK.\n";
     $out = socket_read($socket, 2048, PHP_NORMAL_READ);
     processResponse($out);
    }
   }

   if (file_exists('./reboot') || $_GET['onetime']) 
   {
      $db->Disconnect();
      exit;
   }


}


echo date('Y-m-d H:i:s ')."Closing socket...";
socket_close($socket);
echo "OK.\n\n";

}

/**
* Title
*
* Description
*
* @access public
*/
 function processResponse($out) {
   global $socket;
   echo date('Y-m-d H:i:s ')." Incoming: ".trim($out)."\n";  

        if (preg_match('/REQUEST:(.+)/is', $out, $m)) {
         $url=$m[1];
         if (!preg_match('/^http:/', $url)) {
          $url='http://localhost'.$url;
         }
         echo date('Y-m-d H:i:s ')."Sending request to $url\n";
         $content=getURL($url, 0);
        }
        if (preg_match('/PING/is', $out, $m)) {
         $in='PONG!'."\n";
         echo date('Y-m-d H:i:s ')."Sending: ".$in;
         socket_write($socket, $in, strlen($in));
         echo "OK.\n";
         setGlobal((str_replace('.php', '', basename(__FILE__))).'Run', time());
        }
 }

// closing database connection
$db->Disconnect(); 

?>