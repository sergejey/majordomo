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

goog.provide('Blockly.Blocks.majordomo_objects');

goog.require('Blockly.Blocks');


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

Blockly.Blocks['majordomo_getcurrent'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_GETCURRENT);
    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_PROPERTY);
    this.appendValueInput("PROPERTY")
        .setCheck("String");

    this.setInputsInline(true);

    this.setOutput(true);
    this.setTooltip('');
  }
};


Blockly.Blocks['majordomo_setcurrent'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {

    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_SETCURRENT);
    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_PROPERTY);
    this.appendValueInput("PROPERTY")
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


Blockly.Blocks['majordomo_callmethod'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);


    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_RUNMETHOD);
    this.appendValueInput("METHOD")
        .setCheck("String");
    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_OBJECT);
    this.appendValueInput("OBJECT")
        .setCheck("String");

    this.setInputsInline(true);
    this.setOutput(false);
    this.setPreviousStatement(true);
    this.setNextStatement(true);
    // Assign 'this' to a variable for use in the tooltip closure below.
    var thisBlock = this;
  }
};

Blockly.Blocks['majordomo_callmethodwithparams'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_RUNMETHOD);
    this.appendValueInput("METHOD")
        .setCheck("String");
    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_OBJECT);
    this.appendValueInput("OBJECT")
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

Blockly.Blocks['majordomo_callmethodcurrent'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);


    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_RUNMETHODCURRENT);
    this.appendValueInput("METHOD")
        .setCheck("String");

    this.setInputsInline(true);
    this.setOutput(false);
    this.setPreviousStatement(true);
    this.setNextStatement(true);
    // Assign 'this' to a variable for use in the tooltip closure below.
    var thisBlock = this;
  }
};

Blockly.Blocks['majordomo_callmethodwithparamscurrent'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_RUNMETHODCURRENT);
    this.appendValueInput("METHOD")
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

Blockly.Blocks['majordomo_keyvalue'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_KEY);
    this.appendValueInput("KEY")
        .setCheck("String");
    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_VALUE);
    this.appendValueInput("VALUE");
    this.setInputsInline(true);
    this.setOutput(true, "Array");

  }
};

Blockly.Blocks['majordomo_paramvalue'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_PARAM);
    this.appendValueInput("KEY")
        .setCheck("String");
    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_VALUE);
    this.appendValueInput("VALUE");
    this.setInputsInline(true);
    this.setOutput(true, "Array");

  }
};

Blockly.Blocks['majordomo_getobjects'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField(Blockly.Msg.MAJORDOMO_GETOBJECTS);
    this.appendValueInput("CLASS")
        .setCheck("String");
    this.setInputsInline(true);
    this.setOutput(true, "Array");

  }
};


