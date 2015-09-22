<?php
/*
* @version 0.1 (auto-set)
*/

 function addClass($class_name, $parent_class='') {
  if ($parent_class!='') {
   $parent_class_id=addClass($parent_class);
  } else {
   $parent_class_id=0;
  }
  $class=SQLSelectOne("SELECT ID FROM classes WHERE TITLE LIKE '".DBSafe($class_name)."'");
  if ($class['ID']) {
   return $class['ID'];
  } else {
   $class=array();
   $class['TITLE']=$class_name;
   $class['PARENT_ID']=(int)$parent_class_id;
   $class['ID']=SQLInsert('classes', $class);
  }
 }

 function addClassMethod($class_name, $method_name, $code='') {
  $class_id=addClass($class_name) ;
  if ($class_id) {
   $method=SQLSelectOne("SELECT * FROM methods WHERE CLASS_ID='".$class_id."' AND TITLE LIKE '".DBSafe($method_name)."' AND OBJECT_ID=0");
   if (!$method['ID']) {
    $method=array();
    $method['CLASS_ID']=$class_id;
    $method['OBJECT_ID']=0;
    $method['CODE']=$code;
    $method['TITLE']=$method_name;
    $method['ID']=SQLInsert('methods', $method);
   } else {
    if ($code!='' && $method['CODE']!=$code) {
     $method['CODE']=$code;
     SQLUpdate('methods', $method);
    }
    return $method['ID'];
   }
  }
 }

 function addClassProperty($class_name, $property_name, $keep_history=0) {
  $class_id=addClass($class_name);
  $prop=SQLSelectOne("SELECT ID FROM properties WHERE TITLE LIKE '".DBSafe($property_name)."' AND OBJECT_ID=0 AND CLASS_ID='".$class_id."'");
  if (!$prop['ID']) {
   $prop=array();
   $prop['CLASS_ID']=$class_id;
   $prop['TITLE']=$property_name;
   $prop['KEEP_HISTORY']=$keep_history;
   $prop['OBJECT_ID']=0;
   $prop['ID']=SQLInsert('properties', $prop);
  }
  return $prop['ID'];
 }


 function addClassObject($class_name, $object_name) {
  $class_id=addClass($class_name);
  $object=SQLSelectOne("SELECT ID FROM objects WHERE TITLE LIKE '".DBSafe($object_name)."'");
  if ($object['ID']) {
   return $object['ID'];
  } else {
   $object=array();
   $object['TITLE']=$object_name;
   $object['CLASS_ID']=$class_id;
   $object['ID']=SQLInsert('objects', $object);
  }
 }



  function getValueIdByName($object_name, $property) {

    $value=SQLSelectOne("SELECT ID FROM pvalues WHERE PROPERTY_NAME = '".DBSafe($object_name.'.'.$property)."'");
    if (!$value['ID']) {
     $object=getObject($object_name);
     if (is_object($object)) {
      $property_id=$object->getPropertyByName($property, $object->class_id, $object->id); //
      $value=SQLSelectOne("SELECT ID FROM pvalues WHERE PROPERTY_ID='".(int)$property_id."' AND OBJECT_ID='".(int)$object->id."'");
      if (!$value['ID'] && $property_id) {
       $value=array();
       $value['PROPERTY_ID']=$property_id;
       $value['OBJECT_ID']=$object->id;
       $value['PROPERTY_NAME']=$object_name.'.'.$property;
       $value['ID']=SQLInsert('pvalues', $value);
      }
     }
    }

    return (int)$value['ID'];
    
  }

  function addLinkedProperty($object, $property, $module) {
    $value=SQLSelectOne("SELECT * FROM pvalues WHERE ID='".getValueIdByName($object, $property)."'");
    if ($value['ID']) {
     if (!$value['LINKED_MODULES']) {
      $tmp=array();
     } else {
      $tmp=explode(',', $value['LINKED_MODULES']);
     }
     if (!in_array($module, $tmp)) {
      $tmp[]=$module;
      $value['LINKED_MODULES']=implode(',', $tmp);
      SQLUpdate('pvalues', $value);
     }
    } else {
     return 0;
    }
  }

  function removeLinkedProperty($object, $property, $module) {
    $value=SQLSelectOne("SELECT * FROM pvalues WHERE ID='".getValueIdByName($object, $property)."'");
    if ($value['ID']) {
     if (!$value['LINKED_MODULES']) {
      $tmp=array();
     } else {
      $tmp=explode(',', $value['LINKED_MODULES']);
     }
     if (in_array($module, $tmp)) {
      $total=count($tmp);
      $res=array();
      for($i=0;$i<$total;$i++) {
       if ($tmp[$i]!=$module) {
        $res[]=$tmp[$i];
       }
      }
      $tmp=$res;
      $value['LINKED_MODULES']=implode(',', $tmp);
      SQLUpdate('pvalues', $value);
     }
    } else {
     return 0;
    }
  }

/**
* Title
*
* Description
*
* @access public
*/
 function getObject($name) {
  $qry='1';
  if (preg_match('/^(.+?)\.(.+?)$/', $name, $m)) {
   $class_name=$m[1];
   $object_name=$m[2];
   $rec=SQLSelectOne("SELECT objects.* FROM objects LEFT JOIN classes ON objects.CLASS_ID=classes.ID WHERE objects.TITLE LIKE '".DBSafe($object_name)."' AND classes.TITLE LIKE '".DBSafe($class_name)."'");
  } else {
   $rec=SQLSelectOne("SELECT objects.* FROM objects WHERE TITLE LIKE '".DBSafe($name)."'");
  }
  if ($rec['ID']) {
   include_once(DIR_MODULES.'objects/objects.class.php');
   $obj=new objects();
   $obj->id=$rec['ID'];
   $obj->loadObject($rec['ID']);
   return $obj;
  }
  return 0;
 }


/**
* Title
*
* Description
*
* @access public
*/
 function getObjectsByClass($class_name) {
  $class_record=SQLSelectOne("SELECT ID FROM classes WHERE (TITLE LIKE '".DBSafe(trim($class_name))."' OR ID=".(int)$class_name.")");
  if (!$class_record['ID']) {
   return 0;
  }

  $objects=SQLSelect("SELECT ID, TITLE FROM objects WHERE CLASS_ID='".$class_record['ID']."'");

  $sub_classes=SQLSelect("SELECT ID, TITLE FROM classes WHERE PARENT_ID='".$class_record['ID']."'");
  if ($sub_classes[0]['ID']) {
   $total=count($sub_classes);
   for($i=0;$i<$total;$i++) {
    $sub_objects=getObjectsByClass($sub_classes[$i]['TITLE']);
    if ($sub_objects[0]['ID']) {
     foreach($sub_objects as $obj) {
      $objects[]=$obj;
     }
    }
   }
  }

  /*
  $total=count($objects);
  for($i=0;$i<$total;$i++) {
   $objects[$i]=getObject($objects[$i]['TITLE'])
  }
  */

  return $objects;


 }


/**
* Title
*
* Description
*
* @access public
*/
 function getGlobal($varname) {

  $tmp=explode('.', $varname);
  if (isset($tmp[2])) {
   $object_name=$tmp[0].'.'.$tmp[1];
   $varname=$tmp[2];
  } elseif ($tmp[1]) {
   $object_name=$tmp[0];
   $varname=$tmp[1];
  } else {
   $object_name='ThisComputer';
  }
  
  $cached_name='MJD:'.$object_name.'.'.$varname;
  $cached_value=checkFromCache($cached_name);
  if ($cached_value!==false) {
   return $cached_value;
  }

  $obj=getObject($object_name);
  if ($obj) {
   $value=$obj->getProperty($varname);
   saveToCache($cached_name, $value);
   return $value;
  } else {
   return 0;
  }
 }

/**
* Title
*
* Description
*
* @access public
*/
 function setGlobal($varname, $value, $no_linked=0) {
  $tmp=explode('.', $varname);
  if (IsSet($tmp[2])) {
   $object_name=$tmp[0].'.'.$tmp[1];
   $varname=$tmp[2];
  } elseif (IsSet($tmp[1])) {
   $object_name=$tmp[0];
   $varname=$tmp[1];
  } else {
   $object_name='ThisComputer';
  }
  $obj=getObject($object_name);
  if ($obj) {
   return $obj->setProperty($varname, $value, $no_linked);
  } else {
   return 0;
  }
 }

/**
* Title
*
* Description
*
* @access public
*/
 function callMethod($method_name, $params=0) {
  $tmp=explode('.', $method_name);
  if ($tmp[2]) {
   $object_name=$tmp[0].'.'.$tmp[1];
   $varname=$tmp[2];
  } elseif ($tmp[1]) {
   $object_name=$tmp[0];
   $method_name=$tmp[1];
  } else {
   $object_name='ThisComputer';
  }
  $obj=getObject($object_name);
  if ($obj) {
   return $obj->callMethod($method_name, $params);
  } else {
   return 0;
  }
 
 }

/**
* processTitle
*
* Description
*
* @access public
*/
  function processTitle($title, $object=0) {

   global $title_memory_cache;

   $key=$title;

   if (!$title) {
    return $title;
   }

   startMeasure('processTitle');

   $in_title=substr($title, 0, 100);

   //startMeasure('processTitle ['.$in_title.']');

   if ($in_title!='') {

    if (($_SERVER['REQUEST_METHOD']=='GET' || $_SERVER['REQUEST_METHOD']=='POST')) {
     if ($title_memory_cache[$key]) {
      return $title_memory_cache[$key];
     }
    }


   if (preg_match('/\[#.+?#\]/is', $title)) {
    startMeasure('processTitleJTemplate');
    if ($object) {
     $jTempl=new jTemplate($title, $object->data, $object);
    } else {
     $jTempl=new jTemplate($title, $data, $this);
    }
    $title=$jTempl->result;
    endMeasure('processTitleJTemplate');
   }


   $title=preg_replace('/%rand%/is', rand(), $title);

   if (preg_match_all('/%([\w\d\.]+?)\.([\w\d\.]+?)%/is', $title, $m)) {
    startMeasure('processTitleProperties');
    $total=count($m[0]);
    for($i=0;$i<$total;$i++) {
     $title=str_replace($m[0][$i], getGlobal($m[1][$i].'.'.$m[2][$i]), $title);
    }
    endMeasure('processTitleProperties');
   } elseif (preg_match_all('/%([\w\d\.]+?)%/is', $title, $m)) {
    $total=count($m[0]);
    for($i=0;$i<$total;$i++) {
     if (preg_match('/^%\d/is', $m[0][$i])) {
      continue; // dirty hack, sorry for that
     }
     $title=str_replace($m[0][$i], getGlobal($m[1][$i]), $title);
    }
   }

   if (preg_match_all('/<#LANG_(\w+?)#>/is', $title, $m)) {
    $total=count($m[0]);
    for($i=0;$i<$total;$i++) {
     $title=str_replace($m[0][$i], constant('LANG_'.$m[1][$i]), $title);
    }
   }

   if (preg_match_all('/\&#060#LANG_(.+?)#\&#062/is', $title, $m)) {
    $total=count($m[0]);
    for($i=0;$i<$total;$i++) {
     $title=str_replace($m[0][$i], constant('LANG_'.$m[1][$i]), $title);
    }
   }

   }

   //endMeasure('processTitle ['.$in_title.']', 1);
   if (($_SERVER['REQUEST_METHOD']=='GET' || $_SERVER['REQUEST_METHOD']=='POST')) {
    $title_memory_cache[$key]=$title;
   }

   endMeasure('processTitle', 1);
   return $title;
  }


// SHORT ALIAS *****************************

 function sg($varname, $value, $no_linked=0) {
  return setGlobal($varname, $value, $no_linked);
 }

 function gg($varname) {
  return getGlobal($varname, $value);
 }

 function cm($method_name, $params=0) {
  return callMethod($method_name, $params);
 }

 function runMethod($method_name, $params=0) {
  return callMethod($method_name, $params);
 }

 function rs($script_id, $params=0) {
  return runScript($script_id, $params);
 }



