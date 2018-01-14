<?php

/**
 * Summary of subscribeToEvent
 * @param mixed $module_name    Module name
 * @param mixed $event_name     Event name
 * @param mixed $filter_details Filter details (default '')
 * @return void
 */
function subscribeToEvent($module_name, $event_name, $filter_details = '', $priority = 0)
{
   $rec = SQLSelectOne("SELECT * FROM settings WHERE NAME = 'HOOK_EVENT_" . DBSafe(strtoupper($event_name)) . "'");

   if (!$rec['ID'])
   {
      $rec = array();
      
      $rec['NAME']     = 'HOOK_EVENT_' . strtoupper($event_name);
      $rec['TITLE']    = $rec['NAME'];
      $rec['TYPE']     = 'json';
      $rec['NOTES']     = '';
      $rec['DATA']     = '';
      $rec['PRIORITY'] = 0;
      $rec['ID']       = SQLInsert('settings', $rec);
   }

   $data = json_decode($rec['VALUE'], true);
   
   $data[$module_name] = array('priority'=>$priority, 'filter'=>$filter_details);
   $rec['VALUE']       = json_encode($data);
   SQLUpdate('settings', $rec);
}

/**
 * Summary of unsubscribeFromEvent
 * @param mixed $module_name Module name
 * @param mixed $event_name  Event name (default '')
 * @return void
 */
function unsubscribeFromEvent($module_name, $event_name = '')
{
   $sqlQuery = "SELECT *
                  FROM settings
                 WHERE NAME LIKE 'HOOK_EVENT_" . DBSafe(strtoupper($event_name)) . "'
                   AND TYPE = 'json'";

   $rec = SQLSelectOne($sqlQuery);

   if (isset($rec['ID']))
   {
      $data = json_decode($rec['VALUE'], true);

      if (isset($data[$module_name]))
      {
         unset($data[$module_name]);
         $rec['VALUE'] = json_encode($data);
         SQLUpdate('settings', $rec);
      }
   }
}

/**
 * Summary of processSubscriptions
 * @param mixed $event_name Event name
 * @param mixed $details    Details (default '')
 * @return int|void
 */
function processSubscriptions($event_name, $details = '')
{

   postToWebSocketQueue($event_name, $details, 'PostEvent');

   if (!defined('SETTINGS_HOOK_EVENT_' . strtoupper($event_name)))
   {
      return 0;
   }

   $data = json_decode(constant('SETTINGS_HOOK_EVENT_' . strtoupper($event_name)), true);
   //DebMes("Subscription data: ".serialize($data));
   
   if (is_array($data))
   {

      if (!function_exists('cmpSubscribers')) {
       function cmpSubscribers ($a, $b) { 
        if ($a['priority'] == $b['priority']) return 0; 
        return ($a['priority'] > $b['priority']) ? -1 : 1; 
       } 
      }


      $data2=array();
      foreach($data as $k => $v) {
       $data2[]=array('module'=>$k, 'filter'=>$v['filter'], 'priority'=>(int)$v['priority']);
      }

      usort($data2, 'cmpSubscribers');

      $total=count($data2);
      for($i=0;$i<$total;$i++) {
       
         $module_name    = $data2[$i]['module'];
         $filter_details = $data2[$i]['filter'];

         $modulePath     = DIR_MODULES . $module_name . '/' . $module_name . '.class.php';

         if (file_exists($modulePath))
         {
            include_once($modulePath);
            $module_object = new $module_name();

            if (method_exists($module_object, 'processSubscription'))
            {
               //DebMes("$module_name.processSubscription ($event_name)");
               verbose_log("Processing subscription to [".$event_name."] by [".$module_name."] (".(is_array($details) ? json_encode($details) : '').")");
               $module_object->processSubscription($event_name, $details);
            } else {
             DebMes("$module_name.processSubscription error (method not found)");
            }
            if (!isset($details['BREAK'])) {
             $details['BREAK']=false;
            }
            if ($details['BREAK']) break;
         } else {
          DebMes("$module_name.processSubscription error (module class not found)");
         }
      }

      if (!isset($details['PROCESSED'])) {
       $details['PROCESSED']=false;
      }
      return (int)$details['PROCESSED'];
   }
   return 0;

}

/**
 * Summary of removeMissingSubscribers
 * @return void
 */
function removeMissingSubscribers()
{
   $settings = SQLSelect("SELECT * FROM settings WHERE NAME LIKE 'HOOK_EVENT_%' AND TYPE='json'");
   $total    = count($settings);

   for ($i = 0; $i < $total; $i++)
   {
      $data    = json_decode($settings[$i]['VALUE'], true);
      $changed = 0;

      if (is_array($data))
      {
         foreach ($data as $k => $v)
         {
            $module_name = $k;
            if (!file_exists(DIR_MODULES . $module_name . '/' . $module_name . '.class.php'))
            {
               unset($data[$module_name]);
               $changed = 1;
            }
         }

         if ($changed)
         {
            $settings[$i]['VALUE'] = json_encode($data);
            SQLUpdate('settings', $settings[$i]);
         }
      }
   }
}
