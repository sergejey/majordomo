<?php
/*
* @version 0.2 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  //searching 'URL' (url)
  global $url;
  if ($url!='') {
   $qry.=" AND URL LIKE '%".DBSafe($url)."%'";
   $out['URL']=$url;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['rss_items_qry'];
  } else {
   $session->data['rss_items_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby;
  if (!$sortby) {
   $sortby=$session->data['rss_items_sort'];
  } else {
   if ($session->data['rss_items_sort']==$sortby) {
    if (Is_Integer(strpos($sortby, ' DESC'))) {
     $sortby=str_replace(' DESC', '', $sortby);
    } else {
     $sortby=$sortby." DESC";
    }
   }
   $session->data['rss_items_sort']=$sortby;
  }
  if (!$sortby) $sortby="ADDED DESC";
  $out['SORTBY']=$sortby;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT ID FROM rss_items WHERE $qry ORDER BY $sortby LIMIT 1000");
  if ($res[0]['ID']) {
   paging($res, 50, $out); // search result paging
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
    $res[$i]=SQLSelectOne("SELECT * FROM rss_items WHERE ID='".$res[$i]['ID']."'");
   }
   $out['RESULT']=$res;
  }
?>