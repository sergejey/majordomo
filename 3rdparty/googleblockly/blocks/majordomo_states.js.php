<?php    header("Content-type:application/x-javascript");    ?>
/**
 * @license
 * Visual Blocks Editor
 *
 * Copyright 2012 Google Inc.
 * https://blockly.googlecode.com/
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * @fileoverview Variable blocks for Blockly.
 * @author fraser@google.com (Neil Fraser)
 */
'use strict';

goog.provide('Blockly.Blocks.majordomo_states');

goog.require('Blockly.Blocks');

<?php

chdir(dirname(__FILE__) . '/../../../');

include_once("./config.php");
include_once("./lib/loader.php");

$session = new session("prj");

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

$objects = getObjectsByClass('OperationalModes');


foreach($objects as $object) {
$object = SQLSelectOne("SELECT * FROM objects WHERE ID=" . $object['ID']);
if (!$object['DESCRIPTION']) {
    $object['DESCRIPTION']=$object['TITLE'];
}
?>

Blockly.Blocks['majordomo_<?php echo $object['TITLE']?>'] = {
    init: function () {
        var thisBlock = this;
        this.setColour(220);
        this.appendDummyInput()
            .appendField('<?php echo addcslashes($object['DESCRIPTION'], "'")?>');
        this.setOutput(true);
        this.setTooltip('');
    }
};

<?php
}

$session->save();