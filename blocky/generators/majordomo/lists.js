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
 * @fileoverview Generating JavaScript for list blocks.
 * @author fraser@google.com (Neil Fraser)
 * Due to the frequency of long strings, the 80-column wrap rule need not apply
 * to language files.
 */

Blockly.JavaScript = Blockly.Generator.get('MajorDoMo');

Blockly.MajorDoMo.lists_create_empty = function() {
  // Create an empty list.
  return 'array()';
};

Blockly.MajorDoMo.lists_create_with = function() {
  // Create a list with any number of elements of any type.
  var code = new Array(this.itemCount_);
  for (n = 0; n < this.itemCount_; n++) {
    code[n] = Blockly.MajorDoMo.valueToCode(this, 'ADD' + n, true) || 'null';
  }
  return 'array(' + code.join(', ') + ')';
};

Blockly.MajorDoMo.lists_repeat = function() {
  // Create a list with one element repeated.
  if (!Blockly.MajorDoMo.definitions_['lists_repeat']) {
    // Function copied from Closure's goog.array.repeat.
    var functionName = Blockly.MajorDoMo.variableDB_.getDistinctName('lists_repeat',
        Blockly.Generator.NAME_TYPE);
    Blockly.MajorDoMo.lists_repeat.repeat = functionName;
    var func = [];
    func.push('function ' + functionName + '(value, n) {');
    func.push('  $res = array();');
    func.push('  for ($i = 0; $i < $n; $i++) {');
    func.push('    $res[] = $value;');
    func.push('  }');
    func.push('  return $res;');
    func.push('}');
    Blockly.MajorDoMo.definitions_['lists_repeat'] = func.join('\n');
  }
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'ITEM', true) || 'null';
  var argument1 = Blockly.MajorDoMo.valueToCode(this, 'NUM', true) || '0';
  return Blockly.MajorDoMo.lists_repeat.repeat + '(' + argument0 + ', ' + argument1 + ')';
};

Blockly.MajorDoMo.lists_length = function(opt_dropParens) {
  // Testing the length of a list is the same as for a string.
  //return Blockly.MajorDoMo.text_length.call(this, opt_dropParens);
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'VALUE') || '\'\'';
  return 'count('+ argument0 + ')';

};

Blockly.MajorDoMo.lists_isEmpty = function(opt_dropParens) {
  // Testing a list for being empty is the same as for a string.
  //return Blockly.MajorDoMo.text_isEmpty.call(this, opt_dropParens);
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'VALUE') || '\'\'';
  return 'empty('+ argument0 + ')';

};

Blockly.MajorDoMo.lists_indexOf = function(opt_dropParens) {
  // Searching a list for a value is the same as search for a substring.
  //return Blockly.MajorDoMo.text_indexOf.call(this, opt_dropParens);
  var operator = this.getTitleText('END') == this.MSG_FIRST ? 'indexOf' : 'lastIndexOf';
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'FIND') || '\'\'';
  var argument1 = Blockly.MajorDoMo.valueToCode(this, 'VALUE') || '\'\'';
  return 'array_search('+ argument0 + ','+ argument1 +')';

};

Blockly.MajorDoMo.lists_getIndex = function(opt_dropParens) {
  // Indexing into a list is the same as indexing into a string.
  // Get letter at index.
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'AT', true) || '1';
  var argument1 = Blockly.MajorDoMo.valueToCode(this, 'VALUE') || '[]';
  // Blockly uses one-based indicies.
  if (argument0.match(/^\d+$/)) {
    // If the index is a naked number, decrement it right now.
    argument0 = parseInt(argument0, 10);
  } else {
    // If the index is dynamic, decrement it in code.
    argument0 += '';
  }
  return argument1 + '[' + argument0 + ']';
};

Blockly.MajorDoMo.lists_setIndex = function() {
  // Set element at index.
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'AT', true) || '1';
  var argument1 = Blockly.MajorDoMo.valueToCode(this, 'LIST') || '[]';
  var argument2 = Blockly.MajorDoMo.valueToCode(this, 'TO', true) || 'null';
  // Blockly uses one-based indicies.
  if (argument0.match(/^\d+$/)) {
    // If the index is a naked number, decrement it right now.
    argument0 = parseInt(argument0, 10);
  } else {
    // If the index is dynamic, decrement it in code.
    argument0 += '';
  }
  return ''+ argument1 + '[' + argument0 + '] = ' + argument2 + ';\n';
};
