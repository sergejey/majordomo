<?php
/*
* @version 0.2 (auto-set)
*/
 chdir('../');


 include_once("./config.php");
 include_once("./lib/loader.php");


 $db=new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database
 include_once("./load_settings.php");

 // OPTIMIZATION RULES

 $rules=array(
  'tempSensors.temp'=>array('optimize'=>'avg'),
  'humiditySensors.humidity'=>array('optimize'=>'avg'),
  'uptime'=>array('keep'=>30, 'optimize'=>'max'),
  'Relays.status'=>array('keep'=>0),
  'WeatherStations.tempOutside'=>array('optimize'=>'avg'),
  'WeatherStations.updatedTime'=>array('keep'=>0),
  'WeatherStations.pressureRt'=>array('optimize'=>'avg'),
  'WeatherStations.pressure'=>array('optimize'=>'avg')
 );

 set_time_limit(6000);

 DebMes("Optimize history script started");

 //STEP 1 -- calculate stats
 echo "Calculating stats:<br>";
 $pvalues=SQLSelect("SELECT pvalues.ID, properties.TITLE as PTITLE, classes.TITLE as CTITLE, objects.TITLE as OTITLE FROM pvalues LEFT JOIN objects ON pvalues.OBJECT_ID=objects.ID LEFT JOIN classes ON objects.CLASS_ID=classes.ID LEFT JOIN properties ON pvalues.PROPERTY_ID=properties.ID HAVING PTITLE!=''");
 $total=count($pvalues);
 for($i=0;$i<$total;$i++) {
  $tmp=SQLSelectOne("SELECT COUNT(*) as TOTAL FROM phistory WHERE VALUE_ID='".$pvalues[$i]['ID']."'");
  if ($tmp['TOTAL']) {
   echo $pvalues[$i]['CTITLE'].".".$pvalues[$i]['PTITLE']." (object: ".$pvalues[$i]['OTITLE']."): ";
   $grand_total+=$tmp['TOTAL'];
   if ($tmp['TOTAL']>5000) {
    echo "<b>";
   }
   echo $tmp['TOTAL']."</b>";
   echo "<br>";

   echo str_repeat(' ', 1024);
   flush();
   flush();


  }
 }
 echo "<h2>Grand-total: ".$grand_total."</h2><br>";


   echo str_repeat(' ', 1024);
   flush();
   flush();

//   exit;
 if (!$rules) {
  echo "No rules defined.";
  exit;
 }


 //STEP 2 -- optimize values in time
 $values=SQLSelect("SELECT DISTINCT(VALUE_ID) FROM phistory");
 //print_r($values);
 $total=count($values);
 for($i=0;$i<$total;$i++) {
  $value_id=$values[$i]['VALUE_ID'];
  $pvalue=SQLSelectOne("SELECT pvalues.ID, properties.TITLE as PTITLE, objects.TITLE as OTITLE, classes.TITLE as CTITLE FROM pvalues LEFT JOIN objects ON pvalues.OBJECT_ID=objects.ID LEFT JOIN properties ON pvalues.PROPERTY_ID=properties.ID LEFT JOIN classes ON classes.ID=properties.CLASS_ID WHERE pvalues.ID='".$value_id."'");
  //print_r($pvalue);
  if ($pvalue['CTITLE']!='') {
   $key=$pvalue['CTITLE'].'.'.$pvalue['PTITLE'];
   $rule='';
   if ($rules[$key]) {
    $rule=$rules[$key];
   } elseif ($rules[$pvalue['PTITLE']]) {
    $rule=$rules[$pvalue['PTITLE']];
   }
   if ($rule) {
    //processing
    echo "<h3>".$pvalue['OTITLE']." (".$key.")</h3>";
    $total_before=current(SQLSelectOne("SELECT COUNT(*) as TOTAL FROM phistory WHERE VALUE_ID='".$value_id."'"));
    if (isset($rule['keep'])) {
     echo " removing old (".(int)$rule['keep'].")";
     SQLExec("DELETE FROM phistory WHERE VALUE_ID='".$value_id."' AND TO_DAYS(NOW())-TO_DAYS(ADDED)>=".(int)$rule['keep']);
    }
    if ($rule['optimize']) {

   echo str_repeat(' ', 1024);
   flush();
   flush();

     echo "<br><b>Before last MONTH</b><br>";
     $end=time()-30*24*60*60; // month end older
     $start=current(SQLSelectOne("SELECT UNIX_TIMESTAMP(ADDED) FROM phistory WHERE VALUE_ID='".$value_id."' ORDER BY ADDED LIMIT 1"));
     $interval=2*60*60; // two-hours interval
     optimizeHistoryData($value_id, $rule['optimize'], $interval, $start, $end);

   echo str_repeat(' ', 1024);
   flush();
   flush();


     echo "<br><b>Before last WEEK</b><br>";
     $start=$end+1;
     $end=time()-7*24*60*60; // week and older
     $interval=1*60*60; // one-hour interval
     optimizeHistoryData($value_id, $rule['optimize'], $interval, $start, $end);

   echo str_repeat(' ', 1024);
   flush();
   flush();


     echo "<br><b>Before YESTERDAY</b><br>";
     $start=$end+1;
     $end=time()-1*24*60*60; // day and older
     $interval=20*60; // 20 minutes interval
     optimizeHistoryData($value_id, $rule['optimize'], $interval, $start, $end);

   echo str_repeat(' ', 1024);
   flush();
   flush();


     echo "<br><b>Before last HOUR</b><br>";
     $start=$end+1;
     $end=time()-1*60*60; // 1 hour and older
     $interval=3*60; // 3 minutes interval
     optimizeHistoryData($value_id, $rule['optimize'], $interval, $start, $end);


    }


    $total_after=current(SQLSelectOne("SELECT COUNT(*) as TOTAL FROM phistory WHERE VALUE_ID='".$value_id."'"));
    echo " <b>(changed ".$total_before." -> ".$total_after.")</b><br>";
   }
  }
 }

 SQLExec("OPTIMIZE TABLE phistory;");

 echo "<h1>DONE!!!</h1>";

 $db->Disconnect(); // closing database connection

 DebMes("Optimize history script finished");

/**
* Title
*
* Description
*
* @access public
*/
 function optimizeHistoryData($value_id, $type, $interval, $start, $end) {

   $total_removed=0;
   if (!$interval) {
    return 0;
   }
   echo "Value ID: $value_id <br>";
   echo "Interval from ".date('Y-m-d H:i:s', $start)." to ".date('Y-m-d H:i:s', $end)." (every ".$interval." seconds)<br>";
   $total_values=(int)current(SQLSelectOne("SELECT COUNT(*) FROM phistory WHERE VALUE_ID='".$value_id."' AND ADDED>='".date('Y-m-d H:i:s', $start)."' AND ADDED<='".date('Y-m-d H:i:s', $end)."' "));
   echo "Total values: ".$total_values."<br>";
   if ($total_values<2) {
    return 0;
   }

   $tmp=$end-$start;
   $tmp2=round($tmp/$interval);
   if ($total_values<=$tmp2) {
    echo "... number of values ($total_values) is less than optimal (".$tmp2.") (skipping)<br>";
    return 0;
   }

   echo "Optimizing (should be about ".$tmp2." records)...";

   echo str_repeat(' ', 1024);
   flush();
   flush();

   $first_start=current(SQLSelectOne("SELECT UNIX_TIMESTAMP(ADDED) FROM phistory WHERE VALUE_ID='".$value_id."' AND ADDED>='".date('Y-m-d H:i:s', $start)."' ORDER BY ADDED LIMIT 1"));
   $last_start=current(SQLSelectOne("SELECT UNIX_TIMESTAMP(ADDED) FROM phistory WHERE VALUE_ID='".$value_id."' AND ADDED<='".date('Y-m-d H:i:s', $end)."' ORDER BY ADDED DESC LIMIT 1"));

   while($start<$end) {

    if ($start<($first_start-$interval)) {
     $start+=$interval;
     continue;
    }

   if ($start>($last_start+$interval)) {
     $start+=$interval;
     continue;
    }

    //echo date('Y-m-d H:i:s', $start)."<br>\n";
   echo ".";
   echo str_repeat(' ', 1024);
   flush();
   flush();

    $data=SQLSelect("SELECT * FROM phistory WHERE VALUE_ID='".$value_id."' AND ADDED>='".date('Y-m-d H:i:s', $start)."' AND ADDED<'".date('Y-m-d H:i:s', $start+$interval)."'");
    $total=count($data);
    if ($total>1) {
     $values=array();
     for($i=0;$i<$total;$i++) {
      $values[]=$data[$i]['VALUE'];
     }
     if ($type=='max') {
      $new_value=max($values);
     } elseif ($type=='sum') {
      $new_value=array_sum($values);
     } else {
      $new_value=array_sum($values)/$total;
     }
     SQLExec("DELETE FROM phistory WHERE VALUE_ID='".$value_id."' AND ADDED>='".date('Y-m-d H:i:s', $start)."' AND ADDED<'".date('Y-m-d H:i:s', $start+$interval)."'");
     $rec=array();
     $rec['VALUE_ID']=$value_id;
     $rec['VALUE']=$new_value;
     if ($type=='avg') {
      $rec['ADDED']=date('Y-m-d H:i:s', $start+(int)($interval/2));
     } else {
      $rec['ADDED']=date('Y-m-d H:i:s', $start+$interval-1);
     }
     SQLInsert('phistory', $rec);
     $total_removed+=$total;
     //echo "DONE ($total deleted)<br>";
     //print_r($rec);
     //exit;
    } else {
     //echo "skip mini interval ($total)<br>";
    }

    $start+=$interval;
   }

   echo "<b>Done</b> (removed: $total_removed)<br>";
   SQLExec("OPTIMIZE TABLE `phistory`");

   return $total_removed;

 }

?>
