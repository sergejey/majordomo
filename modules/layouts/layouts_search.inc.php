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
  //searching 'URL' (url)
  global $url;
  if ($url!='') {
   $qry.=" AND URL LIKE '%".DBSafe($url)."%'";
   $out['URL']=$url;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['layouts_qry'];
  } else {
   $session->data['layouts_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby;
  if (!$sortby) {
   $sortby=$session->data['layouts_sort'];
  } else {
   if ($session->data['layouts_sort']==$sortby) {
    if (Is_Integer(strpos($sortby, ' DESC'))) {
     $sortby=str_replace(' DESC', '', $sortby);
    } else {
     $sortby=$sortby." DESC";
    }
   }
   $session->data['layouts_sort']=$sortby;
  }
  $sortby="PRIORITY DESC,TITLE";
  $out['SORTBY']=$sortby;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM layouts WHERE $qry ORDER BY $sortby");
  if ($res[0]['ID']) {
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
?>