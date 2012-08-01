if (!Blockly.Language) {
  Blockly.Language = {};
}

Blockly.Language.majordomo_timenow = {
  // Print statement.
  category: 'Date/Time',
  helpUrl: '',
  init: function() {
    this.setColour(160);
    this.appendTitle('timeNow');
    this.setOutput(true);
    this.setTooltip('Get current time HH:MM');
  }
};


Blockly.Language.majordomo_isweekend = {
  // Print statement.
  category: 'Date/Time',
  helpUrl: '',
  init: function() {
    this.setColour(160);
    this.appendTitle('isWeekEnd');
    this.setOutput(true);
    this.setTooltip('checking for week-end true/false');
  }
};

Blockly.Language.majordomo_isweekday = {
  // Print statement.
  category: 'Date/Time',
  helpUrl: '',
  init: function() {
    this.setColour(160);
    this.appendTitle('isWorkDay');
    this.setOutput(true);
    this.setTooltip('checking for work-day true/false');
  }
};


Blockly.Language.majordomo_timeis = {
  // Print statement.
  category: 'Date/Time',
  helpUrl: '',
  init: function() {
    this.setColour(160);
    this.appendTitle('timeIs');
    this.appendInput('', Blockly.INPUT_VALUE, 'VALUE');
    this.setOutput(true);
    this.setTooltip('checking time');
  }
};

Blockly.Language.majordomo_timebefore = {
  // Print statement.
  category: 'Date/Time',
  helpUrl: '',
  init: function() {
    this.setColour(160);
    this.appendTitle('timeBefore');
    this.appendInput('', Blockly.INPUT_VALUE, 'VALUE');
    this.setOutput(true);
    this.setTooltip('timeBefore');
  }
};

Blockly.Language.majordomo_timeafter = {
  // Print statement.
  category: 'Date/Time',
  helpUrl: '',
  init: function() {
    this.setColour(160);
    this.appendTitle('timeAfter');
    this.appendInput('', Blockly.INPUT_VALUE, 'VALUE');
    this.setOutput(true);
    this.setTooltip('timeAfter');
  }
};

Blockly.Language.majordomo_timebetween = {
  // Print statement.
  category: 'Date/Time',
  helpUrl: '',
  init: function() {
    this.setColour(160);
    this.appendTitle('timeBetween');
    this.appendInput('', Blockly.INPUT_VALUE, 'A');
    this.appendInput('and', Blockly.INPUT_VALUE, 'B');
    this.setInputsInline(true);
    this.setOutput(true);
    this.setTooltip('timeBetween');
  }
};