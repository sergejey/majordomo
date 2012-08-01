if (!Blockly.Language) {
  Blockly.Language = {};
}

Blockly.Language.majordomo_cleartimeout = {
  // Print statement.
  category: 'Schedule',
  helpUrl: '',
  init: function() {
    this.setColour(160);
    this.appendTitle('clearTimeOut');
    this.appendTitle(new Blockly.FieldTextInput(''), 'TEXT');
    this.setPreviousStatement(true);
    this.setNextStatement(true);
    this.setTooltip('Reset named timeOut');
  }
};


Blockly.Language.majordomo_settimeout = {
  // Print statement.
  category: 'Schedule',
  helpUrl: '',
  init: function() {
    this.setColour(160);
    this.appendTitle('setTimeOut');
    this.appendTitle(new Blockly.FieldTextInput(''), 'A');
    this.appendTitle('to be started in');
    this.appendTitle(new Blockly.FieldTextInput(''), 'B');
    this.appendTitle('seconds');
    this.appendInput('do', Blockly.NEXT_STATEMENT, 'DO0');
    this.setPreviousStatement(true);
    this.setNextStatement(true);
    this.setTooltip('Reset named timeOut');
  }
};