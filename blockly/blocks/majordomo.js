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


Blockly.Blocks['majordomo_color'] = {
  init: function() {
    var thisBlock = this;
    this.setColour(220);

    this.appendField(Blockly.Msg.MAJORDOMO_COLOR);
    this.setOutput(true);
    this.setPreviousStatement(false);
    this.setNextStatement(false);
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

Blockly.Blocks['majordomo_phpcode'] = {
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
     .appendField(Blockly.Msg.MAJORDOMO_PHPCODE);
    this.setOutput(false);
    this.setPreviousStatement(true);
    this.setNextStatement(true);
    // Assign 'this' to a variable for use in the tooltip closure below.
    var thisBlock = this;
  }
};


Blockly.Blocks['majordomo_phpexpression'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_PHPEXPRESSION);

    this.appendValueInput("TEXT").setCheck("String");

    this.setInputsInline(true);
    this.setOutput(true);
    var thisBlock = this;

  }
};

Blockly.Blocks['majordomo_getrandomline'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_GETRANDOMLINE);
    this.appendValueInput("TEXT")
        .setCheck("String");
    this.setInputsInline(true);
    this.setOutput(true);
    var thisBlock = this;

  }
};

Blockly.Blocks['majordomo_geturl'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_GETURL);
    this.appendValueInput("TEXT")
        .setCheck("String");
    this.setInputsInline(true);
    this.setOutput(true);
    var thisBlock = this;

  }
};

Blockly.Blocks['majordomo_callurl'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_CALLURL);
    this.appendValueInput("TEXT")
        .setCheck("String");

    this.setInputsInline(true);
    this.setPreviousStatement(true);
    this.setNextStatement(true);


  }
};

Blockly.Blocks['majordomo_playsound'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_PLAYSOUND);
    this.appendValueInput("TEXT")
        .setCheck("String");

    this.setInputsInline(true);
    this.setPreviousStatement(true);
    this.setNextStatement(true);


  }
};


Blockly.Blocks['majordomo_runscriptwithparams'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_RUNSCRIPT);
    this.appendValueInput("TEXT")
        .setCheck("String");
    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_PARAMS);
    this.appendValueInput("PARAMS")
        .setCheck("Array");
    this.setInputsInline(true);
    this.setPreviousStatement(true);
    this.setNextStatement(true);

  }
};