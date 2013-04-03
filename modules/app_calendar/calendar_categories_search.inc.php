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
   $qry=$session->data['calendar_categories_qry'];
  } else {
   $session->data['calendar_categories_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_calendar_categories;
  if (!$sortby_calendar_categories) {
   $sortby_calendar_categories=$session->data['calendar_categories_sort'];
  } else {
   if ($session->data['calendar_categories_sort']==$sortby_calendar_categories) {
    if (Is_Integer(strpos($sortby_calendar_categories, ' DESC'))) {
     $sortby_calendar_categories=str_replace(' DESC', '', $sortby_calendar_categories);
    } else {
     $sortby_calendar_categories=$sortby_calendar_categories." DESC";
    }
   }
   $session->data['calendar_categories_sort']=$sortby_calendar_categories;
  }
  if (!$sortby_calendar_categories) $sortby_calendar_categories="PRIORITY DESC, TITLE";
  $out['SORTBY']=$sortby_calendar_categories;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM calendar_categories WHERE $qry ORDER BY ".$sortby_calendar_categories);
  if ($res[0]['ID']) {
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
?>