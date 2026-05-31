<?php

error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_DEPRECATED));
mb_internal_encoding("UTF-8");


// get settings
$settings = SQLSelect('SELECT NAME, VALUE FROM settings');
$total = count($settings);

if (isset($_GET['theme']) && !defined('SETTINGS_THEME')) {
    Define('SETTINGS_THEME', $_GET['theme']);
}

if (isset($_GET['disable_websockets'])) {
    Define('DISABLE_WEBSOCKETS', 1);
}

if (isset($_GET['lang'])) {
    Define("SETTINGS_SITE_LANGUAGE", $_GET['lang']);
    $_SESSION['lang'] = SETTINGS_SITE_LANGUAGE;
} elseif (isset($_SESSION['lang'])) {
    Define("SETTINGS_SITE_LANGUAGE", $_SESSION['lang']);
}


for ($i = 0; $i < $total; $i++) {
    if ($settings[$i]['NAME'] == 'SITE_TIMEZONE' && !in_array($settings[$i]['VALUE'], DateTimeZone::listIdentifiers())) {
        continue;
    }
    Define('SETTINGS_' . $settings[$i]['NAME'], $settings[$i]['VALUE']);
}


if (!defined('SETTINGS_SITE_LANGUAGE')) {
    Define('SETTINGS_SITE_LANGUAGE', 'en');
}

if (!defined('GIT_URL')) {
    Define('GIT_URL', 'https://github.com/sergejey/majordomo/');
}
if (!isset($aditional_git_urls)) {
    $aditional_git_urls = array();
}

// language selection by settings
if (SETTINGS_SITE_LANGUAGE && file_exists(ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php')) {
    include_once(ROOT . 'languages/' . SETTINGS_SITE_LANGUAGE . '.php');
}
include_once(ROOT . 'languages/default.php');

if (LANG_SETTINGS_SITE_LANGUAGE_CODE) {
    Define('SETTINGS_SITE_LANGUAGE_CODE', LANG_SETTINGS_SITE_LANGUAGE_CODE);
} elseif (SETTINGS_SITE_LANGUAGE == 'en') {
    Define('SETTINGS_SITE_LANGUAGE_CODE', 'en_GB');
} else {
    Define('SETTINGS_SITE_LANGUAGE_CODE', '');
}

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


if (isset($_SERVER['REQUEST_METHOD']) &&
    ($_SERVER['REQUEST_METHOD'] == 'GET' || $_SERVER['REQUEST_METHOD'] == 'POST') &&
    defined('WAIT_FOR_MAIN_CYCLE') &&
    WAIT_FOR_MAIN_CYCLE == 1 &&
    !preg_match('/clear_all_history\.php/', $_SERVER['REQUEST_URI']) &&
    !preg_match('/diagnostic\.php/', $_SERVER['REQUEST_URI']) &&
    !preg_match('/\/ajax\//', $_SERVER['REQUEST_URI']) &&
    !preg_match('/\/api/', $_SERVER['REQUEST_URI']) &&
    !preg_match('/admin\.php/', $_SERVER['REQUEST_URI']) &&
    !preg_match('/xray\.html/', $_SERVER['REQUEST_URI']) &&
    !defined('NO_DATABASE_CONNECTION')
) {

    $maincycleUpdate = (int)getGlobal('ThisComputer.cycle_mainRun');
    $maincycleTimeout = 60;
    $maincycleTimePassed = time() - $maincycleUpdate;
    if ($maincycleTimePassed > $maincycleTimeout) { //main cycle is offline
        if ($_GET['system_call']) {
            echo "Main cycle is down (timeout: $maincycleTimePassed)";
        } else {
            echo "<html><head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
 <meta http-equiv=\"refresh\" content=\"10\">
<link rel=\"stylesheet\" href=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css\" integrity=\"sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u\" crossorigin=\"anonymous\">
<link rel=\"stylesheet\" href=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css\" integrity=\"sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp\" crossorigin=\"anonymous\">
<script src=\"https://code.jquery.com/jquery-3.3.1.min.js\" type=\"text/javascript\"></script>
<script src=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js\" integrity=\"sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa\" crossorigin=\"anonymous\"></script>
</head><body><div class=\"container\">";
            echo "&nbsp;<div class='alert alert-danger'>" . LANG_MAINCYCLEDOWN . "</div>";
            echo '<p>' . LANG_MAINCYCLEDOWN_DETAILS . '<br/>(timeout: ' . $maincycleTimePassed . ')</p>';
            echo "<div><a href='" . ROOTHTML . "diagnostic.php' target='_blank' class='btn btn-default'>" . LANG_SUBMIT_DIAGNOSTIC . "</a></div>&nbsp;";
            echo "<div>" . LANG_CONTROL_PANEL . ": <a href='" . ROOTHTML . "panel/xray.html?view_mode=services' class='btn btn-default'>Services</a>&nbsp;";
            echo "<a href='" . ROOTHTML . "panel/xray.html?view_mode=database' class='btn btn-default'>Database</a>&nbsp;";
            echo "<a href='" . ROOTHTML . "admin.php?md=panel&action=saverestore' class='btn btn-default'>" . LANG_SAVE_BACKUP . " / " . LANG_RESTORE . "</a>&nbsp;";
            echo "</div>&nbsp;";
            echo "</div></body></html>";
        }
        exit;
    }
}

if (isset($_SERVER['SERVER_ADDR']) && isset($_SERVER['SERVER_PORT'])) {
    Define('SERVER_URL', 'http://' . $_SERVER['HTTP_HOST']);
    Define('SERVER_ADDR', $_SERVER['SERVER_ADDR']);
} else {
    Define('SERVER_URL', 'http://localhost:80');
}

if (!defined('WEBSOCKETS_PORT'))
    Define('WEBSOCKETS_PORT', 8001);

// check external access
$home_network = '';
if (defined('SETTINGS_REMOTE_HOME_NETWORK') && SETTINGS_REMOTE_HOME_NETWORK != '') {
    $home_network = SETTINGS_REMOTE_HOME_NETWORK;
} elseif (defined('HOME_NETWORK') && HOME_NETWORK != '') {
    $home_network = HOME_NETWORK;
}

$ext_access_username = '';
if (defined('SETTINGS_REMOTE_EXT_ACCESS_USERNAME') && SETTINGS_REMOTE_EXT_ACCESS_USERNAME != '') {
    $ext_access_username = SETTINGS_REMOTE_EXT_ACCESS_USERNAME;
} elseif (defined('EXT_ACCESS_USERNAME') && EXT_ACCESS_USERNAME != '') {
    $ext_access_username = EXT_ACCESS_USERNAME;
}

$ext_access_password = '';
if (defined('SETTINGS_REMOTE_EXT_ACCESS_PASSWORD') && SETTINGS_REMOTE_EXT_ACCESS_PASSWORD != '') {
    $ext_access_password = SETTINGS_REMOTE_EXT_ACCESS_PASSWORD;
} elseif (defined('EXT_ACCESS_PASSWORD') && EXT_ACCESS_PASSWORD != '') {
    $ext_access_password = EXT_ACCESS_PASSWORD;
}

if ($home_network != ''
    && !isset($argv[0])
    && isset($_SERVER['REQUEST_URI'])
) {
    $home_network_pattern = preg_quote($home_network);
    $home_network_pattern = str_replace('\*', '\d+?', $home_network_pattern);
    $home_network_pattern = str_replace(',', ' ', $home_network_pattern);
    $home_network_pattern = str_replace('  ', ' ', $home_network_pattern);
    $home_network_pattern = str_replace(' ', '|', $home_network_pattern);

    $remoteAddr = $_SERVER["REMOTE_ADDR"];

    if (defined('LOCAL_IP') && LOCAL_IP != '') {
        $local_ip = LOCAL_IP;
    } else {
        $local_ip = '127.0.0.1';
    }

    if (getenv('HTTP_X_FORWARDED_FOR') != '') {
        $remoteAddr = getenv('HTTP_X_FORWARDED_FOR');
    }

    if (!preg_match('/' . $home_network_pattern . '/is', $remoteAddr) && $remoteAddr != $local_ip && trim($remoteAddr) != '::1') {
        if ($ext_access_username && $ext_access_password && $_SERVER['PHP_AUTH_USER'] == $ext_access_username && $_SERVER['PHP_AUTH_PW'] == $ext_access_password) {
            $data_message = $remoteAddr . " " . date("[d/m/Y:H:i:s]") . " Username and/or password valid. Login: " . $_SERVER['PHP_AUTH_USER'] . "\n";
            //DebMes($data_message, 'auth');
        } elseif (!$ext_access_username && !$ext_access_password) {
            $data_message = $remoteAddr . " " . date("[d/m/Y:H:i:s]") . " Username and/or password dont defined and dont needed" . "\n";
            //DebMes($data_message, 'auth');
        } else {
            header("WWW-Authenticate: Basic realm=\"" . PROJECT_TITLE . "\"");
            header("HTTP/1.0 401 Unauthorized");
            echo "Authorization required\n";
            $data_message = $remoteAddr . " " . date("[d/m/Y:H:i:s]") . " Username and/or password invalid. Login: " . $_SERVER['PHP_AUTH_USER'] . "\n";
            DebMes($data_message, 'auth');
            exit;
        }
    }
}
