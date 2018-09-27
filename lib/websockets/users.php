<?php

class WebSocketUser {

  public $socket;
  public $id;
  public $headers = array();
  public $handshake = false;

  public $handlingPartialPacket = false;
  public $partialBuffer = "";

  public $sendingContinuous = false;
  public $partialMessage = "";
  
  public $hasSentClose = false;

  function __construct($id, $socket) {
    $this->id = $id;
    $this->socket = $socket;
  }
}

class MyUser extends WebSocketUser {
  public $myId;
  public $ip;
  public $port;

  function __construct($id, $socket) {
    parent::__construct($id, $socket);
    
    socket_getpeername($socket,$this->ip,$this->port);          
    $this->myId = md5($this->ip . $this->port .  uniqid('u'));
 }
  
  function getClientID()
  {
      return $this->myId;
  }
  
  function getClientIP()
  {
      return $this->ip;
  }
}