<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='devices';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");


  if ($this->tab=='logic') {
      $object=getObject($rec['LINKED_OBJECT']);
      $method_id=$object->getMethodByName('logicAction',$object->class_id,$object->id);

      $method_rec=SQLSelectOne("SELECT * FROM methods WHERE ID=".(int)$method_id);

      if ($method_rec['OBJECT_ID']!=$object->id) {
          $method_rec=array();
          $method_rec['OBJECT_ID']=$object->id;
          $method_rec['TITLE']='logicAction';
          $method_rec['ID']=SQLInsert('methods',$method_rec);
      }
      if ($this->mode=='update') {
          global $code;
          $method_rec['CODE']=$code;

          $ok=1;
          if ($method_rec['CODE']!='') {
              //echo $content;
              $errors=php_syntax_error($method_rec['CODE']);
              if ($errors) {
                  $out['ERR_CODE']=1;
                  $out['ERRORS']=nl2br($errors);
                  $ok=0;
              }
          }
          if ($ok) {
              SQLUpdate('methods',$method_rec);
              $out['OK']=1;
          } else {
              $out['ERR']=1;
          }
      }
      $out['CODE']=htmlspecialchars($method_rec['CODE']);
      $out['OBJECT_ID']=$method_rec['OBJECT_ID'];

      $parent_method_id=$object->getMethodByName('logicAction',$object->class_id,0);
      if ($parent_method_id) {
          $out['METHOD_ID']=$parent_method_id;
      } else {
          $out['METHOD_ID']=$method_rec['ID'];
      }

  }

  if ($this->tab=='settings') {
     $properties=$this->getAllProperties($rec['TYPE']);
      //print_r($properties);exit;
     if ($rec['LINKED_OBJECT'] && is_array($properties)) {
         $res_properties=array();
         $onchanges = array();
         foreach($properties as $k=>$v) {
             if ($v['_CONFIG_TYPE']) {
                 if ($this->mode=='update') {
                     global ${$k.'_value'};
                     if (isset(${$k.'_value'})) {
                      setGlobal($rec['LINKED_OBJECT'].'.'.$k,trim(${$k.'_value'}));
                     }
                     $out['OK']=1;
                     if ($v['ONCHANGE']!='') {
                         $onchanges[$v['ONCHANGE']]=1;
                     }
                 }
                 $v['NAME']=$k;
                 if (isset($v['_CONFIG_HELP'])) $v['CONFIG_HELP']=$v['_CONFIG_HELP'];
                 $v['CONFIG_TYPE']=$v['_CONFIG_TYPE'];
                 $v['VALUE']=getGlobal($rec['LINKED_OBJECT'].'.'.$k);
                 if ($v['CONFIG_TYPE']=='select') {
                     $tmp=explode(',',$v['_CONFIG_OPTIONS']);
                     $total = count($tmp);
                     for ($i = 0; $i < $total; $i++) {
                         $data_s=explode('=',trim($tmp[$i]));
                         $value=$data_s[0];
                         if (isset($data_s[1])) {
                             $title=$data_s[1];
                         } else {
                             $title=$value;
                         }
                         $v['OPTIONS'][]=array('VALUE'=>$value,'TITLE'=>$title);
                     }
                 } elseif ($v['CONFIG_TYPE']=='style_image') {
                     include_once(DIR_MODULES.'scenes/scenes.class.php');
                     $scene_class = new scenes();
                     $styles = $scene_class->getAllTypes();
                     $v['FOLDERS']=$styles;
                 }
                 $res_properties[]=$v;
             }
         }
         if ($this->mode=='update') {
             foreach($onchanges as $k=>$v) {
                 callMethod($rec['LINKED_OBJECT'].'.'.$k);
             }
         }
         //print_r($res_properties);exit;
         $out['PROPERTIES']=$res_properties;
     }
     $groups=$this->getAllGroups($rec['TYPE']);

     global $apply_groups;
     if ($this->mode=='update') {
         if (!is_array($apply_groups)) {
             $apply_groups=array();
         }
     } else {
         $apply_groups=array();
     }

     $total = count($groups);

     $object_id=gg($rec['LINKED_OBJECT'].'.object_id');

     if ($total>0) {
         for($i=0;$i<$total;$i++) {
             $property_title='group'.$groups[$i]['SYS_NAME'];
             if ($this->mode=='update') {
                 if (in_array($groups[$i]['SYS_NAME'],$apply_groups)) {
                     sg($rec['LINKED_OBJECT'].'.'.$property_title,1);
                 } elseif (gg($rec['LINKED_OBJECT'].'.'.$property_title)) {
                     sg($rec['LINKED_OBJECT'].'.'.$property_title,0);
                     $property_id=current(SQLSelectOne("SELECT ID FROM properties WHERE OBJECT_ID=".(int)$object_id." AND TITLE='".DBSafe($property_title)."'"));
                     if ($property_id) {
                         SQLExec("DELETE FROM pvalues WHERE PROPERTY_ID=".$property_id." AND OBJECT_ID=".$object_id);
                         SQLExec("DELETE FROM properties WHERE ID=".$property_id);
                     }
                     //echo $property_id;exit;
                 }
             }
             if (gg($rec['LINKED_OBJECT'].'.'.$property_title)){
                 $groups[$i]['SELECTED']=1;
             }
         }
         $out['GROUPS']=$groups;
     }
  }

  if ($this->tab=='interface') {
      if ($this->mode=='update') {
          global $add_menu;
          global $add_menu_id;

          global $add_scene;
          global $add_scene_id;

          if (!$add_scene) {
              $add_scene_id=0;
          }
          if (!$add_scene_id) {
              $add_scene=0;
          }

          $out['ADD_MENU']=$add_menu;
          $out['ADD_MENU_ID']=$add_menu_id;
          $out['ADD_SCENE']=$add_scene;
          $out['ADD_SCENE_ID']=$add_scene_id;

          if ($out['ADD_MENU']) {
              $this->addDeviceToMenu($rec['ID'],$add_menu_id);
          }

          if ($out['ADD_SCENE'] && $out['ADD_SCENE_ID']) {
              $this->addDeviceToScene($rec['ID'],$add_scene_id);
          }

          $out['OK']=1;
      }

      $out['SCENES']=SQLSelect("SELECT ID,TITLE FROM scenes ORDER BY TITLE");
      $menu_items=SQLSelect("SELECT ID, TITLE FROM commands ORDER BY PARENT_ID,TITLE");
      $res_items=array();
      $total = count($menu_items);
      for ($i = 0; $i < $total; $i++) {
          $sub=SQLSelectOne("SELECT ID FROM commands WHERE PARENT_ID=".$menu_items[$i]['ID']);
          if ($sub['ID']) {
              $res_items[]=$menu_items[$i];
          }
      }
      $out['MENU']=$res_items;

  }


  if ($this->tab=='') {
      global $prefix;
      $out['PREFIX']=$prefix;
      global $source_table;
      $out['SOURCE_TABLE']=$source_table;
      global $source_table_id;
      $out['SOURCE_TABLE_ID']=$source_table_id;
      global $type;
      $out['TYPE']=$type;
      global $linked_object;
      if ($linked_object!='') {
          if (!getObject($linked_object)) {
              $linked_object='';
          }
      }
      $out['LINKED_OBJECT']=trim($linked_object);
      if ($out['LINKED_OBJECT'] && !$rec['ID']) {
          $old_rec=SQLSelectOne("SELECT * FROM devices WHERE LINKED_OBJECT LIKE '".DBSafe($out['LINKED_OBJECT'])."'");
          if ($old_rec['ID']) {
              $rec=$old_rec;
          }
      }
      global $add_title;
      if ($add_title) {
          $out['TITLE']=$add_title;
      }

      if ($out['SOURCE_TABLE'] && !$rec['ID']) {
          $qry_devices=1;
          if ($out['TYPE']) {
              $qry_devices.=" AND devices.TYPE='".DBSafe($out['TYPE'])."'";
          }
          $existing_devices=SQLSelect("SELECT ID, TITLE FROM devices WHERE $qry_devices ORDER BY TITLE");
          if ($existing_devices[0]['ID']) {
              $out['SELECT_EXISTING']=1;
              $out['EXISTING_DEVICES']=$existing_devices;
          }
      }


  }

  if ($this->tab=='links') {
      include_once(DIR_MODULES.'devices/devices_links.inc.php');
  }

  if ($this->mode=='update' && $this->tab=='') {
   $ok=1;
   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }

   $rec['TYPE']=$type;
   if ($rec['TYPE']=='') {
    $out['ERR_TYPE']=1;
    $ok=0;
   }

      global $location_id;
      $rec['LOCATION_ID']=(int)$location_id;

      global $favorite;
      $rec['FAVORITE']=(int)$favorite;

    $rec['LINKED_OBJECT']=$linked_object;
      if ($rec['LINKED_OBJECT'] && !$rec['ID']) {
          $other_device=SQLSelectOne("SELECT ID FROM devices WHERE LINKED_OBJECT LIKE '".DBSafe($rec['LINKED_OBJECT'])."'");
          if ($other_device['ID']) {
              $out['ERR_LINKED_OBJECT']=1;
              $ok=0;
          }
      }

      global $add_object;
      $out['ADD_OBJECT']=$add_object;
      if ($add_object) {
          $rec['LINKED_OBJECT']='';
      }
      

  //UPDATING RECORD
   if ($ok) {

    $this->renderStructure();

    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
     $added=1;
    }

    if ($rec['LOCATION_ID']) {
        $location_title=getRoomObjectByLocation($rec['LOCATION_ID'],1);
    }

    $out['OK']=1;

       $type_details=$this->getTypeDetails($rec['TYPE']);
       if (!$rec['LINKED_OBJECT'] && $out['ADD_OBJECT']) {
           $new_object_title=$out['PREFIX'].ucfirst($rec['TYPE']).$this->getNewObjectIndex($type_details['CLASS']);
           $object_id=addClassObject($type_details['CLASS'],$new_object_title,'sdevice'.$rec['ID']);
           $rec['LINKED_OBJECT']=$new_object_title;
           SQLUpdate('devices',$rec);
       }

       $object_id=addClassObject($type_details['CLASS'],$rec['LINKED_OBJECT']);
       $class_id=current(SQLSelectOne("SELECT ID FROM classes WHERE TITLE LIKE '".DBSafe($type_details['CLASS'])."'"));


       $object_rec=SQLSelectOne("SELECT * FROM objects WHERE ID=".$object_id);
       $object_rec['DESCRIPTION']=$rec['TITLE'];
       $object_rec['LOCATION_ID']=$rec['LOCATION_ID'];
       $class_changed=0;
       if ($object_rec['CLASS_ID']!=$class_id) {
           //move object to new class
           $object_rec['CLASS_ID']=$class_id;
           $class_changed=1;
       }
       SQLUpdate('objects',$object_rec);
       if ($class_changed) {
           objectClassChanged($object_rec['ID']);
       }

       if ($location_title) {
           setGlobal($object_rec['TITLE'].'.linkedRoom',$location_title);
       }

       if ($added && $rec['TYPE']=='sensor_temp') {
           setGlobal($object_rec['TITLE'].'.minValue',16);
           setGlobal($object_rec['TITLE'].'.maxValue',25);
       }
       if ($added && $rec['TYPE']=='sensor_humidity') {
           setGlobal($object_rec['TITLE'].'.minValue',30);
           setGlobal($object_rec['TITLE'].'.maxValue',60);
       }

    clearPropertiesCache();

    if ($out['SOURCE_TABLE'] && $out['SOURCE_TABLE_ID']) {
        $this->addDeviceToSourceTable($out['SOURCE_TABLE'], $out['SOURCE_TABLE_ID'], $rec['ID']);
    }

    $this->homebridgeSync();

    if ($added) {
      $this->redirect("?view_mode=edit_devices&id=".$rec['ID']."&tab=settings");
    }


   } else {
    $out['ERR']=1;
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

$show_methods=array();
 if ($rec['TYPE']!='') {
     $methods=$this->getAllMethods($rec['TYPE']);
     if (is_array($methods)) {
         foreach($methods as $k=>$v) {
             if ($v['_CONFIG_SHOW']) {
                 $v['NAME']=$k;
                 $show_methods[]=$v;
             }
         }
     }
 }
 if (isset($show_methods[0])) {
     usort($show_methods,function($a,$b) {
         if ($a['_CONFIG_SHOW'] == $b['_CONFIG_SHOW']) {
             return 0;
         }
         return ($a['_CONFIG_SHOW'] > $b['_CONFIG_SHOW']) ? -1 : 1;
     });
     $out['SHOW_METHODS']=$show_methods;
 }

  $types=array();
  foreach($this->device_types as $k=>$v) {
      if ($v['TITLE']) {
          $types[]=array('NAME'=>$k,'TITLE'=>$v['TITLE']);
      }
  }


if ($rec['LINKED_OBJECT']) {
    $processed=$this->processDevice($rec['ID']);
    $out['HTML']=$processed['HTML'];
}

usort($types,function ($a,$b) {
    return strcmp($a['TITLE'],$b['TITLE']);
});
$out['TYPES']=$types;

$out['LOCATIONS']=SQLSelect("SELECT ID, TITLE FROM locations ORDER BY TITLE");