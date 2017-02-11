<?php

$this->device_types=array(
    'general'=>array(
        'CLASS'=>'SDevices',
        'DESCRIPTION'=>'General Devices Class',
        'PROPERTIES'=>array(
            'status'=>array('DESCRIPTION'=>LANG_DEVICES_STATUS, 'KEEP_HISTORY'=>365, 'ONCHANGE'=>'statusUpdated', 'DATA_KEY'=>1),
            'alive'=>array('DESCRIPTION'=>'Alive','KEEP_HISTORY'=>365),
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
    'motion'=>array(
        'TITLE'=>LANG_DEVICES_MOTION,
        'PARENT_CLASS'=>'SDevices',
        'CLASS'=>'SMotions',
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
);