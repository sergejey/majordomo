<?php
/*
* @version 0.1 (wizard)
*/
if ($this->owner->name == 'panel') {
    $out['CONTROLPANEL'] = 1;
}
$table_name = 'plans';
$rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");

if ($this->tab=='preview' && is_file(ROOT.'cms/plans/'.$rec['IMAGE'])) {
    $out['CONTENT']=$this->getPreview($rec['ID']);
}

if ($this->tab == 'states') {
    include_once(DIR_MODULES.$this->name.'/states.inc.php');
}

if ($this->tab == 'components') {
    include_once(DIR_MODULES.$this->name.'/components.inc.php');
}

if ($this->mode=='update' && $this->tab=='css') {
    $rec['CUSTOM_CSS']=gr('css');
    SQLUpdate('plans',$rec);
    $out['OK']=1;
    $this->redirect("?view_mode=".$this->view_mode."&id=".$rec['ID']."&tab=preview");
}

if ($this->mode=='update' && $this->tab=='javascript') {
    $rec['CUSTOM_JAVASCRIPT']=gr('javascript');
    SQLUpdate('plans',$rec);
    $out['OK']=1;
    $this->redirect("?view_mode=".$this->view_mode."&id=".$rec['ID']."&tab=preview");
}

if ($this->mode == 'update' && $this->tab=='') {
    $ok = 1;
    //updating '<%LANG_TITLE%>' (varchar, required)
    $rec['TITLE'] = gr('title');
    if ($rec['TITLE'] == '') {
        $out['ERR_TITLE'] = 1;
        $ok = 0;
    }

    $rec['NEED_ZOOM']=gr('need_zoom','int');
    $rec['AUTO_ZOOM']=gr('auto_zoom','int');

    //UPDATING RECORD
    if ($ok) {
        if ($rec['ID']) {
            SQLUpdate($table_name, $rec); // update
        } else {
            $new_rec = 1;
            $rec['ID'] = SQLInsert($table_name, $rec); // adding new record
        }
        global $image;
        global $image_name;
        if (is_file($image) && preg_match('/\.svg/is',$image_name)) {
            if (!is_dir(ROOT.'cms/plans')) {
                umask(0);
                mkdir(ROOT.'cms/plans',0777);
            }
            move_uploaded_file($image,ROOT.'cms/plans/'.$rec['ID'].'.svg');
            $rec['IMAGE']=$rec['ID'].'.svg';
            SQLUpdate($table_name,$rec);
        }


        $out['OK'] = 1;
        $this->redirect("?view_mode=".$this->view_mode."&id=".$rec['ID']."&tab=preview");
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

if ($rec['ID'] && ($this->tab=='css' || $this->tab=='javascript')) {
    $out['ITEMS']=$this->getImageItems($rec['ID']);
}
