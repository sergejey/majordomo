<?
/**
 * open_weather 
 *
 * open_weather
 *
 * @package open_weather
 * @author L.D.V. <dev@silvergate.ru>
 * @copyright http://www.silvergate.ru/ (c)
 * @version 0.1 
 */

class open_weather extends module 
{
   /**
    * open_weather
    *
    * Module class constructor
    *
    * @access private
    */
   function open_weather() 
   {
      $this->name            = "open_weather";
      $this->title           = "open_weather";
      $this->module_category = "CMS";
      $this->checkInstalled();
   }

   /**
    * saveParams
    *
    * Saving module parameters
    *
    * @access public
    */
   function saveParams() 
   {
      $p = array();
      
      if (isset($this->id)) $p["id"] = $this->id;
      
      if (isset($this->view_mode)) $p["view_mode"] = $this->view_mode;
      
      if (IsSet($this->edit_mode)) $p["edit_mode"] = $this->edit_mode;
    
      if (IsSet($this->tab)) $p["tab"] = $this->tab;
      
      return parent::saveParams($p);
   }
  
   /**
    * getParams
    *
    * Getting module parameters from query string
    *
    * @access public
    */
   function getParams() 
   {
      global $id;
      global $mode;
      global $view_mode;
      global $edit_mode;
      global $tab;
  
      if (isset($id)) $this->id = $id;
   
      if (isset($mode)) $this->mode = $mode;
   
      if (isset($view_mode)) $this->view_mode = $view_mode;
   
      if (isset($edit_mode)) $this->edit_mode = $edit_mode;
   
      if (isset($tab)) $this->tab = $tab;
   }
   
   /**
    * Run
    *
    * Description
    *
    * @access public
    */
   function run() 
   {
      global $session;
      
      $out = array();
     
      if ($this->action == 'admin') 
         $this->admin($out);
      else 
         $this->usual($out);
      
      if (IsSet($this->owner->action)) 
         $out['PARENT_ACTION'] = $this->owner->action;
     
      if (IsSet($this->owner->name))
         $out['PARENT_NAME']=$this->owner->name;
     
      $out['VIEW_MODE'] = $this->view_mode;
      $out['EDIT_MODE'] = $this->edit_mode;
      $out['MODE']      = $this->mode;
      $out['ACTION']    = $this->action;
     
      if ($this->single_rec) $out['SINGLE_REC'] = 1;
     
      $this->data = $out;
     
      $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
      $this->result=$p->result;
   }

   /**
    * BackEnd
    *
    * Module backend
    *
    * @access public
    */
   function admin(&$out) {}

   /**
    * FrontEnd
    *
    * Module frontend
    *
    * @access public
    */
   function usual(&$out) 
   {
      $this->admin($out);
   }

   /**
    * Install
    *
    * Module installation routine
    *
    * @access private
    */
   function install() 
   {
      parent::install();
   }
}

/*
 *
 * TW9kdWxlIGNyZWF0ZWQgTWFyIDA0LCAyMDEwIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
 *
 */

?>