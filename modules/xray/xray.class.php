<?php
/**
* Blank 
*
* Blank
*
* @package project
* @author Serge J. <info@atmatic.com>
* @copyright http://www.activeunit.com/ (c)
* @version 0.1 (wizard, 17:03:02 [Mar 04, 2010])
*/
//
//
class xray extends module {
/**
* blank
*
* Module class constructor
*
* @access private
*/
function xray() {
  $this->name="xray";
  $this->title="X-Ray";
  $this->module_category="<#LANG_SECTION_SYSTEM#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=1) {
 $data=array();
 if (IsSet($this->id)) {
  $data["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $data["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $data["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $data["tab"]=$this->tab;
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
  global $action;
  if (isset($action)) {
   $this->action=$action;
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
 global $action;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } elseif ($this->action=='context' || $action=='context') {
   $this->context($out);
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
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}

/**
* Title
*
* Description
*
* @access public
*/
 function context(&$out) {
  global $ajax;
  if ($ajax) {
   header ("HTTP/1.0: 200 OK\n");
   header ('Content-Type: text/html; charset=utf-8');
   global $op;
   if ($op=='process') {
    global $keyword;
    global $body;
    global $type;
    $found=array();
    $keywords=array();
    $keys=array();
    //processing keywords
    if ($keyword) {
     $keys[$keyword]=$type;
    }

    if ($body!='') {
     if (preg_match_all('/runScript\([\'"](.+?)[\'"]/is', $body, $m)) {
      $total=count($m[0]);
      for($i=0;$i<$total;$i++) {
       $keys[$m[1][$i]]='script';
      }
     }
     if (preg_match_all('/setGlobal\([\'"](.+?)\.(.+?)[\'"]/is', $body, $m)) {
      $total=count($m[0]);
      for($i=0;$i<$total;$i++) {
       $keys[$m[1][$i]]='object';
       $keys[$m[1][$i].'.'.$m[2][$i]]='property';
      }
     }
     if (preg_match_all('/getGlobal\([\'"](.+?)\.(.+?)[\'"]/is', $body, $m)) {
      $total=count($m[0]);
      for($i=0;$i<$total;$i++) {
       $keys[$m[1][$i]]='object';
       $keys[$m[1][$i].'.'.$m[2][$i]]='property';
      }
     }
     if (preg_match_all('/sg\([\'"](.+?)\.(.+?)[\'"]/is', $body, $m)) {
      $total=count($m[0]);
      for($i=0;$i<$total;$i++) {
       $keys[$m[1][$i]]='object';
       $keys[$m[1][$i].'.'.$m[2][$i]]='property';
      }
     }
     if (preg_match_all('/gg\([\'"](.+?)\.(.+?)[\'"]/is', $body, $m)) {
      $total=count($m[0]);
      for($i=0;$i<$total;$i++) {
       $keys[$m[1][$i]]='object';
       $keys[$m[1][$i].'.'.$m[2][$i]]='property';
      }
     }
     if (preg_match_all('/callMethod\([\'"](.+?)\.(.+?)[\'"]/is', $body, $m)) {
      $total=count($m[0]);
      for($i=0;$i<$total;$i++) {
       $keys[$m[1][$i]]='object';
       $keys[$m[1][$i].'.'.$m[2][$i]]='method';
      }
     }
     if (preg_match_all('/cm\([\'"](.+?)\.(.+?)[\'"]/is', $body, $m)) {
      $total=count($m[0]);
      for($i=0;$i<$total;$i++) {
       $keys[$m[1][$i]]='object';
       $keys[$m[1][$i].'.'.$m[2][$i]]='method';
      }
     }
    }

    //print_r($keys);echo "<br>";

    foreach($keys as $k=>$v) {
     if ($v=='script') {
      $keywords["runscript(\"".$k."\""]=$k;
      $keywords["runscript('".$k."'"]=$k;
     }
     if ($v=='object') {
      $keywords["setGlobal(\"".$k."."]=$k;
      $keywords["setGlobal('".$k."."]=$k;
      $keywords["sg(\"".$k."."]=$k;
      $keywords["sg('".$k."."]=$k;
     }
     if ($v=='property') {
      $keywords["setGlobal(\"".$k]=$k;
      $keywords["setGlobal('".$k]=$k;
      $keywords["sg(\"".$k]=$k;
      $keywords["sg('".$k]=$k;
      $tmp=explode('.', $k);
      $keywords["->setProperty('".$tmp[1]]=$tmp[1];
      $keywords["->setProperty(\"".$tmp[1]]=$tmp[1];
     }
     if ($v=='method') {
      $keywords["callMethod(\"".$k]=$k;
      $keywords["callMethod('".$k]=$k;
      $keywords["cm(\"".$k]=$k;
      $keywords["cm('".$k]=$k;
      $tmp=explode('.', $k);
      $keywords["->callMethod('".$tmp[1]]=$tmp[1];
      $keywords["->callMethod(\"".$tmp[1]]=$tmp[1];
     }
    }

    //print_r($keywords);echo "<br>";
    $mdl=new module();

    //processing body for keywords
    //...
    //processing keywords
    foreach($keywords as $k=>$v) {
    //scripts
     $scripts=SQLSelect("SELECT ID, TITLE FROM scripts WHERE (CODE LIKE '%".DBSafe($k)."%' OR TITLE LIKE '".DBSafe($v)."')");
     $total=count($scripts);
     for($i=0;$i<$total;$i++) {
      if (!$found['script'.$scripts[$i]['ID']]) {
       $rec=array();
       $rec['TYPE']='script';
       $rec['TITLE']=$scripts[$i]['TITLE'];
       $rec['LINK']='/admin.php?action=scripts&md=scripts&inst=adm&view_mode=edit_scripts&id='.$scripts[$i]['ID'];
       $found['script'.$scripts[$i]['ID']]=$rec;
      }
     }
    //objects
     $objects=SQLSelect("SELECT ID, TITLE, CLASS_ID FROM objects WHERE (TITLE LIKE '".DBSafe($v)."')");
     $total=count($objects);
     for($i=0;$i<$total;$i++) {
      if (!$found['object'.$scripts[$i]['ID']]) {
       $rec=array();
       $rec['TYPE']='object';
       $rec['TITLE']=$objects[$i]['TITLE'];
       $rec['LINK']="?(panel:{action=classes}classes:{view_mode=edit_classes,instance=adm,tab=objects,id=".$objects[$i]['CLASS_ID']."})&md=objects&view_mode=edit_objects&id=".$objects[$i]['ID'];
       $result=$mdl->parseLinks("<a href=\"".$rec['LINK']."\">");
       if (preg_match('/\?pd=.+"/', $result, $m)) {
        $rec['LINK']='/admin.php'.$m[0];
       }
       $found['object'.$objects[$i]['ID']]=$rec;
      }
     }
    //methods
     $methods=SQLSelect("SELECT methods.ID, methods.TITLE, classes.TITLE as CLASS, objects.TITLE as OBJECT, methods.CLASS_ID, methods.OBJECT_ID FROM methods LEFT JOIN classes ON methods.CLASS_ID=classes.ID LEFT JOIN objects ON methods.OBJECT_ID=objects.ID WHERE (methods.CODE LIKE '%".DBSafe($k)."%' OR methods.TITLE LIKE '".DBSafe($v)."')");
     $total=count($methods);
     for($i=0;$i<$total;$i++) {
      if (!$found['method'.$methods[$i]['ID']]) {
       $rec=array();
       $rec['TYPE']='method';
       $rec['TITLE']=$methods[$i]['TITLE'];
       if ($methods[$i]['OBJECT_ID']) {
        $rec['LINK']="?(panel:{action=classes}classes:{view_mode=edit_classes,instance=adm,tab=objects,id=".$methods[$i]['CLASS_ID']."})&md=objects&view_mode=edit_objects&id=".$methods[$i]['OBJECT_ID']."&tab=methods&overwrite=1&method_id=".$methods[$i]['ID'];
        $rec['TITLE']=$methods[$i]['OBJECT'].'.'.$rec['TITLE'];
       } else {
        $rec['LINK']="?(panel:{action=classes}classes:{view_mode=edit_classes,instance=adm,tab=methods,id=".$methods[$i]['CLASS_ID']."})&md=methods&view_mode=edit_methods&id=".$methods[$i]['ID'];
        $rec['TITLE']=$methods[$i]['CLASS'].' (class).'.$rec['TITLE'];
       }
       $result=$mdl->parseLinks("<a href=\"".$rec['LINK']."\">");
       if (preg_match('/\?pd=.+"/', $result, $m)) {
        $rec['LINK']='/admin.php'.$m[0];
       }
       $found['method'.$methods[$i]['ID']]=$rec;
      }
     }
    //properties
     $properties=SQLSelect("SELECT properties.ID, properties.TITLE, classes.TITLE as CLASS, objects.TITLE as OBJECT, properties.CLASS_ID, properties.OBJECT_ID FROM properties LEFT JOIN classes ON properties.CLASS_ID=classes.ID LEFT JOIN objects ON properties.OBJECT_ID=objects.ID WHERE (properties.TITLE LIKE '".DBSafe($v)."')");
     $total=count($properties);
     for($i=0;$i<$total;$i++) {
      if (!$found['property'.$properties[$i]['ID'].'_'.$properties[$i]['OBJECT_ID']]) {
       $rec=array();
       $rec['TYPE']='property';
       $rec['TITLE']=$properties[$i]['TITLE'];
       if ($properties[$i]['OBJECT_ID']) {
        $rec['LINK']="?(panel:{action=classes}classes:{view_mode=edit_classes,instance=adm,tab=objects,id=".$properties[$i]['CLASS_ID']."})&md=objects&view_mode=edit_objects&id=".$properties[$i]['OBJECT_ID']."&tab=properties";
        $rec['TITLE']=$properties[$i]['OBJECT'].'.'.$rec['TITLE'];
       } else {
        $rec['LINK']="?(panel:{action=classes}classes:{view_mode=edit_classes,instance=adm,tab=properties,id=".$properties[$i]['CLASS_ID']."})&md=properties&view_mode=edit_properties&id=".$properties[$i]['ID'];
        $rec['TITLE']=$properties[$i]['CLASS'].' (class).'.$rec['TITLE'];
       }
       $result=$mdl->parseLinks("<a href=\"".$rec['LINK']."\">");
       if (preg_match('/\?pd=.+"/', $result, $m)) {
        $rec['LINK']='/admin.php'.$m[0];
       }
       $found['property'.$properties[$i]['ID'].'_'.$properties[$i]['OBJECT_ID']]=$rec;
      }
     }
    //properties
     $pvalues=SQLSelect("SELECT pvalues.ID, objects.TITLE as OBJECT, properties.ID as PROPERTY_ID, properties.TITLE, properties.CLASS_ID, pvalues.OBJECT_ID FROM pvalues LEFT JOIN properties ON pvalues.PROPERTY_ID=properties.ID LEFT JOIN objects ON pvalues.OBJECT_ID=objects.ID WHERE (properties.TITLE LIKE '".DBSafe($v)."')");
     //print_r($pvalues);
     $total=count($pvalues);
     for($i=0;$i<$total;$i++) {
      if (!$found['property'.$pvalues[$i]['PROPERTY_ID'].'_'.$pvalues[$i]['OBJECT_ID']]) {
       $rec=array();
       $rec['TYPE']='property';
       $rec['TITLE']=$pvalues[$i]['OBJECT'].'.'.$pvalues[$i]['TITLE'];
       $rec['LINK']="?(panel:{action=classes}classes:{view_mode=edit_classes,instance=adm,tab=objects,id=".$pvalues[$i]['CLASS_ID']."})&md=objects&view_mode=edit_objects&id=".$pvalues[$i]['OBJECT_ID']."&tab=properties";
       $result=$mdl->parseLinks("<a href=\"".$rec['LINK']."\">");
       if (preg_match('/\?pd=.+"/', $result, $m)) {
        $rec['LINK']='/admin.php'.$m[0];
       }
       $found['property'.$pvalues[$i]['PROPERTY_ID'].'_'.$pvalues[$i]['OBJECT_ID']]=$rec;
      }
     }
    //menu items
    //timers
    //scene elements
    //web-vars
    }

    foreach($found as $k=>$v) {
     echo '<a href="'.$v['LINK'].'" target=_blank>'.$v['TYPE'].': '.$v['TITLE'].'</a><br/>';
    }

    //print_r($found);
   }
   exit;
  }
  if ($this->keyword) {
   $out['KEYWORD']=$this->keyword;
  }
  if ($this->code_id) {
   $out['CODE_ID']=$this->code_id;
  }
  if ($this->type) {
   $out['TYPE']=$this->type;
  }
 }

/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 global $ajax;
 if ($ajax) {
  global $op;
  if ($op=='getcontent') {
    header ("HTTP/1.0: 200 OK\n");
    header ('Content-Type: text/html; charset=utf-8');
    if ($this->view_mode=='') {
     $res=SQLSelect("SELECT pvalues.*, objects.TITLE as OBJECT, objects.DESCRIPTION as OBJECT_DESCRIPTION, properties.TITLE as PROPERTY, properties.DESCRIPTION FROM pvalues LEFT JOIN objects ON pvalues.OBJECT_ID=objects.ID LEFT JOIN properties ON pvalues.PROPERTY_ID=properties.ID  ORDER BY pvalues.UPDATED DESC");
     $total=count($res);
     echo '<table border=1 cellspacing=4 cellpadding=4 width=100%>';
      echo '<tr>';
      echo '<td><b>PROPERTY</b></td>';
      echo '<td><b>VALUE</b></td>';
      echo '<td><b>UPDATED</b></td>';
      echo '</tr>';
     for($i=0;$i<$total;$i++) {
      echo '<tr>';
      echo '<td>';
      echo $res[$i]['OBJECT'].'.'.$res[$i]['PROPERTY'];
      if ($res[$i]['OBJECT_DESCRIPTION']!='') {
      echo "<br><small style='font-size:9px'>".$res[$i]['OBJECT_DESCRIPTION']."</small>";
      }
      echo '</td>';
      echo '<td>';
      echo $res[$i]['VALUE'].'&nbsp;';
      echo '</td>';
      echo '<td>';
      echo $res[$i]['UPDATED'].'&nbsp;';
      echo '</td>';
      echo '</tr>';
     }
     echo '</table>';
    } elseif ($this->view_mode=='methods') {
     $res=SQLSelect("SELECT methods.*, objects.TITLE as OBJECT, objects.DESCRIPTION as OBJECT_DESCRIPTION, methods.DESCRIPTION FROM methods LEFT JOIN objects ON methods.OBJECT_ID=objects.ID WHERE 1 ORDER BY methods.EXECUTED DESC");//methods.OBJECT_ID<>0
     $total=count($res);
     echo '<table border=1 cellspacing=4 cellpadding=4 width=100%>';
      echo '<tr>';
      echo '<td><b>METHOD</b></td>';
      echo '<td><b>PARAMS</b></td>';
      echo '<td><b>EXECUTED</b></td>';
      echo '</tr>';
     for($i=0;$i<$total;$i++) {
      echo '<tr>';
      echo '<td>';
      @$tmp=unserialize($res[$i]['EXECUTED_PARAMS']);
      if ($tmp['ORIGINAL_OBJECT_TITLE'] && !$res[$i]['OBJECT']) {
       $res[$i]['OBJECT']=$tmp['ORIGINAL_OBJECT_TITLE'];
       //unset($tmp['ORIGINAL_OBJECT_TITLE']);
       $res[$i]['EXECUTED_PARAMS']=serialize($tmp);
      }
      echo $res[$i]['OBJECT'].'.'.$res[$i]['TITLE'];
      if ($res[$i]['DESCRIPTION']) {
       echo "<br><small style='font-size:9px'>";
       echo $res[$i]['DESCRIPTION'];
       echo "</small>";
      }
      echo '</td>';
      echo '<td>';
      echo str_replace(';', '; ', $res[$i]['EXECUTED_PARAMS']).'&nbsp;';
      echo '</td>';
      echo '<td>';
      echo $res[$i]['EXECUTED'].'&nbsp;';
      echo '</td>';
      echo '</tr>';
     }
     echo '</table>';     
    } elseif ($this->view_mode=='scripts') {
     $res=SQLSelect("SELECT scripts.* FROM scripts ORDER BY scripts.EXECUTED DESC");
     $total=count($res);
     echo '<table border=1 cellspacing=4 cellpadding=4 width=100%>';
      echo '<tr>';
      echo '<td><b>SCRIPT</b></td>';
      echo '<td><b>PARAMS</b></td>';
      echo '<td><b>EXECUTED</b></td>';
      echo '</tr>';
     for($i=0;$i<$total;$i++) {
      echo '<tr>';
      echo '<td>';
      echo $res[$i]['TITLE'];
      if ($res[$i]['DESCRIPTION']!='') {
       echo "<br><small style='font-size:9px'>".$res[$i]['DESCRIPTION']."</small>";
      }
      echo '</td>';
      echo '<td>';
      echo str_replace(';', '; ', $res[$i]['EXECUTED_PARAMS']).'&nbsp;';
      echo '</td>';
      echo '<td>';
      echo $res[$i]['EXECUTED'].'&nbsp;';
      echo '</td>';
      echo '</tr>';
     }
     echo '</table>';     
    } elseif ($this->view_mode=='timers') {
     $res=SQLSelect("SELECT jobs.* FROM jobs WHERE EXPIRED!=1 AND PROCESSED!=1 ORDER BY jobs.RUNTIME");
     $total=count($res);
     echo '<table border=1 cellspacing=4 cellpadding=4 width=100%>';
      echo '<tr>';
      echo '<td><b>TIMER</b></td>';
      echo '<td><b>COMMAND</b></td>';
      echo '<td><b>SCHEDULED</b></td>';
      echo '</tr>';
     for($i=0;$i<$total;$i++) {
      echo '<tr>';
      echo '<td>';
      echo $res[$i]['TITLE'];
      echo '</td>';
      echo '<td>';
      echo $res[$i]['COMMANDS'];
      echo '</td>';
      echo '<td>';
      echo $res[$i]['RUNTIME'].'&nbsp;';
      echo '</td>';
      echo '</tr>';
     }
     echo '</table>';     
    } elseif ($this->view_mode=='events') {

     $res=SQLSelect("SELECT events.* FROM events WHERE 1 ORDER BY events.ADDED DESC LIMIT 30");
     $total=count($res);
     echo '<table border=1 cellspacing=4 cellpadding=4 width=100%>';
      echo '<tr>';
      echo '<td><b>EVENT</b></td>';
      echo '<td><b>DETAILS</b></td>';
      echo '<td><b>ADDED</b></td>';
      echo '</tr>';
     for($i=0;$i<$total;$i++) {
      echo '<tr>';
      echo '<td>';
      echo $res[$i]['EVENT_NAME'].'&nbsp;';
      echo '</td>';
      echo '<td>';
      echo $res[$i]['DETAILS'].'&nbsp;';
      echo '</td>';
      echo '<td>';
      echo $res[$i]['ADDED'].'&nbsp;';
      echo '</td>';
      echo '</tr>';
     }
     echo '</table>';     

    }
  exit;
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
* Install
*
* Module installation routine
*
* @access private
*/
 function install($parent_name="") {
  parent::install($parent_name);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTWFyIDA0LCAyMDEwIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>