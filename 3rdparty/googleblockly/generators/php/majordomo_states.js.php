<?php    header("Content-type:application/x-javascript");    ?>
/**
 * @license
 * Visual Blocks Language
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
 * @fileoverview Generating PHP for variable blocks.
 * @author fraser@google.com (Neil Fraser)
 */
'use strict';

goog.provide('Blockly.PHP.majordomo_states');

goog.require('Blockly.PHP');

<?php

chdir(dirname(__FILE__) . '/../../../../');

include_once("./config.php");
include_once("./lib/loader.php");

$session=new session("prj");

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");

$objects = getObjectsByClass('OperationalModes');

foreach($objects as $object) {
$object = SQLSelectOne("SELECT * FROM objects WHERE ID=" . $object['ID']);
?>
Blockly.PHP['majordomo_<?php echo $object['TITLE']?>'] = function(block) {
    var code = 'getGlobal("<?php echo $object['TITLE']?>.active")';
    return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

<?php
}


$session->save();