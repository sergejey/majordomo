<?php

$this->callMethod('statusUpdated');
$this->callMethod('logicAction');

$ot = $this->object_title;

if (!isset($params['NEW_VALUE']))
{
    $new_value = (float)$this->getProperty('value');
}
else
{
    $new_value = (float)$params['NEW_VALUE'];
}



$history_values=array(
    'valueHour' => date('Y-m-d H:00:00'),
    'valueDay' => date('Y-m-d 00:00:00')
);

$main_table_name = $table_name = 'phistory';

$main_value_id = getHistoryValueId($ot.'.value');
if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1)
{
    $main_table_name = createHistoryTable($main_value_id);
}

// DAY & HOUR
foreach($history_values as $history_value => $time)
{
    $val1 = SQLSelectOne("SELECT ID, VALUE FROM `{$main_table_name}` WHERE VALUE_ID='{$main_value_id}' AND ADDED>=('{$time}') ORDER BY ADDED LIMIT 1");
    $set_value = 0;

    if ($val1['ID'])
    {
        $set_value = $new_value - $val1['VALUE'];
    }
    else
    {
        $val1 = SQLSelectOne("SELECT ID, VALUE FROM `{$main_table_name}` WHERE VALUE_ID='{$main_value_id}' ORDER BY ADDED DESC LIMIT 1");
        if ($val1['ID'])
        {
            $set_value = $new_value - $val1['VALUE'];
        }
    }
    $set_value = round($set_value, 4);
    $value_id = (int)getHistoryValueId($ot.'.'.$history_value);
    if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1)
    {
        $table_name = createHistoryTable($value_id);
    }

    SQLExec("DELETE FROM `{$table_name}` WHERE VALUE_ID='{$value_id}' AND ADDED>=('{$time}')");
    $this->setProperty($history_value, $set_value);
}

// MONTH
//sum of days
$startmonthtime = date('Y-m-01 00:00:00');
$todaymorning = date('Y-m-d 00:00:00');
$dayvalue_id = (int)getHistoryValueId($ot.'.valueDay');

if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1)
{
    $table_name = createHistoryTable($dayvalue_id);
}

$byDays = SQLSelectOne("SELECT SUM(VALUE) as MONTH_TOTAL FROM `{$table_name}` WHERE VALUE_ID='{$dayvalue_id}' AND ADDED>=('{$startmonthtime}') AND ADDED<('{$todaymorning}')");
$set_value = round($byDays['MONTH_TOTAL'] + $this->getProperty('valueDay'), 4);
$monthvalue_id = (int)getHistoryValueId($ot.'.valueMonth');

if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1)
{
    $table_name = createHistoryTable($monthvalue_id);
}

SQLExec("DELETE FROM `{$table_name}` WHERE VALUE_ID='{$monthvalue_id}' AND ADDED>=('{$startmonthtime}')");
$this->setProperty('valueMonth', $set_value);


include_once(DIR_MODULES.'devices/devices.class.php');
$dv = new devices();
$dv->checkLinkedDevicesAction($this->object_title, $new_value);