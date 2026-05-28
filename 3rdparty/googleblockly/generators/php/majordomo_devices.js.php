<?php    header("Content-type:application/x-javascript");    ?>
'use strict';

goog.provide('Blockly.PHP.majordomo_devices');

goog.require('Blockly.PHP');

<?php

chdir(dirname(__FILE__) . '/../../../../');

include_once("./config.php");
include_once("./lib/loader.php");

$session = new session("prj");

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
include_once(DIR_MODULES . "devices/devices.class.php");

$ctl = new control_modules();
$dev = new devices();
$dev->setDictionary();

@include_once(ROOT . 'languages/devices' . '_' . SETTINGS_SITE_LANGUAGE . '.php');
@include_once(ROOT . 'languages/devices' . '_default' . '.php');

$blocks = SQLSelect("SELECT * FROM devices_groups ORDER BY ID");
$total = count($blocks);
for($i = 0;$i < $total;$i++) {
?>
Blockly.PHP['majordomo_group_<?php echo $blocks[$i]['ID'];?>_turnOn'] = function (block) {
    var code = '$objects=getObjectsByProperty("group<?php echo $blocks[$i]['SYS_NAME']?>",1);\nforeach($objects as $object_title) {\ncallMethod($object_title.".turnOn");\n}\n';
    return code;
};
Blockly.PHP['majordomo_group_<?php echo $blocks[$i]['ID'];?>_turnOff'] = function (block) {
    var code = '$objects=getObjectsByProperty("group<?php echo $blocks[$i]['SYS_NAME']?>",1);\nforeach($objects as $object_title) {\ncallMethod($object_title.".turnOff");\n}\n';
    return code;
};
<?php
}

$blocks = SQLSelect("SELECT * FROM devices ORDER BY ID");
$total = count($blocks);
for($i = 0;$i < $total;$i++) {

if ($dev->device_types[$blocks[$i]['TYPE']]['PARENT_CLASS'] == 'SControllers') {
?>
Blockly.PHP['majordomo_device_<?php echo $blocks[$i]['ID'];?>_switch'] = function (block) {
    var mode = block.getFieldValue('MODE') || 'OFF';
    var code = 'callMethod("<?php echo $blocks[$i]['LINKED_OBJECT'] . '.';?>';
    if (mode == 'ON') {
        code += 'turnOn';
    }
    if (mode == 'OFF') {
        code += 'turnOff';
    }
    code += '");\n';
    return code;
};
Blockly.PHP['majordomo_device_<?php echo $blocks[$i]['ID'];?>_turnOn'] = function (block) {
    var code = 'callMethod("<?php echo $blocks[$i]['LINKED_OBJECT'] . '.turnOn';?>");\n';
    return code;
};
Blockly.PHP['majordomo_device_<?php echo $blocks[$i]['ID'];?>_turnOff'] = function (block) {
    var code = 'callMethod("<?php echo $blocks[$i]['LINKED_OBJECT'] . '.turnOff';?>");\n';
    return code;
};
Blockly.PHP['majordomo_device_<?php echo $blocks[$i]['ID'];?>_currentStatus'] = function (block) {
    var code = 'getGlobal("<?php echo $blocks[$i]['LINKED_OBJECT'] . '.status';?>")';
    return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};
<?php
}
if ($dev->device_types[$blocks[$i]['TYPE']]['PARENT_CLASS'] == 'SSensors') {
?>
Blockly.PHP['majordomo_device_<?php echo $blocks[$i]['ID'];?>_currentValue'] = function (block) {
    var code = 'getGlobal("<?php echo $blocks[$i]['LINKED_OBJECT'] . '.value';?>")';
    return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};
Blockly.PHP['majordomo_device_<?php echo $blocks[$i]['ID'];?>_minValue'] = function (block) {
    var code = 'getGlobal("<?php echo $blocks[$i]['LINKED_OBJECT'] . '.minValue';?>")';
    return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};
Blockly.PHP['majordomo_device_<?php echo $blocks[$i]['ID'];?>_maxValue'] = function (block) {
    var code = 'getGlobal("<?php echo $blocks[$i]['LINKED_OBJECT'] . '.maxValue';?>")';
    return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};
<?php
}
if ($blocks[$i]['TYPE'] == 'dimmer') {
?>
Blockly.PHP['majordomo_device_<?php echo $blocks[$i]['ID'];?>_setLevel'] = function (block) {
    var value = Blockly.PHP.valueToCode(block, 'VALUE', Blockly.PHP.ORDER_NONE) || '\'\'';
    var code = 'setGlobal("<?php echo $blocks[$i]['LINKED_OBJECT'] . '.level';?>", ' + value + ');\n';
    return code;
};

<?php
}
if ($blocks[$i]['TYPE'] == 'counter') {
?>
Blockly.PHP['majordomo_device_<?php echo $blocks[$i]['ID'];?>_currentValue'] = function (block) {
    var code = 'getGlobal("<?php echo $blocks[$i]['LINKED_OBJECT'] . '.value';?>")';
    return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['majordomo_device_<?php echo $blocks[$i]['ID'];?>_setValue'] = function (block) {
    var value = Blockly.PHP.valueToCode(block, 'VALUE', Blockly.PHP.ORDER_NONE) || '\'\'';
    var code = 'setGlobal("<?php echo $blocks[$i]['LINKED_OBJECT'] . '.value';?>", ' + value + ');\n';
    return code;
};

<?php
}
if ($blocks[$i]['TYPE'] == 'motion') {
?>
Blockly.PHP['majordomo_device_<?php echo $blocks[$i]['ID'];?>_motionDetected'] = function (block) {
    var code = 'getGlobal("<?php echo $blocks[$i]['LINKED_OBJECT'] . '.status';?>")';
    return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};
<?php
}
if ($blocks[$i]['TYPE'] == 'rgb') {
?>
Blockly.PHP['majordomo_device_<?php echo $blocks[$i]['ID'];?>_setColor'] = function (block) {
    var color = Blockly.PHP.valueToCode(block, 'COLOR', Blockly.PHP.ORDER_NONE) || '\'\'';
    var code = 'callMethod("<?php echo $blocks[$i]['LINKED_OBJECT'] . '.setColor';?>",array("color"=>' + color + '));';
    return code;
};
<?php
}
if ($blocks[$i]['TYPE'] == 'openclose') {
?>
Blockly.PHP['majordomo_device_<?php echo $blocks[$i]['ID'];?>_currentStatus'] = function (block) {
    var code = 'getGlobal("<?php echo $blocks[$i]['LINKED_OBJECT'] . '.status';?>")';
    return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};
<?php
}
/*
if ($blocks[$i]['TYPE']=='switch') {
?>
Blockly.PHP['majordomo_device_<?php echo $blocks[$i]['ID'];?>_currentStatus'] = function(block) {
var code = 'getGlobal("<?php echo $blocks[$i]['LINKED_OBJECT'].'.status';?>")';
return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};
<?php
}
*/
if ($blocks[$i]['TYPE'] == 'button') {
?>
Blockly.PHP['majordomo_device_<?php echo $blocks[$i]['ID'];?>_press'] = function (block) {
    var code = 'callMethod("<?php echo $blocks[$i]['LINKED_OBJECT'] . '.press';?>");\n';
    return code;
};
<?php
}

?>

<?php
}

$session->save();
