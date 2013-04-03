<?php
/**
* Sessions handler
*
* Used to work with user sessions in projects
*
* @package framework
* @author Serge Dzheigalo <jey@unit.local>
* @copyright Activeunit Inc 2001-2004
* @version 1.1
*/
 class session {
/**
* @var string Session data
*/
  var $data; // session data
/**
* @var string Session name
*/
  var $name;

/**
* Creating new session
*
* 
*
* @param string Session name
* @access public
*/
  function session($name) {
   $this->name=$name;

   ini_set('session.use_only_cookies', '1');

   if ($this->checkBot($_SERVER['HTTP_USER_AGENT'])) {

    session_set_cookie_params(0); // current browser session only
    session_name($name);
    @session_start();

    // setting expiration time for session (the easiest way)
    $expiretime = 60*60*1; // 2 hours
    if ($_SESSION['expire'] < time()) {
     $_SESSION['DATA']='';
    }
    $_SESSION['expire'] = time()+$expiretime;


    $this->data=unserialize($_SESSION['DATA']);
    $this->started=1;
    Define("SESSION_ID", session_name()."=".session_id());
    Define("SID", session_name()."=".session_id());
   }
  }

/**
* Store session data
*
* 
*
* @access public
*/
  function save() {
   // saving session
   if ($this->started) {
    $_SESSION['DATA']=serialize($this->data); 
   }
  }
/**
* Closing current session
*
* 
*
* @access public
*/
  function close() {
   // closing session
   if ($this->started) {
    $this->data="";
    $_SESSION['DATA']=serialize($this->data);
   }
  }

/**
* Checking for bot (return 0 if bot)
*
* 
*
* @access public
*/
 function checkBot($useragent) {
  $bot_str="Gigabot|AESOP_com_SpiderMan|ah-ha.com|ArchitextSpider|asterias|Atomz|FAST-WebCrawler|Fluffy the spider|Freshbot|GalaxyBot|Googlebot|Gulliver|ia_archiver|LNSpiderguy|Lycos_Spider|MantraAgent|Mercator|MSNBOT|search.msn.com|NationalDirectory-SuperSpider|roach.smo.av.com|Scooter|Scooter2_Mercator|Scrubby|Sidewinder|Slurp/2.0-KiteHourly|Slurp/2.0-OwlWeekly|Slurp/3.0-AU|Speedy Spider|teoma_agent|T-Rex|Merc_resh_26_1_D-1.0|UltraSeek|Wget|ZyBorg|Yandex|yandex";
  $bots=explode('|', $bot_str);
  foreach($bots as $bot) {
   if (is_integer(strpos($useragent, $bot))) {
    return 0;
   }
  }
  return 1;
 }


// --------------------------------------------------------------------   
 }
?>