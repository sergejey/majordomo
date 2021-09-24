<?php

class yandexcloud_tts extends tts_addon
{
    
    function __construct($terminal)
    {
        
        $this->title       = "Yandex smart device cloud";
        $this->description = '<b>Описание:</b>&nbsp;Для работы использует &nbsp;<a href="https://mjdm.ru/forum/viewtopic.php?f=5&t=6922">модуль Яндекс девайс</a>. Без этого модуля ничего работать не будет.<br>';
        //$this->description .= '<b>Проверка доступности:</b>&nbsp;service_ping (пингование проводится проверкой состояния сервиса).<br>';
        $this->description .= '<b>Поддерживаемые возможности:</b>&nbsp;say(), sayTo(), sayReply().';
        
        $this->terminal = $terminal;
        if (!$this->terminal['HOST']) return false;
        
        unsubscribeFromEvent('yadevices', 'SAY');
        unsubscribeFromEvent('yadevices', 'SAYTO');
        unsubscribeFromEvent('yadevices', 'ASK');
        unsubscribeFromEvent('yadevices', 'SAYREPLY');
    }
    
    // Say
    function say_message($message, $terminal) {
        if (file_exists(DIR_MODULES . 'yadevices/yadevices.class.php')) {
            include(DIR_MODULES . 'yadevices/yadevices.class.php');
            $yandex_cloud = new yadevices();
            $station = SQLSelectOne("SELECT * FROM yastations WHERE TTS=2 AND IOT_ID!='' AND IP='".$this->terminal['HOST']."'");
            $yandex_cloud->sendCloudTTS($station['IOT_ID'],$message['MESSAGE']);
        }
        $this->success = TRUE;
        usleep(500000);
        return $this->success;
    }
 }

?>
