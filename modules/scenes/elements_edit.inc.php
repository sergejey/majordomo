<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='elements';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");


  if ($this->mode=='update') {
   $ok=1;
  //updating 'SCENE_ID' (select)
   if (IsSet($this->scene_id)) {
    $rec['SCENE_ID']=$this->scene_id;
   } else {
   global $scene_id;
   $rec['SCENE_ID']=$scene_id;
   }
  //updating 'TITLE' (varchar, required)
   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
  //updating 'TYPE' (select)
   global $type;
   $rec['TYPE']=$type;
  //updating 'TOP' (int)
   global $top;
   $rec['TOP']=(int)$top;
  //updating 'LEFT' (int)
   global $left;
   $rec['LEFT']=(int)$left;
  //updating 'WIDTH' (int)
   global $width;
   $rec['WIDTH']=(int)$width;
  //updating 'HEIGHT' (int)
   global $height;
   $rec['HEIGHT']=(int)$height;

   global $d3d_scene;
   $rec['D3D_SCENE']=$d3d_scene;


   global $background;
   $rec['BACKGROUND']=(int)$background;

  //updating 'CROSS_SCENE' (int)
   global $cross_scene;
   $rec['CROSS_SCENE']=(int)$cross_scene;

   global $smart_repeat;
   $rec['SMART_REPEAT']=(int)$smart_repeat;


  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }
    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }
  //options for 'SCENE_ID' (select)
  $tmp=SQLSelect("SELECT ID, TITLE FROM scenes ORDER BY TITLE");
  $scenes_total=count($tmp);
  for($scenes_i=0;$scenes_i<$scenes_total;$scenes_i++) {
   $scene_id_opt[$tmp[$scenes_i]['ID']]=$tmp[$scenes_i]['TITLE'];
  }
  for($i=0;$i<$scenes_total;$i++) {
   if ($rec['SCENE_ID']==$tmp[$i]['ID']) $tmp[$i]['SELECTED']=1;
  }
  $out['SCENE_ID_OPTIONS']=$tmp;
  //options for 'TYPE' (select)
  $tmp=explode('|', DEF_TYPE_OPTIONS);
  foreach($tmp as $v) {
   if (preg_match('/(.+)=(.+)/', $v, $matches)) {
    $value=$matches[1];
    $title=$matches[2];
   } else {
    $value=$v;
    $title=$v;
   }
   $out['TYPE_OPTIONS'][]=array('VALUE'=>$value, 'TITLE'=>$title);
   $type_opt[$value]=$title;
  }

  $optionsTypeCnt = count($out['TYPE_OPTIONS']);
  for ($i = 0; $i < $optionsTypeCnt; $i++)
  {
      if ($out['TYPE_OPTIONS'][$i]['VALUE'] == $rec['TYPE'])
      {
         $out['TYPE_OPTIONS'][$i]['SELECTED'] = 1;
         $out['TYPE'] = $out['TYPE_OPTIONS'][$i]['TITLE'];
         $rec['TYPE'] = $out['TYPE_OPTIONS'][$i]['TITLE'];
      }
   }

  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);


?>