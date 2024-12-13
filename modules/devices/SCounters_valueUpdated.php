<?php

$ot = $params['ORIGINAL_OBJECT_TITLE'];
$new_value = (float)$params['NEW_VALUE'];
$old_value = (float)$params['OLD_VALUE'];

if ($new_value<0) return;

$timer_name = $ot . '_counter_update_h';
clearTimeOut($timer_name);

$timer_name = $ot . '_counter_update_d';
clearTimeOut($timer_name);

$timer_name = $ot . '_counter_update_m';
clearTimeOut($timer_name);

if ($new_value != $old_value) {

    // HOUR
    $timer_name = $ot . '_counter_update_h';
    $when_start = strtotime(date('Y-m-d H:59:59')) + 1;
    $timeout = $when_start - time() + 1;
    setTimeOut($timer_name, "DebMes('H: Updating $ot.value to $new_value','counter_update');setGlobal('$ot.value', '" . $new_value . "');", $timeout);
    $prev_timeout = $timeout;

    // DAY
    $timer_name = $ot . '_counter_update_d';
    $when_start = strtotime(date('Y-m-d 23:59:59')) + 1;
    $timeout = $when_start - time() + 1;
    if ($prev_timeout != $timeout) {
        setTimeOut($timer_name, "DebMes('D: Updating $ot.value to $new_value','counter_update');setGlobal('$ot.value', '" . $new_value . "');", $timeout);
        $prev_timeout = $timeout;
    }

    // MONTH
    $timer_name = $ot . '_counter_update_m';
    $when_start = strtotime(date('Y-m-t 23:59:59')) + 1;
    $timeout = $when_start - time() + 1;
    if ($prev_timeout != $timeout) {
        setTimeOut($timer_name, "DebMes('M: Updating $ot.value to $new_value','counter_update');setGlobal('$ot.value', '" . $new_value . "');", $timeout);
    }

}

$this->callMethodSafe('keepAlive');
//$this->callMethodSafe('statusUpdated');
$this->callMethodSafe('logicAction');

$history_values = array(
    'valueHour' => date('Y-m-d H:00:00'),
    'valueDay' => date('Y-m-d 00:00:00'),
    'valueMonth' => date('Y-m-01 00:00:00')
);

foreach ($history_values as $history_value => $time) {
    $value_id = (int)getHistoryValueId($ot . '.' . $history_value);
    if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
        $table_name = createHistoryTable($value_id);
    } else {
        $table_name = 'phistory';
    }
    $val1 = SQLSelectOne("SELECT ID, VALUE FROM pvalues WHERE ID='" . $value_id . "' AND UPDATED>=('" . $time . "')");
    if (isset($val1['ID'])) {
        SQLExec("DELETE FROM $table_name WHERE VALUE_ID=$value_id AND ADDED>=('" . $time . "')");
        $set_value = $val1['VALUE'] + ($new_value - $old_value);
        $set_value = round($set_value, 2);
        $this->setProperty($history_value, $set_value);
    } else {
        $set_value = $new_value - $old_value;
        $set_value = round($set_value, 2);
        $this->setProperty($history_value, $set_value);
        //DebMes($history_value . ' ' . $time . ' - id - ' . $value_id .  ' - new - ' . $new_value . ' - old - ' . $old_value, $ot);
    }
}

include_once(dirname(__FILE__) . '/devices.class.php');
$dv = new devices();
$dv->checkLinkedDevicesAction($this->object_title, $params);