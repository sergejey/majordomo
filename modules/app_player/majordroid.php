<?php

/**
 * Title
 *
 * Description
 *
 * @access public
 */

$address=$terminal['HOST']; // ip
$service_port='7999';

$in='';

if ($command == 'refresh')
{
   $out['PLAY'] = preg_replace('/\\\\$/is', '', $out['PLAY']);
   $path = str_replace('/', "\\", ($out['PLAY']));
   $in='play:'.$path;
}

if ($command == 'pause')
{
   $in='pause';
}

if ($command == 'fullscreen')
{
}

if ($command == 'next')
{
}

if ($command == 'prev')
{
}

if ($command == 'close')
{
}

if ($in!='') {
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "<br/>\n";
    return 0;
}
$result = socket_connect($socket, $address, $service_port);
if ($result === false) {
    echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
    return 0;
}
 socket_write($socket, $in, strlen($in));
 socket_close($socket);
}

?>