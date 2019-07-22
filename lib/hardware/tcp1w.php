<?php


  function tcp1w_command($host, $port, $command) {

   $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
   if ($socket === false) {
     echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
     return 0;
   }

   $result = socket_connect($socket, $host, $port);
   if ($result === false) {
     echo "socket_connect( $socket , $host , $port) failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
     return 0;
   }

    $in=$command;
    socket_write($socket, $in, strlen($in));

    $res='';

    $res=socket_read($socket, 2048, PHP_BINARY_READ);

    if ($res) {
     $total=strlen($res);
     for($i=0;$i<$total;$i++) {
      $ar[]=ord($res[$i]);
     }
    } else {
     $ar=0;
    }

   socket_close($socket);
   return $ar;

  }

 // --------------------------------------------------------------------------------
  function tcp1w_getTemp($host, $port) {
   $command=chr(0xbb).chr(0x80).chr(0x00).chr(0xff);
   $res=tcp1w_command($host, $port, $command);
   $ar=array();
   if ($res[3]) {
    $total=$res[3];
    for($i=0;$i<$total;$i++) {
     $temp=round(($res[4+$i*2]*256+$res[4+$i*2+1])*0.0625, 2);
     $ar[]=$temp;
    }
    return $ar;
   } else {
    return 0;
   }
  }


 // --------------------------------------------------------------------------------
  function tcp1w_turnRelayOn($host, $port) {
   $command=chr(0xbb).chr(0x83).chr(0x02).chr(0x01).chr(0x01).chr(0xff);
   $res=tcp1w_command($host, $port, $command);
  }

 // --------------------------------------------------------------------------------
  function tcp1w_turnRelayOff($host, $port) {
   $command=chr(0xbb).chr(0x83).chr(0x02).chr(0x01).chr(0x00).chr(0xff);
   $res=tcp1w_command($host, $port, $command);
  }

 // --------------------------------------------------------------------------------
  function tcp1w_getRelayState($host, $port) {
   $command=chr(0xbb).chr(0x80).chr(0x03).chr(0xff);
   $res=tcp1w_command($host, $port, $command);
   return $res[4];
  }




?>