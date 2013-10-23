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
class objects extends module 
{
   /**
    * objects
    *
    * Module class constructor
    *
    * @access private
    */
   function objects() 
   {
      $this->name            = "objects";
      $this->title           = "<#LANG_MODULE_OBJECT_INSTANCES#>";
      $this->module_category = "<#LANG_SECTION_OBJECTS#>";
      $this->checkInstalled();
   }

   /**
    * saveParams
    *
    * Saving module parameters
    *
    * @access public
    */
   function saveParams() 
   {
      $p = array();
      
      if (isset($this->id)) 
         $p["id"] = $this->id;
    
      if (isset($this->view_mode)) 
         $p["view_mode"] = $this->view_mode;
    
      if (isset($this->edit_mode)) 
         $p["edit_mode"] = $this->edit_mode;
    
      if (isset($this->tab)) 
         $p["tab"] = $this->tab;
    
      return parent::saveParams($p);
   }

   /**
    * getParams
    *
    * Getting module parameters from query string
    *
    * @access public
    */
   function getParams() 
   {
      global $id;
      global $mode;
      global $view_mode;
      global $edit_mode;
      global $tab;
     
      if (isset($id)) 
         $this->id = $id;
     
      if (isset($mode)) 
         $this->mode = $mode;
     
      if (isset($view_mode)) 
         $this->view_mode = $view_mode;
     
      if (isset($edit_mode)) 
         $this->edit_mode = $edit_mode;
     
      if (isset($tab))
         $this->tab=$tab;
   }
      
   /**
    * Run
    *
    * Description
    *
    * @access public
   */
   function run() 
   {
      global $session;
      
      $out = array();
  
      if ($this->action == 'admin') 
         $this->admin($out);
      else 
         $this->usual($out);
      
      if (IsSet($this->owner->action)) 
         $out['PARENT_ACTION'] = $this->owner->action;
  
      if (IsSet($this->owner->name)) 
         $out['PARENT_NAME'] = $this->owner->name;
  
      $out['VIEW_MODE'] = $this->view_mode;
      $out['EDIT_MODE'] = $this->edit_mode;
      $out['MODE']      = $this->mode;
      $out['ACTION']    = $this->action;
      $out['TAB']       = $this->tab;
      
      if ($this->single_rec) 
         $out['SINGLE_REC'] = 1;
  
      $this->data = $out;
      $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
  
      $this->result = $p->result;
   }

   /**
    * BackEnd
    *
    * Module backend
    *
    * @access public
    */
   function admin(&$out) 
   {
      if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) 
         $out['SET_DATASOURCE'] = 1;
    
      if ($this->data_source == 'objects' || $this->data_source == '') 
      {
         if ($this->view_mode == '' || $this->view_mode == 'search_objects') 
            $this->search_objects($out);
     
         if ($this->view_mode == 'edit_objects') 
            $this->edit_objects($out, $this->id);
     
         if ($this->view_mode == 'delete_objects')
         {
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
   function usual(&$out) 
   {
      if ($this->class) 
      {
         $objects  = getObjectsByClass($this->class);
         $template = !$this->code ? '#title# <i>#description#</i><br />' : $this->code;
         $result   = '';
         
         if ($objects[0]['ID']) 
         {
            $total = count($objects);
            for($i = 0; $i < $total; $i++) 
            {
               $objects[$i] = SQLSelectOne("SELECT * FROM objects WHERE ID = " . $objects[$i]['ID']);
               $line = $template;
               $line = preg_replace('/\#title\#/is', $objects[$i]['TITLE'], $line);
               $line = preg_replace('/\#description\#/is', $objects[$i]['DESCRIPTION'], $line);
    
               if (preg_match_all('/\#([\w\d_-]+?)\#/is', $line, $m)) 
               {
                  $totalm = count($m[0]);
                  for($im = 0; $im < $totalm; $im++) 
                  {
                     $property = trim($objects[$i]['TITLE'] . '.' . $m[1][$im]);
                     $line     = str_replace($m[0][$im], getGlobal($property), $line);
                  }
               }
               $result .= $line;
            }
         }
         $out['RESULT'] = $result;
      }
   }

   /**
    * objects search
    *
    * @access public
    */
   function search_objects(&$out) 
   {
      require(DIR_MODULES . $this->name . '/objects_search.inc.php');
   }

   /**
    * objects edit/add
    *
    * @access public
    */
   function edit_objects(&$out, $id) 
   {
      require(DIR_MODULES . $this->name . '/objects_edit.inc.php');
   }

   /**
    * objects delete record
    *
    * @access public
    */
   function delete_objects($id) 
   {
      $rec = SQLSelectOne("SELECT * FROM objects WHERE ID = " . $id);
      // some action for related tables
      SQLExec("DELETE FROM history WHERE OBJECT_ID = " . $rec['ID']);
      SQLExec("DELETE FROM methods WHERE OBJECT_ID = " . $rec['ID']);
      SQLExec("DELETE FROM pvalues WHERE OBJECT_ID = " . $rec['ID']);
      SQLExec("DELETE FROM properties WHERE OBJECT_ID = " . $rec['ID']);
      SQLExec("DELETE FROM objects WHERE ID = " . $rec['ID']);
   }

   /**
    * Title
    * Description
    * @access public
    */
   function loadObject($id) 
   {
      $rec = SQLSelectOne("select * from objects where ID = " . DBSafe($id));
  
      if ($rec['ID']) 
      {
         $this->id           = $rec['ID'];
         $this->object_title = $rec['TITLE'];
         $this->class_id     = $rec['CLASS_ID'];
         $this->description  = $rec['DESCRIPTION'];
         $this->location_id  = $rec['LOCATION_ID'];
      } 
      else 
      {
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
   function getMethodByName
     ($name, 
     $class_id, 
     $id) 
   {
      $name  = strtolower($name);
      
      if (isset($id)) 
      {
         $meth = SQLSelectOne("SELECT ID FROM methods WHERE OBJECT_ID = " . (int)$id . " AND TITLE LIKE '" . DBSafe($name) . "'");
         if (isset($meth['ID'])) 
            return $meth['ID'];
      }

      include_once(DIR_MODULES . 'classes/classes.class.php');
      
      $cl    = new classes();
      $meths = $cl->getParentMethods($class_id, '', 1);
      $total = count($meths);
      
      for($i = 0; $i < $total; $i++) 
      {
         if (strtolower($meths[$i]['TITLE']) == $name) 
            return $meths[$i]['ID'];
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
   function raiseEvent($name, $params = 0, $parent = 0) 
   {
      $p   = '';
      $url = BASE_URL . '/objects/?object=' . urlencode($this->object_title) . '&op=m&m=' . urlencode($name);
      if (is_array($params)) 
      {
         foreach($params as $k=>$v) 
         {
            $p   .= utf2win(' ' . $k . ':"' . $v . '"');
            $url .= '&' . urlencode($k) . '=' . urlencode($v);
         }
      }
      
      $ch = curl_init();

      // set URL and other appropriate options
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $data = curl_exec($ch);
      curl_close($ch);
   }

   /**
    * Title
    *
    * Description
    *
    * @access public
    */
   function callMethod($name, $params = 0, $parent = 0) 
   {
      $tmpID = (!$parent) ? $this->id : 0;
      $id = $this->getMethodByName($name, $this->class_id, $tmpID);
      
      if (isset($id))
      {
         $method = SQLSelectOne("SELECT * FROM methods WHERE ID = " . $id);

         $method['EXECUTED'] = date('Y-m-d H:i:s');
         if (!$method['OBJECT_ID']) 
         {
            if (!$params) 
               $params=array();
    
            $params['ORIGINAL_OBJECT_TITLE'] = $this->object_title;
         }
         
         if ($params) 
            $method['EXECUTED_PARAMS']=serialize($params);
   
         SQLUpdate('methods', $method);

         if ($method['OBJECT_ID'] && $method['CALL_PARENT'] == 1) 
            $this->callMethod($name, $params, 1);
   
         if ($method['SCRIPT_ID']) 
            runScript($method['SCRIPT_ID']);
         else 
            $code = $method['CODE'];
         
         if ($code != '') 
         {
            if (defined('SETTINGS_DEBUG_HISTORY') && SETTINGS_DEBUG_HISTORY == 1) 
            {
               $class_objectv = SQLSelectOne("SELECT NOLOG FROM classes WHERE ID = " . $this->class_id);
               if (!$class_object['NOLOG']) 
               {
                  $prevLog = SQLSelectOne("SELECT ID, UNIX_TIMESTAMP(ADDED) as UNX FROM history WHERE OBJECT_ID = " . $this->id . " AND METHOD_ID = " . $method['ID'] . " ORDER BY ID DESC LIMIT 1");
                  if (isset($prevLog['ID'])) 
                  {
                     $prevRun       = $prevLog['UNX'];
                     $prevRunPassed = time() - $prevLog['UNX'];
                  }

                  $h = array();
                  $h['ADDED']      = date('Y-m-d H:i:s');
                  $h['OBJECT_ID']  = $this->id;
                  $h['METHOD_ID']  = $method['ID'];
                  $h['DETAILS']    = serialize($params);
                  if ($parent) 
                     $h['DETAILS'] = '(parent method) ' . $h['DETAILS'];
                  
                  $h['DETAILS']    .= "\n" . 'code: ' . "\n" . $code;
                  SQLInsert('history', $h);
               }
            }
            eval($code);
         }

         if ($method['OBJECT_ID'] && $method['CALL_PARENT'] == 2) 
            $this->callMethod($name, $params, 1);
      } 
      else 
      {
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
   function getPropertyByName($name, $class_id, $object_id) 
   {
      $name = strtolower($name);
      $rec  = SQLSelectOne("SELECT ID FROM properties WHERE OBJECT_ID = " . (int)$object_id . " AND TITLE LIKE '" . DBSafe($name) . "'");
   
      if (isset($rec['ID'])) 
         return $rec['ID'];
  
      include_once(DIR_MODULES . 'classes/classes.class.php');
      $cl    = new classes();
      $props = $cl->getParentProperties($class_id, '', 1);
      $total = count($props);
  
      for($i = 0; $i < $total; $i++) 
      {
         if (strtolower($props[$i]['TITLE']) == $name) 
            return $props[$i]['ID'];
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
   function getProperty($property) 
   {
      $id = $this->getPropertyByName($property, $this->class_id, $this->id);
  
      if (isset($id)) 
      {
         $value = SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID = " . (int)$id . " AND OBJECT_ID = " . (int)$this->id);
         return $value['VALUE'];
      } 
      else 
      {
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
   function setProperty($property, $value, $no_linked = 0) 
   {
      $id = $this->getPropertyByName($property, $this->class_id, $this->id);
      $old_value = '';

      if (isset($id)) 
      {
         $prop       = SQLSelectOne("SELECT * FROM properties WHERE ID = '" . $id . "'");
         $v          = SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID = '" . $id . "' AND OBJECT_ID = '" . (int)$this->id . "'");
         $old_value  = $v['VALUE'];
         $v['VALUE'] = $value;
   
         if (isset($v['ID'])) 
         {
            $v['UPDATED'] = date('Y-m-d H:i:s');
            if ($old_value != $value) 
               SQLUpdate('pvalues', $v);
            else
               SQLExec("UPDATE pvalues SET UPDATED = '" . $v['UPDATED'] . "' WHERE ID = '" . $v['ID'] . "'");
         } 
         else 
         {
            $v['PROPERTY_ID'] = $id;
            $v['OBJECT_ID']   = $this->id;
            $v['VALUE']       = $value;
            $v['UPDATED']     = date('Y-m-d H:i:s');
            $v['ID']          = SQLInsert('pvalues', $v);
         }
      } 
      else 
      {
         $prop=array();
         $prop['OBJECT_ID']   = $this->id;
         $prop['TITLE']       = $property;
         $prop['ID']          = SQLInsert('properties', $prop);

         $v['PROPERTY_ID'] = $prop['ID'];
         $v['OBJECT_ID']   = $this->id;
         $v['VALUE']       = $value;
         $v['UPDATED']     = date('Y-m-d H:i:s');
         $v['ID']          = SQLInsert('pvalues', $v);
      }

      if ($prop['KEEP_HISTORY'] > 0) 
      {
         SQLExec("DELETE FROM phistory WHERE VALUE_ID = '" . $v['ID'] . "' AND TO_DAYS(NOW()) - TO_DAYS(ADDED) > " . (int)$prop['KEEP_HISTORY']);
         $h = array();
         $h['VALUE_ID'] = $v['ID'];
         $h['ADDED']    = date('Y-m-d H:i:s');
         $h['VALUE']    = $value;
         $h['ID']       = SQLInsert('phistory', $h);
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
      $total    = count($commands);
      for($i = 0; $i < $total; $i++) 
      {
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
   } elseif ($property_linked_history[$property][$prop['ONCHANGE']]) {
    unset($property_linked_history[$property][$prop['ONCHANGE']]);
   }


      }
   }
   
   /**
    * Install
    *
    * Module installation routine
    *
    * @access private
    */
   function install() 
   {
      parent::install();
   }
   
   /**
    * Uninstall
    *
    * Module uninstall routine
    *
    * @access public
    */
   function uninstall() 
   {
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
   function dbInstall()
   {
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