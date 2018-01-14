<?php

chdir(dirname(__FILE__) . '/../');

include_once("./config.php");
include_once("./lib/loader.php");

$session=new session("prj");
// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); 

include_once("./load_settings.php");
include_once(DIR_MODULES . "control_modules/control_modules.class.php");
 
$ctl = new control_modules();

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <script type="text/javascript" src="blockly_compressed.js"></script>
    <script type="text/javascript" src="blocks_compressed.js"></script>
    <script type="text/javascript" src="blocks/majordomo.js"></script>
    <script type="text/javascript" src="blocks/majordomo_objects.js"></script>
    <script type="text/javascript" src="blocks/majordomo_time.js"></script>
    <script type="text/javascript" src="blocks/majordomo_scripts.js.php"></script>
    <script type="text/javascript" src="blocks/majordomo_myblocks.js.php"></script>
    <?php
    if (file_exists(DIR_MODULES.'devices/devices.class.php')) {
      echo '<script type="text/javascript" src="blocks/majordomo_devices.js.php"></script>';
    }
    ?>
    <script type="text/javascript" src="msg/js/<?php echo SETTINGS_SITE_LANGUAGE;?>.js"></script>
    <script type="text/javascript" src="generators/php.js"></script>
    <script type="text/javascript" src="generators/php/majordomo.js"></script>
    <script type="text/javascript" src="generators/php/majordomo_objects.js"></script>
    <script type="text/javascript" src="generators/php/majordomo_time.js"></script>
    <script type="text/javascript" src="generators/php/colour.js"></script>
    <script type="text/javascript" src="generators/php/lists.js"></script>
    <script type="text/javascript" src="generators/php/logic.js"></script>
    <script type="text/javascript" src="generators/php/loops.js"></script>
    <script type="text/javascript" src="generators/php/math.js"></script>
    <script type="text/javascript" src="generators/php/procedures.js"></script>
    <script type="text/javascript" src="generators/php/text.js"></script>
    <script type="text/javascript" src="generators/php/variables.js"></script>
    <script type="text/javascript" src="generators/php/majordomo_scripts.js.php"></script>
    <script type="text/javascript" src="generators/php/majordomo_myblocks.js.php"></script>
    <?php
    if (file_exists(DIR_MODULES.'devices/devices.class.php')) {
     echo '<script type="text/javascript" src="generators/php/majordomo_devices.js.php"></script>';
    }
    ?>
    <style>
      html, body {
        background-color: #fff;
        margin: 0;
        padding: 0;
        overflow: hidden;
        height: 100%;
      }
      .blocklySvg {
        height: 100%;
        width: 100%;
      }
    </style>
    <script>

      function saveCode() {
       var code = Blockly.PHP.workspaceToCode();
       var doc=window.opener.document;

       var xmlDom = Blockly.Xml.workspaceToDom(Blockly.mainWorkspace);
       var xmlText = Blockly.Xml.domToPrettyText(xmlDom);

       var elem1=doc.getElementById('<?php echo $_GET['code_field'];?>');
       elem1.value=code;

       var elem2=doc.getElementById('xml_code');
       elem2.value=xmlText;

       var elem3=doc.getElementById('frmEdit');
       elem3.submit();
       return false;
      }

       function SaveAndClose() {
        saveCode();
        setTimeout(function(){window.close()}, 1000);
        return false;
       }

       function SaveAndContinue() {
        saveCode();
        return false;
       }



      function init() {
        Blockly.inject(document.body,
            {path: './', toolbox: document.getElementById('toolbox')});
        // Let the top-level application know that Blockly is ready.
    //    window.parent.blocklyLoaded(Blockly);

  var doc=window.opener.document;  
  var elem2=doc.getElementById('xml_code');  
  var xmlText=elem2.value;
  if (xmlText) {
    var xmlDom = null;
    try {
      xmlDom = Blockly.Xml.textToDom(xmlText);
    } catch (e) {
      var q = window.confirm('Error parsing XML:\n' + e + '\n\nAbandon changes?');
      if (!q) {
        // Leave the user on the XML tab.
        return;
      }
    }
    if (xmlDom) {
      Blockly.mainWorkspace.clear();
      Blockly.Xml.domToWorkspace(Blockly.mainWorkspace, xmlDom);
    }

  }


      }
    </script>
  </head>
  <body onload="init()">
  <div align="right">
  <input type="button" onClick="return SaveAndClose();" value="OK">
  <input type="button" onClick="window.close();" value="<?php echo LANG_CANCEL;?>">
  </div>



  <xml id="toolbox" style="display: none">

    <category name="<?php echo LANG_GENERAL;?>">
      <block type="majordomo_say_simple">
        <value name="TEXT">
          <block type="text"></block>
        </value>
      </block>
      <block type="majordomo_runscript">
        <value name="TEXT">
          <block type="text"></block>
        </value>
      </block>
      <block type="majordomo_runscriptwithparams">
        <value name="TEXT">
          <block type="text"></block>
        </value>
      </block>
      <block type="majordomo_say">
        <value name="TEXT">
          <block type="text"></block>
        </value>
      </block>
      <block type="majordomo_playsound">
        <value name="TEXT">
          <block type="text"></block>
        </value>
      </block>
      <block type="majordomo_geturl">
        <value name="TEXT">
          <block type="text"></block>
        </value>
      </block>
      <block type="majordomo_callurl">
        <value name="TEXT">
          <block type="text"></block>
        </value>
      </block>
      <block type="majordomo_getrandomline">
        <value name="TEXT">
          <block type="text"></block>
        </value>
      </block>
      <block type="majordomo_phpexpression">
        <value name="TEXT">
          <block type="text"></block>
        </value>
      </block>
      <block type="majordomo_phpcode">
        <value name="TEXT">
          <block type="text"></block>
        </value>
      </block>
    </category>

    <category name="<?php echo LANG_SECTION_OBJECTS;?>">
      <block type="majordomo_getglobal">
        <value name="PROPERTY">
          <block type="text"></block>
        </value>
        <value name="OBJECT">
          <block type="text"></block>
        </value>
      </block>
      <block type="majordomo_setglobal">
        <value name="VALUE">
          <block type="text"></block>
        </value>
        <value name="PROPERTY">
          <block type="text"></block>
        </value>
        <value name="OBJECT">
          <block type="text"></block>
        </value>
      </block>

      <block type="majordomo_callmethod">
        <value name="METHOD">
          <block type="text"></block>
        </value>
        <value name="OBJECT">
          <block type="text"></block>
        </value>
      </block>

      <block type="majordomo_callmethodwithparams">
        <value name="METHOD">
          <block type="text"></block>
        </value>
        <value name="OBJECT">
          <block type="text"></block>
        </value>
      </block>

      <block type="majordomo_callmethodcurrent">
        <value name="METHOD">
          <block type="text"></block>
        </value>
      </block>

      <block type="majordomo_callmethodwithparamscurrent">
        <value name="METHOD">
          <block type="text"></block>
        </value>
      </block>

      <block type="majordomo_getcurrent">
        <value name="PROPERTY">
          <block type="text"></block>
        </value>
      </block>
      <block type="majordomo_setcurrent">
        <value name="VALUE">
          <block type="text"></block>
        </value>
        <value name="PROPERTY">
          <block type="text"></block>
        </value>
      </block>
      <block type="majordomo_keyvalue">
        <value name="KEY">
          <block type="text"></block>
        </value>
        <value name="VALUE">
          <block type="text"></block>
        </value>
      </block>
      <block type="majordomo_paramvalue">
        <value name="KEY">
          <block type="text"></block>
        </value>
        <value name="VALUE">
          <block type="text"></block>
        </value>
      </block>
      <block type="majordomo_getobjects">
        <value name="CLASS">
          <block type="text"></block>
        </value>
      </block>

    </category>

    <category name="<?php echo LANG_TIME;?>">
      <block type="majordomo_timeis">
        <value name="TIME">
          <block type="text"></block>
        </value>
      </block>     
      <block type="majordomo_timebefore">
        <value name="TIME">
          <block type="text"></block>
        </value>
      </block>     
      <block type="majordomo_timeafter">
        <value name="TIME">
          <block type="text"></block>
        </value>
      </block>     
      <block type="majordomo_timebetween">
        <value name="TIME1">
          <block type="text"></block>
        </value>
        <value name="TIME2">
          <block type="text"></block>
        </value>
      </block>     
      <block type="majordomo_cleartimeout">
        <value name="TIMER">
          <block type="text"></block>
        </value>
      </block>     
      <block type="majordomo_settimeout">
        <value name="TIMER">
          <block type="text"></block>
        </value>
        <value name="DELAY">
          <block type="text"></block>
        </value>
      </block>     

      <block type="majordomo_timenow"></block>
      <block type="majordomo_isweekend"></block>
      <block type="majordomo_isworkday"></block>
    </category>

    <category name="<?php echo LANG_LOGIC;?>">
      <block type="controls_if"></block>
      <block type="logic_compare"></block>
      <block type="logic_operation"></block>
      <block type="logic_negate"></block>
      <block type="logic_boolean"></block>
      <block type="logic_null"></block>
      <block type="logic_ternary"></block>
    </category>
    <category name="<?php echo LANG_LOOPS;?>">
      <block type="controls_repeat_ext">
        <value name="TIMES">
          <block type="math_number">
            <field name="NUM">10</field>
          </block>
        </value>
      </block>
      <block type="controls_whileUntil"></block>
      <block type="controls_for"></block>
      <block type="controls_forEach"></block>
      <block type="controls_flow_statements"></block>
    </category>
    <category name="<?php echo LANG_MATH;?>">
      <block type="math_number"></block>
      <block type="math_arithmetic"></block>
      <block type="math_single"></block>
      <block type="math_constant"></block>
      <block type="math_number_property"></block>
      <block type="math_change"></block>
      <block type="math_round"></block>
      <block type="math_trig"></block>
      <block type="math_on_list"></block>
      <block type="math_modulo"></block>
      <block type="math_constrain"></block>
      <block type="math_random_int"></block>
      <block type="math_random_float"></block>
    </category>
    <category name="<?php echo LANG_TEXT;?>">
      <block type="text"></block>
      <block type="text_length"></block>
      <block type="text_join"></block>
      <block type="text_append"></block>
      <block type="text_isEmpty"></block>
      <block type="text_print"></block>
      <block type="text_indexOf"></block>
      <block type="text_charAt"></block>
      <block type="text_getSubstring"></block>
      <block type="text_changeCase"></block>
      <block type="text_trim"></block>
    </category>
    <category name="<?php echo LANG_LISTS;?>">
      <block type="lists_create_empty"></block>
      <block type="lists_create_with"></block>
      <block type="lists_repeat"></block>
      <block type="lists_length"></block>
      <block type="lists_isEmpty"></block>
      <block type="lists_indexOf"></block>
      <block type="lists_getIndex"></block>
      <block type="lists_setIndex"></block>
      <block type="lists_getSublist"></block>
    </category>

    <category name="<?php echo LANG_COLOR;?>">
      <block type="colour_picker"></block>
      <block type="colour_random"></block>
      <block type="colour_rgb"></block>
    </category>

    <category name="<?php echo LANG_VARIABLES;?>" custom="VARIABLE"></category>
    <category name="<?php echo LANG_FUNCTIONS;?>" custom="PROCEDURE"></category>

  <?php


  if (file_exists(DIR_MODULES.'devices/devices.class.php')) {
   @include_once(ROOT.'languages/devices'.'_'.SETTINGS_SITE_LANGUAGE.'.php');
   @include_once(ROOT.'languages/devices'.'_default'.'.php');

    include_once(DIR_MODULES.'devices/devices.class.php');
    $dev=new devices();
    $dev->setDictionary();
    echo '<category name="'.LANG_DEVICES_MODULE_TITLE.'">'."\n";
    $res=SQLSelect("SELECT * FROM devices ORDER BY TITLE");
    $total = count($res);
    for ($i = 0; $i < $total; $i++) {
      if ($res[$i]['TYPE']=='relay') {
      } elseif ($res[$i]['TYPE']=='dimmer') {
      } elseif ($res[$i]['TYPE']=='motion') {
        echo '<block type="majordomo_device_'.$res[$i]['ID'].'_motionDetected"></block>'."\n";
      } elseif ($res[$i]['TYPE']=='sensor_temp') {
      } elseif ($res[$i]['TYPE']=='sensor_humidity') {
      } elseif ($res[$i]['TYPE']=='openclose') {
        echo '<block type="majordomo_device_'.$res[$i]['ID'].'_currentStatus"></block>'."\n";
      } elseif ($res[$i]['TYPE']=='button') {
        echo '<block type="majordomo_device_'.$res[$i]['ID'].'_press"></block>'."\n";
      }
      if ($dev->device_types[$res[$i]['TYPE']]['PARENT_CLASS']=='SControllers') {
        echo '<block type="majordomo_device_'.$res[$i]['ID'].'_turnOn"></block>'."\n";
        echo '<block type="majordomo_device_'.$res[$i]['ID'].'_turnOff"></block>'."\n";
        echo '<block type="majordomo_device_'.$res[$i]['ID'].'_currentStatus"></block>'."\n";
      }
      if ($res[$i]['TYPE']=='rgb') {
        echo '<block type="majordomo_device_'.$res[$i]['ID'].'_setColor">'."\n";
        echo "<value name=\"COLOR\"><block type=\"text\"></block></value>";
        echo "</block>";
      }
      if ($dev->device_types[$res[$i]['TYPE']]['PARENT_CLASS']=='SSensors') {
        echo '<block type="majordomo_device_'.$res[$i]['ID'].'_currentValue"></block>'."\n";
        echo '<block type="majordomo_device_'.$res[$i]['ID'].'_minValue"></block>'."\n";
        echo '<block type="majordomo_device_'.$res[$i]['ID'].'_maxValue"></block>'."\n";
      }
    }
    
   echo '</category>';
  }

  ?>

  <?php
  $sortby="myblocks_categories.TITLE, myblocks.TITLE";
  $res=SQLSelect("SELECT myblocks.*, myblocks_categories.TITLE as CATEGORY FROM myblocks LEFT JOIN myblocks_categories ON myblocks.CATEGORY_ID=myblocks_categories.ID WHERE 1 ORDER BY $sortby");
  $old_category='';
  if ($res[0]['ID']) {
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    if (!$res[$i]['CATEGORY']) {
     $res[$i]['CATEGORY']=LANG_OTHER;
    }
    if ($res[$i]['CATEGORY']!=$old_category) {
     $out['TOTAL_CATEGORIES']++;
     $old_category=$res[$i]['CATEGORY'];
     $res[$i]['NEW_CATEGORY']=1;

     if ($i>0) {
      echo '</category>'."\n";
     }
     echo '<category name="'.processTitle($res[$i]['CATEGORY']).'">'."\n";
    }

    echo '<block type="majordomo_myblock_'.$res[$i]['ID'].'"></block>'."\n";

    if ($i==$total-1) {
     $res[$i]['LAST']=1;
    }

   }
   echo '</category>';
  }
  ?>


  <?php
  $sortby="script_categories.TITLE, scripts.TITLE";
  $res=SQLSelect("SELECT scripts.*, script_categories.TITLE as CATEGORY FROM scripts LEFT JOIN script_categories ON scripts.CATEGORY_ID=script_categories.ID WHERE 1 ORDER BY $sortby");
  $old_category='';
  if ($res[0]['ID']) {
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    if (!$res[$i]['CATEGORY']) {
     $res[$i]['CATEGORY']=LANG_OTHER;
    }
    $res[$i]['DESCRIPTION']=nl2br(htmlspecialchars($res[$i]['DESCRIPTION']));

    if ($res[$i]['CATEGORY']!=$old_category) {
     $out['TOTAL_CATEGORIES']++;
     $old_category=$res[$i]['CATEGORY'];
     $res[$i]['NEW_CATEGORY']=1;

     if ($i>0) {
      echo '</category>'."\n";
     }
     echo '<category name="'.LANG_MODULE_SCRIPTS.': '.processTitle($res[$i]['CATEGORY']).'">'."\n";
    }

    echo '<block type="majordomo_script_'.$res[$i]['ID'].'"></block>'."\n";

    if ($i==$total-1) {
     $res[$i]['LAST']=1;
    }

   }

   echo '</category>';
  }
  ?>

  </xml>
  </body>
</html>
<?php

$session->save();
$db->Disconnect(); 

?>