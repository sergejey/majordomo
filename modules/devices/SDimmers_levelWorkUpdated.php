<?php

$level = $this->getProperty('level');
$minWork = $this->getProperty('minWork');
$maxWork = $this->getProperty('maxWork');
$levelWork = $this->getProperty('levelWork'); //
//DebMes("Levelwork updated to " . $levelWork, 'dimming');
if ($minWork != $maxWork) {
    $new_level = round((($levelWork-$minWork)/($maxWork-$minWork))*100);
    if ($new_level<0) {
        $new_level=0;
    }
    if ($new_level != $level) {
        //DebMes("Setting new level to " . $new_level, 'dimming');
        $this->setProperty('level', $new_level);
    }
}
