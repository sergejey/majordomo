<?php

class yandex_tts extends tts_addon
{
    
    function __construct($terminal)
    {
        
        $this->title       = "Yandex module";
        $this->description = '<b>Описание:</b>&nbsp;Для работы использует&nbsp;<a href="https://connect.smartliving.ru/addons/category1/211.html">модуль YaDevices</a>. Без этого модуля ничего работать не будет.<br>';
        $this->description .= '<b>Поддерживаемые возможности:</b>&nbsp;say(), sayTo(), sayReply().';
        
        $this->terminal = $terminal;
        if (!$this->terminal['HOST']) return false;
        
        unsubscribeFromEvent('yadevices', 'SAY');
        //callAPI('/api/module/yadevices','GET',array('station'=>1,'command'=>'установи громкость 3'));
        //callAPI('/api/module/yadevices','GET',array('station'=>1,'command'=>'повтори за мной установлена вечерняя громкость'));
    }
    
    // Say
    public function say_message($message, $terminal) //SETTINGS_SITE_LANGUAGE_CODE=код языка
    {
        if (file_exists(DIR_MODULES . 'yadevices/yadevices.class.php')) {
            include_once (DIR_MODULES . 'yadevices/yadevices.class.php');
            $yadevice = new yadevices();
            $station = SQLSelectOne("SELECT * FROM yastations WHERE IP='".$this->terminal['HOST']."'");
            if (callAPI('/api/module/yadevices','GET',array('station'=>$station['ID'],'command'=>'повтори за мной '. $message['MESSAGE']))) {
                $this->success = TRUE;
            } else {
                $this->success = FALSE;
            }
        } else {
            $this->success = FALSE;
        }
        usleep(100000);
        return $this->success;
    }
    
     // ping terminal
    public function ping_ttsservice($host) {
        $this->success = TRUE;
        return $this->success;
    }
    
 }

?>
