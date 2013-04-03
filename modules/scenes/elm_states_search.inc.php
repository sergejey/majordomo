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
  if (IsSet($this->element_id)) {
   $element_id=$this->element_id;
   $qry.=" AND ELEMENT_ID='".$this->element_id."'";
  } else {
   global $element_id;
  }
  //searching 'TITLE' (varchar)
  global $title;
  if ($title!='') {
   $qry.=" AND TITLE LIKE '%".DBSafe($title)."%'";
   $out['TITLE']=$title;
  }
  if (IsSet($this->script_id)) {
   $script_id=$this->script_id;
   $qry.=" AND SCRIPT_ID='".$this->script_id."'";
  } else {
   global $script_id;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['elm_states_qry'];
  } else {
   $session->data['elm_states_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_elm_states;
  if (!$sortby_elm_states) {
   $sortby_elm_states=$session->data['elm_states_sort'];
  } else {
   if ($session->data['elm_states_sort']==$sortby_elm_states) {
    if (Is_Integer(strpos($sortby_elm_states, ' DESC'))) {
     $sortby_elm_states=str_replace(' DESC', '', $sortby_elm_states);
    } else {
     $sortby_elm_states=$sortby_elm_states." DESC";
    }
   }
   $session->data['elm_states_sort']=$sortby_elm_states;
  }
  if (!$sortby_elm_states) $sortby_elm_states="TITLE";
  $out['SORTBY']=$sortby_elm_states;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM elm_states WHERE $qry ORDER BY ".$sortby_elm_states);
  if ($res[0]['ID']) {
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
?>