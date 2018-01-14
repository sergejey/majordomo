<?php

/**
 * COMMAND script
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 * @version 1.2
 */
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    exit(0);
}


include_once("./config.php");
include_once("./lib/loader.php");

startMeasure('TOTAL'); // start calculation of execution time

include_once(DIR_MODULES . "application.class.php");

$session = new session("prj");

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once("./load_settings.php");

Define('DEVIDER', 'и');

$sqlQuery = "SELECT MESSAGE
               FROM shouts
              WHERE MEMBER_ID = 0
              ORDER BY ID DESC
              LIMIT 1";

$lastest_word = current(SQLSelectOne($sqlQuery));

if ($qry != '' && $qry != $lastest_word)
{

   global $terminal;
   if ($terminal) {
    $session->data['TERMINAL']=$terminal;
   }

   $terminals=SQLSelect("SELECT * FROM terminals ORDER BY TITLE");
   $total=count($terminals);
   for($i=0;$i<$total;$i++) {
    if ($terminals[$i]['HOST']!='' && $_SERVER['REMOTE_ADDR']==$terminals[$i]['HOST'] && !$session->data['TERMINAL']) {
     $session->data['TERMINAL']=$terminals[$i]['NAME'];
    }
    if (mb_strtoupper($terminals[$i]['NAME'], 'UTF-8')==mb_strtoupper($session->data['TERMINAL'], 'UTF-8')) {
     $terminal_rec=$terminals[$i];
    }
   }


   if ($username) {
       $user    = SQLSelectOne("SELECT ID FROM users WHERE USERNAME LIKE '".DBSafe(trim($username))."'");
       $user_id = (int)$user['ID'];
   }

   if (!$user_id) {
       if ($session->data['logged_user']) {
           $user_id = $session->data['logged_user'];
       } else {
           $user    = SQLSelectOne("SELECT ID FROM users ORDER BY ID");
           $user_id = $user['ID'];
       }
   }

   if (isset($params['user_id'])) { $user_id = $params['user_id']; } 

   include_once(DIR_MODULES . 'patterns/patterns.class.php');
   
   $pt = new patterns();

   $qrys  = explode(' ' . DEVIDER . ' ', $qry);
   $total = count($qrys);
   
   for ($i = 0; $i < $total; $i++)
   {
      $room_id = 0;

      $say_source='';
      if ($terminal_rec['ID']) {
       $say_source='terminal'.$terminal_rec['ID'];
       $terminal_rec['LATEST_ACTIVITY']=date('Y-m-d H:i:s');
       $terminal_rec['LATEST_REQUEST_TIME']=$terminal_rec['LATEST_ACTIVITY'];
       $terminal_rec['LATEST_REQUEST']=$rec['MESSAGE'];
       $terminal_rec['IS_ONLINE']=1;
       SQLUpdate('terminals', $terminal_rec);
      }

      if ($source) {
       $say_source=$source;
      }

      say(htmlspecialchars($qrys[$i]), 0, $user_id, $say_source);

      /*
      $rec = array();
      $rec['ROOM_ID']   = (int)$room_id;
      $rec['MEMBER_ID'] = $user_id;
      $rec['MESSAGE']   = htmlspecialchars($qrys[$i]);
      $rec['ADDED']     = date('Y-m-d H:i:s');
      SQLInsert('shouts', $rec);

      $res = $pt->checkAllPatterns($rec['MEMBER_ID']);
      
      if (!$res)
         processCommand($qrys[$i]);
         */
   }
   SQLExec('UPDATE terminals SET IS_ONLINE=0 WHERE LATEST_ACTIVITY < (NOW() - INTERVAL 30 MINUTE)');
   
}

if (!headers_sent())
{
   header("HTTP/1.0: 200 OK\n");
   header('Content-Type: text/html; charset=utf-8');
   header('Access-Control-Allow-Origin: *');  
}
?>

<html>
<head>
   <title></title>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
   <script type="text/javascript">
      function startSearch(event) {
         event.target.form.submit();
      }
      </script>

   <form action="?" method="get" name="frmSearch">
      <input type="text" name="qry" value="<?php echo $qry; ?>" speech required x-webkit-speech 
         onspeechchange="startSearch" /><input type="submit" name="Submit" value="Say" />
   </form>

<?php

if ($qry != '')
{
   echo "<p>Command: <b>" . $qry . "</b></p>";
}

$qry = "1";

if (!$limit)
   $limit = 20;

$sqlQuery = "SELECT shouts.*, UNIX_TIMESTAMP(shouts.ADDED) as TM, users.NAME
               FROM shouts
               LEFT JOIN users ON shouts.MEMBER_ID = users.ID
              WHERE $qry
              ORDER BY shouts.ADDED DESC, ID DESC
              LIMIT " . (int)$limit;

$res   = SQLSelect($sqlQuery);
$total = count($res);

$latest_date='';

for ($i = 0; $i < $total; $i++)
{

  if (date('Y-m-d',$res[$i]['TM'])!=$latest_date) {
   $latest_date=date('Y-m-d',$res[$i]['TM']);
   echo "<h2>$latest_date</h2>\n\n";
  }

   if ($res[$i]['MEMBER_ID'] == 0)
      $res[$i]['NAME'] = 'Alice';
   
   echo date('H:i', $res[$i]['TM']) . ' <b>' . $res[$i]['NAME'] . '</b>: ' . $res[$i]['MESSAGE'] . "<br />";
}

?>

</body>
</html>