<?php    header("Content-type:application/x-javascript");    ?>
'use strict';

goog.provide('Blockly.PHP.majordomo_myblocks');

goog.require('Blockly.PHP');

<?php

chdir(dirname(__FILE__) . '/../../../../');

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
Blockly.PHP['majordomo_myblock_<?php echo $blocks[$i]['ID'];?>'] = function(block) {

  <?php if ($blocks[$i]['BLOCK_TYPE']=='property') {?>
  var code = 'getGlobal("<?php echo $blocks[$i]['LINKED_OBJECT'].'.'.$blocks[$i]['LINKED_PROPERTY'];?>")';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
  <?php }?>

  <?php if ($blocks[$i]['BLOCK_TYPE']=='method') {?>
  var code = 'callMethod("<?php echo $blocks[$i]['LINKED_OBJECT'].'.'.$blocks[$i]['LINKED_PROPERTY'];?>");\n';
  return code;
  <?php }?>

  <?php if ($blocks[$i]['BLOCK_TYPE']=='script') {?>
  var code = 'runScript("<?php echo $blocks[$i]['SCRIPT_ID'];?>");\n';
  return code;
  <?php }?>

  <?php if ($blocks[$i]['BLOCK_TYPE']=='method_par') {?>
  var params = Blockly.PHP.valueToCode(block, 'PARAMS',Blockly.PHP.ORDER_NONE) || 'array()';
  var code = 'callMethod("<?php echo $blocks[$i]['LINKED_OBJECT'].'.'.$blocks[$i]['LINKED_PROPERTY'];?>", '+params+');\n';
  return code;
  <?php }?>

};
<?php
 
}

$session->save();
