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
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['events_qry'];
  } else {
   $session->data['events_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby;
  if (!$sortby) {
   $sortby=$session->data['events_sort'];
  } else {
   if ($session->data['events_sort']==$sortby) {
    if (Is_Integer(strpos($sortby, ' DESC'))) {
     $sortby=str_replace(' DESC', '', $sortby);
    } else {
     $sortby=$sortby." DESC";
    }
   }
   $session->data['events_sort']=$sortby;
  }
  if (!$sortby) $sortby="ID DESC";
  $out['SORTBY']=$sortby;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM events WHERE $qry ORDER BY EVENT_NAME");
  if ($res[0]['ID']) {
   //paging($res, 50, $out); // search result paging
   //colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
    $tmp=explode(' ', $res[$i]['ADDED']);
    $res[$i]['ADDED']=fromDBDate($tmp[0])." ".$tmp[1];
    $tmp=explode(' ', $res[$i]['EXPIRE']);
    $res[$i]['EXPIRE']=fromDBDate($tmp[0])." ".$tmp[1];
    $objects=SQLSelect("SELECT * FROM events_params WHERE EVENT_ID=".$res[$i]['ID']." AND LINKED_OBJECT!=''");
    $totalo = count($objects);
    for ($io = 0; $io < $totalo; $io++) {
     $res[$i]['OBJECTS'].=$objects[$io]['LINKED_OBJECT'].'.'.$objects[$io]['LINKED_PROPERTY'].';';
    }

    if ($_GET['clear_notlinked'] && !$res[$i]['OBJECTS'] && !$res[$i]['DESCRIPTION']) {
     $this->delete_events($res[$i]['ID']);
    }


   }
   $out['ITEMS']=$this->pathToTree($res);

   $out['RESULT']=$res;

  }

 if ($_GET['clear_notlinked']) {
  $this->redirect('?');
 }

?>