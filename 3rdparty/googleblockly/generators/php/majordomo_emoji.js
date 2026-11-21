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

'use strict';

goog.provide('Blockly.PHP.majordomo_emoji');

goog.require('Blockly.PHP');

// Produces a double-quoted PHP string with \u{...} escapes. PHP turns those
// into the UTF-8 emoji at runtime, while the saved code stays pure ASCII.
Blockly.PHP.majordomoEmojiCode_ = function(block) {
  var codes = block.getFieldValue('EMOJI') || '';
  var parts = codes.split(' ');
  var escaped = '';
  for (var i = 0; i < parts.length; i++) {
    if (/^[0-9A-Fa-f]{1,6}$/.test(parts[i])) {
      escaped += '\\u{' + parts[i] + '}';
    }
  }
  return ['"' + escaped + '"', Blockly.PHP.ORDER_ATOMIC];
};

for (var mjGenIndex = 1; mjGenIndex <= Blockly.MJ_EMOJI_GROUPS.length;
     mjGenIndex++) {
  Blockly.PHP['majordomo_emoji_' + mjGenIndex] = Blockly.PHP.majordomoEmojiCode_;
}
