<?php

$this->device_types['sensor_temphum'] = array(
    'TITLE'=>LANG_DEVICES_TEMP_SENSOR.' + '.LANG_DEVICES_HUM_SENSOR,
    'PARENT_CLASS'=>'SSensors',
    'CLASS'=>'STempHumSensors',
    'PROPERTIES'=>array(
        'valueHumidity'=>array('DESCRIPTION'=>LANG_DEVICES_DESC_HUMIDITY_SENSOR_VALUE,'ONCHANGE'=>'valueUpdated','KEEP_HISTORY'=>365,'DATA_KEY'=>1),
        'minHumidityValue'=>array('DESCRIPTION'=>LANG_DEVICES_DESC_MIN_HUMIDITY_VALUE,'_CONFIG_TYPE'=>'num','_CONFIG_HELP'=>'SdSensorMinMax'),
        'maxHumidityValue'=>array('DESCRIPTION'=>LANG_DEVICES_DESC_MAX_HUMIDITY_VALUE,'_CONFIG_TYPE'=>'num','_CONFIG_HELP'=>'SdSensorMinMax'),
        'directionHumidity'=>array('DESCRIPTION'=>LANG_DEVICES_DESC_DIRECTION_OF_CHANGES,'KEEP_HISTORY'=>0),
    ),
    'METHODS'=>array(
        'valueUpdated'=>array('DESCRIPTION'=>LANG_DEVICES_DESC_VALUE_UPDATED,'CALL_PARENT'=>1),
    ));



