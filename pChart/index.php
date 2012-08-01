<?php   
 chdir('../');
 /*
     Example1 : A simple line chart
 */

 include_once("./config.php");
 include_once("./lib/loader.php");


 include_once(DIR_MODULES."application.class.php");

 $db=new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME); // connecting to database

 $settings=SQLSelect("SELECT NAME, VALUE FROM settings");
 $total=count($settings);
 for($i=0;$i<$total;$i++) {
  Define('SETTINGS_'.$settings[$i]['NAME'], $settings[$i]['VALUE']);
 }


 // Standard inclusions      
 include("./pChart/pData.class");   
 include("./pChart/pChart.class");   
  
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

  if (preg_match('/(\d+)h/', $type, $m)) {
   $total=(int)$m[1];
   for($i=0;$i<$total;$i++) {
    $dt=time()+($i-($total-1))*60*60;
    $new_dt=mktime(date('H', $dt), 0, 0, date('m', $dt), date('d', $dt), date('Y', $dt));
    $dt=$new_dt;
    $h=date('H', $dt);

    for($t=0;$t<60;$t+=10) {

     $ph=SQLSelectOne("SELECT ID, VALUE FROM phistory WHERE VALUE_ID='".$pvalue['ID']."' AND ADDED<=('".date('Y-m-d H:i:s', $dt+$t*60)."') ORDER BY ADDED DESC");
     if ($ph['ID']) {
      $values[]=$ph['VALUE'];
     } else {
      $values[]=0;
     }
     $hours[]=$h;
     $h='';
    }

   }





   $DataSet->AddPoint($values,"Serie1");  
   $DataSet->AddPoint($hours,"Serie3");  

  } elseif (preg_match('/(\d+)m/', $type, $m)) {

   $total=(int)$m[1];
   for($i=0;$i<$total;$i++) {
    $dt=time()+($i-($total-2))*60;
    $new_dt=mktime(date('H', $dt), date('i', $dt), 0, date('m', $dt), date('d', $dt), date('Y', $dt));
    $dt=$new_dt;
    if (($i+1)%10==0) {
     $m=date('h:i', $dt);
    } else {
     $m='';
    }
    $minutes[]=$m;
    $ph=SQLSelectOne("SELECT ID, VALUE FROM phistory WHERE VALUE_ID='".$pvalue['ID']."' AND ADDED<=('".date('Y-m-d H:i:s', $dt)."') ORDER BY ADDED DESC");
    if ($ph['ID']) {
     $values[]=$ph['VALUE'];
    } else {
     $values[]=0;
    }
   }
   $DataSet->AddPoint($values,"Serie1");  
   $DataSet->AddPoint($minutes,"Serie3");  

  } else {
   $DataSet->AddPoint(0,"Serie1");
   $DataSet->AddPoint(0,"Serie3");

//   $DataSet->AddPoint(array(1,4,-3,2,-3,3,2,1,0,7,4,-3,2,-3,3,5,1,0,7),"Serie1");
 //  $DataSet->AddPoint(array(2,5,7,5,1,5,6,4,8,4,0,2,5,6,4,5,6,7,6),"Serie3");
  }


  $DataSet->AddAllSeries();  
  $DataSet->RemoveSerie("Serie3");  
  $DataSet->SetAbsciseLabelSerie("Serie3");  

  $DataSet->SetSerieName("24 hours","Serie1");  

  $DataSet->SetYAxisName($p);  
  if ($unit) {
   $DataSet->SetYAxisUnit($unit);
  } else {
   $DataSet->SetYAxisUnit("°C");  
  }
  $DataSet->SetXAxisUnit("");  
   
  // Initialise the graph  

  if (!$width) {
   $w=610;
  } else {
   $w=(int)$width;
  }

  if (!$height) {
   $h=210;
  } else {
   $h=(int)$height;
  }

  $Test = new pChart($w,$h);  

  $Test->setColorPalette(0,255,255,255);

  $Test->drawGraphAreaGradient(132,153,172,50,TARGET_BACKGROUND);  
  $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",8);  
  $Test->setGraphArea(60,20,$w-25,$h-30);  
  $Test->drawGraphArea(213,217,221,FALSE);  
  $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,213,217,221,TRUE,0,2);  
  $Test->drawGraphAreaGradient(162,183,202,50);  

  if (count($values)<=30) {
   $Test->drawGrid(4,TRUE,230,230,230,20);  
  }

     
  // Draw the line chart  
  $Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());  
  //$Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),2);  
   
   
  // Render the picture  
  $Test->AddBorder(1); 

 Header("Content-type:image/png");
 imagepng($Test->Picture);
 //$Test->Render();


 $db->Disconnect(); // closing database connection