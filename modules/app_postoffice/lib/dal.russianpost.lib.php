<?php
namespace DAL
{
   /**
    * Post Office Data Access Layer
    *
    * @version 0.2
    * @author Lutsenko D.V.
    */
   class RussianPostDAL
   {
      const FLAG_ACTIVE_TRACK   = "Y";   // Отслеживать трек-номер
      const FLAG_INACTIVE_TRACK = "N";   // Не отслеживать трек-номер
      
      /**
       * Возвращает список треков
       * @return array
       */
      public static function SelectTrack()
      {
         $track = array();
         
         $query = "select TRACK_ID, TRACK_NAME, FLAG_CHECK, TRACK_DATE
                     from POST_TRACK
                 order by TRACK_DATE desc;";
         
         $track = SQLSelect($query);
         
         return $track;
      }
      
      /**
       * Return track numbers array with flag
       * @param $userID User_ID
       * @param $checkFlag CheckFlag
       * @return array
       */
      public static function SelectTrackByFlag
        ($checkFlag)
      {
         $query = "select TRACK_ID, TRACK_NAME, FLAG_CHECK, TRACK_DATE
                     from POST_TRACK
                    where FLAG_CHECK = '" . $checkFlag . "'
                 order by TRACK_DATE desc;";
         
         $track = SQLSelect($query);
         
         return $track;
      }
      
      /**
       * Return track status(active/inactive) by track number
       * @param $trackID TrackNumber
       * @return Y/N check status
       */
      public static function GetTrackStatusByID
        ($trackID)
      {
         $query = "select FLAG_CHECK
                     from POST_TRACK
                    where TRACK_ID = '" . $trackID . "'";
         
         $result = SQLSelectOne($query);
         
         $trackCheckFlag = $result['FLAG_CHECK'];
         
         return $trackCheckFlag;
      }
      
      /**
       * Update track check status
       * @param $trackID TrackNumber
       * @return true/false
       */
      public static function UpdateTrackStatus
        ($trackID)
      {
         if ($trackID == null) return false;                                    // track number not found
         
         $trackStatus = RussianPostDAL::GetTrackStatusByID($trackID);
         if (!isset($trackStatus) || count($trackStatus) == 0) return false;    // track status is undefined
         
         $trackStatus = $trackStatus == "Y" ? "N" : "Y";                        // new track status
         $RequestDate =  date('Y-m-d H:i:s');                                   // date was track statud was udated
         $rec = array();
         $rec["FLAG_CHECK"] = $trackStatus;
         $rec["LM_DATE"]    = $RequestDate;
         $rec["TRACK_ID"]   = $trackID;
         
         $result = SQLUpdate("POST_TRACK",$rec,"TRACK_ID");
         
         return $result == 1;
      }
      
      /**
       * Delete track number
       * @param $trackID TrackNumber
       * @return true/false
       */
      public static function DeleteTrack
        ($trackID)
      {
         $query = "delete  
                     from POST_TRACK
                    where TRACK_ID = '" . $trackID . "';";
         $result = SQLExec($query);
         return $result;
      }
      
      /**
       * Add track to database
       * @param $trackID TrackNumber
       * @param $trackName TrackName
       * @return
       */
      public static function AddTrack
        ($trackID,
         $trackName)
      {
         if ($trackID       == null) return false;                    // трек номер не указан
         if ($trackName     == null) return false;                    // название трека не указано
         
         $RequestDate =  date('Y-m-d H:i:s');
         
         $rec = array();
         $rec["TRACK_ID"]   = $trackID;
         $rec["TRACK_NAME"] = $trackName;
         $rec["FLAG_CHECK"] = "Y";
         $rec["TRACK_DATE"] = $RequestDate;
         $rec["LM_DATE"]    = $RequestDate;
         
         $res = SQLInsert("POST_TRACK", $rec);
         
         return $res;
      }
      
      /**
       * Add track info from russian post to database
       * @param $trackID                     TrackNumber
       * @param $operationDate               OperationDate
       * @param $operationTypeID             OperationTypeID
       * @param $operationTypeName           OperationTypeName
       * @param $operationAttributeID        OperationAttribureID
       * @param $operationAttribute          OperationAttribte
       * @param $operationPlacePostalCode    OperationPlacePostalCode
       * @param $operationPlaceName          OperationPlaceName
       * @param $itemWeight                  ItemWeight
       * @param $declaredValue               DeclaredItemValue
       * @param $collectOnDeliveryPrice      CollectOnDeliveryPrice
       * @param $destinationPostalCode       DestinationPostalCode
       * @param $destinationAddress          DestinationAddress
       * @return                             Result(true/false)
       */
      public static function AddTrackDetail
        ($trackID,
         $operationDate,
         $operationTypeID,
         $operationTypeName,
         $operationAttributeID,
         $operationAttribute,
         $operationPlacePostalCode,
         $operationPlaceName,
         $itemWeight,
         $declaredValue,
         $collectOnDeliveryPrice,
         $destinationPostalCode,
         $destinationAddress)
      {
         if (!isset($trackID))            return false;                    // track number not exist
         if (!isset($operationDate))      return false;                    // operation date not exist
         if (is_null($operationTypeID))   return false;                    // operation type id not exist
         if (!isset($operationTypeName))  return false;                    // operation type name not exist
         if (!isset($operationPlaceName)) return false;                    // operation place name not exist
         
         $rec = array();
         $RequestDate =  date('Y-m-d H:i:s');
         
         $rec["TRACK_ID"]             = $trackID;
         $rec["OPER_DATE"]            = $operationDate;
         $rec["OPER_TYPE"]            = $operationTypeID;
         $rec["OPER_NAME"]            = $operationTypeName;
         $rec["ATTRIB_ID"]            = $operationAttributeID;
         $rec["ATTRIB_NAME"]          = $operationAttribute;
         $rec["OPER_POSTCODE"]        = $operationPlacePostalCode;
         $rec["OPER_POSTPLACE"]       = $operationPlaceName;
         $rec["ITEM_WEIGHT"]          = $itemWeight;
         $rec["DECLARED_VALUE"]       = $declaredValue;
         $rec["DELIVERY_PRICE"]       = $collectOnDeliveryPrice;
         $rec["DESTINATION_POSTCODE"] = $destinationPostalCode;
         $rec["DELIVERY_ADDRESS"]     = $destinationAddress;
         $rec["LM_DATE"]              = $RequestDate;
         
         $res = SQLInsert("POST_TRACKINFO", $rec);
         
         return $res;
      }
      
      /**
       * Select short detail about last track position
       * @param $trackID TrackNumber
       * @return array
       */
      public static function SelectTrackLastInfoByID
        ($trackID)
      {
         $query = "select OPER_DATE, ATTRIB_NAME, OPER_POSTPLACE
                     from POST_TRACKINFO
                    where TRACK_ID = '". $trackID . "'
                      and OPER_DATE = (select max(OPER_DATE) 
                                         from POST_TRACKINFO 
                                        where TRACK_ID = '". $trackID . "');";
         
         $track = SQLSelect($query);
         
         return $track;
      }
      
      /**
       * Return true if we use proxy
       * @return true/false
       */
      public static function isProxy()
      {
         $flagUseProxy = "Y";
         
         $query = "select count(*) CNT
                           from POST_PROXY 
                          where FLAG_PROXY = '" . $flagUseProxy . "'";
         $result = SQLSelectOne($query);
         
         $proxyCount = $result['CNT'];
         
         return $proxyCount > 0;
      }
      
      /**
       * Return current proxy settings
       * @return array
       */
      public static function SelectProxySettings()
      {
         $query = "select FLAG_PROXY, PROXY_HOST, PROXY_PORT, PROXY_USER, PROXY_PASSWD
                     from POST_PROXY
                 order by TRACK_DATE desc;";
         
         $proxy = SQLSelect($query);
         
         return $proxy;
      }
   }
}
?>