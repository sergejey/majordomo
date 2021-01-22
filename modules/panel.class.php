<?php
/*
* @version 0.1 (auto-set)
*/

/**
 * @author Serge Dzheigalo <jey@unit.local>
 * @package project
 */
class panel extends module
{

    var $action;

// --------------------------------------------------------------------
    function __construct()
    {
        $this->name = "panel";
    }

// --------------------------------------------------------------------
    function saveParams($data = 1)
    {
        $data = array();
        $data["action"] = $this->action;
        $data["print"] = $this->print;
        return parent::saveParams($data);
    }

// --------------------------------------------------------------------
    function getParams()
    {
        global $action;
        $this->action = $action;
    }

// --------------------------------------------------------------------
    function run()
    {
        global $session;
        Define('ALTERNATIVE_TEMPLATES', 'templates_alt');


        if (gr('toggleLeftPanel')) {
            if (gg('HideLeftPanelAdmin')) {
                sg('HideLeftPanelAdmin',0);
            } else {
                sg('HideLeftPanelAdmin',1);
            }

            $uri=str_replace('toggleLeftPanel=1','',$_SERVER['REQUEST_URI']);
            $this->redirect($uri);
        }
        $out['HIDE_LEFT_PANEL']=gg('HideLeftPanelAdmin');

        global $action;
        $out['TAB']=gr('tab');

        if (defined('NO_DATABASE_CONNECTION')) {
         if (!$action) $action = 'saverestore';
         $this->print = 1;
        }
		
        if (!$this->action && $action) {
            $this->action = $action;
        }

        if ($this->action) {
            $out['TITLE']=$this->action.' ('.LANG_CONTROL_PANEL.')';
			//Узнаем название модуля
			$result = SQLSelectOne("SELECT * FROM `project_modules` WHERE NAME = '".$this->action."'");
			if($result) {
				$out['NAV_MODULE_NAME'] = $result["TITLE"];
				$out['NAV_MODULE_CAT'] = $result["CATEGORY"];
				$out['TITLE'] = $result["TITLE"].' | '.$result["CATEGORY"].' | '.LANG_CONTROL_PANEL;
			}
        }
		
		$out['SETTINGS_SITE_LANGUAGE'] = SETTINGS_SITE_LANGUAGE;
		
        if (!$session->data['SITE_USERNAME']) {
            $users = SQLSelect("SELECT * FROM users ORDER BY NAME");
            $total = count($users);

            if ($total == 1) {
                $session->data['SITE_USERNAME'] = $users[0]['USERNAME'];
                $session->data['SITE_USER_ID'] = $users[0]['ID'];
            } else {
                for ($i = 0; $i < $total; $i++) {
                    if ($users[$i]['HOST'] && $users[$i]['HOST'] == $_SERVER['REMOTE_ADDR']) {
                        $session->data['SITE_USERNAME'] = $users[$i]['USERNAME'];
                        $session->data['SITE_USER_ID'] = $users[$i]['ID'];
                    } elseif ($users[$i]['IS_DEFAULT']) {
                        $session->data['SITE_USERNAME'] = $users[$i]['USERNAME'];
                        $session->data['SITE_USER_ID'] = $users[$i]['ID'];
                    }
                }
            }
        }


        if (!$session->data["AUTHORIZED"] && $session->data['SITE_USERNAME']) {
            $user = SQLSelectOne("SELECT * FROM users WHERE USERNAME LIKE '" . DBSafe($session->data['SITE_USERNAME']) . "'");
            if ($user['IS_ADMIN']) {
                $user = SQLSelectOne("SELECT * FROM admin_users WHERE LOGIN='admin'");
                $session->data['USER_NAME'] = $user['LOGIN'];
                $session->data['USER_LEVEL'] = $user['PRIVATE'];
                $session->data['USER_ID'] = $user['ID'];
                $session->data["AUTHORIZED"] = 1;
                logAction('control_panel_enter',$session->data['USER_NAME']);
            }
        }

        if (IsSet($session->data["AUTHORIZED"]) || defined('NO_DATABASE_CONNECTION')) {
            $this->authorized = 1;
        } else {
            $tmp = SQLSelectOne("SELECT ID FROM users WHERE IS_ADMIN=1");
            if ($tmp['ID']) {
                redirect("/");
            }
            //
        }

        global $ajax_panel;
        if ($ajax_panel) {
            include_once(DIR_MODULES . 'inc_panel_ajax.php');
        }

        if ($this->print || $_GET['print']) {
            $this->print = 1;
            $out['PRINT'] = 1;
        }

        $out["TODAY"] = date('l, F d, Y');
        $out["AUTHORIZED"] = $this->authorized;

        if ($this->authorized) {

            include_once(DIR_MODULES . "control_access/control_access.class.php");
            $acc = new control_access();
            if (!$acc->checkAccess($this->action, 1)) {
                $this->redirect("?");
            }

            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                clearCache(0);
            }

            $sqlQuery = "SELECT *
                  FROM project_modules
                 WHERE (`HIDDEN`='0' OR NAME='control_modules')
                 ORDER BY FIELD(CATEGORY, '<#LANG_SECTION_OBJECTS#>', '<#LANG_SECTION_DEVICES#>', '<#LANG_SECTION_APPLICATIONS#>',
                                '<#LANG_SECTION_SETTINGS#>', '<#LANG_SECTION_SYSTEM#>'),
                          `PRIORITY`, `TITLE`";

            $modules = SQLSelect($sqlQuery);
            $old_cat = 'some_never_should_be_category_name';
            $modulesCnt = count($modules);

            for ($i = 0; $i < $modulesCnt; $i++) {
                if ($modules[$i]['NAME'] == $this->action) {
                    $modules[$i]['SELECTED'] = 1;
                }
                $modules[$i]['CATEGORY_ID'] = substr(md5($modules[$i]['CATEGORY']), 0, 4);
                if ($modules[$i]['CATEGORY'] != $old_cat) {
                    $modules[$i]['NEW_CATEGORY'] = 1;
                    $old_cat = $modules[$i]['CATEGORY'];
                    if ($i > 0) {
                        //echo $last_allow."<br>";
                        $modules[$last_allow]['LAST_IN_CATEGORY'] = 1;
                    }
                }

                if (!$acc->checkAccess($modules[$i]['NAME'])) {
                    $modules[$i]['DENIED'] = 1;
                } else {
                    $last_allow = $i;
                }

                if (file_exists(ROOT . 'img/modules/' . $modules[$i]['NAME'] . '.png')) {
                    $modules[$i]['ICON_SM'] = ROOTHTML . 'img/modules/' . $modules[$i]['NAME'] . '.png';
                } else {
                    $modules[$i]['ICON_SM'] = ROOTHTML . 'img/modules/default.png';
                }
                if ($modules[$i]['NAME']=='devices') {
                    $devices = SQLSelect("SELECT devices.LOCATION_ID, locations.TITLE, COUNT(devices.ID) as TOTAL FROM devices LEFT JOIN locations ON devices.LOCATION_ID=locations.ID WHERE locations.ID>0 GROUP BY devices.LOCATION_ID ORDER BY locations.TITLE");
                    if (is_array($devices)) {
                        $links=array();
                        $links[]=array('TITLE'=>LANG_ALL,'LINK'=>ROOTHTML.'admin.php?action='.$modules[$i]['NAME']);
                        foreach($devices as $device) {
                            $links[]=array('TITLE'=>processTitle($device['TITLE']).' ('.$device['TOTAL'].')','LINK'=>ROOTHTML.'admin.php?action='.$modules[$i]['NAME'].'&location_id='.$device['LOCATION_ID']);
                        }
                        $modules[$i]['LINKS']=$links;
                    }
                }
            }
            $modules[$last_allow]['LAST_IN_CATEGORY'] = 1;
            $out["SUB_MODULES"] = $modules;
        }

        if (is_dir(DIR_MODULES . 'app_tdwiki')) {
            $out['APP_TDWIKI_INSTALLED'] = 1;
        }
        if (is_dir(DIR_MODULES . 'optimizer')) {
            $out['OPTIMIZER_INSTALLED'] = 1;
        }

        $out["ACTION"] = $this->action;
        if (!$out['TITLE']) {
            $out['TITLE'] = LANG_CONTROL_PANEL;
        }
        $this->data = $out;
        $p = new parser(DIR_TEMPLATES . $this->name . ".html", $this->data, $this);
        return $p->result;

    }

// --------------------------------------------------------------------
}
