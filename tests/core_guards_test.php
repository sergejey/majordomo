<?php
// Isolated tests: no MajorDoMo bootstrap, database or network connections.
error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
function check($ok, $name) {
    if (!$ok) throw new RuntimeException('FAIL: '.$name);
    echo 'PASS: '.$name.PHP_EOL;
}
$source = file_get_contents(dirname(__DIR__).'/lib/errors.class.php');
$start = strpos($source, 'class custom_error');
$end = strpos($source, '\nfunction majordomoGetErrorType');
// The actual class is tested without registering production shutdown handlers.
$end = strpos($source, 'function majordomoGetErrorType');
check($start !== false && $end !== false, 'error class located');
eval(substr($source, $start, $end-$start));
define('DEBUG_MODE', true);
foreach (array(
    array(array(), 'CLI'),
    array(array('SCRIPT_FILENAME'=>'/test/cycle.php'), '/test/cycle.php'),
    array(array('argv'=>array('cycle.php')), 'cycle.php'),
    array(array('REQUEST_URI'=>'/admin.php'), 'http://localhost/admin.php'),
    array(array('REQUEST_URI'=>'/admin.php','SERVER_NAME'=>'test.local'), 'http://test.local/admin.php')
) as $case) {
    $_SERVER=$case[0];
    ob_start(); new custom_error('Original SQL error'); $output=ob_get_clean();
    check(strpos($output, $case[1])!==false && strpos($output,'Original SQL error')!==false, 'error context '.$case[1]);
}
$source=file_get_contents(dirname(__DIR__).'/modules/devices/devices_edit.inc.php');
$start=strpos($source, "if (\$this->tab == 'logic') {");
$end=strpos($source, "if (\$this->tab == 'settings') {");
check($start!==false && $end>$start, 'actual logic branch located');
$logic=substr($source,$start,$end-$start);
function gr($key) { return $key==='code' ? 'return 1;' : ''; }
function getObject($name) { return $GLOBALS['test_object']; }
function SQLSelectOne($sql) { return array('ID'=>5,'OBJECT_ID'=>7,'CODE'=>'return 1;','TITLE'=>'logicAction'); }
function SQLInsert($table,$rec) { $GLOBALS['writes']++; return 5; }
function SQLUpdate($table,$rec) { $GLOBALS['writes']++; }
function SQLExec($sql) { $GLOBALS['writes']++; }
function php_syntax_error($code) { return ''; }
class TestObject {
    public $id=7, $class_id=2;
    function getParentMethods($id,$unused,$flag) { return array(); }
    function getMethodByName($name,$class,$id) { return 5; }
}
class EditorHarness {
    public $tab='logic', $mode='';
    function run($logic,$linked) {
        $rec=array('LINKED_OBJECT'=>$linked); $out=array();
        eval($logic);
        $out['COMMON_OUTPUT_REACHED']=true;
        return $out;
    }
}
$h=new EditorHarness();
foreach (array('', 'DeletedObject') as $linked) {
    foreach (array(false,0,null) as $missing) {
        $GLOBALS['test_object']=$missing; $GLOBALS['writes']=0; $h->mode='update';
        $out=$h->run($logic,$linked);
        check(!empty($out['ERR']) && $out['METHODS']===array() && $GLOBALS['writes']===0 && $out['COMMON_OUTPUT_REACHED'], 'missing object blocks save and continues');
    }
}
$GLOBALS['test_object']=new TestObject(); $GLOBALS['writes']=0; $h->mode='';
$out=$h->run($logic,'ExistingObject');
check(empty($out['ERR']) && $out['METHOD_ID']===5 && $GLOBALS['writes']===0, 'existing object read');
$h->mode='update'; $out=$h->run($logic,'ExistingObject');
check(!empty($out['OK']) && $GLOBALS['writes']===1, 'existing object save');
echo "ALL TESTS PASSED\n";
