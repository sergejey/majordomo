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
 * @fileoverview Generating JavaScript for text blocks.
 * @author fraser@google.com (Neil Fraser)
 * Due to the frequency of long strings, the 80-column wrap rule need not apply
 * to language files.
 */

Blockly.MajorDoMo = Blockly.Generator.get('MajorDoMo');

Blockly.MajorDoMo.text = function() {
  // Text value.
  return Blockly.MajorDoMo.quote_(this.getTitleText('TEXT'));
};

Blockly.MajorDoMo.text_join = function(opt_dropParens) {
  // Create a string made up of any number of elements of any type.
  if (this.itemCount_ == 0) {
    return '\'\'';
  } else if (this.itemCount_ == 1) {
    var argument0 = Blockly.MajorDoMo.valueToCode(this, 'ADD0', true) || '\'\'';
    return '(' + argument0 + ')';
  } else if (this.itemCount_ == 2) {
    var argument0 = Blockly.MajorDoMo.valueToCode(this, 'ADD0', true) || '\'\'';
    var argument1 = Blockly.MajorDoMo.valueToCode(this, 'ADD1', true) || '\'\'';
    var code = '(' + argument0 + ') . (' + argument1 + ')';
    if (!opt_dropParens) {
      code = '(' + code + ')';
    }
    return code;
  } else {
    var code = new Array(this.itemCount_);
    for (n = 0; n < this.itemCount_; n++) {
      code[n] = Blockly.MajorDoMo.valueToCode(this, 'ADD' + n, true) || '\'\'';
    }
    return 'implode("", ' + code.join(',') + ')';
  }
};

Blockly.MajorDoMo.text_length = function() {
  // String length.
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'VALUE') || '\'\'';
  return 'strlen('+argument0 + ')';
};

Blockly.MajorDoMo.text_isEmpty = function() {
  // Is the string null?
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'VALUE') || '\'\'';
  return '!' + argument0 + '';
};

Blockly.MajorDoMo.text_endString = function() {
  // Return a leading or trailing substring.
  var first = this.getInputLabel('NUM') == this.MSG_FIRST;
  var code;
  if (first) {
    var argument0 = Blockly.MajorDoMo.valueToCode(this, 'NUM', true) || '1';
    var argument1 = Blockly.MajorDoMo.valueToCode(this, 'TEXT') || '\'\'';
    code =  'substr('+ argument1 +', 0, ' + argument0 + ')';
  } else {
    var argument0 = Blockly.MajorDoMo.valueToCode(this, 'NUM') || '1';
    var argument1 = Blockly.MajorDoMo.valueToCode(this, 'TEXT', true) || '\'\'';
    var tempVar = Blockly.MajorDoMo.variableDB_.getDistinctName('temp_text',
        Blockly.Variables.NAME_TYPE);
    //Blockly.MajorDoMo.definitions_['variables'] += '\nvar ' + tempVar + ';';
    code = 'substr('+argument1+',  -' + argument0 + ')';
  }
  return code;
};

Blockly.MajorDoMo.text_indexOf = function(opt_dropParens) {
  // Search the text for a substring.
  var operator = this.getTitleText('END') == this.MSG_FIRST ? 'strpos' : 'strpos';
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'FIND') || '\'\'';
  var argument1 = Blockly.MajorDoMo.valueToCode(this, 'VALUE') || '\'\'';
  var code = operator + '(' + argument1 + ', ' +  argument0 + ')';
  if (!opt_dropParens) {
    code = '(' + code + ')';
  }
  return code;
};

Blockly.MajorDoMo.text_charAt = function() {
  // Get letter at index.
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'AT', true) || '1';
  var argument1 = Blockly.MajorDoMo.valueToCode(this, 'VALUE') || '';
  // Blockly uses one-based indicies.
  if (argument0.match(/^\d+$/)) {
    // If the index is a naked number, decrement it right now.
    argument0 = parseInt(argument0, 10);
  } else {
    // If the index is dynamic, decrement it in code.
    argument0 += '';
  }
  return 'substr('+argument1 + ', ' + argument0 + ', 1)';
};

Blockly.MajorDoMo.text_changeCase = function() {
  // Change capitalization.
  var operator;
  switch (this.getInputLabel('TEXT')) {
    case this.MSG_UPPERCASE:
      operator = 'strtoupper';
      break;
    case this.MSG_LOWERCASE:
      operator = 'strtolower';
      break;
    case this.MSG_TITLECASE:
      operator = null;
      break;
    default:
      throw 'Unknown operator.';
  }

  var code;
  if (operator) {
    // Upper and lower case are functions built into JavaScript.
    var argument0 = Blockly.MajorDoMo.valueToCode(this, 'TEXT') || '\'\'';
    code = operator + '('+ argument0 + ')';
  } else {
    if (!Blockly.MajorDoMo.definitions_['text_toTitleCase']) {
      // Title case is not a native JavaScript function.  Define one.
      var functionName = Blockly.MajorDoMo.variableDB_.getDistinctName('text_toTitleCase',
          Blockly.Generator.NAME_TYPE);
      Blockly.MajorDoMo.text_changeCase.toTitleCase = functionName;
      var func = [];
      func.push('function ' + functionName + '(str) {');
      func.push('  return str.replace(/\\w\\S*/g,');
      func.push('      function(txt) {return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});');
      func.push('}');
      Blockly.MajorDoMo.definitions_['text_toTitleCase'] = func.join('\n');
    }
    var argument0 = Blockly.MajorDoMo.valueToCode(this, 'TEXT', true) || '\'\'';
    code = 'ucwords(' + argument0 + ')';
  }
  return code;
};

Blockly.MajorDoMo.text_trim = function() {
  // Trim spaces.
  var operator;
  switch (this.getTitleText('MODE')) {
    case this.MSG_LEFT:
      operator = 'ltrim';
      break;
    case this.MSG_RIGHT:
      operator = 'rtrim';
      break;
    case this.MSG_BOTH:
      operator = 'trim';
      break;
    default:
      throw 'Unknown operator.';
  }

  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'TEXT') || '\'\'';
  return operator + '(' + argument0 + ')';
};

Blockly.MajorDoMo.text_print = function() {
  // Print statement.
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'TEXT', true) || '\'\'';
  return 'echo ' + argument0 + ';\n';
};

Blockly.MajorDoMo.text_say = function() {
  // Print statement.
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'TEXT', true) || '\'\'';
  return 'say(' + argument0 + ');\n';
};
