<?php    header("Content-type:application/x-javascript");    ?>
'use strict';

goog.provide('Blockly.Blocks.majordomo_myblocks');

goog.require('Blockly.Blocks');

<?php

chdir(dirname(__FILE__) . '/../../../');

include_once("./config.php");
include_once("./lib/loader.php");

$session=new session("prj");

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
 
$ctl = new control_modules();

$blocks=SQLSelect("SELECT * FROM myblocks");
$total=count($blocks);
for($i=0;$i<$total;$i++) {

?>
Blockly.Blocks['majordomo_myblock_<?php echo $blocks[$i]['ID'];?>'] = {
  /**
   * Block for null data type.
   * @this Blockly.Block
   */
  init: function() {
    // Assign 'this' to a variable for use in the closure below.
    var thisBlock = this;
    this.appendDummyInput()
        .appendField('<?php echo $blocks[$i]['TITLE'];?>');
    this.setInputsInline(true);
    <?php if ($blocks[$i]['BLOCK_TYPE']=='property') {?>
    this.setColour(<?php if ($blocks[$i]['BLOCK_COLOR']) {echo $blocks[$i]['BLOCK_COLOR'];} else {echo "175";}?>);
    this.setOutput(true);
    this.setPreviousStatement(false);
    this.setNextStatement(false);
    <?php } else {?>
    <?php if ($blocks[$i]['BLOCK_TYPE']=='method_par') {?>
     this.appendValueInput("PARAMS").setCheck("Array");
    <?php }?>
    this.setColour(<?php if ($blocks[$i]['BLOCK_COLOR']) {echo $blocks[$i]['BLOCK_COLOR'];} else {echo "275";}?>);
    this.setOutput(false);
    this.setPreviousStatement(true);
    this.setNextStatement(true);
    <?php }?>

    this.setTooltip('<?php echo $blocks[$i]['TITLE'];?>');
  }
};

<?php
 
}

$session->save();
