<?php
/**
 * Russian language file
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 * @version 1.0
 */


$dictionary = array(

    /* general */
    'WIKI_URL' => 'http://smartliving.ru/',
    'KB_URL'=>'https://kb.smartliving.ru/',
    'DEFAULT_COMPUTER_NAME' => 'Алиса',
    'WELCOME_GREETING' => 'Добро пожаловать!',
    'WELCOME_TEXT' => 'Спасибо, что пользуйтесь MajorDoMo -- открытой платформой домашней автоматизации. <br/><br/>Узнайте больше и присоединяйтесь к сообществу: <a href="<#LANG_WIKI_URL#>" target=_blank>Веб-сайт</a> | <a href="<#LANG_WIKI_URL#>forum/" target=_blank>Форум</a> | <a href="https://www.facebook.com/SmartLivingRu" target=_blank>Facebook страница</a> <br/>&nbsp;<br/>&nbsp;<br/><small>P.S. Вы можете изменить или удалить эту страницу через <a href="/admin.php?pd=&md=panel&inst=&action=layouts">Панель управления</a></small>',
    'CONTROL_PANEL' => 'Панель управления',
    'TERMINAL' => 'Терминал',
    'USER' => 'Пользователь',
    'SELECT' => 'выбрать',
    'CONTROL_MENU' => 'Меню',
    'YOU_ARE_HERE' => 'Вы здесь',
    'FRONTEND' => 'Веб-сайт',
    'MY_ACCOUNT' => 'Мой аккаунт',
    'LOGOFF' => 'Выйти',
    'CONSOLE' => 'Консоль',
    'CONSOLE_RETRY' => 'Повтор',
    'MODULE_DESCRIPTION' => 'Описание модуля',
    'STILL_WORKING' => 'Загружаются данные... Нажмите',
    'CLICK_HERE' => 'здесь',
    'TAKES_TOO_LONG' => ', если процесс загрузки занимает слишком много времени.',
    'SUBMIT_DIAGNOSTIC'=>'Отправка данных диагностики',

    'GENERAL_SENSORS' => 'Сенсоры',
    'GENERAL_OPERATIONAL_MODES' => 'Режимы работы',
    'GENERAL_ENERGY_SAVING_MODE' => 'Энергосбережение',
    'GENERAL_SECURITY_MODE' => 'Безопасность',
    'GENERAL_NOBODYS_HOME_MODE' => 'Никого нет дома',
    'GENERAL_WE_HAVE_GUESTS_MODE' => 'У нас гости',
    'GENERAL_NIGHT_MODE' => 'Ночной режим',
    'GENERAL_DARKNESS_MODE' => 'Тёмное время суток',

    'GENERAL_CLIMATE' => 'Климат',
    'GENERAL_WEATHER_FORECAST' => 'Прогноз погоды',
    'GENERAL_TEMPERATURE_OUTSIDE' => 'Температура за окном',
    'GENERAL_GRAPHICS' => 'Графики',

    'GENERAL_SECURITY_CAMERA' => 'Камеры наблюдения',
    'GENERAL_EVENTS_LOG' => 'История событий',

    'GENERAL_SERVICE' => 'Сервис',

    'GENERAL_GREEN' => 'Зелёный',
    'GENERAL_YELLOW' => 'Жёлтый',
    'GENERAL_RED' => 'Красный',
    'GENERAL_CHANGED_TO' => 'изменился на',
    'GENERAL_RESTORED_TO' => 'восстановился на',
    'GENERAL_SYSTEM_STATE' => 'Системный статус',
    'GENERAL_SECURITY_STATE' => 'Статус безопасности',
    'GENERAL_COMMUNICATION_STATE' => 'Статус связи',
    'GENERAL_STOPPED' => 'остановлен',
    'GENERAL_CYCLE' => 'цикл',
    'GENERAL_NO_INTERNET_ACCESS' => 'Нет доступа в Интернет',
    'GENERAL_SETTING_UP_LIGHTS' => 'Настраиваю освещение',
    'GENERAL_CONTROL' => 'Управление',
    'GENERAL_INSIDE' => 'Дома',
    'GENERAL_OUTSIDE' => 'На улице',

    'SECTION_OBJECTS' => 'Объекты',
    'SECTION_APPLICATIONS' => 'Приложения',
    'SECTION_DEVICES' => 'Устройства',
    'SECTION_SETTINGS' => 'Настройки',
    'SECTION_SYSTEM' => 'Система',
    'SECTION_PANEL'=>'Панель',

    /* end general */

    /* module names */
    'APP_GPSTRACK' => 'GPS-трекер',
    'APP_PLAYER' => 'Плеер',
    'APP_MEDIA_BROWSER' => 'Медиа',
    'APP_PRODUCTS' => 'Продукты',
    'APP_TDWIKI' => 'Блокнот',
    'APP_WEATHER' => 'Погода',
    'APP_CALENDAR' => 'Календарь',
    'APP_READIT' => 'Присл. ссылки',
    'APP_QUOTES' => 'Цитаты',
    'APP_ALARMCLOCK' => 'Будильник',
    'APP_OPENWEATHER' => 'Погода от OpenWeatherMap',
    'SYS_DATEFORMAT' => 'Формат даты',
    'APP_YATRAFFIC' => 'Яндекс.Пробки',
    'APP_CHATBOX' => 'Сообщения',

    'MODULE_OBJECTS_HISTORY' => 'История объектов',
    'MODULE_BT_DEVICES' => 'Bluetooth-устройства',
    'MODULE_CONTROL_MENU' => 'Меню управления',
    'MODULE_OBJECTS' => 'Объекты',
    'MODULE_PINGHOSTS' => 'Устройства Online',
    'MODULE_SCRIPTS' => 'Сценарии',
    'MODULE_USB_DEVICES' => 'USB-устройства',
    'MODULE_WATCHFOLDERS' => 'Папки',
    'MODULE_LAYOUTS' => 'Домашние страницы',
    'MODULE_LOCATIONS' => 'Расположение',
    'MODULE_RSS_CHANNELS' => 'Каналы RSS',
    'MODULE_SETTINGS' => 'Общие настройки',
    'MODULE_TERMINALS' => 'Терминалы',
    'MODULE_USERS' => 'Пользователи',
    'MODULE_EVENTS' => 'События',
    'MODULE_JOBS' => 'Задания',
    'MODULE_MASTER_LOGIN' => 'Пароль доступа',
    'MODULE_SAVERESTORE' => 'Проверка обновлений',
    'MODULE_WEBVARS' => 'Веб-переменные',
    'MODULE_PATTERNS' => 'Шаблоны поведения',
    'MODULE_ONEWIRE' => '1-Wire',
    'MODULE_SCENES' => 'Сцены',
    'MODULE_SNMP' => 'SNMP',
    'MODULE_ZWAVE' => 'Z-Wave',
    'MODULE_SECURITY_RULES' => 'Правила безопасности',
    'MODULE_MQTT' => 'MQTT',
    'MODULE_MODBUS' => 'ModBus',
    'MODULE_CONNECT' => 'CONNECT',
    'MODULE_MARKET' => 'Маркет дополнений',
    'MODULE_MYBLOCKS' => 'Мои блоки',
    'MODULE_TEXTFILES' => 'Текстовые файлы',
    'MODULE_SOUNDFILES' => 'Звуковые файлы',
    'MODULE_SYSTEM_ERRORS' => 'Ошибки системы',
    'MODULE_MODULES' => 'Модули',
    'MODULE_USERLOG' => 'Журнал действий',

    'SCENE_HIDDEN' => 'Не включать в список переключающихся сцен',
    'SCENE_AUTO_SCALE' => 'Автоматически изменять размер сцены по ширине экрана',

    'SETUP' => 'Настроить',


    'DATA_SAVED' => 'Данные сохранены!',
    'ALL' => 'Все',
    'EXECUTE' => 'Выполнить',
    'SCRIPT' => 'Сценарий',
    'CODE' => 'Код',


    'CALL_PARENT_METHOD' => 'Вызывать родительский метод',


    'BEFORE_CODE_EXECUTION' => 'перед выполнением кода',


    'AFTER_CODE_EXECUTION' => 'после выполнения кода',
    'NEVER' => 'никогда',
    'UPDATE' => 'Обновить',
    'CANCEL' => 'Отмена',

    'MAKE_COPY' => 'Создать копию (клонировать)',

    'ARE_YOU_SURE' => 'Вы уверены? Пожалуйста, подтвердите операцию.',
    'DELETE' => 'Удалить',

    'DELETE_SELECTED' => 'Удалить выбранное',
    'EXPORT_SELECTED' => 'Экспортировать выбранное',

    'CALL_METHOD' => 'Вызов метода',


    'BY_URL' => 'По ссылке',


    'COMMAND_LINE' => 'Через командную строку',


    'FILLOUT_REQURED' => 'Пожалуйста, заполните необходимые поля!',


    'NEW_OBJECT' => 'Новый объект',
    'TITLE' => 'Название',
    'CLASS' => 'Класс',
    'DESCRIPTION' => 'Описание',
    'LOCATION' => 'Местоположение',
    'ADD' => 'Добавить',
    'BACK' => 'Назад',
    'OBJECT' => 'Объект',
    'DETAILS' => 'Детали',
    'PROPERTIES' => 'Свойства',
    'METHODS' => 'Методы',
    'HISTORY' => 'История',


    'ADD_NEW_OBJECT' => 'Добавить новый объект',
    'PAGES' => 'Страницы',
    'EDIT' => 'Редактировать',


    'NO_OBJECTS_DEFINED' => 'Нет заданных объектов',


    'ADD_NEW_PROPERTY' => 'Добавить новое свойство',


    'NEW_RECORD' => 'Новая запись',
    'PATTERN' => 'Шаблон',


    'TIME_LIMIT' => 'Ограничение по времени',
    'SECONDS' => 'секунд',


    'EXECUTE_ON_MATCH' => 'Выполнить при совпадении',
    'SUBMIT' => 'Сохранить',


    'ADD_NEW_RECORD' => 'Добавить новую запись',
    'EDIT_RECORD' => 'Редактирование записи',


    'NO_RECORDS_FOUND' => 'Нет данных',
    'COMMAND' => 'Команда',


    'RUN_IN' => 'Выполнить через',
    'MINUTES' => 'минуты',
    'HOURS' => 'часы',
    'PROCESSED' => 'обработано',


    'IN_QUEUE' => 'в очереди',


    'NEW_SCRIPT' => 'Новый сценарий',


    'EXECUTE_SCRIPT_AFTER_UPDATE' => 'выполнить после сохранения',


    'RUN_BY_URL' => 'Запуск по ссылке',


    'ADD_NEW_SCRIPT' => 'Добавить новый сценарий',


    'GENERAL_SETTINGS' => 'Общие настройки',


    'SETTINGS_UPDATED' => 'Настройки сохранены!',


    'DEFAULT_VALUE' => 'Значение по умолчанию',


    'RESET_TO_DEFAULT' => 'Сбросить',
    'SEARCH' => 'Поиск',
    'USERNAME' => 'Имя пользователя',
    'NAME' => 'Имя',
    'EMAIL' => 'E-mail',
    'SKYPE' => 'Skype',


    'MOBILE_PHONE' => 'Мобильный телефон',


    'ADD_METHOD' => 'Добавить новый метод',


    'PARENT_METHODS' => 'Родительские методы:',
    'OVERWRITE' => 'Переписать',
    'ONLY_CLASSES' => 'Не импортировать объекты',

    'NEW_METHOD' => 'Новый метод',
    'HOME' => 'Начало',
    'OFF' => 'Выкл',
    'ON' => 'Вкл',


    'ADD_NEW_SECTION' => 'Добавить новый раздел',
    'EXPAND' => 'Расширить',


    'PARENT_MENU_ITEM' => 'Родительский пункт меню',
    'PRIORITY' => 'Приоритет',
    'TYPE' => 'Тип',
    'LABEL' => 'Подпись',


    'NEW_WINDOW' => 'Новое окно',
    'URL' => 'Ссылка',


    'JS_COMMAND' => 'JavaScript команда',
    'BUTTON' => 'Кнопка',


    'ON_OFF_SWITCH' => 'Выключатель',


    'SELECT_BOX' => 'Поле выбора',


    'SLIDER_BOX' => 'Слайдер',


    'PLUS_MINUS_BOX' => 'Плюс-минус',


    'TIME_PICKER' => 'Выбор времени',


    'TEXT_BOX' => 'Текстовое поле',
    'DATE_BOX' => 'Дата',
    'COLOR_PICKER' => 'Выбор цвета',


    'CUSTOM_HTML_BOX' => 'HTML-блок',
    'ICON' => 'Иконка',


    'MIN_VALUE' => 'Мин. значение',


    'MAX_VALUE' => 'Макс. значение',


    'STEP_VALUE' => 'Шаг изменений',
    'DATA' => 'Данные',
    'INTERFACE' => 'Интерфейс',


    'AUTO_UPDATE_PERIOD' => 'Период автообновления',
    'POLLING_PERIOD' => 'Период опроса',


    'CURRENT_VALUE' => 'Текущее значение',
    'PROPERTY' => 'Свойство',


    'ONCHANGE_OBJECT' => 'Запускать Объект',
    'ONCHANGE_METHOD' => 'Запускать метод при изменении',
    'METHOD' => 'Метод',
    'ONCHANGE_SCRIPT' => 'Сценарий',
    'ONCHANGE_CODE' => 'Код',


    'TARGET_WINDOW' => 'Окно',
    'WIDTH' => 'Ширина',
    'HEIGHT' => 'Высота',


    'ON_THE_SAME_LEVEL' => 'На этом уровне',


    'CHILD_ITEMS' => 'Дочерние пункты',
    'ADDED' => 'Добавлено',
    'VALUE' => 'Значение',


    'OLD_VALUE' => 'Старое значение',


    'NEW_VALUE' => 'Новое значение',
    'UPDATES' => 'Обновления',


    'NO_UPDATES_AVAILABLE' => 'Нет доступных обновлений',


    'NEW_VERSION' => 'Новая версия',


    'INSTALL_NEW_MODULES' => 'Установить новые модули',


    'NO_MODULES_AVAILABLE' => 'Нет доступных модулей',


    'GET_LIST_OF_MODULES' => 'Получить список модулей',


    'SUBMIT_NEWER_FILES' => 'Отправить новые файлы',


    'NO_FILES_TO_SUBMIT' => 'Нет файлов для отправки',
    'FOLDER' => 'Папка',


    'YOUR_NAME' => 'Ваше имя',
    'YOUR_EMAIL' => 'Ваш e-mail',
    'NOTES' => 'Заметки',


    'SUBMIT_SELECTED_FILES' => 'Отправить выбранные файлы',


    'CHECK_FILES_FOR_SUBMIT' => 'Выберите файлы для отправки',
    'DESIGN' => 'Дизайн',


    'FILES_UPLOADED' => 'Файлы загружены',


    'CLEAR_TEMPORARY_FOLDER' => 'Очистить временную папку',


    'ADD_NEW_CLASS' => 'Добавить новый класс',
    'OBJECTS' => 'Объекты',
    'EXPORT' => 'Экспорт',
    'EXPORT_CLASS_FULL' => 'Экспорт Класса и Объектов',
    'EXPORT_CLASS_NO_OBJECTS' => 'Экспорт Класса (без объектов)',


    'IMPORT_CLASS_FROM_FILE' => 'Импортировать класс из файла',
    'IMPORT' => 'Импортировать',


    'NEW_CLASS' => 'Новый класс',


    'PARENT_CLASS' => 'Родительский класс',


    'DO_NOT_SAVE_CLASS_ACTIVITY' => 'не сохранять активность объектов класса в лог',
    'MAIN' => 'Основное',


    'STRING_BACK' => 'Назад',
    'STRING_SUCCESS' => 'Данные были сохранены!',
    'STRING_ERROR' => 'Ошибка',
    'STRING_NEW_RECORD' => 'Новая запись',


    'SHOUTROOMS_TITLE' => 'Название',
    'SHOUTROOMS_PRIORITY' => 'Приоритет',


    'FORM_SUBMIT' => 'Сохранить',
    'FORM_ADD' => 'Добавить',
    'FORM_CANCEL' => 'Отмена',


    'STRING_ADD_NEW' => 'Добавить',


    'SHOUTROOMS_STRING_PUBLIC' => 'Открытая',
    'SHOUTROOMS_STRING_PRIVATE' => 'Приватная',


    'STRING_EDIT' => 'Редактировать',
    'STRING_DELETE' => 'Удалить',
    'STRING_NOT_FOUND' => 'Не найдено',


    'SHOUTROOMS_STRING_SHOUTROOMS' => 'ShoutRooms',


    'NEW_LOCATION' => 'Новое местоположение',


    'ADD_NEW_LOCATION' => 'Добавить новое местоположение',
    'LOADING' => 'Загрузка...',


    'PLEASE_LOGIN' => 'Пожалуйста, войдите в систему.',
    'SEND' => 'Отправить',


    'SHOUTBOX_STRING_DELETE_ALL' => 'Удалить всё',


    'STRING_PAGES' => 'Страницы',


    'MEMBERS_MEMBER' => 'Пользователь',


    'SHOUTBOX_MESSAGE' => 'Сообщение',
    'SHOUTBOX_ADDED' => 'Добавлено',


    'STRING_DELETE_CONFIRM' => 'Вы уверены?',


    'DELETE_UNKNOWN_DEVICES' => 'Удалить неизвестные устройства',
    'SERIAL' => 'Серийный номер',


    'FIRST_ATTACHED' => 'Подключено впервые',


    'LAST_ATTACHED' => 'Подключено в последний раз',


    'EXECUTE_ON_ATTACH' => 'Выполнить при подключении',
    'HOSTNAME' => 'Хост (адрес)',


    'SEARCH_WORD' => 'Искать слово',


    'ONLINE_ACTION' => 'Действие при переходе в Online',


    'OFFLINE_ACTION' => 'Действие при переходе в Offline',


    'ONLINE_CHECK_INTERVAL' => 'Интервал проверки (когда online)',


    'OFFLINE_CHECK_INTERVAL' => 'Интервал проверки (когда offline)',
    'LOG' => 'Лог событий',


    'ADD_NEW_HOST' => 'Добавить новый хост',
    'ONLINE' => 'Online',
    'OFFLINE' => 'Offline',
    'UNKNOWN' => 'Неизвестно',


    'DELETE_ALL_UNKNOWN_DEVICES' => 'Удалить все неизвестные устройства',
    'DELETE_FOUND_ONCE' => 'Удалить все устройства, обнаруженные только один раз',


    'FOUND_FIRST' => 'Обнаружено впервые',
    'FOUND_LAST' => 'Обнаружено в последний раз',


    'PAST_DUE' => 'Пропущено',
    'TODAY' => 'Сегодня',


    'NOTHING_TO_DO' => 'Нечего делать... Везёт же!',
    'SOON' => 'Скоро',


    'DONE_RECENTLY' => 'Недавно выполнено',
    'PREVIEW' => 'Просмотр',


    'SYSTEM_NAME' => 'Системное имя',
    'EVENT' => 'Событие',
    'TASK' => 'Задача',
    'DONE' => 'Готово',
    'DATE' => 'Дата',


    'NO_DUE_DATE' => 'без конкретной даты',


    'IS_REPEATING' => 'повторяющееся',
    'YEARLY' => 'Ежегодно',
    'MONTHLY' => 'Ежемесячно',
    'WEEKLY' => 'Еженедельно',
    'OTHER' => 'Другое',


    'RESTORE_IN' => 'Восстановить через',


    'IN_DAYS' => 'дней',


    'AFTER_COMPLETION' => 'после выполнения',


    'MORE_DETAILS' => 'Детали',


    'ANY_USER' => 'Любой пользователь',
    'ANY_LOCATION' => 'Любое местоположение',


    'RUN_SCRIPT' => 'Запустить сценарий',


    'WHEN_TASK_WILL_BE_DONE' => 'когда задача будет выполнена',


    'SIMILAR_ITEMS' => 'Похожие записи',


    'LOCATION_CODE' => 'Код корода (city id)',
    'REFRESH' => 'Обновить',


    'ERROR_GETTING_WEATHER_DATA' => 'Ошибка получения данных о погоде',


    'CLEAR_ALL' => 'Очистить всё',


    'ADD_NEW_CHANNEL' => 'Добавить новый канал',


    'LAST_CHECKED' => 'Последняя проверка',


    'RSS_CHANNELS' => 'RSS-каналы',
    'RSS_NEWS' => 'RSS-новости',


    'NEW_CHANNEL' => 'Новый канал',


    'SOURCE_URL' => 'URL-источник',


    'CHECK_EVERY' => 'Проверять каждые',


    'EXECUTE_FOR_NEW_RECORDS' => 'Выполнять для новых записей',


    'TERMINAL_FROM' => 'From terminal',


    'USER_FROM' => 'From user',
    'USER_TO' => 'To user',
    'WINDOW' => 'Окно',
    'EXPIRE' => 'Истекает',


    'TERMINAL_TO' => 'To terminal',


    'NEW_PAGE' => 'Новая страница',
    'APP' => 'Приложение',
    'QUANTITY' => 'Кол-во',
    'ACTION' => 'Действие',
    'CATEGORY' => 'Категория',
    'ADD_NEW_CATEGORY' => 'Добавить категорию',
    'PRODUCT' => 'Продукт',


    'DELETE_CATEGORY' => 'Удалить категорию',
    'MISSING' => 'Отсутствующие',


    'IN_STORAGE' => 'В наличии',
    'BUY' => 'Купить',
    'CODES' => 'Коды',
    'PARENT' => 'Родитель',
    'ROOT' => '(корень)',


    'CREATE_NOTE' => 'Создать заметку',
    'TOTAL' => 'Всего',


    'EXPIRE_IN' => 'Истекает через',
    'DAYS' => 'дней',
    'CATEGORIES' => 'Категории',


    'ALL_PRODUCTS' => 'Все продукты',
    'EXPIRED' => 'Истёкшие',


    'SHOPPING_LIST' => 'Покупки',
    'PRODUCTS' => 'Продукты',
    'IMAGE' => 'Изображение',


    'EXPIRATION_DATE' => 'Дата истечения срока годности',


    'DEFAULT_EXPIRE_IN' => 'По-умолчанию "истекает через"',
    'UPDATED' => 'Обновлено',


    'RECOMENDED_QUANTITY' => 'Рекомендуемое кол-во',


    'DELETE_FROM_DATABASE' => 'Удалить из базы данных',


    'RESCAN_DEVICES' => 'Сканировать устройства',


    'NO_DEVICES_FOUND' => 'Нет устройств',
    'ID' => 'ID',


    'CHECK_INTERVAL' => 'Интервал проверки',


    'LINKED_OBJECT' => 'Связанный объект',
    'LINKED_PROPERTY' => 'Связанное св-во',
    'SET' => 'установить',


    'ONCHANGE_ACTION' => 'Действие при изменении',
    'RESET' => 'Сбросить',


    'MORE_INFO' => 'Детали',
    'PATH' => 'Путь',


    'INCLUDE_SUBFOLDERS' => 'включая подпапки',


    'CHECK_MASK' => 'Маска файлов',
    'EXAMPLE' => 'Пример',


    'AUTHORIZATION_REQUIRED' => 'требуется авторизация',
    'PASSWORD' => 'Пароль',


    'SOURCE_PAGE_ENCODING' => 'Кодировка страницы',
    'OPTIONAL' => 'не обязательно',


    'BY_DEFAULT' => 'по умолчанию',


    'SEARCH_PATTERN' => 'Шаблон поиска',


    'LATEST_VALUE' => 'Последнее значение',


    'ADD_NEW' => 'Добавить',


    'REFRESH_ALL' => 'Обновить все',


    'NEW_PROPERTY' => 'Новое свойство',


    'KEEP_HISTORY_DAYS' => 'Хранить историю (дней)',


    'DO_NOT_KEEP' => 'не хранить историю',

    'KEEP_HISTORY' => 'хранить историю',

    'PARENT_PROPERTIES' => 'Родительские свойства',


    'ADD_NEW_COLLECTION' => 'Добавить новую коллекцию',


    'NEW_SKIN_INSTALLED' => 'Новый skin установлен',


    'INCORRECT_FILE_FORMAT' => 'Некорректный формат файла',


    'CANNOT_CREATE_FOLDER' => 'Не могу создать папку',


    'SKIN_ALREADY_EXISTS' => 'Skin уже существует',


    'UPLOAD_NEW_SKIN' => 'Загрузка нового skin-а',


    'INSTALL_SKIN' => 'Установить skin',


    'NO_ADDITIONAL_SKINS_INSTALLED' => 'Нет дополнительных skin-ов',
    'BACKGROUND' => 'Фоновое изображение',


    'SCENE' => 'Сцена',
    'ADD_NEW_SCENE' => 'Добавить новую сцену',
    'USE_ELEMENT_TO_POSITION_RELATED' => 'Позиционировать относительно',
    'NO_RELATED' => 'Левого верхнего угла',
    'TOP' => 'Отступ сверху',
    'LEFT' => 'Отступ слева',
    'STATES' => 'Состояния',
    'ADD_NEW_STATE' => 'Добавить новое состояние',


    'RUN_SCRIPT_ON_CLICK' => 'Выполнить сценарий при клике',
    'SHOW_MENU_ON_CLICK' => 'Показать меню при клике',
    'SHOW_HOMEPAGE_ON_CLICK' => 'Показать домашнюю страницу при клике',
    'SHOW_URL_ON_CLICK' => 'Открыть ссылку при клике',
    'SHOW_SCENE_ON_CLICK' => 'Показать другую сцену',


    'DISPLAY_CONDITION' => 'Условие отображения',


    'ALWAYS_VISIBLE' => 'всегда показывать',
    'SIMPLE' => 'простое',
    'ADVANCED' => 'расширенное',


    'SWITCH_SCENE_WHEN_ACTIVATED' => 'переключить на сцену при активации',

    'APPEAR_ANIMATION' => 'Анимация появления',
    'APPEAR_LEFTTORIGHT' => 'Слева-на-право',
    'APPEAR_RIGHTTOLEFT' => 'Справа-на-лево',
    'APPEAR_TOPTOBOTTOM' => 'Сверху-вниз',
    'APPEAR_BOTTOMTOTOP' => 'Снизу-вверх',
    'APPEAR_BLINK' => 'Моргание',
    'APPEAR_SCALE' => 'Масштаб',


    'ADD_NEW_ELEMENT' => 'Добавить новый элемент',
    'ELEMENTS' => 'Элементы',


    'CAN_PLAY_MEDIA' => 'может проигрывать медиа-контент',


    'PLAYER_TYPE' => 'Тип плеера',
    'DEFAULT' => 'По умолчанию',


    'MAKE_SURE_YOU_HAVE_CONTROL_OVER_HTTP_ENABLED' => 'Проверьте, что включена возможность управления по HTTP-протоколу',


    'PLAYER_PORT' => 'Порт доступа к плееру',
    'PLAYER_USERNAME' => 'Имя пользователя доступа к плееру',
    'PLAYER_PASSWORD' => 'Пароль доступа к плееру',
    'DEVICE' => 'Устройство',


    'CLEAR_LOG' => 'Очистить лог',


    'OPTIMIZE_LOG' => 'Оптимизировать лог',
    'LATITUDE' => 'Широта',
    'LONGITUDE' => 'Долгота',
    'SPEED' => 'Скорость',
    'ACCURACY' => 'Точность',


    'BATTERY_LEVEL' => 'Уровень заряда',
    'CHARGING' => 'На зарядке',
    'MAP' => 'Карта',
    'RANGE' => 'Радиус действия',
    'ALTITUDE' => 'Высота',
    'PROVIDER' => 'Провайдер',

    'LOCATIONS' => 'Места',
    'DEVICES' => 'Устройства',
    'ACTIONS' => 'Действия',
    'HOME_LOCATION' => 'Дом (место)',

    'ACTION_TYPE' => 'Тип действия',
    'EXECUTED' => 'Выполнено',


    'VIRTUAL_USER' => 'Виртуальный пользователь',

    'WIND' => 'Ветер',
    'PRESSURE' => 'Давление',
    'HUMIDITY' => 'Влажность',
    'GET_AT' => 'Обновлено',
    'MMHG' => 'мм рт.ст.',
    'HPA' => 'гПа',
    'M_S' => 'м/с',
    'N' => 'С',
    'NNE' => 'ССВ',
    'NE' => 'СВ',
    'ENE' => 'ВСВ',
    'E' => 'В',
    'ESE' => 'ВЮВ',
    'SE' => 'ЮВ',
    'SSE' => 'ЮЮВ',
    'S' => 'Ю',
    'SSW' => 'ЮЮЗ',
    'SW' => 'ЮЗ',
    'WSW' => 'ЗЮЗ',
    'W' => 'З',
    'WNW' => 'ЗСЗ',
    'NW' => 'CЗ',
    'NNW' => 'CCЗ',


    'LONG_OPERATION_WARNING' => 'Внимание: данная операция может занять длительное время (несколько минут). Пожалуйста, дождитесь завершения после запуска.',

    'STARRED' => 'Избранное',

    'USE_BACKGROUND' => 'Использовать фон',
    'YES' => 'Да',
    'NO' => 'Нет',

    'USE_JAVASCRIPT' => 'Дополнительный код JavaScript',
    'USE_CSS' => 'Дополнительный код CSS',

    'PERIOD' => 'Период',
    'PERIOD_TODAY' => 'Сегодня',
    'PERIOD_DAY' => 'Сутки (24 часа)',
    'PERIOD_WEEK' => 'Неделя',
    'PERIOD_MONTH' => 'Месяц',
    'PERIOD_CUSTOM' => 'Выбрать',
    'SEARCH' => 'Искать',
    'SHOWHIDE' => 'Показать/Скрыть',

    'AUTO_UPDATE' => 'Авто обновл.',
    'CHANNEL' => 'Канал',
    'ADD_URL' => 'Добавить URL',
    'OPEN' => 'Открыть',
    'SEND_TO_HOME' => 'Отпр. домой',

    'EXT_ID' => 'Использовать элемент',
    'VISIBLE_DELAY' => 'Задержка при ротации',

    'TREE_VIEW' => 'В виде Дерева',
    'LIST_VIEW' => 'В виде Списка',

    'FILTER_BY_CLASS' => 'Фильтр по Классу',
    'FILTER_BY_LOCATION' => 'Фильтр по Расположению',

    'PHOTO' => 'Фотография',
    'DEFAULT_USER' => 'пользователь по умолчанию для системы',
    'IS_ADMIN' => 'администратор системы',

    'COUNTER_REQUIRED' => 'Количество попыток',
    'COUNTER_REQUIRED_COMMENT' => '(0 для переключения с первого раза)',

    'ACCESS_CONTROL' => 'Контроль доступа',

    'SECURITY_OBJECT_ID' => 'Объект защиты',
    'SECURITY_TERMINALS' => 'Доступ с терминалов',
    'SECURITY_USERS' => 'Доступно для пользователей',
    'SECURITY_TIMES' => 'Доступно в часы',
    'ALLOW_EXCEPT_ABOVE' => 'всегда доступно за исключением выбранного',

    'INLINE_POSITION' => 'Расположить на уровне предыдущего элемента',

    'SUB_PRELOAD' => 'Загружать дочерние элементы в раскрывающуюся область',

    'RUN_PERIODICALLY' => 'Выполнять периодически',
    'RUN_TIME' => 'Время запуска',
    'RUN_WEEKDAYS' => 'Дни недели',
    'WEEK_SUN' => 'Воскресенье',
    'WEEK_MON' => 'Понедельник',
    'WEEK_TUE' => 'Вторник',
    'WEEK_WED' => 'Среда',
    'WEEK_THU' => 'Четверг',
    'WEEK_FRI' => 'Пятница',
    'WEEK_SAT' => 'Суббота',

    'PARENT_CONTEXT' => 'Доступно в контексте',
    'IS_CONTEXT' => 'использовать как контекст',
    'TIMEOUT' => 'Время ожидания команды',
    'SET_CONTEXT_TIMEOUT' => 'По истечении времени переключить в',
    'TIMEOUT_CODE' => 'По истечении времени выполнить',
    'GLOBAL_CONTEXT' => 'глобальный контекст',

    'LAST_RULE' => 'не проверять другие шаблоны при совпадении',

    'SETTINGS_SECTION_' => 'Общие',
    'SETTINGS_SECTION_HOOK' => 'Обработчики',

    'DEVICE_ID' => 'ID устройства',
    'REQUEST_TYPE' => 'Тип запроса',
    'REQUEST_START' => 'Стартовый адрес',
    'REQUEST_TOTAL' => 'Кол-во элементов',
    'RESPONSE_CONVERT' => 'Преобразование данных',
    'CHECK_NEXT' => 'Следующая проверка',
    'CODE_TYPE' => 'Использовать для программирования',


    'GENERAL' => 'Общее',
    'TIME' => 'Время',
    'LOGIC' => 'Логика',
    'LOOPS' => 'Циклы',
    'MATH' => 'Математика',
    'TEXT' => 'Текст',
    'LISTS' => 'Списки',
    'VARIABLES' => 'Переменные',
    'FUNCTIONS' => 'Функции',

    'DO_NOTHING' => 'Ничего не делать',
    'DO_ONCLICK' => 'Выполнить при клике',

    'STYLE' => 'Стиль',
    'PLACE_IN_CONTAINER' => 'Расположить в контейнере',
    'POSITION_TYPE' => 'Позиционирование',
    'POSITION_TYPE_ABSOLUTE' => 'Абсолютное',
    'POSITION_TYPE_SIDE' => 'Друг за другом',

    'CONTAINER' => 'Контейнер',
    'INFORMER' => 'Информер',

    'NAV_LINK' => 'Нав. ссылка (новое окно)',
    'WARNING' => 'Уведомление',
    'NAV_LINK_GO' => 'Нав. ссылка (переход)',

    'TOOLS' => 'Инструменты',
    'COLOR' => 'Цвет',

    'WALLPAPER' => 'Обои',

    'ADDITIONAL_STATES' => 'Дополнительные состояния',
    'MODE_SWITCH' => 'Индикатор режима',
    'HIGH_ABOVE' => 'Значение выше',
    'LOW_BELOW' => 'Значение ниже',
    'ADDITIONAL_STATES_NOTE' => '(вы можете использовать конструкцию %object.property% в качестве значений границ)',
    'UNIT' => 'Единица измерения',

    'COUNTER' => 'Счётчик',
    'USE_CLASS_SETTINGS' => 'использовать настройки свойств класса',

    'USING_LATEST_VERSION' => 'Вы используете последнюю версию!',
    'LATEST_UPDATES' => 'Последние обновления',
    'UPDATE_TO_THE_LATEST' => 'Обновить систему',
    'SAVE_BACKUP' => 'Резервная копия',
    'CREATE_BACKUP' => 'Создать резервную копию',
    'UPLOAD_BACKUP' => 'Восстановить резервную копию',
    'CONTINUE' => 'Продолжить',
    'RESTORE' => 'Восстановить',

    'SHOW' => 'Показать',
    'HIDE' => 'Скрыть',

    'UPDATING' => 'Вкл. в обновление',
    'NOT_UPDATING' => 'Не обновляется',

    'SCRIPTS' => 'Сценарии',
    'CLASSES' => 'Классы/объекты',
    'CLASS_PROPERTIES' => 'Свойства класса',
    'CLASS_METHODS' => 'Методы класса',
    'CLASS_OBJECTS' => 'Объекты класса',
    'OBJECT_PROPERTIES' => 'Свойства объекта',
    'OBJECT_METHODS' => 'Методы объекта',
    'PORT' => 'Порт',
    'USE_DEFAULT' => 'использовать по-умолчанию',

    'FAVORITES' => 'Избранное',
    'RECENTLY_PLAYED' => 'Недавно проиграно',
    'CLEAR_FAVORITES' => 'Очистить Избранное',
    'CLEAR_HISTORY' => 'Очистить Недавно проигранное',

    'SKIP_SYSTEM' => 'Не реагировать на системные сообщения',
    'ONETIME_PATTERN' => 'Разовый шаблон (будет удален)',

    'PATTERN_ENTER' => 'вход',
    'PATTERN_EXIT' => 'выход',
    'PATTERN_TYPE' => 'Тип шаблона',
    'PATTERN_MESSAGE' => 'На основе сообщений',
    'PATTERN_CONDITIONAL' => 'На основе значений свойств',
    'CONDITION' => 'Условие',
    'ADD_EXIT_CODE' => 'Добавить код "выхода"',
    'ADVANCED_CONFIG' => 'Расширенная настройка',
    'UPDATE_ALL_EXTENSIONS' => 'Обновить все установленные дополнения',

    'SAVE_CHANGES' => 'Сохранить изменения',
    'ADD_PANE' => 'Добавить панель',

    'DATA_KEY' => 'Ключевые данные',
    'DATA_TYPE' => 'Тип данных',
    'DATA_TYPE_GENERAL' => 'Общий формат',
    'DATA_TYPE_IMAGE' => 'Изображение',
    'CLASS_TEMPLATE' => 'Шаблон отображения',

    'TEST' => 'test',

    'MODULES_UPDATES_AVAILABLE'=>'Доступны обновления модулей',
    'SYSTEM_UPDATES_AVAILABLE'=>'Доступны обновления системы',
    'ERRORS_SAVED'=>'Сохранены ошибки',

// DEVICES
    'DEVICES_MODULE_TITLE' => 'Простые устройства',
    'DEVICES_LINKED_WARNING' => 'Внимание: выбор существующего объекта приведёт к привязке его к новому классу.',
    'DEVICES_RELAY' => 'Управляемое реле/Выключатель',
    'DEVICES_DIMMER' => 'Управляемый диммер',
    'DEVICES_RGB' => 'RGB-контроллер',
    'DEVICES_MOTION' => 'Датчик движения',
    'DEVICES_BUTTON' => 'Кнопка',
    'DEVICES_SWITCH' => 'Выключатель',
    'DEVICES_OPENCLOSE' => 'Датчик открытия/закрытия',
    'DEVICES_GENERAL_SENSOR' => 'Общий датчик',
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
    'DEVICES_NOTIFY' => 'Уведомлять при выходе за порог',
    'DEVICES_NORMAL_VALUE' => 'Значение в нормальных пределах',
    'DEVICES_DIRECTION_TIMEOUT' => 'Интервал времени расчета направления изменений (сек)',
    'DEVICES_NOTIFY_STATUS' => 'Уведомлять при смене статуса',
    'DEVICES_NOTIFY_OUTOFRANGE' => 'Значение датчика вышло за порог',
    'DEVICES_NOTIFY_BACKTONORMAL' => 'Значение датчика вернулось к норме',
    'DEVICES_NOTIFY_NOT_CLOSED' => 'Напоминать об открытом состоянии',
    'DEVICES_MOTION_IGNORE' => 'Игнорировать события от устройства, когда никого нет дома',
    'DEVICES_MOTION_TIMEOUT' => 'Время активности (секунд)',
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
    'DEVICES_GROUP_ECO_ON' => 'Включать при выходе из режима экономии',
    'DEVICES_GROUP_SUNRISE' => 'Выключать с рассветом',
    'DEVICES_IS_ACTIVITY' => 'Изменение означает активность в помещении',
    'DEVICES_NCNO' => 'Тип устройства/сенсора',
    'DEVICES_LOADTYPE' => 'Тип устройства',
    'DEVICES_LOADTYPE_VENT' => 'Вентиляция',
    'DEVICES_LOADTYPE_HEATING' => 'Обогрев',
    'DEVICES_LOADTYPE_CURTAINS' => 'Шторы',
    'DEVICES_LOADTYPE_GATES' => 'Ворота',
    'DEVICES_LOADTYPE_LIGHT' => 'Освещение',
    'DEVICES_LOADTYPE_POWER' => 'Разное',

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

    'DEVICES_LINK_SENSOR_PASS' => 'Пересылка данных',
    'DEVICES_LINK_SENSOR_PASS_DESCRIPTION' => 'Пересылка данных от сенсора на другое устройство',

    'DEVICES_LINK_THERMOSTAT_SWITCH' => 'Управление устройством',
    'DEVICES_LINK_THERMOSTAT_SWITCH_DESCRIPTION' => 'Управление другим устройствам в зависимости от статуса термостата',
    'DEVICES_LINK_THERMOSTAT_INVERT' => 'Инвертная установка статуса',


    'DEVICES_UPDATE_CLASSSES' => 'Обновить классы',
    'DEVICES_ADD_OBJECT_AUTOMATICALLY' => 'Создать автоматически',

    'DEVICES_PATTERN_TURNON' => 'включи|зажги',
    'DEVICES_PATTERN_TURNOFF' => 'выключи|потуши|отключи',
    'DEVICES_DEGREES' => 'градусов',
    'DEVICES_STATUS_OPEN' => 'открыт',
    'DEVICES_STATUS_CLOSED' => 'закрыт',
    'DEVICES_STATUS_ALARM' => 'состояние тревоги',
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
    'DEVICES_CAMERA_STREAM_TRANSPORT' =>'Транспорт потока',
    'DEVICES_CAMERA_PREVIEW_TYPE' =>'Предпросмотр',
    'DEVICES_CAMERA_PREVIEW_TYPE_STATIC' =>'Статический снимок',
    'DEVICES_CAMERA_PREVIEW_TYPE_SLIDESHOW' =>'Слайд-шоу',
    'DEVICES_CAMERA_PREVIEW_ONCLICK' =>'Действие по нажатию на изображение',
    'DEVICES_CAMERA_PREVIEW_ONCLICK_ENLARGE' =>'Увеличить изображеие',
    'DEVICES_CAMERA_PREVIEW_ONCLICK_ORIGINAL' =>'Перейти на поток',

    'DEVICES_THERMOSTAT' => 'Термостат',
    'DEVICES_THERMOSTAT_MODE' => 'Режим',
    'DEVICES_THERMOSTAT_ECO_MODE' => 'ECO режим',
    'DEVICES_THERMOSTAT_NORMAL_TEMP' => 'Обычная целевая температура',
    'DEVICES_THERMOSTAT_ECO_TEMP' => 'ECO целевая температура',
    'DEVICES_THERMOSTAT_CURRENT_TEMP' => 'Текущая температура',
    'DEVICES_THERMOSTAT_CURRENT_TARGET_TEMP' => 'Целевая температура',
    'DEVICES_THERMOSTAT_THRESHOLD' => 'Порог срабатывания термостата (0.25 по-умолчанию)',
    'DEVICES_THERMOSTAT_RELAY_STATUS' => 'Статус реле',
    'DEVICES_ALL_BY_TYPE' => 'Все по типам',
    'DEVICES_ALL_BY_ROOM' => 'Все по комнатам',
    'DEVICES_LOAD_TIMEOUT'=>'Таймер изменения статуса нагрузки',
    'GROUPS' => 'Группы',
    'APPLIES_TO' => 'Применительно к',

    'AUTO_LINK' => 'Автоматический запуск сценария',
    'FAVORITE_DEVICE' => 'В списке быстрого доступа',

    'ROOMS' => 'Комнаты',
    'APPEARANCE' => 'Внешний вид',
    'MAINTENANCE' => 'Обслуживание',
    'LIST' => 'Список',
    'DATA_OPTIMIZING' => 'Оптимизация данных',
    'DID_YOU_KNOW' => 'А знаете ли вы что...',
    'NEWS' => 'Новости MajorDoMo',
    'KNOWLEDGE_BASE' => 'База знаний',
    'ACTIVITIES' => 'Поведение',
    'COMMANDS' => 'Команды',
    'ADDON_FILE' => 'Файл дополнения',
    'UPLOAD_AND_INSTALL' => 'Загрузить и установить',
    'ADD_UPDATE_MANUALLY' =>'Добавить/обновить вручную',
    'TURNING_ON' =>'Включаю',
    'TURNING_OFF' =>'Выключаю',
    'PATTERN_TIMER' => 'таймер',
    'PATTERN_DO_AFTER' => 'через',
    'PATTERN_DO_FOR' => 'на',
    'PATTERN_SECOND' => 'секунд',
    'PATTERN_MINUTE' => 'минут',
    'PATTERN_HOUR' => 'час',

    /* end module names */


);

foreach ($dictionary as $k => $v) {
    if (!defined('LANG_' . $k)) {
        define('LANG_' . $k, $v);
    }
}
