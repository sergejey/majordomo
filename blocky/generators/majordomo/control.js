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
 * @fileoverview Generating JavaScript for control blocks.
 * @author fraser@google.com (Neil Fraser)
 * Due to the frequency of long strings, the 80-column wrap rule need not apply
 * to language files.
 */

Blockly.MajorDoMo = Blockly.Generator.get('MajorDoMo');

Blockly.MajorDoMo.controls_if = function() {
  // If/elseif/else condition.
  var n = 0;
  var argument = Blockly.MajorDoMo.valueToCode(this, 'IF' + n, true) || 'false';
  var branch = Blockly.MajorDoMo.statementToCode(this, 'DO' + n);
  var code = 'if (' + argument + ') {\n' + branch + '}';
  for (n = 1; n <= this.elseifCount_; n++) {
    argument = Blockly.MajorDoMo.valueToCode(this, 'IF' + n, true) || 'false';
    branch = Blockly.MajorDoMo.statementToCode(this, 'DO' + n);
    code += ' else if (' + argument + ') {\n' + branch + '}';
  }
  if (this.elseCount_) {
    branch = Blockly.MajorDoMo.statementToCode(this, 'ELSE');
    code += ' else {\n' + branch + '}';
  }
  return code + '\n';
};

Blockly.MajorDoMo.controls_whileUntil = function() {
  // Do while/until loop.
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'BOOL', true) || 'false';
  var branch0 = Blockly.MajorDoMo.statementToCode(this, 'DO');
  if (this.getTitleText('MODE') == this.MSG_UNTIL) {
    if (!argument0.match(/^\w+$/)) {
      argument0 = '(' + argument0 + ')';
    }
    argument0 = '!' + argument0;
  }
  return 'while (' + argument0 + ') {\n' + branch0 + '}\n';
};

Blockly.MajorDoMo.controls_for = function() {
  // For loop.
  var variable0 = Blockly.MajorDoMo.variableDB_.getName(
      this.getInputVariable('VAR'), Blockly.Variables.NAME_TYPE);
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'FROM', true) || '0';
  var argument1 = Blockly.MajorDoMo.valueToCode(this, 'TO', true) || '0';
  var branch0 = Blockly.MajorDoMo.statementToCode(this, 'DO');
  var code;
  if (argument1.match(/^\w+$/)) {
    code = 'for ($' + variable0 + ' = ' + argument0 + '; $' + variable0 + ' <= ' + argument1 + '; $' + variable0 + '++) {\n' +
        branch0 + '}\n';
  } else {
    // The end value appears to be more complicated than a simple variable.
    // Cache it to a variable to prevent repeated look-ups.
    var endVar = Blockly.MajorDoMo.variableDB_.getDistinctName(
        variable0 + '_end', Blockly.Variables.NAME_TYPE);
    code = '$' + endVar + ' = ' + argument1 + ';\n' +
        'for ($' + variable0 + ' = ' + argument0 + '; $' + variable0 + ' <= ' + endVar + '; $' + variable0 + '++) {\n' +
        branch0 + '}\n';
  }
  return code;
};

Blockly.MajorDoMo.controls_forEach = function() {
  // For each loop.
  var variable0 = Blockly.MajorDoMo.variableDB_.getName(
      this.getInputVariable('VAR'), Blockly.Variables.NAME_TYPE);
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'LIST', true) || '[]';
  var branch0 = Blockly.MajorDoMo.statementToCode(this, 'DO');
  var code;
  var indexVar = Blockly.MajorDoMo.variableDB_.getDistinctName(
      variable0 + '_index', Blockly.Variables.NAME_TYPE);
  if (argument0.match(/^\w+$/)) {
    branch0 = '  ' + variable0 + ' = ' + argument0 + '[' + indexVar + '];\n' + branch0;
    code = 'for (var ' + indexVar + ' in  ' + argument0 + ') {\n' +
        branch0 + '}\n';
  } else {
    // The list appears to be more complicated than a simple variable.
    // Cache it to a variable to prevent repeated look-ups.
    var listVar = Blockly.MajorDoMo.variableDB_.getDistinctName(
        variable0 + '_list', Blockly.Variables.NAME_TYPE);
    //branch0 = '  ' + variable0 + ' = ' + listVar + '[' + indexVar + '];\n' + branch0;
    code = '$' + listVar + ' = ' + argument0 + ';\n' +
        'foreach ($' + listVar + ' as $' + indexVar + '=>$'+variable0+') {\n' +
        branch0 + '}\n';
  }
  return code;
};

Blockly.MajorDoMo.controls_flow_statements = function() {
  // Flow statements: continue, break.
  switch (this.getTitleText('FLOW')) {
    case this.MSG_BREAK:
      return 'break;\n';
    case this.MSG_CONTINUE:
      return 'continue;\n';
  }
  throw 'Unknown flow statement.';
};
