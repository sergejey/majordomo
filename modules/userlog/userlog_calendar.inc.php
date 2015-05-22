<?php
/*
 * @version 0.1 (auto-set)
 */

if ($field == '') return;

$lfield = strtolower($field);

global ${$lfield . '_from'};
global ${$lfield . '_to'};

$dateDay = 15; // magic number! 
$dateMonth = (int)date('m');
$dateYear = (int)date('Y');

if (${$lfield . '_from'}) 
{
   $tmp = explode('/', ${$lfield . '_from'});
   
   $tmpMonth = (int)$tmp[0];
   $tmpDay = (int)$tmp[1];
   $tmpYear = (int)$tmp[2];
   
   if (checkdate($tmpMonth,$tmpDay,$tmpYear))
   {
      $dateMonth = $tmpMonth;
      $dateYear = $tmpYear;
   }
}

$tm = mktime(0, 0, 0, $dateMonth, $dateDay, $dateYear); // 15 day of current month
$lastDayOfCurrentMonth = (int)date('t', $tm); 
$timeShift = 30*24*60*60;
$datePrevMonth = $tm-$timeShift;
$dateNextMonth = $tm+$timeShift;

$out[$field . '_MONTH'] = date('F', $tm);

$out[$field . '_PREV_MONTH'] = date('m/01/Y', $datePrevMonth);
$out[$field . '_PREV_MONTH_TO'] = date('m/t/Y', $datePrevMonth);

$out[$field . '_NEXT_MONTH'] = date('m/01/Y', $dateNextMonth);  
$out[$field . '_NEXT_MONTH_TO'] = date('m/t/Y', $dateNextMonth);

$out[$field . '_THIS_MONTH'] = date('m/01/Y', $tm);  
$out[$field . '_THIS_MONTH_TO'] = date('m/t/Y', $tm);

$days = array();
for($i = 1; $i <= $lastDayOfCurrentMonth; $i++)
{
   $tm = mktime(0, 0, 0, $dateMonth, $i, $dateYear);
   
   $rec = array();
   $rec['DAY'] = $i;
   $rec['DATE'] = date("m/d/Y", $tm);
   $rec['DB_DATE'] = date("Y-m-d", $tm);
   
   $event = SQLSelectOne("SELECT ID FROM userlog WHERE TO_DAYS($field) = TO_DAYS('".$rec['DB_DATE']."')");
   
   if (isset($event['ID']))
      $rec['EVENTS'] = 1;
   
   if ($rec['DATE'] == ${$lfield.'_from'} && $rec['DATE'] == ${$lfield.'_to'})
      $rec['SELECTED'] = 1;
   
   if ($rec['DATE'] == date('m/d/Y'))
   {
      $rec['TODAY'] = 1;
   }
   
   if ($tm > time())
      $rec['FUTURE'] = 1;
   
   $rec['WEEKDAY'] = date("w", $tm);
   
   if ($i == 1)
   {
      for ($k = 0; $k < ($rec['WEEKDAY']); $k++)
      {
         $rec2 = array();
         $days[] = $rec2;
      }
   }
   
   $last_wday = $rec['WEEKDAY'];
   $days[] = $rec;
}

$weekdays = array("Su", "Mo", "Tu", "We", "Th", "Fr", "Sa");
outArray($field . "_WDAYS", $weekdays, $out);
$out[$field . '_DAYS'] = $days;

?>