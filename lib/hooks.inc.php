<?php


function subscribeToEvent($module_name, $event_name, $filter_details='') {
 $rec=SQLSelectOne("SELECT * FROM settings WHERE NAME='HOOK_EVENT_".DBSafe(strtoupper($event_name))."'");
 if (!$rec['ID']) {
  $rec=array();
  $rec['NAME']='HOOK_EVENT_'.strtoupper($event_name);
  $rec['TITLE']=$rec['NAME'];
  $rec['TYPE']='json';
  $rec['PRIORITY']=0;
  $rec['ID']=SQLInsert('settings', $rec);
 }

 $data=json_decode($rec['VALUE'], true);
 if (!isset($data[$module_name])) {
  $data[$module_name]=1;
  $rec['VALUE']=json_encode($data);
  SQLUpdate('settings', $rec);
 }

}

function unsubscribeFromEvent($module_name, $event_name='') {
 $rec=SQLSelectOne("SELECT * FROM settings WHERE NAME LIKE 'HOOK_EVENT_'".strtoupper($event_name)." AND TYPE='json'");  
 if (IsSet($rec['ID'])) {
  $data=json_decode($rec['VALUE'], true);
  if (isset($data[$module_name])) {
   unset($data[$module_name]);
   $rec['VALUE']=json_encode($data);
   SQLUpdate('settings', $rec);
  }
 }
}

function processSubscriptions($event_name, $details='') {
 if (!defined('SETTINGS_HOOK_EVENT_'.strtoupper($event_name))) {
  return 0;
 }
 $data=json_decode(constant('SETTINGS_HOOK_EVENT_'.strtoupper($event_name)), true);
 if (is_array($data)) {
  foreach($data as $k=>$v) {
   $module_name=$k;
   $filter_details=$v;
   if (file_exists(DIR_MODULES.$module_name.'/'.$module_name.'.class.php')) {
    include_once(DIR_MODULES.$module_name.'/'.$module_name.'.class.php');
    $module_object=new $module_name();
    if (method_exists($module_object, 'processSubscription')) {
     $module_object->processSubscription($event_name, $details);
    }
   }
  }
 }

}

function removeMissingSubscribers() {
  $settings=SQLSelect("SELECT * FROM settings WHERE NAME LIKE 'HOOK_EVENT_%' AND TYPE='json'");
  $total=count($settings);
  for($i=0;$i<$total;$i++) {
   $data=json_decode($settings[$i]['VALUE'], true);
   $changed=0;
   if (is_array($data)) {
    foreach($data as $k=>$v) {
     $module_name=$k;
     if (!file_exists(DIR_MODULES.'modules/'.$module_name.'/'.$module_name.'.class.php')) {
      unset($data[$module_name]);
      $changed=1;
     }
    }

    if ($changed) {
     $settings[$i]['VALUE']=json_encode($data);
     SQLUpdate('settings', $settings[$i]);
    }
  }
 }
}
