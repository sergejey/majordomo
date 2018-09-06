<?php

/*
	Addon XBMC for app_player
*/

class xbmc extends app_player_addon {

	// Constructor
	function __construct($terminal) {
		$this->title = 'XBMC (Kodi)';
		$this->description = 'Бесплатный кроссплатформенный медиаплеер и программное обеспечение для организации HTPC с открытым исходным кодом.';
		
		$this->terminal = $terminal;
		$this->reset_properties();
	}

	/*
		В процессе портирования
	*/

}

?>
