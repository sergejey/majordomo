<?php

$out['ALL_DEVICES'] = SQLSelect("SELECT ID, TITLE FROM devices ORDER BY TITLE");

$days=array(
    array('VALUE'=>0,'TITLE'=>LANG_WEEK_SUN),
    array('VALUE'=>1,'TITLE'=>LANG_WEEK_MON),
    array('VALUE'=>2,'TITLE'=>LANG_WEEK_TUE),
    array('VALUE'=>3,'TITLE'=>LANG_WEEK_WED),
    array('VALUE'=>4,'TITLE'=>LANG_WEEK_THU),
    array('VALUE'=>5,'TITLE'=>LANG_WEEK_FRI),
    array('VALUE'=>6,'TITLE'=>LANG_WEEK_SAT),
);

//$point=SQLSelectOne("SELECT * FROM devices_scheduler_points WHERE ID=".(int)$point_id);
//$devices = SQLSelect("SELECT devices.* FROM devices")
$tmp = array_map('current',SQLSelect("SELECT DEVICE_ID FROM devices_scheduler_points GROUP BY DEVICE_ID"));

$type_methods=array();

if (is_array($tmp) && count($tmp)) {
    $devices = SQLSelect("SELECT * FROM devices WHERE ID IN (".implode(',',$tmp).") ORDER BY devices.TITLE");
    $total = count($devices);
    for($i=0;$i<$total;$i++) {
        if (!isset($type_methods[$devices[$i]['TYPE']])) {
            $all_methods = $this->getAllMethods($devices[$i]['TYPE']);
            $type_methods[$devices[$i]['TYPE']] = $all_methods;
        }
        $points=SQLSelect("SELECT * FROM devices_scheduler_points WHERE DEVICE_ID=".(int)$devices[$i]['ID']);
        foreach($points as &$point_item) {
            $point_days=explode(',',$point_item['SET_DAYS']);
            $point_days_title=array();
            foreach($days as $k=>$v) {
                if (in_array($v['VALUE'],$point_days)) {
                    $point_days_title[]=$v['TITLE'];
                }
            }
            $rule=SQLSelectOne("SELECT ID FROM security_rules WHERE OBJECT_TYPE='spoint' AND OBJECT_ID=".$point_item['ID']);
            if ($rule['ID']) {
                $point_item['HAS_RULE']=1;
            }
            if (isset($type_methods[$devices[$i]['TYPE']][$point_item['LINKED_METHOD']]['DESCRIPTION'])) {
                $point_item['LINKED_METHOD']=$type_methods[$devices[$i]['TYPE']][$point_item['LINKED_METHOD']]['DESCRIPTION'];
            }
            /*
            foreach($out['SHOW_METHODS'] as $method) {
                if ($method['NAME']==$point_item['LINKED_METHOD']) {
                    $point_item['LINKED_METHOD']=$method['DESCRIPTION'];
                    break;
                }
            }
            */
            $point_item['SET_DAYS']=implode(', ',$point_days_title);
        }
        $devices[$i]['POINTS']=$points;
    }
    $out['DEVICES']=$devices;
}