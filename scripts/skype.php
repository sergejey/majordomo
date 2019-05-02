<?php
/**
* SkypeBot
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 1.1
*/

chdir(dirname(__FILE__).'/../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

include_once("./load_settings.php");
include_once(DIR_MODULES."control_modules/control_modules.class.php");

$ctl = new control_modules();

if (!IsWindowsOS()) exit;

if (!Defined('SETTINGS_SKYPE_CYCLE') || SETTINGS_SKYPE_CYCLE == 0) exit;

include_once(DIR_MODULES . 'patterns/patterns.class.php');

$pt = new patterns();

Define('DEVIDER', 'и');

$last_day    = date('d-M-y');
$last_minute = date('H:i');

// Create skype object
$skype = new COM("Skype4COM.Skype");

// Create sink object
$sink = new _ISkypeEvents($com);

// Connect to sink
com_event_sink($skype, $sink, "_ISkypeEvents");

// Start minimized and without splash screen
if (!$skype->client()->isRunning())
   $skype->client()->start(true, true);

//Attach to Skype
$skype->attach(5,false);

//process messages to catch the attachment
com_message_pump(1000);

$old_message  = $latest_message = '';

$tmp = SQLSelectOne("SELECT * FROM shouts WHERE MEMBER_ID=0 ORDER BY ID DESC");

$latest_message = $tmp['MESSAGE'];
$old_message = $latest_message;

$checked_tm=0;

//Main Loop
if ($sink->attached)
{
   $CurrentUser = $skype->CurrentUser;
   echo "Running skypebot...\n";
   //Message loop. Set $sink->terminated to true to quit
   while(!$sink->terminated)
   {
      com_message_pump(10);

      if (time()-$checked_tm>3) {
       $checked_tm=time();
       $tmp = SQLSelectOne("SELECT * FROM shouts WHERE MEMBER_ID=0 ORDER BY ID DESC LIMIT 1");
       $latest_message = $tmp['MESSAGE']; //.' ('.$tmp['IMPORTANCE'].')'
      }

      if ($old_message != $latest_message)
      {
         $old_message = $latest_message;

         if (isset($tmp['IMPORTANCE']) && $tmp['IMPORTANCE']>0)
         {
            $users = SQLSelect("SELECT * FROM users WHERE SKYPE != ''");
            $total = count($users);

            for($i=0;$i<$total;$i++)
            {
               echo "Sending to " . $users[$i]['SKYPE'] . ": " . convert_cyr_string(iconv('UTF-8', 'WINDOWS-1251', $latest_message), 'w', 'd') . "\n";
               $skype->SendMessage(trim($users[$i]['SKYPE']), iconv('UTF-8', 'WINDOWS-1251', $latest_message));
            }
         }
      }

      if (file_exists('./reboot') || IsSet($_GET['onetime']))
      {
         exit;
      }
   }
}

//clear up
$skype = null;

//***************
class _ISkypeEvents
{
   var $terminated = false;
   var $attached   = false;

   //***************
   function AttachmentStatus($status)
   {
      global $skype;

      if ( $status = $skype->Convert->TextToAttachmentStatus("AVAILABLE") )
         $skype->attach(5,false);

      $this->attached = true;
   }

   //***************
   function OnlineStatus(&$pUser, $Status )
   {
      print "Status: $pUser->Handle $Status\n";
   }

   //***************
   function MessageStatus( &$pMessage, $Status )
   {
      global $skype, $CurrentUser, $archivechats;
      global $pt;

      $myhandle = $CurrentUser->Handle;

      $cmeUnknown         = -1;
      $cmeCreatedChatWith = 0;
      $cmeAddedMembers    = 2;
      $cmeSetTopic        = 3;
      $cmeSaid            = 4;
      $cmeLeft            = 5;

      if ($pMessage->Type == $cmeSetTopic)
      {
         //skype_SetTopic($pMessage);
      }
      else if ($pMessage->Type == $cmeAddedMembers)
      {
         //skype_add_member($pMessage);
      }
      else if ($pMessage->Type == $cmeSaid || $pMessage->Type == $cmeUnknown)
      {
         if ($Status == 0 || $Status == 2)
         {
            // print "\n$pMessage->body $Status $pMessage->type";

            if ($pMessage->FromHandle <> $CurrentUser->Handle )
            {
               echo $pMessage->FromHandle . ": " . convert_cyr_string($pMessage->Body, 'w', 'd') . "\n";

               if ( substr(strtolower($pMessage->Body),0,4) == 'ping' )
                  $skype->Chat($pMessage->ChatName)->SendMessage("pong");

               $user = SQLSelectOne("SELECT ID FROM users WHERE SKYPE LIKE '" . $pMessage->FromHandle . "'");

               if (!$user['ID'])
                  $user=SQLSelectOne("SELECT ID FROM users ORDER BY ID");

               $user_id=$user['ID'];

               $qrys  = explode(' '.DEVIDER.' ', iconv('WINDOWS-1251', 'UTF-8', $pMessage->Body));
               $total = count($qrys);

               for($i=0;$i<$total;$i++)
               {
                  $room_id = 0;

                  $rec =array();
                  $rec['ROOM_ID']   =(int)$room_id;
                  $rec['MEMBER_ID'] = $user_id;
                  $rec['MESSAGE']   = htmlspecialchars($qrys[$i]);
                  $rec['ADDED']     = date('Y-m-d H:i:s');

                  SQLInsert('shouts', $rec);
                  $pt->checkAllPatterns();
                  getObject("ThisComputer")->raiseEvent("commandReceived", array("command" => $qrys[$i]));
               }
            }
         }
      }
   }
}
