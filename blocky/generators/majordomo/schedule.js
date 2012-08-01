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
 * @fileoverview Generating PHP(MajorDoMo scheduling functions) for text blocks.
 * @author jey@tut.by (Serge Dzheigalo)
 */

Blockly.MajorDoMo = Blockly.Generator.get('MajorDoMo');


Blockly.MajorDoMo.majordomo_cleartimeout = function() {
  // Print statement.
  var argument0 = Blockly.MajorDoMo.quote_(this.getTitleText('TEXT'));
  return 'clearTimeOut(' + argument0 + ');\n';
};

Blockly.MajorDoMo.majordomo_settimeout = function() {
  // Print statement.
  var argument0 = Blockly.MajorDoMo.quote_(this.getTitleText('A'));
  var argument1 = (this.getTitleText('B')) || '0';
  var branch = Blockly.MajorDoMo.statementToCode(this, 'DO0');
  var res='$tCode='+'<<<'+'\'FF\'\n'+branch;
  res=res+'\nFF;\n';
  res=res+'setTimeOut(' + argument0 + ', $tCode, '+argument1+');\n';
  return res;
};