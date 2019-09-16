<?php
/**
 * Dashboard
 *
 * Dashboard
 *
 * @package MajorDoMo
 * @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
 * @version 0.2 (wizard, 12:02:17 [Feb 23, 2009])
 */
//
//
class dashboard extends module
{
    /**
     * dashboard
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "dashboard";
        $this->title = "Dashboard";
        $this->module_category = "CMS";
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
        $data = array();
        if (IsSet($this->id)) {
            $data["id"] = $this->id;
        }
        if (IsSet($this->view_mode)) {
            $data["view_mode"] = $this->view_mode;
        }
        if (IsSet($this->edit_mode)) {
            $data["edit_mode"] = $this->edit_mode;
        }
        if (IsSet($this->tab)) {
            $data["tab"] = $this->tab;
        }
        return parent::saveParams($data);
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
        $this->getConfig();
        if ($this->view_mode == 'settings') {
            if ($this->mode == 'update') {
                global $cpanel_stats;
                global $cpanel_username;
                global $cpanel_password;
                global $cpanel_domain;
                global $cpanel_url;
                $this->config['CPANEL_STATS'] = (int)$cpanel_stats;
                $this->config['CPANEL_USERNAME'] = $cpanel_username;
                $this->config['CPANEL_PASSWORD'] = $cpanel_password;
                $this->config['CPANEL_DOMAIN'] = $cpanel_domain;
                $this->config['CPANEL_URL'] = $cpanel_url;
                $this->saveConfig();
                $out['OK'] = 1;
            }

        } else {

            if ($this->config['CPANEL_STATS']) {
                $domain = $this->config['CPANEL_URL'];
                $domain = preg_replace('/http:\/\//', '', $domain);
                $domain = preg_replace('/[\/:].+/', '', $domain);
                $url = 'http://' . $this->config['CPANEL_USERNAME'] . ':' . $this->config['CPANEL_PASSWORD'] . '@' . $domain . ':2082/awstats.pl?config=' . $this->config['CPANEL_DOMAIN'] . '&ssl=&lang=en&framename=mainright';
                $stats = file_get_contents($url);
                //$stats = file_get_contents("cached_stats.html");
                preg_match("/Day<\/td>.+?Number of visits<\/td>.+?Pages<\/td>.+?Hits<\/td>.+?Bandwidth<\/td>.+?(<tr.+?)<\/table>/is", $stats, $matches);
                $stats = $matches[1];
                preg_match_all("/<tr.+?<td>(.*?)<\/td><td>(.*?)<\/td><td>(.*?)<\/td><td>(.*?)<\/td><td>(.*?)<\/td><\/tr>/is", $stats, $matches, PREG_SET_ORDER);
                $out['MONTH_STATS'] = array();
                foreach ($matches as $day)
                    $out['MONTH_STATS'][] = array('DAY' => $day[1], 'VISITS' => $day[2], 'PAGES' => $day[3], 'HITS' => $day[4], 'BANDWIDTH' => $day[5]);
            }

        }

        if (is_array($this->config)) {
            foreach ($this->config as $k => $v) {
                $out[$k] = htmlspecialchars($v);
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
     * Install
     *
     * Module installation routine
     *
     * @access private
     */
    function install($parent_name = "")
    {
        parent::install($parent_name);
    }
// --------------------------------------------------------------------
}

/*
*
* TW9kdWxlIGNyZWF0ZWQgRmViIDIzLCAyMDA5IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>