<?php

$mode2 = gr('mode2');

$rec = SQLSelectOne("SELECT * FROM project_modules WHERE NAME='" . DBSafe($name) . "'");
$rec['NAME'] = $name;

if ($mode2 == 'download') {
    //dprint("download");
    if (is_dir(ROOT . 'cms/saverestore/temp')) {
        removeTree(ROOT . 'cms/saverestore/temp');
    }
    mkdir(ROOT . 'cms/saverestore/temp', 0777);
    mkdir(ROOT . 'cms/saverestore/temp/' . $name, 0777);

    mkdir(ROOT . 'cms/saverestore/temp/' . $name . "/modules", 0777);
    mkdir(ROOT . 'cms/saverestore/temp/' . $name . "/modules/" . $name, 0777);
    copyTree(ROOT . 'modules/' . $name, ROOT . 'cms/saverestore/temp/' . $name . "/modules/" . $name);

    mkdir(ROOT . 'cms/saverestore/temp/' . $name . "/templates", 0777);
    mkdir(ROOT . 'cms/saverestore/temp/' . $name . "/templates/" . $name, 0777);
    copyTree(ROOT . 'templates/' . $name, ROOT . 'cms/saverestore/temp/' . $name . "/templates/" . $name);

    if (file_exists(ROOT . 'scripts/cycle_' . $name . '.php')) {
        mkdir(ROOT . 'cms/saverestore/temp/' . $name . "/scripts", 0777);
        copy(ROOT . 'scripts/cycle_' . $name . '.php', ROOT . 'cms/saverestore/temp/' . $name . "/scripts/cycle_" . $name . ".php");
    }

    if (file_exists(ROOT . 'cms/modules_installed/' . $name . '.files')) {
        $data = Loadfile(ROOT . 'cms/modules_installed/' . $name . '.files');
        $tmp = explode("\n", $data);
        foreach ($tmp as $filename) {
            $filename = str_replace(ROOT, '', $filename);
            $target_path = ROOT . 'cms/saverestore/temp/' . $name . "/" . $filename;
            if (is_file(ROOT . $filename) && !is_file($target_path)) {
                copyFile(ROOT . $filename, $target_path);
            }
        }
    }


    $tar_name = $name . '_' . date('Y-m-d__H-i-s');
    $tar_name .= IsWindowsOS() ? '.tar' : '.tgz';

    if (IsWindowsOS()) {
        $result = exec('tar.exe --strip-components=2 -C ./cms/saverestore/temp/ -cvf ./cms/saverestore/' . $tar_name . ' ./');
        $new_name = str_replace('.tar', '.tar.gz', $tar_name);
        $result = exec('gzip.exe ./cms/saverestore/' . $tar_name);
        if (file_exists('./cms/saverestore/' . $new_name)) {
            $tar_name = $new_name;
        }
    } else {
        chdir(DOC_ROOT . DIRECTORY_SEPARATOR . 'cms/saverestore/temp');
        exec('tar cvzf ../' . $tar_name . ' .');
        chdir('../../../');
    }

    removeTree(ROOT . 'cms/saverestore/temp');
    $this->redirect(ROOTHTML . 'cms/saverestore/' . $tar_name);

} elseif ($mode2 == "update") {
    global $title;
    global $category;
    $rec['TITLE'] = $title;
    $rec['CATEGORY'] = $category;
    SQLUpdate("project_modules", $rec);
    $this->redirect("?name=$name&mode=edit");
} elseif ($mode2 == "show") {
    if ($rec['HIDDEN']) {
        $rec['HIDDEN'] = 0;
    } else {
        $rec['HIDDEN'] = 1;
    }
    SQLUpdate('project_modules', $rec);
    $this->redirect("?");

} elseif ($mode2 == "ignore") {
    SQLExec("DELETE FROM ignore_updates WHERE NAME LIKE '" . DBSafe($rec['NAME']) . "'");
    $tmp = array();
    $tmp['NAME'] = $rec['NAME'];
    SQLInsert('ignore_updates', $tmp);
    $this->redirect("?");
} elseif ($mode2 == "unignore") {
    SQLExec("DELETE FROM ignore_updates WHERE NAME LIKE '" . DBSafe($rec['NAME']) . "'");
    $this->redirect("?");
} elseif ($mode2 == "install") {
    $rec = SQLSelectOne("SELECT * FROM project_modules WHERE NAME='" . $name . "'");
    //SQLExec("DELETE FROM project_modules WHERE NAME='" . $name . "'");
    @unlink(ROOT . 'cms/modules_installed/' . $name . ".installed");
    include_once(DIR_MODULES . $name . "/" . $name . ".class.php");
    $obj = "\$object$i";
    $code .= "$obj=new " . $name . ";\n";
    setEvalCode($code);
    @eval($code);
    setEvalCode();
    // add module to control access
    global $session;
    $user = SQLSelectOne("SELECT * FROM admin_users WHERE LOGIN='" . DBSafe($session->data["USER_NAME"]) . "'");
    if ($user['ID'] && !Is_Integer(strpos($user["ACCESS"], $name))) {
        if ($user["ACCESS"] != '') {
            $user["ACCESS"] .= ",$name";
        } else {
            $user["ACCESS"] = $name;
        }
        SQLUpdate('admin_users', $user);
    }
    SQLExec("UPDATE project_modules SET HIDDEN='" . (int)$rec['HIDDEN'] . "' WHERE NAME='" . $name . "'");
    // redirect to edit
    $this->redirect("?name=$name&mode=edit");
} elseif ($mode2 == 'uninstall') {
    SQLExec("DELETE FROM project_modules WHERE NAME='" . $name . "'");
    @unlink(ROOT . 'cms/modules_installed/' . $name . ".installed");
    if (is_dir(DIR_MODULES . $name)) {
        include_once(DIR_MODULES . $name . '/' . $name . '.class.php');
        SQLExec("DELETE FROM project_modules WHERE NAME LIKE '" . DBSafe($name) . "'");
        $code = '$plugin = new ' . $name . '();$plugin->uninstall();';
        setEvalCode($code);
        eval($code);
        setEvalCode();
        removeTree(DIR_MODULES . $name);
        removeTree(DIR_TEMPLATES . $name);
        $cycle_name = ROOT . 'scripts/cycle_' . $name . '.php';
        if (file_exists($cycle_name)) {
            @unlink($cycle_name);
        }
        removeMissingSubscribers();
    }
    $this->redirect("?");
}

if (preg_match('|<#(.*?)#>|si', $rec['TITLE'], $arr)) {
    $rec['TITLE'] = constant($arr[1]);
} else {
    $rec['TITLE'] = $rec['TITLE'];
}

outHash($rec, $out);

//Получим конфиг модуля

include_once(DIR_MODULES . $name . '/' . $name . '.class.php');
$module = new $name();

$genConfig = [];
$iter = 0;

$config = $module->getConfig();
if (is_array($config)) {
    foreach ($config as $key => $value) {
        $genConfig[$iter]['KEY'] = $key;
        $genConfig[$iter]['VALUE'] = $value;
        $iter++;
    }
    $out['MODULE_CONFIG'] = $genConfig;
}


$url = "https://connect.majordomohome.com/market/?op=list&search=" . urlencode($rec['TITLE']);
$marketInfo = file_get_contents($url);
$marketInfo = json_decode($marketInfo, TRUE);
if (isset($marketInfo["PLUGINS"][0])) {
    $marketInfo = $marketInfo["PLUGINS"][0];
    if (is_array($marketInfo)) {
        $out['MARKET_ID'] = $marketInfo['ID'];
        $out['MARKET_REPOSITORY_URL'] = $marketInfo['REPOSITORY_URL'];
        $out['MARKET_AUTHOR'] = $marketInfo['AUTHOR'];
        $out['MARKET_SUPPORT_URL'] = $marketInfo['SUPPORT_URL'];
        $out['MARKET_DESCRIPTION_RU'] = $marketInfo['DESCRIPTION_RU'];
        $out['MARKET_LATEST_VERSION'] = $marketInfo['LATEST_VERSION'];
        $out['MARKET_PRICE'] = $marketInfo['PRICE'];
        $out['MARKET_URL'] = $marketInfo['URL'];
        $out['MARKET_LATEST_VERSION_COMMENT'] = $marketInfo['LATEST_VERSION_COMMENT'];
        $out['MARKET_LATEST_VERSION_URL'] = $marketInfo['LATEST_VERSION_URL'];
    }
}