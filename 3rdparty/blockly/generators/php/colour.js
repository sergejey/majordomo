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
 * @fileoverview Generating PHP for colour blocks.
 * @author fraser@google.com (Neil Fraser)
 */
'use strict';

goog.provide('Blockly.PHP.colour');

goog.require('Blockly.PHP');


Blockly.PHP['colour_picker'] = function(block) {
  // Colour picker.
  var code = '\'' + block.getFieldValue('COLOUR') + '\'';
  return [code, Blockly.PHP.ORDER_ATOMIC];
};

Blockly.PHP['colour_random'] = function(block) {
  // Generate a random colour.
  var code = 'str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, \'0\', STR_PAD_LEFT)';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['colour_rgb'] = function(block) {
  // Compose a colour from RGB components expressed as percentages.
  var red = Blockly.PHP.valueToCode(block, 'RED',
      Blockly.PHP.ORDER_COMMA) || 0;
  var green = Blockly.PHP.valueToCode(block, 'GREEN',
      Blockly.PHP.ORDER_COMMA) || 0;
  var blue = Blockly.PHP.valueToCode(block, 'BLUE',
      Blockly.PHP.ORDER_COMMA) || 0;
  var code = 'sprintf("%02x%02x%02x", '+red+', '+green+', '+blue+')';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['colour_blend'] = function(block) {
  // Blend two colours together.
  var c1 = Blockly.PHP.valueToCode(block, 'COLOUR1',
      Blockly.PHP.ORDER_COMMA) || '\'#000000\'';
  var c2 = Blockly.PHP.valueToCode(block, 'COLOUR2',
      Blockly.PHP.ORDER_COMMA) || '\'#000000\'';
  var ratio = Blockly.PHP.valueToCode(block, 'RATIO',
      Blockly.PHP.ORDER_COMMA) || 0.5;
  var functionName = Blockly.PHP.provideFunction_(
      'colour_blend',
      [ 'function ' + Blockly.PHP.FUNCTION_NAME_PLACEHOLDER_ +
          '(c1, c2, ratio) {',
        '  ratio = Math.max(Math.min(Number(ratio), 1), 0);',
        '  var r1 = parseInt(c1.substring(1, 3), 16);',
        '  var g1 = parseInt(c1.substring(3, 5), 16);',
        '  var b1 = parseInt(c1.substring(5, 7), 16);',
        '  var r2 = parseInt(c2.substring(1, 3), 16);',
        '  var g2 = parseInt(c2.substring(3, 5), 16);',
        '  var b2 = parseInt(c2.substring(5, 7), 16);',
        '  var r = Math.round(r1 * (1 - ratio) + r2 * ratio);',
        '  var g = Math.round(g1 * (1 - ratio) + g2 * ratio);',
        '  var b = Math.round(b1 * (1 - ratio) + b2 * ratio);',
        '  r = (\'0\' + (r || 0).toString(16)).slice(-2);',
        '  g = (\'0\' + (g || 0).toString(16)).slice(-2);',
        '  b = (\'0\' + (b || 0).toString(16)).slice(-2);',
        '  return \'\' + r + g + b;',
        '}']);
  var code = functionName + '(' + c1 + ', ' + c2 + ', ' + ratio + ')';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};
