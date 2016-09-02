<?php

chdir('../');

include_once("./config.php");
include_once("./lib/loader.php");

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once("./load_settings.php");

$res = '';

//default days to keep data
const DAYS_TO_HISTORY_KEEP = 7;

$known_fields = array(
                  'tempOutside'          => 2,
                  'relHumOutside'        => 3,
                  'dewPoint'             => 4,
                  'windLatest'           => 6,
                  'windAverage'          => 5,
                  'rainfallRate'         => 8,
                  'rainfallHour'         => 47,
                  'rainfall24'           => 9,
                  'pressure'             => 10,
                  'pressureRt'           => -1,
                  'pressureTrend'        => 18,
                  'windDirection'        => 11,
                  'windDirectionAverage' => 51,
                  'tempInside'           => 22,
                  'relHumInside'         => 23,
                  'updatedTime'          => 1,
                  'updatedDate'          => 0,
                );

$data  = explode(' ', $_POST['data']);
$total = count($data);

for ($i = 0; $i < $total; $i++)
{
   $res .= 'data[' . $i . '] = ' . $data[$i] . "\n";
}

//DebMes('cumulus data: '.$res);
$obj = getObject('ws');

if (!$obj)
{
   $rec = array();
   $rec['TITLE'] = 'ws';
   $rec['ID']    = SQLInsert('objects', $rec);
   
   $obj = getObject('ws');
}

$sqlQuery = "SELECT * 
               FROM objects 
              WHERE ID = '" . $obj->id . "'";

$object_rec = SQLSelectOne($sqlQuery);

if (!$object_rec['CLASS_ID'])
{
   $class_rec = array();
   $class_rec['TITLE']       = 'WeatherStations';
   $class_rec['SUB_LIST']    = 0;
   $class_rec['PARENT_LIST'] = 0;
   $class_rec['ID']          = SQLInsert('classes', $class_rec);
   $object_rec['CLASS_ID']   = $class_rec['ID'];
   
   SQLUpdate('objects', $object_rec);
}

foreach ($known_fields as $k => $v)
{
   $sqlQuery = "SELECT * 
                  FROM properties 
                 WHERE TITLE LIKE '" . DBSafe($k) . "' 
                   AND CLASS_ID = '" . $object_rec['CLASS_ID'] . "'";
   
   $prop_rec = SQLSelectOne($sqlQuery);
   
   if (!$prop_rec['ID'])
   {
      $prop_rec['CLASS_ID']     = $object_rec['CLASS_ID'];
      $prop_rec['TITLE']        = $k;
      $prop_rec['KEEP_HISTORY'] = DAYS_TO_HISTORY_KEEP;
      $prop_rec['ID']           = SQLInsert('properties', $prop_rec);
   }
}

$res = '';

$updated = array();

$latest_update=gg('ws.updatedTimestamp');

foreach ($known_fields as $k => $v)
{
   if ($v < 0)
      continue;
   
   $res .= $k . ' = ' . $data[(int)$v] . "\n";
   $old_value = getGlobal('ws.' . $k);
   
   if ($old_value != $data[(int)$v]) // || ((time()-$latest_update)>30*60)
   {
      $updated[$k] = 1;
      setGlobal('ws.' . $k, $data[(int)$v]);
   }
}

if ($updated['pressure'])
{
   setGlobal('ws.pressureRt', round(((float)getGlobal('ws.pressure')) / 1.33), 1);
}

echo "OK";

$db->Disconnect(); // closing database connection
