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

goog.provide('Blockly.PHP.majordomo_time');

goog.require('Blockly.PHP');



Blockly.PHP['majordomo_timeis'] = function(block) {
  var time = Blockly.PHP.valueToCode(block, 'TIME',Blockly.PHP.ORDER_NONE) || '00:00';
  var code = 'timeIs('+time+')';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['majordomo_timenow'] = function(block) {
  var code = 'timeNow()';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['majordomo_isweekend'] = function(block) {
  var code = 'isWeekEnd()';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['majordomo_isworkday'] = function(block) {
  var code = 'isWeekDay()';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['majordomo_timebefore'] = function(block) {
  var time = Blockly.PHP.valueToCode(block, 'TIME',Blockly.PHP.ORDER_NONE) || '00:00';
  var code = 'timeBefore('+time+')';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['majordomo_timeafter'] = function(block) {
  var time = Blockly.PHP.valueToCode(block, 'TIME',Blockly.PHP.ORDER_NONE) || '00:00';
  var code = 'timeAfter('+time+')';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['majordomo_timebetween'] = function(block) {
  var time1 = Blockly.PHP.valueToCode(block, 'TIME1',Blockly.PHP.ORDER_NONE) || '00:00';
  var time2 = Blockly.PHP.valueToCode(block, 'TIME2',Blockly.PHP.ORDER_NONE) || '00:00';
  var code = 'timeBetween('+time1+', '+time2+')';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['majordomo_cleartimeout'] = function(block) {
  var timer = Blockly.PHP.valueToCode(block, 'TIMER',Blockly.PHP.ORDER_NONE) || 'none';
  var code = 'clearTimeOut('+timer+');\n';
  return code;
};

Blockly.PHP['majordomo_settimeout'] = function(block) {
  var timer = Blockly.PHP.valueToCode(block, 'TIMER',Blockly.PHP.ORDER_NONE) || 'none';
  var delay = Blockly.PHP.valueToCode(block, 'DELAY',Blockly.PHP.ORDER_NONE) || '60';
  var branch = Blockly.PHP.statementToCode(block, 'DO');
  //branch=branch.replace(new RegExp('\n', 'g'), '');
  var code = '$timerCode=<<<EOT\n'+branch+'\nEOT;\nsetTimeOut('+timer+', $timerCode, (int)('+delay+'));\n';
  return code;
};