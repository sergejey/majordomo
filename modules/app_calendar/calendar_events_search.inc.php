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
   $qry=$session->data['calendar_events_qry'];
  } else {
   $session->data['calendar_events_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_calendar_events;
  if (!$sortby_calendar_events) {
   $sortby_calendar_events=$session->data['calendar_events_sort'];
  } else {
   if ($session->data['calendar_events_sort']==$sortby_calendar_events) {
    if (Is_Integer(strpos($sortby_calendar_events, ' DESC'))) {
     $sortby_calendar_events=str_replace(' DESC', '', $sortby_calendar_events);
    } else {
     $sortby_calendar_events=$sortby_calendar_events." DESC";
    }
   }
   $session->data['calendar_events_sort']=$sortby_calendar_events;
  }
  if (!$sortby_calendar_events) $sortby_calendar_events="ID DESC";
  $out['SORTBY']=$sortby_calendar_events;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM calendar_events WHERE $qry ORDER BY ".$sortby_calendar_events);
  if ($res[0]['ID']) {
   paging($res, 50, $out); // search result paging
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
    $res[$i]['DUE']=fromDBDate($res[$i]['DUE']);
   }
   $out['RESULT']=$res;
  }
?>