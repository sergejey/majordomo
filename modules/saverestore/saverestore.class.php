<?php
/**
 * я┐╜я┐╜я┐╜я┐╜я┐╜я┐╜я┐╜я┐╜я┐╜я┐╜
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

class saverestore extends module
{
    /**
     * saverestore
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "saverestore";
        $this->title = "<#LANG_MODULE_SAVERESTORE#>";
        $this->module_category = "<#LANG_SECTION_SYSTEM#>";
        $this->checkInstalled();
    }

    /**
     * saveParams
     *
     * Saving module parameters
     *
     * @access public
     */
    function saveParams($data = 1)
    {
        $data = array();
        if (IsSet($this->id)) {
            $data["id"] = $this->id;
        }
        if (IsSet($this->view_mode)) {
            $data["view_mode"] = $this->view_mode;
        }
        if (IsSet($this->edit_mode)) {
            $data["edit_mode"] = $this->edit_mode;
        }
        if (IsSet($this->tab)) {
            $data["tab"] = $this->tab;
        }
        return parent::saveParams($data);
    }

    /**
     * getParams
     *
     * Getting module parameters from query string
     *
     * @access public
     */
    function getParams()
    {
        global $id;
        global $mode;
        global $view_mode;
        global $edit_mode;
        global $tab;
        if (isset($id)) {
            $this->id = $id;
        }
        if (isset($mode)) {
            $this->mode = $mode;
        }
        if (isset($view_mode)) {
            $this->view_mode = $view_mode;
        }
        if (isset($edit_mode)) {
            $this->edit_mode = $edit_mode;
        }
        if (isset($tab)) {
            $this->tab = $tab;
        }
    }

    /**
     * Run
     *
     * Description
     *
     * @access public
     */
    function run()
    {
        global $session;
        $out = array();
        if ($this->action == 'admin') {
            $this->admin($out);
        } else {
            $this->usual($out);
        }
        if (IsSet($this->owner->action)) {
            $out['PARENT_ACTION'] = $this->owner->action;
        }
        if (IsSet($this->owner->name)) {
            $out['PARENT_NAME'] = $this->owner->name;
        }
        $out['VIEW_MODE'] = $this->view_mode;
        $out['EDIT_MODE'] = $this->edit_mode;
        $out['MODE'] = $this->mode;
        $out['ACTION'] = $this->action;
        if ($this->single_rec) {
            $out['SINGLE_REC'] = 1;
        }
        $this->data = $out;
        $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
        $this->result = $p->result;
    }

    /**
     * BackEnd
     *
     * Module backend
     *
     * @access public
     */
    function admin(&$out)
    {


        global $err_msg;
        if ($err_msg) {
            $out['ERR_MSG'] = $err_msg;
        }
        global $ok_msg;
        if ($ok_msg) {
            $out['OK_MSG'] = $ok_msg;
        }

        $this->getConfig();

        if (is_dir(ROOT . 'cms/saverestore/temp')) {
            $out['CLEAR_FIRST'] = 1;
        }


        $this->getConfig();

        if (defined('MASTER_UPDATE_URL') && MASTER_UPDATE_URL != '') {
            $github_feed_url = MASTER_UPDATE_URL;
            $github_feed_url = str_replace('/archive/', '/commits/', $github_feed_url);
            $github_feed_url = str_replace('.tar.gz', '.atom', $github_feed_url);
        } else {
            $github_feed_url = 'https://github.com/sergejey/majordomo/commits/master.atom';
        }
        $github_feed = getURL($github_feed_url, 30 * 60);

        if ($github_feed != '') {
            @$tmp = GetXMLTree($github_feed);
            @$data = XMLTreeToArray($tmp);
            @$items = $data['feed']['entry'];
            if (is_array($items)) {
                $total = count($items);
                if ($total) {
                    if ($total > 5) {
                        $total = 5;
                    }
                    for ($i = 0; $i < $total; $i++) {
                        $itm = array();
                        $itm['ID'] = trim($items[$i]['id']['textvalue']);
                        $itm['ID'] = preg_replace('/.+Commit\//is', '', $itm['ID']);
                        $itm['TITLE'] = trim($items[$i]['title']['textvalue']);
                        $itm['AUTHOR'] = $items[$i]['author']['name']['textvalue'];
                        $itm['LINK'] = $items[$i]['link']['href'];
                        $itm['UPDATED'] = strtotime($items[$i]['updated']['textvalue']);
                        $itm['UPDATE_TEXT'] = date('m/d/Y H:i', $itm['UPDATED']);
                        $out['UPDATES'][] = $itm;
                    }
                    $out['LATEST_ID'] = $out['UPDATES'][0]['ID'];
                    if ($out['LATEST_ID'] != '' && $out['LATEST_ID'] == $this->config['LATEST_UPDATED_ID']) {
                        $out['NO_NEED_TO_UPDATE'] = 1;
                    }
                    if ($this->ajax && $_GET['op']=='check_updates') {
                        if (!$out['NO_NEED_TO_UPDATE']) {
                            echo "1";
                        } else {
                            echo "0";
                        }
                        exit;
                    }
                    //print_r($out['UPDATES']);
                    //exit;
                }
            }
        }


        if ($this->mode == 'savedetails') {

            global $ftp_host;
            global $ftp_username;
            global $ftp_password;
            global $ftp_folder;
            global $ftp_clear;


            if ($ftp_clear) {
                $this->config['FTP_USERNAME'] = '';
                $this->config['FTP_PASSWORD'] = '';
                $this->saveConfig();
                $this->redirect("?");
            }

            $out['FTP_HOST'] = $ftp_host;
            $out['FTP_USERNAME'] = $ftp_username;
            $out['FTP_PASSWORD'] = $ftp_password;
            $out['FTP_FOLDER'] = $ftp_folder;


            $conn_id = @ftp_connect($ftp_host);
            if ($conn_id) {

                $login_result = @ftp_login($conn_id, $ftp_username, $ftp_password);
                if ($login_result) {
                    $systyp = ftp_systype($conn_id);

                    if (!preg_match('/\/$/', $ftp_folder)) {
                        $ftp_folder .= '/';
                    }

                    if (@ftp_chdir($conn_id, $ftp_folder . 'cms/saverestore')) {
                        $this->config['FTP_HOST'] = $ftp_host;
                        $this->config['FTP_USERNAME'] = $ftp_username;
                        $this->config['FTP_PASSWORD'] = $ftp_password;
                        $this->config['FTP_FOLDER'] = $ftp_folder;
                        $this->saveConfig();
                        $this->redirect("?");
                    } else {
                        $out['FTP_ERR'] = 'Incorrect folder (' . $ftp_folder . ')';
                    }
                } else {
                    $out['FTP_ERR'] = 'Incorrect username/password';
                }

                ftp_close($conn_id);

            } else {
                $out['FTP_ERR'] = 'Cannot connect to host (' . $ftp_host . ')';
            }

        }

        if ($this->mode != 'savedetails') {
            $out['FTP_HOST'] = $this->config['FTP_HOST'];
            $out['FTP_USERNAME'] = $this->config['FTP_USERNAME'];
            $out['FTP_PASSWORD'] = $this->config['FTP_PASSWORD'];
            $out['FTP_FOLDER'] = $this->config['FTP_FOLDER'];
        }

// if ($this->mode=='' || $this->mode=='upload' || $this->mode=='savedetails') {
        $method = 'ftp';
        if (function_exists('getmyuid') && function_exists('fileowner')) {
            $temp_file = tempnam("./cms/saverestore/", "FOO");
            if (file_exists($temp_file)) {
                $method = 'direct';
                unlink($temp_file);
            }
        }
        $out['METHOD'] = $method;
        $this->method = $method;
// }

        if ($this->mode == 'clear') {
            set_time_limit(0);
            $this->removeTree(ROOT . 'cms/saverestore/temp');
            @unlink(ROOT."modules/control_modules/installed");
            global $with_extensions;
            if ($with_extensions) {
                $this->redirect("?(panel:{action=market})&md=market&mode=update_all");
            }
            $this->redirect("?err_msg=" . urlencode($err_msg) . "&ok_msg=" . urlencode($ok_msg));
        }


        if ($this->mode == 'checksubmit') {
            $this->checkSubmit($out);
        }

        if ($this->mode == 'uploadupdates') {
            $this->uploadUpdates($out);
        }

        if ($this->mode == 'checkupdates') {
            $this->checkupdatesSVN($out);
        }

        if ($this->mode == 'downloadupdates') {
            $this->downloadupdatesSVN($out);
        }

        if ($this->mode == 'checkapps') {
            $this->checkApps($out);
        }

        if ($this->mode == 'downloadapps') {
            $this->downloadApps($out);
        }


        if ($this->mode == 'upload') {
            $this->upload($out);
            //$this->redirect("?mode=clear");
        }
        if ($this->mode == 'dump') {
            $this->dump($out);
            $this->redirect("?mode=clear");
            //$this->redirect("?");
        }

        if ($this->mode == 'delete') {
            global $file;
            @unlink(ROOT . 'cms/saverestore/' . $file);
            $this->redirect("?");
        }

        if ($this->mode == 'getlatest') {
            $this->getLatest($out);
        }

        if ($this->mode == 'getlatest_iframe') {
            global $with_extensions;
            $out['WITH_EXTENSIONS'] = $with_extensions;

            global $backup;
            $out['BACKUP'] = $backup;
            global $data;
            $out['DATA'] = $data;
            global $code;
            $out['CODE'] = $code;
            global $save_files;
            $out['SAVE_FILES'] = $save_files;
            global $design;
            $out['DESIGN'] = $design;

        }


        $source = ROOT . 'cms/saverestore';
        $currentdir = getcwd();
        chdir($source);
        array_multisort(array_map('filemtime', ($files = glob("*.*"))), SORT_DESC, $files);
        if (defined('SETTINGS_BACKUP_PATH') && SETTINGS_BACKUP_PATH != '' && is_dir(SETTINGS_BACKUP_PATH)) {
            $backups_dir=SETTINGS_BACKUP_PATH;
        } else {
            $backups_dir=DOC_ROOT . '/backup';
        }
        chdir($backups_dir);
        $backups = glob("*");
        if (is_array($backups)) {
            foreach($backups as $backup_folder) {
                $files[]=$backups_dir.'/'.$backup_folder;
            }
        }
        chdir($currentdir);
        $out['FILES'] = array();
        foreach ($files as $file) {
            $tmp = array();
            $tmp['FILENAME'] = $file;
            if (is_file($source . "/" . $file)) {
                $tmp['FILESIZE'] = number_format((filesize($source . "/" . $file) / 1024 / 1024), 2);
                $tmp['TITLE']=basename($file);
            } else {
                $tmp['TITLE']='Backup '.basename($file);
            }
            $out['FILES'][] = $tmp;
        }


    }


    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function getLatest(&$out, $iframe = 0)
    {


        if (defined('MASTER_UPDATE_URL') && MASTER_UPDATE_URL != '') {
            $url = MASTER_UPDATE_URL;
        } else {
            $url = 'https://github.com/sergejey/majordomo/archive/master.tar.gz';
        }
        $this->url = $url;

        set_time_limit(0);

        if (!is_dir(ROOT . 'cms/saverestore')) {
            @umask(0);
            @mkdir(ROOT . 'cms/saverestore', 0777);
        }

        $filename = ROOT . 'cms/saverestore/master.tgz';

        @unlink(ROOT . 'cms/saverestore/master.tgz');
        @unlink(ROOT . 'cms/saverestore/master.tar');

        $f = fopen($filename, 'wb');
        if ($f == FALSE) {
            $this->redirect("?err_msg=" . urlencode("Cannot open " . $filename . " for writing"));
        }

        if ($iframe) {
            $this->echonow("Downloading $url ... ");
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

            if ($iframe) {
                $this->echonow(" OK<br/>", "green");
            }


            global $code;
            global $data;
            global $design;
            $code = 1;
            $data = 1; //fix
            $design = 1;
            $out['BACKUP'] = 1;
            $this->dump($out, $iframe);
            $this->removeTree(ROOT . 'cms/saverestore/temp', $iframe);

            if (!$iframe) {
                global $with_extensions;
                $folder = 'majordomo-master';
                $basename = basename($this->url);
                if ($basename != 'master.tar.gz') {
                    $basename = str_replace('.tar.gz', '', $basename);
                    $folder = str_replace('master', $basename, $folder);
                }
                $this->redirect("?mode=upload&restore=" . urlencode('master.tgz') . "&folder=" . urlencode($folder) . "&with_extensions=" . $with_extensions);
            } else {
                return 1;
            }

        } else {

            if ($iframe) {
                $this->echonow("Cannot download file<br/>", "red");
                exit;
            } else {
                $this->redirect("?err_msg=" . urlencode("Cannot download " . $url));
            }
        }
    }


    function echonow($msg, $color = '')
    {
        if ($color) {
            echo '<font color="' . $color . '">';
        }
        echo $msg;
        if ($color) {
            echo '</font>';
        }
        echo str_repeat(' ', 16 * 1024);
        flush();
        ob_flush();
    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function uploadUpdates(&$out)
    {
        global $to_submit;
        global $pack_folders;


        $total = count($to_submit);

        umask(0);

        $copied_dirs = array();

        if (mkdir(ROOT . 'cms/saverestore/temp', 0777)) {
            for ($i = 0; $i < $total; $i++) {
                $this->copyFile(ROOT . $to_submit[$i], ROOT . 'cms/saverestore/temp/' . $to_submit[$i]);
                if (is_array($pack_folders) && in_array($to_submit[$i], $pack_folders) && !$copied_dirs[dirname(ROOT . 'cms/saverestore/temp/' . $to_submit[$i])]) {
                    $this->copyTree(dirname(ROOT . $to_submit[$i]), dirname(ROOT . 'cms/saverestore/temp/' . $to_submit[$i]));
                    $copied_dirs[dirname(ROOT . 'cms/saverestore/temp/' . $to_submit[$i])] = 1;
                }
                if (file_exists(dirname(ROOT . 'cms/saverestore/temp/' . $to_submit[$i]) . '/installed')) {
                    @unlink(dirname(ROOT . 'cms/saverestore/temp/' . $to_submit[$i]) . '/installed');
                }
            }
        }

        // packing into tar.gz
        $tar_name = 'submit_' . date('Y-m-d__h-i-s') . '.tgz';

        chdir(ROOT . 'cms/saverestore/temp');
        exec('tar cvzf ../' . $tar_name . ' .');
        chdir('../../../');
        $this->removeTree(ROOT . 'cms/saverestore/temp');

        // sending to remote server

        $repository_url = UPDATER_URL;

        if (defined('UPDATES_REPOSITORY_NAME')) {
            $repository_name = UPDATES_REPOSITORY_NAME;
        } else {
            $repository_name = 'default';
        }

        $to_send = array();
        global $name;
        $to_send['NAME'] = $name;
        setCookie('SUBMIT_NAME', $name, 0, '/');

        global $email;
        $to_send['EMAIL'] = $email;
        setCookie('SUBMIT_EMAIL', $email, 0, '/');

        global $description;
        $to_send['DESCRIPTION'] = $description;
        $to_send['FILES'] = $to_submit;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $repository_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        $post = array(
            "file" => "@" . ROOT . 'cms/saverestore/' . $tar_name,
            "mode" => "upload_updates",
            "repository" => $repository_name,
            "host" => $_SERVER['HTTP_HOST'],
            "data" => serialize($to_send)
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        //curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=upload_updates&repository=".$repository_name."&host=".$_SERVER['HTTP_HOST']."&data=".$to_send);

        $incoming = curl_exec($ch);

        curl_close($ch);

        $result = unserialize($incoming);

        $ok_msg = 'Error sending files to repository! ';//.$incoming
        if ($result['MESSAGE']) {
            $ok_msg = $result['MESSAGE'];
        }


        if ($result['STATUS'] == 'OK') {
            global $with_extensions;
            $this->redirect("?mode=clear&ok_msg=" . urlencode($ok_msg) . "&with_extensions=" . $with_extensions);
        } else {
            $this->redirect("?mode=clear&err_msg=" . urlencode($ok_msg));
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
    function checkSubmit(&$out)
    {

        $res1 = $this->checkEFiles('.', 0);
        $res2 = $this->checkEFiles('./modules', 1);
        $res3 = $this->checkEFiles('./templates', 1);
        $res4 = $this->checkEFiles('./lib', 0);

        $res = array_merge($res1, $res2, $res3, $res4);

        $to_send = serialize($res);

        $repository_url = UPDATER_URL;

        if (defined('UPDATES_REPOSITORY_NAME')) {
            $repository_name = UPDATES_REPOSITORY_NAME;
        } else {
            $repository_name = 'default';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $repository_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=check_submit&repository=" . $repository_name . "&host=" . $_SERVER['HTTP_HOST'] . "&data=" . $to_send);

        $incoming = curl_exec($ch);

        curl_close($ch);

        //echo $incoming;exit;


        $result = unserialize($incoming);

        //echo $repository_url;
        //echo($result);exit;

        if ($result['STATUS'] != 'OK') {
            $out['ERROR_CHECK'] = 1;
            if ($result['MESSAGE']) {
                $out['ERROR_MESSAGE'] = $result['MESSAGE'];
            } else {
                $out['ERROR_MESSAGE'] = 'Cannot connect to updates server';
            }
        } else {
            $out['OK_CHECKSUBMIT'] = 1;

            //print_r($result['TO_SUBMIT']);exit;

            if (is_array($result['TO_SUBMIT'])) {
                foreach ($result['TO_SUBMIT'] as $f => $v) {
                    $tmp = array('FILE' => $f, 'VERSION' => $v, 'L_VERSION' => $res[$f]);
                    if (preg_match('/\/modules\/.+\/.+/is', $f) || preg_match('/\/templates\/.+\/.+/is', $f)) {
                        $tmp['PACK_FOLDER'] = 1;
                    }
                    $out['TO_SUBMIT'][] = $tmp;
                }
            } else {
                $out['NO_SUBMIT'] = 1;
            }
        }

        $out['NAME'] = $_COOKIE['SUBMIT_NAME'];
        $out['EMAIL'] = $_COOKIE['SUBMIT_EMAIL'];

    }


    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function downloadUpdates(&$out)
    {
        global $to_update;

        $repository_url = UPDATER_URL;

        if (defined('UPDATES_REPOSITORY_NAME')) {
            $repository_name = UPDATES_REPOSITORY_NAME;
        } else {
            $repository_name = 'default';
        }

        // preparing update

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $repository_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=prepare&repository=" . $repository_name . "&host=" . $_SERVER['HTTP_HOST'] . "&data=" . serialize($to_update));
        $incoming = curl_exec($ch);
        curl_close($ch);

        $res = unserialize($incoming);

        if ($res['STATUS'] == 'OK' && $res['DOWNLOAD_FILE'] != '') {
            // downloading update
            $filename = ROOT . 'cms/saverestore/' . $res['DOWNLOAD_FILE'];
            $f = fopen($filename, 'wb');
            if ($f == FALSE) {
                //print "File not opened<br>";
                //exit;
                $this->redirect("?err_msg=" . urlencode("Cannot open " . $filename . " for writing"));
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $repository_url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_FILE, $f);

            curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=download&repository=" . $repository_name . "&host=" . $_SERVER['HTTP_HOST'] . "&file=" . $res['DOWNLOAD_FILE']);

            $incoming = curl_exec($ch);

            curl_close($ch);
            @fclose($f);

            if (file_exists($filename) && filesize($filename) > 0) {
                // backing up current code version
                global $code;
                global $data;
                $code = 1;
                $data = 1;
                $out['BACKUP'] = 1;
                $this->dump($out);
                $this->removeTree(ROOT . 'cms/saverestore/temp');
                // installing update
                $this->redirect("?mode=upload&restore=" . urlencode($res['DOWNLOAD_FILE']));
            } else {
                $this->redirect("?err_msg=" . urlencode("Error downloading update"));
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
    function downloadApps(&$out)
    {
        global $to_install;


        $repository_url = UPDATER_URL;

        if (defined('UPDATES_REPOSITORY_NAME')) {
            $repository_name = UPDATES_REPOSITORY_NAME;
        } else {
            $repository_name = 'default';
        }

        // preparing update

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $repository_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=prepareapps&repository=" . $repository_name . "&host=" . $_SERVER['HTTP_HOST'] . "&data=" . serialize($to_install));
        $incoming = curl_exec($ch);
        curl_close($ch);

        //echo $incoming;exit;

        $res = unserialize($incoming);

        if ($res['STATUS'] == 'OK' && $res['DOWNLOAD_FILE'] != '') {
            // downloading update
            $filename = ROOT . 'cms/saverestore/' . $res['DOWNLOAD_FILE'];
            $f = fopen($filename, 'wb');
            if ($f == FALSE) {
                //print "File not opened<br>";
                //exit;
                $this->redirect("?err_msg=" . urlencode("Cannot open " . $filename . " for writing"));
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $repository_url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 600);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_FILE, $f);

            curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=download&repository=" . $repository_name . "&host=" . $_SERVER['HTTP_HOST'] . "&file=" . $res['DOWNLOAD_FILE']);

            $incoming = curl_exec($ch);

            curl_close($ch);
            @fclose($f);

            if (file_exists($filename) && filesize($filename) > 0) {
                // backing up current code version
                global $code;
                global $data;
                $code = 1;
                $data = 1;
                $out['BACKUP'] = 1;
                $this->dump($out);
                $this->removeTree(ROOT . 'cms/saverestore/temp');
                // installing update
                $this->redirect("?mode=upload&restore=" . urlencode($res['DOWNLOAD_FILE']));
            } else {
                $this->redirect("?err_msg=" . urlencode("Error downloading update"));
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
    function checkApps(&$out)
    {

        $res = array();
        $d = './modules';
        if ($dir = @opendir($d)) {
            while (($file = readdir($dir)) !== false) {
                if (is_dir($d . '/' . $file)
                    && ($file != '..')
                    && ($file != '.')
                    && ($file != 'control_access')
                    && ($file != 'control_modules')
                ) {
                    $res[] = $file;
                }
            }
        }

        $to_send = serialize($res);

        $repository_url = UPDATER_URL;

        if (defined('UPDATES_REPOSITORY_NAME')) {
            $repository_name = UPDATES_REPOSITORY_NAME;
        } else {
            $repository_name = 'default';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $repository_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=checkapps&repository=" . $repository_name . "&host=" . $_SERVER['HTTP_HOST'] . "&data=" . $to_send);

        $incoming = curl_exec($ch);

        curl_close($ch);

        //echo $incoming;exit;

        $result = unserialize($incoming);

        if ($result['STATUS'] != 'OK') {

            if ($result['MESSAGE']) {
                $out['ERROR_MESSAGE'] = $result['MESSAGE'];
            } else {
                $out['ERROR_MESSAGE'] = 'Cannot connect to updates server';
            }
            $this->redirect("?err_msg=" . urlencode($out['ERROR_MESSAGE']));

        } else {
            $out['OK_BROWSE'] = 1;
            if (is_array($result['TO_INSTALL'])) {
                $out['TO_INSTALL'] = $result['TO_INSTALL'];
                /*
                foreach($result['TO_UPDATE'] as $f=>$v) {
                 $out['TO_INSTALL'][]=array('FILE'=>$f, 'VERSION'=>$v);
                }
                */
            } else {
                $out['NO_MODULES'] = 1;
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
    function downloadUpdatesSVN(&$out)
    {

        global $code;
        global $data;
        $code = 1;
        $data = 1;
        $out['BACKUP'] = 1;
        $this->dump($out);
        $this->removeTree(ROOT . 'cms/saverestore/temp');

        include_once DIR_MODULES . 'saverestore/phpsvnclient.php';
        $url = 'http://majordomo-sl.googlecode.com/svn/';
        $phpsvnclient = new phpsvnclient($url);
        set_time_limit(0);
        global $to_update;

        $total = count($to_update);
        for ($i = 0; $i < $total; $i++) {
            $path = 'trunk/' . $to_update[$i];
            $file_content = $phpsvnclient->getFile($path);
            if (!is_dir(dirname(ROOT . $to_update[$i]))) {
                @mkdir(dirname(ROOT . $to_update[$i]), 0777);
            }
            @SaveFile(ROOT . $to_update[$i], $file_content);
            if (file_exists(dirname(ROOT . $to_update[$i]) . '/installed')) {
                @unlink(dirname(ROOT . $to_update[$i]) . '/installed');
            }
        }

        $this->redirect("?ok_msg=" . urlencode('Files have been updated!'));

    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function checkUpdatesSVN(&$out)
    {
        include_once DIR_MODULES . 'saverestore/phpsvnclient.php';

        $url = 'http://majordomo-sl.googlecode.com/svn/';

        $phpsvnclient = new phpsvnclient($url);

        set_time_limit(0);
        //$phpsvnclient->createOrUpdateWorkingCopy('trunk/', ROOT.'cms/saverestore/temp', true);

        $cached_name = ROOT . 'cms/saverestore/svn_tree.txt';
        if (!file_exists($cached_name) || (time() - filemtime($cached_name) > 8 * 60 * 60)) {
            $directory_tree = $phpsvnclient->getDirectoryTree('/trunk/');
            SaveFile($cached_name, serialize($directory_tree));
        } else {
            $directory_tree = unserialize(LoadFile($cached_name));
        }

        $updated = array();
        $total = count($directory_tree);
        for ($i = 0; $i < $total; $i++) {
            $item = $directory_tree[$i];
            if ($item['type'] != 'file' || $item['path'] == 'trunk/config.php') {
                continue;
            }
            $filename = str_replace('trunk/', ROOT, $item['path']);
            @$fsize = filesize($filename);
            $r_rfsize = $item['size'];
            if ($fsize != $r_rfsize || !file_exists($filename)) {
                $updated[] = $item;
            }

        }


        $out['OK_CHECK'] = 1;
        if (!$updated[0]) {
            $out['NO_UPDATES'] = 1;
        } else {
            foreach ($updated as $item) {
                $item['path'] = str_replace('trunk/', '', $item['path']);
                $out['TO_UPDATE'][] = array('FILE' => $item['path'], 'VERSION' => $item['version'] . ' (' . $item['last-mod'] . ')');
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
    function checkUpdates(&$out)
    {

        $res1 = $this->checkEFiles('.', 0);
        $res2 = $this->checkEFiles('./modules', 1);
        $res3 = $this->checkEFiles('./templates', 1);
        $res4 = $this->checkEFiles('./lib', 0);

        $res = array_merge($res1, $res2, $res3, $res4);

        $to_send = serialize($res);

        $repository_url = UPDATER_URL;

        if (defined('UPDATES_REPOSITORY_NAME')) {
            $repository_name = UPDATES_REPOSITORY_NAME;
        } else {
            $repository_name = 'default';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $repository_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($ch, CURLOPT_POSTFIELDS, "mode=check&repository=" . $repository_name . "&host=" . $_SERVER['HTTP_HOST'] . "&data=" . $to_send);

        $incoming = curl_exec($ch);

        curl_close($ch);

        //echo $incoming;exit;


        $result = unserialize($incoming);

        //echo $repository_url;
        //echo($result);exit;

        if ($result['STATUS'] != 'OK') {
            $out['ERROR_CHECK'] = 1;
            if ($result['MESSAGE']) {
                $out['ERROR_MESSAGE'] = $result['MESSAGE'];
            } else {
                $out['ERROR_MESSAGE'] = 'Cannot connect to updates server';
            }
        } else {
            $out['OK_CHECK'] = 1;
            if (is_array($result['TO_UPDATE'])) {
                foreach ($result['TO_UPDATE'] as $f => $v) {
                    $out['TO_UPDATE'][] = array('FILE' => $f, 'VERSION' => $v);
                }
            } else {
                $out['NO_UPDATES'] = 1;
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
    function checkEFiles($d, $max_level = 0, $level = 0)
    {


        $res = array();

        if (!is_dir($d)) {
            return $res;
        }

        if ($dir = @opendir($d)) {
            while (($file = readdir($dir)) !== false) {
                if (Is_Dir($d . "/" . $file) && ($file != '.') && ($file != '..')) {
                    //echo "<br>Dir ".$d."/".$file;
                    if ($level < $max_level) {
                        $res2 = $this->checkEFiles($d . "/" . $file, $max_level, ($level + 1));
                        if (is_array($res2)) {
                            $res = array_merge($res, $res2);
                        }
                    }
                } elseif (Is_File($d . "/" . $file) &&
                    (preg_match('/\.php$/', $file) || preg_match('/\.css$/', $file) || preg_match('/\.html$/', $file) || preg_match('/\.js$/', $file))
                ) {

                    if ($file == 'config.php') {
                        continue;
                    }

                    //echo "<br>".$d.'/'.$file;
                    $version = '';
                    $content = LoadFile($d . '/' . $file);
                    if (preg_match('/@version (.+?)\n/is', $content, $m)) {
                        $version = trim($m[1]);
                        //echo "<br>".$d.'/'.$file.' - '.$version;
                    } elseif (preg_match('/\.class\.php$/is', $file)) {
                        // echo "<br>".$d.'/'.$file.' - '.'unknown';
                        //$version='unknown';
                    }

                    if ($version != '') {
                        $res[$d . '/' . $file] = $version;
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
    function extractVersion($s)
    {
        $o_version = preg_replace('/\(.+/', '', $s);
        $o_version = preg_replace('/[^\d]/', '', $o_version);
        $o_version = (float)substr($o_version, 0, 1) . '.' . substr($o_version, 1, strlen($o_version) - 1);
        return $o_version;
    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function isNewer($o_version, $r_version)
    {

        $o_version = $this->extractVersion($o_version);
        $r_version = $this->extractVersion($r_version);

        //$r_version+=0.1; // just for testing
        //echo $o_version.' to '.$r_version."<br>";

        if ($o_version < $r_version) {
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
    function getLocalFilesTree($dir, $pattern, $ex_pattern, &$log, $verbose)
    {
        $res = array();

        $destination = $dir;

        if (!Is_Dir($destination)) {
            return $res; // cannot create destination path
        }

        if ($dir = @opendir($destination)) {
            while (($file = readdir($dir)) !== false) {
                if (Is_Dir($destination . "/" . $file) && ($file != '.') && ($file != '..')) {
                    $sub_ar = $this->getLocalFilesTree($destination . "/" . $file, $pattern, $ex_pattern, $log, $verbose);
                    $res = array_merge($res, $sub_ar);
                } elseif (Is_File($destination . "/" . $file)) {
                    $fl = array();
                    $fl['FILENAME'] = str_replace('//', '/', $destination . "/" . $file);
                    $fl['FILENAME_SHORT'] = str_replace('//', '/', $file);
                    $fl['MTIME']=filemtime($fl['FILENAME']);
                    $fl['SIZE'] = filesize($fl['FILENAME']);
                    if (preg_match('/' . $pattern . '/is', $fl['FILENAME_SHORT']) && ($ex_pattern == '' || !preg_match('/' . $ex_pattern . '/is', $fl['FILENAME_SHORT']))) {
                        $res[] = $fl;
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
    function upload(&$out, $iframe = 0)
    {

        set_time_limit(0);
        global $restore;
        global $file;
        global $file_name;
        global $folder;
        global $with_extensions;

        if (!$folder)
            $folder = IsWindowsOS() ? '/.' : '/';
        else
            $folder = '/' . $folder;

        if ($restore != '') {
            //$file=ROOT.'cms/saverestore/'.$restore;
            $file = $restore;
            $file_name = basename($file);
        } elseif ($file != '') {
            move_uploaded_file($file, ROOT . 'cms/saverestore/' . $file_name);
            $file = $file_name;
        }



        if ($iframe) {
            $this->echonow("<b>Applying updates.</b><br/>");
        }

        if ($file != '' && preg_match('/\.sql$/', $file_name) && file_exists(ROOT . 'cms/saverestore/' . $file)) {
            // restore database only
            if ($iframe) {
                $this->echonow("Restoring database from $file ... ");
            }
            $this->restoredatabase(ROOT . 'cms/saverestore/' . $file);
            if ($iframe) {
                $this->echonow(" OK<br/> ", 'green');
            }
            if ($iframe) {
                return 1;
            } else {
                $this->redirect("?mode=clear&ok_msg=" . urlencode("Database restored!"));
            }
        } elseif ($file != '' && is_dir($file)) {
            if ($iframe) {
                $this->echonow("Updating files ... ");
            }
            $this->copyTree($file, ROOT, 1); // restore all files
            if ($iframe) {
                $this->echonow(" OK<br/> ", 'green');
            }
            $db_filename=$file.'/'.DB_NAME . ".sql";
            if (file_exists($db_filename)) {
                if ($iframe) {
                    $this->echonow("Restoring database from $db_filename ... ");
                }
                $this->restoredatabase($db_filename);
                if ($iframe) {
                    $this->echonow(" OK<br/> ", 'green');
                }
            }
            if ($iframe) {
                return 1;
            } else {
                $this->redirect("?mode=clear&ok_msg=" . urlencode("Database restored!"));
            }
        } elseif ($file != '') {
            // unpack archive
            umask(0);
            @mkdir(ROOT . 'cms/saverestore/temp', 0777);
            chdir(ROOT . 'cms/saverestore/temp');
            if ($iframe) {
                $this->echonow("Unpacking $file ... ");
            }
            if (IsWindowsOS()) {
                exec(DOC_ROOT . '/gunzip ../' . $file, $output, $res);
                exec(DOC_ROOT . '/tar xvf ../' . str_replace('.tgz', '.tar', $file), $output, $res);
            } else {
                exec('tar xzvf ../' . $file, $output, $res);
            }
            @unlink(ROOT . 'cms/saverestore/temp' . $folder . '/config.php');

            if ($iframe) {
                $this->echonow(" OK<br/> ", 'green');
            }

            chdir('../../../');
            $ignores = SQLSelect("SELECT * FROM ignore_updates ORDER BY NAME");
            $total = count($ignores);
            for ($i = 0; $i < $total; $i++) {
                $name = $ignores[$i]['NAME'];
                if (is_dir(ROOT . 'cms/saverestore/temp/modules/' . $name)) {
                    $this->removeTree(ROOT . 'cms/saverestore/temp/modules/' . $name);
                }
                if (is_dir(ROOT . 'cms/saverestore/temp/templates/' . $name)) {
                    $this->removeTree(ROOT . 'cms/saverestore/temp/templates/' . $name);
                }
            }

            if ($iframe) {
                $this->echonow("Updating files ... ");
            }

            // UPDATING FILES DIRECTLY
            $this->copyTree(ROOT . 'cms/saverestore/temp' . $folder, ROOT, 1); // restore all files

            if ($iframe) {
                $this->echonow(" OK<br/> ", 'green');
            }

            if (file_exists(ROOT . 'cms/saverestore/temp' . $folder . '/dump.sql')) {
                // data restore
                if ($iframe) {
                    $this->echonow("Restoring database ... ");
                }
                $this->restoredatabase(ROOT . 'cms/saverestore/temp' . $folder . '/dump.sql');
                $this->echonow(" OK<br/> ", 'green');
            }


            if ($iframe) {
                $this->echonow("Re-installing modules ... ");
            }

            //if (is_dir(ROOT.'cms/saverestore/temp/'.$folder.'modules')) {
            // code restore
            $source = ROOT . 'modules';
            if ($dir = @opendir($source)) {
                while (($file = readdir($dir)) !== false) {
                    if (Is_Dir($source . "/" . $file) && ($file != '.') && ($file != '..')) { // && !file_exists($source."/".$file."/installed")
                        @unlink(ROOT . "modules/" . $file . "/installed");
                    }
                }
            }
            @unlink(ROOT . "modules/control_modules/installed");

            $this->config['LATEST_UPDATED_ID'] = $out['LATEST_ID'];
            $this->saveConfig();
            sg('LatestUpdateId',$out['LATEST_ID']);
            sg('LatestUpdateTimestamp',date('Y-m-d H:is'));


            if ($iframe) {
                $this->echonow(" OK<br/> ", 'green');
            }
            if ($iframe) {
                $this->echonow("Rebooting system ... ");
            }
            @SaveFile(ROOT . 'reboot', 'updated');
            if ($iframe) {
                $this->echonow(" OK<br/> ", 'green');
            }


            if ($iframe) {
                return 1;
            } else {
                $this->redirect("?mode=clear&ok_msg=" . urlencode("Updates Installed!") . "&with_extensions=" . $with_extensions);
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
    function dump(&$out, $iframe = 0)
    {

        if ($iframe) {
            $this->echonow("<b>Working on backup.</b><br/>");
        }


        if (mkdir(ROOT . 'cms/saverestore/temp', 0777)) {
            // DESIGN
            global $design;
            if ($design) {

                if ($iframe) {
                    $this->echonow("Saving design ... ");
                }

                $tar_name .= 'design_';
                $this->copyTree(ROOT . 'templates', ROOT . 'cms/saverestore/temp/templates');
                $this->copyTree(ROOT . 'img', ROOT . 'cms/saverestore/temp/img');
                $this->copyTree(ROOT . 'js', ROOT . 'cms/saverestore/temp/js');


                $pt = array('\.css');
                $this->copyFiles(ROOT, ROOT . 'cms/saverestore/temp', 0, $pt);

                $pt = array('\.swf');
                $this->copyFiles(ROOT, ROOT . 'cms/saverestore/temp', 0, $pt);

                $pt = array('\.htc');
                $this->copyFiles(ROOT, ROOT . 'cms/saverestore/temp', 0, $pt);

                if ($iframe) {
                    $this->echonow(" OK<br/>", 'green');
                }


            }

            // CODE
            global $code;
            if ($code) {

                if ($iframe) {
                    $this->echonow("Saving code ... ");
                }


                $tar_name .= 'code_';

                $this->copyTree(ROOT . 'lib', ROOT . 'cms/saverestore/temp/lib');
                $this->copyTree(ROOT . 'modules', ROOT . 'cms/saverestore/temp/modules');
                $this->copyTree(ROOT . 'scripts', ROOT . 'cms/saverestore/temp/scripts');
                $this->copyTree(ROOT . 'languages', ROOT . 'cms/saverestore/temp/languages');

                $pt = array('\.php');
                $this->copyFiles(ROOT, ROOT . 'cms/saverestore/temp', 0, $pt);
                @unlink(ROOT . 'cms/saverestore/temp/config.php');

                $this->copyTree(ROOT . 'forum', ROOT . 'cms/saverestore/temp/forum');
                @unlink(ROOT . 'cms/saverestore/temp/forum/config.php');

                if (!$design) {
                    $this->copyTree(ROOT . 'js', ROOT . 'cms/saverestore/temp/js');
                    $this->copyTree(ROOT . 'templates', ROOT . 'cms/saverestore/temp/templates');
                }

                if ($iframe) {
                    $this->echonow(" OK<br/>", 'green');
                }


            }

            // DATA
            global $data;
            if ($data) {
                if ($iframe) {
                    $this->echonow("Saving data ... ");
                }
                $tar_name .= 'data_';
                $this->backupdatabase(ROOT . 'cms/saverestore/temp/dump.sql');
                if ($iframe) {
                    $this->echonow(" OK<br/>", 'green');
                }
            }

            // FILES
            global $save_files;
            if ($save_files) {
                if ($iframe) {
                    $this->echonow("Saving files ... ");
                }
                $tar_name .= 'files_';

                $cms_dirs=scandir(ROOT.'cms');
                foreach($cms_dirs as $d) {
                    if ($d=='.' ||
                        $d=='..' ||
                        $d=='cached' ||
                        $d=='debmes' ||
                        $d=='saverestore') continue;
                    $this->copyTree(ROOT.'cms/'.$d, ROOT . 'cms/saverestore/temp/cms/'.$d);
                }                
                if ($iframe) {
                    $this->echonow(" OK<br/>", 'green');
                }
            }


            // packing into tar.gz
            $tar_name .= date('Y-m-d__h-i-s');
            $tar_name .= IsWindowsOS() ? '.tar' : '.tgz';

            if (isset($out['BACKUP']))
                $tar_name = 'backup_' . $tar_name;

            if ($iframe) {
                $this->echonow("Packing $tar_name ... ");
            }


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

            if ($iframe) {
                $this->echonow(" OK<br/>", 'green');
            }


            if (defined('SETTINGS_BACKUP_PATH') && SETTINGS_BACKUP_PATH != '' && file_exists(ROOT . 'cms/saverestore/' . $tar_name)) {
                if ($iframe) {
                    $this->echonow("Copying to " . $dest . $tar_name . " ... ");
                }
                $dest = SETTINGS_BACKUP_PATH;
                @copy(ROOT . 'cms/saverestore/' . $tar_name, $dest . $tar_name);
                if ($iframe) {
                    $this->echonow(" OK<br/>", 'green');
                }
            }


        }
        return $tar_name;
    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function restoredatabase($filename)
    {
        $mysql_path = (substr(php_uname(), 0, 7) == "Windows") ? SERVER_ROOT . "/server/mysql/bin/mysql" : 'mysql';
        $mysqlParam = " -u " . DB_USER;
        if (DB_PASSWORD != '') $mysqlParam .= " -p" . DB_PASSWORD;
        $mysqlParam .= " " . DB_NAME . " <" . $filename;
        exec($mysql_path . $mysqlParam);
        SQLExec("DELETE FROM cached_values");
        setGlobal('cycle_mainRun',time());
    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function backupdatabase($filename)
    {
        if (defined('PATH_TO_MYSQLDUMP'))
            $pathToMysqlDump = PATH_TO_MYSQLDUMP;
        else
            $pathToMysqlDump = IsWindowsOS() ? SERVER_ROOT . "/server/mysql/bin/mysqldump" : "/usr/bin/mysqldump";

        $cmd = $pathToMysqlDump . " --user=" . DB_USER . " --password=" . DB_PASSWORD . " --no-create-db --add-drop-table " . DB_NAME . ">" . $filename;
        exec($cmd);
    }

    /**
     * removeTree
     *
     * remove directory tree
     *
     * @access public
     */
    function removeTree($destination, $iframe = 0)
    {

        $res = 1;

        if (!Is_Dir($destination)) {
            return 0; // cannot create destination path
        }
        if ($dir = @opendir($destination)) {

            if ($iframe) {
                $this->echonow("Removing dir $destination ... ");
            }


            while (($file = readdir($dir)) !== false) {
                if (Is_Dir($destination . "/" . $file) && ($file != '.') && ($file != '..')) {
                    $res = $this->removeTree($destination . "/" . $file);
                } elseif (Is_File($destination . "/" . $file)) {
                    $res = @unlink($destination . "/" . $file);
                }
            }
            closedir($dir);
            $res = @rmdir($destination);

            if ($iframe) {
                $this->echonow("OK<br/>", "green");
            }


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
    function copyTree($source, $destination, $over = 0, $patterns = 0)
    {


        $res = 1;

        //Remove last slash '/' in source and destination - slash was added when copy
        $source = preg_replace("#/$#", "", $source);
        $destination = preg_replace("#/$#", "", $destination);

        if (!Is_Dir($source)) {
            return 0; // cannot create destination path
        }

        if (!Is_Dir($destination)) {
            if (!mkdir($destination,0777,true)) {
                return 0; // cannot create destination path
            }
        }


        if ($dir = @opendir($source)) {
            while (($file = readdir($dir)) !== false) {
                if (Is_Dir($source . "/" . $file) && ($file != '.') && ($file != '..')) {
                    $res = $this->copyTree($source . "/" . $file, $destination . "/" . $file, $over, $patterns);
                } elseif (Is_File($source . "/" . $file) && (!file_exists($destination . "/" . $file) || $over)) {
                    if (!is_array($patterns)) {
                        $ok_to_copy = 1;
                    } else {
                        $ok_to_copy = 0;
                        $total = count($patterns);
                        for ($i = 0; $i < $total; $i++) {
                            if (preg_match('/' . $patterns[$i] . '/is', $file)) {
                                $ok_to_copy = 1;
                            }
                        }
                    }
                    if ($ok_to_copy) {
                        @$res = copy($source . "/" . $file, $destination . "/" . $file);
                    }
                }
            }
            closedir($dir);
        }
        return $res;

    }

    function copyFile($source, $destination)
    {
        $tmp = explode('/', $destination);
        $total = count($tmp);
        if ($total > 0) {
            $d = $tmp[0];
            for ($i = 1; $i < ($total - 1); $i++) {
                $d .= '/' . $tmp[$i];
                if (!is_dir($d)) {
                    mkdir($d);
                }
            }
        }
        return copy($source, $destination);

    }

    function copyFiles($source, $destination, $over = 0, $patterns = 0)
    {

        $res = 1;

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
                if (Is_Dir($source . "/" . $file) && ($file != '.') && ($file != '..')) {
                    //$res=$this->copyTree($source."/".$file, $destination."/".$file, $over, $patterns);
                } elseif (Is_File($source . "/" . $file) && (!file_exists($destination . "/" . $file) || $over)) {
                    if (!is_array($patterns)) {
                        $ok_to_copy = 1;
                    } else {
                        $ok_to_copy = 0;
                        $total = count($patterns);
                        for ($i = 0; $i < $total; $i++) {
                            if (preg_match('/' . $patterns[$i] . '/is', $file)) {
                                $ok_to_copy = 1;
                            }
                        }
                    }
                    if ($ok_to_copy) {
                        $res = copy($source . "/" . $file, $destination . "/" . $file);
                    }
                }
            }
            closedir($dir);
        }
        return $res;
    }

    /*
    */

    function ftpget($conn_id, $local_file, $remote_file, $mode)
    {
        global $lset_dirs;
        $l_dir = dirname($local_file);
        if (!isSet($lset_dirs[$l_dir])) {
//  echo "zz";
            if (!is_dir($l_dir)) {
                $this->lmkdir($l_dir);
            }
            $lset_dirs[$l_dir] = 1;
        }
        $res = ftp_get($conn_id, $local_file, $remote_file, $mode);
        return $res;
    }

    function ftpmkdir($conn_id, $ftp_dir)
    {
        global $set_dirs;

        $tmp = explode('/', $ftp_dir);
        $res_dir = $tmp[0];
        $tmpCnt = count($tmp);

        for ($i = 1; $i < $tmpCnt; $i++) {
            $res_dir .= '/' . $tmp[$i];

            if (!isset($set_dirs[$res_dir])) {
                $set_dirs[$res_dir] = 1;

                if (!@ftp_chdir($conn_id, $res_dir)) {
                    ftp_mkdir($conn_id, $res_dir);
                }
            }
        }
    }

    function ftpdelete($conn_id, $filename)
    {
        $res = ftp_delete($conn_id, $filename);
        return $res;
    }


    function ftpput($conn_id, $remote_file, $local_file, $mode)
    {
        global $set_dirs;
        $ftp_dir = dirname($remote_file);
        if (!IsSet($set_dirs[$ftp_dir])) {
            if (!@ftp_chdir($conn_id, $ftp_dir)) {
                $this->ftpmkdir($conn_id, $ftp_dir);
            }
            $set_dirs[$ftp_dir] = 1;
        }
        $res = ftp_put($conn_id, $remote_file, $local_file, $mode);
        return $res;
    }


    /**
     * FrontEnd
     *
     * Module frontend
     *
     * @access public
     */
    function usual(&$out)
    {
        $this->admin($out);
    }

    /**
     * Install
     *
     * Module installation routine
     *
     * @access private
     */
    function install($parent_name = "")
    {
        if (!Is_Dir(ROOT . "./cms/saverestore")) {
            mkdir(ROOT . "./cms/saverestore", 0777);
        }
        parent::install($parent_name);
    }
// --------------------------------------------------------------------
}

/*
*
* TW9kdWxlIGNyZWF0ZWQgU2VwIDE2LCAyMDA4IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>
