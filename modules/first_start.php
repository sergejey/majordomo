<?php

 global $mode;

 $languages=array();

 $languages[]=array('TITLE'=>'en');
 $languages[]=array('TITLE'=>'ru');

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
    'NAME'=>'BLUETOOTH_CYCLE', 
    'TITLE'=>'Run bluetooth scanner on startup', 
    'TYPE'=>'onoff',
    'DEFAULT'=>'0',
    'PRIORITY'=>'51'
   ),
   array(
    'NAME'=>'SKYPE_CYCLE', 
    'TITLE'=>'Run Skype script on startup', 
    'TYPE'=>'onoff',
    'DEFAULT'=>'0',
    'PRIORITY'=>'50'
   ),
   array(
    'NAME'=>'THEME', 
    'TITLE'=>'Color theme', 
    'TYPE'=>'text',
    'DEFAULT'=>'dark',
    'VALUE'=>$theme
   )   ,
   array(
    'NAME'=>'TWITTER_CKEY', 
    'TITLE'=>'Twitter Consumer key', 
    'TYPE'=>'text',
    'DEFAULT'=>'',
    'PRIORITY'=>'30'
   )   ,
   array(
    'NAME'=>'TWITTER_CSECRET', 
    'TITLE'=>'Twitter Consumer secret', 
    'TYPE'=>'text',
    'DEFAULT'=>'',
    'PRIORITY'=>'29'
   )   ,
   array(
    'NAME'=>'TWITTER_ATOKEN', 
    'TITLE'=>'Twitter Access token', 
    'TYPE'=>'text',
    'DEFAULT'=>'',
    'PRIORITY'=>'28'
   ),
   array(
    'NAME'=>'TWITTER_ASECRET', 
    'TITLE'=>'Twitter Access token secret', 
    'TYPE'=>'text',
    'DEFAULT'=>'',
    'PRIORITY'=>'27'
   ),
    array(
    'NAME'=>'DEBUG_HISTORY', 
    'TITLE'=>'Save debug information to history', 
    'TYPE'=>'onoff',
    'DEFAULT'=>'0',
    'PRIORITY'=>'0'
   ),
    array(
    'NAME'=>'TTS_GOOGLE',
    'TITLE'=>'Use Google Text-to-Speech engine', 
    'TYPE'=>'onoff',
    'DEFAULT'=>'1',
    'PRIORITY'=>'60'
   ),
    array(
    'NAME'=>'SPEAK_SIGNAL',
    'TITLE'=>'Play sound signal before speaking', 
    'TYPE'=>'onoff',
    'DEFAULT'=>'1',
    'PRIORITY'=>'0'
   ),
    array(
    'NAME'=>'PUSHOVER_USER_KEY',
    'TITLE'=>'Pushover.net user key', 
    'TYPE'=>'text',
    'DEFAULT'=>'',
    'PRIORITY'=>'0'
   ), 
    array(
    'NAME'=>'PUSHOVER_LEVEL',
    'TITLE'=>'Pushover.net message minimum level', 
    'TYPE'=>'text',
    'DEFAULT'=>'1',
    'PRIORITY'=>'0'
   ),

   array(
    'NAME'=>'GROWL_ENABLE',
    'TITLE'=>'Forward notification to Growl service', 
    'TYPE'=>'onoff',
    'DEFAULT'=>'0',
    'PRIORITY'=>'43'
   ),

   array(
    'NAME'=>'GROWL_HOST',
    'TITLE'=>'Growl service hostname', 
    'TYPE'=>'text',
    'DEFAULT'=>'',
    'PRIORITY'=>'42'
   ), 

   array(
    'NAME'=>'GROWL_PASSWORD',
    'TITLE'=>'Growl service password (optional)', 
    'TYPE'=>'text',
    'DEFAULT'=>'',
    'PRIORITY'=>'41'
   ), 

    array(
    'NAME'=>'GROWL_LEVEL',
    'TITLE'=>'Growl notification minimum level', 
    'TYPE'=>'text',
    'DEFAULT'=>'1',
    'PRIORITY'=>'40'
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