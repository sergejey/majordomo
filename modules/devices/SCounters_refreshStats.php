<?php

$ot=$this->object_title;

$total_days=90;

$main_value_id = getHistoryValueId($ot.'.value');
if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
    $main_table_name = createHistoryTable($main_value_id);
} else {
    $main_table_name = 'phistory';
}

$value_id = (int)getHistoryValueId($ot.'.valueDay');
if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
    $table_name = createHistoryTable($value_id);
} else {
    $table_name = 'phistory';
}

$months=array();
for($i=0;$i<$total_days;$i++) {
    $tm=time()-$i*24*60*60;
    $day_begins = date('Y-m-d 00:00:00',$tm);
    $day_ends = date('Y-m-d 23:59:59',$tm);

    /*
    $val1 = SQLSelectOne("SELECT ID, VALUE, ADDED FROM $main_table_name WHERE VALUE_ID='".$main_value_id."' AND ADDED<=('".$day_begins."') ORDER BY ADDED DESC LIMIT 1");
    $val2 = SQLSelectOne("SELECT ID, VALUE, ADDED FROM $main_table_name WHERE VALUE_ID='".$main_value_id."' AND ADDED<=('".$day_ends."') ORDER BY ADDED DESC LIMIT 1");
    */
    $val1 = SQLSelectOne("SELECT ID, VALUE, ADDED FROM $main_table_name WHERE VALUE_ID='".$main_value_id."' AND ADDED>=('".$day_begins."') AND ADDED<=('".$day_ends."') ORDER BY ADDED LIMIT 1");
    $val2 = SQLSelectOne("SELECT ID, VALUE, ADDED FROM $main_table_name WHERE VALUE_ID='".$main_value_id."' AND ADDED>=('".$day_begins."') AND ADDED<=('".$day_ends."') ORDER BY ADDED DESC LIMIT 1");
    $day_result = 0;
    if (isset($val1['VALUE']) && isset($val2['VALUE'])) {
        $day_result=$val2['VALUE']-$val1['VALUE'];
        if ($day_result<0) {
            $day_result=0;
        } else {
            $day_result=round($day_result,2);
        }
    }
    SQLExec("DELETE FROM $table_name WHERE VALUE_ID=$value_id AND ADDED>=('".$day_begins."') AND ADDED<=('".$day_ends."')");
    if ($day_result>0) {
        $new_rec=array();
        $new_rec['VALUE_ID']=$value_id;
        $new_rec['ADDED']=$day_ends;
        $new_rec['VALUE']=$day_result;
        SQLInsert($table_name,$new_rec);
    }
    $months[date('Y-m',$tm)]+=$day_result;
}

$value_id = (int)getHistoryValueId($ot.'.valueMonth');
if (defined('SEPARATE_HISTORY_STORAGE') && SEPARATE_HISTORY_STORAGE == 1) {
    $table_name = createHistoryTable($value_id);
} else {
    $table_name = 'phistory';
}
foreach($months as $k=>$v) {
    $month_begins=$k.'-01 00:00:00';
    $month_ends=date('Y-m-t',strtotime($month_begins)).' 23:59:59';
    SQLExec("DELETE FROM $table_name WHERE VALUE_ID=$value_id AND ADDED>=('".$month_begins."') AND ADDED<=('".$month_ends."')");
    if ($v>0) {
        $new_rec=array();
        $new_rec['VALUE_ID']=$value_id;
        $new_rec['ADDED']=$month_ends;
        $new_rec['VALUE']=$v;
        SQLInsert($table_name,$new_rec);
    }
}