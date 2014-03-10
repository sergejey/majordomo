<?php

  
  function context_getuser() {
   global $session;
   if ($session->data['SITE_USER_ID']) {
    return $session->data['SITE_USER_ID'];
   }
   $user=SQLSelectOne("SELECT ID FROM users WHERE IS_DEFAULT=1");
   $session->data['SITE_USER_ID']=$user['ID'];
   return (int)$user['ID'];
  }

  function context_getcurrent() {
   $user_id=context_getuser();
   $user=SQLSelectOne("SELECT ID, ACTIVE_CONTEXT_ID FROM users WHERE ID='".(int)$user_id."'");
   if (!$user['ID']) {
    return 0;
   }
   return (int)$user['ACTIVE_CONTEXT_ID'];
  }

  function context_clear() {
   $user_id=context_getuser();
   $user=SQLSelectOne("SELECT * FROM users WHERE ID='".(int)$user_id."'");
   $user['ACTIVE_CONTEXT_ID']=0;
   SQLUpdate('users', $user);
  }

  function context_activate($id) {
   $user_id=context_getuser();
   $user=SQLSelectOne("SELECT * FROM users WHERE ID='".(int)$user_id."'");
   $user['ACTIVE_CONTEXT_ID']=$id;
   $user['ACTIVE_CONTEXT_UPDATED']=date('Y-m-d H:i:s');
   SQLUpdate('users', $user);
   if ($id) {
    //execute pattern
    $context=SQLSelectOne("SELECT * FROM patterns WHERE ID='".(int)$id."'");
    $timeout=$context['TIMEOUT'];
    if (!$timeout) {
     $timeout=60;
    }
    setTimeOut('user_'.$user_id.'_contexttimeout', 'context_timeout('.$context['ID'].', '.$user_id.');', $timeout);
   } else {
    clearTimeOut('user_'.$user_id.'_contexttimeout');
   }
  }


  function context_timeout($id, $user_id) {
   global $session;

   $user=SQLSelectOne("SELECT * FROM users WHERE ID='".(int)$user_id."'");
   $session->data['SITE_USER_ID']=$user['ID'];

   $context=SQLSelectOne("SELECT * FROM patterns WHERE ID='".(int)$id."'");
   if ($context['TIMEOUT_SCRIPT']) {
                 try {
                   $code=$context['TIMEOUT_SCRIPT'];
                   $success=eval($code);
                   if ($success===false) {
                    DebMes("Error in context timeout code: ".$code);
                   }
                  } catch(Exception $e){
                   DebMes('Error: exception '.get_class($e).', '.$e->getMessage().'.');
                  }
   }
   context_activate((int)$context['TIMEOUT_CONTEXT_ID']);
  }


?>