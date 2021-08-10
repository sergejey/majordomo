<?php

/**
 * Ping host
 * @param mixed $host Host address
 * @return bool
 */
function pingip($host) {
    if (!$host) return false;
    if (IsWindowsOS())
        exec(sprintf('ping -n 1 %s', escapeshellarg($host)), $res, $rval);
    elseif (substr(php_uname(), 0, 7) === "FreeBSD")
        exec(sprintf('ping -c 1 -t 2 %s', escapeshellarg($host)), $res, $rval);
    else
        exec(sprintf('ping -c 1 -W 2 %s', escapeshellarg($host)), $res, $rval);

    return $rval === 0 && preg_match('/ttl/is', join('', $res));
}

function pingurl($host) {
    if (!$host) return false;
    //web host
    $online = getURL(processTitle($host['HOSTNAME']), 0);
    SaveFile("./cached/host_".$host['ID'].'.html', $online);
    if ($host['SEARCH_WORD'] != '' && !is_integer(strpos($online, $host['SEARCH_WORD']))) {
        $result = 0;
    }
    if ($online) {
        $result = 1;
    }
    return $result;
}

function pingbt($host) {
    if (!$host) return false;
    $data = exec('l2ping '.$host.' -c1 -f | awk \'/loss/ {print $3}\'');
    if (intval($data) > 0) {
        $result = 1;
    } else {
        $result = 0;
    }
    return $result;
}

function pinghostport($host) {
    if (!$host) return false;
    $hostport = explode(":",$host['HOSTNAME']);
    $connection = @fsockopen($hostport[0],$hostport[1],$errno,$errstr,1);
    if (is_resource($connection)) {
        $result=1;
    fclose($connection);
    }
    return $result;
}

function pingble($host) {
    if (!$host) return false;
    $data = exec('sudo timeout -s SIGINT 5s hcitool lescan | grep "'.$host.'"');
    if (stristr($data, $host)) {
        $result = 1;
    } else {
        $result = 0;
    }
    return $result;
}