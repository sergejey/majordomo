<?php

$this->device_types=array(
    'rooms'=>array(
        'CLASS'=>'Rooms',
        'DESCRIPTION'=>'Rooms/Locations',
        'PROPERTIES'=>array(
            'temperature'=>array('DESCRIPTION'=>'Temperature'),
            'humidity'=>array('DESCRIPTION'=>'Humidity'),
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
            'alive'=>array('DESCRIPTION'=>'Alive'),
            'aliveTimeout'=>array('DESCRIPTION'=>LANG_DEVICES_ALIVE_TIMEOUT,'_CONFIG_TYPE'=>'num','_CONFIG_HELP'=>'SdAliveTimeout'),
            'linkedRoom'=>array('DESCRIPTION'=>'LinkedRoom'),
            'updated'=>array('DESCRIPTION'=>'Updated Timestamp'),
        ),
        'METHODS'=>array(
            'statusUpdated'=>array('DESCRIPTION'=>'Status updated event'),
            'logicAction'=>array('DESCRIPTION'=>'Logic Action'),
            'keepAlive'=>array('DESCRIPTION'=>'Alive update'),
        ),
        'INJECTS'=>array(
            'OperationalModes'=>array(
                'EconomMode.activate'=>'econommode_activate',
                'EconomMode.deactivate'=>'econommode_deactivate',
                'NobodyHomeMode.activate'=>'nobodyhomemode_activate',
                'NobodyHomeMode.deactivate'=>'nobodyhomemode_deactivate',
                'NightMode.activate'=>'nightmode_activate',
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
            'groupEco'=>array('DESCRIPTION'=>LANG_DEVICES_GROUP_ECO,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdGroupEco'),
            'groupEcoOn'=>array('DESCRIPTION'=>LANG_DEVICES_GROUP_ECO_ON,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdGroupEcoOn'),
            'groupSunrise'=>array('DESCRIPTION'=>LANG_DEVICES_GROUP_SUNRISE,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdGroupSunrise'),
            'groupSunset'=>array('DESCRIPTION'=>LANG_DEVICES_GROUP_SUNSET,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdGroupSunset'),
            'groupNight'=>array('DESCRIPTION'=>LANG_DEVICES_GROUP_NIGHT,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdGroupNight'),
            'isActivity'=>array('DESCRIPTION'=>LANG_DEVICES_IS_ACTIVITY,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdIsActivity'),
            'loadType'=>array('DESCRIPTION'=>LANG_DEVICES_LOADTYPE,
                '_CONFIG_TYPE'=>'select','_CONFIG_HELP'=>'SdLoadType',
                '_CONFIG_OPTIONS'=>'light='.LANG_DEVICES_LOADTYPE_LIGHT.
                    ',heating='.LANG_DEVICES_LOADTYPE_HEATING.
                    ',vent='.LANG_DEVICES_LOADTYPE_VENT.
                    ',curtains='.LANG_DEVICES_LOADTYPE_CURTAINS.
                    ',gates='.LANG_DEVICES_LOADTYPE_GATES.
                    ',power='.LANG_DEVICES_LOADTYPE_POWER),
            'icon'=>array('DESCRIPTION'=>LANG_IMAGE,'_CONFIG_TYPE'=>'style_image','_CONFIG_HELP'=>'SdIcon'),
        ),
        'METHODS'=>array(
            'turnOn'=>array('DESCRIPTION'=>LANG_DEVICES_TURN_ON,'_CONFIG_SHOW'=>1),
            'turnOff'=>array('DESCRIPTION'=>LANG_DEVICES_TURN_OFF,'_CONFIG_SHOW'=>1),
            'switch'=>array('DESCRIPTION'=>'Switch'),
        )
    ),
    'group'=>array(
        'PARENT_CLASS'=>'SControllers',
        'CLASS'=>'SGroups',
        'PROPERTIES'=>array(
            'groupName'=>array('DESCRIPTION'=>'Group system name'),
        ),
        'METHODS'=>array(
            'turnOn'=>array('DESCRIPTION'=>LANG_DEVICES_TURN_ON),
            'turnOff'=>array('DESCRIPTION'=>LANG_DEVICES_TURN_OFF),
            'switch'=>array('DESCRIPTION'=>'Switch'),
            'statusUpdated'=>array('DESCRIPTION'=>'Status Updated'),
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
            'currentTargetValue'=>array('DESCRIPTION'=>LANG_DEVICES_THERMOSTAT_CURRENT_TARGET_TEMP,'DATA_KEY'=>1,'_CONFIG_DEFAULT'=>22),
            'normalTargetValue'=>array('DESCRIPTION'=>LANG_DEVICES_THERMOSTAT_NORMAL_TEMP,'_CONFIG_TYPE'=>'text','ONCHANGE'=>'valueUpdated','_CONFIG_HELP'=>'SdThermostat','_CONFIG_DEFAULT'=>22),
            'ecoTargetValue'=>array('DESCRIPTION'=>LANG_DEVICES_THERMOSTAT_ECO_TEMP,'_CONFIG_TYPE'=>'text','ONCHANGE'=>'valueUpdated','_CONFIG_HELP'=>'SdThermostat','_CONFIG_DEFAULT'=>18),
            'threshold'=>array('DESCRIPTION'=>LANG_DEVICES_THERMOSTAT_THRESHOLD,'_CONFIG_TYPE'=>'text','ONCHANGE'=>'valueUpdated','_CONFIG_HELP'=>'SdThermostat'),
            'ncno'=>array('DESCRIPTION'=>LANG_DEVICES_NCNO,'_CONFIG_TYPE'=>'select','_CONFIG_OPTIONS'=>'nc=Normal Close,no=Normal Open','_CONFIG_HELP'=>'SdThermostat'),
            'disabled' =>array('DESCRIPTION'=>'Disabled'),
        ),
        'METHODS'=>array(
            'setTargetTemperature'=>array('DESCRIPTION'=>'Set target temperature'),
            'valueUpdated'=>array('DESCRIPTION'=>'Value Updated'),
            'statusUpdated'=>array('DESCRIPTION'=>'Status Updated'),
            'tempUp'=>array('DESCRIPTION'=>'Increase target temperature'),
            'tempDown'=>array('DESCRIPTION'=>'Descrease target temperature'),
            'switchEnable'=>array('DESCRIPTION'=>'Switch Enable'),
            'enable'=>array('DESCRIPTION'=>'Enable'),
            'disable'=>array('DESCRIPTION'=>LANG_DEVICES_THERMOSTAT_MODE.': '.LANG_DEVICES_THERMOSTAT_MODE_OFF,'_CONFIG_SHOW'=>1),
            'turnOn'=>array('DESCRIPTION'=>LANG_DEVICES_THERMOSTAT_MODE.': '.LANG_DEVICES_THERMOSTAT_MODE_NORMAL,'_CONFIG_SHOW'=>1),
            'turnOff'=>array('DESCRIPTION'=>LANG_DEVICES_THERMOSTAT_MODE.': '.LANG_DEVICES_THERMOSTAT_MODE_ECO,'_CONFIG_SHOW'=>1),
        )
    ),
    'ac' => array(
        'TITLE'=>LANG_DEVICES_AC,
        'PARENT_CLASS'=>'SControllers',
        'CLASS'=>'SAirConditioners',
        'PROPERTIES'=>array(
            'value'=>array('DESCRIPTION'=>LANG_DEVICES_THERMOSTAT_CURRENT_TEMP,'ONCHANGE'=>'valueUpdated','KEEP_HISTORY'=>365,'DATA_KEY'=>1),
            'currentTargetValue'=>array('DESCRIPTION'=>LANG_DEVICES_THERMOSTAT_CURRENT_TARGET_TEMP,'DATA_KEY'=>1,'_CONFIG_DEFAULT'=>22),
            'tempStep'=>array('DESCRIPTION'=>LANG_DEVICES_AC_TEMP_STEP,'_CONFIG_TYPE'=>'text'),
            'fanSpeed'=>array('DESCRIPTION'=>'Fan Speed','_CONFIG_DEFAULT'=>'auto','ONCHANGE'=>'fanSpeedUpdated'),
            'fanSpeedModes'=>array('DESCRIPTION'=>LANG_DEVICES_AC_FAN_SPEED,
                '_CONFIG_TYPE'=>'multi_select',
                '_CONFIG_OPTIONS'=>'high='.LANG_DEVICES_AC_FAN_SPEED_HIGH.',medium='.LANG_DEVICES_AC_FAN_SPEED_MEDIUM.',low='.LANG_DEVICES_AC_FAN_SPEED_LOW.',auto='.LANG_DEVICES_AC_FAN_SPEED_AUTO,
                '_CONFIG_DEFAULT'=>'high,medium,low,auto','ONCHANGE'=>'configUpdated','ONCHANGE'=>'fanSpeedUpdated'),
            'fanSpeedModesHTML'=>array('DESCRIPTION'=>'FanSpeedModes HTML'),
            'thermostat'=>array('DESCRIPTION'=>'Thermostat','_CONFIG_DEFAULT'=>'auto','ONCHANGE'=>'thermostatUpdated'),
            'thermostatModes'=>array('DESCRIPTION'=>LANG_DEVICES_AC_THERMOSTAT,
                '_CONFIG_TYPE'=>'multi_select',
                '_CONFIG_OPTIONS'=>'fan_only='.LANG_DEVICES_AC_THERMOSTAT_FAN_ONLY.',heat='.LANG_DEVICES_AC_THERMOSTAT_HEAT.',cool='.LANG_DEVICES_AC_THERMOSTAT_COOL.',dry='.LANG_DEVICES_AC_THERMOSTAT_DRY.',auto='.LANG_DEVICES_AC_THERMOSTAT_AUTO,
                '_CONFIG_DEFAULT'=>'fan_only,heat,cool,dry,auto','ONCHANGE'=>'configUpdated'),
            'thermostatModesHTML'=>array('DESCRIPTION'=>'ThermostatModes HTML'),
        ),
        'METHODS'=>array(
            'setTargetTemperature'=>array('DESCRIPTION'=>'Set target temperature'),
            'setThermostatMode'=>array('DESCRIPTION'=>'Set thermostat mode'),
            'setFanSpeedMode'=>array('DESCRIPTION'=>'Set fan speed mode'),
            'configUpdated'=>array('DESCRIPTION'=>'Config updated'),
            'fanSpeedUpdated'=>array('DESCRIPTION'=>'Fan Speed updated'),
            'thermostatUpdated'=>array('DESCRIPTION'=>'Thermostat updated'),
            'tempUp'=>array('DESCRIPTION'=>'Increase target temperature'),
            'tempDown'=>array('DESCRIPTION'=>'Descrease target temperature'),
        )
    ),
    'dimmer'=>array(
        'TITLE'=>LANG_DEVICES_DIMMER,
        'PARENT_CLASS'=>'SControllers',
        'CLASS'=>'SDimmers',
        'PROPERTIES'=>array(
            'level'=>array('DESCRIPTION'=>'Current brightness level','ONCHANGE'=>'levelUpdated','DATA_KEY'=>1),
            'levelSaved'=>array('DESCRIPTION'=>'Latest level saved'),
            'levelWork'=>array('DESCRIPTION'=>'Brightness level (work)','ONCHANGE'=>'levelWorkUpdated'),
            'minWork'=>array('DESCRIPTION'=>LANG_DEVICES_DIMMER_MIN_WORK,'_CONFIG_TYPE'=>'num','_CONFIG_HELP'=>'SdDimmerMinMax'),
            'maxWork'=>array('DESCRIPTION'=>LANG_DEVICES_DIMMER_MAX_WORK,'_CONFIG_TYPE'=>'num','_CONFIG_HELP'=>'SdDimmerMinMax'),
            'switchLevel'=>array('DESCRIPTION'=>LANG_DEVICES_DIMMER_SWITCH_LEVEL,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdDimmerSwitchLevel'),
            'setMaxTurnOn'=>array('DESCRIPTION'=>LANG_DEVICES_DIMMER_SET_MAX,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdDimmerSetMax'),
            ),
        'METHODS'=>array(
            'setLevel'=>array('DESCRIPTION'=>'Set brightness level'),
            'statusUpdated'=>array('DESCRIPTION'=>'Status Updated'),
            'levelUpdated'=>array('DESCRIPTION'=>'Level Updated'),
            'levelWorkUpdated'=>array('DESCRIPTION'=>'Level Work Updated'),
            'turnOn'=>array('DESCRIPTION'=>LANG_DEVICES_TURN_ON,'_CONFIG_SHOW'=>1),
            'turnOff'=>array('DESCRIPTION'=>LANG_DEVICES_TURN_OFF,'_CONFIG_SHOW'=>1),
        )
    ),
    'rgb'=>array(
        'TITLE'=>LANG_DEVICES_RGB,
        'PARENT_CLASS'=>'SControllers',
        'CLASS'=>'SRGB',
        'PROPERTIES'=>array(
            'color'=>array('DESCRIPTION'=>'Current color','ONCHANGE'=>'colorUpdated','DATA_KEY'=>1),
            'colorSaved'=>array('DESCRIPTION'=>'Saved color'),
            'brightness' => array('DESCRIPTION'=>'Current brightness','ONCHANGE'=>'colorUpdated'),
        ),
        'METHODS'=>array(
            'colorUpdated'=>array('DESCRIPTION'=>'Color Updated'),
            'setColor'=>array('DESCRIPTION'=>'Color Set'),
            'turnOn'=>array('DESCRIPTION'=>LANG_DEVICES_TURN_ON,'_CONFIG_SHOW'=>1),
            'turnOff'=>array('DESCRIPTION'=>LANG_DEVICES_TURN_OFF,'_CONFIG_SHOW'=>1),
        )
    ),
    'motion'=>array(
        'TITLE'=>LANG_DEVICES_MOTION,
        'PARENT_CLASS'=>'SDevices',
        'CLASS'=>'SMotions',
        'PROPERTIES'=>array(
            'ignoreNobodysHome'=>array('DESCRIPTION'=>LANG_DEVICES_MOTION_IGNORE,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdIgnoreNobodysHome'),
            'resetNobodysHome'=>array('DESCRIPTION'=>LANG_DEVICES_MOTION_RESET,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdResetNobodysHome'),
            'timeout'=>array('DESCRIPTION'=>LANG_DEVICES_MOTION_TIMEOUT,'_CONFIG_TYPE'=>'num','_CONFIG_HELP'=>'SdMotionTimeout'),
            'blocked'=>array('DESCRIPTION'=>'Is blocked'),
        ),
        'METHODS'=>array(
            'motionDetected'=>array('DESCRIPTION'=>'Motion Detected'),
            'blockSensor'=>array('DESCRIPTION'=>LANG_BLOCK_SENSOR,'_CONFIG_SHOW'=>1),
            'unblockSensor'=>array('DESCRIPTION'=>LANG_UNBLOCK_SENSOR,'_CONFIG_SHOW'=>1),
        )
    ),
    'camera'=>array(
        'TITLE'=>LANG_DEVICES_CAMERA,
        'PARENT_CLASS'=>'SDevices',
        'CLASS'=>'SCameras',
        'PROPERTIES'=>array(
            'streamURL'=>array('DESCRIPTION'=>LANG_DEVICES_CAMERA_STREAM_URL.' (LQ)','ONCHANGE'=>'updatePreview','_CONFIG_TYPE'=>'text','_CONFIG_HELP'=>'SdCameraStreamUrl'),
            'streamURL_HQ'=>array('DESCRIPTION'=>LANG_DEVICES_CAMERA_STREAM_URL.' (HQ)','ONCHANGE'=>'updatePreview','_CONFIG_TYPE'=>'text','_CONFIG_HELP'=>'SdCameraStreamUrl'),
            'cameraUsername'=>array('DESCRIPTION'=>LANG_DEVICES_CAMERA_USERNAME,'_CONFIG_TYPE'=>'text'),
            'cameraPassword'=>array('DESCRIPTION'=>LANG_DEVICES_CAMERA_PASSWORD,'ONCHANGE'=>'updatePreview','_CONFIG_TYPE'=>'text'),
            'streamTransport'=>array('DESCRIPTION'=>LANG_DEVICES_CAMERA_STREAM_TRANSPORT,'ONCHANGE'=>'updatePreview','_CONFIG_HELP'=>'SdCameraTransport','_CONFIG_TYPE'=>'select','_CONFIG_OPTIONS'=>'auto=Auto,udp=UDP,tcp=TCP'),
            'previewType'=>array('DESCRIPTION'=>LANG_DEVICES_CAMERA_PREVIEW_TYPE,'ONCHANGE'=>'updatePreview','_CONFIG_HELP'=>'SdCameraPreviewType','_CONFIG_TYPE'=>'select','_CONFIG_OPTIONS'=>'static='.LANG_DEVICES_CAMERA_PREVIEW_TYPE_STATIC.',slideshow='.LANG_DEVICES_CAMERA_PREVIEW_TYPE_SLIDESHOW),
            'clickAction'=>array('DESCRIPTION'=>LANG_DEVICES_CAMERA_PREVIEW_ONCLICK,'ONCHANGE'=>'updatePreview','_CONFIG_HELP'=>'SdCameraClickType','_CONFIG_TYPE'=>'select','_CONFIG_OPTIONS'=>'enlarge='.LANG_DEVICES_CAMERA_PREVIEW_ONCLICK_ENLARGE.',stream='.LANG_DEVICES_CAMERA_PREVIEW_ONCLICK_ORIGINAL),
            'snapshotURL'=>array('DESCRIPTION'=>LANG_DEVICES_CAMERA_SNAPSHOT_URL,'_CONFIG_TYPE'=>'text','_CONFIG_HELP'=>'SdCameraSnapshotUrl'),
            'snapshot'=>array('DESCRIPTION'=>LANG_DEVICES_CAMERA_SNAPSHOT,'KEEP_HISTORY'=>365,'DATA_TYPE'=>5),
            'series'=>array('DESCRIPTION'=>LANG_DEVICES_CAMERA_SNAPSHOT,'KEEP_HISTORY'=>30,'DATA_TYPE'=>5),
            'snapshotPreviewURL'=>array('DESCRIPTION'=>'Snapshot Preview URL'),
            'previewHTML'=>array('DESCRIPTION'=>'Preview HTML',),
            'activeHTML'=>array('DESCRIPTION'=>'Active HTML',),
            'ignoreNobodysHome'=>array('DESCRIPTION'=>LANG_DEVICES_MOTION_IGNORE,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdIgnoreNobodysHome'),
            'timeout'=>array('DESCRIPTION'=>LANG_DEVICES_MOTION_TIMEOUT,'_CONFIG_TYPE'=>'num','_CONFIG_HELP'=>'SdMotionTimeout')
        ),
        'METHODS'=>array(
            'motionDetected'=>array('DESCRIPTION'=>'Motion Detected'),
            'updatePreview'=>array('DESCRIPTION'=>'Update preview code'),
            'takeSnapshot'=>array('DESCRIPTION'=>'Takes snapshot'),
            'takeSeries'=>array('DESCRIPTION'=>'Takes image series'),
        )
    ),
    'openclose'=>array(
        'TITLE'=>LANG_DEVICES_OPENCLOSE,
        'PARENT_CLASS'=>'SDevices',
        'CLASS'=>'SOpenClose',
        'PROPERTIES'=>array(
            'isActivity'=>array('DESCRIPTION'=>LANG_DEVICES_IS_ACTIVITY,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdIsActivity'),
            'ncno'=>array('DESCRIPTION'=>LANG_DEVICES_NCNO,'_CONFIG_TYPE'=>'select','_CONFIG_OPTIONS'=>'nc=Normal Close,no=Normal Open'),
            'notify_status'=>array('DESCRIPTION'=>LANG_DEVICES_NOTIFY_STATUS,'_CONFIG_TYPE'=>'yesno'),
            'notify_nc'=>array('DESCRIPTION'=>LANG_DEVICES_NOTIFY_NOT_CLOSED,'_CONFIG_TYPE'=>'yesno'),
            'blocked'=>array('DESCRIPTION'=>'Is blocked'),
            'notify_msg_opening'=>array('DESCRIPTION'=>LANG_DEVICES_MSG_OPENING,'_CONFIG_TYPE'=>'text'),
            'notify_msg_closing'=>array('DESCRIPTION'=>LANG_DEVICES_MSG_CLOSING,'_CONFIG_TYPE'=>'text'),
            'notify_msg_reminder'=>array('DESCRIPTION'=>LANG_DEVICES_MSG_REMINDER,'_CONFIG_TYPE'=>'text'),
        ),
        'METHODS'=>array(
            'statusUpdated'=>array('DESCRIPTION'=>'Status updated event'),
            'blockSensor'=>array('DESCRIPTION'=>LANG_BLOCK_SENSOR,'_CONFIG_SHOW'=>1),
            'unblockSensor'=>array('DESCRIPTION'=>LANG_UNBLOCK_SENSOR,'_CONFIG_SHOW'=>1),
        )
    ),
    'openable'=>array(
        'TITLE'=>LANG_DEVICES_OPENABLE,
        'PARENT_CLASS'=>'SDevices',
        'CLASS'=>'SOpenable',
        'PROPERTIES'=>array(
            'isActivity'=>array('DESCRIPTION'=>LANG_DEVICES_IS_ACTIVITY,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdIsActivity'),
            'notify_status'=>array('DESCRIPTION'=>LANG_DEVICES_NOTIFY_STATUS,'_CONFIG_TYPE'=>'yesno'),
            'notify_nc'=>array('DESCRIPTION'=>LANG_DEVICES_NOTIFY_NOT_CLOSED,'_CONFIG_TYPE'=>'yesno'),
            'openType'=>array('DESCRIPTION'=>LANG_DEVICES_OPENTYPE,
                '_CONFIG_TYPE'=>'select','_CONFIG_HELP'=>'SdOpenType',
                '_CONFIG_OPTIONS'=>
                    'gates='.LANG_DEVICES_OPENTYPE_GATES.
                    ',window='.LANG_DEVICES_OPENTYPE_WINDOW.
                    ',door='.LANG_DEVICES_OPENTYPE_DOOR.
                    ',curtains='.LANG_DEVICES_OPENTYPE_CURTAINS.
                    ',shutters='.LANG_DEVICES_OPENTYPE_SHUTTERS),
            'notify_msg_opening'=>array('DESCRIPTION'=>LANG_DEVICES_MSG_OPENING,'_CONFIG_TYPE'=>'text'),
            'notify_msg_closing'=>array('DESCRIPTION'=>LANG_DEVICES_MSG_CLOSING,'_CONFIG_TYPE'=>'text'),
            'notify_msg_reminder'=>array('DESCRIPTION'=>LANG_DEVICES_MSG_REMINDER,'_CONFIG_TYPE'=>'text'),
        ),
        'METHODS'=>array(
            'statusUpdated'=>array('DESCRIPTION'=>'Status updated event'),
            'switch'=>array('DESCRIPTION'=>'Switch'),
            'open'=>array('DESCRIPTION'=>'Open','_CONFIG_SHOW'=>1),
            'close'=>array('DESCRIPTION'=>'Close','_CONFIG_SHOW'=>1),
        )
    ),
    'leak'=>array(
        'TITLE'=>LANG_DEVICES_LEAK_SENSOR,
        'PARENT_CLASS'=>'SDevices',
        'CLASS'=>'SLeak',
        'PROPERTIES'=>array(
            'notify_eliminated'=>array('DESCRIPTION'=>LANG_DEVICES_NOTIFY_ELIMINATED,'_CONFIG_TYPE'=>'yesno'),
            'blocked'=>array('DESCRIPTION'=>'Is blocked'),
        ),
        'METHODS'=>array(
            'statusUpdated'=>array('DESCRIPTION'=>'Status updated event'),
            'alert'=>array('DESCRIPTION'=>'Sensor alert'),
            'blockSensor'=>array('DESCRIPTION'=>LANG_BLOCK_SENSOR,'_CONFIG_SHOW'=>1),
            'unblockSensor'=>array('DESCRIPTION'=>LANG_UNBLOCK_SENSOR,'_CONFIG_SHOW'=>1),
        )
    ),
    'smoke'=>array(
        'TITLE'=>LANG_DEVICES_SMOKE_SENSOR,
        'PARENT_CLASS'=>'SDevices',
        'CLASS'=>'SSmoke',
        'PROPERTIES'=>array(
            'notify_eliminated'=>array('DESCRIPTION'=>LANG_DEVICES_NOTIFY_ELIMINATED,'_CONFIG_TYPE'=>'yesno'),
            'blocked'=>array('DESCRIPTION'=>'Is blocked'),
        ),
        'METHODS'=>array(
            'statusUpdated'=>array('DESCRIPTION'=>'Status updated event'),
            'alert'=>array('DESCRIPTION'=>'Sensor alert'),
            'blockSensor'=>array('DESCRIPTION'=>LANG_BLOCK_SENSOR,'_CONFIG_SHOW'=>1),
            'unblockSensor'=>array('DESCRIPTION'=>LANG_UNBLOCK_SENSOR,'_CONFIG_SHOW'=>1),
        )
    ),
    'counter'=>array(
        'TITLE'=>LANG_DEVICES_COUNTER,
        'PARENT_CLASS'=>'SDevices',
        'CLASS'=>'SCounters',
        'METHODS'=>array(
            'valueUpdated'=>array('DESCRIPTION'=>'Data Value updated event'),
            'valueWorkUpdated'=>array('DESCRIPTION'=>'Work Value updated event'),
            'refreshStats'=>array('DESCRIPTION'=>'Refreshes daily/monthly stats','_CONFIG_SHOW'=>1),
            'pulseDetected'=>array('DESCRIPTION'=>'Meter pulse detection'),
        ),
        'PROPERTIES'=>array(
            'unit'=>array('DESCRIPTION'=>LANG_DEVICES_UNIT,'_CONFIG_TYPE'=>'text'),
            'price'=>array('DESCRIPTION'=>'Price','_CONFIG_TYPE'=>'text','_CONFIG_HELP'=>'SdCounterPrice'),
            'pulseAmount'=>array('DESCRIPTION'=>'Pulse amount (optional)','_CONFIG_TYPE'=>'text','_CONFIG_HELP'=>'SdPulseAmount'),
            'value'=>array('DESCRIPTION'=>'Data Value','ONCHANGE'=>'valueUpdated','DATA_KEY'=>1),
            'valueWork'=>array('DESCRIPTION'=>'Work Value','ONCHANGE'=>'valueWorkUpdated','KEEP_HISTORY'=>0),
            'valueHour'=>array('DESCRIPTION'=>'Hour Value','KEEP_HISTORY'=>365),
            'valueDay'=>array('DESCRIPTION'=>'Day Value','KEEP_HISTORY'=>5*365),
            'valueMonth'=>array('DESCRIPTION'=>'Month Value','KEEP_HISTORY'=>5*365),
            'conversion'=>array('DESCRIPTION'=>'Conversion coefficient (work-to-data)','_CONFIG_TYPE'=>'text','_CONFIG_HELP'=>'SdConversion'),
        ),
    ),
    'button'=>array(
        'TITLE'=>LANG_DEVICES_BUTTON,
        'PARENT_CLASS'=>'SDevices',
        'CLASS'=>'SButtons',
        'PROPERTIES'=>array(
            'icon'=>array('DESCRIPTION'=>LANG_IMAGE,'_CONFIG_TYPE'=>'style_image','_CONFIG_HELP'=>'SdIcon'),
            'isActivity'=>array('DESCRIPTION'=>LANG_DEVICES_IS_ACTIVITY,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdIsActivity'),
        ),
        'METHODS'=>array(
            'pressed'=>array('DESCRIPTION'=>LANG_DEVICES_PRESS,'_CONFIG_SHOW'=>1),
        )
    ),
    'sensor'=>array(
        'PARENT_CLASS'=>'SDevices',
        'CLASS'=>'SSensors',
        'PROPERTIES'=>array(
            'value'=>array('DESCRIPTION'=>'Current Sensor Value','ONCHANGE'=>'valueUpdated','KEEP_HISTORY'=>365,'DATA_KEY'=>1),
            'minValue'=>array('DESCRIPTION'=>LANG_DEVICES_MIN_VALUE,'_CONFIG_TYPE'=>'num','_CONFIG_HELP'=>'SdSensorMinMax'),
            'maxValue'=>array('DESCRIPTION'=>LANG_DEVICES_MAX_VALUE,'_CONFIG_TYPE'=>'num','_CONFIG_HELP'=>'SdSensorMinMax'),
            'notify'=>array('DESCRIPTION'=>LANG_DEVICES_NOTIFY,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdSensorMinMax'),
            'notify_eliminated'=>array('DESCRIPTION'=>LANG_DEVICES_NOTIFY_ELIMINATED,'_CONFIG_TYPE'=>'yesno'),
            'mainSensor'=>array('DESCRIPTION'=>LANG_DEVICES_MAIN_SENSOR,'_CONFIG_TYPE'=>'yesno','_CONFIG_HELP'=>'SdMainSensor'),
            'normalValue'=>array('DESCRIPTION'=>LANG_DEVICES_NORMAL_VALUE,'KEEP_HISTORY'=>0),
            'direction'=>array('DESCRIPTION'=>'Direction of changes','KEEP_HISTORY'=>0),
            'directionTimeout'=>array('DESCRIPTION'=>LANG_DEVICES_DIRECTION_TIMEOUT,'KEEP_HISTORY'=>0,'_CONFIG_TYPE'=>'num','_CONFIG_HELP'=>'SdDirectionTimeout','ONCHANGE'=>'valueUpdated'),
            'blocked'=>array('DESCRIPTION'=>'Is blocked'),
        ),
        'METHODS'=>array(
            'valueUpdated'=>array('DESCRIPTION'=>'Value Updated'),
            'alert'=>array('DESCRIPTION'=>'Sensor alert'),
            'blockSensor'=>array('DESCRIPTION'=>LANG_BLOCK_SENSOR,'_CONFIG_SHOW'=>1),
            'unblockSensor'=>array('DESCRIPTION'=>LANG_UNBLOCK_SENSOR,'_CONFIG_SHOW'=>1),
        )
    ),
    'sensor_general'=>array(
        'TITLE'=>LANG_DEVICES_GENERAL_SENSOR,
        'PARENT_CLASS'=>'SSensors',
        'CLASS'=>'SGeneralSensors',
        'PROPERTIES'=>array(
            'unit'=>array('DESCRIPTION'=>LANG_DEVICES_UNIT,'_CONFIG_TYPE'=>'text'),
        ),
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
    'sensor_moisture'=>array(
        'TITLE'=>LANG_DEVICES_MOISTURE_SENSOR,
        'PARENT_CLASS'=>'SSensors',
        'CLASS'=>'SMoistureSensors'
    ),
    'sensor_co2'=>array(
        'TITLE'=>LANG_DEVICES_CO2_SENSOR,
        'PARENT_CLASS'=>'SSensors',
        'CLASS'=>'SCO2Sensors'
    ),
    'sensor_radiation'=>array(
        'TITLE'=>LANG_DEVICES_RADIATION_SENSOR,
        'PARENT_CLASS'=>'SSensors',
        'CLASS'=>'SRadiationSensors'
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
        'CLASS'=>'SPowerSensors',
        'PROPERTIES'=>array(
            'loadStatusTimeout'=>array('DESCRIPTION'=>LANG_DEVICES_LOAD_TIMEOUT,'_CONFIG_TYPE'=>'text','_CONFIG_HELP'=>'SdLoadTimeout'),
        ),        
        'METHODS'=>array(
            'valueUpdated'=>array('DESCRIPTION'=>'Value Updated'),
            'loadStatusChanged'=>array('DESCRIPTION'=>'Load Status Changed'),
        )
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

$addons_dir=dirname(__FILE__).'/addons';
if (is_dir($addons_dir)) {
    $addon_files=scandir($addons_dir);
    foreach($addon_files as $file) {
        if (preg_match('/\_structure\.php$/',$file)) {
            require($addons_dir.'/'.$file);
        }
    }
}
