<?

 global $ajax;
 global $op;

 if ($ajax) {

  if (!headers_sent()) {
   header ("HTTP/1.0: 200 OK\n");
   header ('Content-Type: text/html; charset=utf-8');
  }
  
  if ($op=='getmarkers') {
   $data=array();
   $markers=SQLSelect("SELECT gpsdevices.*, users.NAME FROM gpsdevices LEFT JOIN users ON gpsdevices.USER_ID=users.ID WHERE TO_DAYS(NOW())-TO_DAYS(gpsdevices.UPDATED)<=30");
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
   $log=SQLSelect("SELECT * FROM gpslog WHERE DEVICE_ID='".(int)$device_id."' AND (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(ADDED))<24*60*60 ORDER BY ADDED");
   $total=count($log);
   $coords=array();
   $points=array();
   for($i=0;$i<$total;$i++) {
    $coords[]=array($log[$i]['LAT'], $log[$i]['LON']);
    $points[]=array('ID'=>$log[$i]['ID'], 'LAT'=>$log[$i]['LAT'], 'LON'=>$log[$i]['LON'], 'TITLE'=>$device['TITLE'].' ('.$log[$i]['ADDED'].')');
   }
   $res=array();
   $res['PATH']=$coords;
   $res['POINTS']=$points;
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

 $out['DEVICES']=SQLSelect("SELECT * FROM gpsdevices");

?>