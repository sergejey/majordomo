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
 * @fileoverview Generating JavaScript for math blocks.
 * @author fraser@google.com (Neil Fraser)
 * Due to the frequency of long strings, the 80-column wrap rule need not apply
 * to language files.
 */

Blockly.MajorDoMo = Blockly.Generator.get('MajorDoMo');

Blockly.MajorDoMo.math_number = function() {
  // Numeric value.
  return window.parseFloat(this.getTitleText('NUM'));
};

Blockly.MajorDoMo.math_arithmetic = function(opt_dropParens) {
  // Basic arithmetic operators, and power.
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'A') || '0';
  var argument1 = Blockly.MajorDoMo.valueToCode(this, 'B') || '0';
  var code;

  if (this.getInputLabel('B') == this.MSG_POW) {
    code = 'Math.pow(' + argument0 + ', ' + argument1 + ')';
  } else {
    var map = {};
    map[this.MSG_ADD] = '+';
    map[this.MSG_MINUS] = '-';
    map[this.MSG_MULTIPLY] = '*';
    map[this.MSG_DIVIDE] = '/';
    var operator = map[this.getInputLabel('B')];
    code = argument0 + ' ' + operator + ' ' + argument1;
    if (!opt_dropParens) {
      code = '(' + code + ')';
    }
  }
  return code;
};

Blockly.MajorDoMo.math_change = function() {
  // Add to a variable in place.
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'DELTA') || '0';
  var varName = Blockly.MajorDoMo.variableDB_.getName(this.getTitleText('VAR'),
      Blockly.Variables.NAME_TYPE);
  return '$'+ varName + ' += ' + argument0 + ';\n';
};

Blockly.MajorDoMo.math_single = function(opt_dropParens) {
  // Math operators with single operand.
  var argNaked = Blockly.MajorDoMo.valueToCode(this, 'NUM', true) || '0';
  var argParen = Blockly.MajorDoMo.valueToCode(this, 'NUM', false) || '0';
  var operator = this.getInputLabel('NUM');
  var code;
  // First, handle cases which generate values that don't need parentheses wrapping the code.
  switch (operator) {
    case this.MSG_ABS:
      code = 'abs(' + argNaked + ')';
      break;
    case this.MSG_ROOT:
      code = 'sqrt(' + argNaked + ')';
      break;
    case this.MSG_LN:
      code = 'log(' + argNaked + ')';
      break;
    case this.MSG_EXP:
      code = 'exp(' + argNaked + ')';
      break;
    case this.MSG_10POW:
      code = 'pow(10,' + argNaked + ')';
      break;
    case this.MSG_ROUND:
      code = 'round(' + argNaked + ')';
      break;
    case this.MSG_ROUNDUP:
      code = 'ceil(' + argNaked + ')';
      break;
    case this.MSG_ROUNDDOWN:
      code = 'floor(' + argNaked + ')';
      break;
    case this.MSG_SIN:
      code = 'sin(' + argParen + ' / 180 * pi())';
      break;
    case this.MSG_COS:
      code = 'cos(' + argParen + ' / 180 * pi())';
      break;
    case this.MSG_TAN:
      code = 'tan(' + argParen + ' / 180 * pi())';
      break;
  }
  if (code) {
    return code;
  }
  // Second, handle cases which generate values that may need parentheses wrapping the code.
  switch (operator) {
    case this.MSG_NEG:
      code = '-' + argParen;
      break;
    case this.MSG_LOG10:
      code = 'log(' + argNaked + ') / log(10)';
      break;
    case this.MSG_ASIN:
      code = 'asin(' + argNaked + ') / pi() * 180';
      break;
    case this.MSG_ACOS:
      code = 'acos(' + argNaked + ') / pi() * 180';
      break;
    case this.MSG_ATAN:
      code = 'atan(' + argNaked + ') / pi() * 180';
      break;
    default:
      throw 'Unknown math operator.';
  }
  if (!opt_dropParens) {
    code = '(' + code + ')';
  }
  return code;
};

// Rounding functions have a single operand.
Blockly.MajorDoMo.math_round = Blockly.MajorDoMo.math_single;
// Trigonometry functions have a single operand.
Blockly.MajorDoMo.math_trig = Blockly.MajorDoMo.math_single;

Blockly.MajorDoMo.math_on_list = function() {
  // Rounding functions.
  func = this.getTitleText('OP');
  list = Blockly.MajorDoMo.valueToCode(this, 'LIST', true) || '[]';
  var code;
  switch (func) {
    case this.MSG_SUM:
      code = list + '.reduce(function(x, y) {return x + y;})';
      break;
    case this.MSG_MIN:
      code = 'min(' + list + ')';
      break;
    case this.MSG_MAX:
      code = 'max(' + list + ')';
      break;
    case this.MSG_AVERAGE:
      code = '(' + list + '.reduce(function(x, y) {return x + y;})/' + list +
      '.length)';
      break;
    case this.MSG_MEDIAN:
      if (!Blockly.MajorDoMo.definitions_['math_median']) {
        var functionName = Blockly.MajorDoMo.variableDB_.getDistinctName(
            'math_median', Blockly.Generator.NAME_TYPE);
        Blockly.MajorDoMo.math_on_list.math_median = functionName;
        // Median is not a native JavaScript function.  Define one.
        // May need to handle null.
        // Currently math_median([null,null,1,3]) == 0.5.
        var func = [];
        func.push('function ' + functionName + '($myList) {');
        func.push('  $localList = $myList;');
        func.push('  if (!isset($localList[0])) return 0;');
        func.push('  localList.sort(function(a, b) {return b - a;});');
        func.push('  if (localList.length % 2 == 0) {');
        func.push('    return (localList[localList.length / 2 - 1] + localList[localList.length / 2]) / 2;');
        func.push('  } else {');
        func.push('    return localList[(localList.length - 1) / 2];');
        func.push('  }');
        func.push('}');
        Blockly.MajorDoMo.definitions_['math_median'] = func.join('\n');
      }
      code = Blockly.MajorDoMo.math_on_list.math_median + '(' + list + ')';
      break;
    case this.MSG_MODE:
      if (!Blockly.MajorDoMo.definitions_['math_modes']) {
        var functionName = Blockly.MajorDoMo.variableDB_.getDistinctName(
            'math_modes', Blockly.Generator.NAME_TYPE);
        Blockly.MajorDoMo.math_on_list.math_modes = functionName;
        // As a list of numbers can contain more than one mode,
        // the returned result is provided as an array.
        // Mode of [3, 'x', 'x', 1, 1, 2, '3'] -> ['x', 1].
        var func = [];
        func.push('function ' + functionName + '(values) {');
        func.push('  var modes = [];');
        func.push('  var counts = [];');
        func.push('  var maxCount = 0;');
        func.push('  for (var i = 0; i < values.length; i++) {');
        func.push('    var value = values[i];');
        func.push('    var found = false;');
        func.push('    var thisCount;');
        func.push('    for (var j = 0; j < counts.length; j++) {');
        func.push('      if (counts[j][0] === value) {');
        func.push('        thisCount = ++counts[j][1];');
        func.push('        found = true;');
        func.push('        break;');
        func.push('      }');
        func.push('    }');
        func.push('    if (!found) {');
        func.push('      counts.push([value, 1]);');
        func.push('      thisCount = 1;');
        func.push('    }');
        func.push('    maxCount = Math.max(thisCount, maxCount);');
        func.push('  }');
        func.push('  for (var j = 0; j < counts.length; j++) {');
        func.push('    if (counts[j][1] == maxCount) {');
        func.push('        modes.push(counts[j][0]);');
        func.push('    }');
        func.push('  }');
        func.push('  return modes;');
        func.push('}');
        Blockly.MajorDoMo.definitions_['math_modes'] = func.join('\n');
      }
      code = Blockly.MajorDoMo.math_on_list.math_modes + '(' + list + ')';
      break;
    case this.MSG_STD_DEV:
      if (!Blockly.MajorDoMo.definitions_['math_standard_deviation']) {
        var functionName = Blockly.MajorDoMo.variableDB_.getDistinctName(
            'math_standard_deviation', Blockly.Generator.NAME_TYPE);
        Blockly.MajorDoMo.math_on_list.math_standard_deviation = functionName;
        var func = [];
        func.push('function ' + functionName + '($numbers) {');
        func.push('  $n = count(numbers);');
        func.push('  if (!$n) return 0;');
        func.push('  $mean = $numbers.reduce(function($x, $y) {return $x + $y;}) / $n;');
        func.push('  $variance = 0;');
        func.push('  for ($j = 0; $j < $n; $j++) {');
        func.push('    $variance += pow($numbers[$j] - $mean, 2);');
        func.push('  }');
        func.push('  $variance = $variance / $n;');
        func.push('  $standard_dev = sqrt($variance);');
        func.push('  return $standard_dev;');
        func.push('}');
        Blockly.MajorDoMo.definitions_['math_standard_deviation'] = func.join('\n');
      }
      code = Blockly.MajorDoMo.math_on_list.math_standard_deviation + '(' + list + ')';
      break;
    case this.MSG_RANDOM_ITEM:
      code = list + '[floor(rand() * count(' + list + '))]';
      break;
    default:
      throw 'Unknown operator.';
  }
  return code;
};

Blockly.MajorDoMo.math_constrain = function() {
  // Constrain a number between two limits.
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'VALUE', true) || '0';
  var argument1 = Blockly.MajorDoMo.valueToCode(this, 'LOW', true) || '0';
  var argument2 = Blockly.MajorDoMo.valueToCode(this, 'HIGH', true) || '0';
  return 'min(max(' + argument0 + ', ' + argument1 + '), ' + argument2 + ')';
};

Blockly.MajorDoMo.math_modulo = function(opt_dropParens) {
  // Remainder computation.
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'DIVIDEND') || '0';
  var argument1 = Blockly.MajorDoMo.valueToCode(this, 'DIVISOR') || '0';
  var code = argument0 + ' % ' + argument1;
  if (!opt_dropParens) {
    code = '(' + code + ')';
  }
  return code;
};

Blockly.MajorDoMo.math_random_int = function() {
  // Random integer between [X] and [Y].
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'FROM') || '0';
  var argument1 = Blockly.MajorDoMo.valueToCode(this, 'TO') || '0';
  var rand1 = 'floor(rand() * (' + argument1 + ' - ' + argument0 + ' + 1' + ') + ' + argument0 + ')';
  var rand2 = 'floor(rand() * (' + argument0 + ' - ' + argument1 + ' + 1' + ') + ' + argument1 + ')';
  var code;
  if (argument0.match(/^[\d\.]+$/) && argument1.match(/^[\d\.]+$/)) {
    if (parseFloat(argument0) < parseFloat(argument1)) {
      code = rand1;
    } else {
      code = rand2;
    }
  } else {
    code = argument0 + ' < ' + argument1 + ' ? ' + rand1 + ' : ' + rand2;
  }
  return code;
};

Blockly.MajorDoMo.math_random_float = function() {
  // Random fraction between 0 and 1.
  return 'rand()';
};
