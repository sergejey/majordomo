<?php
/**
* Calendar 
*
* App_calendar
*
* @package project
* @author Serge J. <jey@tut.by>
* @copyright http://www.atmatic.eu/ (c)
* @version 0.1 (wizard, 17:05:45 [May 07, 2012])
*/
Define('DEF_REPEAT_TYPE_OPTIONS', '1=Yearly|2=Monthly|3=Weekly|4=Daily'); // options for 'REPEAT_TYPE'
//
//
class app_calendar extends module {
/**
* app_calendar
*
* Module class constructor
*
* @access private
*/
function app_calendar() {
  $this->name="app_calendar";
  $this->title="<#LANG_APP_CALENDAR#>";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $data_source;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($data_source)) {
   $this->data_source=$data_source;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }

  $this->checkSettings();

  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}

/**
* Title
*
* Description
*
* @access public
*/
 function checkSettings() {
  $settings=array(
   array(
    'NAME'=>'APP_CALENDAR_SOONLIMIT', 
    'TITLE'=>'Days to show in "soon" section', 
    'TYPE'=>'text',
    'DEFAULT'=>'6'
    ),
   array(
    'NAME'=>'APP_CALENDAR_SHOWDONE', 
    'TITLE'=>'Show recently done items',
    'TYPE'=>'yesno',
    'DEFAULT'=>'1'
    )
   );


   foreach($settings as $k=>$v) {
    $rec=SQLSelectOne("SELECT ID FROM settings WHERE NAME='".$v['NAME']."'");
    if (!$rec['ID']) {
     $rec['NAME']=$v['NAME'];
     $rec['VALUE']=$v['DEFAULT'];
     $rec['DEFAULTVALUE']=$v['DEFAULT'];
     $rec['TITLE']=$v['TITLE'];
     $rec['TYPE']=$v['TYPE'];
     $rec['ID']=SQLInsert('settings', $rec);
     Define('SETTINGS_'.$rec['NAME'], $v['DEFAULT']);
    }
   }

 }

/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {

 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='calendar_events' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_calendar_events') {
   $this->search_calendar_events($out);
  }
  if ($this->view_mode=='edit_calendar_events') {
   $this->edit_calendar_events($out, $this->id);
  }
  if ($this->view_mode=='delete_calendar_events') {
   $this->delete_calendar_events($this->id);
   $this->redirect("?data_source=calendar_events");
  }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='calendar_categories') {
  if ($this->view_mode=='' || $this->view_mode=='search_calendar_categories') {
   $this->search_calendar_categories($out);
  }
  if ($this->view_mode=='edit_calendar_categories') {
   $this->edit_calendar_categories($out, $this->id);
  }
  if ($this->view_mode=='delete_calendar_categories') {
   $this->delete_calendar_categories($this->id);
   $this->redirect("?data_source=calendar_categories");
  }
 }
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 if ($this->view_mode=='edit') {
  $this->usual_edit($out);
 }

 if ($this->view_mode=='') {
  

  if ($this->mode=='is_done') {
   global $id;
   $this->task_done($id);
   $this->redirect("?");
  }

  if ($this->mode=='reset_done') {
   global $id;

   $rec=SQLSelectOne("SELECT * FROM calendar_events WHERE ID='".(int)$id."'");
   $rec['IS_DONE']=0;
   SQLUpdate('calendar_events', $rec);

   $this->redirect("?");
  }


  $events_today=SQLSelect("SELECT * FROM calendar_events WHERE TO_DAYS(DUE)=TO_DAYS(NOW()) AND IS_REPEATING!=1 AND IS_TASK=0 ORDER BY IS_TASK DESC");
  $tasks_today=SQLSelect("SELECT * FROM calendar_events WHERE TO_DAYS(DUE)=TO_DAYS(NOW()) AND IS_DONE=0 AND IS_TASK=1 ORDER BY IS_TASK DESC");
  if ($tasks_today) {
   foreach($tasks_today as $k=>$v) {
    $events_today[]=$v;
   }
  }


  $events_early_today=SQLSelect("SELECT * FROM calendar_events WHERE TO_DAYS(DATE(CONCAT_WS('-', DATE_FORMAT(NOW(), '%Y'), DATE_FORMAT(DUE, '%m'), DATE_FORMAT(DUE, '%d'))))=TO_DAYS(NOW()) AND IS_REPEATING=1 AND REPEAT_TYPE=1 ORDER BY IS_TASK DESC");
  if ($events_early_today) {
   foreach($events_early_today as $k=>$v) {
    $events_today[]=$v;
   }
  }
  $events_monthly_today=SQLSelect("SELECT * FROM calendar_events WHERE TO_DAYS(DATE(CONCAT_WS('-', DATE_FORMAT(NOW(), '%Y'), DATE_FORMAT(NOW(), '%m'), DATE_FORMAT(DUE, '%d'))))=TO_DAYS(NOW()) AND IS_REPEATING=1 AND REPEAT_TYPE=2 ORDER BY IS_TASK DESC");
  if ($events_monthly_today) {
   foreach($events_monthly_today as $k=>$v) {
    $events_today[]=$v;
   }
  }
  $events_weekly_today=SQLSelect("SELECT * FROM calendar_events WHERE DATE_FORMAT(DUE, '%w')=DATE_FORMAT(NOW(), '%w') AND IS_REPEATING=1 AND REPEAT_TYPE=3 ORDER BY IS_TASK DESC");
  if ($events_weekly_today) {
   foreach($events_weekly_today as $k=>$v) {
    $events_today[]=$v;
   }
  }

  if ($events_today) {
   $out['EVENTS_TODAY']=$events_today;
  }

  $events_past=SQLSelect("SELECT *, (TO_DAYS(DUE)-TO_DAYS(NOW())) as AGE FROM calendar_events WHERE TO_DAYS(DUE)<TO_DAYS(NOW()) AND IS_TASK=1 AND IS_DONE=0 ORDER BY IS_TASK DESC, AGE");
  if ($events_past) {
   $out['EVENTS_PAST']=$events_past;
  }

  $how_soon=SETTINGS_APP_CALENDAR_SOONLIMIT;
  $events_soon=SQLSelect("SELECT *, (TO_DAYS(DUE)-TO_DAYS(NOW())) as AGE FROM calendar_events WHERE IS_TASK=0 AND (TO_DAYS(DUE)>TO_DAYS(NOW()) AND (TO_DAYS(DUE)-TO_DAYS(NOW())<=".(int)$how_soon.")) ORDER BY AGE");

  $tasks_soon=SQLSelect("SELECT *, (TO_DAYS(DUE)-TO_DAYS(NOW())) as AGE FROM calendar_events WHERE IS_TASK=1 AND IS_DONE=0 AND (TO_DAYS(DUE)>TO_DAYS(NOW()) OR (IS_NODATE=1)) ORDER BY AGE");
  if ($tasks_soon) {
   foreach($tasks_soon as $k=>$v) {
    $events_soon[]=$v;
   }
  }

  $events_early_soon=SQLSelect("SELECT *, TO_DAYS(DATE(CONCAT_WS('-', DATE_FORMAT(NOW(), '%Y'), DATE_FORMAT(DUE, '%m'), DATE_FORMAT(DUE, '%d'))))-TO_DAYS(NOW()) as AGE FROM calendar_events WHERE (TO_DAYS(DATE(CONCAT_WS('-', DATE_FORMAT(NOW(), '%Y'), DATE_FORMAT(DUE, '%m'), DATE_FORMAT(DUE, '%d'))))>TO_DAYS(NOW())) AND (TO_DAYS(DATE(CONCAT_WS('-', DATE_FORMAT(NOW(), '%Y'), DATE_FORMAT(DUE, '%m'), DATE_FORMAT(DUE, '%d'))))-TO_DAYS(NOW())<=".(int)$how_soon.") AND IS_REPEATING=1 AND REPEAT_TYPE=1 AND IS_TASK=0 ORDER BY DUE");
  if ($events_early_soon) {
   foreach($events_early_soon as $k=>$v) {
    $events_soon[]=$v;
   }
  }

  $events_monthly_soon=SQLSelect("SELECT * FROM calendar_events WHERE (TO_DAYS(DATE(CONCAT_WS('-', DATE_FORMAT(NOW(), '%Y'), DATE_FORMAT(NOW(), '%m'), DATE_FORMAT(DUE, '%d'))))>TO_DAYS(NOW())) AND (TO_DAYS(DATE(CONCAT_WS('-', DATE_FORMAT(NOW(), '%Y'), DATE_FORMAT(DUE, '%m'), DATE_FORMAT(DUE, '%d'))))-TO_DAYS(NOW())<=".(int)$how_soon.") AND IS_REPEATING=1 AND REPEAT_TYPE=2 AND IS_TASK=0 ORDER BY DUE");
  if ($events_monthly_soon) {
   foreach($events_monthly_soon as $k=>$v) {
    $events_soon[]=$v;
   }
  }

  if ($events_soon) {
   $new_events=array();
   foreach($events_soon as $ev) {
    if (!$seen[$ev['ID']]) {
     $new_events[]=$ev;
    }
    $seen[$ev['ID']]=1;
   }
   $out['EVENTS_SOON']=$new_events;
  }


  if (SETTINGS_APP_CALENDAR_SHOWDONE=='1') {
   $recently_done=SQLSelect("SELECT * FROM calendar_events WHERE IS_TASK=1 AND (IS_DONE=1 OR IS_REPEATING=1) AND TO_DAYS(NOW())-TO_DAYS(DONE_WHEN)<=1");
   if ($recently_done) {
   $out['RECENTLY_DONE']=$recently_done;
   }
  }


 }

}

/**
* Title
*
* Description
*
* @access public
*/
 function usual_edit(&$out) {

  global $title;
  global $id;

  if ($id) {
   $rec=SQLSelectOne("SELECT * FROM calendar_events WHERE ID='".(int)$id."'");

   if ($this->mode=='delete') {
    SQLExec("DELETE FROM calendar_events WHERE ID='".(int)$rec['ID']."'");
    $this->redirect("?");
   }

  } else {
   $out['TITLE']=$title;
   $out['DUE']=date('Y-m-d');
   if ($out['TITLE']) {
    $others=SQLSelect("SELECT ID, TITLE, IS_DONE FROM calendar_events WHERE TITLE LIKE '%".DBSafe($out['TITLE'])."%' ORDER BY ID DESC");
    if ($others) {
     $out['OTHERS']=$others;
    }
   }
  }

  if ($this->mode=='update') {
   $ok=1;

   global $is_task;
   global $notes;

   $rec['TITLE']=$title;

   if (!$rec['TITLE']) {
    $ok=0;
    $out['ERR_TITLE']=1;
   }

   $rec['IS_TASK']=(int)$is_task;
   $rec['NOTES']=$notes;

   global $due;
   $rec['DUE']=$due;
   if (!$rec['DUE']) {
    $rec['DUE']=date('Y-m-d');
   }

   global $is_repeating;
   $rec['IS_REPEATING']=(int)$is_repeating;

   global $is_repeating_after;
   $rec['IS_REPEATING_AFTER']=(int)$is_repeating_after;

   global $repeat_in;
   $rec['REPEAT_IN']=(int)$repeat_in;

   global $repeat_type;
   $rec['REPEAT_TYPE']=(int)$repeat_type;
   

   global $is_done;
   if ($is_done && !$rec['IS_DONE']) {
    $marked_done=1;
   }
   $rec['IS_DONE']=(int)$is_done;


   global $is_nodate;
   $rec['IS_NODATE']=(int)$rec['IS_NODATE'];

   global $user_id;
   $rec['USER_ID']=(int)$user_id;

   global $location_id;
   $rec['LOCATION_ID']=(int)$location_id;

   global $done_script_id;
   $rec['DONE_SCRIPT_ID']=(int)$done_script_id;

   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate('calendar_events', $rec);
    } else {
     $rec['ADDED']=date('Y-m-d H:i:s');
     $rec['ID']=SQLInsert('calendar_events', $rec);
    }
    if ($marked_done) {
     $this->task_done($rec['ID']);
    }

    $this->redirect("?");
   }


  }

  outHash($rec, $out);
  $out['USERS']=SQLSelect("SELECT * FROM users ORDER BY NAME");
  $out['LOCATIONS']=SQLSelect("SELECT * FROM gpslocations ORDER BY TITLE");
  $out['SCRIPTS']=SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");

 }

/**
* Title
*
* Description
*
* @access public
*/
 function task_done($id) {
  //DebMes("Task $id is DONE! Congratulations!!!");
  $rec=SQLSelectOne("SELECT * FROM calendar_events WHERE ID='".(int)$id."'");
  $rec['DONE_WHEN']=date('Y-m-d H:i:s');
  $rec['IS_DONE']=1;

  $tmp=explode('-', $rec['DUE']);
  $due_time=mktime(1, 1, 1, $tmp[1], $tmp[2], $tmp[0]);

  if ($rec['IS_REPEATING']) {
   $rec['IS_DONE']=0;
   if ($rec['REPEAT_TYPE']==1) {
    // yearly task
    $due_time_next_year=mktime(1, 1, 1, $tmp[1], $tmp[2], $tmp[0]+1);
    $rec['DUE']=date('Y-m-d', $due_time_next_year);
   } elseif ($rec['REPEAT_TYPE']==2) {
    // monthly task
    $time_next_month=$due_time+31*24*60*60;
    $due_time_next_month=mktime(1, 1, 1, date('m', $time_next_month), $tmp[2], date('Y', $time_next_month));

   } elseif ($rec['REPEAT_TYPE']==3) {
    // weekly task
    $due_time_next_week=$due_time+7*24*60*60;
    $rec['DUE']=date('Y-m-d', $due_time_next_week);
   } elseif ($rec['REPEAT_TYPE']==9) {
    // custom repeat task
    if ($rec['IS_REPEATING_AFTER']) {
     $rec['DUE']=date('Y-m-d', time()+$rec['REPEAT_IN']*24*60*60);
    } else {
     $rec['DUE']=date('Y-m-d', $due_time+$rec['REPEAT_IN']*24*60*60);
    }
   }
  }

  $rec['LOG']=date('Y-m-d H:i:s').' Task marked DONE'."\n".$rec['LOG'];

  SQLUpdate('calendar_events', $rec);

  if ($rec['DONE_SCRIPT_ID']) {
   runScript($rec['DONE_SCRIPT_ID'], $rec);
  }

 }

/**
* calendar_events search
*
* @access public
*/
 function search_calendar_events(&$out) {
  require(DIR_MODULES.$this->name.'/calendar_events_search.inc.php');
 }
/**
* calendar_events edit/add
*
* @access public
*/
 function edit_calendar_events(&$out, $id) {
  require(DIR_MODULES.$this->name.'/calendar_events_edit.inc.php');
 }
/**
* calendar_events delete record
*
* @access public
*/
 function delete_calendar_events($id) {
  $rec=SQLSelectOne("SELECT * FROM calendar_events WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM calendar_events WHERE ID='".$rec['ID']."'");
 }
/**
* calendar_categories search
*
* @access public
*/
 function search_calendar_categories(&$out) {
  require(DIR_MODULES.$this->name.'/calendar_categories_search.inc.php');
 }
/**
* calendar_categories edit/add
*
* @access public
*/
 function edit_calendar_categories(&$out, $id) {
  require(DIR_MODULES.$this->name.'/calendar_categories_edit.inc.php');
 }
/**
* calendar_categories delete record
*
* @access public
*/
 function delete_calendar_categories($id) {
  $rec=SQLSelectOne("SELECT * FROM calendar_categories WHERE ID='$id'");
  // some action for related tables
  @unlink(ROOT.'./cms/calendar/'.$rec['ICON']);
  SQLExec("DELETE FROM calendar_categories WHERE ID='".$rec['ID']."'");
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
 @umask(0);
  if (!Is_Dir(ROOT."./cms/calendar")) {
   mkdir(ROOT."./cms/calendar", 0777);
  }
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS calendar_events');
  SQLExec('DROP TABLE IF EXISTS calendar_categories');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
calendar_events - Events
calendar_categories - Categories
*/
  $data = <<<EOD
 calendar_events: ID int(10) unsigned NOT NULL auto_increment
 calendar_events: TITLE varchar(255) NOT NULL DEFAULT ''
 calendar_events: SYSTEM varchar(255) NOT NULL DEFAULT ''
 calendar_events: NOTES text
 calendar_events: DUE date
 calendar_events: ADDED datetime
 calendar_events: DONE_WHEN datetime
 calendar_events: IS_TASK int(3) NOT NULL DEFAULT '0'
 calendar_events: IS_DONE int(3) NOT NULL DEFAULT '0'
 calendar_events: IS_NODATE int(3) NOT NULL DEFAULT '0'
 calendar_events: IS_REPEATING int(3) NOT NULL DEFAULT '0'
 calendar_events: REPEAT_TYPE int(3) NOT NULL DEFAULT '0'
 calendar_events: WEEK_DAYS varchar(255) NOT NULL DEFAULT ''
 calendar_events: IS_REPEATING_AFTER int(3) NOT NULL DEFAULT '0'
 calendar_events: REPEAT_IN int(10) NOT NULL DEFAULT '0'
 calendar_events: USER_ID int(10) NOT NULL DEFAULT '0'
 calendar_events: LOCATION_ID int(10) NOT NULL DEFAULT '0'
 calendar_events: CALENDAR_CATEGORY_ID int(10) NOT NULL DEFAULT '0'
 calendar_events: DONE_SCRIPT_ID int(10) NOT NULL DEFAULT '0'
 calendar_events: DONE_CODE text
 calendar_events: LOG text

 calendar_categories: ID int(10) unsigned NOT NULL auto_increment
 calendar_categories: TITLE varchar(255) NOT NULL DEFAULT ''
 calendar_categories: ACTIVE int(255) NOT NULL DEFAULT '0'
 calendar_categories: PRIORITY int(10) NOT NULL DEFAULT '0'
 calendar_categories: ICON varchar(70) NOT NULL DEFAULT ''
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTWF5IDA3LCAyMDEyIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>