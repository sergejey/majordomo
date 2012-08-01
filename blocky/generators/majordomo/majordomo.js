/**
 * Visual Blocks Language
 *
 * Copyright 2012 Google Inc.
 * http://code.google.com/p/google-blockly/
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
 * @fileoverview Generating PHP(MajorDoMo general) for text blocks.
 * @author jey@tut.by (Serge Dzheigalo)
 */

Blockly.MajorDoMo = Blockly.Generator.get('MajorDoMo');


Blockly.MajorDoMo.majordomo_say = function() {
  // Print statement.
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'TEXT', true) || '\'\'';
  return 'say(' + argument0 + ');\n';
};

Blockly.MajorDoMo.majordomo_getglobal = function() {
  // Print statement.
  var argument0 = Blockly.MajorDoMo.quote_(this.getTitleText('TEXT'));
  return 'getGlobal(' + argument0 + ')';
};

Blockly.MajorDoMo.majordomo_setglobal = function() {
  // Print statement.
  var argument0 = Blockly.MajorDoMo.quote_(this.getTitleText('TEXT'));
  var argument1 = Blockly.MajorDoMo.valueToCode(this, 'VALUE', true) || '0';
  return 'setGlobal(' + argument0 + ','+ argument1 +');\n';
};

Blockly.MajorDoMo.majordomo_runscript = function() {
  // Print statement.
  var argument0 = Blockly.MajorDoMo.quote_(this.getTitleText('TEXT'));
  var argument1 = Blockly.MajorDoMo.valueToCode(this, 'VALUE', true) || '0';
  return 'runScript(' + argument0 + ','+ argument1 +');\n';
};

Blockly.MajorDoMo.majordomo_callmethod = function() {
  // Print statement. 
  var argument0 = Blockly.MajorDoMo.quote_(this.getTitleText('TEXT'));
  var argument1 = Blockly.MajorDoMo.valueToCode(this, 'VALUE', true) || '0';
  return 'callMethod(' + argument0 + ','+ argument1 +');\n';
};

Blockly.MajorDoMo.majordomo_playsound = function() {
  // Print statement.
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'TEXT', true) || '\'\'';;
  return 'playSound(' + argument0 + ');\n';
};

Blockly.MajorDoMo.majordomo_callurl = function() {
  // Print statement.
  var argument0 = Blockly.MajorDoMo.quote_(this.getTitleText('TEXT'));
  return 'getURL(' + argument0 + ',0);\n';
};

Blockly.MajorDoMo.majordomo_openurl = function() {
  // Print statement.
  var argument0 = Blockly.MajorDoMo.quote_(this.getTitleText('TEXT'));
  return 'getURL(' + argument0 + ',0)';
};

Blockly.MajorDoMo.majordomo_getrandomline = function() {
  // Print statement.
  var argument0 = Blockly.MajorDoMo.quote_(this.getTitleText('TEXT'));
  return 'getRandomLine(' + argument0 + ')';
};

Blockly.MajorDoMo.majordomo_phpstat = function() {
  // Print statement.
  var argument0 = (this.getTitleText('TEXT'));
  return '(' + argument0 + ')';
};


Blockly.MajorDoMo.majordomo_phpcode = function() {
  // Print statement.
  var argument0 = (this.getTitleText('TEXT'));
  return '' + argument0 + ';\n';
};
