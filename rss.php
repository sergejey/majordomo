<?
/**
* RSS script
*
* @package MajorDoMo
* @author Serge Dzheigalo <jey@tut.by> http://smartliving.ru/
* @version 1.1
*/


 include_once("./config.php");
 include_once("./lib/loader.php");

 startMeasure('TOTAL'); // start calculation of execution time

 include_once(DIR_MODULES."application.class.php");

 $session=new session("prj");
 $db=new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database

// get settings
$settings = SQLSelect('SELECT NAME, VALUE FROM settings');
$total = count($settings);
for ($i = 0; $i < $total; $i ++)
        Define('SETTINGS_' . $settings[$i]['NAME'], $settings[$i]['VALUE']);

// language selection by settings
if (SETTINGS_SITE_LANGUAGE && file_exists(ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php')) include_once (ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php');
include_once (ROOT . 'languages/default.php');

if (defined('SETTINGS_SITE_TIMEZONE')) {
 ini_set('date.timezone', SETTINGS_SITE_TIMEZONE);
}


$qry="1";

if ($_GET['level']) {
 $qry.=" AND shouts.IMPORTANCE>=".(int)$_GET['level'];
}

$res=SQLSelect("SELECT shouts.*, UNIX_TIMESTAMP(shouts.ADDED) as TM, users.NAME FROM shouts LEFT JOIN users ON shouts.MEMBER_ID=users.ID WHERE $qry ORDER BY shouts.ADDED DESC, ID DESC LIMIT 20");

$res=array_reverse($res);

$total=count($res);

  if ($total) {

   $result="<?xml version=\"1.0\" encoding=\"windows-1251\"?>";
   $result.="<rss version=\"2.0\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:annotate=\"http://purl.org/rss/1.0/modules/annotate/\" xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\">";
   $result.="<channel>";
   $result.="<title>".PROJECT_TITLE."</title>";
   $result.="<link>http://".PROJECT_DOMAIN."/</link>";
   $result.="<lastBuildDate>".date('r')."</lastBuildDate>";
   $result.="<description>".PROJECT_TITLE." RSS feed</description>";

   for($i=0;$i<$total;$i++) {

    $res[$i]['LINK']='http://'.PROJECT_DOMAIN.'/event'.$res[$i]['ID'];

    $result.="<item>";
    $result.="<title>".substr($res[$i]['MESSAGE'], 0, 500)."</title>";
    $result.="<pubDate>".date('r', $res[$i]['TM'])."</pubDate>";
    $result.="<description>".str_replace("\r", '', $res[$i]['MESSAGE'])."</description>";
    if ($res[$i]['NAME']) {
     $result.="<dc:creator>".$res[$i]['NAME']."</dc:creator>";
    }
    $result.="<link>".$res[$i]['LINK']."</link>";
    $result.="<guid>".$res[$i]['LINK']."</guid>\n";
    $result.="</item>";
   }
   $result.="</channel>";
   $result.="</rss>";

   Header("Content-type:text/xml; charset=utf-8");
   echo $result;
  }


//print_r($res);


?>