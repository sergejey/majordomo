<?php

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

if (IsSet($_SERVER['SERVER_ADDR']) && IsSet($_SERVER['SERVER_PORT'])) {
 Define('SERVER_URL', 'http://' . $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT']);
} else {
 Define('SERVER_URL','http://localhost:80');
}

if (!defined('ENVIRONMENT'))
   error_reporting(E_ALL & ~(E_STRICT | E_NOTICE));
