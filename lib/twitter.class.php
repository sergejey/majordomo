<?
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

      $consumerKey    = '9kE3stDLoA2ZW0bL3YQ9AA';
      $consumerSecret = 'jDbMLTIANNB6eGMLhiUuPiOK40FtBqbPk4fkxpGD7tE';
      $oAuthToken     = '434920820-MwfMlFa9oO9oXAqjYYQPC6kYOECZXCL1S3ZaiSG6';
      $oAuthSecret    = 'VcyildUk8gjUilFqy6Cd6g1JvL2ZKoVUwLnGNamHQ';

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