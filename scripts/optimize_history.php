<?php

/*
 * @version 0.2 (auto-set)
 */
chdir('../');

include_once("./config.php");
include_once("./lib/loader.php");

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once("./load_settings.php");

// OPTIMIZATION RULES
$rules = array(
   'tempSensors.temp'              => array('optimize' => 'avg'),
   'humSensors.humidity'           => array('optimize' => 'avg'),
   'lumSensors.value'              => array('optimize' => 'avg'),
   'humiditySensors.humidity'      => array('optimize' => 'avg'),
   'uptime'                        => array('keep'     => 30, 'optimize' => 'max'),
   'PowerMeters.power'             => array('optimize' => 'avg'),
   'PowerMeters.electric'          => array('optimize' => 'avg'),
   'Relays.status'                 => array('keep'     => 0),
   'inhouseMovementSensors.status' => array('keep'     => 30),
   'WeatherStations.tempOutside'   => array('optimize' => 'avg'),
   'WeatherStations.updatedTime'   => array('keep'     => 0),
   'WeatherStations.pressureRt'    => array('optimize' => 'avg'),
   'WeatherStations.pressure'      => array('optimize' => 'avg')
);

set_time_limit(6000);

DebMes('Optimize history script started');

//STEP 1 -- calculate stats
echo "Calculating stats:<br />";

$sqlQuery = "SELECT pvalues.ID, properties.TITLE as PTITLE, classes.TITLE as CTITLE, objects.TITLE as OTITLE
               FROM pvalues
               LEFT JOIN objects ON pvalues.OBJECT_ID = objects.ID
               LEFT JOIN classes ON objects.CLASS_ID  = classes.ID
               LEFT JOIN properties ON pvalues.PROPERTY_ID = properties.ID
             HAVING PTITLE != ''";

$pvalues = SQLSelect($sqlQuery);
$total = count($pvalues);

for ($i = 0; $i < $total; $i++)
{
   $sqlQuery = "SELECT COUNT(*) as TOTAL
                  FROM phistory
                 WHERE VALUE_ID = '" . $pvalues[$i]['ID'] . "'";

   $tmp = SQLSelectOne($sqlQuery);

   if ($tmp['TOTAL'])
   {
      echo $pvalues[$i]['CTITLE'] . "." . $pvalues[$i]['PTITLE'] . " (object: " . $pvalues[$i]['OTITLE'] . "): ";
      $grand_total += $tmp['TOTAL'];

      echo $tmp['TOTAL'] > 5000 ? "<b>" . $tmp['TOTAL'] . "</b>" : $tmp['TOTAL'];
      echo "<br />";
      echo str_repeat(' ', 1024);

      flush();
   }
}

echo "<h2>Grand-total: " . $grand_total . "</h2><br />";
echo str_repeat(' ', 1024);

flush();

// exit;
if (!$rules)
{
   echo "No rules defined.";
   exit;
}

//STEP 2 -- optimize values in time
$sqlQuery = "SELECT DISTINCT(VALUE_ID)
               FROM phistory";

$values = SQLSelect($sqlQuery);

$total = count($values);

for ($i = 0; $i < $total; $i++)
{
   $value_id = $values[$i]['VALUE_ID'];
   $sqlQuery = "SELECT pvalues.ID, properties.TITLE as PTITLE, objects.TITLE as OTITLE, classes.TITLE as CTITLE
                  FROM pvalues
                  LEFT JOIN objects ON pvalues.OBJECT_ID = objects.ID
                  LEFT JOIN properties ON pvalues.PROPERTY_ID = properties.ID
                  LEFT JOIN classes ON classes.ID = properties.CLASS_ID
                 WHERE pvalues.ID = '" . $value_id . "'";

   $pvalue = SQLSelectOne($sqlQuery);

   if ($pvalue['CTITLE'] != '')
   {
      $key = $pvalue['CTITLE'] . '.' . $pvalue['PTITLE'];
      $rule = '';

      if ($rules[$key])
         $rule = $rules[$key];
      elseif ($rules[$pvalue['OTITLE'] . '.' . $pvalue['PTITLE']])
         $rule = $rules[$pvalue['OTITLE'] . '.' . $pvalue['PTITLE']];
      elseif ($rules[$pvalue['PTITLE']])
         $rule = $rules[$pvalue['PTITLE']];

      if ($rule)
      {
         //processing
         echo "<h3>" . $pvalue['OTITLE'] . " (" . $key . ")</h3>";

         $sqlQuery = "SELECT COUNT(*) as TOTAL
                        FROM phistory
                       WHERE VALUE_ID = '" . $value_id . "'";

         $total_before = current(SQLSelectOne($sqlQuery));

         if (isset($rule['keep']))
         {
            echo " removing old (" . (int)$rule['keep'] . ")";
            $sqlQuery = "DELETE
                           FROM phistory
                          WHERE VALUE_ID = '" . $value_id . "'
                            AND TO_DAYS(NOW()) - TO_DAYS(ADDED) >= " . (int)$rule['keep'];
            SQLExec($sqlQuery);
         }

         if ($rule['optimize'])
         {
            echo str_repeat(' ', 1024);
            flush();

            $sqlQuery = "SELECT UNIX_TIMESTAMP(ADDED)
                           FROM phistory
                          WHERE VALUE_ID = '" . $value_id . "'
                          ORDER BY ADDED
                          LIMIT 1";

            echo "<br /><b>Before last MONTH</b><br />";
            $end = time() - 30 * 24 * 60 * 60; // month end older
            $start = current(SQLSelectOne($sqlQuery));
            $interval = 2 * 60 * 60; // two-hours interval
            optimizeHistoryData($value_id, $rule['optimize'], $interval, $start, $end);

            echo str_repeat(' ', 1024);
            flush();

            echo "<br /><b>Before last WEEK</b><br />";
            $start = $end + 1;
            $end = time() - 7 * 24 * 60 * 60; // week and older
            $interval = 1 * 60 * 60; // one-hour interval
            optimizeHistoryData($value_id, $rule['optimize'], $interval, $start, $end);

            echo str_repeat(' ', 1024);
            flush();

            echo "<br /><b>Before YESTERDAY</b><br />";
            $start = $end + 1;
            $end = time() - 1 * 24 * 60 * 60; // day and older
            $interval = 20 * 60; // 20 minutes interval
            optimizeHistoryData($value_id, $rule['optimize'], $interval, $start, $end);

            echo str_repeat(' ', 1024);
            flush();

            echo "<br /><b>Before last HOUR</b><br />";
            $start = $end + 1;
            $end = time() - 1 * 60 * 60; // 1 hour and older
            $interval = 3 * 60; // 3 minutes interval
            optimizeHistoryData($value_id, $rule['optimize'], $interval, $start, $end);
         }

         $sqlQuery = "SELECT COUNT(*) as TOTAL
                        FROM phistory
                       WHERE VALUE_ID = '" . $value_id . "'";
         $total_after = current(SQLSelectOne($sqlQuery));
         echo " <b>(changed " . $total_before . " -> " . $total_after . ")</b><br />";
      }
   }
}

SQLExec("OPTIMIZE TABLE phistory;");

echo "<h1>DONE!!!</h1>";

$db->Disconnect(); // closing database connection

DebMes("Optimize history script finished");

/**
 * Summary of optimizeHistoryData
 * @param mixed $valueID  Id value
 * @param mixed $type     Type
 * @param mixed $interval Interval
 * @param mixed $start    Begin date
 * @param mixed $end      End date
 * @return double|int
 */
function optimizeHistoryData($valueID, $type, $interval, $start, $end)
{
   $totalRemoved = 0;

   if (!$interval)
      return 0;

   $beginDate = date('Y-m-d H:i:s', $start);
   $endDate = date('Y-m-d H:i:s', $end);

   echo "Value ID: $valueID <br />";
   echo "Interval from " . $beginDate . " to " . $endDate . " (every " . $interval . " seconds)<br />";

   $sqlQuery = "SELECT COUNT(*)
                  FROM phistory
                 WHERE VALUE_ID =  '" . $valueID . "'
                   AND ADDED    >= '" . $beginDate . "'
                   AND ADDED    <= '" . $endDate . "'";

   $totalValues = (int)current(SQLSelectOne($sqlQuery));

   echo "Total values: " . $totalValues . "<br>";

   if ($totalValues < 2)
      return 0;

   $tmp = $end - $start;
   $tmp2 = round($tmp / $interval);

   if ($totalValues <= $tmp2)
   {
      echo "... number of values ($totalValues) is less than optimal (" . $tmp2 . ") (skipping)<br />";
      return 0;
   }

   echo "Optimizing (should be about " . $tmp2 . " records)...";

   echo str_repeat(' ', 1024);
   flush();

   $sqlQuery = "SELECT UNIX_TIMESTAMP(ADDED)
                  FROM phistory
                 WHERE VALUE_ID =  '" . $valueID . "'
                   AND ADDED    >= '" . $beginDate . "'
                 ORDER BY ADDED
                 LIMIT 1";

   $firstStart = current(SQLSelectOne($sqlQuery));

   $sqlQuery = "SELECT UNIX_TIMESTAMP(ADDED)
                  FROM phistory
                 WHERE VALUE_ID = '" . $valueID . "'
                   AND ADDED    <= '" . $endDate . "'
                 ORDER BY ADDED DESC
                 LIMIT 1";

   $lastStart = current(SQLSelectOne($sqlQuery));

   while ($start < $end)
   {
      if ($start < ($firstStart - $interval))
      {
         $start += $interval;
         continue;
      }

      if ($start > ($lastStart + $interval))
      {
         $start += $interval;
         continue;
      }

      echo ".";
      echo str_repeat(' ', 1024);
      flush();

      $sqlQuery = "SELECT *
                     FROM phistory
                    WHERE VALUE_ID = '" . $valueID . "'
                      AND ADDED    >= '" . date('Y-m-d H:i:s', $start) . "'
                      AND ADDED    <  '" . date('Y-m-d H:i:s', $start + $interval) . "'";

      $data = SQLSelect($sqlQuery);
      $total = count($data);

      if ($total > 1)
      {
         $values = array();

         for ($i = 0; $i < $total; $i++)
            $values[] = $data[$i]['VALUE'];

         if ($type == 'max')
            $newValue = max($values);
         elseif ($type == 'sum')
            $newValue = array_sum($values);
         else
            $newValue = array_sum($values) / $total;

         $sqlQuery = "DELETE
                        FROM phistory
                       WHERE VALUE_ID = '" . $valueID . "'
                         AND ADDED    >= '" . date('Y-m-d H:i:s', $start) . "'
                         AND ADDED    < '" . date('Y-m-d H:i:s', $start + $interval) . "'";

         SQLExec($sqlQuery);

         $addedDate = ($type == 'avg') ? $start + (int)($interval / 2) : $start + $interval - 1;

         $rec = array();
         $rec['VALUE_ID'] = $valueID;
         $rec['VALUE'] = $newValue;
         $rec['ADDED'] = date('Y-m-d H:i:s', $addedDate);

         SQLInsert('phistory', $rec);

         $totalRemoved += $total;
      }

      $start += $interval;
   }

   echo "<b>Done</b> (removed: $totalRemoved)<br>";
   SQLExec("OPTIMIZE TABLE `phistory`");

   return $totalRemoved;
}
