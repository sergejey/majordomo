<?
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
   $qry=$session->data['readit_channels_qry'];
  } else {
   $session->data['readit_channels_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_readit_channels;
  if (!$sortby_readit_channels) {
   $sortby_readit_channels=$session->data['readit_channels_sort'];
  } else {
   if ($session->data['readit_channels_sort']==$sortby_readit_channels) {
    if (Is_Integer(strpos($sortby_readit_channels, ' DESC'))) {
     $sortby_readit_channels=str_replace(' DESC', '', $sortby_readit_channels);
    } else {
     $sortby_readit_channels=$sortby_readit_channels." DESC";
    }
   }
   $session->data['readit_channels_sort']=$sortby_readit_channels;
  }
  if (!$sortby_readit_channels) $sortby_readit_channels="ID DESC";
  $out['SORTBY']=$sortby_readit_channels;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM readit_channels WHERE $qry ORDER BY ".$sortby_readit_channels);
  if ($res[0]['ID']) {
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
   }
   $out['RESULT']=$res;
  }
?>