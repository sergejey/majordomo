<?php

chdir(dirname(__FILE__) . '/../../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once("./load_settings.php");
include_once(DIR_MODULES . "saverestore/saverestore.class.php");

$sv=new saverestore();

global $with_extensions;

echo "<html>";
echo "<body>";


$out=array();

if ($backup) {

 $res=$sv->dump($out, 1);
 if ($res) {

   $sv->echonow("Removing temporary files ... ");
   $sv->removeTree(ROOT.'saverestore/temp');
   $sv->echonow(" OK<br/> ", 'green');


   $sv->echonow("Redirecting to main page...");
   $sv->echonow('<script language="javascript">window.top.location.href="'.ROOTHTML.'admin.php?md=panel&action=saverestore&ok_msg='.urlencode("Backup complete!").'";</script>');
 }

} else {

$res=$sv->admin($out);
$res=$sv->getLatest($out, 1);
if ($res) {
 global $restore;
 $restore='master.tgz';

 $folder='majordomo-master';

     $basename=basename($sv->url);
     if ($basename!='master.tar.gz') {
      $basename=str_replace('.tar.gz', '', $basename);
      $folder=str_replace('master', $basename, $folder);
     }


 $res=$sv->upload($out, 1);

 if ($res) {

  $sv->echonow("Removing temporary files ... ");
  $sv->removeTree(ROOT.'saverestore/temp');
  @unlink(ROOT."modules/control_modules/installed");
  $sv->echonow(" OK<br/> ", 'green');

  $sv->echonow("<b>Main system updated!</b><br/>", 'green');

  if ($with_extensions) {
   $sv->echonow("Redirecting to extensions update...");
   $sv->echonow('<script language="javascript">window.top.location.href="'.ROOTHTML.'admin.php?action=market&mode=iframe&mode2=update_all";</script>');
  } else {
   $sv->echonow("Redirecting to main page...");
   $sv->echonow('<script language="javascript">window.top.location.href="'.ROOTHTML.'admin.php?md=panel&action=saverestore&ok_msg='.urlencode("Updates Installed!").'";</script>');
  }
 }

}

}

echo "</body>";
echo "</html>";


$db->Disconnect();
