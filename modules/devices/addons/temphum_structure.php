<?php

$this->device_types['sensor_temphum'] = array(
    'TITLE'=>LANG_DEVICES_TEMP_SENSOR.' + '.LANG_DEVICES_HUM_SENSOR,
    'PARENT_CLASS'=>'SSensors',
    'CLASS'=>'STempHumSensors',
    'PROPERTIES'=>array(
        'valueHumidity'=>array('DESCRIPTION'=>'Humidity Sensor Value','ONCHANGE'=>'valueUpdated','KEEP_HISTORY'=>365,'DATA_KEY'=>1),
        'minHumidityValue'=>array('DESCRIPTION'=>LANG_DEVICES_MIN_VALUE.' (humidity)','_CONFIG_TYPE'=>'num','_CONFIG_HELP'=>'SdSensorMinMax'),
        'maxHumidityValue'=>array('DESCRIPTION'=>LANG_DEVICES_MAX_VALUE.' (humidity)','_CONFIG_TYPE'=>'num','_CONFIG_HELP'=>'SdSensorMinMax'),
        'directionHumidity'=>array('DESCRIPTION'=>'Direction of changes','KEEP_HISTORY'=>0),
    ),
    'METHODS'=>array(
        'valueUpdated'=>array('DESCRIPTION'=>'Value Updated','CALL_PARENT'=>1),
    ));



