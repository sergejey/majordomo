<?php    header("Content-type:application/x-javascript");    ?>
'use strict';

goog.provide('Blockly.Blocks.majordomo_devices');

goog.require('Blockly.Blocks');

<?php

chdir(dirname(__FILE__) . '/../../../');

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

$blocks = SQLSelect("SELECT * FROM devices_groups ORDER BY TITLE");
$total = count($blocks);
for($i = 0;$i < $total;$i++) {
?>
Blockly.Blocks['majordomo_group_<?php echo $blocks[$i]['ID'];?>_turnOn'] = {
    init: function () {
        var thisBlock = this;
        this.appendDummyInput()
            .appendField('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_TURN_ON;?>');
        this.setInputsInline(true);
        this.setColour(275);
        this.setOutput(false);
        this.setPreviousStatement(true);
        this.setNextStatement(true);
        this.setTooltip('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_TURN_ON;?>');
    }
};

Blockly.Blocks['majordomo_group_<?php echo $blocks[$i]['ID'];?>_turnOff'] = {
    init: function () {
        var thisBlock = this;
        this.appendDummyInput()
            .appendField('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_TURN_OFF;?>');
        this.setInputsInline(true);
        this.setColour(275);
        this.setOutput(false);
        this.setPreviousStatement(true);
        this.setNextStatement(true);
        this.setTooltip('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_TURN_OFF;?>');
    }
};

<?php
}

$blocks = SQLSelect("SELECT * FROM devices ORDER BY ID");
$total = count($blocks);
for($i = 0;$i < $total;$i++) {

if ($dev->device_types[$blocks[$i]['TYPE']]['PARENT_CLASS'] == 'SControllers') {
?>

Blockly.Blocks['majordomo_device_<?php echo $blocks[$i]['ID'];?>_switch'] = {
    init: function () {
        var thisBlock = this;

        var MODE =
            [
                ['<?php echo LANG_DEVICES_TURN_ON;?>', 'ON'],
                ['<?php echo LANG_DEVICES_TURN_OFF;?>', 'OFF']
            ];

        this.appendDummyInput()
            .appendField('<?php echo $blocks[$i]['TITLE'];?>')
            .appendField(new Blockly.FieldDropdown(MODE), 'MODE');
        this.setInputsInline(true);
        this.setColour(275);
        this.setOutput(false);
        this.setPreviousStatement(true);
        this.setNextStatement(true);
        this.setTooltip('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_SWITCH;?>');
    }
};

Blockly.Blocks['majordomo_device_<?php echo $blocks[$i]['ID'];?>_turnOn'] = {
    init: function () {
        var thisBlock = this;
        this.appendDummyInput()
            .appendField('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_TURN_ON;?>');
        this.setInputsInline(true);
        this.setColour(275);
        this.setOutput(false);
        this.setPreviousStatement(true);
        this.setNextStatement(true);
        this.setTooltip('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_TURN_ON;?>');
    }
};

Blockly.Blocks['majordomo_device_<?php echo $blocks[$i]['ID'];?>_turnOff'] = {
    init: function () {
        var thisBlock = this;
        this.appendDummyInput()
            .appendField('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_TURN_OFF;?>');
        this.setInputsInline(true);
        this.setColour(275);
        this.setOutput(false);
        this.setPreviousStatement(true);
        this.setNextStatement(true);
        this.setTooltip('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_TURN_OFF;?>');
    }
};

Blockly.Blocks['majordomo_device_<?php echo $blocks[$i]['ID'];?>_currentStatus'] = {
    init: function () {
        var thisBlock = this;
        this.appendDummyInput()
            .appendField('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_IS_ON;?>');
        this.setInputsInline(true);
        this.setColour(175);
        this.setOutput(true);
        this.setPreviousStatement(false);
        this.setNextStatement(false);
        this.setTooltip('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_IS_ON;?>');
    }
};

<?php
}
if ($blocks[$i]['TYPE'] == 'rgb') {
?>
Blockly.Blocks['majordomo_device_<?php echo $blocks[$i]['ID'];?>_setColor'] = {
    init: function () {
        var thisBlock = this;
        this.appendValueInput('COLOR')
            .appendField('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_SET_COLOR;?>');
        this.setInputsInline(true);
        this.setColour(275);
        this.setOutput(false);
        this.setPreviousStatement(true);
        this.setNextStatement(true);
        this.setTooltip('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_SET_COLOR;?>');

    }
};
<?php
}
if ($dev->device_types[$blocks[$i]['TYPE']]['PARENT_CLASS'] == 'SSensors') {
?>
Blockly.Blocks['majordomo_device_<?php echo $blocks[$i]['ID'];?>_currentValue'] = {
    init: function () {
        var thisBlock = this;
        this.appendDummyInput()
            .appendField('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_CURRENT_VALUE;?>');
        this.setInputsInline(true);
        this.setColour(175);
        this.setOutput(true);
        this.setPreviousStatement(false);
        this.setNextStatement(false);
        this.setTooltip('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_CURRENT_VALUE;?>');
    }
};

Blockly.Blocks['majordomo_device_<?php echo $blocks[$i]['ID'];?>_minValue'] = {
    init: function () {
        var thisBlock = this;
        this.appendDummyInput()
            .appendField('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_MIN_VALUE;?>');
        this.setInputsInline(true);
        this.setColour(175);
        this.setOutput(true);
        this.setPreviousStatement(false);
        this.setNextStatement(false);
        this.setTooltip('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_MIN_VALUE;?>');
    }
};

Blockly.Blocks['majordomo_device_<?php echo $blocks[$i]['ID'];?>_maxValue'] = {
    init: function () {
        var thisBlock = this;
        this.appendDummyInput()
            .appendField('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_MAX_VALUE;?>');
        this.setInputsInline(true);
        this.setColour(175);
        this.setOutput(true);
        this.setPreviousStatement(false);
        this.setNextStatement(false);
        this.setTooltip('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_MAX_VALUE;?>');
    }
};
<?php
}
if ($blocks[$i]['TYPE'] == 'dimmer') {
?>
Blockly.Blocks['majordomo_device_<?php echo $blocks[$i]['ID'];?>_setLevel'] = {
    init: function () {
        var thisBlock = this;
        this.setColour(220);
        this.appendDummyInput()
            .appendField('<?php echo $blocks[$i]['TITLE'] . ' ' . LANG_SET . ' %';?>');
        this.appendValueInput("VALUE");
        //.setCheck("String");
        this.setInputsInline(true);
        this.setPreviousStatement(true);
        this.setNextStatement(true);
        this.setTooltip('');
    }
};
<?php
}
if ($blocks[$i]['TYPE'] == 'counter') {
?>
Blockly.Blocks['majordomo_device_<?php echo $blocks[$i]['ID'];?>_currentValue'] = {
    init: function () {
        var thisBlock = this;
        this.appendDummyInput()
            .appendField('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_CURRENT_VALUE;?>');
        this.setInputsInline(true);
        this.setColour(175);
        this.setOutput(true);
        this.setPreviousStatement(false);
        this.setNextStatement(false);
        this.setTooltip('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_CURRENT_VALUE;?>');
    }
};
Blockly.Blocks['majordomo_device_<?php echo $blocks[$i]['ID'];?>_setValue'] = {
    init: function () {
        var thisBlock = this;
        this.setColour(220);
        this.appendDummyInput()
            .appendField('<?php echo $blocks[$i]['TITLE'] . ' ' . LANG_SET;?>');
        this.appendValueInput("VALUE");
        //.setCheck("String");
        this.setInputsInline(true);
        this.setPreviousStatement(true);
        this.setNextStatement(true);
        this.setTooltip('');
    }
};
<?php
}
if ($blocks[$i]['TYPE'] == 'motion') {
?>
Blockly.Blocks['majordomo_device_<?php echo $blocks[$i]['ID'];?>_motionDetected'] = {
    init: function () {
        var thisBlock = this;
        this.appendDummyInput()
            .appendField('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_MOTION_DETECTED;?>');
        this.setInputsInline(true);
        this.setColour(175);
        this.setOutput(true);
        this.setPreviousStatement(false);
        this.setNextStatement(false);
        this.setTooltip('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_MOTION_DETECTED;?>');
    }
};
<?php
}
if ($blocks[$i]['TYPE'] == 'openclose') {
?>
Blockly.Blocks['majordomo_device_<?php echo $blocks[$i]['ID'];?>_currentStatus'] = {
    init: function () {
        var thisBlock = this;
        this.appendDummyInput()
            .appendField('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_IS_CLOSED;?>');
        this.setInputsInline(true);
        this.setColour(175);
        this.setOutput(true);
        this.setPreviousStatement(false);
        this.setNextStatement(false);
        this.setTooltip('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_IS_CLOSED;?>');
    }
};
<?php
}
/*
if ($blocks[$i]['TYPE']=='switch') {
?>
Blockly.Blocks['majordomo_device_<?php echo $blocks[$i]['ID'];?>_currentStatus'] = {
  init: function () {
    var thisBlock = this;
    this.appendDummyInput()
        .appendField('<?php echo $blocks[$i]['TITLE'].'.'.LANG_DEVICES_IS_ON;?>');
    this.setInputsInline(true);
    this.setColour(175);
    this.setOutput(true);
    this.setPreviousStatement(false);
    this.setNextStatement(false);
    this.setTooltip('<?php echo $blocks[$i]['TITLE'].'.'.LANG_DEVICES__IS_ON;?>');
  }
};
<?php
}
*/
if ($blocks[$i]['TYPE'] == 'button') {
?>

Blockly.Blocks['majordomo_device_<?php echo $blocks[$i]['ID'];?>_press'] = {
    init: function () {
        var thisBlock = this;
        this.appendDummyInput()
            .appendField('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_PRESS;?>');
        this.setInputsInline(true);
        this.setColour(275);
        this.setOutput(false);
        this.setPreviousStatement(true);
        this.setNextStatement(true);
        this.setTooltip('<?php echo $blocks[$i]['TITLE'] . '.' . LANG_DEVICES_PRESS;?>');
    }
};


<?php
}
}

$session->save();
