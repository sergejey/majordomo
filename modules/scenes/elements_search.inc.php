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
  if (IsSet($this->scene_id)) {
   $scene_id=$this->scene_id;
   $qry.=" AND SCENE_ID='".$this->scene_id."'";
  } else {
   global $scene_id;
  }
  //searching 'TITLE' (varchar)
  global $title;
  if ($title!='') {
   $qry.=" AND TITLE LIKE '%".DBSafe($title)."%'";
   $out['TITLE']=$title;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['elements_qry'];
  } else {
   $session->data['elements_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_elements;
  if (!$sortby_elements) {
   $sortby_elements=$session->data['elements_sort'];
  } else {
   if ($session->data['elements_sort']==$sortby_elements) {
    if (Is_Integer(strpos($sortby_elements, ' DESC'))) {
     $sortby_elements=str_replace(' DESC', '', $sortby_elements);
    } else {
     $sortby_elements=$sortby_elements." DESC";
    }
   }
   $session->data['elements_sort']=$sortby_elements;
  }
  if (!$sortby_elements) $sortby_elements="TITLE";
  $out['SORTBY']=$sortby_elements;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM elements WHERE $qry ORDER BY ".$sortby_elements);
  if ($res[0]['ID']) {
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
?>