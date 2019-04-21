<?php

class mediaplayer extends tts_addon
{
    function __construct($terminal)
    {
        $this->title = "MediaPlayer";
        parent::__construct($terminal);
    }

    public function sayCached($phrase, $level = 0, $cached_file = '')
    {
        // poluchaem adress cashed files dlya zapuska ego na vosproizvedeniye
        if (preg_match('/\/cms\/cached.+/', $cached_file, $m)) {
            $server_ip = getLocalIp();
            if (!$server_ip) {
                //DebMes("Server IP not found", 'terminals');
                return false;
            } else {
                $cached_file_url = 'http://' . $server_ip . $m[0];
            }
        } else {
            //DebMes("Unknown file path format: " . $cached_filename, 'terminals');
            return false;
        }

        // berem vse soobsheniya iz shoots dlya poiska soobsheniya s takoy frazoy
        $messages = SQLSelect("SELECT * FROM shouts ORDER BY ID DESC LIMIT 0 , 100");
        foreach ($messages as $message) {
            if ($phrase == $message['MESSAGE']) {
                $number_message = $message['ID'];
                break;
            }
        }

        // получаем данные оплеере для восстановления проигрываемого контента
        $chek_restore = SQLSelectOne("SELECT * FROM jobs WHERE TITLE LIKE'" . 'allsay-target-' . $this->terminal['TITLE'] . '-number-' . "99999999998'");
        if (!$chek_restore) {
            $played = getPlayerStatus($this->terminal['NAME']);
            if (($played['state'] == 'playing') and (stristr($played['file'], 'cms\cached\voice') === FALSE)) {
                addScheduledJob('allsay-target-' . $this->terminal['TITLE'] . '-number-99999999998', "playMedia('" . $played['file'] . "', '" . $this->terminal['TITLE'] . "',1);", time() + 100, 4);
                addScheduledJob('allsay-target-' . $this->terminal['TITLE'] . '-number-99999999999', "seekPlayerPosition('" . $this->terminal['TITLE'] . "'," . $played['time'] . ");", time() + 110, 4);
            }
        }

        // dobavlyaem soobshenie v konec potom otsortituem
        $time_shift = 2 + (int)getMediaDurationSeconds($cached_file); // необходимая задержка для перезапуска проигрівателя на факте 2 секундЫ
        //DebMes("Add new message".$last_mesage,'terminals');
        addScheduledJob('allsay-target-' . $this->terminal['TITLE'] . '-number-' . $number_message, "playMedia('" . $cached_file_url . "', '" . $this->terminal['TITLE'] . "');", time() + 1, $time_shift);

        // vibiraem vse soobsheniya dla terminala s sortirovkoy po nazvaniyu
        $all_messages = SQLSelect("SELECT * FROM jobs WHERE TITLE LIKE'" . 'allsay-target-' . $this->terminal['TITLE'] . '-number-' . "%' ORDER BY `TITLE` ASC");
        $first_fields = reset($all_messages);
        $runtime = (strtotime($first_fields['RUNTIME']));
        $prev_message = '';
        foreach ($all_messages as $message) {
            $expire = (strtotime($message['EXPIRE'])) - (strtotime($message['RUNTIME']));
            $rec['ID'] = $message['ID'];
            $rec['TITLE'] = $message['TITLE'];
            $rec['COMMANDS'] = $message['COMMANDS'];
            $rec['RUNTIME'] = date('Y-m-d H:i:s', $runtime);
            $rec['EXPIRE'] = date('Y-m-d H:i:s', $runtime + $expire);
            // proverka i udaleniye odinakovih soobsheniy
            if ($prev_message['TITLE'] == $message['TITLE']) {
                SQLExec("DELETE FROM jobs WHERE ID='" . $rec['ID'] . "'");
            } else {
                SQLUpdate('jobs', $rec);
            }
            $runtime = $runtime + $expire;
            $prev_message = $message;
        }
    }
}