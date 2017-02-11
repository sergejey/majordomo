<?php


$dictionary=array(

'DEVICES_MODULE_TITLE'=>'Простые устройства',

'DEVICES_LINKED_WARNING'=>'Внимание: выбор существующего объекта приведёт к привязке его к новому классу.',

'DEVICES_RELAY'=>'Управляемое реле/Выключатель',
'DEVICES_DIMMER'=>'Управляемый диммер',
'DEVICES_MOTION'=>'Датчик движения',
'DEVICES_BUTTON'=>'Кнопка',
'DEVICES_SWITCH'=>'Выключатель',
'DEVICES_TEMP_SENSOR'=>'Датчик температуры',
'DEVICES_HUM_SENSOR'=>'Датчик влажности',
'DEVICES_OPENCLOSE'=>'Датчик открытия/закрытия',

'DEVICES_LINKS'=>'Связанные устройства',

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
'DEVICES_IS_CLOSED'=>'Закрыто',

'DEVICES_MOTION_DETECTED'=>'Обнаружено',

'DEVICES_PRESS'=>'Нажать',
'DEVICES_TURN_ON'=>'Включить',
'DEVICES_TURN_OFF'=>'Выключить',

'DEVICES_GROUP_ECO'=>'Выключать в режиме экономии',
'DEVICES_GROUP_SUNRISE'=>'Выключать с рассветом',
'DEVICES_IS_ACTIVITY'=>'Изменение означает активность в помещении',

'DEVICES_ADD_MENU'=>'Добавить устройство в Меню',
'DEVICES_ADD_SCENE'=>'Добавить устройство на Сцену',

'DEVICES_LINKS_NOT_ADDED'=>'Нет связанных устройств',
'DEVICES_LINKS_AVAILABLE'=>'Доступные типы связей',
'DEVICES_LINKS_COMMENT'=>'Комментарий (не обязательно)',
'DEVICES_LINKS_LINKED_DEVICE'=>'Связанное устройство',
'DEVICES_LINKS_ADDED'=>'Связанные устройства',

'DEVICES_LINK_ACTION_TYPE'=>'Действие',
'DEVICES_LINK_TYPE_TURN_ON'=>'Включить',
'DEVICES_LINK_TYPE_TURN_OFF'=>'Выключить',
'DEVICES_LINK_TYPE_SWITCH'=>'Переключить',

'DEVICES_LINK_SWITCH_IT'=>'Включить/Выключить',
'DEVICES_LINK_SWITCH_IT_DESCRIPTION'=>'Управление другим устройством по событию',
'DEVICES_LINK_SWITCH_IT_PARAM_ACTION_DELAY'=>'Задержка выполнения (секунд)',

'DEVICES_LINK_SENSOR_SWITCH'=>'Условное управление',
'DEVICES_LINK_SENSOR_SWITCH_DESCRIPTION'=>'Управление другим устройством по показаниям датчика',
'DEVICES_LINK_SENSOR_SWITCH_PARAM_CONDITION'=>'Тип условия',
'DEVICES_LINK_SENSOR_SWITCH_PARAM_CONDITION_ABOVE'=>'Выше заданного',
'DEVICES_LINK_SENSOR_SWITCH_PARAM_CONDITION_BELOW'=>'Ниже заданного',
'DEVICES_LINK_SENSOR_SWITCH_PARAM_VALUE'=>'Пороговое значение',


'DEVICES_UPDATE_CLASSSES'=>'Обновить классы',
'DEVICES_ADD_OBJECT_AUTOMATICALLY'=>'Создать автоматически'

/* end module names */


);

foreach ($dictionary as $k=>$v) {
 if (!defined('LANG_'.$k)) {
  define('LANG_'.$k, $v);
 }
}
