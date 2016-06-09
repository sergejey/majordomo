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

const CONNECT_HOST = 'connect.smartliving.ru';
const CONNECT_PORT = 11444;

// get settings
$sqlQuery = "SELECT NAME, VALUE
               FROM settings";

$settings = SQLSelect($sqlQuery);
$total = count($settings);

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

$session = new session("prj");

set_time_limit(0);

$socket_connected=0;
$latest_urls_time=0;

while (1)
{
   include_once(DIR_MODULES . 'connect/connect.class.php');

   $connect = new connect();
   $connect->getConfig();

   if (!$connect->config['CONNECT_SYNC'])
   {
      echo "Connect sync turned off.";
      exit;
   }
   else
   {
      /*
      $connect->sendMenu(1);
      */

      $sqlQuery = "SELECT *
                     FROM commands";
      $commands = SQLSelect($sqlQuery);
      $total = count($commands);

      for ($i = 0; $i < $total; $i++)
      {
         $cmd_values[$commands[$i]['ID']] = $commands[$i]['CUR_VALUE'];
         $cmd_titles[$commands[$i]['ID']] = $commands[$i]['RENDER_TITLE'];
         $cmd_data[$commands[$i]['ID']] = $commands[$i]['RENDER_DATA'];
      }
   }


   if ((time()-$latest_urls_time)>5*60) {
    //getting latest URLs
          $latest_urls_time=time();
          $ch = curl_init();

          $url='http://connect.smartliving.ru/latest_urls.php';
          curl_setopt($ch,CURLOPT_URL, $url);
          curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 60);
          curl_setopt($ch,CURLOPT_TIMEOUT, 120);
          curl_setopt($ch,CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
          curl_setopt($ch,CURLOPT_USERPWD, $connect->config['CONNECT_USERNAME'].":".$connect->config['CONNECT_PASSWORD']);

          //execute post
          $result = curl_exec($ch);

          //close connection
          curl_close($ch);

          if ($result) {
           $data=json_decode($result, true);
           if (is_array($data['URLS'])) {
            $total=count($data['URLS']);
            for($i=0;$i<$total;$i++) {
             processResponse($data['URLS'][$i]['DATA']);
            }
           }
          }
   }


   // Create a TCP/IP socket.
   $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

   if ($socket === false)
   {
      echo date('Y-m-d H:i:s ') . "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
      $socket_connected=false;
      sleep(5);
      continue;
   }
   else
   {
      echo "OK.\n";
   }

   socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 10, "usec" => 0));
   socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array("sec" => 10, "usec" => 0));

   echo date('Y-m-d H:i:s ') . 'Attempting to connect to ' . CONNECT_HOST . ' on port ' . CONNECT_PORT . '...';

   $result = socket_connect($socket, CONNECT_HOST, CONNECT_PORT);

   if ($result === false)
   {
      echo 'socket_connect() failed.\nReason: (' . $result . ') ' . socket_strerror(socket_last_error($socket)) . "\n";
      $socket_connected=false;
      sleep(5);
      continue;
   }
   else
   {
      echo "OK.\n";
      $socket_connected=true;
   }

   $in = 'Hello, world!' . "\n";
   echo date('Y-m-d H:i:s ') . 'Sending: ' . $in;
   socket_write($socket, $in, strlen($in));
   echo "OK.\n";

   $out = socket_read($socket, 2048, PHP_NORMAL_READ);
   echo date('Y-m-d H:i:s ') . 'Response: ' . trim($out) . "\n";

   $in = 'auth:' . $connect->config['CONNECT_USERNAME'] . '|' . md5(md5($connect->config['CONNECT_PASSWORD'])) . "\n";

   echo date('Y-m-d H:i:s ') . 'Sending: ' . $in;

   socket_write($socket, $in, strlen($in));

   echo "OK.\n";

   $out = socket_read($socket, 2048, PHP_NORMAL_READ);

   echo date('Y-m-d H:i:s ') . 'Response: ' . trim($out) . "\n";

   $in = 'Hello again :)' . "\n";

   echo date('Y-m-d H:i:s ') . 'Sending: ' . $in;

   socket_write($socket, $in, strlen($in));

   echo "OK.\n";

   $checked_time = 0;
   $menu_sent_time = time();

   while (1)
   {
      $read = array();
      $read[0] = $socket;

      socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 60, "usec" => 0));

      $write  = NULL;
      $except = NULL;
      $num_changed_sockets = socket_select($read, $writ, $except, 0, 1);

      if ($num_changed_sockets > 0)
      {
         $out = socket_read($socket, 2048, PHP_NORMAL_READ);

         if ($out === false)
         {
            break;
         }

         $out = trim($out);

         if (preg_match('/Please login/is', $out)) {
          echo date('Y-m-d H:i:s') . ' Login required. Closing socket...';
          continue 2;
         }

         processResponse($out);
      }

      if (date('Y-m-d H:i:s') != $last_echo)
      {
         $last_echo = date('Y-m-d H:i:s');
         echo $last_echo . " Listening...\n";
      }

      if (time() - $menu_sent_time > 30 * 60)
      {
         echo "Updating full menu\n";

         $sqlQuery = "SELECT *
                        FROM commands";

         $menu_sent_time = time();
         if ($socket_connected) {
          $connect->sendMenu(1);
         }
         $commands = SQLSelect($sqlQuery);
         $total = count($commands);

         for ($i = 0; $i < $total; $i++)
         {
            $cmd_values[$commands[$i]['ID']] = $commands[$i]['CUR_VALUE'];
            $cmd_titles[$commands[$i]['ID']] = $commands[$i]['RENDER_TITLE'];
            $cmd_data[$commands[$i]['ID']] = $commands[$i]['RENDER_DATA'];
         }
      }

      if (time() - $checked_time > 10)
      {
         $checked_time = time();

         // update data
         $sqlQuery = "SELECT *
                        FROM commands
                       WHERE AUTO_UPDATE > 0
                         AND (NOW() - RENDER_UPDATED) > AUTO_UPDATE";

         $commands = SQLSelect($sqlQuery);
         $total = count($commands);

         for ($i = 0; $i < $total; $i++)
         {
            $commands[$i]['RENDER_TITLE'] = processTitle($commands[$i]['TITLE'], $connect);
            $commands[$i]['RENDER_DATA'] = processTitle($commands[$i]['DATA'], $connect);
            $commands[$i]['RENDER_UPDATED'] = date('Y-m-d H:i:s');

            SQLUpdate('commands', $commands[$i]);

            $resultMessage = date('Y-m-d H:i:s');
            $resultMessage .= ' Updating auto update item (id ' . $commands[$i]['ID'];
            $resultMessage .= ' time ' . $commands[$i]['AUTO_UPDATE'] . '): ' . $commands[$i]['TITLE'] . "\n";

            echo $resultMessage;
         }

         // sending changes if any
         $sqlQuery = "SELECT *
                        FROM commands";

         $commands = SQLSelect($sqlQuery);
         $total = count($commands);
         $changed_data = array();

         for ($i = 0; $i < $total; $i++)
         {
            if ($cmd_values[$commands[$i]['ID']] != $commands[$i]['CUR_VALUE'])
            {
               $cmd_values[$commands[$i]['ID']] = $commands[$i]['CUR_VALUE'];
               $changed_data[] = array('TYPE' => 'value',
                                       'ID'   => $commands[$i]['ID'],
                                       'DATA' => $commands[$i]['CUR_VALUE']);
            }

            if ($cmd_titles[$commands[$i]['ID']] != $commands[$i]['RENDER_TITLE'])
            {
               $cmd_titles[$commands[$i]['ID']] = $commands[$i]['RENDER_TITLE'];
               $changed_data[] = array('TYPE' => 'title',
                                       'ID'   => $commands[$i]['ID'],
                                       'DATA' => $commands[$i]['RENDER_TITLE']);
            }

            if ($cmd_data[$commands[$i]['ID']] != $commands[$i]['RENDER_DATA'])
            {
               $cmd_data[$commands[$i]['ID']] = $commands[$i]['RENDER_DATA'];
               $changed_data[] = array('TYPE' => 'data',
                                       'ID'   => $commands[$i]['ID'],
                                       'DATA' => $commands[$i]['RENDER_DATA']);
            }
         }

         $total = count($changed_data);

         for ($i = 0; $i < $total; $i++)
         {
            $changed_data[$i]['DATA'] = str_replace("\n", ' ', $changed_data[$i]['DATA']);
            $changed_data[$i]['DATA'] = str_replace("\r", '', $changed_data[$i]['DATA']);
            $changed_data[$i]['DATA'] = preg_replace("/<!--(.+?)-->/is", '', $changed_data[$i]['DATA']);

            $in = 'serial:' . serialize($changed_data[$i]) . "\n";

            echo date('Y-m-d H:i:s') . ' ' . $i . 'Sending: ' . $in;

            socket_write($socket, $in, strlen($in));

            echo "OK.\n";

            $out = socket_read($socket, 2048, PHP_NORMAL_READ);

            processResponse($out);
         }
      }

      if (file_exists('./reboot') || IsSet($_GET['onetime']))
      {
         socket_close($socket);
         $db->Disconnect();
         exit;
      }
   }

   echo date('Y-m-d H:i:s') . ' Closing socket...';
   socket_close($socket);
   echo "OK.\n\n";
}

/**
 * Summary of processResponse
 * @param mixed $out Out param
 * @return void
 */
function processResponse($out)
{
   global $socket;

   echo date('Y-m-d H:i:s') . ' Incoming: ' . trim($out) . "\n";

   if (preg_match('/REQUEST:(.+)/is', $out, $m))
   {
      $url = $m[1];

      if (!preg_match('/^http:/', $url))
      {
         $url = 'http://localhost' . $url;
      }

      echo date('Y-m-d H:i:s') . ' Sending request to ' . $url . "\n";

      DebMes('Connect command: ' . $url);

      $content = getURL($url, 0);
   }

   if (preg_match('/PING/is', $out, $m))
   {
      $in = "PONG!\n";

      echo date('Y-m-d H:i:s') . ' Sending: ' . $in;

      socket_write($socket, $in, strlen($in));

      echo "OK.\n";

      setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);
   }
}

// closing database connection
$db->Disconnect();
