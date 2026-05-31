<?php  

/*
pChatr2 index.php v.1.0.1

------------------- Parameters

&objt=  - type of object (default "")
&ids=   - ID of sensor (default 1)
&label= - label of sensor (default "")
&unit=  - units (default "")
&theme=black - black theme

//Limits
max=    - max value (default "")
min=    - min value (default "")
middle= - middle value (default "")
limit=  - break color (default "")

//RGB active
&caR=   - R component color active (default 230)
&caG=   - G component color active (default 100)
&caB=   - B component color active (default 100)

//RGB passive
&cpR=   - R component color passive (default 100)
&cpG=   - G component color passive (default 100)
&cpB=   - B component color passive (default 100)

//default colors active
&dca=blue    - &caR=100&caG=160&caB=230
&dca=green   - &caR=100&caG=200&caB=100
&dca=magenta - &caR=200&caG=130&caB=200
&dca=red     - &caR=200&caG=100&caB=100
&dca=gray    - &caR=120&caG=120&caB=120
&dca=orange  - &caR=230&caG=150&caB=70

//default colors passive/alarm
&dcp=blue    - &cpR=100&cpG=160&cpB=230
&dcp=green   - &cpR=100&cpG=200&cpB=100
&dcp=magenta - &cpR=200&cpG=130&cpB=200
&dcp=red     - &cpR=200&cpG=100&cpB=100
&dcp=gray    - &cpR=120&cpG=120&cpB=120
&dcp=orange  - &cpR=230&cpG=150&cpB=70

//alerts
&ralert= - red alert
&oalert= - orange alert
&galert= - green alert
&balert= - blue alert
&dbalert= - deep blue alert

------------------- pChart2
<img src="/pChart2/...

------------------- Free/Creative Commons/Open Font
C:\_majordomo\htdocs\pChart\Fonts\...
marvosym.ttf       - electro
heydings_icons.ttf - lamp
pictogramz.ttf     - snow

------------------- objects
therm1 (value, metr)

level1 (value, metr, alert)

smartStick1 (value, alert)

sticker1a (value)
sticker1b (value)
sticker1c (value)

cloud (value)

pict1a
pict1b
pict1c

*/


//---------------------------- chdir  
 
 chdir('../');
 
//---------------------------- Standard inclusions    

include_once("./config.php");
include_once("./lib/loader.php");
include_once(DIR_MODULES."application.class.php");
include("./pChart/pData.class");   
include("./pChart/pChart.class");  

//---------------------------- MySQL

$settings=SQLSelect("SELECT NAME, VALUE FROM settings");

$total=count($settings);
for($i=0;$i<$total;$i++) {
  Define('SETTINGS_'.$settings[$i]['NAME'], $settings[$i]['VALUE']);
}

//---------------------------- light/black

if (isset($_GET['theme'])) {
  $black=1;
} else {
   $black=0;
  }  

//---------------------------- Type of object  

if ($_GET['objt']) {
  $objtype=$_GET['objt'];
} else {
   $objtype="";
  }  
  
//---------------------------- ID of sensor 
  
if ($_GET['ids']) {
  $idsens=$_GET['ids'];
} else {
   $idsens=0;
  }
  
//---------------------------- Label of object 
  
if ($_GET['label']) {
  $label=$_GET['label'];
} else {
   $label="";
  } 

  //---------------------------- units
  
if ($_GET['unit']) {
  $unit=$_GET['unit'];
} else {
   $unit="";
  } 
  
//---------------------------- Limits 
  
if (isset($_GET['max'])) {
  $max=$_GET['max'];
 } else {
   $max=-1;
 }   
  
 if (isset($_GET['min'])) {
  $min=$_GET['min'];
} else {
   $min=-1;
  }   

if (isset($_GET['middle'])) {
  $middle=$_GET['middle'];
} else {
   $middle=-1;
  }     

if (isset($_GET['limit'])) {
  $limit=$_GET['limit'];
} else {
   $limit=-1;
  }    
  
//---------------------------- Alerts 
  
if (isset($_GET['ralert'])) {
  $ralert=$_GET['ralert'];
} else {
   $ralert=-1;
  }   
  
 if (isset($_GET['oalert'])) {
  $oalert=$_GET['oalert'];
} else {
   $oalert=-1;
  }   

if (isset($_GET['galert'])) {
  $galert=$_GET['galert'];
} else {
   $galert=-1;
  }     

if (isset($_GET['balert'])) {
  $balert=$_GET['balert'];
} else {
   $balert=-1;
  }     

if (isset($_GET['dbalert'])) {
  $dbalert=$_GET['dbalert'];
} else {
   $dbalert=-1;
  }  
  
//---------------------------- Color acive    
  
if ($_GET['caR']) {
  $caR=$_GET['caR'];
} else {
    $caR=230;
  }
        
if ($_GET['caG']) {
  $caG=$_GET['caG'];
} else {
    $caG=100;
  }
        
if ($_GET['caB']) {
    $caB=$_GET['caB'];
} else {
    $caB=100;
  }

//---------------------------- Color passive/alarm
        
if ($_GET['cpR']) {
    $cpR=$_GET['cpR'];
} else {
    $cpR=100;
  }
        
if ($_GET['cpG']) {
    $cpG=$_GET['cpG'];
} else {
    $cpG=100;
  }
        
if ($_GET['cpB']) {
    $cpB=$_GET['cpB'];
} else {
    $cpB=100;
  }

//---------------------------- Default colors active

if ($_GET['dca']=='blue') {
  $caR=100;$caG=160;$caB=230;
} elseif ($_GET['dca']=='green') {
  $caR=100;$caG=200;$caB=100;
} elseif ($_GET['dca']=='magenta') {
  $caR=200;$caG=130;$caB=200;
} elseif ($_GET['dca']=='red') {
  $caR=200;$caG=100;$caB=100;
} elseif ($_GET['dca']=='gray') {
  $caR=120;$caG=120;$caB=120;
} elseif ($_GET['dca']=='orange') {
  $caR=230;$caG=150;$caB=70;
} else {
  }

//---------------------------- Default colors passive/alarm

if ($_GET['dcp']=='blue') {
  $cpR=100;$cpG=160;$cpB=230;
} elseif ($_GET['dcp']=='green') {
  $cpR=100;$cpG=200;$cpB=100;
} elseif ($_GET['dcp']=='magenta') {
  $cpR=200;$cpG=130;$cpB=200;
} elseif ($_GET['dcp']=='red') {
  $cpR=200;$cpG=100;$cpB=100;
} elseif ($_GET['dcp']=='gray') {
  $cpR=120;$cpG=120;$cpB=120;
} elseif ($_GET['dcp']=='orange') {
  $cpR=230;$cpG=150;$cpB=70;
} else {
  }  
  
//---------------------------- SQL q

  if ($p!='') {
   if (preg_match('/(.+)\.(.+)/is', $p, $m)) {
    $obj=getObject($m[1]);
    $prop_id=$obj->getPropertyByName($m[2], $obj->class_id, $obj->id);
   }
   $pvalue=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id."' AND OBJECT_ID='".$obj->id."'");
  }

  if ($idsens) {
   $pvalue=SQLSelectOne("SELECT * FROM pvalues WHERE pvalues.ID='$idsens'");
  }

  if ($max==-1) {
   $tmp=SQLSelectOne("SELECT MAX(CAST(VALUE AS DECIMAL(10,2))) as MAX_VALUE FROM phistory WHERE VALUE_ID='".$pvalue['ID']."'");
   if (isset($tmp['MAX_VALUE'])) {
    $max=round($tmp['MAX_VALUE'], 2);
   }
  }
  if ($min==-1) {
   $tmp=SQLSelectOne("SELECT MIN(CAST(VALUE AS DECIMAL(10,2))) as MIN_VALUE FROM phistory WHERE VALUE_ID='".$pvalue['ID']."'");
   if (isset($tmp['MIN_VALUE'])) {
    $min=round($tmp['MIN_VALUE'], 2);
   }
  }

  if ($middle==-1) {
   $middle=round(($max-$min)/2, 2);
  }



  $currentValue=$pvalue['VALUE'];  
  //$currentValue=28;
  
//======================================================== Objects

  
//---------------------------- generic

if ($objtype == "generic") {

// end of generic
                 
//---------------------------- therm1
        
  } elseif ($objtype == "therm1") {     
     // Settings
     $wid=70;
         $hei=154;
        
         // light/black
     if($black==1){
           $cbg=0;
         }else{
           $cbg=255;
           }
                 
     // Object + GraphArea       
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei);  
        
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);
        
     // Body
     $Test->drawFilledRoundedRectangle(10,10,36,130,8,200,200,200); // body
     $Test->drawFilledCircle(24,130,23,200,200,200,0); // circle body
     $Test->drawFilledRectangle(17,15,29,110,240,240,240,FALSE,100); // white line
        
     // Limits
     if ($max!=-1){
       $pmax=$max;
     }else{
       $pmax="";
     }
   
     if ($min!=-1){
       $pmin=$min;
     }else{
       $pmin="";
     }
   
     if ($middle!=-1){
       $pmiddle=$middle;
     }else{
       $pmiddle="";
     }
   
     // min/max
     $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",10);
     $Test->drawTextBox(45,10,60,30,"$pmax",0,255,100,100,ALIGN_LEFT,FALSE,-1,-1,-1,100);
     $Test->drawTextBox(45,50,60,70,"$pmiddle",0,100,100,100,ALIGN_LEFT,FALSE,-1,-1,-1,100);
     $Test->drawTextBox(40,90,55,110,"$pmin",0,100,100,255,ALIGN_LEFT,FALSE,-1,-1,-1,100);
  
     // Color break limit
     $cmR=$caR; $cmG=$caG; $cmB=$caB;
   
     if ($limit!=-1){
       if ($currentValue > $limit) {
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } else {
         $cmR=$cpR; $cmG=$cpG; $cmB=$cpB;
       } 
     }
        
     $Test->drawFilledCircle(24,130,16,$cmR,$cmG,$cmB,0); // circle
     $Test->drawFilledRectangle(17,60-$currentValue*1.0,29,120,$cmR,$cmG,$cmB,FALSE,100);
   
      // Marks
     $Test->drawLine(14,20,32,20,150,150,150,FALSE);
     $Test->drawLine(14,40,32,40,150,150,150,FALSE);
     $Test->drawLine(14,60,32,60,150,150,150,FALSE);
     $Test->drawLine(14,80,32,80,150,150,150,FALSE);
     $Test->drawLine(14,100,32,100,150,150,150,FALSE);
     
     // Current temp
     $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",9);
     $Test->drawTextBox(10,122,38,140,"$currentValue"."$unit",0,255,255,255,ALIGN_CENTER,FALSE,-1,-1,-1,100);   
   
  // end of therm1
                 
//---------------------------- level1
        
  } elseif ($objtype == "level1") {     

     // Settings
     $wid=100; 
         $hei=200;
         
         // light/black
     if($black==1){
           $cbg=0;
         }else{
           $cbg=255;
           }
  
     $bodyXmin=20; $bodyXmax=70;
     $bodyYmin=0; $bodyYmax=170;
  
     $metrXmin=26; $metrXmax=64;
     $metrYmin=6; $metrYmax=164;
  
     $metrDiap=$metrYmax-$metrYmin;
     $metrCenter=$metrDiap/2;
  
     // Object + GraphArea       
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei);  
        
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);
        
     // Body
     $Test->drawFilledRoundedRectangle($bodyXmin,$bodyYmin,$bodyXmax,$bodyYmax,8,200,200,200); // body rounded
     //$Test->drawFilledRectangle($bodyXmin,$bodyYmin,$bodyXmax,$bodyYmax,200,200,200,FALSE,100); // body rectangle
     $Test->drawFilledRectangle($metrXmin,$metrYmin,$metrXmax,$metrYmax,245,245,245,FALSE,100); // white line
        
     // Limits
     if ($max!=-1){
       $pmax=$max;
     }else{
       $pmax="";
     }
   
     if ($min!=-1){
       $pmin=$min;
     }else{
       $pmin="";
     }
   
     if ($middle!=-1){
       $pmiddle=$middle;
     }else{
       $pmiddle="";
     }
   
     if ($max!=-1 and $min!=-1){
       $valDiap=$max-$min;
       $k=$valDiap/$metrDiap;  
           $realVol=$metrYmax-($currentValue-$min)/$k;
     }else{
       $valDiap=-1;
           $k=-1;
       $realVol=$metrYmin;
     }  
   
     // max/min
     $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",8);
     //$Test->drawTextBox(55,5,60,15,"$pmax"."$unit",0,255,100,100,ALIGN_LEFT,FALSE,-1,-1,-1,100);
     //$Test->drawTextBox(55,80,60,90,"$pmiddle"."$unit",0,100,100,100,ALIGN_LEFT,FALSE,-1,-1,-1,100);
     //$Test->drawTextBox(55,160,60,170,"$pmin"."$unit",0,100,100,255,ALIGN_LEFT,FALSE,-1,-1,-1,100);
  
     // Color break limit
     $cmR=$caR; $cmG=$caG; $cmB=$caB;
   
     if ($limit!=-1){
       if ($currentValue > $limit) {
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } else {
         $cmR=$cpR; $cmG=$cpG; $cmB=$cpB;
       } 
     }
         
         // draw alerts
     if ($ralert!=-1 && $oalert!=-1 && $galert!=-1 && $balert!=-1 && dbalert!=-1)       {
       $caR=100;$caG=200;$caB=100;
       $cmR=$caR; $cmG=$caG; $cmB=$caB;
           $realGreen=$metrYmax-($galert-$min)/$k;
           $Test->drawTextBox($bodyXmax+5,$realGreen-3,60,$realGreen,"$galert"."$unit",0,$cmR,$cmG,$cmB,ALIGN_LEFT,FALSE,-1,-1,-1,100);
         
       $caR=100;$caG=160;$caB=230;
       $cmR=$caR; $cmG=$caG; $cmB=$caB;
           $realBlue=$metrYmax-($balert-$min)/$k;
           $Test->drawTextBox($bodyXmax+5,$realBlue-3,60,$realBlue,"$balert"."$unit",0,$cmR,$cmG,$cmB,ALIGN_LEFT,FALSE,-1,-1,-1,100);

       $caR=80;$caG=80;$caB=180;
       $cmR=$caR; $cmG=$caG; $cmB=$caB;
       $realDeepBlue=$metrYmax-($dbalert-$min)/$k;
           $Test->drawTextBox($bodyXmax+5,$realDeepBlue-3,60,$realDeepBlue,"$dbalert"."$unit",0,$cmR,$cmG,$cmB,ALIGN_LEFT,FALSE,-1,-1,-1,100);
         
       $caR=230;$caG=150;$caB=70;
       $cmR=$caR; $cmG=$caG; $cmB=$caB;
           $realOrange=$metrYmax-($oalert-$min)/$k;
           $Test->drawTextBox($bodyXmax+5,$realOrange-3,60,$realOrange+5,"$oalert"."$unit",0,$cmR,$cmG,$cmB,ALIGN_LEFT,FALSE,-1,-1,-1,100);

       $caR=200;$caG=100;$caB=100;
       $cmR=$caR; $cmG=$caG; $cmB=$caB;
       $realRed=$metrYmax-($ralert-$min)/$k;
           $Test->drawTextBox($bodyXmax+5,$realRed-3,60,$realRed,"$ralert"."$unit",0,$cmR,$cmG,$cmB,ALIGN_LEFT,FALSE,-1,-1,-1,100);
     }
   
     // Color of metr
     if($currentValue > $min and $currentValue < $max){ // green
       $caR=100;$caG=200;$caB=100;
       $cmR=$caR; $cmG=$caG; $cmB=$caB;
     } elseif ($currentValue < $min){ // blue
       $caR=100;$caG=160;$caB=230;
       $cmR=$caR; $cmG=$caG; $cmB=$caB;
     } elseif ($currentValue > $max){ // red
       $caR=200;$caG=100;$caB=100;
       $cmR=$caR; $cmG=$caG; $cmB=$caB;
     } else {
       $caR=120;$caG=120;$caB=120; // gray
       $cmR=$caR; $cmG=$caG; $cmB=$caB;
     }
         
         // Color alert of metr
     if ($ralert!=-1 && $oalert!=-1 && $galert!=-1 && $balert!=-1 && dbalert!=-1)       {
       if($currentValue > $balert and $currentValue < $oalert){ // green
         $caR=100;$caG=200;$caB=100;
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } elseif ($currentValue <= $balert and $currentValue > $dbalert){ // blue
         $caR=100;$caG=160;$caB=230;
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } elseif ($currentValue <= $dbalert){ // deep blue
         $caR=80;$caG=80;$caB=180;
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } elseif ($currentValue >= $oalert and $currentValue < $ralert){ // orange
         $caR=230;$caG=150;$caB=70;
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } elseif ($currentValue >= $ralert){ // red
         $caR=200;$caG=100;$caB=100;
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } else {
         $caR=120;$caG=120;$caB=120; // gray
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       }
     }

         // limits draw
         if ($realVol < $metrYmin) {
           $realVol=$metrYmin;
        }
         if ($realVol > $metrYmax) {
       $realVol=$metrYmax;
     }
         
         // Y pos of Value
     $yVal=$metrYmax-($metrYmax-$realVol)/2;
        
     // draw metr
     $Test->drawFilledRectangle($metrXmin,$realVol,$metrXmax,$metrYmax,$cmR,$cmG,$cmB,FALSE,100);
     //$Test->drawFilledRectangle($metrXmin,$metrCenter-$mVal,$metrXmax,$metrYmax,$cmR,$cmG,$cmB,FALSE,100);

     // Current value
     $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",9);
     $Test->drawTextBox($bodyXmin,$yVal,$bodyXmax,$yVal+5,"$currentValue"."$unit",0,255,255,255,ALIGN_CENTER,FALSE,-1,-1,-1,100);

     // Draw label
     $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",10);
     $Test->drawTextBox($bodyXmin,185,$bodyXmax,190,"$label",0,140,140,140,ALIGN_CENTER,FALSE,-1,-1,-1,100);   

  // end of level1
   
//---------------------------- smartStick1
        
  } elseif ($objtype == "smartStick1") {        

  // Settings
     $wid=75; 
         $hei=55;
     
         // light/black
     if($black==1){
           $cbg=0;
         }else{
           $cbg=255;
           }
  
     $bodyXmin=0; $bodyXmax=70;
     $bodyYmin=0; $bodyYmax=50;
  
     $metrXmin=26; $metrXmax=64;
     $metrYmin=6; $metrYmax=36;
  
     $metrDiap=$metrYmax-$metrYmin;
     $metrCenter=$metrDiap/2;
  
     // Object + GraphArea       
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei);  
        
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);
        
     // Body
     //$Test->drawFilledRoundedRectangle($bodyXmin+3,$bodyYmin+3,$bodyXmax+3,$bodyYmax+3,8,200,200,200); // shadow
     $Test->drawFilledRoundedRectangle($bodyXmin,$bodyYmin,$bodyXmax,$bodyYmax,8,100,100,100); // body
        
     // Limits
     if ($max!=-1){
       $pmax=$max;
     }else{
       $pmax="";
     }
   
     if ($min!=-1){
       $pmin=$min;
     }else{
       $pmin="";
     }
   
     if ($middle!=-1){
       $pmiddle=$middle;
     }else{
       $pmiddle="";
     }
   
     if ($max!=-1 and $min!=-1){
       $valDiap=$max-$min;
           $k=$valDiap/$metrDiap;  
           $realVol=$metrYmax-($currentValue-$min)/$k;
     }else{
       $valDiap=-1;
           $k=-1;
           $realVol=$metrYmin;
     }  

     // min/max
     $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",8);
     //$Test->drawTextBox(55,5,60,15,"$pmax"."$unit",0,255,100,100,ALIGN_LEFT,FALSE,-1,-1,-1,100);
     //$Test->drawTextBox(55,80,60,90,"$pmiddle"."$unit",0,100,100,100,ALIGN_LEFT,FALSE,-1,-1,-1,100);
     //$Test->drawTextBox(55,160,60,170,"$pmin"."$unit",0,100,100,255,ALIGN_LEFT,FALSE,-1,-1,-1,100);
  
     // Color break limit
     $cmR=$caR; $cmG=$caG; $cmB=$caB;
   
     if ($limit!=-1){
       if ($currentValue > $limit) {
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } else {
         $cmR=$cpR; $cmG=$cpG; $cmB=$cpB;
       } 
     }

     if($currentValue > $min and $currentValue < $max){ // green
       $caR=100;$caG=200;$caB=100;
       $cmR=$caR; $cmG=$caG; $cmB=$caB;
     } elseif ($currentValue < $min){ // blue
       $caR=100;$caG=160;$caB=230;
       $cmR=$caR; $cmG=$caG; $cmB=$caB;
     } elseif ($currentValue > $max){ // red
       $caR=200;$caG=100;$caB=100;
       $cmR=$caR; $cmG=$caG; $cmB=$caB;
     } else {
       $caR=120;$caG=120;$caB=120; // gray
       $cmR=$caR; $cmG=$caG; $cmB=$caB;
     }
         
     if ($ralert!=-1 && $oalert!=-1 && $galert!=-1 && $balert!=-1 && dbalert!=-1)       {
       if($currentValue > $balert and $currentValue < $oalert){ // green
         $caR=100;$caG=200;$caB=100;
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } elseif ($currentValue <= $balert and $currentValue > $dbalert){ // blue
         $caR=100;$caG=160;$caB=230;
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } elseif ($currentValue <= $dbalert){ // deep blue
         $caR=80;$caG=80;$caB=180;
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } elseif ($currentValue >= $oalert and $currentValue < $ralert){ // orange
         $caR=230;$caG=150;$caB=70;
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } elseif ($currentValue >= $ralert){ // red
         $caR=200;$caG=100;$caB=100;
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } else {
         $caR=120;$caG=120;$caB=120; // gray
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       }
     }

         // limita draw
         if ($realVol < $metrYmin) {
           $realVol=$metrYmin;
     }
         
         if ($realVol > $metrYmax) {
           $realVol=$metrYmax;
     }
         
         // Y pos of Value
         $yVal=$metrYmax-($metrYmax-$realVol)/2;
        
     $Test->drawFilledRoundedRectangle($bodyXmin,$bodyYmin,$bodyXmax,$metrYmax,8,$cmR,$cmG,$cmB);
     $Test->drawFilledRectangle($bodyXmin,$bodyYmin+30,$bodyXmax,$bodyYmax-10,100,100,100,FALSE,100); // body
     
     // Current value
     $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",12);
     $Test->drawTextBox($bodyXmin,$bodyYmin+15,$bodyXmax,$bodyYmin+20,"$currentValue"."$unit",0,245,245,245,ALIGN_CENTER,FALSE,-1,-1,-1,100);

     // Draw label
     $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",8);
     $Test->drawTextBox($bodyXmin,$bodyYmax-15,$bodyXmax,$bodyYmax-7,"$label",0,255,255,255,ALIGN_CENTER,FALSE,-1,-1,-1,100);   

  //end of smartStick1
   
//---------------------------- sticker1a
         
  } elseif ($objtype == "sticker1a") {

     // Settings
         $wid=100;
         $hei=100;
         
     // light/black
     if($black==1){
           $cbg=0;
         }else{
           $cbg=255;
           }
                 
         // Object + GraphArea
         $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
                 
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);

     // Draw body
         $Test->drawFilledRoundedRectangle(0,0,100,100,8,$caR,$caG,$caB); // body
     $Test->drawLine(10,60,90,60,220,220,220,FALSE); // line
  
         // Draw value
     $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",24);
     $Test->drawTextBox(20,20,90,40,"$currentValue"."$unit",0,250,250,250,ALIGN_CENTER,FALSE,-1,-1,-1,100);

         // Draw label
     $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",13);
         $Test->drawTextBox(10,70,90,80,"$label",0,250,250,250,ALIGN_CENTER,FALSE,-1,-1,-1,100);

  // end of sticker1a
                 
//---------------------------- sticker1b
         
  } elseif ($objtype == "sticker1b") {
  
     // Settings
         $wid=60; 
         $hei=60;
         
         // light/black
     if($black==1){
           $cbg=0;
         }else{
           $cbg=255;
           }
                 
         // Object + GraphArea
         $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
                 
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);

     // Draw body
         $Test->drawFilledRoundedRectangle(0,0,60,60,8,$caR,$caG,$caB); // body
     $Test->drawLine(7,35,53,35,220,220,220,FALSE); // line
  
         // Draw value
     $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",13);
     $Test->drawTextBox(10,13,55,23,"$currentValue"."$unit",0,250,250,250,ALIGN_CENTER,FALSE,-1,-1,-1,100);

         // Draw label
     $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",8);
         $Test->drawTextBox(10,42,50,50,"$label",0,250,250,250,ALIGN_CENTER,FALSE,-1,-1,-1,100);        

  // end of sticker1b            

//---------------------------- sticker1c
         
  } elseif ($objtype == "sticker1c") {
 
     // Settings
         $wid=50;
         $hei=50;
         
         // light/black
     if($black==1){
           $cbg=0;
         }else{
           $cbg=255;
           }
                 
         // Object + GraphArea
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
                 
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);

     // Draw body
     $Test->drawFilledRoundedRectangle(0,0,50,50,8,$caR,$caG,$caB); // body
     $Test->drawLine(6,30,44,30,220,220,220,FALSE); // line
  
     // Draw value
     $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",12);
     $Test->drawTextBox(8,10,45,23,"$currentValue"."$unit",0,250,250,250,ALIGN_CENTER,FALSE,-1,-1,-1,100);

         // Draw label
     $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",8);
     $Test->drawTextBox(8,32,42,46,"$label",0,250,250,250,ALIGN_CENTER,FALSE,-1,-1,-1,100);             
                 
  // end of sticker1c
                 
//---------------------------- cloud
         
  } elseif ($objtype == "cloud") {
  
     // Settings
         $wid=50;
         $hei=50;
         
         // light/black
     if($black==1){
           $cbg=0;
         }else{
           $cbg=255;
           }
                 
         // Object + GraphArea
         $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
                 
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);

     // Draw body
         $Test->drawFilledRoundedRectangle(0,30,50,50,8,$caR,$caG,$caB); // body
     //$Test->drawLine(6,30,44,30,220,220,220,FALSE); // line
  
         // Draw value
     $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",12);
     $Test->drawTextBox(8,10,45,23,"$currentValue"."$unit",0,120,120,120,ALIGN_CENTER,FALSE,-1,-1,-1,100);

         // Draw label
     $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",8);
         $Test->drawTextBox(8,32,42,46,"$label",0,250,250,250,ALIGN_CENTER,FALSE,-1,-1,-1,100);                          
                 
     // end of cloud

//---------------------------- pict1a 
         
  } elseif ($objtype == "pict1a") {
  
     // Settings
         $wid=60;
         $hei=60;
         
     // light/black
     if($black==1){
           $cbg=0;
         }else{
           $cbg=255;
           }
                 
         // Object + GraphArea
         $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
                 
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);

     // Draw body
         $Test->drawFilledRoundedRectangle(0,0,60,60,8,$caR,$caG,$caB); // body

         // Draw label
     $Test->setFontProperties("./pChart/Fonts/marvosym.ttf",32);
         $Test->drawTextBox(10,20,50,40,"E",0,250,250,250,ALIGN_CENTER,FALSE,-1,-1,-1,100);             
                 
  // end of pict1a

//---------------------------- pict1b
         
  } elseif ($objtype == "pict1b") {

     // Settings
         $wid=60;
         $hei=60;
         
     // light/black
     if($black==1){
           $cbg=0;
         }else{
           $cbg=255;
           }
                 
         // Object + GraphArea
         $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
                 
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);

     // Draw body
         $Test->drawFilledRoundedRectangle(0,0,60,60,8,$caR,$caG,$caB); // body

         // Draw label
     $Test->setFontProperties("./pChart/Fonts/heydings_icons.ttf",32);
         $Test->drawTextBox(10,20,50,40,"l",0,250,250,250,ALIGN_CENTER,FALSE,-1,-1,-1,100);                     
                 
         // end of pict1b

//---------------------------- pict1c 
         
  } elseif ($objtype == "pict1c") {
  
     // Settings
         $wid=60;
         $hei=60;

         // light/black
     if($black==1){
           $cbg=0;
         }else{
           $cbg=255;
           }
                 
         // Object + GraphArea
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
                 
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);

     // Draw body
     $Test->drawFilledRoundedRectangle(0,0,60,60,8,$caR,$caG,$caB); // body

         // Draw label
     $Test->setFontProperties("./pChart/Fonts/pictogramz.ttf",44);
     $Test->drawTextBox(10,20,50,40,"O",0,250,250,250,ALIGN_CENTER,FALSE,-1,-1,-1,100);                          
                 
  // end of pict1c
         
//---------------------------- Error mess object
        
        } else {
   
    } // end of ($objtype == "...")

//---------------------------- Final
        
 Header("Content-type:image/png");
 imagepng($Test->Picture);
