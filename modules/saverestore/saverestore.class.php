<?
/**
* Сохранение 
*
* Saverestore
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.6 (2010-08-30) WINDOWS ONLY!
*/
//
//

Define('UPDATER_URL', 'http://updates.au78.com/updates/');

class saverestore extends module {
/**
* saverestore
*
* Module class constructor
*
* @access private
*/
function saverestore() {
  $this->name="saverestore";
  $this->title="<#LANG_MODULE_SAVERESTORE#>";
  $this->module_category="<#LANG_SECTION_SYSTEM#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams() {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {


 global $err_msg;
 if ($err_msg) {
  $out['ERR_MSG']=$err_msg;
 }
 global $ok_msg;
 if ($ok_msg) {
  $out['OK_MSG']=$ok_msg;
 }

 $this->getConfig();

 if (is_dir(ROOT.'saverestore/temp')) {
  $out['CLEAR_FIRST']=1;
 }

 if ($this->mode=='savedetails') {

  global $ftp_host;
  global $ftp_username;
  global $ftp_password;
  global $ftp_folder;
  global $ftp_clear;


  if ($ftp_clear) {
     $this->config['FTP_USERNAME']='';
     $this->config['FTP_PASSWORD']='';
     $this->saveConfig();
     $this->redirect("?");
  }

  $out['FTP_HOST']=$ftp_host;
  $out['FTP_USERNAME']=$ftp_username;
  $out['FTP_PASSWORD']=$ftp_password;
  $out['FTP_FOLDER']=$ftp_folder;


  $conn_id = @ftp_connect($ftp_host); 
  if ($conn_id) {

   $login_result = @ftp_login($conn_id, $ftp_username, $ftp_password); 
   if ($login_result) {
    $systyp=ftp_systype($conn_id);

    if (!preg_match('/\/$/', $ftp_folder)) {
     $ftp_folder.='/';
    }

    if (@ftp_chdir($conn_id, $ftp_folder.'saverestore')) {
     $this->config['FTP_HOST']=$ftp_host;
     $this->config['FTP_USERNAME']=$ftp_username;
     $this->config['FTP_PASSWORD']=$ftp_password;
     $this->config['FTP_FOLDER']=$ftp_folder;
     $this->saveConfig();
     $this->redirect("?");
    } else {
     $out['FTP_ERR']='Incorrect folder ('.$ftp_folder.')';
    }
   } else {
    $out['FTP_ERR']='Incorrect username/password';
   }

   ftp_close($conn_id);

  } else {
   $out['FTP_ERR']='Cannot connect to host ('.$ftp_host.')';
  }

 }

 if ($this->mode!='savedetails') {
  $out['FTP_HOST']=$this->config['FTP_HOST'];
  $out['FTP_USERNAME']=$this->config['FTP_USERNAME'];
  $out['FTP_PASSWORD']=$this->config['FTP_PASSWORD'];
  $out['FTP_FOLDER']=$this->config['FTP_FOLDER'];
 }

// if ($this->mode=='' || $this->mode=='upload' || $this->mode=='savedetails') {
  $method='ftp';
  if( function_exists('getmyuid') && function_exists('fileowner') ){
  $temp_file = tempnam ("./saverestore/", "FOO");
  if (file_exists($temp_file)) {
   $method = 'direct';
   unlink($temp_file);
  }
  }
  $out['METHOD']=$method;
  $this->method=$method;
// }

 if ($this->mode=='clear') {
  $this->removeTree(ROOT.'saverestore/temp');
  $this->redirect("?err_msg=".urlencode($err_msg)."&ok_msg=".urlencode($ok_msg));
 }


 if ($this->mode=='checksubmit') {
  $this->checkSubmit($out);
 }

 if ($this->mode=='uploadupdates') {
  $this->uploadUpdates($out);
 }

 if ($this->mode=='checkupdates') {
  $this->checkupdatesSVN($out);
 }

 if ($this->mode=='downloadupdates') {
  $this->downloadupdatesSVN($out);
 }

 if ($this->mode=='checkapps') {
  $this->checkApps($out);
 }

 if ($this->mode=='downloadapps') {
  $this->downloadApps($out);
 }


 if ($this->mode=='upload') {
  $this->upload($out);
  //$this->redirect("?mode=clear");
 }
 if ($this->mode=='dump') {
  $this->dump($out);
  $this->redirect("?mode=clear");
  //$this->redirect("?");
 }

 if ($this->mode=='delete') {
  global $file;
  @unlink(ROOT.'saverestore/'.$file);
  $this->redirect("?");
 }

 if ($this->mode=='getlatest') {
  $this->getLatest($out);
 }


  $source=ROOT.'saverestore';
  if ($dir = @opendir($source)) { 
  while (($file = readdir($dir)) !== false) { 
    if (!Is_Dir($source."/".$file) && ($file!='.') && ($file!='..')) {
     $tmp=array();
     $tmp['FILENAME']=$file;
     $tmp['FILESIZE']=number_format((filesize($source."/".$file)/1024/1024), 2);
     $out['FILES'][]=$tmp;
    }
  }
 }



}


 /**
 * Title
 *
 * Description
 *
 * @access public
 */
  function getLatest(&$out) {

   $url='https://github.com/sergejey/majordomo/archive/master.tar.gz';

   set_time_limit(0);

   if (!is_dir(ROOT.'saverestore')) {
    @umask(0);
    @mkdir(ROOT.'saverestore', 0777);
   }

    $filename=ROOT.'saverestore/master.tgz';

    @unlink(ROOT.'saverestore/master.tgz');
    @unlink(ROOT.'saverestore/master.tar');

    $f = fopen($filename, 'wb');
    if ($f == FALSE){
      $this->redirect("?err_msg=".urlencode("Cannot open ".$filename." for writing"));
    } 
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_TIMEOUT, 600);
   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);     
   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); 
   curl_setopt($ch, CURLOPT_FILE, $f); 
   $incoming = curl_exec($ch);

   curl_close($ch);
   @fclose($f);

   if (file_exists($filename)) {

    global $code;
    global $data;
    global $design;
    $code=1;
    $data=0;
    $design=1;
    $out['BACKUP']=1;
    $this->dump($out);
    $this->removeTree(ROOT.'saverestore/temp');
    $this->redirect("?mode=upload&restore=".urlencode('master.tgz')."&folder=".urlencode('majordomo-master'));
   } else {
    $this->redirect("?err_msg=".urlencode("Cannot download ".$url));
   }
  }


/**
* Title
*
* Description
*
* @access public
*/
 function uploadUpdates(&$out) {
  global $to_submit;
  global $pack_folders;


  $total=count($to_submit);

  umask(0);

  $copied_dirs=array();

  if (mkdir(ROOT.'saverestore/temp', 0777)) {
   for($i=0;$i<$total;$i++) {
    $this->copyFile(ROOT.$to_submit[$i], ROOT.'saverestore/temp/'.$to_submit[$i]);
    if (is_array($pack_folders) && in_array($to_submit[$i], $pack_folders) && !$copied_dirs[dirname(ROOT.'saverestore/temp/'.$to_submit[$i])]) {
     $this->copyTree(dirname(ROOT.$to_submit[$i]), dirname(ROOT.'saverestore/temp/'.$to_submit[$i]));
     $copied_dirs[dirname(ROOT.'saverestore/temp/'.$to_submit[$i])]=1;
    }
    if (file_exists(dirname(ROOT.'saverestore/temp/'.$to_submit[$i]).'/installed')) {
     @unlink(dirname(ROOT.'saverestore/temp/'.$to_submit[$i]).'/installed');
    }
   }
  }

   // packing into tar.gz
   $tar_name='submit_'.date('Y-m-d__h-i-s').'.tgz';

   chdir(ROOT.'saverestore/temp');
   exec('tar cvzf ../'.$tar_name.' .');
   chdir('../../');
   $this->removeTree(ROOT.'saverestore/temp');

   // sending to remote server

  $repository_url=UPDATER_URL;

  if (defined('UPDATES_REPOSITORY_NAME')) {
   $repository_name=UPDATES_REPOSITORY_NAME;
  } else {
   $repository_name='default';
  }

   $to_send=array();
   global $name;
   $to_send['NAME']=$name;
   setCookie('SUBMIT_NAME', $name, 0, '/');

   global $email;
   $to_send['EMAIL']=$email;
   setCookie('SUBMIT_EMAIL', $email, 0, '/');

   global $description;
   $to_send['DESCRIPTION']=$description;
   $to_send['FILES']=$to_submit;

   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $repository_url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_TIMEOUT, 600);
   curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'POST');

   $post = array(
      "file"=>"@".ROOT.'saverestore/'.$tar_name,
      "mode"=>"upload_updates",
      "repository"=>$repository_name,
      "host"=>$_SERVER['HTTP_HOST'],
      "data"=>serialize($to_send)
   );
   curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

   //curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=upload_updates&repository=".$repository_name."&host=".$_SERVER['HTTP_HOST']."&data=".$to_send);
 
   $incoming = curl_exec($ch);

   curl_close($ch);

   $result=unserialize($incoming);

   $ok_msg='Error sending files to repository! ';//.$incoming
   if ($result['MESSAGE']) {
    $ok_msg=$result['MESSAGE'];
   }



  if ($result['STATUS']=='OK') {
   $this->redirect("?mode=clear&ok_msg=".urlencode($ok_msg));
  } else {
   $this->redirect("?mode=clear&err_msg=".urlencode($ok_msg));
  }
  
  //exit;
  

 }

/**
* Title
*
* Description
*
* @access public
*/
 function checkSubmit(&$out) {

  $res1=$this->checkEFiles('.', 0);
  $res2=$this->checkEFiles('./modules', 1);
  $res3=$this->checkEFiles('./templates', 1);
  $res4=$this->checkEFiles('./lib', 0);

  $res=array_merge($res1, $res2, $res3, $res4);

  $to_send=serialize($res);

  $repository_url=UPDATER_URL;

  if (defined('UPDATES_REPOSITORY_NAME')) {
   $repository_name=UPDATES_REPOSITORY_NAME;
  } else {
   $repository_name='default';
  }

   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $repository_url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_TIMEOUT, 600);
   curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'POST');

   curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=check_submit&repository=".$repository_name."&host=".$_SERVER['HTTP_HOST']."&data=".$to_send);
 
   $incoming = curl_exec($ch);

   curl_close($ch);

   //echo $incoming;exit;


   $result=unserialize($incoming);

   //echo $repository_url;
   //echo($result);exit;

   if ($result['STATUS']!='OK') {
    $out['ERROR_CHECK']=1;
    if ($result['MESSAGE']) {
     $out['ERROR_MESSAGE']=$result['MESSAGE'];
    } else {
     $out['ERROR_MESSAGE']='Cannot connect to updates server';
    }
   } else {
    $out['OK_CHECKSUBMIT']=1;

    //print_r($result['TO_SUBMIT']);exit;

    if (is_array($result['TO_SUBMIT'])) {
     foreach($result['TO_SUBMIT'] as $f=>$v) {
      $tmp=array('FILE'=>$f, 'VERSION'=>$v, 'L_VERSION'=>$res[$f]);
      if (preg_match('/\/modules\/.+\/.+/is', $f) || preg_match('/\/templates\/.+\/.+/is', $f)) {
       $tmp['PACK_FOLDER']=1;
      }
      $out['TO_SUBMIT'][]=$tmp;
     }
    } else {
     $out['NO_SUBMIT']=1;
    }
   }

   $out['NAME']=$_COOKIE['SUBMIT_NAME'];
   $out['EMAIL']=$_COOKIE['SUBMIT_EMAIL'];
 
 }


/**
* Title
*
* Description
*
* @access public
*/
 function downloadUpdates(&$out) {
  global $to_update;

  $repository_url=UPDATER_URL;

  if (defined('UPDATES_REPOSITORY_NAME')) {
   $repository_name=UPDATES_REPOSITORY_NAME;
  } else {
   $repository_name='default';
  }

  // preparing update

   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $repository_url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_TIMEOUT, 600);
   curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'POST');
   curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=prepare&repository=".$repository_name."&host=".$_SERVER['HTTP_HOST']."&data=".serialize($to_update));
   $incoming = curl_exec($ch);
   curl_close($ch);

   $res=unserialize($incoming);

   if ($res['STATUS']=='OK' && $res['DOWNLOAD_FILE']!='') {
    // downloading update
    $filename=ROOT.'saverestore/'.$res['DOWNLOAD_FILE'];
    $f = fopen($filename, 'wb');
    if ($f == FALSE){
      //print "File not opened<br>";
      //exit;
      $this->redirect("?err_msg=".urlencode("Cannot open ".$filename." for writing"));
    } 
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $repository_url);
   curl_setopt($ch, CURLOPT_TIMEOUT, 600);
   curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'POST');
   curl_setopt($ch, CURLOPT_FILE, $f); 

   curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=download&repository=".$repository_name."&host=".$_SERVER['HTTP_HOST']."&file=".$res['DOWNLOAD_FILE']);
 
   $incoming = curl_exec($ch);

   curl_close($ch);
   @fclose($f);

   if (file_exists($filename) && filesize($filename)>0) {
    // backing up current code version
    global $code;
    global $data;
    $code=1;
    $data=1;
    $out['BACKUP']=1;
    $this->dump($out);
    $this->removeTree(ROOT.'saverestore/temp');
    // installing update
    $this->redirect("?mode=upload&restore=".urlencode($res['DOWNLOAD_FILE']));
   } else {
    $this->redirect("?err_msg=".urlencode("Error downloading update"));
   }
   }
   
   exit;

 }

/**
* Title
*
* Description
*
* @access public
*/
 function downloadApps(&$out) {
  global $to_install;


  $repository_url=UPDATER_URL;

  if (defined('UPDATES_REPOSITORY_NAME')) {
   $repository_name=UPDATES_REPOSITORY_NAME;
  } else {
   $repository_name='default';
  }

  // preparing update

   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $repository_url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_TIMEOUT, 600);
   curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'POST');
   curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=prepareapps&repository=".$repository_name."&host=".$_SERVER['HTTP_HOST']."&data=".serialize($to_install));
   $incoming = curl_exec($ch);
   curl_close($ch);

   //echo $incoming;exit;

   $res=unserialize($incoming);

   if ($res['STATUS']=='OK' && $res['DOWNLOAD_FILE']!='') {
    // downloading update
    $filename=ROOT.'saverestore/'.$res['DOWNLOAD_FILE'];
    $f = fopen($filename, 'wb');
    if ($f == FALSE){
      //print "File not opened<br>";
      //exit;
      $this->redirect("?err_msg=".urlencode("Cannot open ".$filename." for writing"));
    } 
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $repository_url);
   curl_setopt($ch, CURLOPT_TIMEOUT, 600);
   curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'POST');
   curl_setopt($ch, CURLOPT_FILE, $f); 

   curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=download&repository=".$repository_name."&host=".$_SERVER['HTTP_HOST']."&file=".$res['DOWNLOAD_FILE']);
 
   $incoming = curl_exec($ch);

   curl_close($ch);
   @fclose($f);

   if (file_exists($filename) && filesize($filename)>0) {
    // backing up current code version
    global $code;
    global $data;
    $code=1;
    $data=1;
    $out['BACKUP']=1;
    $this->dump($out);
    $this->removeTree(ROOT.'saverestore/temp');
    // installing update
    $this->redirect("?mode=upload&restore=".urlencode($res['DOWNLOAD_FILE']));
   } else {
    $this->redirect("?err_msg=".urlencode("Error downloading update"));
   }
   }
   
   exit;

 }


/**
* Title
*
* Description
*
* @access public
*/                  
 function checkApps(&$out) {

  $res=array();
  $d='./modules';
  if ($dir = @opendir($d)) { 
   while (($file = readdir($dir)) !== false) { 
    if (is_dir($d.'/'.$file) 
        && ($file!='..') 
        && ($file!='.') 
        && ($file!='control_access') 
        && ($file!='control_modules') 
       ) {
     $res[]=$file;
    }
   }
  }

  $to_send=serialize($res);

  $repository_url=UPDATER_URL;

  if (defined('UPDATES_REPOSITORY_NAME')) {
   $repository_name=UPDATES_REPOSITORY_NAME;
  } else {
   $repository_name='default';
  }

   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $repository_url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_TIMEOUT, 600);
   curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'POST');

   curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=checkapps&repository=".$repository_name."&host=".$_SERVER['HTTP_HOST']."&data=".$to_send);
 
   $incoming = curl_exec($ch);

   curl_close($ch);

   //echo $incoming;exit;

   $result=unserialize($incoming);

   if ($result['STATUS']!='OK') {

    if ($result['MESSAGE']) {
     $out['ERROR_MESSAGE']=$result['MESSAGE'];
    } else {
     $out['ERROR_MESSAGE']='Cannot connect to updates server';
    }
    $this->redirect("?err_msg=".urlencode($out['ERROR_MESSAGE']));

   } else {
    $out['OK_BROWSE']=1;
    if (is_array($result['TO_INSTALL'])) {
     $out['TO_INSTALL']=$result['TO_INSTALL'];
     /*
     foreach($result['TO_UPDATE'] as $f=>$v) {
      $out['TO_INSTALL'][]=array('FILE'=>$f, 'VERSION'=>$v);
     }
     */
    } else {
     $out['NO_MODULES']=1;
    }
   }
 
 }


/**
* Title
*
* Description
*
* @access public
*/
 function downloadUpdatesSVN(&$out) {

  global $code;
  global $data;
  $code=1;
  $data=0;
  $out['BACKUP']=1;
  $this->dump($out);
  $this->removeTree(ROOT.'saverestore/temp');

  include_once DIR_MODULES.'saverestore/phpsvnclient.php';
  $url = 'http://majordomo-sl.googlecode.com/svn/';
  $phpsvnclient = new phpsvnclient($url);
  set_time_limit(0);
  global $to_update;

  $total=count($to_update);
  for($i=0;$i<$total;$i++) {
   $path='trunk/'.$to_update[$i];
   $file_content = $phpsvnclient->getFile($path);
   if (!is_dir(dirname(ROOT.$to_update[$i]))) {
    @mkdir(dirname(ROOT.$to_update[$i]), 0777);
   }
   @SaveFile(ROOT.$to_update[$i], $file_content);
   if (file_exists(dirname(ROOT.$to_update[$i]).'/installed')) {
    @unlink(dirname(ROOT.$to_update[$i]).'/installed');
   }
  }

  $this->redirect("?ok_msg=".urlencode('Files have been updated!'));
  
 }

/**
* Title
*
* Description
*
* @access public
*/
 function checkUpdatesSVN(&$out) {
  include_once DIR_MODULES.'saverestore/phpsvnclient.php';

  $url = 'http://majordomo-sl.googlecode.com/svn/';

  $phpsvnclient = new phpsvnclient($url);

  set_time_limit(0);
  //$phpsvnclient->createOrUpdateWorkingCopy('trunk/', ROOT.'saverestore/temp', true);

  $cached_name=ROOT.'saverestore/svn_tree.txt';
  if (!file_exists($cached_name) || (time()-filemtime($cached_name)>8*60*60)) {
   $directory_tree = $phpsvnclient->getDirectoryTree('/trunk/');
   SaveFile($cached_name, serialize($directory_tree));
  } else {
   $directory_tree=unserialize(LoadFile($cached_name));
  }

  $updated=array();
  $total=count($directory_tree);
  for($i=0;$i<$total;$i++) {
   $item=$directory_tree[$i];
   if ($item['type']!='file' || $item['path']=='trunk/config.php') {
    continue;
   }
   $filename=str_replace('trunk/', ROOT, $item['path']);
   @$fsize=filesize($filename);
   $r_rfsize=$item['size'];
   if ($fsize!=$r_rfsize || !file_exists($filename)) {
    $updated[]=$item;
   }

  }


  $out['OK_CHECK']=1;
  if (!$updated[0]) {
   $out['NO_UPDATES']=1; 
  } else {
     foreach($updated as $item) {
      $item['path']=str_replace('trunk/', '', $item['path']);
      $out['TO_UPDATE'][]=array('FILE'=>$item['path'], 'VERSION'=>$item['version'].' ('.$item['last-mod'].')');
     }
  }

 }
/**
* Title
*
* Description
*
* @access public
*/
 function checkUpdates(&$out) {

  $res1=$this->checkEFiles('.', 0);
  $res2=$this->checkEFiles('./modules', 1);
  $res3=$this->checkEFiles('./templates', 1);
  $res4=$this->checkEFiles('./lib', 0);

  $res=array_merge($res1, $res2, $res3, $res4);

  $to_send=serialize($res);

  $repository_url=UPDATER_URL;

  if (defined('UPDATES_REPOSITORY_NAME')) {
   $repository_name=UPDATES_REPOSITORY_NAME;
  } else {
   $repository_name='default';
  }

   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $repository_url);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_TIMEOUT, 600);
   curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'POST');

   curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=check&repository=".$repository_name."&host=".$_SERVER['HTTP_HOST']."&data=".$to_send);
 
   $incoming = curl_exec($ch);

   curl_close($ch);

   //echo $incoming;exit;


   $result=unserialize($incoming);

   //echo $repository_url;
   //echo($result);exit;

   if ($result['STATUS']!='OK') {
    $out['ERROR_CHECK']=1;
    if ($result['MESSAGE']) {
     $out['ERROR_MESSAGE']=$result['MESSAGE'];
    } else {
     $out['ERROR_MESSAGE']='Cannot connect to updates server';
    }
   } else {
    $out['OK_CHECK']=1;
    if (is_array($result['TO_UPDATE'])) {
     foreach($result['TO_UPDATE'] as $f=>$v) {
      $out['TO_UPDATE'][]=array('FILE'=>$f, 'VERSION'=>$v);
     }
    } else {
     $out['NO_UPDATES']=1;
    }
   }


   //exec('curl ...')

 }

/**
* Title
*
* Description
*
* @access public
*/
 function checkEFiles($d, $max_level=0, $level=0) {


  $res=array();

  if (!is_dir($d)) {
   return $res;
  }

  if ($dir = @opendir($d)) { 
  while (($file = readdir($dir)) !== false) { 
    if (Is_Dir($d."/".$file) && ($file!='.') && ($file!='..')) {
      //echo "<br>Dir ".$d."/".$file;
      if ($level<$max_level) {
       $res2=$this->checkEFiles($d."/".$file, $max_level, ($level+1));
       if (is_array($res2)) {
        $res=array_merge($res, $res2);
       }
      }
    } elseif (Is_File($d."/".$file) && 
    (preg_match('/\.php$/', $file) || preg_match('/\.css$/', $file) || preg_match('/\.html$/', $file) || preg_match('/\.js$/', $file))) {

     if ($file=='config.php') {
      continue;
     }

     //echo "<br>".$d.'/'.$file;
     $version='';
     $content=LoadFile($d.'/'.$file);
     if (preg_match('/@version (.+?)\n/is', $content, $m)) {
      $version=trim($m[1]);
      //echo "<br>".$d.'/'.$file.' - '.$version;
     } elseif (preg_match('/\.class\.php$/is', $file)) {
      // echo "<br>".$d.'/'.$file.' - '.'unknown';
      //$version='unknown';
     }

     if ($version!='') {
      $res[$d.'/'.$file]=$version;
     }

    }

  }
  closedir($dir); 
  }
  return $res;

}

 /**
 * Title
 *
 * Description
 *
 * @access public
 */
  function extractVersion($s) {
   $o_version=preg_replace('/\(.+/', '', $s);
   $o_version=preg_replace('/[^\d]/', '', $o_version);
   $o_version=(float)substr($o_version, 0, 1).'.'.substr($o_version, 1, strlen($o_version)-1);
   return $o_version;
  }

/**
* Title
*
* Description
*
* @access public
*/
 function isNewer($o_version, $r_version) {
   
   $o_version=$this->extractVersion($o_version);
   $r_version=$this->extractVersion($r_version);

   //$r_version+=0.1; // just for testing
   //echo $o_version.' to '.$r_version."<br>";

   if ($o_version<$r_version) {
    return 1;
   }

   return 0;
 }

/**
* Title
*
* Description
*
* @access public
*/
function getLocalFilesTree($dir, $pattern, $ex_pattern, &$log, $verbose) {
  $res=array();

  $destination=$dir;

  if (!Is_Dir($destination)) {
    return $res; // cannot create destination path
  }

 if ($dir = @opendir($destination)) { 
  while (($file = readdir($dir)) !== false) { 
    if (Is_Dir($destination."/".$file) && ($file!='.') && ($file!='..')) {
     $sub_ar=$this->getLocalFilesTree($destination."/".$file, $pattern, $ex_pattern, $log, $verbose);
     $res = array_merge ($res, $sub_ar); 
    } elseif (Is_File($destination."/".$file)) {

      $fl=array();
      $fl['FILENAME']=str_replace('//', '/', $destination."/".$file);
      $fl['FILENAME_SHORT']=str_replace('//', '/', $file);
      $fl['SIZE']=filesize($fl['FILENAME']);
      if (preg_match('/'.$pattern.'/is', $fl['FILENAME_SHORT']) && ($ex_pattern=='' || !preg_match('/'.$ex_pattern.'/is', $fl['FILENAME_SHORT']))) {
       $res[]=$fl;
      }
    }
  }     
  closedir($dir); 
 }
 return $res;
}


/**
* Title
*
* Description
*
* @access public
*/
 function upload(&$out) {

  set_time_limit(0);
  global $restore;
  global $file;
  global $file_name;
  global $folder;

  if (!$folder) {
   $folder='/.';
  } else {
   $folder='/'.$folder;
  }

  if ($restore!='') {
   //$file=ROOT.'saverestore/'.$restore;
   $file=$restore;
  } elseif ($file!='') {
   copy($file, ROOT.'saverestore/'.$file_name);
   //$file=ROOT.'saverestore/'.$file_name;
   $file=$file_name;
  }

  umask(0);
  @mkdir(ROOT.'saverestore/temp', 0777);

  if ($file!='') { // && mkdir(ROOT.'saverestore/temp', 0777)

       chdir(ROOT.'saverestore/temp');

       if (substr(php_uname(), 0, 7) == "Windows") {
       // for windows only
       exec(DOC_ROOT.'/gunzip ../'.$file, $output, $res);
       exec(DOC_ROOT.'/tar xvf ../'.str_replace('.tgz', '.tar', $file), $output, $res);
       //@unlink('../'.str_replace('.tgz', '.tar', $file));
       } else {
        exec('tar xzvf ../../'.$file, $output, $res);
       }
       @unlink(ROOT.'saverestore/temp'.$folder.'/config.php');

       //print_r($output);exit;

       if (1) {

        chdir('../../');

        if ($this->method=='direct') {
  
         // UPDATING FILES DIRECTLY
         $this->copyTree(ROOT.'saverestore/temp'.$folder, ROOT, 1); // restore all files


        } elseif ($this->method=='ftp') {

  // UPDATING FILES BY FTP


  $conn_id = @ftp_connect($this->config['FTP_HOST']); 
  if ($conn_id) {

   $login_result = @ftp_login($conn_id, $this->config['FTP_USERNAME'], $this->config['FTP_PASSWORD']); 
   if ($login_result) {
    $systyp=ftp_systype($conn_id);
      
      

    if (@ftp_chdir($conn_id, $this->config['FTP_FOLDER'].'saverestore')) {
     @ftp_chdir($conn_id, $this->config['FTP_FOLDER']);
     // ok, we're in. updating!
        $log='';
        $files=$this->getLocalFilesTree(ROOT.'saverestore/temp'.$folder, '.+', 'installed', $log, 0);
        $total=count($files);
        $modules_processed=array();
        for($i=0;$i<$total;$i++) {
          $file=$files[$i];
          $file['REMOTE_FILENAME']=preg_replace('/^'.preg_quote(ROOT.'saverestore/temp/'.$folder, '/').'/is', $this->config['FTP_FOLDER'], $file['FILENAME']);
          $file['REMOTE_FILENAME']=str_replace('//', '/', $file['REMOTE_FILENAME']);
          $res_f=$this->ftpput( $conn_id, $file['REMOTE_FILENAME'], $file['FILENAME'], FTP_BINARY);

          if (preg_match('/\.class\.php$/', basename($file['FILENAME'])) && !$modules_processed[dirname($file['REMOTE_FILENAME'])]) {
           // if this a module then we should update attributes for folder and remove 'installed' file
           $modules_processed[dirname($file['REMOTE_FILENAME'])]=1;
           @ftp_site($conn_id,"CHMOD 0777 ".dirname($file['REMOTE_FILENAME']));
           @ftp_delete($conn_id, dirname($file['REMOTE_FILENAME']).'/installed');
          }
        }


    } else {
     $out['FTP_ERR']='Incorrect folder ('.$ftp_folder.')';
     
    }
   } else {
    $out['FTP_ERR']='Incorrect username/password';
    
   }

   ftp_close($conn_id);

  } else {
   $out['FTP_ERR']='Cannot connect to host ('.$ftp_host.')';
   $this->redirect("?err_msg=".urlencode($out['FTP_ERR']));
  }


        }

        //if (is_dir(ROOT.'saverestore/temp/'.$folder.'modules')) {
        // code restore
        $source=ROOT.'modules';
        if ($dir = @opendir($source)) { 
          while (($file = readdir($dir)) !== false) { 
           if (Is_Dir($source."/".$file) && ($file!='.') && ($file!='..')) { // && !file_exists($source."/".$file."/installed")
            @unlink(ROOT."modules/".$file."/installed");
           }
          }
         }
         @unlink(ROOT."modules/control_modules/installed");
         @SaveFile(ROOT.'reboot', 'updated');

        //}



        if (file_exists(ROOT.'saverestore/temp'.$folder.'/dump.sql')) {
         // data restore
         $this->restoredatabase(ROOT.'saverestore/temp'.$folder.'/dump.sql');
        }

        $this->redirect("?mode=clear&ok_msg=".urlencode("Updates Installed!"));

       }
  }

  /*
       require 'Tar.php';
       $tar_object = new Archive_Tar($file);
       if ($tar_object->extract(ROOT.'skins/'.$basename)) {
        $out['OK_EXT']=1;
       } else {
        $out['ERR_FORMAT']=1;
       }
  */

 }

/**
* Title
*
* Description
*
* @access public
*/
 function dump(&$out) {
  
  
  if (mkdir(ROOT.'saverestore/temp', 0777)) {   
   // DESIGN
   global $design;
   if ($design) {
    $tar_name.='design_';
    $this->copyTree(ROOT.'templates', ROOT.'saverestore/temp/templates');
    $this->copyTree(ROOT.'img', ROOT.'saverestore/temp/img');
    $this->copyTree(ROOT.'js', ROOT.'saverestore/temp/js');

    
    $pt=array('\.css');
    $this->copyFiles(ROOT, ROOT.'saverestore/temp', 0, $pt);

    $pt=array('\.swf');
    $this->copyFiles(ROOT, ROOT.'saverestore/temp', 0, $pt);

    $pt=array('\.htc');
    $this->copyFiles(ROOT, ROOT.'saverestore/temp', 0, $pt);

   }

   // CODE
   global $code;
   if ($code) {
    $tar_name.='code_';
    
    $this->copyTree(ROOT.'lib', ROOT.'saverestore/temp/lib');
    $this->copyTree(ROOT.'modules', ROOT.'saverestore/temp/modules');

    $pt=array('\.php');
    $this->copyFiles(ROOT, ROOT.'saverestore/temp', 0, $pt);
    @unlink(ROOT.'saverestore/temp/config.php');

    $this->copyTree(ROOT.'forum', ROOT.'saverestore/temp/forum');
    @unlink(ROOT.'saverestore/temp/forum/config.php');

    if (!$design) {
     $this->copyTree(ROOT.'js', ROOT.'saverestore/temp/js');
     $this->copyTree(ROOT.'templates', ROOT.'saverestore/temp/templates');
    }
   }

   // DATA
   global $data;
   if ($data) {
    $tar_name.='data_';
    $this->copyTree(ROOT.'cms', ROOT.'saverestore/temp/cms');
    $this->backupdatabase(ROOT.'saverestore/temp/dump.sql');
   }

   // FILES
   global $files;
   if ($files) {
    $tar_name.='files_';
    //$this->copyTree(ROOT.'photos', ROOT.'saverestore/temp/photos');
   }


   // packing into tar.gz
   if (substr(php_uname(), 0, 7) == "Windows") {
    $tar_name.=date('Y-m-d__h-i-s').'.tar';
   } else {
    $tar_name.=date('Y-m-d__h-i-s').'.tgz';
   }

   if ($out['BACKUP']) {
    $tar_name='backup_'.$tar_name;
   }

   if (substr(php_uname(), 0, 7) == "Windows") {
    exec('tar.exe  --strip-components=2 -cvf ./saverestore/'.$tar_name.' ./saverestore/temp/');
   } else {
    chdir(ROOT.'saverestore/temp');
    exec('tar cvzf ../'.$tar_name.' .');
    chdir('../../');
   }


  }
 }

/**
* Title
*
* Description
*
* @access public
*/
 function restoredatabase($filename) {
   $data=LoadFile($filename);
   $data=str_replace("\r", "", $data);
   $data.="\n";
   $query=explode(";\n",$data);
   for ($i=0;$i < count($query)-1;$i++) {
    if ($query[$i]{0}!="#") SQLExec($query[$i]);
   }
 }

/**
* Title
*
* Description
*
* @access public
*/
 function backupdatabase($filename) {
  global $db;

  $tables1 = SQLSelect("SHOW TABLES;");
  foreach($tables1 as $t) {
   foreach($t as $k=>$v) {
    $tables[]=$v;
   }
  }


  $ignores=array('statistic', 'pot_accesslog', 'pot_add_data', 'pot_documents', 'pot_exit_targets', 'pot_hostnames', 
                 'pot_operating_systems', 'pot_referers', 'pot_search_engines', 'pot_user_agents', 'pot_visitors');
  for($i=0;$i<count($ignores);$i++) {
   $ignore[$ignores[$i]]=1;
  }

  $newfile="";
  for($i=0;$i<count($tables);$i++) {
   $table=$tables[$i];
   if (!IsSet($ignore[$table])) {
           $newfile .= "\n# ----------------------------------------------------------\n#\n";
           $newfile .= "# structur for table '$table'\n#\n";
           $newfile .= $db->get_mysql_def($table);
           $newfile .= "\n\n";
           $newfile .= "#\n# data for table '$table'\n#\n";
           $newfile .= $db->get_mysql_content($table);
           $newfile .= "\n\n";   
   }
  }

  //$filename=ROOT.DIR_BACKUP."/".date("M_d_Y_H_i").".sql";
  $fp = fopen ($filename,"w");
  fwrite ($fp,$newfile);
  fclose ($fp);
 }

/**
* removeTree
*
* remove directory tree
*
* @access public
*/
 function removeTree($destination) {

  $res=1;

  if (!Is_Dir($destination)) {
    return 0; // cannot create destination path
  }
 if ($dir = @opendir($destination)) { 
  while (($file = readdir($dir)) !== false) { 
    if (Is_Dir($destination."/".$file) && ($file!='.') && ($file!='..')) {
     $res=$this->removeTree($destination."/".$file);
    } elseif (Is_File($destination."/".$file)) {
     $res=@unlink($destination."/".$file);
    }
  }     
  closedir($dir); 
  $res=@rmdir($destination);
 }
 return $res;
 }


/**
* copyTree
*
* Copy source directory tree to destination directory
*
* @access public
*/
 function copyTree($source, $destination, $over=0, $patterns=0) {


  $res=1;

  if (!Is_Dir($source)) {
   return 0; // cannot create destination path
  }

  if (!Is_Dir($destination)) {
   if (!mkdir($destination)) {
    return 0; // cannot create destination path
   }
  }


  if ($dir = @opendir($source)) { 
  while (($file = readdir($dir)) !== false) { 
    if (Is_Dir($source."/".$file) && ($file!='.') && ($file!='..')) {
     $res=$this->copyTree($source."/".$file, $destination."/".$file, $over, $patterns);
    } elseif (Is_File($source."/".$file) && (!file_exists($destination."/".$file) || $over)) {
     if (!is_array($patterns)) {
      $ok_to_copy=1;
     } else {
      $ok_to_copy=0;
      $total=count($patterns);
      for($i=0;$i<$total;$i++) {
       if (preg_match('/'.$patterns[$i].'/is', $file)) {
        $ok_to_copy=1;
       }
      }
     }
     if ($ok_to_copy) {
      $res=copy($source."/".$file, $destination."/".$file);
     }
    }
  }   
  closedir($dir); 
 }
 return $res;

 }

 function copyFile($source, $destination) {
  $tmp=explode('/', $destination);
  $total=count($tmp);
  if ($total>0) {
   $d=$tmp[0];
   for($i=1;$i<($total-1);$i++) {
    $d.='/'.$tmp[$i];
    if (!is_dir($d)) {
     mkdir($d);
    }
   }
  }
  return copy($source, $destination);

 }

 function copyFiles($source, $destination, $over=0, $patterns=0) {

  $res=1;

  if (!Is_Dir($source)) {
   return 0; // cannot create destination path
  }

  if (!Is_Dir($destination)) {
   if (!mkdir($destination)) {
    return 0; // cannot create destination path
   }
  }


 if ($dir = @opendir($source)) { 
  while (($file = readdir($dir)) !== false) { 
    if (Is_Dir($source."/".$file) && ($file!='.') && ($file!='..')) {
     //$res=$this->copyTree($source."/".$file, $destination."/".$file, $over, $patterns);
    } elseif (Is_File($source."/".$file) && (!file_exists($destination."/".$file) || $over)) {
     if (!is_array($patterns)) {
      $ok_to_copy=1;
     } else {
      $ok_to_copy=0;
      $total=count($patterns);
      for($i=0;$i<$total;$i++) {
       if (preg_match('/'.$patterns[$i].'/is', $file)) {
        $ok_to_copy=1;
       }
      }
     }
     if ($ok_to_copy) {
      $res=copy($source."/".$file, $destination."/".$file);
     }
    }
  }   
  closedir($dir); 
 }
 return $res;
 }

/*
*/

function ftpget($conn_id, $local_file, $remote_file, $mode) {
 global $lset_dirs;
 $l_dir=dirname($local_file);
 if (!isSet($lset_dirs[$l_dir])) {
//  echo "zz";
  if (!is_dir($l_dir)) {
   $this->lmkdir($l_dir);
  }
  $lset_dirs[$l_dir]=1;
 }
 $res=ftp_get($conn_id, $local_file, $remote_file, $mode);
 return $res;
}

function ftpmkdir($conn_id, $ftp_dir) {
 global $set_dirs;
 $tmp=explode('/', $ftp_dir);
 $res_dir=$tmp[0];
 for($i=1;$i<count($tmp);$i++) {
  $res_dir.='/'.$tmp[$i];
  if (!isSet($set_dirs[$res_dir])) {
   $set_dirs[$res_dir]=1;
   if (! @ftp_chdir($conn_id, $res_dir)) {
    ftp_mkdir($conn_id,$res_dir) ;
   }
  }
 }
}

function ftpdelete($conn_id, $filename) {
 $res=ftp_delete($conn_id, $filename);
 return $res;
}


function ftpput($conn_id, $remote_file, $local_file, $mode) {
 global $set_dirs;
 $ftp_dir=dirname($remote_file);
 if (!IsSet($set_dirs[$ftp_dir])) {
   if (! @ftp_chdir($conn_id, $ftp_dir)) {
    $this->ftpmkdir($conn_id,$ftp_dir) ;
   }
  $set_dirs[$ftp_dir]=1;
 }
 $res=ftp_put( $conn_id, $remote_file, $local_file, $mode);
 return $res;
}


/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install() {
  if (!Is_Dir(ROOT."./saverestore")) {
   mkdir(ROOT."./saverestore", 0777);
  }
  parent::install();
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgU2VwIDE2LCAyMDA4IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>