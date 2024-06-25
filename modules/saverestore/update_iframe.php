<?php


chdir(dirname(__FILE__) . '/../../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

Define('WAIT_FOR_MAIN_CYCLE',0);
set_time_limit(0);

include_once("./load_settings.php");
include_once(DIR_MODULES . "saverestore/saverestore.class.php");

$sv = new saverestore();

$with_extensions=gr('with_extensions');
$with_backup=gr('with_backup');

header('X-Accel-Buffering: no');
echo "<html>";
echo "<head>";
echo '<link rel="stylesheet" href="/3rdparty/bootstrap/css/bootstrap.min.css" type="text/css"><script type="text/javascript" src="/3rdparty/bootstrap/js/bootstrap.min.js"></script>';
echo "</head>";
echo '<body style="height: auto;overflow: auto;padding: 10px;font-family: Consolas, Verdana;background: #000080;color: #c0c0c0;border-radius: 5px;">'; 

$out = array();

if ($backup) {

    logAction('system_backup');
    $res = $sv->dump($out, 1);
    if ($res) {
        echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-chevron-right"></i> '.LANG_UPDATEBACKUP_DELETE_TEMP_FILES.'</div>');
        removeTree(ROOT . 'cms/saverestore/temp');
        echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-usd"></i> '.LANG_UPDATEBACKUP_DONE.'</div>');
        echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-usd"></i> '.LANG_UPDATEBACKUP_BACKUP_DONE.'</div>');
		sleep(1);
        echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-usd"></i> '.LANG_UPDATEBACKUP_CLOSE_CONSOLE_AUTO.'</div>');
		sleep(1);
		echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-chevron-right"></i> '.LANG_UPDATEBACKUP_GET_REDIRECT.'</div>');
		sleep(2);
        echonow('<script language="javascript">window.top.location.href="' . ROOTHTML . 'admin.php?md=panel&action=saverestore&ok_msg=' . urlencode(LANG_UPDATEBACKUP_BACKUP_DONE) . '";</script>');
    }

} else {

    $res = $sv->admin($out);
    $res = $sv->getLatest($out, 1, $with_backup);
    if ($res) {
        logAction('system_update');
        global $restore;
        $restore = 'master.tgz';
        $folder = 'majordomo-master';
        $basename = basename($sv->url);
        if ($basename != 'master.tar.gz') {
            $basename = str_replace('.tar.gz', '', $basename);
            $folder = str_replace('master', $basename, $folder);
        }
        $res = $sv->upload($out, 1);
        if ($res) {
            echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-chevron-right"></i> '.LANG_UPDATEBACKUP_DELETE_TEMP_FILES.'</div>');
            removeTree(ROOT . 'cms/saverestore/temp');
            @unlink(ROOT . "cms/modules_installed/control_modules.installed");
            echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-usd"></i> '.LANG_UPDATEBACKUP_DONE.'</div>');
            echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-usd"></i> '.LANG_UPDATEBACKUP_UPDATE_GET_DONE.'</div>');
            if ($with_extensions) {
                echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-chevron-right"></i> '.LANG_UPDATEBACKUP_GET_REDIRECT_TO_MERKET.'</div>');
				sleep(2);
                echonow('<script language="javascript">window.top.location.href="' . ROOTHTML . 'admin.php?action=market&mode=iframe&mode2=update_all";</script>');
            } else {
                echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-chevron-right"></i> '.LANG_UPDATEBACKUP_REQUEST_REBOOT.'</div>');
                @SaveFile(ROOT . 'reboot', 'updated');
                echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-usd"></i> '.LANG_UPDATEBACKUP_REBOOT_WELL_DONE.'</div>');
				sleep(2);
                echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-chevron-right"></i> '.LANG_UPDATEBACKUP_GET_REDIRECT.'</div>');
				sleep(2);
                echonow('<script language="javascript">window.top.location.href="' . ROOTHTML . 'admin.php?md=panel&action=saverestore&ok_msg=' . urlencode(LANG_UPDATEBACKUP_UPDATE_GET_DONE) . '";</script>');
            }
        }
    }
}

echo "</body>";
echo "</html>";

