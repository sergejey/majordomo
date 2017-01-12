<?php


$dictionary=array(

'DEVICES_MODULE_TITLE'=>'Simple Devices',

'DEVICES_LINKED_WARNING'=>'Please note that when linking to existing object, it will be assigned to the new class.',

'DEVICES_RELAY'=>'Relay/Switch',
'DEVICES_DIMMER'=>'Dimmer',
'DEVICES_MOTION'=>'Motion sensor',
'DEVICES_BUTTON'=>'Button',
'DEVICES_SWITCH'=>'Switch',
'DEVICES_TEMP_SENSOR'=>'Temperature sensor',
'DEVICES_HUM_SENSOR'=>'Humidity sensor',

'DEVICES_STATUS'=>'Status',

'DEVICES_LOGIC_ACTION'=>'Action',

'DEVICES_CURRENT_VALUE'=>'Current value',
'DEVICES_CURRENT_HUMIDITY'=>'Humidity',
'DEVICES_CURRENT_TEMPERATURE'=>'Temperature',

'DEVICES_MIN_VALUE'=>'Minimum value',
'DEVICES_MAX_VALUE'=>'Maximum value',
'DEVICES_NOTIFY'=>'Notify when value out of range',
'DEVICES_NORMAL_VALUE'=>'Value within range',
'DEVICES_NOTIFY_OUTOFRANGE'=>'Value is out of normal range',
'DEVICES_NOTIFY_BACKTONORMAL'=>'Value is back to normal',

'DEVICES_IS_ON'=>'is ON',

'DEVICES_MOTION_DETECTED'=>'Detected',

'DEVICES_PRESS'=>'Press',
'DEVICES_TURN_ON'=>'Turn On',
'DEVICES_TURN_OFF'=>'Turn Off',

'DEVICES_GROUP_ECO'=>'Turn it off in ECO mode',
'DEVICES_GROUP_SUNRISE'=>'Turn it off with Sunrise',

'DEVICES_ADD_MENU'=>'Add device to Menu',
'DEVICES_ADD_SCENE'=>'Add device to Scene',

'DEVICES_UPDATE_CLASSSES'=>'Update classes',
'DEVICES_ADD_OBJECT_AUTOMATICALLY'=>'Add automatically'

/* end module names */


);

foreach ($dictionary as $k=>$v) {
 if (!defined('LANG_'.$k)) {
  define('LANG_'.$k, $v);
 }
}
