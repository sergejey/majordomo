<?php
/**
 * Russian Post tracking API PHP library
 * @author InJapan Corp. <max@injapan.ru>
 *
 ************************************************************************
 * You MUST request usage access for this API through request mailed to *
 * fc@russianpost.ru                                                    *
 ************************************************************************ 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

$russianpostRequiredExtensions = array('SimpleXML', 'curl', 'pcre');

foreach($russianpostRequiredExtensions as $russianpostExt) 
{
   if (!extension_loaded($russianpostExt))
   {
      throw new RussianPostSystemException('Required extension ' . $russianpostExt . ' is missing');
   }
}

class RussianPostAPI 
{
   /**
    * SOAP service URL
    */
   const SOAPEndpoint = 'http://voh.russianpost.ru:8080/niips-operationhistory-web/OperationHistory';
   
   protected $proxyHost;
   protected $proxyPort;
   protected $proxyAuthUser;
   protected $proxyAuthPassword;

   /**
    * Constructor. Pass proxy config here.
    * @param string $proxyHost
    * @param string $proxyPort
    * @param string $proxyAuthUser
    * @param string $proxyAuthPassword
    */
   public function __construct($proxyHost = "", $proxyPort = "", $proxyAuthUser = "", $proxyAuthPassword = "") 
   {
      $this->proxyHost         = $proxyHost;
      $this->proxyPort         = $proxyPort;
      $this->proxyAuthUser     = $proxyAuthUser;
      $this->proxyAuthPassword = $proxyAuthPassword;
   }

   /**
    * Returns tracking data
    * @param string $trackingNumber tracking number
    * @return array of RussianPostTrackingRecord
    */
   public function getOperationHistory($trackingNumber) 
   {
      $trackingNumber = trim($trackingNumber);
      if (!preg_match('/^[0-9]{14}|[A-Z]{2}[0-9]{9}[A-Z]{2}$/', $trackingNumber)) 
      {
         throw new RussianPostArgumentException('Incorrect format of tracking number: ' . $trackingNumber);
      }

      $data = $this->makeRequest($trackingNumber);
      $data = $this->parseResponse($data);

      return $data;
   }

   protected function parseResponse($raw) 
   {
      $xml = @simplexml_load_string($raw);
    
      if (!is_object($xml))
         throw new RussianPostDataException("Failed to parse XML response");

      $ns = $xml->getNamespaces(true);
      if (!($xml->children($ns['S'])->Body &&
            $records = $xml->children($ns['S'])->Body->children($ns['ns2'])->OperationHistoryData->historyRecord
           ))
         throw new RussianPostDataException("There is no tracking data in XML response");

      $out = array();
      foreach($records as $rec) 
      {
         $outRecord = new RussianPostTrackingRecord();
         $outRecord->operationType            = (string) $rec->OperationParameters->OperType->Name;
         $outRecord->operationTypeId          = (int) $rec->OperationParameters->OperType->Id;
        
         $outRecord->operationAttribute       = (string) $rec->OperationParameters->OperAttr->Name;
         $outRecord->operationAttributeId     = (int) $rec->OperationParameters->OperAttr->Id;
        
         $outRecord->operationPlacePostalCode = (string) $rec->AddressParameters->OperationAddress->Index;
         $outRecord->operationPlaceName       = (string) $rec->AddressParameters->OperationAddress->Description;

         $outRecord->destinationPostalCode    = (string) $rec->AddressParameters->DestinationAddress->Index;
         $outRecord->destinationAddress       = (string) $rec->AddressParameters->DestinationAddress->Description;

         $outRecord->operationDate            = (string) $rec->OperationParameters->OperDate;

         $outRecord->itemWeight               = round(floatval($rec->ItemParameters->Mass) / 1000, 3);
         $outRecord->declaredValue            = round(floatval($rec->FinanceParameters->Value) / 100, 2);
         $outRecord->collectOnDeliveryPrice   = round(floatval($rec->FinanceParameters->Payment) / 100, 2);

         $out[] = $outRecord;
      }

      return $out;
   }

   protected function makeRequest($trackingNumber) 
   {
      $channel = curl_init(self::SOAPEndpoint);

      $data = <<<EOD
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
   <s:Header/>
   <s:Body xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
       <OperationHistoryRequest xmlns="http://russianpost.org/operationhistory/data">
           <Barcode>$trackingNumber</Barcode>
           <MessageType>0</MessageType>
       </OperationHistoryRequest>
   </s:Body>
</s:Envelope>
EOD;

    curl_setopt_array($channel, array(
      CURLOPT_POST           => true,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CONNECTTIMEOUT => 10,
      CURLOPT_TIMEOUT        => 10,
      CURLOPT_POSTFIELDS     => $data,
      CURLOPT_HTTPHEADER     => array(
        'Content-Type: text/xml; charset=utf-8',
        'SOAPAction: ""',
      ),
    ));

    if (!empty($this->proxyHost) && !empty($this->proxyPort)) {
      curl_setopt($channel, CURLOPT_PROXY, $this->proxyHost . ':' . $this->proxyPort);
    }

    if (!empty($this->proxyAuthUser)) {
      curl_setopt($channel, CURLOPT_PROXYUSERPWD, $this->proxyAuthUser . ':' . $this->proxyAuthPassword);
    }

    $result = curl_exec($channel);
    if ($errorCode = curl_errno($channel)) {
      throw new RussianPostChannelException(curl_error($channel), $errorCode);
    }

    return $result;    
  }
}

/**
 * One record in tracking history
 */
class RussianPostTrackingRecord {
  /**
   * Operation type, e.g. Импорт, Экспорт and so on
   * @var string
   */
  public $operationType;

  /**
   * Operation type ID
   * @var int
   */
  public $operationTypeId;

  /**
   * Operation attribute, e.g. Выпущено таможней
   * @var string
   */
  public $operationAttribute;

  /**
   * Operation attribute ID
   * @var int
   */
  public $operationAttributeId;

  /**
   * ZIP code of the postal office where operation took place
   * @var string
   */
  public $operationPlacePostalCode;

  /**
   * Name of the postal office where operation took place
   * @var [type]
   */
  public $operationPlaceName;

  /**
   * Operation date in ISO 8601 format
   * @var string
   */
  public $operationDate;

  /**
   * Item wight (kg)
   * @var float
   */
  public $itemWeight;

  /**
   * Declared value of the item in rubles
   * @var float
   */
  public $declaredValue;

  /**
   * COD price of the item in rubles
   * @var float
   */
  public $collectOnDeliveryPrice;

  /**
   * Postal code of the place item addressed to
   * @var string
   */
  public $destinationPostalCode;

  /**
   * Destination address of the place item addressed to
   * @var string
   */
  public $destinationAddress;
}

class RussianPostException         extends Exception { }
class RussianPostArgumentException extends RussianPostException { }
class RussianPostSystemException   extends RussianPostException { }
class RussianPostChannelException  extends RussianPostException { }
class RussianPostDataException     extends RussianPostException { }
