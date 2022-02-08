<?php
/**
 * System_errors
 *
 * System_errors
 *
 * @package project
 * @author Serge J. <jey@tut.by>
 * @copyright http://www.atmatic.eu/ (c)
 * @version 0.1 (wizard, 14:12:08 [Dec 09, 2014])
 */
//
//
class system_errors extends module
{
    /**
     * system_errors
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "system_errors";
        $this->title = "<#LANG_MODULE_SYSTEM_ERRORS#>";
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
        if (IsSet($this->data_source)) {
            $p["data_source"] = $this->data_source;
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
        $out['DATA_SOURCE'] = $this->data_source;
        $out['TAB'] = $this->tab;
        if (IsSet($this->error_id)) {
            $out['IS_SET_ERROR_ID'] = 1;
        }
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
        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }

        if ($this->mode == 'update_settings') {
            $keep_history = gr('keep_history','int');
            $rec=SQLSelectOne("SELECT * FROM settings WHERE NAME='ERRORS_KEEP_HISTORY'");
            $rec['NAME']='ERRORS_KEEP_HISTORY';
            $rec['VALUE']=$keep_history;
            SQLInsertUpdate('settings',$rec);
            $this->redirect("?ok_msg=" . urlencode(LANG_DATA_SAVED));
            if ($keep_history>0) {
                SQLExec("DELETE FROM system_errors_data WHERE ADDED<'".date('Y-m-d H:i:s',time()-$keep_history*24*60*60)."'");
            }
        }

        $res = SQLSelectOne("SELECT max(ACTIVE) IS_ERROR FROM system_errors");
        $out['ERRORS_FOUND'] = $res['IS_ERROR'];
        $this->search_system_errors($out);

        if ($this->view_mode == 'addtesterror') {
            registerError('testing_error_#' . rand(1000, 9999), 'Тестовая ошибка');
            $this->redirect("?data_source=system_errors");
        }
        if ($this->view_mode == 'readall') {
            SQLExec("UPDATE system_errors SET ACTIVE=0");
            $this->redirect("?data_source=system_errors");
        }

        if ($this->data_source == 'system_errors' || $this->data_source == '') {
            if ($this->view_mode == 'edit_system_errors') {
                $this->edit_system_errors($out, $this->id);
            }
            if ($this->view_mode == 'view_system_errors') {
                $this->view_system_errors($out, $this->id);
            }
            if ($this->view_mode == 'delete_system_errors') {
                $this->delete_system_errors($this->id);
                $this->redirect("?data_source=system_errors");
            }
            if ($this->view_mode == 'clear_system_errors') {
                SQLExec("UPDATE system_errors SET ACTIVE=0 WHERE ID='" . (int)$this->id . "'");
                SQLExec("DELETE FROM system_errors_data WHERE ERROR_ID='" . (int)$this->id . "'");
                $this->redirect("?data_source=system_errors");
            }
        }
        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }
        if ($this->data_source == 'system_errors_data') {
            if ($this->view_mode == '' || $this->view_mode == 'search_system_errors_data') {
                $this->search_system_errors_data($out);
            }
            if ($this->view_mode == 'edit_system_errors_data') {
                $this->edit_system_errors_data($out, $this->id);
            }
            if ($this->view_mode == 'view_system_errors_data') {
                $this->view_system_errors_data($out, $this->id);
            }
            if ($this->view_mode == 'delete_system_errors_data') {
                $this->delete_system_errors_data($this->id);
                $this->redirect("?data_source=system_errors_data");
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
     * system_errors search
     *
     * @access public
     */
    function search_system_errors(&$out)
    {
        require(DIR_MODULES . $this->name . '/system_errors_search.inc.php');
    }

    /**
     * system_errors edit/add
     *
     * @access public
     */
    function edit_system_errors(&$out, $id)
    {
        require(DIR_MODULES . $this->name . '/system_errors_edit.inc.php');
    }

    /**
     * system_errors view record
     *
     * @access public
     */
    function view_system_errors(&$out, $id)
    {
        require(DIR_MODULES . $this->name . '/system_errors_view.inc.php');
    }

    /**
     * system_errors delete record
     *
     * @access public
     */
    function delete_system_errors($id)
    {
        $rec = SQLSelectOne("SELECT * FROM system_errors WHERE ID='$id'");
        // some action for related tables
        SQLExec("DELETE FROM system_errors WHERE ID='" . $rec['ID'] . "'");
        SQLExec("DELETE FROM system_errors_data WHERE ERROR_ID='" . $rec['ID'] . "'");
    }

    /**
     * system_errors_data search
     *
     * @access public
     */
    function search_system_errors_data(&$out)
    {
        require(DIR_MODULES . $this->name . '/system_errors_data_search.inc.php');
    }

    /**
     * system_errors_data edit/add
     *
     * @access public
     */
    function edit_system_errors_data(&$out, $id)
    {
        require(DIR_MODULES . $this->name . '/system_errors_data_edit.inc.php');
    }

    /**
     * system_errors_data view record
     *
     * @access public
     */
    function view_system_errors_data(&$out, $id)
    {
        require(DIR_MODULES . $this->name . '/system_errors_data_view.inc.php');
    }

    /**
     * system_errors_data delete record
     *
     * @access public
     */
    function delete_system_errors_data($id)
    {
        $rec = SQLSelectOne("SELECT * FROM system_errors_data WHERE ID='$id'");
        // some action for related tables
        SQLExec("DELETE FROM system_errors_data WHERE ID='" . $rec['ID'] . "'");
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
        SQLDropTable('system_errors');
        SQLDropTable('system_errors_data');
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
        system_errors - System Errors
        system_errors_data - System Errors Data
        */
        $data = <<<EOD
 system_errors: ID int(10) unsigned NOT NULL auto_increment
 system_errors: CODE varchar(50) NOT NULL DEFAULT ''
 system_errors: TITLE varchar(255) NOT NULL DEFAULT ''
 system_errors: DETAILS text
 system_errors: ACTIVE int(3) NOT NULL DEFAULT '0'
 system_errors: LATEST_UPDATE datetime
 system_errors: KEEP_HISTORY int(3) NOT NULL DEFAULT '0'
 system_errors_data: ID int(10) unsigned NOT NULL auto_increment
 system_errors_data: ERROR_ID int(10) NOT NULL DEFAULT '0'
 system_errors_data: COMMENTS text
 system_errors_data: ADDED datetime
 system_errors_data: PROPERTIES_DATA text
 system_errors_data: METHODS_DATA text
 system_errors_data: SCRIPTS_DATA text
 system_errors_data: TIMERS_DATA text
 system_errors_data: EVENTS_DATA text
 system_errors_data: DEBUG_DATA text
EOD;
        parent::dbInstall($data);
    }
// --------------------------------------------------------------------
}

/*
*
* TW9kdWxlIGNyZWF0ZWQgRGVjIDA5LCAyMDE0IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>