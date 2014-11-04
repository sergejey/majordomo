<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }

  $table_name='zwave_devices';

  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");

   if ($this->tab=='config') {
    include(DIR_MODULES.'zwave/products.php');
     function sortproducts($a, $b) {
      if (!strcmp($a["BRAND"], $b["BRAND"])) {
       return strcmp($a["TITLE"], $b["TITLE"]);
      } else {
       return strcmp($a["BRAND"], $b["BRAND"]);
      }
     }
     usort($products, 'sortproducts');

     if ($this->mode=='update') {
      global $xmlfile;
      if ($xmlfile) {
       $rec['XMLFILE']=$xmlfile;
      } else {
       $rec['XMLFILE']='';
      }
      SQLUpdate($table_name, $rec);
      $out['OK']=1;
     }

     if (!file_exists(ROOT.'cached/'.$rec['XMLFILE']) && preg_match('/^(\d+)-/is', $rec['XMLFILE'], $m)) {
      $url='http://www.pepper1.net/zwavedb/device/'.$m[1].'/'.$rec['XMLFILE'];
      $data=getURL($url, 0);
      if ($data) {
       SaveFile(ROOT.'cached/'.$rec['XMLFILE'], $data);
      }
     }

     if ($rec['XMLFILE'] && file_exists(ROOT.'cached/'.$rec['XMLFILE'])) {
      $data=simplexml_load_file(ROOT.'cached/'.$rec['XMLFILE']);

      if ($data->resourceLinks->deviceImage) {
       $image_url=((string)$data->resourceLinks->deviceImage->attributes()[0]);
       if ($image_url) {
        $out['IMAGE_URL']=$image_url;
       }
      }
      if (preg_match('/^(\d+)-/is', $rec['XMLFILE'], $m)) {
       $out['DETAILS_URL']='http://www.pepper1.net/zwavedb/device/'.$m[1];
      }

      $params=$data->configParams->configParam;
      $total=count($params);
      $res=array();
      for($i=0;$i<$total;$i++) {
       //var_dump($params[$i]);
       $param=array();
       $param['NUMBER']=(int)$params[$i]->attributes()['number'];
       $param['TYPE']=(string)$params[$i]->attributes()['type'];
       $param['SIZE']=(int)$params[$i]->attributes()['size'];
       $param['DEFAULT']=hexdec((string)$params[$i]->attributes()['default']);
       $param['NAME']=(string)$params[$i]->name->lang[1];
       if (isset($params[$i]->description->lang[1])) {
        $param['DESCRIPTION']=(string)$params[$i]->description->lang[1];
       } else {
        $param['DESCRIPTION']=(string)$params[$i]->description->lang;
       }
       if (isset($params[$i]->value[1])) {
        $total_v=count($params[$i]->value);
        //echo "zz";exit;
        for($iv=0;$iv<$total_v;$iv++) {
         $value=$params[$i]->value[$iv];
         $from=hexdec((string)$value->attributes()['from']);
         $to=hexdec((string)$value->attributes()['to']);
         if ($from!=$to) {
          $param['DESCRIPTION'].="\nValue from ".$from." to ".$to;
         } else {
          $param['DESCRIPTION'].="\nValue ".$from;
         }
         if ((string)$value->description->lang[1]) {
          $param['DESCRIPTION'].=" -- ".$value->description->lang[1];
         } elseif ((string)$value->description->lang) {
           $param['DESCRIPTION'].=" -- ".$value->description->lang;
         }
        }
       } else {
        @$from=hexdec((string)$params[$i]->value->attributes()['from']);
        @$to=hexdec((string)$params[$i]->value->attributes()['to']);
        if ($from!=$to) {
         $param['DESCRIPTION'].="\nValue from ".$from." to ".$to;
        } else {
         $param['DESCRIPTION'].="\nValue ".$from;
        }
        if ((string)$params[$i]->value->description->lang[1]) {
         $param['DESCRIPTION'].=" -- ".$params[$i]->value->description->lang[1];
        } elseif ((string)$params[$i]->value->description->lang) {
          $param['DESCRIPTION'].=" -- ".$params[$i]->value->description->lang;
        }
       }
       $param['DESCRIPTION']=nl2br($param['DESCRIPTION']);
       //print_r($param);
       //       echo "zz";exit;
       $res[]=$param;

       if ($this->mode=='update') {
        global ${'config'.$param['NUMBER']};
        if (${'config'.$param['NUMBER']}) {
         //
         $value=${'config'.$param['NUMBER']};
         $data=$this->apiCall('/ZWaveAPI/Run/devices['.$rec['NODE_ID'].'].instances['.$rec['INSTANCE_ID'].'].commandClasses[112].Set('.$param['NUMBER'].','.$value.','.$param['SIZE'].')');
        }
        if (${'configdefault'.$param['NUMBER']}) {
         $data=$this->apiCall('/ZWaveAPI/Run/devices['.$rec['NODE_ID'].'].instances['.$rec['INSTANCE_ID'].'].commandClasses[112].SetDefault('.$param['NUMBER'].')');
        }

        global $custom_config_number;
        global $custom_config_value;
        global $custom_config_size;
        if ($custom_config_number) {
         $data=$this->apiCall('/ZWaveAPI/Run/devices['.$rec['NODE_ID'].'].instances['.$rec['INSTANCE_ID'].'].commandClasses[112].Set('.(int)$custom_config_number.','.(int)$custom_config_value.','.(int)$custom_config_size.')');
        }

       }

      }
      $out['PARAMS']=$res;
      //print_r($res);exit;
      //exit;
     }

     $out['PRODUCTS']=$products;
   }


  if ($this->mode=='update') {
   $ok=1;
  //updating 'TITLE' (varchar, required)


   if ($this->tab=='') {


   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
  //updating 'LOCATION_ID' (select)
   if (IsSet($this->location_id)) {
    $rec['LOCATION_ID']=$this->location_id;
   } else {
   global $location_id;
   $rec['LOCATION_ID']=$location_id;
   }
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

  }

  if ($rec['LATEST_UPDATE']!='') {
   $tmp=explode(' ', $rec['LATEST_UPDATE']);
   $out['LATEST_UPDATE_DATE']=fromDBDate($tmp[0]);
   $tmp2=explode(':', $tmp[1]);
   $latest_update_hours=$tmp2[0];
   $latest_update_minutes=$tmp2[1];
  }
  for($i=0;$i<60;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$latest_update_minutes) {
    $out['LATEST_UPDATE_MINUTES'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['LATEST_UPDATE_MINUTES'][]=array('TITLE'=>$title);
   }
  }
  for($i=0;$i<24;$i++) {
   $title=$i;
   if ($i<10) $title="0$i";
   if ($title==$latest_update_hours) {
    $out['LATEST_UPDATE_HOURS'][]=array('TITLE'=>$title, 'SELECTED'=>1);
   } else {
    $out['LATEST_UPDATE_HOURS'][]=array('TITLE'=>$title);
   }
  }
  //options for 'LOCATION_ID' (select)
  $tmp=SQLSelect("SELECT ID, TITLE FROM locations ORDER BY TITLE");
  $locations_total=count($tmp);
  for($locations_i=0;$locations_i<$locations_total;$locations_i++) {
   $location_id_opt[$tmp[$locations_i]['ID']]=$tmp[$locations_i]['TITLE'];
  }
  for($i=0;$i<count($tmp);$i++) {
   if ($rec['LOCATION_ID']==$tmp[$i]['ID']) $tmp[$i]['SELECTED']=1;
  }
  $out['LOCATION_ID_OPTIONS']=$tmp;

  if ($rec['ID'] && $this->tab=='') {
   $this->pollDevice($rec['ID']);
   $rec=SQLSelectOne("SELECT * FROM zwave_devices WHERE ID='".$rec['ID']."'");

   $properties=SQLSelect("SELECT * FROM zwave_properties WHERE DEVICE_ID='".$rec['ID']."'");
   if ($properties) {
    if ($this->mode=='update') {
     $total=count($properties);
     for($i=0;$i<$total;$i++) {
      global ${'delete_property'.$properties[$i]['ID']};
      if (${'delete_property'.$properties[$i]['ID']}) {
       SQLExec("DELETE FROM zwave_properties WHERE ID=".(int)$properties[$i]['ID']);
       continue;
      }
      global ${'linked_object'.$properties[$i]['ID']};
      global ${'linked_property'.$properties[$i]['ID']};
      global ${'linked_method'.$properties[$i]['ID']};

      $old_linked_object=$properties[$i]['LINKED_OBJECT'];
      $old_linked_property=$properties[$i]['LINKED_PROPERTY'];

      $properties[$i]['LINKED_OBJECT']=trim(${'linked_object'.$properties[$i]['ID']});
      $properties[$i]['LINKED_PROPERTY']=trim(${'linked_property'.$properties[$i]['ID']});
      $properties[$i]['LINKED_METHOD']=trim(${'linked_method'.$properties[$i]['ID']});

      global ${'update_period'.$properties[$i]['ID']};
      $properties[$i]['UPDATE_PERIOD']=(int)${'update_period'.$properties[$i]['ID']};

      global ${'validate'.$properties[$i]['ID']};
      $properties[$i]['VALIDATE']=(int)${'validate'.$properties[$i]['ID']};

      global ${'valid_from'.$properties[$i]['ID']};
      $properties[$i]['VALID_FROM']=(float)${'valid_from'.$properties[$i]['ID']};

      global ${'valid_to'.$properties[$i]['ID']};
      $properties[$i]['VALID_TO']=(float)${'valid_to'.$properties[$i]['ID']};

      global ${'correct_value'.$properties[$i]['ID']};
      if (${'correct_value'.$properties[$i]['ID']}!='') {
       $properties[$i]['CORRECT_VALUE']=${'correct_value'.$properties[$i]['ID']};
      } else {
       $properties[$i]['CORRECT_VALUE']='';
      }

      SQLUpdate('zwave_properties', $properties[$i]);

      if ($old_linked_object && $old_linked_object!=$properties[$i]['LINKED_OBJECT'] && $old_linked_property && $old_linked_property!=$properties[$i]['LINKED_PROPERTY']) {
       removeLinkedProperty($old_linked_object, $old_linked_property, $this->name);
       //DebMes("Removing linked property ".$old_linked_object.".".$old_linked_property);
      }
      if ($properties[$i]['LINKED_OBJECT'] && $properties[$i]['LINKED_PROPERTY']) {
       addLinkedProperty($properties[$i]['LINKED_OBJECT'], $properties[$i]['LINKED_PROPERTY'], $this->name);
       //DebMes("Adding linked property ".$properties[$i]['LINKED_OBJECT'].".".$properties[$i]['LINKED_PROPERTY']);
      }

     }
     $properties=SQLSelect("SELECT * FROM zwave_properties WHERE DEVICE_ID='".$rec['ID']."'");
    }
    $out['PROPERTIES']=$properties;
   }
  }


  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }

  if ($rec['RAW_DATA']) {
   $rec['RAW_DATA']=$this->prettyPrint($rec['RAW_DATA']);
  }

  outHash($rec, $out);



?>