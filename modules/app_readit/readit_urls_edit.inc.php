<?
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='readit_urls';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'URL' (url)
   global $url;
   $rec['URL']=$url;
  //updating 'TITLE' (varchar, required)
   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
  //updating 'FAVORITE' (int)
   global $favorite;
   $rec['FAVORITE']=(int)$favorite;
  //updating 'ADDED' (datetime)
   global $added_date;
   global $added_minutes;
   global $added_hours;
   $rec['ADDED']=toDBDate($added_date)." $added_hours:$added_minutes:00";
  //updating 'SYS_ID' (varchar)
   if (IsSet($this->sys_id)) {
    $rec['SYS_ID']=$this->sys_id;
   } else {
   global $sys_id;
   $rec['SYS_ID']=$sys_id;
   }
  //updating 'CHANNEL_ID' (select)
   if (IsSet($this->channel_id)) {
    $rec['CHANNEL_ID']=$this->channel_id;
   } else {
   global $channel_id;
   $rec['CHANNEL_ID']=$channel_id;
   }
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }
  if ($rec['ADDED']!='') {
   $tmp=explode(' ', $rec['ADDED']);
   $out['ADDED_DATE']=fromDBDate($tmp[0]);
   $tmp2=explode(':', $tmp[1]);
   $added_hours=$tmp2[0];
   $added_minutes=$tmp2[1];
  }
  for($i=0;$i<60;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$added_minutes) {
    $out['ADDED_MINUTES'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['ADDED_MINUTES'][]=array('TITLE'=>$title);
   }
  }
  for($i=0;$i<24;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$added_hours) {
    $out['ADDED_HOURS'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['ADDED_HOURS'][]=array('TITLE'=>$title);
   }
  }
  //options for 'CHANNEL_ID' (select)
  $tmp=SQLSelect("SELECT ID, TITLE FROM readit_channels ORDER BY TITLE");
  $readit_channels_total=count($tmp);
  for($readit_channels_i=0;$readit_channels_i<$readit_channels_total;$readit_channels_i++) {
   $channel_id_opt[$tmp[$readit_channels_i]['ID']]=$tmp[$readit_channels_i]['TITLE'];
  }
  for($i=0;$i<count($tmp);$i++) {
   if ($rec['CHANNEL_ID']==$tmp[$i]['ID']) $tmp[$i]['SELECTED']=1;
  }
  $out['CHANNEL_ID_OPTIONS']=$tmp;
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
?>