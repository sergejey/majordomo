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
 * @fileoverview Generating PHP for variable blocks.
 * @author fraser@google.com (Neil Fraser)
 */
'use strict';

goog.provide('Blockly.PHP.majordomo_time');

goog.require('Blockly.PHP');



Blockly.PHP['majordomo_timeis'] = function(block) {
  var time = Blockly.PHP.valueToCode(block, 'TIME',Blockly.PHP.ORDER_NONE) || '00:00';
  var code = 'timeIs('+time+')';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['majordomo_timenow'] = function(block) {
  var code = 'timeNow()';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['majordomo_isweekend'] = function(block) {
  var code = 'isWeekEnd()';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['majordomo_isworkday'] = function(block) {
  var code = 'isWeekDay()';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['majordomo_timebefore'] = function(block) {
  var time = Blockly.PHP.valueToCode(block, 'TIME',Blockly.PHP.ORDER_NONE) || '00:00';
  var code = 'timeBefore('+time+')';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['majordomo_timeafter'] = function(block) {
  var time = Blockly.PHP.valueToCode(block, 'TIME',Blockly.PHP.ORDER_NONE) || '00:00';
  var code = 'timeAfter('+time+')';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['majordomo_timebetween'] = function(block) {
  var time1 = Blockly.PHP.valueToCode(block, 'TIME1',Blockly.PHP.ORDER_NONE) || '00:00';
  var time2 = Blockly.PHP.valueToCode(block, 'TIME2',Blockly.PHP.ORDER_NONE) || '00:00';
  var code = 'timeBetween('+time1+', '+time2+')';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
};

Blockly.PHP['majordomo_cleartimeout'] = function(block) {
  var timer = Blockly.PHP.valueToCode(block, 'TIMER',Blockly.PHP.ORDER_NONE) || 'none';
  var code = 'clearTimeOut('+timer+');\n';
  return code;
};

function mjTimerVars_(block, vars) {
  var doBlock = block.getInputTargetBlock('DO');
  if (!doBlock) return;
  var descendants = doBlock.getDescendants();
  for (var i = 0; i < descendants.length; i++) {
    var ids = descendants[i].getVars ? descendants[i].getVars() : [];
    for (var j = 0; j < ids.length; j++) {
      vars[Blockly.PHP.variableDB_.getName(ids[j], Blockly.Variables.NAME_TYPE)] = true;
    }
  }
}

function mjTimerReplaceThis_(code, replacement) {
  var out = '';
  var i = 0;
  while (i < code.length) {
    var ch = code.charAt(i);
    var next = code.charAt(i + 1);
    if (ch === "'" || ch === '"' || ch === '`') {
      var quote = ch;
      out += ch;
      i++;
      while (i < code.length) {
        var c = code.charAt(i++);
        out += c;
        if (c === '\\') {
          if (i < code.length) out += code.charAt(i++);
        } else if (c === quote) {
          break;
        }
      }
    } else if (ch === '#' || (ch === '/' && (next === '/' || next === '*'))) {
      var end = (ch === '#' || next === '/') ? code.indexOf('\n', i) : code.indexOf('*/', i + 2);
      if (end === -1) { out += code.substring(i); break; }
      if (next === '*') end += 2;
      out += code.substring(i, end);
      i = end;
    } else if (code.substr(i, 3) === '<<<') {
      var eol = code.indexOf('\n', i + 3);
      if (eol === -1) { out += code.substring(i); break; }
      var m = code.substring(i, eol).match(/<<<\s*(['"]?)([A-Za-z_][A-Za-z0-9_]*)\1/);
      if (m) {
        var pos = eol + 1;
        while (pos < code.length) {
          var le = code.indexOf('\n', pos);
          if (le === -1) le = code.length;
          var line = code.substring(pos, le).replace(/^\s+/, '').replace(/\s+$/, '');
          pos = le < code.length ? le + 1 : code.length;
          if (line.indexOf(m[2]) === 0 && !/[A-Za-z0-9_]/.test(line.charAt(m[2].length))) break;
        }
        out += code.substring(i, pos);
        i = pos;
      } else {
        out += ch;
        i++;
      }
    } else if (code.substr(i, 7) === '$this->') {
      out += replacement;
      i += 7;
    } else {
      out += ch;
      i++;
    }
  }
  return out;
}

function mjTimerMarker_(code) {
  var lines = code.split('\n');
  for (var i = 0; ; i++) {
    var marker = i ? 'EOT_' + i : 'EOT';
    var re = new RegExp('^\\s*' + marker + '(?![A-Za-z0-9_])');
    var free = true;
    for (var j = 0; j < lines.length; j++) {
      if (re.test(lines[j])) { free = false; break; }
    }
    if (free) return marker;
  }
}

Blockly.PHP['majordomo_settimeout'] = function(block) {
  var timer = Blockly.PHP.valueToCode(block, 'TIMER',Blockly.PHP.ORDER_NONE) || 'none';
  var delay = Blockly.PHP.valueToCode(block, 'DELAY',Blockly.PHP.ORDER_NONE) || '60';
  var branch = Blockly.PHP.statementToCode(block, 'DO');

  var vars = {};
  for (var p = block; p; p = p.getSurroundParent()) {
    if (p.type == 'majordomo_settimeout') mjTimerVars_(p, vars);
  }
  var objVar = '__mjTimerObject';
  for (var n = 0; vars[objVar]; n++) objVar = '__mjTimerObject_' + (n + 1);

  var hasThis = branch.indexOf('$this->') !== -1;
  var thisRef = '$' + objVar + '->';
  branch = mjTimerReplaceThis_(branch, thisRef);

  var nested = false;
  for (var p2 = block.getSurroundParent(); p2; p2 = p2.getSurroundParent()) {
    if (p2.type == 'majordomo_settimeout') { nested = true; break; }
  }

  var prelude = '';
  if (hasThis) {
    var title = nested
      ? 'isset($' + objVar + ') ? $' + objVar + '->object_title : \'\''
      : 'isset($this) ? $this->object_title : \'\'';
    prelude += '\'$' + objVar + ' = getObject(\' . var_export(' + title + ', true) . ");\\n" . ';
  }
  for (var name in vars) {
    prelude += '\'' + name + ' = \' . var_export(isset(' + name + ') ? ' + name + ' : null, true) . ";\\n" . ';
  }

  var marker = mjTimerMarker_(branch);
  var code = '$timerCode=' + prelude + '<<<\'' + marker + '\'\n' + branch + '\n' + marker + ';\n' +
    'setTimeOut(' + timer + ', $timerCode, (int)(' + delay + '));\n';
  return code;
};