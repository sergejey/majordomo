<?php

/**
 * Summary of context_getuser
 * @return int|string
 */
function context_getuser()
{
   global $context_user_id;
   if ($context_user_id) {
      return $context_user_id;
   }

   global $session;
   if ($session->data['SITE_USER_ID']) {
      return (int)$session->data['SITE_USER_ID'];
   }

   $user = SQLSelectOne("SELECT ID FROM users WHERE IS_DEFAULT=1");
   $session->data['SITE_USER_ID'] = $user['ID'];
   
   return (int)$user['ID'];
}

/**
 * Summary of context_getcurrent
 * @return int|string
 */
function context_getcurrent($from_user_id = 0)
{
   if (!$from_user_id) {
      $from_user_id = context_getuser();
   }
   $sqlQuery = "SELECT ID, ACTIVE_CONTEXT_ID, ACTIVE_CONTEXT_EXTERNAL
                  FROM users
                 WHERE ID = '" . (int)$from_user_id . "'";
   $user = SQLSelectOne($sqlQuery);
   if (!$user['ID'])
      return 0;
   if ($user['ACTIVE_CONTEXT_EXTERNAL'])
   {
      return 'ext' . (int)$user['ACTIVE_CONTEXT_ID'];
   }
   else
   {
      return (int)$user['ACTIVE_CONTEXT_ID'];
   }
}

/**
 * Summary of context_get_history
 * @return mixed
 */
function context_get_history($user_id = 0)
{
   if (!$user_id) {
      $user_id = context_getuser();
   }
   $sqlQuery = "SELECT ID, ACTIVE_CONTEXT_ID, ACTIVE_CONTEXT_EXTERNAL, ACTIVE_CONTEXT_HISTORY
                  FROM users
                 WHERE ID = '" . (int)$user_id . "'";
   
   $user = SQLSelectOne($sqlQuery);

   if ($user['ACTIVE_CONTEXT_ID'])
      return $user['ACTIVE_CONTEXT_HISTORY'];

   return '';
}

/**
 * Summary of context_clear
 * @return void
 */
function context_clear($user_id=0)
{

   if (!$user_id) {
      $user_id = context_getuser();
   }

   $user = SQLSelectOne("SELECT * FROM users WHERE ID = '" . (int)$user_id . "'");
   
   $user['ACTIVE_CONTEXT_ID']       = 0;
   $user['ACTIVE_CONTEXT_EXTERNAL'] = 0;
   $user['ACTIVE_CONTEXT_UPDATED']  = date('Y-m-d H:i:s');
   $user['ACTIVE_CONTEXT_HISTORY']  = '';
   
   SQLUpdate('users', $user);
}

/**
 * Summary of context_activate
 * @param mixed $id        ID
 * @param mixed $no_action No action (defailt 0)
 * @param mixed $history   History (default '')
 * @return void
 */
function context_activate($id, $no_action = 0, $history = '', $user_id = 0)
{
   if (!$user_id) {
      $user_id = context_getuser();
   }
   $user    = SQLSelectOne("SELECT * FROM users WHERE ID = '" . (int)$user_id . "'");
   
   $user['ACTIVE_CONTEXT_ID']       = $id;
   $user['ACTIVE_CONTEXT_EXTERNAL'] = 0;
   $user['ACTIVE_CONTEXT_UPDATED']  = date('Y-m-d H:i:s');
   
   if ($history)
      $user['ACTIVE_CONTEXT_HISTORY'] .= ' ' . $history;

   SQLUpdate('users', $user);

   if ($id)
   {
      //execute pattern
      $context = SQLSelectOne("SELECT * FROM patterns WHERE ID = '" . (int)$id . "'");
      $timeout = $context['TIMEOUT'];
      
      if (!$timeout)
         $timeout = 60;

      $timeoutTitle   = 'user_' . $user_id . '_contexttimeout';
      $timeoutCommand = 'context_timeout(' . (int)$context['ID'] . ', ' . $user_id . ');';
      setTimeOut($timeoutTitle, $timeoutCommand, $timeout);
      
      if (!$no_action)
      {
         include_once(DIR_MODULES . 'patterns/patterns.class.php');
         $pt = new patterns();
         $pt->runPatternAction((int)$context['ID']);
      }
   }
   else
   {
      context_clear($user_id);
      clearTimeOut('user_' . $user_id . '_contexttimeout');
   }
}

/**
 * Summary of context_activate_ext
 * @param mixed $id                 ID
 * @param mixed $timeout            Timeout (default 0)
 * @param mixed $timeout_code       Timeout code (default '')
 * @param mixed $timeout_context_id Timeout context id (default 0)
 * @return void
 */
function context_activate_ext($id, $timeout = 0, $timeout_code = '', $timeout_context_id = 0, $user_id = 0)
{

   if (!$user_id) {
      $user_id = context_getuser();
   }
   $user    = SQLSelectOne("SELECT * FROM users WHERE ID = '" . (int)$user_id . "'");
   
   $user['ACTIVE_CONTEXT_ID']       = $id;
   $user['ACTIVE_CONTEXT_EXTERNAL'] = ($id) ? 1 : 0;
   $user['ACTIVE_CONTEXT_UPDATED']  = date('Y-m-d H:i:s');

   DebMes("setting external context: " . $id);
   SQLUpdate('users', $user);
   
   if ($id)
   {
      //execute pattern
      if (!$timeout)
         $timeout = 60;
      
      $ev = '';

      if ($timeout_code)
         $ev .= $timeout_code;

      if ($timeout_context_id)
      {
         $ev .= "context_activate_ext(" . (int)$timeout_context_id . ");";
      }
      else
      {
         $ev .= "context_clear();";
      }

      setTimeOut('user_' . $user_id . '_contexttimeout', $ev, $timeout);
   }
   else
   {
      context_clear($user_id);
      clearTimeOut('user_' . $user_id . '_contexttimeout');
   }
}

/**
 * Summary of context_timeout
 * @param mixed $id      ID
 * @param mixed $user_id User ID
 * @return void
 */
function context_timeout($id, $user_id = 0)
{

   if (!$user_id) {
      $user_id = context_getuser();
   } else {
      global $context_user_id;
      $context_user_id = $user_id;
   }
   //global $session;
   //$user = SQLSelectOne("SELECT * FROM users WHERE ID = '" . (int)$user_id . "'");
   //$session->data['SITE_USER_ID'] = $user['ID'];

   $context = SQLSelectOne("SELECT * FROM patterns WHERE ID = '" . (int)$id . "'");
   
   if (!$context['TIMEOUT_CONTEXT_ID']) context_activate(0,0,'',$user_id);

   if ($context['TIMEOUT_SCRIPT'])
   {
      try
      {
         $code    = $context['TIMEOUT_SCRIPT'];
         $success = eval($code);
         
         if ($success === false)
         {
            DebMes("Error in context timeout code: " . $code);
            registerError('context_timeout_action', "Error in context timeout code: " . $code);
         }
      }
      catch (Exception $e)
      {
         DebMes('Error: exception ' . get_class($e) . ', ' . $e->getMessage() . '.');
         registerError('context_timeout_action', get_class($e) . ', ' . $e->getMessage());
      }
   }

   if ($context['TIMEOUT_CONTEXT_ID'])
      context_activate((int)$context['TIMEOUT_CONTEXT_ID'],0,'',$user_id);
}

/**
 * Summary of addPattern
 * @param mixed $title     Title
 * @param mixed $options   Options (default array())
 * @param mixed $overwrite OverWrite (default 0)
 * @return void
 */
function addPattern($title, $options = array(), $overwrite = 0)
{
   $old = SQLSelectOne("SELECT ID FROM patterns WHERE TITLE LIKE '" . DBSafe($title) . "'");
   
   if ($old['ID'])
   {
      if ($overwrite)
      {
         SQLExec("DELETE FROM patterns WHERE ID = '" . $old['ID'] . "'");
      }
      else
      {
         return;
      }
   }

   $rec          = array();
   $rec['TITLE'] = $title;

   foreach ($options as $k => $v)
   {
      $rec[$k] = $v;
   }

   SQLInsert('patterns', $rec);
}
