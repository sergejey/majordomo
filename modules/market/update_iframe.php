<?php

chdir(dirname(__FILE__) . '/../../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

// connecting to database
$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

include_once("./load_settings.php");
include_once(DIR_MODULES . "market/market.class.php");

$mkt=new market();

echo "<html>";
echo "<body>";

$out=array();

if ($names!='') {
 $names=explode(',', $names);
}

if ($mode2=='uploaded' && $name!='') {
 $out=array();
 $mkt->admin($out);
 $filename = ROOT.'saverestore/'.$name;
 if (file_exists($filename)) {
  $mkt->echonow("Uploaded ".$name);
  $folder=str_replace('.tgz','',$name);
  $restore=$name;
  $version='Unknown version';
  $res=$mkt->upload($out, 1);
  if ($res) {
   $mkt->removeTree(ROOT.'saverestore/temp');
   //@SaveFile(ROOT.'reboot', 'updated');
   $mkt->echonow("Redirecting to main page...");
   $mkt->echonow('<script language="javascript">window.top.location.href="/admin.php?md=panel&action=market&ok_msg='.urlencode($res).'";</script>');
  }
 }
}

if ($mode2=='install' && $name!='') {
 // install/update one extension
 $out=array();
 $mkt->admin($out);
 $res=$mkt->getLatest($out, $mkt->url, $name, $mkt->version, 1);
 if ($res) {
  //$this->redirect("?mode=upload&restore=".urlencode($name.'.tgz')."&folder=".urlencode($name)."&name=".urlencode($name)."&version=".urlencode($version)."&list=".urlencode($list));
  $folder=$name;
  $restore=$name.'.tgz';
  $version=$mkt->version;
  $res=$mkt->upload($out, 1);
  if ($res) {
   $mkt->removeTree(ROOT.'saverestore/temp');
   //@SaveFile(ROOT.'reboot', 'updated');
   $mkt->echonow("Redirecting to main page...");
   $mkt->echonow('<script language="javascript">window.top.location.href="/admin.php?md=panel&action=market&ok_msg='.urlencode($res).'";</script>');
  }
 }
}

if ($mode2=='install_multiple' && $names!='') {
 // install/update multiple extensions
 $mkt->admin($out);
 $res=$mkt->updateAll($mkt->selected_plugins, 1);
 if ($res) {
  $mkt->removeTree(ROOT.'saverestore/temp');
  @SaveFile(ROOT.'reboot', 'updated');
  $mkt->echonow('<script language="javascript">window.top.location.href="/admin.php?md=panel&action=market&ok_msg='.urlencode($res).'";</script>');
 }
}

if ($mode2=='update_all') {
 // update all extensions
 $mkt->admin($out);
 $res=$mkt->updateAll($mkt->can_be_updated, 1);
 if ($res) {
  $mkt->removeTree(ROOT.'saverestore/temp');
  @SaveFile(ROOT.'reboot', 'updated');
  $mkt->echonow('<script language="javascript">window.top.location.href="/admin.php?md=panel&action=market&ok_msg='.urlencode($res).'";</script>');
 }
}

if ($mode2=='uninstall' && $name!='') {
 // remove one extension
 $res=$mkt->uninstallPlugin($name, 1);
 if ($res) {
   $mkt->echonow("Redirecting to main page...");
   $mkt->echonow('<script language="javascript">window.top.location.href="/admin.php?md=panel&action=market&ok_msg='.urlencode($res).'";</script>');
 }
}


//mode2
//$res=$mkt->getLatest($out, 1);
/*
if ($res) {
 global $restore;
 $restore='master.tgz';

 $folder='majordomo-master';

 $res=$mkt->upload($out, 1);

 if ($res) {

  $mkt->echonow("Removing temporary files ... ");
  $mkt->removeTree(ROOT.'saverestore/temp');
  $mkt->echonow(" OK<br/> ", 'green');

  $mkt->echonow("<b>Main system updated!</b><br/>", 'green');

 }

}
*/


echo "</body>";
echo "</html>";


$db->Disconnect();
