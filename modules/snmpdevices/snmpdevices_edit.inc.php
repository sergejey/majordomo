<?php
/*
* @version 0.1 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='snmpdevices';
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
  //updating 'HOST' (varchar)
   global $host;
   $rec['HOST']=$host;
  //updating 'STATUS' (int)
   //global $status;
   //$rec['STATUS']=(int)$status;
  //updating 'SCRIPT_ID' (select)
   global $script_id;
   $rec['SCRIPT_ID']=$script_id;
  //updating 'CODE' (text)
   global $code;
   $rec['CODE']=$code;

   global $read_community;
   $rec['READ_COMMUNITY']=trim($read_community);
   global $write_community;
   $rec['WRITE_COMMUNITY']=trim($write_community);

  //UPDATING RECORD
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate($table_name, $rec); // update
    } else {
     $new_rec=1;
     $rec['ID']=SQLInsert($table_name, $rec); // adding new record
     $tmp=SQLSelectOne("SELECT COUNT(*) as TOTAL FROM snmpdevices");
     if ($tmp['TOTAL']==1) {
      @SaveFile(ROOT.'reboot'); // first device added, need reboot
     }
    }
   //updating 'MIB_FILE' (file)
   global $mib_file;
   global $mib_file_name;
   global $delete_mib_file;
   if ($mib_file!="" && file_exists($mib_file) && (!$delete_mib_file)) {
     $filename=strtolower(basename($mib_file_name));
     $ext=strtolower(end(explode(".",basename($mib_file_name))));
     if (
         (filesize($mib_file)<=(0*1024) || 0==0)
        ) {
           $filename=$rec["ID"]."_mib_file_".time().".".$ext;
           if ($rec["MIB_FILE"]!='') {
            @Unlink(ROOT.'./cms/snmpdevices/'.$rec["MIB_FILE"]);
           }
           Copy($mib_file, ROOT.'./cms/snmpdevices/'.$filename);
           $rec["MIB_FILE"]=$filename;
           SQLUpdate($table_name, $rec);
          }
   } elseif ($delete_mib_file) {
      @Unlink(ROOT.'./cms/snmpdevices/'.$rec["MIB_FILE"]);
      $rec["MIB_FILE"]='';
      SQLUpdate($table_name, $rec);
   }


  if ($rec['ID']) {

   $properties=SQLSelect("SELECT * FROM snmpproperties WHERE DEVICE_ID='".$rec['ID']."'");
   $total=count($properties);
   for($i=0;$i<$total;$i++) {
    global ${'oid_'.$properties[$i]['ID']};
    global ${'type_'.$properties[$i]['ID']};
    global ${'ptitle_'.$properties[$i]['ID']};
    global ${'pvalue_'.$properties[$i]['ID']};
    global ${'pinterval_'.$properties[$i]['ID']};
    global ${'linked_object_'.$properties[$i]['ID']};
    global ${'linked_property_'.$properties[$i]['ID']};
    if (!${'oid_'.$properties[$i]['ID']}) {
     SQLExec("DELETE FROM snmpproperties WHERE ID='".$properties[$i]['ID']."'");
     continue;
    }
    $prec=$properties[$i];
    $prec['OID']=trim(${'oid_'.$properties[$i]['ID']});
    $prec['TYPE']=${'type_'.$properties[$i]['ID']};
    $prec['TITLE']=trim(${'ptitle_'.$properties[$i]['ID']});
    if ($prec['ONLINE_INTERVAL']!=${'pinterval_'.$properties[$i]['ID']}) {
     $prec['ONLINE_INTERVAL']=(int)${'pinterval_'.$properties[$i]['ID']};
     if ($prec['ONLINE_INTERVAL']) {
      $prec['CHECK_NEXT']=date('Y-m-d H:i:s');
     }
    }

    $old_linked_object=$prec['LINKED_OBJECT'];
    $old_linked_property=$prec['LINKED_PROPERTY'];


    $prec['LINKED_OBJECT']=trim(${'linked_object_'.$properties[$i]['ID']});
    $prec['LINKED_PROPERTY']=trim(${'linked_property_'.$properties[$i]['ID']});

    $value_changed=0;
    if (${'pvalue_'.$properties[$i]['ID']}!=$prec['VALUE']) {
     $value_changed=1;
    }
    SQLUpdate('snmpproperties', $prec);

    if ($prec['LINKED_OBJECT'] && $prec['LINKED_PROPERTY']) {
     addLinkedProperty($prec['LINKED_OBJECT'], $prec['LINKED_PROPERTY'], $this->name);
    }
    if ($old_linked_object && $old_linked_object!=$prec['LINKED_OBJECT'] && $old_linked_property && $old_linked_property!=$prec['LINKED_PROPERTY']) {
     removeLinkedProperty($old_linked_object, $old_linked_property, $this->name);
    }

    if ($value_changed) {
     $this->setProperty($properties[$i]['ID'], trim(${'pvalue_'.$properties[$i]['ID']}));
    }
   }

   global $oid_new;
   global $ptitle_new;
   global $pvalue_new;
   global $type_new;
   global $pinterval_new;
   global $linked_object_new;
   global $linked_property_new;
   if ($oid_new) {
    $prec=array();
    $prec['DEVICE_ID']=$rec['ID'];
    $prec['OID']=trim($oid_new);
    $prec['TITLE']=trim($ptitle_new);
    $prec['TYPE']=$type_new;
    //$prec['VALUE']
    $prec['ONLINE_INTERVAL']=(int)$pinterval_new;
    if ($prec['ONLINE_INTERVAL']) {
     $prec['CHECK_NEXT']=date('Y-m-d H:i:s');
    }
    $prec['LINKED_OBJECT']=trim($linked_object_new);
    $prec['LINKED_PROPERTY']=trim($linked_property_new);
    $prec['ID']=SQLInsert('snmpproperties', $prec);
   }
  }


    $out['OK']=1;
   } else {
    $out['ERR']=1;
   }
  }




  //options for 'SCRIPT_ID' (select)
  $tmp=SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");
  $scripts_total=count($tmp);
  for($scripts_i=0;$scripts_i<$scripts_total;$scripts_i++) {
   $script_id_opt[$tmp[$scripts_i]['ID']]=$tmp[$scripts_i]['TITLE'];
  }
  for($i=0;$i<count($tmp);$i++) {
   if ($rec['SCRIPT_ID']==$tmp[$i]['ID']) $tmp[$i]['SELECTED']=1;
  }
  $out['SCRIPT_ID_OPTIONS']=$tmp;
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);

  if ($rec['ID']) {
   $properties=SQLSelect("SELECT * FROM snmpproperties WHERE DEVICE_ID='".$rec['ID']."'");
   $total=count($properties);
   for($i=0;$i<$total;$i++) {
    $properties[$i]['VALUE']=$this->readProperty($properties[$i]['ID']);
   }
   $out['PROPERTIES']=$properties;
  }

?>