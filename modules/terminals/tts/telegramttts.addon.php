<?php

class telegramtts extends tts_addon {

    function __construct($terminal) {
        $this->title="GoogleHomeNotifier API";
        parent::__construct($terminal);
    }

    function say($phrase, $level = 0)
    {
        $users = SQLSelect("SELECT * FROM tlg_user WHERE HISTORY=1;");
        $c_users = count($users);
        if($phrase AND $c_users) {
            for($j = 0; $j < $c_users; $j++) {
                $user_id = $users[$j]['USER_ID'];
                if ($user_id === '0') {
                    $user_id = $users[$j]['NAME'];
                }
                $url=BASE_URL."/ajax/telegram.html?sendMessage=1&user=".$user_id."&text=".urlencode($phrase);
                getURLBackground($url,0);
        	}
         }
    }

}
