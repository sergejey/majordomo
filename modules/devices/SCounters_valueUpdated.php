<?php

$this->callMethod('statusUpdated');
$this->callMethod('logicAction');

$ot=$this->object_title;
$linked_room=$this->getProperty('linkedRoom');

if (!isset($params['NEW_VALUE'])) {
    $new_value=(float)$this->getProperty('value');
} else {
    $new_value=(float)$params['NEW_VALUE'];
}

$history_values=array(
    'valueHour'=>date('Y-m-d H:00:00'),
    'valueDay'=>date('Y-m-d 00:00:00'),
    'valueMonth'=>date('Y-m-01 00:00:00')
);

$main_value_id = getHistoryValueId($ot.'.value');
if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
    $main_table_name = createHistoryTable($main_value_id);
} else {
    $main_table_name = 'phistory';
}

$prev_value_set = 0;
foreach($history_values as $history_value=>$time) {
    $val1 = SQLSelectOne("SELECT ID, VALUE FROM $main_table_name WHERE VALUE_ID='".$main_value_id."' AND ADDED<=('".$time."') ORDER BY ADDED DESC LIMIT 1");
    $prev_value = 0;
    if ($val1['ID']) {
        $prev_value=$val1['VALUE'];
        $set_value = $new_value-$prev_value;
    } else {
        $set_value = $prev_value_set;
    }
    $set_value = round($set_value,3);
    $prev_value_set = $set_value;
    //echo "$history_value = $set_value; ";
    $value_id = (int)getHistoryValueId($ot.'.'.$history_value);
    if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
        $table_name = createHistoryTable($value_id);
    } else {
        $table_name = 'phistory';
    }
    SQLExec("DELETE FROM $table_name WHERE VALUE_ID=$value_id AND ADDED>=('".$time."')");
    //$end_time=date('Y-m-d H:i:s');
    //setTimeout('Counter_'.$value_id.'_'.$time,"SQLExec(\"DELETE FROM $table_name WHERE VALUE_ID=$value_id AND ADDED>=('$time') AND ADDED<('$end_time')\");",30);
    //$old_set_value = $this->getProperty($history_value);
    $this->setProperty($history_value,$set_value);
}


include_once(DIR_MODULES.'devices/devices.class.php');
$dv=new devices();
$dv->checkLinkedDevicesAction($this->object_title, $new_value);