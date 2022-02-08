<?php
/**
* Pinghosts 
*
* Pinghosts
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 0.2 (wizard, 00:01:48 [Jan 06, 2011])
*/
Define('PING_TYPE_OPTIONS', '0=PING (HOST)|1=WEB PAGE (URL)|2=PING (HOST:PORT)'); // options for 'HOST TYPE'
//
//
class pinghosts extends module {
/**
* pinghosts
*
* Module class constructor
*
* @access private
*/
function pinghosts() {
  $this->name="pinghosts";
  $this->title="<#LANG_MODULE_PINGHOSTS#>";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
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

  if ($this->mobile) {
   $out['MOBILE']=1;
  }

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
 if ($this->data_source=='pinghosts' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_pinghosts') {
   $this->search_pinghosts($out);
  }
  if ($this->view_mode=='edit_pinghosts') {
   $this->edit_pinghosts($out, $this->id);
  }
  if ($this->view_mode=='delete_pinghosts') {
   $this->delete_pinghosts($this->id);
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
function usual(&$out) {
 $this->admin($out);
}
/**
* pinghosts search
*
* @access public
*/
 function search_pinghosts(&$out) {
  require(DIR_MODULES.$this->name.'/pinghosts_search.inc.php');
 }
/**
* pinghosts edit/add
*
* @access public
*/
 function edit_pinghosts(&$out, $id) {
  require(DIR_MODULES.$this->name.'/pinghosts_edit.inc.php');
 }
/**
* pinghosts delete record
*
* @access public
*/
 function delete_pinghosts($id) {
  $rec=SQLSelectOne("SELECT * FROM pinghosts WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM pinghosts WHERE ID='".$rec['ID']."'");
 }

/**
* Title
*
* Description
*
* @access public
*/
 function checkAllHosts($limit=1000) {

  // ping hosts
  $pings=SQLSelect("SELECT * FROM pinghosts WHERE CHECK_NEXT<=NOW() ORDER BY CHECK_NEXT LIMIT ".$limit);
  $total=count($pings);
  for($i=0;$i<$total;$i++) {
   $host=$pings[$i];
   echo "Checking ".$host['HOSTNAME']."\n";
   $online_interval=$host['ONLINE_INTERVAL'];
   if (!$online_interval) {
    $online_interval=60;
   }
   $offline_interval=$host['OFFLINE_INTERVAL'];
   if (!$offline_interval) {
    $offline_interval=$online_interval;
   }

   if ($host['STATUS']=='1') {
    $host['CHECK_NEXT']=date('Y-m-d H:i:s', time()+$online_interval);
   } else {
    $host['CHECK_NEXT']=date('Y-m-d H:i:s', time()+$offline_interval);
   }
   SQLUpdate('pinghosts', $host);

   $online=0;
   // checking
   if ($host['TYPE']==0) {
    //ping host

    $online=ping(processTitle($host['HOSTNAME']));
   } elseif ($host['TYPE']==1) {
    //web host
    $online=getURL(processTitle($host['HOSTNAME']), 0);
    SaveFile("./cms/cached/host_".$host['ID'].'.html', $online);
    if ($host['SEARCH_WORD']!='' && !is_integer(strpos($online, $host['SEARCH_WORD']))) {
     $online=0;
    }
    if ($online) {
     $online=1;
    }
   } elseif ($host['TYPE']==2) {
     $hostport = explode(":",$host['HOSTNAME']);
	 $connection = @fsockopen($hostport[0],$hostport[1],$errno,$errstr,1);
     if (is_resource($connection)) {
         $online=1;
         fclose($connection);
     } else {
         $online=0;
     }
   }

   if ($online) {
    $new_status=1;
   } else {
    $new_status=2;
   }

   $old_status=$host['STATUS'];

   if ($host['COUNTER_REQUIRED'] and $new_status!=1) {
    $old_status_expected=$host['STATUS_EXPECTED'];
    $host['STATUS_EXPECTED']=$new_status;
    if ($old_status_expected!=$host['STATUS_EXPECTED']) {
     $host['COUNTER_CURRENT']=0;
     $host['LOG']=date('Y-m-d H:i:s').' tries counter reset (status: '.str_replace('2', 'offline', $host['STATUS_EXPECTED']).')'."\n".$host['LOG'];
    } elseif ($host['STATUS']!=$host['STATUS_EXPECTED']) {
     $host['COUNTER_CURRENT']++;
     $host['LOG']=date('Y-m-d H:i:s').' tries counter increased to '.$host['COUNTER_CURRENT'].' (status: '.str_replace('2', 'offline', $host['STATUS_EXPECTED']).')'."\n".$host['LOG'];
    }
    if ($host['COUNTER_CURRENT']>=$host['COUNTER_REQUIRED']) {
     $host['STATUS']=$host['STATUS_EXPECTED'];
     $host['COUNTER_CURRENT']=0;
    } elseif ($old_status!=$new_status) {
     $online_interval=min($online_interval, 20);
    }
   } else {
    if ($host['COUNTER_REQUIRED'] and $old_status==1 and $host['STATUS_EXPECTED']==2 and $new_status==1) {
     $host['LOG']=date('Y-m-d H:i:s').' Host is still online'."\n".$host['LOG'];
    }
    $host['STATUS']=$new_status;
    $host['STATUS_EXPECTED']=$host['STATUS'];
    $host['COUNTER_CURRENT']=0;
   }

   $host['CHECK_LATEST']=date('Y-m-d H:i:s');

   if ($host['STATUS']=='1') {
    $host['CHECK_NEXT']=date('Y-m-d H:i:s', time()+$online_interval);
   } else {
    $host['CHECK_NEXT']=date('Y-m-d H:i:s', time()+$offline_interval);
   }

   if ($old_status!=$host['STATUS']) {
    if ($host['LINKED_OBJECT']!='' && $host['LINKED_PROPERTY']!='') {
     setGlobal($host['LINKED_OBJECT'].'.'.$host['LINKED_PROPERTY'], $host['STATUS']);
    }
    if ($host['STATUS']==2) {
     $host['LOG']=date('Y-m-d H:i:s').' Host is offline'."\n".$host['LOG'];
    } elseif ($host['STATUS']==1) {
     $host['LOG']=date('Y-m-d H:i:s').' Host is online'."\n".$host['LOG'];
    }
   }

   $tmp=explode("\n", $host['LOG']);
   $total_log=count($tmp);
   if ($total_log > 30) {
    $tmp=array_slice($tmp, 0, 30);
    $host['LOG']=implode("\n", $tmp);
   }

   SQLUpdate('pinghosts', $host);

   if ($old_status!=$host['STATUS'] && $old_status!=0) {
    // do some status change actions
    $run_script_id=0;
    $run_code='';
    if ($old_status==2 && $host['STATUS']==1) {
     // got online
     if ($host['SCRIPT_ID_ONLINE']) {
      $run_script_id=$host['SCRIPT_ID_ONLINE'];
     } elseif ($host['CODE_ONLINE']) {
      $run_code=$host['CODE_ONLINE'];
     }
    } elseif ($old_status==1 && $host['STATUS']==2) {
     // got offline
     if ($host['SCRIPT_ID_OFFLINE']) {
      $run_script_id=$host['SCRIPT_ID_OFFLINE'];
     } elseif ($host['CODE_OFFLINE']) {
      $run_code=$host['CODE_OFFLINE'];
     }
    }

    if ($run_script_id) {
     //run script
     runScriptSafe($run_script_id);
    } elseif ($run_code) {
     //run code

                  try {
                   $code=$run_code;
                   $success=eval($code);
                   if ($success===false) {
                    DebMes("Error in hosts online code: ".$code);
                    registerError('ping_hosts', "Error in hosts online code: ".$code);
                   }
                  } catch(Exception $e){
                   DebMes('Error: exception '.get_class($e).', '.$e->getMessage().'.');
                   registerError('ping_hosts', get_class($e).', '.$e->getMessage());
                  }

    }

   }
   

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
   SQLDropTable('pinghosts');
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
pinghosts - Pinghosts
*/
  $data = <<<EOD
 pinghosts: ID int(10) unsigned NOT NULL auto_increment
 pinghosts: TITLE varchar(255) NOT NULL DEFAULT ''
 pinghosts: HOSTNAME varchar(255) NOT NULL DEFAULT ''
 pinghosts: TYPE int(30) NOT NULL DEFAULT '0'
 pinghosts: STATUS int(3) NOT NULL DEFAULT '0'
 pinghosts: SEARCH_WORD varchar(255) NOT NULL DEFAULT ''
 pinghosts: CHECK_LATEST datetime
 pinghosts: CHECK_NEXT datetime
 pinghosts: SCRIPT_ID_ONLINE int(10) NOT NULL DEFAULT '0'
 pinghosts: CODE_ONLINE text
 pinghosts: SCRIPT_ID_OFFLINE int(10) NOT NULL DEFAULT '0'
 pinghosts: CODE_OFFLINE text
 pinghosts: OFFLINE_INTERVAL int(10) NOT NULL DEFAULT '0'
 pinghosts: ONLINE_INTERVAL int(10) NOT NULL DEFAULT '0'
 pinghosts: LINKED_OBJECT varchar(255) NOT NULL DEFAULT ''
 pinghosts: LINKED_PROPERTY varchar(255) NOT NULL DEFAULT ''
 pinghosts: COUNTER_CURRENT int(10) NOT NULL DEFAULT '0'
 pinghosts: COUNTER_REQUIRED int(10) NOT NULL DEFAULT '0'
 pinghosts: STATUS_EXPECTED int(3) NOT NULL DEFAULT '0'
 pinghosts: LOG text
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgSmFuIDA2LCAyMDExIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>
