<?php
/*
* @version 0.2 (auto-set)
*/

if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}
$table_name = 'users';
$rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
if ($this->mode == 'update') {
    $ok = 1;
    $rec['USERNAME'] = gr('username');
    if ($rec['USERNAME'] == '') {
        $out['ERR_USERNAME'] = 1;
        $ok = 0;
    }
    $rec['NAME'] = gr('name');
    if ($rec['NAME'] == '') {
        $out['ERR_NAME'] = 1;
        $ok = 0;
    }
    $rec['EMAIL'] = gr('email');
    $rec['MOBILE'] = gr('mobile');
    $rec['COLOR'] = gr('color');
    $rec['IS_ADMIN'] = (int)gr('is_admin');
    $rec['IS_DEFAULT'] = (int)gr('is_default');
    if (gr('passwordnew') && gr('passwordnew') == gr('passwordrepeat')) {
        $rec['PASSWORD'] = hash('sha512', gr('passwordnew'));
    } else if (gr('passwordnew') && gr('passwordnew') != gr('passwordrepeat')) {
        $out['ERR_PASSWORD'] = 1;
        $ok = 0;
    } else if (gr('passwordnew') == '' && gr('passwordrepeat') == '' && !gr('use_password', 'int')) {
        $rec['PASSWORD'] = '';
    }
    $rec['LINKED_OBJECT'] = trim(gr('linked_object'));
    $rec['HOST'] = gr('host');

    global $avatar;
    global $avatar_name;
    if ($avatar != '') {
        if ($rec['AVATAR'] != '') {
            @unlink(ROOT . 'cms/avatars/' . $rec['AVATAR']);
        }
        $rec['AVATAR'] = $rec['ID'] . '_' . $avatar_name;
        copy($avatar, ROOT . 'cms/avatars/' . $rec['AVATAR']);
    }

    //UPDATING RECORD
    if ($ok) {

        if ($rec['ID']) {
            SQLUpdate($table_name, $rec); // update
        } else {
            $new_rec = 1;
            $rec['ID'] = SQLInsert($table_name, $rec); // adding new record
        }
        if ($rec['IS_DEFAULT']) {
            SQLExec("UPDATE users SET IS_DEFAULT=0 WHERE ID!=" . $rec['ID']);
        }
        $out['OK'] = 1;

        if (!$rec['LINKED_OBJECT']) {
            $user_title = getUserObjectByTitle($rec['ID'], 1);
            $rec['LINKED_OBJECT'] = $user_title;
            SQLUpdate($table_name, $rec); // update
        }


        $this->redirect("?");

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

if ($rec['PASSWORD'] != '' && hash('sha512', '') == $rec['PASSWORD']) {
    $rec['PASSWORD'] = '';
}

outHash($rec, $out);

