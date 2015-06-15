<?php

/*
Title:      Growl GNTP
URL:        http://github.com/jamiebicknell/Growl-GNTP
Author:     Jamie Bicknell
Twitter:    @jamiebicknell
 */

/*
Example usage:

include 'growl.gntp.php';

$growl = new Growl('IP Address or Hostname','optional-password');
$growl->setApplication('Application Name','Notification Name');

// Only need to use the following method on first use or change of icon
$growl->registerApplication('http://dummyimage.com/100/');

// Basic Notification
$growl->notify('Title','Content goes here!');

// Notification with Image
$growl->notify('Title','Content goes here!','http://dummyimage.com/100/');

// Notification with Image and Link
$growl->notify('Title','Content goes here!','http://dummyimage.com/100/','http://google.com');

 */

class Growl
{
   private $port = 23053;
   private $time = 5;
   
   public function Growl($host, $pass)
   {
      $this->host = $host;
      $this->pass = $pass;
      $this->salt = md5(uniqid());
      $this->application = '';
      $this->notification = '';
   }
   
   public function createHash()
   {
      $pass_hex = bin2hex($this->pass);
      $salt_hex = bin2hex($this->salt);
      $pass_bytes = pack('H*', $pass_hex);
      $salt_bytes = pack('H*', $salt_hex);
      return strtoupper('md5:' . md5(md5($pass_bytes . $salt_bytes, true)) . '.' . $salt_hex);
   }
   
   public function setApplication($application, $notification)
   {
      $this->application = $application;
      $this->notification = $notification;
   }
   
   public function registerApplication($icon = NULL)
   {
      $data  = 'GNTP/1.0 REGISTER NONE ' . $this->createHash() . "\r\n";
      $data .= 'Application-Name: ' . $this->application . "\r\n";
      
      if($icon != NULL)
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
   
   public function notify($title, $text = '', $icon = NULL, $url = NULL)
   {
      $data  = 'GNTP/1.0 NOTIFY NONE ' . $this->createHash() . "\r\n";
      $data .= 'Application-Name: ' . $this->application . "\r\n";
      $data .= 'Notification-Name: ' . $this->notification . "\r\n";
      $data .= 'Notification-Title: ' . $title . "\r\n";
      $data .= 'Notification-Text: ' . $text . "\r\n";
      $data .= 'Notification-Sticky: False' . "\r\n";
   
      if($icon != NULL)
      {
         $data .= 'Notification-Icon: ' . $icon . "\r\n";
      }
      
      if($url != NULL)
      {
         $data .= 'Notification-Callback-Target-Method: GET' . "\r\n";
         $data .= 'Notification-Callback-Target: ' . $url . "\r\n";
      }
      
      $data .= "\r\n\r\n";
      $data .= 'Origin-Software-Name: growl.gntp.php' . "\r\n";
      $data .= 'Origin-Software-Version: 1.0' . "\r\n";
      
      $this->send($data);
   }
   
   public function send($data)
   {
      $fp = fsockopen($this->host, $this->port, $errnum, $errstr, $this->time);
      
      if(!$fp)
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
