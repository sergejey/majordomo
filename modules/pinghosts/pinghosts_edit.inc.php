<?php
if(defined('SETTINGS_CODEEDITOR_TURNONSETTINGS')) {
	$out['SETTINGS_CODEEDITOR_TURNONSETTINGS'] = SETTINGS_CODEEDITOR_TURNONSETTINGS;
	$out['SETTINGS_CODEEDITOR_UPTOLINE'] = SETTINGS_CODEEDITOR_UPTOLINE;
	$out['SETTINGS_CODEEDITOR_SHOWERROR'] = SETTINGS_CODEEDITOR_SHOWERROR;
}


  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='pinghosts';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'HOSTNAME' (varchar)
   global $hostname;
   $rec['HOSTNAME']=$hostname;

   if (!$rec['HOSTNAME']) {
    $out['ERR_HOSTNAME']=1;
    $ok=0;
   }

   global $title;
   $rec['TITLE']=$title;


  //updating 'HOST TYPE' (select)
   global $type;
   $rec['TYPE']=$type;
  //updating 'STATUS' (int)

  //updating 'SEARCH_WORD' (varchar)
  if ($rec['TYPE']) {
   global $search_word;
   $rec['SEARCH_WORD']=trim($search_word);
  }

  global $counter_required;
  $rec['COUNTER_REQUIRED']=(int)$counter_required;

  global $linked_object;
  global $linked_property;
  $rec['LINKED_OBJECT']=trim($linked_object);
  $rec['LINKED_PROPERTY']=trim($linked_property);

  //updating 'SCRIPT_ID_ONLINE' (int)
  //updating 'CODE_ONLINE' (text)
   global $code_online;
   $rec['CODE_ONLINE']=$code_online;

   global $code_offline;
   $rec['CODE_OFFLINE']=$code_offline;

   global $run_type_online;

       if ($run_type_online=='script') {
        global $script_id_online;
        $rec['SCRIPT_ID_ONLINE']=$script_id_online;
       } else {
        $rec['SCRIPT_ID_ONLINE']=0;
       }


   if ($rec['CODE_ONLINE']!='' && $run_type_online=='code') {

	$errors = php_syntax_error($code_online);
	if ($errors) {
		$out['ERR_LINE_ONLINE'] = preg_replace('/[^0-9]/', '', substr(stristr($errors, 'php on line '), 0, 18))-2;
		$out['ERR_CODE_ONLINE'] = 1;
		$errorStr = explode('Parse error: ', htmlspecialchars(strip_tags(nl2br($errors))));
		$errorStr = explode('Errors parsing', $errorStr[1]);
		$errorStr = explode(' in ', $errorStr[0]);
		//var_dump($errorStr);
		$out['ERRORS_ONLINE'] = $errorStr[0];
		$out['ERR_FULL_ONLINE'] = $errorStr[0].' '.$errorStr[1];
		$out['ERR_OLD_CODE_ONLINE'] = $old_code;
		$out['ERR_CODE_ONLINE']=1;
		$out['ERRORS_ONLINE']=nl2br($errors);
		$ok=0;
	}
   }


   global $run_type_offline;

       if ($run_type_offline=='script') {
        global $script_id_offline;
        $rec['SCRIPT_ID_OFFLINE']=$script_id_offline;
       } else {
        $rec['SCRIPT_ID_OFFLINE']=0;
       }


   if ($rec['CODE_OFFLINE']!='' && $run_type_offline=='code') {
	$errors = php_syntax_error($code_offline);
	if ($errors) {
		$out['ERR_LINE_OFFLINE'] = preg_replace('/[^0-9]/', '', substr(stristr($errors, 'php on line '), 0, 18))-2;
		$out['ERR_CODE_OFFLINE'] = 1;
		$errorStr = explode('Parse error: ', htmlspecialchars(strip_tags(nl2br($errors))));
		$errorStr = explode('Errors parsing', $errorStr[1]);
		$errorStr = explode(' in ', $errorStr[0]);
		//var_dump($errorStr);
		$out['ERRORS_OFFLINE'] = $errorStr[0];
		$out['ERR_FULL_OFFLINE'] = $errorStr[0].' '.$errorStr[1];
		$out['ERR_OLD_CODE_OFFLINE'] = $old_code;
		$out['ERR_CODE_OFFLINE']=1;
		$out['ERRORS_OFFLINE']=nl2br($errors);
		$ok=0;
	}
	
	
   }



  //updating 'OFFLINE_INTERVAL' (int)
   global $offline_interval;
   $rec['OFFLINE_INTERVAL']=(int)$offline_interval;
  //updating 'ONLINE_INTERVAL' (int)
   global $online_interval;
   $rec['ONLINE_INTERVAL']=(int)$online_interval;
  //UPDATING RECORD
   if ($ok) {
    $rec['STATUS']=0;
    $rec['CHECK_LATEST']=date('Y-m-d H:i:s');
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
  //options for 'HOST TYPE' (select)
  $tmp=explode('|', PING_TYPE_OPTIONS);
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
  for ($i = 0; $i < $optionsTypeCnt;$i++)
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
  $out['LOG']=nl2br($out['LOG']);

  $out['SCRIPTS']=SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");

?>
