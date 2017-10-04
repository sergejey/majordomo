<?php


$dictionary = array(

    'DEVICES_MODULE_TITLE' => 'Simple Devices',

    'DEVICES_LINKED_WARNING' => 'Please note that when linking to existing object, it will be assigned to the new class.',

    'DEVICES_RELAY' => 'Relay/Switch',
    'DEVICES_DIMMER' => 'Dimmer',
    'DEVICES_RGB' => 'RGB-light',
    'DEVICES_MOTION' => 'Motion sensor',
    'DEVICES_BUTTON' => 'Button',
    'DEVICES_SWITCH' => 'Switch',
    'DEVICES_OPENCLOSE' => 'Open/Close sensor',
    'DEVICES_TEMP_SENSOR' => 'Temperature sensor',
    'DEVICES_HUM_SENSOR' => 'Humidity sensor',
    'DEVICES_STATE_SENSOR' => 'State sensor',
    'DEVICES_PERCENTAGE_SENSOR' => 'Percentage value sensor',
    'DEVICES_PRESSURE_SENSOR' => 'Atmospheric pressure sensor',
    'DEVICES_POWER_SENSOR' => 'Watt sensor',
    'DEVICES_VOLTAGE_SENSOR' => 'Voltage sensor',
    'DEVICES_CURRENT_SENSOR' => 'Current sensor',
    'DEVICES_LIGHT_SENSOR' => 'Light sensor',
    'DEVICES_LEAK_SENSOR' => 'Leak detector',
    'DEVICES_SMOKE_SENSOR' => 'Smoke detector',
    'DEVICES_UNIT' => 'Units',
    'DEVICES_COUNTER' => 'Meter/Counter',

// Measure
    'M_VOLTAGE' => 'V',
    'M_CURRENT' => 'I',
    'M_PRESSURE' => 'Tor',
    'M_WATT' => 'W',

//----
    'DEVICES_LINKS' => 'Linked devices',

    'DEVICES_STATUS' => 'Status',

    'DEVICES_LOGIC_ACTION' => 'Action',

    'DEVICES_CURRENT_VALUE' => 'Current value',
    'DEVICES_CURRENT_HUMIDITY' => 'Humidity',
    'DEVICES_CURRENT_TEMPERATURE' => 'Temperature',

    'DEVICES_MIN_VALUE' => 'Minimum value',
    'DEVICES_MAX_VALUE' => 'Maximum value',
    'DEVICES_NOTIFY' => 'Notify when value out of range',
    'DEVICES_NORMAL_VALUE' => 'Value within range',
    'DEVICES_NOTIFY_OUTOFRANGE' => 'Value is out of normal range',
    'DEVICES_NOTIFY_BACKTONORMAL' => 'Value is back to normal',
    'DEVICES_MOTION_IGNORE' => 'Ignore device events when nobody\'s home',
    'DEVICES_ALIVE_TIMEOUT' => 'Possible inactivity timeout (hours)',
    'DEVICES_MAIN_SENSOR' => 'Main sensor for the room',

    'DEVICES_IS_ON' => 'is ON',
    'DEVICES_IS_CLOSED' => 'is Closed',
    'DEVICES_NOT_UPDATING' => 'is not updating',

    'DEVICES_MOTION_DETECTED' => 'Detected',

    'DEVICES_PRESS' => 'Press',
    'DEVICES_TURN_ON' => 'Turn On',
    'DEVICES_TURN_OFF' => 'Turn Off',
    'DEVICES_SET_COLOR' => 'Set Color',

    'DEVICES_GROUP_ECO' => 'Turn it off in ECO mode',
    'DEVICES_GROUP_SUNRISE' => 'Turn it off with Sunrise',
    'DEVICES_IS_ACTIVITY' => 'Status change means activity in the room',
    'DEVICES_NCNO' => 'Sensor type',

    'DEVICES_ADD_MENU' => 'Add device to Menu',
    'DEVICES_ADD_SCENE' => 'Add device to Scene',

    'DEVICES_LINKS_NOT_ADDED' => 'No linked devices set',
    'DEVICES_LINKS_AVAILABLE' => 'Available links',
    'DEVICES_LINKS_COMMENT' => 'Comment (optional)',
    'DEVICES_LINKS_LINKED_DEVICE' => 'Linked device',
    'DEVICES_LINKS_ADDED' => 'Links added',

    'DEVICES_LINK_ACTION_TYPE' => 'Action type',
    'DEVICES_LINK_TYPE_TURN_ON' => 'Turn On',
    'DEVICES_LINK_TYPE_TURN_OFF' => 'Turn Off',
    'DEVICES_LINK_TYPE_SWITCH' => 'Switch',

    'DEVICES_LINK_SWITCH_IT' => 'Switch it',
    'DEVICES_LINK_SWITCH_IT_DESCRIPTION' => 'Control another devices when action triggered',
    'DEVICES_LINK_SWITCH_IT_PARAM_ACTION_DELAY' => 'Delay (seconds)',

    'DEVICES_LINK_SET_COLOR' => 'Set Color',
    'DEVICES_LINK_SET_COLOR_DESCRIPTION' => 'Change color when action triggered',
    'DEVICES_LINK_SET_COLOR_PARAM_ACTION_COLOR' => 'Color',


    'DEVICES_LINK_SENSOR_SWITCH' => 'Sensor control',
    'DEVICES_LINK_SENSOR_SWITCH_DESCRIPTION' => 'Control another devices based on sensor\'s value',
    'DEVICES_LINK_SENSOR_SWITCH_PARAM_CONDITION' => 'Condition type',
    'DEVICES_LINK_SENSOR_SWITCH_PARAM_CONDITION_ABOVE' => 'Above',
    'DEVICES_LINK_SENSOR_SWITCH_PARAM_CONDITION_BELOW' => 'Below',
    'DEVICES_LINK_SENSOR_SWITCH_PARAM_VALUE' => 'Value',


    'DEVICES_UPDATE_CLASSSES' => 'Update classes',
    'DEVICES_ADD_OBJECT_AUTOMATICALLY' => 'Add automatically',

    'DEVICES_PATTERN_TURNON' => 'turn on',
    'DEVICES_PATTERN_TURNOFF' => 'turn off',
    'DEVICES_DEGREES' => 'degrees',
    'DEVICES_STATUS_OPEN' => 'is open',
    'DEVICES_STATUS_CLOSED' => 'is closed',
    'DEVICES_COMMAND_CONFIRMATION' => 'Done|Ok',

    'DEVICES_ROOMS_NOBODYHOME' => 'Nobody home',
    'DEVICES_ROOMS_SOMEBODYHOME' => 'Somebody home',
    'DEVICES_ROOMS_ACTIVITY' => 'Latest activity',

    'DEVICES_PASSED_NOW' => 'Now',
    'DEVICES_PASSED_SECONDS_AGO' => 'seconds ago',
    'DEVICES_PASSED_MINUTES_AGO' => 'minutes ago',
    'DEVICES_PASSED_HOURS_AGO' => 'hours ago',
    'DEVICES_CHOOSE_EXISTING' => '... or choose existing device record',
    
    'DEVICES_CAMERA' =>'IP camera',
    'DEVICES_CAMERA_STREAM_URL' =>'Stream URL',
    'DEVICES_CAMERA_USERNAME' =>'Camera Username',
    'DEVICES_CAMERA_PASSWORD' =>'Camera Password',
    'DEVICES_CAMERA_SNAPSHOT_URL' =>'Snapshot URL',
    'DEVICES_CAMERA_SNAPSHOT' =>'Snapshot',
    'DEVICES_CAMERA_TAKE_SNAPSHOT' =>'Take Snapshot',
    'DEVICES_CAMERA_SNAPSHOT_HISTORY' =>'History',

    /* end module names */


);

foreach ($dictionary as $k => $v) {
    if (!defined('LANG_' . $k)) {
        define('LANG_' . $k, $v);
    }
}
