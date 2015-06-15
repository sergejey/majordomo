<?php

/**
 * @category   Hardware
 * @package    Growl
 * @author     Jamie Bicknell, Twitter: @jamiebicknell
 * @license    http://opensource.org/licenses/MIT MIT license
 * @link       http://github.com/jamiebicknell/Growl-GNTP
 */


/**
 * Growl  GNTP
 * Example usage:
 * include 'growl.gntp.php';
 * $growl = new Growl('IP Address or Hostname','optional-password');
 * $growl->setApplication('Application Name','Notification Name');
 * Only need to use the following method on first use or change of icon
 * $growl->registerApplication('http://dummyimage.com/100/');
 * Basic Notification
 * $growl->notify('Title','Content goes here!');
 * Notification with Image
 * $growl->notify('Title','Content goes here!','http://dummyimage.com/100/');
 * Notification with Image and Link
 * $growl->notify('Title','Content goes here!','http://dummyimage.com/100/','http://google.com');
 * @category   Hardware
 * @package    Growl
 * @author     Jamie Bicknell <info@jamiebicknell.com>
 * @license    http://opensource.org/licenses/MIT MIT license
 * @link       http://github.com/jamiebicknell/Growl-GNTP
 */
class Growl
{
   private $port;
   private $time;
   
   /**
    * @param mixed $host IP address or host name
    * @param mixed $pass Password
    * @return void
    */
   public function __construct($host, $pass)
   {
      $this->port = 23053;
      $this->time = 5;
      $this->host = $host;
      $this->pass = $pass;
      $this->salt = md5(uniqid());
      $this->application = '';
      $this->notification = '';
   }
   
   /**
    * Summary of __destruct
    */
   public function __destruct()
   {
      unset($this->port);
      unset($this->time);
   }
   
   /**
    * Create Hash
    * @return string
    */
   public function createHash()
   {
      $pass_hex = bin2hex($this->pass);
      $salt_hex = bin2hex($this->salt);
      $pass_bytes = pack('H*', $pass_hex);
      $salt_bytes = pack('H*', $salt_hex);
      return strtoupper('md5:' . md5(md5($pass_bytes . $salt_bytes, true)) . '.' . $salt_hex);
   }
   
   /**
    * Summary of setApplication
    * @param mixed $application  Application name
    * @param mixed $notification Notification name
    * @return void
    */
   public function setApplication($application, $notification)
   {
      $this->application = $application;
      $this->notification = $notification;
   }
   
   /**
    * Only need to use the following method on first use or change of icon
    * @param mixed $icon Icon
    * @return void
    */
   public function registerApplication($icon = null)
   {
      $data  = 'GNTP/1.0 REGISTER NONE ' . $this->createHash() . "\r\n";
      $data .= 'Application-Name: ' . $this->application . "\r\n";
      
      if ($icon != null)
      {
         $data .= 'Application-Icon: ' . $icon . "\r\n";
      }
      
      $data .= 'Notifications-Count: 1' . "\r\n\r\n";
      $data .= 'Notification-Name: ' . $this->notification . "\r\n";
      $data .= 'Notification-Enabled: True' . "\r\n";
      $data .= "\r\n\r\n";
      $data .= 'Origin-Software-Name: growl.gntp.php' . "\r\n";
      $data .= 'Origin-Software-Version: 1.0' . "\r\n";
      
      $this->send($data);
   }
   
   /**
    * Notification
    * @param mixed $title Title
    * @param mixed $text  Text
    * @param mixed $icon  Icon (optional)
    * @param mixed $url   Url (optional)
    * @return void
    */
   public function notify($title, $text = '', $icon = null, $url = null)
   {
      $data  = 'GNTP/1.0 NOTIFY NONE ' . $this->createHash() . "\r\n";
      $data .= 'Application-Name: ' . $this->application . "\r\n";
      $data .= 'Notification-Name: ' . $this->notification . "\r\n";
      $data .= 'Notification-Title: ' . $title . "\r\n";
      $data .= 'Notification-Text: ' . $text . "\r\n";
      $data .= 'Notification-Sticky: False' . "\r\n";
   
      if ($icon != null)
      {
         $data .= 'Notification-Icon: ' . $icon . "\r\n";
      }
      
      if ($url != null)
      {
         $data .= 'Notification-Callback-Target-Method: GET' . "\r\n";
         $data .= 'Notification-Callback-Target: ' . $url . "\r\n";
      }
      
      $data .= "\r\n\r\n";
      $data .= 'Origin-Software-Name: growl.gntp.php' . "\r\n";
      $data .= 'Origin-Software-Version: 1.0' . "\r\n";
      
      $this->send($data);
   }
   
   /**
    * Summary of send
    * @param mixed $data Data to send
    * @return void
    */
   public function send($data)
   {
      $fp = fsockopen($this->host, $this->port, $errnum, $errstr, $this->time);
      
      if (!$fp)
      {
         echo $errstr . ' (' . $errno . ')';
      }
      else
      {
         fwrite($fp,$data);
         fread($fp,12);
         fclose($fp);
      }
   }
}
