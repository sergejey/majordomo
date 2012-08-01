<?
/*
* @version 0.2 (wizard)
*/

 global $clear_log;
 if ($clear_log) {
  SQLExec("DELETE FROM gpslog");
  $this->redirect("?");
 }

 global $optimize_log;
 if ($optimize_log) {
  $records=SQLSelect("SELECT ID, DEVICEID, LOCATION_ID FROM gpslog ORDER BY DEVICEID, ADDED DESC");
  $total=count($records);
  $to_delete=array();
  for($i=1;$i<$total-1;$i++) {
   if (!$records[$i]['LOCATION_ID']) continue;
   if ($records[$i]['LOCATION_ID']==$records[$i+1]['LOCATION_ID'] && $records[$i]['LOCATION_ID']==$records[$i-1]['LOCATION_ID']) {
    $to_delete[]=$records[$i]['ID'];
   }
  }

  if ($to_delete[0]) {
   $total=count($to_delete);
   //echo implode(', ', $to_delete);
   for($i=0;$i<$total;$i++) {
    SQLExec("DELETE FROM gpslog WHERE ID=".$to_delete[$i]);
   }
   $this->redirect("?");
  }

 }

 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  if (IsSet($this->device_id)) {
   $device_id=$this->device_id;
   $qry.=" AND DEVICE_ID='".$this->device_id."'";
  } else {
   global $device_id;
  }
  if (IsSet($this->location_id)) {
   $location_id=$this->location_id;
   $qry.=" AND LOCATION_ID='".$this->location_id."'";
  } else {
   global $location_id;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['gpslog_qry'];
  } else {
   $session->data['gpslog_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_gpslog;
  if (!$sortby_gpslog) {
   $sortby_gpslog=$session->data['gpslog_sort'];
  } else {
   if ($session->data['gpslog_sort']==$sortby_gpslog) {
    if (Is_Integer(strpos($sortby_gpslog, ' DESC'))) {
     $sortby_gpslog=str_replace(' DESC', '', $sortby_gpslog);
    } else {
     $sortby_gpslog=$sortby_gpslog." DESC";
    }
   }
   $session->data['gpslog_sort']=$sortby_gpslog;
  }
  if (!$sortby_gpslog) $sortby_gpslog="gpslog.ID DESC";
  $out['SORTBY']=$sortby_gpslog;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT gpslog.*, gpsdevices.TITLE as DEVICE_TITLE, gpslocations.TITLE as LOCATION_TITLE FROM gpslog LEFT JOIN gpsdevices ON gpsdevices.ID=gpslog.DEVICE_ID LEFT JOIN gpslocations ON gpslocations.ID=gpslog.LOCATION_ID WHERE $qry ORDER BY ".$sortby_gpslog);
  if ($res[0]['ID']) {
   paging($res, 50, $out); // search result paging
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
    $tmp=explode(' ', $res[$i]['ADDED']);
    $res[$i]['ADDED']=fromDBDate($tmp[0])." ".$tmp[1];
   }
   $out['RESULT']=$res;
  }
?>