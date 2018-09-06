<?php

/**
* Media Player Application
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2 (wizard, 11:02:35 [Feb 23, 2009])
*/
//
//

class app_player extends module {
	
	/**
	* player
	*
	* Module class constructor
	*
	* @access private
	*/
	function __construct() {
		$this->name = 'app_player';
		$this->title = '<#LANG_APP_PLAYER#>';
		$this->module_category = '<#LANG_SECTION_APPLICATIONS#>';
		$this->checkInstalled();
	}
	
	/**
	* saveParams
	*
	* Saving module parameters
	*
	* @access public
	*/
	function saveParams($data=1) {
		$p = array();
		if(isset($this->id)) {
			$p['id'] = $this->id;
		}
		if(isset($this->view_mode)) {
			$p['view_mode'] = $this->view_mode;
		}
		if(isset($this->edit_mode)) {
			$p['edit_mode'] = $this->edit_mode;
		}
		if(isset($this->tab)) {
			$p['tab'] = $this->tab;
		}
		return parent::saveParams($p);
	}
	
	/**
	* getParams
	*
	* Getting module parameters from query string
	*
	* @access public
	*/
	function getParams() {
		global $id;
		global $mode;
		global $view_mode;
		global $edit_mode;
		global $tab;
		if(isset($id)) {
			$this->id = $id;
		}
		if(isset($mode)) {
			$this->mode = $mode;
		}
		if(isset($view_mode)) {
			$this->view_mode = $view_mode;
		}
		if(isset($edit_mode)) {
			$this->edit_mode = $edit_mode;
		}
		if(isset($tab)) {
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

		if($this->action == 'admin') {
			$this->admin($out);
		} else {
			$this->usual($out);
		}
		if(isset($this->owner->action)) {
			$out['PARENT_ACTION'] = $this->owner->action;
		}
		if(isset($this->owner->name)) {
			$out['PARENT_NAME'] = $this->owner->name;
		}
		$out['VIEW_MODE'] = $this->view_mode;
		$out['EDIT_MODE'] = $this->edit_mode;
		$out['MODE'] = $this->mode;
		$out['ACTION'] = $this->action;
		if($this->single_rec) {
			$out['SINGLE_REC'] = 1;
		}
		$this->data = $out;
		$p = new parser(DIR_TEMPLATES.$this->name.'/'.$this->name.'.html', $this->data, $this);
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
		if($this->mode == 'update') {
			global $enabled;
			$this->config['ENABLED'] = (int)$enabled;
			$this->saveConfig();
			$out['OK'] = 1;
		}
		$this->usual($out);
		$out['MODE'] = $this->mode;
		$out['ENABLED'] = (int)($this->config['ENABLED']);
	}
	
	/**
	* FrontEnd
	*
	* Module frontend
	*
	* @access public
	*/
	function usual(&$out) {
		global $play; // Deprecated (backward compatibility)
		global $rnd;
		global $session;
		global $play_terminal;
		global $terminal_id;

		$session_terminal = gr('session_terminal');
		if($session_terminal != '') {
			$session->data['SESSION_TERMINAL'] = $session_terminal;
		}

		if($this->play) {
			$play = $this->play;
		}

		if($this->terminal_id) {
			$terminal_id = $this->terminal_id;
		}

		if($terminal_id) {
			$terminal = SQLSelectOne('SELECT * FROM `terminals` WHERE `ID` = '.(int)$terminal_id);
			$session->data['PLAY_TERMINAL'] = $terminal['NAME'];
		}

		if($session->data['PLAY_TERMINAL'] == '') {
			$session->data['PLAY_TERMINAL'] = $session->data['TERMINAL'];
		}

		if($play_terminal != '') {
			$session->data['PLAY_TERMINAL'] = $play_terminal;
		} else {
			$play_terminal = $session->data['PLAY_TERMINAL'];
		}

		if(!$play && $session->data['LAST_PLAY']) {
			$play = $session->data['LAST_PLAY'];
			$out['LAST_PLAY'] = 1;
		} elseif($play) {
			$session->data['LAST_PLAY'] = $play;
		}
		
		if($play != '') {
			$out['PLAY'] = $play;
		}
		
		if($rnd != '') {
			$out['RND'] = $rnd;
		}

		$current_level = getGlobal('ThisComputer.volumeMediaLevel');
		for($i = 0; $i <= 100; $i += 5) {
			$rec = array('VALUE' => $i);
			if($i == $current_level) {
				$rec['SELECTED'] = 1;
			}
			$out['VOLUMES'][] = $rec;
		}

		global $ajax;
		if($this->ajax) {
			$ajax = 1;
		}

		if($session->data['PLAY_TERMINAL'] != '') {
			$terminal=SQLSelectOne('SELECT * FROM `terminals` WHERE `NAME` = \''.DBSafe($session->data['PLAY_TERMINAL']).'\'');
		}

		if(!$terminal['HOST']) {
			$terminal['HOST'] = 'localhost';
		}

		if(!$terminal['CANPLAY']) {
			$terminal = SQLSelectOne('SELECT * FROM `terminals` WHERE `NAME` = \'HOME\' OR `NAME` = \'MAIN\'');
		}

		if(!$terminal['CANPLAY']) {
			$terminal = SQLSelectOne('SELECT * FROM `terminals` WHERE `CANPLAY` = 1 ORDER BY `IS_ONLINE` DESC LIMIT 1');
		}

		if($ajax != '') {
			global $command, $param;
			$command = trim($command);
			$param = trim($param);
			
			// JSON default
			$json = array(
				'play_terminal'		=> $play_terminal,
				'session_terminal'	=> $session_terminal,
				'command'			=> $command,
				'success'			=> FALSE,
				'message'			=> NULL,
				'data'				=> NULL,
			);
			
			if($command != '') {

				// Deprecated (backward compatibility)
				global $volume;
				if($command == 'volume' && isset($volume) && !empty($volume)) {
					$command = 'set_volume';
					$param = $volume;
				} elseif($command == 'refresh' && isset($play) && !empty($play)) {
					$command = 'play';
					$param = $play;
				} elseif($command == 'close') {
					$command = 'stop';
				} elseif($command == 'prev') {
					$command = 'previous';
				}
				
				// Set media volume level
				if($command == 'set_volume' && !empty($param)) {
					if(strtolower($terminal['HOST']) == 'localhost' || $terminal['HOST'] == '127.0.0.1') {
						setGlobal('ThisComputer.volumeMediaLevelOld', (int)getGlobal('ThisComputer.volumeMediaLevel')); // For some types of players (e.g. VLC)
						setGlobal('ThisComputer.volumeMediaLevel', (int)$param);
						callMethod('ThisComputer.VolumeMediaLevelChanged', array('VALUE' => (int)$param, 'HOST' => $terminal['HOST']));
					}
				}

				// Default player type
				if(empty($terminal['PLAYER_TYPE'])) {
					$terminal['PLAYER_TYPE'] = 'vlc';
				}
				
				// Addons main class
				include_once(DIR_MODULES.'app_player/addons.php');
				
				// Load addon
				if(file_exists(DIR_MODULES.'app_player/addons/'.$terminal['PLAYER_TYPE'].'.addon.php')) {

					include_once(DIR_MODULES.'app_player/addons/'.$terminal['PLAYER_TYPE'].'.addon.php');

					if(class_exists($terminal['PLAYER_TYPE'])) {

						if($player = new $terminal['PLAYER_TYPE']($terminal)) {

							// Execute command
							$result = $player->$command($param);

							// Get results
							$json['success'] = $player->success;
							$json['message'] = $player->message;
							$json['data'] = $player->data;

							$player->destroy();
						} else {
							$json['success'] = FALSE;
							$json['message'] = 'Error of the addon "'.$terminal['PLAYER_TYPE'].'" object!';
						}
					} else {
						$json['success'] = FALSE;
						$json['message'] = 'Addon "'.$terminal['PLAYER_TYPE'].'" is damaged!';
					}
				} else {
					$json['success'] = FALSE;
					$json['message'] = 'Addon "'.$terminal['PLAYER_TYPE'].'" is not installed!';
				}
			} else { // HTML5 Player
				$json['success'] = TRUE;
				$json['message'] = 'Nothing to do.';
			}
			
			// Return json
			if(!$this->intCall) {
				$session->save();
				die(json_encode($json));
			}
		}

		$session_terminals = array();
		if($session->data['SESSION_TERMINAL'] != '') {
			$session_terminals = explode(',', $session->data['SESSION_TERMINAL']);
		} elseif($session->data['PLAY_TERMINAL']) {
			$session_terminals = array($session->data['PLAY_TERMINAL']);
		}
		$terminals = SQLSelect('SELECT * FROM `terminals` WHERE `CANPLAY` = 1 ORDER BY `TITLE`');
		array_unshift($terminals, array('NAME'=>'html5', 'TITLE'=>'Web-browser'));
		//$terminals[] = array('NAME'=>'html5', 'TITLE'=>'Web-browser');
		$total = count($terminals);
		for($i = 0 ; $i < $total ; $i++) {
			if(in_array($terminals[$i]['NAME'], $session_terminals)) {
				$terminals[$i]['SELECTED'] = 1;
				$out['TERMINAL_TITLE'] = $terminals[$i]['TITLE'];
			}
		}
		$out['TERMINALS_TOTAL'] = count($terminals);
		if($out['TERMINALS_TOTAL'] == 1 || !count($session_terminals)) {
			$terminals[0]['SELECTED'] = 1;
		}
		$out['TERMINALS'] = $terminals;
	}
	
	/**
	* Install
	*
	* Module installation routine
	*
	* @access private
	*/
	function install($parent_name='') {
		parent::install($parent_name);
	}
	
	// --------------------------------------------------------------------
}

/*
*
* TW9kdWxlIGNyZWF0ZWQgRmViIDIzLCAyMDA5IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/

?>
