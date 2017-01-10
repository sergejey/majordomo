<?php


$dictionary=array(

'DEVICES_MODULE_TITLE'=>'Простые устройства',

'DEVICES_RELAY'=>'Управляемое реле/Выключатель',
'DEVICES_DIMMER'=>'Управляемый диммер',
'DEVICES_MOTION'=>'Датчик движения',
'DEVICES_BUTTON'=>'Кнопка',
'DEVICES_SWITCH'=>'Выключатель',
'DEVICES_TEMP_SENSOR'=>'Датчик температуры',
'DEVICES_HUM_SENSOR'=>'Датчик влажности',

'DEVICES_STATUS'=>'Статус',

'DEVICES_LOGIC_ACTION'=>'Действия',

'DEVICES_CURRENT_VALUE'=>'Текущее значение',
'DEVICES_CURRENT_HUMIDITY'=>'Влажность',
'DEVICES_CURRENT_TEMPERATURE'=>'Температура',

'DEVICES_MIN_VALUE'=>'Нижний порог',
'DEVICES_MAX_VALUE'=>'Верхний порог',
'DEVICES_NOTIFY'=>'Умедомлять при выходе за порог',
'DEVICES_NORMAL_VALUE'=>'Значение в нормальных пределах',
'DEVICES_NOTIFY_OUTOFRANGE'=>'Значение датчика вышло за порог',
'DEVICES_NOTIFY_BACKTONORMAL'=>'Значение датчика вернулось к норме',

'DEVICES_IS_ON'=>'Включено',

'DEVICES_MOTION_DETECTED'=>'Обнаружено',

'DEVICES_PRESS'=>'Нажать',
'DEVICES_TURN_ON'=>'Включить',
'DEVICES_TURN_OFF'=>'Выключить',

'DEVICES_GROUP_ECO'=>'Выключать в режиме экономии',
'DEVICES_GROUP_SUNRISE'=>'Выключать с рассветом',

'DEVICES_ADD_MENU'=>'Добавить устройство в Меню',
'DEVICES_ADD_SCENE'=>'Добавить устройство на Сцену',


'DEVICES_UPDATE_CLASSSES'=>'Обновить классы',
'DEVICES_ADD_OBJECT_AUTOMATICALLY'=>'Создать автоматически'

/* end module names */


);

foreach ($dictionary as $k=>$v) {
 if (!defined('LANG_'.$k)) {
  define('LANG_'.$k, $v);
 }
}
