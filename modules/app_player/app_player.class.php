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
		global $session;

		// Deprecated (backward compatibility)
		$play = gr('play');
		if($this->play) {
			$play = $this->play;
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
		// END Deprecated

		// Play terminal
		$play_terminal = ($this->play_terminal?$this->play_terminal:gr('play_terminal'));
		$terminal_id = ($this->terminal_id?$this->terminal_id:gr('terminal_id'));
		if($play_terminal != '') { // name in request
			$terminal = getTerminalsByName($play_terminal, 1, 'LATEST_ACTIVITY', 'DESC')[0];
		} elseif($terminal_id) { // id in request
			$terminal = getTerminalByID($terminal_id);
		} elseif($session->data['TERMINAL'] != '') { // session -> data -> terminal
			$terminal = getTerminalsByName($session->data['TERMINAL'], 1, 'LATEST_ACTIVITY', 'DESC')[0];
		} else { // default
			if(!$terminal = getTerminalsByHost($_SERVER['REMOTE_ADDR'], 1, 'LATEST_ACTIVITY', 'DESC')[0]) {
				if(!$terminal = getMainTerminal()) {
					$terminal = getAllTerminals(1, 'LATEST_ACTIVITY', 'DESC')[0];
				}
			}
		}
		$session->data['PLAY_TERMINAL'] = $terminal['NAME'];
		
		// Session terminal
		$session_terminal = ($this->session_terminal?$this->session_terminal:gr('session_terminal'));
		if($session_terminal != '') { // name in request
			$session->data['SESSION_TERMINAL'] = $session_terminal;
		} elseif($session->data['SESSION_TERMINAL'] == '') { // default
			$session->data['SESSION_TERMINAL'] = $session->data['PLAY_TERMINAL'];
		}
		
		// Terminal defaults
		if(!$terminal['HOST']) {
			$terminal['HOST'] = 'localhost';
		}
		if(!$terminal['CANPLAY']) {
			$terminal = getMainTerminal();
		}
		if(!$terminal['CANPLAY']) {
			$terminal = getTerminalsCanPlay(1, 'LATEST_ACTIVITY', 'DESC')[0];
		}
		if(!$terminal['PLAYER_TYPE']) {
			$terminal['PLAYER_TYPE'] = 'vlc';
		}

		// AJAX
		if($this->ajax) {
			$ajax = TRUE;
		} else {
			$ajax = gr('ajax');
		}

		if($ajax) {
			
			// Command
			if($this->command) {
				$command = $this->command;
			} else {
				$command = trim(gr('command'));
			}
			
			// Param
			if($this->param) {
				$param = $this->param;
			} else {
				$param = trim(gr('param'));
			}

			// JSON default
			$json = array(
				'play_terminal'		=> $session->data['PLAY_TERMINAL'],
				'session_terminal'	=> $session->data['SESSION_TERMINAL'],
				'command'			=> $command,
				'success'			=> FALSE,
				'message'			=> NULL,
				'data'				=> NULL,
			);
			
			if(strlen($command)) {

				// Deprecated (backward compatibility)
				global $volume;
				if($command == 'volume') {
					$deprecated = true;
					$command = 'set_volume';
					$param = (int)$volume;
				} elseif($command == 'refresh') {
					$deprecated = true;
					$command = 'play';
					$param = $play;
				} elseif($command == 'close') {
					$deprecated = true;
					$command = 'stop';
				} elseif($command == 'prev') {
					$deprecated = true;
					$command = 'previous';
				} else {
					$deprecated = false;
				}
				// END Deprecated

				// Addons main class
				include_once(DIR_MODULES.'app_player/addons.php');
				
				// Load addon
				if(file_exists(DIR_MODULES.'app_player/addons/'.$terminal['PLAYER_TYPE'].'.addon.php')) {

					include_once(DIR_MODULES.'app_player/addons/'.$terminal['PLAYER_TYPE'].'.addon.php');

					if(class_exists($terminal['PLAYER_TYPE'])) {
						
						if(is_subclass_of($terminal['PLAYER_TYPE'], 'app_player_addon', TRUE)) {

							if($player = new $terminal['PLAYER_TYPE']($terminal)) {

								if($command == 'features') {
									
									// Get features
									$json['success'] = TRUE;
									$json['message'] = 'OK';
									$reflection = new ReflectionClass($player);
									foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
										if($method->getDeclaringClass()->getName() == $reflection->getName()) {
											$method_name = $method->getName();
											if(substr($method_name, 0, 2) != '__' and !in_array($method_name, array('destroy', 'command'))) {
												$json['data'][] = $method_name;
											}
										}
									}

								} else {
								
									// Execute command
									$result = $player->$command($param);

									// Get results
									$json['success'] = $player->success;
									$json['message'] = $player->message;
									$json['data'] = $player->data;

									// Deprecated message
									if($deprecated) {
										$json['message'] .= ' This command is deprecated. Please don\'t use it.';
									}
								}

								$player->destroy();
							} else {
								$json['success'] = FALSE;
								$json['message'] = 'Error of the addon "'.$terminal['PLAYER_TYPE'].'" object!';
							}
						
						} else {
							$json['success'] = FALSE;
							$json['message'] = 'Addon "'.$terminal['PLAYER_TYPE'].'" does not inherit from class "app_player_addon"!';
						}
					} else {
						$json['success'] = FALSE;
						$json['message'] = 'Addon "'.$terminal['PLAYER_TYPE'].'" is damaged!';
					}
				} else {
					$json['success'] = FALSE;
					$json['message'] = 'Addon "'.$terminal['PLAYER_TYPE'].'" is not installed!';
				}
				
				// Set media volume level
				if($command == 'set_volume' && strlen($param)) {
					if(strtolower($terminal['HOST']) == 'localhost' || $terminal['HOST'] == '127.0.0.1') {
						setGlobal('ThisComputer.volumeMediaLevel', (int)$param);
						callMethod('ThisComputer.VolumeMediaLevelChanged', array('VALUE' => (int)$param, 'HOST' => $terminal['HOST']));
					}
				}
				
			} else { // HTML5 Player
				$json['success'] = TRUE;
				$json['message'] = 'Nothing to do.';
			}
			
			// Return json
			if($this->intCall) {
				$this->json = $json;
			} else {
				$session->save();
				die(json_encode($json));
			}
		}

		// List of terminals
		$session_terminals = array();
		if($session->data['SESSION_TERMINAL'] != '') {
			$session_terminals = explode(',', $session->data['SESSION_TERMINAL']);
		} elseif($session->data['PLAY_TERMINAL']) {
			$session_terminals = array($session->data['PLAY_TERMINAL']);
		}
		$terminals = getTerminalsCanPlay(-1, 'TITLE', 'ASC');
		array_unshift($terminals, array('NAME'=>'html5', 'TITLE'=>'<#LANG_APP_PLAYER_WEB_BROWSER#>'));
		array_unshift($terminals, array('NAME'=>'system_volume', 'TITLE'=>'<#LANG_APP_PLAYER_SYSTEM_VOLUME#>'));
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
		foreach ($terminals as $temp_terminal) {
			if (!checkAccess('terminal', $temp_terminal['ID'])) {
				continue;// some action for every record if required
			}
			$out['TERMINALS'][] = $temp_terminal;
		}
		//$out['TERMINALS'] = $terminals;
		
		// Unique ID
		$out['APP_PLAYER_ID'] = uniqid('app_player_');
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

}

?>
