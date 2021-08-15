<?php
chdir(dirname(__FILE__) . '/../');
include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");
include_once("./load_settings.php");
include_once(DIR_MODULES . "terminals/terminals.class.php");

set_time_limit(0);

// обьявляем массив обектов дабы не грузить их всегда
$base_terminal = array();

// берем конфигурацию с модуля терминалов - общие настройки
$ter = new terminals();
$ter->getConfig();
//получаем таймут для офлайновых терминалов
if (!$ter->config['TERMINALS_PING_OFFLINE']) {
    $ter->config['TERMINALS_PING_OFFLINE'] = 27;
    if ($ter->config['LOG_ENABLED']) DebMes("Timeout for ping offline terminals is ".$ter->config['TERMINALS_PING_OFFLINE']." minutes", 'terminals');
}
        
//полуйчаем таймаут для онлайновых терминалов
if (!$ter->config['TERMINALS_PING_ONLINE']) {
    $ter->config['TERMINALS_PING_ONLINE'] = 54;
    if ($ter->config['LOG_ENABLED'])  DebMes("Timeout for ping online terminals is ".$ter->config['TERMINALS_PING_ONLINE']." minutes", 'terminals');
}
// получаем таймаут жизни сообщений
if (!$ter->config['TERMINALS_TIMEOUT']) {
    if ($ter->config['LOG_ENABLED']) DebMes("Timeout for message is null minutes, set default 10 minutes", 'terminals');
    $ter->config['TERMINALS_TIMEOUT'] = 10;
}

$checked_time = 0;

//opttmization
setGlobal((str_replace('.php', '', basename(__FILE__))).'Run', time(), 1);
//$cycleVarName='ThisComputer.'.str_replace('.php', '', basename(__FILE__)).'Run';

// set all terminal as free when restart cycle
$term = SQLSelect("SELECT * FROM terminals");
foreach ($term as $t) {
    sg($t['LINKED_OBJECT'] . '.TerminalState', 0);
}
// reset all message when reload cicle
//SQLExec("UPDATE shouts SET SOURCE = '' ");

// проверка на установленность терминалов2
if (!function_exists('restore_terminal_state')) {
    DebMes("то после обновления Мажордомо ОБЯЗАТЕЛЬНО обновите этот модуль");
    DebMes("Если вы в дальнейшем планируете использовать модуль Модификацию Терминалов 2");
    DebMes("Удалите цикл - cycle_terminals.php , поскольку вы не используете модуль Модификацию Терминалов 2");
    setGlobal('cycle_terminalsAutoRestart', '0');
    setGlobal('cycle_terminalsControl', 'stop');
}

// берем последнее сообщение для определения последнего номера и запуска генерации речи
$number_message = SQLSelectOne("SELECT * FROM shouts ORDER BY ID DESC");
$number_message = $number_message['ID'] + 1;

DebMes(date("H:i:s") . " Running " . basename(__FILE__));

while (1) {
    // time update cicle of terminal
    if (time() - $checked_time > 20) {
        $checked_time = time();
        setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', $checked_time, 1);
    }
    
    // проверяем наличие следующего сообщения для запуска генерации речи
    $message = SQLSelectOne("SELECT * FROM shouts WHERE ID = '" . $number_message . "'");
    if ($message) {
        $number_message = $number_message + 1;
        if ($ter->config['LOG_ENABLED'])     DebMes("Run generate media file for Message - " . json_encode($message, JSON_UNESCAPED_UNICODE) . " with EVENT SAY ", 'terminals');
        processSubscriptionsSafe($message['EVENT'], $message); //, 
    } else {
        sleep(1);
    }
    
    // Пингование сервисов офлайн терминалов 
    if (time() - $check_terminaloffline > 60 * $ter->config['TERMINALS_PING_OFFLINE']) {
        $check_terminaloffline = time();
        $term = SQLSelect("SELECT * FROM terminals");
        foreach ($term as $t) {
            if ($t['CANTTS'] AND !$t['TTS_IS_ONLINE']) {
                sg($t['LINKED_OBJECT'] . '.TerminalState', 1);
                pingTerminalSafe($t['NAME'], $t,'CANTTS');
            }
            if ($t['CANPLAY'] AND !$t['PLAYER_IS_ONLINE']) {
                sg($t['LINKED_OBJECT'] . '.PlayerState', 1);
                pingTerminalSafe($t['NAME'], $t,'CANPLAY');
            }
            if ($t['CANRECOGNIZE'] AND !$t['RECOGNIZE_IS_ONLINE']) {
                sg($t['LINKED_OBJECT'] . '.RecognizeState', 1);
                pingTerminalSafe($t['NAME'], $t,'CANRECOGNIZE');
            }
        }
    }     

    // Пингование сервисов онлайн терминалов 
    if (time() - $check_terminalonline > 60 * $ter->config['TERMINALS_PING_ONLINE']) {
        $check_terminalonline = time();
        $term = SQLSelect("SELECT * FROM terminals");
        foreach ($term as $t) {
            if ($t['CANTTS'] AND $t['TTS_IS_ONLINE']) {
                sg($t['LINKED_OBJECT'] . '.TerminalState', 1);
                pingTerminalSafe($t['NAME'], $t,'CANTTS');
            }
            if ($t['CANPLAY'] AND $t['PLAYER_IS_ONLINE']) {
                sg($t['LINKED_OBJECT'] . '.PlayerState', 1);
                pingTerminalSafe($t['NAME'], $t,'CANPLAY');
            }
            if ($t['CANRECOGNIZE'] AND $t['RECOGNIZE_IS_ONLINE']) {
                sg($t['LINKED_OBJECT'] . '.RecognizeState', 1);
                pingTerminalSafe($t['NAME'], $t,'CANRECOGNIZE');
            }
        }
    } 
    
    //время жизни сообщений
    if (time() - $clear_message > 60 * $ter->config['TERMINALS_TIMEOUT']) {
        $clear_message = time();
        $result = SQLSelect("SELECT COUNT(ID) FROM shouts WHERE SOURCE != '' AND ADDED > (NOW() - INTERVAL " . $ter->config['TERMINALS_TIMEOUT'] . " MINUTE)");
        if ($result[0]['COUNT(ID)'] > 0) {
            SQLExec("UPDATE shouts SET SOURCE = '' WHERE SOURCE != '' AND ADDED < (NOW() - INTERVAL " . $ter->config['TERMINALS_TIMEOUT'] . " MINUTE)");
            if ($ter->config['LOG_ENABLED']) DebMes("Clear message - when can not to play. For timeouts - " . $ter->config['TERMINALS_TIMEOUT'], 'terminals');
        }
    }
    
    $out_terminals = getObjectsByProperty('TerminalState', '==', '0');
    foreach ($out_terminals as $terminals) {
        // если нету свободных терминалов пропускаем
        if (!$terminals) {
            if ($ter->config['LOG_ENABLED']) DebMes("Terminal is busy. See properties TerminalState in object " . $terminals, 'terminals');
            sleep(2);
            continue;
        }

        $terminal = SQLSelectOne("SELECT * FROM terminals WHERE LINKED_OBJECT = '" . $terminals . "'");

        // если пустой терминал пропускаем
        if (!$terminal['ID']) {
            DebMes("Cannot find terminal for this object - " . $terminals . ". Object must be deleted.", 'terminals');
            continue;
        }

        // если терминал не воспроизводит сообщения то пропускаем его в этой итерации
        if (!$terminal['CANTTS'] OR !$terminal['TTS_TYPE']) {
            continue;
        }

        // если в терминале отсутствует привязанный обьект или терминал отключен от воспроизведения то выведем ошибку
        if (!$terminal['LINKED_OBJECT'] OR !$terminal['TTS_TYPE']) {
            if ($ter->config['LOG_ENABLED']) DebMes("Cannot find link object or cannot play message for this terminal - " . str_ireplace("terminal_", "", $terminals) . ". Please re-save this terminal for proper operation.", 'terminals');
            $params = array();
            if (!$terminal['LINKED_OBJECT']) {
                $params["ERROR"] = 'Терминал ' . str_ireplace("terminal_", "", $terminals) .' не имеет привязанного обьекта. Для дальнейшей работы терминала необходимо пересохранить его в модуле Терминалы';
            } else if (!$terminal['LINKED_OBJECT']) {
                $params["ERROR"] = 'Терминал ' . str_ireplace("terminal_", "", $terminals) .' отключен для воспроизведения сообщения (непонятно почему попал в список). Для дальнейшей работы терминала необходимо пересохранить его в модуле Терминалы';
            }
            callMethodSafe($terminals . '.MessageError', $params);
            continue;
        }

        // обьявляем новый обьект которого нет в массиве $base_terminal
        if (!$base_terminal[$terminal['TITLE']] AND $terminal['TTS_TYPE'] AND file_exists(DIR_MODULES . 'terminals/tts/' . $terminal['TTS_TYPE'] . '.addon.php')) {
            $file = file_get_contents(DIR_MODULES . 'terminals/tts/' . $terminal['TTS_TYPE'] . '.addon.php');
            if (stristr($file, 'say_media_message') === FALSE) {
                // задаем в массиве тип терминала
                $base_terminal[$terminal['TITLE']]['TYPE'] = 'text_terminal';
            } else {
                $base_terminal[$terminal['TITLE']]['TYPE'] = 'audio_terminal';
            }
            // задаем в массиве нужность восстановления проигрываемого медиа - если есть необходимость
            // то изменится на 1 при передаче аудиосообщений - предназначено для контроля необходимости воспроизведения
            // и уменьшения количества запросов к бд
            $base_terminal[$terminal['TITLE']]['NEEDRESTOREMEDIA'] = 0;
            $base_terminal[$terminal['TITLE']]['ID'] = $terminal['ID'];
            if ($ter->config['LOG_ENABLED']) DebMes("Add class terminal to array tts objects -" . $terminal['TTS_TYPE'], 'terminals');
            continue;
        }

        // берем первоочередное сообщение
        $old_message = SQLSelectOne("SELECT * FROM shouts WHERE SOURCE LIKE '%" . $terminal['ID'] . "^%' ORDER BY ID ASC");

        // если отсутствует сообщение и есть инфа для восстановления состояния терминала или воспроизведения терминала то восстанавливаем состояние
        // и переходим на следующий свободный терминал
        if (!$old_message['ID'] OR !$old_message['SOURCE']) {
            // укажем на то что запущено восстановление состояния медиаплеера
            $base_terminal[$terminal['TITLE']]['NEEDRESTOREMEDIA'] = 0;
            if ($terminal['PLAYER_IS_ONLINE'] AND (gg($terminal['LINKED_OBJECT'] . '.playerdata') OR gg($terminal['LINKED_OBJECT'] . '.terminaldata'))) {
                try {
                    sg($terminal['LINKED_OBJECT'] . '.TerminalState', 1);
                    restore_terminal_stateSafe($terminal);
                    continue;
                } catch (Exception $e) {
                    if ($ter->config['LOG_ENABLED']) DebMes("ОШИБКА!!! Восстановление медиаконтента на  терминале - " . $terminal['NAME'] . " завершилось ошибкой", 'terminals');
                }
            }
        } else if (!$old_message['SOURCE'] OR !$old_message['MESSAGE']) {
            // если нечего восстанавливать просто пропускаем итерацию -
            // иногда попадаются пустые записи ИД терминалов
            continue;
        }

        // если есть сообщение НО терминал оффлайн удаляем из работы эту запись
        // и пропускаем (пингуется дополнительно - если вернется с ошибкой отправления)
        if ($old_message['ID'] AND !$terminal['TTS_IS_ONLINE']) {
            try {
                $old_message['SOURCE'] = str_replace($terminal['ID'] . '^', '', $old_message['SOURCE']);
                SQLUpdate('shouts', $old_message);
                if ($ter->config['LOG_ENABLED']) DebMes("Disable message - " . $terminal['NAME'], 'terminals');
                $params = array();
                $params['NAME'] = $terminal['NAME'];
                $params["MESSAGE"] = $old_message['MESSAGE'];
                $params["ERROR"] = 'Терминал ушел в офлайн. Сообщение удалено';
                $params["IMPORTANCE"] = $old_message['IMPORTANCE'];
                callMethodSafe($terminals . '.MessageError', $params);
                continue;
            } catch (Exception $e) {
                if ($ter->config['LOG_ENABLED']) DebMes("ОШИБКА!!! Ввыполнение метода MessageError с ошибкой 'Терминал ушел в офлайн. Сообщение удалено' на  терминале - " . $terminal['NAME'] . " завершилось ошибкой", 'terminals');
            }
        }

        // если есть сообщение НО не сгенерирован звук (остутсвует в информации о сообщении запись) в течении 2 минут
        // удаляем сообщение из очереди для терминалов воспроизводящих звук
        if ($old_message['CACHED_FILENAME'] AND strtotime($old_message['ADDED']) + 2 * 60 < time() AND $base_terminal[$terminal['TITLE']]['TYPE'] == 'audio_terminal') {
            try {
                $old_message['SOURCE'] = str_replace($terminal['ID'] . '^', '', $old_message['SOURCE']);
                SQLUpdate('shouts', $old_message);
                if ($ter->config['LOG_ENABLED']) DebMes("Disable message not generated sound  - " . $terminal['NAME'], 'terminals');
                DebMes("ВНИМАНИЕ!!! Модуль генератора речи не сгенерировал звуковое сообщение. ПРОВЕРЬТЕ ЕГО РАБОТУ!!!", 'terminals');
                $params = array();
                $params['NAME'] = $terminal['NAME'];
                $params["MESSAGE"] = $old_message['MESSAGE'];
                $params["ERROR"] = 'Не сгенерирован звук модулем генератора речи для голосового терминала в течении 2 минут. Сообщение удалено';
                $params["IMPORTANCE"] = $old_message['IMPORTANCE'];
                callMethodSafe($terminals . '.MessageError', $params);
                continue;
            } catch (Exception $e) {
                if ($ter->config['LOG_ENABLED']) DebMes("ОШИБКА!!! Ввыполнение метода MessageError с ошибкой 'Не сгенерирован звук модулем генератора речи для голосового терминала в течении 2 минут. Сообщение удалено' на  терминале - " . $terminal['NAME'] . " завершилось ошибкой", 'terminals');
            }
        }

        // если есть сообщение и есть запись о существовании файла НО не сгенерирован звук (отсутствует файл)
        // удаляем сообщение из очереди для терминалов воспроизводящих звук
        if ($old_message['CACHED_FILENAME'] AND !file_exists($old_message['CACHED_FILENAME']) AND $base_terminal[$terminal['TITLE']]['TYPE'] == 'audio_terminal') {
            try {
                $old_message['SOURCE'] = str_replace($terminal['ID'] . '^', '', $old_message['SOURCE']);
                SQLUpdate('shouts', $old_message);
                if ($ter->config['LOG_ENABLED']) DebMes("Disable message not generated sound  - " . $terminal['NAME'], 'terminals');
                $params = array();
                $params['NAME'] = $terminal['NAME'];
                $params["MESSAGE"] = $old_message['MESSAGE'];
                $params["ERROR"] = 'Отсутствует сгенерированный файл аудио сообщения. Для голосового терминала. Сообщение удалено';
                $params["IMPORTANCE"] = $old_message['IMPORTANCE'];
                callMethodSafe($terminals . '.MessageError', $params);
                continue;
            } catch (Exception $e) {
                if ($ter->config['LOG_ENABLED']) DebMes("ОШИБКА!!! Ввыполнение метода MessageError с ошибкой 'Отсутствует сгенерированный файл аудио сообщения. Для голосового терминала. Сообщение удалено' на  терминале - " . $terminal['NAME'] . " завершилось ошибкой", 'terminals');
            }
        }

        // если тип терминала передающий только текстовое сообщение
        // запускаем его воспроизведение
        if ($base_terminal[$terminal['TITLE']]['TYPE'] == 'text_terminal' AND $old_message['SOURCE']) {
            try {
                // убираем запись айди терминала из таблицы шутс - если не воспроизведется то вернет эту запись функция send_message($old_message, $terminal);
                $old_message['SOURCE'] = str_replace($terminal['ID'] . '^', '', $old_message['SOURCE']);
                SQLUpdate('shouts', $old_message);
                //записываем что терминал занят
                sg($terminal['LINKED_OBJECT'] . '.TerminalState', 1);
                //передаем сообщение на терминал передающий только текстовое сообщение
                send_messageSafe($old_message, $terminal);
                if ($ter->config['LOG_ENABLED']) DebMes("Send message with text to terminal - " . $terminal['NAME'], 'terminals');
            } catch (Exception $e) {
                if ($ter->config['LOG_ENABLED']) DebMes("ОШИБКА!!! Передача текстового сообщения на  терминале - " . $terminal['NAME'] . " с типом терминала- " . $terminal['TTS_TYPE'] . " завершилось ошибкой", 'terminals');
            }
        }

        // если тип терминала передающий медиа сообщение
        // иначе запускаем его воспроизведение
        if ($base_terminal[$terminal['TITLE']]['TYPE'] == 'audio_terminal' AND $old_message['CACHED_FILENAME'] AND $old_message['SOURCE']) {
            try {
                // убираем запись айди терминала из таблицы шутс - если не воспроизведется то вернет эту запись функция send_message($old_message, $terminal);
                $old_message['SOURCE'] = str_replace($terminal['ID'] . '^', '', $old_message['SOURCE']);
                SQLUpdate('shouts', $old_message);
                //записываем что терминал занят
                sg($terminal['LINKED_OBJECT'] . '.TerminalState', 1);
                //передаем сообщение на терминалы воспроизводящие аудио
                send_messageSafe($old_message, $terminal);
                // ставим что терминал - может восстановить медиа проигрываемое на терминале
                $base_terminal[$terminal['TITLE']]['NEEDRESTOREMEDIA'] = 1;
                if ($ter->config['LOG_ENABLED']) DebMes("Send message with media to terminal - " . $terminal['NAME'], 'terminals');
            } catch (Exception $e) {
                if ($ter->config['LOG_ENABLED']) DebMes("ОШИБКА!!! Передача аудио сообщения на  терминале - " . $terminal['NAME'] . " с типом терминала- " . $terminal['TTS_TYPE'] . " завершилось ошибкой", 'terminals');
            }
        }

    }

    // спим 2 секунды - ничего за это время срочного не случится
    sleep (2);

    if (isRebootRequired() || IsSet($_GET['onetime'])) {
        if ($ter->config['LOG_ENABLED']) DebMes("Цикл перезапущен по команде ребут от сервера ", 'terminals');
        exit;
    }
}
DebMes("Unexpected close of cycle: " . basename(__FILE__));
if ($ter->config['LOG_ENABLED']) DebMes("Цикл неожиданно завершился по неизвестной причине", 'terminals');
