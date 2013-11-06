<?php
//$documentRoot = $_SERVER["DOCUMENT_ROOT"];

//require_once $documentRoot . '/dal/connect.php';
require_once '../../../lib/mysql.class.php';
require_once '../lib/dal.russianpost.lib.php';
require_once '../lib/russianpost.lib.php';
use DAL\RussianPostDAL as RussianPost;



if (isset($_REQUEST['postAdd']))
{
   $trackNum  = isset($_REQUEST['trackid'])   ? $_REQUEST['trackid']    : null;
   $trackName = isset($_REQUEST['trackname']) ? $_REQUEST['trackname']  : null;
   
   if ($trackNum != null || $trackName != null)
      RussianPost::AddTrack($trackNum, $trackName);
   
   $url = "/admin.php?pd=&md=panel&inst=&action=app_postoffice";
   header("Location: " . $url, true);
   die();
}





?>