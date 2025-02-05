<?php

$level = $this->getProperty('level');
$minWork = $this->getProperty('minWork');
$maxWork = $this->getProperty('maxWork');
$levelWork = $this->getProperty('levelWork'); //
if ($minWork != $maxWork) {
    $new_level = round((($levelWork-$minWork)/($maxWork-$minWork))*100);

    if ($new_level<0) {
        $new_level=0;
    }
    if ($new_level != $level) {
        $this->setProperty('level', $new_level);
    }
}
