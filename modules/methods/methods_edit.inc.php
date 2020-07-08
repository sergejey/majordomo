<?php
/*
* @version 0.1 (auto-set)
*/
//Code editor settings
	if(defined('SETTINGS_CODEEDITOR_TURNONSETTINGS')) {
		$out['SETTINGS_CODEEDITOR_TURNONSETTINGS'] = SETTINGS_CODEEDITOR_TURNONSETTINGS;
		$out['SETTINGS_CODEEDITOR_UPTOLINE'] = SETTINGS_CODEEDITOR_UPTOLINE;
		$out['SETTINGS_CODEEDITOR_SHOWERROR'] = SETTINGS_CODEEDITOR_SHOWERROR;
	}
	
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='methods';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'Object ID' (int)
   if (IsSet($this->object_id)) {
    $rec['OBJECT_ID']=$this->object_id;
   } else {
   global $object_id;
   $rec['OBJECT_ID']=(int)$object_id;
   }
  //updating 'Class ID' (int, required)
   if (IsSet($this->class_id)) {
    $rec['CLASS_ID']=$this->class_id;
   } else {
   global $class_id;
   $rec['CLASS_ID']=(int)$class_id;
   /*
   if (!$rec['CLASS_ID']) {
    $out['ERR_CLASS_ID']=1;
    $ok=0;
   }
   */
   }
  //updating 'Titile' (varchar, required)
   $rec['TITLE']=gr('title','trim');
   $rec['TITLE']=str_replace(' ','',$rec['TITLE']);
   if ($rec['TITLE']=='') {
    $out['ERR_TITLE']=1;
    $ok=0;
   }

   global $call_parent;
   $rec['CALL_PARENT']=(int)$call_parent;

  //updating 'Description' (text)
   global $description;
   $rec['DESCRIPTION']=$description;
  //updating 'Code' (text)
   global $code;
   $old_code=$rec['CODE'];
    $rec['CODE'] = $code;

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
            $out['ERR_LINE'] = preg_replace('/[^0-9]/', '', substr(stristr($errors, 'php on line '), 0, 18))-2;
            $out['ERR_CODE'] = 1;
			if($out['ERR_LINE'] != '-2') {
				$errorStr = explode('Parse error: ', str_replace("'", '', strip_tags(nl2br($errors))));
				$errorStr = explode('Errors parsing', $errorStr[1]);
				$errorStr = explode(' in ', $errorStr[0]);
				$out['ERRORS'] = $errorStr[0];
				$out['ERR_FULL'] = $errorStr[0].' '.$errorStr[1];
				$out['ERR_OLD_CODE'] = $old_code;
			} else {
				$out['ERRORS'] = $errors;
			}
            $ok = 0;
        }
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
  if (is_array($rec)) {
   foreach($rec as $k=>$v) {
    if (!is_array($v)) {
     $rec[$k]=htmlspecialchars($v);
    }
   }
  }
  outHash($rec, $out);

  global $overwrite;
  if ($overwrite) {
   $tmp=SQLSelectOne("SELECT * FROM methods WHERE ID='".(int)$overwrite."'");
   unset($tmp['ID']);
   foreach($tmp as $k=>$v) {
    $out[$k]=htmlspecialchars($v);
   }
  }

  $out['SCRIPTS']=SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");


?>