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

/**
 * Sessions handler
 * Used to work with user sessions in projects
 * @category Session
 * @package Framework
 * @author Serge Dzheigalo <jey@unit.local>
 * @copyright 2001-2004 Activeunit Inc
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link https://github.com/sergejey/majordomo/blob/master/lib/session.class.php
 */
class session
{
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
    * @param string $name Session name
    */
   public function __construct($name)
   {
      $this->name = $name;

      if (isset($_GET['no_session'])) return false;

      ini_set('session.use_only_cookies', '0'); //not only cookies

      if (!isset($_SERVER['HTTP_USER_AGENT']) || $this->checkBot($_SERVER['HTTP_USER_AGENT']))
      {
         session_set_cookie_params(0); // current browser session only
         session_name($name);
         if (gr($name)) {
            session_id(gr($name));
         }
         @session_start();

         // setting expiration time for session (the easiest way)
         $expiretime = 60 * 60 * 1; // 2 hours
         
         if (isset($_SESSION['expire']) && ($_SESSION['expire'] < time()))
            $_SESSION['DATA'] = '';

         $_SESSION['expire'] = time() + $expiretime;


         $this->data = array();

         if (isset($_SESSION['DATA']))
            $this->data = unserialize($_SESSION['DATA']);

         $this->started = 1;
         
         if (!defined('SID')) {
		       Define("SESSION_ID", session_name() . "=" . session_id());
             Define("SID", session_name() . "=" . session_id());
		   }
      }
   }

   /**
    * Store session data
    * @return void
    */
   public function save()
   {
      // saving session
      if ($this->started)
         $_SESSION['DATA'] = serialize($this->data);
   }

   /**
    * Closing current session
    * @return void
    */
   public function close()
   {
      // closing session
      if ($this->started)
      {
         $this->data = "";

         $_SESSION['DATA'] = serialize($this->data);
      }
   }

   /**
    * Checking for bot (return 0 if bot)
    * @param mixed $useragent User Agent
    * @return int
    */
   public function checkBot($useragent)
   {
      $bot_str  = "Gigabot|AESOP_com_SpiderMan|ah-ha.com|ArchitextSpider|asterias|Atomz|FAST-WebCrawler|Fluffy the spider|";
      $bot_str .= "Freshbot|GalaxyBot|Googlebot|Gulliver|ia_archiver|LNSpiderguy|Lycos_Spider|MantraAgent|Mercator|";
      $bot_str .= "MSNBOT|search.msn.com|NationalDirectory-SuperSpider|roach.smo.av.com|Scooter|Scooter2_Mercator|Scrubby|";
      $bot_str .= "Sidewinder|Slurp/2.0-KiteHourly|Slurp/2.0-OwlWeekly|Slurp/3.0-AU|Speedy Spider|teoma_agent|T-Rex|";
      $bot_str .= "Merc_resh_26_1_D-1.0|UltraSeek|Wget|ZyBorg|Yandex|yandex";

      $bots = explode('|', $bot_str);

      foreach ($bots as $bot)
      {
         if (is_integer(strpos($useragent, $bot)))
            return 0;
      }

      return 1;
   }
}
