<?

require_once(DIR_MODULES . '/app_postoffice/lib/dal.russianpost.lib.php');
require_once(DIR_MODULES . '/app_postoffice/lib/russianpost.lib.php');
use DAL\RussianPostDAL as RussianPost;

/**
 * Application PostOffice - Check post from RussainPost
 *
 * @package PostOffice
 * @author LDV <dev@silvergate.ru>
 * @version 0.1
 */
class app_postoffice extends module
{
   /**
    * app_postoffice
    *
    * Module class constructor
    *
    * @access private
    */
   function app_postoffice()
   {
      $this->name            = "app_postoffice";
      $this->title           = "PostOffice";
      $this->module_category = "<#LANG_SECTION_APPLICATIONS#>";
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
      
      if (isset($this->id))
         $p["id"] = $this->id;
      
      if (isset($this->view_mode))
         $p["view_mode"] = $this->view_mode;
 
      if (isset($this->edit_mode))
         $p["edit_mode"] = $this->edit_mode;
 
      if (isset($this->tab))
         $p["tab"] = $this->tab;
 
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
      
      if (isset($id))
         $this->id = $id;
      
      if (isset($mode))
         $this->mode = $mode;
      
      if (isset($view_mode))
         $this->view_mode = $view_mode;
      
      if (isset($edit_mode))
         $this->edit_mode = $edit_mode;
      
      if (isset($tab))
         $this->tab = $tab;
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
      
      if (isset($this->owner->action))
         $out['PARENT_ACTION'] = $this->owner->action;
      
      if (isset($this->owner->name))
         $out['PARENT_NAME'] = $this->owner->name;
  
      $out['VIEW_MODE'] = $this->view_mode;
      $out['EDIT_MODE'] = $this->edit_mode;
      $out['MODE']      = $this->mode;
      $out['ACTION']    = $this->action;
  
      if ($this->single_rec)
         $out['SINGLE_REC'] = 1;
  
      $this->data = $out;
      $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
      $this->result = $p->result;
   }
   
   /**
    * BackEnd
    *
    * Module backend
    *
    * @access public
    */
   function admin(&$out)
   {
      $action = isset($_REQUEST['act']) ?  $_REQUEST['act'] : "show";
      
      if ($action == "del")
      {
         $resultMessage = "";
         try
         {
            // Get TrackNumber form request
            $trackID = isset($_REQUEST['trackid']) ? $_REQUEST['trackid'] : null; 
            // Exit then TrackNumber does't exist.
            if ($trackID == null)
               throw new Exception("TrackNumber not found.");
         
            //remove TrackNumber From Database
            $isTrackID =  RussianPost::DeleteTrack($trackID);
            
            $resultMessage = "TrackNumber was delete from database";
         }
         catch(Exception $e)
         {
            $resultMessage = "Oops! We have error: " . $e->getMessage();
         }
         
         echo $resultMessage;
         $action = "";
         return;
      }
      else if ($action == "add")
      {
         $resultMessage = "";
         try
         {
            // Get TrackNumber form request
            $trackID   = isset($_REQUEST['trackid'])   ? $_REQUEST['trackid']    : null;
            $trackName = isset($_REQUEST['trackname']) ? $_REQUEST['trackname']  : null;
            // Exit then TrackNumber does't exist.
            if ($trackID == null)
               throw new Exception("TrackNumber not found.");
            
            $trackName = $trackName == null ? $trackID : $trackName;
            
            //add TrackNumber to Database
            RussianPost::AddTrack($trackID, $trackName);
            $url = "admin.php?pd=&md=panel&inst=&action=app_postoffice";
            header_remove();
            header("Location: " . $url, true);
            die();
         }
         catch(Exception $e)
         {
            $resultMessage = "Oops! We have error: " . $e->getMessage();
         }
         
         //echo $resultMessage;
         $action = "";
         return;
      }
      else if ($action == "check")
      {
         $result = $this->CheckPostTrack();
         echo $result;
         
         return;
      }
      else if ($action == "changestatus")
      {
         $resultMessage = "";
         try
         {
            // Get TrackNumber form request
            $trackID = isset($_REQUEST['trackid']) ? $_REQUEST['trackid'] : null; 
            // Exit then TrackNumber does't exist.
            if ($trackID == null)
               throw new Exception("TrackNumber not found.");
            
            $res = RussianPost::UpdateTrackStatus($trackID);
            
            $resultMessage = $res == true ? "Track status was changed" : "Track status can't change";
         }
         catch(Exception $e)
         {
            $resultMessage = "Oops! We have error: " . $e->getMessage();
         }
         
         echo $resultMessage;
         $action = "";
         return;
         
      }
      else
      {
         $trackArray = array();
         $trackNum   = 1;
      
         $tracks  = RussianPost::SelectTrack();
     
         foreach ($tracks as $track)
         {
            $trackID = $track['TRACK_ID'];
            $arr = array();
            $arr['TRACK_NUM']       = $trackNum;
            $arr['TRACK_ID']        = $trackID;
            $arr['TRACK_NAME']      = $track['TRACK_NAME'];
            $arr['FLAG_CHECK']      = $track['FLAG_CHECK'];
            $arr['TRACK_DATE']      = $track['TRACK_DATE'];
            $arr['OPER_DATE']       = ""; 
            $arr['ATTRIB_NAME']     = "";
            $arr['OPER_POSTPLACE']  = "";
      
            $trackShortInfo = RussianPost::SelectTrackLastInfoByID($trackID);
      
            foreach ($trackShortInfo as $info)
            {
               $arr['OPER_DATE']      = $info['OPER_DATE'];
               $arr['ATTRIB_NAME']    = $info['ATTRIB_NAME'];
               $arr['OPER_POSTPLACE'] = $info['OPER_POSTPLACE'];
            }
            $trackArray[] = $arr;
            $trackNum++;
         }
      
         $out['TRACK_LIST'] = $trackArray;
      }
   }

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
   
   /**
    * Check tracks on russan post
    * @return operation message
    */
   function CheckPostTrack()
   {
      try
      {
         // returned message
         $resultMessage = "";
         // get tracks
         $tracks = RussianPost::SelectTrackByFlag(RussianPost::FLAG_ACTIVE_TRACK);
         // if tracks not found then quit
         if(count($tracks) == 0)
            throw new Exception("Track numbers not found!");
         
         // Check flag Proxy
         //$isProxy = RussianPost::isProxy();
         //proxy settings
         //$proxy   = $isProxy ? RussianPost::SelectProxySettings() : null;

         // init the client
         $client = new RussianPostAPI();
         
         $timeSeparator  = 'T';   //$separator1
         $timeSeparator2 = '.';   //$separator2
         
         // check post tracks
         foreach ($tracks as $track)
         {
            // track id
            $trackID = $track['TRACK_ID'];
            // get track info from russian post
            
            try
            {
               $trackInfo = $client->getOperationHistory($trackID);
               // skip track if no info
               if(count($trackInfo) == 0) continue;
               
               foreach ($trackInfo as $track)
               {
                  $operationDate             = '';  // Operation Date
                  $operationTypeID           = '';  // Operation Type ID
                  $operationTypeName         = '';  // Operation Type Name
                  $operationAttributeID      = '';  // Operation Attribure ID
                  $operationAttribute        = '';  // Operation Attribte
                  $operationPlacePostalCode  = '';  // Operation Place Postal Code
                  $operationPlaceName        = '';  // Operation Place Name
                  $itemWeight                = '';  // Item Weight
                  $declaredValue             = '';  // Declared Item Value
                  $collectOnDeliveryPrice    = '';  // Collect On Delivery Price
                  $destinationPostalCode     = '';  // Destination Postal Code
                  $destinationAddress        = '';  // Destination Address
                  
                  foreach ($track as $key => $value)
                  {
                     if($key == 'operationType')            { $operationTypeName          = $value; }
                     if($key == 'operationTypeId')          { $operationTypeID            = $value; }
                     if($key == 'operationAttribute')       { $operationAttribute         = $value; }
                     if($key == 'operationAttributeId')     { $operationAttributeID       = $value; }
                     if($key == 'operationPlacePostalCode') { $operationPlacePostalCode   = $value; }
                     if($key == 'operationPlaceName')       { $operationPlaceName         = $value; }
                     if($key == 'operationDate')            { $operationDate              = $value; }
                     if($key == 'itemWeight')               { $itemWeight                 = $value; }
                     if($key == 'declaredValue')            { $declaredValue              = $value; }
                     if($key == 'collectOnDeliveryPrice')   { $collectOnDeliveryPrice     = $value; }
                     if($key == 'destinationPostalCode')    { $destinationPostalCode      = $value; }
                     if($key == 'destinationAddress')       { $destinationAddress         = $value; }
                  }
                  
                  $res = RussianPost::AddTrackDetail
                           ($trackID, $operationDate,$operationTypeID,$operationTypeName,$operationAttributeID, $operationAttribute,
                            $operationPlacePostalCode,$operationPlaceName,$itemWeight,$declaredValue,$collectOnDeliveryPrice,$destinationPostalCode,$destinationAddress);
               }
            }
            catch(Exception $e)
            {
               // TODO: сделать обработчик исключений для каждого трека.
            }
         }
         
         $resultMessage = "Russian Post ckeck is complete";
      }
      catch(RussianPostException $e)
      {
         $resultMessage = "Error: " . $e->getMessage();
      }
      
      return $resultMessage;
   }
}
?>