<?php
/*
* @version 0.2 (auto-set)
*/

  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $table_name='users';
  $rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
  if ($this->mode=='update') {
   $ok=1;
  //updating 'USERNAME' (varchar, required)
   global $username;
   $rec['USERNAME']=$username;
   if ($rec['USERNAME']=='') {
    $out['ERR_USERNAME']=1;
    $ok=0;
   }
  //updating 'NAME' (varchar, required)
   global $name;
   $rec['NAME']=$name;
   if ($rec['NAME']=='') {
    $out['ERR_NAME']=1;
    $ok=0;
   }
  //updating 'EMAIL' (email, required)
   global $email;
   $rec['EMAIL']=$email;
   if ($rec['EMAIL']=='' || !preg_match('/.+?@.+?/is', $rec['EMAIL'])) {
    $out['ERR_EMAIL']=1;
    $ok=0;
   }

   $rec['SKYPE']=gr('skype');
   $rec['MOBILE']=gr('mobile');
   $rec['COLOR']=gr('color');
   $rec['IS_ADMIN']=(int)gr('is_admin');
   $rec['IS_DEFAULT']=(int)gr('is_default');
   if (gr('passwordnew') AND gr('passwordnew') == gr('passwordrepeat')) {
       $rec['PASSWORD']=hash('sha512', gr('passwordnew'));
   } else if (gr('passwordnew') AND gr('passwordnew') != gr('passwordrepeat')) {
       $out['ERR_PASSWORD']=1;
       $ok=0;
   } else if (gr('passwordnew') =='' AND gr('passwordrepeat') == '') {
       $rec['PASSWORD']=hash('sha512', '');
   }
   $rec['LINKED_OBJECT']=trim(gr('linked_object'));
   $rec['HOST']=gr('host');

   global $avatar;
   global $avatar_name;
   if ($avatar!='') {
    if ($rec['AVATAR']!='') {
     @unlink(ROOT.'cms/avatars/'.$rec['AVATAR']);
    }
    $rec['AVATAR']=$rec['ID'].'_'.$avatar_name;
    copy($avatar, ROOT.'cms/avatars/'.$rec['AVATAR']);
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

    $user_title=getUserObjectByTitle($rec['ID'],1);
    
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
?>
