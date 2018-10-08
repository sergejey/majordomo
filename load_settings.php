<?php
error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_DEPRECATED));

mb_internal_encoding("UTF-8");

// get settings
$settings = SQLSelect('SELECT NAME, VALUE FROM settings');
$total = count($settings);

if (IsSet($_GET['theme'])) {
    Define('SETTINGS_THEME', $_GET['theme']);
}

if (IsSet($_GET['disable_websockets'])) {
    Define('DISABLE_WEBSOCKETS', 1);
}

if ($_GET['lang']) {
    Define("SETTINGS_SITE_LANGUAGE", $_GET['lang']);
    $_SESSION['lang'] = SETTINGS_SITE_LANGUAGE;
} elseif ($_SESSION['lang']) {
    Define("SETTINGS_SITE_LANGUAGE", $_SESSION['lang']);
}

for ($i = 0; $i < $total; $i++)
    Define('SETTINGS_' . $settings[$i]['NAME'], $settings[$i]['VALUE']);

if (!defined('SETTINGS_SITE_LANGUAGE')) {
    Define('SETTINGS_SITE_LANGUAGE', 'en');
}

if (!defined('GIT_URL')) {
    Define('GIT_URL', 'https://github.com/sergejey/majordomo/');
}
// language selection by settings
if (SETTINGS_SITE_LANGUAGE && file_exists(ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php')) {
    include_once(ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php');
}

include_once(ROOT . 'languages/default.php');

if (!defined('SETTINGS_SITE_TIMEZONE')) {
    Define('SETTINGS_SITE_TIMEZONE', 'Europe/Minsk');
}

ini_set('date.timezone', SETTINGS_SITE_TIMEZONE);
date_default_timezone_set(SETTINGS_SITE_TIMEZONE);

function timezone_offset_string($offset)
{
    return sprintf("%s%02d:%02d", ($offset >= 0) ? '+' : '-', abs($offset / 3600), abs($offset % 3600));
}

$offset = timezone_offset_get(new DateTimeZone(SETTINGS_SITE_TIMEZONE), new DateTime());
$offset_text = timezone_offset_string($offset);
SQLExec("SET time_zone = '" . $offset_text . "';");


if (($_SERVER['REQUEST_METHOD'] == 'GET' || $_SERVER['REQUEST_METHOD'] == 'POST') &&
    defined('WAIT_FOR_MAIN_CYCLE') &&
    WAIT_FOR_MAIN_CYCLE == 1 &&
    !preg_match('/clear_all_history\.php/', $_SERVER['REQUEST_URI']) &&
    !preg_match('/diagnostic\.php/', $_SERVER['REQUEST_URI']) &&
    !preg_match('/admin\.php/', $_SERVER['REQUEST_URI']) &&
    !preg_match('/xray\.html/', $_SERVER['REQUEST_URI']) &&
    !defined('NO_DATABASE_CONNECTION')
) {
    $maincycleUpdate = getGlobal('cycle_mainRun');
    $maincycleTimeout = 60;
    if ((time() - $maincycleUpdate) > $maincycleTimeout) { //main cycle is offline
        echo "<html><head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
 <meta http-equiv=\"refresh\" content=\"10\">
<link rel=\"stylesheet\" href=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css\" integrity=\"sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u\" crossorigin=\"anonymous\">
<link rel=\"stylesheet\" href=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css\" integrity=\"sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp\" crossorigin=\"anonymous\">
<script src=\"https://code.jquery.com/jquery-3.3.1.min.js\" type=\"text/javascript\"></script>
<script src=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js\" integrity=\"sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa\" crossorigin=\"anonymous\"></script>
</head><body><div class=\"container\">";
        echo "&nbsp;<div class='alert alert-danger'>".LANG_MAINCYCLEDOWN."</div>";
        echo '<p>'.LANG_MAINCYCLEDOWN_DETAILS.'</p>';
        echo "<div><a href='".ROOTHTML."diagnostic.php' target='_blank' class='btn btn-default'>" . LANG_SUBMIT_DIAGNOSTIC . "</a></div>&nbsp;";
        echo "<div>".LANG_CONTROL_PANEL.": <a href='".ROOTHTML."panel/xray.html?view_mode=services' class='btn btn-default'>Services</a>&nbsp;";
        echo "<a href='".ROOTHTML."panel/xray.html?view_mode=database' class='btn btn-default'>Database</a>&nbsp;";
        echo "<a href='".ROOTHTML."admin.php?md=panel&action=saverestore' class='btn btn-default'>".LANG_SAVE_BACKUP." / ".LANG_RESTORE."</a>&nbsp;";
        echo "</div>&nbsp;";
        echo "</div></body></html>";
        exit;
    }
}


if (IsSet($_SERVER['SERVER_ADDR']) && IsSet($_SERVER['SERVER_PORT'])) {
    Define('SERVER_URL', 'http://' . $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT']);
    Define('SERVER_ADDR', $_SERVER['SERVER_ADDR']);
} else {
    Define('SERVER_URL', 'http://localhost:80');
}

if (!defined('WEBSOCKETS_PORT'))
    Define('WEBSOCKETS_PORT', 8001);

