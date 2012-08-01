<?
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='gpslocations';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'TITLE' (varchar, required)
   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
  //updating 'LAT' (float, required)
   global $lat;
   $rec['LAT']=(float)$lat;
   /*
   if (!$rec['LAT']) {
    $out['ERR_LAT']=1;
    $ok=0;
   }
   */
  //updating 'LON' (float, required)
   global $lon;
   $rec['LON']=(float)$lon;
   /*
   if (!$rec['LON']) {
    $out['ERR_LON']=1;
    $ok=0;
   }
   */
  //updating 'RANGE' (float, required)
   global $range;
   $rec['RANGE']=(float)$range;
   /*
   if (!$rec['RANGE']) {
    $out['ERR_RANGE']=1;
    $ok=0;
   }
   */
  //updating 'VIRTUAL_USER_ID' (int)
   global $virtual_user_id;
   $rec['VIRTUAL_USER_ID']=(int)$virtual_user_id;
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
?>