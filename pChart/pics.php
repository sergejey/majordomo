<?php  

/*
pChart pics.php v.1.0.0

//------------------- Parameters

&objt=   - type of object (!)
&label=  - label of sensor (-*)
&width=  - ../100*
&height= - ../50*
&unit=   - ../-*
&theme=  - black/-*
$health= - 1..100/100* health of object
&blackc= - 1..255/0* color of black background

//------------------- Colors

//RGB active
&caR=    - R component color active (230*)
&caG=    - G component color active (100*)
&caB=    - B component color active (100*)

//RGB passive
&cpR=    - R component color passive (100*)
&cpG=    - G component color passive (100*)
&cpB=    - B component color passive (100*)

//default RGB colors active
&dca=blue    - &caR=100&caG=160&caB=230
&dca=green   - &caR=100&caG=200&caB=100
&dca=magenta - &caR=200&caG=130&caB=200
&dca=red     - &caR=200&caG=100&caB=100
&dca=gray    - &caR=120&caG=120&caB=120
&dca=orange  - &caR=230&caG=150&caB=70

//default RGB colors passive/alarm
&dcp=blue    - &cpR=100&cpG=160&cpB=230
&dcp=green   - &cpR=100&cpG=200&cpB=100
&dcp=magenta - &cpR=200&cpG=130&cpB=200
&dcp=red     - &cpR=200&cpG=100&cpB=100
&dcp=gray    - &cpR=120&cpG=120&cpB=120
&dcp=orange  - &cpR=230&cpG=150&cpB=70

//------------------- Alerts

&a5= - red alert
&a4= - orange alert
&a3= - green alert
&a2= - blue alert
&a1= - deep blue alert

//------------------- Limits

max=    - max value (-*)
min=    - min value (-*)
middle= - middle value (-*)
limit=  - break color (-*)

------------------- Free/Creative Commons/Open Fonts
C:\_majordomo\htdocs\pChart\Fonts\...

//------------------- Notes

! - required
* - by default
- - none
*/


//---------------------------- Standard inclusions    

chdir('../');
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

//---------------------------- Settings 

$settings=SQLSelect("SELECT NAME, VALUE FROM settings");
$total=count($settings);
for($i=0;$i<$total;$i++) {
  Define('SETTINGS_'.$settings[$i]['NAME'], $settings[$i]['VALUE']);
}

//---------------------------- Type of object  

if ($_GET['objt']) {$objtype=$_GET['objt'];} 
 else {$objtype="";}  

//---------------------------- Light/black

if (isset($_GET['theme'])) {$black=1;} 
 else {$black=0;}    
  
//---------------------------- Black background color  

if ($_GET['blackc']) {$blackc=$_GET['blackc'];}
 else {$blackc=0;}    
 
//---------------------------- Width & height of graphics
 
if (isset($_GET['width'])) {$width=$_GET['width'];} 
 else {$width=100;} 
 
if (isset($_GET['height'])) {$width=$_GET['height'];} 
 else {$height=50;}    

//---------------------------- Health of object

if (isset($_GET['health'])) {$health=$_GET['health'];} 
 else {$health=100;}   
  
//---------------------------- Label of object 
  
if ($_GET['label']) {$label=$_GET['label'];}
 else {$label="";} 

//---------------------------- Units
  
if ($_GET['unit']) {$unit=$_GET['unit'];}
 else {$unit="";} 
  
//---------------------------- Color acive    
  
if ($_GET['caR']) {$caR=$_GET['caR'];} else {$caR=230;}
if ($_GET['caG']) {$caG=$_GET['caG'];} else {$caG=100;}
if ($_GET['caB']) {$caB=$_GET['caB'];} else {$caB=100;}

//---------------------------- Color passive/alarm
	
if ($_GET['cpR']) {$cpR=$_GET['cpR'];} else {$cpR=100;}
if ($_GET['cpG']) {$cpG=$_GET['cpG'];} else {$cpG=100;}
if ($_GET['cpB']) {$cpB=$_GET['cpB'];} else {$cpB=100;}

//---------------------------- Default colors active

if      ($_GET['dca']=='blue')   {$caR=100;$caG=160;$caB=230;} 
 elseif ($_GET['dca']=='green')  {$caR=100;$caG=200;$caB=100;} 
 elseif ($_GET['dca']=='magenta'){$caR=200;$caG=130;$caB=200;}
 elseif ($_GET['dca']=='red')    {$caR=200;$caG=100;$caB=100;} 
 elseif ($_GET['dca']=='gray')   {$caR=120;$caG=120;$caB=120;} 
 elseif ($_GET['dca']=='orange') {$caR=230;$caG=150;$caB=70;} 
 else { }

//---------------------------- Default colors passive/alarm

if      ($_GET['dcp']=='blue')   {$cpR=100;$cpG=160;$cpB=230;}
 elseif ($_GET['dcp']=='green')  {$cpR=100;$cpG=200;$cpB=100;}
 elseif ($_GET['dcp']=='magenta'){$cpR=200;$cpG=130;$cpB=200;}
 elseif ($_GET['dcp']=='red')    {$cpR=200;$cpG=100;$cpB=100;}
 elseif ($_GET['dcp']=='gray')   {$cpR=120;$cpG=120;$cpB=120;}
 elseif ($_GET['dcp']=='orange') {$cpR=230;$cpG=150;$cpB=70;}
 else { }  

//---------------------------- Limits 
  
if (isset($_GET['max']))   {$max=$_GET['max'];} else {$max=-1;}     
if (isset($_GET['min']))   {$min=$_GET['min'];} else {$min=-1;}   
if (isset($_GET['middle'])){$middle=$_GET['middle'];} else {$middle=-1;}     
if (isset($_GET['limit'])) {$limit=$_GET['limit'];} else {$limit=-1;}    
  
//---------------------------- Alerts 
  
if (isset($_GET['a5'])) {$a5=$_GET['a5'];} else {$a5=-1;}   
if (isset($_GET['a4'])) {$a4=$_GET['a4'];} else {$a4=-1;}   
if (isset($_GET['a3'])) {$a3=$_GET['a3'];} else {$a3=-1;}     
if (isset($_GET['a2'])) {$a2=$_GET['a2'];} else {$a2=-1;}     
if (isset($_GET['a1'])) {$a1=$_GET['a1'];} else {$a1=-1;}  
 
//---------------------------- SQL query

if ($p!='') {
  if (preg_match('/(.+)\.(.+)/is', $p, $m)) {
    $obj=getObject($m[1]);
    $prop_id=$obj->getPropertyByName($m[2], $obj->class_id, $obj->id);
  }
  $pvalue=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id."' AND OBJECT_ID='".$obj->id."'");
}
$currentValue=$pvalue['VALUE'];  
  
//-------------------------------------------------------- generic

if ($objtype=="generic") {

// end of generic

//-------------------------------------------------------- level1
	
  } elseif ($objtype == "level1") {	

     // Settings
     $wid=110; $hei=200;
	 
	 // light/black
     if($black==1){
	   $cbg=$blackc;
	 }else{
	   $cbg=255;
	   }
  
     $bodyXmin=22; $bodyXmax=70;
     $bodyYmin=0; $bodyYmax=170;
  
     $metrXmin=26; $metrXmax=66;
     $metrYmin=4; $metrYmax=166;
  
     $metrDiap=$metrYmax-$metrYmin;
     $metrCenter=$metrDiap/2;
  
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei);  
     $Test->drawBackground($cbg,$cbg,$cbg);
	
     // Body
     //$Test->drawFilledRoundedRectangle($bodyXmin,$bodyYmin,$bodyXmax,$bodyYmax,8,200,200,200); // body rounded
     $Test->drawFilledRectangle($bodyXmin,$bodyYmin,$bodyXmax,$bodyYmax,200,200,200,FALSE,100); // body rectangle
     $Test->drawFilledRectangle($metrXmin,$metrYmin,$metrXmax,$metrYmax,245,245,245,FALSE,100); // white line
	
     // Limits
     if ($max!=-1){$pmax=$max;} else {$pmax="";}
     if ($min!=-1){$pmin=$min;} else {$pmin="";}
   
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
     if ($a5!=-1 && $a4!=-1 && $a3!=-1 && $a2!=-1 && dbalert!=-1)	{
       $caR=100;$caG=200;$caB=100;
       $cmR=$caR; $cmG=$caG; $cmB=$caB;
	   $realGreen=$metrYmax-($a3-$min)/$k;
	   $Test->drawTextBox($bodyXmax+5,$realGreen-3,60,$realGreen,"$a3"."$unit",0,$cmR,$cmG,$cmB,ALIGN_LEFT,FALSE,-1,-1,-1,100);
	 
       $caR=100;$caG=160;$caB=230;
       $cmR=$caR; $cmG=$caG; $cmB=$caB;
	   $realBlue=$metrYmax-($a2-$min)/$k;
	   $Test->drawTextBox($bodyXmax+5,$realBlue-3,60,$realBlue,"$a2"."$unit",0,$cmR,$cmG,$cmB,ALIGN_LEFT,FALSE,-1,-1,-1,100);

       $caR=80;$caG=80;$caB=180;
       $cmR=$caR; $cmG=$caG; $cmB=$caB;
       $realDeepBlue=$metrYmax-($a1-$min)/$k;
	   $Test->drawTextBox($bodyXmax+5,$realDeepBlue-3,60,$realDeepBlue,"$a1"."$unit",0,$cmR,$cmG,$cmB,ALIGN_LEFT,FALSE,-1,-1,-1,100);
	 
       $caR=230;$caG=150;$caB=70;
       $cmR=$caR; $cmG=$caG; $cmB=$caB;
	   $realOrange=$metrYmax-($a4-$min)/$k;
	   $Test->drawTextBox($bodyXmax+5,$realOrange-3,60,$realOrange+5,"$a4"."$unit",0,$cmR,$cmG,$cmB,ALIGN_LEFT,FALSE,-1,-1,-1,100);

       $caR=200;$caG=100;$caB=100;
       $cmR=$caR; $cmG=$caG; $cmB=$caB;
       $realRed=$metrYmax-($a5-$min)/$k;
	   $Test->drawTextBox($bodyXmax+5,$realRed-3,60,$realRed,"$a5"."$unit",0,$cmR,$cmG,$cmB,ALIGN_LEFT,FALSE,-1,-1,-1,100);
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
     if ($a5!=-1 && $a4!=-1 && $a3!=-1 && $a2!=-1 && dbalert!=-1)	{
       if($currentValue > $a2 and $currentValue < $a4){ // green
         $caR=100;$caG=200;$caB=100;
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } elseif ($currentValue <= $a2 and $currentValue > $a1){ // blue
         $caR=100;$caG=160;$caB=230;
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } elseif ($currentValue <= $a1){ // deep blue
         $caR=80;$caG=80;$caB=180;
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } elseif ($currentValue >= $a4 and $currentValue < $a5){ // orange
         $caR=230;$caG=150;$caB=70;
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } elseif ($currentValue >= $a5){ // red
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
     $Test->drawTextBox($bodyXmin,$yVal,$bodyXmax,$yVal+5,"$currentValue"."$unit",0,245,245,245,ALIGN_CENTER,FALSE,-1,-1,-1,100);

     // Draw label
     $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",10);
     $Test->drawTextBox($bodyXmin,185,$bodyXmax,190,"$label",0,140,140,140,ALIGN_CENTER,FALSE,-1,-1,-1,100);   

  // end of level1
   
//-------------------------------------------------------- smartStick1
	
  } elseif ($objtype == "smartStick1") {	

  // Settings
     $wid=78; $hei=50;
     
	 // light/black
     if($black==1){
	   $cbg=$blackc;
	 }else{
	   $cbg=255;
	   }
  
     $bodyXmin=0; $bodyXmax=76;
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
     $Test->drawFilledRoundedRectangle($bodyXmin,$bodyYmin,$bodyXmax,$bodyYmax,8,200-$health,200-$health,200-$health); // body
	
     // Limits
     if ($max!=-1){$pmax=$max;} else {$pmax="";}
     if ($min!=-1){$pmin=$min;} else {$pmin="";}
   
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
	 
     if ($a5!=-1 && $a4!=-1 && $a3!=-1 && $a2!=-1 && dbalert!=-1)	{
       if($currentValue > $a2 and $currentValue < $a4){ // green
         $caR=100;$caG=200;$caB=100;
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } elseif ($currentValue <= $a2 and $currentValue > $a1){ // blue
         $caR=100;$caG=160;$caB=230;
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } elseif ($currentValue <= $a1){ // deep blue
         $caR=80;$caG=80;$caB=180;
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } elseif ($currentValue >= $a4 and $currentValue < $a5){ // orange
         $caR=230;$caG=150;$caB=70;
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } elseif ($currentValue >= $a5){ // red
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
     $Test->drawFilledRectangle($bodyXmin,$bodyYmin+30,$bodyXmax,$bodyYmax-10,200-$health,200-$health,200-$health,FALSE,100); // body
     
     // Current value
     $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",12);
     $Test->drawTextBox($bodyXmin,$bodyYmin+15,$bodyXmax,$bodyYmin+20,"$currentValue"."$unit",0,245,245,245,ALIGN_CENTER,FALSE,-1,-1,-1,100);

     // Draw label
     $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",8);
     $Test->drawTextBox($bodyXmin,$bodyYmax-15,$bodyXmax,$bodyYmax-7,"$label",0,255,255,255,ALIGN_CENTER,FALSE,-1,-1,-1,100);   

  //end of smartStick1

//-------------------------------------------------------- sticker1a
	 
  } elseif ($objtype == "sticker1a") {

     // Settings
	 $wid=100; $hei=100;
	 
     // light/black
     if($black==1){
	   $cbg=$blackc;
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
	 $Test->drawTextBox(10,74,90,80,"$label",0,250,250,250,ALIGN_CENTER,FALSE,-1,-1,-1,100);

  // end of sticker1a
		 
//-------------------------------------------------------- sticker1b
	 
  } elseif ($objtype == "sticker1b") {
  
     // Settings
	 $wid=60; $hei=60;
	 
	 // light/black
     if($black==1){
	   $cbg=$blackc;
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

//-------------------------------------------------------- sticker1c
	 
  } elseif ($objtype == "sticker1c") {
 
     // Settings
	 $wid=50; $hei=50;
	 
	 // light/black
     if($black==1){
	   $cbg=$blackc;
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

//-------------------------------------------------------- cloud1c
	 
  } elseif ($objtype == "cloud1c") {
  
     // Settings
	 $wid=50; $hei=50;
	 
	 // light/black
     if($black==1){
	   $cbg=$blackc;
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

//-------------------------------------------------------- label1b (electro)
	 
  } elseif ($objtype=="label1b") {
  
     // Settings
	 $wid=60; $hei=60;
	 
     // light/black
     if($black==1){
	   $cbg=$blackc;
	 }else{
	   $cbg=255;
	   }
		 
	 // Object + GraphArea
	 $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
		 
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);

     // Draw body
	 $Test->drawFilledRoundedRectangle(0,0,60,60,8,$caR,$caG,$caB); //body

	 // Draw label
     $Test->setFontProperties("./pChart/Fonts/marvosym.ttf",32);
	 $Test->drawTextBox(10,20,50,40,"E",0,250,250,250,ALIGN_CENTER,FALSE,-1,-1,-1,100);		
		 
  // end of label1b

//-------------------------------------------------------- label2b (lamp)
	 
  } elseif ($objtype=="label2b") {

     // Settings
	 $wid=60; $hei=60;
	 
     // light/black
     if($black==1){
	   $cbg=$blackc;
	 }else{
	   $cbg=255;
	   }
		 
	 // Object + GraphArea
	 $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
		 
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);

     // Draw body
	 $Test->drawFilledRoundedRectangle(0,0,60,60,8,$caR,$caG,$caB); //body

	 // Draw label
     $Test->setFontProperties("./pChart/Fonts/heydings_icons.ttf",32);
	 $Test->drawTextBox(10,20,50,40,"l",0,250,250,250,ALIGN_CENTER,FALSE,-1,-1,-1,100);			
		 
	 // end of label2b

//-------------------------------------------------------- label3b (snow)
	 
  } elseif ($objtype=="label3b") {
  
     // Settings
	 $wid=60; $hei=60;

	 // light/black
     if($black==1){
	   $cbg=$blackc;
	 }else{
	   $cbg=255;
	   }
		 
	 // Object + GraphArea
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
		 
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);

     // Draw body
     $Test->drawFilledRoundedRectangle(0,0,60,60,8,$caR,$caG,$caB); //body

	 // Draw label
  //   $Test->setFontProperties("./pChart/Fonts/pictogramz.ttf",44);
   //  $Test->drawTextBox(10,20,50,40,"O",0,250,250,250,ALIGN_CENTER,FALSE,-1,-1,-1,100);	

	 

     $Test->setFontProperties("./pChart/Fonts/Zeichen Hundert.TTF",82);
     $Test->drawTextBox(0,0,60,87,"J",0,250,250,250,ALIGN_CENTER,FALSE,-1,-1,-1,100);	 
		 
  // end of label3b

//-------------------------------------------------------- pic1 (air)
	 
  } elseif ($objtype=="pic1c"||$objtype=="pic1d"||$objtype=="pic1e") {
  
     if       ($objtype=="pic1c") {$wid=32; $hei=32; $size=36; $x1=2; $x2=$wid;   $y1=0; $y2=$hei;
	 } elseif ($objtype=="pic1d") {$wid=24; $hei=24; $size=27; $x1=2; $x2=$wid-2; $y1=0; $y2=$hei;
	 } elseif ($objtype=="pic1e") {$wid=20; $hei=16; $size=22; $x1=2; $x2=$wid;   $y1=0; $y2=$hei;}
  
     if ($black==1){
	   $cbg=$blackc; $k=100;
     } else {$cbg=255; $k=0;}
		 
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
     $Test->drawBackground($cbg,$cbg,$cbg);

     $Test->drawFilledRectangle(0,0,$wid,$hei,$cbg,$cbg,$cbg,FALSE,100); //body

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }

     $Test->setFontProperties("./pChart/Fonts/pictogramz.ttf",$size);
     $Test->drawTextBox($x1,$y1,$x2,$y2,"h",0,$caR,$caG,$caB,ALIGN_CENTER,FALSE,-1,-1,-1,100);				 
  //end of pic1
  
//-------------------------------------------------------- pic2 (tri)
	 
  } elseif ($objtype=="pic2c"||$objtype=="pic2d"||$objtype=="pic2e") {
  
     if       ($objtype=="pic2c") {$wid=40; $hei=40; $size=22; $x1=2; $x2=$wid; $y1=0; $y2=$hei-14;
	 } elseif ($objtype=="pic2d") {$wid=30; $hei=30; $size=18; $x1=2; $x2=$wid; $y1=0; $y2=$hei-12;
	 } elseif ($objtype=="pic2e") {$wid=18; $hei=16; $size=14; $x1=0; $x2=$wid; $y1=0; $y2=$hei-10;}
  
     if($black==1){
	   $cbg=$blackc; $k=100;
	 } else {$cbg=255; $k=0;}
		 
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
     $Test->drawBackground($cbg,$cbg,$cbg);

     $Test->drawFilledRectangle(0,0,$wid,$hei,$cbg,$cbg,$cbg,FALSE,100); //body

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }
	 
	 $Test->setFontProperties("./pChart/Fonts/SWISHBUT.TTF",$size);
     $Test->drawTextBox($x1,$y1,$x2,$y2,"f",0,$caR,$caG,$caB,ALIGN_CENTER,FALSE,-1,-1,-1,100);		 
  // end of pic2

//-------------------------------------------------------- pic3 (mstar)
	 
  } elseif ($objtype=="pic3c"||$objtype=="pic3d"||$objtype=="pic3e") {
  
     if       ($objtype=="pic3c") {$wid=32; $hei=32; $size=23; $x1=2; $x2=$wid;  $y1=0; $y2=$hei-11;
	 } elseif ($objtype=="pic3d") {$wid=24; $hei=24; $size=18; $x1=2; $x2=$wid;  $y1=0; $y2=$hei-10;
	 } elseif ($objtype=="pic3e") {$wid=18; $hei=16; $size=12; $x1=0; $x2=$wid+1;$y1=0; $y2=$hei-6;}

     if($black==1){
	   $cbg=$blackc; $k=100;
	 } else {$cbg=255; $k=0;}
		 
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
     $Test->drawBackground($cbg,$cbg,$cbg);

     $Test->drawFilledRectangle(0,0,$wid,$hei,$cbg,$cbg,$cbg,FALSE,100); //body

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }

	 $Test->setFontProperties("./pChart/Fonts/SWISHBUT.TTF",$size);
     $Test->drawTextBox($x1,$y1,$x2,$y2,"p",0,$caR,$caG,$caB,ALIGN_CENTER,FALSE,-1,-1,-1,100);		 
  //end of pic3  
  
//-------------------------------------------------------- pic4 (mcircle)
	 
  } elseif ($objtype=="pic4c"||$objtype=="pic4d"||$objtype=="pic4e") {

     if       ($objtype=="pic4c") {$wid=38; $hei=38; $size=28; $x1=0; $x2=$wid;  $y1=0; $y2=$hei-14;
	 } elseif ($objtype=="pic4d") {$wid=30; $hei=30; $size=22; $x1=0; $x2=$wid-1;$y1=0; $y2=$hei-13;
	 } elseif ($objtype=="pic4e") {$wid=18; $hei=16; $size=14; $x1=0; $x2=$wid;  $y1=0; $y2=$hei-10;}
  
     if($black==1){
	   $cbg=$blackc; $k=100;
	 } else {$cbg=255; $k=0;}
		 
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
     $Test->drawBackground($cbg,$cbg,$cbg);

     $Test->drawFilledRectangle(0,0,$wid,$hei,$cbg,$cbg,$cbg,FALSE,100); //body

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }
	 
	 $Test->setFontProperties("./pChart/Fonts/SWISHBUT.TTF",$size);
     $Test->drawTextBox($x1,$y1,$x2,$y2,"k",0,$caR,$caG,$caB,ALIGN_CENTER,FALSE,-1,-1,-1,100);		 
  // end of pic4

//-------------------------------------------------------- pic5 (romb)
	 
  } elseif ($objtype=="pic5c"||$objtype=="pic5d"||$objtype=="pic5e") {
  
     if       ($objtype=="pic5c") {$wid=32; $hei=32; $size=25; $x1=0; $x2=$wid; $y1=0; $y2=$hei-14;
	 } elseif ($objtype=="pic5d") {$wid=24; $hei=24; $size=19; $x1=0; $x2=$wid; $y1=0; $y2=$hei-12;
	 } elseif ($objtype=="pic5e") {$wid=18; $hei=16; $size=13; $x1=0; $x2=$wid; $y1=0; $y2=$hei-9;}

	 // light/black
     if($black==1){
	   $cbg=$blackc; $k=100;
	 } else {$cbg=255; $k=0;}
		 
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
     $Test->drawBackground($cbg,$cbg,$cbg);

     $Test->drawFilledRectangle(0,0,$wid,$hei,$cbg,$cbg,$cbg,FALSE,100); //body

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }

	 $Test->setFontProperties("./pChart/Fonts/SWISHBUT.TTF",$size);
     $Test->drawTextBox($x1,$y1,$x2,$y2,"b",0,$caR,$caG,$caB,ALIGN_CENTER,FALSE,-1,-1,-1,100);		 
  //end of pic5  
  
//-------------------------------------------------------- pic6 (sc)
	 
  } elseif ($objtype=="pic6c"||$objtype=="pic6d"||$objtype=="pic6e") {
  
     if       ($objtype=="pic6c") {$wid=32; $hei=32; $size=20; $x1=0; $x2=$wid; $y1=0; $y2=$hei-10;
	 } elseif ($objtype=="pic6d") {$wid=24; $hei=24; $size=17; $x1=0; $x2=$wid; $y1=0; $y2=$hei-9;
	 } elseif ($objtype=="pic6e") {$wid=16; $hei=16; $size=10; $x1=0; $x2=$wid; $y1=0; $y2=$hei-6;}
  
     if($black==1){
	   $cbg=$blackc; $k=100;
	 } else {$cbg=255; $k=0;}
		 
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
	 $Test->drawBackground($cbg,$cbg,$cbg);

     $Test->drawFilledRectangle(0,0,$wid,$hei,$cbg,$cbg,$cbg,FALSE,100); //body

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }

	 $Test->setFontProperties("./pChart/Fonts/SWISHBUT.TTF",$size);
     $Test->drawTextBox($x1,$y1,$x2,$y2,"a",0,$caR,$caG,$caB,ALIGN_CENTER,FALSE,-1,-1,-1,100);		 
  //end of pic6c   
  
//-------------------------------------------------------- pic7 (mromb)
	 
  } elseif ($objtype=="pic7c"||$objtype=="pic7d"||$objtype=="pic7e") {
  
     if       ($objtype=="pic7c") {$wid=32; $hei=32; $size=23; $x1=0; $x2=$wid; $y1=0; $y2=$hei-12;
	 } elseif ($objtype=="pic7d") {$wid=24; $hei=24; $size=19; $x1=0; $x2=$wid; $y1=0; $y2=$hei-10;
	 } elseif ($objtype=="pic7e") {$wid=16; $hei=16; $size=12; $x1=0; $x2=$wid; $y1=0; $y2=$hei-8;}
  
     if($black==1){
	   $cbg=$blackc; $k=100;
	 } else {$cbg=255; $k=0;}
		 
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
     $Test->drawBackground($cbg,$cbg,$cbg);

     $Test->drawFilledRectangle(0,0,$wid,$hei,$cbg,$cbg,$cbg,FALSE,100); //body

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }

	 $Test->setFontProperties("./pChart/Fonts/SWISHBUT.TTF",$size);
     $Test->drawTextBox($x1,$y1,$x2,$y2,"c",0,$caR,$caG,$caB,ALIGN_CENTER,FALSE,-1,-1,-1,100);		 	 
  // end of pic7
  
//-------------------------------------------------------- pic8 (oct)
	 
  } elseif ($objtype=="pic8c"||$objtype=="pic8d"||$objtype=="pic8e") {
  
     if       ($objtype=="pic8c") {$wid=38; $hei=38; $size=135;$x1=0; $x2=$wid; $y1=0; $y2=160;
	 } elseif ($objtype=="pic8d") {$wid=24; $hei=24; $size=96; $x1=0; $x2=$wid; $y1=0; $y2=112;
	 } elseif ($objtype=="pic8e") {$wid=16; $hei=16; $size=68; $x1=0; $x2=$wid; $y1=0; $y2=79;}

     if($black==1){
	   $cbg=$blackc; $k=100;
	 } else {$cbg=255; $k=0;}

     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
     $Test->drawBackground($cbg,$cbg,$cbg);

     $Test->drawFilledRectangle(0,0,$wid,$hei,$cbg,$cbg,$cbg,FALSE,100); //body

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }

	 $Test->setFontProperties("./pChart/Fonts/Zeichen Dreihundert.TTF",$size);
     $Test->drawTextBox($x1,$y1,$x2,$y2,"l",0,$caR,$caG,$caB,ALIGN_CENTER,FALSE,-1,-1,-1,100);		 
  // end of pic8

//-------------------------------------------------------- pic9 (snow)
	 
  } elseif ($objtype=="pic9c"||$objtype=="pic9d"||$objtype=="pic9e") {
  
     if       ($objtype=="pic9c") {$wid=36; $hei=36; $size=80; $x1=0; $x2=$wid; $y1=0; $y2=64;
	 } elseif ($objtype=="pic9d") {$wid=24; $hei=24; $size=54; $x1=0; $x2=$wid; $y1=0; $y2=43;
	 } elseif ($objtype=="pic9e") {$wid=16; $hei=16; $size=40; $x1=0; $x2=$wid; $y1=0; $y2=30;} 

     if($black==1){
	   $cbg=$blackc; $k=100;
     } else {$cbg=255; $k=0;}

     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
     $Test->drawBackground($cbg,$cbg,$cbg);
	 
     $Test->drawFilledRectangle(0,0,$wid,$hei,$cbg,$cbg,$cbg,FALSE,100); //body

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }

	 $Test->setFontProperties("./pChart/Fonts/Zeichen Hundert.TTF",$size);
     $Test->drawTextBox($x1,$y1,$x2,$y2,"J",0,$caR,$caG,$caB,ALIGN_CENTER,FALSE,-1,-1,-1,100);		 
  //end of pic9

  
//-------------------------------------------------------- pic10 (empty cyrcle)
	 
  } elseif ($objtype=="pic10c"||$objtype=="pic10d"||$objtype=="pic10e") {
  
     if       ($objtype=="pic10c") {$wid=32; $hei=32; $size=30; $x1=0; $x2=$wid; $y1=0; $y2=$hei+2;
	 } elseif ($objtype=="pic10d") {$wid=24; $hei=24; $size=22; $x1=0; $x2=$wid; $y1=0; $y2=$hei+3;
	 } elseif ($objtype=="pic10e") {$wid=16; $hei=16; $size=15; $x1=0; $x2=$wid; $y1=0; $y2=$hei;} 
  
     if($black==1){
	   $cbg=$blackc; $k=100;
     } else {$cbg=255; $k=0;}

     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
     $Test->drawBackground($cbg,$cbg,$cbg);

     $Test->drawFilledRectangle(0,0,$wid,$hei,$cbg,$cbg,$cbg,FALSE,100); //body

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }

	 $Test->setFontProperties("./pChart/Fonts/Zeichen Zweihundert.ttf",$size);
     $Test->drawTextBox($x1,$y1,$x2,$y2,"-",0,$caR,$caG,$caB,ALIGN_CENTER,FALSE,-1,-1,-1,100);		 
  //end of pic10
    
//-------------------------------------------------------- pic11c (tri electro)
	 
  } elseif ($objtype=="pic11c"||$objtype=="pic11d"||$objtype=="pic11e") {
  
     if       ($objtype=="pic11c") {$wid=38; $hei=38; $size=36; $x1=0; $x2=$wid; $y1=0; $y2=$hei;
	 } elseif ($objtype=="pic11d") {$wid=24; $hei=24; $size=24; $x1=0; $x2=$wid; $y1=0; $y2=$hei;
	 } elseif ($objtype=="pic11e") {$wid=16; $hei=16; $size=16; $x1=0; $x2=$wid; $y1=0; $y2=$hei;} 

     if($black==1){
	   $cbg=$blackc; $k=100;
     } else {$cbg=255; $k=0;}
		 
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
     $Test->drawBackground($cbg,$cbg,$cbg);

     $Test->drawFilledRectangle(0,0,$wid,$hei,$cbg,$cbg,$cbg,FALSE,100); //body

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }

	 $Test->setFontProperties("./pChart/Fonts/haw.ttf",$size);
     $Test->drawTextBox($x1,$y1,$x2,$y2,"F",0,$caR,$caG,$caB,ALIGN_CENTER,FALSE,-1,-1,-1,100);		 
  //end of pic11c
  
//-------------------------------------------------------- pic12 (electro)
  
  } elseif ($objtype=="pic12c"||$objtype=="pic12d"||$objtype=="pic12e") {
  
     if       ($objtype=="pic12c") {$wid=32; $hei=32; $size=48; $x1=0; $x2=$wid; $y1=0; $y2=$hei;
	 } elseif ($objtype=="pic12d") {$wid=24; $hei=24; $size=30; $x1=0; $x2=$wid; $y1=0; $y2=$hei;
	 } elseif ($objtype=="pic12e") {$wid=16; $hei=16; $size=22; $x1=0; $x2=$wid; $y1=0; $y2=$hei;}   

     if($black==1){
	   $cbg=$blackc; $k=100;
	 } else {$cbg=255; $k=0;}
		 
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
     $Test->drawBackground($cbg,$cbg,$cbg);

     $Test->drawFilledRectangle(0,0,$wid,$hei,$cbg,$cbg,$cbg,FALSE,100); //body

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }
	 
     $Test->setFontProperties("./pChart/Fonts/pictogramz.ttf",$size);
     $Test->drawTextBox($x1,$y1,$x2,$y2,"T",0,$caR,$caG,$caB,ALIGN_CENTER,FALSE,-1,-1,-1,100);				 	 
  //end of pic12
  
//-------------------------------------------------------- pic13 (sound)
	 
  } elseif ($objtype=="pic13c"||$objtype=="pic13d"||$objtype=="pic13e") {
  
     if       ($objtype=="pic13c") {$wid=32; $hei=32; $size=26; $x1=0; $x2=$wid; $y1=0; $y2=$hei-2;
	 } elseif ($objtype=="pic13d") {$wid=24; $hei=24; $size=19; $x1=0; $x2=$wid; $y1=0; $y2=$hei-2;
	 } elseif ($objtype=="pic13e") {$wid=20; $hei=16; $size=16; $x1=0; $x2=$wid; $y1=0; $y2=$hei-2;}   

     if($black==1){
	   $cbg=$blackc; $k=100;
	 }else{$cbg=255; $k=0;}
		 
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
     $Test->drawBackground($cbg,$cbg,$cbg);

     $Test->drawFilledRectangle(0,0,$wid,$hei,$cbg,$cbg,$cbg,FALSE,100); //body

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }

     $Test->setFontProperties("./pChart/Fonts/websymbolsligaregular.ttf",$size);
     $Test->drawTextBox($x1,$y1,$x2,$y2,"z",0,$caR,$caG,$caB,ALIGN_CENTER,FALSE,-1,-1,-1,100);				 
  //end of pic13
  
//-------------------------------------------------------- pic14c (heart)
	 
  } elseif ($objtype=="pic14c"||$objtype=="pic14d"||$objtype=="pic14e"||$objtype=="pic14f") {
  
     if       ($objtype=="pic14c") {$wid=32; $hei=32; $size=28; $x1=0; $x2=$wid; $y1=0; $y2=$hei;
	 } elseif ($objtype=="pic14d") {$wid=24; $hei=24; $size=20; $x1=0; $x2=$wid; $y1=0; $y2=$hei;
	 } elseif ($objtype=="pic14e") {$wid=18; $hei=16; $size=14; $x1=0; $x2=$wid; $y1=0; $y2=$hei;
	 } elseif ($objtype=="pic14f") {$wid=14; $hei=12; $size=12; $x1=0; $x2=$wid; $y1=0; $y2=$hei;}  

     if($black==1){
	   $cbg=$blackc; $k=100;
	 } else {$cbg=255; $k=0;}

     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
     $Test->drawBackground($cbg,$cbg,$cbg);

     $Test->drawFilledRectangle(0,0,$wid,$hei,$cbg,$cbg,$cbg,FALSE,100); //body

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }

     $Test->setFontProperties("./pChart/Fonts/signifylite-webfont.ttf",$size);
     $Test->drawTextBox($x1,$y1,$x2,$y2,"H",0,$caR,$caG,$caB,ALIGN_CENTER,FALSE,-1,-1,-1,100);				 
  //end of pic14

//-------------------------------------------------------- pic15e (electro conn)
	 
  } elseif ($objtype=="pic15e") {
  
     // Settings
	 $wid=20; $hei=13;

	 // light/black
     if($black==1){
	   $cbg=$blackc;
	   $k=100;
	 }else{
	   $cbg=255;
	   $k=0;
	   }
		 
	 // Object + GraphArea
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
     $Test->drawBackground($cbg,$cbg,$cbg);

     $Test->drawFilledRectangle(0,0,$wid,$hei,$cbg,$cbg,$cbg,FALSE,100); //body

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }
	 
	 $Test->drawFilledCircle(10,7,6,$caR,$caG,$caB,0);
     $Test->drawFilledRectangle(12,0,16,12,$cbg,$cbg,$cbg);
	 $Test->drawFilledRectangle(7,0,12,12,$caR,$caG,$caB);

	 $Test->drawFilledRectangle(0,6,6,7,$caR,$caG,$caB);
	 $Test->drawFilledRectangle(12,4,17,3,$caR,$caG,$caB);
	 $Test->drawFilledRectangle(12,8,17,9,$caR,$caG,$caB);
	 
	 
  // end of pic15e
  
//-------------------------------------------------------- holo1
	 
  } elseif ($objtype == "holo1") {
  
     // Settings
	 $wid=50; $hei=66;

     if($black==1){
	   $cbg=$blackc;
	   $cbody=80;
	   $k=70;
	 }else{
	   $cbg=255;
	   $cbody=220;
	   $k=0;
	   }
	   
     $bodyXmin=9; $bodyXmax=41;
     $bodyYmin=0; $bodyYmax=100;
  
     $metrXmin=26; $metrXmax=64;
     $metrYmin=6; $metrYmax=66;
  
     $metrDiap=$metrYmax-$metrYmin;
     $metrCenter=$metrDiap/2;
		 
	 // Object + GraphArea
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
		 
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }
	 
	 // Draw
     $Test->drawFilledRectangle($bodyXmin,$bodyYmin,$bodyXmax,$metrYmax,$caR,$caG,$caB);
				 
 	 $Test->drawFilledRectangle($bodyXmin+13,$bodyYmin+12,$bodyXmax-13,$metrYmax-48,$cbody,$cbody,$cbody);
	 
	 $Test->drawLine($bodyXmin+2,40,$bodyXmax-2,40,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine($bodyXmin+2,41,$bodyXmax-2,41,$cbody,$cbody,$cbody,FALSE);
	 
	 $Test->drawLine($bodyXmin+4,28,$bodyXmin+4,52,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine($bodyXmin+3,28,$bodyXmin+3,52,$cbody,$cbody,$cbody,FALSE);

  // end of holo
  
//-------------------------------------------------------- plita1
	 
  } elseif ($objtype=="plita1") {
  
     // Settings
	 $wid=50; $hei=66;

	 // light/black
     if($black==1){
	   $cbg=$blackc;
	   $cbody=80;
	   $k=100;
	 }else{
	   $cbg=255;
	   $cbody=220;
	   $k=0;
	   }
	   
     $bodyXmin=8; $bodyXmax=42;
     $bodyYmin=22; $bodyYmax=100;
  
     $metrXmin=26; $metrXmax=64;
     $metrYmin=6; $metrYmax=66;
  
     $metrDiap=$metrYmax-$metrYmin;
     $metrCenter=$metrDiap/2;

		 
	 // Object + GraphArea
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
		 
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }
	 
	 // Draw
     $Test->drawFilledRectangle($bodyXmin,$bodyYmin,$bodyXmax,$metrYmax,$caR,$caG,$caB);
	 $Test->drawFilledRectangle($bodyXmin+5,$bodyYmin+18,$bodyXmax-5,$metrYmax-12,$cbody,$cbody,$cbody);
	 $Test->drawFilledCircle($bodyXmin+6,$bodyYmin+6,2,$cbody,$cbody,$cbody,0);
	 $Test->drawFilledCircle($bodyXmin+14,$bodyYmin+6,2,$cbody,$cbody,$cbody,0);
	 $Test->drawFilledCircle($bodyXmin+22,$bodyYmin+6,2,$cbody,$cbody,$cbody,0);
	 $Test->drawFilledCircle($bodyXmin+29,$bodyYmin+6,2,$cbody,$cbody,$cbody,0);
	 
	 $Test->drawLine($bodyXmin+2,33,$bodyXmax-2,33,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine($bodyXmin+2,34,$bodyXmax-2,34,$cbody,$cbody,$cbody,FALSE);
	 
	 $Test->drawLine($bodyXmin+2,60,$bodyXmax-2,60,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine($bodyXmin+2,59,$bodyXmax-2,59,$cbody,$cbody,$cbody,FALSE);
  // end of plita1
  
//-------------------------------------------------------- cond1
	 
  } elseif ($objtype == "cond1") {
  
     // Settings
	 $wid=50; $hei=66;

	 // light/black
     if($black==1){
	   $cbg=$blackc;
	   $cbody=80;
	   $k=100;
	 }else{
	   $cbg=255;
	   $cbody=220;
	   $k=0;
	   }
	   
     $bodyXmin=0; $bodyXmax=50;
     $bodyYmin=32; $bodyYmax=55;
  
     $metrXmin=26; $metrXmax=64;
     $metrYmin=6; $metrYmax=66;
  
     $metrDiap=$metrYmax-$metrYmin;
     $metrCenter=$metrDiap/2;

		 
	 // Object + GraphArea
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
		 
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }
	 
	 // Draw
     $Test->drawFilledRectangle($bodyXmin,$bodyYmin,$bodyXmax,$bodyYmax,$caR,$caG,$caB);
	 $Test->drawFilledRectangle($bodyXmin+2,$bodyYmin+16,$bodyXmax-3,$bodyYmin+22,$cbody,$cbody,$cbody);

	 $Test->drawLine($bodyXmin+21,$bodyYmin+10,$bodyXmax-21,$bodyYmin+10,$cbody,$cbody,$cbody,FALSE);
     $Test->drawLine($bodyXmin+2,$bodyYmin+13,$bodyXmax-2,$bodyYmin+13,$cbody,$cbody,$cbody,FALSE);
  // end of cond1
  
//-------------------------------------------------------- stir1
	 
  } elseif ($objtype=="stir1") {
  
     // Settings
	 $wid=50; $hei=66;

	 // light/black
     if($black==1){
	   $cbg=$blackc;
	   $cbody=80;
	   $k=100;
	 }else{
	   $cbg=255;
	   $cbody=220;
	   $k=0;
	   }
	   
     $bodyXmin=8; $bodyXmax=42;
     $bodyYmin=22; $bodyYmax=100;
  
     $metrXmin=26; $metrXmax=64;
     $metrYmin=6; $metrYmax=66;
  
     $metrDiap=$metrYmax-$metrYmin;
     $metrCenter=$metrDiap/2;
		 
	 // Object + GraphArea
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
		 
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }
	 
	 // Draw
     $Test->drawFilledRectangle($bodyXmin,$bodyYmin,$bodyXmax,$metrYmax,$caR,$caG,$caB);
				 
	 $Test->drawFilledRectangle($bodyXmin+6,$bodyYmin+4,$bodyXmax-16,$metrYmax-37,$cbody,$cbody,$cbody);
     
	 $Test->drawFilledCircle($bodyXmin+28,$bodyYmin+6,3,$cbody,$cbody,$cbody,0);
	 $Test->drawFilledCircle($metrCenter-4,$bodyYmin+28,11,$cbody,$cbody,$cbody,0);
	 $Test->drawFilledCircle($metrCenter-4,$bodyYmin+28,7,$caR,$caG,$caB,0);

	 $Test->drawLine($bodyXmin+2,33,$bodyXmax-2,33,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine($bodyXmin+2,34,$bodyXmax-2,34,$cbody,$cbody,$cbody,FALSE);
  // end of stir1
  
//-------------------------------------------------------- pol1
	 
  } elseif ($objtype == "pol1") {
  
     // Settings
	 $wid=50; $hei=66;

	 // light/black
     if($black==1){
	   $cbg=$blackc;
	   $cbody=80;
	   $k=100;
	 }else{
	   $cbg=255;
	   $cbody=220;
	   $k=0;
	   }
	   
     $bodyXmin=8; $bodyXmax=42;
     $bodyYmin=30; $bodyYmax=100;
  
     $metrXmin=26; $metrXmax=64;
     $metrYmin=6; $metrYmax=66;

  
     $metrDiap=$metrYmax-$metrYmin;
     $metrCenter=$metrDiap/2;
		 
	 // Object + GraphArea
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
		 
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }
	 
	 // Draw
	 $Test->drawFilledRectangle($bodyXmin,$bodyYmin,$bodyXmax,$metrYmax,$caR,$caG,$caB);

	 $Test->drawLine($bodyXmin,41,$bodyXmax,41,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine($bodyXmin,42,$bodyXmax,42,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine($bodyXmin,53,$bodyXmax,53,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine($bodyXmin,54,$bodyXmax,54,$cbody,$cbody,$cbody,FALSE);
	 
	 $Test->drawLine(19,$bodyYmin,19,$bodyYmax,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine(20,$bodyYmin,20,$bodyYmax,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine(32,$bodyYmin,32,$bodyYmax,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine(31,$bodyYmin,31,$bodyYmax,$cbody,$cbody,$cbody,FALSE);
	 
	 $Test->drawFilledRectangle($bodyXmin+24,$bodyYmin+24,$bodyXmax,$metrYmax-1,$cbody+30,$cbody+30,$cbody+30);
  // end of pol1
  
//-------------------------------------------------------- server1
	 
  } elseif ($objtype == "server1") {
  
     // Settings
	 $wid=50; $hei=66;

	 // light/black
     if($black==1){
	   $cbg=$blackc;
	   $cbody=80;
	   $k=100;
	 }else{
	   $cbg=255;
	   $cbody=220;
	   $k=0;
	   }
	   
     $bodyXmin=12; $bodyXmax=38;
     $bodyYmin=22; $bodyYmax=66;
  
     $metrXmin=26; $metrXmax=64;
     $metrYmin=6; $metrYmax=66;
  
     $metrDiap=$metrYmax-$metrYmin;
     $metrCenter=$metrDiap/2;

		 
	 // Object + GraphArea
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
		 
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }
	 
	 // Draw
     $Test->drawFilledRectangle($bodyXmin,$bodyYmin,$bodyXmax,$metrYmax,$caR,$caG,$caB);
	 $Test->drawFilledRectangle($bodyXmin+22,$bodyYmin+6,$bodyXmin+24,$bodyYmin+9,$cbody,$cbody,$cbody);
	 $Test->drawFilledRectangle($bodyXmin+22,$bodyYmin+14,$bodyXmin+24,$bodyYmin+17,$cbody,$cbody,$cbody);
	 
	 $Test->drawLine($bodyXmin+3,$bodyYmin+3,$bodyXmin+3,$bodyYmax-3,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine($bodyXmin+4,$bodyYmin+3,$bodyXmin+4,$bodyYmax-3,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine($bodyXmin+8,$bodyYmin+3,$bodyXmin+8,$bodyYmax-3,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine($bodyXmin+9,$bodyYmin+3,$bodyXmin+9,$bodyYmax-3,$cbody,$cbody,$cbody,FALSE);
	 
	 $Test->drawLine($bodyXmin+13,$bodyYmin+3,$bodyXmin+13,$bodyYmax-3,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine($bodyXmin+14,$bodyYmin+3,$bodyXmin+14,$bodyYmax-3,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine($bodyXmin+18,$bodyYmin+3,$bodyXmin+18,$bodyYmax-3,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine($bodyXmin+19,$bodyYmin+3,$bodyXmin+19,$bodyYmax-3,$cbody,$cbody,$cbody,FALSE);
  // end of server1

//-------------------------------------------------------- kettle1
	 
  } elseif ($objtype == "kettle1") {
  
     // Settings
	 $wid=50; $hei=66;

	 // light/black
     if($black==1){
	   $cbg=$blackc;
	   $cbody=80;
	   $k=100;
	 }else{
	   $cbg=255;
	   $cbody=220;
	   $k=0;
	   }
	   
     $bodyXmin=10; $bodyXmax=40;
     $bodyYmin=60; $bodyYmax=66;
  
     $metrXmin=26; $metrXmax=64;
     $metrYmin=6; $metrYmax=66;

  
     $metrDiap=$metrYmax-$metrYmin;
     $metrCenter=$metrDiap/2;
		 
	 // Object + GraphArea
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei); 
		 
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);

	 if ($currentValue==1) {
	   //$caR=100;$caG=160;$caB=230;
	 }elseif ($currentValue==0){
	   $caR=210-$k;$caG=210-$k;$caB=210-$k;
	 }else{
	   $caR=200;$caG=100;$caB=100;
	 }
	 
	 // Draw
	 $Test->drawFilledRectangle($bodyXmin,$bodyYmin,$bodyXmax,$metrYmax,$caR,$caG,$caB);
	 $Test->drawFilledCircle($metrCenter+5,$bodyYmin-10,13,$caR,$caG,$caB,0);
	 $Test->drawFilledCircle($metrCenter+5,$bodyYmin-10,9,$cbody,$cbody,$cbody,0);
     $Test->drawFilledEllipse($metrCenter-4,$bodyYmin,16,30, $caR,$caG,$caB);
	 
	 $Test->drawLine($bodyXmin+4,35,$bodyXmax-4,35,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine($bodyXmin+4,36,$bodyXmax-4,36,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine($bodyXmin-2,60,$bodyXmax+2,60,$cbody,$cbody,$cbody,FALSE);
	 $Test->drawLine($bodyXmin-2,61,$bodyXmax+2,61,$cbody,$cbody,$cbody,FALSE);
	 
	 $Test->drawLine($bodyXmin-5,37,$bodyXmax-4,37,$caR,$caG,$caB,FALSE);
	 $Test->drawLine($bodyXmin-4,38,$bodyXmax-4,38,$caR,$caG,$caB,FALSE);
	 $Test->drawLine($bodyXmin-3,39,$bodyXmax-4,39,$caR,$caG,$caB,FALSE);
	 $Test->drawLine($bodyXmin-2,40,$bodyXmax-4,40,$caR,$caG,$caB,FALSE);
	 $Test->drawLine($bodyXmin-1,41,$bodyXmax-4,41,$caR,$caG,$caB,FALSE);
  // end of kettle1
  
//-------------------------------------------------------- svet1
	
  } elseif ($objtype == "svet1") {	

  // Settings
     $wid=90; 
	 $hei=20;
     
	 // light/black
     if($black==1){
	   $cbg=$blackc;
	 }else{
	   $cbg=255;
	   }
  
     $bodyXmin=0; $bodyXmax=$wid;
     $bodyYmin=0; $bodyYmax=$hei;
  
     $metrXmin=26; $metrXmax=64;
     $metrYmin=6; $metrYmax=36;
  
     $metrDiap=$metrYmax-$metrYmin;
     $metrCenter=$metrDiap/2;
  
     $s0Ymin=2; $s0Ymax=$hei-3;
     $s0Xmin=2; $s0Xmax=$wid-3; 
	 
     $s1Xmin=2; $s1Xmax=30;
	 $s2Xmin=29; $s2Xmax=60;
     $s3Xmin=59; $s3Xmax=$wid-3;
	 
	 
       
     // Object + GraphArea	 
     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei);  
	
     // Background
     $Test->drawBackground($cbg,$cbg,$cbg);
	
	 $Test->drawFilledRectangle($bodyXmin,$bodyYmin,$bodyXmax,$bodyYmax,160,160,160,FALSE,100);
	 $Test->drawFilledRectangle($s1Xmin,$s0Ymin,$s3Xmax,$s0Ymax,220,220,220,FALSE,100);
	
     // Limits
     if ($max!=-1){$pmax=$max;} else {$pmax="";}
     if ($min!=-1){$pmin=$min;} else {$pmin="";}
   
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
  
     // Color break limit
     $cmR=$caR; $cmG=$caG; $cmB=$caB;
   
     if ($limit!=-1){
       if ($currentValue > $limit) {
         $cmR=$caR; $cmG=$caG; $cmB=$caB;
       } else {
         $cmR=$cpR; $cmG=$cpG; $cmB=$cpB;
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
	 
     // Draw State's
	 if($currentValue==1){
       $Test->drawFilledRectangle($s1Xmin,$s0Ymin,$s1Xmax,$s0Ymax,80,220,80,FALSE,100);
     } elseif ($currentValue==2){
       $Test->drawFilledRectangle($s2Xmin,$s0Ymin,$s2Xmax,$s0Ymax,240,130,50,FALSE,100);
     } elseif ($currentValue==3){
       $Test->drawFilledRectangle($s3Xmin,$s0Ymin,$s3Xmax,$s0Ymax,240,80,80,FALSE,100);
     } else {
       //
     }
	 
	 $Test->drawFilledRectangle(29,$bodyYmin,30,$bodyYmax,160,160,160,FALSE,100);
	 $Test->drawFilledRectangle(59,$bodyYmin,60,$bodyYmax,160,160,160,FALSE,100);
  //end of svet1
  
//-------------------------------------------------------- timeline1
	
  } elseif ($objtype == "timeline1") {	

  // Settings
     $wid=$width; 
	 $hei=$height;
     
     if($black==1){
	   $cbg=$blackc;
	 }else{
	   $cbg=255;
	   }
  
     $bodyXmin=0; $bodyXmax=$wid;
     $bodyYmin=10; $bodyYmax=30;
  
     $metrXmin=26; $metrXmax=64;
     $metrYmin=6; $metrYmax=36;
  
     $metrDiap=$metrYmax-$metrYmin;
     $metrCenter=$metrDiap/2;
  
     $s0Ymin=$bodyYmin+2; $s0Ymax=$bodyYmax-2;
     $s0Xmin=2; $s0Xmax=$bodyXmax-3; 
	 
     $s1Xmin=2; $s1Xmax=30;
	 $s2Xmin=29; $s2Xmax=60;
     $s3Xmin=59; $s3Xmax=$bodyXmax-3;
	 

     $Test = new pChart($wid,$hei);
     $Test->setGraphArea(0,0,$wid,$hei);  
     $Test->drawBackground($cbg,$cbg,$cbg);
	
	 $Test->drawFilledRectangle($bodyXmin,$bodyYmin,$bodyXmax,$bodyYmax,160,160,160,FALSE,100);
	 $Test->drawFilledRectangle($s1Xmin,$s0Ymin,$s3Xmax,$s0Ymax,220,220,220,FALSE,100);
	
	 $tt0=timeConvert("00:00")-3600;
     $tt9=timeConvert("23:59")-3600+59;
	 $ttd=$tt9-$tt0;
	 $ttn=time();
	 $tt1=$ttn-$tt0;
	 
	 $ttp=$ttd/$width;
	 $ttx=$tt1/$ttp;

	 if ($ttn-$tt0>86400) {
	   $ttx=$ttx-$width;
	 }
	 
	 $d1on_=timeConvert("00:00")-3600; $d1off_=timeConvert("07:00")-3600;
	 $d2on_=timeConvert("07:00")-3600; $d2off_=timeConvert("10:00")-3600;
	 $d3on_=timeConvert("10:00")-3600; $d3off_=timeConvert("17:00")-3600;
	 $d4on_=timeConvert("17:00")-3600; $d4off_=timeConvert("21:00")-3600;
	 $d5on_=timeConvert("21:00")-3600; $d5off_=timeConvert("23:00")-3600;
	 $d6on_=timeConvert("23:00")-3600; $d6off_=timeConvert("23:59")-3600;
	 
	 $d1on=$d1on_-$tt0; $d1off=$d1off_-$tt0;
	 $d2on=$d2on_-$tt0; $d2off=$d2off_-$tt0;
	 $d3on=$d3on_-$tt0; $d3off=$d3off_-$tt0;
	 $d4on=$d4on_-$tt0; $d4off=$d4off_-$tt0;
	 $d5on=$d5on_-$tt0; $d5off=$d5off_-$tt0;
	 $d6on=$d6on_-$tt0; $d6off=$d6off_-$tt0;
	 
	 $d1onx=$d1on/$ttp; $d1offx=$d1off/$ttp;
	 $d2onx=$d2on/$ttp; $d2offx=$d2off/$ttp;
	 $d3onx=$d3on/$ttp; $d3offx=$d3off/$ttp;
	 $d4onx=$d4on/$ttp; $d4offx=$d4off/$ttp;
	 $d5onx=$d5on/$ttp; $d5offx=$d5off/$ttp;
	 $d6onx=$d6on/$ttp; $d6offx=$d6off/$ttp;
	 
	 $Test->drawFilledRectangle($d1onx,$s0Ymin,$d1offx,$s0Ymax,80,220,80,FALSE,100);
	 $Test->drawFilledRectangle($d2onx,$s0Ymin,$d2offx,$s0Ymax,240,80,80,FALSE,100);
	 $Test->drawFilledRectangle($d3onx,$s0Ymin,$d3offx,$s0Ymax,240,130,50,FALSE,100);
	 $Test->drawFilledRectangle($d4onx,$s0Ymin,$d4offx,$s0Ymax,240,80,80,FALSE,100);
	 $Test->drawFilledRectangle($d5onx,$s0Ymin,$d5offx,$s0Ymax,240,130,50,FALSE,100);
	 $Test->drawFilledRectangle($d6onx,$s0Ymin,$d6offx-3,$s0Ymax,80,220,80,FALSE,100);
	 
	 $Test->drawFilledRectangle($d1onx,$bodyYmin,$d1onx+1,$bodyYmax,160,160,160,FALSE,100);
	 $Test->drawFilledRectangle($d2onx-1,$bodyYmin,$d2onx,$bodyYmax,160,160,160,FALSE,100);
	 $Test->drawFilledRectangle($d3onx-1,$bodyYmin,$d3onx,$bodyYmax,160,160,160,FALSE,100);
	 $Test->drawFilledRectangle($d4onx-1,$bodyYmin,$d4onx,$bodyYmax,160,160,160,FALSE,100);
	 $Test->drawFilledRectangle($d5onx-1,$bodyYmin,$d5onx,$bodyYmax,160,160,160,FALSE,100);
	 $Test->drawFilledRectangle($d6onx-1,$bodyYmin,$d6onx,$bodyYmax,160,160,160,FALSE,100);

	 $Test->drawFilledRectangle($bodyXmin+$ttx-1,$bodyYmin+3,$bodyXmin+$ttx+1,$bodyYmax-3,220,220,220,FALSE,100);
	 
	 $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",8);
	 $Test->drawTextBox($bodyXmin+$d1onx,$bodyYmax,$bodyXmin+$d1onx+20,$bodyYmax+18,"0:00",0,125,125,125,ALIGN_CENTER,FALSE,-1,-1,-1,100); 
	 $Test->drawTextBox($bodyXmin+$d2onx-5,$bodyYmax,$bodyXmin+$d2onx+5,$bodyYmax+18,"7:00",0,125,125,125,ALIGN_CENTER,FALSE,-1,-1,-1,100); 
	 $Test->drawTextBox($bodyXmin+$d3onx-5,$bodyYmax,$bodyXmin+$d3onx+5,$bodyYmax+18,"10:00",0,125,125,125,ALIGN_CENTER,FALSE,-1,-1,-1,100); 
	 $Test->drawTextBox($bodyXmin+$d4onx-5,$bodyYmax,$bodyXmin+$d4onx+5,$bodyYmax+18,"17:00",0,125,125,125,ALIGN_CENTER,FALSE,-1,-1,-1,100); 
	 $Test->drawTextBox($bodyXmin+$d5onx-5,$bodyYmax,$bodyXmin+$d5onx+5,$bodyYmax+18,"21:00",0,125,125,125,ALIGN_CENTER,FALSE,-1,-1,-1,100); 
	 $Test->drawTextBox($bodyXmin+$d6onx-5,$bodyYmax,$bodyXmin+$d6onx+5,$bodyYmax+18,"23:00",0,125,125,125,ALIGN_CENTER,FALSE,-1,-1,-1,100); 
	 
	 //$Test->drawTextBox($bodyXmin+10,$bodyYmax+20,$bodyXmin+500,$bodyYmax+38,"",0,125,125,125,ALIGN_CENTER,FALSE,-1,-1,-1,100);   
  //end of timeline1
  
  
//======================================================== End ========================================================  
 
//---------------------------- Error mess object
	
	} else {
   
    } // end of ($objtype == "...")

//---------------------------- Final
	
 Header("Content-type:image/png");
 imagepng($Test->Picture);
