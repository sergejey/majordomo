<?php
/**
* Russian language file
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 1.0
*/


$dictionary=array(

/* general */
'WIKI_URL'=>'http://smartliving.ru/',
'DEFAULT_COMPUTER_NAME'=>'Алиса',
'WELCOME_GREETING'=>'Добро пожаловать!',
'WELCOME_TEXT'=>'Спасибо, что пользуйтесь MajorDoMo -- открытой платформой домашней автоматизации. <br/><br/>Узнайте больше и присоединяйтесь к сообществу: <a href="<#LANG_WIKI_URL#>" target=_blank>Веб-сайт</a> | <a href="<#LANG_WIKI_URL#>forum/" target=_blank>Форум</a> | <a href="https://www.facebook.com/SmartLivingRu" target=_blank>Facebook страница</a> <br/>&nbsp;<br/>&nbsp;<br/><small>P.S. Вы можете изменить или удалить эту страницу через <a href="/admin.php?pd=&md=panel&inst=&action=layouts">Панель управления</a></small>',
'CONTROL_PANEL'=>'Панель управления',
'TERMINAL'=>'Терминал',
'USER'=>'Пользователь',
'SELECT'=>'выбрать',
'CONTROL_MENU'=>'Меню',
'YOU_ARE_HERE'=>'Вы здесь',
'FRONTEND'=>'Веб-сайт',
'MY_ACCOUNT'=>'Мой аккаунт',
'LOGOFF'=>'Выйти',
'MODULE_DESCRIPTION'=>'Описание модуля',

'GENERAL_SENSORS'=>'Сенсоры',
'GENERAL_OPERATIONAL_MODES'=>'Режимы работы',
'GENERAL_ENERGY_SAVING_MODE'=>'Энергосбережение',
'GENERAL_SECURITY_MODE'=>'Безопасность',
'GENERAL_NOBODYS_HOME_MODE'=>'Никого нет дома',
'GENERAL_WE_HAVE_GUESTS_MODE'=>'У нас гости',

'GENERAL_CLIMATE'=>'Климат',
'GENERAL_WEATHER_FORECAST'=>'Прогноз погоды',
'GENERAL_TEMPERATURE_OUTSIDE'=>'Температура за окном',
'GENERAL_GRAPHICS'=>'Графики',

'GENERAL_SECURITY_CAMERA'=>'Камеры наблюдения',
'GENERAL_EVENTS_LOG'=>'История событий',

'GENERAL_SERVICE'=>'Сервис',

'GENERAL_GREEN'=>'Зелёный',
'GENERAL_YELLOW'=>'Жёлтый',
'GENERAL_RED'=>'Красный',
'GENERAL_CHANGED_TO'=>'изменился на',
'GENERAL_RESTORED_TO'=>'восстановился на',
'GENERAL_SYSTEM_STATE'=>'Системный статус',
'GENERAL_SECURITY_STATE'=>'Статус безопасности',
'GENERAL_COMMUNICATION_STATE'=>'Статус связи',
'GENERAL_STOPPED'=>'остановлен',
'GENERAL_CYCLE'=>'цикл',
'GENERAL_NO_INTERNET_ACCESS'=>'Нет доступа в Интернет',
'GENERAL_SETTING_UP_LIGHTS'=>'Настраиваю освещение',
'GENERAL_CONTROL'=>'Управление',
'GENERAL_INSIDE'=>'Дома',
'GENERAL_OUTSIDE'=>'На улице',

'SECTION_OBJECTS'=>'Объекты',
'SECTION_APPLICATIONS'=>'Приложения',
'SECTION_DEVICES'=>'Устройства',
'SECTION_SETTINGS'=>'Настройки',
'SECTION_SYSTEM'=>'Система',

/* end general */

/* module names */
'APP_GPSTRACK'=>'GPS-трэкер',
'APP_PLAYER'=>'Плэер',
'APP_MEDIA_BROWSER'=>'Медиа',
'APP_PRODUCTS'=>'Продукты',
'APP_TDWIKI'=>'Блокнот',
'APP_WEATHER'=>'Погода',
'APP_CALENDAR'=>'Календарь',
'APP_READIT'=>'Присл. ссылки',
'APP_QUOTES'=>'Цитаты',

'MODULE_OBJECTS_HISTORY'=>'История объектов',
'MODULE_BT_DEVICES'=>'Bluetooth-устройства',
'MODULE_CONTROL_MENU'=>'Меню управления',
'MODULE_OBJECTS'=>'Объекты',
'MODULE_PINGHOSTS'=>'Устройства Online',
'MODULE_SCRIPTS'=>'Сценарии',
'MODULE_USB_DEVICES'=>'USB-устройства',
'MODULE_WATCHFOLDERS'=>'Папки',
'MODULE_LAYOUTS'=>'Домашние страницы',
'MODULE_LOCATIONS'=>'Расположение',
'MODULE_RSS_CHANNELS'=>'Каналы RSS',
'MODULE_SETTINGS'=>'Общие настройки',
'MODULE_TERMINALS'=>'Терминалы',
'MODULE_USERS'=>'Пользователи',
'MODULE_EVENTS'=>'События',
'MODULE_JOBS'=>'Задания',
'MODULE_MASTER_LOGIN'=>'Пароль доступа',
'MODULE_SAVERESTORE'=>'Проверка обновлений',
'MODULE_WEBVARS'=>'Веб-переменные',
'MODULE_PATTERNS'=>'Шаблоны поведения',
'MODULE_ONEWIRE'=>'1-Wire',
'MODULE_SCENES'=>'Сцены',
'MODULE_SNMP'=>'SNMP',
'MODULE_ZWAVE'=>'Z-Wave',
'MODULE_SECURITY_RULES'=>'Правила безопасности',
'MODULE_MQTT'=>'MQTT',
'MODULE_MODBUS'=>'ModBus',
'MODULE_CONNECT'=>'CONNECT',
'MODULE_MARKET'=>'Маркет дополнений',
'MODULE_MYBLOCKS'=>'Мои блоки',
'MODULE_TEXTFILES'=>'Текстовые файлы',
'MODULE_SOUNDFILES'=>'Звуковые файлы',
'MODULE_SYSTEM_ERRORS'=>'Ошибки системы',
'MODULE_MODULES'=>'Модули', 

'SCENE_HIDDEN'=>'Не включать в список переключающихся сцен', 

'SETUP'=>'Настроить', // objects/objects_edit_methods.html; 


'DATA_SAVED'=>'Данные сохранены!', // objects/objects_edit_methods.html; objects/objects_edit_default.html; objects/objects_edit_properties.html; patterns/patterns_edit.html; scripts/scripts_edit.html; users/users_edit.html; methods/methods_edit.html; commands/commands_edit.html; pvalues/pvalues_edit.html; history/history_edit.html; classes/classes_edit_default.html; dashboard/action_admin.html; locations/locations_edit.html; pinghosts/pinghosts_edit.html; rss_channels/rss_channels_edit_default.html; events/events_edit.html; layouts/layouts_edit.html; app_products/product_categories_edit.html; app_products/products_edit_default.html; onewire/onewire_edit.html; watchfolders/watchfolders_edit.html; webvars/webvars_edit.html; properties/properties_edit.html; scenes/scenes_edit_default.html; scenes/elements_edit.html; terminals/terminals_edit.html; control_access/control_access.html; app_gpstrack/gpslog_edit.html; app_gpstrack/gpsdevices_edit.html; app_gpstrack/gpslocations_edit.html; app_gpstrack/gpsactions_edit.html; 
'ALL'=>'Все', // objects/objects_edit_methods.html; pinghosts/pinghosts_search_admin.html; events/events_search_site.html; 
'EXECUTE'=>'Выполнить', // objects/objects_edit_methods.html; scripts/scripts_search_admin.html; methods/methods_edit.html; 
'SCRIPT'=>'Сценарий', // objects/objects_edit_methods.html; patterns/patterns_edit.html; methods/methods_edit.html; usbdevices/action_admin.html; pinghosts/pinghosts_edit.html; pinghosts/pinghosts_edit.html; watchfolders/watchfolders_edit.html; webvars/webvars_edit.html; 
'CODE'=>'Код', // objects/objects_edit_methods.html; patterns/patterns_edit.html; scripts/scripts_edit.html; methods/methods_edit.html; saverestore/action_admin.html; usbdevices/action_admin.html; pinghosts/pinghosts_edit.html; pinghosts/pinghosts_edit.html; btdevices/btdevices.html; btdevices/btdevices.html; layouts/layouts_edit.html; app_products/products_edit_history.html; app_products/products_edit_codes.html; webvars/webvars_edit.html; scenes/elements_edit.html; 


'CALL_PARENT_METHOD'=>'Вызывать родительский метод', // objects/objects_edit_methods.html; 


'BEFORE_CODE_EXECUTION'=>'перед выполнением кода', // objects/objects_edit_methods.html; 


'AFTER_CODE_EXECUTION'=>'после выполнения кода', // objects/objects_edit_methods.html; 
'NEVER'=>'никогда', // objects/objects_edit_methods.html; 
'UPDATE'=>'Обновить', // objects/objects_edit_methods.html; objects/objects_edit_default.html; objects/objects_edit_properties.html; scripts/scripts_edit.html; methods/methods_edit.html; classes/classes_edit_default.html; locations/locations_edit.html; usbdevices/action_admin.html; btdevices/btdevices.html; app_weather/action_admin.html; rss_channels/rss_channels_search_admin.html; rss_channels/rss_channels_edit_default.html; app_products/products_edit_default.html; properties/properties_edit.html; 
'CANCEL'=>'Отмена', // objects/objects_edit_methods.html; objects/objects_edit_default.html; patterns/patterns_edit.html; jobs/action_admin.html; scripts/scripts_edit.html; users/users_search_admin.html; users/users_edit.html; methods/methods_edit.html; commands/commands_edit.html; pvalues/pvalues_edit.html; pvalues/pvalues_search_admin.html; history/history_edit.html; classes/classes_edit_default.html; locations/locations_edit.html; control_modules/control_modules.html; pinghosts/pinghosts_edit.html; app_calendar/usual_edit.html; rss_channels/rss_channels_edit_default.html; events/events_search_admin.html; events/events_edit.html; events/events_search_site.html; layouts/layouts_search_site.html; layouts/layouts_edit.html; app_products/all_products.html; app_products/all_products.html; app_products/all_products.html; app_products/product_categories_edit.html; app_products/product_categories_search_site.html; app_products/products_search_site.html; app_products/shopping_list_items_search_site.html; app_products/product_categories_search_admin.html; app_products/shopping_list_items_search_admin.html; app_products/products_edit_default.html; onewire/onewire_edit.html; watchfolders/watchfolders_edit.html; watchfolders/watchfolders_search_admin.html; webvars/webvars_edit.html; properties/properties_edit.html; scenes/scenes_edit_default.html; scenes/elements_edit.html; terminals/terminals_edit.html; terminals/terminals_search_admin.html; app_gpstrack/gpslog_edit.html; app_gpstrack/gpsdevices_edit.html; app_gpstrack/gpslocations_edit.html; app_gpstrack/gpsactions_edit.html; 

'MAKE_COPY'=>'Создать копию (клонировать)',

'ARE_YOU_SURE'=>'Вы уверены? Пожалуйста, подтвердите операцию.', // objects/objects_edit_methods.html; objects/objects_edit_properties.html; patterns/patterns_search_admin.html; jobs/action_admin.html; scripts/scripts_search_admin.html; scripts/scripts_search_admin.html; settings/settings_search_admin.html; commands/commands_search_admin.html; saverestore/action_admin.html; saverestore/action_admin.html; classes/classes_search_admin.html; usbdevices/action_admin.html; usbdevices/action_admin.html; pinghosts/pinghosts_search_admin.html; btdevices/btdevices.html; btdevices/btdevices.html; btdevices/btdevices.html; app_calendar/usual_edit.html; rss_channels/rss_channels_search_admin.html; events/events_search_admin.html; events/events_search_site.html; layouts/layouts_search_admin.html; app_products/products_edit_codes.html; app_products/product_categories_search_admin.html; app_products/shopping_list_items_search_admin.html; onewire/onewire_search_admin.html; app_tdwiki/action_admin.html; watchfolders/watchfolders_search_admin.html; webvars/webvars_search_admin.html; app_mediabrowser/action_admin.html; skins/action_admin.html; skins/action_admin.html; scenes/scenes_search_admin.html; scenes/elements_search_admin.html; control_access/control_access.html; app_gpstrack/gpsdevices_search_admin.html; app_gpstrack/gpslog_search_admin.html; app_gpstrack/gpslog_search_admin.html; app_gpstrack/gpslog_search_admin.html; app_gpstrack/gpslocations_search_admin.html; app_gpstrack/gpsactions_search_admin.html; userlog/userlog_search_admin.html; 
'DELETE'=>'Удалить', // objects/objects_edit_methods.html; patterns/patterns_search_admin.html; scripts/scripts_search_admin.html; users/users_search_admin.html; methods/methods_search_admin.html; commands/commands_search_admin.html; commands/commands_edit.html; pvalues/pvalues_search_admin.html; history/history_search_admin.html; locations/locations_search_admin.html; usbdevices/action_admin.html; pinghosts/pinghosts_search_admin.html; btdevices/btdevices.html; app_calendar/usual_edit.html; rss_channels/rss_items_search_admin.html; rss_channels/rss_channels_search_admin.html; events/events_search_admin.html; layouts/layouts_search_admin.html; app_products/products_edit_codes.html; app_products/product_categories_search_admin.html; app_products/shopping_list_items_search_admin.html; onewire/onewire_search_admin.html; app_tdwiki/tdwiki_search_admin.html; watchfolders/watchfolders_search_admin.html; webvars/webvars_search_admin.html; properties/properties_search_admin.html; app_mediabrowser/action_admin.html; skins/action_admin.html; scenes/scenes_search_admin.html; scenes/elements_edit.html; scenes/elements_search_admin.html; terminals/terminals_search_admin.html; control_access/control_access.html; app_gpstrack/gpsdevices_search_admin.html; app_gpstrack/gpslog_search_admin.html; app_gpstrack/gpslocations_search_admin.html; app_gpstrack/gpsactions_search_admin.html; userlog/userlog_search_admin.html; 

'DELETE_SELECTED'=>'Удалить выбранное',
'EXPORT_SELECTED'=>'Экспортировать выбранное',

'CALL_METHOD'=>'Вызов метода', // objects/objects_edit_methods.html; 


'BY_URL'=>'По ссылке', // objects/objects_edit_methods.html; 
'TEST'=>'Проверка', // objects/objects_edit_methods.html; 


'COMMAND_LINE'=>'Через командную строку', // objects/objects_edit_methods.html; scripts/scripts_edit.html; 


'FILLOUT_REQURED'=>'Пожалуйста, заполните необходимые поля!', // objects/objects_edit_default.html; patterns/patterns_edit.html; scripts/scripts_edit.html; users/users_edit.html; methods/methods_edit.html; commands/commands_edit.html; pvalues/pvalues_edit.html; history/history_edit.html; classes/classes_edit_default.html; locations/locations_edit.html; pinghosts/pinghosts_edit.html; rss_channels/rss_channels_edit_default.html; events/events_edit.html; layouts/layouts_edit.html; app_products/product_categories_edit.html; app_products/products_edit_default.html; onewire/onewire_edit.html; watchfolders/watchfolders_edit.html; webvars/webvars_edit.html; properties/properties_edit.html; scenes/scenes_edit_default.html; scenes/elements_edit.html; terminals/terminals_edit.html; app_gpstrack/gpslog_edit.html; app_gpstrack/gpsdevices_edit.html; app_gpstrack/gpslocations_edit.html; app_gpstrack/gpsactions_edit.html; 


'NEW_OBJECT'=>'Новый объект', // objects/objects_edit_default.html; 
'TITLE'=>'Название', // objects/objects_edit_default.html; objects/objects_search_admin.html; patterns/patterns_edit.html; patterns/patterns_search_admin.html; jobs/action_admin.html; scripts/scripts_edit.html; methods/methods_search_admin.html; methods/methods_edit.html; commands/commands_edit.html; classes/classes_edit_default.html; locations/locations_edit.html; control_modules/control_modules.html; usbdevices/action_admin.html; usbdevices/action_admin.html; pinghosts/pinghosts_edit.html; btdevices/btdevices.html; btdevices/btdevices.html; app_calendar/usual_edit.html; rss_channels/rss_channels_search_admin.html; rss_channels/rss_channels_edit_default.html; events/events_search_admin.html; layouts/layouts_search_site.html; layouts/layouts_search_site.html; layouts/layouts_search_admin.html; layouts/layouts_edit.html; app_products/products_edit_history.html; app_products/product_categories_edit.html; app_products/product_categories_search_site.html; app_products/products_edit_codes.html; app_products/products_search_admin.html; app_products/shopping_list_items_search_site.html; app_products/product_categories_search_admin.html; app_products/shopping_list_items_search_admin.html; app_products/products_edit_default.html; onewire/onewire_edit.html; onewire/onewire_edit.html; app_tdwiki/scripts.js; watchfolders/watchfolders_edit.html; watchfolders/watchfolders_search_admin.html; webvars/webvars_edit.html; properties/properties_edit.html; properties/properties_search_admin.html; app_mediabrowser/action_admin.html; scenes/scenes_edit_default.html; scenes/elements_edit.html; scenes/elements_edit.html; terminals/terminals_edit.html; terminals/terminals_search_admin.html; app_gpstrack/gpsdevices_search_admin.html; app_gpstrack/gpslocations_search_admin.html; app_gpstrack/gpslocations_edit.html; 
'CLASS'=>'Класс', // objects/objects_edit_default.html; objects/objects_search_admin.html; properties/properties_edit.html; properties/properties_search_admin.html; 
'DESCRIPTION'=>'Описание', // objects/objects_edit_default.html; objects/objects_search_admin.html; methods/methods_search_admin.html; methods/methods_edit.html; properties/properties_edit.html; properties/properties_search_admin.html; 
'LOCATION'=>'Местоположение', // objects/objects_edit_default.html; objects/objects_search_admin.html; app_calendar/usual_edit.html; app_gpstrack/gpslog_search_admin.html; app_gpstrack/gpslog_edit.html; 
'ADD'=>'Добавить', // objects/objects_edit_default.html; patterns/patterns_edit.html; jobs/action_admin.html; scripts/scripts_edit.html; users/users_edit.html; methods/methods_edit.html; commands/commands_edit.html; commands/commands_edit.html; commands/commands_edit.html; pvalues/pvalues_edit.html; history/history_edit.html; classes/classes_edit_default.html; locations/locations_edit.html; pinghosts/pinghosts_edit.html; app_calendar/usual_list.html; rss_channels/rss_channels_edit_default.html; events/action_addevent.html; events/events_edit.html; layouts/layouts_edit.html; app_products/all_products.html; app_products/all_products.html; app_products/product_categories_edit.html; app_products/products_edit_codes.html; app_products/products_search_admin.html; app_products/products_search_admin.html; app_products/products_edit_default.html; onewire/onewire_edit.html; watchfolders/watchfolders_edit.html; webvars/webvars_edit.html; properties/properties_edit.html; scenes/scenes_edit_default.html; scenes/elements_edit.html; terminals/terminals_edit.html; app_gpstrack/gpslog_edit.html; app_gpstrack/gpsdevices_edit.html; app_gpstrack/gpslocations_edit.html; app_gpstrack/gpsactions_edit.html; 
'BACK'=>'Назад', // objects/action_admin.html; patterns/patterns_edit.html; scripts/scripts_edit.html; users/users_edit.html; methods/methods_edit.html; commands/commands_search_pda.html; pvalues/pvalues_edit.html; history/history_edit.html; classes/action_admin.html; dashboard/action_admin.html; locations/locations_edit.html; control_modules/control_modules.html; usbdevices/action_admin.html; pinghosts/pinghosts_edit.html; btdevices/btdevices.html; rss_channels/rss_channels_edit_items.html; rss_channels/rss_channels_edit_default.html; events/events_edit.html; layouts/layouts_edit.html; app_products/action_mobile.html; app_products/product_categories_edit.html; app_products/action_admin.html; onewire/onewire_edit.html; app_tdwiki/tdwiki_view.html; watchfolders/watchfolders_edit.html; webvars/webvars_edit.html; properties/properties_edit.html; scenes/elements_edit.html; scenes/action_admin.html; terminals/terminals_edit.html; control_access/control_access.html; app_gpstrack/gpslog_edit.html; app_gpstrack/gpsdevices_edit.html; app_gpstrack/gpslocations_edit.html; app_gpstrack/gpsactions_edit.html; 
'OBJECT'=>'Объект', // objects/action_admin.html; history/history_edit.html; 
'DETAILS'=>'Детали', // objects/action_admin.html; history/history_edit.html; events/events_search_admin.html; events/action_addevent.html; events/events_edit.html; events/events_search_site.html; app_products/action_mobile.html; app_products/action_admin.html; app_products/action_usual.html; app_products/products_edit_default.html; scenes/action_admin.html; 
'PROPERTIES'=>'Свойства', // objects/action_admin.html; objects/objects_search_admin.html; classes/classes_search_admin.html; classes/action_admin.html; onewire/onewire_edit.html; 
'METHODS'=>'Методы', // objects/action_admin.html; objects/objects_search_admin.html; classes/classes_search_admin.html; classes/action_admin.html; 
'HISTORY'=>'История', // objects/action_admin.html; usbdevices/action_admin.html; btdevices/btdevices.html; app_products/action_mobile.html; app_products/action_admin.html; app_products/action_usual.html; 


'ADD_NEW_OBJECT'=>'Добавить новый объект', // objects/objects_search_admin.html; 
'PAGES'=>'Страницы', // objects/objects_search_admin.html; objects/objects_search_admin.html; users/users_search_admin.html; users/users_search_admin.html; pvalues/pvalues_search_admin.html; pvalues/pvalues_search_admin.html; history/history_search_admin.html; history/history_search_admin.html; pinghosts/pinghosts_search_admin.html; pinghosts/pinghosts_search_admin.html; rss_channels/rss_items_search_admin.html; rss_channels/rss_items_search_admin.html; events/events_search_admin.html; events/events_search_admin.html; events/events_search_site.html; events/events_search_site.html; app_products/products_search_site.html; app_products/products_search_site.html; onewire/onewire_search_admin.html; onewire/onewire_search_admin.html; watchfolders/watchfolders_search_admin.html; watchfolders/watchfolders_search_admin.html; webvars/webvars_search_admin.html; webvars/webvars_search_admin.html; terminals/terminals_search_admin.html; terminals/terminals_search_admin.html; app_gpstrack/gpslog_search_admin.html; app_gpstrack/gpslog_search_admin.html; userlog/userlog_search_admin.html; userlog/userlog_search_admin.html; 
'EDIT'=>'Редактировать', // objects/objects_search_admin.html; patterns/patterns_search_admin.html; scripts/scripts_search_admin.html; users/users_search_admin.html; methods/methods_search_admin.html; commands/commands_edit.html; commands/commands_edit.html; pvalues/pvalues_search_admin.html; classes/classes_search_admin.html; locations/locations_search_admin.html; usbdevices/action_admin.html; pinghosts/pinghosts_search_admin.html; btdevices/btdevices.html; app_calendar/usual_list.html; app_calendar/usual_list.html; app_calendar/usual_list.html; app_calendar/usual_list.html; app_calendar/usual_edit.html; rss_channels/rss_channels_search_admin.html; events/events_search_admin.html; layouts/layouts_search_admin.html; app_products/all_products.html; app_products/product_categories_search_admin.html; onewire/onewire_search_admin.html; app_tdwiki/tdwiki_search_admin.html; watchfolders/watchfolders_search_admin.html; webvars/webvars_search_admin.html; properties/properties_search_admin.html; app_mediabrowser/action_admin.html; scenes/elements_search_admin.html; terminals/terminals_search_admin.html; app_gpstrack/gpsdevices_search_admin.html; app_gpstrack/gpslocations_search_admin.html; app_gpstrack/gpsactions_search_admin.html; 


'NO_OBJECTS_DEFINED'=>'Нет заданных объектов', // objects/objects_search_admin.html; 


'ADD_NEW_PROPERTY'=>'Добавить новое свойство', // objects/objects_edit_properties.html; properties/properties_search_admin.html; 


'NEW_RECORD'=>'Новая запись', // patterns/patterns_edit.html; users/users_edit.html; commands/commands_edit.html; pvalues/pvalues_edit.html; history/history_edit.html; pinghosts/pinghosts_edit.html; rss_channels/rss_channels_edit_items.html; events/events_edit.html; layouts/layouts_edit.html; app_products/products_edit_history.html; app_products/product_categories_edit.html; app_products/products_edit_codes.html; app_products/products_edit_default.html; onewire/onewire_edit.html; watchfolders/watchfolders_edit.html; webvars/webvars_edit.html; scenes/scenes_edit_default.html; scenes/elements_edit.html; terminals/terminals_edit.html; app_gpstrack/gpslog_edit.html; app_gpstrack/gpsdevices_edit.html; app_gpstrack/gpslocations_edit.html; app_gpstrack/gpsactions_edit.html; 
'PATTERN'=>'Шаблон', // patterns/patterns_edit.html; 


'TIME_LIMIT'=>'Ограничение по времени', // patterns/patterns_edit.html; 
'SECONDS'=>'секунд', // patterns/patterns_edit.html; jobs/action_admin.html; pinghosts/pinghosts_edit.html; pinghosts/pinghosts_edit.html; onewire/onewire_edit.html; webvars/webvars_edit.html; 


'EXECUTE_ON_MATCH'=>'Выполнить при совпадении', // patterns/patterns_edit.html; 
'SUBMIT'=>'Сохранить', // patterns/patterns_edit.html; settings/settings_search_admin.html; users/users_edit.html; commands/commands_edit.html; pvalues/pvalues_edit.html; history/history_edit.html; dashboard/action_admin.html; pinghosts/pinghosts_edit.html; app_calendar/usual_edit.html; events/events_edit.html; layouts/layouts_edit.html; app_products/product_categories_edit.html; onewire/onewire_edit.html; watchfolders/watchfolders_edit.html; webvars/webvars_edit.html; app_mediabrowser/action_admin.html; skins/action_admin.html; scenes/scenes_edit_default.html; scenes/elements_edit.html; terminals/terminals_edit.html; control_access/control_access.html; app_gpstrack/gpslog_edit.html; app_gpstrack/gpsdevices_edit.html; app_gpstrack/gpslocations_edit.html; app_gpstrack/gpsactions_edit.html; 


'ADD_NEW_RECORD'=>'Добавить новую запись', // patterns/patterns_search_admin.html; users/users_search_admin.html; pvalues/pvalues_search_admin.html; events/events_search_admin.html; app_products/product_categories_search_admin.html; app_tdwiki/tdwiki_search_admin.html; watchfolders/watchfolders_search_admin.html; terminals/terminals_search_admin.html; app_gpstrack/gpsdevices_search_admin.html; app_gpstrack/gpslocations_search_admin.html; app_gpstrack/gpsactions_search_admin.html; 
'EDIT_RECORD'=>'Редактирование записи', //users


'NO_RECORDS_FOUND'=>'Нет данных', // patterns/patterns_search_admin.html; scripts/scripts_search_admin.html; settings/settings_search_site.html; settings/settings_search_admin.html; users/users_search_admin.html; methods/methods_search_admin.html; commands/commands_search_pda.html; commands/commands_search_admin.html; pvalues/pvalues_search_admin.html; classes/classes_search_admin.html; locations/locations_search_admin.html; usbdevices/action_admin.html; pinghosts/pinghosts_search_admin.html; rss_channels/rss_items_search_admin.html; rss_channels/rss_channels_search_admin.html; events/events_search_admin.html; events/events_search_site.html; layouts/layouts_search_site.html; layouts/layouts_search_admin.html; app_products/product_categories_search_site.html; app_products/products_search_admin.html; app_products/products_search_site.html; app_products/shopping_list_items_search_site.html; app_products/product_categories_search_admin.html; app_products/shopping_list_items_search_admin.html; app_tdwiki/tdwiki_search_admin.html; app_tdwiki/tdwiki_search_site.html; watchfolders/watchfolders_search_admin.html; webvars/webvars_search_admin.html; properties/properties_search_admin.html; scenes/scenes_search_admin.html; scenes/elements_search_admin.html; terminals/terminals_search_admin.html; app_gpstrack/gpsdevices_search_admin.html; app_gpstrack/gpslog_search_admin.html; app_gpstrack/gpslocations_search_admin.html; app_gpstrack/gpsactions_search_admin.html; userlog/userlog_search_admin.html; 
'COMMAND'=>'Команда', // jobs/action_admin.html; commands/commands_edit.html; shoutbox/shouts_search_site.html; 


'RUN_IN'=>'Выполнить через', // jobs/action_admin.html; 
'MINUTES'=>'минуты', // jobs/action_admin.html; rss_channels/rss_channels_edit_default.html; watchfolders/watchfolders_edit.html; 
'HOURS'=>'часы', // jobs/action_admin.html; 
'PROCESSED'=>'обработано', // jobs/action_admin.html; events/events_search_admin.html; events/events_edit.html; events/events_search_site.html; 


'IN_QUEUE'=>'в очереди', // jobs/action_admin.html; 


'NEW_SCRIPT'=>'Новый сценарий', // scripts/scripts_edit.html; 


'EXECUTE_SCRIPT_AFTER_UPDATE'=>'выполнить после сохранения', // scripts/scripts_edit.html; 


'RUN_BY_URL'=>'Запуск по ссылке', // scripts/scripts_edit.html; 


'ADD_NEW_SCRIPT'=>'Добавить новый сценарий', // scripts/scripts_search_admin.html; 


'GENERAL_SETTINGS'=>'Общие настройки', // settings/settings_search_admin.html; 


'SETTINGS_UPDATED'=>'Настройки сохранены!', // settings/settings_search_admin.html; 


'DEFAULT_VALUE'=>'Значение по-умолчанию', // settings/settings_search_admin.html; settings/settings_search_admin.html; settings/settings_search_admin.html; settings/settings_search_admin.html; 


'RESET_TO_DEFAULT'=>'Сбросить', // settings/settings_search_admin.html; 
'SEARCH'=>'Поиск', // users/users_search_admin.html; users/users_search_admin.html; users/users_search_admin.html; pvalues/pvalues_search_admin.html; pvalues/pvalues_search_admin.html; pvalues/pvalues_search_admin.html; events/events_search_admin.html; events/events_search_admin.html; events/events_search_admin.html; events/events_search_site.html; events/events_search_site.html; events/events_search_site.html; layouts/layouts_search_site.html; layouts/layouts_search_site.html; layouts/layouts_search_site.html; app_products/product_categories_search_site.html; app_products/product_categories_search_site.html; app_products/product_categories_search_site.html; app_products/products_search_site.html; app_products/products_search_site.html; app_products/products_search_site.html; app_products/shopping_list_items_search_site.html; app_products/shopping_list_items_search_site.html; app_products/shopping_list_items_search_site.html; app_products/product_categories_search_admin.html; app_products/product_categories_search_admin.html; app_products/product_categories_search_admin.html; app_products/shopping_list_items_search_admin.html; app_products/shopping_list_items_search_admin.html; app_products/shopping_list_items_search_admin.html; watchfolders/watchfolders_search_admin.html; watchfolders/watchfolders_search_admin.html; watchfolders/watchfolders_search_admin.html; terminals/terminals_search_admin.html; terminals/terminals_search_admin.html; terminals/terminals_search_admin.html; 
'USERNAME'=>'Имя пользователя', // users/users_search_admin.html; users/users_edit.html; webvars/webvars_edit.html; 
'NAME'=>'Имя', // users/users_search_admin.html; users/users_edit.html; 
'EMAIL'=>'E-mail', // users/users_search_admin.html; users/users_edit.html; 
'SKYPE'=>'Skype', // users/users_edit.html; 


'MOBILE_PHONE'=>'Мобильный телефон', // users/users_edit.html; 


'ADD_METHOD'=>'Добавить новый метод', // methods/methods_search_admin.html; 


'PARENT_METHODS'=>'Родительские методы:', // methods/methods_search_admin.html; 
'OVERWRITE'=>'Переписать', // methods/methods_search_admin.html; classes/classes_search_admin.html; properties/properties_search_admin.html; 
'ONLY_CLASSES'=>'Не импортировать объекты',

'NEW_METHOD'=>'Новый метод', // methods/methods_edit.html; 
'HOME'=>'Начало', // commands/commands_search_pda.html; commands/commands_edit.html; app_products/action_admin.html; 
'OFF'=>'Выкл', // commands/commands_search_pda.html; 
'ON'=>'Вкл', // commands/commands_search_pda.html; 


'ADD_NEW_SECTION'=>'Добавить новый раздел', // commands/commands_search_admin.html; 
'EXPAND'=>'Расширить', // commands/commands_search_admin.html; classes/classes_search_admin.html; app_products/product_categories_search_admin.html; 


'PARENT_MENU_ITEM'=>'Родительский пункт меню', // commands/commands_edit.html; 
'PRIORITY'=>'Приоритет', // commands/commands_edit.html; layouts/layouts_search_site.html; layouts/layouts_search_admin.html; layouts/layouts_edit.html; app_products/product_categories_edit.html; scenes/scenes_edit_default.html; 
'TYPE'=>'Тип', // commands/commands_edit.html; pinghosts/pinghosts_edit.html; events/events_search_admin.html; events/action_addevent.html; events/events_edit.html; events/events_search_site.html; layouts/layouts_search_site.html; layouts/layouts_search_admin.html; layouts/layouts_edit.html; watchfolders/watchfolders_edit.html; scenes/elements_edit.html; 
'LABEL'=>'Подпись', // commands/commands_edit.html; 


'NEW_WINDOW'=>'Новое окно', // commands/commands_edit.html; 
'URL'=>'Ссылка', // commands/commands_edit.html; commands/commands_edit.html; rss_channels/rss_channels_search_admin.html; layouts/layouts_search_site.html; layouts/layouts_edit.html; webvars/webvars_edit.html; 


'JS_COMMAND'=>'JavaScript команда', // commands/commands_edit.html; 
'BUTTON'=>'Кнопка', // commands/commands_edit.html; 


'ON_OFF_SWITCH'=>'Выключатель', // commands/commands_edit.html; 


'SELECT_BOX'=>'Поле выбора', // commands/commands_edit.html; 


'SLIDER_BOX'=>'Слайдер', // commands/commands_edit.html; 


'PLUS_MINUS_BOX'=>'Плюс-минус', // commands/commands_edit.html; 


'TIME_PICKER'=>'Выбор времени', // commands/commands_edit.html; 


'TEXT_BOX'=>'Текстовое поле', // commands/commands_edit.html; 
'DATE_BOX'=>'Дата', // commands/commands_edit.html; 


'CUSTOM_HTML_BOX'=>'HTML-блок', // commands/commands_edit.html; 
'ICON'=>'Иконка', // commands/commands_edit.html; 


'MIN_VALUE'=>'Мин. значение', // commands/commands_edit.html; 


'MAX_VALUE'=>'Макс. значение', // commands/commands_edit.html; 


'STEP_VALUE'=>'Шаг изменений', // commands/commands_edit.html; 
'DATA'=>'Данные', // commands/commands_edit.html; saverestore/action_admin.html; 


'AUTO_UPDATE_PERIOD'=>'Период авто-обновления', // commands/commands_edit.html; 


'CURRENT_VALUE'=>'Текущее значение', // commands/commands_edit.html; 
'PROPERTY'=>'Свойство', // commands/commands_edit.html; webvars/webvars_edit.html; 


'ONCHANGE_OBJECT'=>'Запускать Объект', // commands/commands_edit.html; 
'ONCHANGE_METHOD'=>'Запускать метод при изменении', // commands/commands_edit.html; 
'METHOD'=>'Метод', // commands/commands_edit.html; history/history_edit.html; 
'ONCHANGE_SCRIPT'=>'Сценарий', // commands/commands_edit.html; onewire/onewire_edit.html; onewire/onewire_edit.html; 
'ONCHANGE_CODE'=>'Код', // commands/commands_edit.html; 


'TARGET_WINDOW'=>'Окно', // commands/commands_edit.html; 
'WIDTH'=>'Ширина', // commands/commands_edit.html; scenes/elements_edit.html; 
'HEIGHT'=>'Высота', // commands/commands_edit.html; scenes/elements_edit.html; 


'ON_THE_SAME_LEVEL'=>'На этом уровне', // commands/commands_edit.html; 


'CHILD_ITEMS'=>'Дочерние пункты', // commands/commands_edit.html; 
'ADDED'=>'Добавлено', // history/history_edit.html; events/events_search_admin.html; events/events_edit.html; events/events_search_site.html; app_products/all_products.html; app_gpstrack/gpslog_search_admin.html; app_gpstrack/gpslog_edit.html; 
'VALUE'=>'Значение', // history/history_edit.html; onewire/onewire_edit.html; 


'OLD_VALUE'=>'Старое значение', // history/history_edit.html; 


'NEW_VALUE'=>'Новое значение', // history/history_edit.html; 
'UPDATES'=>'Обновления', // saverestore/action_admin.html; 


'NO_UPDATES_AVAILABLE'=>'Нет доступных обновлений', // saverestore/action_admin.html; 


'NEW_VERSION'=>'Новая версия', // saverestore/action_admin.html; 


'INSTALL_NEW_MODULES'=>'Установить новые модули', // saverestore/action_admin.html; 


'NO_MODULES_AVAILABLE'=>'Нет доступных модулей', // saverestore/action_admin.html; 


'GET_LIST_OF_MODULES'=>'Получить список модулей', // saverestore/action_admin.html; 


'SUBMIT_NEWER_FILES'=>'Отправить новые файлы', // saverestore/action_admin.html; 


'NO_FILES_TO_SUBMIT'=>'Нет файлов для отправки', // saverestore/action_admin.html; 
'FOLDER'=>'Папка', // saverestore/action_admin.html; watchfolders/watchfolders_search_admin.html; 


'YOUR_NAME'=>'Ваше имя', // saverestore/action_admin.html; 
'YOUR_EMAIL'=>'Ваш e-mail', // saverestore/action_admin.html; 
'NOTES'=>'Заметки', // saverestore/action_admin.html; app_calendar/usual_edit.html; 


'SUBMIT_SELECTED_FILES'=>'Отправить выбранные файлы', // saverestore/action_admin.html; 


'CHECK_FILES_FOR_SUBMIT'=>'Выберите файлы для отправки', // saverestore/action_admin.html; 
'DESIGN'=>'Дизайн', // saverestore/action_admin.html; 


'FILES_UPLOADED'=>'Файлы загружены', // saverestore/action_admin.html; 


'CLEAR_TEMPORARY_FOLDER'=>'Очистить временную папку', // saverestore/action_admin.html; 


'ADD_NEW_CLASS'=>'Добавить новый класс', // classes/classes_search_admin.html; 
'OBJECTS'=>'Объекты', // classes/classes_search_admin.html; classes/classes_search_admin.html; classes/action_admin.html; 
'EXPORT'=>'Экспорт', // classes/classes_search_admin.html; 
'EXPORT_CLASS_FULL'=>'Экспорт Класса и Объектов', // classes/classes_search_admin.html; 
'EXPORT_CLASS_NO_OBJECTS'=>'Экспорт Класса (без объектов)', // classes/classes_search_admin.html; 


'IMPORT_CLASS_FROM_FILE'=>'Импортировать класс из файла', // classes/classes_search_admin.html; 
'IMPORT'=>'Импортировать', // classes/classes_search_admin.html; 


'NEW_CLASS'=>'Новый класс', // classes/classes_edit_default.html; 


'PARENT_CLASS'=>'Родительский класс', // classes/classes_edit_default.html; 


'DO_NOT_SAVE_CLASS_ACTIVITY'=>'не сохранять активность объектов класса в лог', // classes/classes_edit_default.html; 
'MAIN'=>'Основное', // classes/action_admin.html; 


'STRING_BACK'=>'Назад', // shoutrooms/shoutrooms_edit.html; 
'STRING_SUCCESS'=>'Данные были сохранены!', // shoutrooms/shoutrooms_edit.html; 
'STRING_ERROR'=>'Ошибка', // shoutrooms/shoutrooms_edit.html; 
'STRING_NEW_RECORD'=>'Новая запись', // shoutrooms/shoutrooms_edit.html; 


'SHOUTROOMS_TITLE'=>'Название', // shoutrooms/shoutrooms_edit.html; shoutrooms/shoutrooms_search_admin.html; 
'SHOUTROOMS_PRIORITY'=>'Приоритет', // shoutrooms/shoutrooms_edit.html; shoutrooms/shoutrooms_search_admin.html; 


'FORM_SUBMIT'=>'Сохранить', // shoutrooms/shoutrooms_edit.html; 
'FORM_ADD'=>'Добавить', // shoutrooms/shoutrooms_edit.html; 
'FORM_CANCEL'=>'Отмена', // shoutrooms/shoutrooms_edit.html; 


'STRING_ADD_NEW'=>'Добавить', // shoutrooms/shoutrooms_search_admin.html; 


'SHOUTROOMS_STRING_PUBLIC'=>'Открытая', // shoutrooms/shoutrooms_search_admin.html; 
'SHOUTROOMS_STRING_PRIVATE'=>'Приватная', // shoutrooms/shoutrooms_search_admin.html; 


'STRING_EDIT'=>'Редактировать', // shoutrooms/shoutrooms_search_admin.html; 
'STRING_DELETE'=>'Удалить', // shoutrooms/shoutrooms_search_admin.html; shoutbox/shouts_search_admin.html; 
'STRING_NOT_FOUND'=>'Не найдено', // shoutrooms/shoutrooms_search_admin.html; shoutbox/shouts_search_admin.html; 


'SHOUTROOMS_STRING_SHOUTROOMS'=>'ShoutRooms', // shoutrooms/action_admin.html; 


'NEW_LOCATION'=>'Новое местоположение', // locations/locations_edit.html; 


'ADD_NEW_LOCATION'=>'Добавить новое местоположение', // locations/locations_search_admin.html; 
'LOADING'=>'Загрузка...', // shoutbox/shouts_search_site.html; 


'PLEASE_LOGIN'=>'Пожалуйста, войдите в систему.', // shoutbox/shouts_search_site.html; 
'SEND'=>'Отправить', // shoutbox/shouts_search_site.html; 


'SHOUTBOX_STRING_DELETE_ALL'=>'Удалить всё', // shoutbox/shouts_search_admin.html; 


'STRING_PAGES'=>'Страницы', // shoutbox/shouts_search_admin.html; shoutbox/shouts_search_admin.html; 


'MEMBERS_MEMBER'=>'Пользователь', // shoutbox/shouts_search_admin.html; 


'SHOUTBOX_MESSAGE'=>'Сообщение', // shoutbox/shouts_search_admin.html; 
'SHOUTBOX_ADDED'=>'Добавлено', // shoutbox/shouts_search_admin.html; 


'STRING_DELETE_CONFIRM'=>'Вы уверены?', // shoutbox/shouts_search_admin.html; 


'DELETE_UNKNOWN_DEVICES'=>'Удалить неизвестные устройства', // usbdevices/action_admin.html; 
'SERIAL'=>'Серийный номер', // usbdevices/action_admin.html; usbdevices/action_admin.html; 


'FIRST_ATTACHED'=>'Подключено впервые', // usbdevices/action_admin.html; usbdevices/action_admin.html; 


'LAST_ATTACHED'=>'Подключено в последний раз', // usbdevices/action_admin.html; usbdevices/action_admin.html; 


'EXECUTE_ON_ATTACH'=>'Выполнить при подключении', // usbdevices/action_admin.html; 
'HOSTNAME'=>'Хост (адрес)', // pinghosts/pinghosts_edit.html; terminals/terminals_edit.html; 


'SEARCH_WORD'=>'Искать слово', // pinghosts/pinghosts_edit.html; 


'ONLINE_ACTION'=>'Действие при переходе в Online', // pinghosts/pinghosts_edit.html; 


'OFFLINE_ACTION'=>'Действие при переходе в Offline', // pinghosts/pinghosts_edit.html; 


'ONLINE_CHECK_INTERVAL'=>'Интервал проверки (когда online)', // pinghosts/pinghosts_edit.html; 


'OFFLINE_CHECK_INTERVAL'=>'Интервал проверки (когда offline)', // pinghosts/pinghosts_edit.html; 
'LOG'=>'Лог событий', // pinghosts/pinghosts_edit.html; onewire/onewire_edit.html; webvars/webvars_edit.html; 


'ADD_NEW_HOST'=>'Добавить новый хост', // pinghosts/pinghosts_search_admin.html; 
'ONLINE'=>'Online', // pinghosts/pinghosts_search_admin.html; 
'OFFLINE'=>'Offline', // pinghosts/pinghosts_search_admin.html; 
'UNKNOWN'=>'Неизвестно', // pinghosts/pinghosts_search_admin.html; 


'DELETE_ALL_UNKNOWN_DEVICES'=>'Удалить все неизвестные устройства', // btdevices/btdevices.html; 
'DELETE_FOUND_ONCE'=>'Удалить все устройство, обнаруженные только один раз', // btdevices/btdevices.html; 


'FOUND_FIRST'=>'Обнаружено впервые', // btdevices/btdevices.html; btdevices/btdevices.html; 
'FOUND_LAST'=>'Обнаружено в последний раз', // btdevices/btdevices.html; btdevices/btdevices.html; 


'PAST_DUE'=>'Пропущено', // app_calendar/usual_list.html; 
'TODAY'=>'Сегодня', // app_calendar/usual_list.html; 


'NOTHING_TO_DO'=>'Нечего делать... Везёт же!', // app_calendar/usual_list.html; 
'SOON'=>'Скоро', // app_calendar/usual_list.html; 


'DONE_RECENTLY'=>'Недавно выполнено', // app_calendar/usual_list.html; 
'PREVIEW'=>'Просмотр', // app_calendar/action_admin.html; scenes/scenes_search_admin.html; 


'SYSTEM_NAME'=>'Системное имя', // app_calendar/usual_edit.html; terminals/terminals_edit.html; terminals/terminals_search_admin.html; 
'EVENT'=>'Событие', // app_calendar/usual_edit.html; 
'TASK'=>'Задача', // app_calendar/usual_edit.html; 
'DONE'=>'Готово', // app_calendar/usual_edit.html; 
'DATE'=>'Дата', // app_calendar/usual_edit.html; app_products/products_edit_history.html; 


'NO_DUE_DATE'=>'без конкретной даты', // app_calendar/usual_edit.html; 


'IS_REPEATING'=>'повторяющееся', // app_calendar/usual_edit.html; 
'YEARLY'=>'Ежегодно', // app_calendar/usual_edit.html; 
'MONTHLY'=>'Ежемесячно', // app_calendar/usual_edit.html; 
'WEEKLY'=>'Еженедельно', // app_calendar/usual_edit.html; 
'OTHER'=>'Другое', // app_calendar/usual_edit.html; 


'RESTORE_IN'=>'Восстановить через', // app_calendar/usual_edit.html; 


'IN_DAYS'=>'дней', // app_calendar/usual_edit.html; 


'AFTER_COMPLETION'=>'после выполнения', // app_calendar/usual_edit.html; 


'MORE_DETAILS'=>'Детали', // app_calendar/usual_edit.html; 


'ANY_USER'=>'Любой пользователь', // app_calendar/usual_edit.html; 
'ANY_LOCATION'=>'Любое местоположение', // app_calendar/usual_edit.html; 


'RUN_SCRIPT'=>'Запустить сценарий', // app_calendar/usual_edit.html; 


'WHEN_TASK_WILL_BE_DONE'=>'когда задача будет выполнена', // app_calendar/usual_edit.html; 


'SIMILAR_ITEMS'=>'Похожие записи', // app_calendar/usual_edit.html; 


'LOCATION_CODE'=>'Код корода (city id)', // app_weather/action_admin.html; 
'REFRESH'=>'Обновить', // app_weather/action_admin.html; 


'ERROR_GETTING_WEATHER_DATA'=>'Ошибка получения данных о погоде', // app_weather/action_usual.html; 


'CLEAR_ALL'=>'Очистить всё', // rss_channels/rss_items_search_admin.html; 


'ADD_NEW_CHANNEL'=>'Добавить новый канал', // rss_channels/rss_channels_search_admin.html; 


'LAST_CHECKED'=>'Последняя проверка', // rss_channels/rss_channels_search_admin.html; 


'RSS_CHANNELS'=>'RSS-каналы', // rss_channels/action_admin.html; rss_channels/action_admin.html; 
'RSS_NEWS'=>'RSS-новости', // rss_channels/action_admin.html; rss_channels/action_admin.html; 


'NEW_CHANNEL'=>'Новый канал', // rss_channels/rss_channels_edit_default.html; 


'SOURCE_URL'=>'URL-источник', // rss_channels/rss_channels_edit_default.html; 


'CHECK_EVERY'=>'Проверять каждые', // rss_channels/rss_channels_edit_default.html; 


'EXECUTE_FOR_NEW_RECORDS'=>'Выполнять для новых записей', // rss_channels/rss_channels_edit_default.html; 


'TERMINAL_FROM'=>'From terminal', // events/events_search_admin.html; events/events_search_admin.html; events/events_edit.html; events/events_search_site.html; 


'USER_FROM'=>'From user', // events/events_search_admin.html; events/events_edit.html; events/events_search_site.html; 
'USER_TO'=>'To user', // events/events_search_admin.html; events/action_addevent.html; events/events_edit.html; events/events_search_site.html; 
'WINDOW'=>'Окно', // events/events_search_admin.html; events/action_addevent.html; events/events_edit.html; events/events_search_site.html; 
'EXPIRE'=>'Истекает', // events/events_search_admin.html; events/events_edit.html; events/events_search_site.html; 


'TERMINAL_TO'=>'To terminal', // events/action_addevent.html; events/events_edit.html; events/events_search_site.html; 


'NEW_PAGE'=>'Новая страница', // layouts/layouts_search_admin.html; 
'APP'=>'Приложение', // layouts/layouts_edit.html; 
'QUANTITY'=>'Кол-во', // app_products/products_edit_history.html; app_products/products_search_admin.html; app_products/products_edit_default.html; 
'ACTION'=>'Действие', // app_products/products_edit_history.html; 
'CATEGORY'=>'Категория', // app_products/all_products.html; app_products/all_products.html; app_products/products_edit_default.html; 
'ADD_NEW_CATEGORY'=>'Добавить категорию',
'PRODUCT'=>'Продукт', // app_products/all_products.html; 


'DELETE_CATEGORY'=>'Удалить категорию', // app_products/all_products.html; 
'MISSING'=>'Отсутствующие', // app_products/all_products.html; app_products/products_search_admin.html; 


'IN_STORAGE'=>'В наличии', // app_products/all_products.html; 
'BUY'=>'Купить', // app_products/all_products.html; 
'CODES'=>'Коды', // app_products/action_mobile.html; app_products/action_admin.html; app_products/action_usual.html; 
'PARENT'=>'Родитель', // app_products/product_categories_edit.html; 
'ROOT'=>'(корень)', // app_products/product_categories_edit.html; 


'CREATE_NOTE'=>'Создать заметку', // app_products/shoplist.html; 
'TOTAL'=>'Всего', // app_products/products_search_admin.html; 


'EXPIRE_IN'=>'Истекает через', // app_products/products_search_admin.html; 
'DAYS'=>'дней', // app_products/products_search_admin.html; app_products/products_edit_default.html; 
'CATEGORIES'=>'Категории', // app_products/products_search_admin.html; app_products/action_admin.html; app_products/action_usual.html; 


'ALL_PRODUCTS'=>'Все продукты', // app_products/products_search_admin.html; 
'EXPIRED'=>'Истёкшие', // app_products/products_search_admin.html; app_products/products_search_admin.html; 


'SHOPPING_LIST'=>'Покупки', // app_products/products_search_admin.html; app_products/action_usual.html; 
'PRODUCTS'=>'Продукты', // app_products/action_admin.html; app_products/action_admin.html; app_products/action_admin.html; app_products/action_usual.html; 
'IMAGE'=>'Изображение', // app_products/products_edit_default.html; scenes/elements_edit.html; scenes/elements_edit.html; 


'EXPIRATION_DATE'=>'Дата истечения срока годности', // app_products/products_edit_default.html; 


'DEFAULT_EXPIRE_IN'=>'По-умолчанию "истекает через"', // app_products/products_edit_default.html; 
'UPDATED'=>'Обновлено', // app_products/products_edit_default.html; onewire/onewire_edit.html; app_gpstrack/gpsdevices_search_admin.html; 


'RECOMENDED_QUANTITY'=>'Рекомендуемое кол-во', // app_products/products_edit_default.html; 


'DELETE_FROM_DATABASE'=>'Удалить из базы данных', // app_products/products_edit_default.html; 


'RESCAN_DEVICES'=>'Сканировать устройства', // onewire/onewire_search_admin.html; 


'NO_DEVICES_FOUND'=>'Нет устройств', // onewire/onewire_search_admin.html; 
'ID'=>'ID', // onewire/onewire_edit.html; 


'CHECK_INTERVAL'=>'Интервал проверки', // onewire/onewire_edit.html; watchfolders/watchfolders_edit.html; webvars/webvars_edit.html; 


'LINKED_OBJECT'=>'Связанный объект', // onewire/onewire_edit.html; webvars/webvars_edit.html; 
'LINKED_PROPERTY'=>'Связанное св-во', // onewire/onewire_edit.html; 
'SET'=>'установить', // onewire/onewire_edit.html; 


'ONCHANGE_ACTION'=>'Действие при изменении', // onewire/onewire_edit.html; webvars/webvars_edit.html; 
'RESET'=>'Сбросить', // app_tdwiki/action_admin.html; 


'MORE_INFO'=>'Детали', // app_tdwiki/tdwiki_search_admin.html; app_tdwiki/tdwiki_search_site.html; 
'PATH'=>'Путь', // watchfolders/watchfolders_edit.html; app_mediabrowser/action_admin.html; 


'INCLUDE_SUBFOLDERS'=>'включая под-папки', // watchfolders/watchfolders_edit.html; 


'CHECK_MASK'=>'Маска файлов', // watchfolders/watchfolders_edit.html; 
'EXAMPLE'=>'Пример', // watchfolders/watchfolders_edit.html; scenes/elements_edit.html; 


'AUTHORIZATION_REQUIRED'=>'требуется авторизация', // webvars/webvars_edit.html; 
'PASSWORD'=>'Пароль', // webvars/webvars_edit.html; 


'SOURCE_PAGE_ENCODING'=>'Кодировка страницы', // webvars/webvars_edit.html; 
'OPTIONAL'=>'не обязательно', // webvars/webvars_edit.html; 


'BY_DEFAULT'=>'по-умолчанию', // webvars/webvars_edit.html; 


'SEARCH_PATTERN'=>'Шаблон поиска', // webvars/webvars_edit.html; 


'LATEST_VALUE'=>'Последнее значение', // webvars/webvars_edit.html; 


'ADD_NEW'=>'Добавить', // webvars/webvars_search_admin.html; 


'REFRESH_ALL'=>'Обновить все', // webvars/webvars_search_admin.html; 


'NEW_PROPERTY'=>'Новое свойство', // properties/properties_edit.html; 


'KEEP_HISTORY_DAYS'=>'Хранить историю (дней)', // properties/properties_edit.html; 


'DO_NOT_KEEP'=>'не хранить историю', // properties/properties_edit.html; 

'KEEP_HISTORY'=>'хранить историю',

'PARENT_PROPERTIES'=>'Родительские свойства', // properties/properties_search_admin.html; 


'ADD_NEW_COLLECTION'=>'Добавить новую коллекцию', // app_mediabrowser/action_admin.html; 


'NEW_SKIN_INSTALLED'=>'Новый skin установлен', // skins/action_admin.html; 


'INCORRECT_FILE_FORMAT'=>'Некорректный формат файла', // skins/action_admin.html; 


'CANNOT_CREATE_FOLDER'=>'Не могу создать папку', // skins/action_admin.html; 


'SKIN_ALREADY_EXISTS'=>'Skin уже существует', // skins/action_admin.html; 


'UPLOAD_NEW_SKIN'=>'Загрузка нового skin-а', // skins/action_admin.html; 


'INSTALL_SKIN'=>'Установить skin', // skins/action_admin.html; 


'NO_ADDITIONAL_SKINS_INSTALLED'=>'Нет дополнительных skin-ов', // skins/action_admin.html; 
'BACKGROUND'=>'Фоновое изображение', // scenes/scenes_edit_default.html; 


'SCENE'=>'Сцена',
'ADD_NEW_SCENE'=>'Добавить новую сцену', // scenes/scenes_search_admin.html; 
'USE_ELEMENT_TO_POSITION_RELATED'=>'Позиционировать относительно',
'NO_RELATED'=>'Левого верхнего угла',
'TOP'=>'Отступ сверху', // scenes/elements_edit.html; 
'LEFT'=>'Отступ слева', // scenes/elements_edit.html; 
'STATES'=>'Состояния', // scenes/elements_edit.html; 
'ADD_NEW_STATE'=>'Добавить новое состояние', // scenes/elements_edit.html; scenes/elements_edit.html; 


'RUN_SCRIPT_ON_CLICK'=>'Выполнить сценарий при клике', // scenes/elements_edit.html; 
'SHOW_MENU_ON_CLICK'=>'Показать меню при клике',
'SHOW_HOMEPAGE_ON_CLICK'=>'Показать домашнюю страницу при клике',
'SHOW_URL_ON_CLICK'=>'Открыть ссылку при клике',


'DISPLAY_CONDITION'=>'Условие отображения', // scenes/elements_edit.html; 


'ALWAYS_VISIBLE'=>'всегда показывать', // scenes/elements_edit.html; 
'SIMPLE'=>'простое', // scenes/elements_edit.html; 
'ADVANCED'=>'расширенное', // scenes/elements_edit.html; 


'SWITCH_SCENE_WHEN_ACTIVATED'=>'переключить на сцену при активации', // scenes/elements_edit.html; 


'ADD_NEW_ELEMENT'=>'Добавить новый элемент', // scenes/elements_search_admin.html; 
'ELEMENTS'=>'Элементы', // scenes/action_admin.html; 


'CAN_PLAY_MEDIA'=>'может проигрывать медиа-контент', // terminals/terminals_edit.html; 


'PLAYER_TYPE'=>'Тип плэйера', // terminals/terminals_edit.html; 
'DEFAULT'=>'По-умолчанию', // terminals/terminals_edit.html; 


'MAKE_SURE_YOU_HAVE_CONTROL_OVER_HTTP_ENABLED'=>'Проверьте, что включена возможность управления по HTTP-протоколу', // terminals/terminals_edit.html; 


'PLAYER_PORT'=>'Порт доступа к плэеру', // terminals/terminals_edit.html; 
'PLAYER_USERNAME'=>'Имя пользователя доступа к плэеру', // terminals/terminals_edit.html; 
'PLAYER_PASSWORD'=>'Пароль доступа к плэеру', // terminals/terminals_edit.html; 
'DEVICE'=>'Устройство', // app_gpstrack/gpsdevices_search_admin.html; app_gpstrack/gpslog_search_admin.html; app_gpstrack/gpslog_search_admin.html; app_gpstrack/gpslog_edit.html; app_gpstrack/gpslog_edit.html; 


'CLEAR_LOG'=>'Очистить лог', // app_gpstrack/gpslog_search_admin.html; 


'OPTIMIZE_LOG'=>'Оптимизировать лог', // app_gpstrack/gpslog_search_admin.html; 
'LATITUDE'=>'Широта', // app_gpstrack/gpslog_search_admin.html; app_gpstrack/gpslocations_search_admin.html; app_gpstrack/gpslog_edit.html; app_gpstrack/gpslocations_edit.html; 
'LONGITUDE'=>'Долгота', // app_gpstrack/gpslog_search_admin.html; app_gpstrack/gpslocations_search_admin.html; app_gpstrack/gpslog_edit.html; app_gpstrack/gpslocations_edit.html; 
'SPEED'=>'Скорость', // app_gpstrack/gpslog_search_admin.html; app_gpstrack/gpslog_edit.html; 
'ACCURACY'=>'Точность', // app_gpstrack/gpslog_search_admin.html; 


'BATTERY_LEVEL'=>'Уровень заряда', // app_gpstrack/gpslog_search_admin.html; app_gpstrack/gpslog_edit.html; 
'CHARGING'=>'На зарядке', // app_gpstrack/gpslog_search_admin.html; app_gpstrack/gpslog_edit.html; 
'MAP'=>'Карта', // app_gpstrack/gpslog_search_admin.html; 
'RANGE'=>'Радиус действия', // app_gpstrack/gpslocations_search_admin.html; app_gpstrack/gpslocations_edit.html; 
'ALTITUDE'=>'Высота', // app_gpstrack/gpslog_edit.html; 
'PROVIDER'=>'Провайдер', // app_gpstrack/gpslog_edit.html; 

'LOCATIONS'=>'Места', // app_gpstrack/action_admin.html; app_gpstrack/action_admin.html; 
'DEVICES'=>'Устройства', // app_gpstrack/action_admin.html; app_gpstrack/action_admin.html; 
'ACTIONS'=>'Действия', // app_gpstrack/action_admin.html; app_gpstrack/action_admin.html; 


'ACTION_TYPE'=>'Тип действия', // app_gpstrack/gpsactions_edit.html; app_gpstrack/gpsactions_search_admin.html; 
'EXECUTED'=>'Выполнено', // app_gpstrack/gpsactions_edit.html; app_gpstrack/gpsactions_search_admin.html; 


'VIRTUAL_USER'=>'Виртуальный пользователь', // app_gpstrack/gpslocations_edit.html; 

'WIND'=>'Ветер',//lib/OpenWeather/OpenWeather.php;
'PRESSURE'=>'Давление',//lib/OpenWeather/OpenWeather.php;
'HUMIDITY'=>'Влажность',//lib/OpenWeather/OpenWeather.php;
'GET_AT'=>'Обновлено',//lib/OpenWeather/OpenWeather.php;
'MMHG'=>'мм рт.ст.',//lib/OpenWeather/OpenWeather.php;
'HPA'=>'гПа',//lib/OpenWeather/OpenWeather.php;
'M_S'=>'м/с',//lib/OpenWeather/OpenWeather.php;
'N'=>'С',//lib/OpenWeather/OpenWeather.php;
'NNE'=>'ССВ',//lib/OpenWeather/OpenWeather.php;
'NE'=>'СВ',//lib/OpenWeather/OpenWeather.php;
'ENE'=>'ВСВ',//lib/OpenWeather/OpenWeather.php;
'E'=>'В',//lib/OpenWeather/OpenWeather.php;
'ESE'=>'ВЮВ',//lib/OpenWeather/OpenWeather.php;
'SE'=>'ЮВ',//lib/OpenWeather/OpenWeather.php;
'SSE'=>'ЮЮВ',//lib/OpenWeather/OpenWeather.php;
'S'=>'Ю',//lib/OpenWeather/OpenWeather.php;
'SSW'=>'ЮЮЗ',//lib/OpenWeather/OpenWeather.php;
'SW'=>'ЮЗ',//lib/OpenWeather/OpenWeather.php;
'WSW'=>'ЗЮЗ',//lib/OpenWeather/OpenWeather.php;
'W'=>'З',//lib/OpenWeather/OpenWeather.php;
'WNW'=>'ЗСЗ',//lib/OpenWeather/OpenWeather.php;
'NW'=>'CЗ',//lib/OpenWeather/OpenWeather.php;
'NNW'=>'CCЗ',//lib/OpenWeather/OpenWeather.php;



'LONG_OPERATION_WARNING'=>'Внимание: данная операция может занять длительное время (несколько минут). Пожалуйста, дождитесь завершения после запуска.',

'STARRED'=>'Избранное',

'USE_BACKGROUND'=>'Использовать фон',
'YES'=>'Да',
'NO'=>'Нет',

'USE_JAVASCRIPT'=>'Дополнительный код JavaScript',
'USE_CSS'=>'Дополнительный код CSS',

'PERIOD'=>'Период',
'PERIOD_TODAY'=>'Сегодня',
'PERIOD_DAY'=>'Сутки (24 часа)',
'PERIOD_WEEK'=>'Неделя',
'PERIOD_MONTH'=>'Месяц',
'PERIOD_CUSTOM'=>'Выбрать',
'SEARCH'=>'Искать',
'SHOWHIDE'=>'Показать/Скрыть',

'AUTO_UPDATE'=>'Авто обновл.',
'CHANNEL'=>'Канал',
'ADD_URL'=>'Добавить URL',
'OPEN'=>'Открыть',
'SEND_TO_HOME'=>'Отпр. домой',

'EXT_ID'=>'Использовать элемент',
'VISIBLE_DELAY'=>'Задержка при ротации',

'TREE_VIEW'=>'В виде Дерева',
'LIST_VIEW'=>'В виде Списка',

'FILTER_BY_CLASS'=>'Фильтр по Классу',
'FILTER_BY_LOCATION'=>'Фильтр по Расположению',

'PHOTO'=>'Фотография',
'DEFAULT_USER'=>'пользователь по-умолчанию для системы',
'IS_ADMIN'=>'администратор системы',

'COUNTER_REQUIRED'=>'Количество попыток',
'COUNTER_REQUIRED_COMMENT'=>'(0 для переключения с первого раза)',

'ACCESS_CONTROL'=>'Контроль доступа',

'SECURITY_OBJECT_ID'=>'Объект защиты',
'SECURITY_TERMINALS'=>'Доступ с терминалов',
'SECURITY_USERS'=>'Доступно для пользователей',
'SECURITY_TIMES'=>'Доступно в часы',
'ALLOW_EXCEPT_ABOVE'=>'всегда доступно за исключением выбранного',

'INLINE_POSITION'=>'Расположить на уровне предыдущего элемента',

'SUB_PRELOAD'=>'Загружать дочерние элементы в раскрывающуюся область',

'RUN_PERIODICALLY'=>'Выполнять периодически',
'RUN_TIME'=>'Время запуска',
'RUN_WEEKDAYS'=>'Дни недели',
'WEEK_SUN'=>'Воскресенье',
'WEEK_MON'=>'Понедельник',
'WEEK_TUE'=>'Вторник',
'WEEK_WED'=>'Среда',
'WEEK_THU'=>'Четверг',
'WEEK_FRI'=>'Пятница',
'WEEK_SAT'=>'Суббота',

'PARENT_CONTEXT'=>'Доступно в контексте',
'IS_CONTEXT'=>'использовать как контекст',
'TIMEOUT'=>'Время ожидания команды',
'SET_CONTEXT_TIMEOUT'=>'По истечении времени переключить в',
'TIMEOUT_CODE'=>'По истечении времени выполнить',
'GLOBAL_CONTEXT'=>'глобальный контекст',

'LAST_RULE'=>'не проверять другие шаблоны при совпадении',

'SETTINGS_SECTION_'=>'Общие',
'SETTINGS_SECTION_HOOK'=>'Обработчики',

'DEVICE_ID'=>'ID устройства',
'REQUEST_TYPE'=>'Тип запроса',
'REQUEST_START'=>'Стартовый адрес',
'REQUEST_TOTAL'=>'Кол-во элементов',
'RESPONSE_CONVERT'=>'Преобразование данных',
'CHECK_NEXT'=>'Следующая проверка',
'CODE_TYPE'=>'Использовать для программирования',


'GENERAL'=>'Общее',
'TIME'=>'Время',
'LOGIC'=>'Логика',
'LOOPS'=>'Циклы',
'MATH'=>'Математика',
'TEXT'=>'Текст',
'LISTS'=>'Списки',
'VARIABLES'=>'Переменные',
'FUNCTIONS'=>'Функции',

'DO_NOTHING'=>'Ничего не делать',
'DO_ONCLICK'=>'Выполнить при клике',

'STYLE'=>'Стиль',
'PLACE_IN_CONTAINER'=>'Распложить в контейнере',
'POSITION_TYPE'=>'Позиционирование',
'POSITION_TYPE_ABSOLUTE'=>'Абсолютное',
'POSITION_TYPE_SIDE'=>'Друг за другом',

'CONTAINER'=>'Контейнер',
'INFORMER'=>'Информер',

'NAV_LINK'=>'Нав. ссылка (новое окно)',
'WARNING'=>'Уведомление',
'NAV_LINK_GO'=>'Нав. ссылка (переход)',

'TOOLS'=>'Инструменты',
'COLOR'=>'Цвет',

'WALLPAPER'=>'Обои',

'ADDITIONAL_STATES'=>'Дополнительные состояния',
'MODE_SWITCH'=>'Индикатор режима',
'HIGH_ABOVE'=>'Значение выше',
'LOW_BELOW'=>'Значение ниже',
'ADDITIONAL_STATES_NOTE'=>'(вы можете использовать конструкцию %object.property% в качестве значений границ)',
'UNIT'=>'Единица измерения',

'COUNTER'=>'Счётчик',
'USE_CLASS_SETTINGS'=>'использовать настройки свойств класса',

'USING_LATEST_VERSION'=>'Вы используете последнюю версию!',
'LATEST_UPDATES'=>'Последние обновления',
'UPDATE_TO_THE_LATEST'=>'Обновить систему',
'SAVE_BACKUP'=>'Резервная копия',
'CREATE_BACKUP'=>'Создать резервную копию',
'UPLOAD_BACKUP'=>'Восстановить резервную копию', 
'CONTINUE'=>'Продолжить', 
'RESTORE'=>'Восстановить', 

'SHOW'=>'Показать', 
'HIDE'=>'Скрыть', 

'UPDATING'=>'Вкл. в обновление',
'NOT_UPDATING'=>'Не обновляется',

'SCRIPTS'=>'Сценарии',
'CLASSES'=>'Классы/объекты',
'CLASS_PROPERTIES'=>'Свойства класса',
'CLASS_METHODS'=>'Методы класса',
'CLASS_OBJECTS'=>'Объекты класса',
'OBJECT_PROPERTIES'=>'Свойства объекта',
'OBJECT_METHODS'=>'Методы объекта',


'TEST'=>'test'



/* end module names */



);

foreach ($dictionary as $k=>$v) {
 if (!defined('LANG_'.$k)) {
  define('LANG_'.$k, $v);
 }
}
