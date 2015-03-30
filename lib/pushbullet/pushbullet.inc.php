<?php
class PushBullet {
        private $_apiKey;

        const URL_PUSHES         = 'https://api.pushbullet.com/v2/pushes';
        const URL_DEVICES        = 'https://api.pushbullet.com/v2/devices';
        const URL_CONTACTS       = 'https://api.pushbullet.com/v2/contacts';
        const URL_UPLOAD_REQUEST = 'https://api.pushbullet.com/v2/upload-request';
        const URL_USERS          = 'https://api.pushbullet.com/v2/users';

        public function __construct($apiKey)
        {
                $this->_apiKey = $apiKey;

                if (!function_exists('curl_init')) {
                        throw new PushBulletException('cURL library is not loaded.');
                }
        }

        // Pushes

        public function pushNote($recipient, $title, $body = NULL)
        {
                return $this->_push($recipient, 'note', $title, $body);
        }

        public function pushLink($recipient, $title, $url, $body = NULL)
        {
                return $this->_push($recipient, 'link', $title, $url, $body);
        }

        public function pushAddress($recipient, $name, $address)
        {
                return $this->_push($recipient, 'address', $name, $address);
        }

        public function pushList($recipient, $title, $items)
        {
                return $this->_push($recipient, 'list', $title, $items);
        }

        public function pushFile($recipient, $filePath, $mimeType = NULL)
        {
                return $this->_push($recipient, 'file', $filePath, $mimeType);
        }

        public function getPushHistory($modifiedAfter = 0, $cursor = NULL)
        {
                $data = array();
                $data['modified_after'] = $modifiedAfter;

                if ($cursor !== NULL) {
                        $data['cursor'] = $cursor;
                }

                return $this->_curlRequest(self::URL_PUSHES, 'GET', $data);
        }
        
        public function dismissPush($pushIden) {
                return $this->_curlRequest(self::URL_PUSHES . '/' . $pushIden, 'POST', array('dismissed' => TRUE));
        }

        public function deletePush($pushIden)
        {
                return $this->_curlRequest(self::URL_PUSHES . '/' . $pushIden, 'DELETE');
        }

        // Devices

        public function getDevices()
        {
                return $this->_curlRequest(self::URL_DEVICES, 'GET');
        }

        public function deleteDevice($deviceIden)
        {
                return $this->_curlRequest(self::URL_DEVICES . '/' . $deviceIden, 'DELETE');
        }

        // Contacts
        
        public function createContact($name, $email)
        {
                if (filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE) {
                        throw new PushBulletException('Create contact: Invalid email address.');
                }

                $queryData = array(
                        'name' => $name,
                        'email' => $email
                );

                return $this->_curlRequest(self::URL_CONTACTS, 'POST', $queryData);
        }

        public function getContacts()
        {
                return $this->_curlRequest(self::URL_CONTACTS, 'GET');
        }
        
        public function updateContact($contactIden, $name)
        {
                return $this->_curlRequest(self::URL_CONTACTS . '/' . $contactIden, 'POST', array('name' => $name));
        }

        public function deleteContact($contactIden)
        {
                return $this->_curlRequest(self::URL_CONTACTS . '/' . $contactIden, 'DELETE');
        }

        // Users

        public function getUserInformation()
        {
                return $this->_curlRequest(self::URL_USERS . '/me', 'GET');
        }

        public function updateUserPreferences($preferences)
        {
                return $this->_curlRequest(self::URL_USERS . '/me', 'POST', array('preferences' => $preferences));
        }


        private function _push($recipient, $type, $arg1, $arg2 = NULL, $arg3 = NULL)
        {
                $queryData = array();

                if (!empty($recipient)) {
                        if (filter_var($recipient, FILTER_VALIDATE_EMAIL) !== FALSE) {
                                $queryData['email'] = $recipient;
                        } else {
                                $queryData['device_iden'] = $recipient;
                        }
                }

                $queryData['type'] = $type;

                switch($type) {
                        case 'note':
                                $queryData['title'] = $arg1;
                                $queryData['body']  = $arg2;
                        break;


                        case 'link':
                                $queryData['title'] = $arg1;
                                $queryData['url']   = $arg2;

                                if ($arg3 !== NULL) {
                                        $queryData['body'] = $arg3;
                                }
                        break;


                        case 'address':
                                $queryData['name']    = $arg1;
                                $queryData['address'] = $arg2;
                        break;


                        case 'list':
                                $queryData['title'] = $arg1;
                                $queryData['items'] = $arg2;
                        break;


                        case 'file':
                                $fullFilePath = realpath($arg1);

                                if (!is_readable($fullFilePath)) {
                                        throw new PushBulletException('File: File does not exist or is unreadable.');
                                }

                                if (filesize($fullFilePath) > 25*1024*1024) {
                                        throw new PushBulletException('File: File size exceeds 25 MB.');
                                }

                                $queryData['file_name'] = basename($fullFilePath);

                                // Try to guess the MIME type if the argument is NULL
                                if ($arg2 === NULL) {
                                        $queryData['file_type'] = mime_content_type($fullFilePath);
                                } else {
                                        $queryData['file_type'] = $arg2;
                                }

                                // Request authorization to upload a file
                                $response = $this->_curlRequest(self::URL_UPLOAD_REQUEST, 'GET', $queryData);
                                $queryData['file_url'] = $response->file_url;
                                
                                // Upload the file
                                $response->data->file = '@' . $fullFilePath;
                                $this->_curlRequest($response->upload_url, 'POST', $response->data, FALSE, FALSE);
                        break;

                        default:
                                throw new PushBulletException('Unknown push type.');
                }

                return $this->_curlRequest(self::URL_PUSHES, 'POST', $queryData);
        }


        private function _curlRequest($url, $method, $data = NULL, $sendAsJSON = TRUE, $auth = TRUE)
        {
                $curl = curl_init();

                if ($method == 'GET' && $data !== NULL) {
                        $url .= '?' . http_build_query($data);
                }

                curl_setopt($curl, CURLOPT_URL, $url);

                if ($auth) {
                        curl_setopt($curl, CURLOPT_USERPWD, $this->_apiKey);
                }

                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

                if ($method == 'POST' && $data !== NULL) {
                        if ($sendAsJSON) {
                                $data = json_encode($data);
                                curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                                        'Content-Type: application/json',
                                        'Content-Length: ' . strlen($data)
                                ));
                        }

                        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }

                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);     // bad style, I know...
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); 

                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($curl, CURLOPT_HEADER, FALSE);

                $response = curl_exec($curl);

                if ($response === FALSE) {
                        $curlError = curl_error($curl);
                        curl_close($curl);
                        throw new PushBulletException('cURL Error: ' . $curlError);
                }

                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                if ($httpCode >= 400) {
                        curl_close($curl);
                        throw new PushBulletException('HTTP Error ' . $httpCode);
                }

                curl_close($curl);

                return json_decode($response);
        }
}

class PushBulletException extends Exception
{
        // Exception thrown by PushBullet
}

/**
* Title
*
* Description
*
* @access public
*/
 function postToPushbullet($ph) {

  $push_bullet_apikey=trim(SETTINGS_PUSHBULLET_KEY);
  $p = new PushBullet($push_bullet_apikey);
  if (mb_strlen($title, 'UTF-8')>100) {
   $title=mb_substr($title, 0, 100, 'UTF-8').'...';
   $data=$ph;
  } else {
   $title=$ph;
   $data='';
  }


  if (defined('SETTINGS_PUSHBULLET_DEVICE_ID')) {
   $devices=explode(',', SETTINGS_PUSHBULLET_DEVICE_ID);
   $total=count($devices);
   for($i=0;$i<$total;$i++) {
    $push_bullet_device_id=trim($devices[$i]);
    if ($push_bullet_device_id) {

                  try {
                   $res=$p->pushNote($push_bullet_device_id, $title, $data);
                  } catch(Exception $e){
                   registerError('pushbullet', get_class($e).', '.$e->getMessage());
                  }

    }
   }
  } else {
   $res=$p->getDevices();
   $devices=$res->devices;
   $total=count($devices);
   for($i=0;$i<$total;$i++) {
    if ($devices[$i]->iden) {

                  try {
                   $res=$p->pushNote($devices[$i]->iden, $title, $data);
                  } catch(Exception $e){
                   registerError('pushbullet', get_class($e).', '.$e->getMessage());
                  }

    }
   }
  }
 }


?>