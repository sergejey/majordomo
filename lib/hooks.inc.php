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
   $data[$module_name] = array('filter'=>$filter_details);
   if ($priority) {
      $data[$module_name]['priority']=$priority;
   }
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

function processSubscriptionsSafe($event_name,$details='') {
   if (!is_array($details)) {
      $details=array();
   }
   $data=array(
       'processSubscriptions'=>1,
       'event'=>$event_name,
       'params'=>json_encode($details),
   );
   $url=BASE_URL.'/objects/?'.http_build_query($data);
   if (is_array($params)) {
      foreach($params as $k=>$v) {
         $url.='&'.$k.'='.urlencode($v);
      }
   }
   $result = getURLBackground($url,0);
   return $result;
}

/**
 * Summary of processSubscriptions
 * @param mixed $event_name Event name
 * @param mixed $details    Details (default '')
 * @return int|void
 */
function processSubscriptions($event_name, $details = '')
{

   //DebMes("New event: ".$event_name,'process_subscription');
   postToWebSocketQueue($event_name, $details, 'PostEvent');
   //DebMes("Post websocket event done: ".$event_name,'process_subscription');

   if (!defined('SETTINGS_HOOK_EVENT_' . strtoupper($event_name)))
   {
      return 0;
   }

   $data = json_decode(constant('SETTINGS_HOOK_EVENT_' . strtoupper($event_name)), true);
   //DebMes("Subscription data: ".serialize($data));
   
   if (is_array($data))
   {
      $data2=array();
      foreach($data as $k => $v) {
       $data2[]=array('module'=>$k, 'filter'=>$v['filter'], 'priority'=>(int)$v['priority']);
      }

      usort($data2, function($a,$b) {
         if ($a['priority'] == $b['priority']) return 0;
         return ($a['priority'] > $b['priority']) ? -1 : 1;
      });

      $total=count($data2);
      for($i=0;$i<$total;$i++) {
         $module_name    = $data2[$i]['module'];
         $filter_details = $data2[$i]['filter'];

         //DebMes("Post event ".$event_name." to module ".$module_name,'process_subscription');

         $modulePath     = DIR_MODULES . $module_name . '/' . $module_name . '.class.php';

         if (file_exists($modulePath))
         {
            include_once($modulePath);
            $module_object = new $module_name();
            if (method_exists($module_object, 'processSubscription'))
            {
               //DebMes("$module_name.processSubscription ($event_name)",'process_subscription');
               verbose_log("Processing subscription to [".$event_name."] by [".$module_name."] (".(is_array($details) ? json_encode($details) : '').")");
               $module_object->processSubscription($event_name, $details);
               //DebMes("$module_name.processSubscription ($event_name) DONE",'process_subscription');
            } else {
             DebMes("$module_name.processSubscription error (method not found)",'process_subscription');
            }
            if (!isset($details['BREAK'])) {
             $details['BREAK']=false;
            }
            if ($details['BREAK']) break;
         } else {
          DebMes("$module_name.processSubscription error (module class not found)",'process_subscription');
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
