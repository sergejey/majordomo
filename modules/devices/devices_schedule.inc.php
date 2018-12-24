<?php


$point_id=gr('point_id');


if ($point_id) {
    $point=SQLSelectOne("SELECT * FROM devices_scheduler_points WHERE ID=".(int)$point_id);
    if ($this->mode=='update') {
        $ok=1;
        $point['DEVICE_ID']=$rec['ID'];
        $point['LINKED_METHOD']=gr('linked_method','trim');
        if (!$point['LINKED_METHOD']) {
            $out['ERR_LINKED_METHOD']=1;
            $ok=0;
        }
        $set_time=gr('hour').':'.gr('minute');
        $set_days=gr('set_days');
        if (!isset($set_days[0])) {
            $set_days=array(0,1,2,3,4,5,6);
        }
        if (IsSet($set_days[0]) && preg_match('/^\d+:\d+$/', $set_time)) {
            $point['SET_TIME'] = $set_time;
            $point['SET_DAYS'] = implode(',', $set_days);
        } else {
            $ok=0;
        }
        if ($ok) {
            if (!$point['ID']) {
                $point['ACTIVE']=1;
                $point['ID']=SQLInsert('devices_scheduler_points',$point);
            } else {
                SQLUpdate('devices_scheduler_points',$point);
            }
            $this->redirect("?id=".$rec['ID']."&view_mode=".$this->view_mode."&tab=".$this->tab."&ok_msg=".urlencode(LANG_DATA_SAVED));
        }
    }
    foreach($point as $k=>$v) {
        $out['POINT_'.$k]=$v;
    }
    if ($point['SET_TIME']) {

    }
}


$days=array(
    array('VALUE'=>0,'TITLE'=>LANG_WEEK_SUN),
    array('VALUE'=>1,'TITLE'=>LANG_WEEK_MON),
    array('VALUE'=>2,'TITLE'=>LANG_WEEK_TUE),
    array('VALUE'=>3,'TITLE'=>LANG_WEEK_WED),
    array('VALUE'=>4,'TITLE'=>LANG_WEEK_THU),
    array('VALUE'=>5,'TITLE'=>LANG_WEEK_FRI),
    array('VALUE'=>6,'TITLE'=>LANG_WEEK_SAT),
);

$points=SQLSelect("SELECT * FROM devices_scheduler_points WHERE DEVICE_ID=".(int)$rec['ID']);
if ($points[0]['ID']) {
    foreach($points as &$point_item) {
        $point_days=explode(',',$point_item['SET_DAYS']);
        $point_days_title=array();
        foreach($days as $k=>$v) {
            if (in_array($v['VALUE'],$point_days)) {
                $point_days_title[]=$v['TITLE'];
            }
        }
        foreach($out['SHOW_METHODS'] as $method) {
            if ($method['NAME']==$point_item['LINKED_METHOD']) {
                $point_item['LINKED_METHOD']=$method['DESCRIPTION'];
                break;
            }
        }
        $point_item['SET_DAYS']=implode(', ',$point_days_title);
    }
    $out['POINTS']=$points;
}

if (gr('delete_id','int')) {
    SQLExec("DELETE FROM devices_scheduler_points WHERE DEVICE_ID=".$rec['ID']." AND ID='" . gr('delete_id','int') . "'");
    $this->redirect("?id=".$rec['ID']."&view_mode=".$this->view_mode."&tab=".$this->tab);
}


$out['DAYS']=$days;

if ($point['SET_DAYS']!='') {
    $tmp=explode(',',$point['SET_DAYS']);
    foreach($out['DAYS'] as &$day) {
        if (in_array($day['VALUE'],$tmp)) {
            $day['SELECTED']=1;
        }
    }
}

if ($point['SET_TIME']) {
    $tmp=explode(':',$point['SET_TIME']);
    $out['HOUR']=$tmp[0];
    $out['MINUTE']=$tmp[1];
}

$hours=array();
for($i=0;$i<24;$i++) {
    $hours[]=array('VALUE'=>str_pad($i,2,'0',STR_PAD_LEFT));
}
$out['HOURS']=$hours;



$minutes=array();
for($i=0;$i<60;$i++) {
    $minutes[]=array('VALUE'=>str_pad($i,2,'0',STR_PAD_LEFT));
}
$out['MINUTES']=$minutes;



/*
 devices_scheduler_points: ID int(10) unsigned NOT NULL auto_increment
 devices_scheduler_points: LINKED_METHOD varchar(255) NOT NULL DEFAULT ''
 devices_scheduler_points: VALUE varchar(255) NOT NULL DEFAULT ''
 devices_scheduler_points: SET_TIME varchar(50) NOT NULL DEFAULT ''
 devices_scheduler_points: SET_DAYS varchar(50) NOT NULL DEFAULT ''
 devices_scheduler_points: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 devices_scheduler_points: ACTIVE int(3) NOT NULL DEFAULT '1'
 devices_scheduler_points: LATEST_RUN datetime
 */