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
 * @fileoverview Generating PHP for math blocks.
 * @author q.neutron@gmail.com (Quynh Neutron)
 */
'use strict';

goog.provide('Blockly.PHP.math');

goog.require('Blockly.PHP');


Blockly.PHP['math_number'] = function(block) {
  // Numeric value.
  var code = parseFloat(block.getFieldValue('NUM'));
  return [code, Blockly.PHP.ORDER_ATOMIC];
};

Blockly.PHP['math_arithmetic'] = function(block) {
  // Basic arithmetic operators, and power.
  var OPERATORS = {
    'ADD': [' + ', Blockly.PHP.ORDER_ADDITION],
    'MINUS': [' - ', Blockly.PHP.ORDER_SUBTRACTION],
    'MULTIPLY': [' * ', Blockly.PHP.ORDER_MULTIPLICATION],
    'DIVIDE': [' / ', Blockly.PHP.ORDER_DIVISION],
    'POWER': [null, Blockly.PHP.ORDER_COMMA]  // Handle power separately.
  };
  var tuple = OPERATORS[block.getFieldValue('OP')];
  var operator = tuple[0];
  var order = tuple[1];
  var argument0 = Blockly.PHP.valueToCode(block, 'A', order) || '0';
  var argument1 = Blockly.PHP.valueToCode(block, 'B', order) || '0';
  var code;
  // Power in PHP requires a special case since it has no operator.
  if (!operator) {
    code = 'pow(' + argument0 + ', ' + argument1 + ')';
    return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
  }
  code = argument0 + operator + argument1;
  return [code, order];
};

Blockly.PHP['math_single'] = function(block) {
  // Math operators with single operand.
  var operator = block.getFieldValue('OP');
  var code;
  var arg;
  if (operator == 'NEG') {
    // Negation is a special case given its different operator precedence.
    arg = Blockly.PHP.valueToCode(block, 'NUM',
        Blockly.PHP.ORDER_UNARY_NEGATION) || '0';
    if (arg[0] == '-') {
      // --3 is not legal in JS.
      arg = ' ' + arg;
    }
    code = '-' + arg;
    return [code, Blockly.PHP.ORDER_UNARY_NEGATION];
  }
  if (operator == 'SIN' || operator == 'COS' || operator == 'TAN') {
    arg = Blockly.PHP.valueToCode(block, 'NUM',
        Blockly.PHP.ORDER_DIVISION) || '0';
  } else {
    arg = Blockly.PHP.valueToCode(block, 'NUM',
        Blockly.PHP.ORDER_NONE) || '0';
  }
  // First, handle cases which generate values that don't need parentheses
  // wrapping the code.
  switch (operator) {
    case 'ABS':
      code = 'abs(' + arg + ')';
      break;
    case 'ROOT':
      code = 'sqrt(' + arg + ')';
      break;
    case 'LN':
      code = 'log(' + arg + ')';
      break;
    case 'EXP':
      code = 'exp(' + arg + ')';
      break;
    case 'POW10':
      code = 'pow(10,' + arg + ')';
      break;
    case 'ROUND':
      code = 'round(' + arg + ')';
      break;
    case 'ROUNDUP':
      code = 'ceil (' + arg + ')';
      break;
    case 'ROUNDDOWN':
      code = 'floor(' + arg + ')';
      break;
    case 'SIN':
      code = 'sin(' + arg + ' / 180 * M_PI)';
      break;
    case 'COS':
      code = 'cos(' + arg + ' / 180 * M_PI)';
      break;
    case 'TAN':
      code = 'tan(' + arg + ' / 180 * M_PI)';
      break;
  }
  if (code) {
    return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
  }
  // Second, handle cases which generate values that may need parentheses
  // wrapping the code.
  switch (operator) {
    case 'LOG10':
      code = 'log(' + arg + ') / log(10)';
      break;
    case 'ASIN':
      code = 'asin(' + arg + ') / M_PI * 180';
      break;
    case 'ACOS':
      code = 'acos(' + arg + ') / M_PI * 180';
      break;
    case 'ATAN':
      code = 'atan(' + arg + ') / M_PI * 180';
      break;
    default:
      throw 'Unknown math operator: ' + operator;
  }
  return [code, Blockly.PHP.ORDER_DIVISION];
};

Blockly.PHP['math_constant'] = function(block) {
  // Constants: PI, E, the Golden Ratio, sqrt(2), 1/sqrt(2), INFINITY.
  var CONSTANTS = {
    'PI': ['M_PI', Blockly.PHP.ORDER_MEMBER],
    'E': ['M_E', Blockly.PHP.ORDER_MEMBER],
    'GOLDEN_RATIO':
        ['(1 + sqrt(5)) / 2', Blockly.PHP.ORDER_DIVISION],
    'SQRT2': ['M_SQRT2', Blockly.PHP.ORDER_MEMBER],
    'SQRT1_2': ['M_SQRT1_2', Blockly.PHP.ORDER_MEMBER],
    'INFINITY': ['INF', Blockly.PHP.ORDER_ATOMIC]
  };
  return CONSTANTS[block.getFieldValue('CONSTANT')];
};

Blockly.PHP['math_number_property'] = function(block) {
  // Check if a number is even, odd, prime, whole, positive, or negative
  // or if it is divisible by certain number. Returns true or false.
  var number_to_check = Blockly.PHP.valueToCode(block, 'NUMBER_TO_CHECK',
      Blockly.PHP.ORDER_MODULUS) || '0';
  var dropdown_property = block.getFieldValue('PROPERTY');
  var code;
  if (dropdown_property == 'PRIME') {
    // Prime is a special case as it is not a one-liner test.    
    code = 'gmp_prob_prime(' + number_to_check + ') != 0';
    return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
  }
  switch (dropdown_property) {
    case 'EVEN':
      code = number_to_check + ' % 2 == 0';
      break;
    case 'ODD':
      code = number_to_check + ' % 2 == 1';
      break;
    case 'WHOLE':
      code = number_to_check + ' % 1 == 0';
      break;
    case 'POSITIVE':
      code = number_to_check + ' > 0';
      break;
    case 'NEGATIVE':
      code = number_to_check + ' < 0';
      break;
    case 'DIVISIBLE_BY':
      var divisor = Blockly.PHP.valueToCode(block, 'DIVISOR',
          Blockly.PHP.ORDER_MODULUS) || '0';
      code = number_to_check + ' % ' + divisor + ' == 0';
      break;
  }
  return [code, Blockly.PHP.ORDER_EQUALITY];
};

Blockly.PHP['math_change'] = function(block) {
  // Add to a variable in place.
  var argument0 = Blockly.PHP.valueToCode(block, 'DELTA',
      Blockly.PHP.ORDER_ADDITION) || '0';
  var varName = Blockly.PHP.variableDB_.getName(
      block.getFieldValue('VAR'), Blockly.Variables.NAME_TYPE);
  return '$'+varName+' += '+argument0 + ';\n'; 
};

// Rounding functions have a single operand.
Blockly.PHP['math_round'] = Blockly.PHP['math_single'];
// Trigonometry functions have a single operand.
Blockly.PHP['math_trig'] = Blockly.PHP['math_single'];

Blockly.PHP['math_on_list'] = function(block) {
  // Math functions for lists.
  var func = block.getFieldValue('OP');
  var list, code; 
  switch (func) {
    case 'SUM':
      list = Blockly.PHP.valueToCode(block, 'LIST', Blockly.PHP.ORDER_MEMBER) || '[]';
      code = 'array_sum('+list +')';
      break;
    case 'MIN':
      list = Blockly.PHP.valueToCode(block, 'LIST',
          Blockly.PHP.ORDER_COMMA) || '[]';
      code = 'min(' + list + ')';
      break;
    case 'MAX':
      list = Blockly.PHP.valueToCode(block, 'LIST',
          Blockly.PHP.ORDER_COMMA) || '[]';
      code = 'max(' + list + ')';
      break;
    case 'AVERAGE':
      // math_median([null,null,1,3]) == 2.0.      
      list = Blockly.PHP.valueToCode(block, 'LIST', Blockly.PHP.ORDER_NONE) || '[]';
      code = 'array_sum('+list+') / count('+list+')';
      break;
    case 'MEDIAN':
      // math_median([null,null,1,3]) == 2.0.      
      list = Blockly.PHP.valueToCode(block, 'LIST', Blockly.PHP.ORDER_NONE) || '[]';
	  var functionName = Blockly.PHP.provideFunction_(
	      'math_median',
		  [ 'function '+Blockly.PHP.FUNCTION_NAME_PLACEHOLDER_+' ($myList) {',
		    '  $localList = $myList;',
			'  if (!isset($localList[0])) return 0;',
			'  sort($localList);',
			'  if (count($localList) % 2 == 0) {',
			'    return ($localList[count($localList) / 2 - 1] + $localList[count($localList) / 2]) / 2;',
			'  } else {',
			'    return $localList[(count($localList) - 1) / 2];',
			'  }',
			'}'
		  ]);	  
      code = functionName + '(' + list + ')';
      break;
    case 'MODE':
      // As a list of numbers can contain more than one mode,
      // the returned result is provided as an array.
      // Mode of [3, 'x', 'x', 1, 1, 2, '3'] -> ['x', 1].
      var functionName = Blockly.PHP.provideFunction_(
          'math_modes',
          [ 'function '+Blockly.PHP.FUNCTION_NAME_PLACEHOLDER_+'($values) { ',
		    '  $modes = array();',
			'  $counts = array();',
			'  $maxCount = 0;',
			'  for ($i = 0; $i < count($values); $i++) {',
			'    $value = $values[$i];',
			'    $found = false;',
			'    $thisCount = 0;',
			'    for ($j = 0; $j < count($counts); $j++) {',
			'      if ($counts[$j][0] === $value) {',
			'        $thisCount = ++$counts[$j][1];',
			'        $found = true;',
			'        break;',
			'      }',
			'    }',
			'    if (!$found) {',
			'      array_push($counts, array($value, 1));',
			'      $thisCount = 1;',
			'    }',
			'    $maxCount = max($thisCount, $maxCount);',
			'  }',
			'  for ($j = 0; $j < count($counts); $j++) {',
			'    if ($counts[$j][1] == $maxCount) {',
			'        array_push($modes, $counts[$j][0]);',
			'    }',
			'  }',
			'  return $modes;',
			'}']);
      list = Blockly.PHP.valueToCode(block, 'LIST',
          Blockly.PHP.ORDER_NONE) || '[]';
      code = functionName + '(' + list + ')';
      break;
    case 'STD_DEV':      
      list = Blockly.PHP.valueToCode(block, 'LIST', Blockly.PHP.ORDER_NONE) || '[]';  
	  var functionName = Blockly.PHP.provideFunction_(
	      'math_standard_deviation',
		  [ 'function '+Blockly.PHP.FUNCTION_NAME_PLACEHOLDER_+' ($numbers) {',
		    '  $n = count(numbers);',
			'  if (!$n) return 0;',
			'  $mean = array_sum($numbers)/$n;',			
			'  $variance = 0;',
			'  for ($j = 0; $j < $n; $j++) {',
			'    $variance += pow($numbers[$j] - $mean, 2);',
			'  }',
			'  $variance = $variance / $n;',
			'  $standard_dev = sqrt($variance);',
			'  return $standard_dev;',			
			'}'
		  ]);		  
      code = functionName + '(' + list + ')';
      break;
    case 'RANDOM':      
      list = Blockly.PHP.valueToCode(block, 'LIST', Blockly.PHP.ORDER_NONE) || '[]';
      code = list+'[array_rand(' + list + ')]';
      break;
    default:
      throw 'Unknown operator: ' + func;
  }  
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['math_modulo'] = function(block) {
  // Remainder computation.
  var argument0 = Blockly.PHP.valueToCode(block, 'DIVIDEND',
      Blockly.PHP.ORDER_MODULUS) || '0';
  var argument1 = Blockly.PHP.valueToCode(block, 'DIVISOR',
      Blockly.PHP.ORDER_MODULUS) || '0';
  var code = argument0 + ' % ' + argument1;
  return [code, Blockly.PHP.ORDER_MODULUS];
};

Blockly.PHP['math_constrain'] = function(block) {
  // Constrain a number between two limits.
  var argument0 = Blockly.PHP.valueToCode(block, 'VALUE',
      Blockly.PHP.ORDER_COMMA) || '0';
  var argument1 = Blockly.PHP.valueToCode(block, 'LOW',
      Blockly.PHP.ORDER_COMMA) || '0';
  var argument2 = Blockly.PHP.valueToCode(block, 'HIGH',
      Blockly.PHP.ORDER_COMMA) || 'Infinity';
  var code = 'min(max(' + argument0 + ', ' + argument1 + '), ' +
      argument2 + ')';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['math_random_int'] = function(block) {
  // Random integer between [X] and [Y].
  var argument0 = Blockly.PHP.valueToCode(block, 'FROM',
      Blockly.PHP.ORDER_COMMA) || '0';
  var argument1 = Blockly.PHP.valueToCode(block, 'TO',
      Blockly.PHP.ORDER_COMMA) || '0';
  var code = 'rand(' + argument0 + ', ' + argument1 + ')';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['math_random_float'] = function(block) {
  // Random fraction between 0 and 1.
  return ['rand()/getrandmax()', Blockly.PHP.ORDER_FUNCTION_CALL];
};
