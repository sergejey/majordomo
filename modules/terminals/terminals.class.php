<?php
/**
 * Terminals
 *
 * Terminals
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 * @version 0.3
 */
//
//
class terminals extends module
{
    /**
     * terminals
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct() {
        $this->name = "terminals";
        $this->title = "<#LANG_MODULE_TERMINALS#>";
        $this->module_category = "<#LANG_SECTION_SETTINGS#>";
        $this->checkInstalled();
        $this->serverip = getLocalIp();
    }

    /**
     * saveParams
     *
     * Saving module parameters
     *
     * @access public
     */
    function saveParams($data = 1) {
        $data = array();
        if (IsSet($this->id)) {
            $data["id"] = $this->id;
        }
        if (IsSet($this->view_mode)) {
            $data["view_mode"] = $this->view_mode;
        }
        if (IsSet($this->edit_mode)) {
            $data["edit_mode"] = $this->edit_mode;
        }
        if (IsSet($this->tab)) {
            $data["tab"] = $this->tab;
        }
        return parent::saveParams($data);
    }

    /**
     * getParams
     *
     * Getting module parameters from query string
     *
     * @access public
     */
    function getParams($data = 1) {
        global $id;
        global $mode;
        global $view_mode;
        global $edit_mode;
        global $tab;
        if (isset($id)) {
            $this->id = $id;
        }
        if (isset($mode)) {
            $this->mode = $mode;
        }
        if (isset($view_mode)) {
            $this->view_mode = $view_mode;
        }
        if (isset($edit_mode)) {
            $this->edit_mode = $edit_mode;
        }
        if (isset($tab)) {
            $this->tab = $tab;
        }
    }

    /**
     * Run
     *
     * Description
     *
     * @access public
     */
    function run() {
        global $session;
        $out = array();
        if ($this->action == 'admin') {
            $this->admin($out);
        } else {
            $this->usual($out);
        }
        if (IsSet($this->owner->action)) {
            $out['PARENT_ACTION'] = $this->owner->action;
        }
        if (IsSet($this->owner->name)) {
            $out['PARENT_NAME'] = $this->owner->name;
        }
        $out['VIEW_MODE'] = $this->view_mode;
        $out['EDIT_MODE'] = $this->edit_mode;
        $out['MODE'] = $this->mode;
        $out['ACTION'] = $this->action;
        $out['TAB'] = $this->tab;
        if ($this->single_rec) {
            $out['SINGLE_REC'] = 1;
        }
        $this->data = $out;
        $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
        $this->result = $p->result;
    }

    /**
     * BackEnd
     *
     * Module backend
     *
     * @access public
     */
    function admin(&$out) {
        $this->getConfig();
        $out['LOG_ENABLED'] = $this->config['LOG_ENABLED'];

        if ($this->config['TERMINALS_TIMEOUT']) {
            $out['TERMINALS_TIMEOUT'] = $this->config['TERMINALS_TIMEOUT'];
        } else {
            $out['TERMINALS_TIMEOUT'] = 10;
        }
        if ($this->config['TERMINALS_DINGDONG']) {
            $out['TERMINALS_DINGDONG'] = $this->config['TERMINALS_DINGDONG'];
        } else {
            $out['TERMINALS_DINGDONG'] = 'dingdong.mp3';
        }
        if ($this->config['TERMINALS_PING_OFFLINE']) {
            $out['TERMINALS_PING_OFFLINE'] = $this->config['TERMINALS_PING_OFFLINE'];
        } else {
            $out['TERMINALS_PING_OFFLINE'] = 27;
        }
        if ($this->config['TERMINALS_PING_ONLINE']) {
            $out['TERMINALS_PING_ONLINE'] = $this->config['TERMINALS_PING_ONLINE'];
        } else {
            $out['TERMINALS_PING_ONLINE'] = 54;
        }
        if ($this->config['TERMINALS_CASH_CLEAR']) {
            $out['TERMINALS_CASH_CLEAR'] = $this->config['TERMINALS_CASH_CLEAR'];
        } else {
            $out['TERMINALS_CASH_CLEAR'] = 7;
        }
        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }
        if ($this->data_source == 'terminals' || $this->data_source == '') {
            if ($this->view_mode == '' || $this->view_mode == 'search_terminals') {
                $this->search_terminals($out);
            }
            if ($this->view_mode == 'edit_terminals') {
                $this->edit_terminals($out, $this->id);
            }
            if ($this->view_mode == 'delete_terminals') {
                $this->delete_terminals($this->id);
                $this->redirect("?");
            }
            if ($this->view_mode == 'change_terminals_tts') {
                $this->change_terminals_tts($this->id);
				$this->redirect("?ok=1");
            }
            if ($this->view_mode == 'change_terminals_play') {
                $this->change_terminals_play($this->id);
				$this->redirect("?ok=1");
            }
            if ($this->view_mode == 'change_terminals_recognition') {
                $this->change_terminals_recognition($this->id);
				$this->redirect("?ok=1");
            }
        }
        if ($this->view_mode == 'update_settings') {
            $this->config['LOG_ENABLED'] = gr('log_enabled');
            $this->config['TERMINALS_TIMEOUT'] =  trim(gr('terminals_timeout'));
            $this->config['TERMINALS_DINGDONG'] =  trim(gr('dingdong_path'));
            $this->config['TERMINALS_PING_OFFLINE'] = trim(gr('terminals_ping_offline'));
            $this->config['TERMINALS_PING_ONLINE'] = trim(gr('terminals_ping_online'));
            $this->config['TERMINALS_CASH_CLEAR'] = trim(gr('terminals_cash_clear'));
            setGlobal('cycle_terminalsControl','restart');
            $this->saveConfig();
            $this->redirect("?ok=1");
        }
    }

    /**
     *
     * @access public
     */
    function change_terminals_recognition($id) {
        if ($terminal = getTerminalByID($id)) {
            // подключаем класс stt 
            $addon_file = DIR_MODULES . 'terminals/stt/' . $terminal['RECOGNIZE_TYPE'] . '.addon.php';
            if ($terminal['RECOGNIZE_TYPE'] AND file_exists($addon_file) ) {
                include_once (DIR_MODULES . 'terminals/stt_addon.class.php');
                include_once ($addon_file);
                $stt = new $terminal['RECOGNIZE_TYPE']($terminal);
            }
            if ($terminal['CANRECOGNIZE'] == 0 AND $terminal['RECOGNIZE_TYPE']) {
	        	$terminal['CANRECOGNIZE'] = 1;
	        	if ($terminal['CANRECOGNIZE'] AND !$terminal['RECOGNIZE_IS_ONLINE']) {
                    sg($terminal['LINKED_OBJECT'] . '.RecognizeState', 1);
                    pingTerminalSafe($terminal['NAME'], $terminal,'CANRECOGNIZE');
                }
	        	// пробуем включить службу распознавания на устройстве
                try {
                    if ($stt) {
                        $stt->turnOn_stt();
                    }
                } catch (Exception $e) {
                    DebMes("Terminal " . $terminal['NAME'] . " have wrong setting", 'terminals');
                }
            } else {
                $terminal['CANRECOGNIZE'] = 0;
                // пробуем otключить службу распознавания на устройстве
                try {
                    if ($stt) {
                        $stt->turnOff_stt();
                    }
                } catch (Exception $e) {
                    DebMes("Terminal " . $terminal['NAME'] . " have wrong setting", 'terminals');
                }
            }
            SQLUpdate('terminals', $terminal);
        }
    }
	
    /**
     *
     * @access public
     */
    function change_terminals_play($id) {
        if ($rec = getTerminalByID($id)) {
            if ($rec['CANPLAY'] == 0 AND $rec['PLAYER_TYPE']) {
                $rec['CANPLAY'] = 1;
                if ($rec['CANPLAY'] AND !$rec['PLAYER_IS_ONLINE']) {
                    sg($rec['LINKED_OBJECT'] . '.PlayerState', 1);
                    pingTerminalSafe($rec['NAME'], $rec,'CANPLAY');
                }
            } else {
                $rec['CANPLAY'] = 0;
            }
            SQLUpdate('terminals', $rec);
        }
    }
	
    /**
     *
     * @access public
     */
    function change_terminals_tts($id) {
        if ($rec = getTerminalByID($id)) {
            if ($rec['CANTTS'] == 0 AND $rec['TTS_TYPE']) {
                $rec['CANTTS'] = 1;
                if ($rec['CANTTS'] AND !$rec['TTS_IS_ONLINE']) {
                    sg($rec['LINKED_OBJECT'] . '.TerminalState', 1);
                    pingTerminalSafe($rec['NAME'], $rec,'CANTTS');
                }
            } else {
                $rec['CANTTS'] = 0;
            }
            SQLUpdate('terminals', $rec);
        }
    }
	
    /**
     * FrontEnd
     *
     * Module frontend
     *
     * @access public
     */
    function usual(&$out) {
        $this->admin($out);
    }

    /**
     * terminals search
     *
     * @access public
     */
    function search_terminals(&$out) {
        require(DIR_MODULES . $this->name . '/terminals_search.inc.php');
    }

    /**
     * terminals edit/add
     *
     * @access public
     */
    function edit_terminals(&$out, $id) {
        require(DIR_MODULES . $this->name . '/terminals_edit.inc.php');
    }

    /**
     * terminals delete record
     *
     * @access public
     */
    function delete_terminals($id) {
        if ($rec = getTerminalByID($id)) {
            deleteObject($rec['LINKED_OBJECT']);
            SQLExec('DELETE FROM `terminals` WHERE `ID` = ' . $rec['ID']);
        }
    }

    /**
     * terminals subscription events
     *
     * @access public
     */
    function processSubscription($event, $details = '') {
        // если происходит событие SAY_CACHED_READY то запускаемся
        if ($event == 'SAY_CACHED_READY') {
            $this->getConfig();
            if ($this->config['LOG_ENABLED']) DebMes("Processing$event: " . json_encode($details, JSON_UNESCAPED_UNICODE), 'terminals');
            // ждем файл сообщения
            while ($count < 1000) {
                if (file_exists($details['CACHED_FILENAME'])) break;
                usleep(10000);
                $count++;
            }
            if ($this->config['LOG_ENABLED']) DebMes("Wait a file " . $count/100 . " seconds. If >=10 second then PROBLEM NOT GET RIGHT TIME MESSAGE", 'terminals');


            // берем длинну сообщения
            $count = 0;
            while ($count < 3) {
                $duration = get_media_info($details['CACHED_FILENAME'])['duration'];
                if ($duration_norm < $duration) {
                    $duration_norm = $duration;
                }
                usleep(10000);
                $count++;
            }
            if ($this->config['LOG_ENABLED']) DebMes('Duration message - '.$duration_norm, 'terminals');
            if ($duration_norm < 2) {
                $duration = 2;
            } else {
                $duration = $duration_norm;
            }
            if ($this->config['LOG_ENABLED']) DebMes("FINISH Processing$event:  - " . $details['CACHED_FILENAME'], 'terminals');
            $rec['MESSAGE_DURATION'] = $duration;
            $rec['ID'] = $details['ID'];
            $rec['CACHED_FILENAME'] = $details['CACHED_FILENAME'];
            SQLUpdate('shouts', $rec);
        } else if ($event == 'ASK') {
            $this->getConfig();
            $terminal = $details['destination'];
            try {
                include_once DIR_MODULES . 'terminals/tts_addon.class.php';
                $addon_file = DIR_MODULES . 'terminals/tts/' . $terminal['TTS_TYPE'] . '.addon.php';
                if (file_exists($addon_file) AND $terminal['TTS_TYPE']) {
                    include_once($addon_file);
                    $tts = new $terminal['TTS_TYPE']($terminal);
                    if (method_exists($tts,'ask')) {
                        $tts->ask($details['message'], 9999);
                        if ($this->config['LOG_ENABLED']) DebMes("Sending Message - " . $details['message'] . "to : " . $terminal['NAME'], 'terminals');
                    } else {
                        if ($this->config['LOG_ENABLED']) DebMes("Can not ask  Terminal - " . $terminal['NAME'] , 'terminals');
                    }
                } else {
                    sleep (1);
                    if ($this->config['LOG_ENABLED']) DebMes("Terminal not right configured - " . $terminal['NAME'] , 'terminals');
                }
            } catch (Exception $e) {
                if ($this->config['LOG_ENABLED']) DebMes("Terminal terminated, not work addon - " . $terminal['NAME'] , 'terminals');
            }
        } else if ($event == 'DAILY') {
            $this->getConfig();
            // проверяем, что каталог
            if (is_dir(DOC_ROOT."/cms/cached/voice/")) {
                // открываем каталог
                if ($dh = opendir(DOC_ROOT."/cms/cached/voice/")) {
                    // читаем и выводим все элементы
                    // от первого до последнего
                    while (($file = readdir($dh)) !== false) {
                        // текущее время
                        $time_sec = time();
                        // время изменения файла
                        $time_file = fileatime(DOC_ROOT."/cms/cached/voice/" . $file);
                        // тепрь узнаем сколько прошло времени (в секундах)
                        $time = $time_sec-$time_file;
                        $unlink = DOC_ROOT . "/cms/cached/voice/" . $file;
                        if (is_file($unlink)) {
                            if ($time > $this->config['TERMINALS_CASH_CLEAR']*60*60*24) {
                                if (unlink($unlink)) {
                                    echo 'Файл удален';
                                } else {
                                    echo 'Ошибка при удалении файла';
                                }
                            }

                        }
                    }
                    // закрываем каталог
                    closedir($dh);
                }
            }
        } else if ($event == 'HOURLY') {
            // ping device state
            $terminals = getAllTerminals();
            foreach ($terminals as $terminal) {
                $out = ping($terminal['HOST']);
                if ($out) {
                    $terminal['IS_ONLINE'] = 1;
                } else {
                    $terminal['IS_ONLINE'] = 0;
                }
                SQLUpdate('terminals', $terminal);
            }
        }
    }

    /**
     * Install
     *
     * Module installation routine
     *
     * @access private
     */
    function install($parent_name = "") {
        // add class and properties
        addClass('Terminals', 'SDevices');
        // name of terminal
        addClassProperty('Terminals', 'name');
        // state of terminal tts  - busy or not bausy
        addClassProperty('Terminals', 'TerminalState');
        // state of terminal player  - busy or not bausy
        addClassProperty('Terminals', 'PlayerState');
	// state of terminal stt  - busy or not bausy
        addClassProperty('Terminals', 'RecognizeState');
        // playerstate file, playlist, volume etc...
        addClassProperty('Terminals', 'playerdata');
        // terminal state brigtnes, screen, battary etc..
        addClassProperty('Terminals', 'terminaldata');
        // linked username
        addClassProperty('Terminals', 'username');
        // controll adress for upnp device
        addClassProperty('Terminals', 'UPNP_CONTROL_ADDRESS');

        // update main terminal
        //$terminal = getMainTerminal();
        //$terminal['TTS_TYPE'] = 'mainterminal';
        //$terminal['CANTTS'] = '1';
        //SQLUpdate('terminals', $terminal);

        // add autorestart cicle
        setGlobal('cycle_terminalsControl','start');
        setGlobal('cycle_terminalsAutoRestart','1');

        // remove old files
        @unlink(DIR_MODULES . 'terminals/tts/majordroid.addon.php');
        @unlink(DIR_MODULES . 'terminals/tts/mediaplayer.addon.php');


        //редактируем терминалы под новые настройки
        //$terminals = SQLSelect("SELECT * FROM terminals");
        //foreach ($terminals as $terminal) {
        //    if (!$terminal['TTS_TYPE'] AND $terminal['PLAYER_TYPE']=='dnla') {
        //        $terminal['TTS_TYPE']='dnla_tts';
        //    }
        //    if (!$terminal['TTS_TYPE'] AND $terminal['PLAYER_TYPE']=='chromecast') {
        //        $terminal['TTS_TYPE']='chromecast_tts';
        //    }
        //    if (!$terminal['TTS_TYPE'] AND $terminal['PLAYER_TYPE']=='majordroid') {
        //        $terminal['TTS_TYPE']='majordroid_tts';
        //    }
        //    if (!$terminal['TTS_TYPE'] AND $terminal['PLAYER_TYPE']=='vlcweb') {
        //        $terminal['TTS_TYPE']='vlcweb_tts';
        //    }
        //    $terminal['CANTTS'] = '1';
        //    $terminal['USE_SYSTEM_MML'] = '1';
        //    SQLUpdate('terminals', $terminal);
        //}
        unsubscribeFromEvent($this->name, 'SAY');
        unsubscribeFromEvent($this->name, 'SAYTO');
        unsubscribeFromEvent($this->name, 'ASK');
        unsubscribeFromEvent($this->name, 'SAYREPLY');

        subscribeToEvent($this->name, 'SAY_CACHED_READY', 0);
        subscribeToEvent($this->name, 'ASK', 0);
        subscribeToEvent($this->name, 'DAILY');
        subscribeToEvent($this->name, 'HOURLY');
        
        // автодобавления метода который вызывается циклом при ошибках терминала..
        addClassMethod('Terminals', 'MessageError', '
// $params["NAME"]; - Имя Терминала
// $params["MESSAGE"]; - сообщение
// $params["ERROR"]; - тип ошибки
// $params["IMPORTANCE"]; - важность сообщения
// $params["ORIGINAL_OBJECT_TITLE"]; - привязанный обьект
// $this->username; - Привязанный Пользователь
// $this->linkedRoom; - привязанное помещение
');
        
        // автодобавления метода который вызывается циклом при ошибках терминала..
        addClassMethod('Terminals', 'volumeOnChangeMessage', '
$terminal_name=gg($this->object_title.".name"); 
setMessageVolume($terminal_name, $params["volume"]);
');
        parent::install($parent_name);

    }

    /**
     * Uninstall
     *
     * Module uninstall routine
     *
     * @access public
     */
    function uninstall() {
        SQLDropTable('terminals');
        unsubscribeFromEvent($this->name, 'SAY');
        unsubscribeFromEvent($this->name, 'SAYTO');
        unsubscribeFromEvent($this->name, 'ASK');
        unsubscribeFromEvent($this->name, 'SAYREPLY');
        unsubscribeFromEvent($this->name, 'SAY_CACHED_READY');
        unsubscribeFromEvent($this->name, 'HOURLY');
        unsubscribeFromEvent($this->name, 'DAILY');
        parent::uninstall();
    }

    /**
     * dbInstall
     *
     * Database installation routine
     *
     * @access private
     */
    function dbInstall($data) {
        /*
        terminals - Terminals
        */
        $data = <<<EOD
 terminals: ID int(10) unsigned NOT NULL auto_increment
 terminals: NAME varchar(255) NOT NULL DEFAULT ''
 terminals: HOST varchar(255) NOT NULL DEFAULT ''
 terminals: TITLE varchar(255) NOT NULL DEFAULT ''
 terminals: IS_ONLINE int(1) NOT NULL DEFAULT '0'
 terminals: CANPLAY int(1) NOT NULL DEFAULT '0'
 terminals: PLAYER_TYPE char(20) NOT NULL DEFAULT ''
 terminals: PLAYER_PORT varchar(255) NOT NULL DEFAULT ''
 terminals: PLAYER_USERNAME varchar(255) NOT NULL DEFAULT ''
 terminals: PLAYER_PASSWORD varchar(255) NOT NULL DEFAULT ''
 terminals: PLAYER_CONTROL_ADDRESS varchar(255) NOT NULL DEFAULT ''
 terminals: PLAYER_IS_ONLINE int(1) NOT NULL DEFAULT '0'
 terminals: TERMINAL_VOLUME_LEVEL int(3) NOT NULL DEFAULT '100'
 terminals: CANTTS int(1) NOT NULL DEFAULT '0'
 terminals: TTS_TYPE char(20) NOT NULL DEFAULT ''
 terminals: TTS_SETING longtext NOT NULL 
 terminals: TTS_IS_ONLINE int(1) NOT NULL DEFAULT '0'
 terminals: MIN_MSG_LEVEL varchar(255) NOT NULL DEFAULT ''
 terminals: USE_SYSTEM_MML int(1) NOT NULL DEFAULT '1'
 terminals: MESSAGE_VOLUME_LEVEL int(3) NOT NULL DEFAULT '100'
 terminals: CANRECOGNIZE int(1) NOT NULL DEFAULT '0'
 terminals: RECOGNIZE_TYPE char(20) NOT NULL DEFAULT ''
 terminals: RECOGNIZE_SETING longtext NOT NULL 
 terminals: RECOGNIZE_IS_ONLINE int(1) NOT NULL DEFAULT '0'
 terminals: LATEST_REQUEST varchar(255) NOT NULL DEFAULT ''
 terminals: LATEST_REQUEST_TIME datetime
 terminals: LATEST_ACTIVITY datetime
 terminals: LOCATION_ID int(5) NOT NULL DEFAULT '0'
EOD;



        parent::dbInstall($data);


        //добавляем связанный обьект для всех терминалов необходимо для передачи сообщений
        $terminals = SQLSelect("SELECT * FROM terminals ");
        foreach ($terminals as $terminal) {
            addClassObject('Terminals', 'terminal_'.$terminal['NAME']);
            $terminal['LINKED_OBJECT'] = 'terminal_'.$terminal['NAME'];
            $location = gg('terminal_' . $terminal['NAME'] . '.linkedRoom');
            if ($location_id = SQLSelectOne("SELECT * FROM locations WHERE TITLE = '" . $location . "'")) {
                $terminal['LOCATION_ID'] = $location_id['ID'];
            }
            SQLUpdate('terminals', $terminal);
        }


    }
    // --------------------------------------------------------------------
}

/*
*
* TW9kdWxlIGNyZWF0ZWQgTWFyIDI3LCAyMDA5IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>
