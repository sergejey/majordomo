<?
/**
* RSS script
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 1.2
*/


 include_once("./config.php");
 include_once("./lib/loader.php");

 startMeasure('TOTAL'); // start calculation of execution time

 include_once(DIR_MODULES."application.class.php");

 $session=new session("prj");
 $db=new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database
 include_once("./load_settings.php");


Define('DEVIDER', 'и');

if ($qry!='') {

   if (!$session->data['logged_user']) {
    $user=SQLSelectOne("SELECT ID FROM users ORDER BY ID");
    $user_id=$user['ID'];
   } else {
    $user_id=$session->data['logged_user'];
   }

   include_once(DIR_MODULES.'patterns/patterns.class.php');
   $pt=new patterns();

   $qrys=explode(' '.DEVIDER.' ', $qry);
   $total=count($qrys);
   for($i=0;$i<$total;$i++) {

    $room_id=0;
    $rec=array();
    $rec['ROOM_ID']=(int)$room_id;
    $rec['MEMBER_ID']=$user_id;
    $rec['MESSAGE']=htmlspecialchars($qrys[$i]);
    $rec['ADDED']=date('Y-m-d H:i:s');
    SQLInsert('shouts', $rec);

    $pt->checkAllPatterns();
    getObject("ThisComputer")->raiseEvent("commandReceived", array("command"=>$qrys[$i]));

    
   }

   //Header("Location:command.php");exit;

}

?>

<html>
 <head>
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
 <input type="text" name="qry" value="<?echo $qry;?>" speech required x-webkit-speech  onspeechchange="startSearch"><input type="submit" name="Submit" value="Say">
 </form>
 <?if ($qry!='') {?>
 <p>Command: <b><?=$qry?></b></p>
 <?}?>
 <?

$qry="1";

if (!$limit) {
 $limit=20;
}

$res=SQLSelect("SELECT shouts.*, UNIX_TIMESTAMP(shouts.ADDED) as TM, users.NAME FROM shouts LEFT JOIN users ON shouts.MEMBER_ID=users.ID WHERE $qry ORDER BY shouts.ADDED DESC, ID DESC LIMIT ".(int)$limit);
$total=count($res);
for($i=0;$i<$total;$i++) {
 if ($res[$i]['MEMBER_ID']==0) {
  $res[$i]['NAME']='Alice';
 }
 echo date('H:i', $res[$i]['TM']).' <b>'.$res[$i]['NAME'].'</b>: '.$res[$i]['MESSAGE']."<br>";
}

 ?>
 </body>
</html>