<?php

 chdir('../');

 include_once("./config.php");
 include_once("./lib/loader.php");


 $db=new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database
 include_once("./load_settings.php");

 $res='';

 $known_fields=array(
  'tempOutside'=>2,
  'relHumOutside'=>3,
  'dewPoint'=>4,
  'windLatest'=>6,
  'windAverage'=>5,
  'rainfallRate'=>8,
  'rainfallHour'=>9,
  'rainfall24'=>47,
  'pressure'=>10,
  'pressureRt'=>-1,
  'pressureTrend'=>18,
  'windDirection'=>11,
  'windDirectionAverage'=>51,
  'tempInside'=>22,
  'relHumInside'=>23,
  'updatedTime'=>1,
  'updatedDate'=>0,
 );

 $data=explode(' ', $_POST['data']);
 $total=count($data);
 for($i=0;$i<$total;$i++) {
  $res.='data['.$i.'] = '.$data[$i]."\n";
 }

 //DebMes('cumulus data: '.$res);

 $obj=getObject('ws');
 if (!$obj) {
  $rec=array();
  $rec['TITLE']='ws';
  $rec['ID']=SQLInsert('objects', $rec);
  $obj=getObject('ws');
 }

  $object_rec=SQLSelectOne("SELECT * FROM objects WHERE ID='".$obj->id."'");
  if (!$object_rec['CLASS_ID']) {
   $class_rec=array();
   $class_rec['TITLE']='WeatherStations';
   $class_rec['SUB_LIST']=0;
   $class_rec['PARENT_LIST']=0;
   $class_rec['ID']=SQLInsert('classes', $class_rec);
   $object_rec['CLASS_ID']=$class_rec['ID'];
   SQLUpdate('objects', $object_rec);
  }

  foreach($known_fields as $k=>$v) {
   $prop_rec=SQLSelectOne("SELECT * FROM properties WHERE TITLE LIKE '".DBSafe($k)."' AND CLASS_ID='".$object_rec['CLASS_ID']."'");
   if (!$prop_rec['ID']) {
    $prop_rec['CLASS_ID']=$object_rec['CLASS_ID'];
    $prop_rec['TITLE']=$k;
    $prop_rec['KEEP_HISTORY']=7;
    $prop_rec['ID']=SQLInsert('properties', $prop_rec);
   }
  }


 $res='';
 foreach($known_fields as $k=>$v) {
  if ($v<0) {
   continue;
  }
  $res.=$k.' = '.$data[(int)$v]."\n";
  $old_value=getGlobal('ws.'.$k);
  if ($old_value!=$data[(int)$v]) {
   setGlobal('ws.'.$k, $data[(int)$v]);
  }
 }

 setGlobal('ws.pressureRt', round(((float)getGlobal('ws.pressure'))/1.33), 1);


 echo "OK";

 $db->Disconnect(); // closing database connection

?>
