<?

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

 /**
 * Title
 *
 * Description
 *
 * @access public
 */
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
   )
   );


   foreach($settings as $k=>$v) {
    $rec=SQLSelectOne("SELECT * FROM settings WHERE NAME='".$v['NAME']."'");
    if (!$rec['ID']) {
     $rec['NAME']=$v['NAME'];
     $rec['VALUE']=$v['VALUE'];
     $rec['DEFAULTVALUE']=$v['DEFAULT'];
     $rec['TITLE']=$v['TITLE'];
     $rec['TYPE']=$v['TYPE'];
     $rec['ID']=SQLInsert('settings', $rec);
    } else {
     $rec['VALUE']=$v['VALUE'];
     SQLUpdate('settings', $rec);
    }
    Define('SETTINGS_'.$rec['NAME'], $v['VALUE']);
   }

   SaveFile(ROOT.'reboot', '1');

   $this->redirect("/");
  
 }

?>