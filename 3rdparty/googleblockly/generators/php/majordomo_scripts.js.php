<?php    header("Content-type:application/x-javascript");    ?>
'use strict';

goog.provide('Blockly.PHP.majordomo_scripts');

goog.require('Blockly.PHP');

<?php

chdir(dirname(__FILE__) . '/../../../../');

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
Blockly.PHP['majordomo_script_<?php echo $scripts[$i]['ID'];?>'] = function(block) {
  var params = Blockly.PHP.valueToCode(block, 'PARAMS',Blockly.PHP.ORDER_NONE) || 'array()';
  <?php 
  $tmp=explode("\n", $scripts[$i]['CODE']);
  $last_line=$tmp[count($tmp)-1];
  if (preg_match('/return/is', $last_line)) {?>  
  var code = 'runScript(\'<?php echo $scripts[$i]['TITLE'];?>\','+params+')';
  return [code, Blockly.PHP.ORDER_FUNCTION_CALL];
  <?php } else {?>
  var code = 'runScript(\'<?php echo $scripts[$i]['TITLE'];?>\', '+params+');\n';
  return code;
  <?php }?>
};
<?php
 
}

$session->save();
