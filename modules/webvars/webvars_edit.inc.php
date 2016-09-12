<?php
/*
* @version 0.3 (wizard)
*/
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='webvars';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'HOSTNAME' (varchar)
   global $hostname;
   $rec['HOSTNAME']=$hostname;

   if (!preg_match('/^http:/is', $rec['HOSTNAME']) && !preg_match('/^https:/is', $rec['HOSTNAME']) && !preg_match('/\%/is', $rec['HOSTNAME'])) {
    $out['ERR_HOSTNAME']=1;
    $ok=0;
   }

   global $title;
   $rec['TITLE']=$title;


  //updating 'SEARCH_WORD' (varchar)
   global $search_pattern;
   $rec['SEARCH_PATTERN']=trim($search_pattern);

   global $check_pattern;
   $rec['CHECK_PATTERN']=trim($check_pattern);

   global $encoding;
   $rec['ENCODING']=trim($encoding);

   global $auth;
   $rec['AUTH']=(int)$auth;

   global $username;
   $rec['USERNAME']=$username;

   global $password;
   $rec['PASSWORD']=$password;


    global $linked_object;
    $rec['LINKED_OBJECT']=trim($linked_object);
    global $linked_property;
    $rec['LINKED_PROPERTY']=trim($linked_property);



  //updating 'SCRIPT_ID_ONLINE' (int)
  //updating 'CODE_ONLINE' (text)
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
    $rec['LATEST_VALUE']='';
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
  $out['LOG']=nl2br($out['LOG']);

  $out['SCRIPTS']=SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");

?>