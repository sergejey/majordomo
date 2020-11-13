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
echo '<body style="height: auto;overflow: auto;padding: 10px;background: black;color: white;border-radius: 5px;">'; 

$out = array();

if ($backup) {

    logAction('system_backup');
    $res = $sv->dump($out, 1);
    if ($res) {
        echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-chevron-right"></i> Удаляем временные файлы...</div>');
        removeTree(ROOT . 'cms/saverestore/temp');
        echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-usd"></i> Готово!</div>');
        echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-usd"></i> Резервная копия готова!</div>');
		sleep(1);
        echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-usd"></i> Консоль будет закрыта автоматически...</div>');
		sleep(1);
		echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-chevron-right"></i> Запрос редиректа...</div>');
		sleep(2);
        echonow('<script language="javascript">window.top.location.href="' . ROOTHTML . 'admin.php?md=panel&action=saverestore&ok_msg=' . urlencode("Backup complete!") . '";</script>');
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
            echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-chevron-right"></i> Удаляем временные файлы...</div>');
            removeTree(ROOT . 'cms/saverestore/temp');
            @unlink(ROOT . "cms/modules_installed/control_modules.installed");
            echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-usd"></i> Готово!</div>');
            echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-usd"></i> Обновления установлены!</div>');
            if ($with_extensions) {
                echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-chevron-right"></i> Запрос редиректа в маркет дополнений...</div>');
				sleep(2);
                echonow('<script language="javascript">window.top.location.href="' . ROOTHTML . 'admin.php?action=market&mode=iframe&mode2=update_all";</script>');
            } else {
                echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-chevron-right"></i> Запрос перезагрузки MajorDoMo</div>');
                @SaveFile(ROOT . 'reboot', 'updated');
                echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-usd"></i> Система перезагружена!</div>');
				sleep(2);
                echonow('<div><i style="font-size: 7pt;" class="glyphicon glyphicon-chevron-right"></i> Запрос редиректа...</div>');
				sleep(2);
                echonow('<script language="javascript">window.top.location.href="' . ROOTHTML . 'admin.php?md=panel&action=saverestore&ok_msg=' . urlencode("Updates Installed!") . '";</script>');
            }
        }
    }
}

echo "</body>";
echo "</html>";

