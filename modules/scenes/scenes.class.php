<?php
/**
 * Scenes
 *
 * Scenes
 *
 * @package project
 * @author Serge J. <sergejey@gmail.com>
 * @copyright https://majordomohome.com/ (c)
 * @version 0.1 (wizard, 10:05:38 [May 24, 2012])
 */

if (!defined('DEF_TYPE_OPTIONS')) {
    Define('DEF_TYPE_OPTIONS', 'img=Image|html=HTML'); // options for 'TYPE'
}

if (!defined('DEF_CONDITION_OPTIONS')) {
    Define('DEF_CONDITION_OPTIONS', '1=Equa|2=More|3=Less|4=Not equal'); // options for 'CONDITION'
}
//
//
class scenes extends module
{
    /**
     * scenes
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "scenes";
        $this->title = "<#LANG_MODULE_SCENES#>";
        $this->module_category = "<#LANG_SECTION_OBJECTS#>";
        $this->checkInstalled();
    }

    /**
     * saveParams
     *
     * Saving module parameters
     *
     * @access public
     */
    function saveParams($data = 0)
    {
        $p = array();
        if (isset($this->id)) {
            $p["id"] = $this->id;
        }
        if (isset($this->view_mode)) {
            $p["view_mode"] = $this->view_mode;
        }
        if (isset($this->edit_mode)) {
            $p["edit_mode"] = $this->edit_mode;
        }
        if (isset($this->data_source)) {
            $p["data_source"] = $this->data_source;
        }
        if (isset($this->tab)) {
            $p["tab"] = $this->tab;
        }
        return parent::saveParams($p);
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
        global $data_source;
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
        if (isset($data_source)) {
            $this->data_source = $data_source;
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

        $this->loadWidgetTypes();

        if ($this->action == 'admin') {
            $this->admin($out);
        } else {
            $this->usual($out);
        }

        $this->checkSettings();

        if (isset($this->owner->action)) {
            $out['PARENT_ACTION'] = $this->owner->action;
        }
        if (isset($this->owner->name)) {
            $out['PARENT_NAME'] = $this->owner->name;
        }
        $out['VIEW_MODE'] = $this->view_mode;
        $out['EDIT_MODE'] = $this->edit_mode;
        $out['MODE'] = $this->mode;
        $out['ACTION'] = $this->action;
        $out['DATA_SOURCE'] = $this->data_source;
        $out['TAB'] = $this->tab;
        if (isset($this->scene_id)) {
            $out['IS_SET_SCENE_ID'] = 1;
        }
        if (isset($this->element_id)) {
            $out['IS_SET_ELEMENT_ID'] = 1;
        }
        if (isset($this->script_id)) {
            $out['IS_SET_SCRIPT_ID'] = 1;
        }
        if ($this->single_rec) {
            $out['SINGLE_REC'] = 1;
        }
        $this->data = $out;

        if ($this->action == '') {

            /*
             $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
             $this->result=$p->result;
            */
            require_once ROOT . '3rdparty/smarty3/Smarty.class.php';
            $smarty = new Smarty;
            $smarty->setCacheDir(ROOT . 'cms/cached/template_c');

            $smarty->setTemplateDir(ROOT . './templates')
                ->setCompileDir(ROOT . './cms/cached/templates_c')
                ->setCacheDir(ROOT . './cms/cached');

            $smarty->debugging = false;
            $smarty->caching = true;
            $smarty->setCaching(120);

            foreach ($out as $k => $v) {
                $smarty->assign($k, $v);
            }

            $template = DIR_TEMPLATES . 'scenes/scenes.tpl';
            if (defined('ALTERNATIVE_TEMPLATES')) {
                $alt_path = str_replace('templates/', ALTERNATIVE_TEMPLATES . '/', $template);
                if (file_exists($alt_path)) {
                    $template = $alt_path;
                }
            }
            @$this->result = $smarty->fetch($template);


        } else {

            $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
            $this->result = $p->result;


        }

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
        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }
        if ($this->data_source == 'scenes' || $this->data_source == '') {

            if ($this->view_mode == 'moveup' && $this->id) {
                $this->reorder_scenes($this->id, 'up');
                $this->redirect("?");
            }
            if ($this->view_mode == 'movedown' && $this->id) {
                $this->reorder_scenes($this->id, 'down');
                $this->redirect("?");
            }

            if ($this->view_mode == 'import_elements') {

                global $id;
                global $file;

                $seen_elements = array();

                $data = unserialize(LoadFile($file));
                if (is_array($data['ELEMENTS'])) {
                    $elements = $data['ELEMENTS'];

                    $total = count($elements);
                    for ($i = 0; $i < $total; $i++) {
                        $states = $elements[$i]['STATES'];
                        unset($elements[$i]['STATES']);
                        $elements[$i]['SCENE_ID'] = $id;
                        $old_element_id = $elements[$i]['ID'];
                        unset($elements[$i]['ID']);
                        if ($elements[$i]['LINKED_ELEMENT_ID']) {
                            $elements[$i]['LINKED_ELEMENT_ID'] = (int)$seen_elements[$elements[$i]['LINKED_ELEMENT_ID']];
                        }
                        $elements[$i]['ID'] = SQLInsert('elements', $elements[$i]);
                        $seen_elements[$old_element_id] = $elements[$i]['ID'];
                        $totalE = count($states);
                        for ($iE = 0; $iE < $totalE; $iE++) {
                            unset($states[$iE]['ID']);
                            $states[$iE]['ELEMENT_ID'] = $elements[$i]['ID'];
                            if ($states[$iE]['IMAGE_DATA']) {
                                $filename = ROOT . $states[$iE]['IMAGE'];
                                SaveFile($filename, base64_decode($states[$iE]['IMAGE_DATA']));
                                unset($states[$iE]['IMAGE_DATA']);
                            }
                            SQLInsert('elm_states', $states[$iE]);
                        }
                    }
                    for ($i = 0; $i < $total; $i++) {
                        if ($elements[$i]['CONTAINER_ID']) {
                            $elements[$i]['CONTAINER_ID'] = (int)$seen_elements[$elements[$i]['CONTAINER_ID']];
                            SQLUpdate('elements', $elements[$i]);
                        }
                    }

                }
                $this->redirect("?tab=" . $this->tab . "&view_mode=edit_scenes&id=" . $id);

            }


            if ($this->view_mode == 'multiple_elements') {
                global $selected;
                if ($selected[0]) {

                    $res = array();
                    $elements = SQLSelect("SELECT * FROM elements WHERE ID IN (" . implode(',', $selected) . ") ORDER BY LINKED_ELEMENT_ID, CONTAINER_ID, ID");
                    $total = count($elements);
                    for ($i = 0; $i < $total; $i++) {
                        $elm_id = $elements[$i]['ID'];
                        //unset($elements[$i]['ID']);
                        //unset($elements[$i]['CONTAINER_ID']);
                        //unset($elements[$i]['LINKED_ELEMENT_ID']);
                        unset($elements[$i]['SCENE_ID']);
                        $states = SQLSelect("SELECT * FROM elm_states WHERE ELEMENT_ID='" . (int)$elm_id . "'");
                        $totalE = count($states);
                        for ($iE = 0; $iE < $totalE; $iE++) {
                            unset($states[$iE]['ID']);
                            unset($states[$iE]['ELEMENT_ID']);
                            if ($states[$iE]['IMAGE']) {
                                $states[$iE]['IMAGE_DATA'] = base64_encode(LoadFile(ROOT . $states[$iE]['IMAGE']));
                            }
                        }
                        $elements[$i]['STATES'] = $states;
                    }
                    $res['ELEMENTS'] = $elements;

                    $data = serialize($res);

                    $filename = urlencode('Elements' . date('H-i-s'));

                    $ext = "elements";   // file extension

                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . ($filename . '.' . $ext) . '"');
                    echo($data);
                    exit;
                } else {
                    $this->view_mode = 'edit_scenes';
                }
            }

            if ($this->view_mode == '' || $this->view_mode == 'search_scenes') {
                if (gr('draggable')) {
                    $out['DRAGGABLE'] = 1;
                }
                $this->search_scenes($out);
            }
            if ($this->view_mode == 'edit_scenes') {
                $this->edit_scenes($out, $this->id);
            }
            if ($this->view_mode == 'delete_scenes') {
                $this->delete_scenes($this->id);
                $this->redirect("?data_source=scenes");
            }
        }
        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }
        if ($this->data_source == 'elements') {
            if ($this->view_mode == 'delete_elements') {
                $this->delete_elements($this->id);
                $this->redirect("?data_source=elements");
            }


        }

        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }
        if ($this->data_source == 'elm_states') {
            if ($this->view_mode == '' || $this->view_mode == 'search_elm_states') {
                $this->search_elm_states($out);
            }
            if ($this->view_mode == 'edit_elm_states') {
                $this->edit_elm_states($out, $this->id);
            }
            if ($this->view_mode == 'delete_elm_states') {
                $this->delete_elm_states($this->id);
                $this->redirect("?data_source=elm_states");
            }
        }

        if ($this->view_mode == 'clone' && $this->id) {
            $this->clone_scene($this->id);
        }
        if ($this->view_mode == 'export' && $this->id) {
            $this->export_scene($this->id);
        }

        if ($this->view_mode == 'import') {
            global $file;
            $id = $this->import_scene($file);
            if ($id) {
                $this->redirect("?view_mode=edit_scenes&id=" . $id);
            } else {
                $this->redirect("?");
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
    function export_scene($id)
    {
        $rec = SQLSelectOne("SELECT * FROM scenes WHERE ID='" . (int)$id . "'");
        unset($rec['SYSTEM']);
        //elements
        $elements = SQLSelect("SELECT * FROM elements WHERE SCENE_ID='" . (int)$id . "'");
        $total = count($elements);
        for ($i = 0; $i < $total; $i++) {
            $elm_id = $elements[$i]['ID'];
            unset($elements[$i]['SCENE_ID']);
            $states = SQLSelect("SELECT * FROM elm_states WHERE ELEMENT_ID='" . (int)$elm_id . "'");
            $totalE = count($states);
            for ($iE = 0; $iE < $totalE; $iE++) {
                unset($states[$iE]['ID']);
                unset($states[$iE]['ELEMENT_ID']);
            }
            $elements[$i]['STATES'] = $states;
        }
        $rec['ELEMENTS'] = $elements;
        unset($rec['ID']);

        $res = array();
        $res['SCENE_DATA'] = $rec;
        if ($rec['BACKGROUND'] && file_exists(ROOT . $rec['BACKGROUND'])) {
            $res['BACKGROUND_IMAGE'] = base64_encode(LoadFile(ROOT . $rec['BACKGROUND']));
        }
        if ($rec['WALLPAPER'] && file_exists(ROOT . $rec['WALLPAPER'])) {
            $res['WALLPAPER_IMAGE'] = base64_encode(LoadFile(ROOT . $rec['WALLPAPER']));
        }


        $data = serialize($res);

        $filename = urlencode($rec['TITLE']);

        $ext = "scene";   // file extension
        $mime_type = (PMA_USR_BROWSER_AGENT == 'IE' || PMA_USR_BROWSER_AGENT == 'OPERA')
            ? 'application/octetstream'
            : 'application/octet-stream';
        header('Content-Type: ' . $mime_type);
        if (PMA_USR_BROWSER_AGENT == 'IE') {
            header('Content-Disposition: inline; filename="' . $filename . '.' . $ext . '"');
            header("Content-Transfer-Encoding: binary");
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            print $data;
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '.' . $ext . '"');
            header("Content-Transfer-Encoding: binary");
            header('Expires: 0');
            header('Pragma: no-cache');
            print $data;
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
    function import_scene($file, $system = '')
    {
        $text_data = LoadFile($file);
        $data = unserialize($text_data);
        if (!is_array($data)) {
            $text_data = str_replace("\n", "\r\n", $text_data);
            $data = unserialize($text_data);
        }
        if ($data['SCENE_DATA']) {
            if ($system != '') {
                $old_rec = SQLSelectOne("SELECT ID FROM scenes WHERE `SYSTEM` = '" . DBSafe($system) . "'");
                if ($old_rec['ID']) {
                    return;
                }
            }
            $rec = $data['SCENE_DATA'];
            if (!$rec['WALLPAPER']) {
                unset($rec['WALLPAPER']);
            }
            $rec['TITLE'] .= ' (imported)';
            $rec['SYSTEM'] = $system;
            $elements = $rec['ELEMENTS'];
            unset($rec['ID']);
            unset($rec['ELEMENTS']);
            $rec['ID'] = SQLInsert('scenes', $rec);
            $total = count($elements);
            $seen_elements = array();
            for ($i = 0; $i < $total; $i++) {
                $states = $elements[$i]['STATES'];
                $old_element_id = $elements[$i]['ID'];
                unset($elements[$i]['STATES']);
                unset($elements[$i]['ID']);
                $elements[$i]['SCENE_ID'] = $rec['ID'];
                $elements[$i]['ID'] = SQLInsert('elements', $elements[$i]);
                $seen_elements[$old_element_id] = $elements[$i]['ID'];
                $totalE = count($states);
                for ($iE = 0; $iE < $totalE; $iE++) {
                    unset($states[$iE]['ID']);
                    $states[$iE]['ELEMENT_ID'] = $elements[$i]['ID'];
                    SQLInsert('elm_states', $states[$iE]);
                }
            }
            $elements = SQLSelect("SELECT * FROM elements WHERE SCENE_ID=" . $rec['ID'] . " AND CONTAINER_ID!=0");
            $total = count($elements);
            for ($i = 0; $i < $total; $i++) {
                $elements[$i]['CONTAINER_ID'] = $seen_elements[$elements[$i]['CONTAINER_ID']];
                SQLUpdate('elements', $elements[$i]);
            }
            if ($data['BACKGROUND_IMAGE']) {
                $filename = ROOT . $rec['BACKGROUND'];
                SaveFile($filename, base64_decode($data['BACKGROUND_IMAGE']));
            }
            if ($data['WALLPAPER_IMAGE']) {
                $filename = ROOT . $rec['WALLPAPER'];
                SaveFile($filename, base64_decode($data['WALLPAPER_IMAGE']));
            }
            return $rec['ID'];
        }

    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function clone_scene($id)
    {
        $rec = SQLSelectOne("SELECT * FROM scenes WHERE ID='" . (int)$id . "'");
        $rec['TITLE'] .= ' (copy)';
        unset($rec['ID']);
        $rec['ID'] = SQLInsert('scenes', $rec);

        //elements
        $elements = SQLSelect("SELECT * FROM elements WHERE SCENE_ID='" . (int)$id . "'");
        $seen_elements = array();

        $total = count($elements);
        for ($i = 0; $i < $total; $i++) {
            $elm_id = $elements[$i]['ID'];
            $old_element_id = $elements[$i]['ID'];
            unset($elements[$i]['ID']);
            $elements[$i]['SCENE_ID'] = $rec['ID'];
            $elements[$i]['ID'] = SQLInsert('elements', $elements[$i]);
            $seen_elements[$old_element_id] = $elements[$i]['ID'];
            $states = SQLSelect("SELECT * FROM elm_states WHERE ELEMENT_ID='" . (int)$elm_id . "'");
            $totalE = count($states);
            for ($iE = 0; $iE < $totalE; $iE++) {
                unset($states[$iE]['ID']);
                $states[$iE]['ELEMENT_ID'] = $elements[$i]['ID'];
                SQLInsert('elm_states', $states[$iE]);
            }
        }

        for ($i = 0; $i < $total; $i++) {
            if ($elements[$i]['LINKED_ELEMENT_ID']) {
                $elements[$i]['LINKED_ELEMENT_ID'] = (int)$seen_elements[$elements[$i]['LINKED_ELEMENT_ID']];
                SQLUpdate('elements', $elements[$i]);
            }
            if ($elements[$i]['CONTAINER_ID']) {
                $elements[$i]['CONTAINER_ID'] = (int)$seen_elements[$elements[$i]['CONTAINER_ID']];
                SQLUpdate('elements', $elements[$i]);
            }
        }

        $this->redirect("?view_mode=edit_scenes&id=" . $rec['ID']);
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


        if ($this->owner->action == 'apps') {
            $this->redirect(ROOTHTML . "popup/scenes.html");
        }

        if (isset($this->ajax) && $this->ajax) {
            $ajax = $this->ajax;
        } else {
            $ajax = gr('ajax');
        }
        if ($ajax) {
            global $op;
            header("HTTP/1.0: 200 OK\n");
            header('Content-Type: text/html; charset=utf-8');

            if ($op == 'resized' || $op == 'dragged') {
                global $element;
                global $details;
                $element_id = 0;
                if (preg_match('/state_element_(\d+)/', $element, $m)) {
                    $element_id = $m[1];
                } elseif (preg_match('/state_(\d+)/', $element, $m)) {
                    $state = SQLSelectOne("SELECT ELEMENT_ID FROM elm_states WHERE ID='" . (int)$m[1] . "'");
                    $element_id = $state['ELEMENT_ID'];
                } elseif (preg_match('/canvas_(\d+)/', $element, $m) || preg_match('/container_(\d+)/', $element, $m)) {
                    $element_id = $m[1];
                }
                $element = SQLSelectOne("SELECT * FROM elements WHERE ID='" . (int)$element_id . "'");
            }


            if ($op == 'resized' && $element['ID']) {
                if ($details) {
                    $details = json_decode($details, true);
                    $element['WIDTH'] = $details['size']['width'];
                    $element['HEIGHT'] = $details['size']['height'];
                } else {
                    $element['WIDTH'] = gr('dwidth');
                    $element['HEIGHT'] = gr('dheight');
                }
                if ($element['WIDTH'] > 0 && $element['HEIGHT'] > 0) {
                    SQLUpdate('elements', $element);
                }
            }

            if ($op == 'dragged' && $element['ID']) {
                if ($details) {
                    $details = json_decode($details, true);
                    $diff_top = $details['position']['top'] - $details['originalPosition']['top'];
                    $diff_left = $details['position']['left'] - $details['originalPosition']['left'];
                } else {
                    $diff_top = gr('dtop');
                    $diff_left = gr('dleft');
                }
                if ($diff_top != 0 || $diff_left != 0) {
                    $element['TOP'] += $diff_top;
                    $element['LEFT'] += $diff_left;
                    SQLUpdate('elements', $element);

                    $linked_elements = SQLSelect("SELECT * FROM elements WHERE LINKED_ELEMENT_ID=" . (int)$element['ID']);
                    $total = count($linked_elements);
                    for ($i = 0; $i < $total; $i++) {
                        $linked_elements[$i]['TOP'] -= $diff_top;
                        $linked_elements[$i]['LEFT'] -= $diff_left;
                        SQLUpdate('elements', $linked_elements[$i]);
                    }

                }

            }


            if ($op == 'checkAllStates') {
                global $scene_id;
                $qry = "1";
                if (preg_match('/(\d+)\.html/', $_SERVER["REQUEST_URI"], $m)) {
                    $qry .= " AND scenes.ID='" . (int)$m[1] . "'";
                } elseif ($scene_id) {
                    $qry .= " AND scenes.ID='" . (int)$scene_id . "'";
                } else {
                    $qry .= " AND scenes.HIDDEN!=1";
                }

                foreach ($_GET as $k => $v) {
                    $this->data[$k] = $v;
                }


                $states = array();
                $elements = $this->getDynamicElements($qry);
                $total = count($elements);
                for ($i = 0; $i < $total; $i++) {
                    if (is_array($elements[$i]['STATES'])) {
                        foreach ($elements[$i]['STATES'] as $st) {
                            if ($elements[$i]['TYPE'] == 'container') unset($st['HTML']);
                            if ($elements[$i]['TYPE'] == 'widget' && preg_match('/<script/', $st['HTML'])) unset($st['HTML']);
                            $states[] = $st;
                        }
                    }
                }

                $total = count($states);

                for ($i = 0; $i < $total; $i++) {
                    $this->processState($states[$i]);
                }
                echo json_encode($states);
                exit;
            }
            if ($op == 'click') {
                global $id;

                if (preg_match('/(\d+)\_(\d+)/', $id, $m)) {
                    $dynamic_item = 1;
                    $real_part = $m[1];
                    $object_part = $m[2];
                    if ($object_part) {
                        $object_rec = SQLSelectOne("SELECT ID, TITLE FROM objects WHERE ID=" . (int)($object_part));
                    }
                } elseif (preg_match('/^object_(.+?)/', $id, $m)) {
                    return false;
                } else {
                    $dynamic_item = 0;
                    $real_part = $id;
                    $object_part = 0;
                }


                $state = SQLSelectOne("SELECT * FROM elm_states WHERE ID='" . (int)$real_part . "'");
                $element = SQLSelectOne("SELECT * FROM elements WHERE ID=" . (int)$state['ELEMENT_ID']);
                $scene = SQLSelectOne("SELECT * FROM scenes WHERE ID=" . (int)$element['SCENE_ID']);

                logAction('scene_clicked', $scene['TITLE'] . '.' . $element['TITLE'] . '.' . $state['TITLE']);

                $params = array('STATE' => $state['TITLE']);
                if ($state['ACTION_OBJECT'] && $state['ACTION_METHOD']) {
                    if ($object_part) {
                        $state['ACTION_OBJECT'] = $object_rec['TITLE'];
                    }
                    callMethod($state['ACTION_OBJECT'] . '.' . $state['ACTION_METHOD'], $params);
                }
                if ($state['SCRIPT_ID']) {
                    runScript($state['SCRIPT_ID'], $params);
                }
                if ($state['CODE']) {
                    try {
                        $code = $state['CODE'];
                        setEvalCode($code);
                        $success = eval($code);
                        if ($success === false) {
                            DebMes("Error scene item code: " . $code);
                            registerError('scenes', "Error scene item code: " . $code);
                        }
                    } catch (Exception $e) {
                        DebMes('Error: exception ' . get_class($e) . ', ' . $e->getMessage() . '.');
                        registerError('scenes', get_class($e) . ', ' . $e->getMessage());
                    }
                }

                $qry = "1";
                $qry .= " AND elements.ID=" . (int)$state['ELEMENT_ID'];

                $states = array();
                $elements = $this->getDynamicElements($qry);
                $total = count($elements);
                for ($i = 0; $i < $total; $i++) {
                    if (is_array($elements[$i]['STATES'])) {
                        foreach ($elements[$i]['STATES'] as $st) {
                            if ($elements[$i]['TYPE'] == 'container') unset($st['HTML']);
                            $states[] = $st;
                        }
                    }
                }

                $total = count($states);
                for ($i = 0; $i < $total; $i++) {
                    $this->processState($states[$i]);
                    /*
                    $states[$i]['STATE']=(string)$this->checkState($states[$i]['ID']);
                    if ($states[$i]['HTML']!='') {
                     $states[$i]['HTML']=processTitle($states[$i]['HTML'], $this);
                    }
                    if ($states[$i]['TYPE']=='img') {
                     unset($states[$i]['HTML']);
                    }
                    */
                }
                echo json_encode($states);

            }

            if ($op == 'position') {
                global $id;
                global $posx;
                global $posy;
                global $width;
                global $height;
                if ($id && $posx && $posy && $width && $height) {
                    $state = SQLSelectOne("SELECT * FROM elm_states WHERE ID='" . $id . "'");
                    $state['WINDOW_POSX'] = $posx;
                    $state['WINDOW_POSY'] = $posy;
                    $state['WINDOW_WIDTH'] = $width;
                    $state['WINDOW_HEIGHT'] = $height;
                    SQLUpdate('elm_states', $state);
                }
                //
                echo "OK";
            }

            endMeasure('TOTAL');

            if (isset($_GET['performance'])) {
                performanceReport();
            }

            exit;
        }

        $this->admin($out);

        $out['ALL_TYPES'] = $this->getAllTypes();


    }


    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function processState(&$state)
    {
        $state['STATE'] = (string)$this->checkState($state['ID']);

        if ($state['TYPE'] == 'img') {
            unset($state['HTML']);
        }

        if (isset($state['HTML']) && $state['HTML'] != '') {
            if (preg_match('/\[#modul/is', $state['HTML'])) {
                //$states[$i]['HTML']=str_replace('#', '', $state['HTML']);
                unset($state['HTML']);
            } else {
                $state['HTML'] = processTitle($state['HTML'], $this);
            }
        }
    }

    function checkSettings()
    {
        $settings = array(
            array(
                'NAME' => 'SCENES_WIDTH',
                'TITLE' => 'Scene width',
                'TYPE' => 'text',
                'DEFAULT' => '803'
            ),
            array(
                'NAME' => 'SCENES_HEIGHT',
                'TITLE' => 'Scene height',
                'TYPE' => 'text',
                'DEFAULT' => '606'
            )
        );


        foreach ($settings as $k => $v) {
            $rec = SQLSelectOne("SELECT ID FROM settings WHERE NAME='" . $v['NAME'] . "'");
            if (!$rec['ID']) {
                $rec['NAME'] = $v['NAME'];
                $rec['VALUE'] = $v['DEFAULT'];
                $rec['DEFAULTVALUE'] = $v['DEFAULT'];
                $rec['TITLE'] = $v['TITLE'];
                $rec['TYPE'] = $v['TYPE'];
                $rec['ID'] = SQLInsert('settings', $rec);
                Define('SETTINGS_' . $rec['NAME'], $v['DEFAULT']);
            }
        }

    }

    /**
     * scenes search
     *
     * @access public
     */
    function search_scenes(&$out)
    {
        require(DIR_MODULES . $this->name . '/scenes_search.inc.php');
    }

    /**
     * scenes edit/add
     *
     * @access public
     */
    function edit_scenes(&$out, $id)
    {
        require(DIR_MODULES . $this->name . '/scenes_edit.inc.php');
    }

    /**
     * scenes delete record
     *
     * @access public
     */
    function delete_scenes($id)
    {
        $rec = SQLSelectOne("SELECT * FROM scenes WHERE ID='$id'");
        // some action for related tables
        $elements = SQLSelect("SELECT ID FROM elements WHERE SCENE_ID='" . (int)$rec['ID'] . "'");
        $total = count($elements);
        for ($i = 0; $i < $total; $i++) {
            $this->delete_elements($elements[$i]['ID']);
        }

        SQLExec("DELETE FROM scenes WHERE ID='" . $rec['ID'] . "'");
    }

    /**
     * elements delete record
     *
     * @access public
     */
    function delete_elements($id)
    {
        $rec = SQLSelectOne("SELECT * FROM elements WHERE ID='$id'");
        // some action for related tables
        $states = SQLSelect("SELECT ID FROM elm_states WHERE ELEMENT_ID='" . (int)$rec['ID'] . "'");
        $total = count($states);
        for ($i = 0; $i < $total; $i++) {
            $this->delete_elm_states($states[$i]['ID']);
        }
        SQLExec("DELETE FROM elements WHERE ID='" . $rec['ID'] . "' OR (CONTAINER_ID>0 AND CONTAINER_ID='" . $rec['ID'] . "')");
    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function reorder_elements($id, $direction = 'up')
    {
        $element = SQLSelectOne("SELECT * FROM elements WHERE ID='" . (int)$id . "'");
        if ($element['CONTAINER_ID']) {
            $all_elements = SQLSelect("SELECT * FROM elements WHERE CONTAINER_ID=" . $element['CONTAINER_ID'] . " ORDER BY PRIORITY DESC, TITLE");
        } else {
            $all_elements = SQLSelect("SELECT * FROM elements WHERE SCENE_ID=" . $element['SCENE_ID'] . " AND CONTAINER_ID=0 ORDER BY PRIORITY DESC, TITLE");
        }

        $total = count($all_elements);


        for ($i = 0; $i < $total; $i++) {
            if ($all_elements[$i]['ID'] == $id && $i > 0 && $direction == 'up') {
                $tmp = $all_elements[$i - 1];
                $all_elements[$i - 1] = $all_elements[$i];
                $all_elements[$i] = $tmp;
                break;
            }
            if ($all_elements[$i]['ID'] == $id && $i < ($total - 1) && $direction == 'down') {
                $tmp = $all_elements[$i + 1];
                $all_elements[$i + 1] = $all_elements[$i];
                $all_elements[$i] = $tmp;
                break;
            }
        }

        $priority = ($total) * 10;

        for ($i = 0; $i < $total; $i++) {
            $all_elements[$i]['PRIORITY'] = $priority;
            $priority -= 10;
            SQLUpdate('elements', $all_elements[$i]);
        }

    }

    function reorder_scenes($id, $direction = 'up')
    {
        $element = SQLSelectOne("SELECT * FROM scenes WHERE ID='" . (int)$id . "'");
        $all_elements = SQLSelect("SELECT * FROM scenes WHERE 1 ORDER BY PRIORITY DESC, TITLE");

        $total = count($all_elements);


        for ($i = 0; $i < $total; $i++) {
            if ($all_elements[$i]['ID'] == $id && $i > 0 && $direction == 'up') {
                $tmp = $all_elements[$i - 1];
                $all_elements[$i - 1] = $all_elements[$i];
                $all_elements[$i] = $tmp;
                break;
            }
            if ($all_elements[$i]['ID'] == $id && $i < ($total - 1) && $direction == 'down') {
                $tmp = $all_elements[$i + 1];
                $all_elements[$i + 1] = $all_elements[$i];
                $all_elements[$i] = $tmp;
                break;
            }
        }

        $priority = ($total) * 10;

        for ($i = 0; $i < $total; $i++) {
            $all_elements[$i]['PRIORITY'] = $priority;
            $priority -= 10;
            SQLUpdate('scenes', $all_elements[$i]);
        }

    }

    /**
     * elm_states search
     *
     * @access public
     */
    function search_elm_states(&$out)
    {
        require(DIR_MODULES . $this->name . '/elm_states_search.inc.php');
    }

    /**
     * elm_states edit/add
     *
     * @access public
     */
    function edit_elm_states(&$out, $id)
    {
        require(DIR_MODULES . $this->name . '/elm_states_edit.inc.php');
    }

    /**
     * elm_states delete record
     *
     * @access public
     */
    function delete_elm_states($id)
    {
        $rec = SQLSelectOne("SELECT * FROM elm_states WHERE ID='$id'");
        // some action for related tables
        SQLExec("DELETE FROM elm_states WHERE ID='" . $rec['ID'] . "'");
    }


    function loadWidgetTypes()
    {
        if (!isset($this->widget_types['text'])) {
            include DIR_MODULES . 'scenes/widget_types.inc.php';
        }
    }

    function getWidgetData($element_id)
    {

        $widgetData = array();

        $element = SQLSelectOne("SELECT * FROM elements WHERE ID=" . (int)$element_id);
        if (!$element['ID']) return '';

        $data = json_decode($element['WIZARD_DATA'], true);
        if (isset($this->widget_types[$data['WIDGET_TYPE']])) {
            $widget_type = $this->widget_types[$data['WIDGET_TYPE']];
            $widgetData['TYPE'] = $data['WIDGET_TYPE'];
            $widgetData['TYPE_DETAILS'] = $widget_type;
            if (preg_match('/file:(.+)/', $widget_type['TEMPLATE'], $m)) {
                $filename = DIR_TEMPLATES . 'scenes/widgets/' . $m[1];
                if (file_exists($filename)) {
                    $template = LoadFile($filename);
                } else {
                    $template = 'File not found: ' . $filename;
                }
            } else {
                $template = $widget_type['TEMPLATE'];
            }
            $data['element_id'] = $element_id;
            foreach ($data as $k => $v) {
                $template = str_replace('%' . $k . '%', $v, $template);
            }
            $html = $template;

        } else {
            $html = 'Incorrect widget type: ' . $data['WIDGET_TYPE'];
        }

        $widgetData['HTML'] = $html;

        return $widgetData;
    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function checkState($id)
    {


        if (preg_match('/^object_(.+?)/', $id, $m)) {
            return 1;
        }

        if (preg_match('/(\d+)\_(\d+)/', $id, $m)) {
            $dynamic_item = 1;
            $real_part = $m[1];
            $object_part = $m[2];
            if ($object_part) {
                $object_rec = SQLSelectOne("SELECT ID, TITLE FROM objects WHERE ID=" . (int)($object_part));
            }
        } else {
            $dynamic_item = 0;
            $real_part = $id;
            $object_part = 0;
        }


        $rec = SQLSelectOne("SELECT * FROM elm_states WHERE ID='" . $real_part . "'");

        $original_linked_object = $rec['LINKED_OBJECT'];

        if (!checkAccess('scene_elements', $rec['ELEMENT_ID'])) {
            $status = 0;
            return $status;
        }

        startMeasure('state_dynamic' . $rec['IS_DYNAMIC']);
        if (!$rec['IS_DYNAMIC']) {

            $status = 1;

        } elseif ($rec['IS_DYNAMIC'] == 1) {

            if ($rec['LINKED_OBJECT'] != '' && $rec['LINKED_PROPERTY'] != '') {
                if ($dynamic_item) {
                    $rec['LINKED_OBJECT'] = $object_rec['TITLE'];
                }
                $value = gg(trim($rec['LINKED_OBJECT']) . '.' . trim($rec['LINKED_PROPERTY']));
            } elseif ($rec['LINKED_PROPERTY'] != '') {
                $value = gg($rec['LINKED_PROPERTY']);
            } else {
                $value = -1;
            }

            if (($rec['CONDITION'] == 2 || $rec['CONDITION'] == 3)
                && $rec['CONDITION_VALUE'] != ''
                && !is_numeric($rec['CONDITION_VALUE'])
                && !preg_match('/^%/', $rec['CONDITION_VALUE'])
            ) {
                $rec['CONDITION_VALUE'] = '%' . $rec['CONDITION_VALUE'] . '%';
            }


            if (is_integer(strpos($rec['CONDITION_VALUE'], "%"))) {
                if ($dynamic_item) {
                    $rec['CONDITION_VALUE'] = str_replace('%' . $original_linked_object . '.', '%' . $object_rec['TITLE'] . '.', $rec['CONDITION_VALUE']);
                }
                $rec['CONDITION_VALUE'] = processTitle($rec['CONDITION_VALUE']);
            }

            if ($rec['CONDITION'] == 1 && $value == $rec['CONDITION_VALUE']) {
                $status = 1;
            } elseif ($rec['CONDITION'] == 2 && (float)$value > (float)$rec['CONDITION_VALUE']) {
                $status = 1;
            } elseif ($rec['CONDITION'] == 3 && (float)$value < (float)$rec['CONDITION_VALUE']) {
                $status = 1;
            } elseif ($rec['CONDITION'] == 4 && $value != $rec['CONDITION_VALUE']) {
                $status = 1;
            } else {
                $status = 0;
            }

        } elseif ($rec['IS_DYNAMIC'] == 2) {

            $display = 0;

            if (is_integer(strpos($rec['CONDITION_ADVANCED'], "%"))) {
                if ($dynamic_item) {
                    $rec['CONDITION_ADVANCED'] = str_replace('%' . $original_linked_object . '.', '%' . $object_rec['TITLE'] . '.', $rec['CONDITION_ADVANCED']);
                }
                $rec['CONDITION_ADVANCED'] = processTitle($rec['CONDITION_ADVANCED']);
            }

            try {
                $code = $rec['CONDITION_ADVANCED'];
                if ($code != '') {
                    setEvalCode($code);
                    $success = eval($code);
                } else {
                    $success = true;
                }
                if ($success === false) {
                    DebMes("Error in scene code: " . $code);
                    registerError('scenes', "Error in scene code: " . $code);
                }
            } catch (Exception $e) {
                DebMes('Error: exception ' . get_class($e) . ', ' . $e->getMessage() . '.');
                registerError('scenes', get_class($e) . ', ' . $e->getMessage());
            }

            $status = $display;

        }
        endMeasure('state_dynamic' . $rec['IS_DYNAMIC']);

        if ($rec['CURRENT_STATE'] != $status && !$dynamic_item) {
            startMeasure('stateUpdate');
            $rec['CURRENT_STATE'] = $status;
            SQLExec('UPDATE elm_states SET CURRENT_STATE=' . $rec['CURRENT_STATE'] . ' WHERE ID=' . (int)$rec['ID']);
            endMeasure('stateUpdate');
        }

        return $status;
    }


    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function getDynamicElements($qry = '1')
    {

        $this->loadWidgetTypes();
        $elements = SQLSelect("SELECT elements.* FROM elements, scenes WHERE elements.SCENE_ID=scenes.ID AND $qry ORDER BY PRIORITY DESC, TITLE");

        $totale = count($elements);
        $res2 = array();
        for ($ie = 0; $ie < $totale; $ie++) {

            if ($elements[$ie]['TYPE'] == 'object') {
                $state = array();
                $state['ID'] = 'element_' . ($elements[$ie]['ID']);
                $state['ELEMENT_ID'] = $elements[$ie]['ID'];
                $state['HTML'] = getObjectClassTemplate($elements[$ie]['LINKED_OBJECT'], $elements[$ie]['CLASS_TEMPLATE']);
                $state['TYPE'] = $elements[$ie]['TYPE'];
                $state['MENU_ITEM_ID'] = 0;
                $state['HOMEPAGE_ID'] = 0;
                $state['OPEN_SCENE_ID'] = 0;
                $states = array($state);
            } elseif ($elements[$ie]['TYPE'] == 'widget') {
                $state = array();
                $state['ID'] = 'element_' . ($elements[$ie]['ID']);
                $state['ELEMENT_ID'] = $elements[$ie]['ID'];
                $state['MENU_ITEM_ID'] = 0;
                $state['HOMEPAGE_ID'] = 0;
                $state['OPEN_SCENE_ID'] = 0;
                $state['TYPE'] = $elements[$ie]['TYPE'];
                $widgetData = $this->getWidgetData((int)$elements[$ie]['ID']);
                $state['HTML'] = $widgetData['HTML'];
                $elements[$ie]['RESIZABLE'] = $widgetData['TYPE_DETAILS']['RESIZABLE'];

                $states = array($state);
            } elseif ($elements[$ie]['TYPE'] == 'device') {
                $device_rec = SQLSelectOne("SELECT * FROM devices WHERE ID=" . (int)$elements[$ie]['DEVICE_ID']);
                $state = array();
                $state['ID'] = 'element_' . ($elements[$ie]['ID']);
                $state['ELEMENT_ID'] = $elements[$ie]['ID'];
                $state['MENU_ITEM_ID'] = 0;
                $state['HOMEPAGE_ID'] = 0;
                $state['OPEN_SCENE_ID'] = 0;
                $state['TYPE'] = $elements[$ie]['TYPE'];
                if (!$device_rec['ARCHIVED']) {
                    $state['HTML'] = getObjectClassTemplate($device_rec['LINKED_OBJECT'], $elements[$ie]['CLASS_TEMPLATE']);
                } else {
                    $state['HTML'] = '';
                }
                $states = array($state);
            } else {
                $states = SQLSelect("SELECT elm_states.*,elements.TYPE  FROM elm_states, elements WHERE elm_states.ELEMENT_ID=elements.ID AND ELEMENT_ID='" . $elements[$ie]['ID'] . "' ORDER BY elm_states.PRIORITY DESC, elm_states.TITLE");
            }

            if ($elements[$ie]['SMART_REPEAT'] && !$this->action == 'admin') {
                $linked_object = '';
                if ($states[0]['LINKED_OBJECT']) {
                    $linked_object = $states[0]['LINKED_OBJECT'];
                } elseif ($states[0]['ACTION_OBJECT']) {
                    $linked_object = $states[0]['ACTION_OBJECT'];
                }

                if ($linked_object) {
                    $obj = getObject($linked_object);
                    $objects = getObjectsByClass($obj->class_id);
                    if (is_array($objects)) {
                        $total_o = count($objects);
                        for ($io = 0; $io < $total_o; $io++) {
                            $rec = $elements[$ie];
                            $rec['ID'] = $elements[$ie] . '_' . $objects[$io]['ID'];
                            $new_states = array();
                            $total_s = count($states);
                            for ($is = 0; $is < $total_s; $is++) {
                                $state_rec = $states[$is];
                                if ($state_rec['LINKED_OBJECT']) {
                                    $state_rec['LINKED_OBJECT'] = $objects[$io]['TITLE'];
                                }
                                if ($state_rec['ACTION_OBJECT']) {
                                    $state_rec['ACTION_OBJECT'] = $objects[$io]['TITLE'];
                                }
                                if ($state_rec['HTML']) {
                                    $state_rec['HTML'] = str_replace('%' . $linked_object . '.', '%' . $objects[$io]['TITLE'] . '.', $state_rec['HTML']);
                                }
                                $state_rec['ID'] = $state_rec['ID'] . '_' . $objects[$io]['ID'];
                                $new_states[] = $state_rec;
                            }
                            $rec['STATES'] = $new_states;
                            $res2[] = $rec;
                        }
                    }
                } else {
                    $elements[$ie]['STATES'] = $states;
                    $elements[$ie]['SMART_REPEAT'] = 0;
                    $res2[] = $elements[$ie];
                }

            } else {
                $elements[$ie]['STATES'] = $states;
                $res2[] = $elements[$ie];
            }

            if (is_array($elements[$ie]['STATES'])) {
                $total_states = count($elements[$ie]['STATES']);
                for ($is = 0; $is < $total_states; $is++) {
                    if ($elements[$ie]['TYPE'] == 'container') {
                        unset($elements[$ie]['STATES'][$is]['HTML']);
                    }
                }
            }


            if (!isset($elements[$ie]['RESIZABLE']) && $elements[$ie]['TYPE'] != 'device') {
                $elements[$ie]['RESIZABLE'] = 1;
            }


        }
        return $res2;

    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function getElements($qry = '1', $options = 0)
    {

        $elements = $this->getDynamicElements($qry);

        $totale = count($elements);

        for ($ie = 0; $ie < $totale; $ie++) {

            if ($elements[$ie]['CSS_STYLE']) {
                $this->all_styles[$elements[$ie]['CSS_STYLE']] = 1;
                if (!is_array($options) || $options['ignore_css_image'] != 1) {
                    $elements[$ie]['CSS_IMAGE'] = $this->getCSSImage($elements[$ie]['TYPE'], $elements[$ie]['CSS_STYLE']);
                }
            }
            if ($elements[$ie]['PRIORITY']) {
                $elements[$ie]['ZINDEX'] = round($elements[$ie]['PRIORITY'] / 10);
            }
            if ($elements[$ie]['TYPE'] == 'img') {
                $elements[$ie]['BACKGROUND'] = 0;
            }
            $positions[$elements[$ie]['ID']]['TOP'] = $elements[$ie]['TOP'];
            $positions[$elements[$ie]['ID']]['LEFT'] = $elements[$ie]['LEFT'];
            if (isset($elements[$ie]['STATES'])) {
                $states = $elements[$ie]['STATES'];
            } else {
                $states = SQLSelect("SELECT * FROM elm_states WHERE ELEMENT_ID='" . $elements[$ie]['ID'] . "' ORDER BY PRIORITY DESC, TITLE");
            }
            $total_s = count($states);
            for ($is = 0; $is < $total_s; $is++) {
                if ($elements[$ie]['TYPE'] == 'img') {
                    unset($states[$is]['HTML']);
                }
                if ($states[$is]['HTML'] != '' && !isset($options['no_render'])) {
                    $states[$is]['HTML'] = processTitle($states[$is]['HTML']);
                }
                if (!is_array($options) || !isset($options['ignore_state']) || $options['ignore_state'] != 1) {
                    startMeasure('checkstates');
                    $states[$is]['STATE'] = $this->checkState($states[$is]['ID']);
                    endMeasure('checkstates');
                }
            }
            if (!isset($elements[$ie]['RESIZABLE']) && $elements[$ie]['TYPE'] != 'device') {
                $elements[$ie]['RESIZABLE'] = 1;
            }
            $elements[$ie]['STATES'] = $states;
            if ($elements[$ie]['TYPE'] == 'container') {
                if (!is_array($options) || !isset($options['ignore_sub']) || $options['ignore_sub'] != 1) {
                    startMeasure('getSubElements');
                    $elements[$ie]['STATE'] = $elements[$ie]['STATES'][0]['STATE'];
                    $elements[$ie]['STATE_ID'] = $elements[$ie]['STATES'][0]['ID'];

                    if (checkAccess('scene_elements', $elements[$ie]['ID'])) {
                        $elements[$ie]['ELEMENTS'] = $this->getElements("CONTAINER_ID=" . (int)$elements[$ie]['ID'], $options);
                    } else {
                        $elements[$ie]['TYPE'] = '';
                    }
                    endMeasure('getSubElements');
                }
            }
        }
        for ($ie = 0; $ie < $totale; $ie++) {
            if ($elements[$ie]['LINKED_ELEMENT_ID']) {
                $elements[$ie]['TOP'] = $positions[$elements[$ie]['LINKED_ELEMENT_ID']]['TOP'] + $elements[$ie]['TOP'];
                $elements[$ie]['LEFT'] = $positions[$elements[$ie]['LINKED_ELEMENT_ID']]['LEFT'] + $elements[$ie]['LEFT'];
                $positions[$elements[$ie]['ID']]['TOP'] = $elements[$ie]['TOP'];
                $positions[$elements[$ie]['ID']]['LEFT'] = $elements[$ie]['LEFT'];
            }
        }
        return $elements;
    }

    /**
     * Install
     *
     * Module installation routine
     *
     * @access private
     */
    function install($data = '')
    {
        @umask(0);
        if (!Is_Dir(ROOT . "./cms/scenes")) {
            mkdir(ROOT . "./cms/scenes", 0777);
        }
        if (!Is_Dir(ROOT . "./cms/scenes/elements")) {
            mkdir(ROOT . "./cms/scenes/elements", 0777);
        }
        if (!Is_Dir(ROOT . "./cms/scenes/backgrounds")) {
            mkdir(ROOT . "./cms/scenes/backgrounds", 0777);
        }
        parent::install();
    }

    /**
     * Uninstall
     *
     * Module uninstall routine
     *
     * @access public
     */
    function uninstall()
    {
        SQLDropTable('scenes');
        SQLDropTable('elements');
        SQLDropTable('elm_states');
        parent::uninstall();
    }


    function getCSSImage($type, $style)
    {
        $styles = $this->getStyles($type);
        if (is_array($styles)) {
            $total = count($styles);
            for ($i = 0; $i < $total; $i++) {
                if ($styles[$i]['TITLE'] == $style) {
                    return $styles[$i]['IMAGE'];
                }
            }
        }

        $styles = $this->getStyles('common');
        if (is_array($styles)) {
            $total = count($styles);
            for ($i = 0; $i < $total; $i++) {
                if ($styles[$i]['TITLE'] == $style) {
                    return $styles[$i]['IMAGE'];
                }
            }
        }
    }


    function getAllTypes()
    {
        $path = ROOT . 'cms/scenes/styles';
        if (!is_dir($path)) return false;
        $res_types = array();
        if ($handle = opendir($path)) {
            $style_recs = array();
            while (false !== ($entry = readdir($handle))) {
                if ($entry != '.' && $entry != '..' && is_dir($path . '/' . $entry)) {
                    $type_rec = array('TITLE' => $entry, 'STYLES' => $this->getStylesWithCommon($entry));
                    if (file_exists($path . '/' . $entry . '/style.css')) {
                        $type_rec['HAS_STYLE'] = 1;
                    }
                    $res_types[] = $type_rec;
                }
            }
        }
        closedir($handle);
        return $res_types;
    }

    function getStylesWithCommon($type)
    {
        $res1 = $this->getStyles($type);
        if (!is_array($res1)) {
            $res1 = array();
        }
        $res2 = $this->getStyles('common');
        if (!is_array($res2)) {
            $res2 = array();
        }
        return array_merge($res1, $res2);
    }

    function getStyles($type = '')
    {
        startMeasure('getStyles');
        $path = ROOT . 'cms/scenes/styles/' . $type;

        if (!is_dir($path))
            return;

        $enable_style_caching = false;
        $cache_file = ROOT . 'cms/cached/styles_' . $type . '.txt';

        if ($enable_style_caching && file_exists($cache_file) && (time() - filemtime($cache_file)) < 1 * 60 * 60) {
            $styles_recs = unserialize(LoadFile($cache_file));
        } else {
            startMeasure('openAndReadDir');

            if ($handle = opendir($path)) {
                $styles_recs = array();

                while (false !== ($entry = readdir($handle))) {
                    if (preg_match('/(.+?)\.png$/is', $entry, $m)) {
                        $style = $m[1];
                        $style = preg_replace('/^i\_/', '', $style);


                        if (preg_match('/^ign_/', $style))
                            continue;


                        if ($type == 'common')
                            $entry = '../common/' . $entry;


                        $has_low = 0;

                        if (preg_match('/\_lo$/', $style)) {
                            $style = preg_replace('/\_lo$/', '', $style);
                            $has_low = $entry;
                        }

                        $has_high = 0;

                        if (preg_match('/\_hi$/', $style)) {
                            $style = preg_replace('/\_hi$/', '', $style);
                            $has_high = $entry;
                        }

                        $has_on = 0;

                        if (preg_match('/\_on$/', $style)) {
                            $style = preg_replace('/\_on$/', '', $style);
                            $has_on = $entry;
                        }

                        $has_off = 0;

                        if (preg_match('/\_off$/', $style)) {
                            $style = preg_replace('/\_off$/', '', $style);
                            $has_off = $entry;
                        }

                        $has_mid = 0;

                        if (preg_match('/\_mid$/', $style)) {
                            $style = preg_replace('/\_mid$/', '', $style);
                            $has_mid = $entry;
                        }

                        $has_na = 0;

                        if (preg_match('/\_na$/', $style)) {
                            $style = preg_replace('/\_na$/', '', $style);
                            $has_na = $entry;
                        }

                        if (isset($this->all_styles) && is_array($this->all_styles) && !isset($this->all_styles[$style]))
                            continue;

                        $styles_recs[$style]['TITLE'] = $style;

                        if ($has_low)
                            $styles_recs[$style]['HAS_LOW'] = $has_low;

                        if ($has_high)
                            $styles_recs[$style]['HAS_HIGH'] = $has_high;

                        if ($has_on)
                            $styles_recs[$style]['HAS_ON'] = $has_on;

                        if ($has_off)
                            $styles_recs[$style]['HAS_OFF'] = $has_off;

                        if ($has_mid)
                            $styles_recs[$style]['HAS_MID'] = $has_mid;

                        if ($has_na)
                            $styles_recs[$style]['HAS_NA'] = $has_na;

                        if (!$has_low && !$has_high && !$has_on && !$has_off && !$has_mid && !$has_na)
                            $styles_recs[$style]['HAS_DEFAULT'] = $entry;

                        if (!isset($styles_recs[$style]['HAS_DEFAULT']) && $has_on)
                            $styles_recs[$style]['HAS_DEFAULT'] = $has_on;
                    }
                }

                if (is_array($styles_recs)) {
                    foreach ($styles_recs as $k => $v) {
                        if (!isset($styles_recs[$k]['IMAGE']) && file_exists($path . '/' . $v['TITLE'] . '.png'))
                            $styles_recs[$k]['IMAGE'] = $type . '/' . $v['TITLE'] . '.png';

                        if (!isset($styles_recs[$k]['IMAGE']) && file_exists($path . '/i_' . $v['TITLE'] . '.png'))
                            $styles_recs[$k]['IMAGE'] = $type . '/i_' . $v['TITLE'] . '.png';

                        if (!isset($styles_recs[$k]['IMAGE']) && file_exists($path . '/i_' . $v['TITLE'] . '_on.png'))
                            $styles_recs[$k]['IMAGE'] = $type . '/i_' . $v['TITLE'] . '_on.png';
                    }
                }

                closedir($handle);
            }


            if ($enable_style_caching && count($styles_recs) > 0)
                SaveFile($cache_file, serialize($styles_recs));

            endMeasure('openAndReadDir');

        }


        if (is_array($styles_recs)) {
            $res_styles = array();

            foreach ($styles_recs as $k => $v)
                $res_styles[] = $v;
        }

        endMeasure('getStyles');

        return $res_styles;
    }


    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function getWatchedProperties($scenes)
    {

        $this->loadWidgetTypes();

        $qry = '1';
        if (!isset($scenes['all'])) {
            $qry .= " AND (0 ";
            foreach ($scenes as $k => $v) {
                if ($k == 'all') {
                    continue;
                }
                $qry .= " OR SCENE_ID=" . (int)$k;
            }
            $qry .= ")";
        }

        $states = array();
        $elements = $this->getDynamicElements($qry);
        $total = count($elements);
        for ($i = 0; $i < $total; $i++) {
            if (is_array($elements[$i]['STATES'])) {
                foreach ($elements[$i]['STATES'] as $st) {
                    $states[] = $st;
                }
            }
        }


        $properties = array();
        $total = count($states);

        //DebMes("total states: ".$total);

        for ($i = 0; $i < $total; $i++) {

            // linked object.property
            if ($states[$i]['LINKED_OBJECT'] && $states[$i]['LINKED_PROPERTY']) {
                $properties[] = array('PROPERTY' => mb_strtolower($states[$i]['LINKED_OBJECT'] . '.' . $states[$i]['LINKED_PROPERTY'], 'UTF-8'), 'STATE_ID' => $states[$i]['ID']);
            }

            //html content properties
            $content = $states[$i]['HTML'];
            $content = preg_replace('/%([\w\d\.]+?)\.([\w\d\.]+?)\|(\d+)%/uis', '%\1.\2%', $content);
            $content = preg_replace('/%([\w\d\.]+?)\.([\w\d\.]+?)\|".+?"%/uis', '%\1.\2%', $content);
            if (preg_match_all('/%([\w\d\.]+?)%/is', $content, $m)) {
                $totalm = count($m[1]);
                for ($im = 0; $im < $totalm; $im++) {
                    $properties[] = array('PROPERTY' => mb_strtolower($m[1][$im], 'UTF-8'), 'STATE_ID' => $states[$i]['ID']);
                }
            }

            // advanced conditions properties
            if ($states[$i]['IS_DYNAMIC'] == 2 && preg_match_all('/([\w\d\.]+?\.[\w\d\.]+)/is', $states[$i]['CONDITION_ADVANCED'], $mc)) {
                $totala = count($mc[1]);
                for ($ia = 0; $ia < $totala; $ia++) {
                    $properties[] = array('PROPERTY' => mb_strtolower($mc[1][$ia], 'UTF-8'), 'STATE_ID' => $states[$i]['ID']);
                }
            }

        }

        return $properties;
    }

    /**
     * dbInstall
     *
     * Database installation routine
     *
     * @access private
     */
    function dbInstall($data)
    {
        /*
        scenes - Scenes
        elements - Elements
        elm_states - Element states
        */
        $data = <<<EOD
 scenes: ID int(10) unsigned NOT NULL auto_increment
 scenes: TITLE varchar(255) NOT NULL DEFAULT ''
 scenes: BACKGROUND varchar(255) NOT NULL DEFAULT ''
 scenes: WALLPAPER varchar(255) NOT NULL DEFAULT ''
 scenes: PRIORITY int(10) NOT NULL DEFAULT '0'
 scenes: HIDDEN int(3) NOT NULL DEFAULT '0'
 scenes: AUTO_SCALE int(3) NOT NULL DEFAULT '0'
 scenes: WALLPAPER_FIXED int(3) NOT NULL DEFAULT '0'
 scenes: WALLPAPER_NOREPEAT int(3) NOT NULL DEFAULT '0'
 scenes: SYSTEM varchar(255) NOT NULL DEFAULT '' 
 scenes: DEVICES_BACKGROUND varchar(10) NOT NULL DEFAULT ''
 scenes: AUTO_REFRESH int(10) NOT NULL DEFAULT '0' 

 elements: ID int(10) unsigned NOT NULL auto_increment
 elements: SCENE_ID int(10) NOT NULL DEFAULT '0'
 elements: TITLE varchar(255) NOT NULL DEFAULT ''
 elements: SYSTEM varchar(255) NOT NULL DEFAULT ''
 elements: TYPE varchar(255) NOT NULL DEFAULT ''
 elements: CSS_STYLE varchar(255) NOT NULL DEFAULT ''
 elements: DEVICE_ID int(10) NOT NULL DEFAULT '0'
 elements: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 elements: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
 elements: LINKED_METHOD varchar(255) NOT NULL DEFAULT ''
 elements: TOP int(10) NOT NULL DEFAULT '0'
 elements: LEFT int(10) NOT NULL DEFAULT '0'
 elements: WIDTH int(10) NOT NULL DEFAULT '0'
 elements: HEIGHT int(10) NOT NULL DEFAULT '0'
 elements: DX int(10) NOT NULL DEFAULT '0'
 elements: DY int(10) NOT NULL DEFAULT '0'
 elements: POSITION_TYPE int(3) NOT NULL DEFAULT '0'
 elements: LINKED_ELEMENT_ID int(10) NOT NULL DEFAULT '0'
 elements: CONTAINER_ID int(10) NOT NULL DEFAULT '0'
 elements: CROSS_SCENE int(3) NOT NULL DEFAULT '0'
 elements: BACKGROUND int(3) NOT NULL DEFAULT '0'
 elements: PRIORITY int(10) NOT NULL DEFAULT '0'
 elements: JAVASCRIPT text
 elements: WIZARD_DATA text
 elements: CSS longtext
 elements: S3D_SCENE varchar(255) NOT NULL DEFAULT ''
 elements: SMART_REPEAT int(3) NOT NULL DEFAULT '0'
 elements: EASY_CONFIG int(3) NOT NULL DEFAULT '0'
 elements: APPEAR_ANIMATION int(3) NOT NULL DEFAULT '0'
 elements: CLASS_TEMPLATE varchar(50) NOT NULL DEFAULT ''

 elm_states: ID int(10) unsigned NOT NULL auto_increment
 elm_states: ELEMENT_ID int(10) NOT NULL DEFAULT '0'
 elm_states: TITLE varchar(255) NOT NULL DEFAULT ''
 elm_states: IMAGE varchar(255) NOT NULL DEFAULT ''
 elm_states: HTML longtext
 elm_states: IS_DYNAMIC int(3) NOT NULL DEFAULT '0'
 elm_states: CURRENT_STATE int(3) NOT NULL DEFAULT '0'
 elm_states: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 elm_states: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
 elm_states: ACTION_OBJECT varchar(255) NOT NULL DEFAULT ''
 elm_states: ACTION_METHOD varchar(255) NOT NULL DEFAULT ''
 elm_states: CONDITION int(3) NOT NULL DEFAULT '0'
 elm_states: CONDITION_VALUE varchar(255) NOT NULL DEFAULT ''
 elm_states: CONDITION_ADVANCED text
 elm_states: CODE text
 elm_states: SCRIPT_ID int(10) NOT NULL DEFAULT '0'
 elm_states: MENU_ITEM_ID int(10) NOT NULL DEFAULT '0'
 elm_states: HOMEPAGE_ID int(10) NOT NULL DEFAULT '0'
 elm_states: OPEN_SCENE_ID int(10) NOT NULL DEFAULT '0'
 elm_states: EXT_URL varchar(255) NOT NULL DEFAULT ''
 elm_states: WINDOW_POSX int(10) NOT NULL DEFAULT '0'
 elm_states: WINDOW_POSY int(10) NOT NULL DEFAULT '0'
 elm_states: WINDOW_WIDTH int(10) NOT NULL DEFAULT '0'
 elm_states: WINDOW_HEIGHT int(10) NOT NULL DEFAULT '0'
 elm_states: SWITCH_SCENE int(3) NOT NULL DEFAULT '0'
 elm_states: S3D_OBJECT varchar(255) NOT NULL DEFAULT ''
 elm_states: S3D_CAMERA varchar(255) NOT NULL DEFAULT ''
 elm_states: CURRENT_STATUS int(3) NOT NULL DEFAULT '0'
 elm_states: PRIORITY int(10) NOT NULL DEFAULT '0'
EOD;

        parent::dbInstall($data);
    }
// --------------------------------------------------------------------
}

/*
*
* TW9kdWxlIGNyZWF0ZWQgTWF5IDI0LCAyMDEyIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>