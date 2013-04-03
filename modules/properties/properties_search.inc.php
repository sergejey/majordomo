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
  if (IsSet($this->class_id)) {
   $class_id=$this->class_id;
   $qry.=" AND CLASS_ID='".$this->class_id."'";

   include_once(DIR_MODULES.'classes/classes.class.php');
   $cl=new classes();
   $out['PARENT_PROPERTIES']=$cl->getParentProperties($this->class_id);
   if (!$out['PARENT_PROPERTIES'][0]['ID']) {
    unset($out['PARENT_PROPERTIES']);
   }

  } else {
   global $class_id;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['properties_qry'];
  } else {
   $session->data['properties_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby;
  if (!$sortby) {
   $sortby=$session->data['properties_sort'];
  } else {
   if ($session->data['properties_sort']==$sortby) {
    if (Is_Integer(strpos($sortby, ' DESC'))) {
     $sortby=str_replace(' DESC', '', $sortby);
    } else {
     $sortby=$sortby." DESC";
    }
   }
   $session->data['properties_sort']=$sortby;
  }
  if (!$sortby) $sortby="TITLE";
  $out['SORTBY']=$sortby;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM properties WHERE $qry ORDER BY $sortby");
  if ($res[0]['ID']) {
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }


?>