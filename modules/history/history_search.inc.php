<?php
/*
* @version 0.3 (auto-set)
*/


 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  if (IsSet($this->object_id)) {
   $object_id=$this->object_id;
  } else {
   global $object_id;
  }

  if ($object_id) {
    $qry.=" AND history.OBJECT_ID='".$object_id."'";
    $out['OBJECT_ID']=$object_id;
  }

  if (IsSet($this->method_id)) {
   $method_id=$this->method_id;
   $qry.=" AND history.METHOD_ID='".$this->method_id."'";
  } else {
   global $method_id;
  }
  if (IsSet($this->value_id)) {
   $value_id=$this->value_id;
   $qry.=" AND history.VALUE_ID='".$this->value_id."'";
  } else {
   global $value_id;
  }




  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['history_qry'];
  } else {
   $session->data['history_qry']=$qry;
  }
  if (!$qry) $qry="1";

 if ($this->mode=='clear_history') {
  SQLExec("DELETE FROM history WHERE ".$qry);
  $this->redirect("?");
 }


  // FIELDS ORDER
  global $sortby;
  if (!$sortby) {
   $sortby=$session->data['history_sort'];
  } else {
   if ($session->data['history_sort']==$sortby) {
    if (Is_Integer(strpos($sortby, ' DESC'))) {
     $sortby=str_replace(' DESC', '', $sortby);
    } else {
     $sortby=$sortby." DESC";
    }
   }
   $session->data['history_sort']=$sortby;
  }
  $sortby="ADDED DESC, ID DESC";
  $out['SORTBY']=$sortby;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT history.ID FROM history LEFT JOIN objects ON objects.ID=history.OBJECT_ID LEFT JOIN methods ON methods.ID=history.METHOD_ID LEFT JOIN pvalues ON history.VALUE_ID=pvalues.ID LEFT JOIN properties ON pvalues.PROPERTY_ID=properties.ID WHERE $qry ORDER BY $sortby LIMIT 100");
  if ($res[0]['ID']) {
   paging($res, 50, $out); // search result paging
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
    $res[$i]=SQLSelectOne("SELECT history.*, objects.TITLE as OBJECT_TITLE, methods.TITLE as METHOD_TITLE, properties.TITLE as VALUE_TITLE FROM history LEFT JOIN objects ON objects.ID=history.OBJECT_ID LEFT JOIN methods ON methods.ID=history.METHOD_ID LEFT JOIN pvalues ON history.VALUE_ID=pvalues.ID LEFT JOIN properties ON pvalues.PROPERTY_ID=properties.ID WHERE history.ID='".$res[$i]['ID']."' ORDER BY $sortby LIMIT 100");
    $tmp=explode(' ', $res[$i]['ADDED']);
    $res[$i]['ADDED']=fromDBDate($tmp[0])." ".$tmp[1];
   }
   $out['RESULT']=$res;
  }

  if (!$this->object_id) {
   $out['OBJECTS']=SQLSelect("SELECT DISTINCT(history.OBJECT_ID) as ID, objects.TITLE FROM history LEFT JOIN objects ON objects.ID=history.OBJECT_ID ORDER BY objects.TITLE");
  }

?>