<?php
/*
	Default language file for app_player module
*/


$dictionary = array(
	'APP_PLAYER'					=> 'Плеер',
	'APP_PLAYER_WEB_BROWSER'		=> 'Веб-браузер',
	'APP_PLAYER_SYSTEM_VOLUME'		=> 'Системная громкость',
	'APP_PLAYER_PLAYING_TERMINALS'	=> 'Терминалы для воспроизведения',
	'APP_PLAYER_SELECT_TERMINALS'	=> 'Выберите терминал...',
	'APP_PLAYER_VOLUME_LEVEL'		=> 'Уровень громкости',
	'APP_PLAYER_PAUSE'				=> 'Пауза',
	'APP_PLAYER_PREVIOUS'			=> 'Предыдущий',
	'APP_PLAYER_PLAY'				=> 'Играть',
	'APP_PLAYER_NEXT'				=> 'Следующий',
	'APP_PLAYER_STOP'				=> 'Стоп',
);

foreach($dictionary as $key => $value) {
	if(!defined('LANG_'.$key)) {
		define('LANG_'.$key, $value);
	}
}

?>
