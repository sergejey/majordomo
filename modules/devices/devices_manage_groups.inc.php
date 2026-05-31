<?php

$id = gr('id');

$delete_id = gr('delete_id','int');
if ($delete_id) {
    $rec=SQLSelectOne("SELECT * FROM devices_groups WHERE ID=".(int)$delete_id);
    $property_title='group'.$rec['SYS_NAME'];
    $objects = getObjectsByProperty($property_title);
    if (is_array($objects)) {
        $total = count($objects);
        for($i=0;$i<$total;$i++) {
            $object_id=gg($objects[$i].'.object_id');
            $property_id=current(SQLSelectOne("SELECT ID FROM properties WHERE OBJECT_ID=".(int)$object_id." AND TITLE='".DBSafe($property_title)."'"));
            if ($property_id) {
                SQLExec("DELETE FROM pvalues WHERE PROPERTY_ID=".$property_id." AND OBJECT_ID=".$object_id);
                SQLExec("DELETE FROM properties WHERE ID=".$property_id);
            }
        }
    }
    SQLExec("DELETE FROM devices_groups WHERE ID=".$rec['ID']);
    SQLTruncateTable('cached_values');
    $this->updateGroupObjects();
    $this->redirect("?view_mode=".$this->view_mode);
}

if ($id) {
    $rec=SQLSelectOne("SELECT * FROM devices_groups WHERE ID=".(int)$id);
    if ($this->mode=='update') {
        $ok=1;
        $rec['TITLE']=gr('title');
        if (!$rec['TITLE']) {
            $ok=0;
            $out['ERR_TITLE']=1;
        }
        $rec['SYS_NAME']=trim(preg_replace('/\W/','',gr('sys_name')));

        global $types;
        if (is_array($types)) {
            $rec['APPLY_TYPES']=implode(',',$types);
        } else {
            $rec['APPLY_TYPES']='';
        }

        if ($ok) {
            if ($rec['ID']) {
                SQLUpdate('devices_groups',$rec);
            } else {
                $rec['ID']=SQLInsert('devices_groups',$rec);
            }

            if (!$rec['SYS_NAME']) {
                $rec['SYS_NAME']='Num'.$rec['ID'];
                SQLUpdate('devices_groups',$rec);
            }
            $this->updateGroupObjects();

            $object_rec=getObject('group'.$rec['SYS_NAME']);
            if ($object_rec->object_title) {
                $delay = gr('delay','float');
                sg($object_rec->object_title.'.delay', $delay);
            }

            $this->redirect("?view_mode=".$this->view_mode);
        }
    }

    if ($rec['ID']) {
        outHash($rec,$out);
        $object_rec=getObject('group'.$rec['SYS_NAME']);
        if ($object_rec->object_title) {
            $out['DELAY']=gg($object_rec->object_title.'.delay');
        }
    }

    $types=array();

    if ($rec['APPLY_TYPES']!='') {
        $applied=explode(',',$rec['APPLY_TYPES']);
    } else {
        $applied=array();
    }

    foreach($this->device_types as $k=>$v) {
        if (!$v['TITLE']) continue;
        $type_rec=array('NAME'=>$k,'TITLE'=>$v['TITLE']);
        if (in_array($k,$applied)) {
            $type_rec['SELECTED']=1;
        }
        $types[]=$type_rec;
    }
    $out['TYPES']=$types;

} else {
    $groups=SQLSelect("SELECT * FROM devices_groups ORDER BY TITLE");
    foreach($groups as &$group) {
        $object_rec=getObject('group'.$group['SYS_NAME']);
        if ($object_rec->object_title) {
            $group['OBJECT_TITLE']=$object_rec->object_title;
        }
    }
    $out['GROUPS']=$groups;
}

$out['ID']=$id;