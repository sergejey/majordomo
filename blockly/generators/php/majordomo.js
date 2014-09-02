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

goog.provide('Blockly.PHP.majordomo');

goog.require('Blockly.PHP');


Blockly.PHP['majordomo_say'] = function(block) {
  // Variable getter.
  var msg = Blockly.PHP.valueToCode(block, 'TEXT',
      Blockly.PHP.ORDER_NONE) || '\'\'';
  var priority = Number(block.getFieldValue('NUMBER')) || 0;
  var code = 'say(' + msg + ', '+priority+');\n';
  return code;
};

Blockly.PHP['majordomo_say_simple'] = function(block) {
  var msg = Blockly.PHP.valueToCode(block, 'TEXT',
      Blockly.PHP.ORDER_NONE) || '\'\'';

  var code = 'say(' + msg + ', 2);\n';
  return code;
};

Blockly.PHP['majordomo_runscript'] = function(block) {
  var msg = Blockly.PHP.valueToCode(block, 'TEXT',
      Blockly.PHP.ORDER_NONE) || '\'\'';
  var code = 'runScript(' + msg + ');\n';
  return code;
};

Blockly.PHP['majordomo_setglobal'] = function(block) {
  var value = Blockly.PHP.valueToCode(block, 'VALUE',Blockly.PHP.ORDER_NONE) || '\'\'';
  var object = Blockly.PHP.valueToCode(block, 'OBJECT',Blockly.PHP.ORDER_NONE) || 'ThisComputer';
  var property = Blockly.PHP.valueToCode(block, 'PROPERTY',Blockly.PHP.ORDER_NONE) || '';
  var code = 'setGlobal('+object+'.\'.\'.'+property+', ' + value + ');\n';
  return code;
};

Blockly.PHP['majordomo_getglobal'] = function(block) {
  var object = Blockly.PHP.valueToCode(block, 'OBJECT',Blockly.PHP.ORDER_NONE) || 'ThisComputer';
  var property = Blockly.PHP.valueToCode(block, 'PROPERTY',Blockly.PHP.ORDER_NONE) || '';
  var code = 'getGlobal('+object+'.\'.\'.'+property+')';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

//runScript with params
//callMethod
//getURL
//getURL (content)
//getRandomLine (from file)
//playSound
//timeNow
//isWeekEnd
//isWorkDay
//timeIs
//timeBefore
//timeAfter
//timeBetween
//clearTimeOut
//setTimeOut