<?php

function python_run_code($code, $params = '', $object = '') {
    //DebMes("Running python code: ".$code,'python');
    if (defined('PYTHON_PATH')) {
        $python_path = PYTHON_PATH;
    } else {
        DebMes("Chek config.php file for define path to php ");
        return false;
    }
    if ($python_path!='') {
        $result_code = python_make_full_code($code, $object);
        //dprint($result_code,false);
        $fileName = md5($code) . '.py';
        if (!is_dir(DOC_ROOT . '/cms/python/')) {
            umask(0);
            mkdir(DOC_ROOT . '/cms/python/',0777);
        }
        if (!is_array($params)) {
            $params=array();
        }
        $filePath = DOC_ROOT . '/cms/python/' . $fileName;
        $cmd = $python_path." ".$filePath.' \''.json_encode($params).'\' 2>&1';

        if (file_exists($filePath)) {
            $currentMd5=md5(LoadFile($filePath));
        } else {
            $currentMd5='';
        }
        if (md5($result_code)!=$currentMd5) {
            SaveFile($filePath,$result_code);
        }
        exec($cmd,$output);
        $res = implode("\n", $output);
        if ($res!='') {
            echo($res);
        }
        return $res;
    }
}

function python_make_full_code($code, $object = '') {

    $lib_path = DOC_ROOT.'/lib/python';

    $constants="# -*- coding: utf-8 -*-\n";

    $all_constants = get_defined_constants();
    foreach($all_constants as $constant_name=>$constant_value) {
        if (
            preg_match('/^LANG\_/',$constant_name) ||
            preg_match('/^DB_/',$constant_name) ||
            preg_match('/^SETTINGS_/',$constant_name) ||
            $constant_name == 'ROOT' ||
            $constant_name == 'ROOTHTML' ||
            $constant_name == 'DIR_TEMPLATES' ||
            $constant_name == 'DIR_MODULES' ||
            $constant_name == 'BASE_URL' ||
            0
        ) {
            $constant_value = str_replace("\n","\\n",$constant_value);
            $constants.=$constant_name." = \"".addcslashes($constant_value,"\"")."\"\n";
        }
    }

    if (!is_dir(DOC_ROOT . '/cms/python/')) {
        umask(0);
        mkdir(DOC_ROOT . '/cms/python/',0777);
    }
    $constants_fileName = DOC_ROOT . '/cms/python/mjd_constants.py';
    if (file_exists($constants_fileName)) {
        $current_md5=md5(LoadFile($constants_fileName));
    } else {
        $current_md5='';
    }
    if ($current_md5!=md5($constants)) {
        SaveFile($constants_fileName,$constants);
    }

    if ($object!='') {
        $class_code="#object: $object\n";

        $method_code="";
        $tmp=explode("\n",$code);
        foreach($tmp as $line) {
            $method_code.=str_repeat(' ',8).$line."\n";
        }

        $class_code.=<<<CL
class mjdThisObject(mjdObject):
    def thisMethod(self, params):
$method_code
        return 1;

thisObject = mjdThisObject("$object")
thisObject.thisMethod(params)        
CL;

        $code=$class_code;
    }

    $code =<<<FF
# -*- coding: utf-8 -*-    
import os
import sys
import re
import json
import six
import importlib
import MySQLdb as mdb
import datetime
import time
from typing import List, Any
from datetime import datetime as DT, timedelta
from mjd_constants import *
try:
    import urllib.request as urllib2
    from urllib.parse import quote
    from urllib.parse import urlencode
except ImportError:
    import urllib2
    from urllib import urlencode
	
sys.path.append(os.path.abspath("$lib_path"))

from events import *
from general import *
from historys import *
from job import *
from mjdm import *
from timer import *

if (sys.argv[1:]):
    params=sys.argv[1:]
else:
    params = []

$code
FF;
    return $code;
}

function python_syntax_error($code) {
    $fileName = md5(time() . rand(0, 10000)) . '.py';
    $filePath = DOC_ROOT . '/cms/cached/' . $fileName;
    $code = python_make_full_code($code);
    SaveFile($filePath, $code);
    $python_path = '';
    if (defined('PYTHON_PATH')) {
        $python_path = PYTHON_PATH;
    } elseif (substr(php_uname(), 0, 7) != "Windows") {
        $python_path = 'python';
    }
    if ($python_path!='') {
        $cmd = $python_path." -m py_compile ".$filePath.' 2>&1';
        exec($cmd,$output);
        $res = trim(implode("\n", $output));
        return $res;
    } else {
        return "Code not recognized";
    }
}

function isItPythonCode($code) {
    if (preg_match('/^#python/ui',$code)) return true;
    $code = str_replace("\r","",$code);
    $tmp=explode("\n",$code);
    if (count($tmp)==1) return false;
    if (preg_match('/\);/ui',$code)) return false;
    if (preg_match('/{\\n/uis',$code)) return false;
    if (preg_match('/;$/ui',$code)) return false;
    if (preg_match('/;\\n/ui',$code)) return false;
    if (preg_match('/\$\w+.+;/ui',$code)) return false;
    return true;
}
