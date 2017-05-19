<?php

$this->device_types=array(
    'rooms'=>array(
        'CLASS'=>'Rooms',
        'DESCRIPTION'=>'Rooms/Locations',
        'PROPERTIES'=>array(
            'temperature'=>array('DESCRIPTION'=>'Temperature','KEEP_HISTORY'=>365),
            'humidity'=>array('DESCRIPTION'=>'Humidity','KEEP_HISTORY'=>365),
            'SomebodyHere'=>array('DESCRIPTION'=>'Somebody in the room'),
        ),
        'METHODS'=>array(
            'onActivity'=>array('DESCRIPTION'=>'Rooms activity')
        )
    ),
    'general'=>array(
        'CLASS'=>'SDevices',
        'DESCRIPTION'=>'General Devices Class',
        'PROPERTIES'=>array(
            'status'=>array('DESCRIPTION'=>LANG_DEVICES_STATUS, 'KEEP_HISTORY'=>365, 'ONCHANGE'=>'statusUpdated', 'DATA_KEY'=>1),
            'alive'=>array('DESCRIPTION'=>'Alive','KEEP_HISTORY'=>365),
            'aliveTimeout'=>array('DESCRIPTION'=>LANG_DEVICES_ALIVE_TIMEOUT,'_CONFIG_TYPE'=>'num'),
            'linkedRoom'=>array('DESCRIPTION'=>'LinkedRoom'),
            'updated'=>array('DESCRIPTION'=>'Updated Timestamp'),
            'updatedText'=>array('DESCRIPTION'=>'Updated Time (text)'),
        ),
        'METHODS'=>array(
            'statusUpdated'=>array('DESCRIPTION'=>'Status updated event'),
            'logicAction'=>array('DESCRIPTION'=>'Logic Action'),
        ),
        'INJECTS'=>array(
            'OperationalModes'=>array(
                'NobodyHomeMode.activate'=>'nobodyhomemode_activate',
                'NobodyHomeMode.deactivate'=>'nobodyhomemode_deactivate',
                'NightMode.activate'=>'nightmode_activate',
                'NightMode.deactivate'=>'nightmode_deactivate',
                'System.checkstate'=>'system_checkstate',
            ),
        )
    ),
    'controller'=>array(
        'CLASS'=>'SControllers',
        'PARENT_CLASS'=>'SDevices',
        'DESCRIPTION'=>'Controllable device',
        'PROPERTIES'=>array(
            'groupEco'=>array('DESCRIPTION'=>LANG_DEVICES_GROUP_ECO,'_CONFIG_TYPE'=>'yesno'),
            'groupSunrise'=>array('DESCRIPTION'=>LANG_DEVICES_GROUP_SUNRISE,'_CONFIG_TYPE'=>'yesno'),
        ),
        'METHODS'=>array(
            'turnOn'=>array('DESCRIPTION'=>'turnOn'),
            'turnOff'=>array('DESCRIPTION'=>'turnOff'),
            'switch'=>array('DESCRIPTION'=>'Switch'),
        )
    ),
    'relay'=>array(
        'TITLE'=>LANG_DEVICES_RELAY,
        'PARENT_CLASS'=>'SControllers',
        'CLASS'=>'SRelays'
    ),
    'dimmer'=>array(
        'TITLE'=>LANG_DEVICES_DIMMER,
        'PARENT_CLASS'=>'SControllers',
        'CLASS'=>'SDimmers',
        'PROPERTIES'=>array(
            'level'=>array('DESCRIPTION'=>'Current brightness level','ONCHANGE'=>'levelUpdated','DATA_KEY'=>1)),
        'METHODS'=>array(
            'levelUpdated'=>array('DESCRIPTION'=>'Level Updated'),
            'turnOn'=>array('DESCRIPTION'=>'Dimmer turnOn'),
            'turnOff'=>array('DESCRIPTION'=>'Dimmer turnOff'),
        )
    ),
    'rgb'=>array(
        'TITLE'=>LANG_DEVICES_RGB,
        'PARENT_CLASS'=>'SControllers',
        'CLASS'=>'SRGB',
        'PROPERTIES'=>array(
            'color'=>array('DESCRIPTION'=>'Current color','ONCHANGE'=>'colorUpdated','DATA_KEY'=>1),
            'colorSaved'=>array('DESCRIPTION'=>'Saved color')
        ),
        'METHODS'=>array(
            'colorUpdated'=>array('DESCRIPTION'=>'Color Updated'),
            'setColor'=>array('DESCRIPTION'=>'Color Set'),
            'turnOn'=>array('DESCRIPTION'=>'RGB turnOn'),
            'turnOff'=>array('DESCRIPTION'=>'RGB turnOff'),
        )
    ),
    'motion'=>array(
        'TITLE'=>LANG_DEVICES_MOTION,
        'PARENT_CLASS'=>'SDevices',
        'CLASS'=>'SMotions',
        'PROPERTIES'=>array(
            'ignoreNobodysHome'=>array('DESCRIPTION'=>LANG_DEVICES_MOTION_IGNORE,'_CONFIG_TYPE'=>'yesno')
        ),
        'METHODS'=>array(
            'motionDetected'=>array('DESCRIPTION'=>'Motion Detected'),
        )
    ),
    'openclose'=>array(
        'TITLE'=>LANG_DEVICES_OPENCLOSE,
        'PARENT_CLASS'=>'SDevices',
        'CLASS'=>'SOpenClose',
        'PROPERTIES'=>array(
            'isActivity'=>array('DESCRIPTION'=>LANG_DEVICES_IS_ACTIVITY,'_CONFIG_TYPE'=>'yesno'),
            'ncno'=>array('DESCRIPTION'=>LANG_DEVICES_NCNO,'_CONFIG_TYPE'=>'select','_CONFIG_OPTIONS'=>'nc=Normal Close,no=Normal Open'),
        ),
        'METHODS'=>array(
            'statusUpdated'=>array('DESCRIPTION'=>'Status updated event')
        )
    ),
    'button'=>array(
        'TITLE'=>LANG_DEVICES_BUTTON,
        'PARENT_CLASS'=>'SDevices',
        'CLASS'=>'SButtons',
        'METHODS'=>array(
            'pressed'=>array('DESCRIPTION'=>'Button pressed'),
        )
    ),
    'sensor'=>array(
        'PARENT_CLASS'=>'SDevices',
        'CLASS'=>'SSensors',
        'PROPERTIES'=>array(
            'value'=>array('DESCRIPTION'=>'Current Sensor Value','ONCHANGE'=>'valueUpdated','KEEP_HISTORY'=>365,'DATA_KEY'=>1),
            'minValue'=>array('DESCRIPTION'=>LANG_DEVICES_MIN_VALUE,'_CONFIG_TYPE'=>'num'),
            'maxValue'=>array('DESCRIPTION'=>LANG_DEVICES_MAX_VALUE,'_CONFIG_TYPE'=>'num'),
            'notify'=>array('DESCRIPTION'=>LANG_DEVICES_NOTIFY,'_CONFIG_TYPE'=>'yesno'),
            'mainSensor'=>array('DESCRIPTION'=>LANG_DEVICES_MAIN_SENSOR,'_CONFIG_TYPE'=>'yesno'),
            'normalValue'=>array('DESCRIPTION'=>LANG_DEVICES_NORMAL_VALUE,'KEEP_HISTORY'=>365),
        ),
        'METHODS'=>array(
            'valueUpdated'=>array('DESCRIPTION'=>'Value Updated'),
        )
    ),	
    'sensor_temp'=>array(
        'TITLE'=>LANG_DEVICES_TEMP_SENSOR,
        'PARENT_CLASS'=>'SSensors',
        'CLASS'=>'STempSensors'
    ),
    'sensor_humidity'=>array(
        'TITLE'=>LANG_DEVICES_HUM_SENSOR,
        'PARENT_CLASS'=>'SSensors',
        'CLASS'=>'SHumSensors'
    ),
	'sensor_state'=>array(
        'TITLE'=>LANG_DEVICES_STATE_SENSOR,
        'PARENT_CLASS'=>'SSensors',
        'CLASS'=>'SStateSensors'
    ),
	'sensor_percentage'=>array(
        'TITLE'=>LANG_DEVICES_PERCENTAGE_SENSOR,
        'PARENT_CLASS'=>'SSensors',
        'CLASS'=>'SPercentageSensors'
    ),
	'sensor_pressure'=>array(
        'TITLE'=>LANG_DEVICES_PRESSURE_SENSOR,
        'PARENT_CLASS'=>'SSensors',
        'CLASS'=>'SPressureSensors'
    ),
	'sensor_power'=>array(
        'TITLE'=>LANG_DEVICES_POWER_SENSOR,
        'PARENT_CLASS'=>'SSensors',
        'CLASS'=>'SPowerSensors' //fix
    ),
	'sensor_voltage'=>array(
        'TITLE'=>LANG_DEVICES_VOLTAGE_SENSOR,
        'PARENT_CLASS'=>'SSensors',
        'CLASS'=>'SVoltageSensors'
    ),
	'sensor_current'=>array(
        'TITLE'=>LANG_DEVICES_CURRENT_SENSOR,
        'PARENT_CLASS'=>'SSensors',
        'CLASS'=>'SCurrentSensors'
    ),
);