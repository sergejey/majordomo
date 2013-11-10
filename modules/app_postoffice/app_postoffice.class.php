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
            RussianPost::DeleteTrackDetailByID($trackID);
            $isTrackID =  RussianPost::DeleteTrack($trackID);
            
            $resultMessage = "TrackNumber was delete from database";
         }
         catch(Exception $e)
         {
            $resultMessage = "Oops! We have error: " . $e->getMessage();
         }
         
         echo $resultMessage;
         $action = "";
         exit();
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
         exit();
         return;
      }
      else if ($action == "check")
      {
         $result = $this->CheckPostTrack() ? "Russian Post ckeck is complete" : "Error! Error message in log file.";
         echo $result;
         exit();
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
         exit();
         return;
         
      }
      else
      {
         $trackArray = $this->GetLastCheckedTracks();
      
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
    * Uninstall
    *
    * Module uninstall routine
    *
    * @access public
    */
   function uninstall()
   {
      SQLExec('drop table if exists POST_PROXY');
      SQLExec('drop table if exists POST_TRACKINFO');
      SQLExec('drop table if exists POST_TRACK');
      parent::uninstall();
   }
   
   /**
    * dbInstall
    *
    * Database installation routine
    *
    * @access private
    */
   function dbInstall()
   {
      $RequestDate  =  date('Y-m-d H:i:s');
      
      // POST_PROXY     - Proxy setings for RussianPost service
      $query = "drop table if exists POST_PROXY";
      SQLExec($query);
      $query = "create table POST_PROXY
                (
                  FLAG_PROXY           VARCHAR(1) not null default 'N',
                  PROXY_HOST           VARCHAR(64),
                  PROXY_PORT           VARCHAR(4),
                  PROXY_USER           VARCHAR(64),
                  PROXY_PASSWD         VARCHAR(64),
                  LM_DATE              DATETIME not null,
                  primary key (FLAG_PROXY)
                );";
      SQLExec($query);
      
      $rec = array();
      $rec["FLAG_PROXY"]   = "N";
      $rec["PROXY_HOST"]   = "";
      $rec["PROXY_PORT"]   = "";
      $rec["PROXY_USER"]   = "";
      $rec["PROXY_PASSWD"] = "";
      $rec["LM_DATE"]      = $RequestDate;
     
      SQLInsert("POST_PROXY",$rec);
      
      // POST_TRACK     - Track list
      $query = "drop table if exists POST_TRACK";
      SQLExec($query);
      $query = "create table POST_TRACK
                  (
                     TRACK_ID             VARCHAR(14) not null,
                     TRACK_NAME           VARCHAR(64) not null,
                     FLAG_CHECK           VARCHAR(1) not null default 'Y',
                     TRACK_DATE           DATETIME not null,
                     LM_DATE              DATETIME not null,
                     primary key (TRACK_ID)
                  );";
      SQLExec($query);
      
      // POST_TRACKINFO - track detail info
      $query = "drop table if exists POST_TRACKINFO";
      SQLExec($query);
      $query = "create table POST_TRACKINFO
                  (
                     TRACK_ID             VARCHAR(14) not null,
                     OPER_DATE            DATETIME not null,
                     OPER_TYPE            INT(10) not null,
                     OPER_NAME            VARCHAR(64) not null,
                     ATTRIB_ID            INT(10),
                     ATTRIB_NAME          VARCHAR(64),
                     OPER_POSTCODE        INT(10),
                     OPER_POSTPLACE       VARCHAR(64) not null,
                     ITEM_WEIGHT          DECIMAL(10,6),
                     DECLARED_VALUE       DECIMAL(10,6),
                     DELIVERY_PRICE       DECIMAL(10,6),
                     DESTINATION_POSTCODE INT(10),
                     DELIVERY_ADDRESS     VARCHAR(255),
                     LM_DATE              DATETIME not null,
                     primary key (TRACK_ID, OPER_DATE)
                  );";
      SQLExec($query);
      $query = "alter table POST_TRACKINFO add constraint FK_POST_TRACKINFO__TRACK_ID foreign key (TRACK_ID)
      references POST_TRACK (TRACK_ID) on delete restrict on update restrict;";
      SQLExec($query);
      
      $data = "";
      parent::dbInstall($data);
   }
   
   function GetLastCheckedTracks()
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
      
      return $trackArray;
   }
   
   
   /**
    * Check tracks on russan post
    * @return operation message
    */
   function CheckPostTrack()
   {
      // start logger
      $log = Logger::getLogger(__METHOD__);
      
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
                  
                  if ($operationTypeID == 2)                   // change track status to inactive then post is arrived to your postoffice;
                     RussianPost::UpdateTrackStatus($trackID);
                  
                  $trackExist = RussianPost::isTrackInfoExist($trackID,$operationDate);
                  
                  if (!$trackExist)
                  {
                     $res = RussianPost::AddTrackDetail
                           ($trackID, $operationDate,$operationTypeID,$operationTypeName,$operationAttributeID, $operationAttribute,
                            $operationPlacePostalCode,$operationPlaceName,$itemWeight,$declaredValue,$collectOnDeliveryPrice,$destinationPostalCode,$destinationAddress);
                  }
               }
            }
            catch(Exception $e)
            {
               // TODO: сделать обработчик исключений для каждого трека.
            }
         }
         
         return true;
      }
      catch(RussianPostException $e)
      {
         $log->error("Error: " . $e->getMessage());
         return false;
      }
      
      //return $resultMessage;
   }
}
?>