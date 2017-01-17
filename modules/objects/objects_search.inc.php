<?php
/*
* @version 0.1 (auto-set)
*/

 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters

  if ($this->class_id) {
   $class=SQLSelectOne("SELECT * FROM classes WHERE ID='".$this->class_id."'");
   if ($class['SUB_LIST']!='') {
    $qry.=" AND (CLASS_ID IN (".$class['SUB_LIST'].") OR CLASS_ID=".$class['ID'].")";
   } else {
    $qry.=" AND CLASS_ID='".$class['ID']."'";
   }
  }

  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['objects_qry'];
  } else {
   $session->data['objects_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby;
  if (!$sortby) {
   $sortby=$session->data['objects_sort'];
  } else {
   if ($session->data['objects_sort']==$sortby) {
    if (Is_Integer(strpos($sortby, ' DESC'))) {
     $sortby=str_replace(' DESC', '', $sortby);
    } else {
     $sortby=$sortby." DESC";
    }
   }
   $session->data['objects_sort']=$sortby;
  }
  if (!$sortby) $sortby="TITLE";
  $out['SORTBY']=$sortby;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT objects.*, classes.TITLE as CLASS_TITLE, locations.TITLE as LOCATION_TITLE FROM objects LEFT JOIN locations ON locations.ID=objects.LOCATION_ID LEFT JOIN classes ON classes.ID=objects.CLASS_ID WHERE $qry ORDER BY objects.TITLE");
  if ($res[0]['ID']) {
   paging($res, 50, $out); // search result paging
   colorizeArray($res);

   $total=count($res);
   $cached_properties=array();
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
     $res[$i]['ADD_DESCRIPTION']=getKeyData($res[$i]['ID']);
   }

   $out['RESULT']=$res;
  }
?>