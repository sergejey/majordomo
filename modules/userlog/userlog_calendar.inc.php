<?php
/*
* @version 0.1 (auto-set)
*/

  if ($field=='') return;
  $lfield=strtolower($field);
  global ${$lfield.'_from'};
  global ${$lfield.'_to'};
   if (${$lfield.'_from'}) {
    $tmp=explode('/', ${$lfield.'_from'});
    $tm=mktime(0, 0, 0, $tmp[0], 15, $tmp[2]);
   } else {
    $tm=mktime(0, 0, 0, date('m', time()), 15, date('Y', time()));
   }
   $month=date('m', $tm);
   $yr=date('Y', $tm);
   $out[$field.'_MONTH']=date('F', $tm);
   $last_days=array(31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
   $out[$field.'_PREV_MONTH']=date('m/01/Y', $tm-30*24*60*60);
   if (checkDate(date('m', $tm-30*24*60*60), $last_days[(int)date('m', $tm-30*24*60*60)-1], date('Y', $tm-30*24*60*60))) {
    $out[$field.'_PREV_MONTH_TO']=date('m/'.$last_days[(int)date('m', $tm-30*24*60*60)-1].'/Y', $tm-30*24*60*60);
   } else {
    $out[$field.'_PREV_MONTH_TO']=date('m/'.($last_days[(int)date('m', $tm-30*24*60*60)-1]-1).'/Y', $tm-30*24*60*60);
   }
   $out[$field.'_NEXT_MONTH']=date('m/01/Y', $tm+30*24*60*60);  
   if (checkDate(date('m', $tm+30*24*60*60), $last_days[(int)date('m', $tm+30*24*60*60)-1], date('Y', $tm+30*24*60*60))) {
    $out[$field.'_NEXT_MONTH_TO']=date('m/'.$last_days[(int)date('m', $tm+30*24*60*60)-1].'/Y', $tm+30*24*60*60);
   } else {
    $out[$field.'_NEXT_MONTH_TO']=date('m/'.($last_days[(int)date('m', $tm+30*24*60*60)-1]-1).'/Y', $tm+30*24*60*60);
   }
   $out[$field.'_THIS_MONTH']=date('m/01/Y', $tm);  
   if (checkDate(date('m', $tm), $last_days[(int)date('m', $tm)-1], date('Y', $tm))) {
    $out[$field.'_THIS_MONTH_TO']=date('m/'.$last_days[(int)date('m', $tm)-1].'/Y', $tm);
   } else {
    $out[$field.'_THIS_MONTH_TO']=date('m/'.($last_days[(int)date('m', $tm)-1]-1).'/Y', $tm);
   }
   $days=array();
   for($i=1;$i<=31;$i++) {
   if (checkDate($month, $i, $yr)) {
     $tm=mktime(0, 0, 0, $month, $i, $yr);
     $rec=array();
     $day=$i;
     if ($i<10) $day="0$i";
     $rec['DAY']=$i;
     $rec['DATE']="$month/$day/$yr";
     $rec['DB_DATE']="$yr-$month-$day";
     if ($field!='') {
      $event=SQLSelectOne("SELECT ID FROM userlog WHERE TO_DAYS($field)=TO_DAYS('".$rec['DB_DATE']."')");
      if ($event['ID']) {
       $rec['EVENTS']=1;
      }
     }
     if ($rec['DATE']==${$lfield.'_from'} && $rec['DATE']==${$lfield.'_to'}) {
      $rec['SELECTED']=1;
     }
     if ($rec['DATE']==date('m/d/Y')) {
      $rec['TODAY']=1;
     }
     if ($tm>time()) {
      $rec['FUTURE']=1;
     }
     $rec['WEEKDAY']=date("w", $tm);
     if ($i==1) {
      for ($k=0;$k<($rec['WEEKDAY']);$k++) {
       $rec2=array();
       $days[]=$rec2;
      }
     }
     $last_wday=$rec['WEEKDAY'];
     $days[]=$rec;
    }
   }
   $weekdays=array("Su", "Mo", "Tu", "We", "Th", "Fr", "Sa");
   outArray($field."_WDAYS", $weekdays, $out);
   $out[$field.'_DAYS']=$days;
?>