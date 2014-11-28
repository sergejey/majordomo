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
   $qry=$session->data['myblocks_categories_qry'];
  } else {
   $session->data['myblocks_categories_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_myblocks_categories;
  if (!$sortby_myblocks_categories) {
   $sortby_myblocks_categories=$session->data['myblocks_categories_sort'];
  } else {
   if ($session->data['myblocks_categories_sort']==$sortby_myblocks_categories) {
    if (Is_Integer(strpos($sortby_myblocks_categories, ' DESC'))) {
     $sortby_myblocks_categories=str_replace(' DESC', '', $sortby_myblocks_categories);
    } else {
     $sortby_myblocks_categories=$sortby_myblocks_categories." DESC";
    }
   }
   $session->data['myblocks_categories_sort']=$sortby_myblocks_categories;
  }
  if (!$sortby_myblocks_categories) $sortby_myblocks_categories="ID DESC";
  $out['SORTBY']=$sortby_myblocks_categories;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM myblocks_categories WHERE $qry ORDER BY ".$sortby_myblocks_categories);
  if ($res[0]['ID']) {
   paging($res, 50, $out); // search result paging
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
?>