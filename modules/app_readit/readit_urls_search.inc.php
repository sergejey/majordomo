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
  //searching 'URL' (url)
  global $url;
  if ($url!='') {
   $qry.=" AND URL LIKE '%".DBSafe($url)."%'";
   $out['URL']=$url;
  }
  //searching 'TITLE' (varchar)
  global $title;
  if ($title!='') {
   $qry.=" AND TITLE LIKE '%".DBSafe($title)."%'";
   $out['TITLE']=$title;
  }
  if (IsSet($this->sys_id)) {
   $sys_id=$this->sys_id;
   $qry.=" AND SYS_ID='".$this->sys_id."'";
  } else {
   global $sys_id;
  }
  if (IsSet($this->channel_id)) {
   $channel_id=$this->channel_id;
   $qry.=" AND CHANNEL_ID='".$this->channel_id."'";
  } else {
   global $channel_id;
  }
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['readit_urls_qry'];
  } else {
   $session->data['readit_urls_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_readit_urls;
  if (!$sortby_readit_urls) {
   $sortby_readit_urls=$session->data['readit_urls_sort'];
  } else {
   if ($session->data['readit_urls_sort']==$sortby_readit_urls) {
    if (Is_Integer(strpos($sortby_readit_urls, ' DESC'))) {
     $sortby_readit_urls=str_replace(' DESC', '', $sortby_readit_urls);
    } else {
     $sortby_readit_urls=$sortby_readit_urls." DESC";
    }
   }
   $session->data['readit_urls_sort']=$sortby_readit_urls;
  }
  if (!$sortby_readit_urls) $sortby_readit_urls="ID DESC";
  $out['SORTBY']=$sortby_readit_urls;
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM readit_urls WHERE $qry ORDER BY ".$sortby_readit_urls);
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