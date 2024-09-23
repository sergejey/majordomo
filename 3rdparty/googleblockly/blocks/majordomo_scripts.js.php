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

?>
Blockly.Blocks['majordomo_script_<?php echo $scripts[$i]['ID'];?>'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;


    this.appendDummyInput()
        .appendField('<?php echo $scripts[$i]['TITLE'];?>');
    this.appendValueInput("PARAMS")
        .setCheck("Array");
    this.setInputsInline(true);

    <?php 
    $tmp=explode("\n", $scripts[$i]['CODE']);
    $last_line=$tmp[count($tmp)-1];
    if (preg_match('/return/is', $last_line)) {?>
    this.setColour(175);
    this.setOutput(true);
    this.setPreviousStatement(false);
    this.setNextStatement(false);
    <?php } else {?>
    this.setColour(275);
    this.setOutput(false);
    this.setPreviousStatement(true);
    this.setNextStatement(true);
    <?php }?>

    this.setTooltip('<?php echo addcslashes(preg_replace("/[\n\r]/is", '', $scripts[$i]['DESCRIPTION']), "'");?>');
  }
};

<?php
 
}

$session->save();
