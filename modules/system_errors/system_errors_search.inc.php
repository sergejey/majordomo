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
   $qry=$session->data['system_errors_qry'];
  } else {
   $session->data['system_errors_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_system_errors;
  if (!$sortby_system_errors) {
   $sortby_system_errors=$session->data['system_errors_sort'];
  } else {
   if ($session->data['system_errors_sort']==$sortby_system_errors) {
    if (Is_Integer(strpos($sortby_system_errors, ' DESC'))) {
     $sortby_system_errors=str_replace(' DESC', '', $sortby_system_errors);
    } else {
     $sortby_system_errors=$sortby_system_errors." DESC";
    }
   }
   $session->data['system_errors_sort']=$sortby_system_errors;
  }
  $sortby_system_errors="ACTIVE DESC, LATEST_UPDATE DESC, CODE";
  $out['SORTBY']=$sortby_system_errors;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM system_errors WHERE $qry ORDER BY ".$sortby_system_errors);
  $errors_found=0;
  if ($res[0]['ID']) {
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
    $tmp=explode(' ', $res[$i]['LATEST_UPDATE']);
    $res[$i]['LATEST_UPDATE']=fromDBDate($tmp[0])." ".$tmp[1];
    $errors_found+=$res[$i]['ACTIVE'];
   }
   $out['RESULT']=$res;
  }

 if ($this->ajax && $_GET['op']=='check') {
  if ($errors_found>0) {
   echo $errors_found;
  } else {
   echo "0";
  }
  exit;
 }

