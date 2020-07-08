<?php
/*
	Default language file for app_player module
*/


$dictionary = array(
	'APP_PLAYER'					=> 'Player Control',
	'APP_PLAYER_WEB_BROWSER'		=> 'Web-browser',
	'APP_PLAYER_SYSTEM_VOLUME'		=> 'System volume',
	'APP_PLAYER_PLAYING_TERMINALS'	=> 'Playing terminals',
	'APP_PLAYER_SELECT_TERMINALS'	=> 'Select a terminal...',
	'APP_PLAYER_VOLUME_LEVEL'		=> 'Volume level',
	'APP_PLAYER_PAUSE'				=> 'Pause',
	'APP_PLAYER_PREVIOUS'			=> 'Previous',
	'APP_PLAYER_PLAY'				=> 'Play',
	'APP_PLAYER_NEXT'				=> 'Next',
	'APP_PLAYER_STOP'				=> 'Stop',
);

foreach($dictionary as $key => $value) {
	if(!defined('LANG_'.$key)) {
		define('LANG_'.$key, $value);
	}
}

?>
