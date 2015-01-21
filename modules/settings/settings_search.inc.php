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
 $filters=array('', 'scenes', 'calendar', 'growl', 'twitter', 'pushover', 'pushbullet', 'hook', 'backup', 'logger');
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

 if ($this->filter_name=='hook' && !defined('SETTINGS_HOOK_AFTER_PLAYSOUND')) {
  //SETTINGS_HOOK_BEFORE_PLAYSOUND
  //SETTINGS_HOOK_AFTER_PLAYSOUND
  $options=array(
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

 if ($this->filter_name=='logger' && !defined('SETTINGS_LOGGER_DESTINATION')) {

  $options=array(
   'LOGGER_DESTINATION'=>'Write log to (file/database/both)'
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
  $query = "CREATE TABLE IF NOT EXISTS `log4php_log` (`timestamp` DATETIME, `logger` VARCHAR(256), `level` VARCHAR(32), `message` VARCHAR(4000), `thread` INTEGER, `file` VARCHAR(255), `line` VARCHAR(10));";
  SQLExec($query);

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

 if ($this->filter_name=='pushbullet' && !defined('SETTINGS_PUSHBULLET_PREFIX')) {
  $options=array(
   'PUSHBULLET_KEY'=>'Pushbullet API Key', 
   'PUSHBULLET_LEVEL'=>'Pushbullet message minimum level', 
   'PUSHBULLET_DEVICE_ID'=>'Pushbullet Device ID (optional)',
   'PUSHBULLET_PREFIX'=>'Pushbullet notifiaction prefix (optional)'
  );
  foreach($options as $k=>$v) {
   $tmp=SQLSelectOne("SELECT ID FROM settings WHERE NAME LIKE '".$k."'");
   if (!$tmp['ID']) {
    $tmp=array();
    $tmp['NAME']=$k;
    $tmp['TITLE']=$v;
    $tmp['TYPE']='text';
    if ($k=='PUSHBULLET_LEVEL') {
     $tmp['VALUE']=1;
     $tmp['DEFAULTVALUE']=1;
    }
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
     $all_settings[$res[$i]['NAME']]=${'value_'.$res[$i]['ID']};
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
   if ($all_settings['GROWL_ENABLE']) {
    include_once(ROOT.'lib/growl/growl.gntp.php');
    $growl = new Growl($all_settings['GROWL_HOST'], $all_settings['GROWL_PASSWORD']);
    $growl->setApplication('MajorDoMo','Notifications');
    $growl->registerApplication('http://connect.smartliving.ru/img/logo.png');
    $growl->notify('Test!');
   }
   $this->redirect("?updated=1&filter_name=".$this->filter_name);
  }

?>