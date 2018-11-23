<?php

 global $mode;

 $languages=array();

 $languages[]=array('TITLE'=>'en','CAPTION'=>'English');
 $languages[]=array('TITLE'=>'ru','CAPTION'=>'Russian');
 $languages[]=array('TITLE'=>'ua','CAPTION'=>'Ukrainian');
 $languages[]=array('TITLE'=>'lt','CAPTION'=>'Lithuanian');
 $languages[]=array('TITLE'=>'el','CAPTION'=>'Greek');
 $languages[]=array('TITLE'=>'es','CAPTION'=>'Spanish');
 $languages[]=array('TITLE'=>'et','CAPTION'=>'Estonian');
 $languages[]=array('TITLE'=>'it','CAPTION'=>'Italian');
 $languages[]=array('TITLE'=>'bg','CAPTION'=>'Bulgarian');
 $languages[]=array('TITLE'=>'lv','CAPTION'=>'Latvian');
 $languages[]=array('TITLE'=>'ro','CAPTION'=>'Romanian');
 $languages[]=array('TITLE'=>'cs','CAPTION'=>'Czech');
 $languages[]=array('TITLE'=>'pl','CAPTION'=>'Polish');
 $languages[]=array('TITLE'=>'he','CAPTION'=>'Hebrew');

 function cmp_languages($a, $b) {
  return strcmp($a["CAPTION"], $b["CAPTION"]);
 }
 usort($languages,'cmp_languages');

 $out['LANGUAGES']=$languages;

$regions = array(
    'Africa' => DateTimeZone::AFRICA,
    'America' => DateTimeZone::AMERICA,
    'Antarctica' => DateTimeZone::ANTARCTICA,
    'Asia' => DateTimeZone::ASIA,
    'Atlantic' => DateTimeZone::ATLANTIC,
    'Europe' => DateTimeZone::EUROPE,
    'Indian' => DateTimeZone::INDIAN,
    'Pacific' => DateTimeZone::PACIFIC
);

foreach ($regions as $name => $mask) {
    $tzlist[] = DateTimeZone::listIdentifiers($mask);
}

foreach ($tzlist as $k=>$v) {
 foreach($v as $idx=>$zone) {
  $zn[]=$zone;
 }
}

$total=count($zn);
for($i=0;$i<$total;$i++) {
 $z = new DateTimeZone($zn[$i]);
 $c = new DateTime(null, $z);
 $offset=$z->getOffset($c)/60/60;
 if ($offset>0) {
  $offset_text='+'.$offset;
 } else {
  $offset_text=$offset;
 }
 $zones[]=array('TITLE'=>$zn[$i], 'OFFSET'=>$offset, 'OFFSET_TEXT'=>$offset_text);
}

  function sort_zones($a, $b) {
   if ($a['OFFSET'] == $b['OFFSET']) return strcmp($a["TITLE"], $b["TITLE"]); 
   return ($a['OFFSET'] < $b['OFFSET']) ? -1 : 1;
  }
  usort($zones, 'sort_zones');

 $out['ZONELIST']=$zones;

 global $timezone;
 foreach($zones as $k=>$v) {
  if (isset($timezone) && $v['OFFSET']==$timezone) {
   $tz=$v['TITLE'];
  }
 }

 global $timezone_title;
 if ($timezone_title) {
  $tz=trim(preg_replace('/\(GMT.+/is', '', $timezone_title));
 }



 if ($mode=='update') {

  global $language;
  global $theme;

  if (!$theme) {
   $theme='dark';
  }

  if (!$language) {
   $language='en';
  }

  $settings=array(
   array(
    'NAME'=>'SITE_LANGUAGE', 
    'TITLE'=>'Language', 
    'TYPE'=>'text',
    'DEFAULT'=>'en',
    'VALUE'=>$language
    ), 
   array(
    'NAME'=>'VOICE_LANGUAGE', 
    'TITLE'=>'Voice notifications language', 
    'TYPE'=>'text',
    'DEFAULT'=>'en',
    'VALUE'=>$language
    ), 
   array(
    'NAME'=>'SITE_TIMEZONE', 
    'TITLE'=>'Time zone', 
    'TYPE'=>'text',
    'DEFAULT'=>'Europe/Moscow',
    'VALUE'=>$tz
   ),
   array(
    'NAME'=>'THEME', 
    'TITLE'=>'Color theme', 
    'TYPE'=>'text',
    'DEFAULT'=>'dark',
    'VALUE'=>$theme
   ),
    array(
    'NAME'=>'SPEAK_SIGNAL',
    'TITLE'=>'Play sound signal before speaking', 
    'TYPE'=>'onoff',
    'DEFAULT'=>'1',
    'PRIORITY'=>'0'
   ),
    array(
    'NAME'=>'HOOK_BEFORE_SAY',
    'TITLE'=>'Before SAY (code)', 
    'TYPE'=>'text',
    'DEFAULT'=>'',
    'PRIORITY'=>'30'
   ), 

    array(
    'NAME'=>'HOOK_AFTER_SAY',
    'TITLE'=>'After SAY (code)', 
    'TYPE'=>'text',
    'DEFAULT'=>'',
    'PRIORITY'=>'29'
   )



   );




   foreach($settings as $k=>$v) {
    $rec=SQLSelectOne("SELECT * FROM settings WHERE NAME='".$v['NAME']."'");
    if (!$rec['ID']) {
     $rec['NAME']=$v['NAME'];
     if (!isset($v['VALUE'])) {
      $rec['VALUE']=$v['DEFAULT'];
     } else {
      $rec['VALUE']=$v['VALUE'];
     }
     $rec['DATA']='';
     $rec['DEFAULTVALUE']=$v['DEFAULT'];
     $rec['TITLE']=$v['TITLE'];
     $rec['TYPE']=$v['TYPE'];
     $rec['PRIORITY']=(int)$v['PRIORITY'];
     $rec['NOTES']='';
     $rec['ID']=SQLInsert('settings', $rec);
    } elseif (isset($v['VALUE'])) {
     $rec['VALUE']=$v['VALUE'];
     SQLUpdate('settings', $rec);
    }
    Define('SETTINGS_'.$rec['NAME'], $v['VALUE']);
   }

   @unlink(ROOT.'modules/control_modules/installed');
   SaveFile(ROOT.'reboot', '1');

   $this->redirect("/");
  
 }

?>
