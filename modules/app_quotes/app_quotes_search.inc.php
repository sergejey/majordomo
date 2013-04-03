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
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['app_quotes_qry'];
  } else {
   $session->data['app_quotes_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_app_quotes;
  if (!$sortby_app_quotes) {
   $sortby_app_quotes=$session->data['app_quotes_sort'];
  } else {
   if ($session->data['app_quotes_sort']==$sortby_app_quotes) {
    if (Is_Integer(strpos($sortby_app_quotes, ' DESC'))) {
     $sortby_app_quotes=str_replace(' DESC', '', $sortby_app_quotes);
    } else {
     $sortby_app_quotes=$sortby_app_quotes." DESC";
    }
   }
   $session->data['app_quotes_sort']=$sortby_app_quotes;
  }
  if (!$sortby_app_quotes) $sortby_app_quotes="ID DESC";
  $out['SORTBY']=$sortby_app_quotes;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM app_quotes WHERE $qry ORDER BY ".$sortby_app_quotes);
  if ($res[0]['ID']) {
   paging($res, 50, $out); // search result paging
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
    $res[$i]['BODY']=htmlspecialchars($res[$i]['BODY']);
   }
   $out['RESULT']=$res;
  }
?>