<?php

if ($this->app_action) {

    $out['APP_ACTION'] = $this->app_action;
    $rec = SQLSelectOne("SELECT * FROM project_modules WHERE NAME LIKE '" . DBSafe($this->app_action) . "'");
    if ($rec['ID']) {
        $out['APP_TITLE'] = $rec['TITLE'];
        if ($this->app_action == 'devices') {
            $out['APP_TITLE'] = '';
        }
    }

} else {

    $files = scandir(DIR_MODULES, 0);
    $total = count($files);
    $apps = array();
    for ($i = 0; $i < $total; $i++) {
        if ($files[$i] == '.' || $files[$i] == '..' || !is_dir(DIR_MODULES . $files[$i])) continue;
        if (file_exists(DIR_MODULES . $files[$i] . '/app')) {
            //echo $files[$i]."!<br/>";
            $apps[] = $files[$i];
        } else {
            //echo $files[$i]."<br/>";
        }
    }

    $apps[]='panel';

    $project_modules = SQLSelect("SELECT * FROM project_modules");
    $modules = array();
    foreach ($project_modules as $k => $v) {
        $modules[$v['NAME']] = $v;
    }

    $res = array();
    $total = count($apps);
    for ($i = 0; $i < $total; $i++) {
        $rec = array();
        $rec['NAME'] = $apps[$i];
        if (isset($modules[$rec['NAME']])) {
            $rec['TITLE'] = $modules[$rec['NAME']]['TITLE'];
        } elseif ($rec['NAME']=='panel') {
            $rec['TITLE'] = LANG_CONTROL_PANEL;
        } else {
            $rec['TITLE'] = $rec['NAME'];
        }
        if (file_exists(ROOT . 'img/modules/' . $rec['NAME'] . '.png')) {
            $rec['ICON'] = $rec['NAME'] . '.png';
        } else {
            $rec['ICON'] = 'default.png';
        }
        $res[] = $rec;
    }

    $out['APPS'] = $res;

}

//