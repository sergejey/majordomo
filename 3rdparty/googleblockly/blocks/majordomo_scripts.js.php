<?php    header("Content-type:application/x-javascript");    ?>
'use strict';

goog.provide('Blockly.Blocks.majordomo_scripts');

goog.require('Blockly.Blocks');

<?php

chdir(dirname(__FILE__) . '/../../../');

include_once("./config.php");
include_once("./lib/loader.php");

$session=new session("prj");

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
 
$ctl = new control_modules();

$scripts=SQLSelect("SELECT * FROM scripts");
$total=count($scripts);
for($i=0;$i<$total;$i++) {
  $variants = array(
    array('suffix' => '', 'colour' => 275, 'output' => 'false', 'previous' => 'true', 'next' => 'true'),
    array('suffix' => '_return', 'colour' => 175, 'output' => 'true', 'previous' => 'false', 'next' => 'false'),
  );
  foreach ($variants as $variant) {
?>
Blockly.Blocks['majordomo_script<?php echo $variant['suffix'];?>_<?php echo $scripts[$i]['ID'];?>'] = {
  /**
   * Block for calling a MajorDoMo script.
   * @this Blockly.Block
   */
  init: function() {
    this.appendDummyInput()
        .appendField('<?php echo $scripts[$i]['TITLE'];?>');
    this.appendValueInput("PARAMS")
        .setCheck("Array");
    this.setInputsInline(true);

    this.setColour(<?php echo $variant['colour'];?>);
    this.setOutput(<?php echo $variant['output'];?>);
    this.setPreviousStatement(<?php echo $variant['previous'];?>);
    this.setNextStatement(<?php echo $variant['next'];?>);

    this.setTooltip('<?php echo addcslashes(preg_replace("/[\n\r]/is", '', $scripts[$i]['DESCRIPTION']), "'");?>');
  }
};

<?php
  }
}

$session->save();
