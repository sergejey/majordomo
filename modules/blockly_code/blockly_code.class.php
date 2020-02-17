<?php
/**
 * Blockly_code
 *
 * Blockly_code
 *
 * @package project
 * @author Serge J. <jey@tut.by>
 * @copyright http://www.atmatic.eu/ (c)
 * @version 0.1 (wizard, 14:09:29 [Sep 01, 2014])
 */
//
//
class blockly_code extends module
{
    /**
     * blockly_code
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "blockly_code";
        $this->title = "Blockly code";
        $this->module_category = "<#LANG_SECTION_SYSTEM#>";
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
        if (IsSet($this->id)) {
            $p["id"] = $this->id;
        }
        if (IsSet($this->view_mode)) {
            $p["view_mode"] = $this->view_mode;
        }
        if (IsSet($this->edit_mode)) {
            $p["edit_mode"] = $this->edit_mode;
        }
        if (IsSet($this->tab)) {
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
        if ($this->action == 'admin') {
            $this->admin($out);
        } else {
            $this->usual($out);
        }
        if (IsSet($this->owner->action)) {
            $out['PARENT_ACTION'] = $this->owner->action;
        }
        if (IsSet($this->owner->name)) {
            $out['PARENT_NAME'] = $this->owner->name;
        }
        $out['VIEW_MODE'] = $this->view_mode;
        $out['EDIT_MODE'] = $this->edit_mode;
        $out['MODE'] = $this->mode;
        $out['ACTION'] = $this->action;
        $out['TAB'] = $this->tab;
        if ($this->single_rec) {
            $out['SINGLE_REC'] = 1;
        }
        $this->data = $out;
        $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
        $this->result = $p->result;
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


        if (!$this->code_field) {
            $this->code_field = 'code';
        }

        if ($this->type) {
            $out['TYPE'] = $this->type;
        } else {
            $out['TYPE'] = 'php';
        }

        $out['CODE_FIELD'] = $this->code_field;

        $rec = SQLSelectOne("SELECT * FROM blockly_code WHERE SYSTEM_NAME LIKE '" . DBSafe($this->system_name) . "'");
        $out['CODE_TYPE'] = (int)$rec['CODE_TYPE'];
        if (!$rec['ID'] && $this->owner->xml) {
            $rec['XML'] = $this->owner->xml;
        }
        if (preg_match_all('/<block type="(.+?)\_turnoff"(.+?)>/uis',$rec['XML'],$m)) {
            $total = count($m[0]);
            for($i=0;$i<$total;$i++) {
                $new_line = $m[0][$i];
                $closed_block = 0;
                if (preg_match('/\/>/',$new_line)) {
                    $closed_block = 1;
                }
                $new_line = str_replace('_turnOff','_switch',$new_line);
                $new_line .= "\n<field name=\"MODE\">OFF</field>";
                if ($closed_block) {
                    $new_line = str_replace('/>','>',$new_line);
                    $new_line.="\n</block>";
                }
                $rec['XML'] = str_replace($m[0][$i],$new_line,$rec['XML']);
            }
        }
        if (preg_match_all('/<block type="(.+?)\_turnOn"(.+?)>/uis',$rec['XML'],$m)) {
            $total = count($m[0]);
            for($i=0;$i<$total;$i++) {
                $new_line = $m[0][$i];
                $closed_block = 0;
                if (preg_match('/\/>/',$new_line)) {
                    $closed_block = 1;
                }
                $new_line = str_replace('_turnOn','_switch',$new_line);
                $new_line .= "\n<field name=\"MODE\">ON</field>";
                if ($closed_block) {
                    $new_line = str_replace('/>','>',$new_line);
                    $new_line.="\n</block>";
                }
                $rec['XML'] = str_replace($m[0][$i],$new_line,$rec['XML']);
            }
        }

        if (!$rec['ID'] && !$this->type) {
            $rec['CODE_TYPE'] = 0;
            $rec['CODE_TYPE_UNKNOWN'] = 1;
        }


        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $out['TYPE'] == 'php') {
            global $xml;
            global $code;

            global ${$this->code_field . "_code_type"};
            SQLExec("DELETE FROM blockly_code WHERE SYSTEM_NAME LIKE '" . DBSafe($this->system_name) . "'");
            $rec = array();
            $rec['XML'] = $xml;
            $rec['CODE'] = $code;
            $rec['UPDATED'] = date('Y-m-d H:i:s');
            $rec['SYSTEM_NAME'] = $this->system_name;
            if (isset(${$this->code_field . "_code_type"})) {
                $rec['CODE_TYPE'] = (int)${$this->code_field . "_code_type"};
            } else {
                $rec['CODE_TYPE'] = 2;
            }
            if (!$rec['CODE_TYPE']) {
                //$rec['XML']='';
            }
            $rec['ID'] = SQLInsert('blockly_code', $rec);

        }

        $rec['XML'] = preg_replace('/id="\?/', 'id="Q', $rec['XML']);
        $out['XML'] = $rec['XML'];
        //$out['XML']='';

        //dprint($rec);

        $out['CODE_TYPE'] = (int)$rec['CODE_TYPE'];


        $out['DEVICES'] = SQLSelect("SELECT ID,TITLE,TYPE,LINKED_OBJECT FROM devices WHERE TYPE IN ('relay','dimmer','button','thermostat') ORDER BY TITLE");

        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }
        if ($this->data_source == 'blockly_code' || $this->data_source == '') {
            if ($this->view_mode == '' || $this->view_mode == 'search_blockly_code') {
                $this->search_blockly_code($out);
            }
            if ($this->view_mode == 'edit_blockly_code') {
                $this->edit_blockly_code($out, $this->id);
            }
            if ($this->view_mode == 'delete_blockly_code') {
                $this->delete_blockly_code($this->id);
                $this->redirect("?");
            }
        }
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
        $this->admin($out);
    }

    /**
     * blockly_code search
     *
     * @access public
     */
    function search_blockly_code(&$out)
    {
        require(DIR_MODULES . $this->name . '/blockly_code_search.inc.php');
    }

    /**
     * blockly_code edit/add
     *
     * @access public
     */
    function edit_blockly_code(&$out, $id)
    {
        require(DIR_MODULES . $this->name . '/blockly_code_edit.inc.php');
    }

    /**
     * blockly_code delete record
     *
     * @access public
     */
    function delete_blockly_code($id)
    {
        $rec = SQLSelectOne("SELECT * FROM blockly_code WHERE ID='$id'");
        // some action for related tables
        SQLExec("DELETE FROM blockly_code WHERE ID='" . $rec['ID'] . "'");
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
        parent::install();
        SQLExec("UPDATE project_modules SET HIDDEN=1 WHERE NAME LIKE 'blockly_code'");
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
        SQLDropTable('blockly_code');
        parent::uninstall();
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
        blockly_code - Blockly_code
        */
        $data = <<<EOD
 blockly_code: ID int(10) unsigned NOT NULL auto_increment
 blockly_code: SYSTEM_NAME varchar(255) NOT NULL DEFAULT ''
 blockly_code: CODE_TYPE int(3) NOT NULL DEFAULT '0'
 blockly_code: CODE text
 blockly_code: XML text
 blockly_code: UPDATED datetime
EOD;
        parent::dbInstall($data);
    }
// --------------------------------------------------------------------
}

/*
*
* TW9kdWxlIGNyZWF0ZWQgU2VwIDAxLCAyMDE0IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>