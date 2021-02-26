<?php
/**
* Classes 
*
* Classes
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.3 (wizard, 12:05:48 [May 22, 2009])
*/
//
//
class classes extends module {
/**
* classes
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="classes";
  $this->title="<#LANG_MODULE_OBJECTS#>";
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
 $this->getConfig();

 if ($this->data_source=='classes' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_classes') {
   $this->search_classes($out);
  } 
  if ($this->view_mode=='export_classes') {
   $this->export_classes($out, $this->id);
  }

  if ($this->view_mode=='import_classes') {
   global $file;
   global $overwrite;
   global $only_classes;
   $this->import_classes($file,$overwrite,$only_classes);
   $this->redirect("?");
  }

  if ($this->view_mode=='edit_classes') {
   $this->edit_classes($out, $this->id);
  }
  if ($this->view_mode=='delete_classes') {
   $this->delete_classes($this->id);
   $this->redirect("?");
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
 function list_view(&$out) {

  global $class_id;
  global $location_id;

  if ($this->mode=='filter') {
   $this->config['FILTER_CLASS_ID']=(int)$class_id;
   $this->config['FILTER_LOCATION_ID']=(int)$location_id;
   $this->saveConfig();
  }

  $qry=1;

  if ($this->config['FILTER_CLASS_ID']) {
   $qry.=" AND objects.CLASS_ID='".(int)$this->config['FILTER_CLASS_ID']."'";
  }
  if ($this->config['FILTER_LOCATION_ID']) {
   $qry.=" AND objects.LOCATION_ID='".(int)$this->config['FILTER_LOCATION_ID']."'";
  }

  $objects=SQLSelect("SELECT objects.*, locations.TITLE as LOCATION FROM objects LEFT JOIN locations ON objects.LOCATION_ID=locations.ID WHERE $qry ORDER BY CLASS_ID, TITLE");
  $total=count($objects);
  for($i=0;$i<$total;$i++) {
   $objects[$i]['KEY_DATA']=getKeyData($objects[$i]['ID']);
  }
  $out['OBJECTS']=$objects;
  $out['CLASSES']=SQLSelect("SELECT * FROM classes ORDER BY TITLE");
  $out['LOCATIONS']=SQLSelect("SELECT * FROM locations ORDER BY TITLE");

  $out['CLASS_ID']=$this->config['FILTER_CLASS_ID'];
  $out['LOCATION_ID']=$this->config['FILTER_LOCATION_ID'];

 }

/**
* Title
*
* Description
*
* @access public
*/
 function import_classes($file,$overwrite=0,$only_classes=0) {

  $data=LoadFile($file);
  $records=unserialize($data);

  if (is_array($records)) {
   $total=count($records);
   for($i=0;$i<$total;$i++) {
    $old_class=SQLSelectOne("SELECT ID FROM classes WHERE TITLE = '".DBSafe($records[$i]['TITLE'])."'");
    $total_o=0;
    if ($old_class['ID']) {
     $old_objects=SQLSelect("SELECT * FROM objects WHERE CLASS_ID='".$old_class['ID']."'");
     $total_o=count($old_objects);
     for($io=0;$io<$total_o;$io++) {
      $old_objects[$io]['CLASS_ID']=0;
      SQLUpdate('objects', $old_objects[$io]);
     }
     if ($overwrite) {
      if (!$this->delete_classes($old_class['ID'])) {
       $records[$i]['ID']=$old_class['ID'];
      }
     } else {
      $records[$i]['TITLE']=$records[$i]['TITLE'].rand(0, 500);
     }
    }
    $objects=$records[$i]['OBJECTS'];
    unset($records[$i]['OBJECTS']);
    $methods=$records[$i]['METHODS'];
    unset($records[$i]['METHODS']);
    $properties=$records[$i]['PROPERTIES'];
    unset($records[$i]['PROPERTIES']);
    if ($records[$i]['PARENT_CLASS']) {
     $parent_class=SQLSelectOne("SELECT ID FROM classes WHERE TITLE = '".DBSafe($records[$i]['PARENT_CLASS'])."'");
     if ($parent_class['ID']) {
      $records[$i]['PARENT_ID']=$parent_class['ID'];
     }
     unset($records[$i]['PARENT_CLASS']);
    }

    if ($records[$i]['ID']) {
     SQLUpdate('classes', $records[$i]);
    } else {
     $records[$i]['ID']=SQLInsert('classes', $records[$i]);
    }


    if ($total_o) {
     for($io=0;$io<$total_o;$io++) {
      $old_objects[$io]['CLASS_ID']=$records[$i]['ID'];
      SQLUpdate('objects', $old_objects[$io]);
     }
    }

    if (is_array($properties)) {
     $total_p=count($properties);
     for($p=0;$p<$total_p;$p++) {
      $properties[$p]['CLASS_ID']=$records[$i]['ID'];
      $properties[$p]['ID']=SQLInsert('properties', $properties[$p]);
     }
    }

    if (is_array($methods)) {
     $total_m=count($methods);
     for($m=0;$m<$total_m;$m++) {
      $methods[$m]['CLASS_ID']=$records[$i]['ID'];
      $methods[$m]['ID']=SQLInsert('methods', $methods[$m]);
     }
    }

    if (is_array($objects) && !$only_classes) {
     $total_o=count($objects);
     for($o=0;$o<$total_o;$o++) {
      $objects[$o]['CLASS_ID']=$records[$i]['ID'];
      $methods=$objects[$o]['METHODS'];
      Unset($objects[$o]['METHODS']);
      $properties=$objects[$o]['PROPERTIES'];
      Unset($objects[$o]['PROPERTIES']);
      $objects[$o]['ID']=SQLInsert('objects', $objects[$o]);
      if ($objects[$o]['LOCATION_ID']) {
       $location_rec=SQLSelectOne("SELECT ID FROM locations WHERE ID=".$objects[$o]['LOCATION_ID']);
       if (!$location_rec['ID']) {
        $objects[$o]['LOCATION_ID']=0;
        SQLUpdate('objects',$objects[$o]);
       }
      }

      if (is_array($properties)) {
       $total_p=count($properties);
       for($p=0;$p<$total_p;$p++) {
        $properties[$p]['OBJECT_ID']=$objects[$o]['ID'];
        $properties[$p]['ID']=SQLInsert('properties', $properties[$p]);
       }
      }

      if (is_array($methods)) {
       $total_m=count($methods);
       for($m=0;$m<$total_m;$m++) {
        $methods[$m]['OBJECT_ID']=$objects[$o]['ID'];
        $methods[$m]['ID']=SQLInsert('methods', $methods[$m]);
       }
      }

     }
    }

   }
   //print_r($records);
  }

  $this->updateTree_classes();

 }

/**
* Title
*
* Description
*
* @access public
*/
 function export_classes(&$out, $id) {
  global $skip_objects;
  $qry=1;
  $qry.=" AND ID='".(int)$id."'";

  $sub_classes=SQLSelect("SELECT ID FROM classes WHERE PARENT_ID='".(int)$id."'");
  if ($sub_classes[0]['ID']) {
   $total=count($sub_classes);
   for($i=0;$i<$total;$i++) {
    $qry.=" OR ID='".$sub_classes[$i]['ID']."'";
   }
  }

  $records=SQLSelect("SELECT * FROM classes WHERE $qry");

  $total=count($records);
  for($i=0;$i<$total;$i++) {



    $methods=SQLSelect("SELECT * FROM methods WHERE CLASS_ID='".$records[$i]['ID']."'");
    $total_m=count($methods);
    for($m=0;$m<$total_m;$m++) {
     unset($methods[$m]['ID']);
     unset($methods[$m]['OBJECT_ID']);
     unset($methods[$m]['CLASS_ID']);
    }
    if ($total_m>0) {
     $records[$i]['METHODS']=$methods;
    }

    $properties=SQLSelect("SELECT * FROM properties WHERE CLASS_ID='".$records[$i]['ID']."'");
    $total_p=count($properties);
    for($p=0;$p<$total_p;$p++) {
     unset($properties[$p]['ID']);
     unset($properties[$p]['OBJECT_ID']);
     unset($properties[$p]['CLASS_ID']);
    }
    if ($total_p>0) {
     $records[$i]['PROPERTIES']=$properties;
    }


  if (!$skip_objects) {

   $objects=SQLSelect("SELECT * FROM objects WHERE CLASS_ID='".$records[$i]['ID']."'");
   $total_o=count($objects);
   for($o=0;$o<$total_o;$o++) {
    $methods=SQLSelect("SELECT * FROM methods WHERE OBJECT_ID='".$objects[$o]['ID']."'");
    $total_m=count($methods);
    for($m=0;$m<$total_m;$m++) {
     unset($methods[$m]['ID']);
     unset($methods[$m]['OBJECT_ID']);
     unset($methods[$m]['CLASS_ID']);
    }
    $objects[$o]['METHODS']=$methods;

    $properties=SQLSelect("SELECT * FROM properties WHERE OBJECT_ID='".$objects[$o]['ID']."'");
    $total_p=count($properties);
    for($p=0;$p<$total_p;$p++) {
     unset($properties[$p]['ID']);
     unset($properties[$p]['OBJECT_ID']);
     unset($properties[$p]['CLASS_ID']);
    }
    if ($total_p>0) {
     $objects[$o]['PROPERTIES']=$properties;
    }


    unset($objects[$o]['ID']);
    unset($objects[$o]['CLASS_ID']);
    //unset($objects[$o]['LOCATION_ID']);
    unset($objects[$o]['SUB_LIST']);
   }

  } else {
   $objects=array();
  }


   $records[$i]['OBJECTS']=$objects;
   unset($records[$i]['ID']);
   if ($records[$i]['PARENT_ID']) {
    $parent_class=SQLSelectOne("SELECT * FROM classes WHERE ID=".(int)$records[$i]['PARENT_ID']);
    $records[$i]['PARENT_CLASS']=$parent_class['TITLE'];
    unset($records[$i]['PARENT_ID']);
   }
   unset($records[$i]['PARENT_LIST']);
   unset($records[$i]['SUB_LIST']);
  }

  $data=serialize($records);
  $filename=urlencode($records[0]['TITLE']).'.txt';
  header('Content-Type: application/octet-stream');
  header('Content-Disposition: attachment; filename="'.($filename).'"');
  header('Expires: 0');
  echo $data;


  exit;
 }

/**
* Title
*
* Description
*
* @access public
*/
 function getParentProperties($id, $def='', $include_self=0) {
  $class=SQLSelectOne("SELECT PARENT_ID FROM classes WHERE ID='".(int)$id."'");

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
  $class=SQLSelectOne("SELECT PARENT_ID FROM classes WHERE ID='".(int)$id."'");

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
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* classes search
*
* @access public
*/
 function search_classes(&$out) {
  require(DIR_MODULES.$this->name.'/classes_search.inc.php');
 }
/**
* classes edit/add
*
* @access public
*/
 function edit_classes(&$out, $id) {
  require(DIR_MODULES.$this->name.'/classes_edit.inc.php');
 }
/**
* classes delete record
*
* @access public
*/
 function delete_classes($id) {
  $rec=SQLSelectOne("SELECT * FROM classes WHERE ID='$id'");
  // some action for related tables
  if ($rec['SUB_LIST']!='' && $rec['SUB_LIST']!=$rec['ID'] && $rec['SUB_LIST']!='') {
   return 0;
  }
  SQLExec("DELETE FROM properties WHERE CLASS_ID='".$rec['ID']."' AND OBJECT_ID=0");
  SQLExec("DELETE FROM methods WHERE CLASS_ID='".$rec['ID']."' AND OBJECT_ID=0");
  include_once(DIR_MODULES.'objects/objects.class.php');
  $o=new objects();
  $objects=SQLSelect("SELECT * FROM objects WHERE CLASS_ID='".$rec['ID']."'");
  $total=count($objects);
  for($i=0;$i<$total;$i++) {
   $o->delete_objects($objects[$i]['ID']);   
  }
  SQLExec("DELETE FROM classes WHERE ID='".$rec['ID']."'");
  $this->updateTree_classes();
  clearCacheData();
  return 1;
 }
/**
* classes build tree
*
* @access private
*/
 function buildTree_classes($res, $parent_id=0, $level=0) {
  $total=count($res);
  $res2=array();
  for($i=0;$i<$total;$i++) {
   if ($res[$i]['PARENT_ID']==$parent_id) {
    $res[$i]['LEVEL']=$level;
    $res[$i]['LEVEL_PAD']=$level*2;
    $res[$i]['RESULT']=$this->buildTree_classes($res, $res[$i]['ID'], ($level+1));
    if (!is_array($res[$i]['RESULT'])) {
     unset($res[$i]['RESULT']);
    }
    if (!$res[$i]['RESULT'] && !$res[$i]['OBJECTS']) {
     $res[$i]['CAN_DELETE']=1;
    }
    $res2[]=$res[$i];
   }
  }
  $total2=count($res2);
  if ($total2) {
	//echo '<pre>';
	  //var_dump($res2);
   return $res2;
  }
 }
/**
* classes update tree
*
* @access private
*/
 function updateTree_classes($parent_id=0, $parent_list='') {
  $table='classes';
  if (!is_array($parent_list)) {
   $parent_list=array();
  }
  $sub_list=array();
  $res=SQLSelect("SELECT * FROM $table WHERE PARENT_ID='$parent_id'");
  $total=count($res);
  for($i=0;$i<$total;$i++) {
   if ($parent_list[0]) {
    $res[$i]['PARENT_LIST']=implode(',', $parent_list);
   } else {
    $res[$i]['PARENT_LIST']='0';
   }
   $sub_list[]=$res[$i]['ID'];
   $tmp_parent=$parent_list;
   $tmp_parent[]=$res[$i]['ID'];
   $sub_this=$this->updateTree_classes($res[$i]['ID'], $tmp_parent);
   if ($sub_this[0]) {
    $res[$i]['SUB_LIST']=implode(',', $sub_this);
   } else {
    $res[$i]['SUB_LIST']=$res[$i]['ID'];
   }
   SQLUpdate($table, $res[$i]);
   $sub_list=array_merge($sub_list, $sub_this);
  }
  return $sub_list;
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
   SQLDropTable('classes');
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
classes - Classes
*/
  $data = <<<EOD
 classes: ID int(10) unsigned NOT NULL auto_increment
 classes: TITLE varchar(255) NOT NULL DEFAULT ''
 classes: PARENT_ID int(10) NOT NULL DEFAULT '0'
 classes: NOLOG int(3) NOT NULL DEFAULT '0'
 classes: SUB_LIST text
 classes: PARENT_LIST text
 classes: DESCRIPTION text
 classes: TEMPLATE text
 classes: INDEX (PARENT_ID)

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
