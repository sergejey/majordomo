<?php

$ot=$this->object_title;

$this->callMethodSafe('keepAlive');
$this->callMethodSafe('statusUpdated');
$this->callMethodSafe('logicAction');

$linked_room=$this->getProperty('linkedRoom');

if (!isset($params['NEW_VALUE'])) {
    $new_value=(float)$this->getProperty('value');
} else {
    $new_value=(float)$params['NEW_VALUE'];
}



$history_values=array(
    'valueHour'=>date('Y-m-d H:00:00'),
    'valueDay'=>date('Y-m-d 00:00:00')
);
//,'valueMonth'=>date('Y-m-01 00:00:00')

$main_value_id = getHistoryValueId($ot.'.value');
if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
    $main_table_name = createHistoryTable($main_value_id);
} else {
    $main_table_name = 'phistory';
}

$prev_value_set = 0;
foreach($history_values as $history_value=>$time) {
    $val1 = SQLSelectOne("SELECT ID, VALUE FROM $main_table_name WHERE VALUE_ID='".$main_value_id."' AND ADDED>=('".$time."') ORDER BY ADDED LIMIT 1");
    $prev_value = 0;
    if ($val1['ID']) {
        $prev_value=$val1['VALUE'];
        $set_value = $new_value-$prev_value;
    } else {
        $set_value = 0;
    }
    $set_value = round($set_value,2);
    $value_id = (int)getHistoryValueId($ot.'.'.$history_value);
    if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
        $table_name = createHistoryTable($value_id);
    } else {
        $table_name = 'phistory';
    }
    SQLExec("DELETE FROM $table_name WHERE VALUE_ID=$value_id AND ADDED>=('".$time."')");
    //echo "Setting $history_value to $set_value<br/>";
    $this->setProperty($history_value,$set_value);
}

// DAY

// MONTH
//sum of days
$time=date('Y-m-01 00:00:00');
$time2=date('Y-m-d 00:00:00');
$history_value='valueDay';
$value_id = (int)getHistoryValueId($ot.'.'.$history_value);
if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
    $table_name = createHistoryTable($value_id);
} else {
    $table_name = 'phistory';
}

$byDays = SQLSelect("SELECT MAX(VALUE) as DAY_TOTAL FROM $table_name WHERE VALUE_ID='".$value_id."' AND ADDED>=('".$time."') AND ADDED<('".$time2."') GROUP BY TO_DAYS(ADDED)");
$total = count($byDays);
$monthTotal=0;
for($i=0;$i<$total;$i++) {
    $monthTotal+=$byDays[$i]['DAY_TOTAL'];
}
/*
$val1 = SQLSelectOne("SELECT SUM(VALUE) as TOTAL FROM $table_name WHERE VALUE_ID='".$value_id."' AND ADDED>=('".$time."') AND ADDED<('".$time2."')");
$set_value = round($val1['TOTAL']+$this->getProperty('valueDay'),2);
*/
$set_value = round($monthTotal+$this->getProperty('valueDay'),2);
$history_value='valueMonth';
$value_id = (int)getHistoryValueId($ot.'.'.$history_value);
if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
    $table_name = createHistoryTable($value_id);
} else {
    $table_name = 'phistory';
}
SQLExec("DELETE FROM $table_name WHERE VALUE_ID=$value_id AND ADDED>=('".$time."')");
$this->setProperty($history_value,$set_value);


include_once(DIR_MODULES.'devices/devices.class.php');
$dv=new devices();
$dv->checkLinkedDevicesAction($this->object_title, $new_value);