<?php   
 
/*
pChart pie.php v.1.0.0

//------------------- Parameters

&black=  - black (1) or white*
&mode=   - * (normal), 1 - all-other
&width=  - 260*
&height= - 210*
&title=  - ""*
&radius= - 70*
&border= - 0*

&gtype=
pie  - drawBasicPieGraph
pie2 - drawFlatPieGraph
pie3 - drawFlatPieGraphWithShadow
pie4 - drawPieGraph

//------------------- Objects

&p1= - object1.value
&p2= - object2.value
&p3= - object3.value
&p4= - object4.value
&p5= - object5.value
&p6= - object6.value
&p7= - object7.value
&p8= - object8.value
&p9= - object9.value

&n1= - name of object1
&n2= - name of object2
&n3= - name of object3
&n4= - name of object4
&n5= - name of object5
&n6= - name of object6
&n7= - name of object7
&n8= - name of object8
&n9= - name of object9

//------------------- Colors

&c1r=, &c1g=, &c1b= - color of object1
&c2r=, &c2g=, &c2b= - color of object2
&c3r=, &c3g=, &c3b= - color of object3
&c4r=, &c4g=, &c4b= - color of object4
&c5r=, &c5g=, &c5b= - color of object5
&c6r=, &c6g=, &c6b= - color of object6
&c7r=, &c7g=, &c7b= - color of object7
&c8r=, &c8g=, &c8b= - color of object8
&c9r=, &c9g=, &c9b= - color of object9

&bg_r=, &bg_g=, &bg_b= - background graphics (red,blue,green, default -)

//------------------- Notes

* - by default
*/

//---------------------------- chdir 

 chdir('../');

//---------------------------- Standard inclusions 
 
 include_once("./config.php");
 include_once("./lib/loader.php");
 include_once(DIR_MODULES."application.class.php");
 include_once("./load_settings.php");
 include("./pChart/pData.class");   
 include("./pChart/pChart.class");  
 
//---------------------------- Settings 

 $settings = SQLSelect("SELECT NAME, VALUE FROM settings");
 $total = count($settings);
 for($i=0;$i<$total;$i++) {
   Define('SETTINGS_'.$settings[$i]['NAME'], $settings[$i]['VALUE']);
 }

//---------------------------- Black or white 
 
 if ($black==1) {
   $cbg=0;
   $csh=80;
 } else {
     $cbg=255;
	 $csh=200;
	}
	
//---------------------------- Mode

 if ($_GET['mode']) {
  $mode=$_GET['mode'];
} else {
    $mode=0;
  }      
	
//---------------------------- Width & height of graphics
 
 if (!$width) {
   $w=260;
 } else {
    $w=(int)$width;
   }
   
 if (!$height) {
   $h=210;
 } else {
    $h=(int)$height;
   }   

//---------------------------- Radius

 if ($_GET['radius']) {
  $radius=$_GET['radius'];
} else {
    $radius=70;
  }        
   
//---------------------------- Colors

 if ($_GET['bg_r']) {$bg_r=$_GET['bg_r'];} else {$bg_r=0;}
 if ($_GET['bg_g']) {$bg_g=$_GET['bg_g'];} else {$bg_g=0;}
 if ($_GET['bg_b']) {$bg_b=$_GET['bg_b'];} else {$bg_b=0;}

 if ($_GET['c1r']) {$c1r=$_GET['c1r'];} else {$c1r=0;}
 if ($_GET['c1g']) {$c1g=$_GET['c1g'];} else {$c1g=0;}
 if ($_GET['c1b']) {$c1b=$_GET['c1b'];} else {$c1b=0;}
 
 if ($_GET['c2r']) {$c2r=$_GET['c2r'];} else {$c2r=0;}
 if ($_GET['c2g']) {$c2g=$_GET['c2g'];} else {$c2g=0;}
 if ($_GET['c2b']) {$c2b=$_GET['c2b'];} else {$c2b=0;}
 
 if ($_GET['c3r']) {$c3r=$_GET['c3r'];} else {$c3r=0;}
 if ($_GET['c3g']) {$c3g=$_GET['c3g'];} else {$c3g=0;}
 if ($_GET['c3b']) {$c3b=$_GET['c3b'];} else {$c3b=0;}
 
 if ($_GET['c4r']) {$c4r=$_GET['c4r'];} else {$c4r=0;}
 if ($_GET['c4g']) {$c4g=$_GET['c4g'];} else {$c4g=0;}
 if ($_GET['c4b']) {$c4b=$_GET['c4b'];} else {$c4b=0;}
 
 if ($_GET['c5r']) {$c5r=$_GET['c5r'];} else {$c5r=0;}
 if ($_GET['c5g']) {$c5g=$_GET['c5g'];} else {$c5g=0;}
 if ($_GET['c5b']) {$c5b=$_GET['c5b'];} else {$c5b=0;}
 
 if ($_GET['c6r']) {$c6r=$_GET['c6r'];} else {$c6r=0;}
 if ($_GET['c6g']) {$c6g=$_GET['c6g'];} else {$c6g=0;}
 if ($_GET['c6b']) {$c6b=$_GET['c6b'];} else {$c6b=0;}
 
 if ($_GET['c7r']) {$c7r=$_GET['c7r'];} else {$c7r=0;}
 if ($_GET['c7g']) {$c7g=$_GET['c7g'];} else {$c7g=0;}
 if ($_GET['c7b']) {$c7b=$_GET['c7b'];} else {$c7b=0;}
 
 if ($_GET['c8r']) {$c8r=$_GET['c8r'];} else {$c8r=0;}
 if ($_GET['c8g']) {$c8g=$_GET['c8g'];} else {$c8g=0;}
 if ($_GET['c8b']) {$c8b=$_GET['c8b'];} else {$c8b=0;}
 
 if ($_GET['c9r']) {$c9r=$_GET['c9r'];} else {$c9r=0;}
 if ($_GET['c9g']) {$c9g=$_GET['c9g'];} else {$c9g=0;}
 if ($_GET['c9b']) {$c9b=$_GET['c9b'];} else {$c9b=0;}
   
  
//---------------------------- Title
  
 if ($_GET['title']) {
   $title=$_GET['title'];
 } else {
     $title="";
   }  
   
//---------------------------- Names

 if ($_GET['n1']) {$n1=$_GET['n1'];} else {$n1="";}   
 if ($_GET['n2']) {$n2=$_GET['n2'];} else {$n2="";}   
 if ($_GET['n3']) {$n3=$_GET['n3'];} else {$n3="";}   
 if ($_GET['n4']) {$n4=$_GET['n4'];} else {$n4="";}   
 if ($_GET['n5']) {$n5=$_GET['n5'];} else {$n5="";}   
 if ($_GET['n6']) {$n6=$_GET['n6'];} else {$n6="";}   
 if ($_GET['n7']) {$n7=$_GET['n7'];} else {$n7="";}   
 if ($_GET['n8']) {$n8=$_GET['n8'];} else {$n8="";}   
 if ($_GET['n9']) {$n9=$_GET['n9'];} else {$n9="";}  
 
//---------------------------- Dataset definition   

 $DataSet = new pData;
 
//---------------------------- Get object 1

 if ($p1!='') {
   if (preg_match('/(.+)\.(.+)/is', $p1, $m1)) {
     $obj1 = getObject($m1[1]);
     $prop_id1 = $obj1->getPropertyByName($m1[2], $obj1->class_id, $obj1->id);
   }
 }
 $pvalue1=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id1."' AND OBJECT_ID='".$obj1->id."'");

 if ($pvalue1['ID']) {$v1=$pvalue1['VALUE'];} else {$v1=0;}
 
//---------------------------- Get object 2

 if ($p2!='') {
   if (preg_match('/(.+)\.(.+)/is', $p2, $m2)) {
     $obj2 = getObject($m2[1]);
     $prop_id2 = $obj2->getPropertyByName($m2[2], $obj2->class_id, $obj2->id);
   }
 }
 $pvalue2=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id2."' AND OBJECT_ID='".$obj2->id."'");

 if ($pvalue2['ID']) {$v2=$pvalue2['VALUE'];} else {$v2=0;}

//---------------------------- Get object 3

 if ($p3!='') {
   if (preg_match('/(.+)\.(.+)/is', $p3, $m3)) {
     $obj3 = getObject($m3[1]);
     $prop_id3 = $obj3->getPropertyByName($m3[2], $obj3->class_id, $obj3->id);
   }
 }
 $pvalue3=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id3."' AND OBJECT_ID='".$obj3->id."'");

 if ($pvalue3['ID']) {$v3=$pvalue3['VALUE'];} else {$v3=0;}
 
//---------------------------- Get object 4

 if ($p4!='') {
   if (preg_match('/(.+)\.(.+)/is', $p4, $m4)) {
     $obj4 = getObject($m4[1]);
     $prop_id4 = $obj4->getPropertyByName($m4[2], $obj4->class_id, $obj4->id);
   }
 }
 $pvalue4=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id4."' AND OBJECT_ID='".$obj4->id."'");

 if ($pvalue4['ID']) {$v4=$pvalue4['VALUE'];} else {$v4=0;}
 
//---------------------------- Get object 5

 if ($p5!='') {
   if (preg_match('/(.+)\.(.+)/is', $p5, $m5)) {
     $obj5 = getObject($m5[1]);
     $prop_id5 = $obj5->getPropertyByName($m5[2], $obj5->class_id, $obj5->id);
   }
 }
 $pvalue5=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id5."' AND OBJECT_ID='".$obj5->id."'");
 
 if ($pvalue5['ID']) {$v5=$pvalue5['VALUE'];} else {$v5=0;}
 
//---------------------------- Get object 6

 if ($p6!='') {
   if (preg_match('/(.+)\.(.+)/is', $p6, $m6)) {
     $obj6 = getObject($m6[1]);
     $prop_id6 = $obj6->getPropertyByName($m6[2], $obj6->class_id, $obj6->id);
   }
 }
 $pvalue6=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id6."' AND OBJECT_ID='".$obj6->id."'");
 
 if ($pvalue6['ID']) {$v6=$pvalue6['VALUE'];} else {$v6=0;}
 
//---------------------------- Get object 7

 if ($p7!='') {
   if (preg_match('/(.+)\.(.+)/is', $p7, $m7)) {
     $obj7 = getObject($m7[1]);
     $prop_id7 = $obj7->getPropertyByName($m7[2], $obj7->class_id, $obj7->id);
   }
 }
 $pvalue7=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id7."' AND OBJECT_ID='".$obj7->id."'");
 
 if ($pvalue7['ID']) {$v7=$pvalue7['VALUE'];} else {$v7=0;}

//---------------------------- Get object 8

 if ($p8!='') {
   if (preg_match('/(.+)\.(.+)/is', $p8, $m8)) {
     $obj8 = getObject($m8[1]);
     $prop_id8 = $obj8->getPropertyByName($m8[2], $obj8->class_id, $obj8->id);
   }
 }
 $pvalue8=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id8."' AND OBJECT_ID='".$obj8->id."'");
 
 if ($pvalue8['ID']) {$v8=$pvalue8['VALUE'];} else {$v8=0;}

//---------------------------- Get object 9

 if ($p9!='') {
   if (preg_match('/(.+)\.(.+)/is', $p9, $m9)) {
     $obj9 = getObject($m9[1]);
     $prop_id9 = $obj9->getPropertyByName($m9[2], $obj9->class_id, $obj9->id);
   }
 }
 $pvalue9=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id9."' AND OBJECT_ID='".$obj9->id."'");
 
 if ($pvalue9['ID']) {$v9=$pvalue9['VALUE'];} else {$v9=0;} 
 
//---------------------------- Names correction

 if ($v1==0) {$n1="";}
 if ($v2==0) {$n2="";}
 if ($v3==0) {$n3="";}
 if ($v4==0) {$n4="";}
 if ($v5==0) {$n5="";}
 if ($v6==0) {$n6="";}
 if ($v7==0) {$n7="";}
 if ($v8==0) {$n8="";}
 if ($v9==0) {$n9="";} 
 
//---------------------------- Work
  
  if ($mode==1) { //all-other
   $v0=$v1-($v2+$v3+$v4+$v5+$v6+$v7+$v8+$v9);
   if ($v0!=0) {$DataSet->AddPoint($v0,"Serie1"); $DataSet->AddPoint($n1,"Serie3");}
   if ($v2!=0) {$DataSet->AddPoint($v2,"Serie1"); $DataSet->AddPoint($n2,"Serie3");}
   if ($v3!=0) {$DataSet->AddPoint($v3,"Serie1"); $DataSet->AddPoint($n3,"Serie3");}
   if ($v4!=0) {$DataSet->AddPoint($v4,"Serie1"); $DataSet->AddPoint($n4,"Serie3");}
   if ($v5!=0) {$DataSet->AddPoint($v5,"Serie1"); $DataSet->AddPoint($n5,"Serie3");}
   if ($v6!=0) {$DataSet->AddPoint($v6,"Serie1"); $DataSet->AddPoint($n6,"Serie3");}
   if ($v7!=0) {$DataSet->AddPoint($v7,"Serie1"); $DataSet->AddPoint($n7,"Serie3");}
   if ($v8!=0) {$DataSet->AddPoint($v8,"Serie1"); $DataSet->AddPoint($n8,"Serie3");}  
   if ($v9!=0) {$DataSet->AddPoint($v9,"Serie1"); $DataSet->AddPoint($n9,"Serie3");}
 } elseif ($mode==2) {
   //
 } elseif ($mode==3) {
   //
 } else { //normal (default)
   if ($v1!=0) {$DataSet->AddPoint($v1,"Serie1"); $DataSet->AddPoint($n1,"Serie3");}
   if ($v2!=0) {$DataSet->AddPoint($v2,"Serie1"); $DataSet->AddPoint($n2,"Serie3");}
   if ($v3!=0) {$DataSet->AddPoint($v3,"Serie1"); $DataSet->AddPoint($n3,"Serie3");}
   if ($v4!=0) {$DataSet->AddPoint($v4,"Serie1"); $DataSet->AddPoint($n4,"Serie3");}
   if ($v5!=0) {$DataSet->AddPoint($v5,"Serie1"); $DataSet->AddPoint($n5,"Serie3");}
   if ($v6!=0) {$DataSet->AddPoint($v6,"Serie1"); $DataSet->AddPoint($n6,"Serie3");}
   if ($v7!=0) {$DataSet->AddPoint($v7,"Serie1"); $DataSet->AddPoint($n7,"Serie3");}
   if ($v8!=0) {$DataSet->AddPoint($v8,"Serie1"); $DataSet->AddPoint($n8,"Serie3");}  
   if ($v9!=0) {$DataSet->AddPoint($v9,"Serie1"); $DataSet->AddPoint($n9,"Serie3");}
 } 
 
//---------------------------- Add arrays
 
 //$DataSet->AddPoint($values,"Serie1");  
 //$DataSet->AddPoint($names,"Serie3");  
  
//---------------------------- DataSet operations

 $DataSet->AddAllSeries();  
 $DataSet->RemoveSerie("Serie3");  
 $DataSet->SetAbsciseLabelSerie("Serie3");  

//---------------------------- Create Object of pChart  
  
 $Test = new pChart($w,$h);  

//---------------------------- Set colors of sectors (ID,R,G,G)  

 if ($v1!=0 && $c1r!=0) {$Test->setColorPalette(0,$c1r,$c1g,$c1b);}
 if ($v2!=0 && $c2r!=0) {$Test->setColorPalette(1,$c2r,$c2g,$c2b);}
 if ($v3!=0 && $c3r!=0) {$Test->setColorPalette(2,$c3r,$c3g,$c3b);}
 if ($v4!=0 && $c4r!=0) {$Test->setColorPalette(3,$c4r,$c4g,$c4b);}
 if ($v5!=0 && $c5r!=0) {$Test->setColorPalette(4,$c5r,$c5g,$c5b);}
 if ($v6!=0 && $c6r!=0) {$Test->setColorPalette(5,$c6r,$c6g,$c6b);}
 if ($v7!=0 && $c7r!=0) {$Test->setColorPalette(6,$c7r,$c7g,$c7b);}
 if ($v8!=0 && $c8r!=0) {$Test->setColorPalette(7,$c8r,$c8g,$c8b);}
 if ($v9!=0 && $c9r!=0) {$Test->setColorPalette(8,$c9r,$c9g,$c9b);}
 
//---------------------------- Background

 $Test->drawBackground($cbg,$cbg,$cbg);   
 
//---------------------------- Title
  
 $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",10);  
 $Test->drawTitle(2,15,$title,150,150,150);

//---------------------------- Font

 $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",8);  
  
//---------------------------- Set GraphArea  
  
 $Test->setGraphArea(0,0,$w,$h);  
 
//---------------------------- Set background graphics (R,G,G,1/Y)

 if ($bg_r!=0 && $bg_g!=0 && $bg_b!=0) {$Test->drawGraphAreaGradient($bg_r, $bg_g, $bg_b,5);}
 
//---------------------------- Calc center

 $midX=$w/2;
 $midY=$h/2;

//---------------------------- Draw pie
 
 if ($_GET['gtype']=='pie') {
   $Test->drawBasicPieGraph($DataSet->GetData(),$DataSet->GetDataDescription(),$midX,$midY,$radius,PIE_PERCENTAGE_LABEL,$cbg,$cbg,$cbg,1);
 } elseif ($_GET['gtype']=='pie2') {
   $Test->drawFlatPieGraph($DataSet->GetData(),$DataSet->GetDataDescription(),$midX,$midY,$radius,PIE_PERCENTAGE_LABEL,5,1);
 } elseif ($_GET['gtype']=='pie3') {
   $Test->setShadowProperties(3,3,$csh,$csh,$csh,90);
   $Test->drawFlatPieGraphWithShadow($DataSet->GetData(),$DataSet->GetDataDescription(),$midX,$midY,$radius,PIE_PERCENTAGE_LABEL,5,1);
 } elseif ($_GET['gtype']=='pie4') {
   $Test->drawPieGraph($DataSet->GetData(),$DataSet->GetDataDescription(),$midX,$midY,$radius,PIE_PERCENTAGE_LABEL,TRUE,60,20,5,1);
 } else {
   //
 }


//---------------------------- Border

 if ($_GET['border']=='1') {
   $Test->AddBorder(1, 200,200,200);
 }

//---------------------------- Image PNG
 
 Header("Content-type:image/png");
 imagepng($Test->Picture);
 //$Test->Render();
