<?php

if (defined('DISABLE_SIMPLE_DEVICES') && DISABLE_SIMPLE_DEVICES==1) return;

require(dirname(__FILE__).'/Rooms_updateActivityStatus.php');

if (gg('EconomMode.active')) {
    callMethod('EconomMode.deactivate');
}