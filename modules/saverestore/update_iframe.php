<?php

chdir(dirname(__FILE__) . '/../../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

set_time_limit(0);

include_once("./load_settings.php");
include_once(DIR_MODULES . "saverestore/saverestore.class.php");

$sv = new saverestore();

global $with_extensions;

header('X-Accel-Buffering: no');
echo "<html>";
echo "<body>";


$out = array();

if ($backup) {

    logAction('system_backup');
    $res = $sv->dump($out, 1);
    if ($res) {

        echonow("Removing temporary files ... ");
        removeTree(ROOT . 'cms/saverestore/temp');
        echonow(" OK<br/> ", 'green');


        echonow("Redirecting to main page...");
        echonow('<script language="javascript">window.top.location.href="' . ROOTHTML . 'admin.php?md=panel&action=saverestore&ok_msg=' . urlencode("Backup complete!") . '";</script>');
    }

} else {

    $res = $sv->admin($out);
    $res = $sv->getLatest($out, 1);
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
            echonow("Removing temporary files ... ");
            removeTree(ROOT . 'cms/saverestore/temp');
            @unlink(ROOT . "cms/modules_installed/control_modules.installed");
            echonow(" OK<br/> ", 'green');
            echonow("<b>Main system updated!</b><br/>", 'green');
            if ($with_extensions) {
                echonow("Redirecting to extensions update...");
                echonow('<script language="javascript">window.top.location.href="' . ROOTHTML . 'admin.php?action=market&mode=iframe&mode2=update_all";</script>');
            } else {
                echonow("Rebooting system ... ");
                @SaveFile(ROOT . 'reboot', 'updated');
                echonow(" OK<br/> ", 'green');

                echonow("Redirecting to main page...");
                echonow('<script language="javascript">window.top.location.href="' . ROOTHTML . 'admin.php?md=panel&action=saverestore&ok_msg=' . urlencode("Updates Installed!") . '";</script>');
            }
        }
    }
}

echo "</body>";
echo "</html>";

