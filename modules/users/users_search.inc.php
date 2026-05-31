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
  if (!$qry) $qry="1";
  // FIELDS ORDER

  $sortby="NAME";


  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM users WHERE $qry ORDER BY $sortby");
  if ($res[0]['ID']) {
   paging($res, 50, $out); // search result paging
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
       if ($res[$i]['PASSWORD']!='' && hash('sha512','')==$res[$i]['PASSWORD']) {
           $res[$i]['PASSWORD'] = '';
       }
   }
   $out['RESULT']=$res;
  }
