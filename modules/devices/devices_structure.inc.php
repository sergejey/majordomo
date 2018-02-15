<?php

$this->device_types=array(
    'rooms'=>array(
        'CLASS'=>'Rooms',
        'DESCRIPTION'=>'Rooms/Locations',
        'PROPERTIES'=>array(
            'temperature'=>array('DESCRIPTION'=>'Temperature','KEEP_HISTORY'=>365),
            'humidity'=>array('DESCRIPTION'=>'Humidity','KEEP_HISTORY'=>365),
            'SomebodyHere'=>array('DESCRIPTION'=>'Somebody in the room'),
            'IdleDelay'=>array('DESCRIPTION'=>'Nobody here idle delay'),
        ),
        'METHODS'=>array(
            'onActivity'=>array('DESCRIPTION'=>'Rooms Activity'),
            'onIdle'=>array('DESCRIPTION'=>'Rooms Idle'),
            'updateActivityStatus'=>array('DESCRIPTION'=>'Update activity status')
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
            'setUpdatedText'=>array('DESCRIPTION'=>'Change updated text'),
            'logicAction'=>array('DESCRIPTION'=>'Logic Action'),
        ),
        'INJECTS'=>array(
            'OperationalModes'=>array(
                'EconomMode.activate'=>'econommode_activate',
                'EconomMode.deactivate'=>'econommode_deactivate',
                'NobodyHomeMode.activate'=>'nobodyhomemode_activate',
                'NobodyHomeMode.deactivate'=>'nobodyhomemode_deactivate',
                'NightMode.activate'=>'nightmode_activate',
                'NightMode.deactivate'=>'nightmode_deactivate',
                'DarknessMode.activate'=>'darknessmode_activate',
                'DarknessMode.deactivate'=>'darknessmode_deactivate',
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
            'groupEcoOn'=>array('DESCRIPTION'=>LANG_DEVICES_GROUP_ECO_ON,'_CONFIG_TYPE'=>'yesno'),            
            'groupSunrise'=>array('DESCRIPTION'=>LANG_DEVICES_GROUP_SUNRISE,'_CONFIG_TYPE'=>'yesno'),
            'isActivity'=>array('DESCRIPTION'=>LANG_DEVICES_IS_ACTIVITY,'_CONFIG_TYPE'=>'yesno'),
            'loadType'=>array('DESCRIPTION'=>LANG_DEVICES_LOADTYPE,'_CONFIG_TYPE'=>'select','_CONFIG_OPTIONS'=>'light='.LANG_DEVICES_LOADTYPE_LIGHT.',power='.LANG_DEVICES_LOADTYPE_POWER),
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
    'thermostat'=>array(
        'TITLE'=>LANG_DEVICES_THERMOSTAT,
        'PARENT_CLASS'=>'SControllers',
        'CLASS'=>'SThermostats',
        'PROPERTIES'=>array(
            'relay_status'=>array('DESCRIPTION'=>LANG_DEVICES_THERMOSTAT_RELAY_STATUS,'KEEP_HISTORY'=>365,'DATA_KEY'=>1),
            'value'=>array('DESCRIPTION'=>LANG_DEVICES_THERMOSTAT_CURRENT_TEMP,'ONCHANGE'=>'valueUpdated','KEEP_HISTORY'=>365,'DATA_KEY'=>1),
            'currentTargetValue'=>array('DESCRIPTION'=>LANG_DEVICES_THERMOSTAT_CURRENT_TARGET_TEMP,'DATA_KEY'=>1),
            'normalTargetValue'=>array('DESCRIPTION'=>LANG_DEVICES_THERMOSTAT_NORMAL_TEMP,'_CONFIG_TYPE'=>'text','ONCHANGE'=>'valueUpdated'),
            'ecoTargetValue'=>array('DESCRIPTION'=>LANG_DEVICES_THERMOSTAT_ECO_TEMP,'_CONFIG_TYPE'=>'text','ONCHANGE'=>'valueUpdated'),
            'threshold'=>array('DESCRIPTION'=>LANG_DEVICES_THERMOSTAT_THRESHOLD,'_CONFIG_TYPE'=>'text','ONCHANGE'=>'valueUpdated'),
            'ncno'=>array('DESCRIPTION'=>LANG_DEVICES_NCNO,'_CONFIG_TYPE'=>'select','_CONFIG_OPTIONS'=>'nc=Normal Close,no=Normal Open'),
        ),
        'METHODS'=>array(
            'valueUpdated'=>array('DESCRIPTION'=>'Value Updated'),
            'statusUpdated'=>array('DESCRIPTION'=>'Status Updated'),
            'tempUp'=>array('DESCRIPTION'=>'Increase target temperature'),
            'tempDown'=>array('DESCRIPTION'=>'Descrease target temperature'),
        )
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
            'ignoreNobodysHome'=>array('DESCRIPTION'=>LANG_DEVICES_MOTION_IGNORE,'_CONFIG_TYPE'=>'yesno'),
            'timeout'=>array('DESCRIPTION'=>LANG_DEVICES_MOTION_TIMEOUT,'_CONFIG_TYPE'=>'num')
        ),
        'METHODS'=>array(
            'motionDetected'=>array('DESCRIPTION'=>'Motion Detected'),
        )
    ),
    'camera'=>array(
        'TITLE'=>LANG_DEVICES_CAMERA,
        'PARENT_CLASS'=>'SDevices',
        'CLASS'=>'SCameras',
        'PROPERTIES'=>array(
            'streamURL'=>array('DESCRIPTION'=>LANG_DEVICES_CAMERA_STREAM_URL.' (LQ)','ONCHANGE'=>'updatePreview','_CONFIG_TYPE'=>'text'),
            'streamURL_HQ'=>array('DESCRIPTION'=>LANG_DEVICES_CAMERA_STREAM_URL.' (HQ)','ONCHANGE'=>'updatePreview','_CONFIG_TYPE'=>'text'),
            'cameraUsername'=>array('DESCRIPTION'=>LANG_DEVICES_CAMERA_USERNAME,'_CONFIG_TYPE'=>'text'),
            'cameraPassword'=>array('DESCRIPTION'=>LANG_DEVICES_CAMERA_PASSWORD,'ONCHANGE'=>'updatePreview','_CONFIG_TYPE'=>'text'),
            'streamTransport'=>array('DESCRIPTION'=>LANG_DEVICES_CAMERA_STREAM_TRANSPORT,'ONCHANGE'=>'updatePreview','_CONFIG_TYPE'=>'select','_CONFIG_OPTIONS'=>'auto=Auto,udp=UDP,tcp=TCP'),
            'previewType'=>array('DESCRIPTION'=>LANG_DEVICES_CAMERA_PREVIEW_TYPE,'ONCHANGE'=>'updatePreview','_CONFIG_TYPE'=>'select','_CONFIG_OPTIONS'=>'static='.LANG_DEVICES_CAMERA_PREVIEW_TYPE_STATIC.',slideshow='.LANG_DEVICES_CAMERA_PREVIEW_TYPE_SLIDESHOW),
            'clickAction'=>array('DESCRIPTION'=>LANG_DEVICES_CAMERA_PREVIEW_ONCLICK,'ONCHANGE'=>'updatePreview','_CONFIG_TYPE'=>'select','_CONFIG_OPTIONS'=>'enlarge='.LANG_DEVICES_CAMERA_PREVIEW_ONCLICK_ENLARGE.',stream='.LANG_DEVICES_CAMERA_PREVIEW_ONCLICK_ORIGINAL),            
            'snapshotURL'=>array('DESCRIPTION'=>LANG_DEVICES_CAMERA_SNAPSHOT_URL,'_CONFIG_TYPE'=>'text'),
            'snapshot'=>array('DESCRIPTION'=>LANG_DEVICES_CAMERA_SNAPSHOT,'KEEP_HISTORY'=>365,'DATA_TYPE'=>5),
            'snapshotPreviewURL'=>array('DESCRIPTION'=>'Snapshot Preview URL'),
            'previewHTML'=>array('DESCRIPTION'=>'Preview HTML',),
            'activeHTML'=>array('DESCRIPTION'=>'Active HTML',),
        ),
        'METHODS'=>array(
            'motionDetected'=>array('DESCRIPTION'=>'Motion Detected'),
            'updatePreview'=>array('DESCRIPTION'=>'Update preview code'),
            'takeSnapshot'=>array('DESCRIPTION'=>'Takes snapshot'),
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
    'leak'=>array(
        'TITLE'=>LANG_DEVICES_LEAK_SENSOR,
        'PARENT_CLASS'=>'SDevices',
        'CLASS'=>'SLeak',
        'METHODS'=>array(
            'statusUpdated'=>array('DESCRIPTION'=>'Status updated event')
        )
    ),
    'smoke'=>array(
        'TITLE'=>LANG_DEVICES_SMOKE_SENSOR,
        'PARENT_CLASS'=>'SDevices',
        'CLASS'=>'SSmoke',
        'METHODS'=>array(
            'statusUpdated'=>array('DESCRIPTION'=>'Status updated event')
        )
    ),
    'counter'=>array(
        'TITLE'=>LANG_DEVICES_COUNTER,
        'PARENT_CLASS'=>'SDevices',
        'CLASS'=>'SCounters',
        'METHODS'=>array(
            'valueUpdated'=>array('DESCRIPTION'=>'Value updated event')
        ),
        'PROPERTIES'=>array(
            'unit'=>array('DESCRIPTION'=>LANG_DEVICES_UNIT,'_CONFIG_TYPE'=>'text'),
            'value'=>array('DESCRIPTION'=>'Current Value','ONCHANGE'=>'valueUpdated','KEEP_HISTORY'=>365,'DATA_KEY'=>1),
        ),
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
            'normalValue'=>array('DESCRIPTION'=>LANG_DEVICES_NORMAL_VALUE,'KEEP_HISTORY'=>0),
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
    'sensor_light'=>array(
        'TITLE'=>LANG_DEVICES_LIGHT_SENSOR,
        'PARENT_CLASS'=>'SSensors',
        'CLASS'=>'SLightSensors',
        'PROPERTIES'=>array(
            'unit'=>array('DESCRIPTION'=>LANG_DEVICES_UNIT,'_CONFIG_TYPE'=>'text'),
            ),
    ),
);