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

/**
 * Parser
 * @category Templates_Parser
 * @package Framework
 * @author Serge Dzheigalo <jey@unit.local>
 * @copyright 2001-2004 Activeunit Inc
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link https://github.com/sergejey/majordomo/blob/master/lib/parser.class.php
 */
class parser
{
   var $data;     // data
   var $template; // template-file
   var $result;   // result
   var $owner;    // parser's owner object

   /**
    * Used to parse templates with data provided
    * Based on template file extention:
    * .xslt - XSLT-parser used
    * .tpl - SMARTY-parser used
    * all other extensions - jTemplates-parser used
    * @param mixed $template Template filename
    * @param mixed $data     Data
    * @param mixed $owner    Parser's owner object
    */
   public function __construct($template, &$data, $owner = "")
   {
      // set current directory for template includes
      $this->data     = &$data;
      $this->template = $template;

      if (is_object($owner))
      {
         $this->owner = &$owner;
      }

      if (strpos($template, ".xslt") == TRUE)
      {
         // xslt-Templates
         $this->result = $this->xslt_parse($template, $this->data);
      }
      elseif (strpos($template, ".tpl") == TRUE)
      {
         // smarty-Templates
         $this->result = $this->smarty_parse($template, $this->data);
      }
      else
      {
         // j-Templates
         $this->result = $this->jtemplate_parse($template, $this->data);
      }

      if (is_object($this->owner))
      {
         // links parsing for all results (framework support)
         $this->result = $this->owner->parseLinks($this->result);
      }
   }

   /**
    * jTemplates-parse
    * Used to parse jTemplates
    * @access private
    * @param mixed $template jTemplate filename
    * @param mixed $data     Data
    * @return string
    */
   public function jtemplate_parse($template, &$data)
   {
      /*
      if (preg_match('/menu\.html/', $_SERVER['REQUEST_URI'])) {
      $compl=new jTemplateCompiler($template, "out", $this->owner);
      $out=&$data;
      include($compl->compiled_file);
      }
       */

      startMeasure('Parse template ' . $template);
      
      $jTempl = new jTemplate($template, $data, $this->owner);
      $result = $jTempl->result;

      endMeasure('Parse template ' . $template);

      return $result;
   }

   /**
    * XSLT-parser
    * Used to parse xslt-templates
    * @access private
    * @param mixed $template XSLT-rules
    * @param mixed $data     Data
    * @return mixed
    */
   public function xslt_parse($template, &$data)
   {
      $new_data["ROOT"] = $data;
      
      $xml = new xml_data($new_data);
      
      $arguments = array('/_xml' => $xml->string, '/_xsl' => $template);
      
      $xh = xslt_create();
      
      $result = xslt_process($xh, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments);
      
      xslt_free($xh);

      return $result;
   }

   /**
    * SMARTY-parser
    * Used to parse SMARTY templates
    * @access private
    * @param mixed $template_file Template filename
    * @param mixed $data          Data
    * @return mixed
    */
   public function smarty_parse($template_file, &$data)
   {
      define('SMARTY_DIR',ROOT . 'lib/smarty/');
      
      require_once(SMARTY_DIR . 'Smarty.class.php');

      $smarty = new Smarty;
      
      $smarty->compile_dir = SMARTY_DIR . 'templates_c/';

      if (isset($this->owner))
      {
         $smarty->template_dir = DIR_TEMPLATES . $this->owner->name . "/";
      }

      $data["ROOTHTML"] = ROOTHTML;

      foreach ($data as $k => $v)
      {
         $smarty->assign($k, $data[$k]);
      }

      if (is_object($this->owner))
      {
         $smarty->owner = &$this->owner;
      }

      $result = $smarty->fetch($template_file);

      return $result;
   }
}
?>