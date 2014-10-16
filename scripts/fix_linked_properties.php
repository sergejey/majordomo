<?php

chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); 
 
include_once("./load_settings.php");


 $tables=array('commands'=>'commands', 'owproperties'=>'onewire', 'snmpproperties'=>'snmpdevices', 'zwave_properties'=>'zwave', 'mqtt'=>'mqtt', 'modbusdevices'=>'modbus');

 $value_ids=array();

 foreach($tables as $k=>$v) {
  $data=SQLSelect("SELECT * FROM $k WHERE LINKED_OBJECT!='' AND LINKED_PROPERTY!=''");
  $total=count($data);
  for($i=0;$i<$total;$i++) {
   $module=$v;
   $property=$data[$i]['LINKED_OBJECT'].'.'.$data[$i]['LINKED_PROPERTY'];
   if (!$value_ids[$property]) {
    $value_ids[$property]=getValueIdByName($data[$i]['LINKED_OBJECT'], $data[$i]['LINKED_PROPERTY']);
   }

   echo "$v: $property<br>";
   if ($value_ids[$property]) {
    $value=SQLSelectOne("SELECT * FROM pvalues WHERE ID=".(int)$value_ids[$property]);
    if (!$value['LINKED_MODULES']) {
     $tmp=array();
    } else {
     $tmp=explode(',', $value['LINKED_MODULES']);
    }
    if (!in_array($v, $tmp)) {
     echo "Adding linked<br>";
     $tmp[]=$v;
     $value['LINKED_MODULES']=implode(',', $tmp);
     SQLUpdate('pvalues', $value);
    }
   } else {
    echo "Removing linked<br>";
    //
    /*
    $data[$i]['LINKED_OBJECT']='';
    $data[$i]['LINKED_PROPERTY']='';
    SQLUpdate($k, $data[$i]);
    */
   }
  }
 }


?>