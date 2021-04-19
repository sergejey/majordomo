<?php
/**
 * LinkedObject
 *
 * Linkedobject
 *
 * @package project
 * @author Serge J. <jey@tut.by>
 * @copyright http://www.atmatic.eu/ (c)
 * @version 0.1 (wizard, 13:11:32 [Nov 19, 2014])
 */
//
//
class linkedobject extends module
{
    /**
     * linkedobject
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "linkedobject";
        $this->title = "LinkedObject";
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


        global $op;
        global $ajax;
        global $object;
        global $uniq;
        global $first_run;

        if (!$first_run) {
            $out['FIRST_RUN'] = 1;
            $first_run = 1;
        }

        if ($this->width) {
			$ifPX = substr($this->width, -1);
			if($ifPX != 'x' && $ifPX != '%') $this->width = $this->width.'px';
			
            $out['WIDTH'] = $this->width;
        } else {
            $out['WIDTH'] = '90%';
        }

        if ($uniq) {
            $out['UNIQ'] = $uniq;
        } else {
            $out['UNIQ'] = rand(0, 999999);
        }

        if ($op == 'redirect') {
            global $object;
            global $sub;
            if (!$object) {
                redirect(ROOTHTML);
            }
            $obj = getObject($object);
            if (!$obj) {
                redirect(ROOTHTML);
            }
            if ($sub != '') {
                redirect(ROOTHTML . 'panel/class/' . $obj->class_id . '/object/' . $obj->id . '/' . $sub . '.html');
            } else {
                redirect(ROOTHTML . 'panel/class/' . $obj->class_id . '/object/' . $obj->id . '.html');
            }
        }

        if ($ajax == 1) {

            if ($op == 'objects') {
                $res = array();
                $tmp = SQLSelect("SELECT ID, TITLE, DESCRIPTION FROM objects ORDER BY CLASS_ID, TITLE");
                $total = count($tmp);
                for ($i = 0; $i < $total; $i++) {
                    $res[] = $tmp[$i];
                }
                $res[]=array('ID'=>'scripts','TITLE'=>'AllScripts','DESCRIPTION'=>LANG_SCRIPTS);
                $res['OBJECTS'] = $res;

                //$tmp=SQLSelectOne("SELECT TITLE FROM objects ORDER BY ID DESC LIMIT 1");
                //$res['LATEST_OBJECT']=$tmp['TITLE'];
                $res['LATEST_OBJECT'] = '';
                header('Content-type:application/json');
                echo json_encode($res);
            }

            if ($op == 'properties') {
                $res = array();
                $properties = array();
                do {
                    if (!$object) break;
                    if ($object=='AllScripts') break;
                    $obj = getObject($object);
                    if (!$obj) break;
                    $parent_properties = $obj->getParentProperties($obj->class_id, '', 1);
                    if ($parent_properties && is_array($parent_properties)) {
                        foreach ($parent_properties as $v) {
                            $properties[] = $v;
                        }
                    }
                    $tmp = SQLSelect("SELECT * FROM properties WHERE OBJECT_ID='" . (int)$obj->id . "'");
                    if ($tmp && is_array($tmp)) {
                        foreach ($tmp as $i) {
                            $properties[] = $i;
                        }
                    }
                } while (0);
                $res['PROPERTIES'] = $properties;
                header('Content-type:application/json');
                echo json_encode($res);
            }

            if ($op == 'methods') {
                $res = array();
                $properties = array();
                do {
                    if (!$object) break;
                    if ($object=='AllScripts') {
                        $properties=SQLSelect("SELECT * FROM scripts ORDER BY TITLE");
                        break;
                    }
                    $obj = getObject($object);
                    if (!$obj) break;
                    $parent_properties = $obj->getParentMethods($obj->class_id, '', 1);
                    if ($parent_properties && is_array($parent_properties)) {
                        foreach ($parent_properties as $v) {
                            if (!$seen[$v['TITLE']]) {
                                $properties[] = $v;
                                $seen[$v['TITLE']] = 1;
                            }
                        }
                    }
                    $tmp = SQLSelect("SELECT * FROM methods WHERE OBJECT_ID='" . (int)$obj->id . "'");
                    if ($tmp && is_array($tmp)) {
                        foreach ($tmp as $i) {
                            if (!$seen[$i['TITLE']]) {
                                $properties[] = $i;
                                $seen[$i['TITLE']] = 1;
                            }
                        }
                    }
                } while (0);
                $res['METHODS'] = $properties;
                header('Content-type:application/json');
                echo json_encode($res);
            }


            exit;
        }

        if ($this->object_field) {
            $objects = SQLSelect("SELECT * FROM objects ORDER BY CLASS_ID, TITLE");
			
			foreach($objects as $key => $object) {
				$className = SQLSelectOne("SELECT TITLE FROM classes WHERE ID = '".$object['CLASS_ID']."'");
				if($object['CLASS_ID'] != $objects[$key-1]['CLASS_ID']) {
					$objects[$key]['NEW_GROUP_START'] = 1; 
				} else {
					$objects[$key]['NEW_GROUP_START'] = 0; 
				}
				if($object['CLASS_ID'] != $objects[$key+1]['CLASS_ID']) {
					$objects[$key]['NEW_GROUP_END'] = 1; 
				} else {
					$objects[$key]['NEW_GROUP_END'] = 0; 
				}
				
				$objects[$key]['CLASS_NAME'] = $className['TITLE'];
				
			}
			
            $objects[]=array('ID'=>'scripts','TITLE'=>'AllScripts','DESCRIPTION'=>LANG_SCRIPTS);
			//echo '<pre>';
			//var_dump($objects);
			//die();
            $out['OBJECTS'] = $objects;
            $out['OBJECT_FIELD'] = $this->object_field;
        }

        if ($this->property_field) {
            $out['PROPERTY_FIELD'] = $this->property_field;
        }

        if ($this->method_field) {
            $out['METHOD_FIELD'] = $this->method_field;
        }


        $out['VIEW_MODE'] = $this->view_mode;
        $out['EDIT_MODE'] = $this->edit_mode;
        $out['MODE'] = $this->mode;
        $out['ACTION'] = $this->action;
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