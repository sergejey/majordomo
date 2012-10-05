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
  exit;
 }

 $latest_point=SQLSelectOne("SELECT * FROM gpslog ORDER BY ADDED DESC");
 $out['LATEST_LAT']=$latest_point['LAT'];
 $out['LATEST_LON']=$latest_point['LON'];

?>