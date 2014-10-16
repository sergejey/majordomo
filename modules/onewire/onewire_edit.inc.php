<?php
/*
* @version 0.2 (wizard)
*/

  if ($this->mode=='setvalue') {
   global $prop_id;
   global $new_value;
   global $id;
   $this->setProperty($prop_id, $new_value);
   $this->redirect("?id=".$id."&view_mode=".$this->view_mode."&edit_mode=".$this->edit_mode);
  }

  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='owdevices';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'HOSTNAME' (varchar)

   global $title;
   $rec['TITLE']=$title;



   global $code;
   $rec['CODE']=$code;

   global $run_type;

       if ($run_type=='script') {
        global $script_id;
        $rec['SCRIPT_ID']=$script_id;
       } else {
        $rec['SCRIPT_ID']=0;
       }


   if ($rec['CODE']!='' && $run_type=='code') {
    //echo $content;
    $errors=php_syntax_error($code);
    if ($errors) {
     $out['ERR_CODE']=1;
     $out['ERRORS']=nl2br($errors);
     $ok=0;
    }
   }



  //updating 'ONLINE_INTERVAL' (int)
   global $online_interval;
   $rec['ONLINE_INTERVAL']=(int)$online_interval;
  //UPDATING RECORD
   if ($ok) {
    $rec['STATUS']=0;
    $rec['CHECK_LATEST']='';
    $rec['CHECK_NEXT']=date('Y-m-d H:i:s');
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

  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);
  $out['LOG']=nl2br($out['LOG']);

  if ($rec['ID']) {
   $properties=SQLSelect("SELECT * FROM owproperties WHERE DEVICE_ID='".$rec['ID']."' ORDER BY SYSNAME");
   if ($this->mode=='update') {
    $total=count($properties);
    for($i=0;$i<$total;$i++) {
     global ${'linked_object'.$properties[$i]['ID']};
     global ${'linked_property'.$properties[$i]['ID']};

     $old_linked_object=$properties[$i]['LINKED_OBJECT'];
     $old_linked_property=$properties[$i]['LINKED_PROPERTY'];

     if (${'linked_object'.$properties[$i]['ID']} && ${'linked_property'.$properties[$i]['ID']}) {
      $properties[$i]['LINKED_OBJECT']=${'linked_object'.$properties[$i]['ID']};
      $properties[$i]['LINKED_PROPERTY']=${'linked_property'.$properties[$i]['ID']};
      SQLUpdate('owproperties', $properties[$i]);
     } elseif ($properties[$i]['LINKED_OBJECT'] || $properties[$i]['LINKED_PROPERTY']) {
      $properties[$i]['LINKED_OBJECT']='';
      $properties[$i]['LINKED_PROPERTY']='';
      SQLUpdate('owproperties', $properties[$i]);
     }

     if ($properties[$i]['LINKED_OBJECT'] && $properties[$i]['LINKED_PROPERTY']) {
      addLinkedProperty($properties[$i]['LINKED_OBJECT'], $properties[$i]['LINKED_PROPERTY'], $this->name);
     }

     if ($old_linked_object && $old_linked_object!=$properties[$i]['LINKED_OBJECT'] && $old_linked_property && $old_linked_property!=$properties[$i]['LINKED_PROPERTY']) {
      removeLinkedProperty($old_linked_object, $old_linked_property, $this->name);
     }



     global ${'starred'.$properties[$i]['ID']};
     if (${'starred'.$properties[$i]['ID']}) {
       $properties[$i]['STARRED']=1;
       SQLUpdate('owproperties', $properties[$i]);
     } else {
       $properties[$i]['STARRED']=0;
       SQLUpdate('owproperties', $properties[$i]);
     }
    }
   }
   $out['PROPERTIES']=$properties;
  }

  $out['SCRIPTS']=SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");

?>