<?php


$dictionary = array(

    'DEVICES_MODULE_TITLE' => 'Простые устройства',

    'DEVICES_LINKED_WARNING' => 'Внимание: выбор существующего объекта приведёт к привязке его к новому классу.',

    'DEVICES_RELAY' => 'Управляемое реле/Выключатель',
    'DEVICES_DIMMER' => 'Управляемый диммер',
    'DEVICES_RGB' => 'RGB-контроллер',
    'DEVICES_MOTION' => 'Датчик движения',
    'DEVICES_BUTTON' => 'Кнопка',
    'DEVICES_SWITCH' => 'Выключатель',
    'DEVICES_OPENCLOSE' => 'Датчик открытия/закрытия',
    'DEVICES_TEMP_SENSOR' => 'Датчик температуры',
    'DEVICES_HUM_SENSOR' => 'Датчик влажности',
    'DEVICES_STATE_SENSOR' => 'Датчик состояния',
    'DEVICES_PERCENTAGE_SENSOR' => 'Датчик процентное значение',
    'DEVICES_PRESSURE_SENSOR' => 'Датчик атмосферного давления',
    'DEVICES_POWER_SENSOR' => 'Датчик мощности',
    'DEVICES_VOLTAGE_SENSOR' => 'Датчик напряжения',
    'DEVICES_CURRENT_SENSOR' => 'Датчик тока',
    'DEVICES_LIGHT_SENSOR' => 'Датчик освещённости',
    'DEVICES_LEAK_SENSOR' => 'Датчик протечки',
    'DEVICES_SMOKE_SENSOR' => 'Датчик дыма',
    'DEVICES_COUNTER' => 'Счётчик',
    'DEVICES_UNIT' => 'Единица измерения',

// Measure
    'M_VOLTAGE' => 'В',
    'M_CURRENT' => 'А',
    'M_PRESSURE' => 'торр',
    'M_WATT' => 'Вт',

//----
    'DEVICES_LINKS' => 'Связанные устройства',

    'DEVICES_STATUS' => 'Статус',

    'DEVICES_LOGIC_ACTION' => 'Действия',

    'DEVICES_CURRENT_VALUE' => 'Текущее значение',
    'DEVICES_CURRENT_HUMIDITY' => 'Влажность',
    'DEVICES_CURRENT_TEMPERATURE' => 'Температура',

    'DEVICES_MIN_VALUE' => 'Нижний порог',
    'DEVICES_MAX_VALUE' => 'Верхний порог',
    'DEVICES_NOTIFY' => 'Умедомлять при выходе за порог',
    'DEVICES_NORMAL_VALUE' => 'Значение в нормальных пределах',
    'DEVICES_NOTIFY_OUTOFRANGE' => 'Значение датчика вышло за порог',
    'DEVICES_NOTIFY_BACKTONORMAL' => 'Значение датчика вернулось к норме',
    'DEVICES_MOTION_IGNORE' => 'Игнорировать события от устройства, когда никого нет дома',
    'DEVICES_ALIVE_TIMEOUT' => 'Допустимое время отсутствия данных (часов)',
    'DEVICES_MAIN_SENSOR' => 'Основной сенсор помещения',
    'DEVICES_NOT_UPDATING' => 'не обновляется',

    'DEVICES_IS_ON' => 'Включено',
    'DEVICES_IS_CLOSED' => 'Закрыто',

    'DEVICES_MOTION_DETECTED' => 'Обнаружено',

    'DEVICES_PRESS' => 'Нажать',
    'DEVICES_TURN_ON' => 'Включить',
    'DEVICES_TURN_OFF' => 'Выключить',
    'DEVICES_SET_COLOR' => 'Установить цвет',

    'DEVICES_GROUP_ECO' => 'Выключать в режиме экономии',
    'DEVICES_GROUP_SUNRISE' => 'Выключать с рассветом',
    'DEVICES_IS_ACTIVITY' => 'Изменение означает активность в помещении',
    'DEVICES_NCNO' => 'Тип сенсора',

    'DEVICES_ADD_MENU' => 'Добавить устройство в Меню',
    'DEVICES_ADD_SCENE' => 'Добавить устройство на Сцену',

    'DEVICES_LINKS_NOT_ADDED' => 'Нет связанных устройств',
    'DEVICES_LINKS_AVAILABLE' => 'Доступные типы связей',
    'DEVICES_LINKS_COMMENT' => 'Комментарий (не обязательно)',
    'DEVICES_LINKS_LINKED_DEVICE' => 'Связанное устройство',
    'DEVICES_LINKS_ADDED' => 'Связанные устройства',

    'DEVICES_LINK_ACTION_TYPE' => 'Действие',
    'DEVICES_LINK_TYPE_TURN_ON' => 'Включить',
    'DEVICES_LINK_TYPE_TURN_OFF' => 'Выключить',
    'DEVICES_LINK_TYPE_SWITCH' => 'Переключить',

    'DEVICES_LINK_SWITCH_IT' => 'Включить/Выключить',
    'DEVICES_LINK_SWITCH_IT_DESCRIPTION' => 'Управление другим устройством по событию',
    'DEVICES_LINK_SWITCH_IT_PARAM_ACTION_DELAY' => 'Задержка выполнения (секунд)',

    'DEVICES_LINK_SET_COLOR' => 'Установить цвет',
    'DEVICES_LINK_SET_COLOR_DESCRIPTION' => 'Установить цвет по событию',
    'DEVICES_LINK_SET_COLOR_PARAM_ACTION_COLOR' => 'Цвет',

    'DEVICES_LINK_SENSOR_SWITCH' => 'Условное управление',
    'DEVICES_LINK_SENSOR_SWITCH_DESCRIPTION' => 'Управление другим устройством по показаниям датчика',
    'DEVICES_LINK_SENSOR_SWITCH_PARAM_CONDITION' => 'Тип условия',
    'DEVICES_LINK_SENSOR_SWITCH_PARAM_CONDITION_ABOVE' => 'Выше заданного',
    'DEVICES_LINK_SENSOR_SWITCH_PARAM_CONDITION_BELOW' => 'Ниже заданного',
    'DEVICES_LINK_SENSOR_SWITCH_PARAM_VALUE' => 'Пороговое значение',


    'DEVICES_UPDATE_CLASSSES' => 'Обновить классы',
    'DEVICES_ADD_OBJECT_AUTOMATICALLY' => 'Создать автоматически',

    'DEVICES_PATTERN_TURNON' => 'включи|зажги',
    'DEVICES_PATTERN_TURNOFF' => 'выключи|потуши|отключи',
    'DEVICES_DEGREES' => 'градусов',
    'DEVICES_STATUS_OPEN' => 'открыт',
    'DEVICES_STATUS_CLOSED' => 'закрыт',
    'DEVICES_COMMAND_CONFIRMATION' => 'Готово|Сделано|Как пожелаете',

    'DEVICES_ROOMS_NOBODYHOME' => 'Никого нет.',
    'DEVICES_ROOMS_SOMEBODYHOME' => 'Кто-то есть.',
    'DEVICES_ROOMS_ACTIVITY' => 'Активность:',

    'DEVICES_PASSED_NOW' => 'только что',
    'DEVICES_PASSED_SECONDS_AGO' => 'сек. назад',
    'DEVICES_PASSED_MINUTES_AGO' => 'мин. назад',
    'DEVICES_PASSED_HOURS_AGO' => 'ч. назад',
    'DEVICES_CHOOSE_EXISTING' => '... или выберите уже добавленное устройство',

    'DEVICES_CAMERA' =>'IP-камера',
    'DEVICES_CAMERA_STREAM_URL' =>'URL видео-потока',
    'DEVICES_CAMERA_USERNAME' =>'Имя пользователя',
    'DEVICES_CAMERA_PASSWORD' =>'Пароль',
    'DEVICES_CAMERA_SNAPSHOT_URL' =>'URL статического снимка',
    'DEVICES_CAMERA_SNAPSHOT' =>'Снимок',
    'DEVICES_CAMERA_TAKE_SNAPSHOT' =>'Сохранить снимок',
    'DEVICES_CAMERA_SNAPSHOT_HISTORY' =>'История',

    /* end module names */


);

foreach ($dictionary as $k => $v) {
    if (!defined('LANG_' . $k)) {
        define('LANG_' . $k, $v);
    }
}
