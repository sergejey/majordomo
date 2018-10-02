<?php   
 
/*
pChart analog.php v.1.0.0

------------------- Parameters

&width=  - 260*
&height= - >=180 210*
&px=     - precision (10*)
&unit=   - ""*
&title=  - ""*
&scale=  - 1(SCALE_NORMAL)/-*(SCALE_START0)
&line=   - width of line (1*)
&point=  - radius of points (1*)
&points= - 1/-* draw points
&grid=   - 1/-* grid
&border= - 1/-* border

//------------------- Objects

&p=  - object1.value*
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

//period(!)
&type=1z        - 1 �����
&type=1d        - 1 день
&type=1h        - 1 час
&type=1m        - 1 месяц
//custom period
&start=13/02/6  - дата начала
&interva=86400  - секунд в сутках (1 день)
&resolution=900 - ���������� � ��������
&today=         - 1/-* today (0:00-23:59), &type required

//types of graphics(!)
&gtype=
curve - drawCubicCurve
bar   - drawBarGraph
line  - drawLineGraph
plot  - drawPlotGraph
fline - drawFilledLineGraph
fcurve- drawFilledCubicCurve

//------------------- Colors

//one (first) channel generic color
&gcolor= - (generic colors)

//generic channels colors
&g1color= - generic colors (-*)
&g2color= - generic colors (-*)
&g3color= - generic colors (-*)
&g4color= - generic colors (-*)
&g5color= - generic colors (-*)
&g6color= - generic colors (-*)
&g7color= - generic colors (-*)
&g8color= - generic colors (-*)
&g9color= - generic colors (-*)

//custom (RGB) channels colors
&c1r=, &c1g=, &c1b= - color of channel 1 (-*)
&c2r=, &c2g=, &c2b= - color of channel 2 (-*)
&c3r=, &c3g=, &c3b= - color of channel 3 (-*)
&c4r=, &c4g=, &c4b= - color of channel 4 (-*)
&c5r=, &c5g=, &c5b= - color of channel 5 (-*)
&c6r=, &c6g=, &c6b= - color of channel 6 (-*)
&c7r=, &c7g=, &c7b= - color of channel 7 (-*)
&c8r=, &c8g=, &c8b= - color of channel 8 (-*)
&c9r=, &c9g=, &c9b= - color of channel 9 (-*)

//generic colors
red   (220,50,50)
orange(220,190,50)
blue  (100,140,220)
green (100,220,140)
brown (220,140,100)
gray* (150,150,150)

//background colors
&bcolor= - color background (-*)
bgcolor= - background graphics (-*) 
&bg_r=,&bg_g=,&bg_b= - background custom colors RGB (-*)

//------------------- Filters

//"analog" filter 01
&c1fil01= - for channel 1
&c2fil01= - for channel 2
&c3fil01= - for channel 3
&c4fil01= - for channel 4
&c5fil01= - for channel 5
&c6fil01= - for channel 6
&c7fil01= - for channel 7
&c8fil01= - for channel 8
&c9fil01= - for channel 9

"digital" filter 02 
&c1fil02= - for channel 1
&c2fil02= - for channel 2
&c3fil02= - for channel 3
&c4fil02= - for channel 4
&c5fil02= - for channel 5
&c6fil02= - for channel 6
&c7fil02= - for channel 7
&c8fil02= - for channel 8
&c9fil02= - for channel 9

//------------------- Analytics

&remove= - 1/-* remove serie1 & serie2
&middle= - 1/-*

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
 include_once("./load_settings.php");
 include("./pChart/pData.class");   
 include("./pChart/pChart.class");  
 
//---------------------------- Settings 

 $settings = SQLSelect("SELECT NAME, VALUE FROM settings");
 $total = count($settings);
 for($i=0;$i<$total;$i++) {
   Define('SETTINGS_'.$settings[$i]['NAME'], $settings[$i]['VALUE']);
 }

//---------------------------- Width & height of graphics
 
 if (!$width) {$w=260;} else {$w=(int)$width;}
 if (!$height){$h=210;} else {$h=(int)$height;}
 
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
   
//---------------------------- fil01 
  
 if ($_GET['c1fil01']) {$c1fil01=$_GET['c1fil01'];} else {$c1fil01=0;}
 if ($_GET['c2fil01']) {$c2fil01=$_GET['c2fil01'];} else {$c2fil01=0;}
 if ($_GET['c3fil01']) {$c3fil01=$_GET['c3fil01'];} else {$c3fil01=0;}
 if ($_GET['c4fil01']) {$c4fil01=$_GET['c4fil01'];} else {$c4fil01=0;}
 if ($_GET['c5fil01']) {$c5fil01=$_GET['c5fil01'];} else {$c5fil01=0;}
 if ($_GET['c6fil01']) {$c6fil01=$_GET['c6fil01'];} else {$c6fil01=0;}
 if ($_GET['c7fil01']) {$c7fil01=$_GET['c7fil01'];} else {$c7fil01=0;}
 if ($_GET['c8fil01']) {$c8fil01=$_GET['c8fil01'];} else {$c8fil01=0;}
 if ($_GET['c9fil01']) {$c9fil01=$_GET['c9fil01'];} else {$c9fil01=0;} 
 
//---------------------------- fil02 
  
 if ($_GET['c1fil02']) {$c1fil02=$_GET['c1fil02'];} else {$c1fil02=0;}
 if ($_GET['c2fil02']) {$c2fil02=$_GET['c2fil02'];} else {$c2fil02=0;}     
 if ($_GET['c3fil02']) {$c3fil02=$_GET['c3fil02'];} else {$c3fil02=0;} 
 if ($_GET['c4fil02']) {$c4fil02=$_GET['c4fil02'];} else {$c4fil02=0;} 
 if ($_GET['c5fil02']) {$c5fil02=$_GET['c5fil02'];} else {$c5fil02=0;} 
 if ($_GET['c6fil02']) {$c6fil02=$_GET['c6fil02'];} else {$c6fil02=0;} 
 if ($_GET['c7fil02']) {$c7fil02=$_GET['c7fil02'];} else {$c7fil02=0;} 
 if ($_GET['c8fil02']) {$c8fil02=$_GET['c8fil02'];} else {$c8fil02=0;} 
 if ($_GET['c9fil02']) {$c9fil02=$_GET['c9fil02'];} else {$c9fil02=0;} 

//---------------------------- Middle
  
if ($_GET['middle']) {$middle=$_GET['middle'];} 
 else {$middle=0;}
  
//---------------------------- Revove
  
if ($_GET['remove']) {$remove=$_GET['remove'];} 
 else {$remove=0;}

//---------------------------- Labels
  
if ($_GET['labels']) {$labels=$_GET['labels'];} 
 else {$labels=0;}  
  
//---------------------------- Filter 01

function filter01($values, $all, $fil01) {

  for($z=0; $z<$fil01; $z++){
    for($i=0; $i<$all-1; $i++){
      if ($values[$i]!=0 && $values[$i+1]!=0) {
        $values[$i]=($values[$i]+$values[$i+1])/2;
	  }
    }
    for($i=$all-1; $i>=0; $i--){
      if ($values[$i]!=0 && $values[$i-1]!=0) {
        $values[$i]=($values[$i]+$values[$i-1])/2;
      }
    }
  } //for($z)
  return $values;
}

//---------------------------- Filter 02

function filter02($val, $al, $fil) {

  for($z=0; $z<$fil; $z++){
    for($i=0; $i<$al-1; $i++){
      if ($val[$i]==0) {
        $val[$i]=$val[$i+1];
	  }
    }
    for($i=$al-1; $i>=0; $i--){
      if ($val[$i]==0) {
        $val[$i]=$val[$i-1];
      }
    }
  } //for($z)
  return $val;
}  
  
//---------------------------- Dataset definition   

 $DataSet = new pData;
 
//---------------------------- Get object

 if ($p!='') {
   if (preg_match('/(.+)\.(.+)/is', $p, $m)) {
     $obj = getObject($m[1]);
     $prop_id = $obj->getPropertyByName($m[2], $obj->class_id, $obj->id);
   }
 }
 $pvalue=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id."' AND OBJECT_ID='".$obj->id."'");
 if (!$pvalue['ID']) {exit;}

//---------------------------- Get object 2

 if ($p2!='') {
   if (preg_match('/(.+)\.(.+)/is', $p2, $m2)) {
     $obj2 = getObject($m2[1]);
     $prop_id2 = $obj2->getPropertyByName($m2[2], $obj2->class_id, $obj2->id);
   }
 }
 $pvalue2=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id2."' AND OBJECT_ID='".$obj2->id."'");
 if(!$pvalue2['ID']) {$p2='';}

//---------------------------- Get object 3

 if ($p3!='') {
   if (preg_match('/(.+)\.(.+)/is', $p3, $m3)) {
     $obj3 = getObject($m3[1]);
     $prop_id3 = $obj3->getPropertyByName($m3[2], $obj3->class_id, $obj3->id);
   }
 }
 $pvalue3=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id3."' AND OBJECT_ID='".$obj3->id."'");
 if(!$pvalue3['ID']) {$p3='';}
 
//---------------------------- Get object 4

 if ($p4!='') {
   if (preg_match('/(.+)\.(.+)/is', $p4, $m4)) {
     $obj4 = getObject($m4[1]);
     $prop_id4 = $obj4->getPropertyByName($m4[2], $obj4->class_id, $obj4->id);
   }
 }
 $pvalue4=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id4."' AND OBJECT_ID='".$obj4->id."'");
 if(!$pvalue4['ID']) {$p4='';}

//---------------------------- Get object 5

 if ($p5!='') {
   if (preg_match('/(.+)\.(.+)/is', $p5, $m5)) {
     $obj5 = getObject($m5[1]);
     $prop_id5 = $obj5->getPropertyByName($m5[2], $obj5->class_id, $obj5->id);
   }
 }
 $pvalue5=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id5."' AND OBJECT_ID='".$obj5->id."'");
 if(!$pvalue5['ID']) {$p5='';}

//---------------------------- Get object 6

 if ($p6!='') {
   if (preg_match('/(.+)\.(.+)/is', $p6, $m6)) {
     $obj6 = getObject($m6[1]);
     $prop_id6 = $obj6->getPropertyByName($m6[2], $obj6->class_id, $obj6->id);
   }
 }
 $pvalue6=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id6."' AND OBJECT_ID='".$obj6->id."'");
 if(!$pvalue6['ID']) {$p6='';}

//---------------------------- Get object 7

 if ($p7!='') {
   if (preg_match('/(.+)\.(.+)/is', $p7, $m7)) {
     $obj7 = getObject($m7[1]);
     $prop_id7 = $obj7->getPropertyByName($m7[2], $obj7->class_id, $obj7->id);
   }
 }
 $pvalue7=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id7."' AND OBJECT_ID='".$obj7->id."'");
 if(!$pvalue7['ID']) {$p7='';}

//---------------------------- Get object 8

 if ($p8!='') {
   if (preg_match('/(.+)\.(.+)/is', $p8, $m8)) {
     $obj8 = getObject($m8[1]);
     $prop_id8 = $obj8->getPropertyByName($m8[2], $obj8->class_id, $obj8->id);
   }
 }
 $pvalue8=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id8."' AND OBJECT_ID='".$obj8->id."'");
 if(!$pvalue8['ID']) {$p8='';}

//---------------------------- Get object 9

 if ($p9!='') {
   if (preg_match('/(.+)\.(.+)/is', $p9, $m9)) {
     $obj9 = getObject($m9[1]);
     $prop_id9 = $obj9->getPropertyByName($m9[2], $obj9->class_id, $obj9->id);
   }
 }
 $pvalue9=SQLSelectOne("SELECT * FROM pvalues WHERE PROPERTY_ID='".$prop_id9."' AND OBJECT_ID='".$obj9->id."'");
 if(!$pvalue9['ID']) {$p9='';}

//---------------------------- end_time
 
 $end_time=time();
 //$end_time=$end_time + 3600; //time correction (1 hour)
 
//---------------------------- today

 if ($_GET['today']==1) {$today=1;} 
  else {$today=0;}
   
 if ($today) {
     $end_time=timeConvert("23:59")+59; //-3600
 }    
   
//---------------------------- Precision
 
 if ($_GET['px']) {
   $px_per_point = (int)$_GET['px'];
 } else {
     $px_per_point=10; //precision
   }
   
//---------------------------- start_time (end_time) & period def
   
 if (preg_match('/(\d+)d/', $type, $m)) { //days (86400) &type=1d
   $total = (int)$m[1];
   $period = round(($total*24*60*60) / (($w-80)/$px_per_point));
   $start_time = $end_time-$total*24*60*60;

 } elseif (preg_match('/(\d+)h/', $type, $m)) { //hours (3600) &type=1h
   $total = (int)$m[1];
   $period = round(($total*60*60) / (($w-80)/$px_per_point));
   $start_time = $end_time-$total*60*60;

 } elseif (preg_match('/(\d+)m/', $type, $m)) { //minutes (60) &type=1m
   $total = (int)$m[1];
   $period = round(($total*60) / (($w-80)/$px_per_point));
   $start_time = $end_time-$total*60;

  } elseif (preg_match('/(\d+)z/', $type, $m)) { //months (2678400) &type=1z
   $total = (int)$m[1];
   $period = round(($total*31*24*60*60) / (($w-80)/$px_per_point));
   $start_time = $end_time-$total*31*24*60*60;
   
  } elseif (preg_match('/(\d+)\/(\d+)\/(\d+)/', $_GET['start'], $m) && $_GET['resolution']) { // &start=12/12/6 &resolution=900 &interval86400
   $period = (int)$_GET['resolution']; //seconds
   $start_time = mktime(0, 0, 0, $m[2], $m[3], $m[1]);
   $total=1;
   $end_time = $start_time+$_GET['interval'];
 } // end if
 
//---------------------------- Channel 1
 
  if ($total>0) {
   $px=0;
   $px_passed=0;
   $dt=date('Y-m-d',$start_time);
   
   $wid=$w-90;
   $shk=$wid/4;
   
   $start_time2=$start_time;
   $start_time3=$start_time;
   $start_time4=$start_time;
   $start_time5=$start_time;
   $start_time6=$start_time;
   $start_time7=$start_time;
   $start_time8=$start_time;
   $start_time9=$start_time;
   
   $history=SQLSelect("SELECT ID, VALUE, UNIX_TIMESTAMP(ADDED) as UNX FROM phistory WHERE VALUE_ID='".$pvalue['ID']."' AND ADDED>=('".date('Y-m-d H:i:s', $start_time)."') AND ADDED<=('".date('Y-m-d H:i:s', $end_time)."') ORDER BY ADDED");
   $total=count($history);
   
   $t1=date('H:i', $start_time);
   $t2=date('H:i', $end_time);
   $period_refresh=60;
   $not_empty=0;
   $itm=0;
   $tmp=0;
   
   for($i=0;$i<$total;$i++) {
     $unx=$history[$i]['UNX'];
	 
     if ($unx>=$start_time || $i==0) {
	   $not_empty=1;
	   if (($unx>=$start_time && $unx<=$start_time+$period_refresh) || $i==0) {
         $values[$itm]=(float)$history[$i]['VALUE'];
		 $tmp=$values[$itm];
       }else{
	      $values[$itm]=$tmp;
        }	   

       $itm++;
	   $temp_time=$start_time;
       $start_time+=$period;
	   
       if ($px_passed > $shk) {
         if (date('Y-m-d', $unx)!=$dt) {
           $hours[]=date('d/m',$unx);
           $dt=date('Y-m-d',$unx);
         } else {
             $hours[]=date('H:i',$temp_time);
           }
         $px_passed=0;
       } else {
           $hours[]='';
         }
       $px+=$px_per_point;
       $px_passed+=$px_per_point;
     }
   }

   if ($not_empty==0){
	 $values[0]=0; $hours[0]=$t1;
     $values[2]=0; $hours[2]=$t2;
   }  

  $allh = count($hours);
  $hours[0]=$t1;
  $hours[$allh-1]=$t2;
   
//----- filters 1

  $all = count($values);
  if ($c1fil01>0) {$values=filter01($values,$all,$c1fil01);}
  if ($c1fil02>0) {$values=filter02($values,$all,$c1fil02);}
 
//----- final 1
 
   $DataSet->AddPoint($values,"Serie1");  
   $DataSet->AddPoint($hours,"Serie0");  
   } else {
   $DataSet->AddPoint(0,"Serie1");
   $DataSet->AddPoint(0,"Serie0");
  } //end if ($total>0)

//---------------------------- Channel 2

  if($p2!='') {
   $history2=SQLSelect("SELECT ID, VALUE, UNIX_TIMESTAMP(ADDED) as UNX FROM phistory WHERE VALUE_ID='".$pvalue2['ID']."' AND ADDED>=('".date('Y-m-d H:i:s', $start_time2)."') AND ADDED<=('".date('Y-m-d H:i:s', $end_time)."') ORDER BY ADDED");
   $total2=count($history2);
   $itm=0; $tmp=0;

   for($i=0;$i<$total2;$i++) {
     $unx=$history2[$i]['UNX'];
     if ($unx>=$start_time2 || $i==0) {
	   if (($unx>=$start_time2 && $unx<=$start_time2+$period_refresh) || $i==0) {
         $values2[$itm]=(float)$history2[$i]['VALUE'];
		 $tmp=$values2[$itm];
       }else{
	      $values2[$itm]=$tmp;
        }	   
       $itm++;
	   $temp_time=$start_time2;
       $start_time2+=$period;
     }
   }// end for
  $all2 = count($values2);
  if ($c2fil01>0) {$values2=filter01($values2,$all2,$c2fil01);}
  if ($c2fil02>0) {$values2=filter02($values2,$all2,$c2fil02);}
  $DataSet->AddPoint($values2,"Serie2");  
  } //end if ($p2!='')
  
//---------------------------- Channel 3

  if($p3!='') {
   $history3=SQLSelect("SELECT ID, VALUE, UNIX_TIMESTAMP(ADDED) as UNX FROM phistory WHERE VALUE_ID='".$pvalue3['ID']."' AND ADDED>=('".date('Y-m-d H:i:s', $start_time3)."') AND ADDED<=('".date('Y-m-d H:i:s', $end_time)."') ORDER BY ADDED");
   $total3=count($history3);
   $itm=0; $tmp=0;

   for($i=0;$i<$total3;$i++) {
     $unx=$history3[$i]['UNX'];
     if ($unx>=$start_time3 || $i==0) {
	   if (($unx>=$start_time3 && $unx<=$start_time3+$period_refresh) || $i==0) {
         $values3[$itm]=(float)$history3[$i]['VALUE'];
		 $tmp=$values3[$itm];
       }else{
	      $values3[$itm]=$tmp;
        }	   
       $itm++;
	   $temp_time=$start_time3;
       $start_time3+=$period;
     }
   }// end for
  $all3 = count($values3);
  if ($c3fil01>0) {$values3=filter01($values3,$all3,$c3fil01);}
  if ($c3fil02>0) {$values3=filter02($values3,$all3,$c3fil02);}
  $DataSet->AddPoint($values3,"Serie3");  
  } //end if ($p3!='')
  
//---------------------------- Channel 4

  if($p4!='') {
   $history4=SQLSelect("SELECT ID, VALUE, UNIX_TIMESTAMP(ADDED) as UNX FROM phistory WHERE VALUE_ID='".$pvalue4['ID']."' AND ADDED>=('".date('Y-m-d H:i:s', $start_time4)."') AND ADDED<=('".date('Y-m-d H:i:s', $end_time)."') ORDER BY ADDED");
   $total4=count($history4);
   $itm=0; $tmp=0;

   for($i=0;$i<$total4;$i++) {
     $unx=$history4[$i]['UNX'];
     if ($unx>=$start_time4 || $i==0) {
	   if (($unx>=$start_time4 && $unx<=$start_time4+$period_refresh) || $i==0) {
         $values4[$itm]=(float)$history4[$i]['VALUE'];
		 $tmp=$values4[$itm];
       }else{
	      $values4[$itm]=$tmp;
        }	   
       $itm++;
	   $temp_time=$start_time4;
       $start_time4+=$period;
     }
   }// end for
  $all4 = count($values4);
  if ($c4fil01>0) {$values4=filter01($values4,$all4,$c4fil01);}
  if ($c4fil02>0) {$values4=filter02($values4,$all4,$c4fil02);}
  $DataSet->AddPoint($values4,"Serie4");  
  } //end if ($p4!='')

//---------------------------- Channel 5

  if($p5!='') {
   $history5=SQLSelect("SELECT ID, VALUE, UNIX_TIMESTAMP(ADDED) as UNX FROM phistory WHERE VALUE_ID='".$pvalue5['ID']."' AND ADDED>=('".date('Y-m-d H:i:s', $start_time5)."') AND ADDED<=('".date('Y-m-d H:i:s', $end_time)."') ORDER BY ADDED");
   $total5=count($history5);
   $itm=0; $tmp=0;

   for($i=0;$i<$total5;$i++) {
     $unx=$history5[$i]['UNX'];
     if ($unx>=$start_time5 || $i==0) {
	   if (($unx>=$start_time5 && $unx<=$start_time5+$period_refresh) || $i==0) {
         $values5[$itm]=(float)$history5[$i]['VALUE'];
		 $tmp=$values5[$itm];
       }else{
	      $values5[$itm]=$tmp;
        }	   
       $itm++;
	   $temp_time=$start_time5;
       $start_time5+=$period;
     }
   }// end for
  $all5 = count($values5);
  if ($c5fil01>0) {$values5=filter01($values5,$all5,$c5fil01);}
  if ($c5fil02>0) {$values5=filter02($values5,$all5,$c5fil02);}
  $DataSet->AddPoint($values5,"Serie5");  
  } //end if ($p5!='')

//---------------------------- Channel 6

  if($p6!='') {
   $history6=SQLSelect("SELECT ID, VALUE, UNIX_TIMESTAMP(ADDED) as UNX FROM phistory WHERE VALUE_ID='".$pvalue6['ID']."' AND ADDED>=('".date('Y-m-d H:i:s', $start_time6)."') AND ADDED<=('".date('Y-m-d H:i:s', $end_time)."') ORDER BY ADDED");
   $total6=count($history6);
   $itm=0; $tmp=0;

   for($i=0;$i<$total6;$i++) {
     $unx=$history6[$i]['UNX'];
     if ($unx>=$start_time6 || $i==0) {
	   if (($unx>=$start_time6 && $unx<=$start_time6+$period_refresh) || $i==0) {
         $values6[$itm]=(float)$history6[$i]['VALUE'];
		 $tmp=$values6[$itm];
       }else{
	      $values6[$itm]=$tmp;
        }	   
       $itm++;
	   $temp_time=$start_time6;
       $start_time6+=$period;
     }
   }// end for
  $all6 = count($values6);
  if ($c6fil01>0) {$values6=filter01($values6,$all6,$c6fil01);}
  if ($c6fil02>0) {$values6=filter02($values6,$all6,$c6fil02);}
  $DataSet->AddPoint($values6,"Serie6");  
  } //end if ($p6!='')

//---------------------------- Channel 7

  if($p7!='') {
   $history7=SQLSelect("SELECT ID, VALUE, UNIX_TIMESTAMP(ADDED) as UNX FROM phistory WHERE VALUE_ID='".$pvalue7['ID']."' AND ADDED>=('".date('Y-m-d H:i:s', $start_time7)."') AND ADDED<=('".date('Y-m-d H:i:s', $end_time)."') ORDER BY ADDED");
   $total7=count($history7);
   $itm=0; $tmp=0;

   for($i=0;$i<$total7;$i++) {
     $unx=$history7[$i]['UNX'];
     if ($unx>=$start_time7 || $i==0) {
	   if (($unx>=$start_time7 && $unx<=$start_time7+$period_refresh) || $i==0) {
         $values7[$itm]=(float)$history7[$i]['VALUE'];
		 $tmp=$values7[$itm];
       }else{
	      $values7[$itm]=$tmp;
        }	   
       $itm++;
	   $temp_time=$start_time7;
       $start_time7+=$period;
     }
   }// end for
  $all7 = count($values7);
  if ($c7fil01>0) {$values7=filter01($values7,$all7,$c7fil01);}
  if ($c7fil02>0) {$values7=filter02($values7,$all7,$c7fil02);}
  $DataSet->AddPoint($values7,"Serie7");  
  } //end if ($p7!='')

//---------------------------- Channel 8

  if($p8!='') {
   $history8=SQLSelect("SELECT ID, VALUE, UNIX_TIMESTAMP(ADDED) as UNX FROM phistory WHERE VALUE_ID='".$pvalue8['ID']."' AND ADDED>=('".date('Y-m-d H:i:s', $start_time8)."') AND ADDED<=('".date('Y-m-d H:i:s', $end_time)."') ORDER BY ADDED");
   $total8=count($history8);
   $itm=0; $tmp=0;

   for($i=0;$i<$total8;$i++) {
     $unx=$history8[$i]['UNX'];
     if ($unx>=$start_time8 || $i==0) {
	   if (($unx>=$start_time8 && $unx<=$start_time8+$period_refresh) || $i==0) {
         $values8[$itm]=(float)$history8[$i]['VALUE'];
		 $tmp=$values8[$itm];
       }else{
	      $values8[$itm]=$tmp;
        }	   
       $itm++;
	   $temp_time=$start_time8;
       $start_time8+=$period;
     }
   }// end for
  $all8 = count($values8);
  if ($c8fil01>0) {$values8=filter01($values8,$all8,$c8fil01);}
  if ($c8fil02>0) {$values8=filter02($values8,$all8,$c8fil02);}
  $DataSet->AddPoint($values8,"Serie8");  
  } //end if ($p8!='')

//---------------------------- Channel 9

  if($p9!='') {
   $history9=SQLSelect("SELECT ID, VALUE, UNIX_TIMESTAMP(ADDED) as UNX FROM phistory WHERE VALUE_ID='".$pvalue9['ID']."' AND ADDED>=('".date('Y-m-d H:i:s', $start_time9)."') AND ADDED<=('".date('Y-m-d H:i:s', $end_time)."') ORDER BY ADDED");
   $total9=count($history9);
   $itm=0; $tmp=0;

   for($i=0;$i<$total9;$i++) {
     $unx=$history9[$i]['UNX'];
     if ($unx>=$start_time9 || $i==0) {
	   if (($unx>=$start_time9 && $unx<=$start_time9+$period_refresh) || $i==0) {
         $values9[$itm]=(float)$history9[$i]['VALUE'];
		 $tmp=$values9[$itm];
       }else{
	      $values9[$itm]=$tmp;
        }	   
       $itm++;
	   $temp_time=$start_time9;
       $start_time9+=$period;
     }
   }// end for
  $all9 = count($values9);
  if ($c9fil01>0) {$values9=filter01($values9,$all9,$c9fil01);}
  if ($c9fil02>0) {$values9=filter02($values9,$all9,$c9fil02);}
  $DataSet->AddPoint($values9,"Serie9");  
  } //end if ($p9!='')
  

//---------------------------- Middle  
  
 if($middle!=0){
   
   $t1=count($values);
   $t2=count($values2);
   
   if($t1<$t2){ //?
     $total_m=$t1;
   }else{
      $total_m=$t2;
    }

   for($z=0; $z<$total_m; $z++){
     $values_middle[$z]=($values[$z]+$values2[$z])/2;
   } 
   $DataSet->AddPoint($values_middle,"Serie10");
} 
  
//---------------------------- DataSet Series

 if($remove!=0){
  
   for($z=0; $z<$total_m; $z++){
     $values_z[$z]=0;
   } 
   $DataSet->AddPoint($values_z,"Serie11");
 }

 $DataSet->AddAllSeries();  
 $DataSet->RemoveSerie("Serie0");  
 if ($p2=='') {$DataSet->RemoveSerie("Serie2");}
 
 if($remove!=0){
  $DataSet->RemoveSerie("Serie1");  
  $DataSet->RemoveSerie("Serie2");  
 }
 
 $DataSet->SetAbsciseLabelSerie("Serie0");  
 $DataSet->SetSerieName("24 hours","Serie1");  

//---------------------------- Set X & Y axis units
  
 if ($unit) {
    $DataSet->SetYAxisUnit($unit);
 } else {
     $DataSet->SetYAxisUnit("");  
   }
 $DataSet->SetXAxisUnit("");  
   
//---------------------------- Create Object of pChart  
  
 $Test = new pChart($w,$h);  
 
//---------------------------- Set generic colors for one (first) channel
  
 if       ($_GET['gcolor']=='red')   {$Test->setColorPalette(0,220,50,50);
 } elseif ($_GET['gcolor']=='brown') {$Test->setColorPalette(0,220,140,100);
 } elseif ($_GET['gcolor']=='blue')  {$Test->setColorPalette(0,100,140,220);
 } elseif ($_GET['gcolor']=='green') {$Test->setColorPalette(0,100,200,100);
 } elseif ($_GET['gcolor']=='orange'){$Test->setColorPalette(0,220,190,50);
 } else                              {$Test->setColorPalette(0,150,150,150);}

//---------------------------- Set generic colors for channels 1-9
 
 if       ($_GET['g1color']=='red')   {$Test->setColorPalette(0,220,50,50);
 } elseif ($_GET['g1color']=='brown') {$Test->setColorPalette(0,220,140,100);
 } elseif ($_GET['g1color']=='blue')  {$Test->setColorPalette(0,100,140,220);
 } elseif ($_GET['g1color']=='green') {$Test->setColorPalette(0,100,200,100);
 } elseif ($_GET['g1color']=='orange'){$Test->setColorPalette(0,220,190,50);
 } else { }
 
 if       ($_GET['g2color']=='red')   {$Test->setColorPalette(1,220,50,50);
 } elseif ($_GET['g2color']=='brown') {$Test->setColorPalette(1,220,140,100);
 } elseif ($_GET['g2color']=='blue')  {$Test->setColorPalette(1,100,140,220);
 } elseif ($_GET['g2color']=='green') {$Test->setColorPalette(1,100,200,100);
 } elseif ($_GET['g2color']=='orange'){$Test->setColorPalette(1,220,190,50);
 } else { }

 if       ($_GET['g3color']=='red')   {$Test->setColorPalette(2,220,50,50);
 } elseif ($_GET['g3color']=='brown') {$Test->setColorPalette(2,220,140,100);
 } elseif ($_GET['g3color']=='blue')  {$Test->setColorPalette(2,100,140,220);
 } elseif ($_GET['g3color']=='green') {$Test->setColorPalette(2,100,200,100);
 } elseif ($_GET['g3color']=='orange'){$Test->setColorPalette(2,220,190,50);
 } else { } 
 
 if       ($_GET['g4color']=='red')   {$Test->setColorPalette(2,220,50,50);
 } elseif ($_GET['g4color']=='brown') {$Test->setColorPalette(2,220,140,100);
 } elseif ($_GET['g4color']=='blue')  {$Test->setColorPalette(2,100,140,220);
 } elseif ($_GET['g4color']=='green') {$Test->setColorPalette(2,100,200,100);
 } elseif ($_GET['g4color']=='orange'){$Test->setColorPalette(2,220,190,50);
 } else { } 
 
 if       ($_GET['g5color']=='red')   {$Test->setColorPalette(2,220,50,50);
 } elseif ($_GET['g5color']=='brown') {$Test->setColorPalette(2,220,140,100);
 } elseif ($_GET['g5color']=='blue')  {$Test->setColorPalette(2,100,140,220);
 } elseif ($_GET['g5color']=='green') {$Test->setColorPalette(2,100,200,100);
 } elseif ($_GET['g5color']=='orange'){$Test->setColorPalette(2,220,190,50);
 } else { } 
 
 if       ($_GET['g6color']=='red')   {$Test->setColorPalette(2,220,50,50);
 } elseif ($_GET['g6color']=='brown') {$Test->setColorPalette(2,220,140,100);
 } elseif ($_GET['g6color']=='blue')  {$Test->setColorPalette(2,100,140,220);
 } elseif ($_GET['g6color']=='green') {$Test->setColorPalette(2,100,200,100);
 } elseif ($_GET['g6color']=='orange'){$Test->setColorPalette(2,220,190,50);
 } else { } 
 
 if       ($_GET['g7color']=='red')   {$Test->setColorPalette(2,220,50,50);
 } elseif ($_GET['g7color']=='brown') {$Test->setColorPalette(2,220,140,100);
 } elseif ($_GET['g7color']=='blue')  {$Test->setColorPalette(2,100,140,220);
 } elseif ($_GET['g7color']=='green') {$Test->setColorPalette(2,100,200,100);
 } elseif ($_GET['g7color']=='orange'){$Test->setColorPalette(2,220,190,50);
 } else { } 
 
 if       ($_GET['g8color']=='red')   {$Test->setColorPalette(2,220,50,50);
 } elseif ($_GET['g8color']=='brown') {$Test->setColorPalette(2,220,140,100);
 } elseif ($_GET['g8color']=='blue')  {$Test->setColorPalette(2,100,140,220);
 } elseif ($_GET['g8color']=='green') {$Test->setColorPalette(2,100,200,100);
 } elseif ($_GET['g8color']=='orange'){$Test->setColorPalette(2,220,190,50);
 } else { } 
 
 if       ($_GET['g9color']=='red')   {$Test->setColorPalette(2,220,50,50);
 } elseif ($_GET['g9color']=='brown') {$Test->setColorPalette(2,220,140,100);
 } elseif ($_GET['g9color']=='blue')  {$Test->setColorPalette(2,100,140,220);
 } elseif ($_GET['g9color']=='green') {$Test->setColorPalette(2,100,200,100);
 } elseif ($_GET['g9color']=='orange'){$Test->setColorPalette(2,220,190,50);
 } else { } 
 
//---------------------------- Remove color correction
 
 if($remove!=0) {

   if       ($_GET['gcolor']=='red')   {$Test->setColorPalette(1,220,50,50);
   } elseif ($_GET['gcolor']=='brown') {$Test->setColorPalette(1,220,140,100);
   } elseif ($_GET['gcolor']=='blue')  {$Test->setColorPalette(1,100,140,220);
   } elseif ($_GET['gcolor']=='green') {$Test->setColorPalette(1,100,200,100);
   } elseif ($_GET['gcolor']=='orange'){$Test->setColorPalette(1,220,190,50);
   } else                              {$Test->setColorPalette(1,150,150,150);}

   $Test->setColorPalette(2,0,0,0); 
 }

//---------------------------- Set [bcolor] background (R,G,G,1/Y)
  
 if       ($_GET['bcolor']=='red')  {$Test->drawGraphAreaGradient(250,210,210,50,TARGET_BACKGROUND);
 } elseif ($_GET['bcolor']=='blue') {$Test->drawGraphAreaGradient(170,220,250,50,TARGET_BACKGROUND);
 } elseif ($_GET['bcolor']=='green'){$Test->drawGraphAreaGradient(210,250,210,50,TARGET_BACKGROUND);
 } else { }
  
//---------------------------- Title
  
 $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",10);  
 if ($_GET['title']) {
  $Test->drawTitle(100,15,$_GET['title'],150,150,150);
 } else { }

//---------------------------- Font

 $Test->setFontProperties("./pChart/Fonts/tahoma.ttf",8);  
  
//---------------------------- Set GraphArea  
  
 $Test->setGraphArea(60,20,$w-25,$h-30);  
 
//---------------------------- Set [bgcolor] background graphics (R,G,G,1/Y)

 if       ($_GET['bgcolor']=='red')  {$Test->drawGraphAreaGradient(250,210,210,5);
 } elseif ($_GET['bgcolor']=='blue') {$Test->drawGraphAreaGradient(170,220,250,5);
 } elseif ($_GET['bgcolor']=='green'){$Test->drawGraphAreaGradient(220,250,220,5);
 } else { } 
  
//---------------------------- Shadow
 
 $Test->clearShadow();

//---------------------------- Scale

 if ($_GET['scale']=='1') {$scale=1;}
  else {$scale=3;}

//---------------------------- drawScale

 $Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),$scale,80,80,80,TRUE,0,2);
  
//---------------------------- Grid  

 if ($_GET['grid']=='0') {
   //
 } else {
     $Test->drawGrid(1,TRUE,230,230,230,50);
   }

//---------------------------- Set linestyle
  
 if ($_GET['line']) {$line=$_GET['line'];} 
  else {$line=1;}
  
 $Test->setLineStyle($line,0);
 
//---------------------------- Set pointsstyle
  
 if ($_GET['point']) {$point=$_GET['point'];} 
  else {$point=1;} 
  
//---------------------------- Set custom RGB colors of channels 1-9

 if ($p !='' && $c1r!=0) {$Test->setColorPalette(0,$c1r,$c1g,$c1b);}
 if ($p2!='' && $c2r!=0) {$Test->setColorPalette(1,$c2r,$c2g,$c2b);}
 if ($p3!='' && $c3r!=0) {$Test->setColorPalette(2,$c3r,$c3g,$c3b);}
 if ($p4!='' && $c4r!=0) {$Test->setColorPalette(3,$c4r,$c4g,$c4b);}
 if ($p5!='' && $c5r!=0) {$Test->setColorPalette(4,$c5r,$c5g,$c5b);}
 if ($p6!='' && $c6r!=0) {$Test->setColorPalette(5,$c6r,$c6g,$c6b);}
 if ($p7!='' && $c7r!=0) {$Test->setColorPalette(6,$c7r,$c7g,$c7b);}
 if ($p8!='' && $c8r!=0) {$Test->setColorPalette(7,$c8r,$c8g,$c8b);}
 if ($p9!='' && $c9r!=0) {$Test->setColorPalette(8,$c9r,$c9g,$c9b);}  

//---------------------------- Draw points  

 if ($_GET['points']=='1') {
   $Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),$point); //plot
 } 
 
//---------------------------- Draw graphics 
 
 if       ($_GET['gtype']=='curve') {$Test->drawCubicCurve      ($DataSet->GetData(),$DataSet->GetDataDescription());
 } elseif ($_GET['gtype']=='bar')   {$Test->drawBarGraph        ($DataSet->GetData(),$DataSet->GetDataDescription(),FALSE);
 } elseif ($_GET['gtype']=='line')  {$Test->drawLineGraph       ($DataSet->GetData(),$DataSet->GetDataDescription());
 } elseif ($_GET['gtype']=='plot')  {$Test->drawPlotGraph       ($DataSet->GetData(),$DataSet->GetDataDescription(),$point);
 } elseif ($_GET['gtype']=='fline') {$Test->drawFilledLineGraph ($DataSet->GetData(),$DataSet->GetDataDescription(),20,FALSE);
 } elseif ($_GET['gtype']=='fcurve'){$Test->drawFilledCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription(),0.1,20,FALSE);
 } else { }

 
//---------------------------- Labels
 
 if($labels){
    if ($p!='' && $n1!="") {
     $h = count($hours);
     for($i=$h/2; $i>0; $i--){
       if ($hours[$i]!=0) {
         $r=$hours[$i];
       }
     }
     $Test->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie1",$r,$n1,221,230,174);
   }
   if ($p2!='' && $n2!="") {$Test->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie2",$r,$n2,221,230,174);}
   if ($p3!='' && $n3!="") {$Test->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie3",$r,$n3,221,230,174);}
   if ($p4!='' && $n4!="") {$Test->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie4",$r,$n4,221,230,174);}
   if ($p5!='' && $n5!="") {$Test->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie5",$r,$n5,221,230,174);}
   if ($p6!='' && $n6!="") {$Test->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie6",$r,$n6,221,230,174);}
   if ($p7!='' && $n7!="") {$Test->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie7",$r,$n7,221,230,174);}
   if ($p8!='' && $n8!="") {$Test->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie8",$r,$n8,221,230,174);}
   if ($p9!='' && $n9!="") {$Test->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie9",$r,$n9,221,230,174);}
 }
 
//---------------------------- Border

 if ($_GET['border']=='1') {
   $Test->AddBorder(1, 200,200,200);
 }

//---------------------------- Image PNG
 
 Header("Content-type:image/png");
 imagepng($Test->Picture);
 //$Test->Render();
