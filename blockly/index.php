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
    <script type="text/javascript" src="msg/js/<?echo SETTINGS_SITE_LANGUAGE;?>.js"></script>
    <script type="text/javascript" src="generators/php.js"></script>
    <script type="text/javascript" src="generators/php/majordomo.js"></script>
    <script type="text/javascript" src="generators/php/majordomo_objects.js"></script>
    <script type="text/javascript" src="generators/php/colour.js"></script>
    <script type="text/javascript" src="generators/php/lists.js"></script>
    <script type="text/javascript" src="generators/php/logic.js"></script>
    <script type="text/javascript" src="generators/php/loops.js"></script>
    <script type="text/javascript" src="generators/php/math.js"></script>
    <script type="text/javascript" src="generators/php/procedures.js"></script>
    <script type="text/javascript" src="generators/php/text.js"></script>
    <script type="text/javascript" src="generators/php/variables.js"></script>
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

       var elem1=doc.getElementById('code');
       elem1.value=code;

       var elem2=doc.getElementById('xml_code');
       elem2.value=xmlText;

       var elem3=doc.getElementById('frmEdit');
       elem3.submit();
       return false;
      }

       function SaveAndClose() {
        saveCode();
        window.close();
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
  <input type="button" onClick="window.close();" value="<?echo LANG_CANCEL;?>">
  </div>
  <!--
    <category name="Color">
      <block type="colour_picker"></block>
      <block type="colour_random"></block>
      <block type="colour_rgb"></block>
      <block type="colour_blend"></block>
    </category>
  -->
  <xml id="toolbox" style="display: none">
    <category name="<?echo LANG_GENERAL;?>">
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

    <category name="<?echo LANG_SECTION_OBJECTS;?>">
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

    <category name="<?echo LANG_TIME;?>">
     
    </category>

    <category name="<?echo LANG_LOGIC;?>">
      <block type="controls_if"></block>
      <block type="logic_compare"></block>
      <block type="logic_operation"></block>
      <block type="logic_negate"></block>
      <block type="logic_boolean"></block>
      <block type="logic_null"></block>
      <block type="logic_ternary"></block>
    </category>
    <category name="<?echo LANG_LOOPS;?>">
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
    <category name="<?echo LANG_MATH;?>">
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
    <category name="<?echo LANG_TEXT;?>">
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
    <category name="<?echo LANG_LISTS;?>">
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
    <category name="<?echo LANG_VARIABLES;?>" custom="VARIABLE"></category>
    <category name="<?echo LANG_FUNCTIONS;?>" custom="PROCEDURE"></category>
  </xml>
  </body>
</html>
<?php

$session->save();
$db->Disconnect(); 

?>