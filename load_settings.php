<?php

if (!defined('ENVIRONMENT'))
   error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_DEPRECATED));


// get settings
$settings = SQLSelect('SELECT NAME, VALUE FROM settings');
$total    = count($settings);

for ($i = 0; $i < $total; $i ++)
   Define('SETTINGS_' . $settings[$i]['NAME'], $settings[$i]['VALUE']);

// language selection by settings
if (SETTINGS_SITE_LANGUAGE && file_exists(ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php'))
   include_once (ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php');

include_once (ROOT . 'languages/default.php');

if (defined('SETTINGS_SITE_TIMEZONE'))
{
   ini_set('date.timezone', SETTINGS_SITE_TIMEZONE);
   date_default_timezone_set(SETTINGS_SITE_TIMEZONE);
}

function timezone_offset_string( $offset )
{
        return sprintf( "%s%02d:%02d", ( $offset >= 0 ) ? '+' : '-', abs( $offset / 3600 ), abs( $offset % 3600 ) );
}
$offset = timezone_offset_get(new DateTimeZone(SETTINGS_SITE_TIMEZONE), new DateTime());
$offset_text=timezone_offset_string( $offset );
SQLExec("SET time_zone = '".$offset_text."';");

if (IsSet($_SERVER['SERVER_ADDR']) && IsSet($_SERVER['SERVER_PORT'])) {
 Define('SERVER_URL', 'http://' . $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT']);
 Define('SERVER_ADDR', $_SERVER['SERVER_ADDR']);
} else {
 Define('SERVER_URL','http://localhost:80');
}

if (!defined('WEBSOCKETS_PORT'))
   Define('WEBSOCKETS_PORT', 8001);

