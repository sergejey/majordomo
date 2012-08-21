<?
/*
* @version 0.1 (auto-set)
*/


/**
* Title
*
* Description
*
* @access public
*/
 function getObject($name) {
  $rec=SQLSelectOne("SELECT * FROM objects WHERE TITLE LIKE '".DBSafe($name)."'");
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
 function getGlobal($varname) {
  $tmp=explode('.', $varname);
  if ($tmp[1]) {
   $object_name=$tmp[0];
   $varname=$tmp[1];
  } else {
   $object_name='ThisComputer';
  }
  $obj=getObject($object_name);
  if ($obj) {
   return $obj->getProperty($varname);
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
  if ($tmp[1]) {
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
  if ($tmp[1]) {
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

   $title=preg_replace('/%rand%/is', rand(), $title);
   if (preg_match_all('/%([\w\d\.]+?)\.([\w\d\.]+?)%/is', $title, $m)) {
    $total=count($m[0]);
    for($i=0;$i<$total;$i++) {
     $title=str_replace($m[0][$i], getGlobal($m[1][$i].'.'.$m[2][$i]), $title);
    }
   } elseif (preg_match_all('/%([\w\d\.]+?)%/is', $title, $m)) {
    $total=count($m[0]);
    for($i=0;$i<$total;$i++) {
     $title=str_replace($m[0][$i], getGlobal($m[1][$i]), $title);
    }
   }

   if (preg_match_all('/<#LANG_(\w+?)#>/is', $title, $m)) {
    $total=count($m[0]);
    for($i=0;$i<$total;$i++) {
     
    }
    for($i=0;$i<$total;$i++) {
     $title=str_replace($m[0][$i], constant('LANG_'.$m[1][$i]), $title);
    }
   }

   if (preg_match('/\[#.+?#\]/is', $title)) {
    if ($object) {
     $jTempl=new jTemplate($title, $object->data, $object);
    } else {
     $jTempl=new jTemplate($title, $data, $this);
    }
    $result=$jTempl->result;
    $title=$jTempl->result;
   }
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

 function rs($script_id, $params=0) {
  return runScript($script_id, $params);
 }



?>