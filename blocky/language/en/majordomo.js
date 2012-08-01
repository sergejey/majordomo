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
 * @fileoverview Text blocks for Blockly.
 * @author fraser@google.com (Neil Fraser)
 * Due to the frequency of long strings, the 80-column wrap rule need not apply
 * to language files.
 */

if (!Blockly.Language) {
  Blockly.Language = {};
}

Blockly.Language.majordomo_say = {
  // Print statement.
  category: 'MajorDoMo',
  helpUrl: '',
  init: function() {
    this.setColour(160);
    this.appendTitle('say');
    this.appendInput('', Blockly.INPUT_VALUE, 'TEXT');
    this.setPreviousStatement(true);
    this.setNextStatement(true);
    this.setTooltip('Say the specified text, number or other value.');
  }
};

Blockly.Language.majordomo_getglobal = {
  // Print statement.
  category: 'MajorDoMo',
  helpUrl: '',
  init: function() {
    this.setColour(330);
    this.appendTitle('getGlobal');
    this.appendTitle(new Blockly.FieldTextInput(''), 'TEXT');
    this.setOutput(true);
    this.setTooltip('Get value of global variable');
  }
};

Blockly.Language.majordomo_setglobal = {
  // Print statement.
  category: 'MajorDoMo',
  helpUrl: '',
  init: function() {
    this.setColour(330);
    this.appendTitle('setGlobal');
    this.appendTitle(new Blockly.FieldTextInput(''), 'TEXT');
    this.appendInput('', Blockly.INPUT_VALUE, 'VALUE');
    this.setPreviousStatement(true);
    this.setNextStatement(true);
    this.setTooltip('Set value of global variable');
  }
};


Blockly.Language.majordomo_runscript = {
  // Print statement.
  category: 'MajorDoMo',
  helpUrl: '',
  init: function() {
    this.setColour(290);
    this.appendTitle('runScript');
    this.appendTitle(new Blockly.FieldTextInput(''), 'TEXT');
    this.appendInput('', Blockly.INPUT_VALUE, 'VALUE');
    this.setPreviousStatement(true);
    this.setNextStatement(true);
    this.setTooltip('Run specific script');
  }
};

Blockly.Language.majordomo_callmethod = {
  // Print statement.
  category: 'MajorDoMo',
  helpUrl: '',
  init: function() {
    this.setColour(290);
    this.appendTitle('callMethod');
    this.appendTitle(new Blockly.FieldTextInput(''), 'TEXT');
    this.appendInput('', Blockly.INPUT_VALUE, 'VALUE');
    this.setPreviousStatement(true);
    this.setNextStatement(true);
    this.setTooltip('Call method');
  }
};


Blockly.Language.majordomo_callurl = {
  // Print statement.
  category: 'MajorDoMo',
  helpUrl: '',
  init: function() {
    this.setColour(290);
    this.appendTitle('callURL');
    this.appendTitle(new Blockly.FieldTextInput(''), 'TEXT');
    this.setPreviousStatement(true);
    this.setNextStatement(true);
    this.setTooltip('Call HTTP');
  }
};

Blockly.Language.majordomo_openurl = {
  // Print statement.
  category: 'MajorDoMo',
  helpUrl: '',
  init: function() {
    this.setColour(160);
    this.appendTitle('getContentByURL');
    this.appendTitle(new Blockly.FieldTextInput(''), 'TEXT');
    //this.appendInput('', Blockly.INPUT_VALUE, 'TEXT');
    this.setOutput(true);
    //this.setPreviousStatement(true);
    //this.setNextStatement(true);
    this.setTooltip('Open URL');
  }
};

Blockly.Language.majordomo_playsound = {
  // Print statement.
  category: 'MajorDoMo',
  helpUrl: '',
  init: function() {
    this.setColour(330);
    this.appendTitle('playSound');
    this.appendInput('', Blockly.INPUT_VALUE, 'TEXT');
    this.setPreviousStatement(true);
    this.setNextStatement(true);
    this.setTooltip('Play sound file');
  }
};

Blockly.Language.majordomo_getrandomline = {
  // Print statement.
  category: 'MajorDoMo',
  helpUrl: '',
  init: function() {
    this.setColour(160);
    this.appendTitle('getRandomLine');
    this.appendTitle(new Blockly.FieldTextInput(''), 'TEXT');
    //this.appendInput('', Blockly.INPUT_VALUE, 'TEXT');
    this.setOutput(true);
    //this.setPreviousStatement(true);
    //this.setNextStatement(true);
    this.setTooltip('Get random line from text file');
  }
};



Blockly.Language.majordomo_phpcode = {
  // Print statement.
  category: 'MajorDoMo',
  helpUrl: '',
  init: function() {
    this.setColour(290);
    this.appendTitle('phpCode');
    this.appendTitle(new Blockly.FieldTextInput(''), 'TEXT');
    //this.appendInput('', Blockly.INPUT_VALUE, 'TEXT');
    //this.setOutput(true);
    this.setPreviousStatement(true);
    this.setNextStatement(true);
    this.setTooltip('Raw PHP code');
  }
};

Blockly.Language.majordomo_phpstat = {
  // Print statement.
  category: 'MajorDoMo',
  helpUrl: '',
  init: function() {
    this.setColour(290);
    this.appendTitle('phpStatement');
    this.appendTitle(new Blockly.FieldTextInput(''), 'TEXT');
    //this.appendInput('', Blockly.INPUT_VALUE, 'TEXT');
    this.setOutput(true);
    //this.setPreviousStatement(true);
    //this.setNextStatement(true);
    this.setTooltip('Raw PHP statement');
  }
};