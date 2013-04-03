<?php

  function laurent_command($host, $command, $password='Laurent') {

   $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
   if ($socket === false) {
     echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
     return 0;
   }

   $result = socket_connect($socket, $host, 2424);
   if ($result === false) {
     echo "socket_connect( $socket , $host , 2424) failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
     return 0;
   }

    $in='$KE'."\r\n";
    socket_write($socket, $in, strlen($in));

    $res='';
    while ($out = socket_read($socket, 2048, PHP_NORMAL_READ)) {
     $res.=$out;
     if (is_integer(strpos($out, "\n"))) {
      break;
     }
    }

    $in='$KE,PSW,SET,'.$password."\r\n";
    socket_write($socket, $in, strlen($in));

    $res='';
    while ($out = socket_read($socket, 2048, PHP_NORMAL_READ)) {
     $res.=$out;
     if (is_integer(strpos($out, "\n"))) {
      break;
     }
    }

    $in=$command."\r\n";
    socket_write($socket, $in, strlen($in));

    $res='';
    while ($out = socket_read($socket, 2048, PHP_NORMAL_READ)) {
     $res.=$out;
     if (is_integer(strpos($out, "\n"))) {
      break;
     }
    }


   socket_close($socket);
   return trim($res);
   
  }

  function laurent_getStat($host, $password='Laurent') {

   $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
   if ($socket === false) {
     echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
     return 0;
   }

   $result = socket_connect($socket, $host, 2424);
   if ($result === false) {
     echo "socket_connect( $socket , $host , 2424) failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
     return 0;
   }

    $in='$KE'."\r\n";
    socket_write($socket, $in, strlen($in));

    $res='';
    while ($out = socket_read($socket, 2048, PHP_NORMAL_READ)) {
     $res.=$out;
     if (is_integer(strpos($out, "\n"))) {
      break;
     }
    }

    $in='$KE,PSW,SET,'.$password."\r\n";
    socket_write($socket, $in, strlen($in));

    $res='';
    while ($out = socket_read($socket, 2048, PHP_NORMAL_READ)) {
     $res.=$out;
     if (is_integer(strpos($out, "\n"))) {
      break;
     }
    }

    $command='$KE,DAT,ON';
    $in=$command."\r\n";
    socket_write($socket, $in, strlen($in));

    $res='';
    $started=0;
    while ($out = socket_read($socket, 2048, PHP_NORMAL_READ)) {
     $res.=$out;
     if (is_integer(strpos($out, "#TIME,"))) {
      if (!$started) {
       $started=1;
      } else {
       break;
      }
     }
    }

   $command='$KE,DAT,OFF';
   $in=$command."\r\n";
   socket_write($socket, $in, strlen($in));
   sleep(1);
   socket_write($socket, $in, strlen($in));
   socket_write($socket, $in, strlen($in));


   socket_close($socket);
   return trim($res);
   
  }



?>