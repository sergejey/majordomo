<?php
/**
* RSS Channels 
*
* Rss_channels
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2 (wizard, 13:09:56 [Sep 22, 2010])
*/
//
//
class rss_channels extends module {
/**
* rss_channels
*
* Module class constructor
*
* @access private
*/
function rss_channels() {
  $this->name="rss_channels";
  $this->title="<#LANG_MODULE_RSS_CHANNELS#>";
  $this->module_category="<#LANG_SECTION_SETTINGS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams() {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
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
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $data_source;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($data_source)) {
   $this->data_source=$data_source;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='rss_channels' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_rss_channels') {
   $this->search_rss_channels($out);
  }
  if ($this->view_mode=='edit_rss_channels') {
   $this->edit_rss_channels($out, $this->id);
  }
  if ($this->view_mode=='delete_rss_channels') {
   $this->delete_rss_channels($this->id);
   $this->redirect("?data_source=rss_channels");
  }

  if ($this->view_mode=='update_rss_channels') {
   $this->updateChannel($this->id);
   $this->redirect("?data_source=rss_channels");
  }

 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='rss_items') {
  if ($this->view_mode=='' || $this->view_mode=='search_rss_items') {
   $this->search_rss_items($out);
  }

  if ($this->view_mode=='delete_rss_items') {
   SQLExec("DELETE FROM rss_items WHERE ID='".(int)$this->id."'");
   $this->redirect("?data_source=rss_items");
  }

  if ($this->view_mode=='clear_rss_items') {
   SQLExec("DELETE FROM rss_items WHERE 1");
   $this->redirect("?data_source=rss_items");
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
function usual(&$out) {
 $this->admin($out);
}
/**
* rss_channels search
*
* @access public
*/
 function search_rss_channels(&$out) {
  require(DIR_MODULES.$this->name.'/rss_channels_search.inc.php');
 }
/**
* rss_channels edit/add
*
* @access public
*/
 function edit_rss_channels(&$out, $id) {
  require(DIR_MODULES.$this->name.'/rss_channels_edit.inc.php');
 }
/**
* rss_channels delete record
*
* @access public
*/
 function delete_rss_channels($id) {
  $rec=SQLSelectOne("SELECT * FROM rss_channels WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM rss_channels WHERE ID='".$rec['ID']."'");
 }
/**
* rss_items search
*
* @access public
*/
 function search_rss_items(&$out) {
  require(DIR_MODULES.$this->name.'/rss_items_search.inc.php');
 }

/**
* Title
*
* Description
*
* @access public
*/
 function updateChannel($id) {
  $ch=SQLSelectOne("SELECT * FROM rss_channels WHERE ID='".(int)$id."'");

  $ch['LAST_UPDATE']=date('Y-m-d H:i:s');
  $ch['NEXT_UPDATE']=date('Y-m-d H:i:s', time()+$ch['UPDATE_EVERY']*60);
  SQLUpdate('rss_channels', $ch);

  /*
  $cch =curl_init();
  curl_setopt($cch, CURLOPT_URL, $ch['URL']);
  curl_setopt($cch, CURLOPT_HTTPHEADER, array("User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3"));
  curl_setopt($cch, CURLOPT_RETURNTRANSFER, true);
  $rssdata = curl_exec($cch);
  curl_close($cch);
  */
  $rssdata = getURL($ch['URL'], 0);
  $data = simplexml_load_string($rssdata);

                                        if ($data)
                                        {
                                                if (is_object($data->channel) && ! empty($data->channel))
                                                {
                                                        foreach ($data->channel->item as $item)
                                                        {
                                                        $rec=array();
                                                        $rec['CHANNEL_ID']=$ch['ID'];

                                                                $parsedFull = 0;
                                                                if ($item->pubDate)
                                                                {
                                                                        $rec['ADDED'] = date('Y-m-d H:i:s', strtotime((string)$item->pubDate));
                                                                }
                                                                else 
                                                                {
                                                                        $rec['ADDED'] = date('Y-m-d H:i:s');
                                                                }
                                                                $rec['TITLE'] = $this->convertObjDataToStr($item->title);
                                                                $rec['BODY'] = $this->convertObjDataToStr($item->description);
                                                                $rec['URL'] = $this->convertObjDataToStr($item->link);
                                                                $rec['GUID']  = $rec['URL'];
                                                                $timestamp = strtotime($rec['ADDED']);
                                                                //print_r($rec);
                                                                //exit;
                                                                $tmp=SQLSelectOne("SELECT ID FROM rss_items WHERE GUID='".DBSafe($rec['GUID'])."'");
                                                                if (!$tmp['ID']) {
                                                                 $rec['ID']=SQLInsert('rss_items', $rec);
                                                                 if ($ch['SCRIPT_ID']) {
                                                                  $params=$rec;
                                                                  $params['CHANNEL_TITLE']=$ch['TITLE'];
                                                                  $params['TM']=$timestamp;
                                                                  $params['PASSED']=time()-$timestamp;
                                                                  runScript($ch['SCRIPT_ID'], $params);
                                                                 }
                                                                }
                                                         }


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
        function convertObjDataToStr ($data)
        {
                $data = (string)$data;
                if ($data)
                {
                        return (mb_detect_encoding($data) == 'cp1251') ? iconv('cp1251', 'UTF-8', $data) : $data;
                }
                else
                {
                        return '';
                }
        }


/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($parent_name="") {
  parent::install($parent_name);
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS rss_channels');
  SQLExec('DROP TABLE IF EXISTS rss_items');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
rss_channels - RSS Channels
rss_items - RSS Items
*/
  $data = <<<EOD

 rss_channels: ID int(10) unsigned NOT NULL auto_increment
 rss_channels: TITLE varchar(255) NOT NULL DEFAULT ''
 rss_channels: URL char(255) NOT NULL DEFAULT ''
 rss_channels: NEXT_UPDATE datetime
 rss_channels: LAST_UPDATE datetime
 rss_channels: UPDATE_EVERY int(10) NOT NULL DEFAULT '0'
 rss_channels: SCRIPT_ID int(10) NOT NULL DEFAULT '0'

 rss_items: ID int(10) unsigned NOT NULL auto_increment
 rss_items: CHANNEL_ID int(10) unsigned NOT NULL DEFAULT '0'
 rss_items: TITLE varchar(255) NOT NULL DEFAULT ''
 rss_items: BODY text
 rss_items: URL char(255) NOT NULL DEFAULT ''
 rss_items: GUID varchar(255) NOT NULL DEFAULT ''
 rss_items: ADDED varchar(255) NOT NULL DEFAULT ''

EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgU2VwIDIyLCAyMDEwIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>