<?php
/**
* Date Selector 
*
* Use this module to place date picker on form
*
* @package project
* @author Serge J. <jey@unit.local>
* @copyright http://www.activeunit.com/ (c)
* @version 0.1 (wizard)
*/
// 
class dateselect extends module {
/**
* dateselect
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="dateselect";
  $this->title="Date Selector";
  $this->module_category="System";
  $this->checkInstalled();
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $calendar_module_loaded;

  if ($calendar_module_loaded) {
   $out['LOADED']=1;
  } else {
   $calendar_module_loaded=1;
  }

  $out['FORM']=$this->form;
  $out['FIELD']=$this->field;
  if (!$this->format) $this->format='en';
  $out['FORMAT']=$this->format;
  $out['UID']=rand(1, 999999);
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
// --------------------------------------------------------------------
}
?>
