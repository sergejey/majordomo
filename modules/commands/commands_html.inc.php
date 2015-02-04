<?

//$result

 if (!is_array($result)) {
  return;
 }

 $total=count($result);
 for($i=0;$i<$total;$i++) {
  $rec=$result[$i];
  if ($rec['SUB_PRELOAD']) {
   //..
 $res.='<div data-role="collapsible" data-iconpos="right">';
  
  $res.='<h2><span  id="label_'.$rec['ID'].'">';
  if ($rec['ICON']!='') {
   $res.='<img src="'.ROOTHTML.'cms/icons/'.$rec['ICON'].'" alt="" style="margin-right:10px;top:0.4em;max-height:32px;max-width:32px;height:32px;width:32px;vertical-align:middle;">';
  }
  $res.=$rec['TITLE'];
  $res.='</span></h2>';
  $res.='<ul data-role="listview" data-inset="true">';
  $res.=$this->buildHTML($rec['RESULT']);
  $res.='</ul>';
  $res.='</div>';
  } else {
  //----------------------------------------------------------------------------------------------------------------------------
  if ($rec['TYPE']=='' || $rec['TYPE']=='command' || $rec['TYPE']=='window' || $rec['TYPE']=='url') {
  $res.='<li id=\'item[#ID#]\'>';
  if ($rec['VISIBLE_DELAY']!=0) {
   $res.=' class=\'visible_delay\'';
  }
  $res.='<a';
  if (!$rec['RESULT_TOTAL']) {
   $res.=' href="#" ';
   $res.=' onClick="return menuClicked(\''.$rec['ID'].'\', \''.$rec['PARENT_ID'].'\', \''.$rec['SUB_LIST'].'\', \''.$rec['WINDOW'].'\', \''.$rec['TITLE_SAFE'].'\', \''.$rec['COMMAND'].'\', \''.$rec['URL'].'\'';
   if ($rec['TYPE']=='window') {
    $res.=', \''.$rec['WIDTH'].'\', \''.$rec['HEIGHT'].'\'[#else#],0,0';
   }
   $res.=');"';
  } else {
   $res.=' href="'.ROOTHTML.'menu/'.$rec['ID'].'.html"';
  }
  if ($rec['SUB_PRELOAD']) {
  $res.=' onClick="$(\'#sublist'.$rec['ID'].'\').toggle();return false;"';
  }
  $res.='>';
  if ($rec['ICON']!='') {
   $res.='<img src="'.ROOTHTML.'cms/icons/'.$rec['ICON'].'" alt="" class="ui-li-icon" style="left:4px;top:0.4em;max-height:32px;max-width:32px;height:32px;width:32px;">';
  }
  $res.='<span id="label_'.$rec['ID'].'">'.$rec['TITLE'].'</span></a>';
  $res.='</li>';
  }
  }
  //----------------------------------------------------------------------------------------------------------------------------
  //----------------------------------------------------------------------------------------------------------------------------
  if ($rec['TYPE']=='custom') {
   $res.='<li ';
   if ($rec['VISIBLE_DELAY']!="0") {
   $res.='  class=\'visible_delay\'';
   }
   $res.=' id=\'item'.$rec['ID'].'\'>';
   $res.='<div id="label_'.$rec['ID'].'" style="white-space:normal">'.$rec['DATA'].'</div>';
   $res.='</li>';
  }
  //----------------------------------------------------------------------------------------------------------------------------
  //----------------------------------------------------------------------------------------------------------------------------
  //----------------------------------------------------------------------------------------------------------------------------
  //----------------------------------------------------------------------------------------------------------------------------
  //----------------------------------------------------------------------------------------------------------------------------
  //----------------------------------------------------------------------------------------------------------------------------


 }

