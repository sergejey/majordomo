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

goog.provide('Blockly.Blocks.majordomo_test');

goog.require('Blockly.Blocks');

Blockly.Blocks['test_temp'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField('Temberature (Block 1)');
    this.setOutput(true);
    this.setTooltip('');
  }
};

Blockly.Blocks['test_motion'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField('Motion detected (Block 2)');
    this.setOutput(true);
    this.setTooltip('');
  }
};

Blockly.Blocks['test_level'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField('Light level (Block 3)');
    this.setOutput(true);
    this.setTooltip('');
  }
};

Blockly.Blocks['test_turnOff'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField('Turn off Block 4');
    this.setOutput(false);

    this.setPreviousStatement(true);
    this.setNextStatement(true);

    this.setTooltip('');
  }
};

Blockly.Blocks['test_turnOn'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField('Turn on Block 4');
    this.setOutput(false);

    this.setPreviousStatement(true);
    this.setNextStatement(true);

    this.setTooltip('');
  }
};

Blockly.Blocks['test_color'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
	
this.COLOR_OPTIONS =
        [['Red', 'RED'],
         ['Green', 'GREEN'],
         ['Blue', 'BLUE']];	
	
    var thisBlock = this;
    this.setColour(220);

    this.appendDummyInput()
        .appendField('Set color of Block 5');
		
    var menu = new Blockly.FieldDropdown(this.COLOR_OPTIONS, function(value) {});		
	
	this.appendDummyInput('AT');
	this.getInput('AT').appendField(menu, 'COLOR');
	
    //this.appendField(menu, 'COLOR');		
		
    this.setOutput(false);

    this.setPreviousStatement(true);
    this.setNextStatement(true);

    this.setTooltip('');
  }
};


//setTimeOut
//clearTimeOut