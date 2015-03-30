<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='knxdevices';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'TITLE' (varchar, required)
   global $title;
   $rec['TITLE']=$title;
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }
  //updating 'TYPE' (varchar)
   global $type;
   $rec['TYPE']=$type;
  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record

     if ($type=='custom') {
      $prop=array();
      $prop['DEVICE_ID']=$rec['ID'];
      $prop['TITLE']='Custom 1';
      $prop['DATA_TYPE']='small';
      $prop['ID']=SQLInsert('knxproperties', $prop);
     } elseif ($type=='switch') {

      $prop=array();
      $prop['DEVICE_ID']=$rec['ID'];
      $prop['TITLE']='Set status';
      $prop['CAN_READ']=0;
      $prop['CAN_WRITE']=1;
      $prop['DATA_TYPE']='small';
      $prop['ID']=SQLInsert('knxproperties', $prop);
      $prop=array();
      $prop['DEVICE_ID']=$rec['ID'];
      $prop['TITLE']='Get status';
      $prop['CAN_READ']=1;
      $prop['CAN_WRITE']=0;
      $prop['DATA_TYPE']='small';
      $prop['ID']=SQLInsert('knxproperties', $prop);

     } elseif ($type=='dimmer') {


      $prop=array();
      $prop['DEVICE_ID']=$rec['ID'];
      $prop['TITLE']='Set switch status';
      $prop['CAN_READ']=0;
      $prop['CAN_WRITE']=1;
      $prop['DATA_TYPE']='small';
      $prop['ID']=SQLInsert('knxproperties', $prop);

      $prop=array();
      $prop['DEVICE_ID']=$rec['ID'];
      $prop['TITLE']='Get switch status';
      $prop['CAN_READ']=1;
      $prop['CAN_WRITE']=0;
      $prop['DATA_TYPE']='small';
      $prop['ID']=SQLInsert('knxproperties', $prop);

      $prop=array();
      $prop['DEVICE_ID']=$rec['ID'];
      $prop['TITLE']='Set dimmer status';
      $prop['CAN_READ']=0;
      $prop['CAN_WRITE']=1;
      $prop['DATA_TYPE']='small';
      $prop['ID']=SQLInsert('knxproperties', $prop);

      $prop=array();
      $prop['DEVICE_ID']=$rec['ID'];
      $prop['TITLE']='Set dimmer value';
      $prop['CAN_READ']=0;
      $prop['CAN_WRITE']=1;
      $prop['DATA_TYPE']='p1';
      $prop['ID']=SQLInsert('knxproperties', $prop);

      $prop=array();
      $prop['DEVICE_ID']=$rec['ID'];
      $prop['TITLE']='Get dimmer value';
      $prop['CAN_READ']=1;
      $prop['CAN_WRITE']=0;
      $prop['DATA_TYPE']='p1';
      $prop['ID']=SQLInsert('knxproperties', $prop);



     } elseif ($type=='temp') {
      $prop=array();
      $prop['DEVICE_ID']=$rec['ID'];
      $prop['TITLE']='Set temp.';
      $prop['CAN_READ']=0;
      $prop['CAN_WRITE']=1;
      $prop['DATA_TYPE']='f2';
      $prop['ID']=SQLInsert('knxproperties', $prop);
      $prop=array();
      $prop['DEVICE_ID']=$rec['ID'];
      $prop['TITLE']='Get temp.';
      $prop['CAN_READ']=1;
      $prop['CAN_WRITE']=0;
      $prop['DATA_TYPE']='f2';
      $prop['ID']=SQLInsert('knxproperties', $prop);      
     }
     if ($type!='') {
      $this->redirect("?view_mode=edit_knxdevices&id=".$rec['ID']);
     }

    }
    $out['OK']=1;
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


  if ($rec['ID']) {

   global $delete_property;
   if ($delete_property) {
    SQLExec("DELETE FROM knxproperties WHERE DEVICE_ID='".$rec['ID']."' AND ID='".(int)$delete_property."'");
    $this->redirect("?view_mode=edit_knxdevices&id=".$rec['ID']);
   }

   $properties=SQLSelect("SELECT * FROM knxproperties WHERE DEVICE_ID='".$rec['ID']."'");
   if ($this->mode=='update') {
    $total=count($properties);
    for($i=0;$i<$total;$i++) {
     global ${"address".$properties[$i]['ID']};
     global ${"title".$properties[$i]['ID']};
     global ${"data_type".$properties[$i]['ID']};
     global ${"can_read".$properties[$i]['ID']};
     global ${"can_write".$properties[$i]['ID']};
     global ${"linked_object".$properties[$i]['ID']};
     global ${"linked_property".$properties[$i]['ID']};
     global ${"linked_method".$properties[$i]['ID']};

     $properties[$i]['ADDRESS']=${"address".$properties[$i]['ID']};
     $properties[$i]['TITLE']=${"title".$properties[$i]['ID']};
     $properties[$i]['DATA_TYPE']=${"data_type".$properties[$i]['ID']};
     $properties[$i]['CAN_READ']=(int)${"can_read".$properties[$i]['ID']};
     $properties[$i]['CAN_WRITE']=(int)${"can_write".$properties[$i]['ID']};
     $properties[$i]['LINKED_OBJECT']=${"linked_object".$properties[$i]['ID']};
     $properties[$i]['LINKED_PROPERTY']=${"linked_property".$properties[$i]['ID']};
     $properties[$i]['LINKED_METHOD']=${"linked_method".$properties[$i]['ID']};

     if ($properties[$i]['LINKED_OBJECT'] && $properties[$i]['LINKED_PROPERTY']) {
      addLinkedProperty($properties[$i]['LINKED_OBJECT'], $properties[$i]['LINKED_PROPERTY'], $this->name);
     }

     SQLUpdate('knxproperties', $properties[$i]);

     $this->getProperty($properties[$i]['ID']);
    }

    global $new_address;
    if ($new_address) {

     $prop=array();
     $prop['DEVICE_ID']=$rec['ID'];
     $prop['TITLE']='Custom 1';
     $prop['DATA_TYPE']='small';
     $prop['ADDRESS']=trim($new_address);
     $prop['ID']=SQLInsert('knxproperties', $prop);

     $this->redirect("?view_mode=edit_knxdevices&id=".$rec['ID']);
    }

   } else {

    $total=count($properties);
    for($i=0;$i<$total;$i++) {
     if ($properties[$i]['CAN_READ']) {
       $this->getProperty($properties[$i]['ID']);
       $properties[$i]=SQLSelectOne("SELECT * FROM knxproperties WHERE ID='".$properties[$i]['ID']."'");
     }
    }

   }
   $out['PROPERTIES']=$properties;
  }
