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
 * @fileoverview Generating PHP(MajorDoMo date/time functions) for text blocks.
 * @author jey@tut.by (Serge Dzheigalo)
 */

Blockly.MajorDoMo = Blockly.Generator.get('MajorDoMo');


Blockly.MajorDoMo.majordomo_timenow = function() {
  // Print statement.
  return 'timeNow()';
};

Blockly.MajorDoMo.majordomo_isweekend = function() {
  // Print statement.
  return 'isWeekEnd()';
};

Blockly.MajorDoMo.majordomo_isweekday = function() {
  // Print statement.
  return 'isWeekDay()';

};

Blockly.MajorDoMo.majordomo_timeis = function() {
  // Print statement.
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'VALUE', true) || '0';
  return 'timeIs(' + argument0 + ')';
};

Blockly.MajorDoMo.majordomo_timebefore = function() {
  // Print statement.
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'VALUE', true) || '0';
  return 'timeBefore(' + argument0 + ')';
};

Blockly.MajorDoMo.majordomo_timeafter = function() {
  // Print statement.
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'VALUE', true) || '0';
  return 'timeAfter(' + argument0 + ')';
};

Blockly.MajorDoMo.majordomo_timebetween = function() {
  // Print statement.
  var argument0 = Blockly.MajorDoMo.valueToCode(this, 'A', true) || '0';
  var argument1 = Blockly.MajorDoMo.valueToCode(this, 'B', true) || '0';
  return 'timeBetween(' + argument0 + ', '+ argument1 +')';
};