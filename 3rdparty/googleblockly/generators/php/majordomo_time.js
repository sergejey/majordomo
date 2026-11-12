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

Blockly.PHP['majordomo_settimeout'] = function(block) {
  var timer = Blockly.PHP.valueToCode(block, 'TIMER',Blockly.PHP.ORDER_NONE) || 'none';
  var delay = Blockly.PHP.valueToCode(block, 'DELAY',Blockly.PHP.ORDER_NONE) || '60';
  var branch = Blockly.PHP.statementToCode(block, 'DO');
  //branch=branch.replace(new RegExp('\n', 'g'), '');

  // Код таймера выполняется позже, отдельным заданием (job) через eval(): там нет $this
  // и локальных переменных метода. Раньше код вставлялся в heredoc без кавычек (<<<EOT),
  // поэтому PHP подставлял переменные в момент СОЗДАНИЯ таймера: без кавычек
  // (строки превращались в константы, '300000' в число .300000…), пустыми для
  // переменных цикла (foreach (... as ) — ParseError), а $this->setProperty(...)
  // превращался в ('...') и задание не выполнялось.
  // Теперь код вставляется как nowdoc (<<<'EOT') — ровно так, как его показывает Blockly.

  // Свой маркер для вложенных таймеров, чтобы внутренний EOT не закрывал внешний.
  var depth = 0;
  for (var p = block.getSurroundParent(); p; p = p.getSurroundParent()) {
    if (p.type == 'majordomo_settimeout') depth++;
  }
  var marker = depth ? 'EOT_' + depth : 'EOT';

  var prelude = '';

  // Блоки «текущий объект» ($this->...) внутри таймера: объект берётся по имени,
  // которое запоминается в момент создания таймера.
  if (/\$this->/.test(branch)) {
    branch = branch.replace(/\$this->/g, '$timerObject->');
    prelude += '\'$timerObject = getObject(\' . var_export(isset($timerObject) ? $timerObject->object_title : ' +
      '(isset($this->object_title) ? $this->object_title : \'\'), true) . ");\\n" . ';
  }

  // Переменные Blockly, используемые внутри таймера, сохраняют значение на момент
  // создания таймера (как и задумывалось раньше), но теперь корректно экранированы.
  var doBlock = block.getInputTargetBlock('DO');
  if (doBlock) {
    var seen = {};
    var descendants = doBlock.getDescendants();
    for (var i = 0; i < descendants.length; i++) {
      var vars = descendants[i].getVars ? descendants[i].getVars() : [];
      for (var j = 0; j < vars.length; j++) {
        var name = Blockly.PHP.variableDB_.getName(vars[j], Blockly.Variables.NAME_TYPE);
        if (seen[name]) continue;
        seen[name] = true;
        prelude += '\'' + name + ' = \' . var_export(isset(' + name + ') ? ' + name + ' : null, true) . ";\\n" . ';
      }
    }
  }

  var code = '$timerCode=' + prelude + '<<<\'' + marker + '\'\n' + branch + '\n' + marker + ';\n' +
    'setTimeOut(' + timer + ', $timerCode, (int)(' + delay + '));\n';
  return code;
};