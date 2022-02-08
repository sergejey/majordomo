<?php
/**
 * Market
 *
 * Market
 *
 * @package project
 * @author Serge J. <jey@tut.by>
 * @copyright http://www.atmatic.eu/ (c)
 * @version 0.1 (wizard, 14:01:08 [Jan 11, 2014])
 */
//
//
class market extends module
{
    /**
     * market
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "market";
        $this->title = "<#LANG_MODULE_MARKET#>";
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
     * Title
     *
     * Description
     *
     * @access public
     */
    function checkPlugins(&$out)
    {

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

        global $name;

        global $mode;
        if (!$this->mode && $mode) {
            $this->mode = $mode;
        }
		
        $this->can_be_updated = array();
        $this->can_be_updated_new = array();
        $this->have_updates = array();


        global $err_msg;
        if ($err_msg) {
            $out['ERR_MSG'] = $err_msg;
        }
        global $ok_msg;
        if ($ok_msg) {
            $out['OK_MSG'] = $ok_msg;
        }

        if (is_dir(ROOT . 'cms/saverestore/temp')) {
            $out['CLEAR_FIRST'] = 1;
        }

        if ($this->mode == 'upload') {
            global $file;
            global $file_name;
            if (is_file($file) && $file != '') {
                //echo "Moving $file to ".ROOT.'cms/saverestore/'.$file_name;exit;
                $file_name = str_replace('.tar.gz', '.tgz', $file_name);
                copy($file, ROOT . 'cms/saverestore/' . $file_name);
                $this->redirect("?mode=iframe&mode2=uploaded&name=" . urlencode($file_name));
            } else {
                $this->redirect("?");
            }
        }

        if ($this->mode == 'iframe') {
            global $mode2;
            global $name;
            global $names;
            global $value;

            if (is_array($names)) {
                $out['NAMES'] = urlencode(implode(',', $names));
            }
            $out['NAME'] = urlencode($name);

            $out['MODE2'] = $mode2;
			
			if($mode2 == 'dontupdate' && $name) {
				if(!$value) {
					$this->redirect(SERVER_URL."/panel/market.html");
				}
				$this->dontupdate($name, $value);
			}
			
            return;
        }

        if ($this->ajax && $_GET['op'] == 'didyouknow') {
            $result = $this->marketRequest('op=didyouknow', 0);
            $data = json_decode($result, true);
            if ($data['BODY']) {
                echo nl2br(htmlspecialchars($data['BODY']));
                if ($data['LINK'] != '') {
                    echo "<br/><a href='" . $data['LINK'] . "' target='_blank'>" . LANG_DETAILS . "</a>";
                }
            }
            exit;
        }
		
		if ($this->ajax && $_GET['op'] == 'readNoty' && !empty($this->id)) {
            echo $this->readnotification($this->id);
            exit;
        }

        if ($this->ajax && $_GET['op'] == 'news') {
            $result = $this->marketRequest('op=news', 15*60); //15*60
            $data = json_decode($result, true);
            //echo json_encode($data);
            if (is_array($data)) {
                $total = count($data);
				echo '<ul class="list-group">';
                for ($i = 0; $i < 7; $i++) {
					if($i%2 == 0) {
						$bgColor = 'strip';
					} else {
						$bgColor = '';
					}
					
					if(substr($data[$i]['LINK'], 0, 39) == 'https://connect.smartliving.ru/profile/') {
						$postType = '<i>Блог</i> <i class="glyphicon glyphicon-arrow-right" style="color: darkgray;font-size: 10pt;"></i>';
					} else {
						$postType = '<i>Новость</i> <i class="glyphicon glyphicon-arrow-right" style="color: darkgray;font-size: 10pt;"></i>';
					}
					
					if(time()-950400 <= $data[$i]['ADDED_TM']) {
						$actualNews = 'background-color: #dff0d8;';
						$actualNews_Label = '<span class="label label-success" style="margin-right: 10px;">New</span>';
						$bgColor = '';
					} else {
						$actualNews = '';
						$actualNews_Label = '';
					}
					
					if ($data[$i]['LINK'] != '') {
                        $linkDetail = "<a href='" . $data[$i]['LINK'] . "' target='_blank'>Читать полностью...</a>";
                    }  else {
						$linkDetail = '';
					}
					
					$addLinks = preg_replace('/(https?:\/\/[\w\d\-\/\.\?&=#]+)/', '<a href="$1" target=_blank>$1</a>', $data[$i]['BODY']);
					if($addLinks) {
						$body = $addLinks;
					} else {
						$body = htmlspecialchars($data[$i]['BODY']);
					}
					
					echo '<li class="list-group-item '.$bgColor.'" style="margin-bottom: 5px;'.$actualNews.'">';
					echo '<span class="badge">'.date('d.m.Y H:i:s', $data[$i]['ADDED_TM']).'</span>';
					echo '<div onclick="$(\'#news_title_'.$i.'\').toggle(\'slow\');" style="cursor:pointer;">'.$actualNews_Label.$postType.' '.htmlspecialchars($data[$i]['TITLE']).'</div>';			
					echo '<div class="fullTextNewsClass" id="news_title_'.$i.'" style="display: none;margin-top: 10px;padding-top: 10px;border-top: 1px solid lightgray;"><blockquote style="border-left: 5px solid #4d96d3;">'.$body.' '.$linkDetail.'</blockquote></div>';
					echo '</li>';
					
					
					//echo '<a href="javascript://" onclick="$(\'#news_title_'.$i.'\').toggle(\'slow\');" class="list-group-item" style="padding-top: 10px;padding-bottom: 5px;">';
					//echo '<h5 id="news_head_'.$i.'" class="list-group-item-heading">'.(htmlspecialchars($data[$i]['TITLE'])).'</h5>';
					//$body = nl2br(htmlspecialchars($data[$i]['BODY']));
                    //$body = str_replace('&amp;', '&', $body);
                    //$body = preg_replace('/(https?:\/\/[\w\d\-\/\.\?&=#]+)/', '<a href="$1" target=_blank>$1</a>', $body);
					
					//echo '<p id="news_title_'.$i.'" style="display: none;" class="list-group-item-text">'.(htmlspecialchars($data[$i]['BODY'])).'</p>';
					//echo '</a>';
					
					
					/* if ($data[$i]['LINK'] != '') {
                        echo "<br/><a href='" . $data[$i]['LINK'] . "' target='_blank'>" . LANG_DETAILS . "</a>";
                    } */
                }
				echo '</ul>';
            }
            exit;
        }

        if ($_GET['op']=='') {
            $result = $this->marketRequest('op=categories',120);
            $data = json_decode($result,true);
            if (SETTINGS_SITE_LANGUAGE=='ru') {
                $title_field='CATEGORY_RU';
            } else {
                $title_field='CATEGORY_EN';
            }
            if (is_array($data[0])) {
                foreach($data as $item) {
                    if (defined('LANG_MARKET_CATEGORY_'.strtoupper($item['CATEGORY_SYSTEM_NAME']))) {
                        $category_title=constant('LANG_MARKET_CATEGORY_'.strtoupper($item['CATEGORY_SYSTEM_NAME']));
                    } else {
                        $category_title=$item[$title_field];
                    }
                    $out['CATEGORIES'][]=array('ID'=>$item['ID'],'TITLE'=>$category_title);
                }
            } else {
                $out['CATEGORIES']=array();
            }
            array_unshift($out['CATEGORIES'],array('ID'=>'owned','TITLE'=>LANG_MARKET_CATEGORY_OWNED));
            array_unshift($out['CATEGORIES'],array('ID'=>'updates','TITLE'=>LANG_MARKET_CATEGORY_HAVE_UPDATES));
            array_unshift($out['CATEGORIES'],array('ID'=>'installed','TITLE'=>LANG_MARKET_CATEGORY_INSTALLED));
            $out['CATEGORIES'][]=array('ID'=>'custom','TITLE'=>'Custom');
            return;
        }

        if (isset($this->category_id)) {
            $category_id=$this->category_id;
        } else {
            $category_id=gr('category_id');
        }
            $search=gr('search');

            $plugins=array();
            $params='';
            $missing=array();

        $data= new stdClass();

            if ($category_id=='owned') {
                $params='own=1';
            } elseif ($category_id == 'all') {
                $params='all=1';
            } elseif ($category_id == 'custom') {
                $data->PLUGINS=array();
                $added_plugins = SQLSelect("SELECT MODULE_NAME FROM plugins");
                $modules_list=array_map('current',$added_plugins);
                $seen=array();
                $params='';
                foreach($modules_list as $module) {
                    if ($module=='control_modules') continue;
                    if ($module=='control_access') continue;
                    if (!$seen[$module]) {
                        $params.='&c[]='.urlencode($module);
                    }
                    $seen[$module]=1;
                }
            } elseif ($search) {
                $params='search='.urlencode($search);
            } elseif (is_numeric($category_id)) {
                $params='category_id='.$category_id;
            } else {
                //installed
                $modules_in_db=SQLSelect("SELECT NAME FROM project_modules");
                foreach($modules_in_db as $module_in_db) {
                    if (!is_dir(DIR_MODULES.$module_in_db['NAME'])) {
                        $missing[$module_in_db['NAME']]=1;
                    }
                }
                $modules_list=array_map('current',$modules_in_db);
                $seen=array();
                $params='';
                foreach($modules_list as $module) {
                    if ($module=='control_modules') continue;
                    if ($module=='control_access') continue;
                    if (!$seen[$module]) {
                        $params.='&m[]='.urlencode($module);
                    }
                    $seen[$module]=1;
                }
                //dprint($modules_in_db);
            }

            if ($params) {
                $result = $this->marketRequest($params);
                $data = json_decode($result);
            }

                if (!$data->PLUGINS) {
                    $out['ERR'] = 1;
                } else {
                    $this->can_be_updated=array();
                    $this->can_be_updated_new=array();
                    $total = count($data->PLUGINS);
                    for ($i = 0; $i < $total; $i++) {
                        $rec = (array)$data->PLUGINS[$i];
                        $plugin_rec = SQLSelectOne("SELECT * FROM plugins WHERE MODULE_NAME LIKE '" . DBSafe($rec['MODULE_NAME']) . "'");
                        if (is_dir(ROOT . 'modules/' . $rec['MODULE_NAME']) || $plugin_rec['ID']) {
                            $rec['EXISTS'] = 1;
                            if ($plugin_rec['ID']) {
                                $rec['INSTALLED_VERSION'] = $plugin_rec['CURRENT_VERSION'];
                            }
                            $ignore_rec = SQLSelectOne("SELECT * FROM ignore_updates WHERE `NAME` LIKE '" . DBSafe($rec['MODULE_NAME']) . "'");
                            if ($ignore_rec['ID']) {
                                $rec['IGNORE_UPDATE'] = 1;
                            }
                        }

                        //if ($rec['MODULE_NAME']==$name) {
                        //unset($rec['LATEST_VERSION']);
                        if (!isset($rec['LATEST_VERSION_URL'])) {
                            if (preg_match('/github\.com/is', $rec['REPOSITORY_URL']) && ($rec['EXISTS'] || $rec['MODULE_NAME'] == $name)) {
                                $git_url = str_replace('archive/master.tar.gz', 'commits/master.atom', $rec['REPOSITORY_URL']);
                                $github_feed = getURL($git_url, 5 * 60);
                                @$tmp = GetXMLTree($github_feed);
                                @$items_data = XMLTreeToArray($tmp);
                                @$items = $items_data['feed']['entry'];
                                if (is_array($items)) {
                                    $latest_item = $items[0];
                                    //print_r($latest_item);exit;
                                    $updated = strtotime($latest_item['updated']['textvalue']);
                                    $rec['LATEST_VERSION'] = date('Y-m-d H:i:s', $updated);
                                    $rec['LATEST_VERSION_COMMENT'] = $latest_item['title']['textvalue'];
                                    $rec['LATEST_VERSION_URL'] = $latest_item['link']['href'];
                                }
                            }
                        }
                        if ($rec['MODULE_NAME'] == $name) {
                            //$this->url=$rec['REPOSITORY_URL'];
                            $this->url = 'https://connect.smartliving.ru/market/?op=download&name=' . urlencode($rec['MODULE_NAME']) . "&serial=" . urlencode(gg('Serial'));
                            $this->version = $rec['LATEST_VERSION'];
                        }
                        if (($rec['EXISTS'] && !$rec['IGNORE_UPDATE']) || $missing[$rec['MODULE_NAME']]) {
                            $this->can_be_updated[] = array('NAME' => $rec['MODULE_NAME'], 'URL' => $rec['REPOSITORY_URL'], 'VERSION' => $rec['LATEST_VERSION']);
                        }
						
						//var_dump($rec["LATEST_VERSION"]);
                        /*
                        if (in_array($rec['MODULE_NAME'], $names)) {
                            $this->selected_plugins[] = array('NAME' => $rec['MODULE_NAME'], 'URL' => $rec['REPOSITORY_URL'], 'VERSION' => $rec['LATEST_VERSION']);
                        }
                        */
                        if ($rec['EXISTS'] && $rec['INSTALLED_VERSION'] != $rec['LATEST_VERSION'] && $rec['LATEST_VERSION']!='') {
                            $this->have_updates[] = $rec['MODULE_NAME'];
                            $this->can_be_updated_new[] = array('NAME' => $rec['MODULE_NAME'], 'URL' => $rec['REPOSITORY_URL'], 'VERSION' => $rec['LATEST_VERSION']);
                        } elseif ($category_id=='updates') {
                            continue;
                        }
						
                        $plugins[] = $rec;
                    }
					
					if ($this->ajax && $_GET['op'] == 'check_updates') {
						$total = count($this->have_updates);
						if ($total > 0) {
							echo json_encode(array('status' => '1', 'howUpdate' => $total));
						} else {
							echo json_encode(array('status' => '0'));
						}
						exit;
					}
                }




            if (count($plugins)>0) {
                usort($plugins, function($a,$b) {
                    return strcmp($a['TITLE'],$b['TITLE']);
                });
                $out['PLUGINS']=$plugins;
            }

            if ($this->ajax) {
                $p = new parser(DIR_TEMPLATES . $this->name . "/list.html", $out, $this);
                echo $p->result;
                exit;

            }

        return;

        // $data_url='https://connect.smartliving.ru/market/?lang='.SETTINGS_SITE_LANGUAGE."&serial=".urlencode($serial)."&locale=".urlencode($locale)."&os=".urlencode($os);
        $result = $this->marketRequest();
        $data = json_decode($result);

        if (!$data->PLUGINS) {
            $out['ERR'] = 1;
            return;
        }
        $total = count($data->PLUGINS);

        $old_category = '';
        $can_be_updated = array();
        $selected_plugins = array();
        global $names;

        if (!is_array($names)) {
            $names = array();
        }
        $cat = array();
        $cat_id = -1;


        $modules_in_db=SQLSelect("SELECT * FROM project_modules");
        $missing=array();
        foreach($modules_in_db as $module_in_db) {
            if (!is_dir(DIR_MODULES.$module_in_db['NAME'])) {
                $missing[$module_in_db['NAME']]=1;
            }
        }

        $added_plugins = SQLSelect("SELECT MODULE_NAME FROM plugins");
        $market_plugins = array();
        for ($i = 0; $i < $total; $i++) {
            $rec = (array)$data->PLUGINS[$i];
            $market_plugins[] = $rec['MODULE_NAME'];
        }
        foreach ($added_plugins as $plugin) {
            if (!in_array($plugin['MODULE_NAME'], $market_plugins)) {
                $rec = array();
                $rec['MODULE_NAME'] = $plugin['MODULE_NAME'];
                $rec['TITLE'] = $rec['MODULE_NAME'];
                $rec['EXISTS'] = 1;
                $rec['CATEGORY'] = 'Custom';
                $data->PLUGINS[] = $rec;
            }
        }

        $total = count($data->PLUGINS);
        for ($i = 0; $i < $total; $i++) {
            $rec = (array)$data->PLUGINS[$i];
            $plugin_rec = SQLSelectOne("SELECT * FROM plugins WHERE MODULE_NAME LIKE '" . DBSafe($rec['MODULE_NAME']) . "'");
            if (is_dir(ROOT . 'modules/' . $rec['MODULE_NAME']) || $plugin_rec['ID']) {
                $rec['EXISTS'] = 1;
                if ($plugin_rec['ID']) {
                    $rec['INSTALLED_VERSION'] = $plugin_rec['CURRENT_VERSION'];
                }
                $ignore_rec = SQLSelectOne("SELECT * FROM ignore_updates WHERE `NAME` LIKE '" . DBSafe($rec['MODULE_NAME']) . "'");
                if ($ignore_rec['ID']) {
                    $rec['IGNORE_UPDATE'] = 1;
                }
            }

            if ($rec['CATEGORY'] != $old_category) {
                $cat[] = array();
                ++$cat_id;
                $cat[$cat_id]['NAME'] = $rec['CATEGORY'];
                $cat[$cat_id]['CATEGORY_ID'] = $rec['CATEGORY_ID'];
                $old_category = $rec['CATEGORY'];
            }

            //if ($rec['MODULE_NAME']==$name) {
            //unset($rec['LATEST_VERSION']);

            if (!isset($rec['LATEST_VERSION_URL'])) {
                if (preg_match('/github\.com/is', $rec['REPOSITORY_URL']) && ($rec['EXISTS'] || $rec['MODULE_NAME'] == $name)) {
                    $git_url = str_replace('archive/master.tar.gz', 'commits/master.atom', $rec['REPOSITORY_URL']);
                    $github_feed = getURL($git_url, 5 * 60);
                    @$tmp = GetXMLTree($github_feed);
                    @$items_data = XMLTreeToArray($tmp);
                    @$items = $items_data['feed']['entry'];
                    if (is_array($items)) {
                        $latest_item = $items[0];
                        //print_r($latest_item);exit;
                        $updated = strtotime($latest_item['updated']['textvalue']);
                        $rec['LATEST_VERSION'] = date('Y-m-d H:i:s', $updated);
                        $rec['LATEST_VERSION_COMMENT'] = $latest_item['title']['textvalue'];
                        $rec['LATEST_VERSION_URL'] = $latest_item['link']['href'];
                    }
                }
            }


            if ($rec['MODULE_NAME'] == $name) {
                //$this->url=$rec['REPOSITORY_URL'];
                $this->url = 'https://connect.smartliving.ru/market/?op=download&name=' . urlencode($rec['MODULE_NAME']) . "&serial=" . urlencode(gg('Serial'));
                $this->version = $rec['LATEST_VERSION'];
            }
            if (($rec['EXISTS'] && !$rec['IGNORE_UPDATE']) || $missing[$rec['MODULE_NAME']]) {
                $this->can_be_updated[] = array('NAME' => $rec['MODULE_NAME'], 'URL' => $rec['REPOSITORY_URL'], 'VERSION' => $rec['LATEST_VERSION']);
            }
            if (in_array($rec['MODULE_NAME'], $names)) {
                $this->selected_plugins[] = array('NAME' => $rec['MODULE_NAME'], 'URL' => $rec['REPOSITORY_URL'], 'VERSION' => $rec['LATEST_VERSION']);
            }
            if ($rec['EXISTS'] && $rec['INSTALLED_VERSION'] != $rec['LATEST_VERSION']) {
                $cat[$cat_id]['NEW_VERSION'] = 1;
                $this->can_be_updated_new[] = array('NAME' => $rec['MODULE_NAME'], 'URL' => $rec['REPOSITORY_URL'], 'VERSION' => $rec['LATEST_VERSION']);
                $this->have_updates[] = $rec['MODULE_NAME'];
            }
            $cat[$cat_id]['PLUGINS'][] = $rec;
        }
        $out['CATEGORY'] = $cat;

        


        if ($this->mode == 'install_multiple') {
            $this->updateAll($this->selected_plugins);
        }


        if ($this->mode == 'update_all') {
            $this->updateAll($this->can_be_updated);
        }
        
        if ($this->mode == 'update_new') {
            $this->updateAll($this->can_be_updated_new);
        }

        if ($this->mode == 'install' && $this->url) {
            $this->getLatest($out, $this->url, $name, $this->version);
        }

        if ($this->mode == 'upload') {
            $this->upload($out);
        }

        if ($this->mode == 'uninstall' && $name) {
            $this->uninstallPlugin($name);
        }

        if ($this->mode == 'clear') {
            $this->removeTree(ROOT . 'cms/saverestore/temp');
            @SaveFile(ROOT . 'reboot', 'updated');
            $this->redirect("?err_msg=" . urlencode($err_msg) . "&ok_msg=" . urlencode($ok_msg));
        }

    }

    function marketRequest($details = '', $cache_timeout = 0)
    {
        $serial = getSystemSerial();
        if (IsWindowsOS()) {
            $os = 'Windows';
        } else {
            $os = trim(exec("uname -a"));
            if (!$os) {
                $os = trim(exec("sudo uname -a"));
                if (!$os) {
                    $os = 'Linux';
                }
            }
        }
        $locale = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $data_url = 'https://connect.smartliving.ru/market/?lang=' . SETTINGS_SITE_LANGUAGE . "&serial=" . urlencode($serial) . "&locale=" . urlencode($locale) . "&os=" . urlencode($os) . "&" . $details;

        $username='';
        $password='';
        include_once(DIR_MODULES . 'connect/connect.class.php');
        $connect = new connect();
        $connect->getConfig();
        $connect_username = strtolower($connect->config['CONNECT_USERNAME']);
        $connect_password = $connect->config['CONNECT_PASSWORD'];
        if ($connect_username!='' && $connect_password!='') {
            $username=$connect_username;
            $password=$connect_password;
        }
        $result = getURL($data_url, $cache_timeout,$username,$password);
        return $result;

    }
    
    
    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function updateAll($can_be_updated, $frame = 0)
    {

        //$this->redirect("?mode=install&name=".$can_be_updated[0]."&list=".urlencode(implode(',', $can_be_updated)));
        set_time_limit(0);
        if (!is_dir(ROOT . 'cms/saverestore')) {
            @umask(0);
            @mkdir(ROOT . 'cms/saverestore', 0777);
        }

        umask(0);
        @mkdir(ROOT . 'cms/saverestore/temp', 0777);

        if (is_array($can_be_updated)) {
            foreach ($can_be_updated as $k => $v) {
                //$this->getLatest($out, $v['URL'], $v['NAME'], $v['VERSION']);
                $name = $v['NAME'];
                $version = $v['VERSION'];
                $url = $v['URL'];

                $filename = ROOT . 'cms/saverestore/' . $name . '.tgz';
                @unlink(ROOT . 'cms/saverestore/' . $name . '.tgz');
                @unlink(ROOT . 'cms/saverestore/' . $name . '.tar');
                $f = fopen($filename, 'wb');
                if ($f == FALSE) {
                    $this->redirect("?err_msg=" . urlencode("Cannot open " . $filename . " for writing"));
                }

                if ($frame) {
                    $this->echonow("Downloading '$url' ... ");
                }

                DebMes("Downloading plugin $name ($version) from $url");
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_TIMEOUT, 600);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                curl_setopt($ch, CURLOPT_FILE, $f);
                $incoming = curl_exec($ch);
                curl_close($ch);
                @fclose($f);

                if (file_exists($filename)) {

                    if ($frame) {
                        $this->echonow("OK<br/>", 'green');
                    }


                    $file = basename($filename);
                    DebMes("Installing/updating plugin $name ($version)");

                    @chdir(ROOT . 'cms/saverestore/temp');

                    if ($frame) {
                        $this->echonow("Unpacking '$file' ..");
                    }


                    if (IsWindowsOS()) {
                        //DebMes("Running ".DOC_ROOT.'/gunzip ../'.$file);
                        exec(DOC_ROOT . '/gunzip ../' . $file, $output, $res);
                        //DebMes("Running ".DOC_ROOT.'/tar xvf ../'.str_replace('.tgz', '.tar', $file));
                        exec(DOC_ROOT . '/tar xvf ../' . str_replace('.tgz', '.tar', $file), $output, $res);
                    } else {
                        exec('tar xzvf ../' . $file, $output, $res);
                    }

                    $x = 0;
                    $latest_dir = '';
                    $latest_file = '';
                    $dir = opendir('./');
                    while (($filec = readdir($dir)) !== false) {
                        if ($filec == '.' || $filec == '..') {
                            continue;
                        }
                        if (is_Dir($filec)) {
                            $latest_dir = $filec;
                        } elseif (is_File($filec)) {
                            $latest_file = $filec;
                        }
                        $x++;
                    }

                    if ($x == 1 && $latest_dir) {
                        $folder = '/' . $latest_dir;
                    }

                    chdir('../../');

                    DebMes("Latest folder: $latest_dir");

                    if ($latest_dir == '') {
                        if ($frame) {
                            $this->echonow("ERROR<br/>", 'red');
                        }
                        DebMes("Error extracting $file");
                        continue;
                    }

                    if ($frame) {
                        $this->echonow("OK<br/>", 'green');
                    }


                    // UPDATING FILES DIRECTLY
                    if ($frame) {
                        $this->echonow("Updating files ...");
                    }

                    $this->installUnpacketPlugin(ROOT . 'cms/saverestore/temp' . $folder, $name);

                    if ($frame) {
                        $this->echonow("OK<br/>", 'green');
                    }


                    $rec = SQLSelectOne("SELECT * FROM plugins WHERE MODULE_NAME LIKE '" . DBSafe($name) . "'");
                    $rec['MODULE_NAME'] = $name;
                    $rec['CURRENT_VERSION'] = $version;
                    $rec['IS_INSTALLED'] = 1;
                    $rec['LATEST_UPDATE'] = date('Y-m-d H:i:s');
                    if ($rec['ID']) {
                        SQLUpdate('plugins', $rec);
                    } else {
                        SQLInsert('plugins', $rec);
                    }

                }
            }
        }
        $this->removeTree(ROOT . 'cms/saverestore/temp', $frame);

        $source = ROOT . 'modules';
        if ($dir = @opendir($source)) {
            while (($file = readdir($dir)) !== false) {
                if (Is_Dir($source . "/" . $file) && ($file != '.') && ($file != '..')) {
                    @unlink(ROOT . "cms/modules_installed/" . $file . ".installed");
                }
            }
        }
        @unlink(ROOT . "cms/modules_installed/control_modules.installed");

        if ($frame) {
            return ("Updates Installed!");
        } else {
            $this->redirect("?ok_msg=" . urlencode("Updates Installed!"));
        }

    }

    function installUnpacketPlugin($folder, $plugin_name)
    {
        $out = array();
        if (is_dir($folder . '/import')) {
            if (is_dir($folder . '/import/scripts')) {
                include_once(DIR_MODULES . 'scripts/scripts.class.php');
                $scripts_module = new scripts();
                $files_to_import = scandir($folder . '/import/scripts');
                if (is_array($files_to_import)) {
                    foreach ($files_to_import as $file) {
                        $filename = $folder . '/import/scripts/' . $file;
                        if (is_file($filename)) {
                            $scripts_module->import($out, $filename);
                        }
                    }
                }
            }
            if (is_dir($folder . '/import/scenes')) {
                include_once(DIR_MODULES . 'scenes/scenes.class.php');
                $scenes_module = new scenes();
                $files_to_import = scandir($folder . '/import/scenes');
                if (is_array($files_to_import)) {
                    foreach ($files_to_import as $file) {
                        $filename = $folder . '/import/scenes/' . $file;
                        if (is_file($filename)) {
                            $scenes_module->import_scene($filename, $plugin_name.'_'.strtolower($file));
                        }
                    }
                }
            }
            if (is_dir($folder . '/import/classes')) {
                include_once(DIR_MODULES . 'classes/classes.class.php');
                $classes_module = new classes();
                $files_to_import = scandir($folder . '/import/classes');
                if (is_array($files_to_import)) {
                    foreach ($files_to_import as $file) {
                        $filename = $folder . '/import/classes/' . $file;
                        if (is_file($filename)) {
                            $classes_module->import_classes($filename, 1);
                        }
                    }
                }
            }
            $this->removeTree($folder . '/import');
        }
        if (file_exists($folder . '/install.php')) {
            require($folder . '/install.php');
            @unlink($folder . '/install.php');
        }
        $this->copyTree($folder, ROOT, 1); // restore all files
        $this->removeTree($folder);
    }

    /**
     * Title
     *
     * Description
     *
     * @access public
     */
    function dontupdate($name, $value) {
		SQLExec("UPDATE plugins SET CURRENT_VERSION = '".DBSafe($value)."' WHERE MODULE_NAME = '".DBSafe($name)."' LIMIT 1");
		$this->redirect(SERVER_URL."/panel/market.html");
	}
	
	function uninstallPlugin($name, $frame = 0)
    {
        if ($frame) {
            $this->echonow("Removing module '$name' from database ... ");
        }
        SQLExec("DELETE FROM plugins WHERE MODULE_NAME LIKE '" . DBSafe($name) . "'");
        if (is_dir(ROOT . 'modules/' . $name)) {
            include_once(ROOT . 'modules/' . $name . '/' . $name . '.class.php');
            SQLExec("DELETE FROM project_modules WHERE NAME LIKE '" . DBSafe($name) . "'");
            if ($frame) {
                $this->echonow(" OK<br/>", 'green');
            }
            $code = '$plugin = new ' . $name . ';$plugin->uninstall();';
            eval($code);
            $this->removeTree(ROOT . 'modules/' . $name);
            $this->removeTree(ROOT . 'templates/' . $name);
            if ($name == 'scheduler') {
                $cycle_name = ROOT . 'scripts/cycle_schedapp.php';
            } else {
                $cycle_name = ROOT . 'scripts/cycle_' . $name . '.php';
            }
            if (file_exists($cycle_name)) {
                @unlink($cycle_name);
            }
            removeMissingSubscribers();
        }
        $ok_msg = 'Uninstalled';
        if ($frame) {
            $this->echonow(" Plugin uninstalled!<br/>", 'green');
        }

        if (!$frame) {
            $this->redirect("?err_msg=" . urlencode($err_msg) . "&ok_msg=" . urlencode($ok_msg));
        } else {
            return $ok_msg;
        }
    }

    function getLatest(&$out, $url, $name, $version, $frame = 0)
    {

        set_time_limit(0);

        if (!is_dir(ROOT . 'cms/saverestore')) {
            @umask(0);
            @mkdir(ROOT . 'cms/saverestore', 0777);
        }

        $filename = ROOT . 'cms/saverestore/' . $name . '.tgz';

        @unlink(ROOT . 'cms/saverestore/' . $name . '.tgz');
        @unlink(ROOT . 'cms/saverestore/' . $name . '.tar');

        $f = fopen($filename, 'wb');
        if ($f == FALSE) {
            if ($frame) {
                $this->echonow("Cannot open " . $filename . " for writing", "red");
                return 0;
            } else {
                $this->redirect("?err_msg=" . urlencode("Cannot open " . $filename . " for writing"));
            }
        }


        if ($frame) {
            $this->echonow("Downloading '" . $url . "' ... ");
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FILE, $f);

        include_once(DIR_MODULES . 'connect/connect.class.php');
        $connect = new connect();
        $connect->getConfig();
        $connect_username = strtolower($connect->config['CONNECT_USERNAME']);
        $connect_password = $connect->config['CONNECT_PASSWORD'];
        if ($connect_username!='' && $connect_password!='') {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $connect_username . ":" . $connect_password);
        }

        $incoming = curl_exec($ch);

        curl_close($ch);
        @fclose($f);

        if (file_exists($filename)) {

            if ($frame) {
                $this->echonow("OK<br/>", 'green');
            }


            $this->removeTree(ROOT . 'cms/saverestore/temp', $frame);

            if ($frame) {
                return 1;
            } else {
                global $list;
                $this->redirect("?mode=upload&restore=" . urlencode($name . '.tgz') . "&folder=" . urlencode($name) . "&name=" . urlencode($name) . "&version=" . urlencode($version) . "&list=" . urlencode($list));
            }

        } else {
            if ($frame) {
                $this->echonow("Cannot download '" . $url . "'<br/>", "red");
                return 0;
            } else {
                $this->redirect("?err_msg=" . urlencode("Cannot download " . $url));
            }
        }
    }

    function upload(&$out, $frame = 0)
    {
        set_time_limit(0);
        global $restore;
        global $file;
        global $file_name;
        global $folder;
        global $name;
        global $version;

        if (!$folder)
            $folder = IsWindowsOS() ? '/.' : '/';
        else
            $folder = '/' . $folder;

        if ($restore != '') {
            $file = $restore;
        } elseif ($file != '') {
            copy($file, ROOT . 'cms/saverestore/' . $file_name);
            $file = $file_name;
        }

        if (!$name) {
            $name = $file_name;
            $name = str_replace('.tgz', '', $name);
            $name = str_replace('.tar.gz', '', $name);
            $name = strtolower($name);
        }

        umask(0);
        @mkdir(ROOT . 'cms/saverestore/temp', 0777);

        if ($file != '') { // && mkdir(ROOT.'cms/saverestore/temp', 0777)
            chdir(ROOT . 'cms/saverestore/temp');

            if ($frame) {
                $this->echonow("Unpacking '$file' ... ");
            }
            if (IsWindowsOS()) {
                // for windows only
                exec(DOC_ROOT . '/gunzip ../' . $file, $output, $res);
                exec(DOC_ROOT . '/tar xvf ../' . str_replace('.tgz', '.tar', $file), $output, $res);
                @unlink('../' . str_replace('.tgz', '.tar', $file));
            } else {
                exec('tar xzvf ../' . $file, $output, $res);
            }
            if ($frame) {
                $this->echonow(" OK <br/>", 'green');
            }


            $x = 0;
            $dir = opendir('./');
            while (($filec = readdir($dir)) !== false) {
                if ($filec == '.' || $filec == '..') {
                    continue;
                }
                if (is_Dir($filec)) {
                    $latest_dir = $filec;
                } elseif (is_File($filec)) {
                    $latest_file = $filec;
                }
                $x++;
            }

            if ($x == 1 && $latest_dir) {
                $folder = '/' . $latest_dir;
            }
            @unlink(ROOT . 'cms/saverestore/temp' . $folder . '/config.php');
            @unlink(ROOT . 'cms/saverestore/temp' . $folder . '/README.md');
            chdir('../../../');
            // UPDATING FILES DIRECTLY
            if ($frame) {
                $this->echonow("Updating files ... ");
            }
            $this->installUnpacketPlugin(ROOT . 'cms/saverestore/temp' . $folder, $name);
            $source = ROOT . 'modules';
            if ($dir = @opendir($source)) {
                while (($file = readdir($dir)) !== false) {
                    if (Is_Dir($source . "/" . $file) && ($file != '.') && ($file != '..')) {
                        @unlink(ROOT . "cms/modules_installed/" . $file . ".installed");
                    }
                }
            }
            @unlink(ROOT . "cms/modules_installed/control_modules.installed");

            if ($frame) {
                $this->echonow(" OK <br/>", 'green');
            }


            DebMes("Installing/updating plugin $name ($version)");

            $rec = SQLSelectOne("SELECT * FROM plugins WHERE MODULE_NAME LIKE '" . DBSafe($name) . "'");
            $rec['MODULE_NAME'] = $name;
            $rec['CURRENT_VERSION'] = $version;
            $rec['IS_INSTALLED'] = 1;
            $rec['LATEST_UPDATE'] = date('Y-m-d H:i:s');
            if ($rec['ID']) {
                SQLUpdate('plugins', $rec);
            } else {
                SQLInsert('plugins', $rec);
            }

            if ($frame) {
                $this->echonow("Plugin '$name' ($version) installed.<br/>", 'green');
                return "Plugin '$name' ($version) installed.";
            } else {
                $this->redirect("?mode=clear&ok_msg=" . urlencode("Updates Installed!"));
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
    function install($data = '')
    {
        parent::install();
    }

    /**
     * removeTree
     *
     * remove directory tree
     *
     * @access public
     */
    function removeTree($destination, $frame = 0)
    {

        $res = 1;

        if (!Is_Dir($destination)) {
            return 0; // cannot create destination path
        }

        if ($frame) {
            $this->echonow("Removing dir $destination ... ");
        }


        if ($dir = @opendir($destination)) {
            while (($file = readdir($dir)) !== false) {
                if (Is_Dir($destination . "/" . $file) && ($file != '.') && ($file != '..')) {
                    $res = $this->removeTree($destination . "/" . $file);
                } elseif (Is_File($destination . "/" . $file)) {
                    $res = @unlink($destination . "/" . $file);
                }
            }
            closedir($dir);
            $res = @rmdir($destination);
        }

        if ($frame) {
            $this->echonow("OK<br/>", "green");
        }


        return $res;
    }


    /**
     * copyTree
     *
     * Copy source directory tree to destination directory
     *
     * @access public
     */
    function copyTree($source, $destination, $over = 0, $patterns = 0)
    {


        $res = 1;

        //Remove last slash '/' in source and destination - slash was added when copy
        $source = preg_replace("#/$#", "", $source);
        $destination = preg_replace("#/$#", "", $destination);

        if (!Is_Dir($source)) {
            return 0; // cannot create destination path
        }

        if (!Is_Dir($destination)) {
            if (!mkdir($destination, 0777, true)) {
                return 0; // cannot create destination path
            }
        }


        if ($dir = @opendir($source)) {
            while (($file = readdir($dir)) !== false) {
                if (Is_Dir($source . "/" . $file) && ($file != '.') && ($file != '..')) {
                    $res = $this->copyTree($source . "/" . $file, $destination . "/" . $file, $over, $patterns);
                } elseif (Is_File($source . "/" . $file) && (!file_exists($destination . "/" . $file) || $over)) {
                    if (!is_array($patterns)) {
                        $ok_to_copy = 1;
                    } else {
                        $ok_to_copy = 0;
                        $total = count($patterns);
                        for ($i = 0; $i < $total; $i++) {
                            if (preg_match('/' . $patterns[$i] . '/is', $file)) {
                                $ok_to_copy = 1;
                            }
                        }
                    }
                    if ($ok_to_copy) {
                        @$res = copy($source . "/" . $file, $destination . "/" . $file);
                    }
                }
            }
            closedir($dir);
        }
        return $res;

    }

    function copyFile($source, $destination)
    {
        $tmp = explode('/', $destination);
        $total = count($tmp);
        if ($total > 0) {
            $d = $tmp[0];
            for ($i = 1; $i < ($total - 1); $i++) {
                $d .= '/' . $tmp[$i];
                if (!is_dir($d)) {
                    mkdir($d);
                }
            }
        }
        return copy($source, $destination);

    }

    function copyFiles($source, $destination, $over = 0, $patterns = 0)
    {

        $res = 1;

        if (!Is_Dir($source)) {
            return 0; // cannot create destination path
        }

        if (!Is_Dir($destination)) {
            if (!mkdir($destination)) {
                return 0; // cannot create destination path
            }
        }


        if ($dir = @opendir($source)) {
            while (($file = readdir($dir)) !== false) {
                if (Is_Dir($source . "/" . $file) && ($file != '.') && ($file != '..')) {
                    //$res=$this->copyTree($source."/".$file, $destination."/".$file, $over, $patterns);
                } elseif (Is_File($source . "/" . $file) && (!file_exists($destination . "/" . $file) || $over)) {
                    if (!is_array($patterns)) {
                        $ok_to_copy = 1;
                    } else {
                        $ok_to_copy = 0;
                        $total = count($patterns);
                        for ($i = 0; $i < $total; $i++) {
                            if (preg_match('/' . $patterns[$i] . '/is', $file)) {
                                $ok_to_copy = 1;
                            }
                        }
                    }
                    if ($ok_to_copy) {
                        $res = copy($source . "/" . $file, $destination . "/" . $file);
                    }
                }
            }
            closedir($dir);
        }
        return $res;
    }

    function echonow($msg, $color = '')
    {
        DebMes(strip_tags($msg),'auto_update');
        if ($color) {
            echo '<font color="' . $color . '">';
        }
        echo $msg;
        if ($color) {
            echo '</font>';
        }
        echo "<script language='javascript'>window.scrollTo(0,document.body.scrollHeight);</script>";
        echo str_repeat(' ', 16 * 1024);
        flush();
        ob_flush();
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
      SQLDropTable('plugins');
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
        plugins - Plugins
        */
        $data = <<<EOD
 plugins: ID int(10) unsigned NOT NULL auto_increment
 plugins: TITLE varchar(255) NOT NULL DEFAULT ''
 plugins: MODULE_NAME varchar(255) NOT NULL DEFAULT ''
 plugins: REPOSITORY_URL char(255) NOT NULL DEFAULT ''
 plugins: AUTHOR varchar(255) NOT NULL DEFAULT ''
 plugins: SUPPORT_URL char(255) NOT NULL DEFAULT ''
 plugins: DESCRIPTION_RU text
 plugins: DESCRIPTION_EN text
 plugins: CURRENT_VERSION varchar(255) NOT NULL DEFAULT ''
 plugins: LATEST_VERSION varchar(255) NOT NULL DEFAULT ''
 plugins: IS_INSTALLED int(3) NOT NULL DEFAULT '0'
 plugins: WHATSNEW text
 plugins: LATEST_UPDATE datetime
EOD;
        parent::dbInstall($data);
    }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgSmFuIDExLCAyMDE0IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
