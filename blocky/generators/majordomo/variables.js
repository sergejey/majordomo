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
 * @fileoverview Generating JavaScript for variable blocks.
 * @author fraser@google.com (Neil Fraser)
 * Due to the frequency of long strings, the 80-column wrap rule need not apply
 * to language files.
 */

Blockly.MajorDoMo = Blockly.Generator.get('MajorDoMo');

Blockly.MajorDoMo.variables_get = function() {
  // Variable getter.
  //return 'getGlobal("'+Blockly.MajorDoMo.variableDB_.getName(this.getTitleText('VAR'),Blockly.Variables.NAME_TYPE)+'")';
  return '$'+Blockly.MajorDoMo.variableDB_.getName(this.getTitleText('VAR'),Blockly.Variables.NAME_TYPE)+'';
};

Blockly.MajorDoMo.variables_set = function() {
  // Variable setter.
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'VALUE', true) || '0';
  var varName = Blockly.MajorDoMo.variableDB_.getName(
      this.getTitleText('VAR'),Blockly.Variables.NAME_TYPE);
  //return 'setGlobal("'+varName+'", ' + argument0 + ');\n';
  return '$'+varName+'=' + argument0 + ';\n';
};
