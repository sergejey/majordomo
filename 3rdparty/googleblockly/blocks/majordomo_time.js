/**
 * @license
 * Visual Blocks Editor
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
 * @fileoverview Variable blocks for Blockly.
 * @author fraser@google.com (Neil Fraser)
 */
'use strict';

goog.provide('Blockly.Blocks.majordomo_time');

goog.require('Blockly.Blocks');


Blockly.Blocks['majordomo_timeis'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_TIMEIS);
    this.appendValueInput("TIME")
        .setCheck("String");
    this.setInputsInline(true);
    this.setOutput(true);
    this.setTooltip('');
  }
};

Blockly.Blocks['majordomo_timenow'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_TIMENOW);
    this.setOutput(true);
    this.setTooltip('');
  }
};

Blockly.Blocks['majordomo_isweekend'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_ISWEEKEND);
    this.setOutput(true);
    this.setTooltip('');
  }
};

Blockly.Blocks['majordomo_isworkday'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_ISWORKDAY);
    this.setOutput(true);
    this.setTooltip('');
  }
};

Blockly.Blocks['majordomo_timebefore'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_TIMEBEFORE);
    this.appendValueInput("TIME")
        .setCheck("String");
    this.setInputsInline(true);
    this.setOutput(true);
    this.setTooltip('');
  }
};

Blockly.Blocks['majordomo_timeafter'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_TIMEAFTER);
    this.appendValueInput("TIME")
        .setCheck("String");
    this.setInputsInline(true);
    this.setOutput(true);
    this.setTooltip('');
  }
};

Blockly.Blocks['majordomo_timebetween'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_TIMEBETWEEN);
    this.appendValueInput("TIME1")
        .setCheck("String");
    this.appendDummyInput()
        .appendField(' - ');
    this.appendValueInput("TIME2")
        .setCheck("String");
    this.setInputsInline(true);
    this.setOutput(true);
    this.setTooltip('');
  }
};

Blockly.Blocks['majordomo_cleartimeout'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_CLEARTIMEOUT);
    this.appendValueInput("TIMER")
        .setCheck("String");
    this.setInputsInline(true);
    this.setOutput(false);

    this.setPreviousStatement(true);
    this.setNextStatement(true);

    this.setTooltip('');
  }
};

Blockly.Blocks['majordomo_settimeout'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_SETTIMEOUT);
    this.appendValueInput("TIMER")
        .setCheck("String");
    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_SETTIMEOUTDELAY);
    this.appendValueInput("DELAY")
        .setCheck("String");
    this.appendStatementInput('DO')
        .appendField(Blockly.Msg.MAJORDOMO_SETTIMEOUTOPERATIONS);

    this.setInputsInline(true);
    this.setOutput(false);

    this.setPreviousStatement(true);
    this.setNextStatement(true);

    this.setTooltip('');
  }
};


//setTimeOut
//clearTimeOut