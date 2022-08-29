<?php

if ($this->edit_mode=='delete_link') {
    global $link_id;
    SQLExec("DELETE FROM security_rules WHERE OBJECT_TYPE='sdevice' AND OBJECT_ID=".(int)$link_id);
    SQLExec("DELETE FROM devices_linked WHERE ID=".(int)$link_id);
    $this->redirect("?id=".$rec['ID']."&tab=".$this->tab."&view_mode=".$this->view_mode);
}

if ($this->edit_mode=='edit_link') {
    global $link_name;
    global $link_id;

    $link_details=$this->getLinkDetails($link_name);

    $link_rec=SQLSelectOne("SELECT * FROM devices_linked WHERE ID=".(int)$link_id);
    if ($link_rec['ID']) {
        $out['DEVICE2_ID']=$link_rec['DEVICE2_ID'];
        $out['LINK_ID']=$link_rec['ID'];
        $out['COMMENT']=$link_rec['COMMENT'];
        $settings=unserialize($link_rec['LINK_SETTINGS']);
        if (is_array($link_details['PARAMS'])) {
        foreach($link_details['PARAMS'] as &$p) {
            if (isset($settings[$p['PARAM_NAME']])) {
                $p['VALUE']=$settings[$p['PARAM_NAME']];
            }
        }
        }
    }

    if ($this->mode=='update') {
        $ok=1;
        $link_rec['DEVICE1_ID']=$rec['ID'];
        $link_rec['LINK_TYPE']=$link_name;
        global $device2_id;
        $link_rec['DEVICE2_ID']=(int)$device2_id;
        if (!$link_rec['DEVICE2_ID']) {
            $ok=0;
        }

        global $comment;
        $link_rec['COMMENT']=$comment;

        $params=$link_details['PARAMS'];

        $config=array();
        $total = count($params);
        for ($i = 0; $i < $total; $i++) {
            global ${$params[$i]['PARAM_NAME'].'_value'};
            $config[$params[$i]['PARAM_NAME']]=${$params[$i]['PARAM_NAME'].'_value'};
        }
        $link_rec['LINK_SETTINGS']=serialize($config);
        if ($ok) {
            if ($link_rec['ID']) {
                SQLUpdate('devices_linked',$link_rec);
            } else {
                $link_rec['ID']=SQLInsert('devices_linked',$link_rec);
            }
        }
        $this->redirect("?id=".$rec['ID']."&tab=".$this->tab."&view_mode=".$this->view_mode);
    }

    foreach($link_details as $k=>$v) {
        $out['LINK_DETAILS_'.$k]=$v;
    }
    $target_classes=explode(',',$link_details['TARGET_CLASS']);
    $target_classes=array_map('trim',$target_classes);
    $second_devices=array();

    $other_devices=SQLSelect("SELECT * FROM devices WHERE ID!=".(int)$rec['ID']." ORDER BY TITLE");
    $total = count($other_devices);
    for ($i = 0; $i < $total; $i++) {
        $type_details=$this->getTypeDetails($other_devices[$i]['TYPE']);
        if (in_array($type_details['CLASS'],$target_classes) || in_array($type_details['PARENT_CLASS'],$target_classes)) {
            $second_devices[]=$other_devices[$i];
        }
    }
    $out['SECOND_DEVICES']=$second_devices;

    //print_r($link_details);exit;
}

$links=SQLSelect("SELECT devices_linked.*, devices.TITLE FROM devices_linked LEFT JOIN devices ON devices.ID=DEVICE2_ID WHERE (DEVICE1_ID=".(int)$rec['ID']." OR DEVICE2_ID=".(int)$rec['ID'].") ORDER BY ID");
if ($links[0]['ID']) {
    $total = count($links);
    for ($i = 0; $i < $total; $i++) {
        $device1=SQLSelectOne("SELECT ID, TITLE FROM devices WHERE ID=".(int)$links[$i]['DEVICE1_ID']);
        $links[$i]['DEVICE1_TITLE']=$device1['TITLE'];
        if ($links[$i]['LINK_SETTINGS']!='') {
            $settings=unserialize($links[$i]['LINK_SETTINGS']);
            $new_settings='';
            foreach($settings as $k=>$v) {
                if ($v=='' || $v=='0') continue;
                $new_settings.=$k.': <i>'.$v.'</i>; ';
            }
            $links[$i]['LINK_SETTINGS']=$new_settings;
        }
        $rule=SQLSelectOne("SELECT ID FROM security_rules WHERE OBJECT_TYPE='sdevice' AND OBJECT_ID=". $links[$i]['ID']);
        if ($rule['ID']) {
            $links[$i]['HAS_RULE']=1;
        }
    }
    $out['LINKS']=$links;
}

$avail_links=$this->getTypeLinks($rec['TYPE']);
if (isset($avail_links[0])) {
    $out['AVAIL_LINKS']=$avail_links;
}

