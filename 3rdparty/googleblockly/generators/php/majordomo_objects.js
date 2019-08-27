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

goog.provide('Blockly.PHP.majordomo_objects');

goog.require('Blockly.PHP');


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

Blockly.PHP['majordomo_setcurrent'] = function(block) {
  var value = Blockly.PHP.valueToCode(block, 'VALUE',Blockly.PHP.ORDER_NONE) || '\'\'';
  var property = Blockly.PHP.valueToCode(block, 'PROPERTY',Blockly.PHP.ORDER_NONE) || '';
  var code = '$this->setProperty('+property+', ' + value + ');\n';
  return code;
};

Blockly.PHP['majordomo_getcurrent'] = function(block) {
  var property = Blockly.PHP.valueToCode(block, 'PROPERTY',Blockly.PHP.ORDER_NONE) || '';
  var code = '$this->getProperty('+property+')';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['majordomo_callmethod'] = function(block) {
  var object = Blockly.PHP.valueToCode(block, 'OBJECT',Blockly.PHP.ORDER_NONE) || 'ThisComputer';
  var method = Blockly.PHP.valueToCode(block, 'METHOD',Blockly.PHP.ORDER_NONE) || '';
  var code = 'callMethod('+object+'.\'.\'.'+method+');\n';
  return code;
};

Blockly.PHP['majordomo_callmethodwithparams'] = function(block) {
  var object = Blockly.PHP.valueToCode(block, 'OBJECT',Blockly.PHP.ORDER_NONE) || 'ThisComputer';
  var method = Blockly.PHP.valueToCode(block, 'METHOD',Blockly.PHP.ORDER_NONE) || '';
  var params = Blockly.PHP.valueToCode(block, 'PARAMS',Blockly.PHP.ORDER_NONE) || 'array()';
  var code = 'callMethod('+object+'.\'.\'.'+method+', ' + params + ');\n';
  return code;
};

Blockly.PHP['majordomo_callmethodcurrent'] = function(block) {
  var method = Blockly.PHP.valueToCode(block, 'METHOD',Blockly.PHP.ORDER_NONE) || '';
  var code = '$this->callMethod('+method+');\n';
  return code;
};

Blockly.PHP['majordomo_callmethodwithparamscurrent'] = function(block) {
  var method = Blockly.PHP.valueToCode(block, 'METHOD',Blockly.PHP.ORDER_NONE) || '';
  var params = Blockly.PHP.valueToCode(block, 'PARAMS',Blockly.PHP.ORDER_NONE) || 'array()';
  var code = '$this->callMethod('+method+', ' + params + ');\n';
  return code;
};

Blockly.PHP['majordomo_keyvalue'] = function(block) {
  var key = Blockly.PHP.valueToCode(block, 'KEY',Blockly.PHP.ORDER_NONE) || '';
  var value = Blockly.PHP.valueToCode(block, 'VALUE',Blockly.PHP.ORDER_NONE) || '';
  var code = key+'=>'+value;
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['majordomo_paramvalue'] = function(block) {
  var key = Blockly.PHP.valueToCode(block, 'KEY',Blockly.PHP.ORDER_NONE) || '';
  var value = Blockly.PHP.valueToCode(block, 'VALUE',Blockly.PHP.ORDER_NONE) || '';
  var code = 'array('+key+'=>'+value+')';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['majordomo_getobjects'] = function(block) {
  var key = Blockly.PHP.valueToCode(block, 'CLASS',Blockly.PHP.ORDER_NONE) || '';
  var code = 'getObjectsByClass('+key+')';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};