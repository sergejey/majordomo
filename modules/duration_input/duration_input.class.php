<?php

class duration_input extends module
{
    var $input_id;

    /**
     * linkedobject
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "duration_input";
        $this->title = "DurationInput";
        $this->module_category = "<#LANG_SECTION_SYSTEM#>";
        $this->checkInstalled();
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
        $out = array();

        if (isset($this->uniq)) {
            $out['UNIQ'] = $this->uniq;
        } else {
            $out['UNIQ'] = rand(0, 999999);
        }

        if ($this->input_id) {
            $out['INPUT_ID'] = $this->input_id;
        }

        $this->data = $out;
        $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
        $this->result = $p->result;
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
        SQLExec("UPDATE project_modules SET HIDDEN=1 WHERE NAME LIKE '" . $this->name . "'");
    }
// --------------------------------------------------------------------
}

/*
*
* TW9kdWxlIGNyZWF0ZWQgTm92IDE5LCAyMDE0IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>
