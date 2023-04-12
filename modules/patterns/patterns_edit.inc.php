<?php

$out['PARENT_ID'] = gr('parent_id');

if (defined('SETTINGS_CODEEDITOR_TURNONSETTINGS')) {
    $out['SETTINGS_CODEEDITOR_TURNONSETTINGS'] = SETTINGS_CODEEDITOR_TURNONSETTINGS;
    $out['SETTINGS_CODEEDITOR_UPTOLINE'] = SETTINGS_CODEEDITOR_UPTOLINE;
    $out['SETTINGS_CODEEDITOR_SHOWERROR'] = SETTINGS_CODEEDITOR_SHOWERROR;
}

if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}
$table_name = 'patterns';
$rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");

if ($this->mode == 'update') {
    $ok = 1;
    $rec['TITLE'] = gr('title');
    if ($rec['TITLE'] == '') {
        $out['ERR_TITLE'] = 1;
        $ok = 0;
    }

    $rec['PATTERN'] = gr('pattern');
    $script = gr('script');
    $old_code = $rec['SCRIPT'];
    $rec['SCRIPT'] = trim($script);

    if ($rec['SCRIPT'] != '') {
        $errors = php_syntax_error($rec['SCRIPT']);

        if ($errors) {
            $out['ERR_LINE'] = preg_replace('/[^0-9]/', '', substr(stristr($errors, 'php on line '), 0, 18)) - 2;
            $out['ERR_CODE'] = 1;
            $errorStr = explode('Parse error: ', htmlspecialchars(strip_tags(nl2br($errors))));
            $errorStr = explode('Errors parsing', $errorStr[1]);
            $errorStr = explode(' in ', $errorStr[0]);
            //var_dump($errorStr);
            $out['ERRORS'] = $errorStr[0];
            $out['ERR_FULL'] = $errorStr[0] . ' ' . $errorStr[1];
            $out['ERR_OLD_CODE'] = $old_code;
            $ok = 0;
        }
    }


    if (file_exists(ROOT . "lib/phpmorphy/common.php")) {
        $rec['USEMORPHY'] = gr('usemorphy', 'int');
    }

    $script_exit = gr('script_exit');
    $use_script_exit = gr('use_script_exit');
    if (!$use_script_exit) {
        $script_exit = '';
    }

    $rec['SCRIPT_EXIT'] = trim($script_exit);

    if ($rec['SCRIPT_EXIT'] != '') {
        $errors = php_syntax_error($rec['SCRIPT_EXIT']);
        if ($errors) {
            $out['ERR_SCRIPT_EXIT'] = 1;
            $out['ERRORS_SCRIPT_EXIT'] = nl2br($errors);
            $ok = 0;
        }
    }


    if (!$rec['ID']) {
        $rec['PATTERN_TYPE'] = gr('pattern_type', 'int');
    }


    $run_type = gr('run_type');

    if ($run_type == 'script') {
        $rec['SCRIPT_ID'] = gr('script_id', 'int');
    } else {
        $rec['SCRIPT_ID'] = 0;
    }


    if ($rec['SCRIPT'] != '' && $run_type == 'code') {
        //echo $content;
        $errors = php_syntax_error($rec['SCRIPT']);
        if ($errors) {
            $out['ERR_SCRIPT'] = 1;
            $out['ERRORS'] = nl2br($errors);
            $ok = 0;
        }
    }
    $rec['TIME_LIMIT'] = gr('time_limit', 'int');
    $rec['IS_CONTEXT'] = gr('is_context', 'int');
    $rec['IS_COMMON_CONTEXT'] = gr('is_common_context', 'int');
    $rec['MATCHED_CONTEXT_ID'] = gr('matched_context_id', 'int');
    $rec['TIMEOUT'] = gr('timeout', 'int');
    $rec['IS_LAST'] = gr('is_last', 'int');
    $rec['PRIORITY'] = gr('priority', 'int');
    $rec['SKIPSYSTEM'] = gr('skipsystem', 'int');
    $rec['ONETIME'] = gr('onetime', 'int');
    $rec['TIMEOUT_CONTEXT_ID'] = gr('timeout_context_id', 'int');

    $timeout_script = gr('timeout_script');
    if ($timeout_script != '') {
        $rec['TIMEOUT_SCRIPT'] = $timeout_script;
        $errors = php_syntax_error($rec['TIMEOUT_SCRIPT']);
        if ($errors) {
            $out['ERR_TIMEOUT_SCRIPT'] = 1;
            $out['ERRORS_TIMEOUT_SCRIPT'] = nl2br($errors);
            $ok = 0;
        }
    } else {
        $rec['TIMEOUT_SCRIPT'] = '';
    }


    $rec['PARENT_ID'] = gr('parent_id','int');

    if ($rec['PATTERN_TYPE'] == 1) {
        $rec['PARENT_ID'] = 0;
        $rec['LINKED_OBJECT'] = gr('linked_object');
        $rec['LINKED_PROPERTY'] = gr('linked_property');
        $rec['CONDITION'] = gr('condition', 'int');
        $rec['CONDITION_VALUE'] = gr('condition_value');
        if ($rec['LINKED_OBJECT'] && $rec['LINKED_PROPERTY']) {
            addLinkedProperty($rec['LINKED_OBJECT'], $rec['LINKED_PROPERTY'], $this->name);
        }
    }


    //UPDATING RECORD
    if ($ok) {
        if ($rec['ID']) {
            SQLUpdate($table_name, $rec); // update
        } else {
            $new_rec = 1;
            $rec['ID'] = SQLInsert($table_name, $rec); // adding new record
        }
        $out['OK'] = 1;
    } else {
        $out['ERR'] = 1;
    }
}

if (is_array($rec)) {
    foreach ($rec as $k => $v) {
        if (!is_array($v)) {
            $rec[$k] = htmlspecialchars($v);
        }
    }
}
outHash($rec, $out);

$out['SCRIPTS'] = SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");
$out['LOG'] = nl2br($rec['LOG']);
$out['CONTEXTS'] = SQLSelect("SELECT ID, TITLE FROM patterns WHERE IS_CONTEXT=1 AND ID!=" . (int)$rec['ID'] . " ORDER BY PARENT_ID, TITLE");

if ($rec['ID']) {
    $out['CHILDREN'] = SQLSelect("SELECT ID, TITLE FROM patterns WHERE PARENT_ID='" . (int)$rec['ID'] . "'");
    $out['SAME_LEVEL'] = SQLSelect("SELECT ID, TITLE FROM patterns WHERE PARENT_ID='" . (int)$rec['PARENT_ID'] . "'");
}

if (file_exists(ROOT . "lib/phpmorphy/common.php")) {
    $out['SHOW_MORPHY'] = 1;
}

