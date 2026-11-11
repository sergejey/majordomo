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
            // restore only real content, not an interrupted TTS message (file is a URL: /cms/cached/voice/...)
            if (is_array($played) && ($played['state'] == 'playing') && !preg_match('#cms.cached.voice#i', $played['file'])) {
                addScheduledJob('allsay-target-' . $this->terminal['TITLE'] . '-number-99999999998', "playMedia('" . $played['file'] . "', '" . $this->terminal['TITLE'] . "',1);", time() + 100, 4);
                addScheduledJob('allsay-target-' . $this->terminal['TITLE'] . '-number-99999999999', "seekPlayerPosition('" . $this->terminal['TITLE'] . "'," . $played['time'] . ");", time() + 110, 4);
            }
        }

        // dobavlyaem soobshenie v konec potom otsortituem
        $time_shift = 2 + (int)getMediaDurationSeconds($cached_file); // необходимая задержка для перезапуска проигрывателя на факте 2 секунды
        //DebMes("Add new message".$last_mesage,'terminals');
        // The slot is counted from the job start, but the player starts sounding later (HTTP fetch + buffering),
        // so the next message could cut the end of the previous one: wait (max ~4.5 s) while the player
        // is still playing a TTS file, then start this message.
        $command = 'for ($i = 0; $i < 15; $i++) {'
            . ' $st = getPlayerStatus(\'' . $this->terminal['NAME'] . '\');'
            . ' if (!is_array($st) || $st[\'state\'] != \'playing\' || !preg_match(\'#cms.cached.voice#i\', $st[\'file\'])) break;'
            . ' usleep(300000);'
            . ' }'
            . " playMedia('" . $cached_file_url . "', '" . $this->terminal['TITLE'] . "');";
        addScheduledJob('allsay-target-' . $this->terminal['TITLE'] . '-number-' . $number_message, $command, time() + 1, $time_shift);

        // vibiraem vse soobsheniya dla terminala s sortirovkoy po nazvaniyu
        $all_messages = SQLSelect("SELECT * FROM jobs WHERE TITLE LIKE'" . 'allsay-target-' . $this->terminal['TITLE'] . '-number-' . "%' ORDER BY `TITLE` ASC");
        $first_fields = reset($all_messages);
        $runtime = (strtotime($first_fields['RUNTIME']));
        $prev_message = array();
        foreach ($all_messages as $message) {
            $expire = (strtotime($message['EXPIRE'])) - (strtotime($message['RUNTIME']));
            $rec['ID'] = $message['ID'];
            $rec['TITLE'] = $message['TITLE'];
            $rec['COMMANDS'] = $message['COMMANDS'];
            $rec['RUNTIME'] = date('Y-m-d H:i:s', $runtime);
            $rec['EXPIRE'] = date('Y-m-d H:i:s', $runtime + $expire);
            // proverka i udaleniye odinakovih soobsheniy
            if (isset($prev_message['TITLE']) && $prev_message['TITLE'] == $message['TITLE']) {
                SQLExec("DELETE FROM jobs WHERE ID='" . $rec['ID'] . "'");
            } else {
                SQLUpdate('jobs', $rec);
            }
            $runtime = $runtime + $expire;
            $prev_message = $message;
        }
    }
}
