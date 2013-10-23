<?php
/*
* @version 0.1 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  //searching 'TITLE' (varchar)
  global $title;
  if ($title!='') {
   $qry.=" AND TITLE LIKE '%".DBSafe($title)."%'";
   $out['TITLE']=$title;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['snmpdevices_qry'];
  } else {
   $session->data['snmpdevices_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_snmpdevices;
  if (!$sortby_snmpdevices) {
   $sortby_snmpdevices=$session->data['snmpdevices_sort'];
  } else {
   if ($session->data['snmpdevices_sort']==$sortby_snmpdevices) {
    if (Is_Integer(strpos($sortby_snmpdevices, ' DESC'))) {
     $sortby_snmpdevices=str_replace(' DESC', '', $sortby_snmpdevices);
    } else {
     $sortby_snmpdevices=$sortby_snmpdevices." DESC";
    }
   }
   $session->data['snmpdevices_sort']=$sortby_snmpdevices;
  }
  if (!$sortby_snmpdevices) $sortby_snmpdevices="TITLE";
  $out['SORTBY']=$sortby_snmpdevices;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM snmpdevices WHERE $qry ORDER BY ".$sortby_snmpdevices);
  if ($res[0]['ID']) {
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
?>