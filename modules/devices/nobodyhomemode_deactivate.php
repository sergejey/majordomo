<?php

if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES==1) return;

require(DIR_MODULES.'devices/Rooms_updateActivityStatus.php');

if (gg('EconomMode.active')) {
    callMethod('EconomMode.deactivate');
}