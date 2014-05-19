<?php
/**
* Objects 
*
* Objects
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.4 (wizard, 12:05:51 [May 22, 2009])
*/
//
//
class objects extends module {
/**
* objects
*
* Module class constructor
*
* @access private
*/
function objects() {
  $this->name="objects";
  $this->title="<#LANG_MODULE_OBJECT_INSTANCES#>";
  $this->module_category="<#LANG_SECTION_OBJECTS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=1) {
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
  $out['TAB']=$this->tab;
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
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
 if ($this->data_source=='objects' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_objects') {
   $this->search_objects($out);
  }
  if ($this->view_mode=='edit_objects') {
   $this->edit_objects($out, $this->id);
  }
  if ($this->view_mode=='delete_objects') {
   $this->delete_objects($this->id);
   $this->redirect("?");
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

 if ($this->class) {
  $objects=getObjectsByClass($this->class);
  if (!$this->code) {
   $template='#title# <i>#description#</i><br/>';
  } else {
   $template=$this->code;
  }
  $result='';
  if ($objects[0]['ID']) {
   $total=count($objects);
   for($i=0;$i<$total;$i++) {
    $objects[$i]=SQLSelectOne("SELECT * FROM objects WHERE ID='".$objects[$i]['ID']."'");
    $line=$template;
    $line=preg_replace('/\#title\#/is', $objects[$i]['TITLE'], $line);
    $line=preg_replace('/\#description\#/is', $objects[$i]['DESCRIPTION'], $line);
    if (preg_match_all('/\#([\w\d_-]+?)\#/is', $line, $m)) {
     $totalm=count($m[0]);
     for($im=0;$im<$totalm;$im++) {
      $property=trim($objects[$i]['TITLE'].'.'.$m[1][$im]);
      $line=str_replace($m[0][$im], getGlobal($property), $line);
     }
    }
    $result.=$line;
   }
  }
  $out['RESULT']=$result;
 }

}
/**
* objects search
*
* @access public
*/
 function search_objects(&$out) {
  require(DIR_MODULES.$this->name.'/objects_search.inc.php');
 }
/**
* objects edit/add
*
* @access public
*/
 function edit_objects(&$out, $id) {
  require(DIR_MODULES.$this->name.'/objects_edit.inc.php');
 }
/**
* objects delete record
*
* @access public
*/
 function delete_objects($id) {
  $rec=SQLSelectOne("SELECT * FROM objects WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM history WHERE OBJECT_ID='".$rec['ID']."'");
  SQLExec("DELETE FROM methods WHERE OBJECT_ID='".$rec['ID']."'");
  SQLExec("DELETE FROM pvalues WHERE OBJECT_ID='".$rec['ID']."'");
  SQLExec("DELETE FROM properties WHERE OBJECT_ID='".$rec['ID']."'");
  SQLExec("DELETE FROM objects WHERE ID='".$rec['ID']."'");
 }


/**
* Title
*
* Description
*
* @access public
*/
 function loadObject($id) {
  $rec=SQLSelectOne("SELECT * FROM objects WHERE ID='".DBSafe($id)."'");
  if ($rec['ID']) {
   $this->id=$rec['ID'];
   $this->object_title=$rec['TITLE'];
   $this->class_id=$rec['CLASS_ID'];
   $this->description=$rec['DESCRIPTION'];
   $this->location_id=$rec['LOCATION_ID'];
  } else {
   return false;
  }

 }


/**
* Title
*
* Description
*
* @access public
*/
 function getParentProperties($id, $def='', $include_self=0) {
  $class=SQLSelectOne("SELECT * FROM classes WHERE ID='".(int)$id."'");

  $properties=SQLSelect("SELECT properties.*, classes.TITLE as CLASS_TITLE FROM properties LEFT JOIN classes ON properties.CLASS_ID=classes.ID WHERE CLASS_ID='".$id."' AND OBJECT_ID=0");

  if ($include_self) {
   $res=$properties;
  } else {
   $res=array();
  }

  if (!is_array($def)) {
   $def=array();
   foreach($properties as $p) {
    $def[]=$p['TITLE'];
   }
  }

  foreach($properties as $p) {
   if (!in_array($p['TITLE'], $def)) {
    $res[]=$p;
    $def[]=$p['TITLE'];
   }
  }

  if ($class['PARENT_ID']) {
   $p_res=$this->getParentProperties($class['PARENT_ID'], $def);
   if ($p_res[0]['ID']) {
    $res=array_merge($res, $p_res);
   }
  }

  return $res;

 }

 function getParentMethods($id, $def='', $include_self=0) {
  $class=SQLSelectOne("SELECT * FROM classes WHERE ID='".(int)$id."'");

  $methods=SQLSelect("SELECT methods.*, classes.TITLE as CLASS_TITLE FROM methods LEFT JOIN classes ON methods.CLASS_ID=classes.ID WHERE CLASS_ID='".$id."' AND OBJECT_ID=0");

  if ($include_self) {
   $res=$methods;
  } else {
   $res=array();
  }
  


  if (!is_array($def)) {
   $def=array();
   foreach($methods as $p) {
    $def[]=$p['TITLE'];
   }
  }

  foreach($methods as $p) {
   if (!in_array($p['TITLE'], $def)) {
    $res[]=$p;
    $def[]=$p['TITLE'];
   }
  }

  if ($class['PARENT_ID']) {
   $p_res=$this->getParentMethods($class['PARENT_ID'], $def);
   if ($p_res[0]['ID']) {
    $res=array_merge($res, $p_res);
   }
  }

  return $res;

 }


 /**
 * Title
 *
 * Description
 *
 * @access public
 */
  function getMethodByName($name, $class_id, $id) {

   if ($id) {
    $meth=SQLSelectOne("SELECT ID FROM methods WHERE OBJECT_ID='".(int)$id."' AND TITLE LIKE '".DBSafe($name)."'");
    if ($meth['ID']) {
     return $meth['ID'];
    }
   }

   //include_once(DIR_MODULES.'classes/classes.class.php');
   //$cl=new classes();
   //$meths=$cl->getParentMethods($class_id, '', 1);
   $meths=$this->getParentMethods($class_id, '', 1);

   $total=count($meths);
   for($i=0;$i<$total;$i++) {
    if (strtolower($meths[$i]['TITLE'])==strtolower($name)) {
     return $meths[$i]['ID'];
    }
   }
   return false;   

  }

/**
* Title
*
* Description
*
* @access public
*/
function raiseEvent($name, $params=0, $parent=0)
{
   $p = '';
   $url = BASE_URL . '/objects/?object=' . urlencode($this->object_title) . '&op=m&m=' . urlencode($name);
   if (is_array($params))
   {
      foreach($params as $k=>$v)
      {
         $p .= Convert::Utf8ToCp1251(' ' . $k . ':"' . $v . '"');
         $url .= '&' . urlencode($k) . '=' . urlencode($v);
      }
   }
   
   //echo DOC_ROOT.'/obj.bat '.utf2win().'.'.$name.' '.$p."<br>";
   //$cmd=(DOC_ROOT.'/obj.bat '.Convert::Utf8ToCp1251($this->object_title).'.'.$name.' '.$p);
   //echo $url;

   $ch = curl_init();

   // set URL and other appropriate options
   curl_setopt($ch, CURLOPT_URL, $url);

   /*
   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 500);
   curl_setopt($ch, CURLOPT_TIMEOUT, 1);
   curl_setopt($ch, CURLOPT_TIMEOUT_MS, 500);
   */
   curl_setopt($ch, CURLOPT_HEADER, 0);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   $data=curl_exec($ch);
   curl_close($ch);

   //$mh = curl_multi_init();
   //curl_multi_add_handle($mh,$ch);
   //curl_multi_exec($mh,$running);

  //popen("start /B ". $cmd, "r");
}

/**
* Title
*
* Description
*
* @access public
*/
 function callMethod($name, $params=0, $parent=0) {

  startMeasure('callMethod');
  startMeasure('callMethod ('.$name.')');

 if (!$parent) {
  $id=$this->getMethodByName($name, $this->class_id, $this->id);
 } else {
  $id=$this->getMethodByName($name, $this->class_id, 0);
 }

  if ($id) {

   $method=SQLSelectOne("SELECT * FROM methods WHERE ID='".$id."'");

   $method['EXECUTED']=date('Y-m-d H:i:s');
   if (!$method['OBJECT_ID']) {
    if (!$params) {
     $params=array();
    }
    $params['ORIGINAL_OBJECT_TITLE']=$this->object_title;
   }
   if ($params) {
    $method['EXECUTED_PARAMS']=serialize($params);
   }
   SQLUpdate('methods', $method);

   if ($method['OBJECT_ID'] && $method['CALL_PARENT']==1) {
    $this->callMethod($name, $params, 1);
   }

   if ($method['SCRIPT_ID']) {
   /*
    $script=SQLSelectOne("SELECT * FROM scripts WHERE ID='".$method['SCRIPT_ID']."'");
    $code=$script['CODE'];
   */
    runScript($method['SCRIPT_ID']);
   } else {
    $code=$method['CODE'];
   }
   

   if ($code!='') {

    if (defined('SETTINGS_DEBUG_HISTORY') && SETTINGS_DEBUG_HISTORY==1) {
     $class_object=SQLSelectOne("SELECT NOLOG FROM classes WHERE ID='".$this->class_id."'");
     if (!$class_object['NOLOG']) {

      $prevLog=SQLSelectOne("SELECT ID, UNIX_TIMESTAMP(ADDED) as UNX FROM history WHERE OBJECT_ID='".$this->id."' AND METHOD_ID='".$method['ID']."' ORDER BY ID DESC LIMIT 1");
      if ($prevLog['ID']) {
       $prevRun=$prevLog['UNX'];
       $prevRunPassed=time()-$prevLog['UNX'];
      }

      $h=array();
      $h['ADDED']=date('Y-m-d H:i:s');
      $h['OBJECT_ID']=$this->id;
      $h['METHOD_ID']=$method['ID'];
      $h['DETAILS']=serialize($params);
      if ($parent) {
       $h['DETAILS']='(parent method) '.$h['DETAILS'];
      }
      $h['DETAILS'].="\n".'code: '."\n".$code;
      SQLInsert('history', $h);
     }
    }


     try {
       $success = eval($code);
       if ($success === false) {
         getLogger($this)->error(sprintf('Error in "%s.%s" method. Code: %s', $this->object_title, $name, $code));
       }
     } catch (Exception $e) {
       getLogger($this)->error(sprintf('Exception in "%s.%s" method', $this->object_title, $name), $e);
     }

   }
   endMeasure('callMethod', 1);
   endMeasure('callMethod ('.$name.')', 1);
   if ($method['OBJECT_ID'] && $method['CALL_PARENT']==2) {
    $this->callMethod($name, $params, 1);
   }
  } else {
   endMeasure('callMethod ('.$name.')', 1);
   endMeasure('callMethod', 1);
   return false;
  }
 }

/**
* Title
*
* Description
*
* @access public
*/
 function getPropertyByName($name, $class_id, $object_id) {
  $rec=SQLSelectOne("SELECT ID FROM properties WHERE OBJECT_ID='".(int)$object_id."' AND TITLE LIKE '".DBSafe($name)."'");
  if ($rec['ID']) {
   return $rec['ID'];
  }

  //include_once(DIR_MODULES.'classes/classes.class.php');
  //$cl=new classes();
  //$props=$cl->getParentProperties($class_id, '', 1);
  $props=$this->getParentProperties($class_id, '', 1);

  $total=count($props);
  for($i=0;$i<$total;$i++) {
   if (strtolower($props[$i]['TITLE'])==strtolower($name)) {
    return $props[$i]['ID'];
   }
  }

 return false;

 }



/**
* Title
*
* Description
*
* @access public
*/
 function getProperty($property) {
  if ($this->object_title) {
   $value=SQLSelectOne("SELECT VALUE FROM pvalues WHERE PROPERTY_NAME = '".DBSafe($this->object_title.'.'.$property)."'");
   if (isset($value['VALUE'])) {
    startMeasure('getPropertyCached2');
    endMeasure('getPropertyCached2', 1);
    endMeasure('getProperty ('.$property.')', 1);
    endMeasure('getProperty', 1);
    return $value['VALUE'];
   }
  }
  startMeasure('getProperty');
  startMeasure('getProperty ('.$property.')');
  $id=$this->getPropertyByName($property, $this->class_id, $this->id);
  if ($id) {
   $value=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".(int)$id."' AND OBJECT_ID='".(int)$this->id."'");
   if (!$value['PROPERTY_NAME'] && $this->object_title) {
    $value['PROPERTY_NAME']=$this->object_title.'.'.$property;
    SQLUpdate('pvalues', $value);
   }
  } else {
   $value['VALUE']=false;
  }
  endMeasure('getProperty ('.$property.')', 1);
  endMeasure('getProperty', 1);
  return $value['VALUE'];
 }

/**
* Title
*
* Description
*
* @access public
*/
 function setProperty($property, $value, $no_linked=0) {

  startMeasure('setProperty');
  startMeasure('setProperty ('.$property.')');
  $id=$this->getPropertyByName($property, $this->class_id, $this->id);
  $old_value='';

  if ($id) {
   $prop=SQLSelectOne("SELECT * FROM properties WHERE ID='".$id."'");
   $v=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".(int)$id."' AND OBJECT_ID='".(int)$this->id."'");
   $old_value=$v['VALUE'];
   $v['VALUE']=$value;
   if ($v['ID']) {
    $v['UPDATED']=date('Y-m-d H:i:s');
    if ($old_value!=$value) {
     SQLUpdate('pvalues', $v);
     //DebMes("Setting [".$this->object_title.".".$property."] to new value [".$value."]");
    } else {
     SQLExec("UPDATE pvalues SET UPDATED='".$v['UPDATED']."' WHERE ID='".$v['ID']."'");
     //DebMes("Setting [".$this->object_title.".".$property."] to the same value [".$value."]");
    }
   } else {
    $v['PROPERTY_ID']=$id;
    $v['OBJECT_ID']=$this->id;
    $v['VALUE']=$value;
    $v['UPDATED']=date('Y-m-d H:i:s');
    $v['ID']=SQLInsert('pvalues', $v);
   }
   //DebMes(" $id to $value ");
  } else {
    $prop=array();
    $prop['OBJECT_ID']=$this->id;
    $prop['TITLE']=$property;
    $prop['ID']=SQLInsert('properties', $prop);

    $v['PROPERTY_ID']=$prop['ID'];
    $v['OBJECT_ID']=$this->id;
    $v['VALUE']=$value;
    $v['UPDATED']=date('Y-m-d H:i:s');
    $v['ID']=SQLInsert('pvalues', $v);
  }

  if ($prop['KEEP_HISTORY']>0) {
   startMeasure('DeleteOldHistory');
   SQLExec("DELETE FROM phistory WHERE VALUE_ID='".$v['ID']."' AND TO_DAYS(NOW())-TO_DAYS(ADDED)>".(int)$prop['KEEP_HISTORY']);
   endMeasure('DeleteOldHistory', 1);
   $h=array();
   $h['VALUE_ID']=$v['ID'];
   $h['ADDED']=date('Y-m-d H:i:s');
   $h['VALUE']=$value;
   $h['ID']=SQLInsert('phistory', $h);
  }

  /*
   $h=array();
   $h['ADDED']=date('Y-m-d H:i:s');
   $h['OBJECT_ID']=$this->id;
   $h['VALUE_ID']=$v['ID'];
   $h['OLD_VALUE']=$old_value;
   $h['NEW_VALUE']=$value;
   SQLInsert('history', $h);
  */

  //commands, owproperties, snmpproperties, zwave_properties, mqtt
  $tables=array('commands', 'owproperties', 'snmpproperties', 'zwave_properties', 'mqtt');
  if (!is_array($no_linked) && $no_linked) {
   $no_linked=array();
   foreach($tables as $t) {
    $no_linked[$k]='0';
   }
  } elseif (is_array($no_linked)) {
   foreach($tables as $t) {
    if (!isset($no_linked[$k])) {
     $no_linked[$k]='1';
    }
   }   
  } else {
   $no_linked=array();
   foreach($tables as $t) {
    $no_linked[$k]='1';
   }
  }

  foreach($tables as $t) {
   if ($no_linked[$t]=='') {
    $no_linked[$t]='1';
   }
  }

  if ($no_linked['commands']!='') {
   $commands=SQLSelect("SELECT * FROM commands WHERE LINKED_OBJECT LIKE '".DBSafe($this->object_title)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."' AND ".$no_linked['commands']);
   $total=count($commands);
   for($i=0;$i<$total;$i++) {
    $commands[$i]['CUR_VALUE']=$value;
    SQLUpdate('commands', $commands[$i]);
   }
  }

  if ($no_linked['owproperties']!='' && file_exists(DIR_MODULES.'/onewire/onewire.class.php')) {
   $owp=SQLSelect("SELECT ID FROM owproperties WHERE LINKED_OBJECT LIKE '".DBSafe($this->object_title)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."' AND ".$no_linked['owproperties']);
   $total=count($owp);
   if ($total) {
    include_once(DIR_MODULES.'/onewire/onewire.class.php');
    $on_wire=new onewire();
    for($i=0;$i<$total;$i++) {
     $on_wire->setProperty($owp[$i]['ID'], $value);
    }
   }
  }

  if ($no_linked['snmpproperties']!='' && file_exists(DIR_MODULES.'/snmpdevices/snmpdevices.class.php')) {
   $snmpdevices=SQLSelect("SELECT ID FROM snmpproperties WHERE LINKED_OBJECT LIKE '".DBSafe($this->object_title)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."' AND ".$no_linked['snmpproperties']);
   $total=count($snmpdevices);
   if ($total) {
    include_once(DIR_MODULES.'/snmpdevices/snmpdevices.class.php');
    $snmp=new snmpdevices();
    for($i=0;$i<$total;$i++) {
     $snmp->setProperty($snmpdevices[$i]['ID'], $value);
    }
   }
  }

  if ($no_linked['zwave_properties']!='' && file_exists(DIR_MODULES.'/zwave/zwave.class.php')) {
   $zwave_properties=SQLSelect("SELECT ID FROM zwave_properties WHERE LINKED_OBJECT LIKE '".DBSafe($this->object_title)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."' AND ".$no_linked['zwave_properties']);
   $total=count($zwave_properties);
   if ($total) {
    include_once(DIR_MODULES.'/zwave/zwave.class.php');
    $zwave=new zwave();
    for($i=0;$i<$total;$i++) {
     $zwave->setProperty($zwave_properties[$i]['ID'], $value);
    }
   }
  }

  if ($no_linked['mqtt']!='' && file_exists(DIR_MODULES.'/mqtt/mqtt.class.php')) {
   $mqtt_properties=SQLSelect("SELECT ID FROM mqtt WHERE LINKED_OBJECT LIKE '".DBSafe($this->object_title)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."' AND ".$no_linked['mqtt']);
   $total=count($mqtt_properties);
   if ($total) {
    include_once(DIR_MODULES.'/mqtt/mqtt.class.php');
    $mqtt=new mqtt();
    for($i=0;$i<$total;$i++) {
     $mqtt->setProperty($mqtt_properties[$i]['ID'], $value);
    }
   }
  }


  if ($prop['ONCHANGE']) {
   global $property_linked_history;
   if (!$property_linked_history[$property][$prop['ONCHANGE']]) {
    $property_linked_history[$property][$prop['ONCHANGE']]=1;
    global $on_change_called;
    $params=array();
    $params['NEW_VALUE']=(string)$value;
    $params['OLD_VALUE']=(string)$old_value;
    $this->callMethod($prop['ONCHANGE'], $params);
    unset($property_linked_history[$property][$prop['ONCHANGE']]);
   }


  }

  endMeasure('setProperty ('.$property.')', 1);
  endMeasure('setProperty', 1);

 }

/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($parent_name="") {
  parent::install($parent_name);
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS objects');
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
objects - Objects
*/
  $data = <<<EOD
 objects: ID int(10) unsigned NOT NULL auto_increment
 objects: TITLE varchar(255) NOT NULL DEFAULT ''
 objects: CLASS_ID int(10) NOT NULL DEFAULT '0'
 objects: DESCRIPTION text
 objects: LOCATION_ID int(10) NOT NULL DEFAULT '0'
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTWF5IDIyLCAyMDA5IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>