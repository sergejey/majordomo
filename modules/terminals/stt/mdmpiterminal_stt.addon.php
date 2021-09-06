<?php

class mdmpiterminal_stt extends stt_addon
{
    
    function __construct($terminal)
    {
        $this->title       = "mdmTerminal2";

        $this->description = '<b>Описание:</b>&nbsp; Предназначен для устройств с ПО голосовых терминалов &nbsp;<a href="https://github.com/Aculeasis/mdmTerminal2">mdmTerminal2</a>. Фактически представляет собой тип терминала MajorDroid с расширенным API mdmTerminal2.<br>';
        $this->description .= '<b>Проверка доступности:</b>&nbsp;service_ping (пингование проводится проверкой состояния сервиса).<br>';
        $this->description .= '<b>Настройка:</b>&nbsp; Порт доступа по умолчанию 7999 (если по умолчанию, можно не указывать).<br>';
        $this->description .= '<b>Поддерживаемые возможности:</b>&nbsp;set_volume_notification(), say(), sayTo(), sayReply(), ask().';

        $this->terminal = $terminal;
        if (!$this->terminal['HOST']) return false;

        $this->setting     = json_decode($this->terminal['TTS_SETING'], true);
        $this->port = empty($this->setting['TTS_PORT']) ? 7999 : $this->setting['TTS_PORT'];
    }
    
    public function turnOn_stt() {
        return $this->sendCommand('listener:on');
    }
    
    public function turnOff_stt() {
        return $this->sendCommand('listener:off');
    }

    private function sendCommand($cmd)
    {
        if ($this->terminal['HOST']) {
            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($socket === false) {
                return false;
            }
            $result = socket_connect($socket, $this->terminal['HOST'], $this->port);
            if ($result === false) {
                return false;
            }
            $result = socket_write($socket, $cmd, strlen($cmd));
            usleep(500000);
            socket_close($socket);
            return $result;
        }
    }
}
