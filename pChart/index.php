<?php   
 chdir('../');
 set_time_limit(0);
 /*
     Example1 : A simple line chart
 */

 include_once("./config.php");
 include_once("./lib/loader.php");


 include_once(DIR_MODULES."application.class.php");

 $db=new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database
 include_once("./load_settings.php");



 // Standard inclusions      
 include("./pChart/pData.class");   
 include("./pChart/pChart.class");   


  if (!$width) {
   $w=610;
  } else {
   $w=(int)$width;
  }

  
  // Dataset definition   
  $DataSet = new pData;

  if ($p!='') {
   if (preg_match('/(.+)\.(.+)/is', $p, $m)) {
    $obj=getObject($m[1]);
    $prop_id=$obj->getPropertyByName($m[2], $obj->class_id, $obj->id);
   }
  }

  //$type='';

  $pvalue=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id."' AND OBJECT_ID='".$obj->id."'");

  if (!$pvalue['ID']) {
   exit;
  }

  if ($_GET['op']=='value') {
   echo $pvalue['VALUE'];exit;
  }

   $end_time=time();

   if ($_GET['px']) {
    $px_per_point=(int)$_GET['px'];
   } else {
    $px_per_point=6;
   }
   

 if (preg_match('/(\d+)d/', $type, $m)) {

   $total=(int)$m[1];
   $period=round(($total*24*60*60)/(($w-80)/$px_per_point)); // seconds
   $start_time=$end_time-$total*24*60*60;


 } elseif (preg_match('/(\d+)h/', $type, $m)) {

   $total=(int)$m[1];
   $period=round(($total*60*60)/(($w-80)/$px_per_point)); // seconds
   $start_time=$end_time-$total*60*60;

  } elseif (preg_match('/(\d+)m/', $type, $m)) {

   $total=(int)$m[1];
   $period=round(($total*31*24*60*60)/(($w-80)/$px_per_point)); // seconds
   $start_time=$end_time-$total*31*24*60*60;

  } elseif (preg_match('/(\d+)\/(\d+)\/(\d+)/', $_GET['start'], $m) && $_GET['interval']) {
   $period=(int)$_GET['interval']; //seconds
   $start_time=mktime(0, 0, 0, $m[2], $m[3], $m[1]);
   $total=1;
  }


  if ($total>0) {

   $px=0;
   $px_passed=0;

   $dt=date('Y-m-d', $start_time);

   while($start_time<$end_time) {

     $ph=SQLSelectOne("SELECT ID, VALUE FROM phistory WHERE VALUE_ID='".$pvalue['ID']."' AND ADDED<=('".date('Y-m-d H:i:s', $start_time)."') ORDER BY ADDED DESC LIMIT 1");
     if ($ph['ID']) {
      $values[]=(float)$ph['VALUE'];
     } else {
      $values[]=0;
     }

     if ($px_passed>30) {
      if (date('Y-m-d', $start_time)!=$dt) {
       $hours[]=date('d/m', $start_time);
       $dt=date('Y-m-d', $start_time);
      } else {
       $hours[]=date('H:i', $start_time);
      }
      $px_passed=0;
     } else {
      $hours[]='';
     }


     $start_time+=$period;
     $px+=$px_per_point;
     $px_passed+=$px_per_point;

   }

   $DataSet->AddPoint($values,"Serie1");  
   $DataSet->AddPoint($hours,"Serie3");  


  } else {

   $DataSet->AddPoint(0,"Serie1");
   $DataSet->AddPoint(0,"Serie3");
  
  }


  if ($_GET['op']=='values') {
   echo json_encode($values);
   exit;
  }


  if ($_GET['op']=='json') {
   //header("Content-type: text/json");
   $ret = array();
   $ret['VALUES']=$values;
   $ret['TIME']=$hours;
   echo json_encode($ret);
   exit;
  }


  $DataSet->AddAllSeries();  
  $DataSet->RemoveSerie("Serie3");  
  $DataSet->SetAbsciseLabelSerie("Serie3");  

  $DataSet->SetSerieName("24 hours","Serie1");  

  //$DataSet->SetYAxisName($p);  

  
  if ($unit) {
   $DataSet->SetYAxisUnit($unit);
  } else {
   $DataSet->SetYAxisUnit("°C");  
  }
  $DataSet->SetXAxisUnit("");  
   
  // Initialise the graph  


  if (!$height) {
   $h=210;
  } else {
   $h=(int)$height;
  }

  $Test = new pChart($w,$h);  

  if ($_GET['gcolor']=='red') {
   $Test->setColorPalette(0,220,50,50);
  } elseif ($_GET['gcolor']=='brown') {
   $Test->setColorPalette(0,220,140,100);
  } elseif ($_GET['gcolor']=='blue') {
   $Test->setColorPalette(0,100,140,220);
  } elseif ($_GET['gcolor']=='green') {
   $Test->setColorPalette(0,100,220,140);
  } elseif ($_GET['gcolor']=='orange') {
   $Test->setColorPalette(0,220,190,50);
  } else {

   if (SETTINGS_THEME=='light' || $_GET['bg']=='light') {
    $Test->setColorPalette(0,150,150,150);
   } else {
    $Test->setColorPalette(0,255,255,255);
   }
  }



  if (SETTINGS_THEME=='light' || $_GET['bg']=='light') {
   //$Test->drawGraphAreaGradient(132,153,172,50,TARGET_BACKGROUND);  
  } else {
   $Test->drawGraphAreaGradient(132,153,172,50,TARGET_BACKGROUND);  
  }


  $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",10);  

  if (SETTINGS_THEME=='light' || $_GET['bg']=='light') {
   if ($_GET['title']) {
    $Test->drawTitle(60,15,$_GET['title'],55,55,55);
   } else {
    $Test->drawTitle(60,15,$p,55,55,55);
   }
  } else {
   if ($_GET['title']) {
    $Test->drawTitle(60,15,$_GET['title'],250,250,250);
   } else {
    $Test->drawTitle(60,15,$p,250,250,250);
   }
  }


  if ($_GET['scale']=='zero') {
   $scale=SCALE_START0;
  } else {
   $scale=SCALE_NORMAL;
  }


  $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",8);  
  $Test->setGraphArea(60,20,$w-25,$h-30);  

  if (SETTINGS_THEME=='light' || $_GET['bg']=='light') {
   $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),$scale,100,100,100,TRUE,0,2);
   $Test->drawGraphAreaGradient(240,240,240,5);
  } else {
   $Test->drawGraphArea(213,217,221,FALSE);  
   $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),$scale,213,217,221,TRUE,0,2);  
   $Test->drawGraphAreaGradient(162,183,202,50);  
  }

     
  // Draw the line chart  
  $Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),2);  

  if ($_GET['gtype']=='curve') {
   $Test->drawCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription());
  } elseif ($_GET['gtype']=='bar') {
   $Test->drawBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),TRUE);
  } else {
   $Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());  
  }
  //
  

   
   
 // Render the picture  
 if (SETTINGS_THEME=='light' || $_GET['bg']=='light') {
  $Test->AddBorder(1, 200,200,200); 
 } else {
  $Test->AddBorder(1); 
 }



 $path_to_file='./cached/'.md5($_SERVER['REQUEST_URI']).'.png';
 imagepng($Test->Picture, $path_to_file);

 Header("Content-type:image/png");

    $fsize=filesize($path_to_file);
    header("Content-Length:".(string)$fsize);
    $buff_length=200*1024;
    if ($buff_length>$fsize) {
     $buff_length=$fsize;
    }
    if ($buff_length>0) {
     $fd=fopen($path_to_file,'rb');
     if ($fd) {
      while(!feof($fd)) {
       print fread($fd, $buff_length);
      }
      fclose($fd);
     }
    }

 //$Test->Render();


 $db->Disconnect(); // closing database connection