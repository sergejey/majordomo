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
  if (IsSet($this->object_id)) {
   $object_id=$this->object_id;
   $qry.=" AND OBJECT_ID='".$this->object_id."'";
  } else {
   global $object_id;
  }
  if (IsSet($this->class_id)) {
   $class_id=$this->class_id;
   $qry.=" AND CLASS_ID='".$this->class_id."'";

   include_once(DIR_MODULES.'classes/classes.class.php');
   $cl=new classes();
   $out['PARENT_METHODS']=$cl->getParentMethods($this->class_id);
   if (!$out['PARENT_METHODS'][0]['ID']) {
    unset($out['PARENT_METHODS']);
   }


  } else {
   global $class_id;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['methods_qry'];
  } else {
   $session->data['methods_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby;
  if (!$sortby) {
   $sortby=$session->data['methods_sort'];
  } else {
   if ($session->data['methods_sort']==$sortby) {
    if (Is_Integer(strpos($sortby, ' DESC'))) {
     $sortby=str_replace(' DESC', '', $sortby);
    } else {
     $sortby=$sortby." DESC";
    }
   }
   $session->data['methods_sort']=$sortby;
  }
  if (!$sortby) $sortby="TITLE";
  $out['SORTBY']=$sortby;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM methods WHERE $qry ORDER BY $sortby");
  if ($res[0]['ID']) {
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
?>