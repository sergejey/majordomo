<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='layouts';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'TITLE' (varchar, required)
   global $title;
   $rec['TITLE']=gr('title','trim');
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
  //updating 'PRIORITY' (int)
   global $priority;
   $rec['PRIORITY']=(int)$priority;

   global $hidden;
   $rec['HIDDEN']=(int)$hidden;


  //updating 'TYPE' (select)
   global $type;
   $rec['TYPE']=$type;
  //updating 'CODE' (text)
   global $code;
   $rec['CODE']=$code;

  //updating 'APP' (varchar)
   global $appname;
   $rec['APP']=$appname.'';
  //updating 'URL' (url)
   global $url;
   $rec['URL']=$url.'';

      $rec['BACKGROUND_IMAGE']=gr('background_image');
      $rec['THEME']=gr('theme');

   global $delete_icon;
   if ($delete_icon) {
    if ($rec['ICON']!='') {
     @unlink(ROOT.'cms/layouts/'.$rec['ICON']);
    }
    $rec['ICON']="";
   }

   global $icon;
   global $icon_name;
   if ($icon!='') {
    if ($rec['ICON']!='') {
     @unlink(ROOT.'cms/layouts/'.$rec['ICON']);
    }
    $rec['ICON']=$rec['ID'].'_'.$icon_name;
    copy($icon, ROOT.'cms/layouts/'.$rec['ICON']);
   }

   global $refresh;
   $rec['REFRESH']=(int)$refresh;

  //updating 'DETAILS' (text)
  /*
   global $details;
   $rec['DETAILS']=$details;
   */
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
    }

    if ($rec['TYPE']=='html') {
     SaveFile(ROOT.'cms/layouts/'.$rec['ID'].'.html', $rec['CODE']);
    }

    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }
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
         $out['TYPE_OPTIONS'][$i]['SELECTED'] = 1;
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