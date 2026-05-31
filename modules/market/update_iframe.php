<?php

Define('ALLOW_RUNNING_WITH_ERRORS', 1);
chdir(dirname(__FILE__) . '/../../');

include_once("./config.php");
include_once("./lib/loader.php");
include_once("./lib/threads.php");

Define('WAIT_FOR_MAIN_CYCLE', 0);
set_time_limit(0);

include_once("./load_settings.php");
include_once(DIR_MODULES . "market/market.class.php");

$mkt = new market();
$mkt->category_id = 'all';

$_REQUEST['op'] = 'iframe';

header('X-Accel-Buffering: no');
echo "<html>";
echo "<head>";
echo '<link rel="stylesheet" href="/3rdparty/bootstrap/css/bootstrap.min.css" type="text/css"><script type="text/javascript" src="/3rdparty/bootstrap/js/bootstrap.min.js"></script>';
echo "</head>";
echo '<body style="height: auto;overflow: auto;padding: 10px;font-family: Consolas, Verdana;background: #000080;color: #c0c0c0;border-radius: 5px;">';

$out = array();

if ($names != '') {
    $names = explode(',', $names);
}

if ($mode2 == 'uploaded' && $name != '') {
    $out = array();
    $mkt->admin($out);
    $filename = ROOT . 'cms/saverestore/' . $name;
    if (file_exists($filename)) {
        logAction('market_install', $name);
        $mkt->echonow("Uploaded " . $name);
        $folder = str_replace('.tgz', '', $name);
        $restore = $name;
        $version = 'Unknown version';
        $res = $mkt->upload($out, 1);
        if ($res) {
            $mkt->removeTree(ROOT . 'cms/saverestore/temp');
            $mkt->echonow("Redirecting to main page...");
            $mkt->echonow('<script language="javascript">window.top.location.href="' . ROOTHTML . 'admin.php?md=panel&action=market&ok_msg=' . urlencode($res) . '";</script>');
        }
    }
}

if ($mode2 == 'install' && $name != '') {
    // install/update one extension

    $link = gr('link');

    $out = array();
    $mkt->admin($out);
    if (!$mkt->url) {
        echo "Error getting download URL";
        exit;
    }

    $url = '';
    if ($link != '') {
        if (preg_match('/\/commit\/(.+?)$/', $link, $m)) {
            $commit = $m[1];
            $url = str_replace('/commit/','/archive/',$link);
            $url.='.tar.gz';
        }
    }

    if (!$url) {
        $url = $mkt->url;
    }

    $res = $mkt->getLatest($out, $url, $name, $mkt->version, 1);
    if ($res) {
        logAction('market_install', $name);
        $folder = $name;
        $restore = $name . '.tgz';
        $version = $mkt->version;
        $res = $mkt->upload($out, 1);
        if ($res) {
            $mkt->removeTree(ROOT . 'cms/saverestore/temp');
            $mkt->echonow("Redirecting to main page...");
            $mkt->echonow('<script language="javascript">window.top.location.href="' . ROOTHTML . 'admin.php?md=panel&action=market&ok_msg=' . urlencode($res) . '";</script>');
        }
    }
}

if ($mode2 == 'install_multiple' && $names != '') {
    // install/update multiple extensions
    logAction('market_update', implode(', ', $names));
    $mkt->admin($out);
    $res = $mkt->updateAll($mkt->selected_plugins, 1);
    if ($res) {
        $mkt->removeTree(ROOT . 'cms/saverestore/temp');
        $mkt->echonow("Rebooting system ... ");
        @SaveFile(ROOT . 'reboot', 'updated');
        $mkt->echonow(" OK<br/> ", 'green');
        $mkt->echonow('<script language="javascript">window.top.location.href="' . ROOTHTML . 'admin.php?md=panel&action=market&ok_msg=' . urlencode($res) . '";</script>');
    }
}


if ($mode2 == 'update_new') {
    logAction('market_update', 'Update new');
    $mkt->admin($out);
    if (count($mkt->can_be_updated_new) > 0) {
        $res = $mkt->updateAll($mkt->can_be_updated_new, 1);
        if ($res) {
            $mkt->removeTree(ROOT . 'cms/saverestore/temp');
            $mkt->echonow("Rebooting system ... ");
            @SaveFile(ROOT . 'reboot', 'updated');
            $mkt->echonow(" OK<br/> ", 'green');
            $mkt->echonow('<script language="javascript">window.top.location.href="' . ROOTHTML . 'admin.php?md=panel&action=market&ok_msg=' . urlencode($res) . '";</script>');
        }
    } else {
        $res = 'Nothing to update.';
        $mkt->echonow("Nothing to update ... ");
        $mkt->echonow('<script language="javascript">window.top.location.href="' . ROOTHTML . 'admin.php?md=panel&action=market&ok_msg=' . urlencode($res) . '";</script>');
    }
}

if ($mode2 == 'update_all') {
    // update all extensions
    logAction('market_update', 'Update all');
    $mkt->admin($out);
    $res = $mkt->updateAll($mkt->can_be_updated, 1);
    if ($res) {
        $mkt->removeTree(ROOT . 'cms/saverestore/temp');
        $mkt->echonow("Rebooting system ... ");
        @SaveFile(ROOT . 'reboot', 'updated');
        $mkt->echonow(" OK<br/> ", 'green');
        $mkt->echonow('<script language="javascript">window.top.location.href="' . ROOTHTML . 'admin.php?md=panel&action=market&ok_msg=' . urlencode($res) . '";</script>');
    }
}

if ($mode2 == 'uninstall' && $name != '') {
    // remove one extension
    logAction('market_uninstall', $name);
    $res = $mkt->uninstallPlugin($name, 1);
    if ($res) {
        $mkt->echonow("Redirecting to main page...");
        $mkt->echonow('<script language="javascript">window.top.location.href="' . ROOTHTML . 'admin.php?md=panel&action=market&ok_msg=' . urlencode($res) . '";</script>');
    }
}


echo "</body>";
echo "</html>";
