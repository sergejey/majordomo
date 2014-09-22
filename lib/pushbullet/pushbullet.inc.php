<?php

class PushBullet {
        
        
        
        /*
        *       Used to get a list of devices, returns a JSON string
        *
        *       Example usage
        *       $pushbullet = new PushBullet();
        *       $devices = json_decode($pushbullet->get_devices());
        *       $deviceID = $devices->devices[0]->id;
        *       
        *       @return string
        */
        public function get_devices()
        {
                global $push_bullet_apikey;
                if(is_null($push_bullet_apikey))
                        die('Error no API key');
                else
                {
                        $ch = curl_init('https://api.pushbullet.com/v2/devices');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
                        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); 
                        curl_setopt($ch, CURLOPT_USERPWD, "$push_bullet_apikey:");
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);     // bad style, I know...
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
                        return curl_exec($ch);
                }
        }

        /*
        *       Used to push a list to the device specified by $deviceID, returns the JSON output of the API.
        *
        *       Example Usage
        *       $pushbullet = new PushBullet();
        *       $devices        = json_decode($pushbullet->get_devices());
        *       $deviceID       = $devices->devices[0]->id;
        *       $title          = 'Sample List';
        *       $list           = array(
        *                                       'item1',
        *                                       'item2',
        *                                       'item3'
        *                                       );
        *       $pushbullet->push_list($deviceID,$title,$list);
        *
        *
        *       @param int              $deviceID
        *       @param string   $title
        *       @param array    $list
        *       
        *       @return string
        *
        */
        public function push_list($deviceID,$title,$list)
        {
                return $this->push($deviceID,'list',$title,$list);
        }
        
        
        /*
        *       Used to push a URL to the device specified by $deviceID, returns the JSON output of the API.
        *
        *       Example Usage
        *       $pushbullet = new PushBullet();
        *       $devices        = json_decode($pushbullet->get_devices());
        *       $deviceID       = $devices->devices[0]->id;
        *       $title          = 'Sample List';
        *       $url            = 'http://reddit.com';
        *       $pushbullet->push_url($deviceID,$title,$url);
        *
        *
        *       @param int              $deviceID
        *       @param string   $title
        *       @param string   $url
        *
        *
        *       @return string
        *
        */
        public function push_url($deviceID,$title,$url)
        {
                return $this->push($deviceID,'link',$title,$url);
        }
        
        
        
        /*
        *       Used to push a note to the device specified by $deviceID, returns the JSON output of the API.
        *
        *       Example Usage
        *       $pushbullet = new PushBullet();
        *       $devices        = json_decode($pushbullet->get_devices());
        *       $deviceID       = $devices->devices[0]->id;
        *       $title          = 'Sample Note';
        *       $note           = 'Test note, this will hopefully be pushed to the device if everything happens correctly.';
        *       $pushbullet->push_note($deviceID,$title,$note);
        *
        *
        *       @param int              $deviceID
        *       @param string   $title
        *       @param string   $note
        *
        *
        *       @return string
        *
        */
        public function push_note($deviceID,$title,$note)
        {
                return $this->push($deviceID,'note',$title,$note);
        }
        
        
        /*
        *       Used to push a note to the device specified by $deviceID, returns the JSON output of the API.
        *
        *       Example Usage
        *       $pushbullet = new PushBullet();
        *       $devices        = json_decode($pushbullet->get_devices());
        *       $deviceID       = $devices->devices[0]->id;
        *       $title          = 'Google Inc.';
        *       $address        = '1600 Amphitheatre Pkwy  Mountain View, CA 94043';
        *       $pushbullet->push_address($deviceID,$title,$address);
        *
        *
        *       @param int              $deviceID
        *       @param string   $title
        *       @param string   $address
        *
        *
        *       @return string
        *
        */
        public function push_address($deviceID,$title,$address)
        {
                return $this->push($deviceID,'address',$title,$address);
        }
        
        
        
        
        
        /*
        *       Please ignore this, it is very messy and is the actual method for pushing.
        *
        *
        *
        *
        *
        */
        public function push($device_id,$type,$title,$data)
        {
                global $push_bullet_apikey;
                if(is_null($push_bullet_apikey))
                        die('Error no API key');
                        
                switch($type)
                {
                        case "note":{
                                //      Gather data to push a note
                                $post_data = array(
                                        'device_id'     =>$device_id,
                                        'type'          =>$type,
                                        'title'         =>$title,
                                        'body'          =>$data
                                );
                                break;
                        }
                        case "address":{
                                //      Gather data to push a address
                                $post_data = array(
                                        'device_id'     =>$device_id,
                                        'type'          =>$type,
                                        'name'          =>$title,
                                        'address'       =>$data
                                );
                                break;
                        }
                        case "list":{
                                //      Gather data to push a list
                                $post_data = array(
                                        'device_id'     =>$device_id,
                                        'type'          =>$type,
                                        'title'         =>$title,
                                        'items'         =>''//  Set up a default blank space, will add the params later.
                                );
                                //      Loop the $items manually to create the list.
                                foreach($data as $item)
                                {
                                        $post_data['items'] = $item.'&items='.$post_data['items'];
                                }
                                break;
                        }
                        case "link":{
                                //      Gather data to push a link
                                $post_data = array(
                                        'device_id'     =>$device_id,
                                        'type'          =>$type,
                                        'title'         =>$title,
                                        'url'           =>$data
                                );
                                break;
                        }
                        
                        default:{
                                //      $type was incorrect
                                die('Error, no matching PUSH type found. <br>'.$type.' is not a known push type or is not supported.');
                        }
                }
                
                foreach($post_data as $key=>$value) {
                        //      URL-ify the data to post
                        $post_data_string .= $key.'='.$value.'&';
                }
                //      Remove the final & to avoid messing with any servers.
                rtrim($post_data_string, '&');

                //      Curl the data using the apikey as auth and post data
                $ch = curl_init('https://api.pushbullet.com/v2/pushes');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC); 
                curl_setopt($ch, CURLOPT_USERPWD, "$push_bullet_apikey:");
                curl_setopt($ch, CURLOPT_POST, count($post_data));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data_string);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);     // bad style, I know...
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                
                return curl_exec($ch);
        }
        
}

/**
* Title
*
* Description
*
* @access public
*/
 function postToPushbullet($ph) {
  global $push_bullet_apikey;

  $push_bullet_apikey=trim(SETTINGS_PUSHBULLET_KEY);

  $push = new PushBullet();
  if (mb_strlen($title, 'UTF-8')>100) {
   $title=mb_substr($title, 0, 100, 'UTF-8').'...';
   $data=$ph;
  } else {
   $title=$ph;
   $data='';
  }


  if (defined('SETTINGS_PUSHBULLET_DEVICE_ID') && SETTINGS_PUSHBULLET_DEVICE_ID!='') {
   $devices=explode(', ', SETTINGS_PUSHBULLET_DEVICE_ID);
   $total=count($devices);
   for($i=0;$i<$total;$i++) {
    $push_bullet_device_id=trim($devices[$i]);
    $res=$push->push($push_bullet_device_id,'note',$title,$data);
   }
  } else {
   $res=$push->get_devices();
   $devices=json_decode($res);
   $total=count($devices);
   for($i=0;$i<$total;$i++) {
    $push_bullet_device_id=$devices[$i]->iden;
    $res=$push->push($push_bullet_device_id,'note',$title,$data);
   }
  }
 }


?>