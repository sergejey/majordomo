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
    function __construct()
    {
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
    function saveParams($data = 1)
    {
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
    function getParams($data = 1)
    {
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
    function run()
    {
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
    function admin(&$out)
    {
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
        }
    }

    /**
     * FrontEnd
     *
     * Module frontend
     *
     * @access public
     */
    function usual(&$out)
    {
        $this->admin($out);
    }

    /**
     * terminals search
     *
     * @access public
     */
    function search_terminals(&$out)
    {
        require(DIR_MODULES . $this->name . '/terminals_search.inc.php');
    }

    /**
     * terminals edit/add
     *
     * @access public
     */
    function edit_terminals(&$out, $id)
    {
        require(DIR_MODULES . $this->name . '/terminals_edit.inc.php');
    }

    /**
     * terminals delete record
     *
     * @access public
     */
    function delete_terminals($id)
    {
        if ($rec = getTerminalByID($id)) {
            SQLExec('DELETE FROM `terminals` WHERE `ID` = ' . $rec['ID']);
        }
    }


    function terminalSayByCache($terminal_rec, $cached_filename, $level)
    {
        $min_level = getGlobal('ThisComputer.minMsgLevel');
        if ($terminal_rec['MIN_MSG_LEVEL']) {
            $min_level = (int)processTitle($terminal_rec['MIN_MSG_LEVEL']);
        }
        if ($level < $min_level) {
            return false;
        }
        if ($terminal_rec['MAJORDROID_API'] || $terminal_rec['PLAYER_TYPE'] == 'ghn') {
            return;
        }
        if ($terminal_rec['CANPLAY'] && $terminal_rec['PLAYER_TYPE'] != '') {
            if (preg_match('/\/cms\/cached.+/', $cached_filename, $m)) {
                $server_ip = getLocalIp();
                if (!$server_ip) {
                    DebMes("Server IP not found", 'terminals');
                    return false;
                } else {
                    $cached_filename = 'http://' . $server_ip . $m[0];
                }
            } else {
                DebMes("Unknown file path format: " . $cached_filename, 'terminals');
                return false;
            }
            DebMes("Playing cached to " . $terminal_rec['TITLE'] . ' (level ' . $level . '): ' . $cached_filename, 'terminals');
            playMedia($cached_filename, $terminal_rec['TITLE']);
        }
    }

    function terminalSay($terminal_rec, $message, $level)
    {
        $asking = 0;
        if ($level === 'ask') {
            $level = 9999;
            $asking = 1;
        }
        $min_level = getGlobal('ThisComputer.minMsgLevel');
        if ($terminal_rec['MIN_MSG_LEVEL']) {
            $min_level = (int)processTitle($terminal_rec['MIN_MSG_LEVEL']);
        }
        if ($level < $min_level) {
            return false;
        }
        DebMes("Saying to " . $terminal_rec['TITLE'] . ' (level ' . $level . '): ' . $message, 'terminals');
        include_once DIR_MODULES . 'terminals/tts_addon.class.php';
        $addon_file = DIR_MODULES . 'terminals/tts/' . $terminal_rec['TTS_TYPE'] . '.addon.php';
        if (file_exists($addon_file)) {
            include_once($addon_file);
            $tts = new $terminal_rec['TTS_TYPE']($terminal_rec);
            if ($asking) {
                $result = $tts->ask($message, $level);
            } else {
                $result = $tts->say($message, $level);
            }
        } else {
            DebMes("Could not find $addon_file", 'terminals');
        }
        return $result;
    }

    /**
     * terminals subscription events
     *
     * @access public
     */
    function processSubscription($event, $details = '')
    {
        $this->getConfig();
        DebMes("Processing $event: " . json_encode($details, JSON_UNESCAPED_UNICODE), 'terminals');
        if ($event == 'SAY') {
            $terminals = SQLSelect("SELECT * FROM terminals WHERE CANTTS=1 AND TTS_TYPE!=''");
            foreach ($terminals as $terminal_rec) {
                $this->terminalSay($terminal_rec, $details['message'], $details['level']);
            }
        } elseif ($event == 'SAYTO' || $event == 'ASK') {
            $terminal_rec = array();
            if ($details['destination']) {
                if (!$terminal_rec = getTerminalsByName($details['destination'], 1)[0]) {
                    $terminal_rec = getTerminalsByHost($details['destination'], 1)[0];
                }
            }
            if (!$terminal_rec['ID']) {
                return false;
            }
            if ($event == 'ASK') {
                $details['level'] = 'ask';
            }
            $this->terminalSay($terminal_rec, $details['message'], $details['level']);
        } elseif ($event == 'SAY_CACHED_READY') {
            $filename = $details['filename'];
            if (!file_exists($filename)) return false;
            if ($details['event']) {
                $source_event = $details['event'];
            } else {
                $source_event = 'SAY';
            }
            if ($source_event == 'SAY') {
                $terminals = SQLSelect("SELECT * FROM terminals WHERE CANTTS=1");
                foreach ($terminals as $terminal_rec) {
                    //$this->terminalSayByCache($terminal_rec, $filename, $details['level']);
                    $this->terminalSayByCacheQueue($terminal_rec, $details['level'], $filename, $details['message']);
                }
            } elseif ($source_event == 'SAYTO' || $source_event == 'ASK') {
                $terminal_rec = array();
                if ($details['destination']) {
                    if (!$terminal_rec = getTerminalsByName($details['destination'], 1)[0]) {
                        $terminal_rec = getTerminalsByHost($details['destination'], 1)[0];
                    }
                }
                if (!$terminal_rec['ID']) {
                    return false;
                }
                if ($source_event == 'ASK') {
                    $details['level'] = 9999;
                }
                //$this->terminalSayByCache($terminal_rec, $filename, $details['level']);
                $this->terminalSayByCacheQueue($terminal_rec, $details['level'], $filename, $details['message']);
            }

        } elseif ($event == 'HOURLY') {
            // check terminals
            $terminals = SQLSelect("SELECT * FROM terminals WHERE IS_ONLINE=0 AND HOST!=''");
            foreach ($terminals as $terminal) {
                if (ping($terminal['HOST'])) {
                    $terminal['LATEST_ACTIVITY'] = date('Y-m-d H:i:s');
                    $terminal['IS_ONLINE'] = 1;
                    SQLUpdate('terminals', $terminal);
                }
            }
            SQLExec('UPDATE terminals SET IS_ONLINE=0 WHERE LATEST_ACTIVITY < (NOW() - INTERVAL 90 MINUTE)'); //
        } elseif ($event == 'SAYREPLY') {
        }
    }

    /**
     * очередь сообщений
     *
     * @access public
     */
    function terminalSayByCacheQueue($terminal_rec, $level, $cached_filename, $message)
    {

        $min_level = getGlobal('ThisComputer.minMsgLevel');
        if ($terminal_rec['MIN_MSG_LEVEL']) {
            $min_level = (int)processTitle($terminal_rec['MIN_MSG_LEVEL']);
        }
        if ($level < $min_level) {
            return false;
        }
        DebMes("Saying cached to " . $terminal_rec['TITLE'] . ' (level ' . $level . '): ' . $message . " (file: $cached_filename)", 'terminals');
        $result = false;
        include_once DIR_MODULES . 'terminals/tts_addon.class.php';
        $addon_file = DIR_MODULES . 'terminals/tts/' . $terminal_rec['TTS_TYPE'] . '.addon.php';
        if (file_exists($addon_file)) {
            include_once($addon_file);
            $tts = new $terminal_rec['TTS_TYPE']($terminal_rec);
            $result = $tts->sayCached($message, $level, $cached_filename);
        } else {
            DebMes("Could not find $addon_file", 'terminals');
        }
        return $result;
    }

    /**
     * Install
     *
     * Module installation routine
     *
     * @access private
     */
    function install($parent_name = "")
    {
        subscribeToEvent($this->name, 'SAY', '', 0);
        subscribeToEvent($this->name, 'SAYREPLY', '', 0);
        subscribeToEvent($this->name, 'SAYTO', '', 0);
        subscribeToEvent($this->name, 'ASK', '', 0);
        subscribeToEvent($this->name, 'SAY_CACHED_READY', 0);
        subscribeToEvent($this->name, 'HOURLY');
        parent::install($parent_name);

    }

    /**
     * Uninstall
     *
     * Module uninstall routine
     *
     * @access public
     */
    function uninstall()
    {
        SQLDropTable('terminals');
        unsubscribeFromEvent($this->name, 'SAY');
        unsubscribeFromEvent($this->name, 'SAYTO');
        unsubscribeFromEvent($this->name, 'ASK');
        unsubscribeFromEvent($this->name, 'SAYREPLY');
        unsubscribeFromEvent($this->name, 'SAY_CACHED_READY');
        unsubscribeFromEvent($this->name, 'HOURLY');
        parent::uninstall();
    }

    /**
     * dbInstall
     *
     * Database installation routine
     *
     * @access private
     */
    function dbInstall($data)
    {
        /*
        terminals - Terminals
        */
        $data = <<<EOD
 terminals: ID int(10) unsigned NOT NULL auto_increment
 terminals: NAME varchar(255) NOT NULL DEFAULT ''
 terminals: HOST varchar(255) NOT NULL DEFAULT ''
 terminals: TITLE varchar(255) NOT NULL DEFAULT ''
 terminals: CANPLAY int(3) NOT NULL DEFAULT '0'
 terminals: CANTTS int(3) NOT NULL DEFAULT '0'
 terminals: MIN_MSG_LEVEL varchar(255) NOT NULL DEFAULT ''
 terminals: TTS_TYPE char(20) NOT NULL DEFAULT '' 
 terminals: PLAYER_TYPE char(20) NOT NULL DEFAULT ''
 terminals: PLAYER_PORT varchar(255) NOT NULL DEFAULT ''
 terminals: PLAYER_USERNAME varchar(255) NOT NULL DEFAULT ''
 terminals: PLAYER_PASSWORD varchar(255) NOT NULL DEFAULT ''
 terminals: PLAYER_CONTROL_ADDRESS varchar(255) NOT NULL DEFAULT ''
 terminals: IS_ONLINE int(3) NOT NULL DEFAULT '0'
 terminals: MAJORDROID_API int(3) NOT NULL DEFAULT '0'
 terminals: LATEST_REQUEST varchar(255) NOT NULL DEFAULT ''
 terminals: LATEST_REQUEST_TIME datetime
 terminals: LATEST_ACTIVITY datetime
 terminals: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 terminals: LEVEL_LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
 terminals: LOCATION_ID int(5) NOT NULL DEFAULT '0' 
EOD;
        parent::dbInstall($data);

        $terminals = SQLSelect("SELECT * FROM terminals WHERE TTS_TYPE='' AND CANTTS=1");
        foreach ($terminals as $terminal) {
            if ($terminal['MAJORDROID_API']) {
                $terminal['TTS_TYPE'] = 'majordroid';
            } else {
                $terminal['TTS_TYPE'] = 'mediaplayer';
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
