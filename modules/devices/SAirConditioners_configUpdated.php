<?php

$fanSpeedModes = $this->getProperty('fanSpeedModes');
$tmp = explode(',',$fanSpeedModes);
$html='';
foreach($tmp as $mode) {
    $html.='<option value="'.$mode.'">'.constant('LANG_DEVICES_AC_FAN_SPEED_'.strtoupper($mode));
}
$this->setProperty('fanSpeedModesHTML',$html);

$thermostatModes = $this->getProperty('thermostatModes');
$tmp = explode(',',$thermostatModes);
$html='';
foreach($tmp as $mode) {
    $html.='<option value="'.$mode.'">'.constant('LANG_DEVICES_AC_THERMOSTAT_'.strtoupper($mode));
}
$this->setProperty('thermostatModesHTML',$html);