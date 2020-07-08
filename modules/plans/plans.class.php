<?php
/**
 * Plans
 * @package project
 * @author Wizard <sergejey@gmail.com>
 * @copyright http://majordomo.smartliving.ru/ (c)
 * @version 0.1 (wizard, 21:02:32 [Feb 24, 2019])
 */
//
//
class plans extends module
{
    /**
     * plans
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "plans";
        $this->title = "<#LANG_MODULE_PLANS#>";
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
    function saveParams($data = 1)
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
        if ($this->data_source == 'plans' || $this->data_source == '') {
            if ($this->view_mode == '' || $this->view_mode == 'search_plans') {
                $this->search_plans($out);
            }
            if ($this->view_mode == 'edit_plans') {
                $this->edit_plans($out, $this->id);
            }
            if ($this->view_mode == 'delete_plans') {
                $this->delete_plans($this->id);
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
        if ($this->ajax) {
            $op=gr('op');
            if ($op=='checkAllStates') {
                $plan_id = gr('plan_id','int');
                $rec=SQLSelectOne("SELECT * FROM plans WHERE ID=".$plan_id);
                $states=SQLSelect("SELECT ID FROM plan_states WHERE PLAN_ID=".$plan_id);
                foreach($states as &$state) {
                    $this->processState($state);
                }
                $content=LoadFile(ROOT.'cms/plans/'.$rec['IMAGE']);
                $dynData=$this->findDynamicText($content);
                if (is_array($dynData)) {
                    foreach($dynData as $dynItem) {
                        $states[]=$dynItem;
                    }
                }
                $components=SQLSelect("SELECT ID,REPLACE_NAME FROM plan_components WHERE PLAN_ID=".$plan_id);
                foreach($components as $component) {
                    $states[]=array('COMPONENT_ID'=>$component['ID'],'ITEM'=>'component'.$component['ID']);
                }
                $result = $states;
                echo json_encode($result);
            }
            if ($op=='click') {
                $id=gr('id','int');
                $state=SQLSelectOne("SELECT * FROM plan_states WHERE ID=".$id);
                if ($state['ACTION_OBJECT']!='' && $state['ACTION_METHOD']!='') {
                    callMethod($state['ACTION_OBJECT'].'.'.$state['ACTION_METHOD']);
                }
                if ($state['SCRIPT_ID']) {
                    runScript($state['SCRIPT_ID']);
                }
                if ($state['CODE']!='') {
                    eval($state['CODE']);
                }
                echo "OK";
            }
            if ($op=='click_component') {
                $id=gr('id','int');
                $component=SQLSelectOne("SELECT * FROM plan_components WHERE ID=".$id);
                if ($component['ACTION_OBJECT']!='' && $component['ACTION_METHOD']!='') {
                    callMethod($component['ACTION_OBJECT'].'.'.$component['ACTION_METHOD']);
                }
                if ($component['SCRIPT_ID']) {
                    runScript($component['SCRIPT_ID']);
                }
                if ($component['CODE']!='') {
                    eval($component['CODE']);
                }
                echo "OK";
            }
            if ($op=='getComponent') {
                $id=gr('id','int');
                $component=SQLSelectOne("SELECT * FROM plan_components WHERE ID=".$id);
                $result=array();
                if ($component['ID']) {
                    $rec=SQLSelectOne("SELECT * FROM plans WHERE ID=".$component['PLAN_ID']);
                    $content=LoadFile(ROOT.'cms/plans/'.$rec['IMAGE']);
                    $p = xml_parser_create();
                    xml_parse_into_struct($p, $content, $vals, $index);
                    xml_parser_free($p);
                    $total=count($vals);
                    for($i=0;$i<$total;$i++) {
                        $attributes=array();
                        if (is_array($vals[$i]['attributes'])) {
                            foreach($vals[$i]['attributes'] as $k=>$v) {
                                $attributes[strtolower($k)]=$v;
                            }
                            if ($attributes['id']==$component['REPLACE_NAME']) {
                                include_once (DIR_MODULES.$this->name.'/components/'.$component['COMPONENT_NAME'].'.class.php');
                                $object=new $component['COMPONENT_NAME']($component['ID']);
                                $svg=$object->getSVG($attributes);
                                $result['SVG']=$svg;
                                break;
                            }
                        }
                    }
                    //$result=$component;
                }
                echo json_encode($result);
            }
            exit;
        }

        /*
        if ($this->id && !$_GET['id']) {
            $this->embed=1;
        }
        */

        if (!$this->id && gr('id','int')) {
            $this->id=gr('id','int');
        }
        if ($this->id) {
            $out['ID']=$this->id;
            $out['PREVIEW']=$this->getPreview($this->id);
        } else {
            $plans=SQLSelect("SELECT * FROM plans ORDER BY TITLE");
            $out['PLANS']=$plans;
        }
        //$this->admin($out);
    }

    /**
     * plans search
     *
     * @access public
     */
    function search_plans(&$out)
    {
        require(DIR_MODULES . $this->name . '/plans_search.inc.php');
    }

    /**
     * plans edit/add
     *
     * @access public
     */
    function edit_plans(&$out, $id)
    {
        require(DIR_MODULES . $this->name . '/plans_edit.inc.php');
    }
    
    function getPreview($id) {
        $out = array();
        $rec=SQLSelectOne("SELECT * FROM plans WHERE ID=".(int)$id);
        $states = SQLSelect("SELECT * FROM plan_states WHERE PLAN_ID=".(int)$rec['ID']);
        foreach($states as &$state) {
            $this->processState($state);
            $state['CAN_BE_CLICKED']=1;
        }
        $out['STATES']=$states;
        $content=LoadFile(ROOT.'cms/plans/'.$rec['IMAGE']);
        $out['PLAN_ID']=$rec['ID'];
        $out['PLAN_NEED_ZOOM']=$rec['NEED_ZOOM'];
        if ($this->width || $this->height || $this->embed) {
            $rec['AUTO_ZOOM']=0;
            if ($this->width) $out['WIDTH']=$this->width;
            if ($this->height) $out['HEIGHT']=$this->height;
        }
        $out['PLAN_AUTO_ZOOM']=$rec['AUTO_ZOOM'];
        $out['PLAN_CUSTOM_JAVASCRIPT']=$rec['CUSTOM_JAVASCRIPT'];
        $out['PLAN_CUSTOM_CSS']=$rec['CUSTOM_CSS'];
        $dynData=$this->findDynamicText($content);
        if (is_array($dynData)) {
            foreach($dynData as $dynItem) {
                $content = str_replace($dynItem['TEMPLATE'],$dynItem['CONTENT'],$content);
            }
        }

        $components=SQLSelect("SELECT * FROM plan_components WHERE PLAN_ID=".$rec['ID']);
        if ($components[0]['ID']) {
            $p = xml_parser_create();
            xml_parse_into_struct($p, $content, $vals, $index);
            xml_parser_free($p);

            foreach($components as &$component) {
                /*
                if ($component['MENU_ITEM_ID'] || $component['HOMEPAGE_ID'] || $component['EXT_URL'] ||
                $component['CODE'] || $component['ACTION_OBJECT'] || $component['SCRIPT_ID']) {

                }
                */
                $component['CAN_BE_CLICKED']=1;
                $total=count($vals);
                for($i=0;$i<$total;$i++) {
                    $attributes=array();
                    if (is_array($vals[$i]['attributes'])) {
                        foreach($vals[$i]['attributes'] as $k=>$v) {
                            $attributes[strtolower($k)]=$v;
                        }
                        if ($attributes['id']==$component['REPLACE_NAME']) {
                            $out['PLAN_CUSTOM_CSS'].="\n#".$attributes['id']." {display:none !important}";
                            //dprint($vals[$i]);
                            include_once (DIR_MODULES.$this->name.'/components/'.$component['COMPONENT_NAME'].'.class.php');
                            $object=new $component['COMPONENT_NAME']($component['ID']);
                            $svg='<g id="component'.$component['ID'].'">'.$object->getSVG($attributes).'</g>';
                            $js=$object->getJavascript($attributes);
                            if ($js!='') {
                                $out['PLAN_CUSTOM_JAVASCRIPT'].="\n".$js;
                            }
                            $content=preg_replace('/<\w+[^<>]+id=["\']'.$component['REPLACE_NAME'].'["\']/is',$svg.'\0',$content);
                            break;
                        }
                    }
                }
                //dprint($svg);
            }
            $out['COMPONENTS']=$components;
        }




        $out['SVG_CONTENT']=$content;
        $p = new parser(DIR_TEMPLATES . $this->name . "/preview.html", $out, $this);
        return $p->result;
    }

    function findDynamicText($content,$process = 1) {
        $result = array();
        $p = xml_parser_create();
        xml_parse_into_struct($p, $content, $vals, $index);
        xml_parser_free($p);
        foreach($vals as $val) {
            if (isset($val['value']) && preg_match_all('/%([\w\d\.]+?)\.([\w\d\.]+?)%/',$val['value'],$m) && isset($val['attributes'])) {
                $id = '';
                foreach($val['attributes'] as $attr=>$attr_v) {
                    if (strtolower($attr)=='id') {
                        $id=$attr_v;
                    }
                }
                if ($id!='') {
                    $props=array();
                    foreach($m[0] as $prop) {
                        $prop=trim($prop,'%');
                        $props[]=$prop;
                    }
                    $item=array('ITEM'=>$id,'TEMPLATE'=>$val['value'],'PROPERTIES'=>$props);
                    if ($process) {
                        $item['CONTENT']=processTitle($item['TEMPLATE']);
                    }
                    $result[]=$item;
                }
            }
        }
        return $result;
    }
    
    function checkState($id) {
        $rec = SQLSelectOne("SELECT * FROM plan_states WHERE ID=".(int)($id));
        if (!$rec['ID']) return 0;

        if (!$rec['LINKED_OBJECT'] || !$rec['LINKED_PROPERTY']) return 0;
        $value=gg(trim($rec['LINKED_OBJECT']).'.'.trim($rec['LINKED_PROPERTY']));
        if ($rec['CONDITION']==1 && $value==$rec['CONDITION_VALUE']) {
            $status=1;
        } elseif ($rec['CONDITION']==2 && (float)$value>(float)$rec['CONDITION_VALUE']) {
            $status=1;
        } elseif ($rec['CONDITION']==3 && (float)$value<(float)$rec['CONDITION_VALUE']) {
            $status=1;
        } elseif ($rec['CONDITION']==4 && $value!=$rec['CONDITION_VALUE']) {
            $status=1;
        } else {
            $status=0;
        }
        return $status;
    }


    function getWatchedProperties($plans) {

        $qry='1';
        $qry_plans='1';

        if (!IsSet($plans['all'])) {
            $qry.=" AND (0 ";
            $qry_plans.=" AND (0 ";
            foreach($plans as $k=>$v) {
                if ($k=='all') {
                    continue;
                }
                $qry.=" OR PLAN_ID=".(int)$k;
                $qry_plans.=" OR plans.ID=".(int)$k;
            }
            $qry.=")";
            $qry_plans.=")";
        } else {
        }

        //DebMes("qry: ".$qry);
        $plans=SQLSelect("SELECT * FROM plans WHERE $qry_plans");
        $states=SQLSelect("SELECT * FROM plan_states WHERE $qry");

        $properties=array();
        $total=count($states);
        for($i=0;$i<$total;$i++) {
            // linked object.property
            if ($states[$i]['LINKED_OBJECT'] && $states[$i]['LINKED_PROPERTY']) {
                $properties[]=array('PROPERTY'=>mb_strtolower($states[$i]['LINKED_OBJECT'].'.'.$states[$i]['LINKED_PROPERTY'], 'UTF-8'), 'STATE_ID'=>$states[$i]['ID']);
            }
        }

        $components=SQLSelect("SELECT plan_components_data.* FROM plan_components_data LEFT JOIN plan_components ON plan_components_data.COMPONENT_ID=plan_components.ID WHERE $qry AND plan_components_data.LINKED_OBJECT!='' AND plan_components_data.LINKED_PROPERTY!=''");
        foreach($components as $component) {
            if ($component['LINKED_OBJECT'] && $component['LINKED_PROPERTY']) {
                $properties[]=array('PROPERTY'=>mb_strtolower($component['LINKED_OBJECT'].'.'.$component['LINKED_PROPERTY'], 'UTF-8'),'STATE_ID'=>'component'.$component['COMPONENT_ID']);
            }
        }

        foreach($plans as $rec) {
            $content=LoadFile(ROOT.'cms/plans/'.$rec['IMAGE']);
            $dynData=$this->findDynamicText($content,false);
            if (is_array($dynData)) {
                foreach($dynData as $dynItem) {
                    //$content = str_replace($dynItem['TEMPLATE'],$dynItem['CONTENT'],$content);
                    foreach($dynItem['PROPERTIES'] as $property) {
                        $properties[]=array('PROPERTY'=>mb_strtolower($property,'UTF-8'),'STATE_ID'=>$dynItem['ITEM'],'TEMPLATE'=>$dynItem['TEMPLATE']);
                    }
                }
            }
        }

        return $properties;
    }

    function processState(&$state) {
        if (is_numeric($state['ID'])) {
            if (!$state['TITLE']) {
                $state=SQLSelectOne("SELECT ID, TITLE, CSS_CLASS, CSS_CLASS_INVERSE, ITEM FROM plan_states WHERE ID=".(int)$state['ID']);
            }
            $state_value=$this->checkState($state['ID']);
            $state['STATE_VALUE']=(string)$state_value;
            if ($state_value) {
                $state['SET_CLASS']=$state['CSS_CLASS'];
            } else {
                $state['SET_CLASS']=$state['CSS_CLASS_INVERSE'];
            }
        } elseif (preg_match('/^component(\d+)$/',$state['ID'],$m)) {
            $component=SQLSelectOne("SELECT * FROM plan_components WHERE ID=".$m[1]);
            if ($component['ID']) {
                //...
            }
        } elseif ($state['TEMPLATE']) {
            $state['CONTENT']=processTitle($state['TEMPLATE']);
        }
    }

    function getImageItems($plan_id) {
        $rec=SQLSelectOne("SELECT * FROM plans WHERE ID=".(int)$plan_id);
        $items=array();
        if ($rec['IMAGE']) {
            $content=LoadFile(ROOT.'cms/plans/'.$rec['IMAGE']);
            $p = xml_parser_create();
            xml_parse_into_struct($p, $content, $vals, $index);
            xml_parser_free($p);
            $res_vals=array();
            foreach($vals as &$val) {
                if (!isset($val['attributes']['ID'])) continue;
                $val['tag']=strtoupper($val['tag']);
                if ($val['tag']=='TITLE' ||
                    $val['tag']=='TSPAN' ||
                    $val['tag']=='DESC' ||
                    $val['tag']=='METADATA' ||
                    $val['tag']=='FILTER' ||
                    $val['tag']=='DEFS' ||
                    0
                ) continue;
                $res_vals[]=$val;
            }
            foreach($res_vals as $val) {
                $item=array();
                $item['ITEM']=$val['attributes']['ID'];
                $item['TITLE']=$item['ITEM'].' ('.$val['tag'].')';
                $items[]=$item;
            }
        }
        usort($items, function($a,$b) {
            return strcmp($a['TITLE'],$b['TITLE']);
        });
        return $items;
    }

    function getCSSClasses($plan_id) {

        $plan_rec=SQLSelectOne("SELECT * FROM plans WHERE ID=".(int)$plan_id);

        $classes=array();

        $classes[]=array('CLASS'=>'show_it','TITLE'=>LANG_STYLE_SHOW_IT);
        $classes[]=array('CLASS'=>'hide_it','TITLE'=>LANG_STYLE_HIDE_IT);
        $classes[]=array('CLASS'=>'blink_it','TITLE'=>LANG_STYLE_BLINK_IT);
        $classes[]=array('CLASS'=>'spin_it','TITLE'=>LANG_STYLE_SPIN_IT);
        $classes[]=array('CLASS'=>'fadeout50','TITLE'=>LANG_STYLE_FADEOUT50);
        $classes[]=array('CLASS'=>'fadeout30','TITLE'=>LANG_STYLE_FADEOUT30);
        $classes[]=array('CLASS'=>'fadeout10','TITLE'=>LANG_STYLE_FADEOUT10);
        $classes[]=array('CLASS'=>'fadeout0','TITLE'=>LANG_STYLE_FADEOUT0);

        if ($plan_rec['CUSTOM_CSS']!='') {
            if (preg_match_all('/\.([^\s{\n\.]+)\s+{/is',$plan_rec['CUSTOM_CSS'],$m)) {
                foreach($m[1] as $class) {
                    $classes[]=array('CLASS'=>$class,'TITLE'=>'CSS: '.$class);
                }
            }
        }

        return $classes;

    }

    function getComponentTypes() {
        $types=array();

        $files=scandir(DIR_MODULES.$this->name.'/components');
        foreach($files as $filename) {
            if (preg_match('/(.+?)\.class\.php/',$filename,$m)) {
                $tmp=explode('_',$m[1]);
                foreach($tmp as &$mname) {
                    $mname=ucfirst($mname);
                }
                $title=implode(' ',$tmp);
                $types[]=array(
                    'NAME'=>$m[1],
                    'TITLE'=>$title
                );
            }
        }

        return $types;
    }

    function delete_plans($id)
    {
        $rec = SQLSelectOne("SELECT * FROM plans WHERE ID='$id'");
        // some action for related tables
        SQLExec("DELETE FROM plan_states WHERE PLAN_ID='" . $rec['ID'] . "'");
        $components=SQLSelect("SELECT ID FROM plan_components WHERE PLAN_ID=".$rec['ID']);
        foreach($components as $component) {
            SQLExec("DELETE FROM plan_components_data WHERE COMPONENT_ID='" . $component['ID'] . "'");
            SQLExec("DELETE FROM plan_components WHERE ID='" . $component['ID'] . "'");
        }
        SQLExec("DELETE FROM plans WHERE ID='" . $rec['ID'] . "'");
    }

    function install($data = '')
    {
        parent::install();
    }

    function uninstall()
    {
        SQLExec('DROP TABLE IF EXISTS plans');
        SQLExec('DROP TABLE IF EXISTS plan_states');
        SQLExec('DROP TABLE IF EXISTS plan_components');
        SQLExec('DROP TABLE IF EXISTS plan_components_data');
        parent::uninstall();
    }

    function dbInstall($data)
    {
        /*
        plans -
        */
        $data = <<<EOD
 plans: ID int(10) unsigned NOT NULL auto_increment
 plans: TITLE varchar(255) NOT NULL DEFAULT ''
 plans: IMAGE varchar(255) NOT NULL DEFAULT ''
 plans: NEED_ZOOM int(3) NOT NULL DEFAULT '0'
 plans: AUTO_ZOOM int(3) NOT NULL DEFAULT '0'
 plans: CUSTOM_CSS text
 plans: CUSTOM_JAVASCRIPT text
 
 plan_states: ID int(10) unsigned NOT NULL auto_increment
 plan_states: TITLE varchar(255) NOT NULL DEFAULT ''
 plan_states: PLAN_ID int(10) NOT NULL DEFAULT '0'
 plan_states: ITEM varchar(255) NOT NULL DEFAULT ''
 plan_states: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 plan_states: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
 plan_states: CONDITION int(3) NOT NULL DEFAULT '0'
 plan_states: CONDITION_VALUE varchar(255) NOT NULL DEFAULT ''
 plan_states: CONDITION_ADVANCED text
 plan_states: CSS_CLASS varchar(255) NOT NULL DEFAULT '' 
 plan_states: CSS_CLASS_INVERSE varchar(255) NOT NULL DEFAULT '' 
 
 plan_states: ACTION_OBJECT varchar(255) NOT NULL DEFAULT ''
 plan_states: ACTION_METHOD varchar(255) NOT NULL DEFAULT ''
 plan_states: CODE text
 plan_states: SCRIPT_ID int(10) NOT NULL DEFAULT '0'
 plan_states: MENU_ITEM_ID int(10) NOT NULL DEFAULT '0'
 plan_states: HOMEPAGE_ID int(10) NOT NULL DEFAULT '0'
 plan_states: EXT_URL varchar(255) NOT NULL DEFAULT ''
 
 plan_components: ID int(10) unsigned NOT NULL auto_increment
 plan_components: PLAN_ID int(10) NOT NULL DEFAULT '0'
 plan_components: TITLE varchar(255) NOT NULL DEFAULT ''
 plan_components: COMPONENT_NAME varchar(255) NOT NULL DEFAULT ''
 plan_components: REPLACE_NAME varchar(255) NOT NULL DEFAULT ''
 
 plan_components: ACTION_OBJECT varchar(255) NOT NULL DEFAULT ''
 plan_components: ACTION_METHOD varchar(255) NOT NULL DEFAULT ''
 plan_components: CODE text
 plan_components: SCRIPT_ID int(10) NOT NULL DEFAULT '0'
 plan_components: MENU_ITEM_ID int(10) NOT NULL DEFAULT '0'
 plan_components: HOMEPAGE_ID int(10) NOT NULL DEFAULT '0'
 plan_components: EXT_URL varchar(255) NOT NULL DEFAULT ''
 
 
 plan_components_data: ID int(10) unsigned NOT NULL auto_increment
 plan_components_data: COMPONENT_ID int(10) NOT NULL DEFAULT '0'
 plan_components_data: PROPERTY_NAME varchar(255) NOT NULL DEFAULT ''
 plan_components_data: PROPERTY_VALUE varchar(255) NOT NULL DEFAULT ''
 plan_components_data: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 plan_components_data: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
 
EOD;
        parent::dbInstall($data);
    }

}
/*
*
* TW9kdWxlIGNyZWF0ZWQgRmViIDI0LCAyMDE5IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
