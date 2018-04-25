<?php
/*
* @version 0.1 (auto-set)
*/


  global $filter_name;

  if ($this->filter_name) {
   $out['FILTER_SET']=$this->filter_name;
  }

  if ($filter_name) {
   $this->filter_name=$filter_name;
  }


 $sections=array();
 $filters=array('', 'scenes', 'calendar', 'hook', 'backup');
 $total=count($filters);
 for($i=0;$i<$total;$i++) {
  $rec=array();
  $rec['FILTER']=$filters[$i];
  if ($rec['FILTER']==$this->filter_name) {
   $rec['SELECTED']=1;
  }
  if (defined('LANG_SETTINGS_SECTION_'.strtoupper($rec['FILTER']))) {
   $rec['TITLE']=constant('LANG_SETTINGS_SECTION_'.strtoupper($rec['FILTER']));
  } else {
   $rec['TITLE']=ucfirst($rec['FILTER']);
  }
  $sections[]=$rec;
  if ($filters[$i]) {
   $words[]=$filters[$i];
  }
 }
 $out['SECTIONS']=$sections;

 if ($this->filter_name=='' && !defined('SETTINGS_GENERAL_ALICE_NAME')) {
  $options=array(
   'GENERAL_ALICE_NAME'=>'Computer\'s name'
  );

  foreach($options as $k=>$v) {
   $tmp=SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '".$k."'");
   if (!$tmp['ID']) {
    $tmp=array();
    $tmp['NAME']=$k;
    $tmp['TITLE']=$v;
    $tmp['TYPE']='text';
    $tmp['DEFAULTVALUE']='';
    SQLInsert('settings', $tmp);
   }
  }
 }

 if ($this->filter_name=='hook' && !defined('SETTINGS_HOOK_BARCODE')) {
  //SETTINGS_HOOK_BEFORE_PLAYSOUND
  //SETTINGS_HOOK_AFTER_PLAYSOUND
  $options=array(
   'HOOK_BARCODE'=>'Bar-code reading (code)',
   'HOOK_PLAYMEDIA'=>'Playmedia (code)',
   'HOOK_BEFORE_PLAYSOUND'=>'Before PlaySound (code)',
   'HOOK_AFTER_PLAYSOUND'=>'After PlaySound (code)'
  );

  foreach($options as $k=>$v) {
   $tmp=SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '".$k."'");
   if (!$tmp['ID']) {
    $tmp=array();
    $tmp['NAME']=$k;
    $tmp['TITLE']=$v;
    $tmp['TYPE']='text';
    $tmp['DEFAULTVALUE']='';
    SQLInsert('settings', $tmp);
   }
  }
 }

 if ($this->filter_name=='scenes' && !defined('SETTINGS_SCENES_VERTICAL_NAV')) {
  $options=array(
   'SCENES_VERTICAL_NAV'=>'Vertical navigation'
  );
  foreach($options as $k=>$v) {
   $tmp=SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '".$k."'");
   if (!$tmp['ID']) {
    $tmp=array();
    $tmp['NAME']=$k;
    $tmp['TITLE']=$v;
    $tmp['TYPE']='onoff';
    $tmp['DEFAULTVALUE']='0';
    SQLInsert('settings', $tmp);
   }
  }

 }

 if ($this->filter_name=='scenes' && !defined('SETTINGS_SCENES_BACKGROUND_VIDEO')) {

  $options=array(
   'SCENES_BACKGROUND'=>'Path to background',
   'SCENES_BACKGROUND_VIDEO'=>'Path to video background',
   'SCENES_CLICKSOUND'=>'Path to click-sound file'
  );
  foreach($options as $k=>$v) {
   $tmp=SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '".$k."'");
   if (!$tmp['ID']) {
    $tmp=array();
    $tmp['NAME']=$k;
    $tmp['TITLE']=$v;
    $tmp['TYPE']='path';
    SQLInsert('settings', $tmp);
   }
  }

  $options=array(
   'SCENES_BACKGROUND_FIXED'=>'Backround Fixed',
   'SCENES_BACKGROUND_NOREPEAT'=>'Background No repeat'
  );

  foreach($options as $k=>$v) {
   $tmp=SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '".$k."'");
   if (!$tmp['ID']) {
    $tmp=array();
    $tmp['NAME']=$k;
    $tmp['TITLE']=$v;
    $tmp['TYPE']='onoff';
    $tmp['DEFAULTVALUE']='0';
    SQLInsert('settings', $tmp);
   }
  }



 }

 if ($this->filter_name=='backup' && !defined('SETTINGS_BACKUP_PATH')) {

  $options=array(
   'BACKUP_PATH'=>'Path to store backup'
  );
  foreach($options as $k=>$v) {
   $tmp=SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '".$k."'");
   if (!$tmp['ID']) {
    $tmp=array();
    $tmp['NAME']=$k;
    $tmp['TITLE']=$v;
    $tmp['TYPE']='text';
    SQLInsert('settings', $tmp);
   }
  }

 }

// if (!empty($options)) {
// }


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
   //$words=array('HP', 'PROFILE');
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

     if ($res[$i]['TYPE']=='json' && preg_match('/^hook/is',$res[$i]['NAME'])) {
      $data = json_decode($res[$i]['VALUE'], true);
      foreach($data as $k=>$v) {
       $data[$k]['priority']=gr($k.'_'.$res[$i]['ID'].'_priority','int');
      }
      ${'value_'.$res[$i]['ID']} = json_encode($data);
     }

     if (!isset(${'value_'.$res[$i]['ID']})) continue;
     $all_settings[$res[$i]['NAME']]=${'value_'.$res[$i]['ID']};
     $res[$i]['VALUE']=${'value_'.$res[$i]['ID']};
     $res[$i]['NOTES']=htmlspecialchars(${'notes_'.$res[$i]['ID']});
     SQLUpdate('settings', $res[$i]);
    }
    if ($this->mode=='reset') {
     $res[$i]['VALUE']=$res[$i]['DEFAULTVALUE'];
     SQLUpdate('settings', $res[$i]);
    }

    if ($res[$i]['TYPE']=='select') {
     $data=explode('|', $res[$i]['DATA']);
     foreach($data as $v) {
      list($ov, $ot)=explode('=', $v);
      $res[$i]['OPTIONS'][]=array('OPTION_TITLE'=>$ot, 'OPTION_VALUE'=>$ov);
     }
    } elseif ($res[$i]['TYPE']=='json' && preg_match('/^hook/is',$res[$i]['NAME'])) {
     $data=json_decode($res[$i]['VALUE'],true);
     foreach($data as $k=>$v) {
      $row=array('OPTION_TITLE'=>$k, 'FILTER'=>$v['filter'],'PRIORITY'=>(int)$v['priority']);
      $res[$i]['OPTIONS'][]=$row;
     }

     usort($res[$i]['OPTIONS'], function ($a,$b) {
      if ($a['PRIORITY'] == $b['PRIORITY']) {
       return 0;
      }
      return ($a['PRIORITY'] > $b['PRIORITY']) ? -1 : 1;
     });
    }
    if ($res[$i]['VALUE']==$res[$i]['DEFAULTVALUE']) {
     $res[$i]['ISDEFAULT']='1';
    }
    $res[$i]['VALUE']=htmlspecialchars($res[$i]['VALUE']);
    $res[$i]['HINT_NAME']='settings'.str_replace('_','',$res[$i]['NAME']);
   }
   $out['RESULT']=$res;
  }


  
    // some action for every record if required
  if ($this->mode=='update') {
   /*
   if ($all_settings['GROWL_ENABLE']) {
    include_once(ROOT.'lib/growl/growl.gntp.php');
    $growl = new Growl($all_settings['GROWL_HOST'], $all_settings['GROWL_PASSWORD']);
    $growl->setApplication('MajorDoMo','Notifications');
    $growl->registerApplication('http://connect.smartliving.ru/img/logo.png');
    $growl->notify('Test!');
   }
   */
   $this->redirect("?updated=1&filter_name=".$this->filter_name);
  }

?>