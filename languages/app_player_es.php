<?php
/*
	archivo de idioma Español para el módulo app_player
*/


$dictionary = array(
	'APP_PLAYER'					=> 'Control del reproductor',
	'APP_PLAYER_WEB_BROWSER'		=> 'Navegador web',
	'APP_PLAYER_SYSTEM_VOLUME'		=> 'Volumen del sistema',
	'APP_PLAYER_PLAYING_TERMINALS'	=> 'Terminales de reproducción',
	'APP_PLAYER_SELECT_TERMINALS'	=> 'Selecciona un terminal...',
	'APP_PLAYER_VOLUME_LEVEL'		=> 'Nivel de volument',
	'APP_PLAYER_PAUSE'				=> 'Pausa',
	'APP_PLAYER_PREVIOUS'			=> 'Anterior',
	'APP_PLAYER_PLAY'				=> 'Reproducir',
	'APP_PLAYER_NEXT'				=> 'Siguiente',
	'APP_PLAYER_STOP'				=> 'Detener',
);

foreach($dictionary as $key => $value) {
	if(!defined('LANG_'.$key)) {
		define('LANG_'.$key, $value);
	}
}

?>
