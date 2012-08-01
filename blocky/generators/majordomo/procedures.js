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
 * @fileoverview Generating JavaScript for procedure blocks.
 * @author fraser@google.com (Neil Fraser)
 * Due to the frequency of long strings, the 80-column wrap rule need not apply
 * to language files.
 */

Blockly.MajorDoMo = Blockly.Generator.get('MajorDoMo');

Blockly.MajorDoMo.procedures_defreturn = function() {
  // Define a procedure with a return value.
  var funcName = Blockly.MajorDoMo.variableDB_.getName(this.getTitleText('NAME'),
      Blockly.Procedures.NAME_TYPE);
  var branch = Blockly.MajorDoMo.statementToCode(this, 'STACK');
  var returnValue = Blockly.MajorDoMo.valueToCode(this, 'RETURN', true) || '';
  if (returnValue) {
    returnValue = '  return ' + returnValue + ';\n';
  }
  var code = 'function ' + funcName + '() {\n' + branch + returnValue + '}\n';
  code = Blockly.MajorDoMo.scrub_(this, code);
  Blockly.MajorDoMo.definitions_[funcName] = code;
  return null;
};

// Defining a procedure without a return value uses the same generator as
// a procedure with a return value.
Blockly.MajorDoMo.procedures_defnoreturn =
    Blockly.MajorDoMo.procedures_defreturn;

Blockly.MajorDoMo.procedures_callreturn = function() {
  // Call a procedure with a return value.
  var funcName = Blockly.MajorDoMo.variableDB_.getName(this.getTitleText('NAME'),
      Blockly.Procedures.NAME_TYPE);
  var code = funcName + '()';
  return code;
};

Blockly.MajorDoMo.procedures_callnoreturn = function() {
  // Call a procedure with no return value.
  var funcName = Blockly.MajorDoMo.variableDB_.getName(this.getTitleText('NAME'),
      Blockly.Procedures.NAME_TYPE);
  var code = funcName + '();\n';
  return code;
};

