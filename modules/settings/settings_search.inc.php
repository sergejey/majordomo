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
  
  // search filters
  if ($this->filter_name!='') {
   $qry.=" AND NAME LIKE '%".DBSafe($this->filter_name)."%'";
   $out['FILTER_NAME']=$this->filter_name;
  }

  if ($this->filter_exname!='') {
   $qry.=" AND NAME NOT LIKE '%".DBSafe($this->filter_exname)."%'";
   $out['FILTER_EXNAME']=$this->filter_exname;
  }

  if (!$this->filter_name) {
   $words=array('HP', 'PROFILE');
   foreach($words as $wrd) {
    $qry.=" AND NAME NOT LIKE '%".DBSafe($wrd)."%'";
   }
  }


  if ($this->section_title!='') {
   $out['SECTION_TITLE']=$this->section_title;
  }
  // QUERY READY
  
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['settings_qry'];
  } else {
   $session->data['settings_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  
  // FIELDS ORDER
  global $sortby;
  if (!$sortby) {
   $sortby=$session->data['settings_sort'];
  } else {
   if ($session->data['settings_sort']==$sortby) {
    if (Is_Integer(strpos($sortby, ' DESC'))) {
     $sortby=str_replace(' DESC', '', $sortby);
    } else {
     $sortby=$sortby." DESC";
    }
   }
   $session->data['settings_sort']=$sortby;
  }

  $sortby="PRIORITY DESC, NAME";

  $out['SORTBY']=$sortby;
  // SEARCH RESULTS
  
  // SEARCH RESULTS
  $res=SQLSelect("SELECT * FROM settings WHERE $qry ORDER BY $sortby");
  if ($res[0]['ID']) {
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
    
    // some action for every record if required
    if ($this->mode=='update') {
     global ${'value_'.$res[$i]['ID']};
     global ${'notes_'.$res[$i]['ID']};
     $res[$i]['VALUE']=${'value_'.$res[$i]['ID']};
     $res[$i]['NOTES']=htmlspecialchars(${'notes_'.$res[$i]['ID']});
     SQLUpdate('settings', $res[$i]);
    }
    if ($this->mode=='reset') {
     $res[$i]['VALUE']=$res[$i]['DEFAULTVALUE'];
     SQLUpdate('settings', $res[$i]);
    }
    if ($res[$i]['VALUE']==$res[$i]['DEFAULTVALUE']) {
     $res[$i]['ISDEFAULT']='1';
    }
    $res[$i]['VALUE']=htmlspecialchars($res[$i]['VALUE']);
   }
   $out['RESULT']=$res;
  }

  
    // some action for every record if required
    if ($this->mode=='update') {
   $this->redirect("?updated=1");
  }

?>