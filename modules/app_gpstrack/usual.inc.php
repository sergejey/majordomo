<?php

 global $ajax;
 global $op;
 global $period;
 global $to;
 global $from;

 $colors=array('red', 'blue', 'green', 'orange', 'brown', 'gray', 'yellow', 'white');

 $qry=1;
 if ($period=='week') {
  $qry.=" AND (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(ADDED))<7*24*60*60";
  $to=date('Y-m-d');
  $from=date('Y-m-d', time()-7*24*60*60);
 } elseif ($period=='month') {
  $qry.=" AND (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(ADDED))<31*24*60*60";
  $to=date('Y-m-d');
  $from=date('Y-m-d', time()-31*24*60*60);
 } elseif ($period=='custom') {
  $qry.=" AND ADDED>=DATE('".$from." 00:00:00')";
  $qry.=" AND ADDED<=DATE('".$to." 23:59:59')";
 } elseif ($period=='day') {
  $qry.=" AND (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(ADDED))<1*24*60*60";
  $to=date('Y-m-d');
  $from=date('Y-m-d', time()-1*24*60*60);
 } else {
  $period='today';
  $qry.=" AND (TO_DAYS(NOW())=TO_DAYS(ADDED))";
  $to=date('Y-m-d');
  $from=$to;
 }

 $out['DEVICES']=SQLSelect("SELECT gpsdevices.*, users.NAME FROM gpsdevices LEFT JOIN users ON gpsdevices.USER_ID=users.ID WHERE 1 ORDER BY users.NAME");//TO_DAYS(NOW())-TO_DAYS(gpsdevices.UPDATED)<=30 
 $total=count($out['DEVICES']);
 for($i=0;$i<$total;$i++) {
  $latest_point=SQLSelectOne("SELECT * FROM gpslog WHERE DEVICE_ID='".$out['DEVICES'][$i]['ID']."' ORDER BY ADDED DESC");
  $out['DEVICES'][$i]['LATEST_LAT']=$latest_point['LAT'];
  $out['DEVICES'][$i]['LATEST_LON']=$latest_point['LON'];
  $out['DEVICES'][$i]['COLOR']=$colors[$i];
 }


 if ($ajax) {

  if (!headers_sent()) {
   header ("HTTP/1.0: 200 OK\n");
   header ('Content-Type: text/html; charset=utf-8');
  }
  
  if ($op=='getmarkers') {
   $data=array();
   $markers=$out['DEVICES'];
   $total=count($markers);
   for($i=0;$i<$total;$i++) {
    $markers[$i]['HTML']="<span style='color:black;'>".$markers[$i]['NAME']." (".$markers[$i]['TITLE'].")</span>";
    $data['MARKERS'][]=$markers[$i];
   }
   echo json_encode($data);
  }

  if ($op=='getroute') {
   global $device_id;
   $device=SQLSelectOne("SELECT * FROM gpsdevices WHERE ID='".(int)$device_id."'");
   $log=SQLSelect("SELECT * FROM gpslog WHERE DEVICE_ID='".(int)$device_id."' AND ".$qry." ORDER BY ADDED");
   $total=count($log);
   $coords=array();
   $points=array();
   for($i=0;$i<$total;$i++) {
    $coords[]=array($log[$i]['LAT'], $log[$i]['LON']);
    $points[]=array('ID'=>$log[$i]['ID'], 'LAT'=>$log[$i]['LAT'], 'LON'=>$log[$i]['LON'], 'TITLE'=>$device['TITLE'].' ('.$log[$i]['ADDED'].')');
   }
   $res=array();
   if ($total) {
    $res['FIRST_POINT']=$points[0];
    $res['LAST_POINT']=$points[count($points)-1];
    $res['PATH']=$coords;
    $res['POINTS']=$points;
   }
   echo json_encode($res);
  }

  if ($op=='getlocations') {
   $res=array();
   $res['LOCATIONS']=SQLSelect("SELECT * FROM gpslocations");
   echo json_encode($res);
  }

  exit;
 }

 $latest_point=SQLSelectOne("SELECT * FROM gpslog ORDER BY ADDED DESC");
 $out['LATEST_LAT']=$latest_point['LAT'];
 $out['LATEST_LON']=$latest_point['LON'];

 $out['TO']=$to;
 $out['FROM']=$from;
 $out['PERIOD']=$period;

?>