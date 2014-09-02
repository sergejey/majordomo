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

goog.provide('Blockly.Blocks.majordomo');

goog.require('Blockly.Blocks');

//say

Blockly.Blocks['majordomo_say'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this
     .appendValueInput('TEXT')
     .appendField(Blockly.Msg.MAJORDOMO_SAY)
     .appendField(Blockly.Msg.MAJORDOMO_PRIORITY)
     .appendField(new Blockly.FieldTextInput('2',Blockly.FieldTextInput.nonnegativeIntegerValidator), 'NUMBER');
    this.setOutput(false);
    this.setPreviousStatement(true);
    this.setNextStatement(true);
    // Assign 'this' to a variable for use in the tooltip closure below.
    var thisBlock = this;
  }
};

//say

Blockly.Blocks['majordomo_say_simple'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this
     .appendValueInput('TEXT')
     .appendField(Blockly.Msg.MAJORDOMO_SAY);
    this.setOutput(false);
    this.setPreviousStatement(true);
    this.setNextStatement(true);
    // Assign 'this' to a variable for use in the tooltip closure below.
    var thisBlock = this;
  }
};

Blockly.Blocks['majordomo_runscript'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this
     .appendValueInput('TEXT')
     .appendField(Blockly.Msg.MAJORDOMO_RUNSCRIPT);
    this.setOutput(false);
    this.setPreviousStatement(true);
    this.setNextStatement(true);
    // Assign 'this' to a variable for use in the tooltip closure below.
    var thisBlock = this;
  }
};

Blockly.Blocks['majordomo_getglobal'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_GETGLOBAL);
    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_PROPERTY);
    this.appendValueInput("PROPERTY")
        .setCheck("String");

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_OBJECT);
    this.appendValueInput("OBJECT")
        .setCheck("String");
    this.setInputsInline(true);

    this.setOutput(true);
    this.setTooltip('');
  }
};


Blockly.Blocks['majordomo_setglobal'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {

    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_SETGLOBAL);
    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_PROPERTY);
    this.appendValueInput("PROPERTY")
        .setCheck("String");
    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_OBJECT);
    this.appendValueInput("OBJECT")
        .setCheck("String");
    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_SETTO);
    this.appendValueInput("VALUE")
        .setCheck("String");

    this.setInputsInline(true);
    this.setPreviousStatement(true);
    this.setNextStatement(true);
    this.setTooltip('');
  }
};


//runScript with params
//callMethod
//getURL
//getURL (content)
//getRandomLine (from file)
//playSound
//timeNow
//isWeekEnd
//isWorkDay
//timeIs
//timeBefore
//timeAfter
//timeBetween
//clearTimeOut
//setTimeOut