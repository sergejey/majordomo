<?php
/*
* @version 0.1 (auto-set)
*/


/**
* Title
*
* Description
*
* @access public
*/

 function postToTwitter($message) {

      if (!defined('SETTINGS_TWITTER_CKEY')) {
       return 0;
      }

      $consumerKey    = SETTINGS_TWITTER_CKEY;
      $consumerSecret = SETTINGS_TWITTER_CSECRET;
      $oAuthToken     = SETTINGS_TWITTER_ATOKEN;
      $oAuthSecret    = SETTINGS_TWITTER_ASECRET;

      if ($consumerKey=='' || $consumerSecret=='' || $oAuthSecret=='' || $oAuthToken=='') {
       return 0;
      }
       
      require_once(ROOT.'lib/twitter/twitteroauth.php');
       
      // create a new instance
      $tweet = new TwitterOAuth($consumerKey, $consumerSecret, $oAuthToken, $oAuthSecret);
       
      //send a tweet
      $tweet->post('statuses/update', array('status' => $message));

 }

?>