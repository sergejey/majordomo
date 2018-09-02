<?php

include_once("./config.php");
include_once("./lib/loader.php");

$sent_ok=0;

if (preg_match('/ru/is',$_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
   $lang_page_title='Отправка информации для диагностики';
   $lang_comments='Комментарии';
   $lang_comments_note='Пожалуйста, опишите детали инцидента:';
   $lang_tech_details='Техническая информация';
   $lang_tech_details_note='Формируется автоматически';
   $lang_tech_details_review='Просмотреть';
   $lang_attach_log='приложить последние лог-файлы (это может занять некоторое дополнительное время)';
   $lang_send='Отправить';
   $lang_sent_ok='Спасибо. Данные отправлены.';
   $lang_sent_ok_url='Отправленная диагностическая информация доступна по следующей уникальной ссылке:';
   $lang_sent_ok_note='(информация приватная и доступна только по указанной ссылке)';
} else {
   $lang_page_title='Submit diagnostic details';
   $lang_comments='Comments';
   $lang_comments_note='Please, describe the incident details:';
   $lang_tech_details='Technical Data';
   $lang_tech_details_note='Generated automatically';
   $lang_tech_details_review='Review';
   $lang_attach_log='attach latest log-files (it can take some time to pack it)';
   $lang_send='Send';
   $lang_sent_ok='Thank you. Data has been sent.';
   $lang_sent_ok_url='Diagnostic information sent can be reviewed by following uniq URL:';
   $lang_sent_ok_note='(information is kept private and available only by the URL above)';
}


function collectData() {
   $result = array();

   $result['unixtime']=time();
   $result['timestamp']=date('Y-m-d H:i:s',$result['unixtime']);

   if (defined('MASTER_UPDATE_URL')) {
      $result['update_url']=MASTER_UPDATE_URL;
   } else {
      $result['update_url']='Default';
   }


   $result['reboot']=array();
   if (file_exists('./reboot')) {
      $reboot_started=filemtime('./reboot');
      $result['reboot']['status']='initiated';
      $result['reboot']['started']=date('Y-m-d H:i:s',$reboot_started);
      $result['reboot']['since_started']=$result['unixtime']-$reboot_started;
   } else {
      $result['reboot']['status']='ok';
   }

   if (IsWindowsOS()) {
      $os = 'Windows';
   } else {
      $os=trim(exec("uname -a"));
      if (!$os) {
         $os = 'Linux';
      }
   }
   $result['OS']=$os;
   $result['locale'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

   $cycles=array();
   if ($lib_dir = opendir("./scripts"))
   {
      while (($lib_file = readdir($lib_dir)) !== false)
      {
         if ((preg_match("/^cycle_.+?\.php$/", $lib_file)))
            $cycles[$lib_file] = array();
      }
      closedir($lib_dir);
   }
   $result['cycles']=$cycles;

   global $db; 
   $db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);
   if ($db) {

      $result['LatestUpdateId']=gg('LatestUpdateId');
      $result['LatestUpdateTimestamp']=gg('LatestUpdateTimestamp');

      include_once("./load_settings.php");
      $result['DB']['connection']='OK';
      $tables = SQLSelect("SHOW TABLE STATUS;");
      $result['DB']['tables']=$tables;

      $sys_errors = SQLSelect("SELECT ID,CODE,LATEST_UPDATE,ACTIVE FROM system_errors ORDER BY LATEST_UPDATE DESC");
      if ($sys_errors[0]['ID']) {
         foreach($sys_errors as $v) {
            $errors_data=SQLSelect("SELECT COMMENTS,ADDED FROM system_errors_data WHERE ERROR_ID=".$v['ID']." ORDER BY ADDED DESC LIMIT 5");
            $v['DATA']=$errors_data;
            if ($v['ACTIVE']) {
               $result['active_errors']+=$v['ACTIVE'];
            }
            unset($v['ID']);
            $result['SYSTEM_ERRORS'][]=$v;
         }
      }

      foreach($result['cycles'] as $path=>$v) {
         if (preg_match('/(cycle_.+?)\.php/is',$path,$m)) {
            $title=$m[1];
            if (getGlobal($title.'Disabled')) {
               $result['cycles'][$path]['disabled']=1;
            }
            if (getGlobal($title.'Control')) {
               $result['cycles'][$path]['control']=getGlobal($title.'Control');
            }
            $cycle_run_update=getGlobal($title.'Run');
            if ($cycle_run_update) {
               $result['cycles'][$path]['run']=$cycle_run_update;
               $time_passed=($result['unixtime']-$cycle_run_update);
               $result['cycles'][$path]['since_update']=$time_passed;
               if ($time_passed>5*60) {
                  $result['cycles'][$path]['status']='stopped';
               } else {
                  $result['cycles'][$path]['status']='running';
               }
            } else {
               $result['cycles'][$path]['status']='offline';
            }
         }
      }

   } else {
      $result['DB']['connection']='Failed';
   }

   if (!IsWindowsOS()) {
      $max_usage=90; //%
      $output = array();
      exec('df', $output);

      $result['DF_OUTPUT']=implode("\n",$output);

      foreach ($output as $line) {
         if (preg_match('/(\d+)% (\/.+)/', $line, $m))
            $proc = $m[1];
         $path = trim($m[2]);
         if ($path=='') continue;
         $result['SPACE_AVAILABLE'][$path]['usage_proc']=$proc;
         if ($proc > $max_usage) {
            $result['SPACE_AVAILABLE'][$path]['status']='low';
         } else {
            $result['SPACE_AVAILABLE'][$path]['status']='ok';
         }
      }
   }

   $result['language']=SETTINGS_SITE_LANGUAGE;
   $result['timezone']=SETTINGS_SITE_TIMEZONE;
   return $result;
}

$comments='';
if (isset($_POST['send'])) {
   $data=gr('data');
   if (!$data) {
      $data=json_encode(collectData());
   }
   $comments=gr('comments');
   $tar_name='';
   if ($_POST['include_log']) {
      // add latest log files to archive
      mkdir('./cms/saverestore/temp',0777);
      mkdir('./cms/saverestore/temp/cms',0777);
      mkdir('./cms/saverestore/temp/cms/debmes',0777);
      $log_expire=24*60*60;
      $log_path='./cms/debmes';
      $files=scandir($log_path);
      foreach($files as $file) {
         if (is_file($log_path.'/'.$file) && (time()-filemtime($log_path.'/'.$file)<$log_expire)) {
            copy($log_path.'/'.$file,'./cms/saverestore/temp/cms/debmes/'.$file);
         }
      }
      $tar_name .= 'diagnostic_'.date('Y-m-d__h-i-s');
      $tar_name .= IsWindowsOS() ? '.tar' : '.tgz';
      if (IsWindowsOS()) {
         $result = exec('tar.exe --strip-components=2 -C ./cms/saverestore/temp/ -cvf ./cms/saverestore/' . $tar_name . ' ./');
         $new_name = str_replace('.tar', '.tar.gz', $tar_name);
         $result = exec('gzip.exe ./cms/saverestore/' . $tar_name);
         if (file_exists('./cms/saverestore/' . $new_name)) {
            $tar_name = $new_name;
         }
      } else {
         chdir(ROOT . 'cms/saverestore/temp');
         exec('tar cvzf ../' . $tar_name . ' .');
         chdir('../../../');
      }
      removeTree('./cms/saverestore/temp');

   }
   
   $url = 'https://connect.smartliving.ru/market/';
   $fields=array(
       'op'=>'diagnostic',
       'data'=>$data,
       'comments'=>$comments
   );
   if ($_POST['code']) {
      $fields['code']=$code;
   }

   if ($tar_name!='') {
      if (!function_exists('getCurlValue')) {
         function getCurlValue($filename, $contentType, $postname)
         {
            if (function_exists('curl_file_create')) {
               return curl_file_create($filename, $contentType, $postname);
            }
            $value = "@".$filename.";filename=" . $postname;
            if ($contentType) {
               $value .= ';type=' . $contentType;
            }
            return $value;
         }
      }
      $cfile = getCurlValue(ROOT . 'cms/saverestore/'.$tar_name,'application/tar+gzip',$tar_name);
      $fields['datafile']=$cfile;
   }

   $ch = curl_init();
   curl_setopt($ch,CURLOPT_URL, $url);
   curl_setopt($ch,CURLOPT_POST, count($fields));
   curl_setopt($ch,CURLOPT_POSTFIELDS, $fields);
   curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch,CURLOPT_CONNECTTIMEOUT, 60);
   curl_setopt($ch,CURLOPT_TIMEOUT, 120);
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);     // bad style, I know...
   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

   $result = curl_exec($ch);

   if (curl_errno($ch) && !$background) {
      $errorInfo = curl_error($ch);
      $info = curl_getinfo($ch);
      echo ("Failed sending data. Finished with error: \n".$errorInfo."\n".json_encode($info));exit;
   }

   curl_close($ch);

   if ($_POST['code']) {
      header("Content-type:text/json");
      echo $result;
      exit;
   }

   if ($result!='') {
      $data=json_decode($result,true);
      if (is_array($data) && $data['status']=='ok') {
         $sent_ok=1;
         $sent_details_url=$data['url'];
      }
   }

   /*
   if (!$sent_ok) {
      echo "Failed to send data. ";echo $result;exit;
   }
   */

}

$result=collectData();

?>
<html>
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <title><?php echo $lang_page_title;?></title>
   <!-- Latest compiled and minified CSS -->
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
   <!-- Optional theme -->
   <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
   <!-- Latest compiled and minified JavaScript -->
   <script src="https://code.jquery.com/jquery-3.3.1.min.js" type="text/javascript"></script>
   <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
<body>
<div class="container">

   <h1><?php echo $lang_page_title;?></h1>

   <?php if ($sent_ok) {?>
      <div class="alert alert-success"><?php echo $lang_sent_ok;?></div>
      <div>
         <?php echo $lang_sent_ok_url;?>
         <br/>
         <big><a href="<?php echo $sent_details_url;?>"><?php echo $sent_details_url;?></a></big>
         <br/>
         <small><?php echo $lang_sent_ok_note;?></small>
      </div>
   <?php } else {?>

   <form method="post" class="form">
      <h3><?php echo $lang_comments;?></h3>
      <div>
         <?php echo $lang_comments_note;?><br/>
         <textarea name="comments" class="form-control" rows="3"><?php echo $comments;?></textarea>
      </div>
      &nbsp;
   <h3><?php echo $lang_tech_details;?></h3>
      <div>
         <?php echo $lang_tech_details_note;?> <a href="#" onclick="$('#diagnostic_data').toggle();return false;"><?php echo $lang_tech_details_review;?></a><br/>
         <div id="diagnostic_data" style="display:none">
         <textarea name="data" class="form-control" rows="8"><?php echo json_encode($result,JSON_PRETTY_PRINT);?></textarea>
         </div>
      </div>
      &nbsp;
      <div>
         <label><input type="checkbox" name="include_log" value="1"> <?php echo $lang_attach_log;?></label>
      </div>
      &nbsp;
      <div>
      <input type="submit" class="btn btn-success" name="send" value="<?php echo $lang_send;?>">
      </div>
   </form>
   <?php }?>
</div>
</body>
</html>
