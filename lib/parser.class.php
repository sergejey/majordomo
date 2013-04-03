<?php
/**************************************************************
* Parser
*
* Templates parser
*
* @package framework
* @author Serge Dzheigalo <jey@activeunit.com>
* @copyright ActiveUnit, Inc
* @version 1.0
*/
class parser {

 var $data; // data
 var $template; // template-file
 var $result; // result
 var $owner; // parser's owner object

/**************************************************************
* Class constructor
*
* Used to parse templates with data provided
* Based on template file extention:
* .xslt - XSLT-parser used
* .tpl - SMARTY-parser used
* all other extensions - jTemplates-parser used
*
* @param string Template filename
* @param mixed Data
* @param object Process owner
* @return parsing result
* @access public
*/
 function parser($template, &$data, $owner="") {
 // constructor
  // set current directory for template includes

  $this->data=&$data;
  $this->template=$template;

  if (Is_Object($owner)) {
   $this->owner=&$owner;
  }

  if (strpos($template, ".xslt")==TRUE) {
   // xslt-Templates
   $this->result=$this->xslt_parse($template_file, $this->data);
  } elseif (strpos($template, ".tpl")==TRUE) {
   // smarty-Templates
   $this->result=$this->smarty_parse($template, $this->data);
  } else {
   // j-Templates
   $this->result=$this->jtemplate_parse($template, $this->data);
  }

  if (Is_Object($this->owner)) {
   // links parsing for all results (framework support)
   $this->result=$this->owner->parseLinks($this->result);
  }

 }



/**************************************************************
* jTemplates-parser
*
* Used to parse jTemplates
*
* @param string jTemplate filename
* @param mixed Data
* @access private
*/
 function jtemplate_parse($template, &$data) {

/*
  $compl=new jTemplateCompiler($template, "out", $this->owner);
  $out=&$data;
  include($compl->compiled_file);
  return $result;
*/


  $jTempl=new jTemplate($template, $data, $this->owner);
  $result=$jTempl->result;
  return $result;

 }

/**************************************************************
* XSLT-parser
*
* Used to parse xslt-templates
*
* @param string XSLT-rules
* @param mixed Data
* @access private
*/
 function xslt_parse($template, &$data) {
  $new_data["ROOT"]=$data;
  $xml=new xml_data($new_data);
  $arguments = array(
      '/_xml' => $xml->string,
      '/_xsl' => $template
  );
  $xh=xslt_create();
  $result=xslt_process($xh, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments);
  xslt_free($xh);
  return $result;
 }

/**************************************************************
* SMARTY-parser
*
* Used to parse SMARTY templates
*
* param string Template filename
* @param mixed Data
* @access private
*/
 function smarty_parse($template_file, &$data) {

  define(SMARTY_DIR,ROOT.'smarty/');
  require(SMARTY_DIR.'Smarty.class.php');

  $smarty = new Smarty;

  $smarty->compile_dir = SMARTY_DIR.'templates_c/';

  if (IsSet($this->owner)) {
   $smarty->template_dir = DIR_TEMPLATES.$this->owner->name."/";
  }

  $data["ROOTHTML"]=ROOTHTML;

  foreach($data as $k=>$v) {
   $smarty->assign_by_ref($k,$data[$k]);
  }

  if (Is_Object($this->owner)) {
   $smarty->owner=&$this->owner;
  }

  $result=$smarty->fetch($template_file);
  return $result;
 }

}
?>