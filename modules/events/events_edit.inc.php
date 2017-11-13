<?php

  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }

 $rec=SQLSelectOne("SELECT * FROM events WHERE ID='$id'");
if ($this->mode=='update') {
    global $description;
    $rec['DESCRIPTION']=$description;
    SQLUpdate('events',$rec);
    $out['OK']=1;
}
 outHash($rec, $out);

$params=SQLSelect("SELECT * FROM events_params WHERE EVENT_ID=".$rec['ID']." ORDER BY TITLE");
$total = count($params);
for ($i = 0; $i < $total; $i++) {
    if ($this->mode=='update') {
        global ${"linked_object".$params[$i]['ID']};
        global ${"linked_property".$params[$i]['ID']};
        global ${"linked_method".$params[$i]['ID']};
        $params[$i]['LINKED_OBJECT']=${"linked_object".$params[$i]['ID']};
        $params[$i]['LINKED_PROPERTY']=${"linked_property".$params[$i]['ID']};
        $params[$i]['LINKED_METHOD']=${"linked_method".$params[$i]['ID']};
        SQLUpdate('events_params',$params[$i]);
    }
}
$out['PARAMS']=$params;

if ($rec['ID'] && $rec['ADDED']!='') {
    $events=SQLSelect("SELECT ID, EVENT_NAME,ADDED FROM events WHERE ABS(UNIX_TIMESTAMP(ADDED)-UNIX_TIMESTAMP(('".$rec['ADDED']."')))<=5 AND ID!=".$rec['ID']);
    if ($events[0]['ID']) {
        $out['RELEVANT']=$events;
    }
}