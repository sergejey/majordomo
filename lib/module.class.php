<?php
/**
* Module class
*
* Main part of framework
*
* 
* @package framework
* @author Serge Dzheigalo <jey@activeunit.com>
* @copyright http://www.activeunit.com/ (c) 2002
* @since 20-Jan-2004
* @version 2.4
*/

/**
* Used for building query string
*/
Define("PARAMS_DELIMITER", "pz_");
/**
* Used for building query string
*/
Define("STRING_DELIMITER", "sz_");
/**
* Used for building query string
*/
Define("EQ_DELIMITER", "qz_");

/**
* Framework Module Class
*
* Used by all modules to work correctly in project framework
*
* @package framework
* @author Serge Dzheigalo
* @copyright http://www.activeunit.com/ (c) 2002
* @version 1.0b
*/
 class module {
/**
* @var string module name
*/
  var $name;
/**
* @var array module output data
*/
  var $data;
/**
* @var string module instance name (optional)
*/
  var $instance;
/**
* @var string module teplate file (optional)
*/
  var $template;
/**
* @var string module execution result
*/
  var $result;
/**
* @var object module owner (parent module)
*/
  var $owner;
/**
* @var array module configuration
*/
  var $config;

// --------------------------------------------------------------------
/**
* Module constructor
*
* @access public
*/
  function module() {
   // this method should me used in context of each module
  }

// --------------------------------------------------------------------
/**
* Module execution
*
* @access public
*/
  function run() {
   // this method should me used in context of each module
  }

// --------------------------------------------------------------------
/**
* Getting module params from query string
*
* Used for getting module execution params
*
* @access public
*/
  function getParams() {
   // this method should me used in context of each module
  }

// --------------------------------------------------------------------
/**
* Saving module params for correct query strings building
*
* Used to prevent loosing current module state while interraction with child modules
*
* @param array $data params to save
* @access public
*/
  function saveParams($data=1) {
   if (is_array($data)) {
    if (IsSet($this->instance)) {
     $data["instance"]=$this->instance;
    }
//    echo $this->name." ".$this->instance."<br>";
    $res=$this->createParamsString($data, $this->name).PARAMS_DELIMITER.$this->saveParentParams();
    return $res;
   }
  }

// --------------------------------------------------------------------
/**
* Generating query string for saved params
*
* @param array $data params to save
* @param array $data params to save
* @access private
* @return string current module params query string
*/
  function createParamsString($data, $name) {
   $params="";
   foreach($data as $k=>$v) {
    if ($v=="") {
     UnSet($data[$k]);
    } else {
     $params.="m".STRING_DELIMITER.$name.STRING_DELIMITER.$k.EQ_DELIMITER.$v.PARAMS_DELIMITER;
     $params1[]="$k=$v";
    }
   }
   //echo $params."<br>";
   //$params=toXML($data);
   //$params=serialize($data);
   if (count($params1)) {
    $params="$name:{".implode(",", $params1)."}";
    //echo $params."<br>";
    $params=urlencode(base64_encode($params));
   } else {
    $params="";
    //echo "-<br>";
   }
   /*
   if (count($data)>0) {
    $temp[$this->name]=$data;
    $xml=new xml_data($temp);
    $res=$xml->string;
   }
   */
   $res=$params;
   return $res;
  }
// --------------------------------------------------------------------
/**
* Restoring module data from query string
*
* @global string query param - current module
* @global string query param - all params
* @global array GET VARS
* @global array POST VARS
* @access public
*/
  function restoreParams() {
   global $md;
   global $pd;
   global $HTTP_GET_VARS;
   global $HTTP_POST_VARS;

   if (strpos($pd, 'm'.STRING_DELIMITER)) {
    $this->restoreParamsOld();
    return;
   }



  // getting params of all modules
   $modules=explode(PARAMS_DELIMITER, $pd);
   for($i=0;$i<count($modules);$i++) {
    $data=base64_decode(urldecode($modules[$i]));

      if (preg_match_all('/(.+?):{(.+?)}/', $data, $matches2, PREG_PATTERN_ORDER)) {
       for($k=0;$k<count($matches2[1]);$k++) {
        $data=array();
        $module_name=$matches2[1][$k];
        $module_params=explode(",",$matches2[2][$k]);
        $instance="";
        // creating params array for current module
        $cr=array();
        for($m=0;$m<count($module_params);$m++) {
         $ar=explode("=", trim($module_params[$m]));
         $param_name=trim($ar[0]);
         $param_value=trim($ar[1]);
         $cr[$param_name]=$param_value;
        }
        // setting params for current module
        // module has instance in params
        if ($cr['instance']!='') {
         $instance_params[$module_name][$cr['instance']]=$cr;
        } else {
       // module has no instance
         $global_params[$module_name]=$cr;
        }
       }
      }
   }

    // restoring params for non-active module
    if (IsSet($this->instance) && IsSet($instance_params[$this->name][$this->instance])) {
     // setting params for current instance
     $module_data=$instance_params[$this->name][$this->instance];
     foreach ($module_data as $k=>$v) {
      $this->{$k}=$v;
     }
    } elseif (!IsSet($this->instance)) {
     // module has no instances at all
     $module_data=$global_params[$this->name];
    }

    // setting module data
    if (IsSet($module_data)) {
      foreach ($module_data as $k=>$v) {
       $this->{$k}=$v;
      }
    }

  }

// --------------------------------------------------------------------
/**
* Restoring module data from query string (old version)
*
* @global string query param - current module
* @global string query param - all params
* @global array GET VARS
* @global array POST VARS
* @access public
*/
  function restoreParamsOld() {
   global $md;
   global $pd;
   global $HTTP_GET_VARS;
   global $HTTP_POST_VARS;

  // getting params of all modules
  $pd=str_replace(EQ_DELIMITER, "=", $pd);
  if (preg_match_all('/m'.STRING_DELIMITER.'(\w+?)'.STRING_DELIMITER.'(\w+?)=(.*?)'.PARAMS_DELIMITER.'/', $pd, $matches, PREG_PATTERN_ORDER)) {
   for($i=0;$i<count($matches[1]);$i++) {
    $global_params[$matches[1][$i]][$matches[2][$i]]=$matches[3][$i];
   }
  }   

  $xml=new xml_data($global_params);

   if ($md!=$this->name) {
    // restoring params for non-active module
    if (IsSet($xml->hash[$this->name])) {
     $module_data=$xml->hash[$this->name];
     if ((IsSet($this->instance) && ($module_data["instance"] == $this->instance)) || (!IsSet($this->instance))) {
      foreach ($module_data as $k=>$v) {
       $this->{$k}=$v;
      }
     }
    }
   }

   if ($md==$this->name) {
    // if current module then we should take params directly from query string
    if (Is_Array($HTTP_POST_VARS) && (count($HTTP_POST_VARS)>0)) {
     $params=$HTTP_POST_VARS;
    } else {
     $params=$HTTP_GET_VARS;
    }
    foreach($params as $k=>$v) {
     if (($k=="md") || ($k=="pd") || ($k=="inst") || ($k=="name")) continue;
     // setting params as module properties
     $this->{$k}=$v;
    }
   }

  }


// --------------------------------------------------------------------
/**
* Saving parent module params for correct query strings building
*
* @access private
* @return string parent module params query string
*/
  function saveParentParams() {
   if (IsSet($this->owner->name)) {
    return $this->owner->saveParams();
   }
   return "";
  }

// --------------------------------------------------------------------
/**
* Checking module installation status and process with installation if neccessary
*
* @access public
*/
 function checkInstalled() {
  if (!file_exists(DIR_MODULES.$this->name."/installed")) {
   $this->install();
  } else {
   $rec=SQLSelectOne("SELECT * FROM project_modules WHERE NAME='".$this->name."'");
   if (!IsSet($rec["ID"])) $this->install();
  }
 }


// --------------------------------------------------------------------
/**
* Getting module configuration
*
* Used for loading module config data in $this->config variable
*
* @access public
*/
 function getConfig() {
  $rec=SQLSelectOne("SELECT * FROM project_modules WHERE NAME='".$this->name."'");
  $data=$rec["DATA"];
  $this->config=unserialize($data);
  return $this->config;
 }


// --------------------------------------------------------------------
/**
* Saving module configuration
*
* Used for saving $this->config variable in project module repository database for future
*
* @access public
*/
 function saveConfig() {
  $rec=SQLSelectOne("SELECT * FROM project_modules WHERE NAME='".$this->name."'");
  $rec["DATA"]=serialize($this->config);
  SQLUpdate("project_modules", $rec);
 }

// --------------------------------------------------------------------
/**
* Installing current module
*
* Adding information about module in project registry and
* processing to database installation routine if file "installed" does not
* exists in module directory.
*
* @param string $parent_name optional parent module for installing packages (reserved for future development)
* @access private
*/
 function install($parent_name="") {
  $this->dbInstall("");
  $rec=SQLSelectOne("SELECT * FROM project_modules WHERE NAME='".$this->name."'");
  $rec["NAME"]=$this->name;
  if (!IsSet($this->title)) {
   $this->title=$this->name;
  }
  $rec["TITLE"]=$this->title;
  if (IsSet($this->module_category)) {
   $rec["CATEGORY"]=$this->module_category;
  }
  if (!IsSet($rec["ID"])) {
   $rec["ID"]=SQLInsert("project_modules", $rec);
  } else {
   SQLUpdate("project_modules", $rec);
  }
  if (!file_exists(DIR_MODULES.$this->name."/installed")) {
   SaveFile(DIR_MODULES.$this->name."/installed", date("H:m d.M.Y"));
  }
 }

// --------------------------------------------------------------------
/**
* UnInstalling current module
*
* Removing information about module in project registry and
*
* @access private
*/
 function uninstall() {
  $rec=SQLSelectOne("SELECT * FROM project_modules WHERE NAME='".$this->name."'");
  if (IsSet($rec["ID"])) {
   SQLExec("DELETE FROM project_modules WHERE ID='".$rec["ID"]."'");
  }
  if (file_exists(DIR_MODULES.$this->name."/installed")) {
   unlink(DIR_MODULES.$this->name."/installed");
  }
 }


// --------------------------------------------------------------------
/**
* Module data installation
*
* Installing required module data structure into project.
* (Notes: file "initial.sql" will be executed if found in project directory)
*
* @param string $data required database tables and fields
* @access private
*/
 function dbInstall($data) {
  $sql="";
  $need_optimzation=array();
  $strings=explode("\n", $data);
  $table_defined=array();
  for($i=0;$i<count($strings);$i++) {

   $strings[$i]=preg_replace('/\/\/.+$/is', '', $strings[$i]);
   $fields=explode(":", $strings[$i]);
   $table=trim(array_shift($fields));
   $definition=trim(implode(':', $fields));
   $definition=str_replace("\r", "", trim($definition));


   if ($definition=="") continue;

   $tmp=explode(" ", $definition);
   $field=$tmp[0];

   if (!in_array(strtolower($field), array('key', 'index', 'fulltext'))) {
    $definition=str_replace($field.' ', '`'.$field.'` ', $definition);
   }

   if (!IsSet($table_defined[$table])) {
   // new table
    if (strpos($definition, "auto_increment")) {
     $definition.=", PRIMARY KEY(".$field.")";
     //$definition.=", KEY(".$field.")";
    }
    $sql="CREATE TABLE IF NOT EXISTS $table ($definition) CHARACTER SET utf8 COLLATE utf8_general_ci;";
    $table_defined[$table]=1;
    SQLExec($sql);
    $result = SQLExec("SHOW FIELDS FROM $table");
    while($row = mysql_fetch_array($result)) {
     $tbl_fields[$table][$row[Field]]=1;
    }

   } elseif ((strtolower($field)=='key') || (strtolower($field)=='index')  || (strtolower($field)=='fulltext')) {

    if (!$indexes_retrieved[$table]) {
     $result = SQLExec("SHOW INDEX FROM $table");
     while($row = mysql_fetch_array($result)) {
      $tbl_indexes[$table][$row[Key_name]]=1;
     }
     $indexes_retrieved[$table]=1;
    }

    preg_match('/\((.+?)\)/', $definition, $matches);
    $key_name=trim($matches[1], " `");
    if (!IsSet($tbl_indexes[$table][$key_name])) {
     $definition=str_replace('`', '', $definition);
     $sql="ALTER IGNORE TABLE $table ADD $definition;";     
     SQLExec($sql);
     $to_optimize[]=$table;
    }

   } elseif (!IsSet($tbl_fields[$table][$field])) {
   // new field
    $sql="ALTER IGNORE TABLE $table ADD $definition;";
    SQLExec($sql);
   }
  }


  if ($to_optimize[0]) {
   foreach($to_optimize as $table) {
    SQLExec("OPTIMIZE TABLE ".$table.";");
   }
  }



   // executing initial query and comments each line to prevent execution next time
    if (file_exists(DIR_MODULES.$this->name."/initial.sql")) {
     $data=LoadFile(DIR_MODULES.$this->name."/initial.sql");
     $data.="\n";
     $data=str_replace("\r", "", $data);
     $query=explode("\n",$data);
     for ($i=0;$i < count($query)-1;$i++) {
      if ($query[$i]{0}!="#") {
       SQLExec($query[$i]);
       $mdf[]="#".$query[$i];
      } else {
       $mdf[]=$query[$i];
      }
     }
     SaveFile(DIR_MODULES.$this->name."/initial.sql", join("\n", $mdf));
    }

 }

// --------------------------------------------------------------------
/**
* Getting list of sub-modules
*
* Reserved for future development
*
* @access private
*/
 function getSubModules() {
  return SQLSelect("SELECT * FROM project_modules WHERE PARENT_NAME='".$this->name."'");
 }

// --------------------------------------------------------------------
/**
* Redirect to another URL whithin project
*
* Used for redirection from one URL to another within current module or project
*
* @para string $url special formatted url (ex: "?mode=new", "?(application:{action=test})&md=test&var=value1", etc)
* @access private
*/
 function redirect($url) {
  global $session;
  global $db;

  $url=$this->makeRealURL($url);

  $session->save();
  $db->Disconnect();
  if (!headers_sent()) {
   header("Location: $url\n\n");
  } else {
   print "Headers already sent in $filename on line $linenum<br>\n" ."Cannot redirect instead\n";
  }
  exit;
 }

/**
* Create "real" URL for current module
*
* Description
*
* @access public
*/
 function makeRealURL($url) {
  $param_str=$this->parseLinks("<a href=\"$url\">");
  preg_match("<a href=\"(.*?)\">", $param_str, $matches);
  $url=$matches[1];
  $url=str_replace("?", "?".session_name()."=".session_id()."&", $url);
  return $url;
 }


/**
* Title
*
* Description
*
* @access public
*/
// --------------------------------------------------------------------
 function cached($content) {
  $h=md5($content);
  $filename=ROOT.'cached/'.$this->name.'_'.$h.'.txt';
  $cache_expire=15*60; // 15 minutes cache expiration time

  if (file_exists($filename)) {
   if ((time()-filemtime($filename))<=$cache_expire) {
    $cached_result=LoadFile($filename);
   } else {
    unlink($filename);
   }
  }

  if ($cached_result=='') {
   $p=new jTemplate(DIR_TEMPLATES.'null.html', $this->data, $this);
   $cached_result=$p->parse($content, $this->data, DIR_TEMPLATES);
   SaveFile($filename, $cached_result);
  }

  return $cached_result;

 }

/**
* Title
*
* Description
*
* @access public
*/
// --------------------------------------------------------------------
 function dynamic($content) {

  $h=md5($content);

  $content="<!-- begin_data [aj_".$h."] -->".$content."<!-- end_data [aj_".$h."] -->";

  $filename=ROOT.'templates_ajax/'.$this->name.'_'.$h.'.html';

  if (!file_exists($filename)) {
   SaveFile($filename, $content);
  }

  $url=$this->makeRealURL("?");
  if (preg_match('/\?/is', $url)) {
   $url.="&ajt=".$h;
  } else {
   $url.="?ajt=".$h;
  }

  $res.="<div id='aj_".$h."'>Loading...</div><script language='javascript' type='text/JavaScript'>getBlockData('aj_".$h."', '".$url."')</script>";

  return $res;

 }


// --------------------------------------------------------------------
/**
* Parsing links to maintain modules structure and data
*
* Used to maintain framework structure by saving modules data
* in query strings and hidden fields
* Usage:
* following will be changed
* [#link param1=value1#]
* <a href="?param1=value1&param2=value2&...">
* <a href="?(module:{param1=value1, param2=value2, ...})&param1=value1&param2=value2&...">
* (note: to prevent link modification use <a href="?...<!-- modified -->">)
* </form> (note: to prevent "</form>" changing use "</form><!-- modified -->" construction)
*
* @access private
*/
 function parseLinks($result) {
   global $PHP_SELF;
   global $md;

   if (!IsSet($_SERVER['PHP_SELF'])) {
    $_SERVER['PHP_SELF']=$PHP_SELF;
   }

   if ($md!=$this->name) {
    $param_str=$this->saveParams();
   } elseif (IsSet($this->owner)) {
    $param_str=$this->owner->saveParams();
   }

   // a href links like <a href="?param=value">
   if ((preg_match_all('/="\?(.*?)"/is', $result, $matches, PREG_PATTERN_ORDER))) {
    for($i=0;$i<count($matches[1]);$i++) {
     $link=$matches[1][$i];
     if (!Is_Integer(strpos($link, '<!-- modified -->'))) { // skip custom links
      if (preg_match('/^\((.+?)\)(.*)$/', $link, $matches1)) {
       $other=$matches1[2];
       $res_str=$this->codeParams($matches1[1]);
       $result=str_replace($matches[0][$i], '="'.$_SERVER['PHP_SELF'].'?pd='.$res_str.$other.'"', $result);      
      } elseif (strpos($link, "md=")!==0) {
       $result=str_replace($matches[0][$i], '="'.$_SERVER['PHP_SELF'].'?pd='.$param_str.'&md='.$this->name.'&inst='.$this->instance.'&'.$link.'"', $result); // links
      } else {
       $result=str_replace('action="?', 'action="'.$_SERVER['PHP_SELF'].'"', $result); // forms
      }
     } else {
      // remove modified param
      $link=str_replace('<!-- modified -->', '', $link);      
      $result=str_replace($matches[0][$i], '="'.$link.'"', $result);      
     }
    }
   }

   // form hidden params
   if (preg_match_all('/\<input([^\<\>]+?)(value="\((.*?)\)")([^\<\>]*?)\>/is', $result, $matches, PREG_PATTERN_ORDER)) {
      for($i=0;$i<count($matches[3]);$i++) {
         if (strpos($matches[1][$i], 'type="hidden"') !== false || strpos($matches[4][$i], 'type="hidden"') !== false) {
            $res_str=$this->codeParams($matches[3][$i]);
            $result=str_replace($matches[2][$i], 'value="'.$res_str.'"', $result);
         }
      }
   }

   // form hidden params
   /*
   if (preg_match_all('/value="\((.*?)\)"/is', $result, $matches, PREG_PATTERN_ORDER)) {
    for($i=0;$i<count($matches[1]);$i++) {   
      $res_str=$this->codeParams($matches[1][$i]);
      $result=str_replace($matches[0][$i], 'value="'.$res_str.'"', $result);      
    }
   }
   */

   // [#link ...#]
   if (preg_match_all('/\[#link (.*?)#\]/is', $result, $matches, PREG_PATTERN_ORDER)) {
    for($i=0;$i<count($matches[1]);$i++) {
     $link=$matches[1][$i];
     if (preg_match('/^\((.+?)\)(.*)$/', $link, $matches1)) {
      $other=$matches1[2];
      $res_str=$this->codeParams($matches1[1]);
      $result=str_replace($matches[0][$i], $_SERVER['PHP_SELF'].'?pd='.$res_str.$other, $result);      
     } elseif (strpos($link, "md=")!==0) {
      $result=str_replace($matches[0][$i], $_SERVER['PHP_SELF'].'?pd='.$param_str.'&md='.$this->name.'&inst='.$this->instance.'&'.$link, $result); // links
     }
    }
   }


   // form hidden variables (exclude </form><!-- modified -->)
   $result=preg_replace("/<\/form>(?!<!-- modified -->)/is", "<input type=\"hidden\" name=\"pd\" value=\"$param_str\">\n<input type=\"hidden\" name=\"md\" value=\"".$this->name."\">\n<input type=\"hidden\" name=\"inst\" value=\"".$this->instance."\">\n</FORM><!-- modified -->", $result); // forms
   return $result;

 }

// --------------------------------------------------------------------
/**
* Parsing params in coded string
*
* Used to maintain framework structure by saving modules data
* in query strings and hidden fields
*
* @access private
*/
 function codeParams($in) {

      if (preg_match_all('/(.+?):{(.+?)}/', $in, $matches2, PREG_PATTERN_ORDER)) {
       for($k=0;$k<count($matches2);$k++) {
        $data=array();
        $module_name=$matches2[1][$k];
        $module_params=explode(',',$matches2[2][$k]);
        for($m=0;$m<count($module_params);$m++) {
         $ar=explode("=", trim($module_params[$m]));
         $data[trim($ar[0])]=trim($ar[1]);
        }
        $res_str.=$this->createParamsString($data, $module_name).PARAMS_DELIMITER;
       }
      }

      return $res_str;

 }



 }
?>